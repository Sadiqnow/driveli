@extends('layouts.admin_cdn')

@section('title', 'Edit Profile')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="m-0">Edit Profile</h1>
        <small class="text-muted">Update your account information</small>
    </div>
    <div>
        <a href="{{ route('admin.users.profile', $user->id) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Profile
        </a>
    </div>
</div>
@endsection

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.users.profile', $user->id) }}">{{ $user->name }}</a></li>
    <li class="breadcrumb-item active">Edit Profile</li>
@endsection

@section('css')
<style>
.profile-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 30px;
    border-radius: 10px;
    margin-bottom: 30px;
    position: relative;
    overflow: hidden;
}
.profile-header::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 200%;
    height: 200%;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="2" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="1.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>') repeat;
    opacity: 0.1;
    animation: float 20s infinite linear;
}
@keyframes float {
    0% { transform: translate(-50%, -50%) rotate(0deg); }
    100% { transform: translate(-50%, -50%) rotate(360deg); }
}
.form-section {
    background-color: #f8f9fa;
    border-left: 4px solid #007bff;
    padding: 20px;
    margin-bottom: 25px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}
.form-section h5 {
    color: #007bff;
    margin-bottom: 20px;
    font-weight: 600;
}
.avatar-upload {
    position: relative;
    display: inline-block;
}
.avatar-preview {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    border: 4px solid #fff;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    object-fit: cover;
    cursor: pointer;
}
.avatar-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s;
    cursor: pointer;
}
.avatar-upload:hover .avatar-overlay {
    opacity: 1;
}
.strength-meter {
    height: 8px;
    border-radius: 4px;
    background: #e9ecef;
    overflow: hidden;
    margin-top: 5px;
}
.strength-bar {
    height: 100%;
    transition: width 0.3s, background-color 0.3s;
}
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Profile Header -->
        <div class="profile-header text-center">
            <div class="avatar-upload">
                <img src="{{ $user->avatar_url ?? asset('vendor/adminlte/dist/img/avatar.png') }}" 
                     alt="Profile Picture" class="avatar-preview" id="avatarPreview">
                <div class="avatar-overlay">
                    <i class="fas fa-camera text-white fa-2x"></i>
                </div>
                <input type="file" id="avatarInput" accept="image/*" style="display: none;">
            </div>
            <h3 class="mt-3 mb-1">{{ $user->name }}</h3>
            <p class="mb-0">{{ $user->role }} • {{ $user->email }}</p>
        </div>
    </div>
</div>

<form action="{{ route('admin.users.update-profile', $user->id) }}" method="POST" enctype="multipart/form-data" id="profileForm">
    @csrf
    @method('PUT')
    
    <div class="row">
        <div class="col-md-8">
            <!-- Basic Information -->
            <div class="form-section">
                <h5><i class="fas fa-user"></i> Basic Information</h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name', $user->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" name="phone" value="{{ old('phone', $user->phone) }}"
                                   placeholder="+234 XXX XXX XXXX">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="email">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email', $user->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="bio">Bio/Description</label>
                    <textarea class="form-control @error('bio') is-invalid @enderror" 
                              id="bio" name="bio" rows="3" placeholder="Tell us about yourself...">{{ old('bio', $user->bio) }}</textarea>
                    @error('bio')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Security Settings -->
            <div class="form-section">
                <h5><i class="fas fa-shield-alt"></i> Security Settings</h5>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Leave password fields empty to keep current password
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <input type="password" class="form-control @error('current_password') is-invalid @enderror" 
                                   id="current_password" name="current_password">
                            @error('current_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="password">New Password</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                   id="password" name="password" minlength="8">
                            <div class="strength-meter">
                                <div class="strength-bar" id="strengthBar"></div>
                            </div>
                            <small class="text-muted">Minimum 8 characters</small>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="password_confirmation">Confirm New Password</label>
                            <input type="password" class="form-control" 
                                   id="password_confirmation" name="password_confirmation" minlength="8">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notification Preferences -->
            <div class="form-section">
                <h5><i class="fas fa-bell"></i> Notification Preferences</h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="email_notifications" 
                                   name="notifications[email]" value="1" 
                                   {{ old('notifications.email', $user->email_notifications ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="email_notifications">
                                Email Notifications
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="sms_notifications" 
                                   name="notifications[sms]" value="1" 
                                   {{ old('notifications.sms', $user->sms_notifications ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="sms_notifications">
                                SMS Notifications
                            </label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="system_notifications" 
                                   name="notifications[system]" value="1" 
                                   {{ old('notifications.system', $user->system_notifications ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="system_notifications">
                                System Notifications
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="marketing_notifications" 
                                   name="notifications[marketing]" value="1" 
                                   {{ old('notifications.marketing', $user->marketing_notifications ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="marketing_notifications">
                                Marketing Updates
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Quick Info -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Account Information</h3>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-5">Role:</dt>
                        <dd class="col-sm-7">
                            <span class="badge badge-primary">{{ $user->role }}</span>
                        </dd>
                        <dt class="col-sm-5">Status:</dt>
                        <dd class="col-sm-7">
                            <span class="badge badge-{{ $user->status === 'Active' ? 'success' : 'danger' }}">
                                {{ $user->status }}
                            </span>
                        </dd>
                        <dt class="col-sm-5">Created:</dt>
                        <dd class="col-sm-7">{{ $user->created_at->format('M d, Y') }}</dd>
                        <dt class="col-sm-5">Last Login:</dt>
                        <dd class="col-sm-7">
                            {{ $user->last_login_at ? $user->last_login_at->format('M d, Y H:i') : 'Never' }}
                        </dd>
                    </dl>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-save"></i> Update Profile
                    </button>
                    <a href="{{ route('admin.users.profile', $user->id) }}" class="btn btn-secondary btn-block">
                        <i class="fas fa-times"></i> Cancel Changes
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@section('js')
<script>
$(document).ready(function() {
    // Avatar upload functionality
    $('#avatarPreview, .avatar-overlay').on('click', function() {
        $('#avatarInput').click();
    });

    $('#avatarInput').on('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#avatarPreview').attr('src', e.target.result);
            };
            reader.readAsDataURL(file);
        }
    });

    // Password strength meter
    $('#password').on('keyup', function() {
        const password = $(this).val();
        const strength = calculatePasswordStrength(password);
        updateStrengthMeter(strength);
    });

    // Password confirmation validation
    $('#password_confirmation').on('keyup', function() {
        const password = $('#password').val();
        const confirmation = $(this).val();
        
        if (confirmation && password !== confirmation) {
            $(this).addClass('is-invalid');
            if (!$(this).siblings('.invalid-feedback').length) {
                $(this).after('<div class="invalid-feedback">Passwords do not match</div>');
            }
        } else {
            $(this).removeClass('is-invalid');
            $(this).siblings('.invalid-feedback').remove();
        }
    });

    // Form validation
    $('#profileForm').on('submit', function(e) {
        const password = $('#password').val();
        const currentPassword = $('#current_password').val();
        
        if (password && !currentPassword) {
            e.preventDefault();
            toastr.error('Please enter your current password to change it.');
            $('#current_password').focus();
            return false;
        }
        
        if (password !== $('#password_confirmation').val()) {
            e.preventDefault();
            toastr.error('Password confirmation does not match.');
            $('#password_confirmation').focus();
            return false;
        }
    });

    function calculatePasswordStrength(password) {
        let strength = 0;
        
        if (password.length >= 8) strength += 25;
        if (password.match(/[a-z]/)) strength += 25;
        if (password.match(/[A-Z]/)) strength += 25;
        if (password.match(/[0-9]/)) strength += 25;
        if (password.match(/[^a-zA-Z0-9]/)) strength += 25;
        
        return Math.min(strength, 100);
    }

    function updateStrengthMeter(strength) {
        const strengthBar = $('#strengthBar');
        let color = '';
        
        if (strength < 25) color = '#dc3545';
        else if (strength < 50) color = '#fd7e14';
        else if (strength < 75) color = '#ffc107';
        else color = '#28a745';
        
        strengthBar.css({
            width: strength + '%',
            backgroundColor: color
        });
    }
});
</script>
@endsection