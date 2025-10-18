@extends('layouts.admin_master')

@section('title', 'Superadmin - KYC Review Dashboard')

@section('content_header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">KYC Review Dashboard</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.superadmin.dashboard') }}">Super Admin</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.superadmin.drivers.index') }}">Drivers</a></li>
                        <li class="breadcrumb-item active">KYC Review</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid">
        <!-- KYC Statistics Cards -->
        <div class="row mb-4">
            <div class="col-lg-2 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $stats['total_submissions'] ?? 0 }}</h3>
                        <p>Total Submissions</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <a href="{{ route('admin.superadmin.drivers.kyc-review') }}" class="small-box-footer">
                        View All <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-2 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $stats['pending_review'] ?? 0 }}</h3>
                        <p>Pending Review</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <a href="{{ route('admin.superadmin.drivers.kyc-review', ['kyc_status' => 'completed']) }}" class="small-box-footer">
                        Review Now <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-2 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $stats['approved_today'] ?? 0 }}</h3>
                        <p>Approved Today</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ $stats['rejected_today'] ?? 0 }}</h3>
                        <p>Rejected Today</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-6">
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h3>{{ number_format($stats['approval_rate'] ?? 0, 1) }}%</h3>
                        <p>Approval Rate</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-percentage"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-6">
                <div class="small-box bg-secondary">
                    <div class="inner">
                        <h3>{{ $stats['avg_review_time'] ?? 0 }}h</h3>
                        <p>Avg Review Time</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- KYC Performance Metrics -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-line mr-1"></i>
                            KYC Review Performance
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6 text-center">
                                <div class="text-success">
                                    <span class="h4">{{ number_format((($stats['approved'] ?? 0) / max(($stats['total_submissions'] ?? 1), 1)) * 100, 1) }}%</span>
                                </div>
                                <div class="text-muted">Overall Approval</div>
                            </div>
                            <div class="col-6 text-center">
                                <div class="text-info">
                                    <span class="h4">{{ $stats['reviews_today'] ?? 0 }}</span>
                                </div>
                                <div class="text-muted">Reviews Today</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-tasks mr-1"></i>
                            Review Queue Status
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6 text-center">
                                <div class="text-warning">
                                    <span class="h4">{{ $stats['pending_review'] ?? 0 }}</span>
                                </div>
                                <div class="text-muted">In Queue</div>
                            </div>
                            <div class="col-6 text-center">
                                <div class="text-primary">
                                    <span class="h4">{{ $stats['high_priority'] ?? 0 }}</span>
                                </div>
                                <div class="text-muted">High Priority</div>
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
                        <h3 class="card-title">KYC Review Queue</h3>
                    </div>
                    <div class="col-md-6 text-right">
                        <button type="button" class="btn btn-success" onclick="bulkKycReview()">
                            <i class="fas fa-tasks"></i> Bulk Review
                        </button>
                        <button type="button" class="btn btn-info" onclick="exportKycReport()">
                            <i class="fas fa-download"></i> Export Report
                        </button>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card-header">
                <form method="GET" action="{{ route('admin.superadmin.drivers.kyc-review') }}" class="row g-2">
                    <div class="col-md-3">
                        <label for="kyc-search" class="visually-hidden">Search drivers</label>
                        <input type="text"
                               id="kyc-search"
                               name="search"
                               class="form-control"
                               placeholder="Search by name, ID..."
                               value="{{ request('search') }}"
                               aria-describedby="kyc-search-help">
                        <div id="kyc-search-help" class="visually-hidden">Enter text to search for drivers by name or ID</div>
                    </div>
                    <div class="col-md-2">
                        <label for="kyc-status-filter" class="visually-hidden">Filter by KYC status</label>
                        <select id="kyc-status-filter" name="kyc_status" class="form-control" aria-label="Filter by KYC status">
                            <option value="">All Status</option>
                            <option value="completed" {{ request('kyc_status')=='completed' ? 'selected' : '' }}>Completed</option>
                            <option value="pending" {{ request('kyc_status')=='pending' ? 'selected' : '' }}>Pending</option>
                            <option value="rejected" {{ request('kyc_status')=='rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="priority-filter" class="visually-hidden">Filter by priority</label>
                        <select id="priority-filter" name="priority" class="form-control" aria-label="Filter by priority">
                            <option value="">All Priority</option>
                            <option value="high" {{ request('priority')=='high' ? 'selected' : '' }}>High Priority</option>
                            <option value="normal" {{ request('priority')=='normal' ? 'selected' : '' }}>Normal</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="date-from" class="visually-hidden">From date</label>
                        <input type="date" id="date-from" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label for="date-to" class="visually-hidden">To date</label>
                        <input type="date" id="date-to" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-success w-100" aria-label="Search KYC reviews">
                            <i class="fas fa-search" aria-hidden="true"></i>
                            <span class="visually-hidden">Search</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- KYC Review Table -->
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th width="30">
                                <input type="checkbox" id="select-all-kyc">
                            </th>
                            <th>Driver ID</th>
                            <th>Name</th>
                            <th>KYC Status</th>
                            <th>Submitted</th>
                            <th>Priority</th>
                            <th>Documents</th>
                            <th>Review Time</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($drivers ?? [] as $driver)
                            <tr class="{{ $driver->priority === 'high' ? 'table-warning' : '' }}">
                                <td>
                                    <input type="checkbox" class="kyc-checkbox" value="{{ $driver->id }}">
                                </td>
                                <td>
                                    <a href="{{ route('admin.superadmin.drivers.show', $driver) }}">
                                        {{ $driver->driver_id }}
                                    </a>
                                </td>
                                <td>{{ $driver->full_name }}</td>
                                <td>
                                    @if($driver->kyc_status == 'completed')
                                        <span class="badge badge-warning">
                                            <i class="fas fa-clock"></i> Pending Review
                                        </span>
                                    @elseif($driver->kyc_status == 'approved')
                                        <span class="badge badge-success">
                                            <i class="fas fa-check"></i> Approved
                                        </span>
                                    @elseif($driver->kyc_status == 'rejected')
                                        <span class="badge badge-danger">
                                            <i class="fas fa-times"></i> Rejected
                                        </span>
                                    @else
                                        <span class="badge badge-secondary">
                                            {{ ucfirst($driver->kyc_status) }}
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $driver->kyc_submitted_at ? $driver->kyc_submitted_at->format('M d, Y') : 'N/A' }}</td>
                                <td>
                                    @if($driver->priority === 'high')
                                        <span class="badge badge-danger">High</span>
                                    @else
                                        <span class="badge badge-secondary">Normal</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-center">
                                        @php
                                            $docCount = 0;
                                            if($driver->profile_picture) $docCount++;
                                            if($driver->license_front_image) $docCount++;
                                            if($driver->license_back_image) $docCount++;
                                            if($driver->nin_document) $docCount++;
                                        @endphp
                                        <span class="badge badge-info">{{ $docCount }}/4</span>
                                    </div>
                                </td>
                                <td>
                                    @if($driver->kyc_submitted_at)
                                        <span class="text-muted">{{ $driver->kyc_submitted_at->diffForHumans() }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-info btn-sm" onclick="viewKycDetails({{ $driver->id }})">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        @if($driver->kyc_status == 'completed')
                                            <button type="button" class="btn btn-success btn-sm" onclick="approveKyc({{ $driver->id }})">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm" onclick="rejectKyc({{ $driver->id }})">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                        <button type="button" class="btn btn-warning btn-sm" onclick="requestInfo({{ $driver->id }})">
                                            <i class="fas fa-question"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <i class="fas fa-file-alt fa-2x text-muted mb-2"></i>
                                    <p class="text-muted">No KYC reviews found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if(isset($drivers) && $drivers->hasPages())
                <div class="card-footer">
                    {{ $drivers->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- KYC Details Modal -->
    <div class="modal fade" id="kycDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">KYC Review Details</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="kycDetailsContent">
                    <!-- Content loaded via AJAX -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <div id="kycActionButtons">
                        <!-- Action buttons loaded with content -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Approve KYC Modal -->
    <div class="modal fade" id="approveKycModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Approve KYC Application</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="approveKycForm">
                    @csrf
                    <div class="modal-body">
                        <p>Are you sure you want to approve <strong id="approveKycDriverName"></strong>'s KYC application?</p>
                        <div class="form-group">
                            <label for="approvalNotes">Approval Notes (Optional)</label>
                            <textarea class="form-control" id="approvalNotes" name="approval_notes" rows="3" placeholder="Add any notes about this approval..."></textarea>
                        </div>
                        <div class="form-group">
                            <label for="adminPasswordApprove">Admin Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="adminPasswordApprove" name="admin_password" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Approve KYC</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reject KYC Modal -->
    <div class="modal fade" id="rejectKycModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reject KYC Application</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="rejectKycForm">
                    @csrf
                    <div class="modal-body">
                        <p>Are you sure you want to reject <strong id="rejectKycDriverName"></strong>'s KYC application?</p>
                        <div class="form-group">
                            <label for="rejectionReason">Rejection Reason <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="rejectionReason" name="rejection_reason" rows="3" required placeholder="Please provide a detailed reason for rejection..."></textarea>
                        </div>
                        <div class="form-group">
                            <label for="allowRetry">Allow Retry</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="allowRetry" name="allow_retry" checked>
                                <label class="form-check-label" for="allowRetry">
                                    Allow driver to retry KYC process
                                </label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="adminPasswordReject">Admin Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="adminPasswordReject" name="admin_password" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Reject KYC</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Request Info Modal -->
    <div class="modal fade" id="requestInfoModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Request Additional Information</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="requestInfoForm">
                    @csrf
                    <div class="modal-body">
                        <p>Request additional information from <strong id="requestInfoDriverName"></strong></p>
                        <div class="form-group">
                            <label for="infoRequest">Information Request <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="infoRequest" name="info_request" rows="4" required placeholder="Please specify what additional information is needed..."></textarea>
                        </div>
                        <div class="form-group">
                            <label for="adminPasswordInfo">Admin Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="adminPasswordInfo" name="admin_password" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Send Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Select all functionality
    $('#select-all-kyc').on('change', function() {
        $('.kyc-checkbox').prop('checked', $(this).prop('checked'));
    });

    $('.kyc-checkbox').on('change', function() {
        const totalBoxes = $('.kyc-checkbox').length;
        const checkedBoxes = $('.kyc-checkbox:checked').length;
        $('#select-all-kyc').prop('checked', totalBoxes > 0 && checkedBoxes === totalBoxes);
    });
});

// KYC Functions
function viewKycDetails(driverId) {
    $.get(`{{ url('admin/superadmin/drivers') }}/${driverId}/kyc-details`)
        .done(function(response) {
            $('#kycDetailsContent').html(response.html);
            $('#kycActionButtons').html(response.action_buttons || '');
            $('#kycDetailsModal').modal('show');
        })
        .fail(function() {
            alert('Failed to load KYC details');
        });
}

function approveKyc(driverId) {
    // Get driver name via AJAX or from data attribute
    $('#approveKycDriverName').text('this driver');
    $('#approveKycForm').attr('action', `{{ url('admin/superadmin/drivers') }}/${driverId}/approve-kyc`);
    $('#approveKycModal').modal('show');
}

function rejectKyc(driverId) {
    $('#rejectKycDriverName').text('this driver');
    $('#rejectKycForm').attr('action', `{{ url('admin/superadmin/drivers') }}/${driverId}/reject-kyc`);
    $('#rejectKycModal').modal('show');
}

function requestInfo(driverId) {
    $('#requestInfoDriverName').text('this driver');
    $('#requestInfoForm').attr('action', `{{ url('admin/superadmin/drivers') }}/${driverId}/request-kyc-info`);
    $('#requestInfoModal').modal('show');
}

// Form submissions
$('#approveKycForm').on('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    $.post($(this).attr('action'), {
        _token: '{{ csrf_token() }}',
        approval_notes: formData.get('approval_notes'),
        admin_password: formData.get('admin_password')
    })
    .done(function(response) {
        $('#approveKycModal').modal('hide');
        location.reload();
    })
    .fail(function(xhr) {
        alert('Error: ' + xhr.responseJSON?.message || 'Failed to approve KYC');
    });
});

$('#rejectKycForm').on('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    $.post($(this).attr('action'), {
        _token: '{{ csrf_token() }}',
        rejection_reason: formData.get('rejection_reason'),
        allow_retry: formData.get('allow_retry') ? 1 : 0,
        admin_password: formData.get('admin_password')
    })
    .done(function(response) {
        $('#rejectKycModal').modal('hide');
        location.reload();
    })
    .fail(function(xhr) {
        alert('Error: ' + xhr.responseJSON?.message || 'Failed to reject KYC');
    });
});

$('#requestInfoForm').on('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    $.post($(this).attr('action'), {
        _token: '{{ csrf_token() }}',
        info_request: formData.get('info_request'),
        admin_password: formData.get('admin_password')
    })
    .done(function(response) {
        $('#requestInfoModal').modal('hide');
        location.reload();
    })
    .fail(function(xhr) {
        alert('Error: ' + xhr.responseJSON?.message || 'Failed to send info request');
    });
});

function bulkKycReview() {
    const selectedIds = $('.kyc-checkbox:checked').map(function() {
        return $(this).val();
    }).get();

    if (selectedIds.length === 0) {
        alert('Please select at least one driver');
        return;
    }

    // Redirect to bulk KYC review page
    window.location.href = '{{ route("admin.superadmin.drivers.bulk-kyc-review") }}?driver_ids=' + selectedIds.join(',');
}

function exportKycReport() {
    const filters = new URLSearchParams(window.location.search);
    window.location.href = '{{ route("admin.superadmin.drivers.kyc-export") }}?' + filters.toString();
}
</script>
@endpush
