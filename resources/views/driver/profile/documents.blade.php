@extends('drivers.layouts.app')

@section('title', 'Manage Documents')
@section('page_title', 'Manage Documents')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('driver.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Documents</li>
@endsection

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Manage Documents</h4>
                    <a href="{{ route('driver.dashboard') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle"></i> Document Requirements</h6>
                        <p class="mb-0">Please upload the following documents to complete your verification:</p>
                    </div>

                    <div class="row">
                        <!-- Driver's License -->
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="fas fa-id-card text-primary"></i> Driver's License
                                    </h6>
                                    <p class="card-text text-muted">Upload a clear photo of your driver's license</p>
                                    
                                    @if(auth('driver')->user()->drivers_license_photo_path)
                                        <div class="mb-2">
                                            <span class="badge bg-success">
                                                <i class="fas fa-check"></i> Uploaded
                                            </span>
                                        </div>
                                        <img src="{{ Storage::url(auth('driver')->user()->drivers_license_photo_path) }}" 
                                             alt="Driver License" class="img-thumbnail mb-2" style="max-width: 200px;">
                                    @else
                                        <div class="mb-2">
                                            <span class="badge bg-warning">
                                                <i class="fas fa-exclamation-triangle"></i> Required
                                            </span>
                                        </div>
                                    @endif
                                    
                                    <form action="#" method="POST" enctype="multipart/form-data" class="mt-2">
                                        @csrf
                                        <div class="mb-2">
                                            <input type="file" class="form-control form-control-sm" 
                                                   name="drivers_license" accept="image/*,.pdf">
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="fas fa-upload"></i> Upload
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Profile Photo -->
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="fas fa-user-circle text-info"></i> Profile Photo
                                    </h6>
                                    <p class="card-text text-muted">Upload a passport-style photograph</p>
                                    
                                    @if(auth('driver')->user()->profile_photo_path)
                                        <div class="mb-2">
                                            <span class="badge bg-success">
                                                <i class="fas fa-check"></i> Uploaded
                                            </span>
                                        </div>
                                        <img src="{{ Storage::url(auth('driver')->user()->profile_photo_path) }}" 
                                             alt="Profile Photo" class="img-thumbnail mb-2" style="max-width: 200px;">
                                    @else
                                        <div class="mb-2">
                                            <span class="badge bg-warning">
                                                <i class="fas fa-exclamation-triangle"></i> Required
                                            </span>
                                        </div>
                                    @endif
                                    
                                    <form action="#" method="POST" enctype="multipart/form-data" class="mt-2">
                                        @csrf
                                        <div class="mb-2">
                                            <input type="file" class="form-control form-control-sm" 
                                                   name="profile_photo" accept="image/*">
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="fas fa-upload"></i> Upload
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- National ID -->
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="fas fa-credit-card text-success"></i> National ID/Passport
                                    </h6>
                                    <p class="card-text text-muted">Upload your National ID card or International Passport</p>
                                    
                                    @if(auth('driver')->user()->national_id_path)
                                        <div class="mb-2">
                                            <span class="badge bg-success">
                                                <i class="fas fa-check"></i> Uploaded
                                            </span>
                                        </div>
                                        <img src="{{ Storage::url(auth('driver')->user()->national_id_path) }}" 
                                             alt="National ID" class="img-thumbnail mb-2" style="max-width: 200px;">
                                    @else
                                        <div class="mb-2">
                                            <span class="badge bg-warning">
                                                <i class="fas fa-exclamation-triangle"></i> Required
                                            </span>
                                        </div>
                                    @endif
                                    
                                    <form action="#" method="POST" enctype="multipart/form-data" class="mt-2">
                                        @csrf
                                        <div class="mb-2">
                                            <input type="file" class="form-control form-control-sm" 
                                                   name="national_id" accept="image/*,.pdf">
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="fas fa-upload"></i> Upload
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Proof of Address -->
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="fas fa-home text-warning"></i> Proof of Address
                                    </h6>
                                    <p class="card-text text-muted">Upload a utility bill or bank statement (not older than 3 months)</p>
                                    
                                    @if(auth('driver')->user()->proof_of_address_path)
                                        <div class="mb-2">
                                            <span class="badge bg-success">
                                                <i class="fas fa-check"></i> Uploaded
                                            </span>
                                        </div>
                                        <img src="{{ Storage::url(auth('driver')->user()->proof_of_address_path) }}" 
                                             alt="Proof of Address" class="img-thumbnail mb-2" style="max-width: 200px;">
                                    @else
                                        <div class="mb-2">
                                            <span class="badge bg-warning">
                                                <i class="fas fa-exclamation-triangle"></i> Required
                                            </span>
                                        </div>
                                    @endif
                                    
                                    <form action="#" method="POST" enctype="multipart/form-data" class="mt-2">
                                        @csrf
                                        <div class="mb-2">
                                            <input type="file" class="form-control form-control-sm" 
                                                   name="proof_of_address" accept="image/*,.pdf">
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="fas fa-upload"></i> Upload
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-secondary">
                        <h6><i class="fas fa-lightbulb"></i> Upload Tips</h6>
                        <ul class="mb-0">
                            <li>Ensure all documents are clear and readable</li>
                            <li>Accepted formats: JPG, PNG, PDF</li>
                            <li>Maximum file size: 5MB per document</li>
                            <li>Documents should not be older than 6 months (except license and ID)</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.img-thumbnail {
    border-radius: 0.375rem;
}

.badge {
    font-size: 0.75em;
}
</style>
@endsection