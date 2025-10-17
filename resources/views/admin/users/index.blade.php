@extends('layouts.admin_master')

@section('title', 'Admin Users Management')

@section('content_header')
    Admin Users Management
@stop

@section('content')
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-2 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['total'] }}</h3>
                    <p>Total Users</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['active'] }}</h3>
                    <p>Active</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats['inactive'] }}</h3>
                    <p>Inactive</p>
                </div>
                <div class="icon">
                    <i class="fas fa-pause-circle"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $stats['super_admins'] }}</h3>
                    <p>Super Admins</p>
                </div>
                <div class="icon">
                    <i class="fas fa-crown"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $stats['admins'] }}</h3>
                    <p>Admins</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-shield"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-2 col-6">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3>{{ $stats['moderators'] }}</h3>
                    <p>Moderators</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-cog"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Search & Filters -->
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title">
                    <i class="fas fa-filter mr-1"></i>
                    Search & Filter Admin Users
                </h3>
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Admin User
                </a>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.users.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" 
                           placeholder="Search by name, email, phone..." 
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-control">
                        <option value="">All Roles</option>
                        <option value="super_admin" {{ request('role') == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                        <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="moderator" {{ request('role') == 'moderator' ? 'selected' : '' }}>Moderator</option>
                        <option value="viewer" {{ request('role') == 'viewer' ? 'selected' : '' }}>Viewer</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div>
                        <button class="btn btn-success w-100 mb-1">
                            <i class="fas fa-search"></i> Search
                        </button>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary w-100">
                            Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Admin Users Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-users mr-1"></i>
                Admin Users Directory
            </h3>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($adminUsers as $user)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($user->avatar)
                                        <img src="{{ asset('storage/' . $user->avatar) }}" 
                                             class="img-circle mr-2" width="40" height="40" alt="Avatar">
                                    @else
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mr-2" 
                                             style="width: 40px; height: 40px;">
                                            {{ strtoupper(substr($user->name, 0, 2)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <strong>{{ $user->name }}</strong>
                                        @if($user->id === auth('admin')->id())
                                            <small class="badge badge-info">You</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->phone ?? 'N/A' }}</td>
                            <td>
                                @switch($user->role)
                                    @case('super_admin')
                                        <span class="badge badge-danger">
                                            <i class="fas fa-crown"></i> Super Admin
                                        </span>
                                        @break
                                    @case('admin')
                                        <span class="badge badge-primary">
                                            <i class="fas fa-user-shield"></i> Admin
                                        </span>
                                        @break
                                    @case('moderator')
                                        <span class="badge badge-warning">
                                            <i class="fas fa-user-cog"></i> Moderator
                                        </span>
                                        @break
                                    @case('viewer')
                                        <span class="badge badge-secondary">
                                            <i class="fas fa-eye"></i> Viewer
                                        </span>
                                        @break
                                @endswitch
                            </td>
                            <td>
                                @if($user->status === 'active')
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>
                                @if($user->last_login_at)
                                    {{ $user->last_login_at->format('M d, Y H:i') }}
                                @else
                                    <span class="text-muted">Never</span>
                                @endif
                            </td>
                            <td>{{ $user->created_at->format('M d, Y') }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.users.show', $user) }}" 
                                       class="btn btn-info btn-sm" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.users.edit', $user) }}" 
                                       class="btn btn-primary btn-sm" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if($user->id !== auth('admin')->id())
                                        <form action="{{ route('admin.users.destroy', $user) }}" 
                                              method="POST" style="display: inline;" 
                                              onsubmit="return confirm('Are you sure you want to delete this admin user?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-danger btn-sm" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No admin users found</h5>
                                <p class="text-muted">Try adjusting your search criteria or add a new admin user.</p>
                                <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Add First Admin User
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($adminUsers->hasPages())
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        Showing {{ $adminUsers->firstItem() }} to {{ $adminUsers->lastItem() }} 
                        of {{ $adminUsers->total() }} admin users
                    </div>
                    {{ $adminUsers->links() }}
                </div>
            </div>
        @endif
    </div>
@stop

@section('css')
<style>
.small-box {
    border-radius: 10px;
    transition: transform 0.3s ease;
}

.small-box:hover {
    transform: translateY(-2px);
}

.badge {
    font-size: 0.8em;
}

.btn-group .btn {
    margin-right: 2px;
}
</style>
@stop