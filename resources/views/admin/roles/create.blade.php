@extends('layouts.admin_master')

@section('title', 'Create Role')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-10 offset-md-1">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Create New Role</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to Roles
                        </a>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.roles.store') }}">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Role Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                                           value="{{ old('name') }}" placeholder="e.g., admin, manager" required>
                                    <small class="form-text text-muted">Use lowercase with underscores (e.g., content_manager)</small>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="display_name">Display Name <span class="text-danger">*</span></label>
                                    <input type="text" name="display_name" id="display_name" class="form-control @error('display_name') is-invalid @enderror"
                                           value="{{ old('display_name') }}" placeholder="e.g., Content Manager" required>
                                    <small class="form-text text-muted">Human-readable name for the role</small>
                                    @error('display_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="level">Role Level <span class="text-danger">*</span></label>
                                    <input type="number" name="level" id="level" class="form-control @error('level') is-invalid @enderror"
                                           value="{{ old('level', 1) }}" min="1" max="99" required>
                                    <small class="form-text text-muted">Higher numbers = more permissions (Super Admin = 100)</small>
                                    @error('level')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="is_active">Status</label>
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" name="is_active" id="is_active" class="custom-control-input" value="1" checked>
                                        <label class="custom-control-label" for="is_active">Active</label>
                                    </div>
                                    <small class="form-text text-muted">Inactive roles cannot be assigned to users</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea name="description" id="description" class="form-control" rows="3"
                                      placeholder="Describe the responsibilities and scope of this role...">{{ old('description') }}</textarea>
                        </div>

                        <!-- Permissions Section -->
                        <div class="form-group">
                            <label>Permissions <span class="text-danger">*</span></label>
                            <div class="border p-3" style="max-height: 400px; overflow-y: auto;">
                                @php
                                    $groupedPermissions = $permissions->groupBy('category');
                                @endphp

                                @foreach($groupedPermissions as $category => $categoryPermissions)
                                <div class="mb-3">
                                    <h6 class="category-header text-primary mb-2" data-category="{{ $category }}">
                                        <i class="fas fa-folder"></i> {{ $category }}
                                        <span class="badge badge-secondary float-right">0/{{ $categoryPermissions->count() }}</span>
                                    </h6>
                                    <div class="row">
                                        @foreach($categoryPermissions as $permission)
                                        <div class="col-md-4">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                                       id="permission_{{ $permission->id }}" class="custom-control-input"
                                                       data-category="{{ $category }}">
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
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create Role
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
    // Auto-generate display name from name
    $('#name').on('input', function() {
        const name = $(this).val();
        if (!$('#display_name').val()) {
            const displayName = name.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            $('#display_name').val(displayName);
        }
    });

    // Select/Deselect all permissions in a category
    $('.category-header').click(function() {
        const category = $(this).data('category');
        const checkboxes = $(`input[data-category="${category}"]`);
        const allChecked = checkboxes.length === checkboxes.filter(':checked').length;
        checkboxes.prop('checked', !allChecked);
        updateCategoryCount(category);
    });

    // Update category count when checkboxes change
    function updateCategoryCount(category) {
        const checkboxes = $(`input[data-category="${category}"]`);
        const checkedCount = checkboxes.filter(':checked').length;
        const totalCount = checkboxes.length;

        $(`.category-header[data-category="${category}"] .badge`)
            .text(`${checkedCount}/${totalCount}`)
            .removeClass('badge-success badge-warning badge-secondary')
            .addClass(checkedCount === totalCount ? 'badge-success' :
                     checkedCount > 0 ? 'badge-warning' : 'badge-secondary');
    }

    // Initial count update
    $('[data-category]').each(function() {
        updateCategoryCount($(this).data('category'));
    });

    // Update count on checkbox change
    $('input[name="permissions[]"]').change(function() {
        updateCategoryCount($(this).data('category'));
    });
});
</script>
@endsection
