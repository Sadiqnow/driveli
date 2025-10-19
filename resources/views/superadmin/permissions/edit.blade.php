@extends('adminlte::page')

@section('title', 'Edit Permission')

@section('content_header')
    <h1>Edit Permission: {{ $permission->display_name }}</h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li><a href="{{ route('admin.superadmin.dashboard') }}">SuperAdmin</a></li>
        <li><a href="{{ route('superadmin.permissions.index') }}">Permissions</a></li>
        <li class="active">Edit</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Permission Information</h3>
            </div>

            <form method="POST" action="{{ route('superadmin.permissions.update', $permission) }}">
                @csrf
                @method('PUT')

                <div class="box-body">
                    <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                        <label for="name">Permission Name *</label>
                        <input type="text" class="form-control" id="name" name="name"
                               value="{{ old('name', $permission->name) }}" placeholder="e.g., manage_users" required>
                        <small class="help-block">Use lowercase letters, numbers, and underscores only</small>
                        @if($errors->has('name'))
                            <span class="help-block">{{ $errors->first('name') }}</span>
                        @endif
                    </div>

                    <div class="form-group {{ $errors->has('display_name') ? 'has-error' : '' }}">
                        <label for="display_name">Display Name *</label>
                        <input type="text" class="form-control" id="display_name" name="display_name"
                               value="{{ old('display_name', $permission->display_name) }}" placeholder="e.g., Manage Users" required>
                        @if($errors->has('display_name'))
                            <span class="help-block">{{ $errors->first('display_name') }}</span>
                        @endif
                    </div>

                    <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"
                                  placeholder="Describe what this permission allows">{{ old('description', $permission->description) }}</textarea>
                        @if($errors->has('description'))
                            <span class="help-block">{{ $errors->first('description') }}</span>
                        @endif
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group {{ $errors->has('category') ? 'has-error' : '' }}">
                                <label for="category">Category *</label>
                                <select class="form-control" id="category" name="category" required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $key => $name)
                                        <option value="{{ $key }}" {{ old('category', $permission->category) == $key ? 'selected' : '' }}>
                                            {{ $name }}
                                        </option>
                                    @endforeach
                                </select>
                                @if($errors->has('category'))
                                    <span class="help-block">{{ $errors->first('category') }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group {{ $errors->has('resource') ? 'has-error' : '' }}">
                                <label for="resource">Resource *</label>
                                <input type="text" class="form-control" id="resource" name="resource"
                                       value="{{ old('resource', $permission->resource) }}" placeholder="e.g., users" required>
                                <small class="help-block">What resource this permission controls</small>
                                @if($errors->has('resource'))
                                    <span class="help-block">{{ $errors->first('resource') }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group {{ $errors->has('action') ? 'has-error' : '' }}">
                                <label for="action">Action *</label>
                                <select class="form-control" id="action" name="action" required>
                                    <option value="">Select Action</option>
                                    @foreach($actions as $key => $name)
                                        <option value="{{ $key }}" {{ old('action', $permission->action) == $key ? 'selected' : '' }}>
                                            {{ $name }}
                                        </option>
                                    @endforeach
                                </select>
                                @if($errors->has('action'))
                                    <span class="help-block">{{ $errors->first('action') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="box-footer">
                    <button type="submit" class="btn btn-primary">Update Permission</button>
                    <a href="{{ route('superadmin.permissions.index') }}" class="btn btn-default">Cancel</a>
                    <button type="button" class="btn btn-danger pull-right" onclick="confirmDelete()">
                        <i class="fa fa-trash"></i> Delete Permission
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-4">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">Permission Statistics</h3>
            </div>
            <div class="box-body">
                <dl class="dl-horizontal">
                    <dt>Assigned to Roles:</dt>
                    <dd>{{ $permission->roles()->count() }}</dd>
                    <dt>Status:</dt>
                    <dd>
                        <span class="badge" :class="{{ $permission->is_active ? 'bg-green' : 'bg-red' }}">
                            {{ $permission->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </dd>
                    <dt>Created:</dt>
                    <dd>{{ $permission->created_at->format('M d, Y') }}</dd>
                    <dt>Last Updated:</dt>
                    <dd>{{ $permission->updated_at->format('M d, Y') }}</dd>
                </dl>
            </div>
        </div>

        @if($permission->roles()->count() > 0)
        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title">Assigned Roles</h3>
            </div>
            <div class="box-body">
                <ul class="list-unstyled">
                    @foreach($permission->roles()->take(5)->get() as $role)
                        <li><i class="fa fa-shield"></i> {{ $role->display_name }}</li>
                    @endforeach
                    @if($permission->roles()->count() > 5)
                        <li><em>... and {{ $permission->roles()->count() - 5 }} more</em></li>
                    @endif
                </ul>
            </div>
        </div>
        @endif

        <div class="box box-solid">
            <div class="box-header with-border">
                <h3 class="box-title">Permission Details</h3>
            </div>
            <div class="box-body">
                <p><strong>Full Name:</strong> {{ $permission->name }}</p>
                <p><strong>Pattern:</strong> {{ $permission->action }}_{{ $permission->resource }}</p>
                <p><strong>Category:</strong> {{ $categories[$permission->category] ?? $permission->category }}</p>
            </div>
        </div>
    </div>
</div>

<form id="deleteForm" method="POST" action="{{ route('superadmin.permissions.destroy', $permission) }}" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@section('js')
<script>
function confirmDelete() {
    if (confirm('Are you sure you want to delete this permission? This action cannot be undone and may affect users with this permission.')) {
        document.getElementById('deleteForm').submit();
    }
}
</script>
@endsection
