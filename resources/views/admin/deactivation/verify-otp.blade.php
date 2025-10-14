@extends('layouts.admin')

@section('title', 'Verify OTP for Deactivation')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Verify OTP to Complete Deactivation</h3>
                </div>

                <div class="card-body">
                    <div class="text-center mb-4">
                        <p class="text-muted">
                            An OTP has been sent to the {{ ucfirst($otp->user_type) }} for final confirmation.
                        </p>
                        <p class="text-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            This action cannot be undone. The {{ $otp->user_type }} will be permanently deactivated.
                        </p>
                    </div>

                    <form action="{{ route('admin.deactivation.verify-otp', $otp) }}" method="POST" id="otpForm">
                        @csrf
                        <div class="form-group text-center">
                            <label for="otp_code">Enter 6-digit OTP Code</label>
                            <input type="text" name="otp_code" id="otp_code" class="form-control text-center"
                                   maxlength="6" pattern="[0-9]{6}" required
                                   style="font-size: 24px; letter-spacing: 8px; font-family: monospace;">
                            @error('otp_code')
                                <div class="text-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group text-center">
                            <small class="text-muted">
                                OTP expires in: <span id="countdown">{{ $otp->expires_at->diffInSeconds(now()) }}</span> seconds
                            </small>
                        </div>
                    </form>
                </div>

                <div class="card-footer text-center">
                    <button type="submit" form="otpForm" class="btn btn-success btn-lg">
                        <i class="fas fa-check"></i> Verify & Deactivate
                    </button>
                    <a href="{{ route('admin.deactivation.resend-otp', $otp) }}" class="btn btn-warning btn-lg ml-2">
                        <i class="fas fa-redo"></i> Resend OTP
                    </a>
                    <a href="{{ route('admin.deactivation.index') }}" class="btn btn-secondary btn-lg ml-2">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Auto-format OTP input
    $('#otp_code').on('input', function() {
        var value = $(this).val().replace(/\D/g, '');
        $(this).val(value);
    });

    // Countdown timer
    var countdownElement = $('#countdown');
    var seconds = parseInt(countdownElement.text());

    var countdown = setInterval(function() {
        seconds--;
        countdownElement.text(seconds);

        if (seconds <= 0) {
            clearInterval(countdown);
            countdownElement.closest('.form-group').html('<span class="text-danger">OTP has expired</span>');
            $('#otpForm button[type="submit"]').prop('disabled', true);
        }
    }, 1000);

    // Auto-submit when 6 digits entered
    $('#otp_code').on('input', function() {
        if ($(this).val().length === 6) {
            $('#otpForm').submit();
        }
    });
});
</script>
@endsection
