@extends('layouts.admin_master')

@section('title', 'Superadmin - Full KYC Driver Registration')

@section('content_header')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center">
        <h1>
            <i class="fas fa-file-alt"></i> Full KYC Driver Registration
        </h1>
        <div>
            <a href="{{ route('admin.superadmin.drivers.create-simple') }}" class="btn btn-outline-secondary">
                <i class="fas fa-fast-forward"></i> Quick Registration
            </a>
            <a href="{{ route('admin.superadmin.drivers.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Drivers
            </a>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-file-alt"></i> Complete Driver Registration with KYC
                    </h4>
                </div>

                <div class="card-body">
                    <!-- Progress Indicator -->
                    <div class="mb-4">
                        <div class="progress">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 100%" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">
                                All Steps - Complete KYC Registration
                            </div>
                        </div>
                        <small class="text-muted mt-1 d-block">
                            This creates a fully verified driver account with complete KYC information.
                        </small>
                    </div>

                    @if ($errors->any())
                    <div class="alert alert-danger">
                        <h5><i class="fas fa-exclamation-triangle"></i> Please fix the following errors:</h5>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <form action="{{ route('admin.superadmin.drivers.store-comprehensive') }}" method="POST" enctype="multipart/form-data" id="comprehensiveRegistrationForm">
                        @csrf

                        <!-- Personal Information Section -->
                        <div class="card mb-4 border-primary">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">
                                    <i class="fas fa-user"></i> Personal Information
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <label for="first_name" class="form-label">
                                                First Name <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control @error('first_name') is-invalid @enderror"
                                                   id="first_name" name="first_name"
                                                   value="{{ old('first_name') }}" required>
                                            @error('first_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group mb-3">
                                            <label for="middle_name" class="form-label">Middle Name</label>
                                            <input type="text" class="form-control @error('middle_name') is-invalid @enderror"
                                                   id="middle_name" name="middle_name"
