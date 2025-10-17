@extends('layouts.admin_master')

@section('title', 'Role Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Role Details: {{ $role->display_name }}</h3>
                    <div class="card-tools">
                        @can('manage_roles')
                        <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        @endcan
                        <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to Roles
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="150">Name:</th>
                                    <td><code>{{ $role->name }}</code></td>
                                </tr>
                                <tr>
                                    <th>Display Name:</th>
                                    <td><strong>{{ $role->display_name }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Level:</th>
                                    <td>
                                        <span class="badge badge-{{ $role->level >= 100 ? 'danger' : ($role->level >= 10 ? 'warning' : 'info') }}">
                                            Level {{ $role->level }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        @if($role->is_active)
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
                                <tr>
                                    <th width="150">Users Count:</th>
                                    <td><strong>{{ $role->users_count }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Permissions Count:</th>
                                    <td><strong>{{ $role->permissions_count }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Created:</th>
                                    <td>{{ $role->created_at->format('M d, Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>Updated:</th>
                                    <td>{{ $role->updated_at->format('M d, Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($role->description)
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group">
                                <label>Description:</label>
                                <p class="form-control-plaintext">{{ $role->description }}</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Assigned Users -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>Assigned Users ({{ $role->users->count() }})</h5>
                            @if($role->users->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Status</th>
                                                <th>Assigned At</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($role->users as $user)
                                            <tr>
                                                <td>{{ $user->name ?? 'N/A' }}</td>
                                                <td>{{ $user->email }}</td>
                                                <td>
                                                    @if($user->pivot->is_active ?? true)
                                                        <span class="badge badge-success">Active</span>
                                                    @else
                                                        <span class="badge badge-secondary">Inactive</span>
                                                    @endif
                                                </td>
                                                <td>{{ $user->pivot->created_at ? $user->pivot->created_at->format('M d, Y') : 'N/A' }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    No users are assigned to this role.
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Role Permissions -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5>Permissions ({{ $role->permissions->count() }})</h5>
                            @if($role->permissions->count() > 0)
                                @php
                                    $groupedPermissions = $role->permissions->groupBy('category');
                                @endphp

                                @foreach($groupedPermissions as $category => $permissions)
                                <div class="mb-3">
                                    <h6 class="text-primary">
                                        <i class="fas fa-folder"></i> {{ $category }} ({{ $permissions->count() }})
                                    </h6>
                                    <div class="row">
                                        @foreach($permissions as $permission)
                                        <div class="col-md-4 mb-2">
                                            <span class="badge badge-secondary">{{ $permission->display_name }}</span>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endforeach
                            @else
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    This role has no permissions assigned.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
