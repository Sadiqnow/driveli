@extends('layouts.company')

@section('title', 'Create Request - Company Portal')

@section('page-title', 'Create New Request')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('company.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('company.requests.index') }}">My Requests</a></li>
    <li class="breadcrumb-item active">Create Request</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8 mx-auto">
        <div class="company-card">
            <div class="company-card-header">
                <h5 class="mb-0"><i class="fas fa-plus me-2"></i>Create Driver Request</h5>
            </div>
            <div class="company-card-body">
                <form action="{{ route('company.requests.store') }}" method="POST" id="createRequestForm">
                    @csrf

                    <!-- Basic Information -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-muted mb-3"><i class="fas fa-info-circle me-2"></i>Basic Information</h6>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="position_title" class="form-label">Position Title <span class="text-danger">*</span></label>
                            <input type="text" name="position_title" id="position_title" class="form-control"
                                   value="{{ old('position_title') }}" placeholder="e.g., Senior Driver, Logistics Driver" required>
                            @error('position_title')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="request_type" class="form-label">Employment Type <span class="text-danger">*</span></label>
                            <select name="request_type" id="request_type" class="form-select" required>
                                <option value="">Select employment type</option>
                                <option value="full_time" {{ old('request_type') === 'full_time' ? 'selected' : '' }}>Full Time</option>
                                <option value="part_time" {{ old('request_type') === 'part_time' ? 'selected' : '' }}>Part Time</option>
                                <option value="contract" {{ old('request_type') === 'contract' ? 'selected' : '' }}>Contract</option>
                                <option value="temporary" {{ old('request_type') === 'temporary' ? 'selected' : '' }}>Temporary</option>
                            </select>
                            @error('request_type')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 mb-3">
                            <label for="description" class="form-label">Job Description <span class="text-danger">*</span></label>
                            <textarea name="description" id="description" class="form-control" rows="4"
                                      placeholder="Describe the role, responsibilities, and requirements..." required>{{ old('description') }}</textarea>
                            @error('description')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="location" class="form-label">Location <span class="text-danger">*</span></label>
                            <input type="text" name="location" id="location" class="form-control"
                                   value="{{ old('location') }}" placeholder="e.g., Lagos, Abuja" required>
                            @error('location')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="salary_range" class="form-label">Salary Range (Optional)</label>
                            <input type="text" name="salary_range" id="salary_range" class="form-control"
                                   value="{{ old('salary_range') }}" placeholder="e.g., ₦50,000 - ₦80,000">
                            @error('salary_range')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Requirements -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-muted mb-3"><i class="fas fa-check-circle me-2"></i>Driver Requirements</h6>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Requirements <span class="text-danger">*</span></label>
                            <div id="requirements-container">
                                @if(old('requirements'))
                                    @foreach(old('requirements') as $index => $requirement)
                                        <div class="input-group mb-2 requirement-item">
                                            <input type="text" name="requirements[]" class="form-control"
                                                   value="{{ $requirement }}" placeholder="e.g., Valid driver's license" required>
                                            <button type="button" class="btn btn-outline-danger remove-requirement" onclick="removeRequirement(this)">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="input-group mb-2 requirement-item">
                                        <input type="text" name="requirements[]" class="form-control"
                                               placeholder="e.g., Valid driver's license" required>
                                        <button type="button" class="btn btn-outline-danger remove-requirement" onclick="removeRequirement(this)">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                @endif
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm" onclick="addRequirement()">
                                <i class="fas fa-plus me-1"></i>Add Requirement
                            </button>
                            @error('requirements')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            @error('requirements.*')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Additional Details -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-muted mb-3"><i class="fas fa-cog me-2"></i>Additional Details</h6>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="drivers_needed" class="form-label">Number of Drivers Needed <span class="text-danger">*</span></label>
                            <input type="number" name="drivers_needed" id="drivers_needed" class="form-control"
                                   value="{{ old('drivers_needed', 1) }}" min="1" max="50" required>
                            @error('drivers_needed')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="urgency" class="form-label">Urgency Level <span class="text-danger">*</span></label>
                            <select name="urgency" id="urgency" class="form-select" required>
                                <option value="">Select urgency</option>
                                <option value="low" {{ old('urgency') === 'low' ? 'selected' : '' }}>Low - Within 1 month</option>
                                <option value="medium" {{ old('urgency') === 'medium' ? 'selected' : '' }}>Medium - Within 2 weeks</option>
                                <option value="high" {{ old('urgency') === 'high' ? 'selected' : '' }}>High - Within 1 week</option>
                                <option value="critical" {{ old('urgency') === 'critical' ? 'selected' : '' }}>Critical - Immediate</option>
                            </select>
                            @error('urgency')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="expires_at" class="form-label">Application Deadline (Optional)</label>
                            <input type="date" name="expires_at" id="expires_at" class="form-control"
                                   value="{{ old('expires_at') }}" min="{{ date('Y-m-d') }}">
                            <div class="form-text">Leave empty for no deadline</div>
                            @error('expires_at')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('company.requests.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Requests
                                </a>
                                <div>
                                    <button type="button" class="btn btn-outline-primary me-2" onclick="previewRequest()">
                                        <i class="fas fa-eye me-2"></i>Preview
                                    </button>
                                    <button type="submit" class="btn btn-company-primary">
                                        <i class="fas fa-paper-plane me-2"></i>Create Request
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('js')
<script>
function addRequirement() {
    const container = document.getElementById('requirements-container');
    const requirementItem = document.createElement('div');
    requirementItem.className = 'input-group mb-2 requirement-item';
    requirementItem.innerHTML = `
        <input type="text" name="requirements[]" class="form-control" placeholder="e.g., Valid driver's license" required>
        <button type="button" class="btn btn-outline-danger remove-requirement" onclick="removeRequirement(this)">
            <i class="fas fa-times"></i>
        </button>
    `;
    container.appendChild(requirementItem);
}

function removeRequirement(button) {
    const container = document.getElementById('requirements-container');
    const items = container.querySelectorAll('.requirement-item');

    if (items.length > 1) {
        button.closest('.requirement-item').remove();
    } else {
        // Clear the input instead of removing the last item
        button.previousElementSibling.value = '';
    }
}

function previewRequest() {
    // Basic form validation
    const form = document.getElementById('createRequestForm');
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    // Collect form data
    const formData = new FormData(form);
    let preview = 'Request Preview:\n\n';

    preview += `Position: ${formData.get('position_title')}\n`;
    preview += `Type: ${formData.get('request_type')}\n`;
    preview += `Location: ${formData.get('location')}\n`;
    preview += `Drivers Needed: ${formData.get('drivers_needed')}\n`;
    preview += `Urgency: ${formData.get('urgency')}\n\n`;

    preview += 'Requirements:\n';
    formData.getAll('requirements[]').forEach((req, index) => {
        if (req.trim()) {
            preview += `${index + 1}. ${req}\n`;
        }
    });

    alert(preview);
}

// Set minimum date for expiration
document.getElementById('expires_at').min = new Date().toISOString().split('T')[0];
</script>
@endsection
