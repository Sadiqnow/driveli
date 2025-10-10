@extends('layouts.app')

@section('title', 'Company Registration')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4><i class="fas fa-building"></i> Company Registration</h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            <h5><i class="fas fa-check-circle"></i> Registration Successful!</h5>
                            <p>{{ session('success') }}</p>
                        </div>
                    @elseif($errors->has('general'))
                        <div class="alert alert-danger">
                            <h5><i class="fas fa-exclamation-triangle"></i> Registration Failed</h5>
                            <p>{{ $errors->first('general') }}</p>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <h5><i class="fas fa-info-circle"></i> Company Registration</h5>
                            <p>Register your company with Drivelink to access our driver matching services.</p>
                        </div>
                    @endif

                    @if ($errors->any() && !$errors->has('general'))
                        <div class="alert alert-danger">
                            <h5><i class="fas fa-exclamation-triangle"></i> Please correct the following errors:</h5>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('company.register.submit') }}" enctype="multipart/form-data">
                        @csrf

                        <!-- Company Information -->
                        <div class="card mb-4">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0"><i class="fas fa-building"></i> Company Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="company_name">Company Name <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('company_name') is-invalid @enderror" 
                                                   id="company_name" name="company_name" value="{{ old('company_name') }}" required>
                                            @error('company_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="business_type">Business Type <span class="text-danger">*</span></label>
                                            <select class="form-control @error('business_type') is-invalid @enderror" 
                                                    id="business_type" name="business_type" required>
                                                <option value="">Select Business Type</option>
                                                <option value="logistics" {{ old('business_type') == 'logistics' ? 'selected' : '' }}>Logistics & Transportation</option>
                                                <option value="delivery" {{ old('business_type') == 'delivery' ? 'selected' : '' }}>Delivery Services</option>
                                                <option value="ride_hailing" {{ old('business_type') == 'ride_hailing' ? 'selected' : '' }}>Ride Hailing</option>
                                                <option value="freight" {{ old('business_type') == 'freight' ? 'selected' : '' }}>Freight & Cargo</option>
                                                <option value="other" {{ old('business_type') == 'other' ? 'selected' : '' }}>Other</option>
                                            </select>
                                            @error('business_type')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="registration_number">Registration Number</label>
                                            <input type="text" class="form-control @error('registration_number') is-invalid @enderror" 
                                                   id="registration_number" name="registration_number" value="{{ old('registration_number') }}">
                                            @error('registration_number')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="tax_id">Tax ID</label>
                                            <input type="text" class="form-control @error('tax_id') is-invalid @enderror" 
                                                   id="tax_id" name="tax_id" value="{{ old('tax_id') }}">
                                            @error('tax_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="company_address">Company Address <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('company_address') is-invalid @enderror" 
                                              id="company_address" name="company_address" rows="3" required>{{ old('company_address') }}</textarea>
                                    @error('company_address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="city">City <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('city') is-invalid @enderror" 
                                                   id="city" name="city" value="{{ old('city') }}" required>
                                            @error('city')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="state">State <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('state') is-invalid @enderror" 
                                                   id="state" name="state" value="{{ old('state') }}" required>
                                            @error('state')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="postal_code">Postal Code</label>
                                            <input type="text" class="form-control @error('postal_code') is-invalid @enderror" 
                                                   id="postal_code" name="postal_code" value="{{ old('postal_code') }}">
                                            @error('postal_code')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div class="card mb-4">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0"><i class="fas fa-phone"></i> Contact Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="contact_name">Contact Person <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control @error('contact_name') is-invalid @enderror" 
                                                   id="contact_name" name="contact_name" value="{{ old('contact_name') }}" required>
                                            @error('contact_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="contact_title">Contact Title</label>
                                            <input type="text" class="form-control @error('contact_title') is-invalid @enderror" 
                                                   id="contact_title" name="contact_title" value="{{ old('contact_title') }}"
                                                   placeholder="e.g., CEO, Operations Manager">
                                            @error('contact_title')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="email">Email Address <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                                   id="email" name="email" value="{{ old('email') }}" required>
                                            @error('email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="phone">Phone Number <span class="text-danger">*</span></label>
                                            <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                                   id="phone" name="phone" value="{{ old('phone') }}" required>
                                            @error('phone')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="website">Website</label>
                                    <input type="url" class="form-control @error('website') is-invalid @enderror" 
                                           id="website" name="website" value="{{ old('website') }}"
                                           placeholder="https://www.yourcompany.com">
                                    @error('website')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Driver Requirements -->
                        <div class="card mb-4">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0"><i class="fas fa-users"></i> Driver Requirements</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="drivers_needed">Number of Drivers Needed</label>
                                            <input type="number" class="form-control @error('drivers_needed') is-invalid @enderror" 
                                                   id="drivers_needed" name="drivers_needed" value="{{ old('drivers_needed') }}"
                                                   min="1" max="1000">
                                            @error('drivers_needed')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="urgency">Urgency</label>
                                            <select class="form-control @error('urgency') is-invalid @enderror" id="urgency" name="urgency">
                                                <option value="">Select Urgency</option>
                                                <option value="immediate" {{ old('urgency') == 'immediate' ? 'selected' : '' }}>Immediate (Within 1 week)</option>
                                                <option value="urgent" {{ old('urgency') == 'urgent' ? 'selected' : '' }}>Urgent (Within 1 month)</option>
                                                <option value="normal" {{ old('urgency') == 'normal' ? 'selected' : '' }}>Normal (Within 3 months)</option>
                                                <option value="planning" {{ old('urgency') == 'planning' ? 'selected' : '' }}>Planning (Future needs)</option>
                                            </select>
                                            @error('urgency')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="driver_requirements">Additional Requirements</label>
                                    <textarea class="form-control @error('driver_requirements') is-invalid @enderror" 
                                              id="driver_requirements" name="driver_requirements" rows="3"
                                              placeholder="Describe any specific requirements for drivers (experience, vehicle type, etc.)">{{ old('driver_requirements') }}</textarea>
                                    @error('driver_requirements')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Terms and Submit -->
                        <div class="card mb-4">
                            <div class="card-body">
                                <div class="form-check mb-3">
                                    <input type="checkbox" class="form-check-input @error('terms') is-invalid @enderror" 
                                           id="terms" name="terms" value="1" {{ old('terms') ? 'checked' : '' }} required>
                                    <label class="form-check-label" for="terms">
                                        I agree to the <a href="#" target="_blank">Terms and Conditions</a> and <a href="#" target="_blank">Privacy Policy</a>
                                        <span class="text-danger">*</span>
                                    </label>
                                    @error('terms')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-check mb-3">
                                    <input type="checkbox" class="form-check-input" id="marketing_emails" name="marketing_emails" value="1">
                                    <label class="form-check-label" for="marketing_emails">
                                        I would like to receive marketing emails and updates from Drivelink
                                    </label>
                                </div>

                                <div class="text-center">
                                    <button type="submit" class="btn btn-primary btn-lg px-5">
                                        <i class="fas fa-paper-plane"></i> Submit Registration
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Form validation
    $('form').on('submit', function(e) {
        let isValid = true;
        
        // Check required fields
        $('input[required], select[required], textarea[required]').each(function() {
            if (!$(this).val()) {
                $(this).addClass('is-invalid');
                isValid = false;
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        
        // Check terms checkbox
        if (!$('#terms').is(':checked')) {
            $('#terms').addClass('is-invalid');
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault();
            $('html, body').animate({
                scrollTop: $('.is-invalid').first().offset().top - 100
            }, 500);
        }
    });
    
    // Remove invalid class on input
    $('input, select, textarea').on('input change', function() {
        $(this).removeClass('is-invalid');
    });
});
</script>
@endsection