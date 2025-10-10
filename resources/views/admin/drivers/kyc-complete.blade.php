@extends('layouts.admin_cdn')

@section('title', 'Complete KYC Verification - ' . $driver->full_name)

@section('content_header', 'Complete KYC Verification')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ url('admin/drivers') }}">Drivers</a></li>
    <li class="breadcrumb-item"><a href="{{ url('admin/drivers/' . $driver->id) }}">{{ $driver->full_name }}</a></li>
    <li class="breadcrumb-item active">KYC Verification</li>
@endsection

@section('css')
<style>
.kyc-form-container {
    max-width: 800px;
    margin: 0 auto;
}

.driver-info-card {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    color: white;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 25px;
}

.kyc-section {
    background-color: #f8f9fa;
    border-left: 4px solid #007bff;
    padding: 25px;
    margin-bottom: 25px;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.kyc-section h5 {
    color: #007bff;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    font-weight: 600;
}

.kyc-section h5 i {
    margin-right: 10px;
    font-size: 1.1rem;
}

.required {
    color: #dc3545;
}

.form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.field-helper {
    font-size: 0.85rem;
    color: #6c757d;
    margin-top: 0.25rem;
}

.document-upload-area {
    border: 2px dashed #007bff;
    border-radius: 10px;
    padding: 30px;
    text-align: center;
    transition: all 0.3s ease;
    background: linear-gradient(45deg, rgba(0, 123, 255, 0.05), rgba(0, 86, 179, 0.05));
}

.document-upload-area:hover {
    border-color: #0056b3;
    background: linear-gradient(45deg, rgba(0, 123, 255, 0.1), rgba(0, 86, 179, 0.1));
}

.file-preview {
    margin-top: 15px;
    padding: 15px;
    background: white;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.file-preview img {
    max-width: 120px;
    max-height: 120px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}
</style>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
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
        
        <!-- Driver Info Card -->
        <div class="driver-info-card">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h5><i class="fas fa-user"></i> Complete KYC for {{ $driver->full_name }}</h5>
                    <p class="mb-0">Driver ID: {{ $driver->driver_id }} | Account created on {{ $driver->created_at->format('M d, Y') }}</p>
                </div>
                <div class="col-md-4 text-right">
                    <div class="d-flex align-items-center justify-content-end">
                        <i class="fas fa-clipboard-check fa-2x mr-2"></i>
                        <div>
                            <small>Step 2</small><br>
                            <small>KYC Verification</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card kyc-form-container">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-shield-alt"></i> KYC Verification Details
                </h3>
                <div class="card-tools">
                    <small class="text-muted">
                        <span class="required">*</span> Required fields
                    </small>
                </div>
            </div>
            
            <form action="{{ route('admin.drivers.kyc-complete', $driver->id) }}" method="POST" enctype="multipart/form-data" id="kycForm">
                @csrf
                @method('PUT')
                
                <div class="card-body">
                    
                    <!-- Personal Information Section -->
                    <div class="kyc-section">
                        <h5><i class="fas fa-user-circle"></i> Personal Information</h5>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="date_of_birth">Date of Birth <span class="required">*</span></label>
                                    <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" 
                                           id="date_of_birth" name="date_of_birth" 
                                           value="{{ old('date_of_birth', $driver->date_of_birth) }}" 
                                           required>
                                    <small class="field-helper">Must be at least 18 years old</small>
                                    @error('date_of_birth')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="gender">Gender <span class="required">*</span></label>
                                    <select class="form-control @error('gender') is-invalid @enderror" id="gender" name="gender" required>
                                        <option value="">Select Gender</option>
                                        <option value="male" {{ old('gender', $driver->gender) === 'male' ? 'selected' : '' }}>Male</option>
                                        <option value="female" {{ old('gender', $driver->gender) === 'female' ? 'selected' : '' }}>Female</option>
                                        <option value="other" {{ old('gender', $driver->gender) === 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('gender')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="full_address">Full Address <span class="required">*</span></label>
                            <textarea class="form-control @error('full_address') is-invalid @enderror" 
                                      id="full_address" name="full_address" rows="3" 
                                      required placeholder="Enter complete residential address">{{ old('full_address', $driver->full_address) }}</textarea>
                            <small class="field-helper">Include street, area, city, and postal code</small>
                            @error('full_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="city">City <span class="required">*</span></label>
                                    <input type="text" class="form-control @error('city') is-invalid @enderror" 
                                           id="city" name="city" 
                                           value="{{ old('city', $driver->city) }}" 
                                           required>
                                    @error('city')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="state">State</label>
                                    <input type="text" class="form-control @error('state') is-invalid @enderror" 
                                           id="state" name="state" 
                                           value="{{ old('state', $driver->state) }}" 
                                           placeholder="e.g., Lagos, Abuja">
                                    @error('state')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- License Information Section -->
                    <div class="kyc-section">
                        <h5><i class="fas fa-id-card"></i> License Information</h5>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="license_issue_date">License Issue Date <span class="required">*</span></label>
                                    <input type="date" class="form-control @error('license_issue_date') is-invalid @enderror" 
                                           id="license_issue_date" name="license_issue_date" 
                                           value="{{ old('license_issue_date', $driver->license_issue_date) }}" 
                                           required>
                                    @error('license_issue_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="license_expiry_date">License Expiry Date <span class="required">*</span></label>
                                    <input type="date" class="form-control @error('license_expiry_date') is-invalid @enderror" 
                                           id="license_expiry_date" name="license_expiry_date" 
                                           value="{{ old('license_expiry_date', $driver->license_expiry_date) }}" 
                                           required>
                                    <small class="field-helper">License must be valid (not expired)</small>
                                    @error('license_expiry_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Document Upload Section -->
                    <div class="kyc-section">
                        <h5><i class="fas fa-file-upload"></i> Document Upload</h5>
                        <p class="text-muted mb-4">Upload clear, high-quality images of required documents</p>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="driver_license_scan">Driver License Scan <span class="required">*</span></label>
                                    <div class="document-upload-area" id="license-upload-area">
                                        <i class="fas fa-id-card fa-3x text-primary mb-3"></i>
                                        <h6>Upload Driver License</h6>
                                        <p class="text-muted">Drag & drop or click to select</p>
                                        <input type="file" class="form-control @error('driver_license_scan') is-invalid @enderror" 
                                               id="driver_license_scan" name="driver_license_scan" 
                                               accept="image/*,.pdf" required
                                               onchange="handleDocumentUpload(this, 'license-upload-area')"
                                               style="position: absolute; opacity: 0; width: 100%; height: 100%; cursor: pointer;">
                                    </div>
                                    <small class="field-helper">Upload clear image of driver's license. Max 5MB.</small>
                                    <div class="file-preview" id="license-preview" style="display: none;"></div>
                                    @error('driver_license_scan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="national_id">National ID/NIN</label>
                                    <div class="document-upload-area" id="national-id-upload-area">
                                        <i class="fas fa-address-card fa-3x text-success mb-3"></i>
                                        <h6>Upload National ID</h6>
                                        <p class="text-muted">Drag & drop or click to select</p>
                                        <input type="file" class="form-control @error('national_id') is-invalid @enderror" 
                                               id="national_id" name="national_id" 
                                               accept="image/*,.pdf"
                                               onchange="handleDocumentUpload(this, 'national-id-upload-area')"
                                               style="position: absolute; opacity: 0; width: 100%; height: 100%; cursor: pointer;">
                                    </div>
                                    <small class="field-helper">Upload clear image of National ID or NIN slip. Max 5MB.</small>
                                    <div class="file-preview" id="national-id-preview" style="display: none;"></div>
                                    @error('national_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="passport_photo">Passport Photograph</label>
                                    <div class="document-upload-area" id="passport-upload-area">
                                        <i class="fas fa-portrait fa-3x text-warning mb-3"></i>
                                        <h6>Upload Passport Photo</h6>
                                        <p class="text-muted">Drag & drop or click to select</p>
                                        <input type="file" class="form-control @error('passport_photo') is-invalid @enderror" 
                                               id="passport_photo" name="passport_photo" 
                                               accept="image/*"
                                               onchange="handleDocumentUpload(this, 'passport-upload-area')"
                                               style="position: absolute; opacity: 0; width: 100%; height: 100%; cursor: pointer;">
                                    </div>
                                    <small class="field-helper">Upload recent passport-style photograph. Max 2MB.</small>
                                    <div class="file-preview" id="passport-preview" style="display: none;"></div>
                                    @error('passport_photo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="bg-light p-3 rounded">
                                    <h6><i class="fas fa-info-circle text-primary"></i> Document Requirements</h6>
                                    <ul class="small text-muted mb-0">
                                        <li>Documents must be clear and readable</li>
                                        <li>Images should be well-lit with no glare</li>
                                        <li>All corners of documents must be visible</li>
                                        <li>File formats: JPG, PNG, PDF</li>
                                        <li>Maximum file size: 5MB per document</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Emergency Contact Section -->
                    <div class="kyc-section">
                        <h5><i class="fas fa-phone"></i> Emergency Contact</h5>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="emergency_contact_name">Emergency Contact Name</label>
                                    <input type="text" class="form-control @error('emergency_contact_name') is-invalid @enderror" 
                                           id="emergency_contact_name" name="emergency_contact_name" 
                                           value="{{ old('emergency_contact_name', $driver->emergency_contact_name) }}" 
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
                                           value="{{ old('emergency_contact_phone', $driver->emergency_contact_phone) }}" 
                                           placeholder="+234 xxx xxxx xxx">
                                    @error('emergency_contact_phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <a href="{{ route('admin.drivers.show', $driver->id) }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Driver Details
                            </a>
                        </div>
                        <div class="col-md-6 text-right">
                            <button type="submit" class="btn btn-success" id="submitBtn">
                                <i class="fas fa-shield-alt"></i> Complete KYC Verification
                            </button>
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
function handleDocumentUpload(input, uploadAreaId) {
    const file = input.files[0];
    const uploadArea = document.getElementById(uploadAreaId);
    const previewId = uploadAreaId.replace('-upload-area', '-preview');
    const preview = document.getElementById(previewId);
    
    if (!file) return;
    
    // Validate file
    const maxSize = 5 * 1024 * 1024; // 5MB
    const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'application/pdf'];
    
    if (file.size > maxSize) {
        alert('File size must be less than 5MB');
        input.value = '';
        return;
    }
    
    if (!allowedTypes.includes(file.type)) {
        alert('Only images (JPG, PNG, GIF) and PDF files are allowed');
        input.value = '';
        return;
    }
    
    // Update upload area
    uploadArea.classList.add('border-success');
    uploadArea.querySelector('h6').textContent = 'Document Uploaded!';
    uploadArea.querySelector('p').textContent = file.name;
    uploadArea.querySelector('i').className = 'fas fa-check-circle fa-3x text-success mb-3';
    
    // Show preview for images
    if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `
                <div class="d-flex align-items-center">
                    <img src="${e.target.result}" alt="Preview" class="img-thumbnail mr-3" style="max-width: 120px; max-height: 120px;">
                    <div>
                        <h6 class="text-success mb-1">
                            <i class="fas fa-check-circle"></i> ${file.name}
                        </h6>
                        <p class="mb-0 small text-muted">${(file.size / 1024 / 1024).toFixed(2)} MB</p>
                    </div>
                </div>
            `;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    } else {
        // PDF file
        preview.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas fa-file-pdf fa-4x text-danger mr-3"></i>
                <div>
                    <h6 class="text-success mb-1">
                        <i class="fas fa-check-circle"></i> ${file.name}
                    </h6>
                    <p class="mb-0 small text-muted">${(file.size / 1024 / 1024).toFixed(2)} MB</p>
                </div>
            </div>
        `;
        preview.style.display = 'block';
    }
}

$(document).ready(function() {
    // Age validation for date of birth
    $('#date_of_birth').on('change', function() {
        const birthDate = new Date($(this).val());
        const today = new Date();
        const age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();
        
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        
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
    
    // License expiry validation
    $('#license_expiry_date').on('change', function() {
        const expiryDate = new Date($(this).val());
        const today = new Date();
        
        if (expiryDate <= today) {
            $(this).addClass('is-invalid');
            if (!$(this).siblings('.invalid-feedback').length) {
                $(this).after('<div class="invalid-feedback">License must be valid (not expired)</div>');
            }
        } else {
            $(this).removeClass('is-invalid');
            $(this).siblings('.invalid-feedback').remove();
        }
    });
    
    // Form submission handling
    $('#kycForm').on('submit', function(e) {
        // Show loading state
        const submitBtn = $('#submitBtn');
        submitBtn.prop('disabled', true);
        submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Processing KYC...');
    });
});
</script>
@endsection