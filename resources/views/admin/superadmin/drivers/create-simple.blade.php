@extends('layouts.admin_master')

@section('title', 'Superadmin - Quick Driver Registration')

@section('content_header')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center">
        <h1>
            <i class="fas fa-user-plus"></i> Quick Driver Registration
        </h1>
        <div>
            <a href="{{ route('admin.superadmin.drivers.create') }}" class="btn btn-outline-primary">
                <i class="fas fa-plus-circle"></i> Full KYC Registration
            </a>
            <a href="{{ route('admin.superadmin.drivers.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Drivers
            </a>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-user-plus"></i> Quick Driver Registration
                    </h4>
                </div>

                <div class="card-body">
                    <!-- Progress Indicator -->
                    <div class="mb-4">
                        <div class="progress">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">
                                Step 1 of 1 - Account Creation
                            </div>
                        </div>
                        <small class="text-muted mt-1 d-block">
                            This creates a basic driver account. Full KYC verification will be required later.
                        </small>
                    </div>

                    @if ($errors->any())
                    <div class="alert alert-danger">
                        <h5><i class="fas fa-exclamation-triangle"></i> Please fix the following errors:</h5>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <form action="{{ route('admin.superadmin.drivers.store-simple') }}" method="POST" id="quickRegistrationForm">
                        @csrf

                        <!-- Personal Information -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="first_name" class="form-label">
                                        First Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control @error('first_name') is-invalid @enderror"
                                           id="first_name" name="first_name"
                                           value="{{ old('first_name') }}" required>
                                    @error('first_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="surname" class="form-label">
                                        Surname <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control @error('surname') is-invalid @enderror"
                                           id="surname" name="surname"
                                           value="{{ old('surname') }}" required>
                                    @error('surname')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="email" class="form-label">
                                        Email Address <span class="text-danger">*</span>
                                    </label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                                           id="email" name="email"
                                           value="{{ old('email') }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="phone" class="form-label">
                                        Phone Number <span class="text-danger">*</span>
                                    </label>
                                    <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                           id="phone" name="phone"
                                           value="{{ old('phone') }}" required
                                           placeholder="+234 XXX XXX XXXX">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Optional Fields -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="middle_name" class="form-label">Middle Name</label>
                                    <input type="text" class="form-control @error('middle_name') is-invalid @enderror"
                                           id="middle_name" name="middle_name"
                                           value="{{ old('middle_name') }}">
                                    @error('middle_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="nickname" class="form-label">Preferred Name</label>
                                    <input type="text" class="form-control @error('nickname') is-invalid @enderror"
                                           id="nickname" name="nickname"
                                           value="{{ old('nickname') }}">
                                    @error('nickname')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Account Settings -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="status" class="form-label">Initial Status</label>
                                    <select class="form-control @error('status') is-invalid @enderror" id="status" name="status">
                                        <option value="Available" {{ old('status', 'Available') == 'Available' ? 'selected' : '' }}>Available</option>
                                        <option value="Not Available" {{ old('status') == 'Not Available' ? 'selected' : '' }}>Not Available</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="verification_status" class="form-label">Verification Status</label>
                                    <select class="form-control @error('verification_status') is-invalid @enderror" id="verification_status" name="verification_status">
                                        <option value="Pending" {{ old('verification_status', 'Pending') == 'Pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="Verified" {{ old('verification_status') == 'Verified' ? 'selected' : '' }}>Verified</option>
                                    </select>
                                    @error('verification_status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Registration Notes -->
                        <div class="form-group mb-3">
                            <label for="registration_notes" class="form-label">Registration Notes</label>
                            <textarea class="form-control @error('registration_notes') is-invalid @enderror"
                                      id="registration_notes" name="registration_notes" rows="3"
                                      placeholder="Optional notes about this driver registration...">{{ old('registration_notes') }}</textarea>
                            @error('registration_notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Terms and Conditions -->
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="terms_accepted" name="terms_accepted" value="1" required>
                            <label class="form-check-label" for="terms_accepted">
                                I confirm that I have obtained consent from the driver to register them in the system and that all provided information is accurate.
                                <span class="text-danger">*</span>
                            </label>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.superadmin.drivers.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>

                            <div>
                                <button type="button" class="btn btn-outline-primary me-2" onclick="clearForm()">
                                    <i class="fas fa-eraser"></i> Clear Form
                                </button>
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="fas fa-save"></i> Create Driver Account
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Information Panel -->
            <div class="card mt-4 border-info">
                <div class="card-header bg-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle"></i> What happens next?
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <div class="mb-3">
                                <i class="fas fa-user-plus fa-2x text-primary"></i>
                            </div>
                            <h6>Account Created</h6>
                            <p class="text-muted small">Driver account is created with basic information</p>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="mb-3">
                                <i class="fas fa-mobile-alt fa-2x text-warning"></i>
                            </div>
                            <h6>OTP Verification</h6>
                            <p class="text-muted small">Driver receives OTP for phone verification</p>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="mb-3">
                                <i class="fas fa-file-alt fa-2x text-success"></i>
                            </div>
                            <h6>KYC Required</h6>
                            <p class="text-muted small">Full KYC verification needed for activation</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
$(document).ready(function() {
    // Form validation
    $('#quickRegistrationForm').on('submit', function(e) {
        const submitBtn = $('#submitBtn');
        const originalText = submitBtn.html();

        // Show loading state
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Creating Account...');

        // Re-enable button if form is invalid (will be handled by browser validation)
        setTimeout(() => {
            if (!this.checkValidity()) {
                submitBtn.prop('disabled', false).html(originalText);
            }
        }, 100);
    });

    // Phone number formatting
    $('#phone').on('input', function() {
        let value = $(this).val().replace(/\D/g, '');
        if (value.startsWith('234')) {
            value = '+' + value;
        } else if (value.startsWith('0')) {
            value = '+234' + value.substring(1);
        } else if (!value.startsWith('+')) {
            value = '+234' + value;
        }
        $(this).val(value);
    });

    // Email validation feedback
    $('#email').on('blur', function() {
        const email = $(this).val();
        const isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);

        if (email && !isValid) {
            $(this).addClass('is-invalid');
            if (!$(this).next('.invalid-feedback').length) {
                $(this).after('<div class="invalid-feedback">Please enter a valid email address.</div>');
            }
        } else {
            $(this).removeClass('is-invalid').addClass('is-valid');
            $(this).next('.invalid-feedback').remove();
        }
    });

    // Auto-generate preferred name if empty
    $('#first_name, #surname').on('input', function() {
        const firstName = $('#first_name').val();
        const surname = $('#surname').val();
        const nickname = $('#nickname').val();

        if (!nickname && firstName) {
            $('#nickname').val(firstName);
        }
    });
});

function clearForm() {
    if (confirm('Are you sure you want to clear all form data?')) {
        $('#quickRegistrationForm')[0].reset();
        $('.is-valid, .is-invalid').removeClass('is-valid is-invalid');
        $('.invalid-feedback').remove();
    }
}

// Prevent form submission if terms not accepted
document.getElementById('quickRegistrationForm').addEventListener('submit', function(e) {
    const termsAccepted = document.getElementById('terms_accepted').checked;
    if (!termsAccepted) {
        e.preventDefault();
        alert('Please confirm that you have obtained consent from the driver.');
        document.getElementById('terms_accepted').focus();
    }
});
</script>
@endsection
