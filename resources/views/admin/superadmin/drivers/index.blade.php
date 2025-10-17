@extends('layouts.admin_master')

@section('title', 'Superadmin - Driver Management')

@section('content_header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Driver Management</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.superadmin.dashboard') }}">Super Admin</a></li>
                        <li class="breadcrumb-item active">Drivers</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div></div>
            <div>
                <div class="btn-group">
                    <a href="{{ route('admin.superadmin.drivers.create') }}" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> Add Driver
                    </a>
                    <a href="{{ route('admin.superadmin.drivers.analytics') }}" class="btn btn-info">
                        <i class="fas fa-chart-bar"></i> Analytics
                    </a>
                    <a href="{{ route('admin.superadmin.drivers.audit') }}" class="btn btn-warning">
                        <i class="fas fa-history"></i> Audit Trail
                    </a>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-lg-2 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ number_format($stats['total']) }}</h3>
                        <p>Total Drivers</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <a href="{{ route('admin.superadmin.drivers.index') }}" class="small-box-footer">
                        View All <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-2 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ number_format($stats['active']) }}</h3>
                        <p>Active</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <a href="{{ route('admin.superadmin.drivers.index', ['status' => 'active']) }}" class="small-box-footer">
                        View Active <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-2 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ number_format($stats['inactive']) }}</h3>
                        <p>Inactive</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-pause-circle"></i>
                    </div>
                    <a href="{{ route('admin.superadmin.drivers.index', ['status' => 'inactive']) }}" class="small-box-footer">
                        View Inactive <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-2 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ number_format($stats['flagged']) }}</h3>
                        <p>Flagged</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-flag"></i>
                    </div>
                    <a href="{{ route('admin.superadmin.drivers.index', ['status' => 'flagged']) }}" class="small-box-footer">
                        View Flagged <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-2 col-6">
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h3>{{ number_format($stats['verified']) }}</h3>
                        <p>Verified</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <a href="{{ route('admin.superadmin.drivers.index', ['verification_status' => 'verified']) }}" class="small-box-footer">
                        View Verified <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-2 col-6">
                <div class="small-box bg-secondary">
                    <div class="inner">
                        <h3>{{ number_format($stats['kyc_completed']) }}</h3>
                        <p>KYC Complete</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-id-card"></i>
                    </div>
                    <a href="{{ route('admin.superadmin.drivers.index', ['kyc_status' => 'completed']) }}" class="small-box-footer">
                        View KYC Complete <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Performance Metrics Row -->
        <div class="row mb-4">
            <!-- Average Rating Widget -->
            <div class="col-lg-3 col-6">
                <div class="info-box">
                    <span class="info-box-icon bg-yellow"><i class="fas fa-star"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Average Rating</span>
                        <span class="info-box-number">{{ number_format($analytics['average_rating'] ?? 0, 1) }}</span>
                        <div class="progress">
                            <div class="progress-bar bg-yellow" style="width: {{ ($analytics['average_rating'] ?? 0) * 20 }}%"></div>
                        </div>
                        <span class="progress-description">
                            Out of 5 stars
                        </span>
                    </div>
                </div>
            </div>

            <!-- Total Jobs Completed Widget -->
            <div class="col-lg-3 col-6">
                <div class="info-box">
                    <span class="info-box-icon bg-green"><i class="fas fa-briefcase"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Jobs Completed</span>
                        <span class="info-box-number">{{ number_format($analytics['total_jobs_completed'] ?? 0) }}</span>
                        <div class="progress">
                            <div class="progress-bar bg-green" style="width: 100%"></div>
                        </div>
                        <span class="progress-description">
                            All time total
                        </span>
                    </div>
                </div>
            </div>

            <!-- Total Earnings Widget -->
            <div class="col-lg-3 col-6">
                <div class="info-box">
                    <span class="info-box-icon bg-purple"><i class="fas fa-dollar-sign"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Earnings</span>
                        <span class="info-box-number">${{ number_format($analytics['total_earnings'] ?? 0, 0) }}</span>
                        <div class="progress">
                            <div class="progress-bar bg-purple" style="width: 100%"></div>
                        </div>
                        <span class="progress-description">
                            Driver earnings
                        </span>
                    </div>
                </div>
            </div>

            <!-- New Registrations This Month Widget -->
            <div class="col-lg-3 col-6">
                <div class="info-box">
                    <span class="info-box-icon bg-red"><i class="fas fa-user-plus"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">New This Month</span>
                        <span class="info-box-number">{{ $stats['new_this_month'] ?? 0 }}</span>
                        <div class="progress">
                            <div class="progress-bar bg-red" style="width: {{ min(($stats['new_this_month'] ?? 0) * 10, 100) }}%"></div>
                        </div>
                        <span class="progress-description">
                            {{ now()->format('M Y') }} registrations
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-pie mr-1"></i>
                            Verification Status Overview
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6 text-center">
                                <div class="text-success">
                                    <span class="h4">{{ number_format((($stats['verified'] ?? 0) / max(($stats['total'] ?? 1), 1)) * 100, 1) }}%</span>
                                </div>
                                <div class="text-muted">Verified</div>
                            </div>
                            <div class="col-6 text-center">
                                <div class="text-warning">
                                    <span class="h4">{{ number_format((($stats['pending_verification'] ?? 0) / max(($stats['total'] ?? 1), 1)) * 100, 1) }}%</span>
                                </div>
                                <div class="text-muted">Pending</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-tachometer-alt mr-1"></i>
                            Driver Activity
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6 text-center">
                                <div class="text-primary">
                                    <span class="h4">{{ $analytics['active_drivers_today'] ?? 0 }}</span>
                                </div>
                                <div class="text-muted">Active Today</div>
                            </div>
                            <div class="col-6 text-center">
                                <div class="text-info">
                                    <span class="h4">{{ $analytics['online_drivers'] ?? 0 }}</span>
                                </div>
                                <div class="text-muted">Online Now</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>



        <!-- Filters and Actions -->
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-6">
                        <h3 class="card-title">Drivers</h3>
                    </div>
                    <div class="col-md-6 text-right">
                        <a href="{{ route('admin.superadmin.drivers.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Driver
                        </a>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card-header">
                <form method="GET" action="{{ route('admin.superadmin.drivers.index') }}" class="row g-2">
                    <div class="col-md-3">
                        <label for="driver-search" class="visually-hidden">Search drivers</label>
                        <input type="text"
                               id="driver-search"
                               name="search"
                               class="form-control"
                               placeholder="Search by name, phone, email..."
                               value="{{ request('search') }}"
                               aria-describedby="search-help">
                        <div id="search-help" class="visually-hidden">Enter text to search for drivers by name, phone number, or email address</div>
                    </div>
                    <div class="col-md-2">
                        <label for="status-filter" class="visually-hidden">Filter by status</label>
                        <select id="status-filter" name="status" class="form-control" aria-label="Filter drivers by status">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status')=='active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status')=='inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="flagged" {{ request('status')=='flagged' ? 'selected' : '' }}>Flagged</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="verification-filter" class="visually-hidden">Filter by verification status</label>
                        <select id="verification-filter" name="verification_status" class="form-control" aria-label="Filter drivers by verification status">
                            <option value="">All Verification</option>
                            <option value="pending" {{ request('verification_status')=='pending' ? 'selected' : '' }}>Pending</option>
                            <option value="verified" {{ request('verification_status')=='verified' ? 'selected' : '' }}>Verified</option>
                            <option value="rejected" {{ request('verification_status')=='rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="kyc-filter" class="visually-hidden">Filter by KYC status</label>
                        <select id="kyc-filter" name="kyc_status" class="form-control" aria-label="Filter drivers by KYC status">
                            <option value="">All KYC</option>
                            <option value="pending" {{ request('kyc_status')=='pending' ? 'selected' : '' }}>Pending</option>
                            <option value="in_progress" {{ request('kyc_status')=='in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="completed" {{ request('kyc_status')=='completed' ? 'selected' : '' }}>Completed</option>
                            <option value="rejected" {{ request('kyc_status')=='rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-success w-100" aria-label="Search drivers with current filters">
                            <i class="fas fa-search" aria-hidden="true"></i>
                            <span class="visually-hidden">Search</span>
                        </button>
                    </div>
                    <div class="col-md-1">
                        <a href="{{ route('admin.superadmin.drivers.index') }}" class="btn btn-secondary w-100">Reset</a>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-info w-100" data-toggle="modal" data-target="#bulkActionModal">
                            <i class="fas fa-tasks"></i>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Bulk Actions -->
            <div class="card-header">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="select-all">
                            <label class="form-check-label" for="select-all">Select All</label>
                        </div>
                    </div>
                    <div class="col-md-6 text-right">
                        <div class="btn-group" id="bulk-actions" style="display: none;">
                            <button type="button" class="btn btn-success btn-sm" onclick="bulkAction('approve')">
                                <i class="fas fa-check"></i> Bulk Approve
                            </button>
                            <button type="button" class="btn btn-warning btn-sm" onclick="bulkAction('reject')">
                                <i class="fas fa-times"></i> Bulk Reject
                            </button>
                            <button type="button" class="btn btn-danger btn-sm" onclick="bulkAction('flag')">
                                <i class="fas fa-flag"></i> Bulk Flag
                            </button>
                            <button type="button" class="btn btn-info btn-sm" onclick="bulkAction('restore')">
                                <i class="fas fa-undo"></i> Bulk Restore
                            </button>
                            <button type="button" class="btn btn-dark btn-sm" onclick="bulkAction('delete')">
                                <i class="fas fa-trash"></i> Bulk Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Drivers Table -->
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th width="30">
                                <input type="checkbox" id="select-all-header">
                            </th>
                            <th>Driver ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Verification</th>
                            <th>KYC Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($drivers as $driver)
                            <tr>
                                <td>
                                    <input type="checkbox" class="driver-checkbox" value="{{ $driver->id }}">
                                </td>
                                <td>
                                    <a href="{{ route('admin.superadmin.drivers.show', $driver) }}">
                                        {{ $driver->driver_id }}
                                    </a>
                                </td>
                                <td>{{ $driver->full_name }}</td>
                                <td>{{ $driver->email }}</td>
                                <td>{{ $driver->phone }}</td>
                                <td>
                                    <span class="badge badge-{{ $driver->status == 'active' ? 'success' : ($driver->status == 'inactive' ? 'warning' : 'danger') }}">
                                        {{ ucfirst($driver->status) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $driver->verification_status == 'verified' ? 'success' : ($driver->verification_status == 'pending' ? 'warning' : 'danger') }}">
                                        {{ ucfirst($driver->verification_status) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $driver->kyc_status == 'completed' ? 'success' : ($driver->kyc_status == 'in_progress' ? 'info' : 'secondary') }}">
                                        {{ ucfirst(str_replace('_', ' ', $driver->kyc_status)) }}
                                    </span>
                                </td>
                                <td>{{ $driver->created_at->format('M d, Y') }}</td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('admin.superadmin.drivers.show', $driver) }}" class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.superadmin.drivers.edit', $driver) }}" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-secondary btn-sm dropdown-toggle" data-toggle="dropdown">
                                                <i class="fas fa-cogs"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                @if($driver->verification_status == 'pending')
                                                    <a class="dropdown-item" href="#" onclick="approveDriver({{ $driver->id }})">
                                                        <i class="fas fa-check text-success"></i> Approve
                                                    </a>
                                                    <a class="dropdown-item" href="#" onclick="rejectDriver({{ $driver->id }})">
                                                        <i class="fas fa-times text-danger"></i> Reject
                                                    </a>
                                                @endif
                                                @if($driver->status != 'flagged')
                                                    <a class="dropdown-item" href="#" onclick="flagDriver({{ $driver->id }})">
                                                        <i class="fas fa-flag text-warning"></i> Flag
                                                    </a>
                                                @else
                                                    <a class="dropdown-item" href="#" onclick="restoreDriver({{ $driver->id }})">
                                                        <i class="fas fa-undo text-info"></i> Restore
                                                    </a>
                                                @endif
                                                <div class="dropdown-divider"></div>
                                                <a class="dropdown-item text-danger" href="#" onclick="deleteDriver({{ $driver->id }}, '{{ $driver->full_name }}')">
                                                    <i class="fas fa-trash"></i> Delete
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <i class="fas fa-users fa-2x text-muted mb-2"></i>
                                    <p class="text-muted">No drivers found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($drivers->hasPages())
                <div class="card-footer">
                    {{ $drivers->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Modals -->
    <!-- Approve Modal -->
    <div class="modal fade" id="approveModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Approve Driver</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="approveForm">
                    @csrf
                    <div class="modal-body">
                        <p>Are you sure you want to approve <strong id="approveDriverName"></strong>?</p>
                        <div class="form-group">
                            <label for="approvalNotes">Approval Notes (Optional)</label>
                            <textarea class="form-control" id="approvalNotes" name="notes" rows="3" placeholder="Add any notes about this approval..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Approve Driver</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reject Driver</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="rejectForm">
                    @csrf
                    <div class="modal-body">
                        <p>Are you sure you want to reject <strong id="rejectDriverName"></strong>?</p>
                        <div class="form-group">
                            <label for="rejectionReason">Rejection Reason <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="rejectionReason" name="reason" rows="3" required placeholder="Please provide a reason for rejection..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Reject Driver</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Flag Modal -->
    <div class="modal fade" id="flagModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Flag Driver</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="flagForm">
                    @csrf
                    <div class="modal-body">
                        <p>Are you sure you want to flag <strong id="flagDriverName"></strong>?</p>
                        <div class="form-group">
                            <label for="flagReason">Flag Reason <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="flagReason" name="reason" rows="3" required placeholder="Please provide a reason for flagging..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Flag Driver</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Driver</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete <strong id="deleteDriverName"></strong>?</p>
                    <p class="text-danger">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <form id="deleteForm" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete Driver</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
.table th {
    border-top: none;
    font-weight: 600;
    font-size: 0.875rem;
}

.badge {
    font-size: 0.75rem;
}

.btn-group .btn {
    margin-right: 2px;
}

.dropdown-menu {
    min-width: 150px;
}

.modal-body p {
    margin-bottom: 1rem;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Select all functionality
    $('#select-all, #select-all-header').on('change', function() {
        $('.driver-checkbox').prop('checked', $(this).prop('checked'));
        toggleBulkActions();
    });

    $('.driver-checkbox').on('change', function() {
        toggleBulkActions();
        updateSelectAllState();
    });

    function toggleBulkActions() {
        const checkedBoxes = $('.driver-checkbox:checked').length;
        $('#bulk-actions').toggle(checkedBoxes > 0);
    }

    function updateSelectAllState() {
        const totalBoxes = $('.driver-checkbox').length;
        const checkedBoxes = $('.driver-checkbox:checked').length;
        $('#select-all, #select-all-header').prop('checked', totalBoxes > 0 && checkedBoxes === totalBoxes);
    }
});

// Modal functions
let currentDriverId = null;

function approveDriver(driverId) {
    // Get driver name via AJAX or from data attribute
    $('#approveDriverName').text('this driver');
    currentDriverId = driverId;
    $('#approveModal').modal('show');
}

function rejectDriver(driverId) {
    $('#rejectDriverName').text('this driver');
    currentDriverId = driverId;
    $('#rejectModal').modal('show');
}

function flagDriver(driverId) {
    $('#flagDriverName').text('this driver');
    currentDriverId = driverId;
    $('#flagModal').modal('show');
}

function deleteDriver(driverId, driverName) {
    $('#deleteDriverName').text(driverName);
    $('#deleteForm').attr('action', `{{ url('admin/superadmin/drivers') }}/${driverId}`);
    $('#deleteModal').modal('show');
}

// Form submissions
$('#approveForm').on('submit', function(e) {
    e.preventDefault();
    const notes = $('#approvalNotes').val();

    $.post(`{{ url('admin/superadmin/drivers') }}/${currentDriverId}/approve`, {
        _token: '{{ csrf_token() }}',
        notes: notes
    })
    .done(function(response) {
        $('#approveModal').modal('hide');
        location.reload();
    })
    .fail(function(xhr) {
        alert('Error: ' + xhr.responseJSON?.message || 'Failed to approve driver');
    });
});

$('#rejectForm').on('submit', function(e) {
    e.preventDefault();
    const reason = $('#rejectionReason').val();

    $.post(`{{ url('admin/superadmin/drivers') }}/${currentDriverId}/reject`, {
        _token: '{{ csrf_token() }}',
        reason: reason
    })
    .done(function(response) {
        $('#rejectModal').modal('hide');
        location.reload();
    })
    .fail(function(xhr) {
        alert('Error: ' + xhr.responseJSON?.message || 'Failed to reject driver');
    });
});

$('#flagForm').on('submit', function(e) {
    e.preventDefault();
    const reason = $('#flagReason').val();

    $.post(`{{ url('admin/superadmin/drivers') }}/${currentDriverId}/flag`, {
        _token: '{{ csrf_token() }}',
        reason: reason
    })
    .done(function(response) {
        $('#flagModal').modal('hide');
        location.reload();
    })
    .fail(function(xhr) {
        alert('Error: ' + xhr.responseJSON?.message || 'Failed to flag driver');
    });
});

// Bulk actions
function bulkAction(action) {
    const selectedIds = $('.driver-checkbox:checked').map(function() {
        return $(this).val();
    }).get();

    if (selectedIds.length === 0) {
        alert('Please select at least one driver');
        return;
    }

    if (!confirm(`Are you sure you want to ${action} ${selectedIds.length} driver(s)?`)) {
        return;
    }

    // Show loading
    const btn = event.target;
    const originalHtml = $(btn).html();
    $(btn).html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);

    let url, data = {
        _token: '{{ csrf_token() }}',
        driver_ids: selectedIds
    };

    switch(action) {
        case 'approve':
            url = '{{ route("admin.superadmin.drivers.bulk-approve") }}';
            break;
        case 'reject':
            url = '{{ route("admin.superadmin.drivers.bulk-reject") }}';
            data.reason = prompt('Please provide a reason for bulk rejection:');
            if (!data.reason) return;
            break;
        case 'flag':
            url = '{{ route("admin.superadmin.drivers.bulk-flag") }}';
            data.reason = prompt('Please provide a reason for bulk flagging:');
            if (!data.reason) return;
            break;
        case 'restore':
            url = '{{ route("admin.superadmin.drivers.bulk-restore") }}';
            break;
        case 'delete':
            url = '{{ route("admin.superadmin.drivers.bulk-delete") }}';
            break;
    }

    $.post(url, data)
    .done(function(response) {
        location.reload();
    })
    .fail(function(xhr) {
        alert('Error: ' + xhr.responseJSON?.message || `Failed to bulk ${action} drivers`);
    })
    .always(function() {
        $(btn).html(originalHtml).prop('disabled', false);
    });
}
</script>
@endpush
