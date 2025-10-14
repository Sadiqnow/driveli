@extends('layouts.admin_cdn')

@section('title', 'Edit Admin User - Superadmin')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="m-0">Edit Admin User</h1>
            <p class="text-muted">Update admin user details, roles, and permissions</p>
        </div>
        <div>
            <a href="{{ route('superadmin.admins.index') }}" class="btn btn-secondary mr-2">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
            <a href="{{ route('superadmin.admins.create') }}" class="btn btn-success">
                <i class="fas fa-plus"></i> Create New
            </a>
        </div>
    </div>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Edit Admin User: {{ $admin->name }}</h3>
            </div>

            <form action="{{ route('superadmin.admins.update', $admin) }}" method="POST" id="adminForm">
                @csrf
                @method('PUT')
                <div class="card-body">

                    <!-- Basic Information Section -->
                    <div class="form-section">
                        <h5><i class="fas fa-user"></i> Basic Information</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                           id="name" name="name" value="{{ old('name', $admin->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                                           id="email" name="email" value="{{ old('email', $admin->email) }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Account Information Display -->
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="info-box bg-light">
                                    <span class="info-box-icon bg-info"><i class="fas fa-clock"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Last Login</span>
                                        <span class="info-box-number">
                                            {{ $admin->last_login_at ? $admin->last_login_at->format('M d, Y H:i') : 'Never' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-box bg-success">
                                    <span class="info-box-icon bg-calendar"><i class="fas fa-calendar"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Account Created</span>
                                        <span class="info-box-number">
                                            {{ $admin->created_at->format('M d, Y') }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Security Section -->
                    <div class="form-section">
                        <h5><i class="fas fa-lock"></i> Security</h5>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Password Update:</strong> Leave password fields empty to keep the current password unchanged.
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password">New Password</label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                                           id="password" name="password" minlength="8">
                                    <small class="form-text text-muted">Minimum 8 characters (leave blank to keep current password)</small>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password_confirmation">Confirm New Password</label>
                                    <input type="password" class="form-control"
                                           id="password_confirmation" name="password_confirmation" minlength="8">
                                    <small class="form-text text-muted">Must match the new password</small>
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
                                            <option value="{{ $role->name }}"
                                                    {{ old('role', $currentRole ? $currentRole->name : '') == $role->name ? 'selected' : '' }}>
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
                                        <option value="Active" {{ old('status', $admin->status) == 'Active' ? 'selected' : '' }}>Active</option>
                                        <option value="Inactive" {{ old('status', $admin->status) == 'Inactive' ? 'selected' : '' }}>Inactive</option>
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
                                <strong>Permissions:</strong> Update the permissions for this admin user.
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
                                                           {{ in_array($permission->name, old('permissions', $currentPermissions)) ? 'checked' : '' }}>
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
                        <i class="fas fa-save"></i> Update Admin User
                    </button>
                    <a href="{{ route('superadmin.admins.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                    @if($admin->id !== auth('admin')->id())
                        <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#deleteModal">
                            <i class="fas fa-trash"></i> Delete User
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
@if($admin->id !== auth('admin')->id())
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Warning!</strong> This action cannot be undone.
                </div>
                <p>Are you sure you want to delete the admin user <strong>{{ $admin->name }}</strong>?</p>
                <p>This will permanently remove the user and all associated data.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <form action="{{ route('superadmin.admins.destroy', $admin) }}" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Yes, Delete User
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif
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

.info-box {
    border-radius: 5px;
    margin-bottom: 0;
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

        if ($('#password').val() && confirmation && $('#password').val() !== confirmation) {
            $(this).addClass('is-invalid');
            if (!$(this).next('.invalid-feedback').length) {
                $(this).after('<div class="invalid-feedback">Passwords do not match</div>');
            }
        } else {
            $(this).removeClass('is-invalid');
            $(this).next('.invalid-feedback').remove();
        }
    });

    // Clear confirmation when password is cleared
    $('#password').on('keyup', function() {
        if (!$(this).val()) {
            $('#password_confirmation').val('').removeClass('is-invalid');
            $('#password_confirmation').next('.invalid-feedback').remove();
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

    // Initialize "Select All" checkbox on page load
    const totalPermissions = $('.permission-checkbox').length;
    const checkedPermissions = $('.permission-checkbox:checked').length;
    $('#selectAllPermissions').prop('checked', totalPermissions === checkedPermissions);

    // Form validation
    $('#adminForm').on('submit', function(e) {
        // Check password confirmation if password is being changed
        const password = $('#password').val();
        const confirmation = $('#password_confirmation').val();

        if (password && password !== confirmation) {
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
