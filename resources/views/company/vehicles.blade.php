@extends('company.layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="mb-0"><i class="bi bi-car-front" aria-hidden="true"></i> Vehicle Management</h2>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createVehicleModal" aria-label="Add new vehicle">
                <i class="bi bi-plus-circle" aria-hidden="true"></i> Add Vehicle
            </button>
        </div>
        <p class="text-muted mt-2">Manage your fleet vehicles and track their status</p>
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
                    @foreach($fleets as $fleet)
                        <option value="{{ $fleet->id }}" {{ request('fleet_id') == $fleet->id ? 'selected' : '' }}>
                            {{ $fleet->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label for="vehicle_type" class="form-label">Vehicle Type</label>
                <select class="form-select" id="vehicle_type" name="vehicle_type">
                    <option value="">All Types</option>
                    <option value="truck" {{ request('vehicle_type') == 'truck' ? 'selected' : '' }}>Truck</option>
                    <option value="van" {{ request('vehicle_type') == 'van' ? 'selected' : '' }}>Van</option>
                    <option value="pickup" {{ request('vehicle_type') == 'pickup' ? 'selected' : '' }}>Pickup</option>
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

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <x-ui.stats-widget
            title="Total Vehicles"
            :value="$stats['total_vehicles'] ?? 0"
            icon="bi bi-car-front"
            variant="primary"
        />
    </div>
    <div class="col-md-4 mb-3">
        <x-ui.stats-widget
            title="Active Vehicles"
            :value="$stats['active_vehicles'] ?? 0"
            icon="bi bi-play-circle"
            variant="success"
        />
    </div>
    <div class="col-md-4 mb-3">
        <x-ui.stats-widget
            title="In Maintenance"
            :value="$stats['maintenance_vehicles'] ?? 0"
            icon="bi bi-tools"
            variant="warning"
        />
    </div>
</div>

<!-- Vehicles Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Your Vehicles</h5>
        <div class="btn-group" role="group" aria-label="Table actions">
            <button type="button" class="btn btn-sm btn-outline-primary" id="refreshBtn" aria-label="Refresh vehicle data">
                <i class="bi bi-arrow-clockwise" aria-hidden="true"></i> Refresh
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover" id="vehiclesTable" role="table" aria-label="Vehicles table">
                <thead class="table-dark">
                    <tr>
                        <th scope="col" aria-sort="none">Registration</th>
                        <th scope="col" aria-sort="none">Type</th>
                        <th scope="col" aria-sort="none">Make/Model</th>
                        <th scope="col" aria-sort="none">Fleet</th>
                        <th scope="col" aria-sort="none">Status</th>
                        <th scope="col" aria-sort="none">Actions</th>
                    </tr>
                </thead>
                <tbody id="vehiclesTableBody">
                    @forelse($vehicles as $vehicle)
                    <tr data-vehicle-id="{{ $vehicle->id }}">
                        <td>{{ $vehicle->registration_number }}</td>
                        <td>
                            <span class="badge bg-secondary">{{ ucfirst($vehicle->vehicle_type) }}</span>
                        </td>
                        <td>{{ $vehicle->make }} {{ $vehicle->model }} @if($vehicle->year) ({{ $vehicle->year }}) @endif</td>
                        <td>{{ $vehicle->fleet->name ?? 'No Fleet' }}</td>
                        <td>
                            <span class="badge
                                @if($vehicle->status == 'active') bg-success
                                @elseif($vehicle->status == 'maintenance') bg-warning
                                @else bg-secondary
                                @endif">
                                {{ ucfirst($vehicle->status) }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group" role="group" aria-label="Vehicle actions">
                                <button type="button" class="btn btn-sm btn-outline-primary view-vehicle" data-vehicle-id="{{ $vehicle->id }}" aria-label="View vehicle details">
                                    <i class="bi bi-eye" aria-hidden="true"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary edit-vehicle" data-vehicle-id="{{ $vehicle->id }}" aria-label="Edit vehicle">
                                    <i class="bi bi-pencil" aria-hidden="true"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger delete-vehicle" data-vehicle-id="{{ $vehicle->id }}" aria-label="Delete vehicle">
                                    <i class="bi bi-trash" aria-hidden="true"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            <i class="bi bi-car-front" style="font-size: 2rem;" aria-hidden="true"></i>
                            <p class="mb-0 mt-2">No vehicles found.</p>
                            <button type="button" class="btn btn-primary btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#createVehicleModal">
                                <i class="bi bi-plus-circle" aria-hidden="true"></i> Add Your First Vehicle
                            </button>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($vehicles->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $vehicles->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Create Vehicle Modal -->
<x-ui.modal id="createVehicleModal" title="Add New Vehicle" size="lg">
    <form id="createVehicleForm">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="registrationNumber" class="form-label">Registration Number *</label>
                <input type="text" class="form-control" id="registrationNumber" required aria-describedby="registrationHelp">
                <div id="registrationHelp" class="form-text">Official vehicle registration number</div>
            </div>
            <div class="col-md-6 mb-3">
                <label for="vehicleType" class="form-label">Vehicle Type *</label>
                <select class="form-select" id="vehicleType" required aria-describedby="vehicleTypeHelp">
                    <option value="">Select Type</option>
                    <option value="truck">Truck</option>
                    <option value="van">Van</option>
                    <option value="pickup">Pickup</option>
                    <option value="motorcycle">Motorcycle</option>
                    <option value="car">Car</option>
                </select>
                <div id="vehicleTypeHelp" class="form-text">Type of vehicle</div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="make" class="form-label">Make</label>
                <input type="text" class="form-control" id="make" aria-describedby="makeHelp">
                <div id="makeHelp" class="form-text">Vehicle manufacturer</div>
            </div>
            <div class="col-md-4 mb-3">
                <label for="model" class="form-label">Model</label>
                <input type="text" class="form-control" id="model" aria-describedby="modelHelp">
                <div id="modelHelp" class="form-text">Vehicle model</div>
            </div>
            <div class="col-md-4 mb-3">
                <label for="year" class="form-label">Year</label>
                <input type="number" class="form-control" id="year" min="1900" max="{{ date('Y') + 1 }}" aria-describedby="yearHelp">
                <div id="yearHelp" class="form-text">Manufacturing year</div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="capacity" class="form-label">Capacity (kg)</label>
                <input type="number" class="form-control" id="capacity" min="0" step="0.1" aria-describedby="capacityHelp">
                <div id="capacityHelp" class="form-text">Maximum load capacity in kilograms</div>
            </div>
            <div class="col-md-6 mb-3">
                <label for="vehicleFleet" class="form-label">Fleet</label>
                <select class="form-select" id="vehicleFleet" aria-describedby="fleetHelp">
                    <option value="">No Fleet</option>
                    @foreach($fleets as $fleet)
                        <option value="{{ $fleet->id }}">{{ $fleet->name }}</option>
                    @endforeach
                </select>
                <div id="fleetHelp" class="form-text">Assign to a fleet (optional)</div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="vehicleStatus" class="form-label">Status</label>
                <select class="form-select" id="vehicleStatus" aria-describedby="statusHelp">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="maintenance">Maintenance</option>
                </select>
                <div id="statusHelp" class="form-text">Current operational status</div>
            </div>
            <div class="col-md-6 mb-3">
                <label for="vehicleNotes" class="form-label">Notes</label>
                <textarea class="form-control" id="vehicleNotes" rows="2" aria-describedby="notesHelp"></textarea>
                <div id="notesHelp" class="form-text">Additional notes about the vehicle</div>
            </div>
        </div>
    </form>

    <x-slot name="footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="createVehicleBtn">
            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
            Add Vehicle
        </button>
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
                    <option value="pickup">Pickup</option>
                    <option value="motorcycle">Motorcycle</option>
                    <option value="car">Car</option>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="editMake" class="form-label">Make</label>
                <input type="text" class="form-control" id="editMake">
            </div>
            <div class="col-md-4 mb-3">
                <label for="editModel" class="form-label">Model</label>
                <input type="text" class="form-control" id="editModel">
            </div>
            <div class="col-md-4 mb-3">
                <label for="editYear" class="form-label">Year</label>
                <input type="number" class="form-control" id="editYear" min="1900" max="{{ date('Y') + 1 }}">
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="editCapacity" class="form-label">Capacity (kg)</label>
                <input type="number" class="form-control" id="editCapacity" min="0" step="0.1">
            </div>
            <div class="col-md-6 mb-3">
                <label for="editVehicleFleet" class="form-label">Fleet</label>
                <select class="form-select" id="editVehicleFleet">
                    <option value="">No Fleet</option>
                    @foreach($fleets as $fleet)
                        <option value="{{ $fleet->id }}">{{ $fleet->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="editVehicleStatus" class="form-label">Status</label>
                <select class="form-select" id="editVehicleStatus">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="maintenance">Maintenance</option>
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label for="editVehicleNotes" class="form-label">Notes</label>
                <textarea class="form-control" id="editVehicleNotes" rows="2"></textarea>
            </div>
        </div>
    </form>

    <x-slot name="footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="updateVehicleBtn">
            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
            Update Vehicle
        </button>
    </x-slot>
</x-ui.modal>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteVehicleModal" tabindex="-1" aria-labelledby="deleteVehicleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteVehicleModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this vehicle? This action cannot be undone.</p>
                <div class="alert alert-warning">
                    <strong>Warning:</strong> Deleting this vehicle will remove it from the system permanently.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteVehicle">Delete Vehicle</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentVehicleId = null;

    // Create vehicle
    document.getElementById('createVehicleBtn').addEventListener('click', function() {
        const data = {
            registration_number: document.getElementById('registrationNumber').value.trim(),
            vehicle_type: document.getElementById('vehicleType').value,
            make: document.getElementById('make').value.trim(),
            model: document.getElementById('model').value.trim(),
            year: document.getElementById('year').value,
            capacity: document.getElementById('capacity').value,
            fleet_id: document.getElementById('vehicleFleet').value,
            status: document.getElementById('vehicleStatus').value,
            notes: document.getElementById('vehicleNotes').value.trim()
        };

        if (!data.registration_number || !data.vehicle_type) {
            showToast('Registration number and vehicle type are required', 'danger');
            return;
        }

        this.disabled = true;
        this.querySelector('.spinner-border').classList.remove('d-none');

        createVehicle(data);
    });

    // Edit vehicle button handler
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('edit-vehicle') || e.target.closest('.edit-vehicle')) {
            e.preventDefault();
            const button = e.target.classList.contains('edit-vehicle') ? e.target : e.target.closest('.edit-vehicle');
            const vehicleId = button.getAttribute('data-vehicle-id');
            openEditModal(vehicleId);
        }
    });

    // Update vehicle
    document.getElementById('updateVehicleBtn').addEventListener('click', function() {
        const data = {
            registration_number: document.getElementById('editRegistrationNumber').value.trim(),
            vehicle_type: document.getElementById('editVehicleType').value,
            make: document.getElementById('editMake').value.trim(),
            model: document.getElementById('editModel').value.trim(),
            year: document.getElementById('editYear').value,
            capacity: document.getElementById('editCapacity').value,
            fleet_id: document.getElementById('editVehicleFleet').value,
            status: document.getElementById('editVehicleStatus').value,
            notes: document.getElementById('editVehicleNotes').value.trim()
        };

        if (!data.registration_number || !data.vehicle_type) {
            showToast('Registration number and vehicle type are required', 'danger');
            return;
        }

        this.disabled = true;
        this.querySelector('.spinner-border').classList.remove('d-none');

        updateVehicle(currentVehicleId, data);
    });

    // Delete vehicle handler
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('delete-vehicle') || e.target.closest('.delete-vehicle')) {
            e.preventDefault();
            const button = e.target.classList.contains('delete-vehicle') ? e.target : e.target.closest('.delete-vehicle');
            const vehicleId = button.getAttribute('data-vehicle-id');

            const modal = new bootstrap.Modal(document.getElementById('deleteVehicleModal'));
            document.getElementById('confirmDeleteVehicle').onclick = function() {
                deleteVehicle(vehicleId);
                modal.hide();
            };
            modal.show();
        }
    });

    // View vehicle handler
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('view-vehicle') || e.target.closest('.view-vehicle')) {
            e.preventDefault();
            const button = e.target.classList.contains('view-vehicle') ? e.target : e.target.closest('.view-vehicle');
            const vehicleId = button.getAttribute('data-vehicle-id');
            window.location.href = `/company/vehicles/${vehicleId}`;
        }
    });

    // Refresh button
    document.getElementById('refreshBtn').addEventListener('click', function() {
        this.innerHTML = '<i class="bi bi-arrow-clockwise spinning" aria-hidden="true"></i> Refreshing...';
        this.disabled = true;

        refreshVehicles().finally(() => {
            this.innerHTML = '<i class="bi bi-arrow-clockwise" aria-hidden="true"></i> Refresh';
            this.disabled = false;
        });
    });

    // Modal reset on hide
    document.getElementById('createVehicleModal').addEventListener('hidden.bs.modal', function() {
        document.getElementById('createVehicleForm').reset();
        document.getElementById('createVehicleBtn').disabled = false;
        document.getElementById('createVehicleBtn').querySelector('.spinner-border').classList.add('d-none');
    });

    document.getElementById('editVehicleModal').addEventListener('hidden.bs.modal', function() {
        document.getElementById('editVehicleForm').reset();
        document.getElementById('updateVehicleBtn').disabled = false;
        document.getElementById('updateVehicleBtn').querySelector('.spinner-border').classList.add('d-none');
    });

    function createVehicle(data) {
        fetch('/api/company/vehicles', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Vehicle added successfully!', 'success');
                bootstrap.Modal.getInstance(document.getElementById('createVehicleModal')).hide();
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(data.message || 'Failed to add vehicle', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred while adding the vehicle', 'danger');
        })
        .finally(() => {
            document.getElementById('createVehicleBtn').disabled = false;
            document.getElementById('createVehicleBtn').querySelector('.spinner-border').classList.add('d-none');
        });
    }

    function openEditModal(vehicleId) {
        currentVehicleId = vehicleId;

        // Fetch vehicle data
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
                document.getElementById('editVehicleFleet').value = vehicle.fleet_id || '';
                document.getElementById('editVehicleStatus').value = vehicle.status;
                document.getElementById('editVehicleNotes').value = vehicle.notes || '';

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

    function updateVehicle(vehicleId, data) {
        fetch(`/api/company/vehicles/${vehicleId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(data)
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
        })
        .finally(() => {
            document.getElementById('updateVehicleBtn').disabled = false;
            document.getElementById('updateVehicleBtn').querySelector('.spinner-border').classList.add('d-none');
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
                document.querySelector(`tr[data-vehicle-id="${vehicleId}"]`).remove();
            } else {
                showToast(data.message || 'Failed to delete vehicle', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred while deleting the vehicle', 'danger');
        });
    }

    function refreshVehicles() {
        return fetch(window.location.href, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (response.ok) {
                showToast('Vehicles refreshed successfully!', 'success');
            }
        })
        .catch(error => {
            console.error('Error refreshing vehicles:', error);
            showToast('Failed to refresh vehicles', 'danger');
        });
    }

    function showToast(message, type = 'info') {
        const toastContainer = document.getElementById('toastContainer') || createToastContainer();

        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type} border-0`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');

        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;

        toastContainer.appendChild(toast);
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();

        toast.addEventListener('hidden.bs.toast', () => {
            toast.remove();
        });
    }

    function createToastContainer() {
        const container = document.createElement('div');
        container.id = 'toastContainer';
        container.className = 'toast-container position-fixed top-0 end-0 p-3';
        container.style.zIndex = '9999';
        document.body.appendChild(container);
        return container;
    }
});
</script>

<style>
.spinning {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
@endpush
@endsection
