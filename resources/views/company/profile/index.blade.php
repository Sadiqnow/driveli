@extends('layouts.company')

@section('title', 'Company Profile - Company Portal')

@section('page-title', 'Company Profile')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('company.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Profile</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8 mx-auto">
        <!-- Profile Header -->
        <div class="company-card mb-4">
            <div class="company-card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h4 class="mb-1">{{ $company->name }}</h4>
                        <p class="text-muted mb-2">{{ $company->company_id }}</p>
                        <div class="d-flex align-items-center">
                            <span class="badge badge-{{ $company->verification_badge['class'] }} me-3">
                                {{ $company->verification_badge['text'] }}
                            </span>
                            <small class="text-muted">
                                Member since {{ $company->created_at->format('M Y') }}
                            </small>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="{{ route('company.profile.edit') }}" class="btn btn-company-primary">
                            <i class="fas fa-edit me-2"></i>Edit Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Information -->
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="company-card h-100">
                    <div class="company-card-header">
                        <h5 class="mb-0"><i class="fas fa-building me-2"></i>Company Details</h5>
                    </div>
                    <div class="company-card-body">
                        <div class="mb-3">
                            <label class="form-label text-muted">Company Name</label>
                            <p class="mb-0">{{ $company->name }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Registration Number</label>
                            <p class="mb-0">{{ $company->registration_number ?? 'Not provided' }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Tax ID</label>
                            <p class="mb-0">{{ $company->tax_id ?? 'Not provided' }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Industry</label>
                            <p class="mb-0">{{ $company->industry ?? 'Not specified' }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Address</label>
                            <p class="mb-0">{{ $company->address }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Location</label>
                            <p class="mb-0">{{ $company->city ? $company->city . ', ' : '' }}{{ $company->state }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Website</label>
                            <p class="mb-0">
                                @if($company->website)
                                    <a href="{{ $company->website }}" target="_blank" class="text-primary">
                                        {{ $company->website }} <i class="fas fa-external-link-alt ms-1"></i>
                                    </a>
                                @else
                                    Not provided
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="company-card h-100">
                    <div class="company-card-header">
                        <h5 class="mb-0"><i class="fas fa-user me-2"></i>Contact Information</h5>
                    </div>
                    <div class="company-card-body">
                        <div class="mb-3">
                            <label class="form-label text-muted">Contact Person</label>
                            <p class="mb-0">{{ $company->contact_person_name }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Job Title</label>
                            <p class="mb-0">{{ $company->contact_person_title ?? 'Not specified' }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Email</label>
                            <p class="mb-0">{{ $company->contact_person_email }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Phone</label>
                            <p class="mb-0">{{ $company->formatted_contact_phone ?? $company->phone }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-muted">Description</label>
                            <p class="mb-0">{{ $company->description ?? 'No description provided' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Account Statistics -->
        <div class="row">
            <div class="col-12 mb-4">
                <div class="company-card">
                    <div class="company-card-header">
                        <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Account Statistics</h5>
                    </div>
                    <div class="company-card-body">
                        <div class="row text-center">
                            <div class="col-md-3 mb-3">
                                <div class="stats-box">
                                    <h3 class="stats-number">{{ $company->requests()->count() }}</h3>
                                    <p class="stats-label mb-0">Total Requests</p>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="stats-box">
                                    <h3 class="stats-number">{{ $company->requests()->where('status', 'completed')->count() }}</h3>
                                    <p class="stats-label mb-0">Completed Jobs</p>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="stats-box">
                                    <h3 class="stats-number">{{ $company->requests()->whereIn('status', ['active', 'pending'])->count() }}</h3>
                                    <p class="stats-label mb-0">Active Requests</p>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="stats-box">
                                    <h3 class="stats-number">
                                        @php
                                            $avgRating = \App\Models\DriverMatch::whereHas('request', function($q) use($company) {
                                                $q->where('company_id', $company->id);
                                            })->whereNotNull('company_rating')->avg('company_rating');
                                        @endphp
                                        {{ $avgRating ? number_format($avgRating, 1) : 'N/A' }}
                                    </h3>
                                    <p class="stats-label mb-0">Average Rating</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Account Settings -->
        <div class="row">
            <div class="col-12">
                <div class="company-card">
                    <div class="company-card-header">
                        <h5 class="mb-0"><i class="fas fa-cog me-2"></i>Account Settings</h5>
                    </div>
                    <div class="company-card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <h6>Security</h6>
                                <p class="text-muted small mb-2">Manage your account security settings</p>
                                <a href="#" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-lock me-1"></i>Change Password
                                </a>
                            </div>
                            <div class="col-md-6 mb-3">
                                <h6>Notifications</h6>
                                <p class="text-muted small mb-2">Configure how you receive notifications</p>
                                <a href="#" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-bell me-1"></i>Notification Settings
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('css')
<style>
.stats-box {
    padding: 1rem;
    border-radius: 0.5rem;
    background: var(--company-gray-50);
    border: 1px solid var(--company-gray-200);
}

.stats-number {
    font-size: 2rem;
    font-weight: 700;
    color: var(--company-primary);
    margin-bottom: 0.5rem;
}

.stats-label {
    color: var(--company-gray-600);
    font-size: 0.875rem;
}
</style>
@endsection
