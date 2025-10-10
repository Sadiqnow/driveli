@extends('layouts.driver')

@section('title', 'Driver Registration - DriveLink')

@section('content')
<div class="container">
    <!-- Progress Indicator -->
    <div class="step-progress mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div class="step-item active">
                <div class="step-number">1</div>
                <div class="step-title">Basic Info</div>
            </div>
            <div class="progress-line"></div>
            <div class="step-item">
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

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Register New Driver</h4>
                    <small class="text-muted">Step 1 of 4: Basic Information</small>
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

                    <form method="POST" action="{{ route('driver.register.step1') }}">
                        @csrf

                        <!-- Driver's License Number -->
                        <div class="mb-3">
                            <label for="license_number" class="form-label">Driver's License Number <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control @error('license_number') is-invalid @enderror"
                                   id="license_number"
                                   name="license_number"
                                   value="{{ old('license_number') }}"
                                   placeholder="Enter your driver's license number"
                                   required>
                            @error('license_number')
                                <div class="invalid-feedback" role="alert">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Date of Birth -->
                        <div class="mb-3">
                            <label for="date_of_birth" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                            <input type="date"
                                   class="form-control @error('date_of_birth') is-invalid @enderror"
                                   id="date_of_birth"
                                   name="date_of_birth"
                                   value="{{ old('date_of_birth') }}"
                                   required>
                            @error('date_of_birth')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- First Name -->
                        <div class="mb-3">
                            <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control @error('first_name') is-invalid @enderror"
                                   id="first_name"
                                   name="first_name"
                                   value="{{ old('first_name') }}"
                                   placeholder="Enter your first name"
                                   required>
                            @error('first_name')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Surname -->
                        <div class="mb-3">
                            <label for="surname" class="form-label">Surname <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control @error('surname') is-invalid @enderror"
                                   id="surname"
                                   name="surname"
                                   value="{{ old('surname') }}"
                                   placeholder="Enter your surname"
                                   required>
                            @error('surname')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Phone Number -->
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                            <input type="tel"
                                   class="form-control @error('phone') is-invalid @enderror"
                                   id="phone"
                                   name="phone"
                                   value="{{ old('phone') }}"
                                   placeholder="Enter your phone number"
                                   required>
                            @error('phone')
                                <div class="invalid-feedback" role="alert">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Email Address -->
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   id="email"
                                   name="email"
                                   value="{{ old('email') }}"
                                   placeholder="Enter your email address"
                                   required>
                            @error('email')
                                <div class="invalid-feedback" role="alert">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Gender -->
                        <div class="mb-3">
                            <label for="gender" class="form-label">Gender</label>
                            <select class="form-control @error('gender') is-invalid @enderror"
                                    id="gender"
                                    name="gender">
                                <option value="">Select Gender (Optional)</option>
                                <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                                <option value="Other" {{ old('gender') == 'Other' ? 'selected' : '' }}>Other</option>
                                <option value="Prefer not to say" {{ old('gender') == 'Prefer not to say' ? 'selected' : '' }}>Prefer not to say</option>
                            </select>
                            @error('gender')
                                <div class="invalid-feedback" role="alert">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Religion -->
                        <div class="mb-3">
                            <label for="religion" class="form-label">Religion</label>
                            <input type="text"
                                   class="form-control @error('religion') is-invalid @enderror"
                                   id="religion"
                                   name="religion"
                                   value="{{ old('religion') }}"
                                   placeholder="Enter your religion (Optional)">
                            @error('religion')
                                <div class="invalid-feedback" role="alert">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Blood Group -->
                        <div class="mb-3">
                            <label for="blood_group" class="form-label">Blood Group</label>
                            <select class="form-control @error('blood_group') is-invalid @enderror"
                                    id="blood_group"
                                    name="blood_group">
                                <option value="">Select Blood Group (Optional)</option>
                                <option value="A+" {{ old('blood_group') == 'A+' ? 'selected' : '' }}>A+</option>
                                <option value="A-" {{ old('blood_group') == 'A-' ? 'selected' : '' }}>A-</option>
                                <option value="B+" {{ old('blood_group') == 'B+' ? 'selected' : '' }}>B+</option>
                                <option value="B-" {{ old('blood_group') == 'B-' ? 'selected' : '' }}>B-</option>
                                <option value="AB+" {{ old('blood_group') == 'AB+' ? 'selected' : '' }}>AB+</option>
                                <option value="AB-" {{ old('blood_group') == 'AB-' ? 'selected' : '' }}>AB-</option>
                                <option value="O+" {{ old('blood_group') == 'O+' ? 'selected' : '' }}>O+</option>
                                <option value="O-" {{ old('blood_group') == 'O-' ? 'selected' : '' }}>O-</option>
                            </select>
                            @error('blood_group')
                                <div class="invalid-feedback" role="alert">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Height in Meters -->
                        <div class="mb-3">
                            <label for="height_meters" class="form-label">Height (in meters)</label>
                            <input type="number"
                                   class="form-control @error('height_meters') is-invalid @enderror"
                                   id="height_meters"
                                   name="height_meters"
                                   value="{{ old('height_meters') }}"
                                   placeholder="e.g., 1.75"
                                   step="0.01"
                                   min="0.5"
                                   max="3.0">
                            @error('height_meters')
                                <div class="invalid-feedback" role="alert">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Disability Status -->
                        <div class="mb-3">
                            <label for="disability_status" class="form-label">Disability Status</label>
                            <input type="text"
                                   class="form-control @error('disability_status') is-invalid @enderror"
                                   id="disability_status"
                                   name="disability_status"
                                   value="{{ old('disability_status') }}"
                                   placeholder="Enter disability status if any (Optional)">
                            @error('disability_status')
                                <div class="invalid-feedback" role="alert">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="mb-3">
                            <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password"
                                   class="form-control @error('password') is-invalid @enderror"
                                   id="password"
                                   name="password"
                                   placeholder="Create a password"
                                   required>
                            @error('password')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                            <small class="form-text text-muted">Password must be at least 8 characters long.</small>
                        </div>

                        <!-- Confirm Password -->
                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password"
                                   class="form-control @error('password_confirmation') is-invalid @enderror"
                                   id="password_confirmation"
                                   name="password_confirmation"
                                   placeholder="Confirm your password"
                                   required>
                            @error('password_confirmation')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Terms and Conditions -->
                        <div class="mb-3 form-check">
                            <input type="checkbox"
                                   class="form-check-input @error('terms') is-invalid @enderror"
                                   id="terms"
                                   name="terms"
                                   value="1"
                                   required>
                            <label class="form-check-label" for="terms">
                                I agree to the <a href="{{ route('terms') }}" target="_blank">Terms and Conditions</a>
                                and <a href="{{ route('privacy') }}" target="_blank">Privacy Policy</a>
                            </label>
                            @error('terms')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-arrow-right"></i> Continue to Verification
                            </button>
                        </div>
                    </form>

                    <div class="text-center mt-3">
                        <p class="mb-0">Already have an account?
                            <a href="{{ route('driver.login') }}">Login here</a>
                        </p>
                    </div>

                    <!-- Information Alert -->
                    <div class="alert alert-info mt-4">
                        <h6><i class="fas fa-info-circle"></i> What happens next?</h6>
                        <ol class="mb-0">
                            <li>Complete this basic registration form</li>
                            <li>Proceed to KYC (Know Your Customer) verification</li>
                            <li>Upload required documents</li>
                            <li>Wait for admin verification</li>
                            <li>Start receiving job opportunities!</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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

.step-item.active .step-number {
    background-color: #007bff;
    color: white;
}

.step-item:not(.active) .step-number {
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

.progress-line {
    flex: 1;
    height: 2px;
    background-color: #e9ecef;
    margin: 0 10px;
    margin-top: -20px;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.form-label {
    font-weight: 600;
    color: #495057;
}

.text-danger {
    color: #dc3545!important;
}

.btn-primary {
    background-color: #007bff;
    border-color: #007bff;
}

.btn-primary:hover {
    background-color: #0056b3;
    border-color: #0056b3;
}

.alert-info {
    background-color: #d1ecf1;
    border-color: #b8daff;
    color: #0c5460;
}

/* Enhanced validation styles */
.spinner-border-sm {
    width: 1rem;
    height: 1rem;
}

.is-valid {
    border-color: #28a745;
}

.is-invalid {
    border-color: #dc3545;
}

.custom-feedback {
    display: block;
    margin-top: 0.25rem;
    font-size: 0.875rem;
    color: #dc3545;
}

.validation-summary {
    margin-bottom: 1rem;
    border-left: 4px solid #dc3545;
}
</style>

<!-- Enhanced Form Validation Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const submitBtn = form.querySelector('button[type="submit"]');

    // Validation rules
    const validationRules = {
        drivers_license_number: {
            required: true,
            minLength: 3,
            pattern: /^[A-Za-z0-9\-\s]+$/,
            message: 'Driver license number must contain only letters, numbers, spaces, and hyphens'
        },
        date_of_birth: {
            required: true,
            type: 'date',
            minAge: 18,
            maxAge: 80,
            message: 'You must be between 18 and 80 years old'
        },
        first_name: {
            required: true,
            minLength: 2,
            maxLength: 50,
            pattern: /^[A-Za-z\s'-]+$/,
            message: 'First name must contain only letters, spaces, hyphens, and apostrophes'
        },
        surname: {
            required: true,
            minLength: 2,
            maxLength: 50,
            pattern: /^[A-Za-z\s'-]+$/,
            message: 'Surname must contain only letters, spaces, hyphens, and apostrophes'
        },
        phone: {
            required: true,
            pattern: /^[\+]?[0-9\-\(\)\s]+$/,
            minLength: 10,
            maxLength: 15,
            message: 'Please enter a valid phone number'
        },
        email: {
            required: true,
            type: 'email',
            pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
            message: 'Please enter a valid email address'
        },
        gender: {
            required: false,
            pattern: /^(Male|Female|Other|Prefer not to say)$/,
            message: 'Please select a valid gender option'
        },
        religion: {
            required: false,
            maxLength: 100,
            message: 'Religion must be no more than 100 characters'
        },
        blood_group: {
            required: false,
            pattern: /^(A\+|A-|B\+|B-|AB\+|AB-|O\+|O-)$/,
            message: 'Please select a valid blood group'
        },
        height_meters: {
            required: false,
            type: 'number',
            min: 0.5,
            max: 3.0,
            message: 'Height must be between 0.5 and 3.0 meters'
        },
        disability_status: {
            required: false,
            maxLength: 100,
            message: 'Disability status must be no more than 100 characters'
        },
        password: {
            required: true,
            minLength: 8,
            pattern: /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/,
            message: 'Password must contain at least 8 characters, including uppercase, lowercase, and a number'
        },
        password_confirmation: {
            required: true,
            matchField: 'password',
            message: 'Password confirmation must match the password'
        }
    };

    // Real-time validation
    Object.keys(validationRules).forEach(fieldName => {
        const field = form.querySelector(`[name="${fieldName}"]`);
        if (!field) return;

        // Add validation event listeners
        field.addEventListener('blur', () => validateField(field, validationRules[fieldName]));
        field.addEventListener('input', () => {
            // Clear error state on input
            field.classList.remove('is-invalid');
            const feedback = field.parentElement.querySelector('.custom-feedback');
            if (feedback) {
                feedback.remove();
            }

            // Real-time validation for certain fields
            if (['email', 'phone', 'password'].includes(fieldName)) {
                setTimeout(() => validateField(field, validationRules[fieldName]), 500);
            }
        });
    });

    // Form submission validation
    form.addEventListener('submit', function(e) {
        let isValid = true;

        // Validate all fields
        Object.keys(validationRules).forEach(fieldName => {
            const field = form.querySelector(`[name="${fieldName}"]`);
            if (field && !validateField(field, validationRules[fieldName])) {
                isValid = false;
            }
        });

        if (!isValid) {
            e.preventDefault();

            // Focus on first invalid field
            const firstInvalid = form.querySelector('.is-invalid');
            if (firstInvalid) {
                firstInvalid.focus();
                firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            showValidationSummary('Please correct the errors below before submitting.');
            return false;
        }

        // Add loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Creating Account...';
    });

    // Validation function
    function validateField(field, rules) {
        const value = field.value.trim();
        let isValid = true;
        let errorMessage = '';

        // Required validation
        if (rules.required && !value) {
            isValid = false;
            errorMessage = `${getFieldLabel(field)} is required`;
        }
        // Length validation
        else if (value && rules.minLength && value.length < rules.minLength) {
            isValid = false;
            errorMessage = `${getFieldLabel(field)} must be at least ${rules.minLength} characters`;
        }
        else if (value && rules.maxLength && value.length > rules.maxLength) {
            isValid = false;
            errorMessage = rules.message || `${getFieldLabel(field)} must be no more than ${rules.maxLength} characters`;
        }
        // Pattern validation
        else if (value && rules.pattern && !rules.pattern.test(value)) {
            isValid = false;
            errorMessage = rules.message || `${getFieldLabel(field)} format is invalid`;
        }
        // Age validation for date of birth
        else if (value && rules.type === 'date' && (rules.minAge || rules.maxAge)) {
            const birthDate = new Date(value);
            const today = new Date();
            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();

            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }

            if (rules.minAge && age < rules.minAge) {
                isValid = false;
                errorMessage = `You must be at least ${rules.minAge} years old`;
            } else if (rules.maxAge && age > rules.maxAge) {
                isValid = false;
                errorMessage = `Age cannot exceed ${rules.maxAge} years`;
            }
        }
        // Number validation for height
        else if (value && rules.type === 'number' && (rules.min || rules.max)) {
            const numValue = parseFloat(value);
            if (rules.min && numValue < rules.min) {
                isValid = false;
                errorMessage = rules.message || `${getFieldLabel(field)} must be at least ${rules.min}`;
            } else if (rules.max && numValue > rules.max) {
                isValid = false;
                errorMessage = rules.message || `${getFieldLabel(field)} must be no more than ${rules.max}`;
            }
        }
        // Match field validation
        else if (value && rules.matchField) {
            const matchField = form.querySelector(`[name="${rules.matchField}"]`);
            if (matchField && value !== matchField.value) {
                isValid = false;
                errorMessage = rules.message || `${getFieldLabel(field)} must match ${getFieldLabel(matchField)}`;
            }
        }

        // Update field state
        if (isValid) {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
            removeCustomFeedback(field);
        } else {
            field.classList.remove('is-valid');
            field.classList.add('is-invalid');
            showCustomFeedback(field, errorMessage);
        }

        return isValid;
    }

    // Helper functions
    function getFieldLabel(field) {
        const label = form.querySelector(`label[for="${field.id}"]`);
        return label ? label.textContent.replace(' *', '') : field.name.replace('_', ' ');
    }

    function showCustomFeedback(field, message) {
        removeCustomFeedback(field);

        // Don't override Laravel validation feedback
        const existingFeedback = field.parentElement.querySelector('.invalid-feedback:not(.custom-feedback)');
        if (existingFeedback) {
            return;
        }

        const feedback = document.createElement('div');
        feedback.className = 'invalid-feedback custom-feedback';
        feedback.setAttribute('role', 'alert');
        feedback.textContent = message;
        field.parentElement.appendChild(feedback);
    }

    function removeCustomFeedback(field) {
        const customFeedback = field.parentElement.querySelector('.custom-feedback');
        if (customFeedback) {
            customFeedback.remove();
        }
    }

    function showValidationSummary(message) {
        const existingSummary = form.querySelector('.validation-summary');
        if (existingSummary) {
            existingSummary.remove();
        }

        const summary = document.createElement('div');
        summary.className = 'alert alert-danger validation-summary';
        summary.setAttribute('role', 'alert');
        summary.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-triangle me-2" aria-hidden="true"></i>
                <span>${message}</span>
            </div>
        `;

        form.insertBefore(summary, form.firstElementChild);
        summary.scrollIntoView({ behavior: 'smooth', block: 'center' });

        setTimeout(() => {
            if (summary.parentElement) {
                summary.remove();
            }
        }, 5000);
    }
});
</script>

@endsection
