<div class="alert alert-info">
    <i class="fas fa-info-circle"></i>
    <strong>Document Requirements:</strong> Please upload clear, high-quality images of the following documents.
    All documents will be verified during the review process.
</div>

<!-- Profile Picture -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-camera"></i> Profile Picture
            @if($driver->documents->where('document_type', 'profile_picture')->isNotEmpty())
                <span class="badge badge-success ml-2">Uploaded</span>
            @else
                <span class="badge badge-warning ml-2">Required</span>
            @endif
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-8">
                <div class="form-group">
                    <label for="profile_picture">Upload Profile Picture <span class="text-danger">*</span></label>
                    <input type="file" class="form-control-file @error('profile_picture') is-invalid @enderror"
                           id="profile_picture" name="profile_picture" accept="image/*">
                    @error('profile_picture')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">
                        Accepted formats: JPEG, PNG, JPG. Maximum size: 2MB. Use a clear, recent photo.
                    </small>
                </div>
            </div>
            <div class="col-md-4">
                @if($driver->documents->where('document_type', 'profile_picture')->isNotEmpty())
                    <div class="text-center">
                        <img src="{{ asset('storage/' . $driver->documents->where('document_type', 'profile_picture')->first()->document_path) }}"
                             alt="Profile Picture" class="img-thumbnail" style="max-width: 150px; max-height: 150px;">
                        <p class="mt-2"><small class="text-muted">Current Profile Picture</small></p>
                    </div>
                @else
                    <div class="text-center text-muted">
                        <i class="fas fa-user-circle fa-4x"></i>
                        <p class="mt-2">No profile picture uploaded</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- ID Document -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-id-card"></i> Government Issued ID
            @if($driver->documents->whereIn('document_type', ['id_card', 'passport'])->isNotEmpty())
                <span class="badge badge-success ml-2">Uploaded</span>
            @else
                <span class="badge badge-warning ml-2">Required</span>
            @endif
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="id_type">ID Type <span class="text-danger">*</span></label>
                    <select class="form-control @error('id_type') is-invalid @enderror" id="id_type" name="id_type">
                        <option value="">Select ID Type</option>
                        <option value="national_id" {{ old('id_type') == 'national_id' ? 'selected' : '' }}>National ID Card</option>
                        <option value="drivers_license" {{ old('id_type') == 'drivers_license' ? 'selected' : '' }}>Driver's License</option>
                        <option value="passport" {{ old('id_type') == 'passport' ? 'selected' : '' }}>International Passport</option>
                        <option value="voters_card" {{ old('id_type') == 'voters_card' ? 'selected' : '' }}>Voter's Card</option>
                    </select>
                    @error('id_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="id_number">ID Number <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('id_number') is-invalid @enderror"
                           id="id_number" name="id_number" value="{{ old('id_number') }}"
                           placeholder="Enter ID number">
                    @error('id_number')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="id_document">Upload ID Document <span class="text-danger">*</span></label>
            <input type="file" class="form-control-file @error('id_document') is-invalid @enderror"
                   id="id_document" name="id_document" accept="image/*,.pdf">
            @error('id_document')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="form-text text-muted">
                Accepted formats: JPEG, PNG, JPG, PDF. Maximum size: 5MB. Ensure all text is clearly visible.
            </small>
        </div>

        @if($driver->documents->whereIn('document_type', ['id_card', 'passport'])->isNotEmpty())
            <div class="mt-3">
                <p><strong>Current ID Document:</strong></p>
                <div class="border p-2 rounded">
                    @foreach($driver->documents->whereIn('document_type', ['id_card', 'passport']) as $doc)
                        <p class="mb-1">
                            <i class="fas fa-file"></i>
                            {{ ucfirst(str_replace('_', ' ', $doc->document_type)) }} -
                            <a href="{{ asset('storage/' . $doc->document_path) }}" target="_blank">View Document</a>
                        </p>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Driver's License -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-car"></i> Driver's License
            @if($driver->documents->where('document_type', 'drivers_license')->isNotEmpty())
                <span class="badge badge-success ml-2">Uploaded</span>
            @else
                <span class="badge badge-warning ml-2">Required</span>
            @endif
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="license_number">License Number <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('license_number') is-invalid @enderror"
                           id="license_number" name="license_number"
                           value="{{ old('license_number', $driver->performance?->license_number) }}"
                           placeholder="Enter license number">
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
                           value="{{ old('license_expiry', $driver->performance?->license_expiry_date) }}">
                    @error('license_expiry')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="drivers_license">Upload Driver's License <span class="text-danger">*</span></label>
            <input type="file" class="form-control-file @error('drivers_license') is-invalid @enderror"
                   id="drivers_license" name="drivers_license" accept="image/*,.pdf">
            @error('drivers_license')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="form-text text-muted">
                Accepted formats: JPEG, PNG, JPG, PDF. Maximum size: 5MB. Ensure license is valid and not expired.
            </small>
        </div>

        @if($driver->documents->where('document_type', 'drivers_license')->isNotEmpty())
            <div class="mt-3">
                <p><strong>Current Driver's License:</strong></p>
                <div class="border p-2 rounded">
                    @php $licenseDoc = $driver->documents->where('document_type', 'drivers_license')->first(); @endphp
                    <p class="mb-1">
                        <i class="fas fa-file"></i>
                        License Number: {{ $licenseDoc->metadata['license_number'] ?? 'N/A' }} -
                        <a href="{{ asset('storage/' . $licenseDoc->document_path) }}" target="_blank">View Document</a>
                    </p>
                </div>
            </div>
        @endif
    </div>
</div>

<div class="alert alert-warning">
    <i class="fas fa-exclamation-triangle"></i>
    <strong>Important Notes:</strong>
    <ul class="mb-0 mt-2">
        <li>All documents will be automatically verified using OCR technology</li>
        <li>Ensure documents are not expired and all information is clearly visible</li>
        <li>You can re-upload documents if needed during the review process</li>
        <li>Profile pictures should be professional and clearly show your face</li>
    </ul>
</div>
