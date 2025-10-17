@extends('layouts.admin_master')

@section('title', 'Superadmin - Edit Driver')

@section('content_header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Edit Driver</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.superadmin.dashboard') }}">Super Admin</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.superadmin.drivers.index') }}">Drivers</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.superadmin.drivers.show', $driver) }}">{{ $driver->driver_id }}</a></li>
                        <li class="breadcrumb-item active">Edit</li>
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
                        <h3 class="card-title">Edit Driver Information</h3>
                    </div>

                    <form method="POST" action="{{ route('admin.superadmin.drivers.update', $driver) }}">
                        @csrf
                        @method('PUT')

                        <div class="card-body">
                            <div class="row">
                                <!-- Basic Information -->
                                <div class="col-md-6">
                                    <h5 class="text-primary"><i class="fas fa-user"></i> Basic Information</h5>

                                    <div class="form-group">
                                        <label for="driver_id">Driver ID</label>
                                        <input type="text" class="form-control" id="driver_id" value="{{ $driver->driver_id }}" readonly>
                                        <small class="form-text text-muted">Driver ID cannot be changed</small>
                                    </div>

                                    <div class="form-group">
                                        <label for="first_name">First Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('first_name') is-invalid @enderror"
                                               id="first_name" name="first_name" value="{{ old('first_name', $driver->first_name) }}" required>
                                        @error('first_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="surname">Surname <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('surname') is-invalid @enderror"
                                               id="surname" name="surname" value="{{ old('surname', $driver->surname) }}" required>
                                        @error('surname')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="email">Email Address <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                                               id="email" name="email" value="{{ old('email', $driver->email) }}" required>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="phone">Phone Number <span class="text-danger">*</span></label>
                                        <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                               id="phone" name="phone" value="{{ old('phone', $driver->phone) }}" required>
                                        @error('phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="date_of_birth">Date of Birth</label>
                                        <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror"
                                               id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth', $driver->date_of_birth?->format('Y-m-d')) }}">
                                        @error('date_of_birth')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

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

                                <!-- System Settings -->
                                <div class="col-md-6">
                                    <h5 class="text-primary"><i class="fas fa-cogs"></i> System Settings</h5>

                                    <div class="form-group">
                                        <label for="status">Status <span class="text-danger">*</span></label>
                                        <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" required>
                                            <option value="active" {{ old('status', $driver->status) == 'active' ? 'selected' : '' }}>Active</option>
                                            <option value="inactive" {{ old('status', $driver->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                            <option value="flagged" {{ old('status', $driver->status) == 'flagged' ? 'selected' : '' }}>Flagged</option>
                                        </select>
                                        @error('status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="verification_status">Verification Status <span class="text-danger">*</span></label>
                                        <select class="form-control @error('verification_status') is-invalid @enderror" id="verification_status" name="verification_status" required>
                                            <option value="pending" {{ old('verification_status', $driver->verification_status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="verified" {{ old('verification_status', $driver->verification_status) == 'verified' ? 'selected' : '' }}>Verified</option>
                                            <option value="rejected" {{ old('verification_status', $driver->verification_status) == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                        </select>
                                        @error('verification_status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <!-- Current Status Information -->
                                    <div class="card border-info">
                                        <div class="card-header bg-info">
                                            <h6 class="card-title mb-0 text-white"><i class="fas fa-info-circle"></i> Current Status</h6>
                                        </div>
                                        <div class="card-body">
                                            <dl class="row mb-0">
                                                <dt class="col-sm-5">Created:</dt>
                                                <dd class="col-sm-7">{{ $driver->created_at->format('M d, Y H:i') }}</dd>

                                                <dt class="col-sm-5">Last Updated:</dt>
                                                <dd class="col-sm-7">{{ $driver->updated_at->format('M d, Y H:i') }}</dd>

                                                @if($driver->verified_at)
                                                    <dt class="col-sm-5">Verified At:</dt>
                                                    <dd class="col-sm-7">{{ $driver->verified_at->format('M d, Y H:i') }}</dd>
                                                @endif

                                                @if($driver->verifiedBy)
                                                    <dt class="col-sm-5">Verified By:</dt>
                                                    <dd class="col-sm-7">{{ $driver->verifiedBy->name }}</dd>
                                                @endif
                                            </dl>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Driver
                            </button>
                            <a href="{{ route('admin.superadmin.drivers.show', $driver) }}" class="btn btn-info">
                                <i class="fas fa-eye"></i> View Details
                            </a>
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

.card.border-info .card-header {
    background-color: #17a2b8 !important;
    border-color: #17a2b8 !important;
}

.dl-horizontal dt {
    text-align: left;
    width: auto;
    margin-right: 1rem;
    font-weight: 600;
}

.dl-horizontal dd {
    margin-left: 0;
}

.btn {
    margin-right: 0.5rem;
}

.form-text {
    font-size: 0.875rem;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
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

    // Email uniqueness check (optional - can be removed if not needed)
    $('#email').on('blur', function() {
        const email = $(this).val();
        const originalEmail = '{{ $driver->email }}';

        if (email && email !== originalEmail) {
            // You could add AJAX validation here to check if email is unique
            // For now, just basic validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                $(this).addClass('is-invalid');
                if ($(this).next('.invalid-feedback').length === 0) {
                    $(this).after('<div class="invalid-feedback">Please enter a valid email address.</div>');
                }
            } else {
                $(this).removeClass('is-invalid');
                $(this).next('.invalid-feedback').remove();
            }
        }
    });

    // Status change warning
    $('#status').on('change', function() {
        const newStatus = $(this).val();
        const currentStatus = '{{ $driver->status }}';

        if (newStatus !== currentStatus) {
            let message = '';
            switch(newStatus) {
                case 'flagged':
                    message = 'Warning: Flagging this driver will restrict their access to the system.';
                    break;
                case 'inactive':
                    message = 'Warning: Setting this driver to inactive will prevent them from receiving new jobs.';
                    break;
                case 'active':
                    message = 'This driver will be able to receive new jobs and access the system.';
                    break;
            }

            if (message) {
                // You could show a toast notification or modal here
                console.log('Status change:', message);
            }
        }
    });
});
</script>
@endpush
