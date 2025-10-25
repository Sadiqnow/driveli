<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Fleet;
use App\Services\FleetService;

class FleetController extends Controller
{
    protected $fleetService;

    public function __construct(FleetService $fleetService)
    {
        $this->fleetService = $fleetService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $company = Auth::guard('company')->user();

        $fleets = Fleet::where('company_id', $company->id)
            ->withCount('vehicles')
            ->latest()
            ->paginate(15);

        $stats = [
            'total_fleets' => Fleet::where('company_id', $company->id)->count(),
            'active_vehicles' => Fleet::where('company_id', $company->id)
                ->join('vehicles', 'fleets.id', '=', 'vehicles.fleet_id')
                ->where('vehicles.status', 'active')
                ->count(),
            'total_vehicles' => Fleet::where('company_id', $company->id)
                ->join('vehicles', 'fleets.id', '=', 'vehicles.fleet_id')
                ->count(),
        ];

        return view('company.fleet', compact('fleets', 'stats'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $company = Auth::guard('company')->user();

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        try {
            $fleetData = $request->all();
            $fleetData['company_id'] = $company->id;

            $fleet = $this->fleetService->createFleet($fleetData);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Fleet created successfully!',
                    'data' => $fleet
                ]);
            }

            return redirect()->route('company.fleets.index')
                ->with('success', 'Fleet created successfully!');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create fleet'
                ], 500);
            }

            return back()->withErrors(['error' => 'Failed to create fleet. Please try again.'])->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Fleet $fleet)
    {
        $this->authorize('view', $fleet);

        $fleet->load(['vehicles', 'company']);

        return view('company.fleets.show', compact('fleet'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Fleet $fleet)
    {
        $this->authorize('update', $fleet);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        try {
            $this->fleetService->updateFleet($fleet, $request->all());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Fleet updated successfully!'
                ]);
            }

            return redirect()->route('company.fleets.show', $fleet)
                ->with('success', 'Fleet updated successfully!');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update fleet'
                ], 500);
            }

            return back()->withErrors(['error' => 'Failed to update fleet. Please try again.'])->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Fleet $fleet)
    {
        $this->authorize('delete', $fleet);

        try {
            $this->fleetService->deleteFleet($fleet);

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Fleet deleted successfully!'
                ]);
            }

            return redirect()->route('company.fleets.index')
                ->with('success', 'Fleet deleted successfully!');
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete fleet'
                ], 500);
            }

            return back()->withErrors(['error' => 'Failed to delete fleet. Please try again.']);
        }
    }
}
