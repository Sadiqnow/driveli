@extends('layouts.admin_master')

@section('title', 'Super Admin Dashboard')

@section('content_header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Super Admin Dashboard</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Super Admin</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid">
        <!-- System Overview Cards -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $stats['total_users'] ?? 0 }}</h3>
                        <p>Total Admin Users</p>
                        <small>{{ $stats['active_users'] ?? 0 }} active</small>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users-cog"></i>
                    </div>
                    <a href="{{ route('admin.users.index') }}" class="small-box-footer">
                        Manage Users <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $stats['total_drivers'] ?? 0 }}</h3>
                        <p>Total Drivers</p>
                        <small>{{ $stats['verified_drivers'] ?? 0 }} verified</small>
                    </div>
                    <div class="icon">
                        <i class="fas fa-id-card"></i>
                    </div>
                    <a href="{{ route('admin.superadmin.drivers.index') }}" class="small-box-footer">
                        View Drivers <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $stats['total_activities'] ?? 0 }}</h3>
                        <p>Audit Activities</p>
                        <small>{{ $stats['recent_activities'] ?? 0 }} today</small>
                    </div>
                    <div class="icon">
                        <i class="fas fa-history"></i>
                    </div>
                    <a href="{{ route('admin.superadmin.audit-logs') }}" class="small-box-footer">
                        View Logs <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ $stats['database_size'] ?? 'N/A' }}</h3>
                        <p>Database Size</p>
                        <small>{{ $stats['system_uptime'] ?? 'N/A' }} uptime</small>
                    </div>
                    <div class="icon">
                        <i class="fas fa-database"></i>
                    </div>
                    <a href="#system-health" class="small-box-footer">
                        System Health <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Super Admin Quick Actions</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 col-sm-6 mb-3">
                                <a href="{{ route('admin.superadmin.drivers.index') }}" class="btn btn-primary btn-block">
                                    <i class="fas fa-users"></i>
                                    <br>Manage Drivers
                                </a>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-3">
                                <a href="{{ route('admin.superadmin.audit-logs') }}" class="btn btn-success btn-block">
                                    <i class="fas fa-history"></i>
                                    <br>Audit Logs
                                </a>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-3">
                                <a href="{{ route('admin.superadmin.users') }}" class="btn btn-warning btn-block">
                                    <i class="fas fa-user-shield"></i>
                                    <br>Manage Admins
                                </a>
                            </div>
                            <div class="col-md-3 col-sm-6 mb-3">
                                <button class="btn btn-info btn-block" onclick="checkSystemHealth()">
                                    <i class="fas fa-heartbeat"></i>
                                    <br>System Health
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Health Status -->
        <div class="row" id="system-health">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">System Health Check</h3>
                        <div class="card-tools">
                            <button class="btn btn-sm btn-outline-primary" onclick="refreshSystemHealth()">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row" id="health-status">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="health-indicator" id="db-indicator">
                                        <i class="fas fa-database"></i>
                                    </div>
                                    <div class="ml-3">
                                        <strong>Database</strong><br>
                                        <span id="db-status">Checking...</span>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center mb-3">
                                    <div class="health-indicator" id="cache-indicator">
                                        <i class="fas fa-memory"></i>
                                    </div>
                                    <div class="ml-3">
                                        <strong>Cache</strong><br>
                                        <span id="cache-status">Checking...</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="health-indicator" id="storage-indicator">
                                        <i class="fas fa-hdd"></i>
                                    </div>
                                    <div class="ml-3">
                                        <strong>Storage</strong><br>
                                        <span id="storage-status">Checking...</span>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center mb-3">
                                    <div class="health-indicator" id="queue-indicator">
                                        <i class="fas fa-tasks"></i>
                                    </div>
                                    <div class="ml-3">
                                        <strong>Queue System</strong><br>
                                        <span id="queue-status">Checking...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Recent System Activities</h3>
                        <div class="card-tools">
                            <a href="{{ route('admin.superadmin.audit-logs') }}" class="btn btn-sm btn-outline-secondary">
                                View All Logs
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Action</th>
                                        <th>Description</th>
                                        <th>Timestamp</th>
                                        <th>IP Address</th>
                                    </tr>
                                </thead>
                                <tbody id="recent-activities">
                                    @php
                                        $recentActivities = \App\Models\SuperadminActivityLog::with('superadmin')
                                            ->orderBy('created_at', 'desc')
                                            ->limit(10)
                                            ->get();
                                    @endphp
                                    @forelse($recentActivities as $activity)
                                        <tr>
                                            <td>
                                                @if($activity->superadmin)
                                                    <strong>{{ $activity->superadmin->name }}</strong>
                                                @else
                                                    <em class="text-muted">System</em>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge {{ $activity->action_badge_class }}">
                                                    {{ $activity->formatted_action }}
                                                </span>
                                            </td>
                                            <td>{{ Str::limit($activity->description, 60) }}</td>
                                            <td>{{ $activity->created_at->diffForHumans() }}</td>
                                            <td>{{ $activity->ip_address }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4">
                                                <i class="fas fa-inbox fa-2x mb-2 text-muted"></i>
                                                <p class="text-muted">No recent activities found</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
.health-indicator {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 16px;
}

.health-indicator.healthy {
    background-color: #28a745;
}

.health-indicator.warning {
    background-color: #ffc107;
    color: #212529;
}

.health-indicator.error {
    background-color: #dc3545;
}

.btn-block {
    display: block;
    width: 100%;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Load system health on page load
    checkSystemHealth();
});

function checkSystemHealth() {
    $('#health-status .health-indicator').removeClass('healthy warning error');
    $('#db-status, #cache-status, #storage-status, #queue-status').text('Checking...');

    $.get('{{ route("admin.superadmin.system-health") }}')
        .done(function(data) {
            updateHealthIndicator('db', data.database);
            updateHealthIndicator('cache', data.cache);
            updateHealthIndicator('storage', data.storage);
            updateHealthIndicator('queue', data.queue);
        })
        .fail(function() {
            $('#db-status, #cache-status, #storage-status, #queue-status').text('Check failed');
        });
}

function updateHealthIndicator(type, data) {
    const indicator = $(`#${type}-indicator`);
    const status = $(`#${type}-status`);

    indicator.removeClass('healthy warning error');
    indicator.addClass(data.status);
    status.text(data.message);
}

function refreshSystemHealth() {
    const btn = event.target;
    const originalHtml = $(btn).html();
    $(btn).html('<i class="fas fa-spinner fa-spin"></i> Refreshing...').prop('disabled', true);

    checkSystemHealth();

    setTimeout(() => {
        $(btn).html(originalHtml).prop('disabled', false);
    }, 2000);
}
</script>
@endpush
