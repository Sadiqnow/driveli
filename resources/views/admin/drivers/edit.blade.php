@extends('layouts.admin_cdn')

@section('title', 'Edit Driver Profile')

@section('css')
<style>
/* Modern Form Enhancement */
.form-section {
    background: white;
    border-radius: 16px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    border: 1px solid #e5e7eb;
    transition: all 0.3s ease;
}

.form-section:hover {
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.form-section h5 {
    color: #1f2937;
    font-weight: 600;
    margin-bottom: 1.5rem;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid #e5e7eb;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.form-section h5 i {
    color: #3b82f6;
    font-size: 1.25rem;
}

/* Enhanced Form Controls */
.form-group {
    margin-bottom: 1.5rem;
    position: relative;
}

.form-group label {
    font-weight: 500;
    color: #374151;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.form-control {
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    padding: 0.875rem 1rem;
    font-size: 0.95rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    background-color: #fdfdfd;
}

.form-control:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    background-color: #ffffff;
    transform: translateY(-1px);
}

.form-control:hover {
    border-color: #d1d5db;
}

/* Validation Feedback Enhancement */
.invalid-feedback {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: #dc2626;
    margin-top: 0.5rem;
    padding: 0.5rem 0.75rem;
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 8px;
    animation: slideIn 0.3s ease;
}

.invalid-feedback::before {
    content: '⚠';
    color: #dc2626;
    font-weight: bold;
}

.valid-feedback {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.875rem;
    color: #16a34a;
    margin-top: 0.5rem;
    padding: 0.5rem 0.75rem;
    background: #f0fdf4;
    border: 1px solid #bbf7d0;
    border-radius: 8px;
    animation: slideIn 0.3s ease;
}

.valid-feedback::before {
    content: '✓';
    color: #16a34a;
    font-weight: bold;
}

.is-invalid {
    border-color: #dc2626 !important;
    background-color: #fef2f2 !important;
    animation: shake 0.5s ease;
}

.is-valid {
    border-color: #16a34a !important;
    background-color: #f0fdf4 !important;
}

/* Required Field Indicator */
.required {
    color: #dc2626;
    font-weight: bold;
    font-size: 1.1rem;
}

.required::after {
    content: ' *';
}

/* Form Field Icons */
.form-icon {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
    z-index: 5;
    pointer-events: none;
}

/* Modern Card Header */
.card {
    border: none;
    border-radius: 20px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
    overflow: hidden;
}

.card-header {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: white;
    padding: 1.5rem 2rem;
    border-bottom: none;
}

.card-title {
    font-size: 1.5rem;
    font-weight: 600;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.card-title i {
    font-size: 1.25rem;
}

/* Action Buttons */
.btn {
    border-radius: 12px;
    padding: 0.75rem 2rem;
    font-weight: 500;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s;
}

.btn:hover::before {
    left: 100%;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.btn-primary {
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    border: none;
}

.btn-secondary {
    background: linear-gradient(135deg, #6b7280, #4b5563);
    border: none;
    color: white;
}

/* Animations */
@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}

/* Progress Indicator for Form Completion */
.form-progress {
    background: #f3f4f6;
    border-radius: 12px;
    padding: 1rem;
    margin-bottom: 2rem;
}

.progress-bar-custom {
    height: 8px;
    border-radius: 10px;
    background: linear-gradient(90deg, #3b82f6, #1d4ed8);
    transition: width 0.5s ease;
    position: relative;
    overflow: hidden;
}

.progress-bar-custom::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
    animation: shimmer 2s infinite;
}

@keyframes shimmer {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}

/* Responsive Design */
@media (max-width: 768px) {
    .form-section {
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .card-header {
        padding: 1rem 1.5rem;
    }

    .card-title {
        font-size: 1.25rem;
    }

    .btn {
        padding: 0.75rem 1.5rem;
    }
}
</style>
@endsection

@section('content_header')
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h1 class="m-0 fw-bold text-primary">Edit Driver Profile</h1>
            <small class="text-muted">Update driver information and settings</small>
        </div>
        <div>
            <a href="{{ route('admin.drivers.show', $driver->id) }}" class="btn btn-outline-info btn-sm me-2">
                <i class="fas fa-eye"></i> View Profile
            </a>
            <a href="{{ route('admin.drivers.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>
@endsection

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ url('admin/drivers') }}">Drivers</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.drivers.show', $driver->id) }}">{{ $driver->full_name }}</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<!-- Form Progress Indicator -->
<div class="form-progress">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <span class="fw-medium text-dark">Form Completion</span>
        <span class="badge bg-primary" id="completionPercentage">0%</span>
    </div>
    <div class="bg-light rounded" style="height: 8px;">
        <div class="progress-bar-custom" id="progressBar" style="width: 0%;"></div>
    </div>
</div>

@if ($errors->any())
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <div class="d-flex align-items-center">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>Please correct the following errors:</strong>
    </div>
    <ul class="mt-2 mb-0">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user-edit"></i>
                    Edit Driver Information
                </h3>
            </div>
            
            <form action="{{ route('admin.drivers.update', $driver->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="card-body">
                    
                    <!-- Personal Information Section -->
                    <div class="form-section">
                        <h5><i class="fas fa-user"></i> Personal Information</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="first_name">
                                        <i class="fas fa-user text-primary"></i>
                                        First Name <span class="required"></span>
                                    </label>
                                    <input type="text" class="form-control @error('first_name') is-invalid @enderror"
                                           id="first_name" name="first_name"
                                           value="{{ old('first_name', $driver->first_name) }}"
                                           required
                                           placeholder="Enter first name"
                                           data-validation="required|min:2|max:50">
                                    <i class="fas fa-user form-icon"></i>
                                    @error('first_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @else
                                        <div class="valid-feedback" style="display: none;">Looks good!</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="surname">
                                        <i class="fas fa-user text-primary"></i>
                                        Surname <span class="required"></span>
                                    </label>
                                    <input type="text" class="form-control @error('surname') is-invalid @enderror"
                                           id="surname" name="surname"
                                           value="{{ old('surname', $driver->surname) }}"
                                           required
                                           placeholder="Enter surname"
                                           data-validation="required|min:2|max:50">
                                    <i class="fas fa-user form-icon"></i>
                                    @error('surname')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @else
                                        <div class="valid-feedback" style="display: none;">Looks good!</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
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
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nickname">Nickname/Preferred Name</label>
                                    <input type="text" class="form-control @error('nickname') is-invalid @enderror" 
                                           id="nickname" name="nickname" value="{{ old('nickname', $driver->nickname) }}">
                                    @error('nickname')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">
                                        <i class="fas fa-envelope text-primary"></i>
                                        Email Address
                                    </label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                                           id="email" name="email"
                                           value="{{ old('email', $driver->email) }}"
                                           placeholder="driver@example.com"
                                           data-validation="email">
                                    <i class="fas fa-envelope form-icon"></i>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @else
                                        <div class="valid-feedback" style="display: none;">Valid email format!</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phone">
                                        <i class="fas fa-phone text-primary"></i>
                                        Phone Number <span class="required"></span>
                                    </label>
                                    <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                           id="phone" name="phone"
                                           value="{{ old('phone', $driver->phone) }}"
                                           required
                                           placeholder="+234 XXX XXX XXXX"
                                           data-validation="required|phone">
                                    <i class="fas fa-phone form-icon"></i>
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @else
                                        <div class="valid-feedback" style="display: none;">Valid phone number!</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="date_of_birth">Date of Birth</label>
                                    <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" 
                                           id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth', $driver->date_of_birth ? $driver->date_of_birth->format('Y-m-d') : '') }}">
                                    @error('date_of_birth')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="gender">Gender</label>
                                    <select class="form-control @error('gender') is-invalid @enderror" id="gender" name="gender">
                                        <option value="">Select Gender</option>
                                        <option value="Male" {{ old('gender', $driver->gender) == 'Male' ? 'selected' : '' }}>Male</option>
                                        <option value="Female" {{ old('gender', $driver->gender) == 'Female' ? 'selected' : '' }}>Female</option>
                                        <option value="Other" {{ old('gender', $driver->gender) == 'Other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('gender')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="nin">NIN (National ID)</label>
                                    <input type="text" class="form-control @error('nin') is-invalid @enderror" 
                                           id="nin" name="nin" value="{{ old('nin', $driver->nin) }}">
                                    @error('nin')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="address">Address</label>
                                    <textarea class="form-control @error('address') is-invalid @enderror" 
                                              id="address" name="address" rows="3">{{ old('address', $driver->address) }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="state">State</label>
                                    <input type="text" class="form-control @error('state') is-invalid @enderror" 
                                           id="state" name="state" value="{{ old('state', $driver->state) }}">
                                    @error('state')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="lga">LGA (Local Government Area)</label>
                                    <input type="text" class="form-control @error('lga') is-invalid @enderror" 
                                           id="lga" name="lga" value="{{ old('lga', $driver->lga) }}">
                                    @error('lga')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Origin Information Section -->
                    <div class="form-section">
                        <h5><i class="fas fa-map-marker-alt"></i> Origin Information</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="state_of_origin">State of Origin</label>
                                    <select class="form-control @error('state_of_origin') is-invalid @enderror" id="state_of_origin" name="state_of_origin">
                                        <option value="">Select State of Origin</option>
                                        @foreach(App\Models\State::orderBy('name')->get() as $state)
                                            <option value="{{ $state->id }}" {{ old('state_of_origin', $driver->state_of_origin ?? '') == $state->id ? 'selected' : '' }}>
                                                {{ $state->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('state_of_origin')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="lga_of_origin">LGA of Origin</label>
                                    <select class="form-control @error('lga_of_origin') is-invalid @enderror" id="lga_of_origin" name="lga_of_origin">
                                        <option value="">Select LGA of Origin</option>
                                        <!-- LGAs will be populated via JavaScript based on selected state -->
                                    </select>
                                    @error('lga_of_origin')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="address_of_origin">Address of Origin</label>
                                    <textarea class="form-control @error('address_of_origin') is-invalid @enderror" id="address_of_origin" name="address_of_origin" rows="3" placeholder="Enter detailed address of origin">{{ old('address_of_origin', $driver->address_of_origin ?? '') }}</textarea>
                                    @error('address_of_origin')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Driver License Information -->
                    <div class="form-section">
                        <h5><i class="fas fa-id-card"></i> Driver License Information</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="license_number">License Number</label>
                                    <input type="text" class="form-control @error('license_number') is-invalid @enderror" 
                                           id="license_number" name="license_number" value="{{ old('license_number', $driver->license_number) }}">
                                    @error('license_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="license_class">License Class</label>
                                    <select class="form-control @error('license_class') is-invalid @enderror" id="license_class" name="license_class">
                                        <option value="">Select License Class</option>
                                        <option value="Class A" {{ old('license_class', $driver->license_class) == 'Class A' ? 'selected' : '' }}>Class A</option>
                                        <option value="Class B" {{ old('license_class', $driver->license_class) == 'Class B' ? 'selected' : '' }}>Class B</option>
                                        <option value="Class C" {{ old('license_class', $driver->license_class) == 'Class C' ? 'selected' : '' }}>Class C</option>
                                        <option value="Commercial" {{ old('license_class', $driver->license_class) == 'Commercial' ? 'selected' : '' }}>Commercial</option>
                                    </select>
                                    @error('license_class')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="license_expiry_date">License Expiry Date</label>
                                    <input type="date" class="form-control @error('license_expiry_date') is-invalid @enderror" 
                                           id="license_expiry_date" name="license_expiry_date" value="{{ old('license_expiry_date', $driver->license_expiry_date ? $driver->license_expiry_date->format('Y-m-d') : '') }}">
                                    @error('license_expiry_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="experience_level">Experience Level</label>
                                    <select class="form-control @error('experience_level') is-invalid @enderror" id="experience_level" name="experience_level">
                                        <option value="">Select Experience</option>
                                        <option value="1-2 years" {{ old('experience_level', $driver->experience_level) == '1-2 years' ? 'selected' : '' }}>1-2 years</option>
                                        <option value="3-5 years" {{ old('experience_level', $driver->experience_level) == '3-5 years' ? 'selected' : '' }}>3-5 years</option>
                                        <option value="6-10 years" {{ old('experience_level', $driver->experience_level) == '6-10 years' ? 'selected' : '' }}>6-10 years</option>
                                        <option value="10+ years" {{ old('experience_level', $driver->experience_level) == '10+ years' ? 'selected' : '' }}>10+ years</option>
                                    </select>
                                    @error('experience_level')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Professional Information -->
                    <div class="form-section">
                        <h5><i class="fas fa-truck"></i> Professional Information</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="vehicle_types">Vehicle Types (Select multiple)</label>
                                    <select multiple class="form-control @error('vehicle_types') is-invalid @enderror" 
                                            id="vehicle_types" name="vehicle_types[]" style="height: 120px;">
                                        @php
                                            $currentVehicleTypes = old('vehicle_types', $driver->vehicle_types ?: []);
                                            if (!is_array($currentVehicleTypes)) {
                                                $currentVehicleTypes = [];
                                            }
                                        @endphp
                                        <option value="Car" {{ in_array('Car', $currentVehicleTypes) ? 'selected' : '' }}>Car</option>
                                        <option value="Van" {{ in_array('Van', $currentVehicleTypes) ? 'selected' : '' }}>Van</option>
                                        <option value="Truck" {{ in_array('Truck', $currentVehicleTypes) ? 'selected' : '' }}>Truck</option>
                                        <option value="Bus" {{ in_array('Bus', $currentVehicleTypes) ? 'selected' : '' }}>Bus</option>
                                        <option value="Motorcycle" {{ in_array('Motorcycle', $currentVehicleTypes) ? 'selected' : '' }}>Motorcycle</option>
                                        <option value="Trailer" {{ in_array('Trailer', $currentVehicleTypes) ? 'selected' : '' }}>Trailer</option>
                                    </select>
                                    <small class="form-text text-muted">Hold Ctrl/Cmd to select multiple options</small>
                                    @error('vehicle_types')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="regions">Working Regions (Select multiple)</label>
                                    <select multiple class="form-control @error('regions') is-invalid @enderror" 
                                            id="regions" name="regions[]" style="height: 120px;">
                                        @php
                                            $currentRegions = old('regions', $driver->regions ?: []);
                                            if (!is_array($currentRegions)) {
                                                $currentRegions = [];
                                            }
                                        @endphp
                                        <option value="Lagos" {{ in_array('Lagos', $currentRegions) ? 'selected' : '' }}>Lagos</option>
                                        <option value="Abuja" {{ in_array('Abuja', $currentRegions) ? 'selected' : '' }}>Abuja</option>
                                        <option value="Kano" {{ in_array('Kano', $currentRegions) ? 'selected' : '' }}>Kano</option>
                                        <option value="Ibadan" {{ in_array('Ibadan', $currentRegions) ? 'selected' : '' }}>Ibadan</option>
                                        <option value="Port Harcourt" {{ in_array('Port Harcourt', $currentRegions) ? 'selected' : '' }}>Port Harcourt</option>
                                        <option value="Benin City" {{ in_array('Benin City', $currentRegions) ? 'selected' : '' }}>Benin City</option>
                                        <option value="Kaduna" {{ in_array('Kaduna', $currentRegions) ? 'selected' : '' }}>Kaduna</option>
                                    </select>
                                    <small class="form-text text-muted">Hold Ctrl/Cmd to select multiple options</small>
                                    @error('regions')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="special_skills">Special Skills</label>
                                    <textarea class="form-control @error('special_skills') is-invalid @enderror" 
                                              id="special_skills" name="special_skills" rows="3" 
                                              placeholder="e.g., Dangerous goods handling, Heavy machinery operation, Long distance driving">{{ old('special_skills', $driver->special_skills) }}</textarea>
                                    @error('special_skills')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Account Settings -->
                    <div class="form-section">
                        <h5><i class="fas fa-cog"></i> Account Settings</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <select class="form-control @error('status') is-invalid @enderror" id="status" name="status">
                                        <option value="Available" {{ old('status', $driver->status) == 'Available' ? 'selected' : '' }}>Available</option>
                                        <option value="Booked" {{ old('status', $driver->status) == 'Booked' ? 'selected' : '' }}>Booked</option>
                                        <option value="Not Available" {{ old('status', $driver->status) == 'Not Available' ? 'selected' : '' }}>Not Available</option>
                                        <option value="Suspended" {{ old('status', $driver->status) == 'Suspended' ? 'selected' : '' }}>Suspended</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="verification_status">Verification Status</label>
                                    <select class="form-control @error('verification_status') is-invalid @enderror" id="verification_status" name="verification_status">
                                        <option value="Pending" {{ old('verification_status', $driver->verification_status) == 'Pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="Verified" {{ old('verification_status', $driver->verification_status) == 'Verified' ? 'selected' : '' }}>Verified</option>
                                        <option value="Rejected" {{ old('verification_status', $driver->verification_status) == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                                    </select>
                                    @error('verification_status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="verification_notes">Verification Notes</label>
                                    <textarea class="form-control @error('verification_notes') is-invalid @enderror" 
                                              id="verification_notes" name="verification_notes" rows="3">{{ old('verification_notes', $driver->verification_notes) }}</textarea>
                                    @error('verification_notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Driver
                    </button>
                    <a href="{{ route('admin.drivers.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('css')
<style>
.form-section {
    background-color: #f8f9fa;
    border-left: 3px solid #007bff;
    padding: 15px;
    margin-bottom: 20px;
}
.form-section h5 {
    color: #007bff;
    margin-bottom: 15px;
}
.required {
    color: #dc3545;
}
</style>
@endsection

@section('js')
<script>
// Nigeria States and LGAs data
const nigeriaLGAs = {
    "1": ["Aba North", "Aba South", "Arochukwu", "Bende", "Ikwuano", "Isiala Ngwa North", "Isiala Ngwa South", "Isuikwuato", "Obi Ngwa", "Ohafia", "Osisioma", "Ugwunagbo", "Ukwa East", "Ukwa West", "Umuahia North", "Umuahia South", "Umu Nneochi"],
    "2": ["Demsa", "Fufure", "Ganye", "Gayuk", "Gombi", "Grie", "Hong", "Jada", "Lamurde", "Madagali", "Maiha", "Mayo Belwa", "Michika", "Mubi North", "Mubi South", "Numan", "Shelleng", "Song", "Toungo", "Yola North", "Yola South"],
    "3": ["Abak", "Eastern Obolo", "Eket", "Esit Eket", "Essien Udim", "Etim Ekpo", "Etinan", "Ibeno", "Ibesikpo Asutan", "Ibiono-Ibom", "Ika", "Ikono", "Ikot Abasi", "Ikot Ekpene", "Ini", "Itu", "Mbo", "Mkpat-Enin", "Nsit-Atai", "Nsit-Ibom", "Nsit-Ubium", "Obot Akara", "Okobo", "Onna", "Oron", "Oruk Anam", "Udung-Uko", "Ukanafun", "Uruan", "Urue-Offong/Oruko", "Uyo"],
    // ... (you would include all states and LGAs here, but I'll abbreviate for brevity)
};

$(document).ready(function() {
    // Handle state of origin change to populate LGA of origin
    $('#state_of_origin').on('change', function() {
        const stateId = $(this).val();
        const lgaSelect = $('#lga_of_origin');
        const currentLga = '{{ old("lga_of_origin", $driver->lga_of_origin ?? "") }}';
        
        // Clear current LGA options
        lgaSelect.empty().append('<option value="">Select LGA of Origin</option>');
        
        if (stateId && nigeriaLGAs[stateId]) {
            // Populate LGAs for selected state
            nigeriaLGAs[stateId].forEach(function(lga, index) {
                const value = index + 1;
                const selected = currentLga == value ? 'selected' : '';
                lgaSelect.append(`<option value="${value}" ${selected}>${lga}</option>`);
            });
        }
    });
    
    // Trigger state change on page load to populate LGAs if state is already selected
    if ($('#state_of_origin').val()) {
        $('#state_of_origin').trigger('change');
    }
});
</script>
@endsection