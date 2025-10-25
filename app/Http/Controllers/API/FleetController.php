<?php

namespace App\Http\Controllers\API;

use App\Helpers\DrivelinkHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFleetRequest;
use App\Http\Requests\UpdateFleetRequest;
use App\Http\Requests\StoreVehicleRequest;
use App\Http\Requests\UpdateVehicleRequest;
use App\Http\Resources\FleetResource;
use App\Http\Resources\VehicleResource;
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

        return DrivelinkHelper::respondJson('success', 'Fleets retrieved successfully', FleetResource::collection($fleets));
    }

    public function store(StoreFleetRequest $request)
    {
        $company = $request->user();

        $fleet = $this->fleetService->createFleet($company, $request->validated());

        return DrivelinkHelper::respondJson('success', 'Fleet created successfully', new FleetResource($fleet), 201);
    }

    public function show(Fleet $fleet)
    {
        $this->authorize('view', $fleet);

        $fleet->load(['vehicles', 'company']);

        $stats = $this->fleetService->getFleetStats($fleet);

        return DrivelinkHelper::respondJson('success', 'Fleet retrieved successfully', [
            'fleet' => new FleetResource($fleet),
            'stats' => $stats,
        ]);
    }

    public function update(UpdateFleetRequest $request, Fleet $fleet)
    {
        $this->authorize('update', $fleet);

        $fleet->update($request->validated());

        return DrivelinkHelper::respondJson('success', 'Fleet updated successfully', new FleetResource($fleet));
    }

    public function destroy(Fleet $fleet)
    {
        $this->authorize('delete', $fleet);

        $fleet->delete();

        return DrivelinkHelper::respondJson('success', 'Fleet deleted successfully');
    }

    public function addVehicle(StoreVehicleRequest $request, Fleet $fleet)
    {
        $this->authorize('update', $fleet);

        $vehicle = $this->fleetService->addVehicle($fleet, $request->validated());

        return DrivelinkHelper::respondJson('success', 'Vehicle added successfully', new VehicleResource($vehicle), 201);
    }

    public function updateVehicle(UpdateVehicleRequest $request, Fleet $fleet, Vehicle $vehicle)
    {
        $this->authorize('update', $fleet);

        if ($vehicle->fleet_id !== $fleet->id) {
            return DrivelinkHelper::respondJson('error', 'Vehicle does not belong to this fleet', null, 403);
        }

        $vehicle->update($request->validated());

        return DrivelinkHelper::respondJson('success', 'Vehicle updated successfully', new VehicleResource($vehicle));
    }

    public function removeVehicle(Fleet $fleet, Vehicle $vehicle)
    {
        $this->authorize('update', $fleet);

        if ($vehicle->fleet_id !== $fleet->id) {
            return DrivelinkHelper::respondJson('error', 'Vehicle does not belong to this fleet', null, 403);
        }

        $vehicle->delete();

        return DrivelinkHelper::respondJson('success', 'Vehicle removed successfully');
    }

    public function vehicles(Fleet $fleet)
    {
        $this->authorize('view', $fleet);

        $vehicles = $fleet->vehicles()->orderBy('created_at', 'desc')->paginate(15);

        return DrivelinkHelper::respondJson('success', 'Vehicles retrieved successfully', VehicleResource::collection($vehicles));
    }

    public function maintenanceDue(Fleet $fleet)
    {
        $this->authorize('view', $fleet);

        $vehicles = $this->fleetService->getVehiclesDueForMaintenance($fleet);

        return DrivelinkHelper::respondJson('success', 'Maintenance due vehicles retrieved successfully', $vehicles);
    }
}
