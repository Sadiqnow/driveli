@extends('layouts.admin_cdn')

@section('title', 'Driver Full KYC Registration')

@section('content_header', 'Complete Driver Registration - Full KYC')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ url('admin/drivers') }}">Drivers</a></li>
    <li class="breadcrumb-item active">Full KYC Registration</li>
@endsection

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.0/dist/select2-bootstrap4.min.css" />
<style>
.form-section {
    margin-bottom: 30px;
    padding: 25px;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    background: #ffffff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.section-title {
    color: #007bff;
    font-size: 1.2rem;
    font-weight: 600;
    margin-bottom: 20px;
    border-bottom: 3px solid #007bff;
    padding-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-title i {
    font-size: 1.1rem;
}

.required {
    color: #dc3545;
    font-weight: bold;
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

.progress-indicator {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 25px;
    border: 1px solid #dee2e6;
}

.step-indicator {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    position: relative;
}

.step-indicator::before {
    content: '';
    position: absolute;
    top: 15px;
    left: 15px;
    right: 15px;
    height: 2px;
    background: #dee2e6;
    z-index: 1;
}

.step {
    display: flex;
    flex-direction: column;
    align-items: center;
    flex: 1;
    position: relative;
    z-index: 2;
}

.step-number {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    background: #6c757d;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-bottom: 8px;
    transition: all 0.3s ease;
}

.step.active .step-number {
    background: #007bff;
    transform: scale(1.1);
}

.step.completed .step-number {
    background: #28a745;
}

.step-label {
    font-size: 0.85rem;
    text-align: center;
    color: #6c757d;
    font-weight: 500;
}

.step.active .step-label {
    color: #007bff;
    font-weight: 600;
}

.step.completed .step-label {
    color: #28a745;
    font-weight: 600;
}

.form-row {
    margin-bottom: 15px;
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

.file-upload-area:hover,
.file-upload-area:focus {
    border-color: #007bff;
    background: #e7f3ff;
    outline: 2px solid #007bff;
    outline-offset: 2px;
}

.file-upload-area.dragover {
    border-color: #28a745;
    background: #f0fff4;
}

.upload-icon {
    font-size: 2rem;
    color: #6c757d;
    margin-bottom: 10px;
}

.btn-file {
    position: relative;
    overflow: hidden;
}

.btn-file input[type=file] {
    position: absolute;
    top: 0;
    right: 0;
    min-width: 100%;
    min-height: 100%;
    font-size: 100px;
    text-align: right;
    filter: alpha(opacity=0);
    opacity: 0;
    outline: none;
    cursor: inherit;
    display: block;
}

.multi-level-select {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
}

.multi-level-title {
    font-weight: 600;
    color: #495057;
    margin-bottom: 10px;
    font-size: 0.9rem;
}

.address-group {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    border: 1px solid #e9ecef;
}

.address-title {
    font-weight: 600;
    color: #495057;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.invalid-feedback {
    display: block;
}

.alert-kyc {
    border-left: 4px solid #007bff;
    background: linear-gradient(135deg, #e7f3ff 0%, #f0f8ff 100%);
}

.form-check-custom {
    padding-left: 1.5rem;
}

.form-check-custom .form-check-input {
    margin-left: -1.5rem;
}

/* Enhanced Mobile Responsiveness */
@media (max-width: 768px) {
    .form-section {
        padding: 15px;
        margin-bottom: 20px;
    }
    
    .section-title {
        font-size: 1.1rem;
        margin-bottom: 15px;
    }
    
    .step-indicator {
        flex-direction: column;
        gap: 10px;
        align-items: stretch;
    }
    
    .step {
        flex: none;
        flex-direction: row;
        justify-content: flex-start;
        align-items: center;
        padding: 10px;
        background: #f8f9fa;
        border-radius: 5px;
        margin-bottom: 5px;
    }
    
    .step-number {
        width: 25px;
        height: 25px;
        margin-bottom: 0;
        margin-right: 10px;
        flex-shrink: 0;
    }
    
    .step-label {
        text-align: left;
        font-size: 0.85rem;
        margin: 0;
    }
    
    .file-upload-area {
        padding: 15px;
        margin-bottom: 15px;
    }
    
    .upload-icon {
        font-size: 1.5rem;
    }
    
    .col-md-3, .col-md-4, .col-md-6 {
        margin-bottom: 15px;
    }
    
    .form-row {
        margin-bottom: 10px;
    }
    
    /* Stack form controls on mobile */
    .row .col-md-3,
    .row .col-md-4,
    .row .col-md-6 {
        flex: 0 0 100%;
        max-width: 100%;
    }
    
    /* Notification positioning for mobile */
    .notification-toast {
        top: 10px !important;
        right: 10px !important;
        left: 10px !important;
        min-width: auto !important;
        max-width: calc(100% - 20px) !important;
    }
    
    /* Button adjustments */
    .btn-lg {
        padding: 0.75rem 1.5rem;
        font-size: 1rem;
    }
    
    /* Select2 mobile optimization */
    .select2-container--bootstrap4 .select2-selection {
        min-height: 44px;
    }
}

@media (max-width: 576px) {
    .progress-indicator {
        padding: 15px 10px;
    }
    
    .form-section {
        padding: 10px;
        margin-bottom: 15px;
    }
    
    .section-title {
        font-size: 1rem;
        padding-bottom: 5px;
        border-bottom-width: 2px;
    }
    
    .step {
        padding: 8px;
    }
    
    .step-number {
        width: 22px;
        height: 22px;
        font-size: 0.8rem;
    }
    
    .step-label {
        font-size: 0.75rem;
    }
    
    .col-md-6 .text-right {
        text-align: left !important;
        margin-top: 10px;
    }
    
    .btn-lg {
        width: 100%;
        margin-bottom: 10px;
    }
}

.select2-container--bootstrap4 {
    width: 100% !important;
}
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert" aria-live="polite">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h5><i class="fas fa-exclamation-triangle" aria-hidden="true"></i> Please correct the following errors:</h5>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <div class="mt-2">
                    <small class="text-muted">
                        <i class="fas fa-lightbulb" aria-hidden="true"></i> 
                        Scroll down to see highlighted fields that need attention.
                    </small>
                </div>
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

        <!-- Enhanced Step Indicator -->
        <div class="progress-indicator">
            <div class="step-indicator">
                <div class="step active">
                    <div class="step-number">1</div>
                    <div class="step-label">Personal Info</div>
                </div>
                <div class="step">
                    <div class="step-number">2</div>
                    <div class="step-label">Origin Details</div>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <div class="step-label">Residential Info</div>
                </div>
                <div class="step">
                    <div class="step-number">4</div>
                    <div class="step-label">KYC Documents</div>
                </div>
                <div class="step">
                    <div class="step-number">5</div>
                    <div class="step-label">Verification</div>
                </div>
            </div>
            <div class="text-center">
                <small class="text-muted">Complete all sections to enable full driver verification and matching</small>
            </div>
        </div>

        <!-- KYC Information Alert -->
        <div class="alert alert-kyc alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h6><i class="fas fa-info-circle"></i> Complete KYC Registration Process</h6>
            <p class="mb-0">This comprehensive form collects all necessary information for complete driver verification and compliance. All fields marked with <span class="required">*</span> are mandatory for KYC completion.</p>
        </div>

        <form action="{{ route('admin.drivers.store-comprehensive') }}" method="POST" enctype="multipart/form-data" id="driverKYCForm" novalidate>
            @csrf
            
            <!-- 1. Basic Personal Information -->
            <fieldset class="form-section" id="section-personal">
                <legend class="section-title">
                    <i class="fas fa-user" aria-hidden="true"></i>
                    Basic Personal Information
                </legend>
                
                <div class="row form-row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="first_name">First Name <span class="required">*</span></label>
                            <input type="text" class="form-control @error('first_name') is-invalid @enderror" 
                                   id="first_name" name="first_name" 
                                   value="{{ old('first_name') }}" required
                                   placeholder="Enter first name">
                            <small class="field-helper">As written on official documents</small>
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
                            <small class="field-helper">Family name/Last name</small>
                            @error('surname')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="middle_name">Middle Name</label>
                            <input type="text" class="form-control @error('middle_name') is-invalid @enderror" 
                                   id="middle_name" name="middle_name" 
                                   value="{{ old('middle_name') }}"
                                   placeholder="Enter middle name (optional)">
                            <small class="field-helper">Optional middle name</small>
                            @error('middle_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row form-row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="nickname">Nickname/Known As</label>
                            <input type="text" class="form-control @error('nickname') is-invalid @enderror" 
                                   id="nickname" name="nickname" 
                                   value="{{ old('nickname') }}"
                                   placeholder="Common name (optional)">
                            <small class="field-helper">Name commonly used</small>
                            @error('nickname')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="date_of_birth">Date of Birth <span class="required">*</span></label>
                            <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" 
                                   id="date_of_birth" name="date_of_birth" 
                                   value="{{ old('date_of_birth') }}" required
                                   max="{{ date('Y-m-d', strtotime('-18 years')) }}">
                            <small class="field-helper">Must be 18+ years old</small>
                            @error('date_of_birth')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="gender">Gender <span class="required">*</span></label>
                            <select class="form-control @error('gender') is-invalid @enderror" 
                                    id="gender" name="gender" required>
                                <option value="">Select Gender</option>
                                <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                                <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('gender')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="blood_group">Blood Group</label>
                            <select class="form-control @error('blood_group') is-invalid @enderror" 
                                    id="blood_group" name="blood_group">
                                <option value="">Select Blood Group</option>
                                <option value="A+" {{ old('blood_group') == 'A+' ? 'selected' : '' }}>A+</option>
                                <option value="A-" {{ old('blood_group') == 'A-' ? 'selected' : '' }}>A-</option>
                                <option value="B+" {{ old('blood_group') == 'B+' ? 'selected' : '' }}>B+</option>
                                <option value="B-" {{ old('blood_group') == 'B-' ? 'selected' : '' }}>B-</option>
                                <option value="AB+" {{ old('blood_group') == 'AB+' ? 'selected' : '' }}>AB+</option>
                                <option value="AB-" {{ old('blood_group') == 'AB-' ? 'selected' : '' }}>AB-</option>
                                <option value="O+" {{ old('blood_group') == 'O+' ? 'selected' : '' }}>O+</option>
                                <option value="O-" {{ old('blood_group') == 'O-' ? 'selected' : '' }}>O-</option>
                            </select>
                            <small class="field-helper">For medical emergencies</small>
                            @error('blood_group')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row form-row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="religion">Religion</label>
                            <select class="form-control @error('religion') is-invalid @enderror" 
                                    id="religion" name="religion">
                                <option value="">Select Religion</option>
                                <option value="Christianity" {{ old('religion') == 'Christianity' ? 'selected' : '' }}>Christianity</option>
                                <option value="Islam" {{ old('religion') == 'Islam' ? 'selected' : '' }}>Islam</option>
                                <option value="Traditional" {{ old('religion') == 'Traditional' ? 'selected' : '' }}>Traditional</option>
                                <option value="Buddhism" {{ old('religion') == 'Buddhism' ? 'selected' : '' }}>Buddhism</option>
                                <option value="Judaism" {{ old('religion') == 'Judaism' ? 'selected' : '' }}>Judaism</option>
                                <option value="Other" {{ old('religion') == 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('religion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="height_meters">Height (meters)</label>
                            <input type="number" class="form-control @error('height_meters') is-invalid @enderror" 
                                   id="height_meters" name="height_meters" 
                                   value="{{ old('height_meters') }}"
                                   step="0.01" min="1.0" max="2.5"
                                   placeholder="e.g. 1.75">
                            <small class="field-helper">Height in meters (e.g., 1.75m)</small>
                            @error('height_meters')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="disability_status">Disability Status</label>
                            <select class="form-control @error('disability_status') is-invalid @enderror" 
                                    id="disability_status" name="disability_status">
                                <option value="">Select Status</option>
                                <option value="None" {{ old('disability_status') == 'None' ? 'selected' : '' }}>None</option>
                                <option value="Physical" {{ old('disability_status') == 'Physical' ? 'selected' : '' }}>Physical Disability</option>
                                <option value="Visual" {{ old('disability_status') == 'Visual' ? 'selected' : '' }}>Visual Impairment</option>
                                <option value="Hearing" {{ old('disability_status') == 'Hearing' ? 'selected' : '' }}>Hearing Impairment</option>
                                <option value="Other" {{ old('disability_status') == 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                            <small class="field-helper">For accommodation purposes</small>
                            @error('disability_status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row form-row">
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
                    <div class="col-md-3">
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
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="phone_2">Secondary Phone</label>
                            <input type="tel" class="form-control @error('phone_2') is-invalid @enderror" 
                                   id="phone_2" name="phone_2" 
                                   value="{{ old('phone_2') }}"
                                   placeholder="+234 8XX XXX XXXX">
                            <small class="field-helper">Alternative contact</small>
                            @error('phone_2')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row form-row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="password">Password <span class="required">*</span></label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                   id="password" name="password" required
                                   placeholder="Enter secure password">
                            <small class="field-helper">Minimum 8 characters with letters, numbers and symbols</small>
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
            </fieldset>

            <!-- 2. Origin Information -->
            <fieldset class="form-section" id="section-origin">
                <legend class="section-title">
                    <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
                    Origin Details
                </legend>
                
                <div class="address-group">
                    <div class="address-title">
                        <i class="fas fa-home"></i>
                        State and Local Government of Origin
                    </div>
                    
                    <div class="multi-level-select">
                        <div class="multi-level-title">Select Origin Location</div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="state_of_origin">State of Origin <span class="required">*</span></label>
                                    <select class="form-control select2 @error('state_of_origin') is-invalid @enderror" 
                                            id="state_of_origin" name="state_of_origin" required
                                            data-placeholder="Select state of origin">
                                        <option value="">Select State of Origin</option>
                                    </select>
                                    <small class="field-helper">Where you were born or originally from</small>
                                    @error('state_of_origin')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="lga_of_origin">LGA of Origin <span class="required">*</span></label>
                                    <select class="form-control select2 @error('lga_of_origin') is-invalid @enderror" 
                                            id="lga_of_origin" name="lga_of_origin" required
                                            data-placeholder="Select LGA of origin" disabled>
                                        <option value="">First select state</option>
                                    </select>
                                    <small class="field-helper">Local Government Area of origin</small>
                                    @error('lga_of_origin')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="address_of_origin">Origin Address Details</label>
                        <textarea class="form-control @error('address_of_origin') is-invalid @enderror" 
                                  id="address_of_origin" name="address_of_origin" rows="3"
                                  placeholder="Enter detailed address of origin (village, town, street, etc.)">{{ old('address_of_origin') }}</textarea>
                        <small class="field-helper">Detailed address in your place of origin</small>
                        @error('address_of_origin')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- 3. Residential Information -->
            <div class="form-section" id="section-residential">
                <h4 class="section-title">
                    <i class="fas fa-home"></i>
                    Current Residential Information
                </h4>
                
                <div class="address-group">
                    <div class="address-title">
                        <i class="fas fa-map-marked-alt"></i>
                        Current Residence Location
                    </div>
                    
                    <div class="multi-level-select">
                        <div class="multi-level-title">Select Current Residence</div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="residence_state_id">State of Residence <span class="required">*</span></label>
                                    <select class="form-control select2 @error('residence_state_id') is-invalid @enderror" 
                                            id="residence_state_id" name="residence_state_id" required
                                            data-placeholder="Select current state of residence">
                                        <option value="">Select State of Residence</option>
                                    </select>
                                    <small class="field-helper">Where you currently live</small>
                                    @error('residence_state_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="residence_lga_id">LGA of Residence <span class="required">*</span></label>
                                    <select class="form-control select2 @error('residence_lga_id') is-invalid @enderror" 
                                            id="residence_lga_id" name="residence_lga_id" required
                                            data-placeholder="Select LGA of residence" disabled>
                                        <option value="">First select state</option>
                                    </select>
                                    <small class="field-helper">Local Government Area where you live</small>
                                    @error('residence_lga_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="residence_address">Current Residential Address <span class="required">*</span></label>
                        <textarea class="form-control @error('residence_address') is-invalid @enderror" 
                                  id="residence_address" name="residence_address" rows="3" required
                                  placeholder="Enter your complete current address (house number, street name, area, etc.)">{{ old('residence_address') }}</textarea>
                        <small class="field-helper">Complete address where you currently live</small>
                        @error('residence_address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- 4. Identity & Nationality Information -->
            <div class="form-section" id="section-identity">
                <h4 class="section-title">
                    <i class="fas fa-id-card"></i>
                    Identity & Nationality Information
                </h4>

                <div class="row form-row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="nationality_id">Nationality <span class="required">*</span></label>
                            <select class="form-control select2 @error('nationality_id') is-invalid @enderror" 
                                    id="nationality_id" name="nationality_id" required
                                    data-placeholder="Select nationality">
                                <option value="">Select Nationality</option>
                                <option value="1" {{ old('nationality_id') == '1' ? 'selected' : '' }}>Nigerian</option>
                                <option value="2" {{ old('nationality_id') == '2' ? 'selected' : '' }}>Ghanaian</option>
                                <option value="3" {{ old('nationality_id') == '3' ? 'selected' : '' }}>Cameroonian</option>
                                <option value="4" {{ old('nationality_id') == '4' ? 'selected' : '' }}>South African</option>
                                <option value="5" {{ old('nationality_id') == '5' ? 'selected' : '' }}>Kenyan</option>
                                <option value="6" {{ old('nationality_id') == '6' ? 'selected' : '' }}>Beninese</option>
                                <option value="7" {{ old('nationality_id') == '7' ? 'selected' : '' }}>Togolese</option>
                                <option value="8" {{ old('nationality_id') == '8' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('nationality_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="nin_number">NIN (National Identification Number)</label>
                            <input type="text" class="form-control @error('nin_number') is-invalid @enderror" 
                                   id="nin_number" name="nin_number" 
                                   value="{{ old('nin_number') }}" maxlength="11"
                                   placeholder="12345678901">
                            <small class="field-helper">11-digit Nigerian National ID Number</small>
                            @error('nin_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
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
            </div>

            <!-- 5. License Information -->
            <div class="form-section" id="section-license">
                <h4 class="section-title">
                    <i class="fas fa-id-badge"></i>
                    Driving License Information
                </h4>

                <div class="row form-row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="license_number">License Number <span class="required">*</span></label>
                            <input type="text" class="form-control @error('license_number') is-invalid @enderror" 
                                   id="license_number" name="license_number" 
                                   value="{{ old('license_number') }}" required
                                   placeholder="ABC123456789" style="text-transform: uppercase;">
                            <small class="field-helper">As written on your driver's license</small>
                            @error('license_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="license_class">License Class <span class="required">*</span></label>
                            <select class="form-control @error('license_class') is-invalid @enderror" 
                                    id="license_class" name="license_class" required>
                                <option value="">Select License Class</option>
                                <option value="A" {{ old('license_class') == 'A' ? 'selected' : '' }}>Class A (Motorcycles)</option>
                                <option value="B" {{ old('license_class') == 'B' ? 'selected' : '' }}>Class B (Cars, Light Vehicles)</option>
                                <option value="C" {{ old('license_class') == 'C' ? 'selected' : '' }}>Class C (Medium Trucks)</option>
                                <option value="D" {{ old('license_class') == 'D' ? 'selected' : '' }}>Class D (Heavy Vehicles)</option>
                                <option value="E" {{ old('license_class') == 'E' ? 'selected' : '' }}>Class E (Articulated Vehicles)</option>
                                <option value="Commercial" {{ old('license_class') == 'Commercial' ? 'selected' : '' }}>Commercial License</option>
                            </select>
                            @error('license_class')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
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
            </div>

            <!-- 6. Employment Information -->
            <div class="form-section" id="section-employment">
                <h4 class="section-title">
                    <i class="fas fa-briefcase"></i>
                    Employment & Experience Information
                </h4>

                <div class="row form-row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="current_employer">Current Employer</label>
                            <input type="text" class="form-control @error('current_employer') is-invalid @enderror" 
                                   id="current_employer" name="current_employer" 
                                   value="{{ old('current_employer') }}"
                                   placeholder="Company/Employer name">
                            <small class="field-helper">Current or most recent employer</small>
                            @error('current_employer')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
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
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="employment_start_date">Employment Start Date</label>
                            <input type="date" class="form-control @error('employment_start_date') is-invalid @enderror" 
                                   id="employment_start_date" name="employment_start_date" 
                                   value="{{ old('employment_start_date') }}"
                                   max="{{ date('Y-m-d') }}">
                            <small class="field-helper">When did current employment start?</small>
                            @error('employment_start_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row form-row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="vehicle_types">Vehicle Types (Select multiple)</label>
                            <select class="form-control select2-multiple @error('vehicle_types') is-invalid @enderror" 
                                    id="vehicle_types" name="vehicle_types[]" multiple
                                    data-placeholder="Select vehicle types you can drive">
                                <option value="Car" {{ in_array('Car', old('vehicle_types', [])) ? 'selected' : '' }}>Car</option>
                                <option value="Van" {{ in_array('Van', old('vehicle_types', [])) ? 'selected' : '' }}>Van</option>
                                <option value="Truck" {{ in_array('Truck', old('vehicle_types', [])) ? 'selected' : '' }}>Truck</option>
                                <option value="Bus" {{ in_array('Bus', old('vehicle_types', [])) ? 'selected' : '' }}>Bus</option>
                                <option value="Motorcycle" {{ in_array('Motorcycle', old('vehicle_types', [])) ? 'selected' : '' }}>Motorcycle</option>
                                <option value="Trailer" {{ in_array('Trailer', old('vehicle_types', [])) ? 'selected' : '' }}>Trailer</option>
                            </select>
                            <small class="field-helper">Types of vehicles you're qualified to drive</small>
                            @error('vehicle_types')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="work_regions">Preferred Work Regions</label>
                            <input type="text" class="form-control @error('work_regions') is-invalid @enderror" 
                                   id="work_regions" name="work_regions" 
                                   value="{{ old('work_regions') }}"
                                   placeholder="e.g., Lagos, Abuja, South-West">
                            <small class="field-helper">Areas where you prefer to work</small>
                            @error('work_regions')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="special_skills">Special Skills & Certifications</label>
                    <textarea class="form-control @error('special_skills') is-invalid @enderror" 
                              id="special_skills" name="special_skills" rows="3"
                              placeholder="List any special driving skills, certifications, or relevant qualifications">{{ old('special_skills') }}</textarea>
                    <small class="field-helper">Additional qualifications that make you stand out</small>
                    @error('special_skills')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- 7. File Uploads -->
            <div class="form-section" id="section-documents">
                <h4 class="section-title">
                    <i class="fas fa-file-upload"></i>
                    Document Uploads
                </h4>

                <div class="row">
                    <div class="col-md-6">
                        <div class="file-upload-area" 
                             onclick="document.getElementById('profile_photo').click()"
                             onkeypress="handleKeyPress(event, 'profile_photo')"
                             tabindex="0" 
                             role="button" 
                             aria-label="Upload profile photo"
                             aria-describedby="profile-photo-help">
                            <div class="upload-icon">
                                <i class="fas fa-camera" aria-hidden="true"></i>
                            </div>
                            <h6>Profile Photo</h6>
                            <p class="text-muted">Click or press Enter to upload passport photograph</p>
                            <small class="field-helper" id="profile-photo-help">JPEG, PNG, JPG - Max 2MB</small>
                        </div>
                        <input type="file" id="profile_photo" name="profile_photo" 
                               accept="image/jpeg,image/png,image/jpg" 
                               style="display: none;"
                               aria-describedby="profile-photo-help">
                        @error('profile_photo')
                            <div class="invalid-feedback d-block" role="alert">{{ $message }}</div>
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

                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="file-upload-area" onclick="document.getElementById('license_back_image').click()">
                            <div class="upload-icon">
                                <i class="fas fa-id-card"></i>
                            </div>
                            <h6>License Back</h6>
                            <p class="text-muted">Click to upload license back image</p>
                            <small class="field-helper">JPEG, PNG, JPG, PDF - Max 5MB</small>
                        </div>
                        <input type="file" id="license_back_image" name="license_back_image" 
                               accept="image/jpeg,image/png,image/jpg,application/pdf" style="display: none;">
                        @error('license_back_image')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <div class="file-upload-area" onclick="document.getElementById('nin_document').click()">
                            <div class="upload-icon">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <h6>NIN Document</h6>
                            <p class="text-muted">Click to upload NIN slip/document</p>
                            <small class="field-helper">JPEG, PNG, JPG, PDF - Max 5MB</small>
                        </div>
                        <input type="file" id="nin_document" name="nin_document" 
                               accept="image/jpeg,image/png,image/jpg,application/pdf" style="display: none;">
                        @error('nin_document')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- 8. Status Configuration -->
            <div class="form-section" id="section-status">
                <h4 class="section-title">
                    <i class="fas fa-cogs"></i>
                    Account Status & Configuration
                </h4>

                <div class="row form-row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="status">Account Status</label>
                            <select class="form-control @error('status') is-invalid @enderror" 
                                    id="status" name="status">
                                <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="suspended" {{ old('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                                <option value="blocked" {{ old('status') == 'blocked' ? 'selected' : '' }}>Blocked</option>
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
                                <option value="pending" {{ old('verification_status', 'pending') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="verified" {{ old('verification_status') == 'verified' ? 'selected' : '' }}>Verified</option>
                                <option value="rejected" {{ old('verification_status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                            @error('verification_status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="verification_notes">Verification Notes</label>
                            <input type="text" class="form-control @error('verification_notes') is-invalid @enderror" 
                                   id="verification_notes" name="verification_notes" 
                                   value="{{ old('verification_notes') }}"
                                   placeholder="Admin verification notes">
                            <small class="field-helper">Internal notes about verification</small>
                            @error('verification_notes')
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
                        <button type="button" class="btn btn-outline-primary btn-lg ml-2" id="previewBtn">
                            <i class="fas fa-eye"></i> Preview
                        </button>
                    </div>
                    <div class="col-md-6 text-right">
                        <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                            <i class="fas fa-user-check"></i> Create Driver Profile
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

    $('.select2-multiple').select2({
        theme: 'bootstrap4',
        width: '100%',
        closeOnSelect: false
    });

    // Nigerian States and LGAs data structure
    const nigerianStatesLGAs = {
        states: [
            {id: 1, name: 'Abia', code: 'AB'},
            {id: 2, name: 'Adamawa', code: 'AD'},
            {id: 3, name: 'Akwa Ibom', code: 'AK'},
            {id: 4, name: 'Anambra', code: 'AN'},
            {id: 5, name: 'Bauchi', code: 'BA'},
            {id: 6, name: 'Bayelsa', code: 'BY'},
            {id: 7, name: 'Benue', code: 'BN'},
            {id: 8, name: 'Borno', code: 'BO'},
            {id: 9, name: 'Cross River', code: 'CR'},
            {id: 10, name: 'Delta', code: 'DE'},
            {id: 11, name: 'Ebonyi', code: 'EB'},
            {id: 12, name: 'Edo', code: 'ED'},
            {id: 13, name: 'Ekiti', code: 'EK'},
            {id: 14, name: 'Enugu', code: 'EN'},
            {id: 15, name: 'FCT', code: 'FC'},
            {id: 16, name: 'Gombe', code: 'GO'},
            {id: 17, name: 'Imo', code: 'IM'},
            {id: 18, name: 'Jigawa', code: 'JI'},
            {id: 19, name: 'Kaduna', code: 'KD'},
            {id: 20, name: 'Kano', code: 'KN'},
            {id: 21, name: 'Katsina', code: 'KT'},
            {id: 22, name: 'Kebbi', code: 'KB'},
            {id: 23, name: 'Kogi', code: 'KG'},
            {id: 24, name: 'Kwara', code: 'KW'},
            {id: 25, name: 'Lagos', code: 'LA'},
            {id: 26, name: 'Nasarawa', code: 'NA'},
            {id: 27, name: 'Niger', code: 'NI'},
            {id: 28, name: 'Ogun', code: 'OG'},
            {id: 29, name: 'Ondo', code: 'ON'},
            {id: 30, name: 'Osun', code: 'OS'},
            {id: 31, name: 'Oyo', code: 'OY'},
            {id: 32, name: 'Plateau', code: 'PL'},
            {id: 33, name: 'Rivers', code: 'RI'},
            {id: 34, name: 'Sokoto', code: 'SO'},
            {id: 35, name: 'Taraba', code: 'TA'},
            {id: 36, name: 'Yobe', code: 'YO'},
            {id: 37, name: 'Zamfara', code: 'ZA'}
        ],
        lgas: {
            25: [ // Lagos
                {id: 1, name: 'Agege'}, {id: 2, name: 'Ajeromi-Ifelodun'}, {id: 3, name: 'Alimosho'},
                {id: 4, name: 'Amuwo-Odofin'}, {id: 5, name: 'Apapa'}, {id: 6, name: 'Badagry'},
                {id: 7, name: 'Epe'}, {id: 8, name: 'Eti-Osa'}, {id: 9, name: 'Ibeju-Lekki'},
                {id: 10, name: 'Ifako-Ijaiye'}, {id: 11, name: 'Ikeja'}, {id: 12, name: 'Ikorodu'},
                {id: 13, name: 'Kosofe'}, {id: 14, name: 'Lagos Island'}, {id: 15, name: 'Lagos Mainland'},
                {id: 16, name: 'Mushin'}, {id: 17, name: 'Ojo'}, {id: 18, name: 'Oshodi-Isolo'},
                {id: 19, name: 'Shomolu'}, {id: 20, name: 'Surulere'}
            ],
            15: [ // FCT
                {id: 21, name: 'Abaji'}, {id: 22, name: 'Abuja Municipal'}, {id: 23, name: 'Bwari'},
                {id: 24, name: 'Gwagwalada'}, {id: 25, name: 'Kuje'}, {id: 26, name: 'Kwali'}
            ],
            20: [ // Kano
                {id: 27, name: 'Ajingi'}, {id: 28, name: 'Albasu'}, {id: 29, name: 'Bagwai'},
                {id: 30, name: 'Bebeji'}, {id: 31, name: 'Bichi'}, {id: 32, name: 'Bunkure'},
                {id: 33, name: 'Dala'}, {id: 34, name: 'Dambatta'}, {id: 35, name: 'Dawakin Kudu'},
                {id: 36, name: 'Dawakin Tofa'}, {id: 37, name: 'Doguwa'}, {id: 38, name: 'Fagge'},
                {id: 39, name: 'Gabasawa'}, {id: 40, name: 'Garko'}, {id: 41, name: 'Garun Mallam'},
                {id: 42, name: 'Gaya'}, {id: 43, name: 'Gezawa'}, {id: 44, name: 'Gwale'},
                {id: 45, name: 'Gwarzo'}, {id: 46, name: 'Kabo'}, {id: 47, name: 'Kano Municipal'},
                {id: 48, name: 'Karaye'}, {id: 49, name: 'Kibiya'}, {id: 50, name: 'Kiru'},
                {id: 51, name: 'Kumbotso'}, {id: 52, name: 'Kunchi'}, {id: 53, name: 'Kura'},
                {id: 54, name: 'Madobi'}, {id: 55, name: 'Makoda'}, {id: 56, name: 'Minjibir'},
                {id: 57, name: 'Nasarawa'}, {id: 58, name: 'Rano'}, {id: 59, name: 'Rimin Gado'},
                {id: 60, name: 'Rogo'}, {id: 61, name: 'Shanono'}, {id: 62, name: 'Sumaila'},
                {id: 63, name: 'Takai'}, {id: 64, name: 'Tarauni'}, {id: 65, name: 'Tofa'},
                {id: 66, name: 'Tsanyawa'}, {id: 67, name: 'Tudun Wada'}, {id: 68, name: 'Ungogo'},
                {id: 69, name: 'Warawa'}, {id: 70, name: 'Wudil'}
            ]
            // Add more states and their LGAs as needed
        }
    };

    // Populate state dropdowns using API
    function populateStates() {
        const originSelect = $('#state_of_origin');
        const residenceSelect = $('#residence_state_id');
        
        // Show loading state with spinner
        const loadingHtml = '<option value=""><i class="fas fa-spinner fa-spin"></i> Loading states...</option>';
        originSelect.empty().append(loadingHtml);
        residenceSelect.empty().append(loadingHtml);
        
        // Disable dropdowns during loading
        originSelect.prop('disabled', true);
        residenceSelect.prop('disabled', true);
        
        // Fetch states from API with timeout
        $.ajax({
            url: '/api/location/states',
            method: 'GET',
            timeout: 10000, // 10 second timeout
            success: function(response) {
                if (response.success && response.data && response.data.length > 0) {
                    // Clear loading states
                    originSelect.empty().append('<option value="">Select State of Origin</option>');
                    residenceSelect.empty().append('<option value="">Select State of Residence</option>');
                    
                    // Populate both dropdowns
                    response.data.forEach(function(state) {
                        originSelect.append(`<option value="${state.id}">${state.name}</option>`);
                        residenceSelect.append(`<option value="${state.id}">${state.name}</option>`);
                    });
                    
                    // Re-enable dropdowns
                    originSelect.prop('disabled', false);
                    residenceSelect.prop('disabled', false);
                    
                    console.log('States loaded successfully:', response.count, 'states');
                    
                    // Show success indicator
                    showNotification('States loaded successfully', 'success');
                } else {
                    console.error('Failed to load states:', response.message);
                    loadStaticStatesWithError('API returned no data');
                }
            },
            error: function(xhr, status, error) {
                console.error('API Error loading states:', xhr.status, error);
                let errorMessage = 'Failed to load states from server';
                
                if (xhr.status === 404) {
                    errorMessage = 'States API endpoint not found';
                } else if (xhr.status === 500) {
                    errorMessage = 'Server error loading states';
                } else if (status === 'timeout') {
                    errorMessage = 'Request timeout - loading fallback data';
                }
                
                loadStaticStatesWithError(errorMessage);
            }
        });
    }
    
    // Enhanced fallback function with error notification
    function loadStaticStatesWithError(errorMessage) {
        showNotification(errorMessage + ' - Using offline data', 'warning');
        loadStaticStates();
    }
    
    // Fallback function to load static states
    function loadStaticStates() {
        const originSelect = $('#state_of_origin');
        const residenceSelect = $('#residence_state_id');
        
        originSelect.empty().append('<option value="">Select State of Origin</option>');
        residenceSelect.empty().append('<option value="">Select State of Residence</option>');
        
        // Use the static data if API fails
        nigerianStatesLGAs.states.forEach(function(state) {
            originSelect.append(`<option value="${state.id}">${state.name}</option>`);
            residenceSelect.append(`<option value="${state.id}">${state.name}</option>`);
        });
        
        // Re-enable dropdowns
        originSelect.prop('disabled', false);
        residenceSelect.prop('disabled', false);
    }
    
    // Notification system for user feedback
    function showNotification(message, type = 'info') {
        // Remove existing notifications
        $('.notification-toast').remove();
        
        const alertClass = {
            'success': 'alert-success',
            'error': 'alert-danger',
            'warning': 'alert-warning',
            'info': 'alert-info'
        }[type] || 'alert-info';
        
        const icon = {
            'success': 'fa-check-circle',
            'error': 'fa-exclamation-triangle',
            'warning': 'fa-exclamation-circle',
            'info': 'fa-info-circle'
        }[type] || 'fa-info-circle';
        
        const notification = $(`
            <div class="alert ${alertClass} notification-toast" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
                <i class="fas ${icon}"></i> ${message}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        `);
        
        $('body').append(notification);
        
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            notification.fadeOut(300, () => notification.remove());
        }, 5000);
    }

    // Populate LGAs based on state selection using API
    function populateLGAs(stateId, lgaSelectId, placeholder) {
        const lgaSelect = $(lgaSelectId);
        
        if (!stateId) {
            lgaSelect.empty().append(`<option value="">${placeholder}</option>`);
            lgaSelect.prop('disabled', true);
            lgaSelect.select2({theme: 'bootstrap4', width: '100%'});
            return;
        }
        
        // Show loading state
        lgaSelect.empty().append('<option value="">Loading LGAs...</option>');
        lgaSelect.prop('disabled', false);
        
        // Fetch LGAs from API
        $.ajax({
            url: `/api/location/states/${stateId}/lgas`,
            method: 'GET',
            success: function(response) {
                if (response.success && response.data) {
                    // Clear loading state
                    lgaSelect.empty().append(`<option value="">${placeholder}</option>`);
                    
                    // Populate LGAs
                    response.data.forEach(function(lga) {
                        lgaSelect.append(`<option value="${lga.id}">${lga.name}</option>`);
                    });
                    
                    lgaSelect.prop('disabled', false);
                    console.log(`LGAs loaded for state ${stateId}:`, response.count, 'LGAs');
                } else {
                    console.error('Failed to load LGAs:', response.message);
                    // Fallback to static data
                    loadStaticLGAs(stateId, lgaSelectId, placeholder);
                }
            },
            error: function(xhr, status, error) {
                console.error('API Error loading LGAs:', error);
                // Fallback to static data
                loadStaticLGAs(stateId, lgaSelectId, placeholder);
            },
            complete: function() {
                // Reinitialize select2
                lgaSelect.select2({
                    theme: 'bootstrap4',
                    width: '100%'
                });
            }
        });
    }
    
    // Fallback function to load static LGAs
    function loadStaticLGAs(stateId, lgaSelectId, placeholder) {
        const lgaSelect = $(lgaSelectId);
        lgaSelect.empty().append(`<option value="">${placeholder}</option>`);
        
        if (stateId && nigerianStatesLGAs.lgas[stateId]) {
            nigerianStatesLGAs.lgas[stateId].forEach(function(lga) {
                lgaSelect.append(`<option value="${lga.id}">${lga.name}</option>`);
            });
            lgaSelect.prop('disabled', false);
        } else {
            lgaSelect.prop('disabled', true);
            console.log('LGAs for state ID:', stateId, 'not in static data');
        }
        
        // Reinitialize select2
        lgaSelect.select2({
            theme: 'bootstrap4',
            width: '100%'
        });
    }

    // Initialize states
    populateStates();
    
    // Keyboard accessibility handler
    window.handleKeyPress = function(event, inputId) {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            document.getElementById(inputId).click();
        }
    };

    // Origin state change handler
    $('#state_of_origin').change(function() {
        const stateId = $(this).val();
        populateLGAs(stateId, '#lga_of_origin', 'Select LGA of Origin');
    });

    // Residence state change handler
    $('#residence_state_id').change(function() {
        const stateId = $(this).val();
        populateLGAs(stateId, '#residence_lga_id', 'Select LGA of Residence');
    });

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
            uploadArea.addClass('border-success').removeClass('border-danger');
        }
    });

    // NIN and BVN validation (numbers only)
    $('#nin_number, #bvn_number').on('input', function() {
        const value = $(this).val().replace(/\D/g, '');
        $(this).val(value.substring(0, 11));
    });

    // License number formatting
    $('#license_number').on('input', function() {
        $(this).val($(this).val().toUpperCase());
    });

    // Phone number formatting
    $('#phone, #phone_2').on('input', function() {
        let value = $(this).val().replace(/\D/g, '');
        
        // Format Nigerian phone numbers
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

    // Enhanced form validation before submit
    $('#driverKYCForm').on('submit', function(e) {
        let isValid = true;
        let firstErrorField = null;
        
        // Password confirmation check
        const password = $('#password').val();
        const confirmation = $('#password_confirmation').val();
        
        if (password !== confirmation) {
            e.preventDefault();
            showNotification('Passwords do not match. Please check and try again.', 'error');
            $('#password_confirmation').focus();
            return false;
        }

        // Check required fields
        $('input[required], select[required], textarea[required]').each(function() {
            const field = $(this);
            const value = field.val();
            
            if (!value || (Array.isArray(value) && value.length === 0)) {
                isValid = false;
                field.addClass('is-invalid');
                
                if (!firstErrorField) {
                    firstErrorField = field;
                }
                
                // Add error message if not already present
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
            showNotification('Please fill in all required fields before submitting.', 'error');
            
            if (firstErrorField) {
                firstErrorField[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                firstErrorField.focus();
            }
            
            return false;
        }

        // Show loading state
        const submitBtn = $('#submitBtn');
        submitBtn.prop('disabled', true);
        submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Creating Driver Profile...');

        // Update progress indicator
        $('.step').addClass('completed');
        $('.step-number').html('<i class="fas fa-check"></i>');
        
        // Show success message
        showNotification('Processing registration... Please wait.', 'info');
    });

    // Auto-save functionality
    let autoSaveTimer;
    
    function autoSave() {
        const formData = {};
        $('#driverKYCForm').find('input, select, textarea').each(function() {
            const field = $(this);
            const name = field.attr('name');
            const value = field.val();
            
            if (name && name !== '_token' && name !== 'password' && name !== 'password_confirmation') {
                formData[name] = value;
            }
        });
        
        localStorage.setItem('driverKYCFormData', JSON.stringify({
            data: formData,
            timestamp: Date.now()
        }));
        
        console.log('Form data auto-saved');
    }
    
    // Auto-save every 30 seconds
    function startAutoSave() {
        autoSaveTimer = setInterval(autoSave, 30000);
    }
    
    // Load saved data on page load
    function loadSavedData() {
        const savedData = localStorage.getItem('driverKYCFormData');
        if (savedData) {
            try {
                const parsed = JSON.parse(savedData);
                const age = Date.now() - parsed.timestamp;
                
                // Only use saved data if it's less than 24 hours old
                if (age < 24 * 60 * 60 * 1000) {
                    const shouldLoad = confirm('Found previously saved form data. Would you like to restore it?');
                    if (shouldLoad) {
                        Object.keys(parsed.data).forEach(function(key) {
                            const field = $(`[name="${key}"]`);
                            if (field.length && parsed.data[key]) {
                                field.val(parsed.data[key]);
                                if (field.is('select')) {
                                    field.trigger('change');
                                }
                            }
                        });
                        showNotification('Previous form data restored successfully', 'success');
                    }
                }
            } catch (e) {
                console.error('Error loading saved data:', e);
            }
        }
    }
    
    // Clear saved data on successful submission
    $('#driverKYCForm').on('submit', function() {
        localStorage.removeItem('driverKYCFormData');
    });
    
    // Start auto-save and load saved data
    startAutoSave();
    setTimeout(loadSavedData, 1000); // Delay to allow form to fully load
    
    // Trigger auto-save on form changes
    $('#driverKYCForm').on('change', 'input, select, textarea', function() {
        clearTimeout(autoSaveTimer);
        setTimeout(autoSave, 2000); // Save 2 seconds after last change
        startAutoSave(); // Restart the regular timer
    });

    // Preview functionality
    $('#previewBtn').on('click', function() {
        // Collect form data and show preview modal (implement as needed)
        alert('Preview functionality can be implemented here');
    });

    // Real-time validation feedback
    $('input[required], select[required], textarea[required]').on('blur change', function() {
        const field = $(this);
        const value = field.val();
        
        if (!value || (Array.isArray(value) && value.length === 0)) {
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

    // Age calculation from date of birth
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

    // Drag and drop file upload
    $('.file-upload-area').on('dragover', function(e) {
        e.preventDefault();
        $(this).addClass('dragover');
    });

    $('.file-upload-area').on('dragleave', function(e) {
        e.preventDefault();
        $(this).removeClass('dragover');
    });

    $('.file-upload-area').on('drop', function(e) {
        e.preventDefault();
        $(this).removeClass('dragover');
        
        const files = e.originalEvent.dataTransfer.files;
        const fileInput = $(this).next('input[type="file"]');
        
        if (files.length > 0) {
            fileInput[0].files = files;
            fileInput.trigger('change');
        }
    });
});
</script>
@endsection