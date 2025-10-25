@extends('company.layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0"><i class="bi bi-plus-circle"></i> Create New Transport Request</h2>
            <a href="{{ route('company.requests.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Requests
            </a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Request Details</h5>
    </div>
    <div class="card-body">
        <form id="requestForm" method="POST" action="{{ route('company.requests.store') }}">
            @csrf

            <!-- Step 1: Basic Information -->
            <div class="step" id="step1">
                <h6 class="text-primary mb-3"><i class="bi bi-1-circle"></i> Basic Information</h6>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="pickup_location" class="form-label">Pickup Location *</label>
                        <input type="text" class="form-control @error('pickup_location') is-invalid @enderror"
                               id="pickup_location" name="pickup_location" value="{{ old('pickup_location') }}" required>
                        @error('pickup_location')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="dropoff_location" class="form-label">Drop-off Location</label>
                        <input type="text" class="form-control @error('dropoff_location') is-invalid @enderror"
                               id="dropoff_location" name="dropoff_location" value="{{ old('dropoff_location') }}">
                        @error('dropoff_location')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="pickup_state_id" class="form-label">Pickup State *</label>
                        <select class="form-select @error('pickup_state_id') is-invalid @enderror"
                                id="pickup_state_id" name="pickup_state_id" required>
                            <option value="">Select State</option>
                            <!-- States will be loaded via AJAX -->
                        </select>
                        @error('pickup_state_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="pickup_lga_id" class="form-label">Pickup LGA *</label>
                        <select class="form-select @error('pickup_lga_id') is-invalid @enderror"
                                id="pickup_lga_id" name="pickup_lga_id" required disabled>
                            <option value="">Select LGA</option>
                        </select>
                        @error('pickup_lga_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="dropoff_state_id" class="form-label">Drop-off State</label>
                        <select class="form-select @error('dropoff_state_id') is-invalid @enderror"
                                id="dropoff_state_id" name="dropoff_state_id">
                            <option value="">Select State</option>
                        </select>
                        @error('dropoff_state_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="dropoff_lga_id" class="form-label">Drop-off LGA</label>
                        <select class="form-select @error('dropoff_lga_id') is-invalid @enderror"
                                id="dropoff_lga_id" name="dropoff_lga_id" disabled>
                            <option value="">Select LGA</option>
                        </select>
                        @error('dropoff_lga_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="vehicle_type" class="form-label">Vehicle Type *</label>
                        <select class="form-select @error('vehicle_type') is-invalid @enderror"
                                id="vehicle_type" name="vehicle_type" required>
                            <option value="">Select Vehicle Type</option>
                            <option value="truck" {{ old('vehicle_type') == 'truck' ? 'selected' : '' }}>Truck</option>
                            <option value="van" {{ old('vehicle_type') == 'van' ? 'selected' : '' }}>Van</option>
                            <option value="pickup" {{ old('vehicle_type') == 'pickup' ? 'selected' : '' }}>Pickup Truck</option>
                            <option value="motorcycle" {{ old('vehicle_type') == 'motorcycle' ? 'selected' : '' }}>Motorcycle</option>
                            <option value="car" {{ old('vehicle_type') == 'car' ? 'selected' : '' }}>Car</option>
                        </select>
                        @error('vehicle_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="urgency" class="form-label">Urgency Level *</label>
                        <select class="form-select @error('urgency') is-invalid @enderror"
                                id="urgency" name="urgency" required>
                            <option value="">Select Urgency</option>
                            <option value="low" {{ old('urgency') == 'low' ? 'selected' : '' }}>Low</option>
                            <option value="medium" {{ old('urgency') == 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="high" {{ old('urgency') == 'high' ? 'selected' : '' }}>High</option>
                            <option value="critical" {{ old('urgency') == 'critical' ? 'selected' : '' }}>Critical</option>
                        </select>
                        @error('urgency')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="button" class="btn btn-primary next-step">Next <i class="bi bi-arrow-right"></i></button>
                </div>
            </div>

            <!-- Step 2: Cargo Details -->
            <div class="step d-none" id="step2">
                <h6 class="text-primary mb-3"><i class="bi bi-2-circle"></i> Cargo Details</h6>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="cargo_type" class="form-label">Cargo Type</label>
                        <select class="form-select @error('cargo_type') is-invalid @enderror" id="cargo_type" name="cargo_type">
                            <option value="">Select Cargo Type</option>
                            <option value="general" {{ old('cargo_type') == 'general' ? 'selected' : '' }}>General Goods</option>
                            <option value="perishable" {{ old('cargo_type') == 'perishable' ? 'selected' : '' }}>Perishable Goods</option>
                            <option value="fragile" {{ old('cargo_type') == 'fragile' ? 'selected' : '' }}>Fragile Items</option>
                            <option value="hazardous" {{ old('cargo_type') == 'hazardous' ? 'selected' : '' }}>Hazardous Materials</option>
                            <option value="documents" {{ old('cargo_type') == 'documents' ? 'selected' : '' }}>Documents</option>
                            <option value="machinery" {{ old('cargo_type') == 'machinery' ? 'selected' : '' }}>Machinery/Equipment</option>
                        </select>
                        @error('cargo_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="weight_kg" class="form-label">Weight (kg)</label>
                        <input type="number" step="0.01" class="form-control @error('weight_kg') is-invalid @enderror"
                               id="weight_kg" name="weight_kg" value="{{ old('weight_kg') }}" min="0">
                        @error('weight_kg')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="value_naira" class="form-label">Cargo Value (₦)</label>
                        <input type="number" step="0.01" class="form-control @error('value_naira') is-invalid @enderror"
                               id="value_naira" name="value_naira" value="{{ old('value_naira') }}" min="0">
                        @error('value_naira')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="pickup_date" class="form-label">Pickup Date & Time *</label>
                        <input type="datetime-local" class="form-control @error('pickup_date') is-invalid @enderror"
                               id="pickup_date" name="pickup_date" value="{{ old('pickup_date') }}" required>
                        @error('pickup_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="cargo_description" class="form-label">Cargo Description</label>
                    <textarea class="form-control @error('cargo_description') is-invalid @enderror"
                              id="cargo_description" name="cargo_description" rows="3">{{ old('cargo_description') }}</textarea>
                    @error('cargo_description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary prev-step"><i class="bi bi-arrow-left"></i> Previous</button>
                    <button type="button" class="btn btn-primary next-step">Next <i class="bi bi-arrow-right"></i></button>
                </div>
            </div>

            <!-- Step 3: Requirements & Budget -->
            <div class="step d-none" id="step3">
                <h6 class="text-primary mb-3"><i class="bi bi-3-circle"></i> Requirements & Budget</h6>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="budget_min" class="form-label">Minimum Budget (₦)</label>
                        <input type="number" step="0.01" class="form-control @error('budget_min') is-invalid @enderror"
                               id="budget_min" name="budget_min" value="{{ old('budget_min') }}" min="0">
                        @error('budget_min')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="budget_max" class="form-label">Maximum Budget (₦)</label>
                        <input type="number" step="0.01" class="form-control @error('budget_max') is-invalid @enderror"
                               id="budget_max" name="budget_max" value="{{ old('budget_max') }}" min="0">
                        @error('budget_max')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="experience_required" class="form-label">Driver Experience Required (years)</label>
                        <input type="number" class="form-control @error('experience_required') is-invalid @enderror"
                               id="experience_required" name="experience_required" value="{{ old('experience_required', 1) }}" min="0" max="50">
                        @error('experience_required')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="delivery_deadline" class="form-label">Delivery Deadline</label>
                        <input type="datetime-local" class="form-control @error('delivery_deadline') is-invalid @enderror"
                               id="delivery_deadline" name="delivery_deadline" value="{{ old('delivery_deadline') }}">
                        @error('delivery_deadline')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="special_requirements" class="form-label">Special Requirements</label>
                    <textarea class="form-control @error('special_requirements') is-invalid @enderror"
                              id="special_requirements" name="special_requirements" rows="3"
                              placeholder="Any special requirements for the transport...">{{ old('special_requirements') }}</textarea>
                    @error('special_requirements')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary prev-step"><i class="bi bi-arrow-left"></i> Previous</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle"></i> Create Request
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentStep = 1;
    const totalSteps = 3;

    // Load states
    loadStates();

    // Step navigation
    document.querySelectorAll('.next-step').forEach(button => {
        button.addEventListener('click', () => {
            if (validateCurrentStep()) {
                showStep(currentStep + 1);
            }
        });
    });

    document.querySelectorAll('.prev-step').forEach(button => {
        button.addEventListener('click', () => {
            showStep(currentStep - 1);
        });
    });

    function showStep(step) {
        document.querySelectorAll('.step').forEach(s => s.classList.add('d-none'));
        document.getElementById('step' + step).classList.remove('d-none');
        currentStep = step;
    }

    function validateCurrentStep() {
        const stepElement = document.getElementById('step' + currentStep);
        const requiredFields = stepElement.querySelectorAll('[required]');
        let isValid = true;

        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });

        return isValid;
    }

    function loadStates() {
        fetch('/api/location/states')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    const stateSelects = ['pickup_state_id', 'dropoff_state_id'];
                    stateSelects.forEach(selectId => {
                        const select = document.getElementById(selectId);
                        select.innerHTML = '<option value="">Select State</option>';
                        data.data.forEach(state => {
                            select.innerHTML += `<option value="${state.id}">${state.name}</option>`;
                        });
                    });
                }
            })
            .catch(error => console.error('Error loading states:', error));
    }

    // State change handlers
    document.getElementById('pickup_state_id').addEventListener('change', function() {
        loadLGAs(this.value, 'pickup_lga_id');
    });

    document.getElementById('dropoff_state_id').addEventListener('change', function() {
        loadLGAs(this.value, 'dropoff_lga_id');
    });

    function loadLGAs(stateId, lgaSelectId) {
        const lgaSelect = document.getElementById(lgaSelectId);
        if (!stateId) {
            lgaSelect.innerHTML = '<option value="">Select LGA</option>';
            lgaSelect.disabled = true;
            return;
        }

        fetch(`/api/location/states/${stateId}/lgas`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    lgaSelect.innerHTML = '<option value="">Select LGA</option>';
                    data.data.forEach(lga => {
                        lgaSelect.innerHTML += `<option value="${lga.id}">${lga.name}</option>`;
                    });
                    lgaSelect.disabled = false;
                }
            })
            .catch(error => console.error('Error loading LGAs:', error));
    }

    // Budget validation
    document.getElementById('budget_min').addEventListener('input', validateBudget);
    document.getElementById('budget_max').addEventListener('input', validateBudget);

    function validateBudget() {
        const min = parseFloat(document.getElementById('budget_min').value) || 0;
        const max = parseFloat(document.getElementById('budget_max').value) || 0;

        if (max > 0 && min > max) {
            document.getElementById('budget_min').classList.add('is-invalid');
            document.getElementById('budget_max').classList.add('is-invalid');
        } else {
            document.getElementById('budget_min').classList.remove('is-invalid');
            document.getElementById('budget_max').classList.remove('is-invalid');
        }
    }
});
</script>
@endpush
@endsection
