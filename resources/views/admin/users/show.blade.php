@extends('layouts.admin_cdn')

@section('title', 'View Admin User')

@section('content_header', 'Admin User Profile')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Admin Users</a></li>
    <li class="breadcrumb-item active">{{ $user->name }}</li>
@endsection

@section('css')
<style>
.profile-user-info {
    font-size: 15px;
    margin-bottom: 10px;
}

.profile-user-info .profile-user-info-label {
    width: 150px;
    display: inline-block;
    font-weight: bold;
    color: #495057;
}

.activity-item {
    padding: 10px 0;
    border-bottom: 1px solid #eee;
}

.activity-item:last-child {
    border-bottom: none;
}

.permissions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 10px;
}

.permission-item {
    padding: 8px 12px;
    background: #f8f9fa;
    border-left: 3px solid #28a745;
    border-radius: 4px;
    font-size: 0.875rem;
}
</style>
@endsection

@section('content')
<div class="row">
    <!-- User Profile Card -->
    <div class="col-md-4">
        <div class="card card-primary card-outline">
            <div class="card-body box-profile">
                <div class="text-center">
                    @if($user->avatar)
                        <img class="profile-user-img img-fluid img-circle"
                             src="{{ asset('storage/' . $user->avatar) }}"
                             alt="User profile picture">
                    @else
                        <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" 
                             style="width: 100px; height: 100px; font-size: 2rem; margin-bottom: 10px;">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </div>
                    @endif
                </div>

                <h3 class="profile-username text-center">{{ $user->name }}</h3>

                <p class="text-muted text-center">
                    @switch($user->role)
                        @case('super_admin')
                            <span class="badge badge-danger badge-lg">
                                <i class="fas fa-crown"></i> Super Administrator
                            </span>
                            @break
                        @case('admin')
                            <span class="badge badge-primary badge-lg">
                                <i class="fas fa-user-shield"></i> Administrator
                            </span>
                            @break
                        @case('moderator')
                            <span class="badge badge-warning badge-lg">
                                <i class="fas fa-user-cog"></i> Moderator
                            </span>
                            @break
                        @case('viewer')
                            <span class="badge badge-secondary badge-lg">
                                <i class="fas fa-eye"></i> Viewer
                            </span>
                            @break
                    @endswitch
                </p>

                <ul class="list-group list-group-unbordered mb-3">
                    <li class="list-group-item">
                        <b>Status</b> 
                        <span class="float-right">
                            @if($user->status === 'active')
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-secondary">Inactive</span>
                            @endif
                        </span>
                    </li>
                    <li class="list-group-item">
                        <b>Last Login</b> 
                        <span class="float-right text-muted">
                            {{ $user->last_login_at ? $user->last_login_at->format('M d, Y H:i') : 'Never' }}
                        </span>
                    </li>
                    <li class="list-group-item">
                        <b>Member Since</b> 
                        <span class="float-right text-muted">
                            {{ $user->created_at->format('M d, Y') }}
                        </span>
                    </li>
                </ul>

                <div class="row">
                    <div class="col-12">
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary btn-block">
                            <i class="fas fa-edit"></i> Edit Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Role Management Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user-cog"></i> Role Management
                </h3>
            </div>
            <div class="card-body">
                @if($user->id !== auth('admin')->id())
                    <form action="{{ route('admin.users.assign-role', $user) }}" method="POST" class="mb-3">
                        @csrf
                        <div class="form-group">
                            <label>Assign New Role</label>
                            <select name="role" class="form-control">
                                <option value="super_admin" {{ $user->role == 'super_admin' ? 'selected' : '' }}>Super Administrator</option>
                                <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Administrator</option>
                                <option value="moderator" {{ $user->role == 'moderator' ? 'selected' : '' }}>Moderator</option>
                                <option value="viewer" {{ $user->role == 'viewer' ? 'selected' : '' }}>Viewer</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success btn-sm">
                            <i class="fas fa-check"></i> Assign Role
                        </button>
                    </form>

                    @if($user->role !== 'viewer')
                        <form action="{{ route('admin.users.remove-role', $user) }}" method="POST" 
                              onsubmit="return confirm('Are you sure you want to remove this user\'s role?')">
                            @csrf
                            <button type="submit" class="btn btn-warning btn-sm">
                                <i class="fas fa-user-minus"></i> Remove Role
                            </button>
                        </form>
                    @endif
                @else
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        You cannot modify your own role.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- User Details and Activity -->
    <div class="col-md-8">
        <!-- Contact Information -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-address-card"></i> Contact Information
                </h3>
            </div>
            <div class="card-body">
                <div class="profile-user-info">
                    <span class="profile-user-info-label">Full Name:</span>
                    {{ $user->name }}
                </div>
                <div class="profile-user-info">
                    <span class="profile-user-info-label">Email Address:</span>
                    <a href="mailto:{{ $user->email }}">{{ $user->email }}</a>
                </div>
                <div class="profile-user-info">
                    <span class="profile-user-info-label">Phone Number:</span>
                    {{ $user->phone ?: 'Not provided' }}
                </div>
                <div class="profile-user-info">
                    <span class="profile-user-info-label">Last Login IP:</span>
                    {{ $user->last_login_ip ?: 'N/A' }}
                </div>
            </div>
        </div>

        <!-- Permissions Card -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-key"></i> Permissions & Access
                </h3>
            </div>
            <div class="card-body">
                @if($user->permissions && count($user->permissions) > 0)
                    <div class="permissions-grid">
                        @foreach($user->permissions as $permission)
                            <div class="permission-item">
                                <i class="fas fa-check text-success"></i> {{ ucwords(str_replace(['.', '_'], [' → ', ' '], $permission)) }}
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        No specific permissions assigned to this user.
                    </div>
                @endif
            </div>
        </div>

        <!-- Activity Statistics -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-bar"></i> Activity Statistics
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="info-box">
                            <span class="info-box-icon bg-info"><i class="fas fa-building"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Companies Verified</span>
                                <span class="info-box-number">{{ $user->verifiedCompanies->count() ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box">
                            <span class="info-box-icon bg-success"><i class="fas fa-users"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Drivers Verified</span>
                                <span class="info-box-number">{{ $user->verifiedDrivers->count() ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="info-box">
                            <span class="info-box-icon bg-warning"><i class="fas fa-clipboard-list"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Requests Created</span>
                                <span class="info-box-number">{{ $user->createdRequests->count() ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
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
                <div class="activity-item">
                    <div class="activity-content">
                        <i class="fas fa-sign-in-alt text-success"></i>
                        <strong>Last Login:</strong> 
                        {{ $user->last_login_at ? $user->last_login_at->format('F j, Y \a\t g:i A') : 'Never logged in' }}
                    </div>
                </div>
                
                <div class="activity-item">
                    <div class="activity-content">
                        <i class="fas fa-user-plus text-info"></i>
                        <strong>Account Created:</strong> 
                        {{ $user->created_at->format('F j, Y \a\t g:i A') }}
                    </div>
                </div>

                <div class="activity-item">
                    <div class="activity-content">
                        <i class="fas fa-edit text-primary"></i>
                        <strong>Last Updated:</strong> 
                        {{ $user->updated_at->format('F j, Y \a\t g:i A') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection