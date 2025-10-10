@extends('layouts.driver-kyc')

@section('title', 'KYC Step 2 - Contact & Address Information')
@section('page-title', 'Contact & Address Information')
@section('page-description', 'Enter your contact details, address, and banking information')

@php
    $currentStep = 2;
@endphp

@section('content')
<!-- Progress Indicator -->
<div class="step-progress mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div class="step-item completed">
            <div class="step-number"><i class="fas fa-check"></i></div>
            <div class="step-title">Personal Info</div>
        </div>
        <div class="progress-line completed"></div>
        <div class="step-item active">
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
                <i class="fas fa-map-marker-alt me-2" style="color: var(--drivelink-primary);"></i>
                Contact & Address Information
            </h5>
            <p class="mb-0 text-muted">
                Provide your current address, driver's license details, and banking information for payment processing.
            </p>
        </div>
        <div class="col-md-4 text-end">
            <span class="badge bg-primary px-3 py-2">
                <i class="fas fa-clock me-1"></i>
                Step 2 of 3
            </span>
        </div>
    </div>
</div>

<!-- KYC Form -->
<form method="POST" action="{{ route('driver.kyc.step2.submit') }}" class="needs-validation" novalidate>
    @csrf
    
    <!-- Address Information -->
    <div class="step-card">
        <div class="step-card-header">
            <h6 class="mb-0">
                <i class="fas fa-home me-2"></i>
                Current Address Information
            </h6>
        </div>
        <div class="step-card-body">
            <div class="row">
                <!-- Residential Address -->
                <div class="col-md-12 mb-3">
                    <label for="residential_address" class="form-label required">Current Residential Address</label>
                    <textarea class="form-control @error('residential_address') is-invalid @enderror" 
                              id="residential_address" 
                              name="residential_address" 
                              rows="3"
                              placeholder="Enter your full current address"
                              required>{{ old('residential_address', $driver->residential_address) }}</textarea>
                    @error('residential_address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Current State -->
                <div class="col-md-4 mb-3">
                    <label for="residence_state_id" class="form-label required">Current State</label>
                    <select class="form-select @error('residence_state_id') is-invalid @enderror" 
                            id="residence_state_id" 
                            name="residence_state_id" 
                            required>
                        <option value="">Select Current State</option>
                        @foreach($states as $state)
                            <option value="{{ $state->id }}" 
                                {{ old('residence_state_id', $driver->residence_state_id) == $state->id ? 'selected' : '' }}>
                                {{ $state->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('residence_state_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Current LGA -->
                <div class="col-md-4 mb-3">
                    <label for="residence_lga_id" class="form-label required">Current LGA</label>
                    <select class="form-select @error('residence_lga_id') is-invalid @enderror" 
                            id="residence_lga_id" 
                            name="residence_lga_id" 
                            required>
                        <option value="">Select Current LGA</option>
                        <!-- Will be populated by JavaScript based on state selection -->
                    </select>
                    @error('residence_lga_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- City -->
                <div class="col-md-4 mb-3">
                    <label for="city" class="form-label">City</label>
                    <input type="text" 
                           class="form-control @error('city') is-invalid @enderror" 
                           id="city" 
                           name="city" 
                           value="{{ old('city', $driver->city) }}"
                           placeholder="Enter city name">
                    @error('city')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Postal Code -->
                <div class="col-md-12 mb-3">
                    <label for="postal_code" class="form-label">Postal Code</label>
                    <input type="text" 
                           class="form-control @error('postal_code') is-invalid @enderror" 
                           id="postal_code" 
                           name="postal_code" 
                           value="{{ old('postal_code', $driver->postal_code) }}"
                           placeholder="Enter postal code (optional)"
                           maxlength="10">
                    @error('postal_code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <!-- Driver's License Information -->
    <div class="step-card">
        <div class="step-card-header">
            <h6 class="mb-0">
                <i class="fas fa-id-card-alt me-2"></i>
                Driver's License Information
            </h6>
        </div>
        <div class="step-card-body">
            <div class="row">
                <!-- License Class -->
                <div class="col-md-12 mb-3">
                    <label for="license_class" class="form-label required">License Class</label>
                    <select class="form-select @error('license_class') is-invalid @enderror" 
                            id="license_class" 
                            name="license_class" 
                            required>
                        <option value="">Select License Class</option>
                        <option value="A" {{ old('license_class', $driver->license_class) == 'A' ? 'selected' : '' }}>Class A (Heavy Vehicles)</option>
                        <option value="B" {{ old('license_class', $driver->license_class) == 'B' ? 'selected' : '' }}>Class B (Medium Vehicles)</option>
                        <option value="C" {{ old('license_class', $driver->license_class) == 'C' ? 'selected' : '' }}>Class C (Light Vehicles)</option>
                        <option value="D" {{ old('license_class', $driver->license_class) == 'D' ? 'selected' : '' }}>Class D (Passenger Transport)</option>
                        <option value="E" {{ old('license_class', $driver->license_class) == 'E' ? 'selected' : '' }}>Class E (Motorcycle)</option>
                        <option value="F" {{ old('license_class', $driver->license_class) == 'F' ? 'selected' : '' }}>Class F (Agricultural)</option>
                    </select>
                    @error('license_class')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">License number was captured in Step 1</small>
                </div>

                <!-- License Issue Date -->
                <div class="col-md-6 mb-3">
                    <label for="license_issue_date" class="form-label required">License Issue Date</label>
                    <input type="date" 
                           class="form-control @error('license_issue_date') is-invalid @enderror" 
                           id="license_issue_date" 
                           name="license_issue_date" 
                           value="{{ old('license_issue_date', $driver->license_issue_date ? $driver->license_issue_date->format('Y-m-d') : '') }}"
                           max="{{ date('Y-m-d') }}"
                           required>
                    @error('license_issue_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- License Expiry Date -->
                <div class="col-md-6 mb-3">
                    <label for="license_expiry_date" class="form-label required">License Expiry Date</label>
                    <input type="date" 
                           class="form-control @error('license_expiry_date') is-invalid @enderror" 
                           id="license_expiry_date" 
                           name="license_expiry_date" 
                           value="{{ old('license_expiry_date', $driver->license_expiry_date ? $driver->license_expiry_date->format('Y-m-d') : '') }}"
                           min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                           required>
                    @error('license_expiry_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">License must be valid for at least one day</small>
                </div>

                <!-- Years of Experience -->
                <div class="col-md-6 mb-3">
                    <label for="years_of_experience" class="form-label required">Years of Driving Experience</label>
                    <input type="number" 
                           class="form-control @error('years_of_experience') is-invalid @enderror" 
                           id="years_of_experience" 
                           name="years_of_experience" 
                           value="{{ old('years_of_experience', $driver->years_of_experience) }}"
                           min="0" 
                           max="50"
                           placeholder="Enter years of experience"
                           required>
                    @error('years_of_experience')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Previous Company -->
                <div class="col-md-6 mb-3">
                    <label for="previous_company" class="form-label">Previous Company</label>
                    <input type="text" 
                           class="form-control @error('previous_company') is-invalid @enderror" 
                           id="previous_company" 
                           name="previous_company" 
                           value="{{ old('previous_company', $driver->previous_company) }}"
                           placeholder="Enter previous company name (optional)">
                    @error('previous_company')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <!-- Banking Information -->
    <div class="step-card">
        <div class="step-card-header">
            <h6 class="mb-0">
                <i class="fas fa-university me-2"></i>
                Banking Information
                <small class="text-muted ms-2">(For payment processing)</small>
            </h6>
        </div>
        <div class="step-card-body">
            <div class="alert alert-info border-start border-4 border-info">
                <div class="d-flex align-items-center">
                    <i class="fas fa-info-circle me-3"></i>
                    <div>
                        <strong>Secure Banking Details</strong><br>
                        Your banking information is encrypted and used solely for payment processing. We comply with banking security standards.
                    </div>
                </div>
            </div>
            
            <div class="row">
                <!-- Bank -->
                <div class="col-md-12 mb-3">
                    <label for="bank_id" class="form-label required">Bank</label>
                    <select class="form-select @error('bank_id') is-invalid @enderror" 
                            id="bank_id" 
                            name="bank_id" 
                            required>
                        <option value="">Select Your Bank</option>
                        @foreach($banks as $bank)
                            <option value="{{ $bank->id }}" 
                                {{ old('bank_id', $driver->bank_id) == $bank->id ? 'selected' : '' }}>
                                {{ $bank->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('bank_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Account Number -->
                <div class="col-md-6 mb-3">
                    <label for="account_number" class="form-label required">Account Number</label>
                    <input type="text" 
                           class="form-control @error('account_number') is-invalid @enderror" 
                           id="account_number" 
                           name="account_number" 
                           value="{{ old('account_number', $driver->account_number) }}"
                           placeholder="Enter 10-digit account number"
                           maxlength="10"
                           pattern="[0-9]{10}"
                           required>
                    @error('account_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Account Name -->
                <div class="col-md-6 mb-3">
                    <label for="account_name" class="form-label required">Account Name</label>
                    <input type="text" 
                           class="form-control @error('account_name') is-invalid @enderror" 
                           id="account_name" 
                           name="account_name" 
                           value="{{ old('account_name', $driver->account_name) }}"
                           placeholder="Enter account holder name"
                           required>
                    @error('account_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Must match the name on your bank account</small>
                </div>

                <!-- BVN -->
                <div class="col-md-12 mb-3">
                    <label for="bvn" class="form-label required">Bank Verification Number (BVN)</label>
                    <input type="text" 
                           class="form-control @error('bvn') is-invalid @enderror" 
                           id="bvn" 
                           name="bvn" 
                           value="{{ old('bvn', $driver->bvn) }}"
                           placeholder="Enter 11-digit BVN"
                           maxlength="11"
                           pattern="[0-9]{11}"
                           required>
                    @error('bvn')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Your BVN is required for identity verification and payment processing</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Actions -->
    <div class="d-flex justify-content-between mt-4">
        <a href="{{ route('driver.kyc.step1') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Back to Step 1
        </a>
        
        <button type="submit" class="btn btn-kyc-primary">
            Continue to Step 3
            <i class="fas fa-arrow-right ms-1"></i>
        </button>
    </div>
</form>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // State and LGA handling for residence
    const stateSelect = document.getElementById('residence_state_id');
    const lgaSelect = document.getElementById('residence_lga_id');
    
    // Load LGAs when state changes
    stateSelect.addEventListener('change', function() {
        const stateId = this.value;
        lgaSelect.innerHTML = '<option value="">Select Current LGA</option>';
        
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
                    const selectedLga = '{{ old('residence_lga_id', $driver->residence_lga_id) }}';
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
    
    // Account number formatting
    const accountNumberInput = document.getElementById('account_number');
    accountNumberInput.addEventListener('input', function() {
        // Remove non-numeric characters
        this.value = this.value.replace(/[^0-9]/g, '');
        
        // Limit to 10 digits
        if (this.value.length > 10) {
            this.value = this.value.substr(0, 10);
        }
    });
    
    // BVN formatting
    const bvnInput = document.getElementById('bvn');
    bvnInput.addEventListener('input', function() {
        // Remove non-numeric characters
        this.value = this.value.replace(/[^0-9]/g, '');
        
        // Limit to 11 digits
        if (this.value.length > 11) {
            this.value = this.value.substr(0, 11);
        }
    });
    
    // License expiry validation
    const issueDate = document.getElementById('license_issue_date');
    const expiryDate = document.getElementById('license_expiry_date');
    
    issueDate.addEventListener('change', function() {
        if (this.value) {
            // Set minimum expiry date to 1 year after issue date
            const minExpiry = new Date(this.value);
            minExpiry.setFullYear(minExpiry.getFullYear() + 1);
            expiryDate.min = minExpiry.toISOString().split('T')[0];
            
            // Validate current expiry date
            if (expiryDate.value && new Date(expiryDate.value) < minExpiry) {
                expiryDate.setCustomValidity('License must be valid for at least 1 year from issue date');
            } else {
                expiryDate.setCustomValidity('');
            }
        }
    });
    
    expiryDate.addEventListener('change', function() {
        if (this.value && issueDate.value) {
            const issue = new Date(issueDate.value);
            const expiry = new Date(this.value);
            const minExpiry = new Date(issue);
            minExpiry.setFullYear(minExpiry.getFullYear() + 1);
            
            if (expiry < minExpiry) {
                this.setCustomValidity('License must be valid for at least 1 year from issue date');
            } else {
                this.setCustomValidity('');
            }
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