@extends('layouts.driver-kyc')

@section('title', 'KYC Step 1 - Personal Information')
@section('page-title', 'Step 1: Personal Information')
@section('page-description', 'Please provide your complete personal details')

@php
    $currentStep = 1;
@endphp

@section('content')
<div class="step-info">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h6 class="mb-2">
                <i class="fas fa-info-circle me-2" style="color: var(--drivelink-info);"></i>
                What You Need to Complete This Step
            </h6>
            <ul class="mb-0">
                <li>Your complete personal information</li>
                <li>Emergency contact details</li>
                <li>Residential address information</li>
                <li>State and LGA of origin</li>
            </ul>
        </div>
        <div class="col-md-4 text-center">
            <div style="color: var(--drivelink-info);">
                <i class="fas fa-clock me-1"></i>
                <span>Estimated time: 3-5 minutes</span>
            </div>
        </div>
    </div>
</div>
<div class="step-card">
    <div class="step-card-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-1">
                    <i class="fas fa-user me-2"></i>
                    Personal Information
                </h5>
                <p class="mb-0 text-muted">Fill in your basic personal details</p>
            </div>
            <span class="badge bg-primary">Step 1 of 3</span>
        </div>
    </div>
    <div class="step-card-body">

                    <form method="POST" action="{{ route('driver.kyc.step1.submit') }}">
                        @csrf

                        <!-- Basic Information Section -->
                        <div class="mb-4">
                            <h6 class="text-muted mb-3">
                                <i class="fas fa-user me-2"></i>
                                Basic Information
                                <small class="text-muted ms-2">(from registration - cannot be changed)</small>
                            </h6>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <!-- First Name (Read-only) -->
                                <div class="mb-3">
                                    <label class="form-label">First Name</label>
                                    <input type="text" class="form-control bg-light" value="{{ $driver->first_name }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <!-- Last Name (Read-only) -->
                                <div class="mb-3">
                                    <label class="form-label">Last Name</label>
                                    <input type="text" class="form-control bg-light" value="{{ $driver->surname }}" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <!-- Email (Read-only) -->
                                <div class="mb-3">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" class="form-control bg-light" value="{{ $driver->email }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <!-- Phone (Read-only) -->
                                <div class="mb-3">
                                    <label class="form-label">Phone Number</label>
                                    <input type="tel" class="form-control bg-light" value="{{ $driver->phone }}" readonly>
                                </div>
                            </div>
                        </div>

                        @if($driver->date_of_birth)
                        <div class="row">
                            <div class="col-md-6">
                                <!-- Date of Birth (Read-only) -->
                                <div class="mb-3">
                                    <label class="form-label">Date of Birth</label>
                                    <input type="text" class="form-control bg-light" value="{{ \Carbon\Carbon::parse($driver->date_of_birth)->format('d M Y') }}" readonly>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Divider for Additional Information -->
                        <hr class="my-4">
                        <h6 class="text-primary mb-3">
                            <i class="fas fa-edit me-2"></i>
                            Additional Personal Details
                        </h6>

                        <!-- Middle Name -->
                        <div class="mb-3">
                            <label for="middle_name" class="form-label">Middle Name (Optional)</label>
                            <input type="text"
                                   class="form-control @error('middle_name') is-invalid @enderror"
                                   id="middle_name"
                                   name="middle_name"
                                   value="{{ old('middle_name', $driver->middle_name) }}"
                                   placeholder="Enter your middle name">
                            @error('middle_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Gender -->
                        <div class="mb-3">
                            <label for="gender" class="form-label">Gender <span class="text-danger">*</span></label>
                            <select class="form-control @error('gender') is-invalid @enderror"
                                    id="gender"
                                    name="gender"
                                    aria-describedby="gender_help"
                                    aria-required="true"
                                    required>
                                <option value="">Select Gender</option>
                                <option value="Male" {{ old('gender', $driver->gender) == 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ old('gender', $driver->gender) == 'Female' ? 'selected' : '' }}>Female</option>
                                <option value="Other" {{ old('gender', $driver->gender) == 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                            <div id="gender_help" class="sr-only">Please select your gender from the available options</div>
                            @error('gender')
                                <div class="invalid-feedback" role="alert">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Marital Status -->
                        <div class="mb-3">
                            <label for="marital_status" class="form-label">Marital Status <span class="text-danger">*</span></label>
                            <select class="form-control @error('marital_status') is-invalid @enderror"
                                    id="marital_status"
                                    name="marital_status"
                                    required>
                                <option value="">Select Marital Status</option>
                                <option value="Single" {{ old('marital_status', $driver->marital_status) == 'Single' ? 'selected' : '' }}>Single</option>
                                <option value="Married" {{ old('marital_status', $driver->marital_status) == 'Married' ? 'selected' : '' }}>Married</option>
                                <option value="Divorced" {{ old('marital_status', $driver->marital_status) == 'Divorced' ? 'selected' : '' }}>Divorced</option>
                                <option value="Widowed" {{ old('marital_status', $driver->marital_status) == 'Widowed' ? 'selected' : '' }}>Widowed</option>
                            </select>
                            @error('marital_status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Nationality -->
                        <div class="mb-3">
                            <label for="nationality_id" class="form-label">Nationality <span class="text-danger">*</span></label>
                            <select class="form-control @error('nationality_id') is-invalid @enderror"
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

                        <!-- State -->
                        <div class="mb-3">
                            <label for="state_of_origin" class="form-label">State of Origin <span class="text-danger">*</span></label>
                            <select class="form-control @error('state_of_origin') is-invalid @enderror"
                                    id="state_of_origin"
                                    name="state_of_origin"
                                    required>
                                <option value="">Select State</option>
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

                        <!-- Local Government Area -->
                        <div class="mb-3">
                            <label for="lga_of_origin" class="form-label">Local Government Area <span class="text-danger">*</span></label>
                            <select class="form-control @error('lga_of_origin') is-invalid @enderror"
                                    id="lga_of_origin"
                                    name="lga_of_origin"
                                    aria-describedby="lga_help"
                                    aria-live="polite"
                                    aria-label="Local Government Area of Origin"
                                    required>
                                <option value="">Select Local Government Area</option>
                                <!-- LGAs will be populated via JavaScript -->
                                @if(old('lga_of_origin', $driver->lga_of_origin) && $driver->originLga)
                                    <option value="{{ $driver->originLga->id }}" selected>{{ $driver->originLga->name }}</option>
                                @endif
                            </select>
                            <div id="lga_help" class="form-text text-muted">
                                <i class="fas fa-info-circle me-1" aria-hidden="true"></i>
                                This field will populate after selecting your state of origin
                            </div>
                            @error('lga_of_origin')
                                <div class="invalid-feedback" role="alert">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Residential Address -->
                        <div class="mb-3">
                            <label for="residential_address" class="form-label">Residential Address <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('residential_address') is-invalid @enderror"
                                      id="residential_address"
                                      name="residential_address"
                                      rows="3"
                                      placeholder="Enter your full residential address including street name, city, etc."
                                      required>{{ old('residential_address', $driver->residential_address) }}</textarea>
                            @error('residential_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Emergency Contact Information -->
                        <h6 class="mt-4 mb-3"><i class="fas fa-phone"></i> Emergency Contact Information</h6>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="emergency_contact_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text"
                                           class="form-control @error('emergency_contact_name') is-invalid @enderror"
                                           id="emergency_contact_name"
                                           name="emergency_contact_name"
                                           value="{{ old('emergency_contact_name', $driver->emergency_contact_name) }}"
                                           placeholder="Emergency contact full name"
                                           required>
                                    @error('emergency_contact_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="emergency_contact_phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                    <input type="tel"
                                           class="form-control @error('emergency_contact_phone') is-invalid @enderror"
                                           id="emergency_contact_phone"
                                           name="emergency_contact_phone"
                                           value="{{ old('emergency_contact_phone', $driver->emergency_contact_phone) }}"
                                           placeholder="Emergency contact phone"
                                           required>
                                    @error('emergency_contact_phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="emergency_contact_relationship" class="form-label">Relationship <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control @error('emergency_contact_relationship') is-invalid @enderror"
                                   id="emergency_contact_relationship"
                                   name="emergency_contact_relationship"
                                   value="{{ old('emergency_contact_relationship', $driver->emergency_contact_relationship) }}"
                                   placeholder="e.g., Father, Mother, Spouse, Friend"
                                   required>
                            @error('emergency_contact_relationship')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Form Actions -->
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <a href="{{ route('driver.kyc.index') }}" class="btn btn-kyc-outline w-100">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Overview
                                </a>
                            </div>
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-kyc-primary w-100">
                                    Continue to Step 2 <i class="fas fa-arrow-right ms-2"></i>
                                </button>
                            </div>
                        </div>
                    </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const stateSelect = document.getElementById('state_of_origin');
    const lgaSelect = document.getElementById('lga_of_origin');

    // Store the original LGA value for edit mode
    const originalLgaId = '{{ old("lga_of_origin", $driver->lga_of_origin) }}';
    
    // Create screen reader announcement area
    function createAnnouncementArea() {
        if (document.getElementById('sr-announcements')) return;
        
        const announcer = document.createElement('div');
        announcer.id = 'sr-announcements';
        announcer.setAttribute('aria-live', 'polite');
        announcer.setAttribute('aria-atomic', 'true');
        announcer.className = 'sr-only';
        document.body.appendChild(announcer);
    }
    
    // Function to announce messages to screen readers
    function announceToScreenReader(message) {
        createAnnouncementArea();
        const announcer = document.getElementById('sr-announcements');
        announcer.textContent = message;
        
        // Clear after 2 seconds to allow for new announcements
        setTimeout(() => {
            if (announcer.textContent === message) {
                announcer.textContent = '';
            }
        }, 2000);
    }
    
    // Make announceToScreenReader available globally
    window.announceToScreenReader = announceToScreenReader;

    stateSelect.addEventListener('change', function() {
        const stateId = this.value;
        const currentLgaId = lgaSelect.value;

        // Clear LGA options and add loading state
        lgaSelect.innerHTML = '<option value="">Loading...</option>';
        lgaSelect.classList.add('loading-indicator');
        lgaSelect.setAttribute('aria-busy', 'true');

        if (stateId) {
            fetch(`/driver/kyc/lgas/${stateId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    lgaSelect.innerHTML = '<option value="">Select Local Government Area</option>';
                    lgaSelect.classList.remove('loading-indicator');
                    lgaSelect.setAttribute('aria-busy', 'false');
                    
                    // Handle API response format
                    const lgas = data.success ? data.data : data;
                    
                    if (lgas && lgas.length > 0) {
                        lgas.forEach(lga => {
                            const option = document.createElement('option');
                            option.value = lga.id;
                            option.textContent = lga.name;
                            // Restore previously selected LGA if it exists
                            if ((currentLgaId && currentLgaId == lga.id) || (originalLgaId && originalLgaId == lga.id)) {
                                option.selected = true;
                            }
                            lgaSelect.appendChild(option);
                        });
                    } else {
                        lgaSelect.innerHTML = '<option value="">No LGAs found for this state</option>';
                    }
                    
                    // Announce to screen readers
                    const announcement = lgas && lgas.length > 0 
                        ? `${lgas.length} local government areas loaded for selected state`
                        : 'No local government areas found for selected state';
                    announceToScreenReader(announcement);
                })
                .catch(error => {
                    console.error('Error loading LGAs:', error);
                    lgaSelect.innerHTML = '<option value="">Error loading LGAs. Please try again.</option>';
                    lgaSelect.classList.remove('loading-indicator');
                    lgaSelect.setAttribute('aria-busy', 'false');
                    
                    // Announce error to screen readers
                    announceToScreenReader('Error loading local government areas. Please try again or contact support.');
                });
        } else {
            lgaSelect.innerHTML = '<option value="">Select Local Government Area</option>';
        }
    });

    // Trigger change event if state is already selected (for edit mode)
    if (stateSelect.value) {
        stateSelect.dispatchEvent(new Event('change'));
    }
});
</script>

<style>
.progress {
    background-color: #e9ecef;
}

.text-danger {
    color: #dc3545!important;
}

.alert-info {
    background-color: #d1ecf1;
    border-color: #b8daff;
    color: #0c5460;
}

.form-label {
    font-weight: 600;
    color: #495057;
}

/* Screen reader only content */
.sr-only {
    position: absolute !important;
    width: 1px !important;
    height: 1px !important;
    padding: 0 !important;
    margin: -1px !important;
    overflow: hidden !important;
    clip: rect(0, 0, 0, 0) !important;
    border: 0 !important;
}

/* Loading indicator for dynamic content */
.loading-indicator {
    opacity: 0.7;
    pointer-events: none;
    position: relative;
}

.loading-indicator::after {
    content: '';
    position: absolute;
    top: 50%;
    right: 10px;
    width: 16px;
    height: 16px;
    border: 2px solid #dee2e6;
    border-top: 2px solid #007bff;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    transform: translateY(-50%);
}

@keyframes spin {
    0% { transform: translateY(-50%) rotate(0deg); }
    100% { transform: translateY(-50%) rotate(360deg); }
</style>
@endsection
