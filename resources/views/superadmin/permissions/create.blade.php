@extends('adminlte::page')

@section('title', 'Create Permission')

@section('content_header')
    <h1>Create New Permission</h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.dashboard') }}"><i class="fa fa-dashboard"></i> Dashboard</a></li>
        <li><a href="{{ route('admin.superadmin.dashboard') }}">SuperAdmin</a></li>
        <li><a href="{{ route('superadmin.permissions.index') }}">Permissions</a></li>
        <li class="active">Create</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Permission Information</h3>
            </div>

            <form method="POST" action="{{ route('superadmin.permissions.store') }}">
                @csrf

                <div class="box-body">
                    <div class="form-group {{ $errors->has('name') ? 'has-error' : '' }}">
                        <label for="name">Permission Name *</label>
                        <input type="text" class="form-control" id="name" name="name"
                               value="{{ old('name') }}" placeholder="e.g., manage_users" required>
                        <small class="help-block">Use lowercase letters, numbers, and underscores only</small>
                        @if($errors->has('name'))
                            <span class="help-block">{{ $errors->first('name') }}</span>
                        @endif
                    </div>

                    <div class="form-group {{ $errors->has('display_name') ? 'has-error' : '' }}">
                        <label for="display_name">Display Name *</label>
                        <input type="text" class="form-control" id="display_name" name="display_name"
                               value="{{ old('display_name') }}" placeholder="e.g., Manage Users" required>
                        @if($errors->has('display_name'))
                            <span class="help-block">{{ $errors->first('display_name') }}</span>
                        @endif
                    </div>

                    <div class="form-group {{ $errors->has('description') ? 'has-error' : '' }}">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"
                                  placeholder="Describe what this permission allows">{{ old('description') }}</textarea>
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
                                        <option value="{{ $key }}" {{ old('category') == $key ? 'selected' : '' }}>
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
                                       value="{{ old('resource') }}" placeholder="e.g., users" required>
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
                                        <option value="{{ $key }}" {{ old('action') == $key ? 'selected' : '' }}>
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
                    <button type="submit" class="btn btn-primary">Create Permission</button>
                    <a href="{{ route('superadmin.permissions.index') }}" class="btn btn-default">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <div class="col-md-4">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">Permission Guidelines</h3>
            </div>
            <div class="box-body">
                <ul class="list-unstyled">
                    <li><i class="fa fa-info-circle text-info"></i> Permission names should follow the pattern: action_resource</li>
                    <li><i class="fa fa-info-circle text-info"></i> Categories help organize permissions logically</li>
                    <li><i class="fa fa-info-circle text-info"></i> Resources represent what is being controlled</li>
                    <li><i class="fa fa-info-circle text-info"></i> Actions define what can be done (view, create, edit, delete, manage)</li>
                    <li><i class="fa fa-check-circle text-success"></i> Permissions can be assigned to roles after creation</li>
                </ul>
            </div>
        </div>

        <div class="box box-warning">
            <div class="box-header with-border">
                <h3 class="box-title">Common Patterns</h3>
            </div>
            <div class="box-body">
                <dl>
                    <dt>view_users</dt>
                    <dd>Can view user lists and details</dd>
                    <dt>create_users</dt>
                    <dd>Can create new users</dd>
                    <dt>edit_users</dt>
                    <dd>Can modify existing users</dd>
                    <dt>delete_users</dt>
                    <dd>Can remove users</dd>
                    <dt>manage_users</dt>
                    <dd>Full user management (includes all above)</dd>
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection
