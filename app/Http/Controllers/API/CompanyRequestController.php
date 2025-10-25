<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\CompanyRequest;
use App\Models\CompanyMatch;
use App\Services\MatchingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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

        return response()->json([
            'status' => 'success',
            'data' => $requests,
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pickup_location' => 'required|string|max:255',
            'pickup_state_id' => 'required|exists:states,id',
            'pickup_lga_id' => 'required|exists:lgas,id',
            'dropoff_location' => 'nullable|string|max:255',
            'dropoff_state_id' => 'nullable|exists:states,id',
            'dropoff_lga_id' => 'nullable|exists:lgas,id',
            'vehicle_type' => 'required|string|max:100',
            'cargo_type' => 'nullable|string|max:100',
            'cargo_description' => 'nullable|string|max:500',
            'weight_kg' => 'nullable|numeric|min:0',
            'value_naira' => 'nullable|numeric|min:0',
            'pickup_date' => 'required|date|after:now',
            'delivery_deadline' => 'nullable|date|after:pickup_date',
            'special_requirements' => 'nullable|string|max:1000',
            'budget_min' => 'nullable|numeric|min:0',
            'budget_max' => 'nullable|numeric|min:0|gte:budget_min',
            'experience_required' => 'nullable|integer|min:0|max:50',
            'urgency' => 'required|in:low,medium,high,critical',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

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

        return response()->json([
            'status' => 'success',
            'message' => 'Transport request created successfully. Driver matching in progress.',
            'data' => $companyRequest->load('matches.driver'),
        ], 201);
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

        return response()->json([
            'status' => 'success',
            'data' => $companyRequest,
        ]);
    }

    public function update(Request $request, CompanyRequest $companyRequest)
    {
        $this->authorize('update', $companyRequest);

        $validator = Validator::make($request->all(), [
            'pickup_location' => 'sometimes|string|max:255',
            'pickup_state_id' => 'sometimes|exists:states,id',
            'pickup_lga_id' => 'sometimes|exists:lgas,id',
            'dropoff_location' => 'nullable|string|max:255',
            'dropoff_state_id' => 'nullable|exists:states,id',
            'dropoff_lga_id' => 'nullable|exists:lgas,id',
            'vehicle_type' => 'sometimes|string|max:100',
            'cargo_type' => 'nullable|string|max:100',
            'cargo_description' => 'nullable|string|max:500',
            'weight_kg' => 'nullable|numeric|min:0',
            'value_naira' => 'nullable|numeric|min:0',
            'pickup_date' => 'sometimes|date|after:now',
            'delivery_deadline' => 'nullable|date|after:pickup_date',
            'special_requirements' => 'nullable|string|max:1000',
            'budget_min' => 'nullable|numeric|min:0',
            'budget_max' => 'nullable|numeric|min:0|gte:budget_min',
            'experience_required' => 'nullable|integer|min:0|max:50',
            'urgency' => 'sometimes|in:low,medium,high,critical',
            'status' => 'sometimes|in:pending,active,completed,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $companyRequest->update($request->only([
            'pickup_location', 'pickup_state_id', 'pickup_lga_id',
            'dropoff_location', 'dropoff_state_id', 'dropoff_lga_id',
            'vehicle_type', 'cargo_type', 'cargo_description',
            'weight_kg', 'value_naira', 'pickup_date', 'delivery_deadline',
            'special_requirements', 'budget_min', 'budget_max',
            'experience_required', 'urgency', 'status'
        ]));

        return response()->json([
            'status' => 'success',
            'message' => 'Request updated successfully',
            'data' => $companyRequest,
        ]);
    }

    public function destroy(CompanyRequest $companyRequest)
    {
        $this->authorize('delete', $companyRequest);

        $companyRequest->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Request deleted successfully',
        ]);
    }

    public function matches(CompanyRequest $companyRequest)
    {
        $this->authorize('view', $companyRequest);

        $matches = $companyRequest->matches()
            ->with('driver')
            ->orderBy('match_score', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $matches,
        ]);
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

        return response()->json([
            'status' => 'success',
            'message' => 'Match accepted successfully',
            'data' => $companyMatch->load('driver'),
        ]);
    }

    public function rejectMatch(Request $request, CompanyMatch $companyMatch)
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

    private function generateRequestId(): string
    {
        do {
            $id = 'REQ-' . date('Y') . '-' . strtoupper(substr(md5(uniqid()), 0, 8));
        } while (CompanyRequest::where('request_id', $id)->exists());

        return $id;
    }
}
