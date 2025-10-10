@extends('layouts.admin_cdn')

@section('title', 'Complete Driver Registration')

@section('content_header', 'Complete Driver Registration')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ url('admin/drivers') }}">Drivers</a></li>
    <li class="breadcrumb-item active">Complete Registration</li>
@endsection

@section('css')
<style>
/* Modern form sections with improved visual hierarchy */
.form-section {
    margin-bottom: 2rem;
    padding: 2rem;
    background: #ffffff;
    border: 1px solid #e3e6f0;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
}

.form-section:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,0.12);
    border-color: #d1ecf1;
}

.section-title {
    color: #2c3e50;
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    position: relative;
}

.section-title::after {
    content: '';
    flex: 1;
    height: 2px;
    background: linear-gradient(to right, #3498db, #e3e6f0);
    border-radius: 1px;
}

.section-title i {
    color: #3498db;
    font-size: 1.1rem;
}

/* Enhanced form controls */
.form-group {
    margin-bottom: 1.5rem;
    position: relative;
}

.form-label {
    font-weight: 500;
    color: #495057;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.required {
    color: #e74c3c;
    font-weight: 600;
}

.form-control, .form-select {
    border: 2px solid #e9ecef;
    border-radius: 8px;
    padding: 0.75rem 1rem;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    background-color: #fdfdfd;
}

.form-control:focus, .form-select:focus {
    border-color: #3498db;
    box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
    background-color: #ffffff;
    transform: translateY(-1px);
}

.form-control:hover, .form-select:hover {
    border-color: #ced4da;
}

.field-helper {
    font-size: 0.875rem;
    color: #6c757d;
    margin-top: 0.375rem;
    display: flex;
    align-items: center;
    gap: 0.375rem;
}

.field-helper::before {
    content: 'ℹ';
    color: #17a2b8;
    font-weight: 600;
}

/* Modern progress indicator */
.progress-indicator {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 2rem;
    border-radius: 16px;
    margin-bottom: 2rem;
    border: 1px solid #dee2e6;
    box-shadow: inset 0 1px 3px rgba(0,0,0,0.06);
}

.step-indicator {
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
    margin-bottom: 1rem;
}

.step-indicator::before {
    content: '';
    position: absolute;
    top: 20px;
    left: 5%;
    right: 5%;
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
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #6c757d;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    margin-bottom: 0.5rem;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.step.active .step-number {
    background: linear-gradient(135deg, #3498db, #2980b9);
    transform: scale(1.1);
    box-shadow: 0 4px 12px rgba(52, 152, 219, 0.4);
}

.step.completed .step-number {
    background: linear-gradient(135deg, #27ae60, #229954);
    box-shadow: 0 2px 8px rgba(39, 174, 96, 0.3);
}

.step-label {
    font-size: 0.875rem;
    text-align: center;
    color: #6c757d;
    font-weight: 500;
    line-height: 1.3;
    max-width: 120px;
}

.step.active .step-label {
    color: #3498db;
    font-weight: 600;
}

.step.completed .step-label {
    color: #27ae60;
    font-weight: 600;
}

/* Enhanced validation feedback */
.invalid-feedback {
    display: block;
    font-size: 0.875rem;
    color: #e74c3c;
    margin-top: 0.375rem;
    padding: 0.5rem 0.75rem;
    background: #fdf2f2;
    border-left: 3px solid #e74c3c;
    border-radius: 0 4px 4px 0;
}

.is-invalid {
    border-color: #e74c3c !important;
    background-color: #fef5f5 !important;
}

/* Modern alert styles */
.alert {
    border: none;
    border-radius: 12px;
    padding: 1.25rem 1.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.alert-danger {
    background: linear-gradient(135deg, #fdf2f2, #fef5f5);
    color: #c0392b;
    border-left: 4px solid #e74c3c;
}

.alert-info {
    background: linear-gradient(135deg, #e8f4fd, #f0f8ff);
    color: #2980b9;
    border-left: 4px solid #3498db;
}

.alert-warning {
    background: linear-gradient(135deg, #fef9e7, #fffbf0);
    color: #d68910;
    border-left: 4px solid #f39c12;
}

/* Enhanced buttons */
.btn {
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 500;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.btn-primary {
    background: linear-gradient(135deg, #3498db, #2980b9);
    border-color: #3498db;
    box-shadow: 0 2px 4px rgba(52, 152, 219, 0.3);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(52, 152, 219, 0.4);
}

.btn-secondary {
    background: linear-gradient(135deg, #95a5a6, #7f8c8d);
    border-color: #95a5a6;
}

/* Responsive enhancements */
@media (max-width: 768px) {
    .form-section {
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .step-indicator {
        flex-wrap: wrap;
        gap: 1rem;
    }

    .step {
        min-width: 80px;
    }

    .section-title {
        font-size: 1.1rem;
    }

    .form-control, .form-select {
        padding: 0.875rem;
    }
}
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        @if ($errors->any())
            <div class="alert alert-danger">
                <h5><i class="fas fa-exclamation-triangle"></i> Validation Errors:</h5>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Modern Step Indicator -->
        <div class="progress-indicator">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h5 class="mb-0 text-primary"><i class="fas fa-route"></i> Registration Progress</h5>
                <span class="badge bg-primary">Step 2 of 4</span>
            </div>
            <div class="step-indicator">
                <div class="step completed">
                    <div class="step-number"><i class="fas fa-check"></i></div>
                    <div class="step-label">Basic Information</div>
                </div>
                <div class="step active">
                    <div class="step-number">2</div>
                    <div class="step-label">Additional Details</div>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <div class="step-label">Document Upload</div>
                </div>
                <div class="step">
                    <div class="step-number">4</div>
                    <div class="step-label">Verification & Review</div>
                </div>
            </div>
            <div class="progress mt-3">
                <div class="progress-bar bg-gradient-primary" role="progressbar" style="width: 50%" aria-valuenow="50" aria-valuemin="0" aria-valuemax="100">
                    <span class="sr-only">50% Complete</span>
                </div>
            </div>
        </div>

        <!-- Enhanced Driver Basic Info Display -->
        <div class="card mb-4 border-0 shadow-sm">
            <div class="card-header bg-gradient-success text-white border-0">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="mb-0"><i class="fas fa-user-check"></i> Driver Basic Information</h6>
                    <span class="badge bg-light text-success"><i class="fas fa-check-circle"></i> Verified</span>
                </div>
            </div>
            <div class="card-body bg-light">
                <div class="row g-3">
                    <div class="col-lg-3 col-md-6">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-user text-primary"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <strong class="text-muted small">Full Name:</strong><br>
                                <span class="text-dark fw-medium" id="driverName">{{ $driver->first_name ?? 'John' }} {{ $driver->surname ?? 'Doe' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-envelope text-info"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <strong class="text-muted small">Email:</strong><br>
                                <span class="text-dark" id="driverEmail">{{ $driver->email ?? 'john.doe@example.com' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-phone text-success"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <strong class="text-muted small">Phone:</strong><br>
                                <span class="text-dark" id="driverPhone">{{ $driver->phone ?? '+234 xxx xxxx xxx' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-id-card text-warning"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <strong class="text-muted small">License No:</strong><br>
                                <span class="text-dark fw-medium" id="driverLicense">{{ $driver->driver_license_number ?? 'ABC123456' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <hr class="my-3">
                <div class="row">
                    <div class="col-md-6">
                        <small class="text-muted"><i class="fas fa-calendar"></i> Date of Birth: <span class="fw-medium" id="driverDOB">{{ $driver->date_of_birth ?? '1990-01-01' }}</span></small>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <small class="text-muted"><i class="fas fa-clock"></i> Profile Created: <span class="fw-medium">{{ $driver->created_at ?? now() }}</span></small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Progress Info -->
        <div class="alert alert-info">
            <h6><i class="fas fa-info-circle"></i> Completing Registration</h6>
            <p class="mb-0">Now collecting additional required details to complete the driver's profile and enable KYC verification.</p>
        </div>

        <form action="{{ route('admin.drivers.store') }}" method="POST" enctype="multipart/form-data" id="driverCreateForm">
            @csrf
            
            <!-- Hidden driver ID if updating existing driver -->
            @if(isset($driver))
                <input type="hidden" name="driver_id" value="{{ $driver->id }}">
                <input type="hidden" name="_method" value="PATCH">
            @endif
            
            <!-- Additional Personal Details Section -->
            <div class="form-section">
                <h4 class="section-title"><i class="fas fa-user-plus"></i> Additional Personal Details</h4>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="other_names" class="form-label">Other Names/Middle Name</label>
                            <input type="text" class="form-control @error('other_names') is-invalid @enderror" 
                                   id="other_names" name="other_names" 
                                   value="{{ old('other_names') }}"
                                   aria-describedby="other_names-help @error('other_names') other_names-error @enderror">
                            <div id="other_names-help" class="form-text field-helper">Additional names (optional)</div>
                            @error('other_names')
                                <div id="other_names-error" class="invalid-feedback" role="alert">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="gender" class="form-label">Gender <span class="text-danger" aria-label="required">*</span></label>
                            <select class="form-control @error('gender') is-invalid @enderror" 
                                    id="gender" name="gender" required
                                    aria-describedby="@error('gender') gender-error @enderror"
                                    aria-required="true">
                                <option value="">Select Gender</option>
                                <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                            </select>
                            @error('gender')
                                <div id="gender-error" class="invalid-feedback" role="alert">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="marital_status">Marital Status</label>
                            <select class="form-control @error('marital_status') is-invalid @enderror" id="marital_status" name="marital_status">
                                <option value="">Select Status</option>
                                <option value="single" {{ old('marital_status') == 'single' ? 'selected' : '' }}>Single</option>
                                <option value="married" {{ old('marital_status') == 'married' ? 'selected' : '' }}>Married</option>
                                <option value="divorced" {{ old('marital_status') == 'divorced' ? 'selected' : '' }}>Divorced</option>
                                <option value="widowed" {{ old('marital_status') == 'widowed' ? 'selected' : '' }}>Widowed</option>
                            </select>
                            @error('marital_status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="nationality_id">Nationality</label>
                            <select class="form-control @error('nationality_id') is-invalid @enderror" id="nationality_id" name="nationality_id">
                                <option value="">Select Nationality</option>
                                <option value="1" {{ old('nationality_id') == '1' ? 'selected' : '' }}>Nigerian</option>
                                <option value="2" {{ old('nationality_id') == '2' ? 'selected' : '' }}>Ghanaian</option>
                                <option value="3" {{ old('nationality_id') == '3' ? 'selected' : '' }}>Cameroonian</option>
                                <option value="4" {{ old('nationality_id') == '4' ? 'selected' : '' }}>South African</option>
                                <option value="5" {{ old('nationality_id') == '5' ? 'selected' : '' }}>Kenyan</option>
                                <option value="6" {{ old('nationality_id') == '6' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('nationality_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="religion">Religion</label>
                            <select class="form-control @error('religion') is-invalid @enderror" id="religion" name="religion">
                                <option value="">Select Religion</option>
                                <option value="christianity" {{ old('religion') == 'christianity' ? 'selected' : '' }}>Christianity</option>
                                <option value="islam" {{ old('religion') == 'islam' ? 'selected' : '' }}>Islam</option>
                                <option value="traditional" {{ old('religion') == 'traditional' ? 'selected' : '' }}>Traditional</option>
                                <option value="other" {{ old('religion') == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('religion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Location & Contact Section -->
            <div class="form-section">
                <h4 class="section-title"><i class="fas fa-map-marker-alt"></i> Location & Additional Contact</h4>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="state_id" class="form-label">State of Origin <span class="text-danger" aria-label="required">*</span></label>
                            <select class="form-control @error('state_id') is-invalid @enderror" 
                                    id="state_id" name="state_id" required
                                    aria-describedby="@error('state_id') state_id-error @enderror"
                                    aria-required="true">
                                <option value="">Loading states...</option>
                            </select>
                            @error('state_id')
                                <div id="state_id-error" class="invalid-feedback" role="alert">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="lga_id" class="form-label">Local Government Area <span class="text-danger" aria-label="required">*</span></label>
                            <select class="form-control @error('lga_id') is-invalid @enderror" 
                                    id="lga_id" name="lga_id" required
                                    aria-describedby="@error('lga_id') lga_id-error @enderror"
                                    aria-required="true">
                                <option value="">Select LGA</option>
                                <!-- LGAs will be populated based on state selection -->
                            </select>
                            @error('lga_id')
                                <div id="lga_id-error" class="invalid-feedback" role="alert">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="address" class="form-label">Residential Address <span class="text-danger" aria-label="required">*</span></label>
                    <textarea class="form-control @error('address') is-invalid @enderror" 
                              id="address" name="address" rows="3" required 
                              placeholder="Enter full residential address"
                              aria-describedby="@error('address') address-error @enderror"
                              aria-required="true">{{ old('address') }}</textarea>
                    @error('address')
                        <div id="address-error" class="invalid-feedback" role="alert">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6">
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
                    </div>
                    <div class="col-md-6">
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
                </div>

                <div class="form-group">
                    <label for="emergency_contact_relationship">Relationship to Emergency Contact</label>
                    <select class="form-control @error('emergency_contact_relationship') is-invalid @enderror" 
                            id="emergency_contact_relationship" name="emergency_contact_relationship">
                        <option value="">Select Relationship</option>
                        <option value="parent" {{ old('emergency_contact_relationship') == 'parent' ? 'selected' : '' }}>Parent</option>
                        <option value="spouse" {{ old('emergency_contact_relationship') == 'spouse' ? 'selected' : '' }}>Spouse</option>
                        <option value="sibling" {{ old('emergency_contact_relationship') == 'sibling' ? 'selected' : '' }}>Sibling</option>
                        <option value="child" {{ old('emergency_contact_relationship') == 'child' ? 'selected' : '' }}>Child</option>
                        <option value="friend" {{ old('emergency_contact_relationship') == 'friend' ? 'selected' : '' }}>Friend</option>
                        <option value="relative" {{ old('emergency_contact_relationship') == 'relative' ? 'selected' : '' }}>Relative</option>
                        <option value="other" {{ old('emergency_contact_relationship') == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('emergency_contact_relationship')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Additional Documents & KYC Section -->
            <div class="form-section">
                <h4 class="section-title"><i class="fas fa-id-card"></i> Additional Documents & KYC Information</h4>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="license_expiry_date">Driver License Expiry Date</label>
                            <input type="date" class="form-control @error('license_expiry_date') is-invalid @enderror" 
                                   id="license_expiry_date" name="license_expiry_date" 
                                   value="{{ old('license_expiry_date') }}">
                            <small class="field-helper">When does the driver's license expire?</small>
                            @error('license_expiry_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="license_class">License Class</label>
                            <select class="form-control @error('license_class') is-invalid @enderror" id="license_class" name="license_class">
                                <option value="">Select License Class</option>
                                <option value="class_a" {{ old('license_class') == 'class_a' ? 'selected' : '' }}>Class A</option>
                                <option value="class_b" {{ old('license_class') == 'class_b' ? 'selected' : '' }}>Class B</option>
                                <option value="class_c" {{ old('license_class') == 'class_c' ? 'selected' : '' }}>Class C</option>
                                <option value="class_d" {{ old('license_class') == 'class_d' ? 'selected' : '' }}>Class D</option>
                            </select>
                            @error('license_class')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="bvn">BVN (Bank Verification Number)</label>
                            <input type="text" class="form-control @error('bvn') is-invalid @enderror" 
                                   id="bvn" name="bvn" 
                                   value="{{ old('bvn') }}" maxlength="11"
                                   placeholder="Enter 11-digit BVN">
                            <small class="field-helper">11-digit Bank Verification Number</small>
                            @error('bvn')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nin">NIN (National ID Number)</label>
                            <input type="text" class="form-control @error('nin') is-invalid @enderror" 
                                   id="nin" name="nin" 
                                   value="{{ old('nin') }}" maxlength="11"
                                   placeholder="Enter 11-digit NIN">
                            <small class="field-helper">11-digit National Identification Number</small>
                            @error('nin')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="guarantor_name">Guarantor Full Name</label>
                            <input type="text" class="form-control @error('guarantor_name') is-invalid @enderror" 
                                   id="guarantor_name" name="guarantor_name" 
                                   value="{{ old('guarantor_name') }}"
                                   placeholder="Full name of guarantor">
                            @error('guarantor_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="guarantor_phone">Guarantor Phone Number</label>
                            <input type="tel" class="form-control @error('guarantor_phone') is-invalid @enderror" 
                                   id="guarantor_phone" name="guarantor_phone" 
                                   value="{{ old('guarantor_phone') }}"
                                   placeholder="+234 xxx xxxx xxx">
                            @error('guarantor_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="guarantor_address">Guarantor Address</label>
                    <textarea class="form-control @error('guarantor_address') is-invalid @enderror" 
                              id="guarantor_address" name="guarantor_address" rows="2"
                              placeholder="Guarantor's full address">{{ old('guarantor_address') }}</textarea>
                    @error('guarantor_address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Driving & Employment History Section -->
            <div class="form-section">
                <h4 class="section-title"><i class="fas fa-road"></i> Driving & Employment Experience</h4>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="years_of_driving_experience">Years of Driving Experience</label>
                            <input type="number" class="form-control @error('years_of_driving_experience') is-invalid @enderror" 
                                   id="years_of_driving_experience" name="years_of_driving_experience" 
                                   value="{{ old('years_of_driving_experience') }}" min="0" max="50"
                                   placeholder="Number of years">
                            @error('years_of_driving_experience')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="previous_employment">Previous Employment/Company</label>
                            <input type="text" class="form-control @error('previous_employment') is-invalid @enderror" 
                                   id="previous_employment" name="previous_employment" 
                                   value="{{ old('previous_employment') }}"
                                   placeholder="Previous employer or company">
                            @error('previous_employment')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="driving_violations">Traffic Violations History</label>
                    <textarea class="form-control @error('driving_violations') is-invalid @enderror" 
                              id="driving_violations" name="driving_violations" rows="2"
                              placeholder="List any traffic violations or accidents (if none, enter 'None')">{{ old('driving_violations', 'None') }}</textarea>
                    <small class="field-helper">Include any traffic violations, accidents, or DUI history</small>
                    @error('driving_violations')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Account Status Section -->
            <div class="form-section">
                <h4 class="section-title"><i class="fas fa-cog"></i> Account Status & Settings</h4>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="status">Account Status</label>
                            <select class="form-control @error('status') is-invalid @enderror" id="status" name="status">
                                <option value="active">Active</option>
                                <option value="inactive" selected>Inactive (Pending Verification)</option>
                                <option value="suspended">Suspended</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="verification_status">Verification Status</label>
                            <select class="form-control @error('verification_status') is-invalid @enderror" id="verification_status" name="verification_status">
                                <option value="pending" selected>Pending Verification</option>
                                <option value="in_progress">In Progress</option>
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
                            <select class="form-control @error('kyc_status') is-invalid @enderror" id="kyc_status" name="kyc_status">
                                <option value="pending" selected>Pending KYC</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                                <option value="failed">Failed</option>
                            </select>
                            @error('kyc_status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Hidden fields -->
                <input type="hidden" name="created_by" value="{{ auth('admin')->id() }}">
                
                <!-- Note about basic info -->
                <div class="alert alert-warning mt-3">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Note:</strong> Basic information (First Name, Surname, Email, DOB, Phone, Driver License Number) should already exist for this driver. This form completes their profile with additional details.
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-section">
                <div class="row">
                    <div class="col-md-6">
                        <a href="{{ route('admin.drivers.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Cancel
                        </a>
                    </div>
                    <div class="col-md-6 text-right">
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="fas fa-user-check"></i> Complete Driver Registration
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('js')
<script src="{{ asset('js/location-dropdown.js') }}"></script>
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
        submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Completing Registration...');
    });
    
    // Set current values for location manager (if editing)
    const selectedState = '{{ old("state_id") }}';
    const selectedLGA = '{{ old("lga_id") }}';
    
    if (selectedState) {
        $('#state_id').data('current-value', selectedState);
    }
    if (selectedLGA) {
        $('#lga_id').data('current-value', selectedLGA);
    }
    
    // Wait for location manager to initialize, then populate
    setTimeout(() => {
        if (window.locationManager && window.locationManager.initialized) {
            console.log('Location manager initialized - dropdowns should be populated');
        }
    }, 1000);
    
    // Real-time validation for required fields
    $('input[required], select[required]').on('blur change', function() {
        const field = $(this);
        const value = field.val();
        
        if (!value || (Array.isArray(value) && value.length === 0)) {
            field.addClass('is-invalid');
            if (!field.siblings('.invalid-feedback').length) {
                const fieldName = field.attr('name').replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
                field.after(`<div class="invalid-feedback">${fieldName} is required</div>`);
            }
        } else {
            field.removeClass('is-invalid');
            field.siblings('.invalid-feedback').remove();
        }
    });
    
    // BVN validation
    $('#bvn').on('input', function() {
        const value = $(this).val().replace(/\D/g, '');
        $(this).val(value.substring(0, 11));
    });
    
    // NIN validation
    $('#nin').on('input', function() {
        const value = $(this).val().replace(/\D/g, '');
        $(this).val(value.substring(0, 11));
    });
    
    // Phone number formatting
    $('#phone').on('input', function() {
        let value = $(this).val().replace(/\D/g, '');
        if (value.length > 0 && !value.startsWith('234')) {
            if (value.startsWith('0')) {
                value = '234' + value.substring(1);
            } else if (!value.startsWith('234')) {
                value = '234' + value;
            }
        }
        $(this).val(value);
    });
});
</script>
@endsection