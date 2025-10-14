<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\DriverMatch;
use App\Models\CompanyRequest;
use App\Models\Drivers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DriverJobController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:driver');
    }

    public function index()
    {
        $driver = Auth::guard('driver')->user();

        $currentJobs = DriverMatch::where('driver_id', $driver->id)
            ->with(['companyRequest.company'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $availableJobs = CompanyRequest::whereDoesntHave('matches', function ($query) use ($driver) {
                $query->where('driver_id', $driver->id);
            })
            ->where('status', 'open')
            ->with('company')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('driver.jobs.index', compact('currentJobs', 'availableJobs'));
    }

    public function history()
    {
        $driver = Auth::guard('driver')->user();
        
        $completedJobs = DriverMatch::where('driver_id', $driver->id)
            ->whereIn('status', ['completed', 'declined', 'cancelled'])
            ->with(['companyRequest.company', 'commission'])
            ->orderBy('updated_at', 'desc')
            ->paginate(10);

        return view('driver.jobs.history', compact('completedJobs'));
    }

    public function availableJobs()
    {
        $driver = Auth::guard('driver')->user();

        $availableJobs = CompanyRequest::whereDoesntHave('matches', function ($query) use ($driver) {
                $query->where('driver_id', $driver->id);
            })
            ->where('status', 'open')
            ->with('company')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('driver.jobs.available', compact('availableJobs'));
    }

    public function show(DriverMatch $match)
    {
        $this->authorizeDriverMatch($match);
        
        $match->load(['companyRequest.company', 'commission']);

        return view('driver.jobs.show', compact('match'));
    }

    public function accept(DriverMatch $match)
    {
        $this->authorizeDriverMatch($match);

        if ($match->status !== 'pending') {
            return redirect()->back()->with('error', 'This job is no longer available for acceptance.');
        }

        if ($match->accept()) {
            return redirect()->route('driver.jobs.show', $match)
                ->with('success', 'Job accepted successfully!');
        }

        return redirect()->back()->with('error', 'Failed to accept the job. Please try again.');
    }

    public function decline(DriverMatch $match, Request $request)
    {
        $this->authorizeDriverMatch($match);

        if ($match->status !== 'pending') {
            return redirect()->back()->with('error', 'This job is no longer available.');
        }

        $request->validate([
            'reason' => 'nullable|string|max:255'
        ]);

        if ($match->decline()) {
            if ($request->filled('reason')) {
                $match->update(['driver_feedback' => $request->reason]);
            }

            return redirect()->route('driver.jobs.index')
                ->with('success', 'Job declined successfully.');
        }

        return redirect()->back()->with('error', 'Failed to decline the job. Please try again.');
    }

    public function markComplete(DriverMatch $match, Request $request)
    {
        $this->authorizeDriverMatch($match);

        if ($match->status !== 'accepted') {
            return redirect()->back()->with('error', 'Only accepted jobs can be marked as complete.');
        }

        $request->validate([
            'feedback' => 'nullable|string|max:500',
            'rating' => 'nullable|integer|min:1|max:5'
        ]);

        DB::transaction(function () use ($match, $request) {
            $updateData = [
                'status' => 'completed',
                'completed_at' => now(),
            ];

            if ($request->filled('feedback')) {
                $updateData['driver_feedback'] = $request->feedback;
            }

            if ($request->filled('rating')) {
                $updateData['company_rating'] = $request->rating;
            }

            $match->update($updateData);

            if (!$match->commission_amount) {
                $commissionAmount = $match->calculateCommission();
                $match->update(['commission_amount' => $commissionAmount]);
            }
        });

        return redirect()->route('driver.jobs.show', $match)
            ->with('success', 'Job marked as complete successfully!');
    }

    public function updateJobAlerts(Request $request)
    {
        $request->validate([
            'email_notifications' => 'boolean',
            'sms_notifications' => 'boolean',
            'job_categories' => 'nullable|array',
            'location_radius' => 'nullable|integer|min:1|max:100'
        ]);

        $driver = Auth::guard('driver')->user();

        $preferences = $driver->preferences ?? [];
        $preferences['job_alerts'] = [
            'email_notifications' => $request->boolean('email_notifications'),
            'sms_notifications' => $request->boolean('sms_notifications'),
            'job_categories' => $request->input('job_categories', []),
            'location_radius' => $request->input('location_radius', 10)
        ];

        $driver->update(['preferences' => $preferences]);

        return response()->json([
            'success' => true,
            'message' => 'Job alert preferences updated successfully!'
        ]);
    }

    private function authorizeDriverMatch(DriverMatch $match)
    {
        $driver = Auth::guard('driver')->user();
        
        if ($match->driver_id !== $driver->id) {
            abort(403, 'Unauthorized access to this job.');
        }
    }
}