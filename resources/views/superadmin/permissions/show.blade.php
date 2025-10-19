@extends('adminlte::page')

@section('title', 'Permission Details')

@section('content_header')
    <h1>Permission: {{ $permission->display_name }}</h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li><a href="{{ route('admin.superadmin.dashboard') }}">SuperAdmin</a></li>
        <li><a href="{{ route('superadmin.permissions.index') }}">Permissions</a></li>
        <li class="active">{{ $permission->display_name }}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <!-- Permission Information -->
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Permission Information</h3>
                <div class="box-tools">
                    <a href="{{ route('superadmin.permissions.edit', $permission) }}" class="btn btn-warning btn-sm">
                        <i class="fa fa-edit"></i> Edit
                    </a>
                </div>
            </div>
            <div class="box-body">
                <dl class="dl-horizontal">
                    <dt>Name:</dt>
                    <dd>{{ $permission->name }}</dd>
                    <dt>Display Name:</dt>
                    <dd>{{ $permission->display_name }}</dd>
                    <dt>Description:</dt>
                    <dd>{{ $permission->description ?: 'No description provided' }}</dd>
                    <dt>Category:</dt>
                    <dd>{{ $categories[$permission->category] ?? $permission->category }}</dd>
                    <dt>Resource:</dt>
                    <dd>{{ $permission->resource }}</dd>
                    <dt>Action:</dt>
                    <dd>{{ $permission->action }}</dd>
                    <dt>Status:</dt>
                    <dd>
                        <span class="badge" :class="{{ $permission->is_active ? 'bg-green' : 'bg-red' }}">
                            {{ $permission->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </dd>
                    <dt>Created:</dt>
                    <dd>{{ $permission->created_at->format('M d, Y H:i') }}</dd>
                    <dt>Last Updated:</dt>
                    <dd>{{ $permission->updated_at->format('M d, Y H:i') }}</dd>
                </dl>
            </div>
        </div>

        <!-- Assigned Roles -->
        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title">Assigned Roles ({{ $permission->roles->count() }})</h3>
            </div>
            <div class="box-body">
                @if($permission->roles->count() > 0)
                    <div class="row">
                        @foreach($permission->roles->sortBy('level') as $role)
                            <div class="col-md-4">
                                <div class="info-box">
                                    <span class="info-box-icon" :class="getRoleIconClass({{ $role->level }})">
                                        <i class="fa fa-shield"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">{{ $role->display_name }}</span>
                                        <span class="info-box-number">Level {{ $role->level }}</span>
                                        <div class="progress">
                                            <div class="progress-bar" style="width: {{ ($role->level / 100) * 100 }}%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted">This permission is not assigned to any roles.</p>
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
                    <span class="info-box-icon bg-blue"><i class="fa fa-shield"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Assigned Roles</span>
                        <span class="info-box-number">{{ $permission->roles->count() }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Permission Pattern -->
        <div class="box box-solid">
            <div class="box-header with-border">
                <h3 class="box-title">Permission Pattern</h3>
            </div>
            <div class="box-body">
                <p><strong>Pattern:</strong> <code>{{ $permission->action }}_{{ $permission->resource }}</code></p>
                <p><strong>Category:</strong> {{ $categories[$permission->category] ?? $permission->category }}</p>
                <p><strong>Scope:</strong> {{ ucfirst($permission->action) }} operations on {{ $permission->resource }}</p>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="box box-solid">
            <div class="box-header with-border">
                <h3 class="box-title">Quick Actions</h3>
            </div>
            <div class="box-body">
                <div class="btn-group-vertical btn-block">
                    <a href="{{ route('superadmin.permissions.edit', $permission) }}" class="btn btn-warning">
                        <i class="fa fa-edit"></i> Edit Permission
                    </a>
                    <button class="btn btn-default" onclick="toggleStatus()">
                        <i class="fa fa-ban"></i> {{ $permission->is_active ? 'Deactivate' : 'Activate' }}
                    </button>
                    <button class="btn btn-danger" onclick="confirmDelete()">
                        <i class="fa fa-trash"></i> Delete Permission
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="deleteForm" method="POST" action="{{ route('superadmin.permissions.destroy', $permission) }}" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<form id="toggleForm" method="POST" action="{{ route('superadmin.permissions.toggle-status', $permission) }}" style="display: none;">
    @csrf
    @method('PATCH')
</form>
@endsection

@section('js')
<script>
function confirmDelete() {
    if (confirm('Are you sure you want to delete this permission? This action cannot be undone and may affect users with this permission.')) {
        document.getElementById('deleteForm').submit();
    }
}

function toggleStatus() {
    if (confirm('Are you sure you want to {{ $permission->is_active ? 'deactivate' : 'activate' }} this permission?')) {
        document.getElementById('toggleForm').submit();
    }
}

function getRoleIconClass(level) {
    if (level >= 90) return 'bg-red';
    if (level >= 70) return 'bg-yellow';
    if (level >= 50) return 'bg-blue';
    return 'bg-gray';
}
</script>
@endsection
