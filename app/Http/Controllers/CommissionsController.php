<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Commission;

class CommissionsController extends Controller
{
    public function index()
    {
        $commissions = Commission::with(['driver', 'match'])->paginate(20);
        return view('admin.commissions.index', compact('commissions'));
    }

    public function show($commission)
    {
        $commission = Commission::with(['driver', 'match'])->findOrFail($commission);
        return view('admin.commissions.show', compact('commission'));
    }

    public function markAsPaid($commission)
    {
        $commission = Commission::findOrFail($commission);
        $commission->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        return back()->with('success', 'Commission marked as paid successfully!');
    }

    public function dispute($commission)
    {
        $commission = Commission::findOrFail($commission);
        $commission->update(['status' => 'disputed']);

        return back()->with('success', 'Commission marked as disputed!');
    }

    public function refund($commission)
    {
        $commission = Commission::findOrFail($commission);
        $commission->update([
            'status' => 'refunded',
            'refunded_at' => now(),
        ]);

        return back()->with('success', 'Commission refunded successfully!');
    }

    public function export($format)
    {
        // TODO: Implement export functionality
        return back()->with('info', 'Export functionality coming soon!');
    }
}