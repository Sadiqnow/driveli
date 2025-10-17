<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="phone">Primary Phone Number <span class="text-danger">*</span></label>
            <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                   id="phone" name="phone" value="{{ old('phone', $driver->phone) }}" required
                   placeholder="+2348012345678">
            @error('phone')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="form-text text-muted">Include country code (e.g., +234 for Nigeria)</small>
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label for="phone_2">Secondary Phone Number</label>
            <input type="tel" class="form-control @error('phone_2') is-invalid @enderror"
                   id="phone_2" name="phone_2" value="{{ old('phone_2', $driver->phone_2) }}"
                   placeholder="+2348098765432">
            @error('phone_2')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="form-text text-muted">Optional backup number</small>
        </div>
    </div>
</div>

<hr>

<h5 class="text-primary"><i class="fas fa-user-friends"></i> Emergency Contact Information</h5>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="emergency_contact_name">Emergency Contact Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('emergency_contact_name') is-invalid @enderror"
                   id="emergency_contact_name" name="emergency_contact_name"
                   value="{{ old('emergency_contact_name', $driver->personalInfo?->name) }}" required>
            @error('emergency_contact_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label for="emergency_contact_relationship">Relationship <span class="text-danger">*</span></label>
            <select class="form-control @error('emergency_contact_relationship') is-invalid @enderror"
                    id="emergency_contact_relationship" name="emergency_contact_relationship" required>
                <option value="">Select Relationship</option>
                <option value="spouse" {{ old('emergency_contact_relationship', $driver->personalInfo?->relationship) == 'spouse' ? 'selected' : '' }}>Spouse</option>
                <option value="parent" {{ old('emergency_contact_relationship', $driver->personalInfo?->relationship) == 'parent' ? 'selected' : '' }}>Parent</option>
                <option value="sibling" {{ old('emergency_contact_relationship', $driver->personalInfo?->relationship) == 'sibling' ? 'selected' : '' }}>Sibling</option>
                <option value="child" {{ old('emergency_contact_relationship', $driver->personalInfo?->relationship) == 'child' ? 'selected' : '' }}>Child</option>
                <option value="friend" {{ old('emergency_contact_relationship', $driver->personalInfo?->relationship) == 'friend' ? 'selected' : '' }}>Friend</option>
                <option value="other" {{ old('emergency_contact_relationship', $driver->personalInfo?->relationship) == 'other' ? 'selected' : '' }}>Other</option>
            </select>
            @error('emergency_contact_relationship')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="emergency_contact_phone">Emergency Contact Phone <span class="text-danger">*</span></label>
            <input type="tel" class="form-control @error('emergency_contact_phone') is-invalid @enderror"
                   id="emergency_contact_phone" name="emergency_contact_phone"
                   value="{{ old('emergency_contact_phone', $driver->personalInfo?->phone) }}" required
                   placeholder="+2348012345678">
            @error('emergency_contact_phone')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label for="emergency_contact_email">Emergency Contact Email</label>
            <input type="email" class="form-control @error('emergency_contact_email') is-invalid @enderror"
                   id="emergency_contact_email" name="emergency_contact_email"
                   value="{{ old('emergency_contact_email', $driver->personalInfo?->email) }}"
                   placeholder="contact@example.com">
            @error('emergency_contact_email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<hr>

<h5 class="text-primary"><i class="fas fa-map-marker-alt"></i> Address Information</h5>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="city">City</label>
            <input type="text" class="form-control @error('city') is-invalid @enderror"
                   id="city" name="city" value="{{ old('city', $driver->personalInfo?->city) }}"
                   placeholder="e.g., Lagos">
            @error('city')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label for="state">State</label>
            <input type="text" class="form-control @error('state') is-invalid @enderror"
                   id="state" name="state" value="{{ old('state', $driver->personalInfo?->state) }}"
                   placeholder="e.g., Lagos State">
            @error('state')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="form-group">
    <label for="postal_code">Postal Code</label>
    <input type="text" class="form-control @error('postal_code') is-invalid @enderror"
           id="postal_code" name="postal_code" value="{{ old('postal_code', $driver->personalInfo?->postal_code) }}"
           placeholder="e.g., 100001">
    @error('postal_code')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="alert alert-warning">
    <i class="fas fa-exclamation-triangle"></i>
    <strong>Important:</strong> Emergency contact information will only be used in case of emergencies.
    Please ensure the contact details are accurate and the person is aware they may be contacted.
</div>
