@extends('adminlte::page')

@section('title', 'Create Role')

@section('content_header')
    <h1>Create New Role</h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li><a href="{{ route('admin.superadmin.dashboard') }}">SuperAdmin</a></li>
        <li><a href="{{ route('superadmin.roles.index') }}">Roles</a></li>
        <li class="active">Create</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Role Information</h3>
            </div>

            <form method="POST" action="{{ route('superadmin.roles.store') }}">
                @csrf

                <div class="box-body">
                    <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                        <label for="name">Role Name *</label>
                        <input type="text" class="form-control" id="name" name="name"
                               value="{{ old('name') }}" placeholder="e.g., content_moderator" required>
                        <small class="help-block">Use lowercase letters, numbers, and underscores only</small>
                        @if($errors->has('name'))
                            <span class="help-block">{{ $errors->first('name') }}</span>
                        @endif
                    </div>

                    <div class="form-group {{ $errors->has('display_name') ? 'has-error' : '' }}">
                        <label for="display_name">Display Name *</label>
                        <input type="text" class="form-control" id="display_name" name="display_name"
                               value="{{ old('display_name') }}" placeholder="e.g., Content Moderator" required>
                        @if($errors->has('display_name'))
                            <span class="help-block">{{ $errors->first('display_name') }}</span>
                        @endif
                    </div>

                    <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"
                                  placeholder="Describe the role's purpose and responsibilities">{{ old('description') }}</textarea>
                        @if($errors->has('description'))
                            <span class="help-block">{{ $errors->first('description') }}</span>
                        @endif
                    </div>

                    <div class="form-group {{ $errors->has('level') ? 'has-error' : '' }}">
                        <label for="level">Role Level *</label>
                        <select class="form-control" id="level" name="level" required>
                            <option value="">Select Level</option>
                            @foreach($roleLevels as $level => $name)
                                <option value="{{ $level }}" {{ old('level') == $level ? 'selected' : '' }}>
                                    {{ $name }} (Level {{ $level }})
                                </option>
                            @endforeach
                        </select>
                        <small class="help-block">Higher levels have more privileges</small>
                        @if($errors->has('level'))
                            <span class="help-block">{{ $errors->first('level') }}</span>
                        @endif
                    </div>
                </div>

                <div class="box-footer">
                    <button type="submit" class="btn btn-primary">Create Role</button>
                    <a href="{{ route('superadmin.roles.index') }}" class="btn btn-default">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-4">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">Role Guidelines</h3>
            </div>
            <div class="box-body">
                <ul class="list-unstyled">
                    <li><i class="fa fa-info-circle text-info"></i> Role names should be descriptive and follow naming conventions</li>
                    <li><i class="fa fa-info-circle text-info"></i> Higher level numbers indicate more privileges</li>
                    <li><i class="fa fa-warning text-warning"></i> System roles (super_admin, admin) cannot be modified</li>
                    <li><i class="fa fa-check-circle text-success"></i> Permissions can be assigned after role creation</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
