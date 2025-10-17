<div class="alert alert-info">
    <i class="fas fa-info-circle"></i>
    <strong>Professional Information:</strong> This information helps us match you with appropriate job opportunities
    and track your performance in the platform.
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="license_number">Driver's License Number <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('license_number') is-invalid @enderror"
                   id="license_number" name="license_number"
                   value="{{ old('license_number', $driver->performance?->license_number) }}" required
                   placeholder="Enter your license number">
            @error('license_number')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label for="license_expiry">License Expiry Date <span class="text-danger">*</span></label>
            <input type="date" class="form-control @error('license_expiry') is-invalid @enderror"
                   id="license_expiry" name="license_expiry"
                   value="{{ old('license_expiry', $driver->performance?->license_expiry_date) }}" required>
            @error('license_expiry')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="years_of_experience">Years of Driving Experience <span class="text-danger">*</span></label>
            <select class="form-control @error('years_of_experience') is-invalid @enderror"
                    id="years_of_experience" name="years_of_experience" required>
                <option value="">Select Experience</option>
                <option value="0-1" {{ old('years_of_experience', $driver->performance?->years_of_experience) == '0-1' ? 'selected' : '' }}>Less than 1 year</option>
                <option value="1-2" {{ old('years_of_experience', $driver->performance?->years_of_experience) == '1-2' ? 'selected' : '' }}>1-2 years</option>
                <option value="2-5" {{ old('years_of_experience', $driver->performance?->years_of_experience) == '2-5' ? 'selected' : '' }}>2-5 years</option>
                <option value="5-10" {{ old('years_of_experience', $driver->performance?->years_of_experience) == '5-10' ? 'selected' : '' }}>5-10 years</option>
                <option value="10+" {{ old('years_of_experience', $driver->performance?->years_of_experience) == '10+' ? 'selected' : '' }}>More than 10 years</option>
            </select>
            @error('years_of_experience')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label for="vehicle_type">Preferred Vehicle Type <span class="text-danger">*</span></label>
            <select class="form-control @error('vehicle_type') is-invalid @enderror"
                    id="vehicle_type" name="vehicle_type" required>
                <option value="">Select Vehicle Type</option>
                <option value="sedan" {{ old('vehicle_type', $driver->performance?->vehicle_type) == 'sedan' ? 'selected' : '' }}>Sedan</option>
                <option value="suv" {{ old('vehicle_type', $driver->performance?->vehicle_type) == 'suv' ? 'selected' : '' }}>SUV</option>
                <option value="hatchback" {{ old('vehicle_type', $driver->performance?->vehicle_type) == 'hatchback' ? 'selected' : '' }}>Hatchback</option>
                <option value="pickup" {{ old('vehicle_type', $driver->performance?->vehicle_type) == 'pickup' ? 'selected' : '' }}>Pickup Truck</option>
                <option value="van" {{ old('vehicle_type', $driver->performance?->vehicle_type) == 'van' ? 'selected' : '' }}>Van/Minibus</option>
                <option value="motorcycle" {{ old('vehicle_type', $driver->performance?->vehicle_type) == 'motorcycle' ? 'selected' : '' }}>Motorcycle</option>
                <option value="tricycle" {{ old('vehicle_type', $driver->performance?->vehicle_type) == 'tricycle' ? 'selected' : '' }}>Tricycle (Keke)</option>
                <option value="any" {{ old('vehicle_type', $driver->performance?->vehicle_type) == 'any' ? 'selected' : '' }}>Any Available</option>
            </select>
            @error('vehicle_type')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<hr>

<h5 class="text-primary"><i class="fas fa-route"></i> Service Areas & Preferences</h5>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="preferred_areas">Preferred Service Areas</label>
            <select class="form-control @error('preferred_areas') is-invalid @enderror"
                    id="preferred_areas" name="preferred_areas[]" multiple>
                <option value="lagos_island" {{ in_array('lagos_island', old('preferred_areas', [])) ? 'selected' : '' }}>Lagos Island</option>
                <option value="lagos_mainland" {{ in_array('lagos_mainland', old('preferred_areas', [])) ? 'selected' : '' }}>Lagos Mainland</option>
                <option value="ikeja" {{ in_array('ikeja', old('preferred_areas', [])) ? 'selected' : '' }}>Ikeja</option>
                <option value="surulere" {{ in_array('surulere', old('preferred_areas', [])) ? 'selected' : '' }}>Surulere</option>
                <option value="yaba" {{ in_array('yaba', old('preferred_areas', [])) ? 'selected' : '' }}>Yaba</option>
                <option value="lekki" {{ in_array('lekki', old('preferred_areas', [])) ? 'selected' : '' }}>Lekki</option>
                <option value="ajah" {{ in_array('ajah', old('preferred_areas', [])) ? 'selected' : '' }}>Ajah</option>
                <option value="ikorodu" {{ in_array('ikorodu', old('preferred_areas', [])) ? 'selected' : '' }}>Ikorodu</option>
                <option value="ogun" {{ in_array('ogun', old('preferred_areas', [])) ? 'selected' : '' }}>Ogun State</option>
                <option value="anywhere" {{ in_array('anywhere', old('preferred_areas', [])) ? 'selected' : '' }}>Anywhere in Lagos/Ogun</option>
            </select>
            @error('preferred_areas')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="form-text text-muted">Hold Ctrl/Cmd to select multiple areas</small>
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label for="work_schedule">Preferred Work Schedule</label>
            <select class="form-control @error('work_schedule') is-invalid @enderror"
                    id="work_schedule" name="work_schedule">
                <option value="">Select Schedule</option>
                <option value="full_time" {{ old('work_schedule') == 'full_time' ? 'selected' : '' }}>Full Time (8+ hours/day)</option>
                <option value="part_time" {{ old('part_time') == 'part_time' ? 'selected' : '' }}>Part Time (4-6 hours/day)</option>
                <option value="flexible" {{ old('flexible') == 'flexible' ? 'selected' : '' }}>Flexible Hours</option>
                <option value="weekends_only" {{ old('weekends_only') == 'weekends_only' ? 'selected' : '' }}>Weekends Only</option>
                <option value="weekdays_only" {{ old('weekdays_only') == 'weekdays_only' ? 'selected' : '' }}>Weekdays Only</option>
            </select>
            @error('work_schedule')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<hr>

<h5 class="text-primary"><i class="fas fa-user-shield"></i> Guarantor Information (Optional)</h5>

<div class="form-group">
    <div class="custom-control custom-checkbox">
        <input class="custom-control-input" type="checkbox" id="has_guarantor" name="has_guarantor" value="1"
               {{ old('has_guarantor') ? 'checked' : '' }}>
        <label for="has_guarantor" class="custom-control-label">
            I have a guarantor who can vouch for my character and reliability
        </label>
    </div>
</div>

<div id="guarantor_fields" style="{{ old('has_guarantor') ? '' : 'display: none;' }}">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="guarantor_name">Guarantor Full Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('guarantor_name') is-invalid @enderror"
                       id="guarantor_name" name="guarantor_name" value="{{ old('guarantor_name') }}"
                       placeholder="Full name of guarantor">
                @error('guarantor_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label for="guarantor_phone">Guarantor Phone Number <span class="text-danger">*</span></label>
                <input type="tel" class="form-control @error('guarantor_phone') is-invalid @enderror"
                       id="guarantor_phone" name="guarantor_phone" value="{{ old('guarantor_phone') }}"
                       placeholder="+2348012345678">
                @error('guarantor_phone')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="guarantor_relationship">Relationship to Guarantor</label>
                <select class="form-control @error('guarantor_relationship') is-invalid @enderror"
                        id="guarantor_relationship" name="guarantor_relationship">
                    <option value="">Select Relationship</option>
                    <option value="employer" {{ old('guarantor_relationship') == 'employer' ? 'selected' : '' }}>Employer</option>
                    <option value="colleague" {{ old('guarantor_relationship') == 'colleague' ? 'selected' : '' }}>Colleague</option>
                    <option value="friend" {{ old('guarantor_relationship') == 'friend' ? 'selected' : '' }}>Friend</option>
                    <option value="family" {{ old('guarantor_relationship') == 'family' ? 'selected' : '' }}>Family Member</option>
                    <option value="other" {{ old('guarantor_relationship') == 'other' ? 'selected' : '' }}>Other</option>
                </select>
                @error('guarantor_relationship')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label for="guarantor_email">Guarantor Email</label>
                <input type="email" class="form-control @error('guarantor_email') is-invalid @enderror"
                       id="guarantor_email" name="guarantor_email" value="{{ old('guarantor_email') }}"
                       placeholder="guarantor@example.com">
                @error('guarantor_email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>
</div>

@if($driver->performance)
<div class="card border-info mt-4">
    <div class="card-header bg-light">
        <h6 class="card-title mb-0">
            <i class="fas fa-chart-line text-info"></i> Current Performance Metrics
        </h6>
    </div>
    <div class="card-body">
        <div class="row text-center">
            <div class="col-md-3">
                <div class="description-block">
                    <span class="description-header">{{ $driver->performance->total_jobs_completed ?? 0 }}</span>
                    <span class="description-text">JOBS COMPLETED</span>
                </div>
            </div>
            <div class="col-md-3">
                <div class="description-block">
                    <span class="description-header">{{ number_format($driver->performance->average_rating ?? 0, 1) }}</span>
                    <span class="description-text">AVERAGE RATING</span>
                </div>
            </div>
            <div class="col-md-3">
                <div class="description-block">₦{{ number_format($driver->performance->total_earnings ?? 0, 2) }}</span>
                    <span class="description-text">TOTAL EARNINGS</span>
                </div>
            </div>
            <div class="col-md-3">
                <div class="description-block">
                    <span class="description-header">{{ $driver->performance->success_rate ?? 0 }}%</span>
                    <span class="description-text">SUCCESS RATE</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="alert alert-success">
    <i class="fas fa-lightbulb"></i>
    <strong>Pro Tips:</strong>
    <ul class="mb-0 mt-2">
        <li>Be honest about your experience - this helps us match you with suitable opportunities</li>
        <li>Having a guarantor can speed up your verification process</li>
        <li>Specify your preferred areas to get more relevant job notifications</li>
        <li>Your performance metrics will be visible to potential clients</li>
    </ul>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Toggle guarantor fields
    $('#has_guarantor').change(function() {
        if ($(this).is(':checked')) {
            $('#guarantor_fields').show();
            $('#guarantor_name, #guarantor_phone').attr('required', true);
        } else {
            $('#guarantor_fields').hide();
            $('#guarantor_name, #guarantor_phone').removeAttr('required');
        }
    });

    // Initialize on page load
    if ($('#has_guarantor').is(':checked')) {
        $('#guarantor_fields').show();
    }

    // License expiry validation
    $('#license_expiry').change(function() {
        const expiryDate = new Date($(this).val());
        const today = new Date();
        const oneYearFromNow = new Date();
        oneYearFromNow.setFullYear(today.getFullYear() + 1);

        if (expiryDate < today) {
            alert('License expiry date cannot be in the past.');
            $(this).val('');
        } else if (expiryDate < oneYearFromNow) {
            if (!confirm('Your license expires within the next year. Are you sure this is correct?')) {
                $(this).val('');
            }
        }
    });
});
</script>
@endpush
