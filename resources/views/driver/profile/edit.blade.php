@extends('drivers.layouts.app')

@section('title', 'Edit Profile')
@section('page_title', 'Edit Profile')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('driver.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Edit Profile</li>
@endsection

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Edit Profile</h4>
                    <a href="{{ route('driver.dashboard') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('driver.profile.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Personal Information Section -->
                        <div class="mb-4">
                            <h5 class="text-primary border-bottom pb-2">
                                <i class="fas fa-user"></i> Personal Information
                            </h5>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('first_name') is-invalid @enderror" 
                                           id="first_name" 
                                           name="first_name" 
                                           value="{{ old('first_name', auth('driver')->user()->first_name) }}" 
                                           required>
                                    @error('first_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="surname" class="form-label">Surname <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('surname') is-invalid @enderror" 
                                           id="surname" 
                                           name="surname" 
                                           value="{{ old('surname', auth('driver')->user()->surname) }}" 
                                           required>
                                    @error('surname')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                    <input type="tel" 
                                           class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" 
                                           name="phone" 
                                           value="{{ old('phone', auth('driver')->user()->phone) }}" 
                                           required>
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" 
                                           class="form-control @error('email') is-invalid @enderror" 
                                           id="email" 
                                           name="email" 
                                           value="{{ old('email', auth('driver')->user()->email) }}" 
                                           required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="date_of_birth" class="form-label">Date of Birth</label>
                                    <input type="date" 
                                           class="form-control @error('date_of_birth') is-invalid @enderror" 
                                           id="date_of_birth" 
                                           name="date_of_birth" 
                                           value="{{ old('date_of_birth', auth('driver')->user()->date_of_birth?->format('Y-m-d')) }}">
                                    @error('date_of_birth')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="gender" class="form-label">Gender</label>
                                    <select class="form-control @error('gender') is-invalid @enderror" 
                                            id="gender" 
                                            name="gender">
                                        <option value="">Select Gender</option>
                                        <option value="Male" {{ old('gender', auth('driver')->user()->gender) == 'Male' ? 'selected' : '' }}>Male</option>
                                        <option value="Female" {{ old('gender', auth('driver')->user()->gender) == 'Female' ? 'selected' : '' }}>Female</option>
                                        <option value="Other" {{ old('gender', auth('driver')->user()->gender) == 'Other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('gender')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- License Information Section -->
                        <div class="mb-4">
                            <h5 class="text-primary border-bottom pb-2">
                                <i class="fas fa-id-card"></i> License Information
                            </h5>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="license_number" class="form-label">License Number</label>
                                    <input type="text" 
                                           class="form-control @error('license_number') is-invalid @enderror" 
                                           id="license_number" 
                                           name="license_number" 
                                           value="{{ old('license_number', auth('driver')->user()->license_number) }}" 
                                           readonly>
                                    <small class="text-muted">License number cannot be changed after registration</small>
                                    @error('license_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="license_class" class="form-label">License Class</label>
                                    <select class="form-control @error('license_class') is-invalid @enderror" 
                                            id="license_class" 
                                            name="license_class">
                                        <option value="">Select License Class</option>
                                        <option value="C" {{ old('license_class', auth('driver')->user()->license_class) == 'C' ? 'selected' : '' }}>Class C (Private)</option>
                                        <option value="D" {{ old('license_class', auth('driver')->user()->license_class) == 'D' ? 'selected' : '' }}>Class D (Commercial)</option>
                                        <option value="E" {{ old('license_class', auth('driver')->user()->license_class) == 'E' ? 'selected' : '' }}>Class E (Motorcycle)</option>
                                    </select>
                                    @error('license_class')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Status Information (Read-only) -->
                        <div class="mb-4">
                            <h5 class="text-primary border-bottom pb-2">
                                <i class="fas fa-info-circle"></i> Account Status
                            </h5>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Verification Status</label>
                                    <div>
                                        <span class="badge bg-{{ auth('driver')->user()->verification_status === 'verified' ? 'success' : (auth('driver')->user()->verification_status === 'pending' ? 'warning' : 'danger') }}">
                                            {{ ucfirst(auth('driver')->user()->verification_status) }}
                                        </span>
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Registration Date</label>
                                    <div class="form-control-plaintext">
                                        {{ auth('driver')->user()->registered_at?->format('M d, Y') ?? 'N/A' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('driver.dashboard') }}" class="btn btn-outline-secondary me-md-2">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
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

.badge {
    font-size: 0.875em;
    padding: 0.375rem 0.75rem;
}

.border-bottom {
    border-bottom: 2px solid #e9ecef!important;
}
</style>
@endsection