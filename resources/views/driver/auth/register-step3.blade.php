@extends('layouts.driver-kyc')

@section('title', 'Driver Registration - Step 3: Facial Recognition')
@section('page-title', 'Facial Recognition')
@section('page-description', 'Please capture your facial image for identity verification')

@php
    $currentStep = 3;
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
        <div class="step-item active">
            <div class="step-number">3</div>
            <div class="step-title">Face ID</div>
        </div>
        <div class="progress-line"></div>
        <div class="step-item">
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
                <i class="fas fa-camera me-2" style="color: var(--drivelink-primary);"></i>
                Facial Recognition Capture
            </h5>
            <p class="mb-0 text-muted">
                Please ensure your face is clearly visible, well-lit, and centered in the frame.
            </p>
        </div>
        <div class="col-md-4 text-end">
            <span class="badge bg-primary px-3 py-2">
                <i class="fas fa-camera me-1"></i>
                Step 3 of 4
            </span>
        </div>
    </div>
</div>

<!-- Facial Capture Form -->
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Capture Your Facial Image</h5>
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

                <form method="POST" action="{{ route('driver.register.step3.submit') }}" enctype="multipart/form-data" id="facialForm">
                    @csrf

                    <!-- Camera Instructions -->
                    <div class="alert alert-info mb-4">
                        <h6><i class="fas fa-info-circle me-2"></i>Instructions:</h6>
                        <ul class="mb-0">
                            <li>Ensure good lighting and remove glasses if possible</li>
                            <li>Look directly at the camera with a neutral expression</li>
                            <li>Keep your face centered and clearly visible</li>
                            <li>Avoid hats, masks, or heavy makeup</li>
                        </ul>
                    </div>

                    <!-- Camera/Capture Area -->
                    <div class="text-center mb-4">
                        <div class="camera-container mb-3">
                            <video id="camera" autoplay playsinline width="320" height="240" class="border rounded shadow-sm d-none"></video>
                            <canvas id="snapshot" width="320" height="240" class="border rounded shadow-sm d-none"></canvas>
                            <div id="placeholder" class="camera-placeholder">
                                <i class="fas fa-camera fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Camera will appear here</p>
                            </div>
                        </div>

                        <!-- Control Buttons -->
                        <div class="btn-group mb-3" role="group">
                            <button type="button" class="btn btn-outline-primary" id="startCameraBtn">
                                <i class="fas fa-play me-1"></i>
                                Start Camera
                            </button>
                            <button type="button" class="btn btn-outline-success" id="captureBtn" disabled>
                                <i class="fas fa-camera me-1"></i>
                                Capture
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="retakeBtn" disabled>
                                <i class="fas fa-redo me-1"></i>
                                Retake
                            </button>
                        </div>

                        <!-- File Upload Alternative -->
                        <div class="mb-3">
                            <label class="form-label text-muted">Or upload an existing photo:</label>
                            <input type="file" name="facial_image" id="facialImage" class="form-control" accept="image/*" capture="user">
                            <small class="form-text text-muted">Supported formats: JPEG, PNG, JPG (Max: 2MB)</small>
                        </div>
                    </div>

                    <!-- Hidden canvas for image data -->
                    <input type="hidden" name="facial_data" id="facialData">

                    <!-- Submit Button -->
                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-primary btn-lg" id="submitBtn" disabled>
                            <i class="fas fa-arrow-right me-1"></i>
                            Continue to Documents
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Navigation -->
        <div class="d-flex justify-content-between mt-4">
            <a href="{{ route('driver.register.step2') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>
                Back to Step 2
            </a>
            <div></div> <!-- Spacer -->
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const video = document.getElementById('camera');
    const snapshot = document.getElementById('snapshot');
    const placeholder = document.getElementById('placeholder');
    const startCameraBtn = document.getElementById('startCameraBtn');
    const captureBtn = document.getElementById('captureBtn');
    const retakeBtn = document.getElementById('retakeBtn');
    const submitBtn = document.getElementById('submitBtn');
    const facialImageInput = document.getElementById('facialImage');
    const facialDataInput = document.getElementById('facialData');
    const form = document.getElementById('facialForm');

    let stream = null;

    // Start camera
    startCameraBtn.addEventListener('click', async function() {
        try {
            stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    width: 320,
                    height: 240,
                    facingMode: 'user'
                }
            });

            video.srcObject = stream;
            video.classList.remove('d-none');
            placeholder.classList.add('d-none');
            snapshot.classList.add('d-none');

            startCameraBtn.disabled = true;
            captureBtn.disabled = false;

            showToast('Camera started successfully', 'success');
        } catch (error) {
            console.error('Error accessing camera:', error);
            showToast('Unable to access camera. Please check permissions or use file upload.', 'error');
        }
    });

    // Capture image
    captureBtn.addEventListener('click', function() {
        const context = snapshot.getContext('2d');
        context.drawImage(video, 0, 0, 320, 240);

        // Convert to base64
        const imageData = snapshot.toDataURL('image/jpeg', 0.8);
        facialDataInput.value = imageData;

        // Show captured image
        video.classList.add('d-none');
        snapshot.classList.remove('d-none');
        placeholder.classList.add('d-none');

        captureBtn.disabled = true;
        retakeBtn.disabled = false;
        submitBtn.disabled = false;

        showToast('Image captured successfully', 'success');
    });

    // Retake image
    retakeBtn.addEventListener('click', function() {
        snapshot.classList.add('d-none');
        video.classList.remove('d-none');

        facialDataInput.value = '';
        facialImageInput.value = '';

        captureBtn.disabled = false;
        retakeBtn.disabled = true;
        submitBtn.disabled = true;
    });

    // File upload handling
    facialImageInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const file = this.files[0];

            // Validate file size (2MB max)
            if (file.size > 2 * 1024 * 1024) {
                showToast('File size must be less than 2MB', 'error');
                this.value = '';
                return;
            }

            // Validate file type
            if (!file.type.match('image.*')) {
                showToast('Please select a valid image file', 'error');
                this.value = '';
                return;
            }

            submitBtn.disabled = false;
            showToast('Image selected successfully', 'success');
        }
    });

    // Form submission
    form.addEventListener('submit', function(e) {
        if (!facialDataInput.value && !facialImageInput.files[0]) {
            e.preventDefault();
            showToast('Please capture or upload a facial image', 'error');
            return;
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Processing...';
    });

    // Cleanup on page unload
    window.addEventListener('beforeunload', function() {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
        }
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

.camera-container {
    position: relative;
    display: inline-block;
}

.camera-placeholder {
    width: 320px;
    height: 240px;
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    background-color: #f8f9fa;
}

#camera, #snapshot {
    max-width: 100%;
    height: auto;
}

.btn-group .btn {
    margin-right: 5px;
}

.btn-group .btn:last-child {
    margin-right: 0;
}
</style>
@endsection
