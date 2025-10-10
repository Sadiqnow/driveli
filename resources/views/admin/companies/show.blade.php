@extends('layouts.admin_cdn')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid d-flex justify-content-between">
            <h1>Company Details</h1>
            <a href="{{ route('admin.companies.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Company Information -->
                <div class="col-md-8">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Company Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Company Name:</strong> {{ $company->name }}</p>
                                    <p><strong>Company ID:</strong> {{ $company->company_id ?? 'N/A' }}</p>
                                    <p><strong>Registration Number:</strong> {{ $company->registration_number ?? 'N/A' }}</p>
                                    <p><strong>Email:</strong> {{ $company->email }}</p>
                                    <p><strong>Phone:</strong> {{ $company->phone }}</p>
                                    <p><strong>Website:</strong> 
                                        @if($company->website)
                                            <a href="{{ $company->website }}" target="_blank">{{ $company->website }}</a>
                                        @else
                                            N/A
                                        @endif
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Industry:</strong> {{ ucfirst($company->industry ?? 'N/A') }}</p>
                                    <p><strong>Status:</strong> 
                                        @if($company->status == 'active')
                                            <span class="badge bg-success">Active</span>
                                        @elseif($company->status == 'inactive')
                                            <span class="badge bg-secondary">Inactive</span>
                                        @else
                                            <span class="badge bg-danger">Suspended</span>
                                        @endif
                                    </p>
                                    <p><strong>Verification Status:</strong>
                                        @if($company->verification_status == 'verified')
                                            <span class="badge bg-success">Verified</span>
                                        @elseif($company->verification_status == 'pending')
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        @else
                                            <span class="badge bg-danger">Rejected</span>
                                        @endif
                                    </p>
                                    <p><strong>Created:</strong> {{ $company->created_at ? $company->created_at->format('d/m/Y H:i') : 'N/A' }}</p>
                                    <p><strong>Commission Rate:</strong> {{ $company->default_commission_rate ?? 0 }}%</p>
                                    <p><strong>Payment Terms:</strong> {{ ucfirst(str_replace('_', ' ', $company->payment_terms ?? 'N/A')) }}</p>
                                </div>
                            </div>
                            
                            @if($company->description)
                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <p><strong>Description:</strong></p>
                                        <p class="bg-light p-3 rounded">{{ $company->description }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Contact Person -->
                    <div class="card card-info card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Contact Person</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Name:</strong> {{ $company->contact_person_name ?? 'N/A' }}</p>
                                    <p><strong>Title:</strong> {{ $company->contact_person_title ?? 'N/A' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Email:</strong> {{ $company->contact_person_email ?? 'N/A' }}</p>
                                    <p><strong>Phone:</strong> {{ $company->contact_person_phone ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Address -->
                    <div class="card card-success card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Address Information</h3>
                        </div>
                        <div class="card-body">
                            <p><strong>Address:</strong></p>
                            <p>{{ $company->address ?? 'N/A' }}</p>
                            <div class="row">
                                <div class="col-md-4">
                                    <p><strong>State:</strong> {{ $company->state ?? 'N/A' }}</p>
                                </div>
                                <div class="col-md-4">
                                    <p><strong>LGA:</strong> {{ $company->lga ?? 'N/A' }}</p>
                                </div>
                                <div class="col-md-4">
                                    <p><strong>Postal Code:</strong> {{ $company->postal_code ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions & Requirements -->
                <div class="col-md-4">
                    <div class="card card-secondary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Actions</h3>
                        </div>
                        <div class="card-body">
                            <a href="{{ route('admin.companies.edit', $company->id) }}" class="btn btn-primary btn-block mb-2">
                                <i class="fas fa-edit"></i> Edit Company
                            </a>
                            
                            <form action="{{ route('admin.companies.toggle-status', $company->id) }}" method="POST" class="mb-2">
                                @csrf
                                <button class="btn btn-warning btn-block" onclick="return confirm('Toggle company status?')">
                                    <i class="fas fa-power-off"></i> Toggle Status
                                </button>
                            </form>
                            
                            <form action="{{ route('admin.companies.destroy', $company->id) }}" method="POST">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-block" onclick="return confirm('Are you sure you want to delete this company?')">
                                    <i class="fas fa-trash"></i> Delete Company
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Business Requirements -->
                    <div class="card card-warning card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Business Requirements</h3>
                        </div>
                        <div class="card-body">
                            <p><strong>Vehicle Types Needed:</strong></p>
                            <p>
                                @if($company->vehicle_types_needed && is_array($company->vehicle_types_needed))
                                    {{ implode(', ', $company->vehicle_types_needed) }}
                                @else
                                    N/A
                                @endif
                            </p>
                            
                            <p><strong>Preferred Regions:</strong></p>
                            <p>
                                @if($company->preferred_regions && is_array($company->preferred_regions))
                                    {{ implode(', ', $company->preferred_regions) }}
                                @else
                                    N/A
                                @endif
                            </p>
                        </div>
                    </div>

                    <!-- Statistics -->
                    <div class="card card-info card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Statistics</h3>
                        </div>
                        <div class="card-body">
                            <p><strong>Total Requests:</strong> {{ $company->total_requests ?? 0 }}</p>
                            <p><strong>Fulfilled Requests:</strong> {{ $company->fulfilled_requests ?? 0 }}</p>
                            <p><strong>Total Amount Paid:</strong> ₦{{ number_format($company->total_amount_paid ?? 0, 2) }}</p>
                            <p><strong>Average Rating:</strong> {{ $company->average_rating ?? 0 }}/5</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection