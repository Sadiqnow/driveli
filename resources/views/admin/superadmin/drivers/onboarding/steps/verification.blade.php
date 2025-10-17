<div class="alert alert-info">
    <i class="fas fa-shield-alt"></i>
    <strong>Verification Step:</strong> Please verify your email and phone number to complete the onboarding process.
    This ensures we can communicate important updates and job opportunities.
</div>

<div class="row">
    <!-- Email Verification -->
    <div class="col-md-6">
        <div class="card {{ $driver->email_verified_at ? 'border-success' : 'border-warning' }}">
            <div class="card-header {{ $driver->email_verified_at ? 'bg-success text-white' : 'bg-warning' }}">
                <h5 class="card-title mb-0">
                    <i class="fas fa-envelope"></i> Email Verification
                    @if($driver->email_verified_at)
                        <span class="badge badge-light ml-2">Verified</span>
                    @else
                        <span class="badge badge-light ml-2">Pending</span>
                    @endif
                </h5>
            </div>
            <div class="card-body">
                <p><strong>Email:</strong> {{ $driver->email }}</p>

                @if($driver->email_verified_at)
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> Email verified on {{ $driver->email_verified_at->format('M d, Y H:i') }}
                    </div>
                @else
                    <div class="form-group">
                        <label for="email_verification_code">Verification Code <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('email_verification_code') is-invalid @enderror"
                               id="email_verification_code" name="email_verification_code"
                               placeholder="Enter 6-digit code" maxlength="6" pattern="\d{6}" required>
                        @error('email_verification_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Check your email for the verification code</small>
                    </div>

                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="resendEmailCode()">
                        <i class="fas fa-redo"></i> Resend Code
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Phone Verification -->
    <div class="col-md-6">
        <div class="card {{ $driver->phone_verified_at ? 'border-success' : 'border-warning' }}">
            <div class="card-header {{ $driver->phone_verified_at ? 'bg-success text-white' : 'bg-warning' }}">
                <h5 class="card-title mb-0">
                    <i class="fas fa-phone"></i> Phone Verification
                    @if($driver->phone_verified_at)
                        <span class="badge badge-light ml-2">Verified</span>
                    @else
                        <span class="badge badge-light ml-2">Pending</span>
                    @endif
                </h5>
            </div>
            <div class="card-body">
                <p><strong>Phone:</strong> {{ $driver->phone }}</p>

                @if($driver->phone_verified_at)
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> Phone verified on {{ $driver->phone_verified_at->format('M d, Y H:i') }}
                    </div>
                @else
                    <div class="form-group">
                        <label for="phone_verification_code">Verification Code</label>
                        <input type="text" class="form-control @error('phone_verification_code') is-invalid @enderror"
                               id="phone_verification_code" name="phone_verification_code"
                               placeholder="Enter 6-digit code" maxlength="6" pattern="\d{6}">
                        @error('phone_verification_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Check your phone for the verification code</small>
                    </div>

                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="resendPhoneCode()">
                        <i class="fas fa-redo"></i> Resend Code
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>

<hr>

<!-- Onboarding Summary -->
<div class="card">
    <div class="card-header bg-light">
        <h5 class="card-title mb-0">
            <i class="fas fa-clipboard-check"></i> Onboarding Summary
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h6>Driver Information</h6>
                <table class="table table-sm">
                    <tr>
                        <td><strong>Driver ID:</strong></td>
                        <td>{{ $driver->driver_id }}</td>
                    </tr>
                    <tr>
                        <td><strong>Full Name:</strong></td>
                        <td>{{ $driver->full_name }}</td>
                    </tr>
                    <tr>
                        <td><strong>Email:</strong></td>
                        <td>{{ $driver->email }}</td>
                    </tr>
                    <tr>
                        <td><strong>Phone:</strong></td>
                        <td>{{ $driver->phone }}</td>
                    </tr>
                    <tr>
                        <td><strong>Date of Birth:</strong></td>
                        <td>{{ $driver->personalInfo?->date_of_birth?->format('M d, Y') ?: 'Not provided' }}</td>
                    </tr>
                </table>
            </div>

            <div class="col-md-6">
                <h6>Progress Status</h6>
                <div class="progress mb-3">
                    <div class="progress-bar bg-success" style="width: {{ $progress['overall_progress'] }}%">
                        {{ $progress['overall_progress'] }}%
                    </div>
                </div>

                <div class="mb-3">
                    @foreach($progress['breakdown'] as $stepKey => $stepData)
                        <div class="d-flex justify-content-between mb-1">
                            <small>{{ $stepData['name'] }}</small>
                            <small class="badge badge-{{ $stepData['completed'] ? 'success' : 'warning' }}">
                                {{ $stepData['completed'] ? 'Complete' : 'Pending' }}
                            </small>
                        </div>
                    @endforeach
                </div>

                @if($progress['overall_progress'] >= 100)
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> All onboarding steps completed! Ready for submission.
                    </div>
                @else
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> Complete all steps before submitting for review.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Terms and Conditions -->
<div class="card border-primary">
    <div class="card-header bg-primary text-white">
        <h5 class="card-title mb-0">
            <i class="fas fa-file-contract"></i> Terms & Conditions
        </h5>
    </div>
    <div class="card-body">
        <div class="form-group">
            <div class="custom-control custom-checkbox">
                <input class="custom-control-input" type="checkbox" id="accept_terms" name="accept_terms" value="1" required>
                <label for="accept_terms" class="custom-control-label">
                    I agree to the <a href="#" target="_blank">Driver Terms of Service</a> and <a href="#" target="_blank">Privacy Policy</a>
                    <span class="text-danger">*</span>
                </label>
            </div>
            @error('accept_terms')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <div class="custom-control custom-checkbox">
                <input class="custom-control-input" type="checkbox" id="accept_data_processing" name="accept_data_processing" value="1" required>
                <label for="accept_data_processing" class="custom-control-label">
                    I consent to the processing of my personal data for verification and service provision purposes
                    <span class="text-danger">*</span>
                </label>
            </div>
            @error('accept_data_processing')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="alert alert-info">
            <small>
                By completing this verification, you acknowledge that:
                <ul class="mb-0 mt-2">
                    <li>All provided information is accurate and truthful</li>
                    <li>You are legally eligible to work as a driver in Nigeria</li>
                    <li>You have valid driving credentials and vehicle insurance (if applicable)</li>
                    <li>You understand the commission structure and payment terms</li>
                </ul>
            </small>
        </div>
    </div>
</div>

@push('scripts')
<script>
function resendEmailCode() {
    if (confirm('Send a new verification code to {{ $driver->email }}?')) {
        // AJAX call to resend email code
        $.post('{{ route("admin.superadmin.drivers.verification.resend-email", $driver) }}', {
            _token: '{{ csrf_token() }}'
        })
        .done(function(response) {
            alert('Verification code sent to your email.');
        })
        .fail(function(xhr) {
            alert('Failed to send verification code. Please try again.');
        });
    }
}

function resendPhoneCode() {
    if (confirm('Send a new verification code to {{ $driver->phone }}?')) {
        // AJAX call to resend phone code
        $.post('{{ route("admin.superadmin.drivers.verification.resend-phone", $driver) }}', {
            _token: '{{ csrf_token() }}'
        })
        .done(function(response) {
            alert('Verification code sent to your phone.');
        })
        .fail(function(xhr) {
            alert('Failed to send verification code. Please try again.');
        });
    }
}

// Form validation
$('form').on('submit', function(e) {
    const acceptTerms = $('#accept_terms').is(':checked');
    const acceptDataProcessing = $('#accept_data_processing').is(':checked');

    if (!acceptTerms || !acceptDataProcessing) {
        e.preventDefault();
        alert('Please accept all terms and conditions before proceeding.');
        return false;
    }

    // Validate verification codes if not already verified
    @if(!$driver->email_verified_at)
        const emailCode = $('#email_verification_code').val();
        if (!emailCode || emailCode.length !== 6) {
            e.preventDefault();
            alert('Please enter a valid 6-digit email verification code.');
            return false;
        }
    @endif

    @if(!$driver->phone_verified_at)
        const phoneCode = $('#phone_verification_code').val();
        if (phoneCode && phoneCode.length !== 6) {
            e.preventDefault();
            alert('Please enter a valid 6-digit phone verification code.');
            return false;
        }
    @endif
});
</script>
@endpush
