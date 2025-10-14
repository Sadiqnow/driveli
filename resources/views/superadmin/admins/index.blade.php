@extends('layouts.admin_cdn')

@section('title', 'Admin Management - Superadmin')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0">Admin Management</h1>
            <p class="text-muted">Manage admin users, roles, and permissions</p>
        </div>
        <a href="{{ route('superadmin.admins.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create New Admin
        </a>
    </div>
@endsection

@section('content')
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['total'] }}</h3>
                    <p>Total Admins</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
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
        <div class="col-lg-3 col-6">
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
        <div class="col-lg-3 col-6">
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
    </div>

    <!-- Search & Filters -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-filter mr-1"></i>
                Search & Filter Admins
            </h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('superadmin.admins.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control"
                           placeholder="Search by name or email..."
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-control">
                        <option value="">All Roles</option>
                        @foreach($availableRoles as $roleName => $roleDisplay)
                            <option value="{{ $roleName }}" {{ request('role') == $roleName ? 'selected' : '' }}>
                                {{ $roleDisplay }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="Active" {{ request('status') == 'Active' ? 'selected' : '' }}>Active</option>
                        <option value="Inactive" {{ request('status') == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div>
                        <button class="btn btn-success w-100 mb-1">
                            <i class="fas fa-search"></i> Search
                        </button>
                        <a href="{{ route('superadmin.admins.index') }}" class="btn btn-secondary w-100">
                            Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Admins Table -->
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
                        <th>Role</th>
                        <th>Permissions</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($admins as $admin)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($admin->avatar)
                                        <img src="{{ asset('storage/' . $admin->avatar) }}"
                                             class="img-circle mr-2" width="40" height="40" alt="Avatar">
                                    @else
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mr-2"
                                             style="width: 40px; height: 40px;">
                                            {{ strtoupper(substr($admin->name, 0, 2)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <strong>{{ $admin->name }}</strong>
                                        @if($admin->id === auth('admin')->id())
                                            <small class="badge badge-info">You</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>{{ $admin->email }}</td>
                            <td>
                                @if($admin->activeRoles->count() > 0)
                                    @foreach($admin->activeRoles as $role)
                                        <span class="badge badge-primary">
                                            <i class="fas fa-user-tag"></i> {{ $role->display_name }}
                                        </span>
                                    @endforeach
                                @else
                                    <span class="badge badge-secondary">No Role</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $permissions = $admin->getAllPermissions();
                                    $permissionCount = count($permissions);
                                @endphp
                                @if($permissionCount > 0)
                                    <span class="badge badge-info">
                                        {{ $permissionCount }} permission{{ $permissionCount > 1 ? 's' : '' }}
                                    </span>
                                    <small class="text-muted d-block">
                                        {{ implode(', ', array_slice($permissions, 0, 3)) }}
                                        @if($permissionCount > 3)
                                            +{{ $permissionCount - 3 }} more
                                        @endif
                                    </small>
                                @else
                                    <span class="badge badge-warning">No Permissions</span>
                                @endif
                            </td>
                            <td>
                                @if($admin->status === 'Active')
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>
                                @if($admin->last_login_at)
                                    {{ $admin->last_login_at->format('M d, Y H:i') }}
                                @else
                                    <span class="text-muted">Never</span>
                                @endif
                            </td>
                            <td>{{ $admin->created_at->format('M d, Y') }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('superadmin.admins.edit', $admin) }}"
                                       class="btn btn-primary btn-sm" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if($admin->id !== auth('admin')->id())
                                        <form action="{{ route('superadmin.admins.destroy', $admin) }}"
                                              method="POST" style="display: inline;"
                                              onsubmit="return confirm('Are you sure you want to delete this admin user? This action cannot be undone.')">
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
                                <p class="text-muted">Try adjusting your search criteria or create a new admin user.</p>
                                <a href="{{ route('superadmin.admins.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Create First Admin
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($admins->hasPages())
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        Showing {{ $admins->firstItem() }} to {{ $admins->lastItem() }}
                        of {{ $admins->total() }} admin users
                    </div>
                    {{ $admins->links() }}
                </div>
            </div>
        @endif
    </div>
@endsection

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

.table-responsive {
    border-radius: 0.375rem;
}
</style>
@endsection
