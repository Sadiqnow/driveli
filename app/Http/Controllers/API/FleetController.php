<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Fleet;
use App\Models\Vehicle;
use App\Services\FleetService;
use Illuminate\Http\Request;

class FleetController extends Controller
{
    protected $fleetService;

    public function __construct(FleetService $fleetService)
    {
        $this->fleetService = $fleetService;
    }

    public function index(Request $request)
    {
        $company = $request->user();

        $query = Fleet::where('company_id', $company->id)->with('vehicles');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $fleets = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => $fleets,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'manager_name' => 'nullable|string|max:255',
            'manager_phone' => 'nullable|string|max:20',
            'manager_email' => 'nullable|email|max:255',
            'operating_regions' => 'nullable|array',
            'base_location' => 'nullable|string|max:255',
        ]);

        $company = $request->user();

        $fleet = $this->fleetService->createFleet($company, $request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Fleet created successfully',
            'data' => $fleet,
        ], 201);
    }

    public function show(Fleet $fleet)
    {
        $this->authorize('view', $fleet);

        $fleet->load(['vehicles', 'company']);

        $stats = $this->fleetService->getFleetStats($fleet);

        return response()->json([
            'status' => 'success',
            'data' => [
                'fleet' => $fleet,
                'stats' => $stats,
            ],
        ]);
    }

    public function update(Request $request, Fleet $fleet)
    {
        $this->authorize('update', $fleet);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:1000',
            'manager_name' => 'nullable|string|max:255',
            'manager_phone' => 'nullable|string|max:20',
            'manager_email' => 'nullable|email|max:255',
            'operating_regions' => 'nullable|array',
            'base_location' => 'nullable|string|max:255',
            'status' => 'sometimes|in:active,inactive',
        ]);

        $fleet->update($request->only([
            'name', 'description', 'manager_name', 'manager_phone',
            'manager_email', 'operating_regions', 'base_location', 'status'
        ]));

        return response()->json([
            'status' => 'success',
            'message' => 'Fleet updated successfully',
            'data' => $fleet,
        ]);
    }

    public function destroy(Fleet $fleet)
    {
        $this->authorize('delete', $fleet);

        $fleet->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Fleet deleted successfully',
        ]);
    }

    public function addVehicle(Request $request, Fleet $fleet)
    {
        $this->authorize('update', $fleet);

        $request->validate([
            'registration_number' => 'required|string|max:20|unique:vehicles',
            'make' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'color' => 'nullable|string|max:50',
            'vin' => 'nullable|string|max:50',
            'engine_number' => 'nullable|string|max:50',
            'chassis_number' => 'nullable|string|max:50',
            'vehicle_type' => 'required|string|max:50',
            'seating_capacity' => 'required|integer|min:1|max:100',
            'purchase_price' => 'nullable|numeric|min:0',
            'purchase_date' => 'nullable|date',
            'current_value' => 'nullable|numeric|min:0',
            'insurance_expiry' => 'nullable|date',
            'insurance_provider' => 'nullable|string|max:100',
            'road_worthiness_expiry' => 'nullable|date',
            'mileage' => 'nullable|integer|min:0',
            'notes' => 'nullable|string|max:1000',
            'features' => 'nullable|array',
        ]);

        $vehicle = $this->fleetService->addVehicle($fleet, $request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Vehicle added successfully',
            'data' => $vehicle,
        ], 201);
    }

    public function updateVehicle(Request $request, Fleet $fleet, Vehicle $vehicle)
    {
        $this->authorize('update', $fleet);

        if ($vehicle->fleet_id !== $fleet->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Vehicle does not belong to this fleet',
            ], 403);
        }

        $request->validate([
            'registration_number' => 'sometimes|string|max:20|unique:vehicles,registration_number,' . $vehicle->id,
            'make' => 'sometimes|string|max:100',
            'model' => 'sometimes|string|max:100',
            'year' => 'sometimes|integer|min:1900|max:' . (date('Y') + 1),
            'color' => 'nullable|string|max:50',
            'vin' => 'nullable|string|max:50',
            'engine_number' => 'nullable|string|max:50',
            'chassis_number' => 'nullable|string|max:50',
            'vehicle_type' => 'sometimes|string|max:50',
            'seating_capacity' => 'sometimes|integer|min:1|max:100',
            'purchase_price' => 'nullable|numeric|min:0',
            'purchase_date' => 'nullable|date',
            'current_value' => 'nullable|numeric|min:0',
            'insurance_expiry' => 'nullable|date',
            'insurance_provider' => 'nullable|string|max:100',
            'road_worthiness_expiry' => 'nullable|date',
            'mileage' => 'nullable|integer|min:0',
            'status' => 'sometimes|in:active,maintenance,sold',
            'notes' => 'nullable|string|max:1000',
            'features' => 'nullable|array',
        ]);

        $vehicle->update($request->only([
            'registration_number', 'make', 'model', 'year', 'color', 'vin',
            'engine_number', 'chassis_number', 'vehicle_type', 'seating_capacity',
            'purchase_price', 'purchase_date', 'current_value', 'insurance_expiry',
            'insurance_provider', 'road_worthiness_expiry', 'mileage', 'status',
            'notes', 'features'
        ]));

        return response()->json([
            'status' => 'success',
            'message' => 'Vehicle updated successfully',
            'data' => $vehicle,
        ]);
    }

    public function removeVehicle(Fleet $fleet, Vehicle $vehicle)
    {
        $this->authorize('update', $fleet);

        if ($vehicle->fleet_id !== $fleet->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Vehicle does not belong to this fleet',
            ], 403);
        }

        $vehicle->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Vehicle removed successfully',
        ]);
    }

    public function vehicles(Fleet $fleet)
    {
        $this->authorize('view', $fleet);

        $vehicles = $fleet->vehicles()->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => $vehicles,
        ]);
    }

    public function maintenanceDue(Fleet $fleet)
    {
        $this->authorize('view', $fleet);

        $vehicles = $this->fleetService->getVehiclesDueForMaintenance($fleet);

        return response()->json([
            'status' => 'success',
            'data' => $vehicles,
        ]);
    }
}
