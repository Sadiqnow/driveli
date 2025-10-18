@extends('layouts.admin_master')

@section('title', 'Enhanced Admin Dashboard')

@section('head')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
/* Enhanced Dashboard Styles */
.dashboard-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2rem 0;
    margin: -1rem -1rem 2rem -1rem;
    border-radius: 0 0 15px 15px;
}

.stat-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    transition: all 0.3s ease;
    overflow: hidden;
    position: relative;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 2rem 0 rgba(58, 59, 69, 0.3);
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: linear-gradient(90deg, #007bff, #28a745, #ffc107, #dc3545);
}

.stat-number {
    font-size: 2.5rem;
    font-weight: bold;
    line-height: 1;
    margin-bottom: 0.5rem;
}

.stat-icon {
    font-size: 3rem;
    opacity: 0.3;
    position: absolute;
    top: 1rem;
    right: 1rem;
}

.chart-container {
    position: relative;
    height: 300px;
    margin-bottom: 1rem;
}

.quick-actions {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 15px;
    color: white;
    border: none;
}

.quick-actions .btn {
    border: 1px solid rgba(255,255,255,0.3);
    color: white;
    transition: all 0.3s ease;
    margin-bottom: 0.5rem;
}

.quick-actions .btn:hover {
    background: rgba(255,255,255,0.2);
    border-color: rgba(255,255,255,0.5);
    transform: translateY(-2px);
}

.activity-item {
    padding: 10px 0;
    border-bottom: 1px solid #e3e6f0;
    transition: all 0.3s ease;
}

.activity-item:hover {
    background-color: #f8f9fa;
    padding-left: 10px;
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
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.real-time-indicator {
    display: inline-block;
    width: 8px;
    height: 8px;
    background: #28a745;
    border-radius: 50%;
    animation: pulse 2s infinite;
    margin-left: 10px;
}

@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7); }
    70% { box-shadow: 0 0 0 10px rgba(40, 167, 69, 0); }
    100% { box-shadow: 0 0 0 0 rgba(40, 167, 69, 0); }
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

.system-health {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    border-radius: 15px;
    color: white;
    padding: 1.5rem;
    margin-top: 1rem;
}

.system-health h6 {
    color: white;
    margin-bottom: 1rem;
}

.system-health .status-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.system-health .status-item:last-child {
    margin-bottom: 0;
}

.metric-card {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    margin-bottom: 1.5rem;
    transition: all 0.3s ease;
}

.metric-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.25rem 2rem 0 rgba(58, 59, 69, 0.2);
}

.metric-title {
    font-size: 0.9rem;
    color: #6c757d;
    text-transform: uppercase;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.metric-value {
    font-size: 2rem;
    font-weight: bold;
    color: #495057;
    margin-bottom: 0.25rem;
}

.metric-change {
    font-size: 0.8rem;
}

.metric-change.positive {
    color: #28a745;
}

.metric-change.negative {
    color: #dc3545;
}

.metric-change.neutral {
    color: #6c757d;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .dashboard-header {
        padding: 1rem 0;
        margin: -1rem -1rem 1rem -1rem;
    }

    .stat-card {
        margin-bottom: 1rem;
    }

    .quick-actions .btn {
        width: 100%;
        margin-bottom: 0.5rem;
    }

    .chart-container {
        height: 250px;
    }
}

/* Loading animation */
.loading {
    position: relative;
    color: transparent !important;
}

.loading::after {
    content: '';
    position: absolute;
    width: 16px;
    height: 16px;
    top: 50%;
    left: 50%;
    margin-left: -8px;
    margin-top: -8px;
    border: 2px solid #ffffff;
    border-radius: 50%;
    border-top-color: transparent;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Notification styles */
.notification-toast {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
    min-width: 300px;
    max-width: 400px;
}

.alert-enhanced {
    border: none;
    border-radius: 10px;
    box-shadow: 0 0.25rem 1rem 0 rgba(58, 59, 69, 0.15);
}

/* Custom scrollbar */
::-webkit-scrollbar {
    width: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}
</style>
@endsection

@section('content')
<div class="content-wrapper">
    <!-- Enhanced Header -->
    <div class="dashboard-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-0">
                        <i class="fas fa-tachometer-alt"></i> Enhanced Dashboard
                        <span class="real-time-indicator" title="Live Data"></span>
                    </h1>
                    <p class="mb-0 opacity-75">Comprehensive overview of your driver management system</p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="d-flex justify-content-end gap-2">
                        <button class="btn btn-light btn-sm" onclick="refreshDashboard()">
                            <i class="fas fa-sync"></i> Refresh
                        </button>
                        <button class="btn btn-light btn-sm" onclick="exportDashboard()">
                            <i class="fas fa-download"></i> Export
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Enhanced Statistics Cards -->
            <div class="row">
                <!-- Total Drivers -->
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card stat-card bg-primary text-white h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="stat-number" id="totalDrivers">{{ $stats['total_drivers'] ?? 0 }}</div>
                                    <div class="text-uppercase font-weight-bold">Total Drivers</div>
                                    <small class="opacity-75">+{{ $stats['drivers_today'] ?? 0 }} today</small>
                                </div>
                                <div class="stat-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-0">
                            <a href="{{ route('admin.superadmin.drivers.index') }}" class="text-white text-decoration-none">
                                <small>View All <i class="fas fa-arrow-right ml-1"></i></small>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Active Drivers -->
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card stat-card bg-success text-white h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="stat-number" id="activeDrivers">{{ $stats['active_drivers'] ?? 0 }}</div>
                                    <div class="text-uppercase font-weight-bold">Active Drivers</div>
                                    <small class="opacity-75">{{ number_format((($stats['active_drivers'] ?? 0) / max(($stats['total_drivers'] ?? 0), 1)) * 100, 1) }}% of total</small>
                                </div>
                                <div class="stat-icon">
                                    <i class="fas fa-user-check"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-0">
                            <a href="{{ route('admin.drivers.index', ['status' => 'active']) }}" class="text-white text-decoration-none">
                                <small>View Active <i class="fas fa-arrow-right ml-1"></i></small>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Verified Drivers -->
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card stat-card bg-info text-white h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="stat-number" id="verifiedDrivers">{{ $stats['verified_drivers'] ?? 0 }}</div>
                                    <div class="text-uppercase font-weight-bold">Verified</div>
                                    <small class="opacity-75">+{{ $stats['verifications_today'] ?? 0 }} today</small>
                                </div>
                                <div class="stat-icon">
                                    <i class="fas fa-certificate"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-0">
                            <a href="{{ route('admin.superadmin.drivers.verification') }}" class="text-white text-decoration-none">
                                <small>View Verification <i class="fas fa-arrow-right ml-1"></i></small>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Pending Verifications -->
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card stat-card bg-warning text-white h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="stat-number" id="pendingVerifications">{{ $stats['pending_verifications'] ?? 0 }}</div>
                                    <div class="text-uppercase font-weight-bold">Pending</div>
                                    <small class="opacity-75">Requires attention</small>
                                </div>
                                <div class="stat-icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-0">
                            <a href="{{ route('admin.verification.dashboard') }}" class="text-white text-decoration-none">
                                <small>Review Pending <i class="fas fa-arrow-right ml-1"></i></small>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Metrics Row -->
            <div class="row">
                <!-- Admin Users -->
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="metric-card">
                        <div class="metric-title">Admin Users</div>
                        <div class="metric-value text-warning">{{ $stats['total_users'] ?? 0 }}</div>
                        <div class="metric-change positive">
                            <i class="fas fa-arrow-up"></i> {{ $stats['active_users'] ?? 0 }} active
                        </div>
                    </div>
                </div>

                <!-- Companies -->
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="metric-card">
                        <div class="metric-title">Total Companies</div>
                        <div class="metric-value text-primary">{{ $stats['total_companies'] ?? 0 }}</div>
                        <div class="metric-change neutral">
                            <i class="fas fa-building"></i> Active partnerships
                        </div>
                    </div>
                </div>

                <!-- Active Requests -->
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="metric-card">
                        <div class="metric-title">Active Requests</div>
                        <div class="metric-value text-secondary">{{ $stats['active_requests'] ?? 0 }}</div>
                        <div class="metric-change neutral">
                            <i class="fas fa-clipboard-list"></i> Driver requests
                        </div>
                    </div>
                </div>

                <!-- Commission Earned -->
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="metric-card">
                        <div class="metric-title">Commission Earned</div>
                        <div class="metric-value text-success">₦{{ number_format($stats['total_commission'] ?? 0, 0) }}</div>
                        <div class="metric-change positive">
                            <i class="fas fa-chart-line"></i> Total earnings
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts and Analytics Row -->
            <div class="row">
                <!-- Driver Registration Trend -->
                <div class="col-lg-8 mb-4">
                    <div class="card">
                        <div class="card-header bg-gradient-primary text-white">
                            <h3 class="card-title mb-0">
                                <i class="fas fa-chart-line mr-2"></i>
                                Driver Registrations (Last 30 Days)
                            </h3>
                            <div class="card-tools">
                                <button class="btn btn-light btn-sm" onclick="refreshCharts()">
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

                <!-- Quick Actions & System Health -->
                <div class="col-lg-4 mb-4">
                    <div class="card quick-actions h-100">
                        <div class="card-header bg-transparent border-0">
                            <h3 class="card-title mb-0 text-white">
                                <i class="fas fa-bolt mr-2"></i>
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
                                <a href="{{ route('admin.superadmin.drivers.bulk-operations') }}" class="btn btn-block">
                                    <i class="fas fa-tasks"></i> Bulk Operations
                                </a>
                                <a href="{{ route('admin.superadmin.drivers.analytics') }}" class="btn btn-block">
                                    <i class="fas fa-chart-bar"></i> Analytics
                                </a>
                                <a href="{{ route('admin.superadmin.drivers.export') }}" class="btn btn-block">
                                    <i class="fas fa-download"></i> Export Data
                                </a>
                            </div>

                            <!-- System Health -->
                            <div class="system-health">
                                <h6><i class="fas fa-server mr-1"></i> System Health</h6>
                                <div class="status-item">
                                    <span>Database</span>
                                    <span><i class="fas fa-circle text-success"></i> Online</span>
                                </div>
                                <div class="status-item">
                                    <span>OCR Service</span>
                                    <span><i class="fas fa-circle text-success"></i> Active</span>
                                </div>
                                <div class="status-item">
                                    <span>File Storage</span>
                                    <span><i class="fas fa-circle text-success"></i> Available</span>
                                </div>
                                <div class="status-item">
                                    <span>API Services</span>
                                    <span><i class="fas fa-circle text-success"></i> Operational</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Analytics Row -->
            <div class="row">
                <!-- Verification Status Breakdown -->
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header bg-gradient-info text-white">
                            <h3 class="card-title mb-0">
                                <i class="fas fa-chart-pie mr-2"></i>
                                Verification Status Breakdown
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
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header bg-gradient-teal text-white">
                            <h3 class="card-title mb-0">
                                <i class="fas fa-robot mr-2"></i>
                                OCR Verification Statistics
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

            <!-- Performance Metrics and Recent Activity -->
            <div class="row">
                <!-- Performance Metrics -->
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header bg-gradient-success text-white">
                            <h3 class="card-title mb-0">
                                <i class="fas fa-chart-bar mr-2"></i>
                                Performance Metrics
                            </h3>
                        </div>
                        <div class="card-body">
                            <!-- Verification Rate -->
                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="font-weight-bold">Verification Rate</span>
                                    <span class="badge badge-primary" id="verificationRate">
                                        {{ $stats['total_drivers'] > 0 ? number_format(($stats['verified_drivers'] / $stats['total_drivers']) * 100, 1) : 0 }}%
                                    </span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-primary" id="verificationProgress"
                                         style="width: {{ $stats['total_drivers'] > 0 ? ($stats['verified_drivers'] / $stats['total_drivers']) * 100 : 0 }}%"></div>
                                </div>
                            </div>

                            <!-- OCR Success Rate -->
                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="font-weight-bold">OCR Success Rate</span>
                                    <span class="badge badge-info" id="ocrRate">
                                        {{ ($stats['ocr_processed'] > 0) ? number_format(($stats['ocr_passed'] / $stats['ocr_processed']) * 100, 1) : 0 }}%
                                    </span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-info" id="ocrProgress"
                                         style="width: {{ ($stats['ocr_processed'] > 0) ? ($stats['ocr_passed'] / $stats['ocr_processed']) * 100 : 0 }}%"></div>
                                </div>
                            </div>

                            <!-- Active Driver Ratio -->
                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="font-weight-bold">Active Driver Ratio</span>
                                    <span class="badge badge-success" id="activeRate">
                                        {{ $stats['total_drivers'] > 0 ? number_format(($stats['active_drivers'] / $stats['total_drivers']) * 100, 1) : 0 }}%
                                    </span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-success" id="activeProgress"
                                         style="width: {{ $stats['total_drivers'] > 0 ? ($stats['active_drivers'] / $stats['total_drivers']) * 100 : 0 }}%"></div>
                                </div>
                            </div>

                            <!-- Monthly Growth -->
                            <div class="text-center">
                                <div class="metric-value text-primary">{{ $stats['drivers_this_month'] ?? 0 }}</div>
                                <small class="text-muted">New drivers this month</small>
                                <div class="mt-2">
                                    <span class="text-success">
                                        <i class="fas fa-arrow-up"></i> Monthly Growth
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="col-lg-6 mb-4">
                    <div class="card">
                        <div class="card-header bg-gradient-warning text-white">
                            <h3 class="card-title mb-0">
                                <i class="fas fa-history mr-2"></i>
                                Recent Activity
                            </h3>
                            <div class="card-tools">
                                <span class="badge badge-light" id="activityCount">{{ count($recentActivity ?? []) }}</span>
                            </div>
                        </div>
                        <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                            <div id="activityContainer">
                                @forelse($recentActivity ?? [] as $activity)
                                <div class="activity-item">
                                    <div class="d-flex align-items-center">
                                        <div class="activity-icon bg-{{ $activity['color'] ?? 'primary' }} mr-3">
                                            <i class="{{ $activity['icon'] ?? 'fas fa-info' }}"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="font-weight-bold">{{ $activity['message'] ?? 'Activity' }}</div>
                                            <small class="text-muted">
                                                {{ isset($activity['timestamp']) ? $activity['timestamp']->diffForHumans() : 'Recently' }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <div class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-3x mb-3 opacity-50"></i>
                                    <h5>No Recent Activity</h5>
                                    <p class="mb-0">Activity will appear here as drivers interact with the system.</p>
                                </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@section('scripts')
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
    const chartData = @json($chartData['driver_registrations'] ?? []);

    registrationChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.map(item => item.date ?? ''),
            datasets: [{
                label: 'New Registrations',
                data: chartData.map(item => item.count ?? 0),
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#007bff',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
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
                        stepSize: 1,
                        callback: function(value) {
                            return value;
                        }
                    },
                    grid: {
                        color: 'rgba(0,0,0,0.05)'
                    }
                },
                x: {
                    grid: {
                        color: 'rgba(0,0,0,0.05)'
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });
}

// Create verification status pie chart
function createVerificationChart() {
    const ctx = document.getElementById('verificationChart').getContext('2d');
    const chartData = @json($chartData['verification_breakdown'] ?? []);

    verificationChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Verified', 'Pending', 'Rejected', 'Reviewing'],
            datasets: [{
                data: [
                    chartData.verified ?? 0,
                    chartData.pending ?? 0,
                    chartData.rejected ?? 0,
                    chartData.reviewing ?? 0
                ],
                backgroundColor: [
                    '#28a745',
                    '#ffc107',
                    '#dc3545',
                    '#17a2b8'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                }
            }
        }
    });
}

// Create OCR status chart
function createOCRChart() {
    const ctx = document.getElementById('ocrChart').getContext('2d');
    const chartData = @json($chartData['ocr_stats'] ?? []);

    ocrChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Passed', 'Failed', 'Pending'],
            datasets: [{
                label: 'OCR Results',
                data: [
                    chartData.passed ?? 0,
                    chartData.failed ?? 0,
                    chartData.pending ?? 0
                ],
                backgroundColor: [
                    'rgba(40, 167, 69, 0.8)',
                    'rgba(220, 53, 69, 0.8)',
                    'rgba(255, 193, 7, 0.8)'
                ],
                borderColor: [
                    '#28a745',
                    '#dc3545',
                    '#ffc107'
                ],
                borderWidth: 1,
                borderRadius: 4,
                borderSkipped: false
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
                        stepSize: 1,
                        callback: function(value) {
                            return value;
                        }
                    },
                    grid: {
                        color: 'rgba(0,0,0,0.05)'
                    }
                },
                x: {
                    grid: {
                        color: 'rgba(0,0,0,0.05)'
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
        document.getElementById('totalDrivers').textContent = data.total_drivers ?? 0;
        document.getElementById('activeDrivers').textContent = data.active_drivers ?? 0;
        document.getElementById('verifiedDrivers').textContent = data.verified_drivers ?? 0;
        document.getElementById('pendingVerifications').textContent = data.pending_verifications ?? 0;

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

        // Update activity count
        if (data.activity_count !== undefined) {
            document.getElementById('activityCount').textContent = data.activity_count;
        }
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

// Refresh entire dashboard
function refreshDashboard() {
    const button = event.target.closest('button');
    const originalContent = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Refreshing...';
    button.disabled = true;

    location.reload();
}

// Export dashboard data
function exportDashboard() {
    const button = event.target.closest('button');
    const originalContent = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Exporting...';
    button.disabled = true;

    // Create export data
    const exportData = {
        stats: @json($stats),
        chartData: @json($chartData),
        exported_at: new Date().toISOString()
    };

    // Create and download file
    const dataStr = JSON.stringify(exportData, null, 2);
    const dataUri = 'data:application/json;charset=utf-8,'+ encodeURIComponent(dataStr);

    const exportFileDefaultName = `dashboard-export-${new Date().toISOString().split('T')[0]}.json`;

    const linkElement = document.createElement('a');
    linkElement.setAttribute('href', dataUri);
    linkElement.setAttribute('download', exportFileDefaultName);
    linkElement.click();

    // Restore button
    button.innerHTML = originalContent;
    button.disabled = false;

    showNotification('Dashboard data exported successfully', 'success');
}

// Show notification
function showNotification(message, type) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const iconClass = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-triangle';

    const alertHtml = `
        <div class="alert alert-enhanced ${alertClass} alert-dismissible fade show notification-toast" role="alert">
            <i class="${iconClass} mr-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;

    document.body.insertAdjacentHTML('beforeend', alertHtml);

    // Auto-remove after 3 seconds
    setTimeout(() => {
        document.querySelector('.notification-toast')?.remove();
    }, 3000);
}

// Auto-refresh dashboard data every 5 minutes (but not full reload)
setInterval(() => {
    updateDashboardStats();
}, 300000);
</script>
@endsection
