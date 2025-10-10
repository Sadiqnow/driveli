@extends('layouts.admin_cdn')

@section('title', 'Verify Driver Contact Information')

@section('content_header', 'OTP Verification')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ url('admin/drivers') }}">Drivers</a></li>
    <li class="breadcrumb-item active">Verify OTP</li>
@endsection

@section('css')
<style>
.verification-container {
    max-width: 600px;
    margin: 0 auto;
}

.otp-input-group {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin: 20px 0;
}

.otp-input {
    width: 50px;
    height: 50px;
    text-align: center;
    font-size: 24px;
    font-weight: bold;
    border: 2px solid #dee2e6;
    border-radius: 8px;
    background: #f8f9fa;
}

.otp-input:focus {
    border-color: #007bff;
    background: white;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.otp-input.filled {
    border-color: #28a745;
    background: #f8fff9;
}

.contact-info-card {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    color: white;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 25px;
}

.verification-step {
    background: #f8f9fa;
    border-left: 4px solid #007bff;
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 5px;
}

.resend-timer {
    color: #6c757d;
    font-size: 0.9rem;
}

.resend-link {
    color: #007bff;
    cursor: pointer;
    text-decoration: underline;
}

.resend-link:hover {
    color: #0056b3;
}

.verification-type-tabs {
    display: flex;
    margin-bottom: 20px;
}

.verification-tab {
    flex: 1;
    padding: 10px 15px;
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    cursor: pointer;
    text-align: center;
    transition: all 0.3s ease;
}

.verification-tab:first-child {
    border-top-left-radius: 5px;
    border-bottom-left-radius: 5px;
}

.verification-tab:last-child {
    border-top-right-radius: 5px;
    border-bottom-right-radius: 5px;
    border-left: none;
}

.verification-tab.active {
    background: #007bff;
    color: white;
    border-color: #007bff;
}

.success-animation {
    text-align: center;
    color: #28a745;
}

.error-animation {
    text-align: center;
    color: #dc3545;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

.pulse {
    animation: pulse 2s infinite;
}
</style>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        @if ($errors->any())
            <div class="alert alert-danger">
                <h5><i class="fas fa-exclamation-triangle"></i> Verification Error:</h5>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            </div>
        @endif

        <!-- Driver Info Card -->
        <div class="contact-info-card">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h5><i class="fas fa-user-check"></i> Verifying Contact Information</h5>
                    <p class="mb-2"><strong>Driver:</strong> {{ $driver->first_name }} {{ $driver->surname }}</p>
                    <p class="mb-2"><strong>Mobile:</strong> {{ $driver->phone }}</p>
                    <p class="mb-0"><strong>Email:</strong> {{ $driver->email }}</p>
                </div>
                <div class="col-md-4 text-right">
                    <i class="fas fa-shield-alt fa-3x pulse"></i>
                </div>
            </div>
        </div>

        <!-- Step Indicator -->
        <div class="verification-step">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="mb-1"><i class="fas fa-mobile-alt"></i> Step 2: Contact Verification</h6>
                    <small class="text-muted">Verify mobile number and email before proceeding to full registration</small>
                </div>
                <div class="text-muted">
                    <small>Next: Complete Registration</small>
                </div>
            </div>
        </div>

        <div class="card verification-container">
            <div class="card-header">
                <h4 class="card-title mb-0">
                    <i class="fas fa-key"></i> Enter Verification Code
                </h4>
            </div>

            <!-- Verification Type Tabs -->
            <div class="card-body">
                <div class="verification-type-tabs">
                    <div class="verification-tab active" id="sms-tab" onclick="switchVerificationType('sms')">
                        <i class="fas fa-sms"></i> SMS Verification
                    </div>
                    <div class="verification-tab" id="email-tab" onclick="switchVerificationType('email')">
                        <i class="fas fa-envelope"></i> Email Verification
                    </div>
                </div>

                <form id="otpVerificationForm" action="{{ route('admin.drivers.verify-otp', $driver->id) }}" method="POST">
                    @csrf
                    
                    <!-- SMS Verification Section -->
                    <div id="sms-verification" class="verification-section">
                        <div class="text-center mb-3">
                            <i class="fas fa-mobile-alt fa-2x text-primary mb-2"></i>
                            <p class="mb-1">We've sent a 6-digit code to:</p>
                            <strong class="text-primary">{{ $driver->phone }}</strong>
                        </div>
                        
                        <input type="hidden" name="verification_type" value="sms" id="verification_type">
                        
                        <div class="form-group">
                            <label class="text-center d-block mb-2">Enter SMS Code</label>
                            <div class="otp-input-group">
                                <input type="text" class="otp-input" name="otp_1" id="otp_1" maxlength="1" pattern="[0-9]" autocomplete="off">
                                <input type="text" class="otp-input" name="otp_2" id="otp_2" maxlength="1" pattern="[0-9]" autocomplete="off">
                                <input type="text" class="otp-input" name="otp_3" id="otp_3" maxlength="1" pattern="[0-9]" autocomplete="off">
                                <input type="text" class="otp-input" name="otp_4" id="otp_4" maxlength="1" pattern="[0-9]" autocomplete="off">
                                <input type="text" class="otp-input" name="otp_5" id="otp_5" maxlength="1" pattern="[0-9]" autocomplete="off">
                                <input type="text" class="otp-input" name="otp_6" id="otp_6" maxlength="1" pattern="[0-9]" autocomplete="off">
                            </div>
                            <input type="hidden" name="otp_code" id="otp_code">
                        </div>
                    </div>

                    <!-- Email Verification Section -->
                    <div id="email-verification" class="verification-section" style="display: none;">
                        <div class="text-center mb-3">
                            <i class="fas fa-envelope fa-2x text-primary mb-2"></i>
                            <p class="mb-1">We've sent a 6-digit code to:</p>
                            <strong class="text-primary">{{ $driver->email }}</strong>
                        </div>
                        
                        <div class="form-group">
                            <label class="text-center d-block mb-2">Enter Email Code</label>
                            <div class="otp-input-group">
                                <input type="text" class="otp-input email-otp" name="email_otp_1" id="email_otp_1" maxlength="1" pattern="[0-9]" autocomplete="off">
                                <input type="text" class="otp-input email-otp" name="email_otp_2" id="email_otp_2" maxlength="1" pattern="[0-9]" autocomplete="off">
                                <input type="text" class="otp-input email-otp" name="email_otp_3" id="email_otp_3" maxlength="1" pattern="[0-9]" autocomplete="off">
                                <input type="text" class="otp-input email-otp" name="email_otp_4" id="email_otp_4" maxlength="1" pattern="[0-9]" autocomplete="off">
                                <input type="text" class="otp-input email-otp" name="email_otp_5" id="email_otp_5" maxlength="1" pattern="[0-9]" autocomplete="off">
                                <input type="text" class="otp-input email-otp" name="email_otp_6" id="email_otp_6" maxlength="1" pattern="[0-9]" autocomplete="off">
                            </div>
                            <input type="hidden" name="email_otp_code" id="email_otp_code">
                        </div>
                    </div>

                    <!-- Timer and Resend -->
                    <div class="text-center mb-3">
                        <div class="resend-timer" id="timer-display">
                            Code expires in: <span id="countdown">05:00</span>
                        </div>
                        <div style="display: none;" id="resend-section">
                            <span>Didn't receive the code? </span>
                            <a href="#" class="resend-link" id="resend-otp">Resend Code</a>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="text-center">
                        <button type="button" class="btn btn-secondary mr-2" onclick="goBack()">
                            <i class="fas fa-arrow-left"></i> Back
                        </button>
                        <button type="submit" class="btn btn-primary" id="verifyBtn" disabled>
                            <i class="fas fa-check-circle"></i> Verify & Continue
                        </button>
                    </div>
                </form>
            </div>

            <div class="card-footer">
                <div class="alert alert-info mb-0">
                    <i class="fas fa-info-circle"></i>
                    <strong>Next Step:</strong> After verification, you'll complete the full driver registration with additional details and document uploads.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
$(document).ready(function() {
    let currentVerificationType = 'sms';
    let countdown = 300; // 5 minutes
    let countdownInterval;
    
    // Start countdown timer
    startCountdown();
    
    // OTP input handling
    $('.otp-input').on('input', function() {
        let value = $(this).val();
        
        // Only allow digits
        if (!/^\d$/.test(value)) {
            $(this).val('');
            return;
        }
        
        $(this).addClass('filled');
        
        // Move to next input
        let nextInput = $(this).next('.otp-input');
        if (nextInput.length && value) {
            nextInput.focus();
        }
        
        // Update hidden field and check completion
        updateOTPCode();
    });
    
    // Handle backspace
    $('.otp-input').on('keydown', function(e) {
        if (e.key === 'Backspace' && !$(this).val()) {
            let prevInput = $(this).prev('.otp-input');
            if (prevInput.length) {
                prevInput.focus().removeClass('filled');
            }
        }
    });
    
    // Handle paste
    $('.otp-input').on('paste', function(e) {
        e.preventDefault();
        let paste = (e.originalEvent.clipboardData || window.clipboardData).getData('text');
        let digits = paste.replace(/\D/g, '').substring(0, 6);
        
        for (let i = 0; i < digits.length && i < 6; i++) {
            let input = currentVerificationType === 'sms' ? 
                $(`#otp_${i + 1}`) : $(`#email_otp_${i + 1}`);
            input.val(digits[i]).addClass('filled');
        }
        
        updateOTPCode();
    });
    
    // Form submission
    $('#otpVerificationForm').on('submit', function(e) {
        const verifyBtn = $('#verifyBtn');
        verifyBtn.prop('disabled', true);
        verifyBtn.html('<i class="fas fa-spinner fa-spin"></i> Verifying...');
        
        // Add a slight delay to show the loading state
        setTimeout(() => {
            // Form will submit normally
        }, 500);
    });
    
    // Resend OTP
    $('#resend-otp').on('click', function(e) {
        e.preventDefault();
        resendOTP();
    });
    
    function switchVerificationType(type) {
        currentVerificationType = type;
        $('#verification_type').val(type);
        
        // Update tab appearance
        $('.verification-tab').removeClass('active');
        $(`#${type}-tab`).addClass('active');
        
        // Show/hide sections
        $('.verification-section').hide();
        $(`#${type}-verification`).show();
        
        // Clear inputs
        $('.otp-input').val('').removeClass('filled');
        updateOTPCode();
        
        // Reset timer
        countdown = 300;
        startCountdown();
    }
    
    function updateOTPCode() {
        let otp = '';
        let inputClass = currentVerificationType === 'sms' ? '.otp-input:not(.email-otp)' : '.email-otp';
        
        $(inputClass).each(function() {
            otp += $(this).val() || '';
        });
        
        // Update hidden field
        if (currentVerificationType === 'sms') {
            $('#otp_code').val(otp);
        } else {
            $('#email_otp_code').val(otp);
        }
        
        // Enable/disable verify button
        $('#verifyBtn').prop('disabled', otp.length !== 6);
        
        // Auto-submit when complete (optional)
        if (otp.length === 6) {
            // Could auto-submit here, but let's keep manual control
            // $('#otpVerificationForm').submit();
        }
    }
    
    function startCountdown() {
        clearInterval(countdownInterval);
        $('#timer-display').show();
        $('#resend-section').hide();
        
        countdownInterval = setInterval(function() {
            countdown--;
            
            let minutes = Math.floor(countdown / 60);
            let seconds = countdown % 60;
            
            $('#countdown').text(
                String(minutes).padStart(2, '0') + ':' + 
                String(seconds).padStart(2, '0')
            );
            
            if (countdown <= 0) {
                clearInterval(countdownInterval);
                $('#timer-display').hide();
                $('#resend-section').show();
            }
        }, 1000);
    }
    
    function resendOTP() {
        // Show loading
        $('#resend-otp').text('Sending...');
        
        $.ajax({
            url: '{{ route("admin.drivers.resend-otp", $driver->id) }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                verification_type: currentVerificationType
            },
            success: function(response) {
                alert('New verification code sent!');
                countdown = 300;
                startCountdown();
                
                // Clear current inputs
                $('.otp-input').val('').removeClass('filled');
                updateOTPCode();
            },
            error: function() {
                alert('Failed to resend code. Please try again.');
            },
            complete: function() {
                $('#resend-otp').text('Resend Code');
            }
        });
    }
    
    // Global function for switching tabs
    window.switchVerificationType = switchVerificationType;
    
    // Global function for back button
    window.goBack = function() {
        if (confirm('Are you sure you want to go back? You will need to verify again later.')) {
            window.location.href = '{{ route("admin.drivers.index") }}';
        }
    };
});
</script>
@endsection