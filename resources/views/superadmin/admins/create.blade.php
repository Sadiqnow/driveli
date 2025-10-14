@extends('layouts.admin_cdn')

@section('title', 'Create Admin User - Superadmin')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0">Create New Admin User</h1>
            <p class="text-muted">Add a new administrator with specific roles and permissions</p>
        </div>
        <a href="{{ route('superadmin.admins.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Admin User Registration Form</h3>
            </div>

            <form action="{{ route('superadmin.admins.store') }}" method="POST" id="adminForm">
                @csrf
                <div class="card-body">

                    <!-- Basic Information Section -->
                    <div class="form-section">
                        <h5><i class="fas fa-user"></i> Basic Information</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                           id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                                           id="email" name="email" value="{{ old('email') }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password">Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                                           id="password" name="password" required minlength="8">
                                    <small class="form-text text-muted">Minimum 8 characters with uppercase, lowercase, and number</small>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password_confirmation">Confirm Password <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control"
                                           id="password_confirmation" name="password_confirmation" required minlength="8">
                                    <small class="form-text text-muted">Must match the password above</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Role & Permissions Section -->
                    <div class="form-section">
                        <h5><i class="fas fa-user-shield"></i> Role & Permissions</h5>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="role">Role <span class="text-danger">*</span></label>
                                    <select class="form-control @error('role') is-invalid @enderror"
                                            id="role" name="role" required>
                                        <option value="">Select Role</option>
                                        @foreach($roles as $role)
                                            <option value="{{ $role->name }}" {{ old('role') == $role->name ? 'selected' : '' }}>
                                                {{ $role->display_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('role')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror

                                    <!-- Role description -->
                                    <div id="role-description" class="mt-2">
                                        @foreach($roles as $role)
                                            <div class="role-description" data-role="{{ $role->name }}" style="display: none;">
                                                <div class="alert alert-info">
                                                    <strong>{{ $role->display_name }}:</strong> {{ $role->description }}
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status">Status <span class="text-danger">*</span></label>
                                    <select class="form-control @error('status') is-invalid @enderror"
                                            id="status" name="status" required>
                                        <option value="">Select Status</option>
                                        <option value="Active" {{ old('status', 'Active') == 'Active' ? 'selected' : '' }}>Active</option>
                                        <option value="Inactive" {{ old('status') == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Permissions Section -->
                        <div class="form-group">
                            <label>Permissions</label>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                <strong>Permissions:</strong> Select specific permissions for this admin user.
                                You can also select all permissions for the chosen role.
                            </div>

                            <!-- Select All Checkbox -->
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="selectAllPermissions">
                                    <label class="form-check-label" for="selectAllPermissions">
                                        <strong>Select All Permissions</strong>
                                    </label>
                                </div>
                            </div>

                            <!-- Permissions by Category -->
                            @foreach($permissions as $category => $categoryPermissions)
                                <div class="permission-category mb-4">
                                    <h6 class="text-primary mb-3">
                                        <i class="fas fa-folder"></i> {{ ucfirst(str_replace('_', ' ', $category)) }} Permissions
                                    </h6>
                                    <div class="row">
                                        @foreach($categoryPermissions as $permission)
                                            <div class="col-md-6 col-lg-4">
                                                <div class="form-check">
                                                    <input class="form-check-input permission-checkbox"
                                                           type="checkbox"
                                                           name="permissions[]"
                                                           value="{{ $permission->name }}"
                                                           id="perm_{{ $permission->id }}"
                                                           {{ in_array($permission->name, old('permissions', [])) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="perm_{{ $permission->id }}">
                                                        {{ $permission->display_name }}
                                                        @if($permission->description)
                                                            <br><small class="text-muted">{{ $permission->description }}</small>
                                                        @endif
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Create Admin User
                    </button>
                    <a href="{{ route('superadmin.admins.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('css')
<style>
.form-section {
    background-color: #f8f9fa;
    border-left: 3px solid #007bff;
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 5px;
}

.form-section h5 {
    color: #007bff;
    margin-bottom: 15px;
}

.permission-category {
    border: 1px solid #dee2e6;
    border-radius: 5px;
    padding: 15px;
    background-color: #fff;
}

.role-description {
    font-size: 0.875rem;
}

.alert {
    border-radius: 5px;
}
</style>
@endsection

@section('js')
<script>
$(document).ready(function() {
    // Show role description based on selection
    $('#role').on('change', function() {
        const selectedRole = $(this).val();
        $('.role-description').hide();
        if (selectedRole) {
            $(`.role-description[data-role="${selectedRole}"]`).show();

            // Load role permissions via AJAX
            loadRolePermissions(selectedRole);
        }
    });

    // Trigger on page load if role is pre-selected
    if ($('#role').val()) {
        $('#role').trigger('change');
    }

    // Password confirmation validation
    $('#password_confirmation').on('keyup', function() {
        const password = $('#password').val();
        const confirmation = $(this).val();

        if (confirmation && password !== confirmation) {
            $(this).addClass('is-invalid');
            if (!$(this).next('.invalid-feedback').length) {
                $(this).after('<div class="invalid-feedback">Passwords do not match</div>');
            }
        } else {
            $(this).removeClass('is-invalid');
            $(this).next('.invalid-feedback').remove();
        }
    });

    // Select All Permissions functionality
    $('#selectAllPermissions').on('change', function() {
        const isChecked = $(this).is(':checked');
        $('.permission-checkbox').prop('checked', isChecked);
    });

    // Update "Select All" when individual permissions change
    $('.permission-checkbox').on('change', function() {
        const totalPermissions = $('.permission-checkbox').length;
        const checkedPermissions = $('.permission-checkbox:checked').length;
        $('#selectAllPermissions').prop('checked', totalPermissions === checkedPermissions);
    });

    // Form validation
    $('#adminForm').on('submit', function(e) {
        // Check password confirmation
        const password = $('#password').val();
        const confirmation = $('#password_confirmation').val();

        if (password !== confirmation) {
            e.preventDefault();
            alert('Passwords do not match!');
            return false;
        }

        // Check if at least one permission is selected
        const selectedPermissions = $('.permission-checkbox:checked').length;
        if (selectedPermissions === 0) {
            e.preventDefault();
            alert('Please select at least one permission for the admin user.');
            return false;
        }
    });
});

function loadRolePermissions(roleName) {
    $.get("{{ route('superadmin.admins.role-permissions') }}", { role: roleName })
        .done(function(data) {
            // Uncheck all permissions first
            $('.permission-checkbox').prop('checked', false);

            // Check permissions that belong to the selected role
            data.permissions.forEach(function(permissionName) {
                $(`input[name="permissions[]"][value="${permissionName}"]`).prop('checked', true);
            });

            // Update "Select All" checkbox
            const totalPermissions = $('.permission-checkbox').length;
            const checkedPermissions = $('.permission-checkbox:checked').length;
            $('#selectAllPermissions').prop('checked', totalPermissions === checkedPermissions);
        })
        .fail(function() {
            console.error('Failed to load role permissions');
        });
}
</script>
@endsection
