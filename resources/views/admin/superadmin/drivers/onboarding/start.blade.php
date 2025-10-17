@extends('layouts.admin_master')

@section('title', 'Superadmin - Start Driver Onboarding')

@section('content_header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Start Driver Onboarding</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.superadmin.dashboard') }}">Super Admin</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.superadmin.drivers.index') }}">Drivers</a></li>
                        <li class="breadcrumb-item active">Start Onboarding</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-user-plus"></i> Driver Onboarding Setup</h3>
                    </div>

                    <form method="POST" action="{{ route('admin.superadmin.drivers.onboarding.start') }}">
                        @csrf

                        <div class="card-body">
                            <div class="alert alert-info">
                                <h5><i class="icon fas fa-info"></i> Onboarding Process Overview</h5>
                                <p>This will start a comprehensive 6-step onboarding process for the driver:</p>
                                <ol>
                                    <li><strong>Personal Information</strong> - Basic details and demographics</li>
                                    <li><strong>Contact & Emergency</strong> - Phone numbers and emergency contacts</li>
                                    <li><strong>Documents</strong> - ID, license, and profile picture uploads</li>
                                    <li><strong>Banking Details</strong> - Account information for payments</li>
                                    <li><strong>Professional Info</strong> - Experience, vehicle, and work preferences</li>
                                    <li><strong>Verification</strong> - Email and phone verification</li>
                                </ol>
                                <p><strong>Note:</strong> The driver will be created with a system-generated ID and can save progress at any step.</p>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="first_name">First Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('first_name') is-invalid @enderror"
                                               id="first_name" name="first_name" value="{{ old('first_name') }}" required>
                                        @error('first_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="surname">Surname <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('surname') is-invalid @enderror"
                                               id="surname" name="surname" value="{{ old('surname') }}" required>
                                        @error('surname')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
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

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="phone">Phone Number <span class="text-danger">*</span></label>
                                        <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                               id="phone" name="phone" value="{{ old('phone') }}" required>
                                        @error('phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input class="custom-control-input" type="checkbox" id="send_notifications" name="send_notifications" value="1" checked>
                                    <label for="send_notifications" class="custom-control-label">
                                        Send email/SMS notifications during onboarding
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-play"></i> Start Onboarding Process
                            </button>
                            <a href="{{ route('admin.superadmin.drivers.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
.card-header h3 {
    margin: 0;
}

.form-group label {
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.alert ol {
    margin-bottom: 0;
}

.btn {
    margin-right: 0.5rem;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Form validation
    $('form').on('submit', function(e) {
        const requiredFields = ['first_name', 'surname', 'email', 'phone'];
        let isValid = true;

        requiredFields.forEach(field => {
            const $field = $(`#${field}`);
            if (!$field.val().trim()) {
                $field.addClass('is-invalid');
                isValid = false;
            } else {
                $field.removeClass('is-invalid');
            }
        });

        // Email validation
        const email = $('#email').val();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (email && !emailRegex.test(email)) {
            $('#email').addClass('is-invalid');
            if (!$('#email').next('.invalid-feedback').length) {
                $('#email').after('<div class="invalid-feedback">Please enter a valid email address.</div>');
            }
            isValid = false;
        }

        if (!isValid) {
            e.preventDefault();
            alert('Please fill in all required fields correctly.');
        }
    });

    // Auto-generate preview (optional)
    function updatePreview() {
        const firstName = $('#first_name').val().toUpperCase().substring(0, 2) || 'XX';
        const surname = $('#surname').val().toUpperCase().substring(0, 2) || 'XX';
        const timestamp = Date.now().toString().substring(-4);
        const preview = `DRV-${firstName}${surname}-${timestamp}`;

        // Could show preview somewhere if desired
        console.log('Generated Driver ID:', preview);
    }

    $('#first_name, #surname').on('input', updatePreview);
});
</script>
@endpush
