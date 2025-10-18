@extends('layouts.admin_master')

@section('title', 'Dashboard')

@section('breadcrumbs')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
    <!-- Statistics Cards -->
    <div class="row">
        <!-- Total Drivers -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['total_drivers'] ?? 0 }}</h3>
                    <p>Total Drivers</p>
                    <small>+{{ $stats['drivers_today'] ?? 0 }} today</small>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
                <a href="{{ route('admin.superadmin.drivers.index') }}" class="small-box-footer">
                    More info <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <!-- Active Drivers -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['active_drivers'] ?? 0 }}</h3>
                    <p>Active Drivers</p>
                    <small>{{ number_format((($stats['active_drivers'] ?? 0) / max(($stats['total_drivers'] ?? 0), 1)) * 100, 1) }}% of total</small>
                </div>
                <div class="icon">
                    <i class="fas fa-user-check"></i>
                </div>
                <a href="{{ route('admin.drivers.index', ['status' => 'active']) }}" class="small-box-footer">
                    More info <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <!-- Admin Users -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats['total_users'] ?? 0 }}</h3>
                    <p>Admin Users</p>
                    <small>{{ $stats['active_users'] ?? 0 }} active</small>
                </div>
                <div class="icon">
                    <i class="fas fa-user-shield"></i>
                </div>
                <a href="{{ route('admin.users.index') }}" class="small-box-footer">
                    More info <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <!-- Pending Verifications -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $stats['pending_verifications'] ?? 0 }}</h3>
                    <p>Pending Verifications</p>
                    <small>Requires attention</small>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
                <a href="{{ route('admin.verification.dashboard') }}" class="small-box-footer">
                    Review <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Additional Metrics -->
    <div class="row">
        <!-- Companies -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $stats['total_companies'] ?? 0 }}</h3>
                    <p>Total Companies</p>
                    <small>Active partnerships</small>
                </div>
                <div class="icon">
                    <i class="fas fa-building"></i>
                </div>
                <a href="{{ route('admin.companies.index') }}" class="small-box-footer">
                    More info <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <!-- Active Requests -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3>{{ $stats['active_requests'] ?? 0 }}</h3>
                    <p>Active Requests</p>
                    <small>Driver requests</small>
                </div>
                <div class="icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <a href="{{ route('admin.requests.index') }}" class="small-box-footer">
                    View <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <!-- Successful Matches -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-teal">
                <div class="inner">
                    <h3>{{ $stats['completed_matches'] ?? 0 }}</h3>
                    <p>Successful Matches</p>
                    <small>This month</small>
                </div>
                <div class="icon">
                    <i class="fas fa-handshake"></i>
                </div>
                <a href="{{ route('admin.matching.dashboard') }}" class="small-box-footer">
                    View <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <!-- Commission Earned -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-dark">
                <div class="inner">
                    <h3>₦{{ number_format($stats['total_commission'] ?? 0, 0) }}</h3>
                    <p>Commission Earned</p>
                    <small>Total earnings</small>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <a href="{{ route('admin.commissions.index') }}" class="small-box-footer">
                    View <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>
    </section>

    <!-- Charts and Recent Activity -->
    <div class="row">
        <!-- Driver Registration Chart -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line"></i> Driver Registrations (Last 30 Days)
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="driverChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-cog"></i> Quick Actions
                    </h3>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.drivers.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Driver
                        </a>
                        <a href="{{ route('admin.requests.create') }}" class="btn btn-success">
                            <i class="fas fa-plus"></i> Create Request
                        </a>
                        <a href="{{ route('admin.verification.dashboard') }}" class="btn btn-warning">
                            <i class="fas fa-check-circle"></i> Review Verifications
                        </a>
                        <a href="{{ route('admin.matching.dashboard') }}" class="btn btn-info">
                            <i class="fas fa-handshake"></i> View Matches
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-history"></i> Recent Activity
                    </h3>
                </div>
                <div class="card-body">
                    @if($recentActivity->count() > 0)
                        <div class="timeline timeline-inverse">
                            @foreach($recentActivity->take(5) as $activity)
                            <div class="time-label">
                                <span class="bg-{{ $activity['color'] }}">{{ $activity['timestamp']->format('M d') }}</span>
                            </div>
                            <div>
                                <i class="{{ $activity['icon'] }} bg-{{ $activity['color'] }}"></i>
                                <div class="timeline-item">
                                    <span class="time"><i class="fas fa-clock"></i> {{ $activity['timestamp']->diffForHumans() }}</span>
                                    <h3 class="timeline-header">{{ $activity['message'] }}</h3>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-inbox fa-2x mb-2"></i>
                            <p>No recent activity</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Driver Registration Chart
    const ctx = document.getElementById('driverChart').getContext('2d');
    const driverChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json(collect($chartData['driver_registrations'])->pluck('date')),
            datasets: [{
                label: 'Driver Registrations',
                data: @json(collect($chartData['driver_registrations'])->pluck('count')),
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
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
                    beginAtZero: true
                }
            }
        }
    });

    // Auto-refresh dashboard data every 5 minutes
    setInterval(function() {
        location.reload();
    }, 300000);
});
</script>
@endsection

