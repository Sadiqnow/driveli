@extends('layouts.admin_cdn')

@section('title', 'Driver Documents - ' . $driver->full_name)

@section('head')
<style>
/* Modern Document Upload Zone */
.document-upload-zone {
    border: 3px dashed #cbd5e0;
    border-radius: 20px;
    padding: 3rem 2rem;
    text-align: center;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    cursor: pointer;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.document-upload-zone::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(59, 130, 246, 0.1), transparent);
    transition: left 0.5s ease;
}

.document-upload-zone:hover {
    border-color: #3b82f6;
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(59, 130, 246, 0.15);
}

.document-upload-zone:hover::before {
    left: 100%;
}

.document-upload-zone.dragover {
    border-color: #10b981;
    background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
    transform: scale(1.02);
    box-shadow: 0 20px 40px rgba(16, 185, 129, 0.2);
}

.upload-zone-icon {
    font-size: 4rem;
    color: #6b7280;
    margin-bottom: 1.5rem;
    transition: all 0.3s ease;
}

.document-upload-zone:hover .upload-zone-icon {
    color: #3b82f6;
    transform: scale(1.1);
}

.document-upload-zone.dragover .upload-zone-icon {
    color: #10b981;
    transform: scale(1.2) rotate(10deg);
}

/* Enhanced Document Items */
.document-item {
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
    background: white;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.document-item:hover {
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    transform: translateY(-2px);
    border-color: #d1d5db;
}

.document-preview {
    width: 120px;
    height: 120px;
    object-fit: cover;
    border-radius: 12px;
    border: 2px solid #e5e7eb;
    transition: all 0.3s ease;
}

.document-preview:hover {
    transform: scale(1.05);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

/* Modern Status Badges */
.status-badge {
    font-size: 0.75rem;
    padding: 0.375rem 1rem;
    border-radius: 20px;
    font-weight: 600;
    letter-spacing: 0.025em;
    text-transform: uppercase;
    border: 2px solid transparent;
    transition: all 0.3s ease;
}

.status-badge.bg-warning {
    background: linear-gradient(135deg, #fbbf24, #f59e0b) !important;
    color: white !important;
    border-color: #f59e0b;
}

.status-badge.bg-success {
    background: linear-gradient(135deg, #10b981, #059669) !important;
    color: white !important;
    border-color: #059669;
}

.status-badge.bg-danger {
    background: linear-gradient(135deg, #ef4444, #dc2626) !important;
    color: white !important;
    border-color: #dc2626;
}

/* Enhanced Upload Progress */
.upload-progress {
    height: 8px;
    border-radius: 10px;
    overflow: hidden;
    background: #f3f4f6;
    margin-top: 1rem;
    position: relative;
    box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
}

.upload-progress-bar {
    height: 100%;
    background: linear-gradient(90deg, #3b82f6, #1d4ed8, #3b82f6);
    background-size: 200% 100%;
    animation: progressShimmer 2s infinite;
    transition: width 0.3s ease;
    border-radius: 10px;
    position: relative;
}

.upload-progress-bar::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
    animation: progressGlow 1.5s infinite;
}

@keyframes progressShimmer {
    0% { background-position: -200% 0; }
    100% { background-position: 200% 0; }
}

@keyframes progressGlow {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}

/* Quick Upload Buttons */
.quick-upload-btn {
    border-radius: 12px;
    padding: 0.75rem 1rem;
    font-weight: 500;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.quick-upload-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    transition: left 0.5s ease;
}

.quick-upload-btn:hover::before {
    left: 100%;
}

.quick-upload-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

/* Document Actions */
.document-actions {
    display: flex;
    gap: 0.5rem;
    justify-content: center;
    flex-wrap: wrap;
    margin-top: 1rem;
}

.document-actions .btn {
    border-radius: 8px;
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
    font-weight: 500;
    transition: all 0.3s ease;
}

.document-actions .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Enhanced Cards */
.card {
    border: none;
    border-radius: 16px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    overflow: hidden;
}

.card:hover {
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.card-header {
    border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    padding: 1.5rem;
    background: linear-gradient(135deg, #f8fafc, #f1f5f9);
}

/* Responsive Design */
@media (max-width: 768px) {
    .document-upload-zone {
        padding: 2rem 1rem;
    }

    .upload-zone-icon {
        font-size: 3rem;
    }

    .document-preview {
        width: 80px;
        height: 80px;
    }

    .quick-upload-btn {
        font-size: 0.75rem;
        padding: 0.5rem 0.75rem;
    }
}
</style>
@endsection

@section('content')
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">
                        <i class="fas fa-file-alt"></i> Driver Documents
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.drivers.index') }}">Drivers</a></li>
                        <li class="breadcrumb-item active">Documents</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Driver Info Card -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h3 class="card-title">
                                <i class="fas fa-user"></i> {{ $driver->full_name }}
                            </h3>
                            <div class="card-tools">
                                <a href="{{ route('admin.drivers.show', $driver->id) }}" class="btn btn-light btn-sm">
                                    <i class="fas fa-eye"></i> View Profile
                                </a>
                                <a href="{{ route('admin.drivers.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-arrow-left"></i> Back to Drivers
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <strong>Driver ID:</strong> {{ $driver->driver_id }}
                                </div>
                                <div class="col-md-3">
                                    <strong>Phone:</strong> {{ $driver->phone }}
                                </div>
                                <div class="col-md-3">
                                    <strong>Email:</strong> {{ $driver->email ?: 'Not provided' }}
                                </div>
                                <div class="col-md-3">
                                    <strong>Status:</strong> 
                                    <span class="badge badge-{{ $driver->status === 'active' ? 'success' : ($driver->status === 'inactive' ? 'warning' : 'danger') }}">
                                        {{ ucfirst($driver->status) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Document Upload Section -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h3 class="card-title">
                                <i class="fas fa-cloud-upload-alt"></i> Upload Documents
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-light btn-sm" onclick="loadDocuments()">
                                    <i class="fas fa-sync"></i> Refresh
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Upload Zone -->
                            <div class="document-upload-zone" id="uploadZone" onclick="document.getElementById('fileInput').click()">
                                <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                <h4 class="text-muted">Drop files here or click to browse</h4>
                                <p class="text-muted">Supports: JPG, PNG, PDF (Max 5MB each)</p>
                                <input type="file" id="fileInput" multiple accept=".jpg,.jpeg,.png,.pdf" style="display: none;">
                            </div>
                            
                            <!-- Upload Progress -->
                            <div id="uploadProgress" style="display: none;">
                                <div class="upload-progress">
                                    <div class="upload-progress-bar" id="progressBar" style="width: 0%"></div>
                                </div>
                                <p class="text-center mt-2" id="progressText">Uploading...</p>
                            </div>
                            
                            <!-- Quick Upload Buttons -->
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <h6><i class="fas fa-bolt"></i> Quick Upload</h6>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" class="btn btn-outline-primary" onclick="uploadSpecificDocument('profile_picture')">
                                            <i class="fas fa-user-circle"></i> Profile Photo
                                        </button>
                                        <button type="button" class="btn btn-outline-info" onclick="uploadSpecificDocument('nin')">
                                            <i class="fas fa-id-card"></i> NIN Document
                                        </button>
                                        <button type="button" class="btn btn-outline-warning" onclick="uploadSpecificDocument('license_front')">
                                            <i class="fas fa-credit-card"></i> License Front
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary" onclick="uploadSpecificDocument('license_back')">
                                            <i class="fas fa-credit-card"></i> License Back
                                        </button>
                                        <button type="button" class="btn btn-outline-dark" onclick="uploadSpecificDocument('passport_photo')">
                                            <i class="fas fa-passport"></i> Passport Photo
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Current Documents Status -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-list"></i> Current Documents
                            </h3>
                            <div class="card-tools">
                                <span class="badge badge-info" id="documentCount">Loading...</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div id="documentsContainer">
                                <div class="text-center">
                                    <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                                    <p class="text-muted mt-2">Loading documents...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Documents Grid -->
            <div class="row">
                <!-- Profile Photo -->
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card">
                        <div class="card-header bg-info text-white">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-user-circle"></i> Profile Photo
                            </h5>
                        </div>
                        <div class="card-body text-center">
                            @php
                                $profileDoc = $driver->documents()->where('document_type', 'profile_picture')->first();
                                $profilePath = $profileDoc ? $profileDoc->document_path : $driver->profile_picture;
                            @endphp
                            @if($profilePath)
                                <img src="{{ Storage::url($profilePath) }}" 
                                     alt="Profile Photo" 
                                     class="img-fluid rounded mb-3" 
                                     style="max-height: 200px;"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                <div class="text-muted" style="display: none;">
                                    <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                                    <p>Unable to load image</p>
                                </div>
                                <div class="document-actions">
                                    <button class="btn btn-success btn-sm" onclick="approveDocument('profile_picture', '{{ $driver->id }}')">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                    <button class="btn btn-danger btn-sm" onclick="rejectDocument('profile_picture', '{{ $driver->id }}')">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                    <a href="{{ Storage::url($profilePath) }}" target="_blank" class="btn btn-primary btn-sm">
                                        <i class="fas fa-external-link-alt"></i> View Full
                                    </a>
                                </div>
                                @if($profileDoc && $profileDoc->verification_status)
                                <div class="mt-2">
                                    <span class="badge badge-{{ $profileDoc->verification_status === 'approved' ? 'success' : ($profileDoc->verification_status === 'rejected' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($profileDoc->verification_status) }}
                                    </span>
                                </div>
                                @endif
                            @else
                                <div class="text-muted">
                                    <i class="fas fa-image fa-3x mb-3"></i>
                                    <p>No profile photo uploaded</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- License Front -->
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-id-card"></i> License (Front)
                            </h5>
                        </div>
                        <div class="card-body text-center">
                            @php
                                $licenseDoc = $driver->documents()->where('document_type', 'license_front')->first();
                                $licensePath = $licenseDoc ? $licenseDoc->document_path : $driver->license_front_image;
                            @endphp
                            @if($licensePath)
                                <img src="{{ Storage::url($licensePath) }}" 
                                     alt="License Front" 
                                     class="img-fluid rounded mb-3" 
                                     style="max-height: 200px;"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                <div class="text-muted" style="display: none;">
                                    <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                                    <p>Unable to load image</p>
                                </div>
                                <div class="document-actions">
                                    <button class="btn btn-success btn-sm" onclick="approveDocument('license_front', '{{ $driver->id }}')">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                    <button class="btn btn-danger btn-sm" onclick="rejectDocument('license_front', '{{ $driver->id }}')">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                    <a href="{{ Storage::url($licensePath) }}" target="_blank" class="btn btn-primary btn-sm">
                                        <i class="fas fa-external-link-alt"></i> View Full
                                    </a>
                                    <button class="btn btn-warning btn-sm" onclick="initiateOCRVerification('{{ $driver->id }}', 'frsc')">
                                        <i class="fas fa-eye"></i> OCR Verify
                                    </button>
                                </div>
                                @if($licenseDoc && $licenseDoc->verification_status)
                                <div class="mt-2">
                                    <span class="badge badge-{{ $licenseDoc->verification_status === 'approved' ? 'success' : ($licenseDoc->verification_status === 'rejected' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($licenseDoc->verification_status) }}
                                    </span>
                                </div>
                                @endif
                                @if($licenseDoc && $licenseDoc->ocr_match_score > 0)
                                <div class="mt-2">
                                    <span class="badge badge-{{ $licenseDoc->ocr_match_score >= 80 ? 'success' : 'danger' }}">
                                        OCR Score: {{ $licenseDoc->ocr_match_score }}%
                                    </span>
                                </div>
                                @endif
                            @else
                                <div class="text-muted">
                                    <i class="fas fa-id-card fa-3x mb-3"></i>
                                    <p>No license front image uploaded</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- License Back -->
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-id-card"></i> License (Back)
                            </h5>
                        </div>
                        <div class="card-body text-center">
                            @if($driver->license_back_image)
                                <img src="{{ Storage::url($driver->license_back_image) }}" 
                                     alt="License Back" 
                                     class="img-fluid rounded mb-3" 
                                     style="max-height: 200px;">
                                <div class="document-actions">
                                    <button class="btn btn-success btn-sm" onclick="approveDocument('license_back', '{{ $driver->id }}')">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                    <button class="btn btn-danger btn-sm" onclick="rejectDocument('license_back', '{{ $driver->id }}')">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                    <a href="{{ Storage::url($driver->license_back_image) }}" target="_blank" class="btn btn-primary btn-sm">
                                        <i class="fas fa-external-link-alt"></i> View Full
                                    </a>
                                </div>
                            @else
                                <div class="text-muted">
                                    <i class="fas fa-id-card fa-3x mb-3"></i>
                                    <p>No license back image uploaded</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- NIN Document -->
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-id-badge"></i> NIN Document
                            </h5>
                        </div>
                        <div class="card-body text-center">
                            @php
                                $ninDoc = $driver->documents()->where('document_type', 'nin')->first();
                                $ninPath = $ninDoc ? $ninDoc->document_path : $driver->nin_document;
                            @endphp
                            @if($ninPath)
                                <img src="{{ Storage::url($ninPath) }}" 
                                     alt="NIN Document" 
                                     class="img-fluid rounded mb-3" 
                                     style="max-height: 200px;"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                <div class="text-muted" style="display: none;">
                                    <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                                    <p>Unable to load image</p>
                                </div>
                                <div class="document-actions">
                                    <button class="btn btn-success btn-sm" onclick="approveDocument('nin_document', '{{ $driver->id }}')">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                    <button class="btn btn-danger btn-sm" onclick="rejectDocument('nin_document', '{{ $driver->id }}')">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                    <a href="{{ Storage::url($ninPath) }}" target="_blank" class="btn btn-primary btn-sm">
                                        <i class="fas fa-external-link-alt"></i> View Full
                                    </a>
                                    <button class="btn btn-warning btn-sm" onclick="initiateOCRVerification('{{ $driver->id }}', 'nin')">
                                        <i class="fas fa-eye"></i> OCR Verify
                                    </button>
                                </div>
                                
                                @if($ninDoc && $ninDoc->verification_status)
                                <div class="mt-2">
                                    <span class="badge badge-{{ $ninDoc->verification_status === 'approved' ? 'success' : ($ninDoc->verification_status === 'rejected' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($ninDoc->verification_status) }}
                                    </span>
                                </div>
                                @endif

                                @if($ninDoc && $ninDoc->ocr_match_score > 0)
                                <div class="mt-2">
                                    <span class="badge badge-{{ $ninDoc->ocr_match_score >= 80 ? 'success' : 'danger' }}">
                                        OCR Score: {{ $ninDoc->ocr_match_score }}%
                                    </span>
                                </div>
                                @elseif($driver->nin_verification_data && $driver->nin_ocr_match_score)
                                <div class="mt-2">
                                    <span class="badge badge-{{ $driver->nin_ocr_match_score >= 80 ? 'success' : 'danger' }}">
                                        OCR Score: {{ $driver->nin_ocr_match_score }}%
                                    </span>
                                </div>
                                @endif
                            @else
                                <div class="text-muted">
                                    <i class="fas fa-id-badge fa-3x mb-3"></i>
                                    <p>No NIN document uploaded</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Passport Photograph -->
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-portrait"></i> Passport Photo
                            </h5>
                        </div>
                        <div class="card-body text-center">
                            @if($driver->passport_photograph)
                                <img src="{{ Storage::url($driver->passport_photograph) }}" 
                                     alt="Passport Photo" 
                                     class="img-fluid rounded mb-3" 
                                     style="max-height: 200px;">
                                <div class="document-actions">
                                    <button class="btn btn-success btn-sm" onclick="approveDocument('passport_photo', '{{ $driver->id }}')">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                    <button class="btn btn-danger btn-sm" onclick="rejectDocument('passport_photo', '{{ $driver->id }}')">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                    <a href="{{ Storage::url($driver->passport_photograph) }}" target="_blank" class="btn btn-primary btn-sm">
                                        <i class="fas fa-external-link-alt"></i> View Full
                                    </a>
                                </div>
                            @else
                                <div class="text-muted">
                                    <i class="fas fa-portrait fa-3x mb-3"></i>
                                    <p>No passport photo uploaded</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Additional Documents -->
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card">
                        <div class="card-header bg-dark text-white">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-folder"></i> Additional Documents
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($driver->additional_documents)
                                @php
                                    $additionalDocs = is_string($driver->additional_documents) 
                                        ? json_decode($driver->additional_documents, true) 
                                        : $driver->additional_documents;
                                @endphp
                                
                                @if(is_array($additionalDocs) && !empty($additionalDocs))
                                    <div class="list-group">
                                        @foreach($additionalDocs as $index => $doc)
                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong>Document {{ $index + 1 }}</strong>
                                                    <br><small class="text-muted">{{ basename($doc) }}</small>
                                                </div>
                                                <div>
                                                    <a href="{{ Storage::url($doc) }}" target="_blank" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <button class="btn btn-sm btn-success" onclick="approveDocument('additional_{{ $index }}', '{{ $driver->id }}')">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger" onclick="rejectDocument('additional_{{ $index }}', '{{ $driver->id }}')">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-muted text-center">
                                        <i class="fas fa-folder fa-3x mb-3"></i>
                                        <p>No additional documents uploaded</p>
                                    </div>
                                @endif
                            @else
                                <div class="text-muted text-center">
                                    <i class="fas fa-folder fa-3x mb-3"></i>
                                    <p>No additional documents uploaded</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- OCR Verification Summary -->
            @if($driver->nin_verification_data || $driver->frsc_verification_data)
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-primary text-white">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-robot"></i> OCR Verification Summary
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-md-4">
                                    <h6>Overall Status</h6>
                                    <span class="badge badge-lg badge-{{ $driver->ocr_verification_status === 'passed' ? 'success' : ($driver->ocr_verification_status === 'failed' ? 'danger' : 'warning') }}">
                                        {{ strtoupper($driver->ocr_verification_status) }}
                                    </span>
                                </div>
                                @if($driver->nin_verification_data)
                                <div class="col-md-4">
                                    <h6>NIN Verification</h6>
                                    <span class="badge badge-{{ $driver->nin_ocr_match_score >= 80 ? 'success' : 'danger' }}">
                                        Score: {{ $driver->nin_ocr_match_score }}%
                                    </span>
                                    <br><small class="text-muted">{{ $driver->nin_verified_at ? $driver->nin_verified_at->format('M d, Y H:i') : 'Not processed' }}</small>
                                </div>
                                @endif
                                @if($driver->frsc_verification_data)
                                <div class="col-md-4">
                                    <h6>FRSC Verification</h6>
                                    <span class="badge badge-{{ $driver->frsc_ocr_match_score >= 80 ? 'success' : 'danger' }}">
                                        Score: {{ $driver->frsc_ocr_match_score }}%
                                    </span>
                                    <br><small class="text-muted">{{ $driver->frsc_verified_at ? $driver->frsc_verified_at->format('M d, Y H:i') : 'Not processed' }}</small>
                                </div>
                                @endif
                            </div>
                            
                            @if($driver->ocr_verification_notes)
                            <div class="mt-3">
                                <h6>Verification Notes:</h6>
                                <div class="alert alert-info">
                                    {{ $driver->ocr_verification_notes }}
                                </div>
                            </div>
                            @endif
                            
                            <div class="text-center mt-3">
                                <button class="btn btn-primary" onclick="viewOCRDetails('{{ $driver->id }}', '{{ $driver->full_name }}')">
                                    <i class="fas fa-eye"></i> View Detailed OCR Results
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </section>
</div>

<!-- Document Action Modal -->
<div class="modal fade" id="documentActionModal" tabindex="-1" aria-labelledby="documentActionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="documentActionModalLabel">Document Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="documentActionForm" method="POST">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="documentType" name="document_type">
                    <input type="hidden" id="actionType" name="action_type">
                    
                    <div class="alert alert-info">
                        <span id="actionMessage"></span>
                    </div>
                    
                    <div class="mb-3">
                        <label for="actionNotes" class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" id="actionNotes" name="notes" rows="3" 
                                  placeholder="Add notes about this action..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn" id="confirmActionBtn">Confirm</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// File Upload System
const driverId = {{ $driver->id }};
const uploadZone = document.getElementById('uploadZone');
const fileInput = document.getElementById('fileInput');
const uploadProgress = document.getElementById('uploadProgress');
const progressBar = document.getElementById('progressBar');
const progressText = document.getElementById('progressText');

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    loadDocuments();
    setupDragAndDrop();
    setupFileInput();
});

// Setup drag and drop
function setupDragAndDrop() {
    uploadZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        uploadZone.classList.add('dragover');
    });
    
    uploadZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        uploadZone.classList.remove('dragover');
    });
    
    uploadZone.addEventListener('drop', function(e) {
        e.preventDefault();
        uploadZone.classList.remove('dragover');
        const files = Array.from(e.dataTransfer.files);
        handleFileSelection(files);
    });
}

// Setup file input
function setupFileInput() {
    fileInput.addEventListener('change', function() {
        const files = Array.from(this.files);
        handleFileSelection(files);
    });
}

// Handle file selection
function handleFileSelection(files) {
    if (files.length === 0) return;
    
    // Validate files
    for (let file of files) {
        if (!validateFile(file)) {
            return;
        }
    }
    
    if (files.length === 1) {
        uploadSingleFile(files[0]);
    } else {
        uploadMultipleFiles(files);
    }
}

// Validate file
function validateFile(file) {
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
    const maxSize = 5 * 1024 * 1024; // 5MB
    
    if (!allowedTypes.includes(file.type)) {
        showAlert('error', `File "${file.name}" has invalid type. Only JPG, PNG, and PDF are allowed.`);
        return false;
    }
    
    if (file.size > maxSize) {
        showAlert('error', `File "${file.name}" is too large. Maximum size is 5MB.`);
        return false;
    }
    
    return true;
}

// Upload single file
function uploadSingleFile(file) {
    // Show document type selection modal
    showDocumentTypeModal(file);
}

// Upload multiple files
function uploadMultipleFiles(files) {
    // Show bulk upload modal
    showBulkUploadModal(files);
}

// Show document type selection modal
function showDocumentTypeModal(file) {
    const modalHtml = `
        <div class="modal fade" id="documentTypeModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Select Document Type</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>What type of document is "<strong>${file.name}</strong>"?</p>
                        <select class="form-control" id="selectedDocumentType">
                            <option value="">Select document type...</option>
                            <option value="profile_picture">Profile Picture</option>
                            <option value="nin">NIN Document</option>
                            <option value="license_front">Driver License (Front)</option>
                            <option value="license_back">Driver License (Back)</option>
                            <option value="passport_photo">Passport Photograph</option>
                            <option value="employment_letter">Employment Letter</option>
                            <option value="service_certificate">Service Certificate</option>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" onclick="confirmUpload('${file.name}')">Upload</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('documentTypeModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add new modal
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    const modal = new bootstrap.Modal(document.getElementById('documentTypeModal'));
    modal.show();
    
    // Store file reference
    window.currentUploadFile = file;
}

// Confirm upload
function confirmUpload(fileName) {
    const documentType = document.getElementById('selectedDocumentType').value;
    
    if (!documentType) {
        showAlert('warning', 'Please select a document type');
        return;
    }
    
    const modal = bootstrap.Modal.getInstance(document.getElementById('documentTypeModal'));
    modal.hide();
    
    uploadFile(window.currentUploadFile, documentType);
}

// Upload file to server
function uploadFile(file, documentType) {
    const formData = new FormData();
    formData.append('document', file);
    formData.append('document_type', documentType);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    
    // Show progress
    showUploadProgress();
    
    fetch(`{{ route('admin.drivers.files.upload', $driver->id) }}`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        hideUploadProgress();
        
        if (data.success) {
            showAlert('success', data.message);
            loadDocuments(); // Refresh the documents list
        } else {
            showAlert('error', data.message || 'Upload failed');
        }
    })
    .catch(error => {
        hideUploadProgress();
        showAlert('error', 'Upload failed: ' + error.message);
    });
}

// Load documents
function loadDocuments() {
    fetch(`{{ route('admin.drivers.files.list', $driver->id) }}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderDocuments(data.documents);
            document.getElementById('documentCount').textContent = data.documents.length + ' documents';
        } else {
            showAlert('error', 'Failed to load documents');
        }
    })
    .catch(error => {
        showAlert('error', 'Error loading documents: ' + error.message);
    });
}

// Render documents
function renderDocuments(documents) {
    const container = document.getElementById('documentsContainer');
    
    if (documents.length === 0) {
        container.innerHTML = `
            <div class="text-center text-muted">
                <i class="fas fa-folder-open fa-3x mb-3"></i>
                <p>No documents uploaded yet</p>
            </div>
        `;
        return;
    }
    
    let html = '';
    documents.forEach(doc => {
        const statusBadge = getStatusBadge(doc.verification_status);
        const isImage = doc.url.match(/\.(jpg|jpeg|png)$/i);
        
        html += `
            <div class="document-item">
                <div class="row align-items-center">
                    <div class="col-md-2">
                        ${isImage ? 
                            `<img src="${doc.url}" alt="${doc.type_name}" class="document-preview">` :
                            `<div class="document-preview d-flex align-items-center justify-content-center bg-light">
                                <i class="fas fa-file-pdf fa-2x text-danger"></i>
                            </div>`
                        }
                    </div>
                    <div class="col-md-6">
                        <h6 class="mb-1">${doc.type_name}</h6>
                        <p class="text-muted mb-1">${doc.number || 'No number provided'}</p>
                        <small class="text-muted">Uploaded: ${doc.created_at}</small>
                    </div>
                    <div class="col-md-2">
                        ${statusBadge}
                        ${doc.ocr_match_score > 0 ? 
                            `<br><small class="text-muted">OCR: ${doc.ocr_match_score}%</small>` : ''
                        }
                    </div>
                    <div class="col-md-2">
                        <div class="btn-group-vertical btn-group-sm">
                            <a href="${doc.url}" target="_blank" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-eye"></i> View
                            </a>
                            <button class="btn btn-outline-danger btn-sm" onclick="deleteDocument(${doc.id})">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

// Get status badge
function getStatusBadge(status) {
    const badges = {
        'pending': '<span class="status-badge bg-warning text-dark">Pending</span>',
        'approved': '<span class="status-badge bg-success text-white">Approved</span>',
        'rejected': '<span class="status-badge bg-danger text-white">Rejected</span>'
    };
    
    return badges[status] || '<span class="status-badge bg-secondary text-white">Unknown</span>';
}

// Delete document
function deleteDocument(documentId) {
    if (!confirm('Are you sure you want to delete this document?')) {
        return;
    }
    
    fetch(`{{ route('admin.drivers.files.delete', [$driver->id, '__DOC_ID__']) }}`.replace('__DOC_ID__', documentId), {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', data.message);
            loadDocuments();
        } else {
            showAlert('error', data.message);
        }
    })
    .catch(error => {
        showAlert('error', 'Delete failed: ' + error.message);
    });
}

// Upload specific document type
function uploadSpecificDocument(documentType) {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = '.jpg,.jpeg,.png,.pdf';
    input.onchange = function() {
        if (this.files.length > 0) {
            uploadFile(this.files[0], documentType);
        }
    };
    input.click();
}

// Show/hide upload progress
function showUploadProgress() {
    uploadProgress.style.display = 'block';
    progressBar.style.width = '0%';
    progressText.textContent = 'Uploading...';
}

function hideUploadProgress() {
    uploadProgress.style.display = 'none';
}

// Show alert
function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 
                      type === 'warning' ? 'alert-warning' : 'alert-danger';
    
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Remove existing alerts
    document.querySelectorAll('.alert').forEach(alert => {
        if (alert.classList.contains('alert-dismissible')) {
            alert.remove();
        }
    });
    
    // Add new alert at top of container
    document.querySelector('.container-fluid').insertAdjacentHTML('afterbegin', alertHtml);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        document.querySelector('.alert-dismissible')?.remove();
    }, 5000);
}
</script>
<script>
function approveDocument(documentType, driverId) {
    showDocumentActionModal('approve', documentType, driverId);
}

function rejectDocument(documentType, driverId) {
    showDocumentActionModal('reject', documentType, driverId);
}

function showDocumentActionModal(action, documentType, driverId) {
    const modal = new bootstrap.Modal(document.getElementById('documentActionModal'));
    const form = document.getElementById('documentActionForm');
    
    // Set form action
    form.action = action === 'approve' 
        ? `{{ url('admin/drivers') }}/${driverId}/documents/approve`
        : `{{ url('admin/drivers') }}/${driverId}/documents/reject`;
    
    // Set hidden fields
    document.getElementById('documentType').value = documentType;
    document.getElementById('actionType').value = action;
    
    // Set modal content
    document.getElementById('documentActionModalLabel').textContent = 
        action === 'approve' ? 'Approve Document' : 'Reject Document';
    
    document.getElementById('actionMessage').textContent = 
        `Are you sure you want to ${action} the ${documentType.replace('_', ' ')} document?`;
    
    const confirmBtn = document.getElementById('confirmActionBtn');
    confirmBtn.textContent = action === 'approve' ? 'Approve' : 'Reject';
    confirmBtn.className = action === 'approve' ? 'btn btn-success' : 'btn btn-danger';
    
    // Clear form
    document.getElementById('actionNotes').value = '';
    
    modal.show();
}

function initiateOCRVerification(driverId, documentType) {
    if (confirm(`Start OCR verification for ${documentType.toUpperCase()} document?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `{{ url('admin/drivers') }}/${driverId}/ocr-verify`;
        
        const tokenInput = document.createElement('input');
        tokenInput.type = 'hidden';
        tokenInput.name = '_token';
        tokenInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        form.appendChild(tokenInput);
        
        const typeInput = document.createElement('input');
        typeInput.type = 'hidden';
        typeInput.name = 'document_type';
        typeInput.value = documentType;
        form.appendChild(typeInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}

function viewOCRDetails(driverId, driverName) {
    // Redirect to verification page with OCR details
    window.location.href = `{{ url('admin/drivers/verification') }}?driver=${driverId}&show_ocr=true`;
}

// Auto-reload images on error
document.querySelectorAll('img').forEach(img => {
    img.addEventListener('error', function() {
        this.style.display = 'none';
        this.nextElementSibling.innerHTML = `
            <div class="text-muted">
                <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                <p>Unable to load image</p>
            </div>
        `;
    });
});
</script>
@endsection