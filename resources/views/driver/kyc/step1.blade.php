@extends('layouts.driver-kyc')

@section('title', 'KYC Step 1 - Personal Information')
@section('page-title', 'Personal Information')
@section('page-description', 'Enter your personal details and emergency contact information')

@php
    $currentStep = 1;
@endphp

@section('content')
<!-- Progress Indicator -->
<div class="step-progress mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div class="step-item active">
            <div class="step-number">1</div>
            <div class="step-title">Personal Info</div>
        </div>
        <div class="progress-line"></div>
        <div class="step-item">
            <div class="step-number">2</div>
            <div class="step-title">Contact & Address</div>
        </div>
        <div class="progress-line"></div>
        <div class="step-item">
            <div class="step-number">3</div>
            <div class="step-title">Documents</div>
        </div>
    </div>
</div>

<!-- Step Information -->
<div class="step-info mb-4">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h5 class="mb-2">
                <i class="fas fa-user-edit me-2" style="color: var(--drivelink-primary);"></i>
                Personal Information
            </h5>
            <p class="mb-0 text-muted">
                Please provide accurate personal information. This information will be verified against your official documents.
            </p>
        </div>
        <div class="col-md-4 text-end">
            <span class="badge bg-primary px-3 py-2">
                <i class="fas fa-clock me-1"></i>
                Step 1 of 3
            </span>
        </div>
    </div>
</div>

<!-- KYC Form -->
<form method="POST" action="{{ route('driver.kyc.step1.submit') }}" class="needs-validation" novalidate>
    @csrf
    
    <div class="step-card">
        <div class="step-card-header">
            <h6 class="mb-0">
                <i class="fas fa-id-card me-2"></i>
                Basic Personal Details
            </h6>
        </div>
        <div class="step-card-body">
            <div class="row">
                <!-- First Name -->
                <div class="col-md-4 mb-3">
                    <label for="first_name" class="form-label required">First Name</label>
                    <input type="text" 
                           class="form-control @error('first_name') is-invalid @enderror" 
                           id="first_name" 
                           name="first_name" 
                           value="{{ old('first_name', $driver->first_name) }}"
                           placeholder="Enter your first name"
                           required>
                    @error('first_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Middle Name -->
                <div class="col-md-4 mb-3">
                    <label for="middle_name" class="form-label">Middle Name</label>
                    <input type="text" 
                           class="form-control @error('middle_name') is-invalid @enderror" 
                           id="middle_name" 
                           name="middle_name" 
                           value="{{ old('middle_name', $driver->middle_name) }}"
                           placeholder="Enter your middle name (optional)">
                    @error('middle_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Last Name -->
                <div class="col-md-4 mb-3">
                    <label for="surname" class="form-label required">Last Name</label>
                    <input type="text" 
                           class="form-control @error('surname') is-invalid @enderror" 
                           id="surname" 
                           name="surname" 
                           value="{{ old('surname', $driver->surname) }}"
                           placeholder="Enter your last name"
                           required>
                    @error('surname')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Date of Birth -->
                <div class="col-md-4 mb-3">
                    <label for="date_of_birth" class="form-label required">Date of Birth</label>
                    <input type="date" 
                           class="form-control @error('date_of_birth') is-invalid @enderror" 
                           id="date_of_birth" 
                           name="date_of_birth" 
                           value="{{ old('date_of_birth', $driver->date_of_birth ? $driver->date_of_birth->format('Y-m-d') : '') }}"
                           max="{{ date('Y-m-d', strtotime('-18 years')) }}"
                           required>
                    @error('date_of_birth')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">You must be at least 18 years old</small>
                </div>

                <!-- Gender -->
                <div class="col-md-4 mb-3">
                    <label for="gender" class="form-label required">Gender</label>
                    <select class="form-select @error('gender') is-invalid @enderror" 
                            id="gender" 
                            name="gender" 
                            required>
                        <option value="">Select Gender</option>
                        <option value="male" {{ old('gender', $driver->gender) == 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('gender', $driver->gender) == 'female' ? 'selected' : '' }}>Female</option>
                        <option value="other" {{ old('gender', $driver->gender) == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('gender')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Marital Status -->
                <div class="col-md-4 mb-3">
                    <label for="marital_status" class="form-label required">Marital Status</label>
                    <select class="form-select @error('marital_status') is-invalid @enderror" 
                            id="marital_status" 
                            name="marital_status" 
                            required>
                        <option value="">Select Marital Status</option>
                        <option value="single" {{ old('marital_status', $driver->marital_status) == 'single' ? 'selected' : '' }}>Single</option>
                        <option value="married" {{ old('marital_status', $driver->marital_status) == 'married' ? 'selected' : '' }}>Married</option>
                        <option value="divorced" {{ old('marital_status', $driver->marital_status) == 'divorced' ? 'selected' : '' }}>Divorced</option>
                        <option value="widowed" {{ old('marital_status', $driver->marital_status) == 'widowed' ? 'selected' : '' }}>Widowed</option>
                    </select>
                    @error('marital_status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Nationality -->
                <div class="col-md-6 mb-3">
                    <label for="nationality_id" class="form-label required">Nationality</label>
                    <select class="form-select @error('nationality_id') is-invalid @enderror" 
                            id="nationality_id" 
                            name="nationality_id" 
                            required>
                        <option value="">Select Nationality</option>
                        @foreach($nationalities as $nationality)
                            <option value="{{ $nationality->id }}" 
                                {{ old('nationality_id', $driver->nationality_id) == $nationality->id ? 'selected' : '' }}>
                                {{ $nationality->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('nationality_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Religion -->
                <div class="col-md-6 mb-3">
                    <label for="religion" class="form-label">Religion</label>
                    <input type="text" 
                           class="form-control @error('religion') is-invalid @enderror" 
                           id="religion" 
                           name="religion" 
                           value="{{ old('religion', $driver->religion) }}"
                           placeholder="Enter your religion (optional)">
                    @error('religion')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- State of Origin -->
                <div class="col-md-6 mb-3">
                    <label for="state_of_origin" class="form-label required">State of Origin</label>
                    <select class="form-select @error('state_of_origin') is-invalid @enderror" 
                            id="state_of_origin" 
                            name="state_of_origin" 
                            required>
                        <option value="">Select State of Origin</option>
                        @foreach($states as $state)
                            <option value="{{ $state->id }}" 
                                {{ old('state_of_origin', $driver->state_of_origin) == $state->id ? 'selected' : '' }}>
                                {{ $state->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('state_of_origin')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- LGA of Origin -->
                <div class="col-md-6 mb-3">
                    <label for="lga_of_origin" class="form-label required">Local Government Area of Origin</label>
                    <select class="form-select @error('lga_of_origin') is-invalid @enderror" 
                            id="lga_of_origin" 
                            name="lga_of_origin" 
                            required>
                        <option value="">Select LGA of Origin</option>
                        <!-- Will be populated by JavaScript based on state selection -->
                    </select>
                    @error('lga_of_origin')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Blood Group -->
                <div class="col-md-4 mb-3">
                    <label for="blood_group" class="form-label">Blood Group</label>
                    <select class="form-select @error('blood_group') is-invalid @enderror" 
                            id="blood_group" 
                            name="blood_group">
                        <option value="">Select Blood Group (optional)</option>
                        @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bloodGroup)
                            <option value="{{ $bloodGroup }}" 
                                {{ old('blood_group', $driver->blood_group) == $bloodGroup ? 'selected' : '' }}>
                                {{ $bloodGroup }}
                            </option>
                        @endforeach
                    </select>
                    @error('blood_group')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Height -->
                <div class="col-md-4 mb-3">
                    <label for="height_meters" class="form-label">Height (meters)</label>
                    <input type="number" 
                           class="form-control @error('height_meters') is-invalid @enderror" 
                           id="height_meters" 
                           name="height_meters" 
                           value="{{ old('height_meters', $driver->height_meters) }}"
                           min="1.0" 
                           max="2.5" 
                           step="0.01"
                           placeholder="e.g., 1.75">
                    @error('height_meters')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- NIN Number -->
                <div class="col-md-6 mb-3">
                    <label for="nin_number" class="form-label required">NIN Number</label>
                    <input type="text" 
                           class="form-control @error('nin_number') is-invalid @enderror" 
                           id="nin_number" 
                           name="nin_number" 
                           value="{{ old('nin_number', $driver->nin_number) }}"
                           placeholder="Enter 11-digit NIN"
                           maxlength="11"
                           pattern="[0-9]{11}"
                           required>
                    @error('nin_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">National Identification Number (11 digits)</small>
                </div>

                <!-- Driver License Number -->
                <div class="col-md-6 mb-3">
                    <label for="license_number" class="form-label required">Driver's License Number</label>
                    <input type="text" 
                           class="form-control @error('license_number') is-invalid @enderror" 
                           id="license_number" 
                           name="license_number" 
                           value="{{ old('license_number', $driver->license_number) }}"
                           placeholder="Enter your driver's license number"
                           required>
                    @error('license_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Enter your official driver's license number</small>
                </div>

                <!-- Alternative Phone -->
                <div class="col-md-12 mb-3">
                    <label for="phone_2" class="form-label">Alternative Phone Number</label>
                    <input type="tel" 
                           class="form-control @error('phone_2') is-invalid @enderror" 
                           id="phone_2" 
                           name="phone_2" 
                           value="{{ old('phone_2', $driver->phone_2) }}"
                           placeholder="Enter alternative phone number">
                    @error('phone_2')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <!-- Emergency Contact Section -->
    <div class="step-card">
        <div class="step-card-header">
            <h6 class="mb-0">
                <i class="fas fa-phone-alt me-2"></i>
                Emergency Contact Information
            </h6>
        </div>
        <div class="step-card-body">
            <div class="row">
                <!-- Emergency Contact Name -->
                <div class="col-md-4 mb-3">
                    <label for="emergency_contact_name" class="form-label required">Contact Name</label>
                    <input type="text" 
                           class="form-control @error('emergency_contact_name') is-invalid @enderror" 
                           id="emergency_contact_name" 
                           name="emergency_contact_name" 
                           value="{{ old('emergency_contact_name', $driver->emergency_contact_name) }}"
                           placeholder="Enter emergency contact name"
                           required>
                    @error('emergency_contact_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Emergency Contact Phone -->
                <div class="col-md-4 mb-3">
                    <label for="emergency_contact_phone" class="form-label required">Contact Phone</label>
                    <input type="tel" 
                           class="form-control @error('emergency_contact_phone') is-invalid @enderror" 
                           id="emergency_contact_phone" 
                           name="emergency_contact_phone" 
                           value="{{ old('emergency_contact_phone', $driver->emergency_contact_phone) }}"
                           placeholder="Enter emergency contact phone"
                           required>
                    @error('emergency_contact_phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Emergency Contact Relationship -->
                <div class="col-md-4 mb-3">
                    <label for="emergency_contact_relationship" class="form-label required">Relationship</label>
                    <input type="text" 
                           class="form-control @error('emergency_contact_relationship') is-invalid @enderror" 
                           id="emergency_contact_relationship" 
                           name="emergency_contact_relationship" 
                           value="{{ old('emergency_contact_relationship', $driver->emergency_contact_relationship) }}"
                           placeholder="e.g., Spouse, Parent, Sibling"
                           required>
                    @error('emergency_contact_relationship')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <!-- Form Actions -->
    <div class="d-flex justify-content-between mt-4">
        <a href="{{ route('driver.kyc.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Back to Overview
        </a>
        
        <button type="submit" class="btn btn-kyc-primary">
            Continue to Step 2
            <i class="fas fa-arrow-right ms-1"></i>
        </button>
    </div>
</form>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // State and LGA handling
    const stateSelect = document.getElementById('state_of_origin');
    const lgaSelect = document.getElementById('lga_of_origin');
    
    // Load LGAs when state changes
    stateSelect.addEventListener('change', function() {
        const stateId = this.value;
        lgaSelect.innerHTML = '<option value="">Select LGA of Origin</option>';
        
        if (stateId) {
            fetch(`{{ route('driver.kyc.lgas', '') }}/${stateId}`)
                .then(response => response.json())
                .then(lgas => {
                    lgas.forEach(lga => {
                        const option = document.createElement('option');
                        option.value = lga.id;
                        option.textContent = lga.name;
                        lgaSelect.appendChild(option);
                    });
                    
                    // Restore selected value if exists
                    const selectedLga = '{{ old('lga_of_origin', $driver->lga_of_origin) }}';
                    if (selectedLga) {
                        lgaSelect.value = selectedLga;
                    }
                })
                .catch(error => {
                    console.error('Error loading LGAs:', error);
                    showToast('Error loading Local Government Areas', 'error');
                });
        }
    });
    
    // Load LGAs on page load if state is already selected
    if (stateSelect.value) {
        stateSelect.dispatchEvent(new Event('change'));
    }
    
    // NIN input formatting
    const ninInput = document.getElementById('nin_number');
    ninInput.addEventListener('input', function() {
        // Remove non-numeric characters
        this.value = this.value.replace(/[^0-9]/g, '');
        
        // Limit to 11 digits
        if (this.value.length > 11) {
            this.value = this.value.substr(0, 11);
        }
    });
    
    // Form validation
    const form = document.querySelector('.needs-validation');
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        form.classList.add('was-validated');
    });
    
    // Age calculation and display
    const dobInput = document.getElementById('date_of_birth');
    dobInput.addEventListener('change', function() {
        if (this.value) {
            const birthDate = new Date(this.value);
            const today = new Date();
            const age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();
            
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }
            
            if (age < 18) {
                this.setCustomValidity('You must be at least 18 years old');
            } else {
                this.setCustomValidity('');
            }
        }
    });
});

function showToast(message, type = 'info') {
    // Simple toast notification
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'error' ? 'danger' : type} position-fixed`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 1060; min-width: 300px;';
    toast.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas fa-${type === 'error' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 5000);
}
</script>
@endsection