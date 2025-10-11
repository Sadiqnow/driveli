@extends('layouts.company')

@section('title', 'Dashboard - Company Portal')

@section('page-title', 'Dashboard')

@section('content')
<div class="row">
    <!-- Statistics Cards -->
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="company-card stats-card">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <h6 class="text-white-50 mb-1">Total Requests</h6>
                    <h3 class="stats-number mb-1">{{ number_format($stats['total_requests']) }}</h3>
                    <p class="stats-label mb-0">{{ $stats['active_requests'] }} Active</p>
                </div>
                <div class="ms-3">
                    <i class="fas fa-clipboard-list fa-2x text-white-50"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="company-card stats-card success">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <h6 class="text-white-50 mb-1">Completed Jobs</h6>
                    <h3 class="stats-number mb-1">{{ number_format($stats['completed_requests']) }}</h3>
                    <p class="stats-label mb-0">{{ $stats['fulfillment_rate'] }}% Success Rate</p>
                </div>
                <div class="ms-3">
                    <i class="fas fa-check-circle fa-2x text-white-50"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="company-card stats-card warning">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <h6 class="text-white-50 mb-1">Active Matches</h6>
                    <h3 class="stats-number mb-1">{{ number_format($stats['total_matches']) }}</h3>
                    <p class="stats-label mb-0">{{ $stats['match_success_rate'] }}% Success Rate</p>
                </div>
                <div class="ms-3">
                    <i class="fas fa-handshake fa-2x text-white-50"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="company-card stats-card info">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <h6 class="text-white-50 mb-1">Average Rating</h6>
                    <h3 class="stats-number mb-1">{{ $stats['average_rating'] }}</h3>
                    <p class="stats-label mb-0">Out of 5.0</p>
                </div>
                <div class="ms-3">
                    <i class="fas fa-star fa-2x text-white-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Quick Actions -->
    <div class="col-lg-4 mb-4">
        <div class="company-card">
            <div class="company-card-header">
                <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
            </div>
            <div class="company-card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('company.requests.create') }}" class="btn btn-company-primary">
                        <i class="fas fa-plus me-2"></i>Create New Request
                    </a>
                    <a href="{{ route('company.matching.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-search me-2"></i>Find Drivers
                    </a>
                    <a href="{{ route('company.requests.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-list me-2"></i>View All Requests
                    </a>
                    <a href="{{ route('company.reports.index') }}" class="btn btn-outline-info">
                        <i class="fas fa-chart-bar me-2"></i>View Reports
                    </a>
                </div>
            </div>
        </div>

        <!-- Company Info -->
        <div class="company-card mt-4">
            <div class="company-card-header">
                <h5 class="mb-0"><i class="fas fa-building me-2"></i>Company Info</h5>
            </div>
            <div class="company-card-body">
                <div class="mb-3">
                    <strong>{{ $company->name }}</strong><br>
                    <small class="text-muted">{{ $company->company_id }}</small>
                </div>
                <div class="mb-3">
                    <i class="fas fa-user me-2"></i>{{ $company->contact_person_name }}<br>
                    <i class="fas fa-envelope me-2"></i>{{ $company->contact_person_email }}<br>
                    <i class="fas fa-phone me-2"></i>{{ $company->formatted_contact_phone ?? $company->phone }}
                </div>
                <div class="mb-3">
                    <span class="badge badge-{{ $company->verification_badge['class'] }}">
                        {{ $company->verification_badge['text'] }}
                    </span>
                </div>
                <a href="{{ route('company.profile.index') }}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-edit me-1"></i>Edit Profile
                </a>
            </div>
        </div>
    </div>

    <!-- Recent Requests -->
    <div class="col-lg-4 mb-4">
        <div class="company-card">
            <div class="company-card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Recent Requests</h5>
                <a href="{{ route('company.requests.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="company-card-body">
                @if($recentRequests->count() > 0)
                    @foreach($recentRequests as $request)
                        <div class="d-flex align-items-start mb-3 pb-3 border-bottom">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $request->position_title }}</h6>
                                <small class="text-muted d-block">{{ $request->location }}</small>
                                <small class="text-muted">{{ $request->created_at->diffForHumans() }}</small>
                            </div>
                            <div class="ms-2">
                                <span class="badge badge-{{ $request->status_badge }}">
                                    {{ ucfirst($request->status) }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No requests yet</p>
                        <a href="{{ route('company.requests.create') }}" class="btn btn-sm btn-company-primary">
                            Create Your First Request
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Recent Matches -->
    <div class="col-lg-4 mb-4">
        <div class="company-card">
            <div class="company-card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-handshake me-2"></i>Recent Matches</h5>
                <a href="{{ route('company.matching.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="company-card-body">
                @if($recentMatches->count() > 0)
                    @foreach($recentMatches as $match)
                        <div class="d-flex align-items-start mb-3 pb-3 border-bottom">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $match->driver->first_name }} {{ $match->driver->surname }}</h6>
                                <small class="text-muted d-block">{{ $match->request->position_title }}</small>
                                <small class="text-muted">{{ $match->matched_at->diffForHumans() }}</small>
                            </div>
                            <div class="ms-2">
                                <span class="badge badge-{{ $match->status === 'completed' ? 'completed' : ($match->status === 'active' ? 'active' : 'pending') }}">
                                    {{ ucfirst($match->status) }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-handshake fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No matches yet</p>
                        <a href="{{ route('company.matching.index') }}" class="btn btn-sm btn-company-primary">
                            Find Drivers
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Available Drivers Count -->
<div class="row">
    <div class="col-12">
        <div class="company-card">
            <div class="company-card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h5 class="mb-1"><i class="fas fa-users me-2"></i>Driver Marketplace</h5>
                        <p class="text-muted mb-0">{{ number_format($availableDriversCount) }} verified drivers available for matching</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="{{ route('company.matching.index') }}" class="btn btn-company-primary">
                            <i class="fas fa-search me-2"></i>Browse Drivers
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
