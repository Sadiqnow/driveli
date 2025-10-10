@extends('layouts.driver-kyc')

@section('title', 'Driver Registration - Step 4: Document Upload')
@section('page-title', 'Document Upload')
@section('page-description', 'Upload required documents to complete your registration')

@php
    $currentStep = 4;
@endphp

@section('content')
<!-- Progress Indicator -->
<div class="step-progress mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div class="step-item completed">
            <div class="step-number"><i class="fas fa-check"></i></div>
            <div class="step-title">Basic Info</div>
        </div>
        <div class="progress-line completed"></div>
        <div class="step-item completed">
            <div class="step-number"><i class="fas fa-check"></i></div>
            <div class="step-title">Verify</div>
        </div>
        <div class="progress-line completed"></div>
        <div class="step-item completed">
            <div class="step-number"><i class="fas fa-check"></i></div>
            <div class="step-title">Face ID</div>
        </div>
        <div class="progress-line completed"></div>
        <div class="step-item active">
            <div class="step-number">4</div>
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
                Upload Required Documents
            </h5>
            <p class="mb-0 text-muted">
                Please upload clear, readable copies of your identification documents. All documents are required for verification.
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

<!-- Document Upload Form -->
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Required Documents</h5>
            </div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <!-- Document Requirements Alert -->
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

                <form method="POST" action="{{ route('driver.register.step4.submit') }}" enctype="multipart/form-data" id="documentForm">
                    @csrf

                    <div class="row">
                        <!-- Driver's License Scan -->
                        <div class="col-md-4 mb-4">
                            <div class="document-upload-card">
                                <div class="document-icon">
                                    <i class="fas fa-id-card-alt fa-2x"></i>
                                </div>
                                <h6 class="document-title">Driver's License</h6>
                                <p class="document-description">Upload a clear photo or scan of your driver's license</p>

                                <input type="file"
                                       class="form-control @error('license_scan') is-invalid @enderror"
                                       id="license_scan"
                                       name="license_scan"
                                       accept=".jpg,.jpeg,.png,.pdf"
                                       required>
                                @error('license_scan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                                <div class="file-preview mt-2" id="license_scan_preview"></div>
                            </div>
                        </div>

                        <!-- National ID Card -->
                        <div class="col-md-4 mb-4">
                            <div class="document-upload-card">
                                <div class="document-icon">
                                    <i class="fas fa-id-badge fa-2x"></i>
                                </div>
                                <h6 class="document-title">National ID Card</h6>
                                <p class="document-description">Upload a clear photo or scan of your National ID</p>

                                <input type="file"
                                       class="form-control @error('national_id') is-invalid @enderror"
                                       id="national_id"
                                       name="national_id"
                                       accept=".jpg,.jpeg,.png,.pdf"
                                       required>
                                @error('national_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                                <div class="file-preview mt-2" id="national_id_preview"></div>
                            </div>
                        </div>

                        <!-- Passport Photograph -->
                        <div class="col-md-4 mb-4">
                            <div class="document-upload-card">
                                <div class="document-icon">
                                    <i class="fas fa-camera fa-2x"></i>
                                </div>
                                <h6 class="document-title">Passport Photo</h6>
                                <p class="document-description">Upload a recent passport-style photograph</p>

                                <input type="file"
                                       class="form-control @error('passport_photo') is-invalid @enderror"
                                       id="passport_photo"
                                       name="passport_photo"
                                       accept=".jpg,.jpeg,.png"
                                       required>
                                @error('passport_photo')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror

                                <div class="file-preview mt-2" id="passport_photo_preview"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Terms and Conditions -->
                    <div class="terms-section mt-4 p-3 bg-light rounded">
                        <h6 class="mb-3">
                            <i class="fas fa-shield-alt me-2"></i>
                            Terms and Conditions
                        </h6>

                        <div class="form-check mb-3">
                            <input type="checkbox"
                                   class="form-check-input @error('terms') is-invalid @enderror"
                                   id="terms"
                                   name="terms"
                                   value="1"
                                   required>
                            <label class="form-check-label" for="terms">
                                I agree to the <a href="{{ route('terms') }}" target="_blank" class="fw-bold">Terms and Conditions</a> and
                                <a href="{{ route('privacy') }}" target="_blank" class="fw-bold">Privacy Policy</a>
                            </label>
                            @error('terms')
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

                    <!-- Submit Button -->
                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                            <i class="fas fa-check-circle me-1"></i>
                            Complete Registration
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Navigation -->
        <div class="d-flex justify-content-between mt-4">
            <a href="{{ route('driver.register.step3') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>
                Back to Step 3
            </a>
            <div></div> <!-- Spacer -->
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInputs = ['license_scan', 'national_id', 'passport_photo'];
    const submitBtn = document.getElementById('submitBtn');
    const form = document.getElementById('documentForm');

    // File upload handling with preview
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
                showToast('File size must be less than 2MB', 'error');
                input.value = '';
                preview.innerHTML = '';
                return;
            }

            // Validate file type
            const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
            if (!validTypes.includes(file.type)) {
                showToast('Please upload only JPG, PNG, or PDF files', 'error');
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

            // Check if all required files are uploaded
            checkAllFilesUploaded();
        }
    }

    // Clear file function
    window.clearFile = function(inputId) {
        const input = document.getElementById(inputId);
        const preview = document.getElementById(inputId + '_preview');
        input.value = '';
        preview.innerHTML = '';
        checkAllFilesUploaded();
    };

    function checkAllFilesUploaded() {
        const allUploaded = fileInputs.every(id => document.getElementById(id).files[0]);
        const termsChecked = document.getElementById('terms').checked;
        const dataAccuracyChecked = document.getElementById('data_accuracy').checked;

        submitBtn.disabled = !(allUploaded && termsChecked && dataAccuracyChecked);
    }

    // Terms checkbox handling
    document.getElementById('terms').addEventListener('change', checkAllFilesUploaded);
    document.getElementById('data_accuracy').addEventListener('change', checkAllFilesUploaded);

    // Form submission
    form.addEventListener('submit', function(e) {
        if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
            showToast('Please upload all required documents and accept the terms', 'error');
            return;
        }

        // Show confirmation dialog
        const confirmed = confirm(
            'Are you sure you want to complete your registration?\n\n' +
            'Please make sure all documents are correct and information is accurate.\n\n' +
            'Click OK to complete registration.'
        );

        if (!confirmed) {
            e.preventDefault();
            return;
        }

        // Show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Completing Registration...';
    });
});

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'error' ? 'danger' : type} position-fixed`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 1060; min-width: 300px;';
    toast.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas fa-${type === 'error' ? 'exclamation-circle' : 'check-circle'} me-2"></i>
            <span>${message}</span>
    `;

    document.body.appendChild(toast);

    setTimeout(() => {
        toast.remove();
    }, 5000);
}
</script>

<style>
/* Progress Indicator Styles */
.step-progress {
    max-width: 600px;
    margin: 0 auto;
}

.step-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
}

.step-number {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 16px;
    margin-bottom: 8px;
    transition: all 0.3s ease;
}

.step-item.completed .step-number {
    background-color: #28a745;
    color: white;
}

.step-item.active .step-number {
    background-color: #007bff;
    color: white;
}

.step-item:not(.active):not(.completed) .step-number {
    background-color: #e9ecef;
    color: #6c757d;
}

.step-title {
    font-size: 12px;
    font-weight: 500;
    color: #6c757d;
    text-align: center;
}

.step-item.active .step-title {
    color: #007bff;
    font-weight: 600;
}

.step-item.completed .step-title {
    color: #28a745;
    font-weight: 600;
}

.progress-line {
    flex: 1;
    height: 2px;
    background-color: #e9ecef;
    margin: 0 10px;
    margin-top: -20px;
}

.progress-line.completed {
    background-color: #28a745;
}

.document-upload-card {
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    padding: 2rem 1rem;
    text-align: center;
    background-color: #f8f9fa;
    transition: all 0.3s ease;
    height: 100%;
    min-height: 280px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.document-upload-card:hover {
    border-color: #007bff;
    background-color: #fff;
}

.document-icon {
    color: #6c757d;
    margin-bottom: 1rem;
}

.document-upload-card:hover .document-icon {
    color: #007bff;
}

.document-title {
    color: #495057;
    margin-bottom: 0.5rem;
    font-weight: 600;
}

.document-description {
    color: #6c757d;
    font-size: 0.875rem;
    margin-bottom: 1rem;
}

.document-upload-card input[type="file"] {
    margin-bottom: 1rem;
}

.file-preview .alert {
    border-radius: 4px;
    font-size: 0.875rem;
    padding: 0.5rem;
}

.terms-section {
    border: 1px solid #dee2e6;
}

.form-check-label {
    font-size: 0.875rem;
    line-height: 1.4;
}
</style>
@endsection
