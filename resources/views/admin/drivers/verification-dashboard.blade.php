@extends('layouts.admin_cdn')

@section('title', 'Driver Verification Dashboard')

@section('content_header')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center">
        <h1>
            <i class="fas fa-user-check"></i> Driver Verification Dashboard
        </h1>
        <div class="header-stats">
            <span class="badge bg-warning text-dark fs-6 me-2">{{ $pendingCount ?? 0 }} Pending</span>
            <span class="badge bg-success fs-6 me-2">{{ $verifiedToday ?? 0 }} Verified Today</span>
            <span class="badge bg-info fs-6">{{ $avgProcessingTime ?? '0' }}min Avg Time</span>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">
    
    <!-- Quick Actions Bar -->
    <div class="card bg-light mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="btn-toolbar" role="toolbar">
                        <div class="btn-group me-3" role="group">
                            <button type="button" class="btn btn-success" onclick="verifyAllVisible()">
                                <i class="fas fa-check-double"></i> Verify All Visible
                            </button>
                            <button type="button" class="btn btn-warning" onclick="flagForReview()">
                                <i class="fas fa-flag"></i> Flag for Review
                            </button>
                        </div>
                        
                        <div class="btn-group me-3" role="group">
                            <button type="button" class="btn btn-outline-primary" onclick="expandAllDrivers()">
                                <i class="fas fa-expand-arrows-alt"></i> Expand All
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="collapseAllDrivers()">
                                <i class="fas fa-compress-arrows-alt"></i> Collapse All
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Search drivers..." id="quickSearch">
                        <button class="btn btn-outline-secondary" type="button">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Verification Queue -->
    <div class="row">
        @forelse($pendingDrivers as $driver)
        <div class="col-12 mb-4">
            <div class="card verification-card" data-driver-id="{{ $driver->id }}">
                <div class="card-header bg-warning text-dark">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <h5 class="mb-0 me-3">
                                <i class="fas fa-user-clock"></i> 
                                {{ $driver->full_name ?? trim($driver->first_name . ' ' . $driver->surname) }}
                            </h5>
                            <span class="badge bg-secondary">{{ $driver->driver_id ?? 'DRV-' . str_pad($driver->id, 4, '0', STR_PAD_LEFT) }}</span>
                            @if($driver->created_at && $driver->created_at->diffInHours(now()) < 24)
                                <span class="badge bg-danger ms-2">New Registration</span>
                            @endif
                        </div>
                        
                        <div class="verification-timer">
                            <small class="text-muted">
                                <i class="fas fa-clock"></i> Waiting {{ $driver->created_at ? $driver->created_at->diffForHumans() : 'N/A' }}
                            </small>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <!-- Quick Info Column -->
                        <div class="col-md-3">
                            <div class="verification-checklist">
                                <h6 class="text-muted mb-3">Verification Checklist</h6>
                                
                                <div class="checklist-item mb-2">
                                    <input type="checkbox" id="phone-{{ $driver->id }}" {{ $driver->phone ? 'checked' : '' }}>
                                    <label for="phone-{{ $driver->id }}" class="ms-2">
                                        <i class="fas fa-phone text-success"></i> Phone Verified
                                        <small class="d-block text-muted">{{ $driver->phone ?? 'N/A' }}</small>
                                    </label>
                                </div>
                                
                                <div class="checklist-item mb-2">
                                    <input type="checkbox" id="email-{{ $driver->id }}" {{ $driver->email ? 'checked' : '' }}>
                                    <label for="email-{{ $driver->id }}" class="ms-2">
                                        <i class="fas fa-envelope text-info"></i> Email Verified
                                        <small class="d-block text-muted">{{ $driver->email ?? 'N/A' }}</small>
                                    </label>
                                </div>
                                
                                <div class="checklist-item mb-2">
                                    <input type="checkbox" id="license-{{ $driver->id }}" {{ $driver->license_number ? 'checked' : '' }}>
                                    <label for="license-{{ $driver->id }}" class="ms-2">
                                        <i class="fas fa-id-card text-primary"></i> License Valid
                                        <small class="d-block text-muted">{{ $driver->license_number ?? 'N/A' }}</small>
                                    </label>
                                </div>
                                
                                <div class="checklist-item mb-2">
                                    <input type="checkbox" id="nin-{{ $driver->id }}" {{ $driver->nin_number ? 'checked' : '' }}>
                                    <label for="nin-{{ $driver->id }}" class="ms-2">
                                        <i class="fas fa-fingerprint text-warning"></i> NIN Verified
                                        <small class="d-block text-muted">{{ $driver->nin_number ? substr($driver->nin_number, 0, 4) . '****' : 'N/A' }}</small>
                                    </label>
                                </div>
                                
                                <div class="checklist-item">
                                    <input type="checkbox" id="docs-{{ $driver->id }}" {{ $driver->documents && $driver->documents->count() > 0 ? 'checked' : '' }}>
                                    <label for="docs-{{ $driver->id }}" class="ms-2">
                                        <i class="fas fa-file-alt text-secondary"></i> Documents Complete
                                        <small class="d-block text-muted">{{ $driver->documents ? $driver->documents->count() : 0 }} documents</small>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Overall Completion Score -->
                            <div class="mt-3">
                                @php
                                    $completionScore = 0;
                                    if($driver->phone) $completionScore += 20;
                                    if($driver->email) $completionScore += 20;
                                    if($driver->license_number) $completionScore += 20;
                                    if($driver->nin_number) $completionScore += 20;
                                    if($driver->documents && $driver->documents->count() > 0) $completionScore += 20;
                                @endphp
                                
                                <div class="completion-score">
                                    <h6 class="text-muted">Completion Score</h6>
                                    <div class="progress mb-2" style="height: 10px;">
                                        <div class="progress-bar 
                                             @if($completionScore >= 80) bg-success
                                             @elseif($completionScore >= 60) bg-warning
                                             @else bg-danger @endif" 
                                             style="width: {{ $completionScore }}%"></div>
                                    </div>
                                    <small class="text-muted">{{ $completionScore }}% Complete</small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Document Preview Column -->
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">Document Preview</h6>
                            
                            <div class="document-preview-container">
                                <div class="row g-2">
                                    @if($driver->documents && $driver->documents->count() > 0)
                                        @foreach($driver->documents->take(4) as $document)
                                        <div class="col-6">
                                            <div class="document-preview-card">
                                                <div class="document-thumbnail">
                                                    @if(in_array(pathinfo($document->document_path ?? '', PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif']))
                                                        <img src="{{ asset($document->document_path) }}" 
                                                             class="img-thumbnail" 
                                                             style="width: 100%; height: 120px; object-fit: cover;"
                                                             onclick="previewDocument('{{ asset($document->document_path) }}', '{{ $document->document_type }}')">
                                                    @else
                                                        <div class="document-placeholder d-flex align-items-center justify-content-center" 
                                                             style="height: 120px; background: #f8f9fa; border: 1px dashed #dee2e6;">
                                                            <i class="fas fa-file-alt fa-2x text-muted"></i>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="document-info p-2">
                                                    <small class="fw-bold">{{ ucwords(str_replace('_', ' ', $document->document_type ?? 'Document')) }}</small>
                                                    <br>
                                                    <small class="text-muted">{{ $document->created_at ? $document->created_at->format('M d') : 'N/A' }}</small>
                                                </div>
                                            </div>
                                        </div>
                                        @endforeach
                                        
                                        @if($driver->documents->count() > 4)
                                        <div class="col-12">
                                            <small class="text-muted">
                                                <i class="fas fa-plus-circle"></i> +{{ $driver->documents->count() - 4 }} more documents
                                                <a href="{{ route('admin.drivers.documents', $driver->id) }}" class="ms-2">View All</a>
                                            </small>
                                        </div>
                                        @endif
                                    @else
                                        <div class="col-12">
                                            <div class="alert alert-warning">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                No documents uploaded yet.
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <!-- Actions Column -->
                        <div class="col-md-3">
                            <h6 class="text-muted mb-3">Verification Actions</h6>
                            
                            <!-- Primary Actions -->
                            <div class="verification-actions mb-3">
                                <form method="POST" action="{{ route('admin.drivers.verify', $driver->id) }}" class="mb-2">
                                    @csrf
                                    <input type="hidden" name="admin_password" value="">
                                    <input type="hidden" name="verification_notes" value="">
                                    <button type="submit" class="btn btn-success btn-lg w-100"
                                            onclick="return confirmVerification(this, '{{ $driver->full_name ?? $driver->first_name }}')">
                                        <i class="fas fa-check-circle"></i> VERIFY DRIVER
                                    </button>
                                </form>
                                
                                <button type="button" class="btn btn-danger btn-lg w-100 mb-2"
                                        onclick="showRejectModal({{ $driver->id }}, '{{ $driver->full_name ?? $driver->first_name }}')">
                                    <i class="fas fa-times-circle"></i> REJECT
                                </button>
                                
                                <button type="button" class="btn btn-warning w-100 mb-2"
                                        onclick="flagForReviewSingle({{ $driver->id }})">
                                    <i class="fas fa-flag"></i> Flag for Review
                                </button>
                            </div>
                            
                            <!-- Quick Actions -->
                            <div class="quick-actions">
                                <h6 class="text-muted mb-2">Quick Actions</h6>
                                <div class="btn-group-vertical w-100">
                                    <a href="{{ route('admin.drivers.show', $driver->id) }}" 
                                       class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-eye"></i> View Full Profile
                                    </a>
                                    <a href="{{ route('admin.drivers.documents', $driver->id) }}" 
                                       class="btn btn-outline-info btn-sm">
                                        <i class="fas fa-file-alt"></i> View Documents
                                    </a>
                                    <button type="button" class="btn btn-outline-secondary btn-sm"
                                            onclick="contactDriver({{ $driver->id }}, '{{ $driver->phone }}')">
                                        <i class="fas fa-phone"></i> Contact Driver
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Verification Notes -->
                            <div class="verification-notes mt-3">
                                <label for="notes-{{ $driver->id }}" class="form-label">
                                    <small class="text-muted">Verification Notes</small>
                                </label>
                                <textarea class="form-control form-control-sm" 
                                          id="notes-{{ $driver->id }}" 
                                          rows="3" 
                                          placeholder="Add notes about this verification..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="text-center py-5">
                <i class="fas fa-check-circle fa-4x text-success mb-4"></i>
                <h4>All Caught Up!</h4>
                <p class="text-muted">No drivers pending verification at the moment.</p>
                <a href="{{ route('admin.drivers.index') }}" class="btn btn-primary">
                    <i class="fas fa-users"></i> View All Drivers
                </a>
            </div>
        </div>
        @endforelse
    </div>
    
    <!-- Verification Statistics -->
    @if(isset($pendingDrivers) && $pendingDrivers->count() > 0)
    <div class="row mt-4">
        <div class="col-12">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <h4>{{ $pendingCount ?? 0 }}</h4>
                            <small>Pending Today</small>
                        </div>
                        <div class="col-md-3">
                            <h4>{{ $verifiedToday ?? 0 }}</h4>
                            <small>Verified Today</small>
                        </div>
                        <div class="col-md-3">
                            <h4>{{ $avgProcessingTime ?? '0' }}min</h4>
                            <small>Avg Processing Time</small>
                        </div>
                        <div class="col-md-3">
                            <h4>{{ number_format((($verifiedToday ?? 0) / max(($verifiedToday ?? 0) + ($pendingCount ?? 0), 1)) * 100, 1) }}%</h4>
                            <small>Today's Success Rate</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Admin Password Modal -->
<div class="modal fade" id="adminPasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Admin Verification Required</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="adminPasswordForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Driver to Verify</label>
                        <input type="text" class="form-control" id="verifyDriverName" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label for="admin_password" class="form-label">Admin Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="admin_password" required 
                               placeholder="Enter your admin password to confirm verification">
                    </div>
                    
                    <div class="mb-3">
                        <label for="verification_notes" class="form-label">Verification Notes</label>
                        <textarea class="form-control" id="verification_notes" rows="3" 
                                  placeholder="Optional notes about this verification..."></textarea>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check-circle"></i> Verify Driver
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Reject Driver Verification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="rejectForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Driver Name</label>
                        <input type="text" class="form-control" id="rejectDriverName" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label for="admin_password_reject" class="form-label">Admin Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" name="admin_password" id="admin_password_reject" required 
                               placeholder="Enter your admin password to confirm rejection">
                    </div>
                    
                    <div class="mb-3">
                        <label for="rejection_reason" class="form-label">Reason for Rejection <span class="text-danger">*</span></label>
                        <select class="form-control" name="rejection_reason" id="rejection_reason" required>
                            <option value="">Select Reason...</option>
                            <option value="incomplete_documents">Incomplete Documents</option>
                            <option value="invalid_license">Invalid License</option>
                            <option value="failed_nin_verification">Failed NIN Verification</option>
                            <option value="suspicious_information">Suspicious Information</option>
                            <option value="poor_document_quality">Poor Document Quality</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="verification_notes_reject" class="form-label">Additional Notes</label>
                        <textarea class="form-control" name="verification_notes" id="verification_notes_reject" 
                                  rows="4" placeholder="Provide specific details about why this driver was rejected..."></textarea>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times-circle"></i> Reject Driver
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Document Preview Modal -->
<div class="modal fade" id="documentPreviewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="documentPreviewTitle">Document Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="documentPreviewImage" class="img-fluid" style="max-height: 70vh;">
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
// Verification Dashboard JavaScript
$(document).ready(function() {
    // Initialize verification dashboard
    initializeVerificationDashboard();
});

let currentVerificationForm = null;

function initializeVerificationDashboard() {
    // Add keyboard shortcuts
    $(document).on('keydown', function(e) {
        if (e.target.tagName.toLowerCase() !== 'input' && e.target.tagName.toLowerCase() !== 'textarea') {
            switch(e.key.toLowerCase()) {
                case 'v': // Verify first driver
                    $('.btn-success:first').click();
                    break;
                case 'r': // Reject first driver
                    $('.btn-danger:first').click();
                    break;
                case 'f': // Flag for review
                    $('.btn-warning:first').click();
                    break;
            }
        }
    });
    
    // Auto-save notes
    $('textarea[id^="notes-"]').on('input', debounce(function() {
        const driverId = this.id.split('-')[1];
        const notes = $(this).val();
        saveVerificationNotes(driverId, notes);
    }, 1000));
}

function confirmVerification(button, driverName) {
    currentVerificationForm = $(button).closest('form');
    $('#verifyDriverName').val(driverName);
    $('#adminPasswordModal').modal('show');
    return false; // Prevent form submission
}

function showRejectModal(driverId, driverName) {
    $('#rejectDriverName').val(driverName);
    $('#rejectForm').attr('action', `/admin/drivers/${driverId}/reject`);
    $('#rejectModal').modal('show');
}

function previewDocument(imageSrc, documentType) {
    $('#documentPreviewTitle').text(ucwords(documentType.replace('_', ' ')) + ' Preview');
    $('#documentPreviewImage').attr('src', imageSrc);
    $('#documentPreviewModal').modal('show');
}

function verifyAllVisible() {
    if (confirm('Are you sure you want to verify all visible drivers? This action cannot be undone.')) {
        const verifyButtons = $('.btn-success[type="submit"]');
        let completed = 0;
        
        verifyButtons.each(function(index) {
            const button = $(this);
            setTimeout(() => {
                button.click();
                completed++;
                if (completed === verifyButtons.length) {
                    location.reload();
                }
            }, index * 500); // Stagger the requests
        });
    }
}

function flagForReview() {
    const selectedDrivers = $('.verification-card').map(function() {
        return $(this).data('driver-id');
    }).get();
    
    if (selectedDrivers.length > 0) {
        $.post('/admin/drivers/bulk-flag-review', {
            driver_ids: selectedDrivers,
            _token: $('meta[name="csrf-token"]').attr('content')
        }).done(function(response) {
            toastr.success(`${selectedDrivers.length} drivers flagged for review`);
            location.reload();
        });
    }
}

function flagForReviewSingle(driverId) {
    $.post('/admin/drivers/flag-review', {
        driver_id: driverId,
        _token: $('meta[name="csrf-token"]').attr('content')
    }).done(function(response) {
        toastr.success('Driver flagged for review');
        $(`[data-driver-id="${driverId}"]`).fadeOut();
    });
}

function contactDriver(driverId, phone) {
    if (phone && phone !== 'N/A') {
        if (confirm(`Call ${phone}?`)) {
            window.open(`tel:${phone}`);
        }
    } else {
        toastr.warning('No phone number available for this driver');
    }
}

function saveVerificationNotes(driverId, notes) {
    $.post('/admin/drivers/save-verification-notes', {
        driver_id: driverId,
        notes: notes,
        _token: $('meta[name="csrf-token"]').attr('content')
    }).done(function(response) {
        // Show subtle success indicator
        $(`#notes-${driverId}`).addClass('border-success');
        setTimeout(() => {
            $(`#notes-${driverId}`).removeClass('border-success');
        }, 2000);
    });
}

function expandAllDrivers() {
    $('.verification-card .collapse').collapse('show');
}

function collapseAllDrivers() {
    $('.verification-card .collapse').collapse('hide');
}

// Handle admin password form submission
$('#adminPasswordForm').on('submit', function(e) {
    e.preventDefault();
    
    if (currentVerificationForm) {
        const password = $('#admin_password').val();
        const notes = $('#verification_notes').val();
        
        // Update the hidden form fields
        currentVerificationForm.find('input[name="admin_password"]').val(password);
        currentVerificationForm.find('input[name="verification_notes"]').val(notes);
        
        $('#adminPasswordModal').modal('hide');
        
        // Submit the form
        currentVerificationForm.submit();
    }
});

// Handle reject form submission
$('#rejectForm').on('submit', function(e) {
    e.preventDefault();
    
    const form = $(this);
    const submitBtn = form.find('button[type="submit"]');
    const originalText = submitBtn.html();
    
    submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Rejecting...');
    
    $.post(form.attr('action'), form.serialize())
        .done(function(response) {
            toastr.success('Driver rejected successfully');
            $('#rejectModal').modal('hide');
            location.reload();
        })
        .fail(function(xhr) {
            toastr.error(xhr.responseJSON?.message || 'Error rejecting driver');
            submitBtn.prop('disabled', false).html(originalText);
        });
});

// Utility functions
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function ucwords(str) {
    return str.replace(/\w\S*/g, (txt) => 
        txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase()
    );
}

// Real-time search
$('#quickSearch').on('input', function() {
    const query = $(this).val().toLowerCase();
    $('.verification-card').each(function() {
        const text = $(this).text().toLowerCase();
        $(this).toggle(text.includes(query));
    });
});

// Clear modal forms when closed
$('#adminPasswordModal, #rejectModal').on('hidden.bs.modal', function() {
    $(this).find('form')[0].reset();
    currentVerificationForm = null;
});
</script>
@endsection