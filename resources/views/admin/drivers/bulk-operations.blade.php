@extends('layouts.admin_cdn')

@section('title', 'Bulk Operations - Drivers')

@section('head')
<style>
.operation-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    transition: all 0.3s ease;
}

.operation-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 2rem 0 rgba(58, 59, 69, 0.3);
}

.driver-selection {
    max-height: 400px;
    overflow-y: auto;
    border: 1px solid #dee2e6;
    border-radius: 8px;
}

.driver-item {
    padding: 10px;
    border-bottom: 1px solid #f8f9fa;
    cursor: pointer;
}

.driver-item:hover {
    background-color: #f8f9fa;
}

.driver-item.selected {
    background-color: #e3f2fd;
    border-left: 4px solid #007bff;
}

.bulk-action-btn {
    min-width: 120px;
}

.progress-container {
    display: none;
}

.selection-stats {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 10px;
}

.filter-section {
    background-color: #f8f9fa;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
}
</style>
@endsection

@section('content')
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">
                        <i class="fas fa-tasks"></i> Bulk Operations
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.drivers.index') }}">Drivers</a></li>
                        <li class="breadcrumb-item active">Bulk Operations</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Filter Section -->
            <div class="filter-section">
                <h5><i class="fas fa-filter mr-2"></i>Filter Drivers</h5>
                <form id="driverFilterForm">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" id="statusFilter" class="form-control">
                                    <option value="">All Status</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="suspended">Suspended</option>
                                    <option value="blocked">Blocked</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Verification Status</label>
                                <select name="verification_status" id="verificationFilter" class="form-control">
                                    <option value="">All Verification</option>
                                    <option value="verified">Verified</option>
                                    <option value="pending">Pending</option>
                                    <option value="rejected">Rejected</option>
                                    <option value="reviewing">Reviewing</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Registration Date</label>
                                <select name="registration_period" id="registrationFilter" class="form-control">
                                    <option value="">Any Time</option>
                                    <option value="today">Today</option>
                                    <option value="week">This Week</option>
                                    <option value="month">This Month</option>
                                    <option value="quarter">This Quarter</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Search</label>
                                <input type="text" name="search" id="searchFilter" class="form-control" placeholder="Name, email, phone...">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <button type="button" id="applyFilters" class="btn btn-primary">
                                <i class="fas fa-search"></i> Apply Filters
                            </button>
                            <button type="button" id="clearFilters" class="btn btn-secondary ml-2">
                                <i class="fas fa-times"></i> Clear
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="row">
                <!-- Driver Selection -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-users mr-1"></i>
                                Select Drivers
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-sm btn-primary" id="selectAllBtn">
                                    <i class="fas fa-check-double"></i> Select All
                                </button>
                                <button type="button" class="btn btn-sm btn-secondary ml-2" id="clearSelectionBtn">
                                    <i class="fas fa-times"></i> Clear Selection
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="driver-selection" id="driverList">
                                <!-- Driver list will be populated via AJAX -->
                                <div class="text-center p-4">
                                    <i class="fas fa-spinner fa-spin"></i> Loading drivers...
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Operations Panel -->
                <div class="col-lg-4">
                    <!-- Selection Stats -->
                    <div class="card selection-stats">
                        <div class="card-body text-center">
                            <h4 class="mb-0" id="selectedCount">0</h4>
                            <small>Drivers Selected</small>
                            <hr class="border-light">
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="h6 mb-0" id="totalDrivers">0</div>
                                    <small>Total Available</small>
                                </div>
                                <div class="col-6">
                                    <div class="h6 mb-0" id="filteredCount">0</div>
                                    <small>After Filters</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bulk Operations -->
                    <div class="card operation-card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-cogs mr-1"></i>
                                Bulk Actions
                            </h3>
                        </div>
                        <div class="card-body">
                            <form id="bulkOperationForm" method="POST" action="{{ route('admin.drivers.bulk-action') }}">
                                @csrf
                                <input type="hidden" name="driver_ids" id="selectedDriverIds" value="">

                                <div class="form-group">
                                    <label>Select Operation</label>
                                    <select name="action" id="bulkAction" class="form-control" required>
                                        <option value="">Choose operation...</option>
                                        <optgroup label="Status Changes">
                                            <option value="activate">🟢 Activate Drivers</option>
                                            <option value="deactivate">🔴 Deactivate Drivers</option>
                                            <option value="suspend">⏸️ Suspend Drivers</option>
                                        </optgroup>
                                        <optgroup label="Verification">
                                            <option value="verify">✅ Verify Drivers</option>
                                            <option value="reject">❌ Reject Verification</option>
                                        </optgroup>
                                        <optgroup label="OCR Operations">
                                            <option value="ocr_verify">🤖 Run OCR Verification</option>
                                        </optgroup>
                                        <optgroup label="Export">
                                            <option value="export">📊 Export Selected</option>
                                        </optgroup>
                                    </select>
                                </div>

                                <div class="form-group" id="notesGroup" style="display: none;">
                                    <label>Notes/Reason <span class="text-muted">(for verification operations)</span></label>
                                    <textarea name="notes" id="operationNotes" class="form-control" rows="3" 
                                              placeholder="Add notes or reason for this bulk operation..."></textarea>
                                </div>

                                <div class="form-group" id="adminPasswordGroup" style="display: none;">
                                    <label>Admin Password <span class="text-danger">*</span></label>
                                    <input type="password" name="admin_password" id="adminPassword" 
                                           class="form-control" placeholder="Enter your admin password">
                                    <small class="text-muted">Required for verification operations</small>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary bulk-action-btn" id="executeBtn" disabled>
                                        <i class="fas fa-play"></i> Execute Operation
                                    </button>
                                </div>
                            </form>

                            <!-- Progress Bar -->
                            <div class="progress-container mt-3">
                                <div class="progress">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                         style="width: 0%"></div>
                                </div>
                                <small class="text-muted mt-2">Processing bulk operation...</small>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-pie mr-1"></i>
                                Selection Breakdown
                            </h3>
                        </div>
                        <div class="card-body">
                            <div id="selectionBreakdown">
                                <div class="text-muted text-center">
                                    Select drivers to see breakdown
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@section('js')
<script>
let selectedDrivers = new Set();
let allDrivers = [];
let filteredDrivers = [];

$(document).ready(function() {
    loadDrivers();
    setupEventHandlers();
});

function setupEventHandlers() {
    // Filter handlers
    $('#applyFilters').click(function() {
        loadDrivers();
    });

    $('#clearFilters').click(function() {
        $('#driverFilterForm')[0].reset();
        loadDrivers();
    });

    // Selection handlers
    $('#selectAllBtn').click(function() {
        selectAllFiltered();
    });

    $('#clearSelectionBtn').click(function() {
        clearSelection();
    });

    // Bulk action handler
    $('#bulkAction').change(function() {
        const action = $(this).val();
        toggleFormFields(action);
        updateExecuteButton();
    });

    // Form submission
    $('#bulkOperationForm').submit(function(e) {
        e.preventDefault();
        if (selectedDrivers.size === 0) {
            showAlert('Please select at least one driver', 'warning');
            return;
        }
        executeBulkOperation();
    });

    // Real-time search
    $('#searchFilter').on('input', debounce(function() {
        $('#applyFilters').click();
    }, 500));
}

function loadDrivers() {
    const formData = new FormData($('#driverFilterForm')[0]);
    
    $('#driverList').html('<div class="text-center p-4"><i class="fas fa-spinner fa-spin"></i> Loading drivers...</div>');
    
    fetch('{{ route("admin.drivers.bulk-list") }}?' + new URLSearchParams(formData))
    .then(response => response.json())
    .then(data => {
        allDrivers = data.drivers;
        filteredDrivers = data.drivers;
        renderDriverList(data.drivers);
        updateStats();
    })
    .catch(error => {
        console.error('Error loading drivers:', error);
        $('#driverList').html('<div class="text-center p-4 text-danger"><i class="fas fa-exclamation-triangle"></i> Error loading drivers</div>');
    });
}

function renderDriverList(drivers) {
    const container = $('#driverList');
    
    if (drivers.length === 0) {
        container.html('<div class="text-center p-4 text-muted"><i class="fas fa-users"></i><br>No drivers match your filters</div>');
        return;
    }

    let html = '';
    drivers.forEach(driver => {
        const isSelected = selectedDrivers.has(driver.id);
        const statusBadge = getStatusBadge(driver.status);
        const verificationBadge = getVerificationBadge(driver.verification_status);
        
        html += `
            <div class="driver-item ${isSelected ? 'selected' : ''}" data-driver-id="${driver.id}">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <input type="checkbox" class="driver-checkbox mr-3" ${isSelected ? 'checked' : ''}>
                        <div>
                            <div class="font-weight-bold">${driver.first_name} ${driver.surname}</div>
                            <div class="text-muted small">
                                ${driver.driver_id} • ${driver.email || 'No email'} • ${driver.phone}
                            </div>
                        </div>
                    </div>
                    <div class="text-right">
                        ${statusBadge}
                        ${verificationBadge}
                        <div class="text-muted small mt-1">
                            Registered: ${new Date(driver.created_at).toLocaleDateString()}
                        </div>
                    </div>
                </div>
            </div>
        `;
    });

    container.html(html);

    // Add click handlers
    $('.driver-item').click(function(e) {
        if (e.target.type !== 'checkbox') {
            const checkbox = $(this).find('.driver-checkbox');
            checkbox.prop('checked', !checkbox.prop('checked')).trigger('change');
        }
    });

    $('.driver-checkbox').change(function() {
        const driverId = parseInt($(this).closest('.driver-item').data('driver-id'));
        const item = $(this).closest('.driver-item');
        
        if ($(this).is(':checked')) {
            selectedDrivers.add(driverId);
            item.addClass('selected');
        } else {
            selectedDrivers.delete(driverId);
            item.removeClass('selected');
        }
        
        updateSelectionUI();
    });
}

function getStatusBadge(status) {
    const badges = {
        'active': '<span class="badge badge-success">Active</span>',
        'inactive': '<span class="badge badge-secondary">Inactive</span>',
        'suspended': '<span class="badge badge-warning">Suspended</span>',
        'blocked': '<span class="badge badge-danger">Blocked</span>'
    };
    return badges[status] || '<span class="badge badge-light">Unknown</span>';
}

function getVerificationBadge(status) {
    const badges = {
        'verified': '<span class="badge badge-success">Verified</span>',
        'pending': '<span class="badge badge-warning">Pending</span>',
        'rejected': '<span class="badge badge-danger">Rejected</span>',
        'reviewing': '<span class="badge badge-info">Reviewing</span>'
    };
    return badges[status] || '<span class="badge badge-light">Unknown</span>';
}

function selectAllFiltered() {
    filteredDrivers.forEach(driver => selectedDrivers.add(driver.id));
    $('.driver-checkbox').prop('checked', true);
    $('.driver-item').addClass('selected');
    updateSelectionUI();
}

function clearSelection() {
    selectedDrivers.clear();
    $('.driver-checkbox').prop('checked', false);
    $('.driver-item').removeClass('selected');
    updateSelectionUI();
}

function updateSelectionUI() {
    updateStats();
    updateSelectionBreakdown();
    updateExecuteButton();
    $('#selectedDriverIds').val(JSON.stringify(Array.from(selectedDrivers)));
}

function updateStats() {
    $('#selectedCount').text(selectedDrivers.size);
    $('#totalDrivers').text(allDrivers.length);
    $('#filteredCount').text(filteredDrivers.length);
}

function updateSelectionBreakdown() {
    if (selectedDrivers.size === 0) {
        $('#selectionBreakdown').html('<div class="text-muted text-center">Select drivers to see breakdown</div>');
        return;
    }

    const selectedData = allDrivers.filter(driver => selectedDrivers.has(driver.id));
    const breakdown = {
        status: {},
        verification: {}
    };

    selectedData.forEach(driver => {
        breakdown.status[driver.status] = (breakdown.status[driver.status] || 0) + 1;
        breakdown.verification[driver.verification_status] = (breakdown.verification[driver.verification_status] || 0) + 1;
    });

    let html = '<div class="small">';
    html += '<strong>Status:</strong><br>';
    Object.entries(breakdown.status).forEach(([status, count]) => {
        html += `${status}: ${count}<br>`;
    });
    html += '<br><strong>Verification:</strong><br>';
    Object.entries(breakdown.verification).forEach(([status, count]) => {
        html += `${status}: ${count}<br>`;
    });
    html += '</div>';

    $('#selectionBreakdown').html(html);
}

function toggleFormFields(action) {
    const needsNotes = ['verify', 'reject'].includes(action);
    const needsPassword = ['verify', 'reject', 'ocr_verify'].includes(action);
    
    $('#notesGroup').toggle(needsNotes);
    $('#adminPasswordGroup').toggle(needsPassword);
    
    if (needsNotes) {
        $('#operationNotes').prop('required', true);
    } else {
        $('#operationNotes').prop('required', false);
    }
    
    if (needsPassword) {
        $('#adminPassword').prop('required', true);
    } else {
        $('#adminPassword').prop('required', false);
    }
}

function updateExecuteButton() {
    const hasSelection = selectedDrivers.size > 0;
    const hasAction = $('#bulkAction').val() !== '';
    
    $('#executeBtn').prop('disabled', !hasSelection || !hasAction);
    
    if (hasSelection && hasAction) {
        const count = selectedDrivers.size;
        const action = $('#bulkAction option:selected').text();
        $('#executeBtn').html(`<i class="fas fa-play"></i> Execute on ${count} driver${count !== 1 ? 's' : ''}`);
    } else {
        $('#executeBtn').html('<i class="fas fa-play"></i> Execute Operation');
    }
}

function executeBulkOperation() {
    const action = $('#bulkAction').val();
    const count = selectedDrivers.size;
    
    if (!confirm(`Are you sure you want to ${action} ${count} driver${count !== 1 ? 's' : ''}?`)) {
        return;
    }
    
    showProgressBar();
    
    $.ajax({
        url: $('#bulkOperationForm').attr('action'),
        method: 'POST',
        data: $('#bulkOperationForm').serialize(),
        success: function(response) {
            hideProgressBar();
            showAlert(response.message || 'Bulk operation completed successfully!', 'success');
            clearSelection();
            loadDrivers(); // Reload to show updated data
        },
        error: function(xhr) {
            hideProgressBar();
            const errorMsg = xhr.responseJSON?.message || 'Bulk operation failed';
            showAlert(errorMsg, 'error');
        }
    });
}

function showProgressBar() {
    $('.progress-container').show();
    $('#executeBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
}

function hideProgressBar() {
    $('.progress-container').hide();
    updateExecuteButton();
}

function showAlert(message, type) {
    const alertClass = type === 'success' ? 'alert-success' : (type === 'warning' ? 'alert-warning' : 'alert-danger');
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
             style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    $('body').append(alertHtml);
    
    setTimeout(() => {
        $('.alert-dismissible').fadeOut();
    }, 5000);
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}
</script>
@endsection