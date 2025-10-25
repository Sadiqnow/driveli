<?php

namespace App\Http\Controllers\API;

use App\Helpers\DrivelinkHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateVehicleRequest;
use App\Http\Resources\VehicleResource;
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

        return DrivelinkHelper::respondJson('success', 'Vehicles retrieved successfully', VehicleResource::collection($vehicles));
    }

    public function show(Vehicle $vehicle)
    {
        $this->authorize('view', $vehicle->fleet);

        $vehicle->load('fleet');

        return DrivelinkHelper::respondJson('success', 'Vehicle retrieved successfully', new VehicleResource($vehicle));
    }

    public function update(UpdateVehicleRequest $request, Vehicle $vehicle)
    {
        $this->authorize('update', $vehicle->fleet);

        $vehicle->update($request->validated());

        return DrivelinkHelper::respondJson('success', 'Vehicle updated successfully', new VehicleResource($vehicle));
    }

    public function destroy(Vehicle $vehicle)
    {
        $this->authorize('update', $vehicle->fleet);

        $vehicle->delete();

        return DrivelinkHelper::respondJson('success', 'Vehicle deleted successfully');
    }

    public function maintenanceHistory(Vehicle $vehicle)
    {
        $this->authorize('view', $vehicle->fleet);

        // TODO: Implement maintenance history
        $maintenance = []; // Fetch maintenance records

        return DrivelinkHelper::respondJson('success', 'Maintenance history retrieved successfully', $maintenance);
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

        return DrivelinkHelper::respondJson('success', 'Vehicle assigned to fleet successfully', new VehicleResource($vehicle->load('fleet')));
    }
}
