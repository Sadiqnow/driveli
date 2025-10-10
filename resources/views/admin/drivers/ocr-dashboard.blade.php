@extends('layouts.admin_cdn')

@section('title', 'OCR Verification Dashboard')

@section('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
.ocr-widget {
    border: 1px solid #e3e6f0;
    border-radius: 15px;
    transition: all 0.3s ease;
    background: white;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.05);
}

.ocr-widget:hover {
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

.document-preview {
    max-width: 100px;
    max-height: 100px;
    border-radius: 8px;
    border: 2px solid #e3e6f0;
    object-fit: cover;
}

.verification-timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    padding-bottom: 20px;
    border-left: 2px solid #e3e6f0;
    margin-left: -1px;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: -6px;
    top: 5px;
    width: 10px;
    height: 10px;
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

.timeline-item.failed::before {
    background: #e74a3b;
}

@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(54, 185, 204, 0.7); }
    70% { box-shadow: 0 0 0 10px rgba(54, 185, 204, 0); }
    100% { box-shadow: 0 0 0 0 rgba(54, 185, 204, 0); }
}

.processing-spinner {
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

.ocr-stats-chart {
    height: 300px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 15px;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
}

.accuracy-meter {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    background: conic-gradient(#1cc88a 0deg, #1cc88a var(--accuracy)deg, #e3e6f0 var(--accuracy)deg);
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
}

.accuracy-meter::before {
    content: '';
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: white;
    position: absolute;
}

.accuracy-text {
    position: relative;
    z-index: 1;
    font-size: 1.5rem;
    font-weight: bold;
    color: #333;
}

.real-time-status {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 1050;
    padding: 10px 20px;
    border-radius: 25px;
    background: rgba(255, 255, 255, 0.95);
    border: 1px solid #e3e6f0;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    backdrop-filter: blur(10px);
}

.quick-action-panel {
    position: sticky;
    top: 20px;
    z-index: 100;
}

.document-comparison-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin: 20px 0;
}

.comparison-card {
    background: #f8f9fc;
    border: 1px solid #e3e6f0;
    border-radius: 10px;
    padding: 15px;
}

.match-indicator {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: inline-block;
    margin-right: 10px;
}

.match-excellent { background: #1cc88a; }
.match-good { background: #36b9cc; }
.match-fair { background: #f6c23e; }
.match-poor { background: #e74a3b; }

.bulk-progress-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.progress-modal {
    background: white;
    padding: 30px;
    border-radius: 15px;
    min-width: 400px;
    text-align: center;
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
                        <i class="fas fa-robot text-primary"></i> OCR Verification Dashboard
                    </h1>
                    <p class="text-muted">Intelligent document verification and processing center</p>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.drivers.index') }}">Drivers</a></li>
                        <li class="breadcrumb-item active">OCR Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Real-time Status Indicator -->
    <div class="real-time-status" id="realTimeStatus">
        <i class="fas fa-circle text-success" id="statusIndicator"></i>
        <span id="statusText">System Online</span>
        <span class="badge bg-info ms-2" id="queueCounter">0 in queue</span>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Top Statistics Row -->
            <div class="row mb-4">
                <!-- Total Processed Widget -->
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3 id="totalProcessed">{{ $stats['total_processed'] ?? 0 }}</h3>
                            <p>Total Processed</p>
                            <div class="progress progress-sm mt-2">
                                <div class="progress-bar bg-white" id="processedProgress" style="width: 100%"></div>
                            </div>
                        </div>
                        <div class="icon">
                            <i class="fas fa-cogs"></i>
                        </div>
                        <a href="#" class="small-box-footer" onclick="showProcessedDetails()">
                            View Details <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <!-- Success Rate Widget -->
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3 id="successRate">{{ number_format((($stats['passed'] ?? 0) / max(($stats['total_processed'] ?? 1), 1)) * 100, 1) }}%</h3>
                            <p>Success Rate</p>
                            <div class="progress progress-sm mt-2">
                                <div class="progress-bar bg-white" id="successProgress" style="width: {{ (($stats['passed'] ?? 0) / max(($stats['total_processed'] ?? 1), 1)) * 100 }}%"></div>
                            </div>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <a href="#" class="small-box-footer" onclick="filterByStatus('passed')">
                            View Passed <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <!-- Pending Queue Widget -->
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3 id="pendingCount">{{ $stats['pending'] ?? 0 }}</h3>
                            <p>Pending Review</p>
                            <div class="progress progress-sm mt-2">
                                <div class="progress-bar bg-white" id="pendingProgress" style="width: 70%"></div>
                            </div>
                        </div>
                        <div class="icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <a href="#" class="small-box-footer" onclick="filterByStatus('pending')">
                            Process Queue <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <!-- Failed Review Widget -->
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3 id="failedCount">{{ $stats['failed'] ?? 0 }}</h3>
                            <p>Failed Review</p>
                            <div class="progress progress-sm mt-2">
                                <div class="progress-bar bg-white" id="failedProgress" style="width: 30%"></div>
                            </div>
                        </div>
                        <div class="icon">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <a href="#" class="small-box-footer" onclick="filterByStatus('failed')">
                            Review Failed <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Analytics and Controls Row -->
            <div class="row mb-4">
                <!-- OCR Analytics Chart -->
                <div class="col-md-8">
                    <div class="card ocr-widget">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-line"></i> OCR Performance Analytics
                            </h3>
                            <div class="card-tools">
                                <select class="form-control form-control-sm" id="analyticsTimeframe" onchange="updateAnalytics()">
                                    <option value="today">Today</option>
                                    <option value="week">This Week</option>
                                    <option value="month" selected>This Month</option>
                                    <option value="quarter">This Quarter</option>
                                </select>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <canvas id="performanceChart" height="300"></canvas>
                                </div>
                                <div class="col-md-4">
                                    <div class="accuracy-meter" style="--accuracy: {{ (($stats['passed'] ?? 0) / max(($stats['total_processed'] ?? 1), 1)) * 360 }}deg;">
                                        <div class="accuracy-text">
                                            {{ number_format((($stats['passed'] ?? 0) / max(($stats['total_processed'] ?? 1), 1)) * 100, 1) }}%
                                            <br><small class="text-muted">Accuracy</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions Panel -->
                <div class="col-md-4">
                    <div class="quick-action-panel">
                        <div class="card ocr-widget">
                            <div class="card-header bg-primary text-white">
                                <h3 class="card-title">
                                    <i class="fas fa-bolt"></i> Quick Actions
                                </h3>
                            </div>
                            <div class="card-body p-0">
                                <div class="list-group list-group-flush">
                                    <button class="list-group-item list-group-item-action" onclick="startBulkProcessing()">
                                        <i class="fas fa-play text-success"></i> Start Bulk Processing
                                    </button>
                                    <button class="list-group-item list-group-item-action" onclick="pauseProcessing()">
                                        <i class="fas fa-pause text-warning"></i> Pause Processing
                                    </button>
                                    <button class="list-group-item list-group-item-action" onclick="showSystemSettings()">
                                        <i class="fas fa-cog text-info"></i> System Settings
                                    </button>
                                    <button class="list-group-item list-group-item-action" onclick="exportOCRReport()">
                                        <i class="fas fa-download text-secondary"></i> Export Report
                                    </button>
                                    <button class="list-group-item list-group-item-action" onclick="reprocessFailed()">
                                        <i class="fas fa-redo text-danger"></i> Reprocess Failed
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Processing Queue Status -->
                        <div class="card ocr-widget mt-3">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-list"></i> Processing Queue
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>Queue Status:</span>
                                    <span class="badge bg-success" id="queueStatus">Active</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>Items in Queue:</span>
                                    <span id="queueCount">0</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>Processing Speed:</span>
                                    <span id="processingSpeed">0/min</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>ETA:</span>
                                    <span id="processingETA">-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Drivers OCR Status Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card ocr-widget">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-users"></i> Driver OCR Verification Status
                            </h3>
                            <div class="card-tools">
                                <div class="input-group input-group-sm" style="width: 300px;">
                                    <input type="text" id="searchDrivers" class="form-control" placeholder="Search drivers..." onkeyup="searchDrivers()">
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" onclick="refreshDriversList()">
                                            <i class="fas fa-sync"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Filter Tabs -->
                            <ul class="nav nav-tabs mb-3" id="statusTabs">
                                <li class="nav-item">
                                    <a class="nav-link active" href="#all" onclick="filterDrivers('all')">
                                        All <span class="badge bg-secondary ms-1" id="allCount">0</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#pending" onclick="filterDrivers('pending')">
                                        Pending <span class="badge bg-warning ms-1" id="pendingTabCount">0</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#passed" onclick="filterDrivers('passed')">
                                        Passed <span class="badge bg-success ms-1" id="passedTabCount">0</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#failed" onclick="filterDrivers('failed')">
                                        Failed <span class="badge bg-danger ms-1" id="failedTabCount">0</span>
                                    </a>
                                </li>
                            </ul>

                            <!-- Bulk Actions Bar -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="selectAllDrivers" onchange="toggleSelectAll()">
                                        <label class="form-check-label" for="selectAllDrivers">
                                            Select All (<span id="selectedCount">0</span> selected)
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6 text-end">
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-success btn-sm" onclick="bulkVerify()" disabled id="bulkVerifyBtn">
                                            <i class="fas fa-check"></i> Verify Selected
                                        </button>
                                        <button class="btn btn-warning btn-sm" onclick="bulkOverride()" disabled id="bulkOverrideBtn">
                                            <i class="fas fa-edit"></i> Override Selected
                                        </button>
                                        <button class="btn btn-info btn-sm" onclick="bulkReprocess()" disabled id="bulkReprocessBtn">
                                            <i class="fas fa-redo"></i> Reprocess Selected
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Drivers Table -->
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped align-middle" id="driversTable">
                                    <thead class="table-dark">
                                        <tr>
                                            <th width="50">
                                                <input type="checkbox" id="headerSelectAll" onchange="toggleSelectAll()">
                                            </th>
                                            <th>Driver</th>
                                            <th>Documents</th>
                                            <th>NIN Verification</th>
                                            <th>License Verification</th>
                                            <th>Overall Status</th>
                                            <th>Last Processed</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="driversTableBody">
                                        <tr>
                                            <td colspan="8" class="text-center py-4">
                                                <div class="processing-spinner"></div>
                                                <div class="mt-2">Loading drivers data...</div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div>
                                    <span class="text-muted">Showing <span id="showingStart">0</span> to <span id="showingEnd">0</span> of <span id="totalDrivers">0</span> drivers</span>
                                </div>
                                <nav id="paginationNav">
                                    <!-- Pagination will be loaded here -->
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Bulk Processing Overlay -->
<div class="bulk-progress-overlay" id="bulkProgressOverlay">
    <div class="progress-modal">
        <h4><i class="fas fa-cogs"></i> Bulk OCR Processing</h4>
        <p>Processing selected drivers...</p>
        <div class="progress mb-3" style="height: 25px;">
            <div class="progress-bar progress-bar-striped progress-bar-animated" id="bulkProgressBar" style="width: 0%"></div>
        </div>
        <div class="d-flex justify-content-between">
            <span>Progress:</span>
            <span id="bulkProgressText">0/0</span>
        </div>
        <button class="btn btn-warning mt-3" onclick="cancelBulkProcessing()">Cancel Processing</button>
    </div>
</div>

<!-- Include modals from original file -->
@include('admin.drivers.modals.ocr-details')
@include('admin.drivers.modals.ocr-override')
@include('admin.drivers.modals.system-settings')
@endsection

@section('js')
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
let driversData = [];
let filteredDrivers = [];
let selectedDrivers = [];
let currentFilter = 'all';
let processingQueue = [];
let isProcessing = false;
let performanceChart;

// Initialize dashboard
document.addEventListener('DOMContentLoaded', function() {
    initializeDashboard();
    loadOCRStatistics();
    loadDriversData();
    initializePerformanceChart();
    startRealTimeUpdates();
});

// Initialize dashboard components
function initializeDashboard() {
    // Set up event listeners
    document.getElementById('searchDrivers').addEventListener('input', debounce(searchDrivers, 300));
    
    // Initialize real-time status
    updateRealTimeStatus();
}

// Load OCR statistics
function loadOCRStatistics() {
    fetch('{{ route("admin.drivers.ocr-dashboard") }}?format=json')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            updateStatisticsWidgets(data.stats);
        }
    })
    .catch(error => console.error('Error loading statistics:', error));
}

// Update statistics widgets
function updateStatisticsWidgets(stats) {
    document.getElementById('totalProcessed').textContent = stats.total_processed || 0;
    document.getElementById('pendingCount').textContent = stats.pending || 0;
    document.getElementById('failedCount').textContent = stats.failed || 0;
    
    // Calculate success rate
    const successRate = stats.total_processed > 0 ? (stats.passed / stats.total_processed * 100) : 0;
    document.getElementById('successRate').textContent = successRate.toFixed(1) + '%';
    
    // Update progress bars
    document.getElementById('successProgress').style.width = successRate + '%';
    document.getElementById('pendingProgress').style.width = (stats.pending / Math.max(stats.total_processed, 1) * 100) + '%';
    document.getElementById('failedProgress').style.width = (stats.failed / Math.max(stats.total_processed, 1) * 100) + '%';
    
    // Update tab counts
    document.getElementById('allCount').textContent = stats.total_processed || 0;
    document.getElementById('pendingTabCount').textContent = stats.pending || 0;
    document.getElementById('passedTabCount').textContent = stats.passed || 0;
    document.getElementById('failedTabCount').textContent = stats.failed || 0;
}

// Load drivers data
function loadDriversData() {
    fetch('{{ route("admin.drivers.index") }}?format=json&include_ocr=true')
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            driversData = data.drivers;
            filteredDrivers = [...driversData];
            renderDriversTable();
            updateDriverCounts();
        } else {
            showAlert('error', 'Failed to load drivers data');
        }
    })
    .catch(error => {
        console.error('Error loading drivers:', error);
        showAlert('error', 'Error loading drivers data');
    });
}

// Render drivers table
function renderDriversTable() {
    const tbody = document.getElementById('driversTableBody');
    
    if (filteredDrivers.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center text-muted py-4">
                    <i class="fas fa-users fa-3x mb-3"></i>
                    <div>No drivers found matching current filter</div>
                </td>
            </tr>
        `;
        return;
    }
    
    let html = '';
    filteredDrivers.forEach(driver => {
        const ninScore = driver.nin_ocr_match_score || 0;
        const frscScore = driver.frsc_ocr_match_score || 0;
        const overallStatus = driver.ocr_verification_status || 'pending';
        
        html += `
            <tr data-driver-id="${driver.id}">
                <td>
                    <input type="checkbox" class="driver-checkbox" value="${driver.id}" onchange="updateSelectedDrivers()">
                </td>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                ${(driver.first_name?.charAt(0) || 'D')+(driver.surname?.charAt(0) || 'R')}
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="fw-bold">${driver.full_name || driver.first_name + ' ' + driver.surname}</div>
                            <div class="text-muted small">${driver.driver_id}</div>
                            <div class="text-muted small">${driver.phone}</div>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="d-flex gap-1">
                        ${driver.nin_document ? '<span class="badge bg-success">NIN</span>' : '<span class="badge bg-secondary">No NIN</span>'}
                        ${driver.frsc_document ? '<span class="badge bg-success">License</span>' : '<span class="badge bg-secondary">No License</span>'}
                    </div>
                </td>
                <td class="text-center">
                    ${getVerificationStatusBadge(ninScore, 'NIN')}
                    <div class="small text-muted mt-1">${ninScore}% match</div>
                </td>
                <td class="text-center">
                    ${getVerificationStatusBadge(frscScore, 'License')}
                    <div class="small text-muted mt-1">${frscScore}% match</div>
                </td>
                <td class="text-center">
                    ${getOverallStatusBadge(overallStatus)}
                </td>
                <td>
                    <div class="small">
                        ${driver.nin_verified_at || driver.frsc_verified_at || 'Never processed'}
                    </div>
                </td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-info" onclick="viewOCRDetails(${driver.id})" title="View Details">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-outline-primary" onclick="processDriverOCR(${driver.id})" title="Process OCR">
                            <i class="fas fa-cogs"></i>
                        </button>
                        <button class="btn btn-outline-warning" onclick="showOCROverride(${driver.id})" title="Manual Override">
                            <i class="fas fa-user-cog"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
    updatePagination();
}

// Get verification status badge
function getVerificationStatusBadge(score, type) {
    let badgeClass, icon;
    
    if (score >= 90) {
        badgeClass = 'bg-success';
        icon = 'fas fa-check-circle';
    } else if (score >= 80) {
        badgeClass = 'bg-info';
        icon = 'fas fa-check';
    } else if (score >= 60) {
        badgeClass = 'bg-warning';
        icon = 'fas fa-exclamation-triangle';
    } else if (score > 0) {
        badgeClass = 'bg-danger';
        icon = 'fas fa-times-circle';
    } else {
        badgeClass = 'bg-secondary';
        icon = 'fas fa-question';
    }
    
    return `<span class="badge ${badgeClass}"><i class="${icon}"></i> ${type}</span>`;
}

// Get overall status badge
function getOverallStatusBadge(status) {
    const badges = {
        'passed': '<span class="badge bg-success">Passed</span>',
        'failed': '<span class="badge bg-danger">Failed</span>',
        'pending': '<span class="badge bg-warning">Pending</span>'
    };
    
    return badges[status] || '<span class="badge bg-secondary">Unknown</span>';
}

// Filter drivers by status
function filterDrivers(status) {
    currentFilter = status;
    
    // Update active tab
    document.querySelectorAll('#statusTabs .nav-link').forEach(link => {
        link.classList.remove('active');
    });
    document.querySelector(`#statusTabs .nav-link[href="#${status}"]`).classList.add('active');
    
    // Filter data
    if (status === 'all') {
        filteredDrivers = [...driversData];
    } else {
        filteredDrivers = driversData.filter(driver => 
            (driver.ocr_verification_status || 'pending') === status
        );
    }
    
    renderDriversTable();
}

// Search drivers
function searchDrivers() {
    const searchTerm = document.getElementById('searchDrivers').value.toLowerCase();
    
    if (searchTerm === '') {
        filterDrivers(currentFilter);
        return;
    }
    
    filteredDrivers = driversData.filter(driver => {
        const fullName = (driver.full_name || driver.first_name + ' ' + driver.surname).toLowerCase();
        const driverId = (driver.driver_id || '').toLowerCase();
        const phone = (driver.phone || '').toLowerCase();
        const email = (driver.email || '').toLowerCase();
        
        return fullName.includes(searchTerm) || 
               driverId.includes(searchTerm) || 
               phone.includes(searchTerm) || 
               email.includes(searchTerm);
    });
    
    renderDriversTable();
}

// Initialize performance chart
function initializePerformanceChart() {
    const ctx = document.getElementById('performanceChart').getContext('2d');
    
    performanceChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [{
                label: 'Documents Processed',
                data: [12, 19, 15, 25, 22, 18, 24],
                borderColor: '#36b9cc',
                backgroundColor: 'rgba(54, 185, 204, 0.1)',
                tension: 0.4
            }, {
                label: 'Success Rate (%)',
                data: [85, 88, 92, 87, 90, 85, 88],
                borderColor: '#1cc88a',
                backgroundColor: 'rgba(28, 200, 138, 0.1)',
                tension: 0.4,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    position: 'left'
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    min: 0,
                    max: 100,
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            }
        }
    });
}

// Update real-time status
function updateRealTimeStatus() {
    const statusIndicator = document.getElementById('statusIndicator');
    const statusText = document.getElementById('statusText');
    const queueCounter = document.getElementById('queueCounter');
    
    if (isProcessing) {
        statusIndicator.className = 'fas fa-circle text-warning';
        statusText.textContent = 'Processing...';
    } else if (processingQueue.length > 0) {
        statusIndicator.className = 'fas fa-circle text-info';
        statusText.textContent = 'Queue Active';
    } else {
        statusIndicator.className = 'fas fa-circle text-success';
        statusText.textContent = 'System Online';
    }
    
    queueCounter.textContent = `${processingQueue.length} in queue`;
}

// Start real-time updates
function startRealTimeUpdates() {
    setInterval(() => {
        updateRealTimeStatus();
        // Update queue status
        document.getElementById('queueCount').textContent = processingQueue.length;
        document.getElementById('queueStatus').textContent = isProcessing ? 'Processing' : 'Idle';
    }, 5000);
}

// Utility functions
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

function showAlert(type, message) {
    // Create Bootstrap alert
    const alertTypes = {
        'success': 'alert-success',
        'error': 'alert-danger',
        'warning': 'alert-warning',
        'info': 'alert-info'
    };
    
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert ${alertTypes[type] || 'alert-info'} alert-dismissible fade show`;
    alertDiv.style.position = 'fixed';
    alertDiv.style.top = '70px';
    alertDiv.style.right = '20px';
    alertDiv.style.zIndex = '9999';
    alertDiv.style.minWidth = '300px';
    
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (alertDiv && alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}

function updateDriverCounts() {
    // Update driver counts in tabs
    const allCount = driversData.length;
    const pendingCount = driversData.filter(d => (d.ocr_verification_status || 'pending') === 'pending').length;
    const passedCount = driversData.filter(d => d.ocr_verification_status === 'passed').length;
    const failedCount = driversData.filter(d => d.ocr_verification_status === 'failed').length;
    
    document.getElementById('allCount').textContent = allCount;
    document.getElementById('pendingTabCount').textContent = pendingCount;
    document.getElementById('passedTabCount').textContent = passedCount;
    document.getElementById('failedTabCount').textContent = failedCount;
}

function updatePagination() {
    // Implement pagination logic
    const start = 1;
    const end = Math.min(filteredDrivers.length, 50);
    const total = filteredDrivers.length;
    
    document.getElementById('showingStart').textContent = start;
    document.getElementById('showingEnd').textContent = end;
    document.getElementById('totalDrivers').textContent = total;
}

// UI interaction functions
function startBulkProcessing() {
    const selected = document.querySelectorAll('.driver-checkbox:checked');
    if (selected.length === 0) {
        showAlert('warning', 'Please select drivers to process');
        return;
    }
    
    if (confirm(`Start bulk OCR processing for ${selected.length} driver(s)?`)) {
        processingQueue = Array.from(selected).map(cb => parseInt(cb.value));
        isProcessing = true;
        document.getElementById('bulkProgressOverlay').style.display = 'flex';
        processBulkQueue();
    }
}

function pauseProcessing() {
    isProcessing = false;
    updateRealTimeStatus();
    showAlert('info', 'Processing paused');
}

function showSystemSettings() {
    $('#systemSettingsModal').modal('show');
}

function exportOCRReport() {
    window.open('{{ route("admin.drivers.index") }}?export=ocr_report', '_blank');
}

function reprocessFailed() {
    const failedDrivers = filteredDrivers.filter(d => d.ocr_verification_status === 'failed');
    if (failedDrivers.length === 0) {
        showAlert('info', 'No failed drivers to reprocess');
        return;
    }
    
    if (confirm(`Reprocess ${failedDrivers.length} failed driver(s)?`)) {
        processingQueue = failedDrivers.map(d => d.id);
        isProcessing = true;
        document.getElementById('bulkProgressOverlay').style.display = 'flex';
        processBulkQueue();
    }
}

function refreshDriversList() { 
    loadDriversData(); 
    loadOCRStatistics();
    showAlert('success', 'Data refreshed');
}

function toggleSelectAll() {
    const selectAll = document.getElementById('selectAllDrivers');
    const checkboxes = document.querySelectorAll('.driver-checkbox');
    
    checkboxes.forEach(cb => {
        cb.checked = selectAll.checked;
    });
    
    updateSelectedDrivers();
}

function updateSelectedDrivers() {
    const selected = document.querySelectorAll('.driver-checkbox:checked');
    selectedDrivers = Array.from(selected).map(cb => parseInt(cb.value));
    
    document.getElementById('selectedCount').textContent = selectedDrivers.length;
    
    // Enable/disable bulk action buttons
    const bulkButtons = document.querySelectorAll('#bulkVerifyBtn, #bulkOverrideBtn, #bulkReprocessBtn');
    bulkButtons.forEach(btn => {
        btn.disabled = selectedDrivers.length === 0;
    });
}

function viewOCRDetails(id) {
    const driver = driversData.find(d => d.id === id);
    if (!driver) return;
    
    $('#ocrDetailsModal').modal('show');
    loadDriverOCRDetails(id);
}

function processDriverOCR(id) {
    if (confirm('Process OCR verification for this driver?')) {
        fetch(`/admin/drivers/${id}/process-ocr`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', 'OCR processing started');
                setTimeout(() => loadDriversData(), 2000);
            } else {
                showAlert('error', data.message || 'Processing failed');
            }
        })
        .catch(error => {
            showAlert('error', 'Error processing OCR');
        });
    }
}

function showOCROverride(id) {
    document.getElementById('overrideDriverId').value = id;
    $('#ocrOverrideModal').modal('show');
}

function bulkVerify() {
    if (selectedDrivers.length === 0) return;
    
    if (confirm(`Mark ${selectedDrivers.length} driver(s) as verified?`)) {
        bulkUpdateStatus('passed');
    }
}

function bulkOverride() {
    if (selectedDrivers.length === 0) return;
    
    showAlert('info', 'Use individual override for detailed control');
}

function bulkReprocess() {
    if (selectedDrivers.length === 0) return;
    
    if (confirm(`Reprocess OCR for ${selectedDrivers.length} driver(s)?`)) {
        processingQueue = [...selectedDrivers];
        isProcessing = true;
        document.getElementById('bulkProgressOverlay').style.display = 'flex';
        processBulkQueue();
    }
}

function cancelBulkProcessing() {
    isProcessing = false;
    processingQueue = [];
    document.getElementById('bulkProgressOverlay').style.display = 'none';
    updateRealTimeStatus();
    showAlert('info', 'Bulk processing cancelled');
}

function updateAnalytics() {
    const timeframe = document.getElementById('analyticsTimeframe').value;
    // Update chart data based on timeframe
    if (performanceChart) {
        performanceChart.update();
    }
}

function filterByStatus(status) { 
    filterDrivers(status); 
}

function showProcessedDetails() {
    showAlert('info', 'Opening processed drivers details...');
    filterDrivers('all');
}

// Additional helper functions
function processBulkQueue() {
    if (!isProcessing || processingQueue.length === 0) {
        document.getElementById('bulkProgressOverlay').style.display = 'none';
        isProcessing = false;
        return;
    }
    
    const total = processingQueue.length;
    const processed = total - processingQueue.length;
    const progress = (processed / total) * 100;
    
    document.getElementById('bulkProgressBar').style.width = progress + '%';
    document.getElementById('bulkProgressText').textContent = `${processed}/${total}`;
    
    // Process next item
    setTimeout(() => {
        processingQueue.shift();
        processBulkQueue();
    }, 2000);
}

function bulkUpdateStatus(status) {
    fetch('/admin/drivers/bulk-update-ocr-status', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            driver_ids: selectedDrivers,
            status: status
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', `Updated ${selectedDrivers.length} driver(s)`);
            loadDriversData();
            loadOCRStatistics();
        } else {
            showAlert('error', data.message || 'Update failed');
        }
    })
    .catch(error => {
        showAlert('error', 'Error updating status');
    });
}

function loadDriverOCRDetails(driverId) {
    const content = document.getElementById('ocrDetailsContent');
    content.innerHTML = '<div class="text-center py-5"><div class="processing-spinner"></div><div class="mt-2">Loading OCR details...</div></div>';
    
    fetch(`/admin/drivers/${driverId}/ocr-details`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderOCRDetails(data.driver);
        } else {
            content.innerHTML = '<div class="alert alert-danger">Failed to load OCR details</div>';
        }
    })
    .catch(error => {
        content.innerHTML = '<div class="alert alert-danger">Error loading OCR details</div>';
    });
}

function renderOCRDetails(driver) {
    const content = document.getElementById('ocrDetailsContent');
    content.innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <h6>NIN Verification</h6>
                <p>Score: ${driver.nin_ocr_match_score || 0}%</p>
                <p>Status: ${driver.nin_verified_at ? 'Processed' : 'Pending'}</p>
            </div>
            <div class="col-md-6">
                <h6>License Verification</h6>
                <p>Score: ${driver.frsc_ocr_match_score || 0}%</p>
                <p>Status: ${driver.frsc_verified_at ? 'Processed' : 'Pending'}</p>
            </div>
        </div>
    `;
}
</script>
@endsection