<?php

namespace App\Services;

use App\Models\Fleet;
use App\Models\Vehicle;
use App\Models\Company;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class FleetService
{
    public function createFleet(Company $company, array $data): Fleet
    {
        return $company->fleets()->create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'manager_name' => $data['manager_name'] ?? null,
            'manager_phone' => $data['manager_phone'] ?? null,
            'manager_email' => $data['manager_email'] ?? null,
            'operating_regions' => $data['operating_regions'] ?? [],
            'base_location' => $data['base_location'] ?? null,
            'status' => 'active',
        ]);
    }

    public function addVehicle(Fleet $fleet, array $data): Vehicle
    {
        Log::info('Adding vehicle to fleet', ['fleet_id' => $fleet->id, 'registration_number' => $data['registration_number']]);

        $vehicle = $fleet->vehicles()->create([
            'registration_number' => $data['registration_number'],
            'make' => $data['make'],
            'model' => $data['model'],
            'year' => $data['year'],
            'color' => $data['color'] ?? null,
            'vin' => $data['vin'] ?? null,
            'engine_number' => $data['engine_number'] ?? null,
            'chassis_number' => $data['chassis_number'] ?? null,
            'vehicle_type' => $data['vehicle_type'],
            'seating_capacity' => $data['seating_capacity'],
            'purchase_price' => $data['purchase_price'] ?? null,
            'purchase_date' => $data['purchase_date'] ?? null,
            'current_value' => $data['current_value'] ?? null,
            'insurance_expiry' => $data['insurance_expiry'] ?? null,
            'insurance_provider' => $data['insurance_provider'] ?? null,
            'road_worthiness_expiry' => $data['road_worthiness_expiry'] ?? null,
            'mileage' => $data['mileage'] ?? 0,
            'status' => 'active',
            'notes' => $data['notes'] ?? null,
            'features' => $data['features'] ?? [],
        ]);

        Log::info('Vehicle added successfully', ['vehicle_id' => $vehicle->id]);

        return $vehicle;
    }

    public function getFleetStats(Fleet $fleet): array
    {
        $vehicles = $fleet->vehicles;

        return [
            'total_vehicles' => $vehicles->count(),
            'active_vehicles' => $vehicles->where('status', 'active')->count(),
            'maintenance_vehicles' => $vehicles->where('status', 'maintenance')->count(),
            'sold_vehicles' => $vehicles->where('status', 'sold')->count(),
            'total_value' => $vehicles->sum('current_value'),
            'expiring_insurance' => $vehicles->filter(function ($vehicle) {
                return $vehicle->insuranceExpired();
            })->count(),
            'expiring_road_worthiness' => $vehicles->filter(function ($vehicle) {
                return $vehicle->roadWorthinessExpired();
            })->count(),
        ];
    }

    public function updateVehicleStatus(Vehicle $vehicle, string $status, string $notes = null): bool
    {
        Log::info('Updating vehicle status', ['vehicle_id' => $vehicle->id, 'old_status' => $vehicle->status, 'new_status' => $status]);

        $result = $vehicle->update([
            'status' => $status,
            'notes' => $notes,
        ]);

        Log::info('Vehicle status updated', ['vehicle_id' => $vehicle->id, 'status' => $status]);

        return $result;
    }

    public function updateVehicle(Vehicle $vehicle, array $data): bool
    {
        Log::info('Updating vehicle details', ['vehicle_id' => $vehicle->id]);

        $result = $vehicle->update($data);

        Log::info('Vehicle updated successfully', ['vehicle_id' => $vehicle->id]);

        return $result;
    }

    public function deleteVehicle(Vehicle $vehicle): bool
    {
        Log::info('Deleting vehicle', ['vehicle_id' => $vehicle->id]);

        $result = $vehicle->delete();

        Log::info('Vehicle deleted successfully', ['vehicle_id' => $vehicle->id]);

        return $result;
    }

    public function getVehiclesDueForMaintenance(Fleet $fleet): Collection
    {
        return $fleet->vehicles()
            ->where('status', 'active')
            ->where(function ($query) {
                $query->where('insurance_expiry', '<=', now()->addDays(30))
                      ->orWhere('road_worthiness_expiry', '<=', now()->addDays(30));
            })
            ->get();
    }
}
