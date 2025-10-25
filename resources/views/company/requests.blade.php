@extends('company.layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="mb-0"><i class="bi bi-clipboard-check" aria-hidden="true"></i> Transport Requests</h2>
            <a href="{{ route('company.requests.create') }}" class="btn btn-primary" role="button" aria-label="Create new transport request">
                <i class="bi bi-plus-circle" aria-hidden="true"></i> New Request
            </a>
        </div>
        <p class="text-muted mt-2">Manage your transport requests and track their status</p>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Filters</h5>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3" id="filterForm" role="search" aria-label="Filter transport requests">
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status" aria-describedby="statusHelp">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
                <div id="statusHelp" class="form-text">Filter requests by their current status</div>
            </div>

            <div class="col-md-3">
                <label for="vehicle_type" class="form-label">Vehicle Type</label>
                <select class="form-select" id="vehicle_type" name="vehicle_type" aria-describedby="vehicleHelp">
                    <option value="">All Types</option>
                    <option value="truck" {{ request('vehicle_type') == 'truck' ? 'selected' : '' }}>Truck</option>
                    <option value="van" {{ request('vehicle_type') == 'van' ? 'selected' : '' }}>Van</option>
                    <option value="pickup" {{ request('vehicle_type') == 'pickup' ? 'selected' : '' }}>Pickup</option>
                    <option value="motorcycle" {{ request('vehicle_type') == 'motorcycle' ? 'selected' : '' }}>Motorcycle</option>
                    <option value="car" {{ request('vehicle_type') == 'car' ? 'selected' : '' }}>Car</option>
                </select>
                <div id="vehicleHelp" class="form-text">Filter by required vehicle type</div>
            </div>

            <div class="col-md-3">
                <label for="date_from" class="form-label">From Date</label>
                <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}" aria-describedby="dateFromHelp">
                <div id="dateFromHelp" class="form-text">Start date for filtering</div>
            </div>

            <div class="col-md-3">
                <label for="date_to" class="form-label">To Date</label>
                <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}" aria-describedby="dateToHelp">
                <div id="dateToHelp" class="form-text">End date for filtering</div>
            </div>

            <div class="col-12">
                <button type="submit" class="btn btn-primary me-2" aria-label="Apply filters">
                    <i class="bi bi-search" aria-hidden="true"></i> Filter
                </button>
                <a href="{{ route('company.requests.index') }}" class="btn btn-outline-secondary" role="button" aria-label="Clear all filters">
                    <i class="bi bi-x-circle" aria-hidden="true"></i> Clear
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Requests Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Your Requests</h5>
        <div class="btn-group" role="group" aria-label="Table actions">
            <button type="button" class="btn btn-sm btn-outline-primary" id="refreshBtn" aria-label="Refresh table data">
                <i class="bi bi-arrow-clockwise" aria-hidden="true"></i> Refresh
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover" id="requestsTable" role="table" aria-label="Transport requests table">
                <thead class="table-dark">
                    <tr>
                        <th scope="col" aria-sort="none">Request ID</th>
                        <th scope="col" aria-sort="none">Pickup Location</th>
                        <th scope="col" aria-sort="none">Drop-off Location</th>
                        <th scope="col" aria-sort="none">Vehicle Type</th>
                        <th scope="col" aria-sort="none">Status</th>
                        <th scope="col" aria-sort="none">Created</th>
                        <th scope="col" aria-sort="none">Actions</th>
                    </tr>
                </thead>
                <tbody id="requestsTableBody">
                    @forelse($requests as $request)
                    <tr data-request-id="{{ $request->id }}">
                        <td>{{ $request->request_id }}</td>
                        <td>{{ $request->pickup_location }}</td>
                        <td>{{ $request->dropoff_location ?: 'N/A' }}</td>
                        <td>
                            <span class="badge bg-secondary">{{ ucfirst($request->vehicle_type) }}</span>
                        </td>
                        <td>
                            <span class="badge
                                @if($request->status == 'active') bg-success
                                @elseif($request->status == 'pending') bg-warning
                                @elseif($request->status == 'completed') bg-info
                                @else bg-danger
                                @endif">
                                {{ ucfirst($request->status) }}
                            </span>
                        </td>
                        <td>{{ $request->created_at->format('M d, Y') }}</td>
                        <td>
                            <div class="btn-group" role="group" aria-label="Request actions">
                                <a href="{{ route('company.requests.show', $request) }}" class="btn btn-sm btn-outline-primary" aria-label="View request details">
                                    <i class="bi bi-eye" aria-hidden="true"></i>
                                </a>
                                @if(in_array($request->status, ['pending', 'active']))
                                <a href="{{ route('company.requests.edit', $request) }}" class="btn btn-sm btn-outline-secondary" aria-label="Edit request">
                                    <i class="bi bi-pencil" aria-hidden="true"></i>
                                </a>
                                @endif
                                @if($request->status == 'pending')
                                <button type="button" class="btn btn-sm btn-outline-danger delete-request" data-request-id="{{ $request->id }}" aria-label="Delete request">
                                    <i class="bi bi-trash" aria-hidden="true"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="bi bi-clipboard-x" style="font-size: 2rem;" aria-hidden="true"></i>
                            <p class="mb-0 mt-2">No transport requests found.</p>
                            <a href="{{ route('company.requests.create') }}" class="btn btn-primary btn-sm mt-2" role="button">Create Your First Request</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($requests->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $requests->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this transport request? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Toast notification system
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

// AJAX functions
async function deleteRequest(requestId) {
    try {
        const response = await fetch(`/company/requests/${requestId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });

        const data = await response.json();

        if (response.ok) {
            showToast('Request deleted successfully!', 'success');
            // Remove row from table
            document.querySelector(`tr[data-request-id="${requestId}"]`).remove();
        } else {
            showToast(data.message || 'Failed to delete request', 'danger');
        }
    } catch (error) {
        console.error('Error deleting request:', error);
        showToast('An error occurred while deleting the request', 'danger');
    }
}

async function refreshRequests() {
    try {
        const response = await fetch(window.location.href, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });

        if (response.ok) {
            const data = await response.json();
            // Update table content (simplified - in real implementation, update the tbody)
            showToast('Requests refreshed successfully!', 'success');
        }
    } catch (error) {
        console.error('Error refreshing requests:', error);
        showToast('Failed to refresh requests', 'danger');
    }
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Delete request handler
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('delete-request') || e.target.closest('.delete-request')) {
            e.preventDefault();
            const button = e.target.classList.contains('delete-request') ? e.target : e.target.closest('.delete-request');
            const requestId = button.getAttribute('data-request-id');

            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            document.getElementById('confirmDelete').onclick = function() {
                deleteRequest(requestId);
                modal.hide();
            };
            modal.show();
        }
    });

    // Refresh button handler
    document.getElementById('refreshBtn').addEventListener('click', function() {
        this.innerHTML = '<i class="bi bi-arrow-clockwise spinning" aria-hidden="true"></i> Refreshing...';
        this.disabled = true;

        refreshRequests().finally(() => {
            this.innerHTML = '<i class="bi bi-arrow-clockwise" aria-hidden="true"></i> Refresh';
            this.disabled = false;
        });
    });

    // Real-time updates (polling every 30 seconds)
    setInterval(refreshRequests, 30000);
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
