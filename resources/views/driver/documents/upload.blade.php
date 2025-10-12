@extends('layouts.driver')

@section('title', 'Document Upload')
@section('page-title', 'Upload Documents')
@section('page-description', 'Upload your documents for verification')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-cloud-upload-alt me-2"></i>
                        Document Upload
                    </h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Upload your documents for admin verification. Supported formats: JPG, PNG, PDF. Max size: 10MB per file.
                    </div>

                    <!-- Upload Form -->
                    <form id="uploadForm" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="document_type" class="form-label">Document Type</label>
                                <select class="form-control" id="document_type" name="document_type" required>
                                    <option value="">Select document type</option>
                                    <option value="nin">National ID (NIN)</option>
                                    <option value="license_front">Driver License (Front)</option>
                                    <option value="license_back">Driver License (Back)</option>
                                    <option value="profile_picture">Profile Picture</option>
                                    <option value="passport_photo">Passport Photograph</option>
                                    <option value="employment_letter">Employment Letter</option>
                                    <option value="service_certificate">Service Certificate</option>
                                    <option value="vehicle_papers">Vehicle Papers</option>
                                    <option value="insurance">Insurance Document</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="document_file" class="form-label">Document File</label>
                                <input type="file" class="form-control" id="document_file" name="document_file"
                                       accept=".jpg,.jpeg,.png,.pdf" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="description" class="form-label">Description (Optional)</label>
                                <textarea class="form-control" id="description" name="description" rows="3"
                                          placeholder="Add any additional information about this document"></textarea>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary" id="uploadBtn">
                            <i class="fas fa-upload me-2"></i>
                            Upload Document
                        </button>
                    </form>

                    <!-- Uploaded Documents List -->
                    <div class="mt-5">
                        <h5>Your Uploaded Documents</h5>
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
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    loadDocuments();

    $('#uploadForm').on('submit', function(e) {
        e.preventDefault();

        // Client-side validation
        if (!$('#document_file').val()) {
            toastr.error('Please select a document file to upload.');
            $('#document_file').focus();
            return;
        }

        if (!$('#document_type').val()) {
            toastr.error('Please select the document type.');
            $('#document_type').focus();
            return;
        }

        const formData = new FormData(this);
        const uploadBtn = $('#uploadBtn');

        uploadBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Uploading...');

        $.ajax({
            url: '/api/driver/documents/upload',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('driver_token')
            },
            success: function(response) {
                if (response.success) {
                    toastr.success('Document uploaded successfully!');
                    $('#uploadForm')[0].reset();
                    loadDocuments();
                } else {
                    toastr.error(response.message || 'Upload failed');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                if (response && response.errors) {
                    // Display specific validation errors
                    for (let field in response.errors) {
                        toastr.error(response.errors[field][0]);
                    }
                } else {
                    toastr.error(response?.message || 'Upload failed');
                }
            },
            complete: function() {
                uploadBtn.prop('disabled', false).html('<i class="fas fa-upload me-2"></i>Upload Document');
            }
        });
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
                }
            },
            error: function() {
                $('#documentsList').html('<div class="alert alert-danger">Failed to load documents</div>');
            }
        });
    }

    function displayDocuments(documents) {
        if (documents.length === 0) {
            $('#documentsList').html('<div class="alert alert-info">No documents uploaded yet</div>');
            return;
        }

        let html = '<div class="table-responsive"><table class="table table-striped"><thead><tr><th>Type</th><th>Status</th><th>Uploaded</th><th>Actions</th></tr></thead><tbody>';

        documents.forEach(function(doc) {
            const statusBadge = getStatusBadge(doc.verification_status);
            const uploadedDate = new Date(doc.created_at).toLocaleDateString();

            html += `<tr>
                <td>${doc.document_type_name || doc.document_type}</td>
                <td>${statusBadge}</td>
                <td>${uploadedDate}</td>
                <td>
                    <button class="btn btn-sm btn-info me-2" onclick="downloadDocument(${doc.id})">
                        <i class="fas fa-download"></i>
                    </button>
                    ${doc.verification_status === 'pending' ? `<button class="btn btn-sm btn-danger" onclick="deleteDocument(${doc.id})"><i class="fas fa-trash"></i></button>` : ''}
                </td>
            </tr>`;
        });

        html += '</tbody></table></div>';
        $('#documentsList').html(html);
    }

    function getStatusBadge(status) {
        const badges = {
            'pending': '<span class="badge bg-warning">Pending Review</span>',
            'approved': '<span class="badge bg-success">Approved</span>',
            'rejected': '<span class="badge bg-danger">Rejected</span>'
        };
        return badges[status] || '<span class="badge bg-secondary">Unknown</span>';
    }

    window.downloadDocument = function(id) {
        window.open(`/api/driver/documents/${id}/download`, '_blank');
    };

    window.deleteDocument = function(id) {
        if (confirm('Are you sure you want to delete this document?')) {
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
                    }
                },
                error: function() {
                    toastr.error('Failed to delete document');
                }
            });
        }
    };
});
</script>
@endsection
