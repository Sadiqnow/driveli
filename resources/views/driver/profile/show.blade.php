@extends('drivers.layouts.app')

@section('title', 'Profile')
@section('page_title', 'My Profile')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('driver.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Profile</li>
@endsection

@section('content')
<div class="row">
    <!-- Profile Overview Card -->
    <div class="col-lg-8">
        <div class="driver-card">
            <div class="driver-card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-user me-2" aria-hidden="true"></i>
                        Profile Information
                    </h5>
                    <a href="{{ route('driver.profile.edit') }}" class="btn btn-driver-primary btn-sm">
                        <i class="fas fa-edit me-1" aria-hidden="true"></i>
                        Edit Profile
                    </a>
                </div>
            </div>
            <div class="driver-card-body">
                @if (session('success'))
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-1"></i>
                        {{ session('success') }}
                    </div>
                @endif

                <div class="row">
                    <!-- Personal Information -->
                    <div class="col-md-6">
                        <h6 class="text-primary border-bottom pb-2 mb-3">
                            <i class="fas fa-user-circle me-1"></i> Personal Information
                        </h6>
                        
                        <div class="mb-3">
                            <label class="form-label text-muted small">Full Name</label>
                            <div class="fw-medium">
                                {{ $driver->first_name }} {{ $driver->middle_name }} {{ $driver->surname }}
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted small">Email Address</label>
                            <div class="fw-medium">
                                {{ $driver->email ?? 'Not provided' }}
                                @if($driver->email_verified_at)
                                    <i class="fas fa-check-circle text-success ms-1" title="Verified"></i>
                                @else
                                    <i class="fas fa-exclamation-triangle text-warning ms-1" title="Not verified"></i>
                                @endif
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted small">Phone Number</label>
                            <div class="fw-medium">
                                {{ $driver->phone ?? 'Not provided' }}
                                @if($driver->phone_verified_at)
                                    <i class="fas fa-check-circle text-success ms-1" title="Verified"></i>
                                @else
                                    <i class="fas fa-exclamation-triangle text-warning ms-1" title="Not verified"></i>
                                @endif
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted small">Date of Birth</label>
                            <div class="fw-medium">
                                {{ $driver->date_of_birth ? $driver->date_of_birth->format('M d, Y') : 'Not provided' }}
                                @if($driver->date_of_birth)
                                    <small class="text-muted ms-1">({{ $driver->date_of_birth->age }} years old)</small>
                                @endif
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted small">Gender</label>
                            <div class="fw-medium">{{ $driver->gender ?? 'Not specified' }}</div>
                        </div>
                    </div>

                    <!-- Professional Information -->
                    <div class="col-md-6">
                        <h6 class="text-primary border-bottom pb-2 mb-3">
                            <i class="fas fa-id-card me-1"></i> Professional Information
                        </h6>

                        <div class="mb-3">
                            <label class="form-label text-muted small">Driver ID</label>
                            <div class="fw-medium">
                                <code class="bg-light px-2 py-1 rounded">{{ $driver->driver_id }}</code>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted small">License Number</label>
                            <div class="fw-medium">{{ $driver->license_number ?? 'Not provided' }}</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted small">License Class</label>
                            <div class="fw-medium">{{ $driver->license_class ?? 'Not specified' }}</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted small">Years of Experience</label>
                            <div class="fw-medium">{{ $driver->years_of_experience ?? 'Not specified' }}</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted small">Registration Date</label>
                            <div class="fw-medium">
                                {{ $driver->registered_at ? $driver->registered_at->format('M d, Y') : 'Not available' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status & Actions Sidebar -->
    <div class="col-lg-4">
        <!-- Account Status -->
        <div class="driver-card mb-4">
            <div class="driver-card-header">
                <h6 class="mb-0">
                    <i class="fas fa-shield-alt me-1" aria-hidden="true"></i>
                    Account Status
                </h6>
            </div>
            <div class="driver-card-body">
                <div class="mb-3">
                    <label class="form-label text-muted small">Verification Status</label>
                    <div>
                        <span class="badge bg-{{ $driver->verification_status === 'verified' ? 'success' : ($driver->verification_status === 'pending' ? 'warning' : 'danger') }}">
                            @if($driver->verification_status === 'verified')
                                <i class="fas fa-check-circle me-1"></i>
                            @elseif($driver->verification_status === 'pending')
                                <i class="fas fa-clock me-1"></i>
                            @else
                                <i class="fas fa-times-circle me-1"></i>
                            @endif
                            {{ ucfirst($driver->verification_status) }}
                        </span>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted small">Account Status</label>
                    <div>
                        <span class="badge bg-{{ $driver->status === 'active' ? 'success' : ($driver->status === 'inactive' ? 'secondary' : 'danger') }}">
                            {{ ucfirst($driver->status) }}
                        </span>
                    </div>
                </div>

                @if($driver->verified_at)
                <div class="mb-3">
                    <label class="form-label text-muted small">Verified Date</label>
                    <div class="fw-medium">{{ $driver->verified_at->format('M d, Y') }}</div>
                </div>
                @endif

                @if($driver->verification_status !== 'verified')
                <div class="alert alert-warning p-2 small">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    Complete your profile and upload documents to get verified.
                </div>
                @endif
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="driver-card mb-4">
            <div class="driver-card-header">
                <h6 class="mb-0">
                    <i class="fas fa-bolt me-1" aria-hidden="true"></i>
                    Quick Actions
                </h6>
            </div>
            <div class="driver-card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('driver.profile.edit') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-user-edit me-1" aria-hidden="true"></i>
                        Edit Profile
                    </a>
                    
                    <a href="{{ route('driver.profile.documents') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-file-upload me-1" aria-hidden="true"></i>
                        Manage Documents
                    </a>
                    
                    <a href="#" class="btn btn-outline-info btn-sm" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                        <i class="fas fa-key me-1" aria-hidden="true"></i>
                        Change Password
                    </a>
                    
                    <a href="{{ route('driver.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-tachometer-alt me-1" aria-hidden="true"></i>
                        Back to Dashboard
                    </a>
                </div>
            </div>
        </div>

        <!-- Profile Completion -->
        <div class="driver-card">
            <div class="driver-card-header">
                <h6 class="mb-0">
                    <i class="fas fa-chart-pie me-1" aria-hidden="true"></i>
                    Profile Completion
                </h6>
            </div>
            <div class="driver-card-body">
                @php
                    $completionFields = [
                        'first_name', 'surname', 'email', 'phone', 'date_of_birth', 
                        'gender', 'license_number', 'license_class'
                    ];
                    $completedFields = collect($completionFields)->filter(function($field) use ($driver) {
                        return !empty($driver->$field);
                    })->count();
                    $completionPercentage = round(($completedFields / count($completionFields)) * 100);
                @endphp

                <div class="text-center mb-3">
                    <div class="h4 mb-1">{{ $completionPercentage }}%</div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-{{ $completionPercentage >= 80 ? 'success' : ($completionPercentage >= 50 ? 'warning' : 'danger') }}" 
                             role="progressbar" 
                             style="width: {{ $completionPercentage }}%"
                             aria-valuenow="{{ $completionPercentage }}" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                        </div>
                    </div>
                </div>

                <div class="small text-muted text-center">
                    {{ $completedFields }} of {{ count($completionFields) }} fields completed
                </div>

                @if($completionPercentage < 100)
                <div class="text-center mt-2">
                    <a href="{{ route('driver.profile.edit') }}" class="btn btn-driver-primary btn-sm">
                        <i class="fas fa-plus me-1" aria-hidden="true"></i>
                        Complete Profile
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changePasswordModalLabel">
                    <i class="fas fa-key me-1"></i>
                    Change Password
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('driver.profile.update-password') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <div class="form-text">Password must be at least 8 characters long.</div>
                    </div>
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>
                        Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle password form submission
    const passwordForm = document.querySelector('#changePasswordModal form');
    if (passwordForm) {
        passwordForm.addEventListener('submit', function(e) {
            const newPassword = document.getElementById('password').value;
            const confirmPassword = document.getElementById('password_confirmation').value;
            
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('New passwords do not match. Please try again.');
                return false;
            }
            
            if (newPassword.length < 8) {
                e.preventDefault();
                alert('Password must be at least 8 characters long.');
                return false;
            }
        });
    }
});
</script>
@endsection