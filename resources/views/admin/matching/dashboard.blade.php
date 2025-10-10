@extends('layouts.admin_cdn')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1>Matching Dashboard</h1>
                <div>
                    <a href="{{ route('admin.matching.index') }}" class="btn btn-primary">
                        <i class="fas fa-handshake"></i> Manual Matching
                    </a>
                    <a href="{{ route('admin.matching.matches') }}" class="btn btn-info">
                        <i class="fas fa-list"></i> View Matches
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3 id="availableDrivers">{{ $availableDrivers ?? 0 }}</h3>
                            <p>Available Drivers</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <a href="#" class="small-box-footer">
                            View Details <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3 id="pendingRequests">{{ $pendingRequests ?? 0 }}</h3>
                            <p>Pending Requests</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <a href="#" class="small-box-footer">
                            View Details <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3 id="successfulMatches">{{ $successfulMatches ?? 0 }}</h3>
                            <p>Successful Matches</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <a href="#" class="small-box-footer">
                            View Details <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3 id="matchingRate">{{ $matchingRate ?? '0' }}%</h3>
                            <p>Matching Rate</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-percentage"></i>
                        </div>
                        <a href="#" class="small-box-footer">
                            View Details <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Additional Statistics Row -->
            <div class="row mb-4">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-secondary">
                        <div class="inner">
                            <h3>{{ $pendingMatches ?? 0 }}</h3>
                            <p>Pending Matches</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <h3>{{ $autoMatches ?? 0 }}</h3>
                            <p>Auto Matches</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-robot"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-indigo">
                        <div class="inner">
                            <h3>{{ $manualMatches ?? 0 }}</h3>
                            <p>Manual Matches</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-user-cog"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-teal">
                        <div class="inner">
                            <h3>{{ number_format((($autoMatches + $manualMatches) / max(1, $pendingRequests)) * 100, 1) }}%</h3>
                            <p>Coverage Rate</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Recent Matches Chart -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-line"></i> Matching Trends
                            </h3>
                        </div>
                        <div class="card-body">
                            <canvas id="matchingTrendsChart" width="400" height="200"></canvas>
                            <div class="text-center mt-3 text-muted">
                                <i class="fas fa-info-circle"></i> Chart showing matching activity over time
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Companies -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-building"></i> Top Companies
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="list-group">
                                @if(isset($topCompanies) && count($topCompanies) > 0)
                                    @foreach($topCompanies as $company)
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            {{ $company->name }}
                                            <span class="badge badge-primary badge-pill">{{ $company->requests_count }}</span>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="text-center text-muted">
                                        <i class="fas fa-info-circle"></i><br>
                                        No company data available
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-history"></i> Recent Matching Activity
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="refresh">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Driver</th>
                                            <th>Company</th>
                                            <th>Match Type</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if(isset($recentMatches) && count($recentMatches) > 0)
                                            @foreach($recentMatches as $match)
                                                <tr>
                                                    <td>{{ $match->created_at->format('M d, Y H:i') }}</td>
                                                    <td>
                                                        <strong>{{ $match->driver->first_name }} {{ $match->driver->surname }}</strong>
                                                        <br>
                                                        <small class="text-muted">{{ $match->driver->driver_id }}</small>
                                                    </td>
                                                    <td>
                                                        <strong>{{ $match->companyRequest->company->name ?? 'Unknown Company' }}</strong>
                                                        <br>
                                                        <small class="text-muted">{{ $match->companyRequest->location ?? 'N/A' }}</small>
                                                    </td>
                                                    <td>
                                                        @if($match->auto_matched)
                                                            <span class="badge badge-info">
                                                                <i class="fas fa-robot"></i> Auto
                                                            </span>
                                                        @elseif($match->matched_by_admin)
                                                            <span class="badge badge-warning">
                                                                <i class="fas fa-user"></i> Manual
                                                            </span>
                                                        @else
                                                            <span class="badge badge-secondary">System</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-{{ $match->status_color }}">
                                                            {{ ucfirst($match->status) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            @if($match->status === 'pending')
                                                            <form method="POST" action="{{ route('admin.matching.matches.confirm', $match->match_id) }}" style="display: inline;">
                                                                @csrf
                                                                <button type="submit" class="btn btn-success btn-sm" title="Confirm Match">
                                                                    <i class="fas fa-check"></i>
                                                                </button>
                                                            </form>
                                                            @endif
                                                            <button class="btn btn-info btn-sm" title="Match ID: {{ $match->match_id }}">
                                                                <i class="fas fa-info"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="6" class="text-center text-muted">
                                                    <i class="fas fa-info-circle"></i> No recent matching activity
                                                </td>
                                            </tr>
                                        @endif
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
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Initialize Chart
    const ctx = document.getElementById('matchingTrendsChart').getContext('2d');
    const matchingTrendsChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
            datasets: [{
                label: 'Successful Matches',
                data: [12, 19, 8, 15, 22, 18, 25],
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }, {
                label: 'Pending Matches',
                data: [5, 8, 12, 7, 9, 6, 4],
                borderColor: 'rgb(255, 205, 86)',
                backgroundColor: 'rgba(255, 205, 86, 0.2)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Monthly Matching Activity'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Auto-refresh dashboard every 5 minutes
    setInterval(function() {
        location.reload();
    }, 300000);
});
</script>
@endsection