<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="first_name">First Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('first_name') is-invalid @enderror"
                   id="first_name" name="first_name" value="{{ old('first_name', $driver->first_name) }}" required>
            @error('first_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label for="middle_name">Middle Name</label>
            <input type="text" class="form-control @error('middle_name') is-invalid @enderror"
                   id="middle_name" name="middle_name" value="{{ old('middle_name', $driver->middle_name) }}">
            @error('middle_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="surname">Surname <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('surname') is-invalid @enderror"
                   id="surname" name="surname" value="{{ old('surname', $driver->surname) }}" required>
            @error('surname')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label for="date_of_birth">Date of Birth <span class="text-danger">*</span></label>
            <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror"
                   id="date_of_birth" name="date_of_birth"
                   value="{{ old('date_of_birth', $driver->personalInfo?->date_of_birth) }}" required>
            @error('date_of_birth')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="gender">Gender <span class="text-danger">*</span></label>
            <select class="form-control @error('gender') is-invalid @enderror" id="gender" name="gender" required>
                <option value="">Select Gender</option>
                <option value="male" {{ old('gender', $driver->personalInfo?->gender) == 'male' ? 'selected' : '' }}>Male</option>
                <option value="female" {{ old('gender', $driver->personalInfo?->gender) == 'female' ? 'selected' : '' }}>Female</option>
                <option value="other" {{ old('gender', $driver->personalInfo?->gender) == 'other' ? 'selected' : '' }}>Other</option>
            </select>
            @error('gender')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label for="marital_status">Marital Status</label>
            <select class="form-control @error('marital_status') is-invalid @enderror" id="marital_status" name="marital_status">
                <option value="">Select Status</option>
                <option value="single" {{ old('marital_status', $driver->personalInfo?->marital_status) == 'single' ? 'selected' : '' }}>Single</option>
                <option value="married" {{ old('marital_status', $driver->personalInfo?->marital_status) == 'married' ? 'selected' : '' }}>Married</option>
                <option value="divorced" {{ old('marital_status', $driver->personalInfo?->marital_status) == 'divorced' ? 'selected' : '' }}>Divorced</option>
                <option value="widowed" {{ old('marital_status', $driver->personalInfo?->marital_status) == 'widowed' ? 'selected' : '' }}>Widowed</option>
            </select>
            @error('marital_status')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="nationality">Nationality</label>
            <input type="text" class="form-control @error('nationality') is-invalid @enderror"
                   id="nationality" name="nationality" value="{{ old('nationality', $driver->personalInfo?->nationality) }}"
                   placeholder="e.g., Nigerian">
            @error('nationality')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            <label for="state_of_origin">State of Origin</label>
            <input type="text" class="form-control @error('state_of_origin') is-invalid @enderror"
                   id="state_of_origin" name="state_of_origin" value="{{ old('state_of_origin', $driver->personalInfo?->state_of_origin) }}"
                   placeholder="e.g., Lagos">
            @error('state_of_origin')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="form-group">
    <label for="address">Residential Address</label>
    <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="3"
              placeholder="Full residential address">{{ old('address', $driver->personalInfo?->address) }}</textarea>
    @error('address')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="alert alert-info">
    <i class="fas fa-info-circle"></i>
    <strong>Note:</strong> This information is required for identity verification and compliance purposes.
    All personal data is handled securely and in accordance with data protection regulations.
</div>
