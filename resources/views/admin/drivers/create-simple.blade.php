@extends('layouts.admin_cdn')

@section('title', 'Create Driver Account')

@section('content_header', 'Create Driver Account')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ url('admin/drivers') }}">Drivers</a></li>
    <li class="breadcrumb-item active">Create Account</li>
@endsection

@section('css')
<style>
.create-form-container {
    max-width: 600px;
    margin: 0 auto;
}

.form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.required {
    color: #dc3545;
}

.field-helper {
    font-size: 0.85rem;
    color: #6c757d;
    margin-top: 0.25rem;
}

.info-banner {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    color: white;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 25px;
}

.info-banner h6 {
    margin-bottom: 10px;
    font-weight: 600;
}

.step-indicator {
    background: #f8f9fa;
    border-left: 4px solid #007bff;
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 5px;
}
</style>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        @if ($errors->any())
            <div class="alert alert-danger">
                <h5><i class="fas fa-exclamation-triangle"></i> Validation Errors:</h5>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                @if (config('app.debug'))
                    <hr>
                    <small><strong>Debug Info:</strong></small>
                    <pre>{{ print_r($errors->toArray(), true) }}</pre>
                @endif
            </div>
        @endif
        
        <!-- Information Banner -->
        <div class="info-banner">
            <div class="row align-items-center">
                <div class="col-md-9">
                    <h6><i class="fas fa-user-plus"></i> Driver Account Creation - Step 1</h6>
                    <p class="mb-0">Create a basic driver account with essential information. Full KYC verification will be completed in the next step.</p>
                </div>
                <div class="col-md-3 text-right">
                    <i class="fas fa-id-card fa-3x"></i>
                </div>
            </div>
        </div>
        
        <!-- Step Indicator -->
        <div class="step-indicator">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="mb-1"><i class="fas fa-circle-notch"></i> Step 1: Basic Account Setup</h6>
                    <small class="text-muted">After creation, the driver will complete full KYC verification</small>
                </div>
                <div class="text-muted">
                    <small>Next: KYC Verification</small>
                </div>
            </div>
        </div>
        
        <div class="card create-form-container">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user-plus"></i> Create Driver Account
                </h3>
                <div class="card-tools">
                    <small class="text-muted">
                        <span class="required">*</span> Required fields
                    </small>
                </div>
            </div>
            
            <form action="{{ route('admin.drivers.store-simple') }}" method="POST" id="driverCreateForm">
                @csrf
                
                <div class="card-body">
                    
                    <!-- Driver License Number -->
                    <div class="form-group">
                        <label for="driver_license_number">Driver License Number <span class="required">*</span></label>
                        <input type="text" class="form-control @error('driver_license_number') is-invalid @enderror" 
                               id="driver_license_number" name="driver_license_number" 
                               value="{{ old('driver_license_number') }}" 
                               required autocomplete="off"
                               placeholder="Enter driver license number">
                        <small class="field-helper">License number as shown on the driver's license</small>
                        @error('driver_license_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Name Fields -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="first_name">First Name <span class="required">*</span></label>
                                <input type="text" class="form-control @error('first_name') is-invalid @enderror" 
                                       id="first_name" name="first_name" 
                                       value="{{ old('first_name') }}" 
                                       required autocomplete="given-name"
                                       placeholder="Enter first name">
                                @error('first_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="surname">Surname/Last Name <span class="required">*</span></label>
                                <input type="text" class="form-control @error('surname') is-invalid @enderror" 
                                       id="surname" name="surname" 
                                       value="{{ old('surname') }}" 
                                       required autocomplete="family-name"
                                       placeholder="Enter surname">
                                @error('surname')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    
                    <!-- Contact Information -->
                    <div class="form-group">
                        <label for="phone">Mobile Number <span class="required">*</span></label>
                        <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                               id="phone" name="phone" 
                               value="{{ old('phone') }}" 
                               required autocomplete="tel"
                               placeholder="+234 xxx xxxx xxx">
                        <small class="field-helper">Mobile number for account notifications</small>
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address <span class="required">*</span></label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                               id="email" name="email" 
                               value="{{ old('email') }}" 
                               required autocomplete="email"
                               placeholder="Enter email address">
                        <small class="field-helper">Email will be used for login and notifications</small>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Password Fields -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="password">Password <span class="required">*</span></label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                       id="password" name="password" 
                                       required minlength="8"
                                       placeholder="Create password">
                                <small class="field-helper">Minimum 8 characters</small>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="password_confirmation">Confirm Password <span class="required">*</span></label>
                                <input type="password" class="form-control" 
                                       id="password_confirmation" name="password_confirmation" 
                                       required minlength="8"
                                       placeholder="Confirm password">
                                <small class="field-helper">Must match password</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Account Status -->
                    <div class="form-group">
                        <label for="status">Account Status</label>
                        <select class="form-control @error('status') is-invalid @enderror" id="status" name="status">
                            <option value="active" selected>Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        <small class="field-helper">Account can be activated after KYC completion</small>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Hidden fields for system tracking -->
                    <input type="hidden" name="verification_status" value="pending">
                    <input type="hidden" name="kyc_status" value="pending">
                    <input type="hidden" name="created_by" value="{{ auth('admin')->id() }}">
                    
                </div>
                
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <a href="{{ url('admin/drivers') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Cancel
                            </a>
                        </div>
                        <div class="col-md-6 text-right">
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="fas fa-user-plus"></i> Create Account
                            </button>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <div class="alert alert-info">
                            <i class="fas fa-shield-alt"></i>
                            <strong>Next Step:</strong> After creating the account, we'll send verification codes to the driver's phone and email for security verification before proceeding to complete registration.
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
$(document).ready(function() {
    // Password confirmation validation
    $('#password_confirmation').on('input', function() {
        const password = $('#password').val();
        const confirmation = $(this).val();
        
        if (confirmation && confirmation !== password) {
            $(this).addClass('is-invalid');
            if (!$(this).siblings('.invalid-feedback').length) {
                $(this).after('<div class="invalid-feedback">Passwords do not match</div>');
            }
        } else {
            $(this).removeClass('is-invalid');
            $(this).siblings('.invalid-feedback').remove();
        }
    });
    
    // Form submission handling
    $('#driverCreateForm').on('submit', function(e) {
        const password = $('#password').val();
        const confirmation = $('#password_confirmation').val();
        
        if (password !== confirmation) {
            e.preventDefault();
            alert('Please ensure passwords match before submitting.');
            $('#password_confirmation').focus();
            return false;
        }
        
        // Show loading state
        const submitBtn = $('#submitBtn');
        submitBtn.prop('disabled', true);
        submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Creating Account...');
    });
    
    // Real-time validation for required fields
    $('input[required]').on('blur', function() {
        const field = $(this);
        const value = field.val().trim();
        
        if (!value) {
            field.addClass('is-invalid');
            if (!field.siblings('.invalid-feedback').length) {
                field.after(`<div class="invalid-feedback">${field.attr('name').replace('_', ' ')} is required</div>`);
            }
        } else {
            field.removeClass('is-invalid');
            field.siblings('.invalid-feedback').remove();
        }
    });
});
</script>
@endsection