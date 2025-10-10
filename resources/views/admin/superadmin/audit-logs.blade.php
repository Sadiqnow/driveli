@extends('layouts.admin_cdn')

@section('title', 'SuperAdmin - Audit Logs')

@section('content_header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Audit Logs</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.superadmin.dashboard') }}">SuperAdmin</a></li>
                        <li class="breadcrumb-item active">Audit Logs</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid">
        <!-- Info Card -->
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle mr-1"></i>
                    System Audit Logs
                </h3>
            </div>
            <div class="card-body">
                <div class="text-center py-5">
                    <i class="fas fa-tools fa-4x text-muted mb-4"></i>
                    <h4 class="text-muted">Audit Logging System</h4>
                    <p class="text-muted mb-4">
                        The comprehensive audit logging system is currently being implemented.<br>
                        This will track all administrative actions and system changes.
                    </p>
                    
                    <div class="row justify-content-center">
                        <div class="col-md-8">
                            <div class="card card-outline card-primary">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Planned Audit Features</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <ul class="list-unstyled">
                                                <li><i class="fas fa-check-circle text-success mr-2"></i> User Authentication Logs</li>
                                                <li><i class="fas fa-check-circle text-success mr-2"></i> Administrative Actions</li>
                                                <li><i class="fas fa-check-circle text-success mr-2"></i> Role & Permission Changes</li>
                                                <li><i class="fas fa-check-circle text-success mr-2"></i> Data Modifications</li>
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <ul class="list-unstyled">
                                                <li><i class="fas fa-check-circle text-success mr-2"></i> System Configuration Changes</li>
                                                <li><i class="fas fa-check-circle text-success mr-2"></i> File Upload/Download Logs</li>
                                                <li><i class="fas fa-check-circle text-success mr-2"></i> API Access Logs</li>
                                                <li><i class="fas fa-check-circle text-success mr-2"></i> Security Events</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <a href="{{ route('admin.superadmin.dashboard') }}" class="btn btn-primary">
                            <i class="fas fa-arrow-left mr-1"></i> Back to Dashboard
                        </a>
                        <a href="{{ route('admin.superadmin.settings') }}" class="btn btn-outline-secondary ml-2">
                            <i class="fas fa-cog mr-1"></i> System Settings
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Temporary Activity Overview -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-line mr-1"></i>
                    Recent System Activity Overview
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="info-box bg-info">
                            <span class="info-box-icon"><i class="fas fa-sign-in-alt"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Admin Logins Today</span>
                                <span class="info-box-number">{{ \App\Models\AdminUser::whereDate('last_login_at', today())->count() }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-success">
                            <span class="info-box-icon"><i class="fas fa-users"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Active Admin Users</span>
                                <span class="info-box-number">{{ \App\Models\AdminUser::where('status', 'Active')->count() }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-warning">
                            <span class="info-box-icon"><i class="fas fa-database"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">System Health</span>
                                <span class="info-box-number text-success">Good</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-danger">
                            <span class="info-box-icon"><i class="fas fa-server"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Memory Usage</span>
                                <span class="info-box-number">{{ round(memory_get_usage(true) / 1024 / 1024, 1) }}MB</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Actions Preview -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-history mr-1"></i>
                    Recent Administrative Actions
                </h3>
            </div>
            <div class="card-body">
                <div class="timeline">
                    @php
                        $recentAdmins = \App\Models\AdminUser::latest()->limit(5)->get();
                    @endphp
                    @foreach($recentAdmins as $admin)
                    <div class="time-label">
                        <span class="bg-info">{{ $admin->created_at->format('M d') }}</span>
                    </div>
                    <div>
                        <i class="fas fa-user-plus bg-blue"></i>
                        <div class="timeline-item">
                            <span class="time">
                                <i class="fas fa-clock"></i> {{ $admin->created_at->format('H:i') }}
                            </span>
                            <h3 class="timeline-header">
                                Admin User Created
                            </h3>
                            <div class="timeline-body">
                                New admin user "{{ $admin->name }}" was created with role "{{ $admin->role }}"
                            </div>
                        </div>
                    </div>
                    @endforeach
                    
                    <div>
                        <i class="fas fa-clock bg-gray"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
.info-box {
    border-radius: 10px;
    transition: transform 0.3s ease;
}

.info-box:hover {
    transform: translateY(-2px);
}

.timeline-item {
    border-radius: 8px;
}

.card-outline.card-primary {
    border-color: #007bff;
}
</style>
@endpush