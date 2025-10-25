<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\Fleet;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        $company = $request->user();

        $query = Vehicle::whereHas('fleet', function ($q) use ($company) {
            $q->where('company_id', $company->id);
        })->with('fleet');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by vehicle type
        if ($request->filled('vehicle_type')) {
            $query->where('vehicle_type', $request->vehicle_type);
        }

        $vehicles = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => $vehicles,
        ]);
    }

    public function show(Vehicle $vehicle)
    {
        $this->authorize('view', $vehicle->fleet);

        $vehicle->load('fleet');

        return response()->json([
            'status' => 'success',
            'data' => $vehicle,
        ]);
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $this->authorize('update', $vehicle->fleet);

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

    public function destroy(Vehicle $vehicle)
    {
        $this->authorize('update', $vehicle->fleet);

        $vehicle->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Vehicle deleted successfully',
        ]);
    }

    public function maintenanceHistory(Vehicle $vehicle)
    {
        $this->authorize('view', $vehicle->fleet);

        // TODO: Implement maintenance history
        $maintenance = []; // Fetch maintenance records

        return response()->json([
            'status' => 'success',
            'data' => $maintenance,
        ]);
    }

    public function assignToFleet(Request $request, Vehicle $vehicle)
    {
        $this->authorize('update', $vehicle->fleet);

        $request->validate([
            'fleet_id' => 'required|exists:fleets,id',
        ]);

        $fleet = Fleet::findOrFail($request->fleet_id);

        $this->authorize('update', $fleet);

        $vehicle->update(['fleet_id' => $request->fleet_id]);

        return response()->json([
            'status' => 'success',
            'message' => 'Vehicle assigned to fleet successfully',
            'data' => $vehicle->load('fleet'),
        ]);
    }
}
