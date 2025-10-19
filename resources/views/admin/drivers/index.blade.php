@extends('layouts.admin_cdn')

@section('title', 'Driver Management')

@section('content_header')
    Driver Pool Management
@stop

@section('content')
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div></div>
        <div>
            <div class="btn-group">
                <a href="{{ route('admin.drivers.create-simple') }}" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i> Quick Create Driver
                </a>
                <a href="{{ route('admin.drivers.create') }}" class="btn btn-outline-primary">
                    <i class="fas fa-plus"></i> Full KYC Registration
                </a>
            </div>
        </div>
    </div>
            <!-- Driver Pool Widgets -->
            <section aria-labelledby="driver-stats-heading" class="row mb-4">
                <h2 id="driver-stats-heading" class="visually-hidden">Driver Statistics Overview</h2>
                <!-- Total Drivers Widget -->
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ $drivers->total() ?? 0 }}</h3>
                            <p>Total Drivers</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <a href="{{ route('admin.drivers.index') }}" class="small-box-footer">
                            View All <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <!-- Verified Drivers Widget -->
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{ $verifiedCount ?? 0 }}</h3>
                            <p>Verified Drivers</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-user-check"></i>
                        </div>
                        <a href="{{ route('admin.drivers.index', ['verification_status' => 'verified']) }}" class="small-box-footer">
                            View Verified <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <!-- Pending Verification Widget -->
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{ $pendingCount ?? 0 }}</h3>
                            <p>Pending Verification</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <a href="{{ route('admin.drivers.index', ['verification_status' => 'pending']) }}" class="small-box-footer">
                            Review Pending <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>

                <!-- Active Drivers Widget -->
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <h3>{{ $activeCount ?? 0 }}</h3>
                            <p>Active Drivers</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-car"></i>
                        </div>
                        <a href="{{ route('admin.drivers.index', ['status' => 'active']) }}" class="small-box-footer">
                            View Active <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
            </section>

            <!-- Performance Metrics Row -->
            <div class="row mb-4">
                <!-- Average Rating Widget -->
                <div class="col-lg-3 col-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-yellow"><i class="fas fa-star"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Average Rating</span>
                            <span class="info-box-number">{{ number_format($averageRating ?? 0, 1) }}</span>
                            <div class="progress">
                                <div class="progress-bar bg-yellow" style="width: {{ ($averageRating ?? 0) * 20 }}%"></div>
                            </div>
                            <span class="progress-description">
                                Out of 5 stars
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Total Jobs Completed Widget -->
                <div class="col-lg-3 col-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-green"><i class="fas fa-briefcase"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Jobs Completed</span>
                            <span class="info-box-number">{{ number_format($totalJobsCompleted ?? 0) }}</span>
                            <div class="progress">
                                <div class="progress-bar bg-green" style="width: 100%"></div>
                            </div>
                            <span class="progress-description">
                                All time total
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Total Earnings Widget -->
                <div class="col-lg-3 col-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-purple"><i class="fas fa-dollar-sign"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Earnings</span>
                            <span class="info-box-number">${{ number_format($totalEarnings ?? 0, 0) }}</span>
                            <div class="progress">
                                <div class="progress-bar bg-purple" style="width: 100%"></div>
                            </div>
                            <span class="progress-description">
                                Driver earnings
                            </span>
                        </div>
                    </div>
                </div>

                <!-- New Registrations This Month Widget -->
                <div class="col-lg-3 col-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-red"><i class="fas fa-user-plus"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">New This Month</span>
                            <span class="info-box-number">{{ $newDriversThisMonth ?? 0 }}</span>
                            <div class="progress">
                                <div class="progress-bar bg-red" style="width: {{ min(($newDriversThisMonth ?? 0) * 10, 100) }}%"></div>
                            </div>
                            <span class="progress-description">
                                {{ now()->format('M Y') }} registrations
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-chart-pie mr-1"></i>
                                Verification Status Overview
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6 text-center">
                                    <div class="text-success">
                                        <span class="h4">{{ number_format((($verifiedCount ?? 0) / max(($drivers->total() ?? 1), 1)) * 100, 1) }}%</span>
                                    </div>
                                    <div class="text-muted">Verified</div>
                                </div>
                                <div class="col-6 text-center">
                                    <div class="text-warning">
                                        <span class="h4">{{ number_format((($pendingCount ?? 0) / max(($drivers->total() ?? 1), 1)) * 100, 1) }}%</span>
                                    </div>
                                    <div class="text-muted">Pending</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-tachometer-alt mr-1"></i>
                                Driver Activity
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6 text-center">
                                    <div class="text-primary">
                                        <span class="h4">{{ $activeDriversToday ?? 0 }}</span>
                                    </div>
                                    <div class="text-muted">Active Today</div>
                                </div>
                                <div class="col-6 text-center">
                                    <div class="text-info">
                                        <span class="h4">{{ $onlineDrivers ?? 0 }}</span>
                                    </div>
                                    <div class="text-muted">Online Now</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search & Filters -->
            <section aria-labelledby="filters-heading">
                <h2 id="filters-heading" class="visually-hidden">Driver Search and Filters</h2>
                <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.drivers.index') }}" class="row g-2">
                        <div class="col-md-3">
                            <label for="driver-search" class="visually-hidden">Search drivers</label>
                            <input type="text" 
                                   id="driver-search"
                                   name="search" 
                                   class="form-control" 
                                   placeholder="Search by name, phone, email..." 
                                   value="{{ request('search') }}"
                                   aria-describedby="search-help">
                            <div id="search-help" class="visually-hidden">Enter text to search for drivers by name, phone number, or email address</div>
                        </div>
                        <div class="col-md-2">
                            <label for="status-filter" class="visually-hidden">Filter by status</label>
                            <select id="status-filter" name="status" class="form-control" aria-label="Filter drivers by status">
                                <option value="">All Status</option>
                                <option value="active" {{ request('status')=='active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status')=='inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="suspended" {{ request('status')=='suspended' ? 'selected' : '' }}>Suspended</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="verification-filter" class="visually-hidden">Filter by verification status</label>
                            <select id="verification-filter" name="verification_status" class="form-control" aria-label="Filter drivers by verification status">
                                <option value="">All Verification</option>
                                <option value="pending" {{ request('verification_status')=='pending' ? 'selected' : '' }}>Pending</option>
                                <option value="verified" {{ request('verification_status')=='verified' ? 'selected' : '' }}>Verified</option>
                                <option value="rejected" {{ request('verification_status')=='rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="experience-filter" class="visually-hidden">Filter by experience level</label>
                            <select id="experience-filter" name="experience_level" class="form-control" aria-label="Filter drivers by experience level">
                                <option value="">All Experience</option>
                                <option value="1-2 years" {{ request('experience_level')=='1-2 years' ? 'selected' : '' }}>1-2 years</option>
                                <option value="3-5 years" {{ request('experience_level')=='3-5 years' ? 'selected' : '' }}>3-5 years</option>
                                <option value="6-10 years" {{ request('experience_level')=='6-10 years' ? 'selected' : '' }}>6-10 years</option>
                                <option value="10+ years" {{ request('experience_level')=='10+ years' ? 'selected' : '' }}>10+ years</option>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-success w-100" aria-label="Search drivers with current filters">
                                <i class="fas fa-search" aria-hidden="true"></i>
                                <span class="visually-hidden">Search</span>
                            </button>
                        </div>
                        <div class="col-md-1">
                            <a href="{{ route('admin.drivers.index') }}" class="btn btn-secondary w-100">Reset</a>
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-info w-100" data-toggle="modal" data-target="#bulkActionModal">
                                <i class="fas fa-tasks"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            </section>

            <!-- Drivers Table -->
            <section aria-labelledby="drivers-list-heading">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2 id="drivers-list-heading" class="card-title">All Drivers</h2>
                    <div class="card-tools">
                        <span class="badge bg-secondary">Total: {{ $drivers->total() ?? 0 }}</span>
                        <span class="badge bg-success">Verified: {{ $verifiedCount ?? 0 }}</span>
                        <span class="badge bg-warning text-dark">Pending: {{ $pendingCount ?? 0 }}</span>
                        <span class="badge bg-danger">Rejected: {{ $rejectedCount ?? 0 }}</span>
                    </div>
                </div>
                <div class="card-body table-responsive">
                    <!-- Progressive Disclosure Driver Cards -->
                    <div class="driver-cards-container">
                        @forelse($drivers as $driver)
                        <div class="driver-card mb-3" data-driver-id="{{ $driver->id }}">
                            <div class="card">
                                <div class="card-body">
                                    <!-- Primary Information Row -->
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center">
                                            <input type="checkbox" 
                                                   name="driver_ids[]" 
                                                   value="{{ $driver->id }}" 
                                                   class="me-3"
                                                   id="driver-checkbox-{{ $driver->id }}"
                                                   aria-label="Select {{ $driver->full_name ?? $driver->first_name }} for bulk actions">
                                            
                                            <!-- Driver Avatar & Name -->
                                            <div class="driver-avatar me-3">
                                                @if($driver->profile_picture || $driver->profile_photo)
                                                    <img src="{{ asset($driver->profile_picture ?? $driver->profile_photo) }}" 
                                                         class="rounded-circle" width="50" height="50" alt="Profile">
                                                @else
                                                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white" 
                                                         style="width: 50px; height: 50px; font-size: 18px;">
                                                        {{ substr($driver->first_name ?? 'D', 0, 1) }}{{ substr($driver->surname ?? 'R', 0, 1) }}
                                                    </div>
                                                @endif
                                            </div>
                                            
                                            <!-- Essential Info -->
                                            <div class="driver-essential-info">
                                                <h6 class="mb-1 fw-bold">{{ $driver->full_name ?? trim($driver->first_name . ' ' . $driver->surname) }}</h6>
                                                <div class="d-flex flex-wrap gap-2 mb-2">
                                                    <!-- Verification Status Badge -->
                                                    @if($driver->verification_status == 'verified')
                                                        <span class="badge bg-success" aria-label="Verification status: Verified">
                                                            <i class="fas fa-check-circle" aria-hidden="true"></i> Verified
                                                        </span>
                                                    @elseif($driver->verification_status == 'pending')
                                                        <span class="badge bg-warning text-dark" aria-label="Verification status: Pending review" style="color: #000 !important;">
                                                            <i class="fas fa-clock" aria-hidden="true"></i> Pending Review
                                                        </span>
                                                    @elseif($driver->verification_status == 'rejected')
                                                        <span class="badge bg-danger" aria-label="Verification status: Rejected">
                                                            <i class="fas fa-times-circle" aria-hidden="true"></i> Rejected
                                                        </span>
                                                    @endif
                                                    
                                                    <!-- Status Badge -->
                                                    @if($driver->status == 'active')
                                                        <span class="badge bg-success" aria-label="Driver status: Active">Active</span>
                                                    @elseif($driver->status == 'inactive')
                                                        <span class="badge bg-secondary" aria-label="Driver status: Inactive">Inactive</span>
                                                    @else
                                                        <span class="badge bg-danger" aria-label="Driver status: Suspended">Suspended</span>
                                                    @endif
                                                    
                                                    <!-- Experience Badge -->
                                                    @if($driver->experience_years)
                                                        <span class="badge bg-info">{{ $driver->experience_years }} years exp</span>
                                                    @endif
                                                </div>
                                                
                                                <!-- Key Contact Info -->
                                                <div class="text-muted small">
                                                    <i class="fas fa-phone"></i> {{ $driver->phone ?? 'N/A' }} • 
                                                    <i class="fas fa-id-card"></i> {{ $driver->driver_id ?? 'DRV-' . str_pad($driver->id, 4, '0', STR_PAD_LEFT) }}
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Primary Actions -->
                                        <div class="driver-primary-actions d-flex flex-column flex-md-row gap-2">
                                            @if($driver->verification_status == 'pending')
                                                @can('manage_drivers')
                                                <button class="btn btn-success btn-sm btn-lg-md me-2 verify-driver-btn"
                                                        data-driver-id="{{ $driver->id }}"
                                                        data-driver-name="{{ $driver->full_name ?? $driver->first_name }}"
                                                        aria-label="Verify {{ $driver->full_name ?? $driver->first_name }}">
                                                    <i class="fas fa-check-circle" aria-hidden="true"></i> <span class="d-none d-md-inline">Verify</span>
                                                </button>
                                                @endcan
                                                @can('manage_drivers')
                                                <button class="btn btn-danger btn-sm btn-lg-md me-2 reject-driver-btn"
                                                        data-driver-id="{{ $driver->id }}"
                                                        data-driver-name="{{ $driver->full_name ?? $driver->first_name }}"
                                                        aria-label="Reject verification for {{ $driver->full_name ?? $driver->first_name }}">
                                                    <i class="fas fa-times-circle" aria-hidden="true"></i> <span class="d-none d-md-inline">Reject</span>
                                                </button>
                                                @endcan
                                            @elseif($driver->verification_status == 'verified')
                                                <div class="verification-actions d-flex align-items-center gap-2" data-driver-id="{{ $driver->id }}">
                                                    <span class="badge bg-success" aria-label="Status: Verified">
                                                        <i class="fas fa-check-circle" aria-hidden="true"></i> <span class="d-none d-md-inline">Verified</span>
                                                    </span>
                                                    @can('manage_drivers')
                                                    <button class="btn btn-outline-warning btn-sm undo-verification-btn"
                                                            data-driver-id="{{ $driver->id }}"
                                                            data-driver-name="{{ $driver->full_name ?? $driver->first_name }}"
                                                            title="Undo verification"
                                                            aria-label="Undo verification for {{ $driver->full_name ?? $driver->first_name }}">
                                                        <i class="fas fa-undo" aria-hidden="true"></i>
                                                    </button>
                                                    @endcan
                                                </div>
                                            @elseif($driver->verification_status == 'rejected')
                                                <div class="verification-actions d-flex align-items-center gap-2" data-driver-id="{{ $driver->id }}">
                                                    <span class="badge bg-danger" aria-label="Status: Rejected">
                                                        <i class="fas fa-times-circle" aria-hidden="true"></i> <span class="d-none d-md-inline">Rejected</span>
                                                    </span>
                                                    @can('manage_drivers')
                                                    <button class="btn btn-outline-success btn-sm undo-rejection-btn"
                                                            data-driver-id="{{ $driver->id }}"
                                                            data-driver-name="{{ $driver->full_name ?? $driver->first_name }}"
                                                            title="Undo rejection"
                                                            aria-label="Undo rejection for {{ $driver->full_name ?? $driver->first_name }}">
                                                        <i class="fas fa-undo" aria-hidden="true"></i>
                                                    </button>
                                                    @endcan
                                                </div>
                                            @endif

                                            <!-- View Details Button -->
                                            <a href="{{ route('admin.drivers.show', $driver->id) }}"
                                               class="btn btn-primary btn-sm btn-lg-md me-2"
                                               aria-label="View details for {{ $driver->full_name ?? $driver->first_name }}">
                                                <i class="fas fa-eye"></i> <span class="d-none d-md-inline">View</span>
                                            </a>

                                            <!-- Expand/Collapse Details -->
                                            <button class="btn btn-outline-secondary btn-sm"
                                                    type="button"
                                                    onclick="toggleDriverDetails({{ $driver->id }})"
                                                    aria-label="Toggle additional details">
                                                <i class="fas fa-chevron-down expand-icon"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <!-- Expandable Detailed Information -->
                                    <div class="driver-details mt-3" id="details-{{ $driver->id }}" style="display: none;">
                                        <hr class="mb-3">
                                        <div class="row">
                                            <!-- Contact & Location -->
                                            <div class="col-md-3">
                                                <h6 class="text-muted mb-2">Contact & Location</h6>
                                                <p class="mb-1"><i class="fas fa-envelope text-muted"></i> {{ $driver->email ?? 'N/A' }}</p>
                                                <p class="mb-1"><i class="fas fa-map-marker-alt text-muted"></i> {{ $driver->nationality?->name ?? 'N/A' }}</p>
                                                <p class="mb-0"><i class="fas fa-calendar text-muted"></i> Joined {{ $driver->created_at ? $driver->created_at->format('M d, Y') : 'N/A' }}</p>
                                            </div>
                                            
                                            <!-- License & Documents -->
                                            <div class="col-md-3">
                                                <h6 class="text-muted mb-2">License & Documents</h6>
                                                <p class="mb-1">
                                                    <strong>{{ $driver->license_number ?? 'N/A' }}</strong>
                                                    @if($driver->license_class)
                                                        <span class="badge bg-info ms-1">{{ $driver->license_class }}</span>
                                                    @endif
                                                </p>
                                                <p class="mb-1">Expires: {{ $driver->license_expiry_date ? $driver->license_expiry_date->format('M d, Y') : 'N/A' }}</p>
                                                <p class="mb-0">NIN: {{ $driver->nin_number ? substr($driver->nin_number, 0, 4) . '****' : 'N/A' }}</p>
                                            </div>
                                            
                                            <!-- Experience & Skills -->
                                            <div class="col-md-3">
                                                <h6 class="text-muted mb-2">Experience & Skills</h6>
                                                <p class="mb-2">{{ $driver->experience_years ? $driver->experience_years . ' years experience' : 'No experience data' }}</p>
                                                @if($driver->vehicle_types && is_array($driver->vehicle_types))
                                                    <div>
                                                        @foreach($driver->vehicle_types as $type)
                                                            <span class="badge bg-secondary me-1">{{ $type }}</span>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                            
                                            <!-- Performance & Ratings -->
                                            <div class="col-md-3">
                                                <h6 class="text-muted mb-2">Performance</h6>
                                                @if($driver->performance?->average_rating)
                                                    <div class="mb-1">
                                                        @for($i = 1; $i <= 5; $i++)
                                                            @if($i <= $driver->performance->average_rating)
                                                                <i class="fas fa-star text-warning"></i>
                                                            @else
                                                                <i class="far fa-star text-warning"></i>
                                                            @endif
                                                        @endfor
                                                        <small class="ms-1">({{ number_format($driver->performance->average_rating, 1) }})</small>
                                                    </div>
                                                    <p class="mb-1">Jobs: {{ $driver->performance?->total_jobs_completed ?? 0 }}</p>
                                                    <p class="mb-0">Earnings: ${{ number_format($driver->performance?->total_earnings ?? 0, 2) }}</p>
                                                @else
                                                    <p class="text-muted mb-0">No performance data</p>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        <!-- Additional Actions -->
                                        <div class="mt-3 pt-3 border-top">
                                            <div class="btn-group" role="group" aria-label="Additional driver actions">
                                                <a href="{{ route('admin.drivers.edit', $driver->id) }}" 
                                                   class="btn btn-outline-primary btn-sm"
                                                   aria-label="Edit {{ $driver->full_name ?? $driver->first_name }}">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <form action="{{ route('admin.drivers.toggle-status', $driver->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button class="btn btn-outline-secondary btn-sm" 
                                                            title="Toggle Status"
                                                            aria-label="Toggle status for {{ $driver->full_name ?? $driver->first_name }}">
                                                        <i class="fas fa-power-off"></i> Toggle Status
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-users fa-3x mb-3"></i>
                                <h5>No drivers found</h5>
                                <p>Try adjusting your search criteria or add a new driver.</p>
                            </div>
                        </div>
                        @endforelse
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            @if(isset($drivers) && method_exists($drivers, 'links'))
                                <div class="d-flex align-items-center">
                                    <span class="text-muted me-3">
                                        Showing {{ $drivers->firstItem() ?? 0 }} to {{ $drivers->lastItem() ?? 0 }} of {{ $drivers->total() }} drivers
                                    </span>
                                    <div class="d-flex align-items-center">
                                        <label for="perPageSelect" class="form-label me-2 mb-0">Per page:</label>
                                        <select id="perPageSelect" class="form-select form-select-sm" style="width: auto;" 
                                                onchange="changePerPage(this.value)"
                                                aria-label="Number of drivers per page">
                                            <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                                            <option value="20" {{ request('per_page') == 20 || !request('per_page') ? 'selected' : '' }}>20</option>
                                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                                        </select>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-end align-items-center">
                                @if(isset($drivers) && method_exists($drivers, 'links'))
                                    <div class="me-3">
                                        {{ $drivers->appends(request()->query())->links('pagination::bootstrap-5') }}
                                    </div>
                                @endif
                                <button type="button" class="btn btn-outline-success btn-sm" onclick="exportDrivers()"
                                        aria-label="Export driver list">
                                    <i class="fas fa-download" aria-hidden="true"></i> Export
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </section>

<!-- Bulk Action Modal -->
<div class="modal fade" id="bulkActionModal" tabindex="-1" aria-labelledby="bulkActionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkActionModalLabel">Bulk Actions</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="{{ route('admin.drivers.bulk-action') }}" id="bulkActionForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="bulkAction" class="form-label">Select Action</label>
                        <select class="form-control" id="bulkAction" name="action" required>
                            <option value="">Choose Action...</option>
                            <option value="verify">Verify Selected Drivers</option>
                            <option value="reject">Reject Selected Drivers</option>
                            <option value="activate">Activate Selected Drivers</option>
                            <option value="deactivate">Deactivate Selected Drivers</option>
                            <option value="suspend">Suspend Selected Drivers</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="bulkNotes" class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" id="bulkNotes" name="notes" rows="3" placeholder="Add notes for this action..."></textarea>
                    </div>
                    <div id="selectedDrivers"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Execute Action</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('js')
<script>
// Optimized Driver Management Interface
class DriverInterface {
    constructor() {
        this.selectedDrivers = new Set();
        this.init();
    }
    
    init() {
        // Use event delegation for better performance
        this.bindEvents();
        this.initializeProgressiveDisclosure();
        this.setupKeyboardNavigation();
        this.setupIntersectionObserver();
    }
    
    bindEvents() {
        // Single event listener using delegation
        document.addEventListener('click', this.handleClick.bind(this));
        document.addEventListener('change', this.handleChange.bind(this));
    }
    
    handleClick(e) {
        const target = e.target;
        const button = target.closest('button, a');
        
        if (!button) return;
        
        // Verification buttons
        if (button.matches('.verify-driver-btn')) {
            e.preventDefault();
            this.handleVerification(button, 'verify');
        } else if (button.matches('.reject-driver-btn')) {
            e.preventDefault();
            this.handleVerification(button, 'reject');
        } else if (button.matches('.undo-verification-btn, .undo-rejection-btn')) {
            e.preventDefault();
            this.handleUndo(button);
        } else if (button.getAttribute('onclick')?.includes('toggleDriverDetails')) {
            e.preventDefault();
            const driverId = this.extractDriverId(button.getAttribute('onclick'));
            this.toggleDetails(driverId);
        } else if (button.matches('[data-dismiss="alert"]')) {
            this.dismissAlert(button);
        }
    }
    
    handleChange(e) {
        if (e.target.matches('input[name="driver_ids[]"]')) {
            this.updateSelection(e.target);
        } else if (e.target.id === 'selectAll') {
            this.toggleSelectAll(e.target.checked);
        }
    }

    // Optimized verification handling
    async handleVerification(button, action) {
        const driverId = button.dataset.driverId;
        const driverName = button.dataset.driverName;
        
        if (!driverId) return;
        
        this.setButtonLoading(button, action);
        
        try {
            const response = await this.makeVerificationRequest(driverId, action);
            const data = await response.json();
            
            if (data.success) {
                this.updateDriverStatus(driverId, action, driverName);
                this.showToast(`${driverName} has been ${action}ed successfully`, 'success');
                this.showUndoOption(action, driverId, driverName);
            } else {
                throw new Error(data.message || `${action} failed`);
            }
        } catch (error) {
            console.error(`${action} error:`, error);
            this.showToast(error.message || 'Network error occurred', 'error');
            this.resetButton(button, action);
        }
    }
    
    async makeVerificationRequest(driverId, action) {
        const endpoint = action === 'verify' ? 'verify' : 'reject';
        
        return fetch(`/admin/drivers/${driverId}/${endpoint}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ action, driver_id: driverId })
        });
    }

    setButtonLoading(button, action) {
        button.disabled = true;
        button.classList.add('loading');
        const text = action === 'verify' ? 'Verifying...' : 'Rejecting...';
        button.innerHTML = `<i class="fas fa-spinner fa-spin" aria-hidden="true"></i> ${text}`;
    }
    
    resetButton(button, action) {
        button.disabled = false;
        button.classList.remove('loading');
        const icon = action === 'verify' ? 'check-circle' : 'times-circle';
        const text = action === 'verify' ? 'Verify' : 'Reject';
        button.innerHTML = `<i class="fas fa-${icon}" aria-hidden="true"></i> ${text}`;
    }
    
    // Optimized status update with minimal DOM manipulation
    updateDriverStatus(driverId, status, driverName) {
        const card = document.querySelector(`[data-driver-id="${driverId}"]`);
        if (!card) return;
        
        // Update badge using template
        this.updateStatusBadge(card, status);
        this.updateActionButtons(card, status, driverId, driverName);
        
        // Add visual feedback
        card.classList.add(`verification-${status === 'verify' ? 'success' : 'error'}`);
        setTimeout(() => {
            card.classList.remove(`verification-${status === 'verify' ? 'success' : 'error'}`);
        }, 2000);
    }

    updateStatusBadge(card, status) {
        const badgeContainer = card.querySelector('.d-flex.flex-wrap');
        const existingBadge = badgeContainer.querySelector('span[aria-label*="Verification status"]');
        
        if (existingBadge) {
            const newBadge = this.createStatusBadge(status);
            existingBadge.replaceWith(newBadge);
        }
    }
    
    createStatusBadge(status) {
        const badge = document.createElement('span');
        badge.className = 'badge';
        
        const configs = {
            verify: {
                class: 'bg-success',
                icon: 'check-circle',
                text: 'Verified',
                label: 'Verification status: Verified'
            },
            reject: {
                class: 'bg-danger',
                icon: 'times-circle',
                text: 'Rejected',
                label: 'Verification status: Rejected'
            },
            pending: {
                class: 'bg-warning text-dark',
                icon: 'clock',
                text: 'Pending Review',
                label: 'Verification status: Pending review'
            }
        };
        
        const config = configs[status] || configs.pending;
        badge.className += ` ${config.class}`;
        badge.setAttribute('aria-label', config.label);
        badge.innerHTML = `<i class="fas fa-${config.icon}" aria-hidden="true"></i> ${config.text}`;
        
        return badge;
    }

function handleUndoVerification(button) {
    const driverId = button.dataset.driverId;
    const driverName = button.dataset.driverName;
    
    undoVerificationAction(driverId, driverName, 'verification');
}

function handleUndoRejection(button) {
    const driverId = button.dataset.driverId;
    const driverName = button.dataset.driverName;
    
    undoVerificationAction(driverId, driverName, 'rejection');
}

function undoVerificationAction(driverId, driverName, actionType) {
    fetch(`/admin/drivers/${driverId}/undo-verification`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            action: 'undo',
            driver_id: driverId,
            previous_action: actionType
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Restore pending state
            updateDriverVerificationStatus(driverId, 'pending', driverName);
            showNotificationToast(`${driverName}'s ${actionType} has been undone`, 'info');
        } else {
            showNotificationToast(`Failed to undo ${actionType}`, 'error');
        }
    })
    .catch(error => {
        console.error('Undo error:', error);
        showNotificationToast('Network error during undo operation', 'error');
    });
}

function updateDriverVerificationStatus(driverId, status, driverName) {
    const driverCard = document.querySelector(`[data-driver-id="${driverId}"]`);
    const actionsContainer = driverCard.querySelector('.driver-primary-actions');
    
    // Update the verification status badge in the card
    const badgeContainer = driverCard.querySelector('.d-flex.flex-wrap');
    const existingBadge = badgeContainer.querySelector('span[aria-label*="Verification status"]');
    
    let newBadgeHtml = '';
    if (status === 'verified') {
        newBadgeHtml = `
            <span class="badge bg-success" aria-label="Verification status: Verified">
                <i class="fas fa-check-circle" aria-hidden="true"></i> Verified
            </span>
        `;
    } else if (status === 'rejected') {
        newBadgeHtml = `
            <span class="badge bg-danger" aria-label="Verification status: Rejected">
                <i class="fas fa-times-circle" aria-hidden="true"></i> Rejected
            </span>
        `;
    } else if (status === 'pending') {
        newBadgeHtml = `
            <span class="badge bg-warning text-dark" aria-label="Verification status: Pending review" style="color: #000 !important;">
                <i class="fas fa-clock" aria-hidden="true"></i> Pending Review
            </span>
        `;
    }
    
    if (existingBadge) {
        existingBadge.outerHTML = newBadgeHtml;
    }
    
    // Update action buttons
    let newActionsHtml = '';
    if (status === 'pending') {
        newActionsHtml = `
            <button class="btn btn-success btn-lg me-2 verify-driver-btn" 
                    data-driver-id="${driverId}"
                    data-driver-name="${driverName}"
                    aria-label="Verify ${driverName}">
                <i class="fas fa-check-circle" aria-hidden="true"></i> Verify
            </button>
            <button class="btn btn-danger btn-lg me-2 reject-driver-btn" 
                    data-driver-id="${driverId}"
                    data-driver-name="${driverName}"
                    aria-label="Reject verification for ${driverName}">
                <i class="fas fa-times-circle" aria-hidden="true"></i> Reject
            </button>
        `;
    } else if (status === 'verified') {
        newActionsHtml = `
            <div class="verification-actions" data-driver-id="${driverId}">
                <span class="badge bg-success me-2" aria-label="Status: Verified">
                    <i class="fas fa-check-circle" aria-hidden="true"></i> Verified
                </span>
                <button class="btn btn-outline-warning btn-sm undo-verification-btn" 
                        data-driver-id="${driverId}"
                        data-driver-name="${driverName}"
                        title="Undo verification"
                        aria-label="Undo verification for ${driverName}">
                    <i class="fas fa-undo" aria-hidden="true"></i>
                </button>
            </div>
        `;
    } else if (status === 'rejected') {
        newActionsHtml = `
            <div class="verification-actions" data-driver-id="${driverId}">
                <span class="badge bg-danger me-2" aria-label="Status: Rejected">
                    <i class="fas fa-times-circle" aria-hidden="true"></i> Rejected
                </span>
                <button class="btn btn-outline-success btn-sm undo-rejection-btn" 
                        data-driver-id="${driverId}"
                        data-driver-name="${driverName}"
                        title="Undo rejection"
                        aria-label="Undo rejection for ${driverName}">
                    <i class="fas fa-undo" aria-hidden="true"></i>
                </button>
            </div>
        `;
    }
    
    // Replace the verification action buttons
    const existingActions = actionsContainer.querySelector('.verify-driver-btn, .reject-driver-btn, .verification-actions');
    if (existingActions) {
        if (existingActions.classList.contains('verification-actions')) {
            existingActions.outerHTML = newActionsHtml;
        } else {
            // Replace both verify and reject buttons
            const verifyBtn = actionsContainer.querySelector('.verify-driver-btn');
            const rejectBtn = actionsContainer.querySelector('.reject-driver-btn');
            if (verifyBtn) verifyBtn.remove();
            if (rejectBtn) rejectBtn.remove();
            
            const viewBtn = actionsContainer.querySelector('a[href*="show"]');
            if (viewBtn) {
                viewBtn.insertAdjacentHTML('beforebegin', newActionsHtml);
            } else {
                actionsContainer.insertAdjacentHTML('afterbegin', newActionsHtml);
            }
        }
    }
}

function showUndoNotification(actionType, driverId, driverName) {
    const notification = document.createElement('div');
    notification.className = 'alert alert-success alert-dismissible fade show undo-notification';
    notification.style.cssText = 'position: fixed; top: 80px; right: 20px; z-index: 1060; min-width: 350px; max-width: 400px;';
    notification.innerHTML = `
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <i class="fas fa-check-circle me-2"></i>
                <strong>${driverName}</strong> ${actionType === 'verification' ? 'verified' : 'rejected'}
            </div>
            <button type="button" class="btn btn-outline-secondary btn-sm undo-action-btn" 
                    data-driver-id="${driverId}" 
                    data-driver-name="${driverName}"
                    data-action-type="${actionType}">
                <i class="fas fa-undo me-1"></i> Undo
            </button>
        </div>
        <button type="button" class="close" data-dismiss="alert">
            <span>&times;</span>
        </button>
    `;
    
    document.body.appendChild(notification);
    
    // Handle undo from notification
    notification.querySelector('.undo-action-btn').addEventListener('click', function() {
        undoVerificationAction(driverId, driverName, actionType);
        notification.remove();
    });
    
    // Auto-remove after 10 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 10000);
}

function resetVerificationButton(button, actionType) {
    if (actionType === 'verify') {
        button.disabled = false;
        button.innerHTML = '<i class="fas fa-check-circle" aria-hidden="true"></i> Verify';
    } else {
        button.disabled = false;
        button.innerHTML = '<i class="fas fa-times-circle" aria-hidden="true"></i> Reject';
    }
}

    // Optimized notification system
    showToast(message, type = 'info', duration = 5000) {
        const toastContainer = this.getToastContainer();
        const toast = this.createToast(message, type);
        
        toastContainer.appendChild(toast);
        
        // Trigger entrance animation
        requestAnimationFrame(() => {
            toast.classList.add('show');
        });
        
        // Auto-dismiss
        setTimeout(() => this.dismissToast(toast), duration);
        
        return toast;
    }
    
    getToastContainer() {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 1055; width: 350px;';
            document.body.appendChild(container);
        }
        return container;
    }
    
    createToast(message, type) {
        const toast = document.createElement('div');
        const config = this.getToastConfig(type);
        
        toast.className = `alert ${config.class} alert-dismissible notification-toast`;
        toast.style.cssText = 'margin-bottom: 10px; transform: translateX(100%); transition: transform 0.3s ease;';
        toast.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="${config.icon} me-2"></i>
                <span>${message}</span>
            </div>
            <button type="button" class="btn-close" data-dismiss="alert" aria-label="Close"></button>
        `;
        
        return toast;
    }
    
    getToastConfig(type) {
        const configs = {
            success: { class: 'alert-success', icon: 'fas fa-check-circle' },
            error: { class: 'alert-danger', icon: 'fas fa-exclamation-triangle' },
            warning: { class: 'alert-warning', icon: 'fas fa-exclamation-circle' },
            info: { class: 'alert-info', icon: 'fas fa-info-circle' }
        };
        return configs[type] || configs.info;
    }
    
    dismissToast(toast) {
        if (!toast.parentNode) return;
        
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (toast.parentNode) {
                toast.remove();
            }
        }, 300);
    }

    // Optimized progressive disclosure
    initializeProgressiveDisclosure() {
        this.expandedDetails = new Set();
    }
    
    toggleDetails(driverId) {
        const detailsDiv = document.getElementById(`details-${driverId}`);
        const button = document.querySelector(`[onclick*="toggleDriverDetails(${driverId})"]`);
        const icon = button?.querySelector('.expand-icon');
        
        if (!detailsDiv) return;
        
        const isExpanded = this.expandedDetails.has(driverId);
        
        if (isExpanded) {
            this.collapseDetails(detailsDiv, icon, driverId);
        } else {
            this.expandDetails(detailsDiv, icon, driverId);
        }
    }
    
    expandDetails(detailsDiv, icon, driverId) {
        detailsDiv.style.display = 'block';
        detailsDiv.style.opacity = '0';
        detailsDiv.style.transform = 'translateY(-10px)';
        
        requestAnimationFrame(() => {
            detailsDiv.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
            detailsDiv.style.opacity = '1';
            detailsDiv.style.transform = 'translateY(0)';
        });
        
        if (icon) {
            icon.classList.remove('fa-chevron-down');
            icon.classList.add('fa-chevron-up');
        }
        
        this.expandedDetails.add(driverId);
        this.trackAnalytics('driver_expand', { driver_id: driverId });
    }
    
    collapseDetails(detailsDiv, icon, driverId) {
        detailsDiv.style.opacity = '0';
        detailsDiv.style.transform = 'translateY(-10px)';
        
        setTimeout(() => {
            detailsDiv.style.display = 'none';
            detailsDiv.style.transition = '';
        }, 300);
        
        if (icon) {
            icon.classList.remove('fa-chevron-up');
            icon.classList.add('fa-chevron-down');
        }
        
        this.expandedDetails.delete(driverId);
        this.trackAnalytics('driver_collapse', { driver_id: driverId });
    }

// Enhanced keyboard navigation
document.addEventListener('keydown', function(e) {
    // Press 'E' to expand/collapse first visible driver details
    if (e.key === 'e' || e.key === 'E') {
        if (!e.target.matches('input, textarea, select')) {
            const firstExpandBtn = document.querySelector('.driver-card button[onclick*="toggleDriverDetails"]');
            if (firstExpandBtn) {
                firstExpandBtn.click();
                e.preventDefault();
            }
        }
    }
});

// Batch expand/collapse functionality
function expandAllDriverDetails() {
    const allDriverCards = document.querySelectorAll('.driver-card');
    allDriverCards.forEach(card => {
        const driverId = card.dataset.driverId;
        const detailsDiv = document.getElementById(`details-${driverId}`);
        const expandIcon = card.querySelector('.expand-icon');
        
        if (detailsDiv && (detailsDiv.style.display === 'none' || detailsDiv.style.display === '')) {
            toggleDriverDetails(driverId);
        }
    });
}

function collapseAllDriverDetails() {
    const allDriverCards = document.querySelectorAll('.driver-card');
    allDriverCards.forEach(card => {
        const driverId = card.dataset.driverId;
        const detailsDiv = document.getElementById(`details-${driverId}`);
        
        if (detailsDiv && detailsDiv.style.display !== 'none') {
            toggleDriverDetails(driverId);
        }
    });
}

// Analytics tracking
function trackDriverExpansion(driverId, action) {
    // Send analytics data if tracking is enabled
    if (typeof gtag !== 'undefined') {
        gtag('event', 'driver_card_interaction', {
            'action': action,
            'driver_id': driverId,
            'page': 'driver_index'
        });
    }
}

// Enhanced search with real-time filtering (optional progressive enhancement)
let searchTimeout;
function enhancedSearch(searchInput) {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        // Could implement live search here
        console.log('Searching for:', searchInput.value);
    }, 300);
}

function exportDrivers() {
    const filters = new URLSearchParams(window.location.search);
    window.location.href = `{{ route('admin.drivers.export') }}?${filters.toString()}`;
}

// Pagination functionality
function changePerPage(perPage) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', perPage);
    url.searchParams.set('page', '1'); // Reset to first page
    window.location.href = url.toString();
}

// Enhanced pagination with keyboard navigation
document.addEventListener('keydown', function(e) {
    // Don't interfere with form inputs
    if (e.target.matches('input, textarea, select')) return;
    
    // Navigate pagination with arrow keys
    if (e.ctrlKey) {
        if (e.key === 'ArrowLeft') {
            // Go to previous page
            const prevLink = document.querySelector('.pagination .page-item:not(.disabled) .page-link[rel="prev"]');
            if (prevLink) {
                e.preventDefault();
                prevLink.click();
            }
        } else if (e.key === 'ArrowRight') {
            // Go to next page
            const nextLink = document.querySelector('.pagination .page-item:not(.disabled) .page-link[rel="next"]');
            if (nextLink) {
                e.preventDefault();
                nextLink.click();
            }
        }
    }
});

// Add loading states to pagination links
document.addEventListener('click', function(e) {
    if (e.target.matches('.pagination .page-link')) {
        const link = e.target;
        if (!link.closest('.page-item').classList.contains('disabled') && !link.closest('.page-item').classList.contains('active')) {
            link.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        }
    }
});

// Add hover effects and improved interaction feedback
document.addEventListener('DOMContentLoaded', function() {
    // Initialize notification close handlers
    document.addEventListener('click', function(e) {
        if (e.target.matches('[data-dismiss="alert"]') || e.target.parentElement.matches('[data-dismiss="alert"]')) {
            const alert = e.target.closest('.alert');
            if (alert) {
                alert.classList.remove('show');
                setTimeout(() => alert.remove(), 150);
            }
        }
    });
    
    // Add hover effect classes
    const style = document.createElement('style');
    style.textContent = `
        .driver-card .card {
            transition: all 0.2s ease-out;
            border: 1px solid #dee2e6;
        }
        
        .driver-card .card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            transform: translateY(-2px);
            border-color: #007bff;
        }
        
        .driver-primary-actions .btn-lg {
            min-width: 100px;
            font-weight: 600;
        }
        
        .driver-primary-actions .btn-success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
        }
        
        .driver-primary-actions .btn-danger {
            background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
            border: none;
        }
        
        .driver-primary-actions .btn-primary {
            background: linear-gradient(135deg, #007bff 0%, #6610f2 100%);
            border: none;
        }
        
        .expand-icon {
            transition: transform 0.3s ease-out;
        }
        
        .badge {
            font-weight: 500;
        }
        
        @media (max-width: 768px) {
            .driver-primary-actions {
                flex-direction: column;
                align-items: stretch;
            }
            
            .driver-primary-actions .btn {
                margin: 2px 0;
                width: 100%;
            }
            
            .driver-essential-info .d-flex {
                flex-direction: column;
            }
            
            /* Mobile pagination adjustments */
            .card-footer .row {
                flex-direction: column;
            }
            
            .card-footer .col-md-6:first-child {
                margin-bottom: 1rem;
            }
            
            .pagination {
                justify-content: center;
                margin-bottom: 0.5rem;
            }
            
            .pagination .page-link {
                padding: 0.375rem 0.75rem;
                font-size: 0.875rem;
            }
        }
        
        /* Enhanced verification workflow styles */
        .verification-actions {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .undo-notification {
            animation: slideInRight 0.3s ease-out;
        }
        
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        .notification-toast {
            animation: slideInRight 0.3s ease-out;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .btn.loading {
            position: relative;
            color: transparent !important;
        }
        
        .btn.loading::after {
            content: '';
            position: absolute;
            width: 16px;
            height: 16px;
            top: 50%;
            left: 50%;
            margin-left: -8px;
            margin-top: -8px;
            border: 2px solid #ffffff;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Undo button styling */
        .undo-verification-btn,
        .undo-rejection-btn {
            opacity: 0.7;
            transition: opacity 0.2s ease;
        }
        
        .undo-verification-btn:hover,
        .undo-rejection-btn:hover {
            opacity: 1;
        }
        
        /* Success/error states */
        .driver-card.verification-success {
            border-left: 4px solid #28a745;
            animation: highlightSuccess 2s ease-out;
        }
        
        .driver-card.verification-error {
            border-left: 4px solid #dc3545;
            animation: highlightError 2s ease-out;
        }
        
        @keyframes highlightSuccess {
            0% { background-color: rgba(40, 167, 69, 0.1); }
            100% { background-color: transparent; }
        }
        
        @keyframes highlightError {
            0% { background-color: rgba(220, 53, 69, 0.1); }
            100% { background-color: transparent; }
        }
    `;
    document.head.appendChild(style);
    // Selection management
    updateSelection(checkbox) {
        const driverId = checkbox.value;
        if (checkbox.checked) {
            this.selectedDrivers.add(driverId);
        } else {
            this.selectedDrivers.delete(driverId);
        }
        this.updateBulkActionButton();
    }
    
    toggleSelectAll(checked) {
        const checkboxes = document.querySelectorAll('input[name="driver_ids[]"]');
        checkboxes.forEach(cb => {
            cb.checked = checked;
            this.updateSelection(cb);
        });
    }
    
    updateBulkActionButton() {
        const button = document.querySelector('[data-target="#bulkActionModal"]');
        if (!button) return;
        
        const count = this.selectedDrivers.size;
        button.disabled = count === 0;
        button.innerHTML = count > 0 
            ? `<i class="fas fa-tasks"></i> (${count})` 
            : '<i class="fas fa-tasks"></i>';
    }
    
    // Keyboard navigation
    setupKeyboardNavigation() {
        document.addEventListener('keydown', (e) => {
            if (e.target.matches('input, textarea, select')) return;
            
            switch(e.key.toLowerCase()) {
                case 'e':
                    if (!e.ctrlKey) {
                        this.toggleFirstDriverDetails();
                        e.preventDefault();
                    }
                    break;
                case 'arrowleft':
                    if (e.ctrlKey) {
                        this.navigatePage('prev');
                        e.preventDefault();
                    }
                    break;
                case 'arrowright':
                    if (e.ctrlKey) {
                        this.navigatePage('next');
                        e.preventDefault();
                    }
                    break;
            }
        });
    }
    
    // Intersection Observer for lazy loading
    setupIntersectionObserver() {
        if (!('IntersectionObserver' in window)) return;
        
        this.observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const card = entry.target;
                    card.classList.add('loaded');
                    this.observer.unobserve(card);
                }
            });
        }, { threshold: 0.1 });
        
        document.querySelectorAll('.driver-card').forEach(card => {
            this.observer.observe(card);
        });
    }
    
    // Utility methods
    extractDriverId(onclickString) {
        const match = onclickString.match(/toggleDriverDetails\((\d+)\)/);
        return match ? match[1] : null;
    }
    
    dismissAlert(button) {
        const alert = button.closest('.alert');
        if (alert) {
            this.dismissToast(alert);
        }
    }
    
    trackAnalytics(event, data = {}) {
        if (typeof gtag !== 'undefined') {
            gtag('event', event, {
                page: 'driver_index',
                ...data
            });
        }
    }
    
    toggleFirstDriverDetails() {
        const firstButton = document.querySelector('.driver-card button[onclick*="toggleDriverDetails"]');
        if (firstButton) {
            firstButton.click();
        }
    }
    
    navigatePage(direction) {
        const selector = direction === 'prev' 
            ? '.pagination .page-item:not(.disabled) .page-link[rel="prev"]'
            : '.pagination .page-item:not(.disabled) .page-link[rel="next"]';
        const link = document.querySelector(selector);
        if (link) link.click();
    }
    
    // Undo functionality
    async handleUndo(button) {
        const driverId = button.dataset.driverId;
        const driverName = button.dataset.driverName;
        
        try {
            const response = await fetch(`/admin/drivers/${driverId}/undo-verification`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ action: 'undo', driver_id: driverId })
            });
            
            const data = await response.json();
            if (data.success) {
                this.updateDriverStatus(driverId, 'pending', driverName);
                this.showToast(`${driverName}'s status has been reset to pending`, 'info');
            }
        } catch (error) {
            this.showToast('Undo operation failed', 'error');
        }
    }
    
    showUndoOption(action, driverId, driverName) {
        // Implement undo notification if needed
        // This could be expanded based on requirements
    }
    
    updateActionButtons(card, status, driverId, driverName) {
        const actionsContainer = card.querySelector('.driver-primary-actions');
        // Implementation for updating action buttons based on status
        // This would replace the existing verification buttons with appropriate UI
    }
}

// Global utility functions for backward compatibility
function toggleDriverDetails(driverId) {
    if (window.driverInterface) {
        window.driverInterface.toggleDetails(driverId);
    }
}

function changePerPage(perPage) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', perPage);
    url.searchParams.set('page', '1');
    window.location.href = url.toString();
}

function exportDrivers() {
    const filters = new URLSearchParams(window.location.search);
    window.location.href = `{{ route('admin.drivers.export') }}?${filters.toString()}`;
}

// Initialize the driver interface
document.addEventListener('DOMContentLoaded', function() {
    window.driverInterface = new DriverInterface();
    
    // Enhanced styles
    const style = document.createElement('style');
    style.textContent = `
        .driver-card {
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .driver-card.loaded {
            opacity: 1;
            transform: translateY(0);
        }
        
        .driver-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .notification-toast {
            transform: translateX(100%);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .notification-toast.show {
            transform: translateX(0);
        }
        
        .btn.loading {
            pointer-events: none;
            position: relative;
            color: transparent !important;
        }
        
        .btn.loading::after {
            content: '';
            position: absolute;
            width: 16px;
            height: 16px;
            top: 50%;
            left: 50%;
            margin: -8px 0 0 -8px;
            border: 2px solid currentColor;
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .verification-success {
            border-left: 4px solid #28a745;
            animation: highlightSuccess 2s ease-out;
        }
        
        .verification-error {
            border-left: 4px solid #dc3545;
            animation: highlightError 2s ease-out;
        }
        
        @keyframes highlightSuccess {
            0% { background-color: rgba(40, 167, 69, 0.1); }
            100% { background-color: transparent; }
        }
        
        @keyframes highlightError {
            0% { background-color: rgba(220, 53, 69, 0.1); }
            100% { background-color: transparent; }
        }
        
        @media (max-width: 768px) {
            .driver-primary-actions {
                flex-direction: column;
                gap: 8px;
            }
            
            .driver-primary-actions .btn {
                width: 100%;
            }
        }
    `;
    document.head.appendChild(style);
});
</script>
@endsection
