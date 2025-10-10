@extends('layouts.admin_cdn')

@section('title', 'Driver Management - Optimized')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0">Driver Pool Management</h1>
        <div class="btn-group" role="group">
            <a href="{{ route('admin.drivers.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Driver
            </a>
            <a href="{{ route('admin.drivers.bulk-operations') }}" class="btn btn-secondary">
                <i class="fas fa-tasks"></i> Bulk Operations
            </a>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-download"></i> Export
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ route('admin.drivers.export', 'csv') }}">CSV</a></li>
                    <li><a class="dropdown-item" href="{{ route('admin.drivers.export', 'excel') }}">Excel</a></li>
                    <li><a class="dropdown-item" href="{{ route('admin.drivers.export', 'pdf') }}">PDF</a></li>
                </ul>
            </div>
        </div>
    </div>
@stop

@section('content')
    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Driver Statistics Cards -->
    <div class="row mb-4">
        <!-- Total Drivers -->
        <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title">{{ number_format($drivers->total() ?? 0) }}</h4>
                            <p class="card-text">Total Drivers</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.drivers.index') }}" class="text-white text-decoration-none">
                        View All <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Verified Drivers -->
        <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title">{{ number_format($verifiedCount ?? 0) }}</h4>
                            <p class="card-text">Verified Drivers</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-user-check fa-2x"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.drivers.verification', ['type' => 'verified']) }}" class="text-white text-decoration-none">
                        View Verified <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Pending Verification -->
        <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title">{{ number_format($pendingCount ?? 0) }}</h4>
                            <p class="card-text">Pending Verification</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.drivers.verification', ['type' => 'pending']) }}" class="text-white text-decoration-none">
                        Review Pending <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Active Drivers -->
        <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="card-title">{{ number_format($activeCount ?? 0) }}</h4>
                            <p class="card-text">Active Drivers</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-user-plus fa-2x"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.drivers.index', ['status' => 'active']) }}" class="text-white text-decoration-none">
                        View Active <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Metrics Row -->
    <div class="row mb-4">
        <!-- Total Earnings -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-left-success">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                        Total Earnings (₦)
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        {{ number_format($totalEarnings ?? 0, 2) }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Jobs -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-left-info">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                        Jobs Completed
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        {{ number_format($totalJobsCompleted ?? 0) }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Average Rating -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-left-warning">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                        Average Rating
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        {{ number_format($averageRating ?? 0, 1) }} / 5.0
                    </div>
                </div>
            </div>
        </div>

        <!-- Online Now -->
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-left-danger">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                        Online Now
                    </div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                        {{ number_format($onlineDrivers ?? 0) }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filters Card -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-filter me-2"></i>Search & Filters
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.drivers.index') }}" id="driver-filters">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="search">Search Drivers</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="search" 
                                   name="search" 
                                   value="{{ request('search') }}"
                                   placeholder="Name, email, phone, ID...">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select class="form-control" id="status" name="status">
                                <option value="">All Statuses</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                                <option value="blocked" {{ request('status') === 'blocked' ? 'selected' : '' }}>Blocked</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="verification_status">Verification</label>
                            <select class="form-control" id="verification_status" name="verification_status">
                                <option value="">All Verification</option>
                                <option value="pending" {{ request('verification_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="verified" {{ request('verification_status') === 'verified' ? 'selected' : '' }}>Verified</option>
                                <option value="rejected" {{ request('verification_status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="gender">Gender</label>
                            <select class="form-control" id="gender" name="gender">
                                <option value="">All Genders</option>
                                <option value="Male" {{ request('gender') === 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ request('gender') === 'Female' ? 'selected' : '' }}>Female</option>
                                <option value="Other" {{ request('gender') === 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary flex-fill">
                                    <i class="fas fa-search"></i> Search
                                </button>
                                <a href="{{ route('admin.drivers.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Drivers Table Card -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <i class="fas fa-list me-2"></i>Driver List 
                <span class="badge bg-primary ms-2">{{ $drivers->total() }} Total</span>
            </h5>
            <div class="btn-group btn-group-sm" role="group">
                <input type="checkbox" class="btn-check" id="select-all" autocomplete="off">
                <label class="btn btn-outline-primary" for="select-all">Select All</label>
                <button type="button" class="btn btn-outline-danger" id="bulk-actions-btn" disabled>
                    <i class="fas fa-cogs"></i> Bulk Actions
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            @if($drivers->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th width="5%">
                                    <input type="checkbox" id="select-all-table" class="form-check-input">
                                </th>
                                <th width="10%">Driver ID</th>
                                <th width="20%">Name</th>
                                <th width="15%">Contact</th>
                                <th width="10%">Status</th>
                                <th width="12%">Verification</th>
                                <th width="8%">Rating</th>
                                <th width="10%">Joined</th>
                                <th width="10%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($drivers as $driver)
                                <tr data-driver-id="{{ $driver->id }}">
                                    <td>
                                        <input type="checkbox" class="form-check-input driver-checkbox" value="{{ $driver->id }}">
                                    </td>
                                    <td>
                                        <strong class="text-primary">{{ $driver->driver_id }}</strong>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($driver->profile_picture)
                                                <img src="{{ Storage::url($driver->profile_picture) }}" 
                                                     alt="Profile" 
                                                     class="rounded-circle me-2"
                                                     width="32" height="32"
                                                     loading="lazy">
                                            @else
                                                <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center me-2" 
                                                     style="width: 32px; height: 32px;">
                                                    <i class="fas fa-user text-white"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <div class="fw-bold">{{ $driver->full_name }}</div>
                                                @if($driver->nickname)
                                                    <small class="text-muted">"{{ $driver->nickname }}"</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <i class="fas fa-envelope me-1"></i>
                                            <a href="mailto:{{ $driver->email }}" class="text-decoration-none">
                                                {{ Str::limit($driver->email, 20) }}
                                            </a>
                                        </div>
                                        <div>
                                            <i class="fas fa-phone me-1"></i>
                                            <a href="tel:{{ $driver->phone }}" class="text-decoration-none">
                                                {{ $driver->phone }}
                                            </a>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $driver->status === 'active' ? 'success' : ($driver->status === 'suspended' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($driver->status) }}
                                        </span>
                                        @if($driver->is_active)
                                            <br><small class="text-success"><i class="fas fa-circle"></i> Online</small>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $badgeClass = match($driver->verification_status) {
                                                'verified' => 'success',
                                                'pending' => 'warning',
                                                'rejected' => 'danger',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge badge-{{ $badgeClass }}">
                                            {{ ucfirst($driver->verification_status) }}
                                        </span>
                                        @if($driver->verified_at)
                                            <br><small class="text-muted">{{ $driver->verified_at->format('M d, Y') }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($driver->performance)
                                            <div class="text-warning">
                                                @for($i = 1; $i <= 5; $i++)
                                                    @if($i <= floor($driver->performance->average_rating))
                                                        <i class="fas fa-star"></i>
                                                    @elseif($i - 0.5 <= $driver->performance->average_rating)
                                                        <i class="fas fa-star-half-alt"></i>
                                                    @else
                                                        <i class="far fa-star"></i>
                                                    @endif
                                                @endfor
                                            </div>
                                            <small class="text-muted">
                                                {{ number_format($driver->performance->average_rating, 1) }} 
                                                ({{ $driver->performance->total_jobs_completed }} jobs)
                                            </small>
                                        @else
                                            <span class="text-muted">No ratings yet</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span title="{{ $driver->created_at->format('F d, Y H:i:s') }}">
                                            {{ $driver->created_at->diffForHumans() }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('admin.drivers.show', $driver) }}" 
                                               class="btn btn-outline-info" 
                                               title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.drivers.edit', $driver) }}" 
                                               class="btn btn-outline-primary" 
                                               title="Edit Driver">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @if($driver->verification_status === 'pending')
                                                <button type="button" 
                                                        class="btn btn-outline-success verify-driver-btn" 
                                                        data-driver-id="{{ $driver->id }}"
                                                        title="Verify Driver">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            @endif
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button type="button" class="btn btn-outline-secondary dropdown-toggle" 
                                                        data-bs-toggle="dropdown" aria-expanded="false" title="More Actions">
                                                    <i class="fas fa-ellipsis-h"></i>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a class="dropdown-item" href="{{ route('admin.drivers.documents', $driver) }}">
                                                        <i class="fas fa-file-alt me-2"></i>View Documents
                                                    </a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><button class="dropdown-item text-warning toggle-status-btn" data-driver-id="{{ $driver->id }}">
                                                        <i class="fas fa-toggle-{{ $driver->status === 'active' ? 'off' : 'on' }} me-2"></i>
                                                        {{ $driver->status === 'active' ? 'Deactivate' : 'Activate' }}
                                                    </button></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><button class="dropdown-item text-danger delete-driver-btn" data-driver-id="{{ $driver->id }}">
                                                        <i class="fas fa-trash me-2"></i>Delete
                                                    </button></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No drivers found</h5>
                    <p class="text-muted">Try adjusting your search criteria or add a new driver.</p>
                    <a href="{{ route('admin.drivers.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Add First Driver
                    </a>
                </div>
            @endif
        </div>
        
        @if($drivers->hasPages())
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        Showing {{ $drivers->firstItem() }} to {{ $drivers->lastItem() }} of {{ $drivers->total() }} drivers
                    </div>
                    <div>
                        {{ $drivers->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Bulk Actions Modal -->
    <div class="modal fade" id="bulkActionsModal" tabindex="-1" aria-labelledby="bulkActionsModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bulkActionsModalLabel">Bulk Actions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="bulk-actions-form" method="POST" action="{{ route('admin.drivers.bulk-action') }}">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="driver_ids" id="selected-driver-ids">
                        
                        <div class="mb-3">
                            <label for="bulk-action" class="form-label">Select Action</label>
                            <select class="form-select" id="bulk-action" name="action" required>
                                <option value="">Choose an action...</option>
                                <option value="activate">Activate Selected</option>
                                <option value="deactivate">Deactivate Selected</option>
                                <option value="suspend">Suspend Selected</option>
                                <option value="verify">Verify Selected</option>
                                <option value="reject">Reject Selected</option>
                                <option value="export">Export Selected</option>
                            </select>
                        </div>
                        
                        <div class="mb-3" id="admin-password-field" style="display: none;">
                            <label for="admin-password" class="form-label">Admin Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="admin-password" name="admin_password" placeholder="Enter your admin password">
                            <small class="form-text text-muted">Required for verification actions</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="bulk-notes" class="form-label">Notes (Optional)</label>
                            <textarea class="form-control" id="bulk-notes" name="notes" rows="3" placeholder="Add notes for this action..."></textarea>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <span id="selected-count">0</span> driver(s) selected for bulk action.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Execute Action</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .border-left-success {
        border-left: 0.25rem solid #1cc88a !important;
    }
    .border-left-info {
        border-left: 0.25rem solid #36b9cc !important;
    }
    .border-left-warning {
        border-left: 0.25rem solid #f6c23e !important;
    }
    .border-left-danger {
        border-left: 0.25rem solid #e74a3b !important;
    }
    
    .badge {
        font-size: 0.75em;
    }
    
    .table th {
        border-top: none;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.05em;
    }
    
    .driver-checkbox:checked + td {
        background-color: rgba(13, 202, 240, 0.1);
    }
    
    .btn-group-sm .btn {
        border-radius: 0.2rem;
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.02);
    }
    
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        border: 1px solid rgba(0, 0, 0, 0.125);
    }
    
    .text-truncate {
        max-width: 150px;
    }
    
    @media (max-width: 768px) {
        .table-responsive {
            font-size: 0.875rem;
        }
        
        .btn-group-sm .btn {
            padding: 0.25rem 0.375rem;
        }
        
        .card-body.p-0 {
            padding: 0.5rem !important;
        }
    }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Initialize tooltips
    $('[title]').tooltip();
    
    // Handle select all checkboxes
    $('#select-all, #select-all-table').change(function() {
        const isChecked = $(this).is(':checked');
        $('.driver-checkbox').prop('checked', isChecked);
        $('#select-all, #select-all-table').prop('checked', isChecked);
        updateBulkActionsButton();
    });
    
    // Handle individual checkboxes
    $('.driver-checkbox').change(function() {
        const totalCheckboxes = $('.driver-checkbox').length;
        const checkedCheckboxes = $('.driver-checkbox:checked').length;
        
        $('#select-all, #select-all-table').prop('checked', checkedCheckboxes === totalCheckboxes);
        updateBulkActionsButton();
    });
    
    // Update bulk actions button state
    function updateBulkActionsButton() {
        const checkedCount = $('.driver-checkbox:checked').length;
        $('#bulk-actions-btn').prop('disabled', checkedCount === 0);
        $('#selected-count').text(checkedCount);
    }
    
    // Handle bulk actions modal
    $('#bulk-actions-btn').click(function() {
        const selectedIds = $('.driver-checkbox:checked').map(function() {
            return $(this).val();
        }).get();
        
        $('#selected-driver-ids').val(JSON.stringify(selectedIds));
        $('#bulkActionsModal').modal('show');
    });
    
    // Show/hide admin password field based on action
    $('#bulk-action').change(function() {
        const action = $(this).val();
        const requiresPassword = ['verify', 'reject', 'ocr_verify'].includes(action);
        
        if (requiresPassword) {
            $('#admin-password-field').show();
            $('#admin-password').prop('required', true);
        } else {
            $('#admin-password-field').hide();
            $('#admin-password').prop('required', false);
        }
    });
    
    // Handle bulk actions form submission
    $('#bulk-actions-form').submit(function(e) {
        e.preventDefault();
        
        const selectedCount = $('.driver-checkbox:checked').length;
        const action = $('#bulk-action').val();
        
        if (selectedCount === 0) {
            alert('Please select at least one driver.');
            return false;
        }
        
        if (!confirm(`Are you sure you want to ${action} ${selectedCount} driver(s)?`)) {
            return false;
        }
        
        // Show loading state
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.text();
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Processing...');
        
        // Submit form via AJAX
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                $('#bulkActionsModal').modal('hide');
                if (response.success) {
                    showAlert('success', response.message);
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert('danger', response.message || 'Bulk action failed');
                }
            },
            error: function(xhr) {
                let errorMessage = 'Bulk action failed';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                showAlert('danger', errorMessage);
            },
            complete: function() {
                submitBtn.prop('disabled', false).text(originalText);
            }
        });
    });
    
    // Handle individual driver verification
    $('.verify-driver-btn').click(function() {
        const driverId = $(this).data('driver-id');
        const adminPassword = prompt('Enter your admin password to verify this driver:');
        
        if (!adminPassword) {
            return;
        }
        
        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        
        $.ajax({
            url: `/admin/drivers/${driverId}/verify`,
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                admin_password: adminPassword
            },
            success: function(response) {
                showAlert('success', 'Driver verified successfully!');
                setTimeout(() => location.reload(), 1500);
            },
            error: function(xhr) {
                let errorMessage = 'Verification failed';
                if (xhr.responseJSON && xhr.responseJSON.errors && xhr.responseJSON.errors.admin_password) {
                    errorMessage = xhr.responseJSON.errors.admin_password[0];
                }
                showAlert('danger', errorMessage);
            },
            complete: function() {
                $('.verify-driver-btn').prop('disabled', false).html('<i class="fas fa-check"></i>');
            }
        });
    });
    
    // Handle status toggle
    $('.toggle-status-btn').click(function() {
        const driverId = $(this).data('driver-id');
        
        if (!confirm('Are you sure you want to toggle this driver\'s status?')) {
            return;
        }
        
        $.ajax({
            url: `/admin/drivers/${driverId}/toggle-status`,
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                showAlert('success', 'Driver status updated successfully!');
                setTimeout(() => location.reload(), 1000);
            },
            error: function() {
                showAlert('danger', 'Failed to update driver status');
            }
        });
    });
    
    // Handle driver deletion
    $('.delete-driver-btn').click(function() {
        const driverId = $(this).data('driver-id');
        
        if (!confirm('Are you sure you want to delete this driver? This action cannot be undone.')) {
            return;
        }
        
        $.ajax({
            url: `/admin/drivers/${driverId}`,
            method: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                showAlert('success', 'Driver deleted successfully!');
                setTimeout(() => location.reload(), 1000);
            },
            error: function() {
                showAlert('danger', 'Failed to delete driver');
            }
        });
    });
    
    // Utility function to show alerts
    function showAlert(type, message) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        
        // Remove existing alerts
        $('.alert').remove();
        
        // Add new alert at the top of the content
        $('.content').prepend(alertHtml);
        
        // Auto-hide success alerts after 5 seconds
        if (type === 'success') {
            setTimeout(() => {
                $('.alert-success').fadeOut();
            }, 5000);
        }
    }
    
    // Auto-submit search form on filter change
    $('#status, #verification_status, #gender').change(function() {
        $('#driver-filters').submit();
    });
    
    // Handle search input with debounce
    let searchTimeout;
    $('#search').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            if ($(this).val().length >= 3 || $(this).val().length === 0) {
                $('#driver-filters').submit();
            }
        }, 500);
    });
});
</script>
@stop