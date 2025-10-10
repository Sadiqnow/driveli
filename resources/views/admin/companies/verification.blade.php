@extends('layouts.admin_cdn')

@section('title', 'Company Verification')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Company Verification</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ url('admin/companies') }}">Companies</a></li>
                    <li class="breadcrumb-item active">Verification</li>
                </ol>
            </div>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Verification Statistics -->
            <div class="row mb-4">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ $stats['total'] ?? 0 }}</h3>
                            <p>Total Companies</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-building"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{ $stats['verified'] ?? 0 }}</h3>
                            <p>Verified</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{ $stats['pending'] ?? 0 }}</h3>
                            <p>Pending</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3>{{ $stats['rejected'] ?? 0 }}</h3>
                            <p>Rejected</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-times-circle"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Company Verification Table -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Company Verification Status</h3>
                    <div class="card-tools">
                        <div class="input-group input-group-sm" style="width: 150px;">
                            <input type="text" name="table_search" class="form-control float-right" placeholder="Search">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-default">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap" id="companyVerificationTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Company Name</th>
                                <th>Email</th>
                                <th>Registration No.</th>
                                <th>Industry</th>
                                <th>Status</th>
                                <th>Verification Status</th>
                                <th>Submitted</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($companies as $company)
                            <tr>
                                <td>{{ $company->company_id }}</td>
                                <td>
                                    <strong>{{ $company->name }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $company->contact_person_name }}</small>
                                </td>
                                <td>{{ $company->email }}</td>
                                <td>{{ $company->registration_number }}</td>
                                <td>{{ $company->industry }}</td>
                                <td>
                                    @if($company->status === 'Active')
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-secondary">{{ $company->status }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($company->verification_status === 'Verified')
                                        <span class="badge badge-success">
                                            <i class="fas fa-check"></i> Verified
                                        </span>
                                    @elseif($company->verification_status === 'Rejected')
                                        <span class="badge badge-danger">
                                            <i class="fas fa-times"></i> Rejected
                                        </span>
                                    @else
                                        <span class="badge badge-warning">
                                            <i class="fas fa-clock"></i> Pending
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $company->created_at->format('M d, Y') }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.companies.show', $company->id) }}" 
                                           class="btn btn-sm btn-info" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        @if($company->verification_status === 'Pending')
                                        <button type="button" class="btn btn-sm btn-success verify-company" 
                                                data-company-id="{{ $company->id }}" title="Verify Company">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger reject-company" 
                                                data-company-id="{{ $company->id }}" title="Reject Company">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        @endif
                                        
                                        @if($company->verification_status === 'Rejected')
                                        <button type="button" class="btn btn-sm btn-warning reverify-company" 
                                                data-company-id="{{ $company->id }}" title="Re-verify Company">
                                            <i class="fas fa-redo"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer clearfix">
                    {{ $companies->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Verification Modal -->
    <div class="modal fade" id="verificationModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="verificationModalTitle">Verify Company</h4>
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="verificationForm">
                    <div class="modal-body">
                        <input type="hidden" id="companyId" name="company_id">
                        <input type="hidden" id="verificationAction" name="action">
                        
                        <div class="form-group">
                            <label for="verificationNotes">Notes/Comments:</label>
                            <textarea class="form-control" id="verificationNotes" name="notes" 
                                    rows="4" placeholder="Add any verification notes or comments..."></textarea>
                        </div>
                        
                        <div id="rejectionReasonGroup" class="form-group" style="display: none;">
                            <label for="rejectionReason">Rejection Reason:</label>
                            <select class="form-control" id="rejectionReason" name="rejection_reason">
                                <option value="">Select a reason</option>
                                <option value="incomplete_documents">Incomplete Documents</option>
                                <option value="invalid_registration">Invalid Registration</option>
                                <option value="suspicious_activity">Suspicious Activity</option>
                                <option value="duplicate_company">Duplicate Company</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="submitVerification">Confirm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#companyVerificationTable').DataTable({
        "responsive": true,
        "lengthChange": false,
        "autoWidth": false,
        "order": [[ 7, "desc" ]],
        "columnDefs": [
            { "orderable": false, "targets": 8 }
        ]
    });

    // Verify company
    $('.verify-company').click(function() {
        const companyId = $(this).data('company-id');
        setupVerificationModal(companyId, 'verify', 'Verify Company', 'btn-success');
    });

    // Reject company
    $('.reject-company').click(function() {
        const companyId = $(this).data('company-id');
        setupVerificationModal(companyId, 'reject', 'Reject Company', 'btn-danger');
    });

    // Re-verify company
    $('.reverify-company').click(function() {
        const companyId = $(this).data('company-id');
        setupVerificationModal(companyId, 'verify', 'Re-verify Company', 'btn-warning');
    });

    function setupVerificationModal(companyId, action, title, buttonClass) {
        $('#companyId').val(companyId);
        $('#verificationAction').val(action);
        $('#verificationModalTitle').text(title);
        $('#submitVerification').removeClass('btn-success btn-danger btn-warning').addClass(buttonClass);
        
        if (action === 'reject') {
            $('#rejectionReasonGroup').show();
            $('#rejectionReason').prop('required', true);
        } else {
            $('#rejectionReasonGroup').hide();
            $('#rejectionReason').prop('required', false);
        }
        
        $('#verificationModal').modal('show');
    }

    // Handle verification form submission
    $('#verificationForm').submit(function(e) {
        e.preventDefault();
        
        const formData = {
            company_id: $('#companyId').val(),
            action: $('#verificationAction').val(),
            notes: $('#verificationNotes').val(),
            rejection_reason: $('#rejectionReason').val(),
            _token: '{{ csrf_token() }}'
        };

        $.ajax({
            url: '{{ route("admin.companies.update-verification") }}',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    $('#verificationModal').modal('hide');
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('An error occurred while processing the verification.');
            }
        });
    });
});
</script>
@stop