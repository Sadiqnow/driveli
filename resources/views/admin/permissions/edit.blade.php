@extends('layouts.admin_master')

@section('title', 'Edit Permission')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Permission: {{ $permission->display_name }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to Permissions
                        </a>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.permissions.update', $permission) }}">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Permission Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                                           value="{{ old('name', $permission->name) }}" required>
                                    <small class="form-text text-muted">Use snake_case format (e.g., manage_users, view_reports)</small>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="display_name">Display Name <span class="text-danger">*</span></label>
                                    <input type="text" name="display_name" id="display_name" class="form-control @error('display_name') is-invalid @enderror"
                                           value="{{ old('display_name', $permission->display_name) }}" required>
                                    <small class="form-text text-muted">Human-readable name for the permission</small>
                                    @error('display_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="category">Category <span class="text-danger">*</span></label>
                                    <select name="category" id="category" class="form-control @error('category') is-invalid @enderror" required>
                                        <option value="">Select Category</option>
                                        @foreach(\App\Models\Permission::getCategories() as $category)
                                            <option value="{{ $category }}" {{ old('category', $permission->category) == $category ? 'selected' : '' }}>
                                                {{ $category }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('category')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="action">Action</label>
                                    <select name="action" id="action" class="form-control">
                                        <option value="">Select Action</option>
                                        @foreach(\App\Models\Permission::getActions() as $action)
                                            <option value="{{ $action }}" {{ old('action', $permission->action) == $action ? 'selected' : '' }}>
                                                {{ $action }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted">Optional: Specify the action type</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="resource">Resource</label>
                            <input type="text" name="resource" id="resource" class="form-control"
                                   value="{{ old('resource', $permission->resource) }}" placeholder="e.g., users, reports, settings">
                            <small class="form-text text-muted">Optional: Specify the resource this permission applies to</small>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea name="description" id="description" class="form-control" rows="3">{{ old('description', $permission->description) }}</textarea>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" name="is_active" id="is_active" class="custom-control-input"
                                       value="1" {{ old('is_active', $permission->is_active) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_active">Active</label>
                            </div>
                            <small class="form-text text-muted">Inactive permissions cannot be assigned to roles</small>
                        </div>

                        <!-- Permission Usage Info -->
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle"></i> Permission Usage</h6>
                            <p class="mb-1"><strong>Assigned to {{ $permission->roles_count }} role(s)</strong></p>
                            @if($permission->roles_count > 0)
                                <small>This permission is currently assigned to roles. Changes may affect user access.</small>
                            @else
                                <small>This permission is not assigned to any roles and can be safely modified.</small>
                            @endif
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Permission
                        </button>
                        <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
