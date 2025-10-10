@extends('layouts.admin_cdn')

@section('title', 'SuperAdmin Dashboard')

@section('content_header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">SuperAdmin Dashboard</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">SuperAdmin</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid">
        <!-- Stats Cards -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $stats['total_admins'] }}</h3>
                        <p>Total Admin Users</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users-cog"></i>
                    </div>
                    <a href="{{ route('admin.superadmin.users') }}" class="small-box-footer">
                        More info <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $stats['active_admins'] }}</h3>
                        <p>Active Admins</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <a href="{{ route('admin.superadmin.users') }}?status=Active" class="small-box-footer">
                        More info <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $stats['total_roles'] }}</h3>
                        <p>System Roles</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-tag"></i>
                    </div>
                    <a href="#" class="small-box-footer" onclick="alert('Role management system is being implemented. Currently using legacy role system.')">
                        More info <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ $stats['total_permissions'] }}</h3>
                        <p>Permissions</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-key"></i>
                    </div>
                    <a href="#" class="small-box-footer" onclick="alert('Permission management system is being implemented. Currently using legacy role system.')">
                        More info <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Recent Admins -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-users mr-1"></i>
                            Recent Admin Users
                        </h3>
                        <div class="card-tools">
                            <a href="{{ route('admin.superadmin.users') }}" class="btn btn-tool">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Roles</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentAdmins ?? [] as $admin)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="user-initials bg-info text-white rounded-circle mr-2" style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; font-size: 12px;">
                                                    {{ $admin->initials }}
                                                </div>
                                                {{ $admin->name }}
                                            </div>
                                        </td>
                                        <td>{{ $admin->email }}</td>
                                        <td>
                                            @switch($admin->role)
                                                @case('Super Admin')
                                                    <span class="badge badge-danger badge-sm">
                                                        <i class="fas fa-crown"></i> Super Admin
                                                    </span>
                                                    @break
                                                @case('Admin')
                                                    <span class="badge badge-primary badge-sm">
                                                        <i class="fas fa-user-shield"></i> Admin
                                                    </span>
                                                    @break
                                                @case('Moderator')
                                                    <span class="badge badge-warning badge-sm">
                                                        <i class="fas fa-user-cog"></i> Moderator
                                                    </span>
                                                    @break
                                                @case('Viewer')
                                                    <span class="badge badge-secondary badge-sm">
                                                        <i class="fas fa-eye"></i> Viewer
                                                    </span>
                                                    @break
                                                @default
                                                    <span class="badge badge-light badge-sm">{{ $admin->role }}</span>
                                            @endswitch
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $admin->status === 'Active' ? 'success' : 'secondary' }}">
                                                {{ $admin->status }}
                                            </span>
                                        </td>
                                        <td>{{ $admin->created_at->format('M d, Y') }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            <i class="fas fa-users mb-2" style="font-size: 2rem;"></i>
                                            <p>No admin users found.</p>
                                            <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
                                                <i class="fas fa-plus mr-1"></i> Create First Admin
                                            </a>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Role Distribution -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-pie mr-1"></i>
                            Role Distribution
                        </h3>
                    </div>
                    <div class="card-body">
                        @if(isset($roleDistribution) && count($roleDistribution) > 0)
                            @foreach($roleDistribution as $role)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span>{{ $role->name }}</span>
                                <span class="badge badge-info">{{ $role->count }}</span>
                            </div>
                            <div class="progress progress-sm">
                                @php
                                    $percentage = $stats['total_admins'] > 0 ? ($role->count / $stats['total_admins']) * 100 : 0;
                                @endphp
                                <div class="progress-bar bg-info" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                            @endforeach
                        @else
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-info-circle mb-2" style="font-size: 2rem;"></i>
                                <p>No role distribution data available.</p>
                                <small>Roles will appear here once the role system is fully configured.</small>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-tools mr-1"></i>
                            Quick Actions
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-user-plus mr-1"></i> Create Admin User
                            </a>
                            <a href="#" class="btn btn-success btn-sm" onclick="alert('Role system is being implemented. Currently using legacy role system.')">
                                <i class="fas fa-plus mr-1"></i> Create Role
                            </a>
                            <a href="#" class="btn btn-warning btn-sm" onclick="alert('Permission system is being implemented. Currently using legacy role system.')">
                                <i class="fas fa-key mr-1"></i> Create Permission
                            </a>
                            <a href="{{ route('admin.superadmin.audit-logs') }}" class="btn btn-info btn-sm">
                                <i class="fas fa-history mr-1"></i> View Audit Logs
                            </a>
                            <a href="{{ route('admin.superadmin.settings') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-cog mr-1"></i> System Settings
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Status -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-server mr-1"></i>
                            System Status
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-success"><i class="fas fa-database"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Database</span>
                                        <span class="info-box-number text-success">Online</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-info"><i class="fas fa-memory"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Memory Usage</span>
                                        <span class="info-box-number">{{ round(memory_get_usage(true) / 1024 / 1024, 2) }}MB</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-warning"><i class="fas fa-clock"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Server Time</span>
                                        <span class="info-box-number">{{ now()->format('H:i:s') }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-danger"><i class="fas fa-php"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">PHP Version</span>
                                        <span class="info-box-number">{{ PHP_VERSION }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-refresh system status every 30 seconds
    setInterval(function() {
        $('.info-box-number:contains("' + new Date().toLocaleTimeString('en-GB') + '")').text(new Date().toLocaleTimeString('en-GB'));
    }, 30000);
});
</script>
@endpush
