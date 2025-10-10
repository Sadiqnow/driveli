@extends('layouts.admin_cdn')

@section('title', 'OCR Verification Dashboard')

@section('head')
<style>
.ocr-card {
    border: 1px solid #e3e6f0;
    border-radius: 15px;
    transition: all 0.3s ease;
    background: white;
}

.ocr-card:hover {
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    transform: translateY(-2px);
}

.verification-score {
    font-size: 2rem;
    font-weight: bold;
}

.score-excellent { color: #1cc88a; }
.score-good { color: #36b9cc; }
.score-fair { color: #f6c23e; }
.score-poor { color: #e74a3b; }

.document-comparison {
    background: #f8f9fc;
    border: 1px solid #e3e6f0;
    border-radius: 10px;
    padding: 15px;
    margin: 10px 0;
}

.comparison-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #e3e6f0;
}

.comparison-item:last-child {
    border-bottom: none;
}

.match-indicator {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: inline-block;
}

.match-excellent { background: #1cc88a; }
.match-good { background: #36b9cc; }
.match-fair { background: #f6c23e; }
.match-poor { background: #e74a3b; }

.ocr-text-box {
    background: #2d3748;
    color: #e2e8f0;
    border-radius: 8px;
    padding: 15px;
    font-family: 'Courier New', monospace;
    font-size: 12px;
    max-height: 300px;
    overflow-y: auto;
}

.processing-animation {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 3px solid #f3f3f3;
    border-top: 3px solid #3498db;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.verification-timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    padding-bottom: 20px;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: -38px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #e3e6f0;
}

.timeline-item.completed::before {
    background: #1cc88a;
}

.timeline-item.current::before {
    background: #36b9cc;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(54, 185, 204, 0.7); }
    70% { box-shadow: 0 0 0 10px rgba(54, 185, 204, 0); }
    100% { box-shadow: 0 0 0 0 rgba(54, 185, 204, 0); }
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
                        <i class="fas fa-robot"></i> OCR Verification Dashboard
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.drivers.index') }}">Drivers</a></li>
                        <li class="breadcrumb-item active">OCR Verification</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3 id="totalProcessed">0</h3>
                            <p>Total Processed</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-cogs"></i>
                        </div>
                        <div class="small-box-footer">
                            <i class="fas fa-chart-line"></i> OCR Statistics
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3 id="passedCount">0</h3>
                            <p>Passed Verification</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="small-box-footer">
                            <i class="fas fa-thumbs-up"></i> Success Rate
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3 id="pendingCount">0</h3>
                            <p>Pending Review</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="small-box-footer">
                            <i class="fas fa-hourglass-half"></i> In Queue
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3 id="failedCount">0</h3>
                            <p>Failed Verification</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div class="small-box-footer">
                            <i class="fas fa-exclamation-triangle"></i> Need Review
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bulk Actions -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h3 class="card-title">
                                <i class="fas fa-bolt"></i> Bulk OCR Operations
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <button class="btn btn-success btn-block" onclick="bulkOCRVerification()">
                                        <i class="fas fa-play"></i> Start Bulk OCR
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <button class="btn btn-info btn-block" onclick="showOCRQueue()">
                                        <i class="fas fa-list"></i> View Queue
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <button class="btn btn-warning btn-block" onclick="pauseOCRProcessing()">
                                        <i class="fas fa-pause"></i> Pause Processing
                                    </button>
                                </div>
                                <div class="col-md-3">
                                    <button class="btn btn-secondary btn-block" onclick="downloadOCRReport()">
                                        <i class="fas fa-download"></i> Download Report
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Progress Bar -->
                            <div class="mt-3" id="bulkProgressContainer" style="display: none;">
                                <div class="d-flex justify-content-between">
                                    <span>Processing...</span>
                                    <span id="progressText">0/0</span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                         id="bulkProgressBar" 
                                         style="width: 0%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Driver List with OCR Status -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-users"></i> Drivers OCR Status
                            </h3>
                            <div class="card-tools">
                                <button class="btn btn-primary btn-sm" onclick="refreshDriversList()">
                                    <i class="fas fa-sync"></i> Refresh
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped" id="driversTable">
                                    <thead>
                                        <tr>
                                            <th>
                                                <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                                            </th>
                                            <th>Driver</th>
                                            <th>NIN OCR</th>
                                            <th>License OCR</th>
                                            <th>Overall Status</th>
                                            <th>Last Processed</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="driversTableBody">
                                        <tr>
                                            <td colspan="7" class="text-center">
                                                <i class="fas fa-spinner fa-spin"></i> Loading drivers...
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- OCR Details Modal -->
<div class="modal fade" id="ocrDetailsModal" tabindex="-1" aria-labelledby="ocrDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ocrDetailsModalLabel">OCR Verification Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="ocrDetailsContent">
                <!-- Content will be loaded dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="reprocessOCR()">
                    <i class="fas fa-redo"></i> Reprocess
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Manual Override Modal -->
<div class="modal fade" id="ocrOverrideModal" tabindex="-1" aria-labelledby="ocrOverrideModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ocrOverrideModalLabel">Manual OCR Override</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="ocrOverrideForm" onsubmit="submitOCROverride(event)">
                <div class="modal-body">
                    <input type="hidden" id="overrideDriverId" name="driver_id">
                    
                    <div class="mb-3">
                        <label for="verificationType" class="form-label">Verification Type</label>
                        <select class="form-control" id="verificationType" name="verification_type" required>
                            <option value="">Select type...</option>
                            <option value="nin">NIN Document</option>
                            <option value="frsc">FRSC License</option>
                            <option value="both">Both Documents</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="overrideStatus" class="form-label">Override Status</label>
                        <select class="form-control" id="overrideStatus" name="status" required>
                            <option value="">Select status...</option>
                            <option value="passed">Passed</option>
                            <option value="failed">Failed</option>
                            <option value="pending">Pending</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="adminNotes" class="form-label">Admin Notes *</label>
                        <textarea class="form-control" id="adminNotes" name="admin_notes" rows="3" 
                                  placeholder="Explain the reason for manual override..." required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="adminPassword" class="form-label">Admin Password *</label>
                        <input type="password" class="form-control" id="adminPassword" name="admin_password" 
                               placeholder="Enter your password to confirm" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-user-cog"></i> Apply Override
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
let selectedDrivers = [];
let processingQueue = [];
let isProcessing = false;

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    loadOCRStatistics();
    loadDriversList();
});

// Load OCR statistics
function loadOCRStatistics() {
    fetch('{{ route("admin.drivers.index") }}?format=json&ocr_stats=true')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('totalProcessed').textContent = data.stats.total_processed || 0;
            document.getElementById('passedCount').textContent = data.stats.passed || 0;
            document.getElementById('pendingCount').textContent = data.stats.pending || 0;
            document.getElementById('failedCount').textContent = data.stats.failed || 0;
        }
    })
    .catch(error => console.error('Error loading statistics:', error));
}

// Load drivers list
function loadDriversList() {
    fetch('{{ route("admin.drivers.index") }}?format=json&include_ocr=true')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderDriversList(data.drivers);
        } else {
            showAlert('error', 'Failed to load drivers list');
        }
    })
    .catch(error => {
        console.error('Error loading drivers:', error);
        showAlert('error', 'Error loading drivers list');
    });
}

// Render drivers list
function renderDriversList(drivers) {
    const tbody = document.getElementById('driversTableBody');
    
    if (drivers.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center text-muted">
                    No drivers found
                </td>
            </tr>
        `;
        return;
    }
    
    let html = '';
    drivers.forEach(driver => {
        const ninScore = driver.nin_ocr_match_score || 0;
        const frscScore = driver.frsc_ocr_match_score || 0;
        const overallStatus = driver.ocr_verification_status || 'pending';
        
        html += `
            <tr>
                <td>
                    <input type="checkbox" class="driver-checkbox" value="${driver.id}" 
                           onchange="updateSelectedDrivers()">
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="ml-3">
                            <strong>${driver.full_name}</strong><br>
                            <small class="text-muted">${driver.driver_id}</small>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="text-center">
                        ${getScoreBadge(ninScore, 'NIN')}
                        <br><small>${ninScore}%</small>
                    </div>
                </td>
                <td>
                    <div class="text-center">
                        ${getScoreBadge(frscScore, 'License')}
                        <br><small>${frscScore}%</small>
                    </div>
                </td>
                <td>
                    <div class="text-center">
                        ${getStatusBadge(overallStatus)}
                    </div>
                </td>
                <td>
                    <small class="text-muted">
                        ${driver.nin_verified_at || driver.frsc_verified_at || 'Never'}
                    </small>
                </td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-info" onclick="viewOCRDetails(${driver.id})" 
                                title="View Details">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-outline-primary" onclick="processDriverOCR(${driver.id})" 
                                title="Process OCR">
                            <i class="fas fa-cogs"></i>
                        </button>
                        <button class="btn btn-outline-warning" onclick="showOCROverride(${driver.id})" 
                                title="Manual Override">
                            <i class="fas fa-user-cog"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
}

// Get score badge
function getScoreBadge(score, type) {
    let badgeClass = 'badge-secondary';
    let icon = 'fas fa-question';
    
    if (score >= 90) {
        badgeClass = 'badge-success';
        icon = 'fas fa-check-circle';
    } else if (score >= 80) {
        badgeClass = 'badge-info';
        icon = 'fas fa-check';
    } else if (score >= 60) {
        badgeClass = 'badge-warning';
        icon = 'fas fa-exclamation-triangle';
    } else if (score > 0) {
        badgeClass = 'badge-danger';
        icon = 'fas fa-times-circle';
    }
    
    return `<span class="badge ${badgeClass}"><i class="${icon}"></i> ${type}</span>`;
}

// Get status badge
function getStatusBadge(status) {
    const badges = {
        'passed': '<span class="badge badge-success">Passed</span>',
        'failed': '<span class="badge badge-danger">Failed</span>',
        'pending': '<span class="badge badge-warning">Pending</span>'
    };
    
    return badges[status] || '<span class="badge badge-secondary">Unknown</span>';
}

// Toggle select all
function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.driver-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
    
    updateSelectedDrivers();
}

// Update selected drivers
function updateSelectedDrivers() {
    const checkboxes = document.querySelectorAll('.driver-checkbox:checked');
    selectedDrivers = Array.from(checkboxes).map(cb => cb.value);
    
    // Update select all state
    const selectAll = document.getElementById('selectAll');
    const allCheckboxes = document.querySelectorAll('.driver-checkbox');
    
    if (selectedDrivers.length === 0) {
        selectAll.indeterminate = false;
        selectAll.checked = false;
    } else if (selectedDrivers.length === allCheckboxes.length) {
        selectAll.indeterminate = false;
        selectAll.checked = true;
    } else {
        selectAll.indeterminate = true;
    }
}

// Bulk OCR verification
function bulkOCRVerification() {
    if (selectedDrivers.length === 0) {
        showAlert('warning', 'Please select drivers to process');
        return;
    }
    
    if (!confirm(`Start OCR verification for ${selectedDrivers.length} selected drivers?`)) {
        return;
    }
    
    showBulkProgress();
    processingQueue = [...selectedDrivers];
    isProcessing = true;
    
    processBulkOCR();
}

// Process bulk OCR
function processBulkOCR() {
    if (processingQueue.length === 0 || !isProcessing) {
        hideBulkProgress();
        loadOCRStatistics();
        loadDriversList();
        showAlert('success', 'Bulk OCR processing completed');
        return;
    }
    
    const driverId = processingQueue.shift();
    const processed = selectedDrivers.length - processingQueue.length;
    const total = selectedDrivers.length;
    
    updateBulkProgress(processed, total);
    
    fetch(`{{ url('admin/drivers') }}/${driverId}/ocr-verify`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            verification_type: 'both'
        })
    })
    .then(response => response.json())
    .then(data => {
        // Continue with next driver after a short delay
        setTimeout(() => processBulkOCR(), 1000);
    })
    .catch(error => {
        console.error('OCR processing error:', error);
        // Continue with next driver even if one fails
        setTimeout(() => processBulkOCR(), 1000);
    });
}

// Show/hide bulk progress
function showBulkProgress() {
    document.getElementById('bulkProgressContainer').style.display = 'block';
}

function hideBulkProgress() {
    document.getElementById('bulkProgressContainer').style.display = 'none';
}

function updateBulkProgress(processed, total) {
    const percentage = Math.round((processed / total) * 100);
    document.getElementById('bulkProgressBar').style.width = percentage + '%';
    document.getElementById('progressText').textContent = `${processed}/${total}`;
}

// Process individual driver OCR
function processDriverOCR(driverId) {
    if (!confirm('Start OCR verification for this driver?')) {
        return;
    }
    
    const button = event.target.closest('button');
    const originalContent = button.innerHTML;
    button.innerHTML = '<div class="processing-animation"></div>';
    button.disabled = true;
    
    fetch(`{{ url('admin/drivers') }}/${driverId}/ocr-verify`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            verification_type: 'both'
        })
    })
    .then(response => response.json())
    .then(data => {
        button.innerHTML = originalContent;
        button.disabled = false;
        
        if (data.success) {
            showAlert('success', 'OCR verification completed');
            loadDriversList();
        } else {
            showAlert('error', data.message || 'OCR verification failed');
        }
    })
    .catch(error => {
        button.innerHTML = originalContent;
        button.disabled = false;
        showAlert('error', 'OCR verification failed: ' + error.message);
    });
}

// View OCR details
function viewOCRDetails(driverId) {
    fetch(`{{ url('admin/drivers') }}/${driverId}/ocr-details`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderOCRDetails(data.details);
            const modal = new bootstrap.Modal(document.getElementById('ocrDetailsModal'));
            modal.show();
        } else {
            showAlert('error', 'Failed to load OCR details');
        }
    })
    .catch(error => {
        showAlert('error', 'Error loading OCR details: ' + error.message);
    });
}

// Render OCR details
function renderOCRDetails(details) {
    const content = document.getElementById('ocrDetailsContent');
    
    let html = `
        <div class="row">
            <div class="col-md-6">
                <h6><i class="fas fa-id-card"></i> NIN Verification</h6>
                <div class="ocr-card p-3 mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Status:</span>
                        <span>${getStatusBadge(details.summary.nin_verification.status)}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Score:</span>
                        <span class="verification-score ${getScoreClass(details.summary.nin_verification.score)}">
                            ${details.summary.nin_verification.score}%
                        </span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Verified:</span>
                        <span>${details.summary.nin_verification.verified_at || 'Not verified'}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <h6><i class="fas fa-credit-card"></i> FRSC Verification</h6>
                <div class="ocr-card p-3 mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Status:</span>
                        <span>${getStatusBadge(details.summary.frsc_verification.status)}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Score:</span>
                        <span class="verification-score ${getScoreClass(details.summary.frsc_verification.score)}">
                            ${details.summary.frsc_verification.score}%
                        </span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Verified:</span>
                        <span>${details.summary.frsc_verification.verified_at || 'Not verified'}</span>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    if (details.nin_data) {
        html += `
            <div class="mt-4">
                <h6><i class="fas fa-search"></i> NIN OCR Data</h6>
                <div class="ocr-text-box">
                    ${JSON.stringify(details.nin_data, null, 2)}
                </div>
            </div>
        `;
    }
    
    if (details.frsc_data) {
        html += `
            <div class="mt-4">
                <h6><i class="fas fa-search"></i> FRSC OCR Data</h6>
                <div class="ocr-text-box">
                    ${JSON.stringify(details.frsc_data, null, 2)}
                </div>
            </div>
        `;
    }
    
    content.innerHTML = html;
}

// Get score class
function getScoreClass(score) {
    if (score >= 90) return 'score-excellent';
    if (score >= 80) return 'score-good';
    if (score >= 60) return 'score-fair';
    return 'score-poor';
}

// Show OCR override modal
function showOCROverride(driverId) {
    document.getElementById('overrideDriverId').value = driverId;
    document.getElementById('ocrOverrideForm').reset();
    
    const modal = new bootstrap.Modal(document.getElementById('ocrOverrideModal'));
    modal.show();
}

// Submit OCR override
function submitOCROverride(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const driverId = formData.get('driver_id');
    
    fetch(`{{ url('admin/drivers') }}/${driverId}/ocr-override`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            const modal = bootstrap.Modal.getInstance(document.getElementById('ocrOverrideModal'));
            modal.hide();
            loadDriversList();
        } else {
            showAlert('error', data.message || 'Override failed');
        }
    })
    .catch(error => {
        showAlert('error', 'Override failed: ' + error.message);
    });
}

// Refresh drivers list
function refreshDriversList() {
    loadOCRStatistics();
    loadDriversList();
    showAlert('info', 'Data refreshed');
}

// Pause OCR processing
function pauseOCRProcessing() {
    isProcessing = false;
    showAlert('info', 'OCR processing paused');
}

// Show OCR queue
function showOCRQueue() {
    showAlert('info', `Processing queue: ${processingQueue.length} drivers remaining`);
}

// Download OCR report
function downloadOCRReport() {
    window.open(`{{ route('admin.drivers.export') }}?format=csv&include_ocr=true`, '_blank');
}

// Show alert
function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 
                      type === 'warning' ? 'alert-warning' : 
                      type === 'info' ? 'alert-info' : 'alert-danger';
    
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Remove existing alerts
    document.querySelectorAll('.alert-dismissible').forEach(alert => alert.remove());
    
    // Add new alert
    document.querySelector('.container-fluid').insertAdjacentHTML('afterbegin', alertHtml);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        document.querySelector('.alert-dismissible')?.remove();
    }, 5000);
}
</script>
@endsection