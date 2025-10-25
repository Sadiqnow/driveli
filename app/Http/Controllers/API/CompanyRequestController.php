<?php

namespace App\Http\Controllers\API;

use App\Helpers\DrivelinkHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompanyRequestRequest;
use App\Http\Requests\UpdateCompanyRequestRequest;
use App\Http\Resources\CompanyRequestResource;
use App\Http\Resources\CompanyMatchResource;
use App\Models\CompanyRequest;
use App\Models\CompanyMatch;
use App\Services\MatchingService;
use Illuminate\Http\Request;

class CompanyRequestController extends Controller
{
    protected $matchingService;

    public function __construct(MatchingService $matchingService)
    {
        $this->matchingService = $matchingService;
    }

    public function index(Request $request)
    {
        $company = $request->user();

        $query = CompanyRequest::where('company_id', $company->id)
            ->with(['matches.driver', 'matches' => function ($q) {
                $q->orderBy('match_score', 'desc');
            }]);

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

        $requests = $query->orderBy('created_at', 'desc')->paginate(15);

        return DrivelinkHelper::respondJson('success', 'Transport requests retrieved successfully', CompanyRequestResource::collection($requests));
    }

    public function store(StoreCompanyRequestRequest $request)
    {
        $company = $request->user();

        $companyRequest = CompanyRequest::create([
            'company_id' => $company->id,
            'request_id' => $this->generateRequestId(),
            'pickup_location' => $request->pickup_location,
            'pickup_state_id' => $request->pickup_state_id,
            'pickup_lga_id' => $request->pickup_lga_id,
            'dropoff_location' => $request->dropoff_location,
            'dropoff_state_id' => $request->dropoff_state_id,
            'dropoff_lga_id' => $request->dropoff_lga_id,
            'vehicle_type' => $request->vehicle_type,
            'cargo_type' => $request->cargo_type,
            'cargo_description' => $request->cargo_description,
            'weight_kg' => $request->weight_kg,
            'value_naira' => $request->value_naira,
            'pickup_date' => $request->pickup_date,
            'delivery_deadline' => $request->delivery_deadline,
            'special_requirements' => $request->special_requirements,
            'budget_min' => $request->budget_min,
            'budget_max' => $request->budget_max,
            'experience_required' => $request->experience_required ?? 1,
            'urgency' => $request->urgency,
            'status' => 'pending',
        ]);

        // Dispatch matching job
        $this->matchingService->dispatchMatchingJob($companyRequest);

        return DrivelinkHelper::respondJson('success', 'Transport request created successfully. Driver matching in progress.', new CompanyRequestResource($companyRequest->load('matches.driver')), 201);
    }

    public function show(CompanyRequest $companyRequest)
    {
        $this->authorize('view', $companyRequest);

        $companyRequest->load([
            'matches' => function ($q) {
                $q->with('driver')->orderBy('match_score', 'desc');
            },
            'company'
        ]);

        return DrivelinkHelper::respondJson('success', 'Transport request retrieved successfully', new CompanyRequestResource($companyRequest));
    }

    public function update(UpdateCompanyRequestRequest $request, CompanyRequest $companyRequest)
    {
        $this->authorize('update', $companyRequest);

        $companyRequest->update($request->validated());

        return DrivelinkHelper::respondJson('success', 'Request updated successfully', new CompanyRequestResource($companyRequest));
    }

    public function destroy(CompanyRequest $companyRequest)
    {
        $this->authorize('delete', $companyRequest);

        $companyRequest->delete();

        return DrivelinkHelper::respondJson('success', 'Request deleted successfully');
    }

    public function matches(CompanyRequest $companyRequest)
    {
        $this->authorize('view', $companyRequest);

        $matches = $companyRequest->matches()
            ->with('driver')
            ->orderBy('match_score', 'desc')
            ->get();

        return DrivelinkHelper::respondJson('success', 'Matches retrieved successfully', CompanyMatchResource::collection($matches));
    }

    public function acceptMatch(Request $request, CompanyMatch $companyMatch)
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

        return DrivelinkHelper::respondJson('success', 'Match accepted successfully', new CompanyMatchResource($companyMatch->load('driver')));
    }

    public function rejectMatch(Request $request, CompanyMatch $companyMatch)
    {
        $this->authorize('update', $companyMatch->companyRequest);

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $companyMatch->reject($request->reason);

        return DrivelinkHelper::respondJson('success', 'Match rejected');
    }

    private function generateRequestId(): string
    {
        do {
            $id = 'REQ-' . date('Y') . '-' . strtoupper(substr(md5(uniqid()), 0, 8));
        } while (CompanyRequest::where('request_id', $id)->exists());

        return $id;
    }
}
