<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminReportController extends Controller
{
    public function index()
    {
        // Check if user has permission to view reports
        if (!auth('admin')->user()->hasPermission('view_reports')) {
            abort(403, 'Access denied. Insufficient permissions.');
        }

        return view('admin.reports.index');
    }

    public function dashboard()
    {
        return view('admin.reports.dashboard');
    }

    public function commission()
    {
        return view('admin.reports.commission');
    }

    public function driverPerformance()
    {
        return view('admin.reports.driver-performance');
    }

    public function companyActivity()
    {
        return view('admin.reports.company-activity');
    }

    public function financial()
    {
        return view('admin.reports.financial');
    }

    public function export($type)
    {
        return back()->with('info', 'Report export functionality coming soon!');
    }

    public function scheduleReport(Request $request)
    {
        return back()->with('info', 'Report scheduling functionality coming soon!');
    }

    public function scheduledReports()
    {
        return view('admin.reports.scheduled');
    }
}