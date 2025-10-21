@extends('layouts.admin_cdn')

@section('title', 'Admin Dashboard')

@section('head')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
.stat-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 2rem 0 rgba(58, 59, 69, 0.3);
}

.stat-number {
    font-size: 2.5rem;
    font-weight: bold;
    line-height: 1;
}

.stat-icon {
    font-size: 3rem;
    opacity: 0.3;
}

.chart-container {
    position: relative;
    height: 300px;
}

.activity-item {
    padding: 10px 0;
    border-bottom: 1px solid #e3e6f0;
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.quick-actions {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 15px;
    color: white;
}

.quick-actions .btn {
    border: 1px solid rgba(255,255,255,0.3);
    color: white;
    transition: all 0.3s ease;
}

.quick-actions .btn:hover {
    background: rgba(255,255,255,0.2);
    border-color: rgba(255,255,255,0.5);
}

.progress-ring {
    width: 120px;
    height: 120px;
}

.progress-ring-circle {
    fill: transparent;
    stroke: #e9ecef;
    stroke-width: 8;
}

.progress-ring-progress {
    fill: transparent;
    stroke: #28a745;
    stroke-width: 8;
    stroke-linecap: round;
    transform: rotate(-90deg);
    transform-origin: center;
    transition: stroke-dasharray 0.5s ease;
}

.real-time-indicator {
    display: inline-block;
    width: 8px;
    height: 8px;
    background: #28a745;
    border-radius: 50%;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7); }
    70% { box-shadow: 0 0 0 10px rgba(40, 167, 69, 0); }
    100% { box-shadow: 0 0 0 0 rgba(40, 167, 69, 0); }
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
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                        <span class="real-time-indicator ml-2" title="Live Data"></span>
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Top Statistics Cards -->
            <div class="row">
                <!-- Total Drivers -->
                @if(\App\Helpers\PermissionHelper::hasPermission('view_drivers'))
                <div class="col-lg-3 col-md-6">
                    <div class="card stat-card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="stat-number" id="totalDrivers">{{ $stats['total_drivers'] }}</div>
                                    <div class="text-uppercase">Total Drivers</div>
                                    <small>+{{ $stats['drivers_today'] }} today</small>
                                </div>
                                <div class="stat-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('admin.superadmin.drivers.index') }}" class="text-white">
                                View All <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Active Drivers -->
                @if(\App\Helpers\PermissionHelper::hasPermission('view_drivers'))
                <div class="col-lg-3 col-md-6">
                    <div class="card stat-card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="stat-number" id="activeDrivers">{{ $stats['active_drivers'] }}</div>
                                    <div class="text-uppercase">Active Drivers</div>
                                    <small>{{ number_format(($stats['active_drivers'] / max($stats['total_drivers'], 1)) * 100, 1) }}% of total</small>
                                </div>
                                <div class="stat-icon">
                                    <i class="fas fa-user-check"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('admin.superadmin.drivers.index', ['status' => 'active']) }}" class="text-white">
                                View Active <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Verified Drivers -->
                @if(\App\Helpers\PermissionHelper::hasPermission('verify_documents'))
                <div class="col-lg-3 col-md-6">
                    <div class="card stat-card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="stat-number" id="verifiedDrivers">{{ $stats['verified_drivers'] }}</div>
                                    <div class="text-uppercase">Verified</div>
                                    <small>+{{ $stats['verifications_today'] }} today</small>
                                </div>
                                <div class="stat-icon">
                                    <i class="fas fa-certificate"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('admin.superadmin.drivers.verification') }}" class="text-white">
                                View Verification <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Pending Verifications -->
                @if(\App\Helpers\PermissionHelper::hasPermission('verify_documents'))
                <div class="col-lg-3 col-md-6">
                    <div class="card stat-card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="stat-number" id="pendingVerifications">{{ $stats['pending_verifications'] }}</div>
                                    <div class="text-uppercase">Pending</div>
                                    <small>Need attention</small>
                                </div>
                                <div class="stat-icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('admin.superadmin.drivers.verification') }}?type=pending" class="text-white">
                                Review Pending <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
                @endif

                <!-- System Analytics - SuperAdmin Only -->
                @if(\App\Helpers\PermissionHelper::hasRole('super_admin'))
                <div class="col-lg-3 col-md-6">
                    <div class="card stat-card bg-danger text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <div class="stat-number">{{ $stats['system_users'] ?? 0 }}</div>
                                    <div class="text-uppercase">System Users</div>
                                    <small>All roles combined</small>
                                </div>
                                <div class="stat-icon">
                                    <i class="fas fa-server"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('admin.superadmin.audit-logs') }}" class="text-white">
                                View Audit Logs <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Charts and Analytics Row -->
            <div class="row">
                <!-- Driver Registration Trend -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-line mr-1"></i>
                                Driver Registrations (Last 30 Days)
                            </h3>
                            <div class="card-tools">
                                <button class="btn btn-sm btn-primary" onclick="refreshCharts()">
                                    <i class="fas fa-sync"></i> Refresh
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="registrationChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="col-lg-4">
                    <div class="card quick-actions">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-bolt mr-1"></i>
                                Quick Actions
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="{{ route('admin.superadmin.drivers.create') }}" class="btn btn-block">
                                    <i class="fas fa-user-plus"></i> Add New Driver
                                </a>                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 
                                <a href="{{ route('admin.superadmin.drivers.verification') }}" class="btn btn-block">
                                    <i class="fas fa-clipboard-check"></i> Review Verifications
                                </a>
                                <a href="{{ route('admin.superadmin.drivers.ocr-verification') }}" class="btn btn-block">
                                    <i class="fas fa-robot"></i> OCR Dashboard
                                </a>
                                <a href="{{ route('admin.superadmin.drivers.analytics') }}" class="btn btn-block">
                                    <i class="fas fa-chart-bar"></i> Analytics
                                </a>
                                <a href="{{ route('admin.superadmin.drivers.bulk-operations') }}" class="btn btn-block">
                                    <i class="fas fa-tasks"></i> Bulk Operations
                                </a>
                                <a href="{{ route('admin.superadmin.drivers.export') }}" class="btn btn-block">
                                    <i class="fas fa-download"></i> Export Data
                                </a>
                            </div>
                            
                            <!-- System Health -->
                            <div class="mt-4 pt-3 border-top border-light">
                                <h6>System Health</h6>
                                <div class="d-flex justify-content-between text-sm">
                                    <span>Database</span>
                                    <span><i class="fas fa-circle text-success"></i> Online</span>
                                </div>
                                <div class="d-flex justify-content-between text-sm">
                                    <span>OCR Service</span>
                                    <span><i class="fas fa-circle text-success"></i> Active</span>
                                </div>
                                <div class="d-flex justify-content-between text-sm">
                                    <span>File Storage</span>
                                    <span><i class="fas fa-circle text-success"></i> Available</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Statistics Row -->
            <div class="row">
                <!-- Verification Status Breakdown -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-pie mr-1"></i>
                                Verification Status
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="verificationChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- OCR Statistics -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-robot mr-1"></i>
                                OCR Verification Status
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="ocrChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity and Performance -->
            <div class="row">
                <!-- Recent Activity -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-history mr-1"></i>
                                Recent Activity
                            </h3>
                            <div class="card-tools">
                                <span class="badge badge-secondary" id="activityCount">{{ count($recentActivity) }}</span>
                            </div>
                        </div>
                        <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                            <div id="activityContainer">
                                @forelse($recentActivity as $activity)
                                <div class="activity-item">
                                    <div class="d-flex align-items-center">
                                        <div class="activity-icon bg-{{ $activity['color'] }} mr-3">
                                            <i class="{{ $activity['icon'] }}"></i>
                                        </div>
                                        <div>
                                            <div class="font-weight-bold">{{ $activity['message'] }}</div>
                                            <small class="text-muted">
                                                {{ $activity['timestamp']->diffForHumans() }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <div class="text-center text-muted">
                                    <i class="fas fa-history fa-2x mb-2"></i>
                                    <p>No recent activity</p>
                                </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Performance Metrics -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-bar mr-1"></i>
                                Performance Metrics
                            </h3>
                        </div>
                        <div class="card-body">
                            <!-- Verification Rate -->
                            <div class="mb-4">
                                <div class="d-flex justify-content-between">
                                    <span>Verification Rate</span>
                                    <span id="verificationRate">
                                        {{ $stats['total_drivers'] > 0 ? number_format(($stats['verified_drivers'] / $stats['total_drivers']) * 100, 1) : 0 }}%
                                    </span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar bg-success" id="verificationProgress" 
                                         style="width: {{ $stats['total_drivers'] > 0 ? ($stats['verified_drivers'] / $stats['total_drivers']) * 100 : 0 }}%"></div>
                                </div>
                            </div>

                            <!-- OCR Success Rate -->
                            <div class="mb-4">
                                <div class="d-flex justify-content-between">
                                    <span>OCR Success Rate</span>
                                    <span id="ocrRate">
                                        {{ ($stats['ocr_processed'] > 0) ? number_format(($stats['ocr_passed'] / $stats['ocr_processed']) * 100, 1) : 0 }}%
                                    </span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar bg-info" id="ocrProgress" 
                                         style="width: {{ ($stats['ocr_processed'] > 0) ? ($stats['ocr_passed'] / $stats['ocr_processed']) * 100 : 0 }}%"></div>
                                </div>
                            </div>

                            <!-- Active Driver Ratio -->
                            <div class="mb-4">
                                <div class="d-flex justify-content-between">
                                    <span>Active Driver Ratio</span>
                                    <span id="activeRate">
                                        {{ $stats['total_drivers'] > 0 ? number_format(($stats['active_drivers'] / $stats['total_drivers']) * 100, 1) : 0 }}%
                                    </span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar bg-primary" id="activeProgress" 
                                         style="width: {{ $stats['total_drivers'] > 0 ? ($stats['active_drivers'] / $stats['total_drivers']) * 100 : 0 }}%"></div>
                                </div>
                            </div>

                            <!-- Monthly Growth -->
                            <div>
                                <div class="d-flex justify-content-between">
                                    <span>Monthly Growth</span>
                                    <span class="text-success">
                                        <i class="fas fa-arrow-up"></i> {{ $stats['drivers_this_month'] }}
                                    </span>
                                </div>
                                <small class="text-muted">New drivers this month</small>
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
// Chart instances
let registrationChart, verificationChart, ocrChart;

// Initialize dashboard
document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
    startRealTimeUpdates();
});

// Initialize all charts
function initializeCharts() {
    createRegistrationChart();
    createVerificationChart();
    createOCRChart();
}

// Create registration trend chart
function createRegistrationChart() {
    const ctx = document.getElementById('registrationChart').getContext('2d');
    const chartData = @json($chartData['driver_registrations']);
    
    registrationChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.map(item => item.date),
            datasets: [{
                label: 'New Registrations',
                data: chartData.map(item => item.count),
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
}

// Create verification status pie chart
function createVerificationChart() {
    const ctx = document.getElementById('verificationChart').getContext('2d');
    const chartData = @json($chartData['verification_breakdown']);
    
    verificationChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Verified', 'Pending', 'Rejected', 'Reviewing'],
            datasets: [{
                data: [
                    chartData.verified,
                    chartData.pending,
                    chartData.rejected,
                    chartData.reviewing
                ],
                backgroundColor: [
                    '#28a745',
                    '#ffc107',
                    '#dc3545',
                    '#17a2b8'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

// Create OCR status chart
function createOCRChart() {
    const ctx = document.getElementById('ocrChart').getContext('2d');
    const chartData = @json($chartData['ocr_stats']);
    
    ocrChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Passed', 'Failed', 'Pending'],
            datasets: [{
                data: [
                    chartData.passed,
                    chartData.failed,
                    chartData.pending
                ],
                backgroundColor: [
                    '#28a745',
                    '#dc3545',
                    '#ffc107'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
}

// Start real-time updates
function startRealTimeUpdates() {
    // Update every 30 seconds
    setInterval(updateDashboardStats, 30000);
}

// Update dashboard statistics
function updateDashboardStats() {
    fetch('{{ route("admin.dashboard.stats") }}')
    .then(response => response.json())
    .then(data => {
        // Update stat cards
        document.getElementById('totalDrivers').textContent = data.total_drivers;
        document.getElementById('activeDrivers').textContent = data.active_drivers;
        document.getElementById('verifiedDrivers').textContent = data.verified_drivers;
        document.getElementById('pendingVerifications').textContent = data.pending_verifications;
        
        // Update progress bars
        const verificationRate = data.total_drivers > 0 ? (data.verified_drivers / data.total_drivers) * 100 : 0;
        const activeRate = data.total_drivers > 0 ? (data.active_drivers / data.total_drivers) * 100 : 0;
        const ocrRate = data.ocr_processed > 0 ? (data.ocr_passed / data.ocr_processed) * 100 : 0;
        
        document.getElementById('verificationRate').textContent = verificationRate.toFixed(1) + '%';
        document.getElementById('verificationProgress').style.width = verificationRate + '%';
        
        document.getElementById('activeRate').textContent = activeRate.toFixed(1) + '%';
        document.getElementById('activeProgress').style.width = activeRate + '%';
        
        document.getElementById('ocrRate').textContent = ocrRate.toFixed(1) + '%';
        document.getElementById('ocrProgress').style.width = ocrRate + '%';
    })
    .catch(error => console.error('Error updating stats:', error));
}

// Refresh charts
function refreshCharts() {
    // Show loading indication
    const button = event.target.closest('button');
    const originalContent = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
    button.disabled = true;
    
    // Fetch updated chart data
    fetch('{{ route("admin.dashboard") }}?chart_data=true')
    .then(response => response.json())
    .then(data => {
        // Update registration chart
        if (registrationChart && data.driver_registrations) {
            registrationChart.data.labels = data.driver_registrations.map(item => item.date);
            registrationChart.data.datasets[0].data = data.driver_registrations.map(item => item.count);
            registrationChart.update();
        }
        
        // Update verification chart
        if (verificationChart && data.verification_breakdown) {
            verificationChart.data.datasets[0].data = [
                data.verification_breakdown.verified,
                data.verification_breakdown.pending,
                data.verification_breakdown.rejected,
                data.verification_breakdown.reviewing
            ];
            verificationChart.update();
        }
        
        // Update OCR chart
        if (ocrChart && data.ocr_stats) {
            ocrChart.data.datasets[0].data = [
                data.ocr_stats.passed,
                data.ocr_stats.failed,
                data.ocr_stats.pending
            ];
            ocrChart.update();
        }
        
        // Restore button
        button.innerHTML = originalContent;
        button.disabled = false;
        
        // Show success message
        showNotification('Charts updated successfully', 'success');
    })
    .catch(error => {
        console.error('Error refreshing charts:', error);
        button.innerHTML = originalContent;
        button.disabled = false;
        showNotification('Error refreshing charts', 'error');
    });
}

// Show notification
function showNotification(message, type) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
             style="top: 20px; right: 20px; z-index: 9999;">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', alertHtml);
    
    // Auto-remove after 3 seconds
    setTimeout(() => {
        document.querySelector('.alert-dismissible')?.remove();
    }, 3000);
}
</script>
@endsection