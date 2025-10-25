@extends('company.layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2 class="mb-0"><i class="bi bi-speedometer2"></i> Dashboard</h2>
        <p class="text-muted">Welcome back, {{ Auth::user()->name }}!</p>
    </div>
</div>

<!-- KPI Cards -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card bg-primary text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title mb-1">{{ $dashboard['total_requests'] ?? 0 }}</h5>
                        <p class="card-text mb-0">Total Requests</p>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-clipboard-check" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card bg-success text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title mb-1">{{ $dashboard['active_requests'] ?? 0 }}</h5>
                        <p class="card-text mb-0">Active Requests</p>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-play-circle" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card bg-info text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title mb-1">{{ $dashboard['total_matches'] ?? 0 }}</h5>
                        <p class="card-text mb-0">Total Matches</p>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-hand-thumbs-up" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card bg-warning text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title mb-1">{{ $dashboard['total_fleets'] ?? 0 }}</h5>
                        <p class="card-text mb-0">Total Fleets</p>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-truck" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card bg-secondary text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title mb-1">{{ $dashboard['total_vehicles'] ?? 0 }}</h5>
                        <p class="card-text mb-0">Total Vehicles</p>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-car-front" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card bg-danger text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title mb-1">{{ $dashboard['pending_invoices'] ?? 0 }}</h5>
                        <p class="card-text mb-0">Pending Invoices</p>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-receipt" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card bg-dark text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title mb-1">₦{{ number_format($dashboard['total_spent'] ?? 0, 2) }}</h5>
                        <p class="card-text mb-0">Total Spent</p>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-cash" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card bg-light text-dark h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title mb-1">{{ $dashboard['completed_requests'] ?? 0 }}</h5>
                        <p class="card-text mb-0">Completed</p>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-check-circle" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-bar-chart"></i> Request Status Distribution</h5>
            </div>
            <div class="card-body">
                <canvas id="requestStatusChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-pie-chart"></i> Fleet Performance</h5>
            </div>
            <div class="card-body">
                <canvas id="fleetPerformanceChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Recent Requests</h5>
            </div>
            <div class="card-body">
                @if(isset($recentRequests) && $recentRequests->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($recentRequests->take(5) as $request)
                            <div class="list-group-item px-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">{{ $request->request_id }}</h6>
                                        <small class="text-muted">{{ $request->pickup_location }} → {{ $request->dropoff_location }}</small>
                                    </div>
                                    <span class="badge bg-{{ $request->status === 'active' ? 'success' : ($request->status === 'pending' ? 'warning' : 'secondary') }}">
                                        {{ ucfirst($request->status) }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted mb-0">No recent requests found.</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-bell"></i> Recent Matches</h5>
            </div>
            <div class="card-body">
                @if(isset($recentMatches) && $recentMatches->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($recentMatches->take(5) as $match)
                            <div class="list-group-item px-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">{{ $match->companyRequest->request_id }}</h6>
                                        <small class="text-muted">{{ $match->driver->name ?? 'Driver' }}</small>
                                    </div>
                                    <span class="badge bg-{{ $match->status === 'accepted' ? 'success' : ($match->status === 'pending' ? 'warning' : 'secondary') }}">
                                        {{ ucfirst($match->status) }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted mb-0">No recent matches found.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Request Status Chart
    const requestStatusCtx = document.getElementById('requestStatusChart').getContext('2d');
    new Chart(requestStatusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Active', 'Completed', 'Pending', 'Cancelled'],
            datasets: [{
                data: [
                    {{ $dashboard['active_requests'] ?? 0 }},
                    {{ $dashboard['completed_requests'] ?? 0 }},
                    {{ $dashboard['total_requests'] ?? 0 - $dashboard['active_requests'] ?? 0 - $dashboard['completed_requests'] ?? 0 }},
                    0 // cancelled
                ],
                backgroundColor: [
                    '#28a745',
                    '#007bff',
                    '#ffc107',
                    '#dc3545'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                }
            }
        }
    });

    // Fleet Performance Chart
    const fleetPerformanceCtx = document.getElementById('fleetPerformanceChart').getContext('2d');
    new Chart(fleetPerformanceCtx, {
        type: 'bar',
        data: {
            labels: ['Fleets', 'Vehicles', 'Active Vehicles'],
            datasets: [{
                label: 'Count',
                data: [
                    {{ $dashboard['total_fleets'] ?? 0 }},
                    {{ $dashboard['total_vehicles'] ?? 0 }},
                    {{ $dashboard['total_vehicles'] ?? 0 }} // Assuming all are active for now
                ],
                backgroundColor: [
                    '#6c757d',
                    '#17a2b8',
                    '#28a745'
                ]
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>
@endpush
@endsection
