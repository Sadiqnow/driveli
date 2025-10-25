@extends('company.layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="mb-0"><i class="bi bi-truck" aria-hidden="true"></i> Fleet Management</h2>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createFleetModal" aria-label="Create new fleet">
                <i class="bi bi-plus-circle" aria-hidden="true"></i> Add Fleet
            </button>
        </div>
        <p class="text-muted mt-2">Manage your vehicle fleets and track performance</p>
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
            icon="bi bi-play-circle"
            variant="success"
        />
    </div>
    <div class="col-md-4 mb-3">
        <x-ui.stats-widget
            title="Total Vehicles"
            :value="$stats['total_vehicles'] ?? 0"
            icon="bi bi-car-front"
            variant="info"
        />
    </div>
</div>

<!-- Fleets Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Your Fleets</h5>
        <div class="btn-group" role="group" aria-label="Table actions">
            <button type="button" class="btn btn-sm btn-outline-primary" id="refreshBtn" aria-label="Refresh fleet data">
                <i class="bi bi-arrow-clockwise" aria-hidden="true"></i> Refresh
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover" id="fleetsTable" role="table" aria-label="Fleets table">
                <thead class="table-dark">
                    <tr>
                        <th scope="col" aria-sort="none">Fleet Name</th>
                        <th scope="col" aria-sort="none">Description</th>
                        <th scope="col" aria-sort="none">Vehicles</th>
                        <th scope="col" aria-sort="none">Status</th>
                        <th scope="col" aria-sort="none">Created</th>
                        <th scope="col" aria-sort="none">Actions</th>
                    </tr>
                </thead>
                <tbody id="fleetsTableBody">
                    @forelse($fleets as $fleet)
                    <tr data-fleet-id="{{ $fleet->id }}">
                        <td>{{ $fleet->name }}</td>
                        <td>{{ $fleet->description ?: 'No description' }}</td>
                        <td>
                            <span class="badge bg-info">{{ $fleet->vehicles_count }} vehicles</span>
                        </td>
                        <td>
                            <span class="badge
                                @if($fleet->status == 'active') bg-success
                                @else bg-secondary
                                @endif">
                                {{ ucfirst($fleet->status) }}
                            </span>
                        </td>
                        <td>{{ $fleet->created_at->format('M d, Y') }}</td>
                        <td>
                            <div class="btn-group" role="group" aria-label="Fleet actions">
                                <button type="button" class="btn btn-sm btn-outline-primary view-fleet" data-fleet-id="{{ $fleet->id }}" aria-label="View fleet details">
                                    <i class="bi bi-eye" aria-hidden="true"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary edit-fleet" data-fleet-id="{{ $fleet->id }}" aria-label="Edit fleet">
                                    <i class="bi bi-pencil" aria-hidden="true"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger delete-fleet" data-fleet-id="{{ $fleet->id }}" aria-label="Delete fleet">
                                    <i class="bi bi-trash" aria-hidden="true"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            <i class="bi bi-truck" style="font-size: 2rem;" aria-hidden="true"></i>
                            <p class="mb-0 mt-2">No fleets found.</p>
                            <button type="button" class="btn btn-primary btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#createFleetModal">
                                <i class="bi bi-plus-circle" aria-hidden="true"></i> Create Your First Fleet
                            </button>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($fleets->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $fleets->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>

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
            <select class="form-select" id="fleetStatus" aria-describedby="fleetStatusHelp">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
            <div id="fleetStatusHelp" class="form-text">Set the initial status of the fleet</div>
        </div>
    </form>

    <x-slot name="footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="createFleetBtn">
            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
            Create Fleet
        </button>
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
        <button type="button" class="btn btn-primary" id="updateFleetBtn">
            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
            Update Fleet
        </button>
    </x-slot>
</x-ui.modal>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteFleetModal" tabindex="-1" aria-labelledby="deleteFleetModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteFleetModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this fleet? This action cannot be undone and will affect all associated vehicles.</p>
                <div class="alert alert-warning">
                    <strong>Warning:</strong> Deleting this fleet will remove it from all associated vehicles.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteFleet">Delete Fleet</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentFleetId = null;

    // Create fleet
    document.getElementById('createFleetBtn').addEventListener('click', function() {
        const name = document.getElementById('fleetName').value.trim();
        const description = document.getElementById('fleetDescription').value.trim();
        const status = document.getElementById('fleetStatus').value;

        if (!name) {
            showToast('Fleet name is required', 'danger');
            return;
        }

        this.disabled = true;
        this.querySelector('.spinner-border').classList.remove('d-none');

        createFleet({ name, description, status });
    });

    // Edit fleet button handler
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('edit-fleet') || e.target.closest('.edit-fleet')) {
            e.preventDefault();
            const button = e.target.classList.contains('edit-fleet') ? e.target : e.target.closest('.edit-fleet');
            const fleetId = button.getAttribute('data-fleet-id');
            openEditModal(fleetId);
        }
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

        this.disabled = true;
        this.querySelector('.spinner-border').classList.remove('d-none');

        updateFleet(currentFleetId, { name, description, status });
    });

    // Delete fleet handler
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('delete-fleet') || e.target.closest('.delete-fleet')) {
            e.preventDefault();
            const button = e.target.classList.contains('delete-fleet') ? e.target : e.target.closest('.delete-fleet');
            const fleetId = button.getAttribute('data-fleet-id');

            const modal = new bootstrap.Modal(document.getElementById('deleteFleetModal'));
            document.getElementById('confirmDeleteFleet').onclick = function() {
                deleteFleet(fleetId);
                modal.hide();
            };
            modal.show();
        }
    });

    // View fleet handler
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('view-fleet') || e.target.closest('.view-fleet')) {
            e.preventDefault();
            const button = e.target.classList.contains('view-fleet') ? e.target : e.target.closest('.view-fleet');
            const fleetId = button.getAttribute('data-fleet-id');
            window.location.href = `/company/fleets/${fleetId}`;
        }
    });

    // Refresh button
    document.getElementById('refreshBtn').addEventListener('click', function() {
        this.innerHTML = '<i class="bi bi-arrow-clockwise spinning" aria-hidden="true"></i> Refreshing...';
        this.disabled = true;

        refreshFleets().finally(() => {
            this.innerHTML = '<i class="bi bi-arrow-clockwise" aria-hidden="true"></i> Refresh';
            this.disabled = false;
        });
    });

    // Modal reset on hide
    document.getElementById('createFleetModal').addEventListener('hidden.bs.modal', function() {
        document.getElementById('createFleetForm').reset();
        document.getElementById('createFleetBtn').disabled = false;
        document.getElementById('createFleetBtn').querySelector('.spinner-border').classList.add('d-none');
    });

    document.getElementById('editFleetModal').addEventListener('hidden.bs.modal', function() {
        document.getElementById('editFleetForm').reset();
        document.getElementById('updateFleetBtn').disabled = false;
        document.getElementById('updateFleetBtn').querySelector('.spinner-border').classList.add('d-none');
    });

    function createFleet(data) {
        fetch('/api/company/fleets', {
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
                showToast('Fleet created successfully!', 'success');
                bootstrap.Modal.getInstance(document.getElementById('createFleetModal')).hide();
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(data.message || 'Failed to create fleet', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred while creating the fleet', 'danger');
        })
        .finally(() => {
            document.getElementById('createFleetBtn').disabled = false;
            document.getElementById('createFleetBtn').querySelector('.spinner-border').classList.add('d-none');
        });
    }

    function openEditModal(fleetId) {
        currentFleetId = fleetId;

        // Fetch fleet data
        fetch(`/api/company/fleets/${fleetId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('editFleetId').value = data.data.id;
                document.getElementById('editFleetName').value = data.data.name;
                document.getElementById('editFleetDescription').value = data.data.description || '';
                document.getElementById('editFleetStatus').value = data.data.status;

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

    function updateFleet(fleetId, data) {
        fetch(`/api/company/fleets/${fleetId}`, {
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
        })
        .finally(() => {
            document.getElementById('updateFleetBtn').disabled = false;
            document.getElementById('updateFleetBtn').querySelector('.spinner-border').classList.add('d-none');
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
                document.querySelector(`tr[data-fleet-id="${fleetId}"]`).remove();
            } else {
                showToast(data.message || 'Failed to delete fleet', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred while deleting the fleet', 'danger');
        });
    }

    function refreshFleets() {
        return fetch(window.location.href, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (response.ok) {
                showToast('Fleets refreshed successfully!', 'success');
            }
        })
        .catch(error => {
            console.error('Error refreshing fleets:', error);
            showToast('Failed to refresh fleets', 'danger');
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
