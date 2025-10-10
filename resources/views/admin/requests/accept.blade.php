@extends('layouts.admin_cdn')

@section('title', 'Accept Company Requests')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Accept Company Requests</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ url('admin/requests') }}">Requests</a></li>
                    <li class="breadcrumb-item active">Accept Requests</li>
                </ol>
            </div>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Request Statistics -->
            <div class="row mb-4">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{ $stats['pending'] ?? 0 }}</h3>
                            <p>Pending Requests</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{ $stats['accepted'] ?? 0 }}</h3>
                            <p>Accepted Today</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ $stats['processing'] ?? 0 }}</h3>
                            <p>In Processing</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-cogs"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3>{{ $stats['urgent'] ?? 0 }}</h3>
                            <p>Urgent Requests</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card card-outline card-primary mb-4">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-tachometer-alt"></i> Quick Actions
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <button class="btn btn-success btn-block" id="bulkAcceptBtn">
                                <i class="fas fa-check-double"></i> Bulk Accept Selected
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-warning btn-block" id="assignDriversBtn">
                                <i class="fas fa-user-plus"></i> Auto-Assign Drivers
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-info btn-block" id="prioritizeUrgentBtn">
                                <i class="fas fa-sort-amount-up"></i> Prioritize Urgent
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-secondary btn-block" id="exportRequestsBtn">
                                <i class="fas fa-download"></i> Export Requests
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Requests Table -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Pending Company Requests</h3>
                    <div class="card-tools">
                        <div class="input-group input-group-sm" style="width: 250px;">
                            <select class="form-control form-control-sm" id="statusFilter">
                                <option value="">All Statuses</option>
                                <option value="Pending">Pending</option>
                                <option value="Under Review">Under Review</option>
                                <option value="Urgent">Urgent</option>
                            </select>
                            <div class="input-group-append">
                                <input type="text" id="searchInput" class="form-control" placeholder="Search...">
                                <button type="button" class="btn btn-default">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap" id="requestsTable">
                        <thead>
                            <tr>
                                <th>
                                    <div class="custom-control custom-checkbox">
                                        <input class="custom-control-input" type="checkbox" id="selectAll">
                                        <label for="selectAll" class="custom-control-label"></label>
                                    </div>
                                </th>
                                <th>Request ID</th>
                                <th>Company</th>
                                <th>Job Type</th>
                                <th>Location</th>
                                <th>Salary Range</th>
                                <th>Priority</th>
                                <th>Submitted</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($requests as $request)
                            <tr class="{{ $request->priority === 'Urgent' ? 'table-warning' : '' }}">
                                <td>
                                    <div class="custom-control custom-checkbox">
                                        <input class="custom-control-input request-checkbox" 
                                               type="checkbox" 
                                               id="request{{ $request->id }}" 
                                               value="{{ $request->id }}">
                                        <label for="request{{ $request->id }}" class="custom-control-label"></label>
                                    </div>
                                </td>
                                <td>
                                    <strong>REQ-{{ str_pad($request->id, 4, '0', STR_PAD_LEFT) }}</strong>
                                    @if($request->priority === 'Urgent')
                                        <span class="badge badge-danger badge-sm ml-1">URGENT</span>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $request->company->name }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $request->company->contact_person_name }}</small>
                                </td>
                                <td>{{ $request->location ?? 'N/A' }}</td>
                                <td>{{ $request->location }}</td>
                                <td>
                                    @if($request->salary_range)
                                        <span class="badge badge-success">{{ $request->salary_range }}</span>
                                    @else
                                        <span class="text-muted">Not specified</span>
                                    @endif
                                </td>
                                <td>
                                    @if($request->priority === 'Urgent')
                                        <span class="badge badge-danger">Urgent</span>
                                    @elseif($request->priority === 'High')
                                        <span class="badge badge-warning">High</span>
                                    @else
                                        <span class="badge badge-info">Normal</span>
                                    @endif
                                </td>
                                <td>{{ $request->created_at->diffForHumans() }}</td>
                                <td>
                                    @if($request->status === 'Pending')
                                        <span class="badge badge-warning">
                                            <i class="fas fa-clock"></i> Pending
                                        </span>
                                    @elseif($request->status === 'Under Review')
                                        <span class="badge badge-info">
                                            <i class="fas fa-eye"></i> Under Review
                                        </span>
                                    @elseif($request->status === 'Accepted')
                                        <span class="badge badge-success">
                                            <i class="fas fa-check"></i> Accepted
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('admin.requests.show', $request->id) }}" 
                                           class="btn btn-info btn-sm" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        @if($request->status === 'Pending')
                                        <button type="button" 
                                                class="btn btn-success btn-sm accept-request" 
                                                data-request-id="{{ $request->id }}" 
                                                title="Accept Request">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button type="button" 
                                                class="btn btn-warning btn-sm review-request" 
                                                data-request-id="{{ $request->id }}" 
                                                title="Mark Under Review">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        @endif
                                        
                                        <button type="button" 
                                                class="btn btn-primary btn-sm assign-driver" 
                                                data-request-id="{{ $request->id }}" 
                                                title="Assign Driver">
                                            <i class="fas fa-user-plus"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer clearfix">
                    {{ $requests->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Accept Request Modal -->
    <div class="modal fade" id="acceptRequestModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Accept Company Request</h4>
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="acceptRequestForm">
                    <div class="modal-body">
                        <input type="hidden" id="requestId" name="request_id">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="acceptanceNotes">Acceptance Notes:</label>
                                    <textarea class="form-control" id="acceptanceNotes" name="notes" 
                                            rows="3" placeholder="Add any notes about the acceptance..."></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="estimatedCompletion">Estimated Completion:</label>
                                    <input type="date" class="form-control" id="estimatedCompletion" 
                                           name="estimated_completion" min="{{ date('Y-m-d') }}">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="assignedTo">Assign To:</label>
                                    <select class="form-control" id="assignedTo" name="assigned_to">
                                        <option value="">Select Administrator</option>
                                        @foreach($administrators as $admin)
                                        <option value="{{ $admin->id }}">{{ $admin->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="priority">Priority Level:</label>
                                    <select class="form-control" id="priority" name="priority">
                                        <option value="Normal">Normal</option>
                                        <option value="High">High</option>
                                        <option value="Urgent">Urgent</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="autoAssignDrivers" name="auto_assign_drivers">
                            <label class="custom-control-label" for="autoAssignDrivers">
                                Automatically assign suitable drivers based on location and requirements
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check"></i> Accept Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Driver Assignment Modal -->
    <div class="modal fade" id="driverAssignmentModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Assign Drivers to Request</h4>
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="assignRequestId" name="request_id">
                    
                    <!-- Available Drivers Table will be loaded here -->
                    <div id="availableDriversContainer">
                        <div class="text-center py-4">
                            <i class="fas fa-spinner fa-spin fa-2x"></i>
                            <p>Loading available drivers...</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="assignSelectedDrivers">
                        <i class="fas fa-user-plus"></i> Assign Selected Drivers
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#requestsTable').DataTable({
        "responsive": true,
        "lengthChange": false,
        "autoWidth": false,
        "order": [[ 7, "desc" ]],
        "columnDefs": [
            { "orderable": false, "targets": [0, 9] }
        ]
    });

    // Accept single request
    $('.accept-request').click(function() {
        const requestId = $(this).data('request-id');
        $('#requestId').val(requestId);
        $('#acceptRequestModal').modal('show');
    });

    // Mark request under review
    $('.review-request').click(function() {
        const requestId = $(this).data('request-id');
        updateRequestStatus(requestId, 'Under Review');
    });

    // Assign driver to request
    $('.assign-driver').click(function() {
        const requestId = $(this).data('request-id');
        loadAvailableDrivers(requestId);
    });

    // Handle accept request form submission
    $('#acceptRequestForm').submit(function(e) {
        e.preventDefault();
        
        const formData = {
            request_id: $('#requestId').val(),
            notes: $('#acceptanceNotes').val(),
            estimated_completion: $('#estimatedCompletion').val(),
            assigned_to: $('#assignedTo').val(),
            priority: $('#priority').val(),
            auto_assign_drivers: $('#autoAssignDrivers').is(':checked'),
            _token: '{{ csrf_token() }}'
        };

        $.ajax({
            url: '{{ route("admin.requests.accept") }}',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    $('#acceptRequestModal').modal('hide');
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('An error occurred while accepting the request.');
            }
        });
    });

    // Bulk operations
    $('#bulkAcceptBtn').click(function() {
        const selectedIds = $('.request-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        if (selectedIds.length === 0) {
            alert('Please select at least one request to accept.');
            return;
        }

        if (confirm('Are you sure you want to accept ' + selectedIds.length + ' requests?')) {
            bulkAcceptRequests(selectedIds);
        }
    });

    $('#selectAll').change(function() {
        $('.request-checkbox').prop('checked', $(this).is(':checked'));
    });

    function updateRequestStatus(requestId, status) {
        $.ajax({
            url: '{{ route("admin.requests.update-status") }}',
            method: 'POST',
            data: {
                request_id: requestId,
                status: status,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            }
        });
    }

    function loadAvailableDrivers(requestId) {
        $('#assignRequestId').val(requestId);
        $('#driverAssignmentModal').modal('show');
        
        $.ajax({
            url: '{{ route("admin.requests.available-drivers") }}',
            method: 'GET',
            data: { request_id: requestId },
            success: function(response) {
                $('#availableDriversContainer').html(response.html);
            },
            error: function() {
                $('#availableDriversContainer').html('<div class="alert alert-danger">Error loading drivers.</div>');
            }
        });
    }

    function bulkAcceptRequests(requestIds) {
        $.ajax({
            url: '{{ route("admin.requests.bulk-accept") }}',
            method: 'POST',
            data: {
                request_ids: requestIds,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            }
        });
    }
});
</script>
@stop