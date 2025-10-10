@extends('layouts.admin_cdn')

@section('title', 'Driver Performance Report')

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
.performance-metric {
    text-align: center;
    padding: 20px;
}
.metric-value {
    font-size: 2.5rem;
    font-weight: bold;
    color: #495057;
}
.metric-label {
    color: #6c757d;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 1px;
}
.performance-badge {
    font-size: 0.8rem;
    padding: 4px 8px;
}
</style>
@endsection

@section('content_header')
    <h1>Driver Performance Report</h1>
@stop

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Driver Performance</li>
@stop

@section('content')
    {{-- Summary Cards --}}
    <div class="row mb-4">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $totalDrivers ?? 0 }}</h3>
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
                    <h3>{{ $activeDrivers ?? 0 }}</h3>
                    <p>Active Drivers</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-check"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $avgRating ?? '0.0' }}</h3>
                    <p>Average Rating</p>
                </div>
                <div class="icon">
                    <i class="fas fa-star"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $completedJobs ?? 0 }}</h3>
                    <p>Jobs Completed</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-filter"></i> Filters
            </h3>
        </div>
        <div class="card-body">
            <form method="GET" class="row">
                <div class="col-md-3">
                    <label>Date Range</label>
                    <select name="period" class="form-control">
                        <option value="last_30_days" {{ request('period') == 'last_30_days' ? 'selected' : '' }}>Last 30 Days</option>
                        <option value="last_3_months" {{ request('period') == 'last_3_months' ? 'selected' : '' }}>Last 3 Months</option>
                        <option value="last_6_months" {{ request('period') == 'last_6_months' ? 'selected' : '' }}>Last 6 Months</option>
                        <option value="last_year" {{ request('period') == 'last_year' ? 'selected' : '' }}>Last Year</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Location</label>
                    <select name="location" class="form-control">
                        <option value="">All Locations</option>
                        <option value="lagos" {{ request('location') == 'lagos' ? 'selected' : '' }}>Lagos</option>
                        <option value="abuja" {{ request('location') == 'abuja' ? 'selected' : '' }}>Abuja</option>
                        <option value="portharcourt" {{ request('location') == 'portharcourt' ? 'selected' : '' }}>Port Harcourt</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>&nbsp;</label>
                    <div class="d-flex">
                        <button type="submit" class="btn btn-primary mr-2">
                            <i class="fas fa-search"></i> Apply Filters
                        </button>
                        <a href="{{ route('admin.reports.driver-performance') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Performance Charts --}}
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line"></i> Performance Trends
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="performanceChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie"></i> Driver Distribution
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="distributionChart" width="400" height="400"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Top Performers --}}
    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-trophy"></i> Top Performers
            </h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Driver</th>
                            <th>Rating</th>
                            <th>Jobs Completed</th>
                            <th>Revenue Generated</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topPerformers ?? [] as $index => $driver)
                            <tr>
                                <td>
                                    <span class="badge badge-{{ $index < 3 ? 'warning' : 'light' }}">
                                        #{{ $index + 1 }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($driver->first_name . ' ' . $driver->last_name) }}&background=random" 
                                             class="img-circle mr-2" width="32" height="32" alt="Avatar">
                                        <div>
                                            <strong>{{ $driver->first_name }} {{ $driver->last_name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $driver->phone_number ?? 'N/A' }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="mr-1">{{ number_format($driver->rating ?? 0, 1) }}</span>
                                        <div>
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="fas fa-star {{ $i <= ($driver->rating ?? 0) ? 'text-warning' : 'text-muted' }}"></i>
                                            @endfor
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $driver->jobs_completed ?? 0 }}</td>
                                <td>₦{{ number_format($driver->revenue_generated ?? 0, 2) }}</td>
                                <td>
                                    <span class="badge badge-{{ $driver->status == 'active' ? 'success' : 'secondary' }}">
                                        {{ ucfirst($driver->status ?? 'inactive') }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.drivers.show', $driver->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x mb-2"></i>
                                    <br>No performance data available
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Export Options --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-download"></i> Export Options
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p class="text-muted mb-3">Download detailed performance reports in your preferred format:</p>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-success" onclick="exportReport('excel')">
                            <i class="fas fa-file-excel"></i> Export to Excel
                        </button>
                        <button type="button" class="btn btn-danger" onclick="exportReport('pdf')">
                            <i class="fas fa-file-pdf"></i> Export to PDF
                        </button>
                        <button type="button" class="btn btn-info" onclick="exportReport('csv')">
                            <i class="fas fa-file-csv"></i> Export to CSV
                        </button>
                    </div>
                </div>
                <div class="col-md-6">
                    <p class="text-muted mb-3">Schedule automated reports:</p>
                    <button type="button" class="btn btn-outline-primary" data-toggle="modal" data-target="#scheduleModal">
                        <i class="fas fa-calendar-alt"></i> Schedule Report
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Schedule Modal --}}
    <div class="modal fade" id="scheduleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Schedule Report</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p class="text-info">
                        <i class="fas fa-info-circle"></i>
                        Report scheduling functionality will be available soon!
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Performance Trends Chart
    const performanceCtx = document.getElementById('performanceChart').getContext('2d');
    new Chart(performanceCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Active Drivers',
                data: [12, 19, 15, 25, 22, 28],
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                tension: 0.1
            }, {
                label: 'Jobs Completed',
                data: [8, 15, 12, 18, 16, 22],
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.1)',
                tension: 0.1
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

    // Driver Distribution Chart
    const distributionCtx = document.getElementById('distributionChart').getContext('2d');
    new Chart(distributionCtx, {
        type: 'doughnut',
        data: {
            labels: ['Active', 'Inactive', 'Suspended'],
            datasets: [{
                data: [65, 25, 10],
                backgroundColor: [
                    '#28a745',
                    '#6c757d',
                    '#dc3545'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
});

function exportReport(format) {
    // Show loading state
    const btn = event.target;
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Exporting...';
    btn.disabled = true;

    // Simulate export process
    setTimeout(() => {
        btn.innerHTML = originalHtml;
        btn.disabled = false;
        
        // Show success message
        toastr.success('Report export will be available soon!');
    }, 2000);
}
</script>
@stop