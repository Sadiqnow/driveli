@extends('layouts.admin_master')

@section('title', 'Permission Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Permission Details</h3>
                    <div class="card-tools">
                        @can('manage_permissions')
                        <a href="{{ route('admin.permissions.edit', $permission) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        @endcan
                        <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to Permissions
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="150">Name:</th>
                                    <td><code>{{ $permission->name }}</code></td>
                                </tr>
                                <tr>
                                    <th>Display Name:</th>
                                    <td><strong>{{ $permission->display_name }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Category:</th>
                                    <td><span class="badge badge-info">{{ $permission->category }}</span></td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        @if($permission->is_active)
                                            <span class="badge badge-success">Active</span>
                                        @else
                                            <span class="badge badge-secondary">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <div class="col-md-6">
                            <table class="table table-borderless">
                                @if($permission->action)
                                <tr>
                                    <th width="150">Action:</th>
                                    <td>{{ $permission->action }}</td>
                                </tr>
                                @endif
                                @if($permission->resource)
                                <tr>
                                    <th>Resource:</th>
                                    <td>{{ $permission->resource }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <th>Created:</th>
                                    <td>{{ $permission->created_at->format('M d, Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>Updated:</th>
                                    <td>{{ $permission->updated_at->format('M d, Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($permission->description)
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label>Description:</label>
                                <p class="form-control-plaintext">{{ $permission->description }}</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Assigned Roles -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>Assigned to Roles ({{ $permission->roles->count() }})</h5>
                            @if($permission->roles->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Role Name</th>
                                                <th>Display Name</th>
                                                <th>Level</th>
                                                <th>Users Count</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($permission->roles as $role)
                                            <tr>
                                                <td><code>{{ $role->name }}</code></td>
                                                <td>{{ $role->display_name }}</td>
                                                <td>
                                                    <span class="badge badge-{{ $role->level >= 100 ? 'danger' : ($role->level >= 10 ? 'warning' : 'info') }}">
                                                        Level {{ $role->level }}
                                                    </span>
                                                </td>
                                                <td>{{ $role->users_count ?? 0 }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    This permission is not assigned to any roles.
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Meta Information -->
                    @if($permission->meta && count($permission->meta) > 0)
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>Additional Information</h5>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <tbody>
                                        @foreach($permission->meta as $key => $value)
                                        <tr>
                                            <th width="200">{{ ucfirst(str_replace('_', ' ', $key)) }}</th>
                                            <td>{{ is_array($value) ? json_encode($value) : $value }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
