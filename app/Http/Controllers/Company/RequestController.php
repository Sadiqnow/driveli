<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\CompanyRequest;
use App\Services\CompanyService;

class RequestController extends Controller
{
    protected $companyService;

    public function __construct(CompanyService $companyService)
    {
        $this->companyService = $companyService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $company = Auth::guard('company')->user();

        $query = CompanyRequest::where('company_id', $company->id)->with('matches');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('vehicle_type')) {
            $query->where('vehicle_type', $request->vehicle_type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $requests = $query->latest()->paginate(15);

        return view('company.requests', compact('requests'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('company.requests.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $company = Auth::guard('company')->user();

        $validator = Validator::make($request->all(), [
            'pickup_location' => 'required|string|max:255',
            'dropoff_location' => 'nullable|string|max:255',
            'pickup_state_id' => 'required|exists:states,id',
            'pickup_lga_id' => 'required|exists:lgas,id',
            'dropoff_state_id' => 'nullable|exists:states,id',
            'dropoff_lga_id' => 'nullable|exists:lgas,id',
            'vehicle_type' => 'required|in:truck,van,pickup,motorcycle,car',
            'urgency' => 'required|in:low,medium,high,critical',
            'cargo_type' => 'nullable|in:general,perishable,fragile,hazardous,documents,machinery',
            'weight_kg' => 'nullable|numeric|min:0',
            'value_naira' => 'nullable|numeric|min:0',
            'pickup_date' => 'required|date|after:now',
            'budget_min' => 'nullable|numeric|min:0',
            'budget_max' => 'nullable|numeric|min:0',
            'experience_required' => 'nullable|integer|min:0|max:50',
            'delivery_deadline' => 'nullable|date|after:pickup_date',
            'cargo_description' => 'nullable|string|max:1000',
            'special_requirements' => 'nullable|string|max:1000',
        ]);

        $validator->after(function ($validator) {
            if ($validator->errors()->isEmpty()) {
                $budgetMin = request('budget_min');
                $budgetMax = request('budget_max');

                if ($budgetMax && $budgetMin && $budgetMin > $budgetMax) {
                    $validator->errors()->add('budget_min', 'Minimum budget cannot be greater than maximum budget.');
                }
            }
        });

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $requestData = $request->all();
            $requestData['company_id'] = $company->id;

            $companyRequest = $this->companyService->createRequest($requestData);

            return redirect()->route('company.requests.index')
                ->with('success', 'Transport request created successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to create request. Please try again.'])->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(CompanyRequest $request)
    {
        $this->authorize('view', $request);

        $request->load(['matches.driver', 'invoices']);

        return view('company.requests.show', compact('request'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CompanyRequest $request)
    {
        $this->authorize('update', $request);

        return view('company.requests.edit', compact('request'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CompanyRequest $companyRequest)
    {
        $this->authorize('update', $companyRequest);

        // Only allow updates for pending/active requests
        if (!in_array($companyRequest->status, ['pending', 'active'])) {
            return back()->withErrors(['error' => 'Cannot update a completed or cancelled request.']);
        }

        $validator = Validator::make($request->all(), [
            'pickup_location' => 'required|string|max:255',
            'dropoff_location' => 'nullable|string|max:255',
            'pickup_state_id' => 'required|exists:states,id',
            'pickup_lga_id' => 'required|exists:lgas,id',
            'dropoff_state_id' => 'nullable|exists:states,id',
            'dropoff_lga_id' => 'nullable|exists:lgas,id',
            'vehicle_type' => 'required|in:truck,van,pickup,motorcycle,car',
            'urgency' => 'required|in:low,medium,high,critical',
            'cargo_type' => 'nullable|in:general,perishable,fragile,hazardous,documents,machinery',
            'weight_kg' => 'nullable|numeric|min:0',
            'value_naira' => 'nullable|numeric|min:0',
            'pickup_date' => 'required|date|after:now',
            'budget_min' => 'nullable|numeric|min:0',
            'budget_max' => 'nullable|numeric|min:0',
            'experience_required' => 'nullable|integer|min:0|max:50',
            'delivery_deadline' => 'nullable|date|after:pickup_date',
            'cargo_description' => 'nullable|string|max:1000',
            'special_requirements' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $this->companyService->updateRequest($companyRequest, $request->all());

            return redirect()->route('company.requests.show', $companyRequest)
                ->with('success', 'Request updated successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to update request. Please try again.'])->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CompanyRequest $request)
    {
        $this->authorize('delete', $request);

        // Only allow deletion for pending requests
        if ($request->status !== 'pending') {
            return back()->withErrors(['error' => 'Cannot delete an active or completed request.']);
        }

        try {
            $this->companyService->deleteRequest($request);

            return redirect()->route('company.requests.index')
                ->with('success', 'Request deleted successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to delete request. Please try again.']);
        }
    }
}
