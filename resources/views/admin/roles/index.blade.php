@extends('layouts.admin_cdn')

@section('title', 'Role Management')

@section('content_header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Role Management</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Roles</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">System Roles</h3>
                        <div class="card-tools">
                            @if(auth('admin')->user()->hasPermission('create_roles'))
                            <a href="{{ route('admin.roles.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> Create Role
                            </a>
                            @endif
                        </div>
                    </div>
                    
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Display Name</th>
                                        <th>Level</th>
                                        <th>Users Count</th>
                                        <th>Permissions Count</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($roles as $role)
                                    <tr>
                                        <td>
                                            <code>{{ $role->name }}</code>
                                        </td>
                                        <td>
                                            <strong>{{ $role->display_name }}</strong>
                                            @if($role->description)
                                            <br><small class="text-muted">{{ Str::limit($role->description, 50) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $role->level >= 100 ? 'danger' : ($role->level >= 10 ? 'warning' : 'info') }}">
                                                Level {{ $role->level }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-secondary">{{ $role->users_count }}</span>
                                        </td>
                                        <td>
                                            <span class="badge badge-info">{{ $role->permissions_count }}</span>
                                        </td>
                                        <td>
                                            @if($role->name !== 'super_admin')
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" class="custom-control-input role-status-toggle" 
                                                       id="status{{ $role->id }}" 
                                                       data-role-id="{{ $role->id }}" 
                                                       {{ $role->is_active ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="status{{ $role->id }}"></label>
                                            </div>
                                            @else
                                            <span class="badge badge-success">Always Active</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                @if(auth('admin')->user()->hasPermission('view_roles'))
                                                <a href="{{ route('admin.roles.show', $role) }}" class="btn btn-info btn-sm" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @endif
                                                
                                                @if(auth('admin')->user()->hasPermission('edit_roles') && $role->name !== 'super_admin')
                                                <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-warning btn-sm" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                @endif
                                                
                                                @if(auth('admin')->user()->hasPermission('delete_roles') && $role->name !== 'super_admin' && $role->users_count == 0)
                                                <button class="btn btn-danger btn-sm delete-role" data-role-id="{{ $role->id }}" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        {{ $roles->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Role status toggle
    $('.role-status-toggle').change(function() {
        const roleId = $(this).data('role-id');
        const isActive = $(this).is(':checked');
        
        $.post('{{ route('admin.roles.index') }}/' + roleId + '/toggle-status', {
            _token: '{{ csrf_token() }}'
        })
        .done(function(response) {
            if (response.success) {
                toastr.success(response.message);
            } else {
                toastr.error(response.message);
                // Revert the toggle
                $(this).prop('checked', !isActive);
            }
        })
        .fail(function() {
            toastr.error('Failed to update role status');
            // Revert the toggle
            $(this).prop('checked', !isActive);
        });
    });
    
    // Delete role
    $('.delete-role').click(function() {
        const roleId = $(this).data('role-id');
        const row = $(this).closest('tr');
        
        if (confirm('Are you sure you want to delete this role? This action cannot be undone.')) {
            $.ajax({
                url: '{{ route('admin.roles.index') }}/' + roleId,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                }
            })
            .done(function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    row.fadeOut(function() {
                        $(this).remove();
                    });
                } else {
                    toastr.error(response.message);
                }
            })
            .fail(function() {
                toastr.error('Failed to delete role');
            });
        }
    });
});
</script>
@endpush