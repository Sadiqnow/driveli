@extends('layouts.admin_master')

@section('title', 'Edit Role')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Role: {{ $role->display_name }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.roles.show', $role) }}" class="btn btn-info btn-sm">
                            <i class="fas fa-eye"></i> View
                        </a>
                        <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to Roles
                        </a>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.roles.update', $role) }}">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="display_name">Display Name <span class="text-danger">*</span></label>
                                    <input type="text" name="display_name" id="display_name" class="form-control @error('display_name') is-invalid @enderror"
                                           value="{{ old('display_name', $role->display_name) }}" required>
                                    <small class="form-text text-muted">Human-readable name for the role</small>
                                    @error('display_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="level">Role Level <span class="text-danger">*</span></label>
                                    <input type="number" name="level" id="level" class="form-control @error('level') is-invalid @enderror"
                                           value="{{ old('level', $role->level) }}" min="1" max="99" required>
                                    <small class="form-text text-muted">Higher numbers = more permissions (Super Admin = 100)</small>
                                    @error('level')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea name="description" id="description" class="form-control" rows="3">{{ old('description', $role->description) }}</textarea>
                        </div>

                        <!-- Permissions Section -->
                        <div class="form-group">
                            <label>Permissions</label>
                            <div class="border p-3" style="max-height: 400px; overflow-y: auto;">
                                @php
                                    $groupedPermissions = $permissions->groupBy('category');
                                @endphp

                                @foreach($groupedPermissions as $category => $categoryPermissions)
                                <div class="mb-3">
                                    <h6 class="text-primary mb-2">
                                        <i class="fas fa-folder"></i> {{ $category }}
                                    </h6>
                                    <div class="row">
                                        @foreach($categoryPermissions as $permission)
                                        <div class="col-md-4">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                                       id="permission_{{ $permission->id }}" class="custom-control-input"
                                                       {{ in_array($permission->id, old('permissions', $rolePermissions)) ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="permission_{{ $permission->id }}">
                                                    {{ $permission->display_name }}
                                                </label>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            <small class="form-text text-muted">Select the permissions this role should have</small>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" name="is_active" id="is_active" class="custom-control-input"
                                       value="1" {{ old('is_active', $role->is_active) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_active">Active</label>
                            </div>
                            <small class="form-text text-muted">Inactive roles cannot be assigned to users</small>
                        </div>

                        <!-- Role Statistics -->
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle"></i> Role Statistics</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>{{ $role->users_count }}</strong> users assigned
                                </div>
                                <div class="col-md-4">
                                    <strong>{{ $role->permissions_count }}</strong> permissions
                                </div>
                                <div class="col-md-4">
                                    <strong>Level {{ $role->level }}</strong> hierarchy
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Role
                        </button>
                        <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Select/Deselect all permissions in a category
    $('.category-header').click(function() {
        const category = $(this).data('category');
        const checkboxes = $(`input[id^="permission_"][data-category="${category}"]`);
        const allChecked = checkboxes.length === checkboxes.filter(':checked').length;
        checkboxes.prop('checked', !allChecked);
    });

    // Update category header state
    function updateCategoryHeaders() {
        $('[data-category]').each(function() {
            const category = $(this).data('category');
            const checkboxes = $(`input[id^="permission_"][data-category="${category}"]`);
            const checkedCount = checkboxes.filter(':checked').length;
            const totalCount = checkboxes.length;

            $(this).find('.badge').text(`${checkedCount}/${totalCount}`);
            $(this).toggleClass('text-success', checkedCount === totalCount)
                   .toggleClass('text-warning', checkedCount > 0 && checkedCount < totalCount)
                   .toggleClass('text-muted', checkedCount === 0);
        });
    }

    // Initial update
    updateCategoryHeaders();

    // Update on checkbox change
    $('input[name="permissions[]"]').change(updateCategoryHeaders);
});
</script>
@endsection
