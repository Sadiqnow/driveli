@extends('admin.layouts.app')

@section('title', 'Feedback Analytics')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Employment Feedback Analytics</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3>{{ $stats['total_requested'] ?? 0 }}</h3>
                                    <p>Total Requests</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-envelope"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3>{{ $stats['total_submitted'] ?? 0 }}</h3>
                                    <p>Submitted</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-check"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3>{{ $stats['total_pending'] ?? 0 }}</h3>
                                    <p>Pending</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-danger">
                                <div class="inner">
                                    <h3>{{ $stats['total_flagged'] ?? 0 }}</h3>
                                    <p>Flagged</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-flag"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Response Rate -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-primary"><i class="fas fa-chart-line"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Response Rate</span>
                                    <span class="info-box-number">{{ $stats['response_rate'] ?? 0 }}%</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-secondary"><i class="fas fa-clock"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Avg Response Time</span>
                                    <span class="info-box-number">{{ $responseTimeAnalysis['average_days'] ?? 0 }} days</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Monthly Trends Chart -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Monthly Trends</h3>
                                    <div class="card-tools">
                                        <select id="trendPeriod" class="form-control form-control-sm" style="width: auto;">
                                            <option value="12months" selected>Last 12 Months</option>
                                            <option value="6months">Last 6 Months</option>
                                            <option value="3months">Last 3 Months</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <canvas id="trendsChart" style="height: 300px;"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Rating Distribution -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Performance Rating Distribution</h3>
                                </div>
                                <div class="card-body">
                                    <canvas id="ratingChart" style="height: 250px;"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Response Time Analysis</h3>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <strong>Fastest Response:</strong> {{ $responseTimeAnalysis['fastest_days'] ?? 0 }} days
                                    </div>
                                    <div class="mb-3">
                                        <strong>Slowest Response:</strong> {{ $responseTimeAnalysis['slowest_days'] ?? 0 }} days
                                    </div>
                                    <div class="mb-3">
                                        <strong>Within 7 days:</strong> {{ $responseTimeAnalysis['within_7_days'] ?? 0 }} responses
                                    </div>
                                    <div class="mb-3">
                                        <strong>Within 14 days:</strong> {{ $responseTimeAnalysis['within_14_days'] ?? 0 }} responses
                                    </div>
                                    <div class="mb-3">
                                        <strong>Within 30 days:</strong> {{ $responseTimeAnalysis['within_30_days'] ?? 0 }} responses
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Flagged Drivers Table -->
                    @if($flaggedDrivers->count() > 0)
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Flagged Drivers</h3>
                                </div>
                                <div class="card-body table-responsive p-0">
                                    <table class="table table-hover text-nowrap">
                                        <thead>
                                            <tr>
                                                <th>Driver Name</th>
                                                <th>Company</th>
                                                <th>Performance Rating</th>
                                                <th>Reason for Leaving</th>
                                                <th>Submitted</th>
                                                <th>Days Since</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($flaggedDrivers as $relation)
                                            <tr>
                                                <td>{{ $relation->driver->full_name }}</td>
                                                <td>{{ $relation->company->name }}</td>
                                                <td>
                                                    <span class="badge badge-{{ $relation->performance_rating === 'excellent' ? 'success' : ($relation->performance_rating === 'good' ? 'primary' : 'danger') }}">
                                                        {{ ucfirst($relation->performance_rating) }}
                                                    </span>
                                                </td>
                                                <td>{{ Str::limit($relation->reason_for_leaving, 30) }}</td>
                                                <td>{{ $relation->feedback_submitted_at->format('M d, Y') }}</td>
                                                <td>{{ $relation->feedback_submitted_at->diffInDays(now()) }} days</td>
                                                <td>
                                                    <a href="{{ route('admin.drivers.show', $relation->driver) }}" class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i> View Driver
                                                    </a>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Initialize charts
    initializeCharts();

    // Handle period change
    $('#trendPeriod').on('change', function() {
        updateTrendsChart($(this).val());
    });
});

function initializeCharts() {
    // Monthly Trends Chart
    const trendsCtx = document.getElementById('trendsChart').getContext('2d');
    window.trendsChart = new Chart(trendsCtx, {
        type: 'line',
        data: {
            labels: @json(collect($monthlyTrends)->pluck('month')),
            datasets: [{
                label: 'Requested',
                data: @json(collect($monthlyTrends)->pluck('requested')),
                borderColor: 'rgb(54, 162, 235)',
                backgroundColor: 'rgba(54, 162, 235, 0.1)',
                tension: 0.1
            }, {
                label: 'Submitted',
                data: @json(collect($monthlyTrends)->pluck('submitted')),
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                tension: 0.1
            }, {
                label: 'Flagged',
                data: @json(collect($monthlyTrends)->pluck('flagged')),
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

    // Rating Distribution Chart
    const ratingCtx = document.getElementById('ratingChart').getContext('2d');
    window.ratingChart = new Chart(ratingCtx, {
        type: 'bar',
        data: {
            labels: ['1 Star', '2 Stars', '3 Stars', '4 Stars', '5 Stars'],
            datasets: [{
                label: 'Number of Ratings',
                data: @json(array_values($ratingDistribution)),
                backgroundColor: [
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(255, 159, 64, 0.8)',
                    'rgba(255, 205, 86, 0.8)',
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(54, 162, 235, 0.8)'
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
}

function updateTrendsChart(period) {
    $.get('{{ route("admin.feedback-analytics.trends") }}', { period: period })
        .done(function(data) {
            window.trendsChart.data.labels = data.map(item => item.month);
            window.trendsChart.data.datasets[0].data = data.map(item => item.requested);
            window.trendsChart.data.datasets[1].data = data.map(item => item.submitted);
            window.trendsChart.data.datasets[2].data = data.map(item => item.flagged);
            window.trendsChart.update();
        })
        .fail(function() {
            toastr.error('Failed to update chart data');
        });
}
</script>
@endsection
