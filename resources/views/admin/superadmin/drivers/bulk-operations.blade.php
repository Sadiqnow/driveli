@extends('layouts.admin_master')

@section('title', 'Superadmin - Bulk Driver Operations')

@section('content_header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Bulk Driver Operations</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.superadmin.dashboard') }}">Super Admin</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.superadmin.drivers.index') }}">Drivers</a></li>
                        <li class="breadcrumb-item active">Bulk Operations</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid">
        <!-- Operation Statistics -->
        <div class="row mb-4">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $stats['total_drivers'] ?? 0 }}</h3>
                        <p>Total Drivers</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $stats['operations_today'] ?? 0 }}</h3>
                        <p>Operations Today</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $stats['pending_operations'] ?? 0 }}</h3>
                        <p>Pending Operations</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ $stats['failed_operations'] ?? 0 }}</h3>
                        <p>Failed Operations</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bulk Operations Interface -->
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Bulk Operations</h3>
                    </div>
                    <div class="card-body">
                        <!-- Operation Type Selection -->
                        <div class="mb-4">
                            <h5>Select Operation Type</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="operation_type" id="verify_operation" value="verify">
                                        <label class="form-check-label" for="verify_operation">
                                            <strong>Verify Drivers</strong>
                                            <br><small class="text-muted">Approve selected drivers' verification status</small>
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="operation_type" id="activate_operation" value="activate">
                                        <label class="form-check-label" for="activate_operation">
                                            <strong>Activate Drivers</strong>
                                            <br><small class="text-muted">Set selected drivers to active status</small>
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="operation_type" id="suspend_operation" value="suspend">
                                        <label class="form-check-label" for="suspend_operation">
                                            <strong>Suspend Drivers</strong>
                                            <br><small class="text-muted">Temporarily suspend selected drivers</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="operation_type" id="reject_operation" value="reject">
                                        <label class="form-check-label" for="reject_operation">
                                            <strong>Reject Drivers</strong>
                                            <br><small class="text-muted">Reject selected drivers' applications</small>
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="operation_type" id="deactivate_operation" value="deactivate">
                                        <label class="form-check-label" for="deactivate_operation">
                                            <strong>Deactivate Drivers</strong>
                                            <br><small class="text-muted">Set selected drivers to inactive status</small>
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="operation_type" id="delete_operation" value="delete">
                                        <label class="form-check-label" for="delete_operation">
                                            <strong>Delete Drivers</strong>
                                            <br><small class="text-muted">Permanently delete selected drivers</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Options -->
                        <div id="additional_options" style="display: none;">
                            <div class="mb-3" id="rejection_reason_container" style="display: none;">
                                <label for="rejection_reason" class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3" placeholder="Please provide a detailed reason for rejection..."></textarea>
                            </div>

                            <div class="mb-3" id="verification_notes_container" style="display: none;">
                                <label for="verification_notes" class="form-label">Verification Notes</label>
                                <textarea class="form-control" id="verification_notes" name="verification_notes" rows="3" placeholder="Add any additional notes..."></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="admin_password" class="form-label">Admin Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="admin_password" name="admin_password" placeholder="Enter your admin password">
                                <div class="form-text">Required for security verification</div>
                            </div>
                        </div>

                        <!-- Driver Selection -->
                        <div class="mb-4">
                            <h5>Select Drivers</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <button type="button" class="btn btn-outline-primary btn-sm mb-2" onclick="selectAllDrivers()">
                                        <i class="fas fa-check-square"></i> Select All
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm mb-2" onclick="clearSelection()">
                                        <i class="fas fa-square"></i> Clear Selection
                                    </button>
                                </div>
                                <div class="col-md-6 text-right">
                                    <span id="selection_count" class="badge badge-secondary">0 drivers selected</span>
                                </div>
                            </div>
                        </div>

                        <!-- Execute Operation -->
                        <div class="text-center">
                            <button type="button" class="btn btn-success btn-lg" id="execute_operation" disabled onclick="executeBulkOperation()">
                                <i class="fas fa-play"></i> Execute Operation
                            </button>
                            <button type="button" class="btn btn-secondary btn-lg ml-2" onclick="previewOperation()">
                                <i class="fas fa-eye"></i> Preview Operation
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <!-- Operation History -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Recent Operations</h3>
                    </div>
                    <div class="card-body p-0">
                        <div id="operation_history">
                            <!-- History loaded via AJAX -->
                            <div class="text-center p-3">
                                <i class="fas fa-spinner fa-spin"></i> Loading...
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Operation Summary -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h3 class="card-title">Operation Summary</h3>
                    </div>
                    <div class="card-body">
                        <div id="operation_summary">
                            <p class="text-muted">Select an operation type to see summary</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Driver Selection Table -->
        <div class="card mt-4">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-6">
                        <h3 class="card-title">Select Drivers for Operation</h3>
                    </div>
                    <div class="col-md-6">
                        <div class="input-group">
                            <input type="text" class="form-control" id="driver_search" placeholder="Search drivers...">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" onclick="searchDrivers()">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card-header">
                <form id="filter_form" class="row g-2">
                    <div class="col-md-2">
                        <select class="form-control" name="status" onchange="applyFilters()">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="pending">Pending</option>
                            <option value="suspended">Suspended</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-control" name="verification_status" onchange="applyFilters()">
                            <option value="">All Verification</option>
                            <option value="pending">Pending</option>
                            <option value="verified">Verified</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select class="form-control" name="kyc_status" onchange="applyFilters()">
                            <option value="">All KYC</option>
                            <option value="pending">Pending</option>
                            <option value="completed">Completed</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" class="form-control" name="date_from" onchange="applyFilters()">
                    </div>
                    <div class="col-md-2">
                        <input type="date" class="form-control" name="date_to" onchange="applyFilters()">
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-secondary w-100" onclick="resetFilters()">
                            <i class="fas fa-undo"></i> Reset
                        </button>
                    </div>
                </form>
            </div>

            <!-- Drivers Table -->
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th width="30">
                                <input type="checkbox" id="select_all_drivers">
                            </th>
                            <th>Driver ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Verification</th>
                            <th>KYC Status</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody id="drivers_table_body">
                        <!-- Drivers loaded via AJAX -->
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-spinner fa-spin fa-2x"></i>
                                <p class="mt-2">Loading drivers...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="card-footer">
                <div class="row">
                    <div class="col-md-6">
                        <div id="pagination_info"></div>
                    </div>
                    <div class="col-md-6">
                        <div id="pagination_links" class="float-right"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Operation Preview Modal -->
    <div class="modal fade" id="previewModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Operation Preview</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="previewContent">
                    <!-- Preview content loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" onclick="confirmExecuteOperation()">Execute Operation</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Operation Progress Modal -->
    <div class="modal fade" id="progressModal" tabindex="-1" data-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Executing Operation</h5>
                </div>
                <div class="modal-body">
                    <div class="progress mb-3">
                        <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                    </div>
                    <div id="progress_text">Initializing operation...</div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
let selectedDrivers = new Set();
let currentOperation = null;
let currentPage = 1;

$(document).ready(function() {
    loadDrivers();
    loadOperationHistory();

    // Operation type change handler
    $('input[name="operation_type"]').on('change', function() {
        currentOperation = $(this).val();
        updateOperationUI();
        updateOperationSummary();
    });

    // Select all checkbox
    $('#select_all_drivers').on('change', function() {
        const checked = $(this).prop('checked');
        $('.driver-checkbox').prop('checked', checked);
        updateSelection();
    });

    // Search functionality
    $('#driver_search').on('keyup', function(e) {
        if (e.key === 'Enter') {
            searchDrivers();
        }
    });
});

function updateOperationUI() {
    $('#additional_options').show();

    // Show/hide specific fields based on operation
    const showRejectionReason = ['reject'].includes(currentOperation);
    const showVerificationNotes = ['verify', 'reject'].includes(currentOperation);

    $('#rejection_reason_container').toggle(showRejectionReason);
    $('#verification_notes_container').toggle(showVerificationNotes);

    // Update execute button
    $('#execute_operation').prop('disabled', selectedDrivers.size === 0);
}

function updateOperationSummary() {
    let summary = '';

    switch(currentOperation) {
        case 'verify':
            summary = `
                <h6>Verify Drivers</h6>
                <p>Selected drivers will be marked as verified and activated.</p>
                <ul class="list-unstyled">
                    <li><i class="fas fa-check text-success"></i> Status set to 'verified'</li>
                    <li><i class="fas fa-check text-success"></i> Driver activated</li>
                    <li><i class="fas fa-check text-success"></i> Verification notification sent</li>
                </ul>
            `;
            break;
        case 'reject':
            summary = `
                <h6>Reject Drivers</h6>
                <p>Selected drivers will be rejected and deactivated.</p>
                <ul class="list-unstyled">
                    <li><i class="fas fa-times text-danger"></i> Status set to 'rejected'</li>
                    <li><i class="fas fa-times text-danger"></i> Driver deactivated</li>
                    <li><i class="fas fa-times text-danger"></i> Rejection notification sent</li>
                </ul>
            `;
            break;
        case 'activate':
            summary = `
                <h6>Activate Drivers</h6>
                <p>Selected drivers will be set to active status.</p>
                <ul class="list-unstyled">
                    <li><i class="fas fa-play text-success"></i> Status set to 'active'</li>
                    <li><i class="fas fa-play text-success"></i> Driver can receive jobs</li>
                </ul>
            `;
            break;
        case 'deactivate':
            summary = `
                <h6>Deactivate Drivers</h6>
                <p>Selected drivers will be set to inactive status.</p>
                <ul class="list-unstyled">
                    <li><i class="fas fa-pause text-warning"></i> Status set to 'inactive'</li>
                    <li><i class="fas fa-pause text-warning"></i> Driver cannot receive jobs</li>
                </ul>
            `;
            break;
        case 'suspend':
            summary = `
                <h6>Suspend Drivers</h6>
                <p>Selected drivers will be temporarily suspended.</p>
                <ul class="list-unstyled">
                    <li><i class="fas fa-ban text-warning"></i> Status set to 'suspended'</li>
                    <li><i class="fas fa-ban text-warning"></i> Driver blocked from platform</li>
                </ul>
            `;
            break;
        case 'delete':
            summary = `
                <h6>Delete Drivers</h6>
                <p>Selected drivers will be permanently deleted.</p>
                <ul class="list-unstyled">
                    <li><i class="fas fa-trash text-danger"></i> All driver data removed</li>
                    <li><i class="fas fa-trash text-danger"></i> Cannot be undone</li>
                </ul>
            `;
            break;
    }

    $('#operation_summary').html(summary);
}

function updateSelection() {
    selectedDrivers.clear();
    $('.driver-checkbox:checked').each(function() {
        selectedDrivers.add($(this).val());
    });

    $('#selection_count').text(`${selectedDrivers.size} drivers selected`);
    $('#execute_operation').prop('disabled', selectedDrivers.size === 0 || !currentOperation);
}

function selectAllDrivers() {
    $('.driver-checkbox').prop('checked', true);
    updateSelection();
}

function clearSelection() {
    $('.driver-checkbox').prop('checked', false);
    updateSelection();
}

function loadDrivers(page = 1) {
    currentPage = page;
    const filters = $('#filter_form').serialize();
    const search = $('#driver_search').val();

    $.get('{{ route("admin.superadmin.drivers.bulk-list") }}', {
        page: page,
        search: search,
        ...Object.fromEntries(new URLSearchParams(filters))
    })
    .done(function(response) {
        renderDriversTable(response.drivers);
        renderPagination(response);
    })
    .fail(function() {
        $('#drivers_table_body').html(`
            <tr>
                <td colspan="8" class="text-center text-danger py-4">
                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                    <p>Failed to load drivers</p>
                </td>
            </tr>
        `);
    });
}

function renderDriversTable(drivers) {
    let html = '';

    if (drivers.length === 0) {
        html = `
            <tr>
                <td colspan="8" class="text-center py-4">
                    <i class="fas fa-users fa-2x text-muted mb-2"></i>
                    <p class="text-muted">No drivers found</p>
                </td>
            </tr>
        `;
    } else {
        drivers.forEach(driver => {
            const isSelected = selectedDrivers.has(driver.id.toString());
            html += `
                <tr>
                    <td>
                        <input type="checkbox" class="driver-checkbox" value="${driver.id}"
                               ${isSelected ? 'checked' : ''} onchange="updateSelection()">
                    </td>
                    <td>${driver.driver_id}</td>
                    <td>${driver.first_name} ${driver.surname}</td>
                    <td>${driver.email}</td>
                    <td>
                        <span class="badge badge-${getStatusBadgeClass(driver.status)}">
                            ${driver.status}
                        </span>
                    </td>
                    <td>
                        <span class="badge badge-${getVerificationBadgeClass(driver.verification_status)}">
                            ${driver.verification_status}
                        </span>
                    </td>
                    <td>
                        <span class="badge badge-${getKycBadgeClass(driver.kyc_status)}">
                            ${driver.kyc_status}
                        </span>
                    </td>
                    <td>${new Date(driver.created_at).toLocaleDateString()}</td>
                </tr>
            `;
        });
    }

    $('#drivers_table_body').html(html);
}

function getStatusBadgeClass(status) {
    const classes = {
        'active': 'success',
        'inactive': 'secondary',
        'pending': 'warning',
        'suspended': 'danger'
    };
    return classes[status] || 'secondary';
}

function getVerificationBadgeClass(status) {
    const classes = {
        'verified': 'success',
        'pending': 'warning',
        'rejected': 'danger'
    };
    return classes[status] || 'secondary';
}

function getKycBadgeClass(status) {
    const classes = {
        'completed': 'success',
        'pending': 'warning',
        'rejected': 'danger',
        'in_progress': 'info'
    };
    return classes[status] || 'secondary';
}

function renderPagination(response) {
    $('#pagination_info').html(`Showing ${response.from || 0} to ${response.to || 0} of ${response.total || 0} drivers`);

    let paginationHtml = '';
    if (response.last_page > 1) {
        paginationHtml = '<ul class="pagination">';

        // Previous button
        if (response.current_page > 1) {
            paginationHtml += `<li class="page-item"><a class="page-link" href="#" onclick="loadDrivers(${response.current_page - 1})">Previous</a></li>`;
        }

        // Page numbers
        for (let i = Math.max(1, response.current_page - 2); i <= Math.min(response.last_page, response.current_page + 2); i++) {
            paginationHtml += `<li class="page-item ${i === response.current_page ? 'active' : ''}">
                <a class="page-link" href="#" onclick="loadDrivers(${i})">${i}</a>
            </li>`;
        }

        // Next button
        if (response.current_page < response.last_page) {
            paginationHtml += `<li class="page-item"><a class="page-link" href="#" onclick="loadDrivers(${response.current_page + 1})">Next</a></li>`;
        }

        paginationHtml += '</ul>';
    }

    $('#pagination_links').html(paginationHtml);
}

function searchDrivers() {
    loadDrivers(1);
}

function applyFilters() {
    loadDrivers(1);
}

function resetFilters() {
    $('#filter_form')[0].reset();
    $('#driver_search').val('');
    loadDrivers(1);
}

function previewOperation() {
    if (!currentOperation || selectedDrivers.size === 0) {
        alert('Please select an operation type and drivers');
        return;
    }

    const previewData = {
        operation: currentOperation,
        driver_ids: Array.from(selectedDrivers),
        rejection_reason: $('#rejection_reason').val(),
        verification_notes: $('#verification_notes').val()
    };

    $.post('{{ route("admin.superadmin.drivers.bulk-preview") }}', {
        _token: '{{ csrf_token() }}',
        ...previewData
    })
    .done(function(response) {
        $('#previewContent').html(response.html);
        $('#previewModal').modal('show');
    })
    .fail(function() {
        alert('Failed to load operation preview');
    });
}

function executeBulkOperation() {
    if (!currentOperation || selectedDrivers.size === 0) {
        alert('Please select an operation type and drivers');
        return;
    }

    if (!confirm(`Are you sure you want to ${currentOperation} ${selectedDrivers.size} driver(s)? This action cannot be undone.`)) {
        return;
    }

    // Show progress modal
    $('#progressModal').modal('show');
    $('#progress_text').text('Initializing operation...');

    const operationData = {
        action: currentOperation,
        driver_ids: Array.from(selectedDrivers),
        admin_password: $('#admin_password').val(),
        notes: $('#verification_notes').val(),
        rejection_reason: $('#rejection_reason').val()
    };

    $.post('{{ route("admin.superadmin.drivers.bulk-verify") }}', {
        _token: '{{ csrf_token() }}',
        ...operationData
    })
    .done(function(response) {
        $('#progressModal').modal('hide');
        if (response.success) {
            alert(`Operation completed successfully! ${response.processed || 0} drivers processed.`);
            location.reload();
        } else {
            alert('Operation failed: ' + response.message);
        }
    })
    .fail(function(xhr) {
        $('#progressModal').modal('hide');
        alert('Error: ' + xhr.responseJSON?.message || 'Operation failed');
    });
}

function confirmExecuteOperation() {
    $('#previewModal').modal('hide');
    executeBulkOperation();
}

function loadOperationHistory() {
    $.get('{{ route("admin.superadmin.drivers.bulk-history") }}')
        .done(function(response) {
            let html = '';
            if (response.history && response.history.length > 0) {
                response.history.forEach(operation => {
                    html += `
                        <div class="p-2 border-bottom">
                            <div class="d-flex justify-content-between">
                                <small class="text-muted">${operation.operation_type}</small>
                                <small class="text-muted">${operation.created_at}</small>
                            </div>
                            <div>${operation.driver_count} drivers affected</div>
                        </div>
                    `;
                });
            } else {
                html = '<div class="p-3 text-center text-muted">No recent operations</div>';
            }
            $('#operation_history').html(html);
        })
        .fail(function() {
            $('#operation_history').html('<div class="p-3 text-center text-danger">Failed to load history</div>');
        });
}
</script>
@endpush
