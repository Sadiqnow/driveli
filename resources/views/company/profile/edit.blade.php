@extends('company.layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0"><i class="bi bi-person-gear"></i> Company Profile & Settings</h2>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Company Information</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('company.profile.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Company Name *</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name', Auth::user()->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email Address *</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   id="email" name="email" value="{{ old('email', Auth::user()->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                   id="phone" name="phone" value="{{ old('phone', Auth::user()->phone) }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="industry" class="form-label">Industry</label>
                            <select class="form-select @error('industry') is-invalid @enderror" id="industry" name="industry">
                                <option value="">Select Industry</option>
                                <option value="logistics" {{ old('industry', Auth::user()->industry) == 'logistics' ? 'selected' : '' }}>Logistics</option>
                                <option value="manufacturing" {{ old('industry', Auth::user()->industry) == 'manufacturing' ? 'selected' : '' }}>Manufacturing</option>
                                <option value="retail" {{ old('industry', Auth::user()->industry) == 'retail' ? 'selected' : '' }}>Retail</option>
                                <option value="construction" {{ old('industry', Auth::user()->industry) == 'construction' ? 'selected' : '' }}>Construction</option>
                                <option value="agriculture" {{ old('industry', Auth::user()->industry) == 'agriculture' ? 'selected' : '' }}>Agriculture</option>
                                <option value="technology" {{ old('industry', Auth::user()->industry) == 'technology' ? 'selected' : '' }}>Technology</option>
                                <option value="other" {{ old('industry', Auth::user()->industry) == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('industry')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control @error('address') is-invalid @enderror"
                                  id="address" name="address" rows="3">{{ old('address', Auth::user()->address) }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="website" class="form-label">Website</label>
                            <input type="url" class="form-control @error('website') is-invalid @enderror"
                                   id="website" name="website" value="{{ old('website', Auth::user()->website) }}"
                                   placeholder="https://www.example.com">
                            @error('website')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="company_size" class="form-label">Company Size</label>
                            <select class="form-select @error('company_size') is-invalid @enderror" id="company_size" name="company_size">
                                <option value="">Select Size</option>
                                <option value="1-10" {{ old('company_size', Auth::user()->company_size) == '1-10' ? 'selected' : '' }}>1-10 employees</option>
                                <option value="11-50" {{ old('company_size', Auth::user()->company_size) == '11-50' ? 'selected' : '' }}>11-50 employees</option>
                                <option value="51-200" {{ old('company_size', Auth::user()->company_size) == '51-200' ? 'selected' : '' }}>51-200 employees</option>
                                <option value="201-1000" {{ old('company_size', Auth::user()->company_size) == '201-1000' ? 'selected' : '' }}>201-1000 employees</option>
                                <option value="1000+" {{ old('company_size', Auth::user()->company_size) == '1000+' ? 'selected' : '' }}>1000+ employees</option>
                            </select>
                            @error('company_size')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Company Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                                  id="description" name="description" rows="4"
                                  placeholder="Brief description of your company...">{{ old('description', Auth::user()->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Update Profile
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Company Stats -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Account Status</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Verification Status:</strong>
                    <span class="badge bg-{{ Auth::user()->verification_status === 'Verified' ? 'success' : 'warning' }} ms-2">
                        {{ Auth::user()->verification_status ?? 'Pending' }}
                    </span>
                </div>

                <div class="mb-3">
                    <strong>Member Since:</strong>
                    <br>
                    <small class="text-muted">{{ Auth::user()->created_at->format('M d, Y') }}</small>
                </div>

                <div class="mb-3">
                    <strong>Last Updated:</strong>
                    <br>
                    <small class="text-muted">{{ Auth::user()->updated_at->format('M d, Y H:i') }}</small>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('company.requests.create') }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-plus-circle"></i> New Request
                    </a>
                    <a href="{{ route('company.fleets.create') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-truck"></i> Add Fleet
                    </a>
                    <a href="{{ route('company.vehicles.create') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-car-front"></i> Add Vehicle
                    </a>
                    <a href="{{ route('company.members.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-people"></i> Manage Members
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
