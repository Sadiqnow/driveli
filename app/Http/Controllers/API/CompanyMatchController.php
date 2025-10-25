<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\CompanyMatch;
use Illuminate\Http\Request;

class CompanyMatchController extends Controller
{
    public function index(Request $request)
    {
        $company = $request->user();

        $query = CompanyMatch::whereHas('companyRequest', function ($q) use ($company) {
            $q->where('company_id', $company->id);
        })->with(['companyRequest', 'driver']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $matches = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => $matches,
        ]);
    }

    public function show(CompanyMatch $companyMatch)
    {
        $this->authorize('view', $companyMatch->companyRequest);

        $companyMatch->load(['companyRequest', 'driver']);

        return response()->json([
            'status' => 'success',
            'data' => $companyMatch,
        ]);
    }

    public function accept(Request $request, CompanyMatch $companyMatch)
    {
        $this->authorize('update', $companyMatch->companyRequest);

        $request->validate([
            'agreed_rate' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $companyMatch->accept();
        $companyMatch->update([
            'agreed_rate' => $request->agreed_rate,
            'notes' => $request->notes,
        ]);

        // Update request status
        $companyMatch->companyRequest->update(['status' => 'active']);

        // Create invoice
        // TODO: Implement invoice creation

        return response()->json([
            'status' => 'success',
            'message' => 'Match accepted successfully',
            'data' => $companyMatch->load('driver'),
        ]);
    }

    public function reject(Request $request, CompanyMatch $companyMatch)
    {
        $this->authorize('update', $companyMatch->companyRequest);

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $companyMatch->reject($request->reason);

        return response()->json([
            'status' => 'success',
            'message' => 'Match rejected',
        ]);
    }

    public function negotiate(Request $request, CompanyMatch $companyMatch)
    {
        $this->authorize('update', $companyMatch->companyRequest);

        $request->validate([
            'proposed_rate' => 'required|numeric|min:0',
            'message' => 'required|string|max:1000',
        ]);

        $companyMatch->update([
            'proposed_rate' => $request->proposed_rate,
            'negotiation_message' => $request->message,
            'status' => 'negotiating',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Negotiation sent to driver',
            'data' => $companyMatch,
        ]);
    }
}
