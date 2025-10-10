@extends('layouts.admin_cdn')

@section('title', 'Edit Company')

@section('content_header', 'Edit Company')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ url('admin/companies') }}">Companies</a></li>
    <li class="breadcrumb-item active">Edit</li>
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

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Edit Company: {{ $company->name }}</h3>
            </div>
            
            <form action="{{ route('admin.companies.update', $company->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="card-body">
                    
                    <!-- Company Information Section -->
                    <div class="form-section">
                        <h5><i class="fas fa-building"></i> Company Information</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Company Name <span class="required">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', $company->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="registration_number">Registration Number</label>
                                    <input type="text" class="form-control @error('registration_number') is-invalid @enderror" 
                                           id="registration_number" name="registration_number" value="{{ old('registration_number', $company->registration_number) }}">
                                    @error('registration_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">Email Address <span class="required">*</span></label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email', $company->email) }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phone">Phone Number <span class="required">*</span></label>
                                    <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" name="phone" value="{{ old('phone', $company->phone) }}" required>
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="website">Website</label>
                                    <input type="url" class="form-control @error('website') is-invalid @enderror" 
                                           id="website" name="website" value="{{ old('website', $company->website) }}">
                                    @error('website')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="industry">Industry</label>
                                    <select class="form-control @error('industry') is-invalid @enderror" id="industry" name="industry">
                                        <option value="">Select Industry</option>
                                        <option value="logistics" {{ old('industry', $company->industry) == 'logistics' ? 'selected' : '' }}>Logistics</option>
                                        <option value="transportation" {{ old('industry', $company->industry) == 'transportation' ? 'selected' : '' }}>Transportation</option>
                                        <option value="manufacturing" {{ old('industry', $company->industry) == 'manufacturing' ? 'selected' : '' }}>Manufacturing</option>
                                        <option value="retail" {{ old('industry', $company->industry) == 'retail' ? 'selected' : '' }}>Retail</option>
                                        <option value="construction" {{ old('industry', $company->industry) == 'construction' ? 'selected' : '' }}>Construction</option>
                                        <option value="other" {{ old('industry', $company->industry) == 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('industry')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="description">Company Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" name="description" rows="3">{{ old('description', $company->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Person Section -->
                    <div class="form-section">
                        <h5><i class="fas fa-user-tie"></i> Contact Person</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="contact_person_name">Contact Person Name <span class="required">*</span></label>
                                    <input type="text" class="form-control @error('contact_person_name') is-invalid @enderror" 
                                           id="contact_person_name" name="contact_person_name" value="{{ old('contact_person_name', $company->contact_person_name) }}" required>
                                    @error('contact_person_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="contact_person_title">Title/Position</label>
                                    <input type="text" class="form-control @error('contact_person_title') is-invalid @enderror" 
                                           id="contact_person_title" name="contact_person_title" value="{{ old('contact_person_title', $company->contact_person_title) }}">
                                    @error('contact_person_title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="contact_person_email">Contact Email</label>
                                    <input type="email" class="form-control @error('contact_person_email') is-invalid @enderror" 
                                           id="contact_person_email" name="contact_person_email" value="{{ old('contact_person_email', $company->contact_person_email) }}">
                                    @error('contact_person_email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="contact_person_phone">Contact Phone</label>
                                    <input type="tel" class="form-control @error('contact_person_phone') is-invalid @enderror" 
                                           id="contact_person_phone" name="contact_person_phone" value="{{ old('contact_person_phone', $company->contact_person_phone) }}">
                                    @error('contact_person_phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Address Information -->
                    <div class="form-section">
                        <h5><i class="fas fa-map-marker-alt"></i> Address Information</h5>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="address">Street Address</label>
                                    <textarea class="form-control @error('address') is-invalid @enderror" 
                                              id="address" name="address" rows="3">{{ old('address', $company->address) }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="state">State</label>
                                    <input type="text" class="form-control @error('state') is-invalid @enderror" 
                                           id="state" name="state" value="{{ old('state', $company->state) }}">
                                    @error('state')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="lga">LGA (Local Government Area)</label>
                                    <input type="text" class="form-control @error('lga') is-invalid @enderror" 
                                           id="lga" name="lga" value="{{ old('lga', $company->lga) }}">
                                    @error('lga')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="postal_code">Postal Code</label>
                                    <input type="text" class="form-control @error('postal_code') is-invalid @enderror" 
                                           id="postal_code" name="postal_code" value="{{ old('postal_code', $company->postal_code) }}">
                                    @error('postal_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Business Requirements -->
                    <div class="form-section">
                        <h5><i class="fas fa-clipboard-list"></i> Business Requirements</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="vehicle_types_needed">Vehicle Types Needed</label>
                                    <select multiple class="form-control @error('vehicle_types_needed') is-invalid @enderror" 
                                            id="vehicle_types_needed" name="vehicle_types_needed[]" style="height: 120px;">
                                        @php
                                            $currentVehicleTypes = old('vehicle_types_needed', $company->vehicle_types_needed ?: []);
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
                                    <small class="form-text text-muted">Hold Ctrl/Cmd to select multiple</small>
                                    @error('vehicle_types_needed')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="preferred_regions">Preferred Regions</label>
                                    <select multiple class="form-control @error('preferred_regions') is-invalid @enderror" 
                                            id="preferred_regions" name="preferred_regions[]" style="height: 120px;">
                                        @php
                                            $currentRegions = old('preferred_regions', $company->preferred_regions ?: []);
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
                                    </select>
                                    <small class="form-text text-muted">Hold Ctrl/Cmd to select multiple</small>
                                    @error('preferred_regions')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="default_commission_rate">Default Commission Rate (%)</label>
                                    <input type="number" step="0.01" max="100" class="form-control @error('default_commission_rate') is-invalid @enderror" 
                                           id="default_commission_rate" name="default_commission_rate" value="{{ old('default_commission_rate', $company->default_commission_rate) }}">
                                    @error('default_commission_rate')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="payment_terms">Payment Terms</label>
                                    <select class="form-control @error('payment_terms') is-invalid @enderror" id="payment_terms" name="payment_terms">
                                        <option value="">Select Payment Terms</option>
                                        <option value="immediate" {{ old('payment_terms', $company->payment_terms) == 'immediate' ? 'selected' : '' }}>Immediate</option>
                                        <option value="net_7" {{ old('payment_terms', $company->payment_terms) == 'net_7' ? 'selected' : '' }}>Net 7 days</option>
                                        <option value="net_15" {{ old('payment_terms', $company->payment_terms) == 'net_15' ? 'selected' : '' }}>Net 15 days</option>
                                        <option value="net_30" {{ old('payment_terms', $company->payment_terms) == 'net_30' ? 'selected' : '' }}>Net 30 days</option>
                                    </select>
                                    @error('payment_terms')
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
                                        <option value="active" {{ old('status', $company->status) == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ old('status', $company->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                        <option value="suspended" {{ old('status', $company->status) == 'suspended' ? 'selected' : '' }}>Suspended</option>
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
                                        <option value="pending" {{ old('verification_status', $company->verification_status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="verified" {{ old('verification_status', $company->verification_status) == 'verified' ? 'selected' : '' }}>Verified</option>
                                        <option value="rejected" {{ old('verification_status', $company->verification_status) == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                    </select>
                                    @error('verification_status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Company
                    </button>
                    <a href="{{ route('admin.companies.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection