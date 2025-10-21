@extends('layouts.admin_master')

@section('title', 'Route Permission Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-route"></i> Route Permission Management
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-success btn-sm" onclick="syncRoutes()">
                            <i class="fas fa-sync"></i> Sync Routes
                        </button>
                        <button type="button" class="btn btn-info btn-sm" onclick="exportMappings()">
                            <i class="fas fa-download"></i> Export
                        </button>
                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#bulkUpdateModal">
                            <i class="fas fa-edit"></i> Bulk Update
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="routePermissionsTable">
                            <thead>
                                <tr>
                                    <th>Route Name</th>
                                    <th>URI</th>
                                    <th>Methods</th>
                                    <th>Current Permission</th>
                                    <th>Description</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($routes as $route)
                                <tr>
                                    <td>{{ $route['name'] }}</td>
                                    <td>{{ $route['uri'] }}</td>
                                    <td>
                                        @foreach($route['methods'] as $method)
                                            <span class="badge badge-secondary">{{ $method }}</span>
                                        @endforeach
                                    </td>
                                    <td>
                                        @php
                                            $routePermission = $routePermissions->get($route['name']);
                                        @endphp
                                        @if($routePermission)
                                            <span class="badge badge-success">{{ $routePermission->permission->name }}</span>
                                        @else
                                            <span class="badge badge-warning">Not Set</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $routePermission ? $routePermission->description : '-' }}
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary"
                                                onclick="editRoutePermission('{{ $route['name'] }}', '{{ $routePermission ? $routePermission->permission_id : '' }}', '{{ $routePermission ? $routePermission->description : '' }}')">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Route Permission Modal -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Route Permission</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="editForm">
                <div class="modal-body">
                    <input type="hidden" id="editRouteName" name="route_name">

                    <div class="form-group">
                        <label for="editPermission">Permission</label>
                        <select class="form-control" id="editPermission" name="permission_id" required>
                            <option value="">Select Permission</option>
                            @foreach($permissions as $permission)
                                <option value="{{ $permission->id }}">{{ $permission->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="editDescription">Description</label>
                        <textarea class="form-control" id="editDescription" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Update Modal -->
<div class="modal fade" id="bulkUpdateModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bulk Update Route Permissions</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="bulkUpdateForm">
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Route Name</th>
                                    <th>URI</th>
                                    <th>Permission</th>
                                </tr>
                            </thead>
                            <tbody id="bulkUpdateTable">
                                @foreach($routes as $route)
                                <tr>
                                    <td>{{ $route['name'] }}</td>
                                    <td>{{ $route['uri'] }}</td>
                                    <td>
                                        <select class="form-control form-control-sm" name="mappings[{{ $route['name'] }}][permission_id]">
                                            <option value="">No Permission</option>
                                            @foreach($permissions as $permission)
                                                @php $routePermission = $routePermissions->get($route['name']); @endphp
                                                <option value="{{ $permission->id }}" {{ $routePermission && $routePermission->permission_id == $permission->id ? 'selected' : '' }}>
                                                    {{ $permission->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" name="mappings[{{ $route['name'] }}][route_name]" value="{{ $route['name'] }}">
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update All</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    $('#routePermissionsTable').DataTable({
        "pageLength": 25,
        "order": [[0, 'asc']]
    });
});

function editRoutePermission(routeName, permissionId, description) {
    $('#editRouteName').val(routeName);
    $('#editPermission').val(permissionId);
    $('#editDescription').val(description);
    $('#editModal').modal('show');
}

$('#editForm').on('submit', function(e) {
    e.preventDefault();

    $.ajax({
        url: '{{ route("admin.route-permissions.store") }}',
        method: 'POST',
        data: $(this).serialize(),
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                $('#editModal').modal('hide');
                location.reload();
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function(xhr) {
            alert('Error updating route permission');
        }
    });
});

$('#bulkUpdateForm').on('submit', function(e) {
    e.preventDefault();

    $.ajax({
        url: '{{ route("admin.route-permissions.bulk-update") }}',
        method: 'POST',
        data: $(this).serialize(),
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                $('#bulkUpdateModal').modal('hide');
                location.reload();
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function(xhr) {
            alert('Error updating route permissions');
        }
    });
});

function syncRoutes() {
    if (confirm('This will sync all application routes with the database. Continue?')) {
        $.ajax({
            url: '{{ route("admin.route-permissions.sync-routes") }}',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    alert('Routes synchronized successfully');
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr) {
                alert('Error syncing routes');
            }
        });
    }
}

function exportMappings() {
    window.location.href = '{{ route("admin.route-permissions.export") }}';
}
</script>
@endsection
