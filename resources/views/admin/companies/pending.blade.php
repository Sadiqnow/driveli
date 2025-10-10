@extends('layouts.admin_cdn')

@section('title', 'Pending Company Verification')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Pending Company Verification</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ url('admin/companies') }}">Companies</a></li>
                    <li class="breadcrumb-item active">Pending Verification</li>
                </ol>
            </div>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Alert for pending count -->
            @if($pendingCount > 0)
            <div class="alert alert-warning alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <h5><i class="icon fas fa-exclamation-triangle"></i> Attention!</h5>
                You have {{ $pendingCount }} company{{ $pendingCount > 1 ? 'ies' : 'y' }} waiting for verification.
            </div>
            @endif

            <!-- Pending Companies Card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-clock"></i> 
                        Companies Pending Verification ({{ $pendingCount }})
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-sm btn-success" id="bulkVerifyBtn">
                            <i class="fas fa-check-double"></i> Bulk Verify
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if($companies->count() > 0)
                    <div class="row">
                        @foreach($companies as $company)
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card card-outline card-warning">
                                <div class="card-header">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="card-title mb-0">{{ $company->name }}</h5>
                                        <div class="custom-control custom-checkbox">
                                            <input class="custom-control-input company-checkbox" 
                                                   type="checkbox" 
                                                   id="company{{ $company->id }}" 
                                                   value="{{ $company->id }}">
                                            <label for="company{{ $company->id }}" class="custom-control-label"></label>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="info-box mb-2">
                                        <span class="info-box-icon bg-warning">
                                            <i class="fas fa-building"></i>
                                        </span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Company ID</span>
                                            <span class="info-box-number">{{ $company->company_id }}</span>
                                        </div>
                                    </div>
                                    
                                    <p><strong>Industry:</strong> {{ $company->industry }}</p>
                                    <p><strong>Email:</strong> {{ $company->email }}</p>
                                    <p><strong>Phone:</strong> {{ $company->phone }}</p>
                                    <p><strong>Registration:</strong> {{ $company->registration_number }}</p>
                                    <p><strong>Contact Person:</strong> {{ $company->contact_person_name }}</p>
                                    <p><strong>Submitted:</strong> {{ $company->created_at->diffForHumans() }}</p>
                                    
                                    @if($company->logo)
                                    <div class="mb-2">
                                        <small class="text-muted">Company Logo:</small>
                                        <img src="{{ asset('storage/' . $company->logo) }}" 
                                             alt="Company Logo" 
                                             class="img-thumbnail" 
                                             style="max-height: 50px;">
                                    </div>
                                    @endif
                                </div>
                                <div class="card-footer">
                                    <div class="btn-group btn-group-sm w-100" role="group">
                                        <a href="{{ route('admin.companies.show', $company->id) }}" 
                                           class="btn btn-info">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <button type="button" 
                                                class="btn btn-success verify-single" 
                                                data-company-id="{{ $company->id }}">
                                            <i class="fas fa-check"></i> Verify
                                        </button>
                                        <button type="button" 
                                                class="btn btn-danger reject-single" 
                                                data-company-id="{{ $company->id }}">
                                            <i class="fas fa-times"></i> Reject
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    
                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $companies->links() }}
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="fas fa-check-circle fa-5x text-success mb-3"></i>
                        <h4>All Caught Up!</h4>
                        <p class="text-muted">No companies are currently pending verification.</p>
                        <a href="{{ route('admin.companies.index') }}" class="btn btn-primary">
                            <i class="fas fa-building"></i> View All Companies
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Verification Modal -->
    <div class="modal fade" id="quickVerificationModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="quickVerificationTitle">Quick Verification</h4>
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="quickVerificationForm">
                    <div class="modal-body">
                        <input type="hidden" id="quickCompanyIds" name="company_ids">
                        <input type="hidden" id="quickAction" name="action">
                        
                        <div id="companyList" class="mb-3"></div>
                        
                        <div class="form-group">
                            <label for="quickNotes">Verification Notes:</label>
                            <textarea class="form-control" id="quickNotes" name="notes" 
                                    rows="3" placeholder="Add verification notes..."></textarea>
                        </div>
                        
                        <div id="quickRejectionGroup" class="form-group" style="display: none;">
                            <label for="quickRejectionReason">Rejection Reason:</label>
                            <select class="form-control" id="quickRejectionReason" name="rejection_reason">
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
                        <button type="submit" class="btn btn-primary" id="submitQuickVerification">Process</button>
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
    // Single company verification
    $('.verify-single').click(function() {
        const companyId = $(this).data('company-id');
        processQuickVerification([companyId], 'verify');
    });

    $('.reject-single').click(function() {
        const companyId = $(this).data('company-id');
        processQuickVerification([companyId], 'reject');
    });

    // Bulk verification
    $('#bulkVerifyBtn').click(function() {
        const selectedIds = $('.company-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        if (selectedIds.length === 0) {
            alert('Please select at least one company to verify.');
            return;
        }

        processQuickVerification(selectedIds, 'verify');
    });

    function processQuickVerification(companyIds, action) {
        $('#quickCompanyIds').val(companyIds.join(','));
        $('#quickAction').val(action);
        
        if (action === 'verify') {
            $('#quickVerificationTitle').text('Verify Companies');
            $('#submitQuickVerification').removeClass('btn-danger').addClass('btn-success');
            $('#quickRejectionGroup').hide();
        } else {
            $('#quickVerificationTitle').text('Reject Companies');
            $('#submitQuickVerification').removeClass('btn-success').addClass('btn-danger');
            $('#quickRejectionGroup').show();
        }

        // Show company list
        const companyListHtml = companyIds.length === 1 
            ? '<p>You are about to ' + action + ' <strong>1</strong> company.</p>'
            : '<p>You are about to ' + action + ' <strong>' + companyIds.length + '</strong> companies.</p>';
        
        $('#companyList').html(companyListHtml);
        $('#quickVerificationModal').modal('show');
    }

    // Handle quick verification form submission
    $('#quickVerificationForm').submit(function(e) {
        e.preventDefault();
        
        const formData = {
            company_ids: $('#quickCompanyIds').val(),
            action: $('#quickAction').val(),
            notes: $('#quickNotes').val(),
            rejection_reason: $('#quickRejectionReason').val(),
            _token: '{{ csrf_token() }}'
        };

        $.ajax({
            url: '{{ route("admin.companies.bulk-verification") }}',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    $('#quickVerificationModal').modal('hide');
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

    // Select all checkbox functionality
    $('#selectAll').change(function() {
        $('.company-checkbox').prop('checked', $(this).is(':checked'));
    });

    $('.company-checkbox').change(function() {
        if (!$(this).is(':checked')) {
            $('#selectAll').prop('checked', false);
        }
    });
});
</script>
@stop