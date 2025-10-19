@extends('adminlte::page')

@section('title', 'Role Details')

@section('content_header')
    <h1>Role: {{ $role->display_name }}</h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li><a href="{{ route('admin.superadmin.dashboard') }}">SuperAdmin</a></li>
        <li><a href="{{ route('superadmin.roles.index') }}">Roles</a></li>
        <li class="active">{{ $role->display_name }}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <!-- Role Information -->
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Role Information</h3>
                <div class="box-tools">
                    <a href="{{ route('superadmin.roles.edit', $role) }}" class="btn btn-warning btn-sm">
                        <i class="fa fa-edit"></i> Edit
                    </a>
                </div>
            </div>
            <div class="box-body">
                <dl class="dl-horizontal">
                    <dt>Name:</dt>
                    <dd>{{ $role->name }}</dd>
                    <dt>Display Name:</dt>
                    <dd>{{ $role->display_name }}</dd>
                    <dt>Description:</dt>
                    <dd>{{ $role->description ?: 'No description provided' }}</dd>
                    <dt>Level:</dt>
                    <dd>
                        <span class="badge" :class="getLevelBadgeClass({{ $role->level }})">
                            Level {{ $role->level }}
                        </span>
                    </dd>
                    <dt>Status:</dt>
                    <dd>
                        <span class="badge" :class="{{ $role->is_active ? 'bg-green' : 'bg-red' }}">
                            {{ $role->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </dd>
                    <dt>Created:</dt>
                    <dd>{{ $role->created_at->format('M d, Y H:i') }}</dd>
                    <dt>Last Updated:</dt>
                    <dd>{{ $role->updated_at->format('M d, Y H:i') }}</dd>
                </dl>
            </div>
        </div>

        <!-- Permissions -->
        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title">Permissions ({{ $role->permissions->count() }})</h3>
            </div>
            <div class="box-body">
                @if($role->permissions->count() > 0)
                    <div class="row">
                        @foreach($role->permissions->groupBy('category') as $category => $permissions)
                            <div class="col-md-6">
                                <h4>{{ $category }}</h4>
                                <ul class="list-unstyled">
                                    @foreach($permissions as $permission)
                                        <li>
                                            <i class="fa fa-check text-green"></i>
                                            {{ $permission->display_name }}
                                            <small class="text-muted">({{ $permission->name }})</small>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted">No permissions assigned to this role.</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Statistics -->
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">Statistics</h3>
            </div>
            <div class="box-body">
                <div class="info-box">
                    <span class="info-box-icon bg-blue"><i class="fa fa-users"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Active Users</span>
                        <span class="info-box-number">{{ $role->activeUsers()->count() }}</span>
                    </div>
                </div>
                <div class="info-box">
                    <span class="info-box-icon bg-green"><i class="fa fa-key"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Permissions</span>
                        <span class="info-box-number">{{ $role->permissions->count() }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Users -->
        @if($role->activeUsers()->count() > 0)
        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title">Active Users ({{ $role->activeUsers()->count() }})</h3>
            </div>
            <div class="box-body">
                <ul class="list-unstyled">
                    @foreach($role->activeUsers()->take(10)->get() as $user)
                        <li>
                            <i class="fa fa-user"></i>
                            <a href="{{ route('admin.users.show', $user) }}">{{ $user->name }}</a>
                            <small class="text-muted">({{ $user->email }})</small>
                        </li>
                    @endforeach
                    @if($role->activeUsers()->count() > 10)
                        <li><em>... and {{ $role->activeUsers()->count() - 10 }} more</em></li>
                    @endif
                </ul>
            </div>
        </div>
        @endif

        <!-- Quick Actions -->
        <div class="box box-solid">
            <div class="box-header with-border">
                <h3 class="box-title">Quick Actions</h3>
            </div>
            <div class="box-body">
                <div class="btn-group-vertical btn-block">
                    <a href="{{ route('superadmin.roles.edit', $role) }}" class="btn btn-warning">
                        <i class="fa fa-edit"></i> Edit Role
                    </a>
                    @if(!in_array($role->name, ['super_admin', 'admin']))
                        <button class="btn btn-danger" onclick="confirmDelete()">
                            <i class="fa fa-trash"></i> Delete Role
                        </button>
                        <button class="btn btn-default" onclick="toggleStatus()">
                            <i class="fa fa-ban"></i> {{ $role->is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@if(!in_array($role->name, ['super_admin', 'admin']))
<form id="deleteForm" method="POST" action="{{ route('superadmin.roles.destroy', $role) }}" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<form id="toggleForm" method="POST" action="{{ route('superadmin.roles.toggle-status', $role) }}" style="display: none;">
    @csrf
    @method('PATCH')
</form>
@endif
@endsection

@section('js')
<script>
function confirmDelete() {
    if (confirm('Are you sure you want to delete this role? This action cannot be undone.')) {
        document.getElementById('deleteForm').submit();
    }
}

function toggleStatus() {
    if (confirm('Are you sure you want to {{ $role->is_active ? 'deactivate' : 'activate' }} this role?')) {
        document.getElementById('toggleForm').submit();
    }
}
</script>
@endsection
