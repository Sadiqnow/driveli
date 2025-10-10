@extends('layouts.admin_cdn')

@section('title', 'Edit Driver')

@section('content_header', 'Edit Driver')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ url('admin/drivers') }}">Drivers</a></li>
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
                <h3 class="card-title">Edit Driver Information - {{ $driver->full_name }}</h3>
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
                                    <label for="first_name">First Name <span class="required">*</span></label>
                                    <input type="text" class="form-control @error('first_name') is-invalid @enderror" 
                                           id="first_name" name="first_name" value="{{ old('first_name', $driver->first_name) }}" required>
                                    @error('first_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="surname">Surname <span class="required">*</span></label>
                                    <input type="text" class="form-control @error('surname') is-invalid @enderror" 
                                           id="surname" name="surname" value="{{ old('surname', $driver->surname) }}" required>
                                    @error('surname')
                                        <div class="invalid-feedback">{{ $message }}</div>
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
                                    <label for="email">Email Address</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email', $driver->email) }}">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phone">Phone Number <span class="required">*</span></label>
                                    <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" name="phone" value="{{ old('phone', $driver->phone) }}" required>
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phone_2">Alternate Phone</label>
                                    <input type="tel" class="form-control @error('phone_2') is-invalid @enderror" 
                                           id="phone_2" name="phone_2" value="{{ old('phone_2', $driver->phone_2) }}">
                                    @error('phone_2')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nationality_id">Nationality</label>
                                    <select class="form-control @error('nationality_id') is-invalid @enderror" id="nationality_id" name="nationality_id">
                                        <option value="">Select Nationality</option>
                                        @if(isset($nationalities))
                                            @foreach($nationalities as $nationality)
                                                <option value="{{ $nationality->id }}" {{ old('nationality_id', $driver->nationality_id) == $nationality->id ? 'selected' : '' }}>{{ $nationality->name }}</option>
                                            @endforeach
                                        @else
                                            <option value="1" {{ old('nationality_id', $driver->nationality_id) == '1' ? 'selected' : '' }}>Nigerian</option>
                                        @endif
                                    </select>
                                    @error('nationality_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
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
                                        <option value="male" {{ old('gender', $driver->gender) == 'male' ? 'selected' : '' }}>Male</option>
                                        <option value="female" {{ old('gender', $driver->gender) == 'female' ? 'selected' : '' }}>Female</option>
                                        <option value="other" {{ old('gender', $driver->gender) == 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('gender')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="nin_number">NIN (National ID)</label>
                                    <input type="text" class="form-control @error('nin_number') is-invalid @enderror" 
                                           id="nin_number" name="nin_number" value="{{ old('nin_number', $driver->nin_number) }}" maxlength="11">
                                    @error('nin_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="religion">Religion</label>
                                    <select class="form-control @error('religion') is-invalid @enderror" id="religion" name="religion">
                                        <option value="">Select Religion</option>
                                        <option value="Christianity" {{ old('religion', $driver->religion) == 'Christianity' ? 'selected' : '' }}>Christianity</option>
                                        <option value="Islam" {{ old('religion', $driver->religion) == 'Islam' ? 'selected' : '' }}>Islam</option>
                                        <option value="Traditional" {{ old('religion', $driver->religion) == 'Traditional' ? 'selected' : '' }}>Traditional</option>
                                        <option value="Other" {{ old('religion', $driver->religion) == 'Other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                    @error('religion')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="blood_group">Blood Group</label>
                                    <select class="form-control @error('blood_group') is-invalid @enderror" id="blood_group" name="blood_group">
                                        <option value="">Select Blood Group</option>
                                        <option value="A+" {{ old('blood_group', $driver->blood_group) == 'A+' ? 'selected' : '' }}>A+</option>
                                        <option value="A-" {{ old('blood_group', $driver->blood_group) == 'A-' ? 'selected' : '' }}>A-</option>
                                        <option value="B+" {{ old('blood_group', $driver->blood_group) == 'B+' ? 'selected' : '' }}>B+</option>
                                        <option value="B-" {{ old('blood_group', $driver->blood_group) == 'B-' ? 'selected' : '' }}>B-</option>
                                        <option value="AB+" {{ old('blood_group', $driver->blood_group) == 'AB+' ? 'selected' : '' }}>AB+</option>
                                        <option value="AB-" {{ old('blood_group', $driver->blood_group) == 'AB-' ? 'selected' : '' }}>AB-</option>
                                        <option value="O+" {{ old('blood_group', $driver->blood_group) == 'O+' ? 'selected' : '' }}>O+</option>
                                        <option value="O-" {{ old('blood_group', $driver->blood_group) == 'O-' ? 'selected' : '' }}>O-</option>
                                    </select>
                                    @error('blood_group')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="height_meters">Height (meters)</label>
                                    <input type="number" step="0.01" min="0.5" max="3.0" class="form-control @error('height_meters') is-invalid @enderror" 
                                           id="height_meters" name="height_meters" value="{{ old('height_meters', $driver->height_meters) }}" placeholder="1.75">
                                    @error('height_meters')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="disability_status">Disability Status (if any)</label>
                                    <input type="text" class="form-control @error('disability_status') is-invalid @enderror" 
                                           id="disability_status" name="disability_status" value="{{ old('disability_status', $driver->disability_status) }}" placeholder="None or describe any disability">
                                    @error('disability_status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Residence Location Section -->
                    <div class="form-section">
                        <h5><i class="fas fa-map-marker-alt"></i> Residence Location</h5>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="residence_address">Address</label>
                                    <textarea class="form-control @error('residence_address') is-invalid @enderror" 
                                              id="residence_address" name="residence_address" rows="3">{{ old('residence_address', $driver->residenceLocation?->address) }}</textarea>
                                    @error('residence_address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="residence_state_id">State</label>
                                    <select class="form-control @error('residence_state_id') is-invalid @enderror" id="residence_state_id" name="residence_state_id">
                                        <option value="">Select State</option>
                                        @if(isset($states))
                                            @foreach($states as $state)
                                                <option value="{{ $state->id }}" {{ old('residence_state_id', $driver->residenceLocation?->state_id) == $state->id ? 'selected' : '' }}>{{ $state->name }}</option>
                                            @endforeach
                                        @else
                                            <!-- Static Nigerian States -->
                                            <option value="1" {{ old('residence_state_id', $driver->residenceLocation?->state_id) == '1' ? 'selected' : '' }}>Abia</option>
                                            <option value="25" {{ old('residence_state_id', $driver->residenceLocation?->state_id) == '25' ? 'selected' : '' }}>Lagos</option>
                                            <!-- Add more states as needed -->
                                        @endif
                                    </select>
                                    @error('residence_state_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="residence_lga_id">LGA (Local Government Area)</label>
                                    <select class="form-control @error('residence_lga_id') is-invalid @enderror" id="residence_lga_id" name="residence_lga_id">
                                        <option value="">Select LGA</option>
                                        @if($driver->residenceLocation?->local_government)
                                            <option value="{{ $driver->residenceLocation->local_government_id }}" selected>{{ $driver->residenceLocation->localGovernment->name }}</option>
                                        @endif
                                    </select>
                                    @error('residence_lga_id')
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
                                    <label for="current_employer">Current Employer (if any)</label>
                                    <input type="text" class="form-control @error('current_employer') is-invalid @enderror" 
                                           id="current_employer" name="current_employer" value="{{ old('current_employer', $driver->currentEmployment?->employer_name) }}" placeholder="Company name">
                                    @error('current_employer')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="experience_years">Years of Experience</label>
                                    <input type="number" min="0" max="50" class="form-control @error('experience_years') is-invalid @enderror" 
                                           id="experience_years" name="experience_years" value="{{ old('experience_years', $driver->currentEmployment?->experience_years) }}" placeholder="Enter years">
                                    @error('experience_years')
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
                                        @php $vehicleTypes = old('vehicle_types', $driver->preferences?->vehicle_types ?? []); @endphp
                                        <option value="Car" {{ in_array('Car', $vehicleTypes) ? 'selected' : '' }}>Car</option>
                                        <option value="Van" {{ in_array('Van', $vehicleTypes) ? 'selected' : '' }}>Van</option>
                                        <option value="Truck" {{ in_array('Truck', $vehicleTypes) ? 'selected' : '' }}>Truck</option>
                                        <option value="Bus" {{ in_array('Bus', $vehicleTypes) ? 'selected' : '' }}>Bus</option>
                                        <option value="Motorcycle" {{ in_array('Motorcycle', $vehicleTypes) ? 'selected' : '' }}>Motorcycle</option>
                                        <option value="Trailer" {{ in_array('Trailer', $vehicleTypes) ? 'selected' : '' }}>Trailer</option>
                                    </select>
                                    <small class="form-text text-muted">Hold Ctrl/Cmd to select multiple options</small>
                                    @error('vehicle_types')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="work_regions">Working Regions (Select multiple)</label>
                                    <select multiple class="form-control @error('work_regions') is-invalid @enderror" 
                                            id="work_regions" name="work_regions[]" style="height: 120px;">
                                        @php $workRegions = old('work_regions', $driver->preferences?->work_regions ?? []); @endphp
                                        <option value="Lagos" {{ in_array('Lagos', $workRegions) ? 'selected' : '' }}>Lagos</option>
                                        <option value="Abuja" {{ in_array('Abuja', $workRegions) ? 'selected' : '' }}>Abuja</option>
                                        <option value="Kano" {{ in_array('Kano', $workRegions) ? 'selected' : '' }}>Kano</option>
                                        <option value="Ibadan" {{ in_array('Ibadan', $workRegions) ? 'selected' : '' }}>Ibadan</option>
                                        <option value="Port Harcourt" {{ in_array('Port Harcourt', $workRegions) ? 'selected' : '' }}>Port Harcourt</option>
                                        <option value="Benin City" {{ in_array('Benin City', $workRegions) ? 'selected' : '' }}>Benin City</option>
                                        <option value="Kaduna" {{ in_array('Kaduna', $workRegions) ? 'selected' : '' }}>Kaduna</option>
                                    </select>
                                    <small class="form-text text-muted">Hold Ctrl/Cmd to select multiple options</small>
                                    @error('work_regions')
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
                                        <option value="active" {{ old('status', $driver->status) == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ old('status', $driver->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                        <option value="suspended" {{ old('status', $driver->status) == 'suspended' ? 'selected' : '' }}>Suspended</option>
                                        <option value="blocked" {{ old('status', $driver->status) == 'blocked' ? 'selected' : '' }}>Blocked</option>
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
                                        <option value="pending" {{ old('verification_status', $driver->verification_status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="verified" {{ old('verification_status', $driver->verification_status) == 'verified' ? 'selected' : '' }}>Verified</option>
                                        <option value="rejected" {{ old('verification_status', $driver->verification_status) == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                        <option value="reviewing" {{ old('verification_status', $driver->verification_status) == 'reviewing' ? 'selected' : '' }}>Under Review</option>
                                    </select>
                                    @error('verification_status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password">New Password (leave blank to keep current)</label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                           id="password" name="password">
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password_confirmation">Confirm New Password</label>
                                    <input type="password" class="form-control" 
                                           id="password_confirmation" name="password_confirmation">
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

@section('js')
<script>
// Nigeria States and LGAs data (same as create form)
const nigeriaLGAs = {
    "1": ["Aba North", "Aba South", "Arochukwu", "Bende", "Ikwuano", "Isiala Ngwa North", "Isiala Ngwa South", "Isuikwuato", "Obi Ngwa", "Ohafia", "Osisioma", "Ugwunagbo", "Ukwa East", "Ukwa West", "Umuahia North", "Umuahia South", "Umu Nneochi"],
    "25": ["Agege", "Ajeromi-Ifelodun", "Alimosho", "Amuwo-Odofin", "Apapa", "Badagry", "Epe", "Eti Osa", "Ibeju-Lekki", "Ifako-Ijaiye", "Ikeja", "Ikorodu", "Kosofe", "Lagos Island", "Lagos Mainland", "Mushin", "Ojo", "Oshodi-Isolo", "Shomolu", "Surulere"],
    // Add other states LGAs as needed
};

$(document).ready(function() {
    // State and LGA population
    $('#residence_state_id').on('change', function() {
        const stateId = $(this).val();
        const lgaSelect = $('#residence_lga_id');
        
        // Clear current LGA options
        lgaSelect.empty().append('<option value="">Select LGA</option>');
        
        if (stateId && nigeriaLGAs[stateId]) {
            // Populate LGAs for selected state
            nigeriaLGAs[stateId].forEach(function(lga, index) {
                lgaSelect.append(`<option value="${index + 1}">${lga}</option>`);
            });
        }
    });
    
    // Form validation
    $('form').on('submit', function(e) {
        let isValid = true;
        
        // Check required fields
        $('input[required], select[required]').each(function() {
            if (!$(this).val()) {
                $(this).addClass('is-invalid');
                isValid = false;
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        
        // Check password confirmation
        if ($('#password').val() && $('#password').val() !== $('#password_confirmation').val()) {
            $('#password_confirmation').addClass('is-invalid');
            alert('Password and confirmation password do not match!');
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault();
            alert('Please fill in all required fields correctly!');
        }
    });
});
</script>
@endsection