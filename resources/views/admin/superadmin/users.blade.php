@extends('layouts.admin_master')

@section('title', 'SuperAdmin - User Management')

@section('content_header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">User Management</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.superadmin.dashboard') }}">SuperAdmin</a></li>
                        <li class="breadcrumb-item active">Users</li>
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
                        <h3>{{ $users->total() }}</h3>
                        <p>Total Admin Users</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users-cog"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $users->where('status', 'Active')->count() }}</h3>
                        <p>Active Users</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-check"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $users->where('role', 'Super Admin')->count() }}</h3>
                        <p>Super Admins</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-crown"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-secondary">
                    <div class="inner">
                        <h3>{{ $users->whereNull('deleted_at')->count() }}</h3>
                        <p>Active Records</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-database"></i>
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
                        Search & Filter Users
                    </h3>
                    <div>
                        <button class="btn btn-warning mr-2" onclick="bulkOperations()">
                            <i class="fas fa-tasks"></i> Bulk Actions
                        </button>
                        <a href="{{ route('admin.superadmin.admins.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus mr-1"></i> Create Admin
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" class="row g-3" id="search-form">
                    <div class="col-md-4">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control" 
                               placeholder="Search by name, email..." 
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-control">
                            <option value="">All Roles</option>
                            <option value="Super Admin" {{ request('role') == 'Super Admin' ? 'selected' : '' }}>Super Admin</option>
                            <option value="Admin" {{ request('role') == 'Admin' ? 'selected' : '' }}>Admin</option>
                            <option value="Moderator" {{ request('role') == 'Moderator' ? 'selected' : '' }}>Moderator</option>
                            <option value="Viewer" {{ request('role') == 'Viewer' ? 'selected' : '' }}>Viewer</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="Active" {{ request('status') == 'Active' ? 'selected' : '' }}>Active</option>
                            <option value="Inactive" {{ request('status') == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Include Deleted</label>
                        <select name="with_trashed" class="form-control">
                            <option value="0" {{ request('with_trashed') == '0' ? 'selected' : '' }}>No</option>
                            <option value="1" {{ request('with_trashed') == '1' ? 'selected' : '' }}>Yes</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">&nbsp;</label>
                        <div>
                            <button class="btn btn-success w-100 mb-1">
                                <i class="fas fa-search"></i> Search
                            </button>
                            <a href="{{ route('admin.superadmin.users') }}" class="btn btn-secondary w-100">
                                Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Users Table -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-users mr-1"></i>
                    Admin Users Directory
                </h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="users-table">
                        <thead>
                            <tr>
                                <th width="40">
                                    <input type="checkbox" id="select-all">
                                </th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Last Login</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr class="{{ $user->deleted_at ? 'table-danger' : '' }}">
                                    <td>
                                        <input type="checkbox" class="user-checkbox" value="{{ $user->id }}">
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="user-initials bg-{{ $user->status === 'Active' ? 'info' : 'secondary' }} text-white rounded-circle mr-2" 
                                                 style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center; font-size: 14px;">
                                                {{ $user->initials }}
                                            </div>
                                            <div>
                                                <strong>{{ $user->name }}</strong>
                                                @if($user->deleted_at)
                                                    <small class="badge badge-danger">Deleted</small>
                                                @endif
                                                @if($user->id === auth('admin')->id())
                                                    <small class="badge badge-info">You</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        @switch($user->role)
                                            @case('Super Admin')
                                                <span class="badge badge-danger">
                                                    <i class="fas fa-crown"></i> Super Admin
                                                </span>
                                                @break
                                            @case('Admin')
                                                <span class="badge badge-primary">
                                                    <i class="fas fa-user-shield"></i> Admin
                                                </span>
                                                @break
                                            @case('Moderator')
                                                <span class="badge badge-warning">
                                                    <i class="fas fa-user-cog"></i> Moderator
                                                </span>
                                                @break
                                            @case('Viewer')
                                                <span class="badge badge-secondary">
                                                    <i class="fas fa-eye"></i> Viewer
                                                </span>
                                                @break
                                            @default
                                                <span class="badge badge-light">{{ $user->role }}</span>
                                        @endswitch
                                    </td>
                                    <td>
                                        @if($user->status === 'Active')
                                            <span class="badge badge-success">Active</span>
                                        @else
                                            <span class="badge badge-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($user->last_login_at)
                                            {{ $user->last_login_at->format('M d, Y H:i') }}
                                            <br><small class="text-muted">{{ $user->last_login_at->diffForHumans() }}</small>
                                        @else
                                            <span class="text-muted">Never</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $user->created_at->format('M d, Y') }}
                                        <br><small class="text-muted">{{ $user->created_at->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-info btn-sm" onclick="viewUser({{ $user->id }})" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            
                                            @if(!$user->deleted_at)
                                                <button class="btn btn-warning btn-sm" onclick="assignRole({{ $user->id }}, '{{ $user->name }}')" title="Assign Role">
                                                    <i class="fas fa-user-tag"></i>
                                                </button>
                                                
                                                @if($user->status === 'Active')
                                                    <button class="btn btn-secondary btn-sm" onclick="toggleUserStatus({{ $user->id }}, 'deactivate')" title="Deactivate">
                                                        <i class="fas fa-ban"></i>
                                                    </button>
                                                @else
                                                    <button class="btn btn-success btn-sm" onclick="toggleUserStatus({{ $user->id }}, 'activate')" title="Activate">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                @endif
                                                
                                                @if($user->id !== auth('admin')->id())
                                                    <button class="btn btn-danger btn-sm" onclick="deleteUser({{ $user->id }}, '{{ $user->name }}')" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @endif
                                            @else
                                                <button class="btn btn-outline-success btn-sm" onclick="restoreUser({{ $user->id }}, '{{ $user->name }}')" title="Restore">
                                                    <i class="fas fa-undo"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4">
                                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">No users found</h5>
                                        <p class="text-muted">Try adjusting your search criteria or add a new user.</p>
                                        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                                            <i class="fas fa-plus"></i> Add First User
                                        </a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            @if($users->hasPages())
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted">
                            Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} 
                            of {{ $users->total() }} users
                        </div>
                        {{ $users->appends(request()->query())->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Role Assignment Modal -->
    <div class="modal fade" id="roleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Assign Role</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p>Assign role to: <strong id="role-user-name"></strong></p>
                    <div class="form-group">
                        <label>Select Role</label>
                        <select class="form-control" id="role-select">
                            <option value="Super Admin">Super Admin</option>
                            <option value="Admin">Admin</option>
                            <option value="Moderator">Moderator</option>
                            <option value="Viewer">Viewer</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="submitRoleAssignment()">Assign Role</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Operations Modal -->
    <div class="modal fade" id="bulkModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Bulk Operations</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Select Action</label>
                        <select class="form-control" id="bulk-action">
                            <option value="activate">Activate Users</option>
                            <option value="deactivate">Deactivate Users</option>
                            <option value="assign_role">Assign Role</option>
                            <option value="remove_role">Remove Role (Set to Viewer)</option>
                        </select>
                    </div>
                    <div class="form-group" id="bulk-role-group" style="display: none;">
                        <label>Role to Assign</label>
                        <select class="form-control" id="bulk-role-select">
                            <option value="Super Admin">Super Admin</option>
                            <option value="Admin">Admin</option>
                            <option value="Moderator">Moderator</option>
                            <option value="Viewer">Viewer</option>
                        </select>
                    </div>
                    <p><span id="selected-count">0</span> user(s) selected</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="submitBulkOperation()">Execute</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
let currentUserId = null;

$(document).ready(function() {
    // Select all checkbox
    $('#select-all').change(function() {
        $('.user-checkbox').prop('checked', $(this).prop('checked'));
        updateSelectedCount();
    });
    
    $('.user-checkbox').change(function() {
        updateSelectedCount();
    });
    
    // Show/hide role select for bulk operations
    $('#bulk-action').change(function() {
        if($(this).val() === 'assign_role') {
            $('#bulk-role-group').show();
        } else {
            $('#bulk-role-group').hide();
        }
    });
});

function updateSelectedCount() {
    const count = $('.user-checkbox:checked').length;
    $('#selected-count').text(count);
}

function assignRole(userId, userName) {
    currentUserId = userId;
    $('#role-user-name').text(userName);
    $('#roleModal').modal('show');
}

function submitRoleAssignment() {
    const roleId = $('#role-select').val();
    
    $.ajax({
        url: '{{ route("admin.superadmin.assign-role") }}',
        method: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            user_id: currentUserId,
            role_name: roleId
        },
        success: function(response) {
            if(response.success) {
                toastr.success(response.message);
                location.reload();
            } else {
                toastr.error(response.message);
            }
        },
        error: function() {
            toastr.error('Failed to assign role');
        }
    });
    
    $('#roleModal').modal('hide');
}

function toggleUserStatus(userId, action) {
    const actionText = action === 'activate' ? 'activate' : 'deactivate';
    
    if(confirm(`Are you sure you want to ${actionText} this user?`)) {
        $.ajax({
            url: '{{ route("admin.superadmin.bulk-user-operations") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                user_ids: [userId],
                action: action
            },
            success: function(response) {
                if(response.success) {
                    toastr.success('User status updated successfully');
                    location.reload();
                } else {
                    toastr.error('Failed to update user status');
                }
            },
            error: function() {
                toastr.error('Failed to update user status');
            }
        });
    }
}

function deleteUser(userId, userName) {
    if(confirm(`Are you sure you want to delete user: ${userName}?\n\nThis action can be undone later.`)) {
        // Use Laravel's delete route if available, otherwise use soft delete via status change
        location.href = `{{ route('admin.users.index') }}/${userId}/delete`;
    }
}

function restoreUser(userId, userName) {
    if(confirm(`Are you sure you want to restore user: ${userName}?`)) {
        // Implementation depends on your restore route
        toastr.info('Restore functionality needs to be implemented');
    }
}

function bulkOperations() {
    const selectedCount = $('.user-checkbox:checked').length;
    if(selectedCount === 0) {
        toastr.warning('Please select at least one user');
        return;
    }
    updateSelectedCount();
    $('#bulkModal').modal('show');
}

function submitBulkOperation() {
    const selectedUsers = $('.user-checkbox:checked').map(function() {
        return $(this).val();
    }).get();
    
    const action = $('#bulk-action').val();
    const roleId = $('#bulk-role-select').val();
    
    const data = {
        _token: '{{ csrf_token() }}',
        user_ids: selectedUsers,
        action: action
    };
    
    if(action === 'assign_role') {
        data.role_name = roleId;
    }
    
    $.ajax({
        url: '{{ route("admin.superadmin.bulk-user-operations") }}',
        method: 'POST',
        data: data,
        success: function(response) {
            if(response.success) {
                toastr.success(`Bulk operation completed for ${selectedUsers.length} user(s)`);
                location.reload();
            } else {
                toastr.error('Some operations failed. Please check the results.');
            }
        },
        error: function() {
            toastr.error('Bulk operation failed');
        }
    });
    
    $('#bulkModal').modal('hide');
}

function viewUser(userId) {
    // Open user details in new tab or modal
    window.open(`{{ route('admin.users.index') }}/${userId}`, '_blank');
}
</script>
@endpush

@push('styles')
<style>
.small-box {
    border-radius: 10px;
    transition: transform 0.3s ease;
}

.small-box:hover {
    transform: translateY(-2px);
}

.table-danger {
    background-color: rgba(248, 215, 218, 0.3);
}

.user-initials {
    font-weight: bold;
}

.btn-group .btn {
    margin-right: 2px;
}

.badge {
    font-size: 0.8em;
}
</style>
@endpush