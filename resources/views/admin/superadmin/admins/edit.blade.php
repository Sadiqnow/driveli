@extends('layouts.admin_master')

@section('title', 'Edit Admin User')

@section('content_header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Edit Admin User</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.superadmin.dashboard') }}">Super Admin</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.superadmin.admins.index') }}">Admins</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.superadmin.admins.show', $admin) }}">{{ $admin->name }}</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Admin User Information</h3>
                    </div>

                    <form action="{{ route('admin.superadmin.admins.update', $admin) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="card-body">
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

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="phone">Phone Number</label>
                                        <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                               id="phone" name="phone" value="{{ old('phone', $admin->phone) }}">
                                        @error('phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="role">Role <span class="text-danger">*</span></label>
                                        <select class="form-control @error('role') is-invalid @enderror" id="role" name="role" required>
                                            <option value="">Select Role</option>
                                            @foreach($roles as $role)
                                                <option value="{{ $role->name }}"
                                                        {{ (old('role', $currentRole ? $currentRole->name : '')) == $role->name ? 'selected' : '' }}>
                                                    {{ $role->display_name ?? $role->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('role')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="password">New Password</label>
                                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                                               id="password" name="password">
                                        @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">Leave blank to keep current password</small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="password_confirmation">Confirm New Password</label>
                                        <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror"
                                               id="password_confirmation" name="password_confirmation">
                                        @error('password_confirmation')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="status">Status <span class="text-danger">*</span></label>
                                        <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" required>
                                            <option value="Active" {{ old('status', $admin->status) == 'Active' ? 'selected' : '' }}>Active</option>
                                            <option value="Inactive" {{ old('status', $admin->status) == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                        @error('status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i> Update Admin
                            </button>
                            <a href="{{ route('admin.superadmin.admins.show', $admin) }}" class="btn btn-secondary ml-2">
                                <i class="fas fa-times mr-1"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Current Information</h3>
                    </div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-5">Created:</dt>
                            <dd class="col-sm-7">{{ $admin->created_at->format('M d, Y H:i') }}</dd>

                            <dt class="col-sm-5">Last Updated:</dt>
                            <dd class="col-sm-7">{{ $admin->updated_at->format('M d, Y H:i') }}</dd>

                            <dt class="col-sm-5">Last Login:</dt>
                            <dd class="col-sm-7">{{ $admin->last_login_at ? $admin->last_login_at->format('M d, Y H:i') : 'Never' }}</dd>
                        </dl>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Role Information</h3>
                    </div>
                    <div class="card-body">
                        <div id="role-info">
                            @if($currentRole)
                                <p class="text-info">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Current role: <strong>{{ $currentRole->display_name ?? $currentRole->name }}</strong>
                                </p>
                            @else
                                <p class="text-muted">No role assigned</p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Security Notes</h3>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled">
                            <li><i class="fas fa-exclamation-triangle text-warning mr-2"></i> Changing role may affect permissions</li>
                            <li><i class="fas fa-check-circle text-success mr-2"></i> Password change is optional</li>
                            <li><i class="fas fa-info-circle text-info mr-2"></i> User will be notified of changes</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Password confirmation validation
    $('#password_confirmation').on('keyup', function() {
        var password = $('#password').val();
        var confirmPassword = $(this).val();

        if (password && password !== confirmPassword) {
            $(this).addClass('is-invalid');
            $(this).siblings('.invalid-feedback').remove();
            $(this).after('<div class="invalid-feedback">Passwords do not match</div>');
        } else {
            $(this).removeClass('is-invalid');
            $(this).siblings('.invalid-feedback').remove();
        }
    });

    // Role change handler
    $('#role').on('change', function() {
        var roleName = $(this).val();
        if (roleName) {
            $('#role-info').html('<p class="text-info"><i class="fas fa-info-circle mr-1"></i> Changing to: ' + roleName + ' role</p>');
        } else {
            $('#role-info').html('<p class="text-warning"><i class="fas fa-exclamation-triangle mr-1"></i> No role selected</p>');
        }
    });
});
</script>
@endpush
