<?php

namespace App\Services;

use App\Models\Drivers as Driver;
use App\Models\CompanyRequest;
use App\Models\DriverMatch;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Collection;

class DashboardActivityService
{
    /**
     * Get recent dashboard activities
     */
    public function getRecentActivity(): Collection
    {
        $activities = collect();

        // Add different types of activities
        $activities = $activities->merge($this->getUserActivities());
        $activities = $activities->merge($this->getDriverActivities());
        $activities = $activities->merge($this->getRequestActivities());
        $activities = $activities->merge($this->getMatchActivities());

        // Sort by timestamp and limit
        return $activities->sortByDesc('timestamp')->take(15)->values();
    }

    /**
     * Get recent user activities
     */
    private function getUserActivities(): Collection
    {
        $activities = collect();

        try {
            if (Schema::hasTable('user_activities')) {
                $recentActivities = \App\Services\ActivityLogger::getDashboardActivities(7, 8);
                foreach ($recentActivities as $activity) {
                    $activities->push($activity);
                }
            }
        } catch (\Exception $e) {
            // Silently handle missing table
        }

        return $activities;
    }

    /**
     * Get recent driver-related activities
     */
    private function getDriverActivities(): Collection
    {
        $activities = collect();

        try {
            // Recent driver registrations
            $recentDrivers = Driver::select(['id', 'first_name', 'middle_name', 'surname', 'created_at'])
                ->latest('created_at')
                ->limit(5)
                ->get();

            foreach ($recentDrivers as $driver) {
                $activities->push([
                    'type' => 'driver_registered',
                    'message' => "New driver {$driver->full_name} registered",
                    'timestamp' => $driver->created_at,
                    'icon' => 'fas fa-user-plus',
                    'color' => 'success',
                    'driver_id' => $driver->id
                ]);
            }

            // Recent verifications
            if (Schema::hasColumn('drivers', 'verified_at')) {
                $recentVerifications = Driver::select(['id', 'first_name', 'middle_name', 'surname', 'verified_at'])
                    ->whereNotNull('verified_at')
                    ->latest('verified_at')
                    ->limit(3)
                    ->get();

                foreach ($recentVerifications as $driver) {
                    $activities->push([
                        'type' => 'driver_verified',
                        'message' => "Driver {$driver->full_name} was verified",
                        'timestamp' => $driver->verified_at,
                        'icon' => 'fas fa-check-circle',
                        'color' => 'info',
                        'driver_id' => $driver->id
                    ]);
                }
            }
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle missing tables/columns gracefully
        }

        return $activities;
    }

    /**
     * Get recent company request activities
     */
    private function getRequestActivities(): Collection
    {
        $activities = collect();

        $recentRequests = CompanyRequest::select(['id', 'request_type', 'description', 'created_at'])
            ->with(['company:id,name'])
            ->latest('created_at')
            ->limit(3)
            ->get();

        foreach ($recentRequests as $request) {
            $requestTitle = $request->request_type ?: 'Driver Request';
            $activities->push([
                'type' => 'request_created',
                'message' => "New {$requestTitle} from {$request->company->name}",
                'timestamp' => $request->created_at,
                'icon' => 'fas fa-briefcase',
                'color' => 'warning',
                'request_id' => $request->id
            ]);
        }

        return $activities;
    }

    /**
     * Get recent match activities
     */
    private function getMatchActivities(): Collection
    {
        $activities = collect();

        $recentMatches = DriverMatch::select(['id', 'status', 'created_at'])
            ->with([
                'driver:id,first_name,surname',
                'companyRequest:id,request_type,description'
            ])
            ->latest('created_at')
            ->limit(2)
            ->get();

        foreach ($recentMatches as $match) {
            $driverName = $match->driver ? $match->driver->first_name . ' ' . $match->driver->surname : 'Unknown Driver';
            $position = $match->companyRequest ? ($match->companyRequest->request_type ?: 'Driver Position') : 'Unknown Position';

            $activities->push([
                'type' => 'match_created',
                'message' => "Matched {$driverName} to {$position}",
                'timestamp' => $match->created_at,
                'icon' => 'fas fa-handshake',
                'color' => 'primary',
                'match_id' => $match->id
            ]);
        }

        return $activities;
    }
}
