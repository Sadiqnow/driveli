@extends('layouts.admin_cdn')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid d-flex justify-content-between">
            <h1>Driver Details</h1>
            <a href="{{ route('admin.drivers.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-primary card-outline">
                <div class="card-body">
                    <h4>{!! htmlspecialchars($driver->full_name, ENT_NOQUOTES, 'UTF-8') !!}</h4>
                    <p><strong>Phone:</strong> {{ $driver->phone }}</p>
                    <p><strong>Status:</strong> 
                        @if($driver->status == 'Available')
                            <span class="badge bg-success">Available</span>
                        @elseif($driver->status == 'Booked')
                            <span class="badge bg-warning text-dark">Booked</span>
                        @elseif($driver->status == 'Not Available')
                            <span class="badge bg-secondary">Not Available</span>
                        @else
                            <span class="badge bg-danger">Suspended</span>
                        @endif
                    </p>
                    <p><strong>Vehicle Types:</strong> {{ is_array($driver->vehicle_types) ? implode(', ', $driver->vehicle_types) : $driver->vehicle_types }}</p>
                    <p><strong>License Number:</strong> {{ $driver->license_number }}</p>
                    <p><strong>License Expiry:</strong> {{ $driver->license_expiry_date ? $driver->license_expiry_date->format('d/m/Y') : 'N/A' }}</p>
                    <p><strong>Email:</strong> {{ $driver->email }}</p>
                    <p><strong>Date of Birth:</strong> {{ $driver->date_of_birth ? $driver->date_of_birth->format('d/m/Y') : 'N/A' }}</p>
                    <p><strong>Gender:</strong> {{ $driver->gender }}</p>
                    
                    <!-- Origin Information -->
                    <h5 class="mt-4 mb-3"><i class="fas fa-map-marker-alt"></i> Origin Information</h5>
                    <p><strong>State of Origin:</strong> {{ $driver->originState->name ?? ($driver->state_of_origin ? 'State ID: ' . $driver->state_of_origin : 'N/A') }}</p>
                    <p><strong>LGA of Origin:</strong> {{ $driver->originLga->name ?? ($driver->lga_of_origin ? 'LGA ID: ' . $driver->lga_of_origin : 'N/A') }}</p>
                    <p><strong>Address of Origin:</strong> {{ $driver->address_of_origin ?? 'N/A' }}</p>
                    
                    <p><strong>Address:</strong> {{ $driver->address }}</p>
                    <p><strong>State:</strong> {{ $driver->state }}</p>
                    <p><strong>LGA:</strong> {{ $driver->lga }}</p>
                    <p><strong>Experience Level:</strong> {{ $driver->experience_level }}</p>
                    <p><strong>Regions:</strong> {{ is_array($driver->regions) ? implode(', ', $driver->regions) : $driver->regions }}</p>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
