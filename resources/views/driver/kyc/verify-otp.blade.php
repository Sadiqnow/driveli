@extends('layouts.driver_auth')

@section('title', 'Verify Your Account')
@section('content')
<div class="min-vh-100 d-flex align-items-center justify-content-center bg-light">
  <div class="card shadow-lg border-0 rounded-4 p-4 w-100" style="max-width: 420px;">
    
    <!-- Header -->
    <div class="text-center mb-4">
      <img src="{{ asset('images/logo.png') }}" alt="Drivelink Logo" height="60">
      <h4 class="fw-bold mt-3">Phone Number Verification</h4>
      <p class="text-muted small mb-0">Enter the 6-digit OTP sent to your phone</p>
    </div>

    <!-- Verification Form -->
    <form method="POST" action="{{ route('driver.verify-otp.submit') }}" class="needs-validation" novalidate>
      @csrf

      <div class="mb-3">
        <label for="phone" class="form-label">Phone Number</label>
        <input type="text" 
               name="phone" 
               id="phone" 
               class="form-control form-control-lg @error('phone') is-invalid @enderror"
               value="{{ old('phone', session('driver_phone')) }}" 
               placeholder="+234 801 234 5678" 
               required 
               readonly>
        @error('phone')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>

      <div class="mb-4 text-center">
        <label for="otp" class="form-label d-block">One-Time Password (OTP)</label>
        <div class="otp-input-group d-flex justify-content-center gap-2">
          @for($i = 1; $i <= 6; $i++)
            <input type="text" 
                   maxlength="1" 
                   class="otp-box text-center form-control form-control-lg" 
                   name="otp[]" 
                   id="otp{{ $i }}" 
                   pattern="[0-9]*" 
                   required>
          @endfor
        </div>
        @error('otp')
          <div class="invalid-feedback d-block text-center mt-2">{{ $message }}</div>
        @enderror
      </div>

      <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">
        Verify & Continue
      </button>
    </form>

    <!-- Resend Section -->
    <form method="POST" action="{{ route('driver.resend-otp') }}" class="text-center mt-2">
      @csrf
      <input type="hidden" name="phone" value="{{ old('phone', session('driver_phone')) }}">
      <button type="submit" class="btn btn-link small">Didn’t receive code? <span class="fw-bold text-primary">Resend OTP</span></button>
    </form>

    <!-- Footer -->
    <div class="text-center mt-3">
      <p class="small text-muted mb-0">Having trouble? <a href="{{ route('driver.support') }}" class="fw-bold text-primary">Contact Support</a></p>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  const otpBoxes = document.querySelectorAll('.otp-box');
  
  otpBoxes.forEach((box, index) => {
    box.addEventListener('input', function(e) {
      const value = e.target.value;
      if (value.length === 1 && index < otpBoxes.length - 1) {
        otpBoxes[index + 1].focus();
      } else if (e.inputType === 'deleteContentBackward' && index > 0 && value === '') {
        otpBoxes[index - 1].focus();
      }
    });
  });

  // Auto-focus first box
  otpBoxes[0].focus();

  // Validation on submit
  const form = document.querySelector('.needs-validation');
  form.addEventListener('submit', function(event) {
    let otpComplete = Array.from(otpBoxes).every(input => input.value.trim() !== '');
    if (!otpComplete) {
      event.preventDefault();
      alert('Please fill in all 6 digits of your OTP.');
    } else {
      const otpValue = Array.from(otpBoxes).map(input => input.value).join('');
      const otpHidden = document.createElement('input');
      otpHidden.type = 'hidden';
      otpHidden.name = 'otp';
      otpHidden.value = otpValue;
      form.appendChild(otpHidden);
    }
  });
});
</script>

<style>
.otp-input-group .otp-box {
  width: 48px;
  height: 58px;
  font-size: 1.25rem;
  border: 1px solid #ccc;
  border-radius: 8px;
}
.otp-input-group .otp-box:focus {
  border-color: #0066cc;
  box-shadow: 0 0 0 0.2rem rgba(0,102,204,.25);
}
</style>
@endsection
