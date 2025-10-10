@extends('layouts.admin_cdn')

@section('title', 'Create Admin User')

@section('content_header', 'Create Admin User')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Admin Users</a></li>
    <li class="breadcrumb-item active">Create</li>
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
.required {
    color: #dc3545;
}
.role-description {
    font-size: 0.875rem;
    color: #6c757d;
    margin-top: 5px;
}
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Admin User Registration Form</h3>
            </div>
            
            <form action="{{ route('admin.users.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    
                    <!-- Basic Information Section -->
                    <div class="form-section">
                        <h5><i class="fas fa-user"></i> Basic Information</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Full Name <span class="required">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phone">Phone Number</label>
                                    <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" name="phone" value="{{ old('phone') }}" 
                                           placeholder="e.g., +2348012345678">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="email">Email Address <span class="required">*</span></label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email') }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Security Section -->
                    <div class="form-section">
                        <h5><i class="fas fa-lock"></i> Security</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password">Password <span class="required">*</span></label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                           id="password" name="password" required minlength="8">
                                    <small class="form-text text-muted">Minimum 8 characters</small>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password_confirmation">Confirm Password <span class="required">*</span></label>
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
                                    <label for="role">Role <span class="required">*</span></label>
                                    <select class="form-control @error('role') is-invalid @enderror" 
                                            id="role" name="role" required>
                                        <option value="">Select Role</option>
                                        @foreach($roles as $key => $label)
                                            <option value="{{ $key }}" {{ old('role') == $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('role')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    
                                    <!-- Role descriptions -->
                                    <div id="role-descriptions" class="mt-2">
                                        <div class="role-description" data-role="super_admin" style="display: none;">
                                            <strong>Super Administrator:</strong> Full system access including user management, system settings, and all administrative functions.
                                        </div>
                                        <div class="role-description" data-role="admin" style="display: none;">
                                            <strong>Administrator:</strong> Can manage drivers, companies, requests, and view reports. Cannot manage other admin users.
                                        </div>
                                        <div class="role-description" data-role="moderator" style="display: none;">
                                            <strong>Moderator:</strong> Can verify drivers and companies, moderate requests, but has limited management capabilities.
                                        </div>
                                        <div class="role-description" data-role="viewer" style="display: none;">
                                            <strong>Viewer:</strong> Read-only access to drivers, companies, requests, and reports. Cannot make changes.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status">Status <span class="required">*</span></label>
                                    <select class="form-control @error('status') is-invalid @enderror" 
                                            id="status" name="status" required>
                                        <option value="">Select Status</option>
                                        <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Create Admin User
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
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
});
</script>
@endsection