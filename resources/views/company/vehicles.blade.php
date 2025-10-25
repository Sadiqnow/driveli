@extends('company.layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="mb-0"><i class="bi bi-car-front"></i> Vehicle Management</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createVehicleModal" aria-label="Add new vehicle">
                <i class="bi bi-plus-circle" aria-hidden="true"></i> Add Vehicle
            </button>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <x-ui.stats-widget
            title="Total Vehicles"
            :value="$stats['total_vehicles'] ?? 0"
            icon="bi bi-car-front"
            variant="primary"
        />
    </div>
    <div class="col-md-3 mb-3">
        <x-ui.stats-widget
            title="Active Vehicles"
            :value="$stats['active_vehicles'] ?? 0"
            icon="bi bi-car-front-fill"
            variant="success"
        />
    </div>
    <div class="col-md-3 mb-3">
        <x-ui.stats-widget
            title="In Maintenance"
            :value="$stats['maintenance_vehicles'] ?? 0"
            icon="bi bi-tools"
            variant="warning"
        />
    </div>
    <div class="col-md-3 mb-3">
        <x-ui.stats-widget
            title="Available"
            :value="$stats['available_vehicles'] ?? 0"
            icon="bi bi-check-circle"
            variant="info"
        />
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-funnel"></i> Filters</h5>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3" role="search" aria-label="Filter vehicles">
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                </select>
            </div>

            <div class="col-md-3">
                <label for="fleet_id" class="form-label">Fleet</label>
                <select class="form-select" id="fleet_id" name="fleet_id">
                    <option value="">All Fleets</option>
                    @if(isset($fleets))
                        @foreach($fleets as $fleet)
                            <option value="{{ $fleet->id }}" {{ request('fleet_id') == $fleet->id ? 'selected' : '' }}>
                                {{ $fleet->name }}
                            </option>
                        @endforeach
                    @endif
                </select>
            </div>

            <div class="col-md-3">
                <label for="vehicle_type" class="form-label">Vehicle Type</label>
                <select class="form-select" id="vehicle_type" name="vehicle_type">
                    <option value="">All Types</option>
                    <option value="truck" {{ request('vehicle_type') == 'truck' ? 'selected' : '' }}>Truck</option>
                    <option value="van" {{ request('vehicle_type') == 'van' ? 'selected' : '' }}>Van</option>
                    <option value="pickup" {{ request('vehicle_type') == 'pickup' ? 'selected' : '' }}>Pickup Truck</option>
                    <option value="motorcycle" {{ request('vehicle_type') == 'motorcycle' ? 'selected' : '' }}>Motorcycle</option>
                    <option value="car" {{ request('vehicle_type') == 'car' ? 'selected' : '' }}>Car</option>
                </select>
            </div>

            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2" aria-label="Apply filters">
                    <i class="bi bi-search" aria-hidden="true"></i> Filter
                </button>
                <a href="{{ route('company.vehicles.index') }}" class="btn btn-outline-secondary" aria-label="Clear all filters">
                    <i class="bi bi-x-circle" aria-hidden="true"></i> Clear
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Vehicles Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Your Vehicles</h5>
    </div>
    <div class="card-body">
        @if(isset($vehicles) && $vehicles->count() > 0)
            <x-ui.table
                :headers="[
                    ['label' => 'Registration', 'sortable' => true],
                    ['label' => 'Make/Model', 'sortable' => true],
                    ['label' => 'Type'],
                    ['label' => 'Fleet'],
                    ['label' => 'Status'],
                    ['label' => 'Last Service'],
                    ['label' => 'Actions']
                ]"
                :data="$vehicles->map(function($vehicle) {
                    return [
                        'registration_number' => $vehicle->registration_number,
                        'make_model' => ($vehicle->make ?? '') . ' ' . ($vehicle->model ?? ''),
                        'vehicle_type' => ucfirst($vehicle->vehicle_type ?? ''),
                        'fleet_name' => $vehicle->fleet->name ?? 'Unassigned',
                        'status' => ucfirst($vehicle->status ?? 'active'),
                        'last_service_date' => $vehicle->last_service_date ? $vehicle->last_service_date->format('M d, Y') : 'N/A'
                    ];
                })"
                :actions="[
                    [
                        'text' => 'View',
                        'icon' => 'bi bi-eye',
                        'class' => 'btn-outline-primary',
                        'data' => ['action' => 'view', 'id' => 'vehicle_id']
                    ],
                    [
                        'text' => 'Edit',
                        'icon' => 'bi bi-pencil',
                        'class' => 'btn-outline-warning',
                        'data' => ['action' => 'edit', 'id' => 'vehicle_id']
                    ],
                    [
                        'text' => 'Delete',
                        'icon' => 'bi bi-trash',
                        'class' => 'btn-outline-danger',
                        'data' => ['action' => 'delete', 'id' => 'vehicle_id']
                    ]
                ]"
                empty-message="No vehicles found"
            />
        @else
            <div class="text-center py-5">
                <i class="bi bi-car-front" style="font-size: 3rem; color: #6c757d;" aria-hidden="true"></i>
                <h5 class="mt-3">No Vehicles Yet</h5>
                <p class="text-muted">Add your first vehicle to start managing your fleet.</p>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createVehicleModal">
                    <i class="bi bi-plus-circle" aria-hidden="true"></i> Add Vehicle
                </button>
            </div>
        @endif
    </div>
</div>

<!-- Pagination -->
@if(isset($vehicles) && $vehicles->hasPages())
<div class="d-flex justify-content-center mt-4">
    {{ $vehicles->links() }}
</div>
@endif

<!-- Create Vehicle Modal -->
<x-ui.modal id="createVehicleModal" title="Add New Vehicle" size="lg">
    <form id="createVehicleForm">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="registrationNumber" class="form-label">Registration Number *</label>
                <input type="text" class="form-control" id="registrationNumber" required aria-describedby="registrationHelp">
                <div id="registrationHelp" class="form-text">Vehicle registration number</div>
            </div>
            <div class="col-md-6 mb-3">
                <label for="vehicleType" class="form-label">Vehicle Type *</label>
                <select class="form-select" id="vehicleType" required>
                    <option value="">Select Type</option>
                    <option value="truck">Truck</option>
                    <option value="van">Van</option>
                    <option value="pickup">Pickup Truck</option>
                    <option value="motorcycle">Motorcycle</option>
                    <option value="car">Car</option>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="make" class="form-label">Make</label>
                <input type="text" class="form-control" id="make">
            </div>
            <div class="col-md-6 mb-3">
                <label for="model" class="form-label">Model</label>
                <input type="text" class="form-control" id="model">
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="year" class="form-label">Year</label>
                <input type="number" class="form-control" id="year" min="1900" max="{{ date('Y') + 1 }}">
            </div>
            <div class="col-md-6 mb-3">
                <label for="capacity" class="form-label">Capacity (tons)</label>
                <input type="number" step="0.1" class="form-control" id="capacity" min="0">
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="fleetId" class="form-label">Fleet</label>
                <select class="form-select" id="fleetId">
                    <option value="">Select Fleet</option>
                    @if(isset($fleets))
                        @foreach($fleets as $fleet)
                            <option value="{{ $fleet->id }}">{{ $fleet->name }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label for="vehicleStatus" class="form-label">Status</label>
                <select class="form-select" id="vehicleStatus">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="maintenance">Maintenance</option>
                </select>
            </div>
        </div>

        <div class="mb-3">
            <label for="notes" class="form-label">Notes</label>
            <textarea class="form-control" id="notes" rows="3"></textarea>
        </div>
    </form>

    <x-slot name="footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="saveVehicleBtn">Add Vehicle</button>
    </x-slot>
</x-ui.modal>

<!-- Edit Vehicle Modal -->
<x-ui.modal id="editVehicleModal" title="Edit Vehicle" size="lg">
    <form id="editVehicleForm">
        <input type="hidden" id="editVehicleId">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="editRegistrationNumber" class="form-label">Registration Number *</label>
                <input type="text" class="form-control" id="editRegistrationNumber" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="editVehicleType" class="form-label">Vehicle Type *</label>
                <select class="form-select" id="editVehicleType" required>
                    <option value="truck">Truck</option>
                    <option value="van">Van</option>
                    <option value="pickup">Pickup Truck</option>
                    <option value="motorcycle">Motorcycle</option>
                    <option value="car">Car</option>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="editMake" class="form-label">Make</label>
                <input type="text" class="form-control" id="editMake">
            </div>
            <div class="col-md-6 mb-3">
                <label for="editModel" class="form-label">Model</label>
                <input type="text" class="form-control" id="editModel">
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="editYear" class="form-label">Year</label>
                <input type="number" class="form-control" id="editYear" min="1900" max="{{ date('Y') + 1 }}">
            </div>
            <div class="col-md-6 mb-3">
                <label for="editCapacity" class="form-label">Capacity (tons)</label>
                <input type="number" step="0.1" class="form-control" id="editCapacity" min="0">
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="editFleetId" class="form-label">Fleet</label>
                <select class="form-select" id="editFleetId">
                    <option value="">Select Fleet</option>
                    @if(isset($fleets))
                        @foreach($fleets as $fleet)
                            <option value="{{ $fleet->id }}">{{ $fleet->name }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label for="editVehicleStatus" class="form-label">Status</label>
                <select class="form-select" id="editVehicleStatus">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="maintenance">Maintenance</option>
                </select>
            </div>
        </div>

        <div class="mb-3">
            <label for="editNotes" class="form-label">Notes</label>
            <textarea class="form-control" id="editNotes" rows="3"></textarea>
        </div>
    </form>

    <x-slot name="footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="updateVehicleBtn">Update Vehicle</button>
    </x-slot>
</x-ui.modal>

<!-- Delete Confirmation Modal -->
<x-ui.modal id="deleteVehicleModal" title="Delete Vehicle" size="md">
    <p>Are you sure you want to delete this vehicle? This action cannot be undone.</p>
    <p class="text-danger"><strong>Note:</strong> This will remove the vehicle from any assigned fleets and may affect ongoing requests.</p>

    <x-slot name="footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="confirmDeleteVehicleBtn">Delete Vehicle</button>
    </x-slot>
</x-ui.modal>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentVehicleId = null;

    // Action button handlers
    document.querySelectorAll('[data-action]').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const action = this.getAttribute('data-action');
            const vehicleId = this.getAttribute('data-id') || this.closest('tr').getAttribute('data-vehicle-id');

            switch(action) {
                case 'view':
                    window.location.href = `/company/vehicles/${vehicleId}`;
                    break;
                case 'edit':
                    openEditModal(vehicleId);
                    break;
                case 'delete':
                    openDeleteModal(vehicleId);
                    break;
            }
        });
    });

    // Create vehicle
    document.getElementById('saveVehicleBtn').addEventListener('click', function() {
        const vehicleData = {
            registration_number: document.getElementById('registrationNumber').value.trim(),
            vehicle_type: document.getElementById('vehicleType').value,
            make: document.getElementById('make').value.trim(),
            model: document.getElementById('model').value.trim(),
            year: document.getElementById('year').value,
            capacity: document.getElementById('capacity').value,
            fleet_id: document.getElementById('fleetId').value,
            status: document.getElementById('vehicleStatus').value,
            notes: document.getElementById('notes').value.trim()
        };

        if (!vehicleData.registration_number || !vehicleData.vehicle_type) {
            showToast('Registration number and vehicle type are required', 'danger');
            return;
        }

        createVehicle(vehicleData);
    });

    // Update vehicle
    document.getElementById('updateVehicleBtn').addEventListener('click', function() {
        const vehicleData = {
            registration_number: document.getElementById('editRegistrationNumber').value.trim(),
            vehicle_type: document.getElementById('editVehicleType').value,
            make: document.getElementById('editMake').value.trim(),
            model: document.getElementById('editModel').value.trim(),
            year: document.getElementById('editYear').value,
            capacity: document.getElementById('editCapacity').value,
            fleet_id: document.getElementById('editFleetId').value,
            status: document.getElementById('editVehicleStatus').value,
            notes: document.getElementById('editNotes').value.trim()
        };

        if (!vehicleData.registration_number || !vehicleData.vehicle_type) {
            showToast('Registration number and vehicle type are required', 'danger');
            return;
        }

        updateVehicle(currentVehicleId, vehicleData);
    });

    // Delete vehicle
    document.getElementById('confirmDeleteVehicleBtn').addEventListener('click', function() {
        deleteVehicle(currentVehicleId);
    });

    function openEditModal(vehicleId) {
        currentVehicleId = vehicleId;
        // Fetch vehicle data and populate modal
        fetch(`/api/company/vehicles/${vehicleId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const vehicle = data.data;
                    document.getElementById('editVehicleId').value = vehicle.id;
                    document.getElementById('editRegistrationNumber').value = vehicle.registration_number;
                    document.getElementById('editVehicleType').value = vehicle.vehicle_type;
                    document.getElementById('editMake').value = vehicle.make || '';
                    document.getElementById('editModel').value = vehicle.model || '';
                    document.getElementById('editYear').value = vehicle.year || '';
                    document.getElementById('editCapacity').value = vehicle.capacity || '';
                    document.getElementById('editFleetId').value = vehicle.fleet_id || '';
                    document.getElementById('editVehicleStatus').value = vehicle.status || 'active';
                    document.getElementById('editNotes').value = vehicle.notes || '';

                    const modal = new bootstrap.Modal(document.getElementById('editVehicleModal'));
                    modal.show();
                } else {
                    showToast('Failed to load vehicle data', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred while loading vehicle data', 'danger');
            });
    }

    function openDeleteModal(vehicleId) {
        currentVehicleId = vehicleId;
        const modal = new bootstrap.Modal(document.getElementById('deleteVehicleModal'));
        modal.show();
    }

    function createVehicle(vehicleData) {
        fetch('/api/company/vehicles', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(vehicleData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Vehicle added successfully!', 'success');
                bootstrap.Modal.getInstance(document.getElementById('createVehicleModal')).hide();
                document.getElementById('createVehicleForm').reset();
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(data.message || 'Failed to add vehicle', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred while adding the vehicle', 'danger');
        });
    }

    function updateVehicle(vehicleId, vehicleData) {
        fetch(`/api/company/vehicles/${vehicleId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(vehicleData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Vehicle updated successfully!', 'success');
                bootstrap.Modal.getInstance(document.getElementById('editVehicleModal')).hide();
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(data.message || 'Failed to update vehicle', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred while updating the vehicle', 'danger');
        });
    }

    function deleteVehicle(vehicleId) {
        fetch(`/api/company/vehicles/${vehicleId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Vehicle deleted successfully!', 'success');
                bootstrap.Modal.getInstance(document.getElementById('deleteVehicleModal')).hide();
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(data.message || 'Failed to delete vehicle', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred while deleting the vehicle', 'danger');
        });
    }

    function showToast(message, type = 'info') {
        let toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            toastContainer.style.zIndex = '9999';
            document.body.appendChild(toastContainer);
        }

        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type} border-0`;
        toast.setAttribute('role', 'alert');
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;

        toastContainer.appendChild(toast);
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();

        toast.addEventListener('hidden.bs.toast', () => {
            toast.remove();
        });
    }
});
</script>
@endpush
@endsection
