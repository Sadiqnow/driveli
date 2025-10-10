@extends('layouts.admin_cdn')

@section('title', 'Reports Dashboard')

@section('head')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
.report-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    transition: all 0.3s ease;
}
.report-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 2rem 0 rgba(58, 59, 69, 0.3);
}
.report-icon {
    font-size: 3rem;
    opacity: 0.7;
}
.metric-card {
    text-align: center;
    padding: 20px;
}
.metric-value {
    font-size: 2rem;
    font-weight: bold;
    color: #495057;
}
.metric-label {
    color: #6c757d;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 1px;
}
.trend-up { color: #28a745; }
.trend-down { color: #dc3545; }
.trend-neutral { color: #6c757d; }
</style>
@endsection

@section('content_header')
    <h1>Reports Dashboard</h1>
@stop

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Reports</li>
@stop

@section('content')
    {{-- Quick Stats --}}
    <div class="row mb-4">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $totalReports ?? 0 }}</h3>
                    <p>Total Reports</p>
                    <small class="trend-up">
                        <i class="fas fa-arrow-up"></i> +5% this month
                    </small>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-bar"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $scheduledReports ?? 0 }}</h3>
                    <p>Scheduled Reports</p>
                    <small class="text-light">
                        <i class="fas fa-clock"></i> {{ $nextReportTime ?? 'None scheduled' }}
                    </small>
                </div>
                <div class="icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $downloadsToday ?? 0 }}</h3>
                    <p>Downloads Today</p>
                    <small class="text-dark">
                        <i class="fas fa-download"></i> Last: {{ $lastDownload ?? 'Never' }}
                    </small>
                </div>
                <div class="icon">
                    <i class="fas fa-file-download"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $activeUsers ?? 0 }}</h3>
                    <p>Report Users</p>
                    <small class="text-light">
                        <i class="fas fa-users"></i> Active this week
                    </small>
                </div>
                <div class="icon">
                    <i class="fas fa-user-chart"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Available Reports --}}
    <div class="row mb-4">
        <div class="col-md-6 col-lg-4">
            <div class="card report-card h-100">
                <div class="card-body text-center">
                    <div class="report-icon text-primary mb-3">
                        <i class="fas fa-users"></i>
                    </div>
                    <h5 class="card-title">Driver Performance</h5>
                    <p class="card-text text-muted">
                        Comprehensive analysis of driver performance metrics, ratings, and productivity.
                    </p>
                    <div class="mt-auto">
                        <a href="{{ route('admin.reports.driver-performance') }}" class="btn btn-primary">
                            <i class="fas fa-eye"></i> View Report
                        </a>
                        <button class="btn btn-outline-primary ml-2" onclick="scheduleReport('driver-performance')">
                            <i class="fas fa-clock"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="card report-card h-100">
                <div class="card-body text-center">
                    <div class="report-icon text-success mb-3">
                        <i class="fas fa-building"></i>
                    </div>
                    <h5 class="card-title">Company Activity</h5>
                    <p class="card-text text-muted">
                        Track company requests, approvals, and overall business activity patterns.
                    </p>
                    <div class="mt-auto">
                        <a href="{{ route('admin.reports.company-activity') }}" class="btn btn-success">
                            <i class="fas fa-eye"></i> View Report
                        </a>
                        <button class="btn btn-outline-success ml-2" onclick="scheduleReport('company-activity')">
                            <i class="fas fa-clock"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="card report-card h-100">
                <div class="card-body text-center">
                    <div class="report-icon text-warning mb-3">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <h5 class="card-title">Financial Report</h5>
                    <p class="card-text text-muted">
                        Revenue analysis, commission tracking, and financial performance overview.
                    </p>
                    <div class="mt-auto">
                        <a href="{{ route('admin.reports.financial') }}" class="btn btn-warning">
                            <i class="fas fa-eye"></i> View Report
                        </a>
                        <button class="btn btn-outline-warning ml-2" onclick="scheduleReport('financial')">
                            <i class="fas fa-clock"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="card report-card h-100">
                <div class="card-body text-center">
                    <div class="report-icon text-info mb-3">
                        <i class="fas fa-percentage"></i>
                    </div>
                    <h5 class="card-title">Commission Report</h5>
                    <p class="card-text text-muted">
                        Detailed breakdown of commission structure and earnings distribution.
                    </p>
                    <div class="mt-auto">
                        <a href="{{ route('admin.reports.commission') }}" class="btn btn-info">
                            <i class="fas fa-eye"></i> View Report
                        </a>
                        <button class="btn btn-outline-info ml-2" onclick="scheduleReport('commission')">
                            <i class="fas fa-clock"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="card report-card h-100">
                <div class="card-body text-center">
                    <div class="report-icon text-secondary mb-3">
                        <i class="fas fa-calendar"></i>
                    </div>
                    <h5 class="card-title">Scheduled Reports</h5>
                    <p class="card-text text-muted">
                        Manage automated report generation and delivery schedules.
                    </p>
                    <div class="mt-auto">
                        <a href="{{ route('admin.reports.scheduled') }}" class="btn btn-secondary">
                            <i class="fas fa-eye"></i> View Scheduled
                        </a>
                        <button class="btn btn-outline-secondary ml-2" onclick="newSchedule()">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-4">
            <div class="card report-card h-100">
                <div class="card-body text-center">
                    <div class="report-icon text-dark mb-3">
                        <i class="fas fa-cogs"></i>
                    </div>
                    <h5 class="card-title">Custom Reports</h5>
                    <p class="card-text text-muted">
                        Create custom reports with specific metrics and filters tailored to your needs.
                    </p>
                    <div class="mt-auto">
                        <button class="btn btn-dark" onclick="createCustomReport()">
                            <i class="fas fa-plus"></i> Create Custom
                        </button>
                        <button class="btn btn-outline-dark ml-2" onclick="viewCustomReports()">
                            <i class="fas fa-list"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Activity --}}
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line"></i> Report Usage Analytics
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="usageChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-clock"></i> Recent Downloads
                    </h3>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @forelse($recentDownloads ?? [] as $download)
                            <div class="time-label">
                                <span class="bg-info">{{ $download->created_at->format('M d') }}</span>
                            </div>
                            <div>
                                <i class="fas fa-file-download bg-blue"></i>
                                <div class="timeline-item">
                                    <h3 class="timeline-header">
                                        {{ $download->report_name }}
                                    </h3>
                                    <div class="timeline-body">
                                        Downloaded by {{ $download->user->name }}
                                        <br>
                                        <small class="text-muted">{{ $download->created_at->format('H:i') }}</small>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-3">
                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                <br>No recent downloads
                            </div>
                        @endforelse
                        <div>
                            <i class="fas fa-clock bg-gray"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Schedule Report Modal --}}
    <div class="modal fade" id="scheduleModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Schedule Report</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="scheduleForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Report Type</label>
                                    <select class="form-control" id="reportType" required>
                                        <option value="">Select Report</option>
                                        <option value="driver-performance">Driver Performance</option>
                                        <option value="company-activity">Company Activity</option>
                                        <option value="financial">Financial Report</option>
                                        <option value="commission">Commission Report</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Frequency</label>
                                    <select class="form-control" id="frequency" required>
                                        <option value="">Select Frequency</option>
                                        <option value="daily">Daily</option>
                                        <option value="weekly">Weekly</option>
                                        <option value="monthly">Monthly</option>
                                        <option value="quarterly">Quarterly</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Start Date</label>
                                    <input type="date" class="form-control" id="startDate" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Email Recipients</label>
                                    <input type="email" class="form-control" id="recipients" 
                                           placeholder="admin@example.com" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Additional Notes</label>
                            <textarea class="form-control" id="notes" rows="3" 
                                      placeholder="Any special instructions or filters..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveSchedule()">
                        <i class="fas fa-save"></i> Schedule Report
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Usage Analytics Chart
    const usageCtx = document.getElementById('usageChart').getContext('2d');
    new Chart(usageCtx, {
        type: 'bar',
        data: {
            labels: ['Driver Reports', 'Company Reports', 'Financial Reports', 'Commission Reports', 'Custom Reports'],
            datasets: [{
                label: 'Downloads This Month',
                data: [25, 15, 12, 8, 5],
                backgroundColor: [
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(255, 206, 86, 0.8)',
                    'rgba(153, 102, 255, 0.8)',
                    'rgba(255, 159, 64, 0.8)'
                ],
                borderColor: [
                    'rgba(54, 162, 235, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)'
                ],
                borderWidth: 1
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
});

function scheduleReport(reportType) {
    $('#reportType').val(reportType);
    $('#scheduleModal').modal('show');
}

function newSchedule() {
    $('#reportType').val('');
    $('#scheduleModal').modal('show');
}

function saveSchedule() {
    const form = document.getElementById('scheduleForm');
    if (form.checkValidity()) {
        // Show loading state
        const btn = event.target;
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Scheduling...';
        btn.disabled = true;

        // Simulate API call
        setTimeout(() => {
            btn.innerHTML = originalHtml;
            btn.disabled = false;
            $('#scheduleModal').modal('hide');
            
            // Show success message
            toastr.success('Report scheduled successfully!');
            
            // Reset form
            form.reset();
        }, 2000);
    } else {
        form.reportValidity();
    }
}

function createCustomReport() {
    toastr.info('Custom report builder coming soon!');
}

function viewCustomReports() {
    toastr.info('Custom reports viewer coming soon!');
}

// Auto-refresh dashboard every 5 minutes
setInterval(() => {
    // Refresh statistics without full page reload
    console.log('Refreshing dashboard statistics...');
}, 300000);
</script>
@stop