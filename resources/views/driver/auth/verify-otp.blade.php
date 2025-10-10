@extends('drivers.layouts.app')

@section('title', 'Verify Contact Information')
@section('page_title', 'Verify Your Contact Information')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('driver.login') }}">Login</a></li>
    <li class="breadcrumb-item active">Verify OTP</li>
@endsection

@section('styles')
<style>
    .otp-input-group {
        display: flex;
        justify-content: center;
        gap: 10px;
        margin: 20px 0;
    }

    .otp-input {
        width: 50px;
        height: 50px;
        border: 2px solid #dee2e6;
        border-radius: 8px;
        text-align: center;
        font-size: 24px;
        font-weight: bold;
        transition: border-color 0.3s ease;
    }

    .otp-input:focus {
        outline: none;
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    .otp-input.filled {
        border-color: #28a745;
    }

    .verification-card {
        max-width: 500px;
        margin: 0 auto;
    }

    .verification-method {
        display: none;
    }

    .verification-method.active {
        display: block;
    }

    .method-tabs {
        display: flex;
        justify-content: center;
        margin-bottom: 30px;
    }

    .method-tab {
        padding: 10px 20px;
        border: 2px solid #dee2e6;
        background: white;
        cursor: pointer;
        border-radius: 8px 8px 0 0;
        transition: all 0.3s ease;
    }

    .method-tab.active {
        border-color: #007bff;
        background: #007bff;
        color: white;
    }

    .method-tab:not(.active):hover {
        border-color: #007bff;
        color: #007bff;
    }

    @media (max-width: 576px) {
        .otp-input-group {
            gap: 5px;
        }

        .otp-input {
            width: 40px;
            height: 40px;
            font-size: 20px;
        }
    }
</style>
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card verification-card shadow">
                <div class="card-header bg-primary text-white text-center">
                    <h4 class="mb-0">
                        <i class="fas fa-shield-alt me-2"></i>
                        Verify Your Contact Information
                    </h4>
                </div>

                <div class="card-body p-4">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="text-center mb-4">
                        <p class="text-muted">
                            We've sent verification codes to your phone and email.
                            Please enter the codes below to verify your contact information.
                        </p>
                    </div>

                    <!-- Method Tabs -->
                    <div class="method-tabs">
                        <div class="method-tab {{ old('verification_type', 'sms') === 'sms' ? 'active' : '' }}"
                             onclick="switchMethod('sms')">
                            <i class="fas fa-sms me-1"></i>
                            SMS Code
                        </div>
                        <div class="method-tab {{ old('verification_type', 'email') === 'email' ? 'active' : '' }}"
                             onclick="switchMethod('email')">
                            <i class="fas fa-envelope me-1"></i>
                            Email Code
                        </div>
                    </div>

                    <form id="otpForm" action="{{ route('driver.verify-otp.submit') }}" method="POST">
                        @csrf
                        <input type="hidden" name="verification_type" id="verification_type" value="{{ old('verification_type', 'sms') }}">

                        <!-- SMS Verification -->
                        <div class="verification-method {{ old('verification_type', 'sms') === 'sms' ? 'active' : '' }}" id="sms-method">
                            <div class="text-center mb-3">
                                <h5>SMS Verification</h5>
                                <p class="text-muted small">
                                    Enter the 6-digit code sent to <strong>{{ $driver->phone }}</strong>
                                </p>
                            </div>

                            <div class="otp-input-group">
                                <input type="text" class="otp-input" name="otp_1" id="otp_1" maxlength="1" pattern="[0-9]" autocomplete="off" required>
                                <input type="text" class="otp-input" name="otp_2" id="otp_2" maxlength="1" pattern="[0-9]" autocomplete="off" required>
                                <input type="text" class="otp-input" name="otp_3" id="otp_3" maxlength="1" pattern="[0-9]" autocomplete="off" required>
                                <input type="text" class="otp-input" name="otp_4" id="otp_4" maxlength="1" pattern="[0-9]" autocomplete="off" required>
                                <input type="text" class="otp-input" name="otp_5" id="otp_5" maxlength="1" pattern="[0-9]" autocomplete="off" required>
                                <input type="text" class="otp-input" name="otp_6" id="otp_6" maxlength="1" pattern="[0-9]" autocomplete="off" required>
                            </div>
                            <input type="hidden" name="otp" id="sms_otp_code">
                        </div>

                        <!-- Email Verification -->
                        <div class="verification-method {{ old('verification_type', 'email') === 'email' ? 'active' : '' }}" id="email-method">
                            <div class="text-center mb-3">
                                <h5>Email Verification</h5>
                                <p class="text-muted small">
                                    Enter the 6-digit code sent to <strong>{{ $driver->email }}</strong>
                                </p>
                            </div>

                            <div class="otp-input-group">
                                <input type="text" class="otp-input email-otp" name="email_otp_1" id="email_otp_1" maxlength="1" pattern="[0-9]" autocomplete="off" required>
                                <input type="text" class="otp-input email-otp" name="email_otp_2" id="email_otp_2" maxlength="1" pattern="[0-9]" autocomplete="off" required>
                                <input type="text" class="otp-input email-otp" name="email_otp_3" id="email_otp_3" maxlength="1" pattern="[0-9]" autocomplete="off" required>
                                <input type="text" class="otp-input email-otp" name="email_otp_4" id="email_otp_4" maxlength="1" pattern="[0-9]" autocomplete="off" required>
                                <input type="text" class="otp-input email-otp" name="email_otp_5" id="email_otp_5" maxlength="1" pattern="[0-9]" autocomplete="off" required>
                                <input type="text" class="otp-input email-otp" name="email_otp_6" id="email_otp_6" maxlength="1" pattern="[0-9]" autocomplete="off" required>
                            </div>
                            <input type="hidden" name="email_otp" id="email_otp_code">
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-primary btn-lg" id="verifyBtn">
                                <i class="fas fa-check-circle me-2"></i>
                                Verify Code
                            </button>

                            <button type="button" class="btn btn-outline-secondary" id="resendBtn" onclick="resendOTP()">
                                <i class="fas fa-redo me-2"></i>
                                Resend Code
                            </button>
                        </div>
                    </form>

                    <!-- Status Messages -->
                    <div id="statusMessage" class="mt-3" style="display: none;"></div>
                </div>

                <div class="card-footer text-center text-muted">
                    <small>
                        <i class="fas fa-info-circle me-1"></i>
                        Codes expire in 5 minutes. Need help? <a href="{{ route('driver.support') }}">Contact Support</a>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let currentMethod = '{{ old('verification_type', 'sms') }}';
let resendCooldown = 0;
let cooldownInterval;

document.addEventListener('DOMContentLoaded', function() {
    // Initialize OTP inputs
    initializeOTPInputs();

    // Set initial method
    switchMethod(currentMethod);

    // Start cooldown if needed
    @if(isset($otpStatus))
        @if($otpStatus['sms_cooldown'] > 0)
            startCooldown('sms', {{ $otpStatus['sms_cooldown'] }});
        @endif
        @if($otpStatus['email_cooldown'] > 0)
            startCooldown('email', {{ $otpStatus['email_cooldown'] }});
        @endif
    @endif
});

function initializeOTPInputs() {
    // Handle input events for OTP fields
    document.querySelectorAll('.otp-input').forEach((input, index) => {
        input.addEventListener('input', function(e) {
            // Only allow numbers
            this.value = this.value.replace(/[^0-9]/g, '');

            // Mark as filled
            if (this.value) {
                this.classList.add('filled');
            } else {
                this.classList.remove('filled');
            }

            // Auto-focus next input
            if (this.value && index < 5) {
                const nextInput = this.parentElement.children[index + 1];
                if (nextInput) nextInput.focus();
            }

            // Update hidden input
            updateHiddenOTP();
        });

        input.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace' && !this.value && index > 0) {
                // Focus previous input on backspace
                const prevInput = this.parentElement.children[index - 1];
                if (prevInput) prevInput.focus();
            }
        });

        input.addEventListener('paste', function(e) {
            e.preventDefault();
            const paste = (e.clipboardData || window.clipboardData).getData('text');
            const digits = paste.replace(/[^0-9]/g, '').slice(0, 6);

            // Fill inputs with pasted digits
            for (let i = 0; i < digits.length; i++) {
                if (this.parentElement.children[index + i]) {
                    this.parentElement.children[index + i].value = digits[i];
                    this.parentElement.children[index + i].classList.add('filled');
                }
            }

            updateHiddenOTP();

            // Focus last filled input or next available
            const nextIndex = Math.min(index + digits.length, 5);
            const nextInput = this.parentElement.children[nextIndex];
            if (nextInput) nextInput.focus();
        });
    });
}

function switchMethod(method) {
    currentMethod = method;

    // Update tabs
    document.querySelectorAll('.method-tab').forEach(tab => {
        tab.classList.remove('active');
    });
    document.querySelector(`[onclick="switchMethod('${method}')"]`).classList.add('active');

    // Update hidden input
    document.getElementById('verification_type').value = method;

    // Show/hide methods
    document.querySelectorAll('.verification-method').forEach(el => {
        el.classList.remove('active');
    });
    document.getElementById(`${method}-method`).classList.add('active');

    // Clear inputs when switching
    document.querySelectorAll('.otp-input').forEach(input => {
        input.value = '';
        input.classList.remove('filled');
    });
    updateHiddenOTP();

    // Update resend button state
    updateResendButton();
}

function updateHiddenOTP() {
    let otp = '';
    const inputs = currentMethod === 'sms' ?
        document.querySelectorAll('.otp-input:not(.email-otp)') :
        document.querySelectorAll('.email-otp');

    inputs.forEach(input => {
        otp += input.value || '';
    });

    const hiddenInput = document.getElementById(`${currentMethod}_otp_code`);
    if (hiddenInput) {
        hiddenInput.value = otp;
    }

    // Enable/disable verify button
    const verifyBtn = document.getElementById('verifyBtn');
    verifyBtn.disabled = otp.length !== 6;
}

function updateResendButton() {
    const resendBtn = document.getElementById('resendBtn');
    const cooldownText = resendBtn.querySelector('.cooldown-text');

    if (resendCooldown > 0) {
        resendBtn.disabled = true;
        resendBtn.innerHTML = `<i class="fas fa-clock me-2"></i>Wait ${resendCooldown}s`;
    } else {
        resendBtn.disabled = false;
        resendBtn.innerHTML = `<i class="fas fa-redo me-2"></i>Resend Code`;
    }
}

function resendOTP() {
    const resendBtn = document.getElementById('resendBtn');
    const originalHTML = resendBtn.innerHTML;

    // Disable button and show loading
    resendBtn.disabled = true;
    resendBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';

    // Send AJAX request
    fetch('{{ route("driver.resend-otp") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            verification_type: currentMethod
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage('OTP sent successfully!', 'success');
            if (data.expires_in) {
                startCooldown(currentMethod, 60); // 1 minute cooldown
            }
        } else {
            showMessage(data.message || 'Failed to send OTP', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showMessage('Network error. Please try again.', 'error');
    })
    .finally(() => {
        resendBtn.innerHTML = originalHTML;
        updateResendButton();
    });
}

function startCooldown(method, seconds) {
    resendCooldown = seconds;
    updateResendButton();

    cooldownInterval = setInterval(() => {
        resendCooldown--;
        updateResendButton();

        if (resendCooldown <= 0) {
            clearInterval(cooldownInterval);
        }
    }, 1000);
}

function showMessage(message, type) {
    const statusDiv = document.getElementById('statusMessage');
    statusDiv.style.display = 'block';
    statusDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} mt-3`;
    statusDiv.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>${message}`;

    // Auto-hide after 5 seconds
    setTimeout(() => {
        statusDiv.style.display = 'none';
    }, 5000);
}

// Form submission handling
document.getElementById('otpForm').addEventListener('submit', function(e) {
    const verifyBtn = document.getElementById('verifyBtn');
    const originalHTML = verifyBtn.innerHTML;

    verifyBtn.disabled = true;
    verifyBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Verifying...';

    // Re-enable after 10 seconds in case of error
    setTimeout(() => {
        verifyBtn.disabled = false;
        verifyBtn.innerHTML = originalHTML;
    }, 10000);
});
</script>
@endsection
