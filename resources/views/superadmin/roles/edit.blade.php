@extends('adminlte::page')

@section('title', 'Edit Role')

@section('content_header')
    <h1>Edit Role: {{ $role->display_name }}</h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li><a href="{{ route('admin.superadmin.dashboard') }}">SuperAdmin</a></li>
        <li><a href="{{ route('superadmin.roles.index') }}">Roles</a></li>
        <li class="active">Edit</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Role Information</h3>
            </div>

            <form method="POST" action="{{ route('superadmin.roles.update', $role) }}">
                @csrf
                @method('PUT')

                <div class="box-body">
                    <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                        <label for="name">Role Name *</label>
                        <input type="text" class="form-control" id="name" name="name"
                               value="{{ old('name', $role->name) }}" placeholder="e.g., content_moderator" required>
                        <small class="help-block">Use lowercase letters, numbers, and underscores only</small>
                        @if($errors->has('name'))
                            <span class="help-block">{{ $errors->first('name') }}</span>
                        @endif
                    </div>

                    <div class="form-group {{ $errors->has('display_name') ? 'has-error' : '' }}">
                        <label for="display_name">Display Name *</label>
                        <input type="text" class="form-control" id="display_name" name="display_name"
                               value="{{ old('display_name', $role->display_name) }}" placeholder="e.g., Content Moderator" required>
                        @if($errors->has('display_name'))
                            <span class="help-block">{{ $errors->first('display_name') }}</span>
                        @endif
                    </div>

                    <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"
                                  placeholder="Describe the role's purpose and responsibilities">{{ old('description', $role->description) }}</textarea>
                        @if($errors->has('description'))
                            <span class="help-block">{{ $errors->first('description') }}</span>
                        @endif
                    </div>

                    <div class="form-group {{ $errors->has('level') ? 'has-error' : '' }}">
                        <label for="level">Role Level *</label>
                        <select class="form-control" id="level" name="level" required>
                            <option value="">Select Level</option>
                            @foreach($roleLevels as $level => $name)
                                <option value="{{ $level }}" {{ old('level', $role->level) == $level ? 'selected' : '' }}>
                                    {{ $name }} (Level {{ $level }})
                                </option>
                            @endforeach
                        </select>
                        <small class="help-block">Higher levels have more privileges</small>
                        @if($errors->has('level'))
                            <span class="help-block">{{ $errors->first('level') }}</span>
                        @endif
                    </div>

                    <!-- Permissions Section -->
                    <div class="form-group">
                        <label>Permissions</label>
                        <div class="row">
                            @foreach($permissions->groupBy('category') as $category => $categoryPermissions)
                                <div class="col-md-6">
                                    <div class="box box-solid">
                                        <div class="box-header">
                                            <h4 class="box-title">{{ $category }}</h4>
                                        </div>
                                        <div class="box-body">
                                            @foreach($categoryPermissions as $permission)
                                                <div class="checkbox">
                                                    <label>
                                                        <input type="checkbox" name="permissions[]"
                                                               value="{{ $permission->id }}"
                                                               {{ in_array($permission->id, $rolePermissions) ? 'checked' : '' }}>
                                                        {{ $permission->display_name }}
                                                        <small class="text-muted">({{ $permission->name }})</small>
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @if($errors->has('permissions'))
                            <span class="help-block text-red">{{ $errors->first('permissions') }}</span>
                        @endif
                    </div>
                </div>

                <div class="box-footer">
                    <button type="submit" class="btn btn-primary">Update Role</button>
                    <a href="{{ route('superadmin.roles.index') }}" class="btn btn-default">Cancel</a>
                    @if(!in_array($role->name, ['super_admin', 'admin']))
                        <button type="button" class="btn btn-danger pull-right" onclick="confirmDelete()">
                            <i class="fa fa-trash"></i> Delete Role
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-4">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">Role Statistics</h3>
            </div>
            <div class="box-body">
                <dl class="dl-horizontal">
                    <dt>Active Users:</dt>
                    <dd>{{ $role->activeUsers()->count() }}</dd>
                    <dt>Total Permissions:</dt>
                    <dd>{{ $role->permissions()->count() }}</dd>
                    <dt>Created:</dt>
                    <dd>{{ $role->created_at->format('M d, Y') }}</dd>
                    <dt>Last Updated:</dt>
                    <dd>{{ $role->updated_at->format('M d, Y') }}</dd>
                </dl>
            </div>
        </div>

        @if($role->activeUsers()->count() > 0)
        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title">Active Users</h3>
            </div>
            <div class="box-body">
                <ul class="list-unstyled">
                    @foreach($role->activeUsers()->take(5)->get() as $user)
                        <li><i class="fa fa-user"></i> {{ $user->name }}</li>
                    @endforeach
                    @if($role->activeUsers()->count() > 5)
                        <li><em>... and {{ $role->activeUsers()->count() - 5 }} more</em></li>
                    @endif
                </ul>
            </div>
        </div>
        @endif
    </div>
</div>

@if(!in_array($role->name, ['super_admin', 'admin']))
<form id="deleteForm" method="POST" action="{{ route('superadmin.roles.destroy', $role) }}" style="display: none;">
    @csrf
    @method('DELETE')
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
</script>
@endsection
