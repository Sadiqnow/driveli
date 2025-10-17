@extends('layouts.admin_master')

@section('title', 'Permission Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">System Permissions</h3>
                    <div class="card-tools">
                        @can('manage_permissions')
                        <a href="{{ route('admin.permissions.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Create Permission
                        </a>
                        @endcan
                    </div>
                </div>

                <div class="card-body">
                    <!-- Search and Filters -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <form method="GET" class="d-flex">
                                <input type="text" name="search" class="form-control" placeholder="Search permissions..." value="{{ request('search') }}">
                                <button type="submit" class="btn btn-outline-secondary ml-2">
                                    <i class="fas fa-search"></i>
                                </button>
                            </form>
                        </div>
                        <div class="col-md-3">
                            <form method="GET">
                                <select name="category" class="form-control" onchange="this.form.submit()">
                                    <option value="">All Categories</option>
                                    @foreach(\App\Models\Permission::getCategories() as $cat)
                                        <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                        <div class="col-md-3">
                            <form method="GET">
                                <select name="status" class="form-control" onchange="this.form.submit()">
                                    <option value="">All Status</option>
                                    <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </form>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary btn-block">Reset</a>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Display Name</th>
                                    <th>Category</th>
                                    <th>Status</th>
                                    <th>Roles Count</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($permissions as $permission)
                                <tr>
                                    <td>
                                        <code>{{ $permission->name }}</code>
                                    </td>
                                    <td>
                                        <strong>{{ $permission->display_name }}</strong>
                                        @if($permission->description)
                                        <br><small class="text-muted">{{ Str::limit($permission->description, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-info">{{ $permission->category }}</span>
                                    </td>
                                    <td>
                                        @if($permission->is_active)
                                            <span class="badge badge-success">Active</span>
                                        @else
                                            <span class="badge badge-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-warning">{{ $permission->roles_count }}</span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            @can('view_permissions')
                                            <a href="{{ route('admin.permissions.show', $permission) }}" class="btn btn-info btn-sm" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @endcan
                                            @can('manage_permissions')
                                            <a href="{{ route('admin.permissions.edit', $permission) }}" class="btn btn-warning btn-sm" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @if($permission->roles_count == 0)
                                            <button class="btn btn-danger btn-sm delete-permission" data-permission-id="{{ $permission->id }}" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            @endif
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer">
                    {{ $permissions->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Permission</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="deleteForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <p>Are you sure you want to delete this permission?</p>
                    <p class="text-danger">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    $('.delete-permission').click(function() {
        const permissionId = $(this).data('permission-id');
        $('#deleteForm').attr('action', '{{ route("admin.permissions.index") }}/' + permissionId);
        $('#deleteModal').modal('show');
    });
});
</script>
@endsection
