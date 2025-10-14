@extends('layouts.driver-kyc')

@section('title', 'KYC Step 3 - Employment & Document Upload')
@section('page-title', 'Employment & Document Upload')
@section('page-description', 'Complete your employment details and upload required documents')

@php
    $currentStep = 3;
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
        <div class="step-item completed">
            <div class="step-number"><i class="fas fa-check"></i></div>
            <div class="step-title">Contact & Address</div>
        </div>
        <div class="progress-line completed"></div>
        <div class="step-item active">
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
                <i class="fas fa-file-upload me-2" style="color: var(--drivelink-primary);"></i>
                Employment & Document Upload
            </h5>
            <p class="mb-0 text-muted">
                Provide employment details and upload required documents for verification. All documents must be clear and readable.
            </p>
        </div>
        <div class="col-md-4 text-end">
            <span class="badge bg-primary px-3 py-2">
                <i class="fas fa-clipboard-check me-1"></i>
                Final Step
            </span>
        </div>
    </div>
</div>

<!-- KYC Form -->
<form method="POST" action="{{ route('driver.kyc.step3.submit') }}" class="needs-validation" enctype="multipart/form-data" novalidate>
    @csrf

    <!-- Employment Information -->
    <div class="step-card">
        <div class="step-card-header">
            <h6 class="mb-0">
                <i class="fas fa-briefcase me-2"></i>
                Employment & Work Preferences
            </h6>
        </div>
        <div class="step-card-body">
            <div class="row">
                <!-- Current Employer -->
                <div class="col-md-6 mb-3">
                    <label for="current_employer" class="form-label">Current Employer</label>
                    <input type="text"
                           class="form-control @error('current_employer') is-invalid @enderror"
                           id="current_employer"
                           name="current_employer"
                           value="{{ old('current_employer', $driver->current_employer) }}"
                           placeholder="Enter current employer name (if any)">
                    @error('current_employer')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Employment Start Date -->
                <div class="col-md-6 mb-3">
                    <label for="employment_start_date" class="form-label">Employment Start Date</label>
                    <input type="date"
                           class="form-control @error('employment_start_date') is-invalid @enderror"
                           id="employment_start_date"
                           name="employment_start_date"
                           value="{{ old('employment_start_date', $driver->employment_start_date ? $driver->employment_start_date->format('Y-m-d') : '') }}"
                           max="{{ date('Y-m-d') }}">
                    @error('employment_start_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Current Employment Status -->
                <div class="col-md-12 mb-3">
                    <label class="form-label required">Are you currently working?</label>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input type="radio"
                                       class="form-check-input @error('is_working') is-invalid @enderror"
                                       id="is_working_yes"
                                       name="is_working"
                                       value="1"
                                       {{ old('is_working', $driver->is_working) == '1' ? 'checked' : '' }}
                                       required>
                                <label class="form-check-label" for="is_working_yes">
                                    <i class="fas fa-briefcase me-2 text-success"></i>
                                    Yes, I am currently working
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input type="radio"
                                       class="form-check-input @error('is_working') is-invalid @enderror"
                                       id="is_working_no"
                                       name="is_working"
                                       value="0"
                                       {{ old('is_working', $driver->is_working) == '0' ? 'checked' : '' }}
                                       required>
                                <label class="form-check-label" for="is_working_no">
                                    <i class="fas fa-times me-2 text-danger"></i>
                                    No, I am not currently working
                                </label>
                            </div>
                        </div>
                    </div>
                    @error('is_working')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Previous Employment Details (shown only if not currently working) -->
                <div id="previous_employment_details" class="col-md-12" style="display: none;">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="previous_workplace" class="form-label">Previous Workplace</label>
                            <input type="text"
                                   class="form-control @error('previous_workplace') is-invalid @enderror"
                                   id="previous_workplace"
                                   name="previous_workplace"
                                   value="{{ old('previous_workplace', $driver->previous_workplace) }}"
                                   placeholder="Enter your previous employer name">
                            @error('previous_workplace')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="previous_work_id_record" class="form-label">Previous Work ID Record</label>
                            <input type="text"
                                   class="form-control @error('previous_work_id_record') is-invalid @enderror"
                                   id="previous_work_id_record"
                                   name="previous_work_id_record"
                                   value="{{ old('previous_work_id_record', $driver->previous_work_id_record) }}"
                                   placeholder="Enter your previous work ID number">
                            @error('previous_work_id_record')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-12 mb-3">
                            <label for="reason_stopped_working" class="form-label">Reason for Stopping Work</label>
                            <textarea class="form-control @error('reason_stopped_working') is-invalid @enderror"
                                      id="reason_stopped_working"
                                      name="reason_stopped_working"
                                      rows="3"
                                      placeholder="Please explain why you stopped working">{{ old('reason_stopped_working', $driver->reason_stopped_working) }}</textarea>
                            @error('reason_stopped_working')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Vehicle Ownership -->
                <div class="col-md-12 mb-3">
                    <label class="form-label required">Do you own a vehicle?</label>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input type="radio"
                                       class="form-check-input @error('has_vehicle') is-invalid @enderror"
                                       id="has_vehicle_yes"
                                       name="has_vehicle"
                                       value="1"
                                       {{ old('has_vehicle', $driver->has_vehicle) == '1' ? 'checked' : '' }}
                                       required>
                                <label class="form-check-label" for="has_vehicle_yes">
                                    <i class="fas fa-car me-2 text-success"></i>
                                    Yes, I own a vehicle
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input type="radio"
                                       class="form-check-input @error('has_vehicle') is-invalid @enderror"
                                       id="has_vehicle_no"
                                       name="has_vehicle"
                                       value="0"
                                       {{ old('has_vehicle', $driver->has_vehicle) == '0' ? 'checked' : '' }}
                                       required>
                                <label class="form-check-label" for="has_vehicle_no">
                                    <i class="fas fa-times me-2 text-danger"></i>
                                    No, I don't own a vehicle
                                </label>
                            </div>
                        </div>
                    </div>
                    @error('has_vehicle')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Vehicle Details (shown only if has_vehicle is Yes) -->
                <div id="vehicle_details" class="col-md-12" style="display: none;">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="vehicle_type" class="form-label">Vehicle Type</label>
                            <input type="text"
                                   class="form-control @error('vehicle_type') is-invalid @enderror"
                                   id="vehicle_type"
                                   name="vehicle_type"
                                   value="{{ old('vehicle_type', $driver->vehicle_type) }}"
                                   placeholder="e.g., Toyota Corolla, Honda Civic">
                            @error('vehicle_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="vehicle_year" class="form-label">Vehicle Year</label>
                            <input type="number"
                                   class="form-control @error('vehicle_year') is-invalid @enderror"
                                   id="vehicle_year"
                                   name="vehicle_year"
                                   value="{{ old('vehicle_year', $driver->vehicle_year) }}"
                                   min="1980"
                                   max="{{ date('Y') + 1 }}"
                                   placeholder="e.g., 2018">
                            @error('vehicle_year')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Work Preferences -->
                <div class="col-md-12 mb-3">
                    <label for="preferred_work_location" class="form-label">Preferred Work Location</label>
                    <input type="text"
                           class="form-control @error('preferred_work_location') is-invalid @enderror"
                           id="preferred_work_location"
                           name="preferred_work_location"
                           value="{{ old('preferred_work_location', $driver->preferred_work_location) }}"
                           placeholder="Enter your preferred work area/location">
                    @error('preferred_work_location')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Availability Preferences -->
                <div class="col-md-6 mb-3">
                    <label class="form-label required">Available for Night Shifts?</label>
                    <div class="form-check">
                        <input type="radio"
                               class="form-check-input"
                               id="night_shifts_yes"
                               name="available_for_night_shifts"
                               value="1"
                               {{ old('available_for_night_shifts', $driver->available_for_night_shifts) == '1' ? 'checked' : '' }}
                               required>
                        <label class="form-check-label" for="night_shifts_yes">Yes</label>
                    </div>
                    <div class="form-check">
                        <input type="radio"
                               class="form-check-input"
                               id="night_shifts_no"
                               name="available_for_night_shifts"
                               value="0"
                               {{ old('available_for_night_shifts', $driver->available_for_night_shifts) == '0' ? 'checked' : '' }}
                               required>
                        <label class="form-check-label" for="night_shifts_no">No</label>
                    </div>
                    @error('available_for_night_shifts')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label required">Available for Weekend Work?</label>
                    <div class="form-check">
                        <input type="radio"
                               class="form-check-input"
                               id="weekend_work_yes"
                               name="available_for_weekend_work"
                               value="1"
                               {{ old('available_for_weekend_work', $driver->available_for_weekend_work) == '1' ? 'checked' : '' }}
                               required>
                        <label class="form-check-label" for="weekend_work_yes">Yes</label>
                    </div>
                    <div class="form-check">
                        <input type="radio"
                               class="form-check-input"
                               id="weekend_work_no"
                               name="available_for_weekend_work"
                               value="0"
                               {{ old('available_for_weekend_work', $driver->available_for_weekend_work) == '0' ? 'checked' : '' }}
                               required>
                        <label class="form-check-label" for="weekend_work_no">No</label>
                    </div>
                    @error('available_for_weekend_work')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Special Skills -->
                <div class="col-md-12 mb-3">
                    <label for="special_skills" class="form-label">Special Skills or Certifications</label>
                    <textarea class="form-control @error('special_skills') is-invalid @enderror"
                              id="special_skills"
                              name="special_skills"
                              rows="3"
                              placeholder="List any special driving skills, certifications, or relevant experience (optional)">{{ old('special_skills', $driver->special_skills) }}</textarea>
                    @error('special_skills')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">e.g., Defensive driving, Commercial vehicle operation, etc.</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Document Upload Section -->
    <div class="step-card">
        <div class="step-card-header">
            <h6 class="mb-0">
                <i class="fas fa-cloud-upload-alt me-2"></i>
                Required Document Upload
            </h6>
        </div>
        <div class="step-card-body">
            <div class="alert alert-info border-start border-4 border-info mb-4">
                <div class="d-flex align-items-start">
                    <i class="fas fa-info-circle me-3 mt-1"></i>
                    <div>
                        <strong>Document Requirements:</strong>
                        <ul class="mb-0 mt-2">
                            <li>All documents must be clear and readable</li>
                            <li>Maximum file size: 2MB per document</li>
                            <li>Accepted formats: JPG, PNG, PDF</li>
                            <li>Documents must be recent and valid</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Driver License Scan -->
                <div class="col-md-6 mb-4">
                    <div class="document-upload-card">
                        <label for="driver_license_scan" class="form-label required">
                            <i class="fas fa-id-card-alt me-2"></i>
                            Driver's License Scan
                        </label>
                        <input type="file"
                               class="form-control @error('driver_license_scan') is-invalid @enderror"
                               id="driver_license_scan"
                               name="driver_license_scan"
                               accept=".jpg,.jpeg,.png,.pdf"
                               required>
                        @error('driver_license_scan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Upload a clear photo or scan of your driver's license</small>
                        <div class="file-preview mt-2" id="driver_license_scan_preview"></div>
                    </div>
                </div>

                <!-- National ID -->
                <div class="col-md-6 mb-4">
                    <div class="document-upload-card">
                        <label for="national_id" class="form-label required">
                            <i class="fas fa-id-badge me-2"></i>
                            National ID Card
                        </label>
                        <input type="file"
                               class="form-control @error('national_id') is-invalid @enderror"
                               id="national_id"
                               name="national_id"
                               accept=".jpg,.jpeg,.png,.pdf"
                               required>
                        @error('national_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Upload a clear photo or scan of your National ID</small>
                        <div class="file-preview mt-2" id="national_id_preview"></div>
                    </div>
                </div>

                <!-- Passport Photo -->
                <div class="col-md-6 mb-4">
                    <div class="document-upload-card">
                        <label for="passport_photo" class="form-label required">
                            <i class="fas fa-camera me-2"></i>
                            Passport Photograph
                        </label>
                        <input type="file"
                               class="form-control @error('passport_photo') is-invalid @enderror"
                               id="passport_photo"
                               name="passport_photo"
                               accept=".jpg,.jpeg,.png"
                               required>
                        @error('passport_photo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Upload a recent passport-style photograph</small>
                        <div class="file-preview mt-2" id="passport_photo_preview"></div>
                    </div>
                </div>

                <!-- Utility Bill (Optional) -->
                <div class="col-md-6 mb-4">
                    <div class="document-upload-card">
                        <label for="utility_bill" class="form-label">
                            <i class="fas fa-file-invoice me-2"></i>
                            Utility Bill <span class="text-muted">(Optional)</span>
                        </label>
                        <input type="file"
                               class="form-control @error('utility_bill') is-invalid @enderror"
                               id="utility_bill"
                               name="utility_bill"
                               accept=".jpg,.jpeg,.png,.pdf">
                        @error('utility_bill')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Recent utility bill for address verification (optional)</small>
                        <div class="file-preview mt-2" id="utility_bill_preview"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Terms and Conditions -->
    <div class="step-card">
        <div class="step-card-header">
            <h6 class="mb-0">
                <i class="fas fa-shield-alt me-2"></i>
                Terms and Conditions
            </h6>
        </div>
        <div class="step-card-body">
            <div class="form-check mb-3">
                <input type="checkbox"
                       class="form-check-input @error('terms_agreement') is-invalid @enderror"
                       id="terms_agreement"
                       name="terms_agreement"
                       value="1"
                       required>
                <label class="form-check-label" for="terms_agreement">
                    I agree to the <a href="#" target="_blank" class="fw-bold">Terms and Conditions</a> and
                    <a href="#" target="_blank" class="fw-bold">Privacy Policy</a>
                </label>
                @error('terms_agreement')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-check mb-3">
                <input type="checkbox"
                       class="form-check-input @error('data_accuracy') is-invalid @enderror"
                       id="data_accuracy"
                       name="data_accuracy"
                       value="1"
                       required>
                <label class="form-check-label" for="data_accuracy">
                    I confirm that all information provided is <strong>accurate and complete</strong> to the best of my knowledge
                </label>
                @error('data_accuracy')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <!-- Form Actions -->
    <div class="d-flex justify-content-between mt-4">
        <a href="{{ route('driver.kyc.step2') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Back to Step 2
        </a>

        <button type="submit" class="btn btn-kyc-primary" id="submitBtn">
            <i class="fas fa-paper-plane me-1"></i>
            Submit KYC Application
        </button>
    </div>
</form>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Employment status visibility
    const isWorkingInputs = document.querySelectorAll('input[name="is_working"]');
    const previousEmploymentDetails = document.getElementById('previous_employment_details');

    function togglePreviousEmploymentDetails() {
        const isWorking = document.querySelector('input[name="is_working"]:checked');
        if (isWorking && isWorking.value === '0') {
            previousEmploymentDetails.style.display = 'block';
            document.getElementById('previous_workplace').required = true;
            document.getElementById('previous_work_id_record').required = true;
            document.getElementById('reason_stopped_working').required = true;
        } else {
            previousEmploymentDetails.style.display = 'none';
            document.getElementById('previous_workplace').required = false;
            document.getElementById('previous_work_id_record').required = false;
            document.getElementById('reason_stopped_working').required = false;
        }
    }

    isWorkingInputs.forEach(input => {
        input.addEventListener('change', togglePreviousEmploymentDetails);
    });

    // Initialize on page load
    togglePreviousEmploymentDetails();

    // Vehicle details visibility
    const hasVehicleInputs = document.querySelectorAll('input[name="has_vehicle"]');
    const vehicleDetails = document.getElementById('vehicle_details');

    function toggleVehicleDetails() {
        const hasVehicle = document.querySelector('input[name="has_vehicle"]:checked');
        if (hasVehicle && hasVehicle.value === '1') {
            vehicleDetails.style.display = 'block';
            document.getElementById('vehicle_type').required = true;
            document.getElementById('vehicle_year').required = true;
        } else {
            vehicleDetails.style.display = 'none';
            document.getElementById('vehicle_type').required = false;
            document.getElementById('vehicle_year').required = false;
        }
    }

    hasVehicleInputs.forEach(input => {
        input.addEventListener('change', toggleVehicleDetails);
    });

    // Initialize on page load
    toggleVehicleDetails();

    // File upload handling with preview
    const fileInputs = ['driver_license_scan', 'national_id', 'passport_photo', 'utility_bill'];

    fileInputs.forEach(inputId => {
        const input = document.getElementById(inputId);
        const preview = document.getElementById(inputId + '_preview');

        input.addEventListener('change', function() {
            handleFileUpload(this, preview);
        });
    });

    function handleFileUpload(input, preview) {
        if (input.files && input.files[0]) {
            const file = input.files[0];

            // Validate file size (2MB)
            if (file.size > 2 * 1024 * 1024) {
                alert('File size must be less than 2MB');
                input.value = '';
                preview.innerHTML = '';
                return;
            }

            // Validate file type
            const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
            if (!validTypes.includes(file.type)) {
                alert('Please upload only JPG, PNG, or PDF files');
                input.value = '';
                preview.innerHTML = '';
                return;
            }

            // Show preview
            const fileName = file.name;
            const fileSize = (file.size / 1024 / 1024).toFixed(2);

            preview.innerHTML = `
                <div class="alert alert-success py-2">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>${fileName}</strong> (${fileSize} MB)
                    <button type="button" class="btn btn-sm btn-outline-danger ms-2" onclick="clearFile('${input.id}')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
        }
    }

    // Clear file function
    window.clearFile = function(inputId) {
        const input = document.getElementById(inputId);
        const preview = document.getElementById(inputId + '_preview');
        input.value = '';
        preview.innerHTML = '';
    };

    // Form validation
    const form = document.querySelector('.needs-validation');
    const submitBtn = document.getElementById('submitBtn');

    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
            showToast('Please fill in all required fields and upload required documents', 'error');
        } else {
            // Show confirmation dialog
            const confirmed = confirm(
                'Are you sure you want to submit your KYC application?\n\n' +
                'Please make sure all information is accurate as you cannot make changes after submission.\n\n' +
                'Click OK to proceed.'
            );

            if (!confirmed) {
                event.preventDefault();
                return;
            }

            // Show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Submitting...';
        }

        form.classList.add('was-validated');
    });

    // Real-time form validation
    const requiredInputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    requiredInputs.forEach(input => {
        input.addEventListener('change', function() {
            if (this.checkValidity()) {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            } else {
                this.classList.remove('is-valid');
                this.classList.add('is-invalid');
            }
        });
    });
});

function showToast(message, type = 'info') {
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

<style>
.document-upload-card {
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    padding: 1rem;
    background-color: #f8f9fa;
}

.document-upload-card:hover {
    border-color: var(--drivelink-primary);
    background-color: #fff;
}

.file-preview .alert {
    border-radius: 0.25rem;
    font-size: 0.875rem;
}

.step-progress .step-item.completed .step-number {
    background-color: var(--drivelink-success);
    color: white;
}

.step-progress .step-item.active .step-number {
    background-color: var(--drivelink-primary);
    color: white;
}

.progress-line.completed {
    background-color: var(--drivelink-success);
}
</style>
@endsection
