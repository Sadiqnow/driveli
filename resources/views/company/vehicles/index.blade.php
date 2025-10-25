@extends('company.layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0"><i class="bi bi-car-front"></i> Vehicle Management</h2>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createVehicleModal">
                <i class="bi bi-plus-circle"></i> Add Vehicle
            </button>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="fleet_id" class="form-label">Fleet</label>
                <select class="form-select" id="fleet_id" name="fleet_id">
                    <option value="">All Fleets</option>
                    @foreach($fleets ?? [] as $fleet)
                        <option value="{{ $fleet->id }}" {{ request('fleet_id') == $fleet->id ? 'selected' : '' }}>
                            {{ $fleet->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                    <option value="sold" {{ request('status') == 'sold' ? 'selected' : '' }}>Sold</option>
                </select>
            </div>

            <div class="col-md-3">
                <label for="vehicle_type" class="form-label">Type</label>
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
                <button type="submit" class="btn btn-primary me-2">
                    <i class="bi bi-search"></i> Filter
                </button>
                <a href="{{ route('company.vehicles.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> Clear
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
        @if($vehicles->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Registration</th>
                            <th>Make & Model</th>
                            <th>Type</th>
                            <th>Fleet</th>
                            <th>Status</th>
                            <th>Mileage</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($vehicles as $vehicle)
                            <tr>
                                <td>
                                    <strong>{{ $vehicle->registration_number }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $vehicle->vin }}</small>
                                </td>
                                <td>
                                    {{ $vehicle->make }} {{ $vehicle->model }}
                                    <br>
                                    <small class="text-muted">{{ $vehicle->year }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ ucfirst($vehicle->vehicle_type) }}</span>
                                </td>
                                <td>
                                    {{ $vehicle->fleet->name ?? 'Unassigned' }}
                                </td>
                                <td>
                                    <span class="badge bg-{{ $vehicle->status === 'active' ? 'success' : ($vehicle->status === 'maintenance' ? 'warning' : 'secondary') }}">
                                        {{ ucfirst($vehicle->status) }}
                                    </span>
                                </td>
                                <td>
                                    {{ number_format($vehicle->mileage ?? 0) }} km
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('company.vehicles.show', $vehicle) }}" class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-secondary btn-sm"
                                                onclick="editVehicle({{ $vehicle->id }})">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger btn-sm"
                                                onclick="deleteVehicle({{ $vehicle->id }})">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-3">
                {{ $vehicles->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-car-front" style="font-size: 3rem; color: #6c757d;"></i>
                <h5 class="mt-3">No Vehicles Found</h5>
                <p class="text-muted">Add your first vehicle to start managing your fleet.</p>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createVehicleModal">
                    <i class="bi bi-plus-circle"></i> Add Vehicle
                </button>
            </div>
        @endif
    </div>
</div>

<!-- Create Vehicle Modal -->
<div class="modal fade" id="createVehicleModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Vehicle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createVehicleForm" method="POST" action="{{ route('company.vehicles.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="registration_number" class="form-label">Registration Number *</label>
                            <input type="text" class="form-control" id="registration_number" name="registration_number" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="vin" class="form-label">VIN</label>
                            <input type="text" class="form-control" id="vin" name="vin">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="make" class="form-label">Make *</label>
                            <input type="text" class="form-control" id="make" name="make" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="model" class="form-label">Model *</label>
                            <input type="text" class="form-control" id="model" name="model" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="year" class="form-label">Year *</label>
                            <input type="number" class="form-control" id="year" name="year" min="1900" max="{{ date('Y') + 1 }}" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="vehicle_type" class="form-label">Vehicle Type *</label>
                            <select class="form-select" id="vehicle_type" name="vehicle_type" required>
                                <option value="">Select Type</option>
                                <option value="truck">Truck</option>
                                <option value="van">Van</option>
                                <option value="pickup">Pickup Truck</option>
                                <option value="motorcycle">Motorcycle</option>
                                <option value="car">Car</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="seating_capacity" class="form-label">Seating Capacity</label>
                            <input type="number" class="form-control" id="seating_capacity" name="seating_capacity" min="1">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="color" class="form-label">Color</label>
                            <input type="text" class="form-control" id="color" name="color">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="fleet_id" class="form-label">Assign to Fleet</label>
                            <select class="form-select" id="fleet_id" name="fleet_id">
                                <option value="">Select Fleet</option>
                                @foreach($fleets ?? [] as $fleet)
                                    <option value="{{ $fleet->id }}">{{ $fleet->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="purchase_price" class="form-label">Purchase Price (₦)</label>
                            <input type="number" step="0.01" class="form-control" id="purchase_price" name="purchase_price" min="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="purchase_date" class="form-label">Purchase Date</label>
                            <input type="date" class="form-control" id="purchase_date" name="purchase_date">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="insurance_expiry" class="form-label">Insurance Expiry</label>
                            <input type="date" class="form-control" id="insurance_expiry" name="insurance_expiry">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="road_worthiness_expiry" class="form-label">Road Worthiness Expiry</label>
                            <input type="date" class="form-control" id="road_worthiness_expiry" name="road_worthiness_expiry">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Vehicle</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Vehicle Modal -->
<div class="modal fade" id="editVehicleModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Vehicle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editVehicleForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <!-- Form fields will be populated by JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Vehicle</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function editVehicle(vehicleId) {
    fetch(`/company/vehicles/${vehicleId}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const vehicle = data.data;
                const form = document.getElementById('editVehicleForm');
                form.action = `/company/vehicles/${vehicleId}`;

                const modalBody = form.querySelector('.modal-body');
                modalBody.innerHTML = `
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_registration_number" class="form-label">Registration Number *</label>
                            <input type="text" class="form-control" id="edit_registration_number" name="registration_number" value="${vehicle.registration_number}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_vin" class="form-label">VIN</label>
                            <input type="text" class="form-control" id="edit_vin" name="vin" value="${vehicle.vin || ''}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="edit_make" class="form-label">Make *</label>
                            <input type="text" class="form-control" id="edit_make" name="make" value="${vehicle.make}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_model" class="form-label">Model *</label>
                            <input type="text" class="form-control" id="edit_model" name="model" value="${vehicle.model}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_year" class="form-label">Year *</label>
                            <input type="number" class="form-control" id="edit_year" name="year" value="${vehicle.year}" min="1900" max="2025" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_vehicle_type" class="form-label">Vehicle Type *</label>
                            <select class="form-select" id="edit_vehicle_type" name="vehicle_type" required>
                                <option value="truck" ${vehicle.vehicle_type === 'truck' ? 'selected' : ''}>Truck</option>
                                <option value="van" ${vehicle.vehicle_type === 'van' ? 'selected' : ''}>Van</option>
                                <option value="pickup" ${vehicle.vehicle_type === 'pickup' ? 'selected' : ''}>Pickup Truck</option>
                                <option value="motorcycle" ${vehicle.vehicle_type === 'motorcycle' ? 'selected' : ''}>Motorcycle</option>
                                <option value="car" ${vehicle.vehicle_type === 'car' ? 'selected' : ''}>Car</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_seating_capacity" class="form-label">Seating Capacity</label>
                            <input type="number" class="form-control" id="edit_seating_capacity" name="seating_capacity" value="${vehicle.seating_capacity || ''}" min="1">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_color" class="form-label">Color</label>
                            <input type="text" class="form-control" id="edit_color" name="color" value="${vehicle.color || ''}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_status" class="form-label">Status</label>
                            <select class="form-select" id="edit_status" name="status">
                                <option value="active" ${vehicle.status === 'active' ? 'selected' : ''}>Active</option>
                                <option value="maintenance" ${vehicle.status === 'maintenance' ? 'selected' : ''}>Maintenance</option>
                                <option value="sold" ${vehicle.status === 'sold' ? 'selected' : ''}>Sold</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_mileage" class="form-label">Mileage (km)</label>
                            <input type="number" class="form-control" id="edit_mileage" name="mileage" value="${vehicle.mileage || ''}" min="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_current_value" class="form-label">Current Value (₦)</label>
                            <input type="number" step="0.01" class="form-control" id="edit_current_value" name="current_value" value="${vehicle.current_value || ''}" min="0">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="edit_notes" name="notes" rows="3">${vehicle.notes || ''}</textarea>
                    </div>
                `;

                const editModal = new bootstrap.Modal(document.getElementById('editVehicleModal'));
                editModal.show();
            }
        })
        .catch(error => console.error('Error loading vehicle:', error));
}

function deleteVehicle(vehicleId) {
    if (confirm('Are you sure you want to delete this vehicle? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/company/vehicles/${vehicleId}`;

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken;
        form.appendChild(csrfInput);

        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);

        document.body.appendChild(form);
        form.submit();
    }
}

// Reset form when modal is hidden
document.getElementById('createVehicleModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('createVehicleForm').reset();
});
</script>
@endpush
@endsection
