@extends('layouts.admin_cdn')

@section('title', 'Driver Verification Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1>Verification Dashboard</h1>
        </div>
    </div>
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h4 class="mb-0">{{ $statistics['statistics']['total_drivers'] ?? 0 }}</h4>
                            <p class="mb-0">Total Drivers</p>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h4 class="mb-0">{{ $statistics['statistics']['status_breakdown']['verified'] ?? 0 }}</h4>
                            <p class="mb-0">Verified Drivers</p>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h4 class="mb-0">{{ $statistics['statistics']['status_breakdown']['requires_manual_review'] ?? 0 }}</h4>
                            <p class="mb-0">Pending Review</p>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h4 class="mb-0">{{ $statistics['statistics']['status_breakdown']['failed'] ?? 0 }}</h4>
                            <p class="mb-0">Failed Verifications</p>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="fas fa-times-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="row align-items-end">
                        <div class="col-md-4">
                            <label for="start_date">Start Date</label>
                            <input type="date" name="start_date" id="start_date" class="form-control" 
                                   value="{{ $dateRange['start'] }}">
                        </div>
                        <div class="col-md-4">
                            <label for="end_date">End Date</label>
                            <input type="date" name="end_date" id="end_date" class="form-control" 
                                   value="{{ $dateRange['end'] }}">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="{{ route('admin.verification.dashboard') }}" class="btn btn-secondary">Reset</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Pending Manual Reviews -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Pending Manual Reviews</h5>
                </div>
                <div class="card-body">
                    @if($pendingReviews->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Driver</th>
                                        <th>Score</th>
                                        <th>Started</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pendingReviews as $driver)
                                    <tr>
                                        <td>
                                            <strong>{{ $driver->first_name }} {{ $driver->last_name }}</strong><br>
                                            <small class="text-muted">{{ $driver->email }}</small>
                                        </td>
                                        <td>
                                            <span class="badge badge-warning">
                                                {{ number_format($driver->overall_verification_score ?? 0, 1) }}%
                                            </span>
                                        </td>
                                        <td>
                                            <small>{{ $driver->verification_started_at ? $driver->verification_started_at->diffForHumans() : 'N/A' }}</small>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.verification.driver-details', $driver->id) }}" 
                                               class="btn btn-sm btn-primary">Review</a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">No pending reviews</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Recent Verification Activities</h5>
                </div>
                <div class="card-body">
                    @if($recentActivities->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Driver</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentActivities as $activity)
                                    <tr>
                                        <td>
                                            <strong>{{ $activity->first_name }} {{ $activity->last_name }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge badge-info">{{ ucfirst(str_replace('_', ' ', $activity->verification_type)) }}</span>
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $activity->status === 'completed' ? 'success' : ($activity->status === 'failed' ? 'danger' : 'warning') }}">
                                                {{ ucfirst($activity->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <small>{{ \Carbon\Carbon::parse($activity->created_at)->diffForHumans() }}</small>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">No recent activities</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Failed Verifications -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Failed Verifications</h5>
                    <div>
                        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#bulkApproveModal">
                            Bulk Approve
                        </button>
                        <a href="{{ route('admin.verification.report') }}" class="btn btn-sm btn-info">
                            Generate Report
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($failedVerifications->count() > 0)
                        <form id="bulkForm">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>
                                                <input type="checkbox" id="selectAll">
                                            </th>
                                            <th>Driver</th>
                                            <th>Email</th>
                                            <th>Score</th>
                                            <th>Last Updated</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($failedVerifications as $driver)
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="driver_ids[]" value="{{ $driver->id }}" class="driver-checkbox">
                                            </td>
                                            <td>
                                                <strong>{{ $driver->first_name }} {{ $driver->last_name }}</strong>
                                            </td>
                                            <td>{{ $driver->email }}</td>
                                            <td>
                                                <span class="badge badge-danger">
                                                    {{ number_format($driver->overall_verification_score ?? 0, 1) }}%
                                                </span>
                                            </td>
                                            <td>
                                                <small>{{ $driver->updated_at->diffForHumans() }}</small>
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.verification.driver-details', $driver->id) }}" 
                                                   class="btn btn-sm btn-primary">Review</a>
                                                <form method="POST" action="{{ route('admin.verification.retry', $driver->id) }}" 
                                                      style="display: inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-warning">Retry</button>
                                                </form>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </form>
                    @else
                        <p class="text-muted">No failed verifications</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Approve Modal -->
<div class="modal fade" id="bulkApproveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.verification.bulk-approve') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Bulk Approve Verifications</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes (Optional)</label>
                        <textarea name="notes" id="notes" class="form-control" rows="3" 
                                  placeholder="Add notes for bulk approval..."></textarea>
                    </div>
                    <div id="selectedDrivers"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Approve Selected</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Select all functionality
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.driver-checkbox');
    checkboxes.forEach(checkbox => checkbox.checked = this.checked);
    updateSelectedDrivers();
});

// Individual checkbox change
document.querySelectorAll('.driver-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', updateSelectedDrivers);
});

function updateSelectedDrivers() {
    const selectedCheckboxes = document.querySelectorAll('.driver-checkbox:checked');
    const selectedDriversDiv = document.getElementById('selectedDrivers');
    
    if (selectedCheckboxes.length > 0) {
        selectedDriversDiv.innerHTML = `<p><strong>${selectedCheckboxes.length}</strong> driver(s) selected for bulk approval.</p>`;
        
        // Add hidden inputs for selected driver IDs
        selectedCheckboxes.forEach(checkbox => {
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'driver_ids[]';
            hiddenInput.value = checkbox.value;
            selectedDriversDiv.appendChild(hiddenInput);
        });
    } else {
        selectedDriversDiv.innerHTML = '<p class="text-muted">No drivers selected.</p>';
    }
}
</script>
@endsection