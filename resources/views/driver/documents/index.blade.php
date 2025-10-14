@extends('layouts.driver')

@section('title', 'My Documents')
@section('page-title', 'Document Management')
@section('page-description', 'View and manage your uploaded documents')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-file-alt me-2"></i>
                        My Documents
                    </h4>
                    <a href="{{ route('driver.documents.upload') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        Upload New Document
                    </a>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Manage your documents for verification. Upload required documents like driver's license, NIN, and other identification.
                    </div>

                    <!-- Documents List -->
                    <div id="documentsList">
                        <div class="text-center">
                            <div class="spinner-border" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div>
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
    loadDocuments();

    // Refresh documents every 30 seconds
    setInterval(loadDocuments, 30000);
});

function loadDocuments() {
    $.ajax({
        url: '/api/driver/documents',
        type: 'GET',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('driver_token')
        },
        success: function(response) {
            if (response.success) {
                displayDocuments(response.documents);
            } else {
                $('#documentsList').html('<div class="alert alert-danger">Failed to load documents</div>');
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON;
            if (response && response.message) {
                $('#documentsList').html('<div class="alert alert-danger">' + response.message + '</div>');
            } else {
                $('#documentsList').html('<div class="alert alert-danger">Failed to load documents</div>');
            }
        }
    });
}

function displayDocuments(documents) {
    if (documents.length === 0) {
        $('#documentsList').html(`
            <div class="text-center py-5">
                <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No documents uploaded yet</h5>
                <p class="text-muted mb-4">Upload your documents for verification to get started.</p>
                <a href="{{ route('driver.documents.upload') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>
                    Upload Your First Document
                </a>
            </div>
        `);
        return;
    }

    let html = '<div class="table-responsive"><table class="table table-striped"><thead><tr><th>Document Type</th><th>Status</th><th>Uploaded Date</th><th>Actions</th></tr></thead><tbody>';

    documents.forEach(function(doc) {
        const statusBadge = getStatusBadge(doc.verification_status);
        const uploadedDate = new Date(doc.created_at).toLocaleDateString();
        const documentTypeName = getDocumentTypeName(doc.document_type);

        html += `<tr>
            <td>
                <div class="d-flex align-items-center">
                    <i class="fas fa-file-alt me-2 text-primary"></i>
                    <span>${documentTypeName}</span>
                </div>
            </td>
            <td>${statusBadge}</td>
            <td>${uploadedDate}</td>
            <td>
                <div class="btn-group" role="group">
                    <button class="btn btn-sm btn-info me-1" onclick="downloadDocument(${doc.id})" title="Download">
                        <i class="fas fa-download"></i>
                    </button>
                    ${doc.verification_status === 'pending' ? `<button class="btn btn-sm btn-danger" onclick="deleteDocument(${doc.id})" title="Delete"><i class="fas fa-trash"></i></button>` : ''}
                </div>
            </td>
        </tr>`;
    });

    html += '</tbody></table></div>';
    $('#documentsList').html(html);
}

function getDocumentTypeName(type) {
    const names = {
        'nin': 'National ID (NIN)',
        'license_front': 'Driver License (Front)',
        'license_back': 'Driver License (Back)',
        'profile_picture': 'Profile Picture',
        'passport_photo': 'Passport Photograph',
        'employment_letter': 'Employment Letter',
        'service_certificate': 'Service Certificate',
        'vehicle_papers': 'Vehicle Papers',
        'insurance': 'Insurance Document',
        'other': 'Other Document'
    };
    return names[type] || type;
}

function getStatusBadge(status) {
    const badges = {
        'pending': '<span class="badge bg-warning">Pending Review</span>',
        'approved': '<span class="badge bg-success">Approved</span>',
        'rejected': '<span class="badge bg-danger">Rejected</span>'
    };
    return badges[status] || '<span class="badge bg-secondary">Unknown</span>';
}

function downloadDocument(id) {
    window.open(`/api/driver/documents/${id}/download`, '_blank');
}

function deleteDocument(id) {
    if (confirm('Are you sure you want to delete this document? This action cannot be undone.')) {
        $.ajax({
            url: `/api/driver/documents/${id}`,
            type: 'DELETE',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('driver_token')
            },
            success: function(response) {
                if (response.success) {
                    toastr.success('Document deleted successfully');
                    loadDocuments();
                } else {
                    toastr.error(response.message || 'Failed to delete document');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                toastr.error(response?.message || 'Failed to delete document');
            }
        });
    }
}
</script>
@endsection
