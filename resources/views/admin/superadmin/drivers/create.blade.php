@extends('layouts.admin_master')

@section('title', 'Superadmin - Create Driver')

@section('content_header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Create New Driver</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.superadmin.dashboard') }}">Super Admin</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.superadmin.drivers.index') }}">Drivers</a></li>
                        <li class="breadcrumb-item active">Create</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Driver Information</h3>
                    </div>

                    <form method="POST" action="{{ route('admin.superadmin.drivers.store') }}">
                        @csrf

                        <div class="card-body">
                            <div class="row">
                                <!-- Basic Information -->
                                <div class="col-md-6">
                                    <h5 class="text-primary"><i class="fas fa-user"></i> Basic Information</h5>

                                    <div class="form-group">
                                        <label for="first_name">First Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('first_name') is-invalid @enderror"
                                               id="first_name" name="first_name" value="{{ old('first_name') }}" required>
                                        @error('first_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="surname">Surname <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('surname') is-invalid @enderror"
                                               id="surname" name="surname" value="{{ old('surname') }}" required>
                                        @error('surname')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="email">Email Address <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                                               id="email" name="email" value="{{ old('email') }}" required>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="phone">Phone Number <span class="text-danger">*</span></label>
                                        <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                               id="phone" name="phone" value="{{ old('phone') }}" required>
                                        @error('phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="date_of_birth">Date of Birth</label>
                                        <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror"
                                               id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}">
                                        @error('date_of_birth')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="gender">Gender</label>
                                        <select class="form-control @error('gender') is-invalid @enderror" id="gender" name="gender">
                                            <option value="">Select Gender</option>
                                            <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Male</option>
                                            <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Female</option>
                                            <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Other</option>
                                        </select>
                                        @error('gender')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- System Settings -->
                                <div class="col-md-6">
                                    <h5 class="text-primary"><i class="fas fa-cogs"></i> System Settings</h5>

                                    <div class="form-group">
                                        <label for="status">Status <span class="text-danger">*</span></label>
                                        <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" required>
                                            <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                                            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                            <option value="flagged" {{ old('status') == 'flagged' ? 'selected' : '' }}>Flagged</option>
                                        </select>
                                        @error('status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="verification_status">Verification Status <span class="text-danger">*</span></label>
                                        <select class="form-control @error('verification_status') is-invalid @enderror" id="verification_status" name="verification_status" required>
                                            <option value="pending" {{ old('verification_status', 'pending') == 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="verified" {{ old('verification_status') == 'verified' ? 'selected' : '' }}>Verified</option>
                                            <option value="rejected" {{ old('verification_status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                        </select>
                                        @error('verification_status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i>
                                        <strong>Note:</strong> The driver will be created with a system-generated Driver ID.
                                        Additional information like KYC details, employment history, and documents can be added after creation.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Create Driver
                            </button>
                            <a href="{{ route('admin.superadmin.drivers.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
.card-header h3 {
    margin: 0;
}

.form-group label {
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.alert {
    border-radius: 0.375rem;
}

.btn {
    margin-right: 0.5rem;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-generate driver ID preview (optional)
    function updateDriverIdPreview() {
        const firstName = $('#first_name').val().toUpperCase().substring(0, 2) || 'XX';
        const surname = $('#surname').val().toUpperCase().substring(0, 2) || 'XX';
        const timestamp = Date.now().toString().substring(-4);
        const preview = `DRV-${firstName}${surname}-${timestamp}`;

        // You could show this preview somewhere if desired
        console.log('Generated Driver ID:', preview);
    }

    $('#first_name, #surname').on('input', updateDriverIdPreview);

    // Form validation
    $('form').on('submit', function(e) {
        const requiredFields = ['first_name', 'surname', 'email', 'phone', 'status', 'verification_status'];
        let isValid = true;

        requiredFields.forEach(field => {
            const $field = $(`#${field}`);
            if (!$field.val().trim()) {
                $field.addClass('is-invalid');
                isValid = false;
            } else {
                $field.removeClass('is-invalid');
            }
        });

        if (!isValid) {
            e.preventDefault();
            alert('Please fill in all required fields.');
        }
    });
});
</script>
@endpush
