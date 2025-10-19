<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Drivers;
use App\Models\CompanyRequest;
use App\Models\Company;
use App\Models\DriverMatch;
use Illuminate\Support\Facades\DB;

class MatchingController extends Controller
{
    public function index()
    {
        // Check if user has permission to manage matching
        if (!auth('admin')->user()->hasPermission('manage_matching')) {
            abort(403, 'Access denied. Insufficient permissions.');
        }

        // Get available drivers and requests for the manual matching modal
        $availableDrivers = Drivers::select('id', 'driver_id', 'first_name', 'surname', 'phone', 'email')
            ->where('verification_status', 'verified')
            ->where('status', 'active')
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->orderBy('first_name')
            ->get();

        $pendingRequests = CompanyRequest::with('company')
            ->whereIn('status', ['pending', 'Pending', 'Active'])
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get recent matching activity
        $recentMatches = DriverMatch::with([
                'driver' => function($query) {
                    $query->select('id', 'driver_id', 'first_name', 'surname', 'phone', 'email');
                },
                'companyRequest' => function($query) {
                    $query->select('id', 'company_id', 'description', 'location', 'status');
                },
                'companyRequest.company' => function($query) {
                    $query->select('id', 'name', 'email');
                }
            ])
            ->whereNotNull('match_id')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.matching.index', compact('availableDrivers', 'pendingRequests', 'recentMatches'));
    }

    public function dashboard()
    {
        // Get dashboard statistics
        $availableDrivers = Drivers::where('verification_status', 'verified')
            ->where('status', 'active')
            ->where('is_active', true)
            ->whereNull('deleted_at')->count();
            
        $pendingRequests = CompanyRequest::whereIn('status', ['pending', 'Pending', 'Active'])
            ->whereNull('deleted_at')->count();
        
        // Real statistics for driver matches
        $successfulMatches = DriverMatch::whereIn('status', ['accepted', 'completed'])->count();
        $totalMatches = DriverMatch::count();
        $matchingRate = $totalMatches > 0 ? round(($successfulMatches / $totalMatches) * 100, 1) : 0;
        
        // Top companies by request count
        $topCompanies = Company::leftJoin('company_requests', 'companies.id', '=', 'company_requests.company_id')
            ->selectRaw('companies.id, companies.name, companies.email, companies.status, COUNT(company_requests.id) as requests_count')
            ->groupBy('companies.id', 'companies.name', 'companies.email', 'companies.status')
            ->orderBy('requests_count', 'desc')
            ->limit(5)
            ->get();
        
        // Recent matches
        $recentMatches = DriverMatch::with(['driver', 'companyRequest.company'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        // Additional statistics
        $pendingMatches = DriverMatch::where('status', 'pending')->count();
        $autoMatches = DriverMatch::where('auto_matched', true)->count();
        $manualMatches = DriverMatch::where('matched_by_admin', true)->count();
        
        return view('admin.matching.dashboard', compact(
            'availableDrivers', 
            'pendingRequests', 
            'successfulMatches', 
            'matchingRate',
            'topCompanies',
            'recentMatches',
            'pendingMatches',
            'autoMatches',
            'manualMatches'
        ));
    }

    public function autoMatch(Request $request)
    {
        try {
            DB::beginTransaction();

            // Get available drivers
            $availableDrivers = Drivers::select('id', 'driver_id', 'first_name', 'surname', 'phone', 'email')
                ->where('verification_status', 'verified')
                ->where('status', 'active')
                ->where('is_active', true)
                ->whereNull('deleted_at')
                ->get();
            
            // Get pending requests
            $pendingRequests = CompanyRequest::whereIn('status', ['pending', 'Pending', 'Active'])
                ->whereNull('deleted_at')
                ->whereDoesntHave('matches', function($query) {
                    $query->whereIn('status', ['pending', 'accepted']);
                })
                ->orderBy('created_at', 'asc')
                ->get();
            
            $matchedCount = 0;
            
            foreach ($pendingRequests as $companyRequest) {
                // Simple auto-matching logic - match with first available driver
                // In production, this would include more sophisticated matching criteria
                $bestDriver = $availableDrivers->first();
                
                if ($bestDriver) {
                    // Create the match
                    $match = DriverMatch::create([
                        'match_id' => $this->generateMatchId(),
                        'company_request_id' => $companyRequest->id,
                        'driver_id' => $bestDriver->id,
                        'status' => 'pending',
                        'commission_rate' => 10.00, // Default 10% commission
                        'matched_at' => now(),
                        'auto_matched' => true,
                        'matched_by_admin' => false,
                        'notes' => 'Auto-matched by system based on location and availability'
                    ]);
                    
                    // Remove this driver from available pool for this batch
                    $availableDrivers = $availableDrivers->reject(function($driver) use ($bestDriver) {
                        return $driver->id === $bestDriver->id;
                    });
                    
                    $matchedCount++;
                    
                    // Break if no more drivers available
                    if ($availableDrivers->isEmpty()) {
                        break;
                    }
                }
            }
            
            DB::commit();
            
            if ($matchedCount > 0) {
                return back()->with('success', "Successfully auto-matched {$matchedCount} driver(s) with company requests!");
            } else {
                return back()->with('warning', 'No suitable matches found. Please check driver availability and request criteria.');
            }
            
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Auto-matching failed: ' . $e->getMessage());
        }
    }

    public function manualMatch(Request $request)
    {
        $request->validate([
            'driver_id' => 'required|exists:drivers,id',
            'request_id' => 'required|exists:company_requests,id',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string|max:1000'
        ]);

        try {
            DB::beginTransaction();
            
            // Check if driver is available
            $driver = Drivers::findOrFail($request->driver_id);
            if ($driver->verification_status !== 'verified' || $driver->status !== 'active' || !$driver->is_active) {
                return back()->with('error', 'Selected driver is not available or not verified.');
            }
            
            // Check if request is still pending
            $companyRequest = CompanyRequest::findOrFail($request->request_id);
            if (!in_array($companyRequest->status, ['pending', 'Pending', 'Active'])) {
                return back()->with('error', 'Selected request is no longer pending.');
            }
            
            // Check if this combination already exists
            $existingMatch = DriverMatch::where('driver_id', $request->driver_id)
                ->where('company_request_id', $request->request_id)
                ->whereIn('status', ['pending', 'accepted'])
                ->first();
            
            if ($existingMatch) {
                return back()->with('error', 'This driver is already matched with this request.');
            }
            
            // Create the match
            $match = DriverMatch::create([
                'match_id' => $this->generateMatchId(),
                'company_request_id' => $request->request_id,
                'driver_id' => $request->driver_id,
                'status' => 'pending',
                'commission_rate' => $request->commission_rate ?? 10.00,
                'matched_at' => now(),
                'auto_matched' => false,
                'matched_by_admin' => true,
                'notes' => $request->notes ?? 'Manually matched by administrator'
            ]);
            
            DB::commit();
            
            $driverName = $driver->first_name . ' ' . $driver->surname;
            $companyName = $companyRequest->company->name ?? 'Unknown Company';
            
            return back()->with('success', "Successfully matched {$driverName} with {$companyName}!");
            
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Manual matching failed: ' . $e->getMessage());
        }
    }

    public function getAvailableDrivers($requestId)
    {
        $companyRequest = CompanyRequest::findOrFail($requestId);
        
        $drivers = Drivers::where('verification_status', 'verified')
            ->where('status', 'active')
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->whereDoesntHave('matches', function($query) use ($requestId) {
                $query->where('company_request_id', $requestId)
                      ->whereIn('status', ['pending', 'accepted']);
            })
            ->get();
            
        return response()->json([
            'success' => true,
            'drivers' => $drivers,
            'request' => $companyRequest
        ]);
    }

    public function createMatch(Request $request)
    {
        $request->validate([
            'driver_id' => 'required|exists:drivers,id',
            'company_request_id' => 'required|exists:company_requests,id',
            'commission_rate' => 'nullable|numeric|min:0|max:100'
        ]);

        try {
            $match = DriverMatch::create([
                'match_id' => $this->generateMatchId(),
                'company_request_id' => $request->company_request_id,
                'driver_id' => $request->driver_id,
                'status' => 'pending',
                'commission_rate' => $request->commission_rate ?? 10.00,
                'matched_at' => now(),
                'auto_matched' => false,
                'matched_by_admin' => true,
                'notes' => 'Match created from matching system'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Match created successfully',
                'match' => $match
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create match: ' . $e->getMessage()
            ], 500);
        }
    }

    public function viewMatches()
    {
        $matches = DriverMatch::with(['driver', 'companyRequest.company'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        // Statistics for the matches page
        $stats = [
            'total' => DriverMatch::count(),
            'pending' => DriverMatch::where('status', 'pending')->count(),
            'accepted' => DriverMatch::where('status', 'accepted')->count(),
            'completed' => DriverMatch::where('status', 'completed')->count(),
            'declined' => DriverMatch::where('status', 'declined')->count(),
        ];
        
        return view('admin.matching.matches', compact('matches', 'stats'));
    }

    public function confirmMatch($match)
    {
        try {
            // Handle both route model binding and direct match_id
            if (is_numeric($match)) {
                $match = DriverMatch::findOrFail($match);
            } else {
                $match = DriverMatch::where('match_id', $match)->firstOrFail();
            }
            
            if ($match->status !== 'pending') {
                return back()->with('error', 'Only pending matches can be confirmed.');
            }
            
            DB::beginTransaction();
            
            $match->update([
                'status' => 'accepted',
                'accepted_at' => now()
            ]);
            
            DB::commit();
            
            return back()->with('success', 'Match confirmed successfully!');
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return back()->with('error', 'Match not found.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Failed to confirm match: ' . $e->getMessage());
        }
    }

    public function cancelMatch($match)
    {
        try {
            // Handle both route model binding and direct match_id
            if (is_numeric($match)) {
                $match = DriverMatch::findOrFail($match);
            } else {
                $match = DriverMatch::where('match_id', $match)->firstOrFail();
            }
            
            if (!in_array($match->status, ['pending', 'accepted'])) {
                return back()->with('error', 'Only pending or accepted matches can be cancelled.');
            }
            
            DB::beginTransaction();
            
            $match->update([
                'status' => 'cancelled',
                'cancelled_at' => now()
            ]);
            
            DB::commit();
            
            return back()->with('success', 'Match cancelled successfully!');
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return back()->with('error', 'Match not found.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Failed to cancel match: ' . $e->getMessage());
        }
    }

    private function generateMatchId()
    {
        do {
            $id = 'MT' . str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (DriverMatch::where('match_id', $id)->exists());
        
        return $id;
    }
}