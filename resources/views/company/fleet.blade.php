@extends('company.layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="mb-0"><i class="bi bi-truck"></i> Fleet Management</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createFleetModal" aria-label="Create new fleet">
                <i class="bi bi-plus-circle" aria-hidden="true"></i> Add Fleet
            </button>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <x-ui.stats-widget
            title="Total Fleets"
            :value="$stats['total_fleets'] ?? 0"
            icon="bi bi-truck"
            variant="primary"
        />
    </div>
    <div class="col-md-4 mb-3">
        <x-ui.stats-widget
            title="Active Vehicles"
            :value="$stats['active_vehicles'] ?? 0"
            icon="bi bi-car-front"
            variant="success"
        />
    </div>
    <div class="col-md-4 mb-3">
        <x-ui.stats-widget
            title="Total Vehicles"
            :value="$stats['total_vehicles'] ?? 0"
            icon="bi bi-car-front-fill"
            variant="info"
        />
    </div>
</div>

<!-- Fleets Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Your Fleets</h5>
    </div>
    <div class="card-body">
        @if(isset($fleets) && $fleets->count() > 0)
            <x-ui.table
                :headers="[
                    ['label' => 'Fleet Name', 'sortable' => true],
                    ['label' => 'Description'],
                    ['label' => 'Vehicles Count'],
                    ['label' => 'Status'],
                    ['label' => 'Created'],
                    ['label' => 'Actions']
                ]"
                :data="$fleets->map(function($fleet) {
                    return [
                        'name' => $fleet->name,
                        'description' => Str::limit($fleet->description ?? '', 50),
                        'vehicles_count' => $fleet->vehicles_count ?? 0,
                        'status' => ucfirst($fleet->status ?? 'active'),
                        'created_at' => $fleet->created_at->format('M d, Y')
                    ];
                })"
                :actions="[
                    [
                        'text' => 'View',
                        'icon' => 'bi bi-eye',
                        'class' => 'btn-outline-primary',
                        'data' => ['action' => 'view', 'id' => 'fleet_id']
                    ],
                    [
                        'text' => 'Edit',
                        'icon' => 'bi bi-pencil',
                        'class' => 'btn-outline-warning',
                        'data' => ['action' => 'edit', 'id' => 'fleet_id']
                    ],
                    [
                        'text' => 'Delete',
                        'icon' => 'bi bi-trash',
                        'class' => 'btn-outline-danger',
                        'data' => ['action' => 'delete', 'id' => 'fleet_id']
                    ]
                ]"
                empty-message="No fleets found"
            />
        @else
            <div class="text-center py-5">
                <i class="bi bi-truck" style="font-size: 3rem; color: #6c757d;" aria-hidden="true"></i>
                <h5 class="mt-3">No Fleets Yet</h5>
                <p class="text-muted">Create your first fleet to organize your vehicles.</p>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createFleetModal">
                    <i class="bi bi-plus-circle" aria-hidden="true"></i> Create Fleet
                </button>
            </div>
        @endif
    </div>
</div>

<!-- Pagination -->
@if(isset($fleets) && $fleets->hasPages())
<div class="d-flex justify-content-center mt-4">
    {{ $fleets->links() }}
</div>
@endif

<!-- Create Fleet Modal -->
<x-ui.modal id="createFleetModal" title="Create New Fleet" size="md">
    <form id="createFleetForm">
        <div class="mb-3">
            <label for="fleetName" class="form-label">Fleet Name *</label>
            <input type="text" class="form-control" id="fleetName" required aria-describedby="fleetNameHelp">
            <div id="fleetNameHelp" class="form-text">Enter a unique name for your fleet</div>
        </div>
        <div class="mb-3">
            <label for="fleetDescription" class="form-label">Description</label>
            <textarea class="form-control" id="fleetDescription" rows="3" aria-describedby="fleetDescriptionHelp"></textarea>
            <div id="fleetDescriptionHelp" class="form-text">Optional description of the fleet</div>
        </div>
        <div class="mb-3">
            <label for="fleetStatus" class="form-label">Status</label>
            <select class="form-select" id="fleetStatus">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
    </form>

    <x-slot name="footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="saveFleetBtn">Create Fleet</button>
    </x-slot>
</x-ui.modal>

<!-- Edit Fleet Modal -->
<x-ui.modal id="editFleetModal" title="Edit Fleet" size="md">
    <form id="editFleetForm">
        <input type="hidden" id="editFleetId">
        <div class="mb-3">
            <label for="editFleetName" class="form-label">Fleet Name *</label>
            <input type="text" class="form-control" id="editFleetName" required>
        </div>
        <div class="mb-3">
            <label for="editFleetDescription" class="form-label">Description</label>
            <textarea class="form-control" id="editFleetDescription" rows="3"></textarea>
        </div>
        <div class="mb-3">
            <label for="editFleetStatus" class="form-label">Status</label>
            <select class="form-select" id="editFleetStatus">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
    </form>

    <x-slot name="footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="updateFleetBtn">Update Fleet</button>
    </x-slot>
</x-ui.modal>

<!-- Delete Confirmation Modal -->
<x-ui.modal id="deleteFleetModal" title="Delete Fleet" size="md">
    <p>Are you sure you want to delete this fleet? This action cannot be undone.</p>
    <p class="text-danger"><strong>Note:</strong> All vehicles in this fleet will be unassigned.</p>

    <x-slot name="footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete Fleet</button>
    </x-slot>
</x-ui.modal>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentFleetId = null;

    // Action button handlers
    document.querySelectorAll('[data-action]').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const action = this.getAttribute('data-action');
            const fleetId = this.getAttribute('data-id') || this.closest('tr').getAttribute('data-fleet-id');

            switch(action) {
                case 'view':
                    window.location.href = `/company/fleets/${fleetId}`;
                    break;
                case 'edit':
                    openEditModal(fleetId);
                    break;
                case 'delete':
                    openDeleteModal(fleetId);
                    break;
            }
        });
    });

    // Create fleet
    document.getElementById('saveFleetBtn').addEventListener('click', function() {
        const name = document.getElementById('fleetName').value.trim();
        const description = document.getElementById('fleetDescription').value.trim();
        const status = document.getElementById('fleetStatus').value;

        if (!name) {
            showToast('Fleet name is required', 'danger');
            return;
        }

        createFleet(name, description, status);
    });

    // Update fleet
    document.getElementById('updateFleetBtn').addEventListener('click', function() {
        const name = document.getElementById('editFleetName').value.trim();
        const description = document.getElementById('editFleetDescription').value.trim();
        const status = document.getElementById('editFleetStatus').value;

        if (!name) {
            showToast('Fleet name is required', 'danger');
            return;
        }

        updateFleet(currentFleetId, name, description, status);
    });

    // Delete fleet
    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        deleteFleet(currentFleetId);
    });

    function openEditModal(fleetId) {
        currentFleetId = fleetId;
        // Fetch fleet data and populate modal
        fetch(`/api/company/fleets/${fleetId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('editFleetId').value = data.data.id;
                    document.getElementById('editFleetName').value = data.data.name;
                    document.getElementById('editFleetDescription').value = data.data.description || '';
                    document.getElementById('editFleetStatus').value = data.data.status || 'active';

                    const modal = new bootstrap.Modal(document.getElementById('editFleetModal'));
                    modal.show();
                } else {
                    showToast('Failed to load fleet data', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred while loading fleet data', 'danger');
            });
    }

    function openDeleteModal(fleetId) {
        currentFleetId = fleetId;
        const modal = new bootstrap.Modal(document.getElementById('deleteFleetModal'));
        modal.show();
    }

    function createFleet(name, description, status) {
        fetch('/api/company/fleets', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                name: name,
                description: description,
                status: status
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Fleet created successfully!', 'success');
                bootstrap.Modal.getInstance(document.getElementById('createFleetModal')).hide();
                document.getElementById('createFleetForm').reset();
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(data.message || 'Failed to create fleet', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred while creating the fleet', 'danger');
        });
    }

    function updateFleet(fleetId, name, description, status) {
        fetch(`/api/company/fleets/${fleetId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                name: name,
                description: description,
                status: status
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Fleet updated successfully!', 'success');
                bootstrap.Modal.getInstance(document.getElementById('editFleetModal')).hide();
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(data.message || 'Failed to update fleet', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred while updating the fleet', 'danger');
        });
    }

    function deleteFleet(fleetId) {
        fetch(`/api/company/fleets/${fleetId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Fleet deleted successfully!', 'success');
                bootstrap.Modal.getInstance(document.getElementById('deleteFleetModal')).hide();
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(data.message || 'Failed to delete fleet', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred while deleting the fleet', 'danger');
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
