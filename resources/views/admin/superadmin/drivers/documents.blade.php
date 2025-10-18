@extends('layouts.admin_master')

@section('title', 'Superadmin - Driver Documents')

@section('content_header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Driver Documents</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.superadmin.dashboard') }}">Super Admin</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.superadmin.drivers.index') }}">Drivers</a></li>
                        <li class="breadcrumb-item active">Documents</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid">
        <!-- Document Statistics -->
        <div class="row mb-4">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $stats['total_documents'] ?? 0 }}</h3>
                        <p>Total Documents</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $stats['approved_documents'] ?? 0 }}</h3>
                        <p>Approved</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $stats['pending_documents'] ?? 0 }}</h3>
                        <p>Pending Review</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ $stats['rejected_documents'] ?? 0 }}</h3>
                        <p>Rejected</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Document Management Interface -->
        <div class="row">
            <div class="col-md-4">
                <!-- Driver Selection -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Select Driver</h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="driver_search">Search Driver</label>
                            <input type="text" class="form-control" id="driver_search" placeholder="Enter driver name or ID">
                        </div>
                        <div id="driver_list" class="list-group" style="max-height: 300px; overflow-y: auto;">
                            <!-- Driver list will be loaded here -->
                        </div>
                    </div>
                </div>

                <!-- Bulk Document Actions -->
                <div class="card mt-3">
                    <div class="card-header">
                        <h3 class="card-title">Bulk Actions</h3>
                    </div>
                    <div class="card-body">
                        <button type="button" class="btn btn-success btn-block mb-2" onclick="bulkApproveDocuments()">
                            <i class="fas fa-check"></i> Bulk Approve
                        </button>
                        <button type="button" class="btn btn-danger btn-block mb-2" onclick="bulkRejectDocuments()">
                            <i class="fas fa-times"></i> Bulk Reject
                        </button>
                        <button type="button" class="btn btn-info btn-block" onclick="exportDocumentReport()">
                            <i class="fas fa-download"></i> Export Report
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <!-- Document Viewer -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title" id="document_viewer_title">Document Viewer</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" onclick="refreshDocuments()">
                                <i class="fas fa-sync"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="document_viewer_content">
                            <div class="text-center text-muted">
                                <i class="fas fa-file-alt fa-3x mb-3"></i>
                                <p>Select a driver to view their documents</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Document Actions -->
                <div class="card mt-3" id="document_actions" style="display: none;">
                    <div class="card-header">
                        <h3 class="card-title">Document Actions</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <button type="button" class="btn btn-success btn-block" onclick="approveSelectedDocuments()">
                                    <i class="fas fa-check"></i> Approve Selected
                                </button>
                            </div>
                            <div class="col-md-6">
                                <button type="button" class="btn btn-danger btn-block" onclick="rejectSelectedDocuments()">
                                    <i class="fas fa-times"></i> Reject Selected
                                </button>
                            </div>
                        </div>
                        <hr>
                        <div class="form-group">
                            <label for="action_notes">Notes</label>
                            <textarea class="form-control" id="action_notes" rows="3" placeholder="Add notes for this action..."></textarea>
                        </div>
                        <div class="form-group">
                            <label for="admin_password">Admin Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="admin_password" placeholder="Enter admin password">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Document History -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Document Verification History</h3>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover text-nowrap">
                            <thead>
                                <tr>
                                    <th>Driver</th>
                                    <th>Document Type</th>
                                    <th>Action</th>
                                    <th>Admin</th>
                                    <th>Date</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody id="document_history_table">
                                <!-- History will be loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Document Preview Modal -->
    <div class="modal fade" id="documentPreviewModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="documentPreviewTitle">Document Preview</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center" id="documentPreviewContent">
                    <!-- Document preview will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <a href="#" class="btn btn-primary" id="downloadDocumentBtn" target="_blank">
                        <i class="fas fa-download"></i> Download
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
let selectedDriverId = null;
let selectedDocuments = new Set();

$(document).ready(function() {
    initializeDocumentViewer();
    loadDocumentHistory();
});

function initializeDocumentViewer() {
    // Driver search functionality
    let searchTimeout;
    $('#driver_search').on('input', function() {
        clearTimeout(searchTimeout);
        const query = $(this).val();

        if (query.length < 2) {
            $('#driver_list').html('');
            return;
        }

        searchTimeout = setTimeout(() => {
            searchDrivers(query);
        }, 300);
    });
}

function searchDrivers(query) {
    $.get('{{ route("admin.superadmin.drivers.index") }}', {
        search: query,
        format: 'json',
        per_page: 10
    })
    .done(function(response) {
        renderDriverList(response.drivers);
    })
    .fail(function() {
        $('#driver_list').html('<div class="list-group-item text-danger">Search failed</div>');
    });
}

function renderDriverList(drivers) {
    let html = '';

    if (drivers.length === 0) {
        html = '<div class="list-group-item text-muted">No drivers found</div>';
    } else {
        drivers.forEach(driver => {
            html += `
                <a href="#" class="list-group-item list-group-item-action" onclick="selectDriver(${driver.id}, '${driver.driver_id}', '${driver.first_name} ${driver.surname}')">
                    <div class="d-flex w-100 justify-content-between">
                        <h6 class="mb-1">${driver.first_name} ${driver.surname}</h6>
                        <small>${driver.driver_id}</small>
                    </div>
                    <p class="mb-1">${driver.email}</p>
                    <small class="text-muted">Status: ${driver.verification_status}</small>
                </a>
            `;
        });
    }

    $('#driver_list').html(html);
}

function selectDriver(driverId, driverCode, driverName) {
    selectedDriverId = driverId;
    $('#document_viewer_title').text(`Documents - ${driverName} (${driverCode})`);
    loadDriverDocuments(driverId);
    $('#document_actions').show();
}

function loadDriverDocuments(driverId) {
    $('#document_viewer_content').html(`
        <div class="text-center">
            <i class="fas fa-spinner fa-spin fa-2x"></i>
            <p class="mt-2">Loading documents...</p>
        </div>
    `);

    $.get(`{{ url('admin/superadmin/drivers') }}/${driverId}/documents-data`)
        .done(function(response) {
            renderDocuments(response.documents);
        })
        .fail(function() {
            $('#document_viewer_content').html(`
                <div class="text-center text-danger">
                    <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                    <p>Failed to load documents</p>
                </div>
            `);
        });
}

function renderDocuments(documents) {
    if (!documents || documents.length === 0) {
        $('#document_viewer_content').html(`
            <div class="text-center text-muted">
                <i class="fas fa-file-alt fa-3x mb-3"></i>
                <p>No documents found for this driver</p>
            </div>
        `);
        return;
    }

    let html = '<div class="row">';

    documents.forEach(doc => {
        const statusClass = getDocumentStatusClass(doc.verification_status);
        const statusIcon = getDocumentStatusIcon(doc.verification_status);

        html += `
            <div class="col-md-6 mb-3">
                <div class="card document-card ${doc.verification_status === 'pending' ? 'border-warning' : ''}" data-document-id="${doc.id}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="card-title mb-1">${formatDocumentType(doc.document_type)}</h6>
                                <p class="card-text small text-muted mb-2">
                                    Uploaded: ${new Date(doc.created_at).toLocaleDateString()}
                                </p>
                                <span class="badge ${statusClass}">
                                    <i class="${statusIcon}"></i> ${doc.verification_status}
                                </span>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input document-checkbox"
                                       value="${doc.id}" onchange="toggleDocumentSelection(${doc.id})">
                            </div>
                        </div>
                        <div class="mt-3">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="previewDocument('${doc.document_path}', '${doc.document_type}')">
                                <i class="fas fa-eye"></i> Preview
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-success" onclick="approveDocument(${doc.id})">
                                <i class="fas fa-check"></i> Approve
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="rejectDocument(${doc.id})">
                                <i class="fas fa-times"></i> Reject
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });

    html += '</div>';
    $('#document_viewer_content').html(html);
}

function getDocumentStatusClass(status) {
    const classes = {
        'approved': 'badge-success',
        'rejected': 'badge-danger',
        'pending': 'badge-warning'
    };
    return classes[status] || 'badge-secondary';
}

function getDocumentStatusIcon(status) {
    const icons = {
        'approved': 'fas fa-check',
        'rejected': 'fas fa-times',
        'pending': 'fas fa-clock'
    };
    return icons[status] || 'fas fa-question';
}

function formatDocumentType(type) {
    const types = {
        'profile_picture': 'Profile Picture',
        'license_front_image': 'Driver License (Front)',
        'license_back_image': 'Driver License (Back)',
        'nin_document': 'NIN Document',
        'passport_photograph': 'Passport Photograph',
        'frsc_document': 'FRSC Document'
    };
    return types[type] || type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
}

function toggleDocumentSelection(docId) {
    if (selectedDocuments.has(docId)) {
        selectedDocuments.delete(docId);
    } else {
        selectedDocuments.add(docId);
    }
}

function previewDocument(path, type) {
    const fileUrl = '{{ asset("") }}' + path;

    $('#documentPreviewTitle').text(formatDocumentType(type));

    if (type.includes('image') || path.match(/\.(jpg|jpeg|png|gif)$/i)) {
        $('#documentPreviewContent').html(`<img src="${fileUrl}" class="img-fluid" style="max-height: 500px;">`);
    } else {
        $('#documentPreviewContent').html(`
            <div class="text-center">
                <i class="fas fa-file-alt fa-4x text-muted mb-3"></i>
                <p>This document type cannot be previewed directly.</p>
                <p>Click download to view the file.</p>
            </div>
        `);
    }

    $('#downloadDocumentBtn').attr('href', fileUrl);
    $('#documentPreviewModal').modal('show');
}

function approveDocument(docId) {
    if (!confirm('Are you sure you want to approve this document?')) return;

    const notes = $('#action_notes').val();
    const password = $('#admin_password').val();

    if (!password) {
        alert('Admin password is required');
        return;
    }

    $.post(`{{ url('admin/superadmin/drivers') }}/documents/${docId}/approve`, {
        _token: '{{ csrf_token() }}',
        notes: notes,
        admin_password: password
    })
    .done(function(response) {
        if (response.success) {
            alert('Document approved successfully');
            if (selectedDriverId) {
                loadDriverDocuments(selectedDriverId);
            }
            loadDocumentHistory();
        } else {
            alert('Failed to approve document: ' + response.message);
        }
    })
    .fail(function(xhr) {
        alert('Error: ' + xhr.responseJSON?.message || 'Failed to approve document');
    });
}

function rejectDocument(docId) {
    if (!confirm('Are you sure you want to reject this document?')) return;

    const notes = $('#action_notes').val();
    const password = $('#admin_password').val();

    if (!password) {
        alert('Admin password is required');
        return;
    }

    $.post(`{{ url('admin/superadmin/drivers') }}/documents/${docId}/reject`, {
        _token: '{{ csrf_token() }}',
        notes: notes,
        admin_password: password
    })
    .done(function(response) {
        if (response.success) {
            alert('Document rejected successfully');
            if (selectedDriverId) {
                loadDriverDocuments(selectedDriverId);
            }
            loadDocumentHistory();
        } else {
            alert('Failed to reject document: ' + response.message);
        }
    })
    .fail(function(xhr) {
        alert('Error: ' + xhr.responseJSON?.message || 'Failed to reject document');
    });
}

function approveSelectedDocuments() {
    if (selectedDocuments.size === 0) {
        alert('Please select documents to approve');
        return;
    }

    if (!confirm(`Approve ${selectedDocuments.size} selected document(s)?`)) return;

    const notes = $('#action_notes').val();
    const password = $('#admin_password').val();

    if (!password) {
        alert('Admin password is required');
        return;
    }

    // Process each selected document
    const promises = Array.from(selectedDocuments).map(docId =>
        $.post(`{{ url('admin/superadmin/drivers') }}/documents/${docId}/approve`, {
            _token: '{{ csrf_token() }}',
            notes: notes,
            admin_password: password
        })
    );

    Promise.all(promises)
        .then(() => {
            alert('Selected documents approved successfully');
            selectedDocuments.clear();
            if (selectedDriverId) {
                loadDriverDocuments(selectedDriverId);
            }
            loadDocumentHistory();
        })
        .catch(() => {
            alert('Some documents failed to approve');
        });
}

function rejectSelectedDocuments() {
    if (selectedDocuments.size === 0) {
        alert('Please select documents to reject');
        return;
    }

    if (!confirm(`Reject ${selectedDocuments.size} selected document(s)?`)) return;

    const notes = $('#action_notes').val();
    const password = $('#admin_password').val();

    if (!password) {
        alert('Admin password is required');
        return;
    }

    // Process each selected document
    const promises = Array.from(selectedDocuments).map(docId =>
        $.post(`{{ url('admin/superadmin/drivers') }}/documents/${docId}/reject`, {
            _token: '{{ csrf_token() }}',
            notes: notes,
            admin_password: password
        })
    );

    Promise.all(promises)
        .then(() => {
            alert('Selected documents rejected successfully');
            selectedDocuments.clear();
            if (selectedDriverId) {
                loadDriverDocuments(selectedDriverId);
            }
            loadDocumentHistory();
        })
        .catch(() => {
            alert('Some documents failed to reject');
        });
}

function bulkApproveDocuments() {
    alert('Bulk document approval feature coming soon!');
}

function bulkRejectDocuments() {
    alert('Bulk document rejection feature coming soon!');
}

function exportDocumentReport() {
    const url = '{{ route("admin.superadmin.drivers.documents-export") }}';
    window.open(url, '_blank');
}

function refreshDocuments() {
    if (selectedDriverId) {
        loadDriverDocuments(selectedDriverId);
    }
}

function loadDocumentHistory() {
    $.get('{{ route("admin.superadmin.drivers.documents-history") }}')
        .done(function(response) {
            renderDocumentHistory(response.history);
        })
        .fail(function() {
            $('#document_history_table').html('<tr><td colspan="6" class="text-center text-danger">Failed to load history</td></tr>');
        });
}

function renderDocumentHistory(history) {
    if (!history || history.length === 0) {
        $('#document_history_table').html('<tr><td colspan="6" class="text-center text-muted">No document history found</td></tr>');
        return;
    }

    let html = '';
    history.forEach(item => {
        const actionClass = item.action === 'approved' ? 'text-success' : 'text-danger';
        const actionIcon = item.action === 'approved' ? 'fas fa-check' : 'fas fa-times';

        html += `
            <tr>
                <td>${item.driver_name} (${item.driver_id})</td>
                <td>${formatDocumentType(item.document_type)}</td>
                <td><span class="${actionClass}"><i class="${actionIcon}"></i> ${item.action}</span></td>
                <td>${item.admin_name || 'System'}</td>
                <td>${new Date(item.created_at).toLocaleDateString()}</td>
                <td>${item.notes || '-'}</td>
            </tr>
        `;
    });

    $('#document_history_table').html(html);
}
</script>
@endpush
