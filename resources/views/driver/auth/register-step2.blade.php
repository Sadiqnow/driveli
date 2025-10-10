@extends('layouts.driver-kyc')

@section('title', 'Driver Registration - Step 2: Verify Contact')
@section('page-title', 'Contact Verification')
@section('page-description', 'Please verify your phone number and email address')

@php
    $currentStep = 2;
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
        <div class="step-item active">
            <div class="step-number">2</div>
            <div class="step-title">Verify</div>
        </div>
        <div class="progress-line"></div>
        <div class="step-item">
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
                <i class="fas fa-shield-alt me-2" style="color: var(--drivelink-primary);"></i>
                Verify Your Contact Information
            </h5>
            <p class="mb-0 text-muted">
                We've sent verification codes to your phone and email. Please enter the codes below to continue.
            </p>
        </div>
        <div class="col-md-4 text-end">
            <span class="badge bg-primary px-3 py-2">
                <i class="fas fa-mobile-alt me-1"></i>
                Step 2 of 4
            </span>
        </div>
    </div>
</div>

<!-- OTP Verification Form -->
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Enter Verification Codes</h5>
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

                <form method="POST" action="{{ route('driver.register.step2.submit') }}" id="otpForm">
                    @csrf

                    <!-- Demo Notice -->
                    <div class="alert alert-info mb-4">
                        <h6><i class="fas fa-info-circle me-2"></i>Demo Mode</h6>
                        <p class="mb-1">For testing purposes, use OTP code: <strong>123456</strong></p>
                        <small class="text-muted">In production, actual OTP codes will be sent to your phone and email.</small>
                    </div>

                    <!-- Verification Method Tabs -->
                    <div class="verification-tabs mb-4">
                        <div class="method-tab active" onclick="switchMethod('sms')">
                            <i class="fas fa-sms me-2"></i>
                            SMS Verification
                        </div>
                        <div class="method-tab" onclick="switchMethod('email')">
                            <i class="fas fa-envelope me-2"></i>
                            Email Verification
                        </div>
                    </div>

                    <!-- Hidden verification type -->
                    <input type="hidden" name="verification_type" id="verification_type" value="sms">

                    <!-- SMS Verification -->
                    <div class="verification-method active" id="sms-method">
                        <div class="text-center mb-3">
                            <i class="fas fa-mobile-alt fa-2x text-primary mb-2"></i>
                            <h6>SMS Code Sent</h6>
                            <p class="text-muted small">We've sent a 6-digit code to<br><strong>{{ $registrationData['phone'] ?? 'your phone' }}</strong></p>
                        </div>

                        <div class="mb-3">
                            <label for="sms_otp" class="form-label">Enter SMS Code</label>
                            <input type="text" class="form-control form-control-lg text-center otp-input"
                                   id="sms_otp" name="otp" maxlength="6"
                                   placeholder="000000" pattern="[0-9]{6}" required>
                            <div class="form-text text-center">
                                <small>Enter the 6-digit code sent to your phone</small>
                            </div>
                        </div>

                        <div class="text-center mb-3">
                            <button type="button" class="btn btn-link btn-sm" id="resendSmsBtn" onclick="resendOtp('sms')">
                                <i class="fas fa-redo me-1"></i>
                                Resend SMS Code
                            </button>
                        </div>
                    </div>

                    <!-- Email Verification -->
                    <div class="verification-method" id="email-method">
                        <div class="text-center mb-3">
                            <i class="fas fa-envelope fa-2x text-primary mb-2"></i>
                            <h6>Email Code Sent</h6>
                            <p class="text-muted small">We've sent a 6-digit code to<br><strong>{{ $registrationData['email'] ?? 'your email' }}</strong></p>
                        </div>

                        <div class="mb-3">
                            <label for="email_otp" class="form-label">Enter Email Code</label>
                            <input type="text" class="form-control form-control-lg text-center otp-input"
                                   id="email_otp" name="otp" maxlength="6"
                                   placeholder="000000" pattern="[0-9]{6}" required>
                            <div class="form-text text-center">
                                <small>Enter the 6-digit code sent to your email</small>
                            </div>
                        </div>

                        <div class="text-center mb-3">
                            <button type="button" class="btn btn-link btn-sm" id="resendEmailBtn" onclick="resendOtp('email')">
                                <i class="fas fa-redo me-1"></i>
                                Resend Email Code
                            </button>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-primary btn-lg" id="verifyBtn">
                            <i class="fas fa-check me-1"></i>
                            Verify & Continue
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Navigation -->
        <div class="d-flex justify-content-between mt-4">
            <a href="{{ route('driver.register') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>
                Back to Step 1
            </a>
            <div></div> <!-- Spacer -->
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentMethod = 'sms';
    let resendCooldown = 0;

    // Switch verification method
    window.switchMethod = function(method) {
        currentMethod = method;
        document.getElementById('verification_type').value = method;

        // Update tabs
        document.querySelectorAll('.method-tab').forEach(tab => tab.classList.remove('active'));
        event.target.closest('.method-tab').classList.add('active');

        // Update methods
        document.querySelectorAll('.verification-method').forEach(method => method.classList.remove('active'));
        document.getElementById(method + '-method').classList.add('active');

        // Clear OTP inputs
        document.getElementById('sms_otp').value = '';
        document.getElementById('email_otp').value = '';
    };

    // Auto-format OTP input
    document.querySelectorAll('.otp-input').forEach(input => {
        input.addEventListener('input', function() {
            // Remove non-numeric characters
            this.value = this.value.replace(/\D/g, '');

            // Auto-submit if 6 digits entered
            if (this.value.length === 6) {
                document.getElementById('otpForm').submit();
            }
        });

        input.addEventListener('paste', function(e) {
            e.preventDefault();
            const paste = (e.clipboardData || window.clipboardData).getData('text');
            const cleaned = paste.replace(/\D/g, '').substring(0, 6);
            this.value = cleaned;

            if (cleaned.length === 6) {
                document.getElementById('otpForm').submit();
            }
        });
    });

    // Resend OTP
    window.resendOtp = function(type) {
        if (resendCooldown > 0) {
            showToast('Please wait ' + resendCooldown + ' seconds before resending.', 'warning');
            return;
        }

        const btn = document.getElementById('resend' + type.charAt(0).toUpperCase() + type.slice(1) + 'Btn');
        const originalText = btn.innerHTML;

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Sending...';

        fetch('{{ route("driver.resend-otp") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                verification_type: type
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                startResendCooldown();
            } else {
                showToast(data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Failed to resend code. Please try again.', 'error');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    };

    // Form submission
    document.getElementById('otpForm').addEventListener('submit', function(e) {
        const submitBtn = document.getElementById('verifyBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Verifying...';
    });

    function startResendCooldown() {
        resendCooldown = 30;
        const interval = setInterval(() => {
            resendCooldown--;
            if (resendCooldown <= 0) {
                clearInterval(interval);
            }
        }, 1000);
    }

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
});
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

/* Verification Tabs */
.verification-tabs {
    display: flex;
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid #dee2e6;
}

.method-tab {
    flex: 1;
    padding: 12px 20px;
    text-align: center;
    background-color: #f8f9fa;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 500;
}

.method-tab:hover {
    background-color: #e9ecef;
}

.method-tab.active {
    background-color: #007bff;
    color: white;
}

.verification-method {
    display: none;
}

.verification-method.active {
    display: block;
}

/* OTP Input */
.otp-input {
    font-size: 24px;
    letter-spacing: 8px;
    font-weight: bold;
    text-align: center;
}

.otp-input:focus {
    box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25);
    border-color: #007bff;
}
</style>
@endsection
