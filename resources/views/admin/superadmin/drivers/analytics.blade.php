@extends('layouts.admin_master')

@section('title', 'Superadmin - Driver Analytics')

@section('content_header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Driver Analytics Dashboard</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.superadmin.dashboard') }}">Super Admin</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.superadmin.drivers.index') }}">Drivers</a></li>
                        <li class="breadcrumb-item active">Analytics</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid">
        <!-- Key Metrics Row -->
        <div class="row mb-4">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ number_format($analytics['total_drivers'] ?? 0) }}</h3>
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
                        <h3>{{ number_format($analytics['active_drivers'] ?? 0) }}</h3>
                        <p>Active Drivers</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ number_format($analytics['verification_rate'] ?? 0, 1) }}%</h3>
                        <p>Verification Rate</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ number_format($analytics['churn_rate'] ?? 0, 1) }}%</h3>
                        <p>Churn Rate</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Metrics -->
        <div class="row mb-4">
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

            <div class="col-lg-3 col-6">
                <div class="info-box">
                    <span class="info-box-icon bg-green"><i class="fas fa-briefcase"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Jobs</span>
                        <span class="info-box-number">{{ number_format($analytics['total_jobs'] ?? 0) }}</span>
                        <div class="progress">
                            <div class="progress-bar bg-green" style="width: 100%"></div>
                        </div>
                        <span class="progress-description">
                            All time total
                        </span>
                    </div>
                </div>
            </div>

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

            <div class="col-lg-3 col-6">
                <div class="info-box">
                    <span class="info-box-icon bg-red"><i class="fas fa-user-plus"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">New This Month</span>
                        <span class="info-box-number">{{ $analytics['new_this_month'] ?? 0 }}</span>
                        <div class="progress">
                            <div class="progress-bar bg-red" style="width: {{ min(($analytics['new_this_month'] ?? 0) * 10, 100) }}%"></div>
                        </div>
                        <span class="progress-description">
                            {{ now()->format('M Y') }} registrations
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-pie mr-1"></i>
                            Driver Status Distribution
                        </h3>
                    </div>
                    <div class="card-body">
                        <canvas id="statusChart" style="height: 250px;"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-line mr-1"></i>
                            Verification Trends
                        </h3>
                    </div>
                    <div class="card-body">
                        <canvas id="verificationChart" style="height: 250px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Demographics and Performance -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-users mr-1"></i>
                            Driver Demographics
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <h6>Gender Distribution</h6>
                                @foreach($analytics['gender_distribution'] ?? [] as $gender => $count)
                                    <div class="d-flex justify-content-between">
                                        <span>{{ ucfirst($gender) }}</span>
                                        <span class="badge badge-secondary">{{ $count }}</span>
                                    </div>
                                @endforeach
                            </div>
                            <div class="col-6">
                                <h6>Experience Levels</h6>
                                @foreach($analytics['experience_distribution'] ?? [] as $level => $count)
                                    <div class="d-flex justify-content-between">
                                        <span>{{ ucfirst(str_replace('_', ' ', $level)) }}</span>
                                        <span class="badge badge-info">{{ $count }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-trophy mr-1"></i>
                            Top Performers
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @forelse($analytics['top_performers'] ?? [] as $performer)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $performer['name'] ?? 'Unknown' }}</strong>
                                        <br><small class="text-muted">{{ $performer['driver_id'] ?? '' }}</small>
                                    </div>
                                    <div class="text-right">
                                        <span class="badge badge-warning">{{ $performer['score'] ?? 0 }}★</span>
                                        <br><small class="text-muted">{{ $performer['jobs'] ?? 0 }} jobs</small>
                                    </div>
                                </div>
                            @empty
                                <div class="list-group-item text-center text-muted">
                                    No performance data available
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity and Trends -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-calendar-alt mr-1"></i>
                            Registration Trends
                        </h3>
                    </div>
                    <div class="card-body">
                        <canvas id="registrationChart" style="height: 250px;"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-clock mr-1"></i>
                            Activity Patterns
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6 text-center">
                                <div class="text-primary">
                                    <h4>{{ $analytics['active_today'] ?? 0 }}</h4>
                                    <small>Active Today</small>
                                </div>
                            </div>
                            <div class="col-6 text-center">
                                <div class="text-info">
                                    <h4>{{ $analytics['online_now'] ?? 0 }}</h4>
                                    <small>Online Now</small>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-6 text-center">
                                <div class="text-success">
                                    <h4>{{ $analytics['completed_today'] ?? 0 }}</h4>
                                    <small>Jobs Today</small>
                                </div>
                            </div>
                            <div class="col-6 text-center">
                                <div class="text-warning">
                                    <h4>{{ number_format($analytics['avg_session_time'] ?? 0, 1) }}m</h4>
                                    <small>Avg Session</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Analytics Tables -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-map-marker-alt mr-1"></i>
                            Geographic Distribution
                        </h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>State</th>
                                        <th>Count</th>
                                        <th>Percentage</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($analytics['state_distribution'] ?? [] as $state => $count)
                                        <tr>
                                            <td>{{ $state }}</td>
                                            <td>{{ $count }}</td>
                                            <td>{{ number_format(($count / ($analytics['total_drivers'] ?? 1)) * 100, 1) }}%</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">No geographic data available</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-flag mr-1"></i>
                            Issues & Flags
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <div class="text-center">
                                    <h4 class="text-danger">{{ $analytics['flagged_drivers'] ?? 0 }}</h4>
                                    <small>Flagged Drivers</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center">
                                    <h4 class="text-warning">{{ $analytics['pending_reviews'] ?? 0 }}</h4>
                                    <small>Pending Reviews</small>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-6">
                                <div class="text-center">
                                    <h4 class="text-info">{{ $analytics['incomplete_profiles'] ?? 0 }}</h4>
                                    <small>Incomplete Profiles</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-center">
                                    <h4 class="text-secondary">{{ $analytics['expired_documents'] ?? 0 }}</h4>
                                    <small>Expired Documents</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Export Options -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Export Analytics</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <button type="button" class="btn btn-primary btn-block" onclick="exportAnalytics('pdf')">
                                    <i class="fas fa-file-pdf"></i> Export PDF Report
                                </button>
                            </div>
                            <div class="col-md-3">
                                <button type="button" class="btn btn-success btn-block" onclick="exportAnalytics('excel')">
                                    <i class="fas fa-file-excel"></i> Export Excel
                                </button>
                            </div>
                            <div class="col-md-3">
                                <button type="button" class="btn btn-info btn-block" onclick="exportAnalytics('csv')">
                                    <i class="fas fa-file-csv"></i> Export CSV
                                </button>
                            </div>
                            <div class="col-md-3">
                                <button type="button" class="btn btn-warning btn-block" onclick="scheduleReport()">
                                    <i class="fas fa-calendar"></i> Schedule Report
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
.chart-container {
    position: relative;
    height: 250px;
}

.list-group-item {
    border: none;
    padding: 1rem;
}

.table th {
    border-top: none;
    font-weight: 600;
    font-size: 0.875rem;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    initializeCharts();
});

function initializeCharts() {
    // Status Distribution Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Active', 'Inactive', 'Pending', 'Suspended'],
            datasets: [{
                data: [
                    {{ $analytics['active_drivers'] ?? 0 }},
                    {{ $analytics['inactive_drivers'] ?? 0 }},
                    {{ $analytics['pending_drivers'] ?? 0 }},
                    {{ $analytics['suspended_drivers'] ?? 0 }}
                ],
                backgroundColor: [
                    '#28a745',
                    '#6c757d',
                    '#ffc107',
                    '#dc3545'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            legend: {
                position: 'bottom'
            }
        }
    });

    // Verification Trends Chart
    const verificationCtx = document.getElementById('verificationChart').getContext('2d');
    new Chart(verificationCtx, {
        type: 'line',
        data: {
            labels: {{ Js::from($analytics['verification_trends']->pluck('date') ?? []) }},
            datasets: [{
                label: 'Verifications',
                data: {{ Js::from($analytics['verification_trends']->pluck('count') ?? []) }},
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Registration Trends Chart
    const registrationCtx = document.getElementById('registrationChart').getContext('2d');
    new Chart(registrationCtx, {
        type: 'bar',
        data: {
            labels: {{ Js::from($analytics['registration_trends']->pluck('month') ?? []) }},
            datasets: [{
                label: 'Registrations',
                data: {{ Js::from($analytics['registration_trends']->pluck('count') ?? []) }},
                backgroundColor: '#28a745'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

function exportAnalytics(format) {
    const url = '{{ route("admin.superadmin.drivers.analytics-export") }}?format=' + format;
    window.open(url, '_blank');
}

function scheduleReport() {
    // Implement scheduled report functionality
    alert('Scheduled reporting feature coming soon!');
}
</script>
@endpush
