@extends('layouts.admin_cdn')

@section('title', 'Create Driver Account - Unified KYC')

@section('content_header', 'Create Driver Account - Complete KYC Registration')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ url('admin/drivers') }}">Drivers</a></li>
    <li class="breadcrumb-item active">Create Driver</li>
@endsection

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.0/dist/select2-bootstrap4.min.css" />
<style>
.form-section {
    margin-bottom: 25px;
    padding: 20px;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    background: #ffffff;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.section-title {
    color: #007bff;
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 15px;
    border-bottom: 2px solid #007bff;
    padding-bottom: 5px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.required {
    color: #dc3545;
}

.field-helper {
    font-size: 0.85rem;
    color: #6c757d;
    margin-top: 0.25rem;
    font-style: italic;
}

.form-control:focus, .select2-container--bootstrap4 .select2-selection:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.step-indicator {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    border-left: 4px solid #007bff;
}

.file-upload-area {
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
    transition: all 0.3s ease;
    cursor: pointer;
    background: #f8f9fa;
}

.file-upload-area:hover {
    border-color: #007bff;
    background: #e7f3ff;
}

.upload-icon {
    font-size: 2rem;
    color: #6c757d;
    margin-bottom: 10px;
}

.creation-mode-selector {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    color: white;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 25px;
}

.mode-option {
    background: rgba(255,255,255,0.1);
    border: 2px solid rgba(255,255,255,0.2);
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.mode-option:hover, .mode-option.active {
    background: rgba(255,255,255,0.2);
    border-color: rgba(255,255,255,0.4);
}

.mode-option.active {
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.conditional-section {
    display: none;
}

.conditional-section.active {
    display: block;
}

@media (max-width: 768px) {
    .form-section {
        padding: 15px;
        margin-bottom: 20px;
    }
    
    .section-title {
        font-size: 1rem;
    }
    
    .file-upload-area {
        padding: 15px;
    }
}
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h5><i class="fas fa-exclamation-triangle"></i> Please correct the following errors:</h5>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        <!-- Creation Mode Selector -->
        <div class="creation-mode-selector">
            <h5><i class="fas fa-cogs"></i> Select Registration Mode</h5>
            <p class="mb-3">Choose how comprehensive you want the driver registration to be:</p>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="mode-option" data-mode="basic">
                        <h6><i class="fas fa-user-plus"></i> Basic Registration</h6>
                        <small>Essential fields only - Quick setup for immediate use</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mode-option active" data-mode="standard">
                        <h6><i class="fas fa-id-card"></i> Standard KYC</h6>
                        <small>Recommended - Includes identity verification fields</small>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mode-option" data-mode="comprehensive">
                        <h6><i class="fas fa-shield-alt"></i> Complete KYC</h6>
                        <small>Full verification - All fields and document uploads</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Progress Indicator -->
        <div class="step-indicator">
            <h6 class="mb-1"><i class="fas fa-info-circle"></i> <span id="mode-title">Standard KYC Registration</span></h6>
            <small class="text-muted" id="mode-description">Complete driver registration with identity verification fields</small>
        </div>

        <form action="{{ route('admin.drivers.store-unified') }}" method="POST" enctype="multipart/form-data" id="driverUnifiedForm" novalidate>
            @csrf
            
            <!-- Hidden field to track selected mode -->
            <input type="hidden" name="registration_mode" id="registration_mode" value="standard">
            
            <!-- 1. Basic Information (Always Required) -->
            <div class="form-section" id="basic-info">
                <h4 class="section-title">
                    <i class="fas fa-user"></i>
                    Basic Information
                </h4>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="driver_license_number">Driver License Number <span class="required">*</span></label>
                            <input type="text" class="form-control @error('driver_license_number') is-invalid @enderror" 
                                   id="driver_license_number" name="driver_license_number" 
                                   value="{{ old('driver_license_number') }}" required
                                   placeholder="Enter license number" style="text-transform: uppercase;">
                            <small class="field-helper">As written on your driver's license</small>
                            @error('driver_license_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="first_name">First Name <span class="required">*</span></label>
                            <input type="text" class="form-control @error('first_name') is-invalid @enderror" 
                                   id="first_name" name="first_name" 
                                   value="{{ old('first_name') }}" required
                                   placeholder="Enter first name">
                            @error('first_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="surname">Surname <span class="required">*</span></label>
                            <input type="text" class="form-control @error('surname') is-invalid @enderror" 
                                   id="surname" name="surname" 
                                   value="{{ old('surname') }}" required
                                   placeholder="Enter surname">
                            @error('surname')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email">Email Address <span class="required">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" 
                                   value="{{ old('email') }}" required
                                   placeholder="driver@example.com">
                            <small class="field-helper">Primary contact email</small>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="phone">Phone Number <span class="required">*</span></label>
                            <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" name="phone" 
                                   value="{{ old('phone') }}" required
                                   placeholder="+234 8XX XXX XXXX">
                            <small class="field-helper">Primary contact number</small>
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="password">Password <span class="required">*</span></label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                   id="password" name="password" required
                                   placeholder="Enter secure password">
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
                                   id="password_confirmation" name="password_confirmation" required
                                   placeholder="Re-enter password">
                            <small class="field-helper">Must match the password above</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. Standard KYC Fields (Show for Standard and Comprehensive) -->
            <div class="form-section conditional-section standard comprehensive active" id="standard-kyc">
                <h4 class="section-title">
                    <i class="fas fa-id-card"></i>
                    Identity & Personal Details
                </h4>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="date_of_birth">Date of Birth <span class="required">*</span></label>
                            <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" 
                                   id="date_of_birth" name="date_of_birth" 
                                   value="{{ old('date_of_birth') }}"
                                   max="{{ date('Y-m-d', strtotime('-18 years')) }}">
                            <small class="field-helper">Must be 18+ years old</small>
                            @error('date_of_birth')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="gender">Gender <span class="required">*</span></label>
                            <select class="form-control @error('gender') is-invalid @enderror" 
                                    id="gender" name="gender">
                                <option value="">Select Gender</option>
                                <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                            </select>
                            @error('gender')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="nationality_id">Nationality</label>
                            <select class="form-control @error('nationality_id') is-invalid @enderror" 
                                    id="nationality_id" name="nationality_id">
                                <option value="">Select Nationality</option>
                                <option value="1" {{ old('nationality_id') == '1' ? 'selected' : '' }}>Nigerian</option>
                                <option value="2" {{ old('nationality_id') == '2' ? 'selected' : '' }}>Ghanaian</option>
                                <option value="3" {{ old('nationality_id') == '3' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('nationality_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nin_number">NIN (National ID Number)</label>
                            <input type="text" class="form-control @error('nin_number') is-invalid @enderror" 
                                   id="nin_number" name="nin_number" 
                                   value="{{ old('nin_number') }}" maxlength="11"
                                   placeholder="12345678901">
                            <small class="field-helper">11-digit National Identification Number</small>
                            @error('nin_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="bvn_number">BVN (Bank Verification Number)</label>
                            <input type="text" class="form-control @error('bvn_number') is-invalid @enderror" 
                                   id="bvn_number" name="bvn_number" 
                                   value="{{ old('bvn_number') }}" maxlength="11"
                                   placeholder="12345678901">
                            <small class="field-helper">11-digit Bank Verification Number</small>
                            @error('bvn_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="address">Residential Address</label>
                    <textarea class="form-control @error('address') is-invalid @enderror" 
                              id="address" name="address" rows="3"
                              placeholder="Enter complete current address">{{ old('address') }}</textarea>
                    <small class="field-helper">Complete address where you currently live</small>
                    @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- 3. Comprehensive Fields (Show only for Comprehensive) -->
            <div class="form-section conditional-section comprehensive" id="comprehensive-fields">
                <h4 class="section-title">
                    <i class="fas fa-clipboard-list"></i>
                    Additional Details & Experience
                </h4>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="middle_name">Middle Name</label>
                            <input type="text" class="form-control @error('middle_name') is-invalid @enderror" 
                                   id="middle_name" name="middle_name" 
                                   value="{{ old('middle_name') }}"
                                   placeholder="Enter middle name (optional)">
                            @error('middle_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="state_of_origin">State of Origin</label>
                            <select class="form-control select2 @error('state_of_origin') is-invalid @enderror" 
                                    id="state_of_origin" name="state_of_origin">
                                <option value="">Select State of Origin</option>
                            </select>
                            @error('state_of_origin')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="lga_of_origin">LGA of Origin</label>
                            <select class="form-control select2 @error('lga_of_origin') is-invalid @enderror" 
                                    id="lga_of_origin" name="lga_of_origin" disabled>
                                <option value="">First select state</option>
                            </select>
                            @error('lga_of_origin')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="experience_years">Years of Experience</label>
                            <input type="number" class="form-control @error('experience_years') is-invalid @enderror" 
                                   id="experience_years" name="experience_years" 
                                   value="{{ old('experience_years') }}"
                                   min="0" max="50" placeholder="0">
                            <small class="field-helper">Total years of driving experience</small>
                            @error('experience_years')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="license_expiry_date">License Expiry Date</label>
                            <input type="date" class="form-control @error('license_expiry_date') is-invalid @enderror" 
                                   id="license_expiry_date" name="license_expiry_date" 
                                   value="{{ old('license_expiry_date') }}"
                                   min="{{ date('Y-m-d') }}">
                            <small class="field-helper">When does your license expire?</small>
                            @error('license_expiry_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="emergency_contact_name">Emergency Contact Name</label>
                    <input type="text" class="form-control @error('emergency_contact_name') is-invalid @enderror" 
                           id="emergency_contact_name" name="emergency_contact_name" 
                           value="{{ old('emergency_contact_name') }}"
                           placeholder="Full name of emergency contact">
                    @error('emergency_contact_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="emergency_contact_phone">Emergency Contact Phone</label>
                    <input type="tel" class="form-control @error('emergency_contact_phone') is-invalid @enderror" 
                           id="emergency_contact_phone" name="emergency_contact_phone" 
                           value="{{ old('emergency_contact_phone') }}"
                           placeholder="+234 xxx xxxx xxx">
                    @error('emergency_contact_phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- 4. Document Uploads (Show for Standard and Comprehensive) -->
            <div class="form-section conditional-section standard comprehensive active" id="document-uploads">
                <h4 class="section-title">
                    <i class="fas fa-file-upload"></i>
                    Document Uploads
                </h4>

                <div class="row">
                    <div class="col-md-6">
                        <div class="file-upload-area" onclick="document.getElementById('profile_photo').click()">
                            <div class="upload-icon">
                                <i class="fas fa-camera"></i>
                            </div>
                            <h6>Profile Photo</h6>
                            <p class="text-muted">Click to upload passport photograph</p>
                            <small class="field-helper">JPEG, PNG, JPG - Max 2MB</small>
                        </div>
                        <input type="file" id="profile_photo" name="profile_photo" 
                               accept="image/jpeg,image/png,image/jpg" style="display: none;">
                        @error('profile_photo')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <div class="file-upload-area" onclick="document.getElementById('license_front_image').click()">
                            <div class="upload-icon">
                                <i class="fas fa-id-card"></i>
                            </div>
                            <h6>License Front</h6>
                            <p class="text-muted">Click to upload license front image</p>
                            <small class="field-helper">JPEG, PNG, JPG, PDF - Max 5MB</small>
                        </div>
                        <input type="file" id="license_front_image" name="license_front_image" 
                               accept="image/jpeg,image/png,image/jpg,application/pdf" style="display: none;">
                        @error('license_front_image')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- 5. Account Status -->
            <div class="form-section" id="account-status">
                <h4 class="section-title">
                    <i class="fas fa-cogs"></i>
                    Account Status
                </h4>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="status">Account Status</label>
                            <select class="form-control @error('status') is-invalid @enderror" 
                                    id="status" name="status">
                                <option value="active" selected>Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="pending">Pending Verification</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="verification_status">Verification Status</label>
                            <select class="form-control @error('verification_status') is-invalid @enderror" 
                                    id="verification_status" name="verification_status">
                                <option value="pending" selected>Pending</option>
                                <option value="verified">Verified</option>
                                <option value="rejected">Rejected</option>
                            </select>
                            @error('verification_status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="kyc_status">KYC Status</label>
                            <select class="form-control @error('kyc_status') is-invalid @enderror" 
                                    id="kyc_status" name="kyc_status">
                                <option value="pending" selected>Pending</option>
                                <option value="completed">Completed</option>
                                <option value="failed">Failed</option>
                            </select>
                            @error('kyc_status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-section">
                <div class="row">
                    <div class="col-md-6">
                        <a href="{{ route('admin.drivers.index') }}" class="btn btn-secondary btn-lg">
                            <i class="fas fa-arrow-left"></i> Cancel
                        </a>
                    </div>
                    <div class="col-md-6 text-right">
                        <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                            <i class="fas fa-user-check"></i> Create Driver Account
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
        theme: 'bootstrap4',
        width: '100%'
    });

    // Registration mode switching
    $('.mode-option').on('click', function() {
        const mode = $(this).data('mode');
        
        // Update active state
        $('.mode-option').removeClass('active');
        $(this).addClass('active');
        
        // Update hidden field
        $('#registration_mode').val(mode);
        
        // Show/hide conditional sections
        $('.conditional-section').removeClass('active');
        $('.conditional-section.' + mode).addClass('active');
        
        // Update mode title and description
        const titles = {
            'basic': 'Basic Registration',
            'standard': 'Standard KYC Registration', 
            'comprehensive': 'Complete KYC Registration'
        };
        
        const descriptions = {
            'basic': 'Quick driver account setup with essential fields only',
            'standard': 'Standard registration with identity verification fields',
            'comprehensive': 'Comprehensive registration with all verification fields and documents'
        };
        
        $('#mode-title').text(titles[mode]);
        $('#mode-description').text(descriptions[mode]);
        
        // Update required fields based on mode
        updateRequiredFields(mode);
    });

    // Update required fields based on selected mode
    function updateRequiredFields(mode) {
        // Reset all required fields
        $('.conditional-section input, .conditional-section select, .conditional-section textarea').prop('required', false);
        
        // Set required fields based on mode
        if (mode === 'standard' || mode === 'comprehensive') {
            $('#date_of_birth, #gender').prop('required', true);
        }
    }

    // Nigerian States data (simplified)
    const nigerianStates = [
        {id: 1, name: 'Abia'}, {id: 2, name: 'Adamawa'}, {id: 3, name: 'Akwa Ibom'},
        {id: 4, name: 'Anambra'}, {id: 5, name: 'Bauchi'}, {id: 6, name: 'Bayelsa'},
        {id: 7, name: 'Benue'}, {id: 8, name: 'Borno'}, {id: 9, name: 'Cross River'},
        {id: 10, name: 'Delta'}, {id: 11, name: 'Ebonyi'}, {id: 12, name: 'Edo'},
        {id: 13, name: 'Ekiti'}, {id: 14, name: 'Enugu'}, {id: 15, name: 'FCT'},
        {id: 16, name: 'Gombe'}, {id: 17, name: 'Imo'}, {id: 18, name: 'Jigawa'},
        {id: 19, name: 'Kaduna'}, {id: 20, name: 'Kano'}, {id: 21, name: 'Katsina'},
        {id: 22, name: 'Kebbi'}, {id: 23, name: 'Kogi'}, {id: 24, name: 'Kwara'},
        {id: 25, name: 'Lagos'}, {id: 26, name: 'Nasarawa'}, {id: 27, name: 'Niger'},
        {id: 28, name: 'Ogun'}, {id: 29, name: 'Ondo'}, {id: 30, name: 'Osun'},
        {id: 31, name: 'Oyo'}, {id: 32, name: 'Plateau'}, {id: 33, name: 'Rivers'},
        {id: 34, name: 'Sokoto'}, {id: 35, name: 'Taraba'}, {id: 36, name: 'Yobe'}, {id: 37, name: 'Zamfara'}
    ];

    // Populate states dropdown
    function populateStates() {
        const stateSelect = $('#state_of_origin');
        stateSelect.empty().append('<option value="">Select State of Origin</option>');
        
        nigerianStates.forEach(function(state) {
            stateSelect.append(`<option value="${state.id}">${state.name}</option>`);
        });
    }

    // Initialize states
    populateStates();

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

    // File upload preview
    $('input[type="file"]').on('change', function() {
        const file = this.files[0];
        const uploadArea = $(this).prev('.file-upload-area');
        
        if (file) {
            const fileName = file.name;
            const fileSize = (file.size / 1024 / 1024).toFixed(2);
            uploadArea.find('p').html(`<strong>${fileName}</strong><br><small>${fileSize} MB</small>`);
            uploadArea.css('border-color', '#28a745');
        }
    });

    // NIN and BVN validation (numbers only)
    $('#nin_number, #bvn_number').on('input', function() {
        const value = $(this).val().replace(/\D/g, '');
        $(this).val(value.substring(0, 11));
    });

    // License number formatting
    $('#driver_license_number').on('input', function() {
        $(this).val($(this).val().toUpperCase());
    });

    // Phone number formatting
    $('#phone, #emergency_contact_phone').on('input', function() {
        let value = $(this).val().replace(/\D/g, '');
        
        if (value.length > 0) {
            if (value.startsWith('0')) {
                value = '+234' + value.substring(1);
            } else if (!value.startsWith('234') && !value.startsWith('+234')) {
                value = '+234' + value;
            } else if (value.startsWith('234') && !value.startsWith('+234')) {
                value = '+' + value;
            }
        }
        
        $(this).val(value);
    });

    // Form validation before submit
    $('#driverUnifiedForm').on('submit', function(e) {
        let isValid = true;
        let firstErrorField = null;
        
        // Password confirmation check
        const password = $('#password').val();
        const confirmation = $('#password_confirmation').val();
        
        if (password !== confirmation) {
            e.preventDefault();
            alert('Passwords do not match. Please check and try again.');
            $('#password_confirmation').focus();
            return false;
        }

        // Check required fields in active sections only
        $('.form-section:visible input[required], .form-section:visible select[required], .conditional-section.active input[required], .conditional-section.active select[required]').each(function() {
            const field = $(this);
            const value = field.val();
            
            if (!value || (Array.isArray(value) && value.length === 0)) {
                isValid = false;
                field.addClass('is-invalid');
                
                if (!firstErrorField) {
                    firstErrorField = field;
                }
                
                if (!field.siblings('.invalid-feedback').length) {
                    const label = field.closest('.form-group').find('label').text().replace('*', '').trim();
                    field.after(`<div class="invalid-feedback">${label} is required</div>`);
                }
            } else {
                field.removeClass('is-invalid');
                field.siblings('.invalid-feedback').remove();
            }
        });

        if (!isValid) {
            e.preventDefault();
            alert('Please fill in all required fields before submitting.');
            
            if (firstErrorField) {
                firstErrorField[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstErrorField.focus();
            }
            
            return false;
        }

        // Show loading state
        const submitBtn = $('#submitBtn');
        submitBtn.prop('disabled', true);
        submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Creating Account...');
    });

    // Real-time validation for required fields
    $('input[required], select[required]').on('blur change', function() {
        const field = $(this);
        const value = field.val();
        
        if (!value) {
            field.addClass('is-invalid');
            if (!field.siblings('.invalid-feedback').length) {
                const label = field.closest('.form-group').find('label').text().replace('*', '').trim();
                field.after(`<div class="invalid-feedback">${label} is required</div>`);
            }
        } else {
            field.removeClass('is-invalid');
            field.siblings('.invalid-feedback').remove();
        }
    });

    // Age validation
    $('#date_of_birth').on('change', function() {
        const birthDate = new Date($(this).val());
        const today = new Date();
        const age = Math.floor((today - birthDate) / (365.25 * 24 * 60 * 60 * 1000));
        
        if (age < 18) {
            $(this).addClass('is-invalid');
            if (!$(this).siblings('.invalid-feedback').length) {
                $(this).after('<div class="invalid-feedback">Driver must be at least 18 years old</div>');
            }
        } else {
            $(this).removeClass('is-invalid');
            $(this).siblings('.invalid-feedback').remove();
        }
    });
});
</script>
@endsection