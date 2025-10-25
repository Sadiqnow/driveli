@extends('company.layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="mb-0"><i class="bi bi-clipboard-check"></i> Transport Requests</h2>
            <a href="{{ route('company.requests.create') }}" class="btn btn-primary" aria-label="Create new transport request">
                <i class="bi bi-plus-circle" aria-hidden="true"></i> New Request
            </a>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-funnel"></i> Filters</h5>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3" role="search" aria-label="Filter transport requests">
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
                <select class="form-select" id="vehicle_type" name="vehicle_type">
                    <option value="">All Types</option>
                    <option value="truck" {{ request('vehicle_type') == 'truck' ? 'selected' : '' }}>Truck</option>
                    <option value="van" {{ request('vehicle_type') == 'van' ? 'selected' : '' }}>Van</option>
                    <option value="pickup" {{ request('vehicle_type') == 'pickup' ? 'selected' : '' }}>Pickup Truck</option>
                    <option value="motorcycle" {{ request('vehicle_type') == 'motorcycle' ? 'selected' : '' }}>Motorcycle</option>
                    <option value="car" {{ request('vehicle_type') == 'car' ? 'selected' : '' }}>Car</option>
                </select>
            </div>

            <div class="col-md-3">
                <label for="date_from" class="form-label">From Date</label>
                <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
            </div>

            <div class="col-md-3">
                <label for="date_to" class="form-label">To Date</label>
                <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
            </div>

            <div class="col-12">
                <button type="submit" class="btn btn-primary me-2" aria-label="Apply filters">
                    <i class="bi bi-search" aria-hidden="true"></i> Filter
                </button>
                <a href="{{ route('company.requests.index') }}" class="btn btn-outline-secondary" aria-label="Clear all filters">
                    <i class="bi bi-x-circle" aria-hidden="true"></i> Clear
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Requests Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Your Transport Requests</h5>
        <div class="input-group" style="width: 300px;">
            <input type="text" class="form-control" id="searchInput" placeholder="Search requests..." aria-label="Search transport requests">
            <button class="btn btn-outline-secondary" type="button" id="searchBtn">
                <i class="bi bi-search" aria-hidden="true"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        @if(isset($requests) && $requests->count() > 0)
            <x-ui.table
                :headers="[
                    ['label' => 'Request ID', 'sortable' => true],
                    ['label' => 'Pickup Location', 'sortable' => true],
                    ['label' => 'Drop-off Location'],
                    ['label' => 'Vehicle Type'],
                    ['label' => 'Status'],
                    ['label' => 'Created'],
                    ['label' => 'Actions']
                ]"
                :data="$requests"
                :actions="[
                    [
                        'text' => 'View',
                        'icon' => 'bi bi-eye',
                        'class' => 'btn-outline-primary',
                        'href' => '#',
                        'data' => ['action' => 'view']
                    ],
                    [
                        'text' => 'Edit',
                        'icon' => 'bi bi-pencil',
                        'class' => 'btn-outline-warning',
                        'href' => '#',
                        'data' => ['action' => 'edit']
                    ]
                ]"
                empty-message="No transport requests found"
            />
        @else
            <div class="text-center py-5">
                <i class="bi bi-clipboard-x" style="font-size: 3rem; color: #6c757d;" aria-hidden="true"></i>
                <h5 class="mt-3">No Transport Requests Yet</h5>
                <p class="text-muted">Create your first transport request to get started.</p>
                <a href="{{ route('company.requests.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle" aria-hidden="true"></i> Create Request
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Pagination -->
@if(isset($requests) && $requests->hasPages())
<div class="d-flex justify-content-center mt-4">
    {{ $requests->links() }}
</div>
@endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const searchBtn = document.getElementById('searchBtn');

    // Search functionality
    searchBtn.addEventListener('click', performSearch);
    searchInput.addEventListener('keyup', function(e) {
        if (e.key === 'Enter') {
            performSearch();
        }
    });

    function performSearch() {
        const query = searchInput.value.trim();
        if (query) {
            // Implement search logic here
            console.log('Searching for:', query);
            // You can add AJAX search or redirect with query parameter
        }
    }

    // Table action handlers
    document.querySelectorAll('[data-action]').forEach(btn => {
        btn.addEventListener('click', function() {
            const action = this.getAttribute('data-action');
            const requestId = this.closest('tr').querySelector('td:first-child').textContent;

            switch(action) {
                case 'view':
                    window.location.href = `/company/requests/${requestId}`;
                    break;
                case 'edit':
                    window.location.href = `/company/requests/${requestId}/edit`;
                    break;
            }
        });
    });
});
</script>
@endpush
@endsection
