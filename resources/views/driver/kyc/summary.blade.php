@extends('drivers.layouts.app')

@section('title', 'KYC Summary')
@section('page_title', 'KYC Summary')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('driver.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">KYC Summary</li>
@endsection

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-10 mx-auto">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>KYC Verification Summary</h4>
                    <a href="{{ route('driver.dashboard') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> {{ session('success') }}
                        </div>
                    @endif

                    <!-- KYC Status Overview -->
                    <div class="alert alert-info mb-4">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h6 class="mb-1">
                                    <i class="fas fa-info-circle"></i> 
                                    KYC Verification Status
                                </h6>
                                <p class="mb-0">
                                    Your KYC verification has been submitted and is under review by our team.
                                </p>
                            </div>
                            <div class="col-md-4 text-end">
                                <span class="badge bg-warning fs-6 px-3 py-2">
                                    <i class="fas fa-clock"></i> Under Review
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Progress Steps -->
                    <div class="mb-4">
                        <h5 class="text-primary border-bottom pb-2">
                            <i class="fas fa-list-check"></i> Verification Steps
                        </h5>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="card border-success">
                                    <div class="card-body text-center">
                                        <div class="mb-2">
                                            <i class="fas fa-user fa-2x text-success"></i>
                                        </div>
                                        <h6>Personal Information</h6>
                                        <span class="badge bg-success">
                                            <i class="fas fa-check"></i> Completed
                                        </span>
                                        <p class="text-muted small mt-2 mb-0">
                                            Basic details and contact information
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <div class="card border-success">
                                    <div class="card-body text-center">
                                        <div class="mb-2">
                                            <i class="fas fa-briefcase fa-2x text-success"></i>
                                        </div>
                                        <h6>Professional Details</h6>
                                        <span class="badge bg-success">
                                            <i class="fas fa-check"></i> Completed
                                        </span>
                                        <p class="text-muted small mt-2 mb-0">
                                            Experience and banking information
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <div class="card border-success">
                                    <div class="card-body text-center">
                                        <div class="mb-2">
                                            <i class="fas fa-file-upload fa-2x text-success"></i>
                                        </div>
                                        <h6>Document Upload</h6>
                                        <span class="badge bg-success">
                                            <i class="fas fa-check"></i> Completed
                                        </span>
                                        <p class="text-muted small mt-2 mb-0">
                                            Required documents uploaded
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submitted Information -->
                    <div class="mb-4">
                        <h5 class="text-primary border-bottom pb-2">
                            <i class="fas fa-file-alt"></i> Submitted Information
                        </h5>

                        <div class="row">
                            <!-- Personal Information -->
                            <div class="col-md-6 mb-4">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title text-primary">
                                            <i class="fas fa-user"></i> Personal Information
                                        </h6>
                                        <div class="row mb-2">
                                            <div class="col-sm-5"><strong>Full Name:</strong></div>
                                            <div class="col-sm-7">
                                                {{ auth('driver')->user()->first_name ?? 'N/A' }} 
                                                {{ auth('driver')->user()->surname ?? '' }}
                                            </div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-sm-5"><strong>Email:</strong></div>
                                            <div class="col-sm-7">{{ auth('driver')->user()->email ?? 'N/A' }}</div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-sm-5"><strong>Phone:</strong></div>
                                            <div class="col-sm-7">{{ auth('driver')->user()->phone ?? 'N/A' }}</div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-sm-5"><strong>Date of Birth:</strong></div>
                                            <div class="col-sm-7">
                                                {{ auth('driver')->user()->date_of_birth?->format('M d, Y') ?? 'N/A' }}
                                            </div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-sm-5"><strong>Gender:</strong></div>
                                            <div class="col-sm-7">{{ auth('driver')->user()->gender ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- License Information -->
                            <div class="col-md-6 mb-4">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title text-primary">
                                            <i class="fas fa-id-card"></i> License Information
                                        </h6>
                                        <div class="row mb-2">
                                            <div class="col-sm-5"><strong>License Number:</strong></div>
                                            <div class="col-sm-7">{{ auth('driver')->user()->license_number ?? 'N/A' }}</div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-sm-5"><strong>License Class:</strong></div>
                                            <div class="col-sm-7">{{ auth('driver')->user()->license_class ?? 'N/A' }}</div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-sm-5"><strong>Registration Date:</strong></div>
                                            <div class="col-sm-7">
                                                {{ auth('driver')->user()->registered_at?->format('M d, Y') ?? 'N/A' }}
                                            </div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-sm-5"><strong>Status:</strong></div>
                                            <div class="col-sm-7">
                                                <span class="badge bg-{{ auth('driver')->user()->verification_status === 'verified' ? 'success' : (auth('driver')->user()->verification_status === 'pending' ? 'warning' : 'danger') }}">
                                                    {{ ucfirst(auth('driver')->user()->verification_status) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Next Steps -->
                    <div class="mb-4">
                        <h5 class="text-primary border-bottom pb-2">
                            <i class="fas fa-forward"></i> What Happens Next?
                        </h5>
                        
                        <div class="alert alert-light border-left border-primary">
                            <ol class="mb-0">
                                <li class="mb-2">
                                    <strong>Document Review:</strong> Our verification team will review all your submitted documents within 24-48 hours.
                                </li>
                                <li class="mb-2">
                                    <strong>Identity Verification:</strong> We may contact you via phone or email for additional verification if needed.
                                </li>
                                <li class="mb-2">
                                    <strong>Background Check:</strong> A basic background check will be performed using your provided information.
                                </li>
                                <li class="mb-2">
                                    <strong>Approval Notification:</strong> You will receive an email notification once your verification is complete.
                                </li>
                                <li class="mb-0">
                                    <strong>Start Working:</strong> Once approved, you can start receiving job matches and begin earning!
                                </li>
                            </ol>
                        </div>
                    </div>

                    <!-- Contact Support -->
                    <div class="text-center">
                        <h6 class="text-muted">Need help or have questions?</h6>
                        <p class="text-muted">
                            Contact our support team at 
                            <a href="mailto:support@drivelink.com">support@drivelink.com</a>
                            or call <a href="tel:+234123456789">+234 123 456 789</a>
                        </p>
                        
                        <div class="mt-4">
                            <a href="{{ route('driver.dashboard') }}" class="btn btn-primary me-2">
                                <i class="fas fa-tachometer-alt"></i> Go to Dashboard
                            </a>
                            <a href="{{ route('driver.profile.documents') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-file-alt"></i> View Documents
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.border-left {
    border-left: 4px solid !important;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.border-bottom {
    border-bottom: 2px solid #e9ecef!important;
}

.badge {
    font-size: 0.875em;
}

.fs-6 {
    font-size: 1.25rem!important;
}
</style>
@endsection