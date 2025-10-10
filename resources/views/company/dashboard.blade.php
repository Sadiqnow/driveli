@extends('layouts.app')

@section('title', 'Company Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3 class="mb-1">
                                <i class="fas fa-building"></i> Welcome, {{ $company->name }}!
                            </h3>
                            <p class="mb-0">Company ID: {{ $company->company_id }} | Status: 
                                <span class="badge badge-light">{{ $company->status }}</span> | 
                                Verification: <span class="badge badge-warning">{{ $company->verification_status }}</span>
                            </p>
                        </div>
                        <div class="col-md-4 text-right">
                            <form method="POST" action="{{ route('company.logout') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-outline-light">
                                    <i class="fas fa-sign-out-alt"></i> Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($company->verification_status === 'Pending')
        <div class="alert alert-warning" role="alert">
            <h5><i class="fas fa-clock"></i> Verification Pending</h5>
            <p class="mb-0">Your company is currently under review. Our team will verify your details and activate full access within 24-48 hours. You'll receive an email notification once verification is complete.</p>
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="text-xs font-weight-bold text-uppercase mb-1">Total Requests</div>
                            <div class="h5 mb-0">{{ number_format($stats['total_requests']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-alt fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="text-xs font-weight-bold text-uppercase mb-1">Fulfilled Requests</div>
                            <div class="h5 mb-0">{{ number_format($stats['fulfilled_requests']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="text-xs font-weight-bold text-uppercase mb-1">Fulfillment Rate</div>
                            <div class="h5 mb-0">{{ number_format($stats['fulfillment_rate'], 1) }}%</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-percentage fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card bg-secondary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <div class="text-xs font-weight-bold text-uppercase mb-1">Average Rating</div>
                            <div class="h5 mb-0">{{ number_format($stats['average_rating'], 1) }}/5.0</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-star fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="row">
        <!-- Company Information -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Company Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Company Name:</strong> {{ $company->name }}</p>
                            <p><strong>Email:</strong> {{ $company->email }}</p>
                            <p><strong>Phone:</strong> {{ $company->phone }}</p>
                            <p><strong>Industry:</strong> {{ $company->industry ?? 'Not specified' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Registration Number:</strong> {{ $company->registration_number ?? 'Not provided' }}</p>
                            <p><strong>Tax ID:</strong> {{ $company->tax_id ?? 'Not provided' }}</p>
                            <p><strong>Website:</strong> 
                                @if($company->website)
                                    <a href="{{ $company->website }}" target="_blank">{{ $company->website }}</a>
                                @else
                                    Not provided
                                @endif
                            </p>
                            <p><strong>Member Since:</strong> {{ $company->created_at->format('F Y') }}</p>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <p><strong>Address:</strong> {{ $company->address }}</p>
                            <p><strong>State:</strong> {{ $company->state }}</p>
                        </div>
                    </div>

                    @if($company->description)
                        <div class="row">
                            <div class="col-12">
                                <p><strong>Description:</strong></p>
                                <p class="text-muted">{{ $company->description }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Coming Soon Features -->
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-rocket"></i> Coming Soon Features</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center mb-3">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <i class="fas fa-search fa-3x text-primary mb-2"></i>
                                    <h6>Driver Search</h6>
                                    <p class="small text-muted">Find and hire qualified drivers</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-center mb-3">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <i class="fas fa-file-contract fa-3x text-success mb-2"></i>
                                    <h6>Request Management</h6>
                                    <p class="small text-muted">Manage your driver requests</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-center mb-3">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <i class="fas fa-chart-line fa-3x text-info mb-2"></i>
                                    <h6>Analytics</h6>
                                    <p class="small text-muted">Track performance metrics</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Account Status -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-user-shield"></i> Account Status</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-circle text-{{ $company->status === 'Active' ? 'success' : 'warning' }}"></i>
                            Account: {{ $company->status }}
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-circle text-{{ $company->verification_status === 'Verified' ? 'success' : 'warning' }}"></i>
                            Verification: {{ $company->verification_status }}
                        </li>
                        @if($company->verified_at)
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success"></i>
                                Verified: {{ $company->verified_at->format('M d, Y') }}
                            </li>
                        @endif
                    </ul>
                </div>
            </div>

            <!-- Contact Person -->
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0"><i class="fas fa-user"></i> Primary Contact</h6>
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong>{{ $company->contact_person_name }}</strong></p>
                    @if($company->contact_person_title)
                        <p class="text-muted mb-2">{{ $company->contact_person_title }}</p>
                    @endif
                    <p class="mb-1"><i class="fas fa-envelope"></i> {{ $company->contact_person_email }}</p>
                    <p class="mb-0"><i class="fas fa-phone"></i> {{ $company->contact_person_phone }}</p>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="fas fa-bolt"></i> Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-primary btn-sm" disabled>
                            <i class="fas fa-plus"></i> Create Driver Request
                        </button>
                        <button class="btn btn-outline-secondary btn-sm" disabled>
                            <i class="fas fa-edit"></i> Update Company Profile
                        </button>
                        <button class="btn btn-outline-info btn-sm" disabled>
                            <i class="fas fa-file-download"></i> Download Reports
                        </button>
                        <small class="text-muted mt-2">Features coming soon!</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Auto-hide success alerts after 5 seconds
    $('.alert-success').delay(5000).fadeOut();
});
</script>
@endsection