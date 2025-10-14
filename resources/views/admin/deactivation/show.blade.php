@extends('layouts.admin')

@section('title', 'Deactivation Request Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Deactivation Request #{{ $deactivationRequest->id }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.deactivation.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-4">Status:</dt>
                                <dd class="col-sm-8">
                                    <span class="badge badge-{{ $deactivationRequest->status === 'pending' ? 'warning' : ($deactivationRequest->status === 'approved' ? 'success' : 'danger') }}">
                                        {{ ucfirst($deactivationRequest->status) }}
                                    </span>
                                </dd>

                                <dt class="col-sm-4">User Type:</dt>
                                <dd class="col-sm-8">{{ ucfirst($deactivationRequest->user_type) }}</dd>

                                <dt class="col-sm-4">User:</dt>
                                <dd class="col-sm-8">
                                    @if($deactivationRequest->user_type === 'driver')
                                        {{ $deactivationRequest->user->full_name ?? 'N/A' }}
                                        (ID: {{ $deactivationRequest->user_id }})
                                    @else
                                        {{ $deactivationRequest->user->name ?? 'N/A' }}
                                        (ID: {{ $deactivationRequest->user_id }})
                                    @endif
                                </dd>

                                <dt class="col-sm-4">Requested By:</dt>
                                <dd class="col-sm-8">{{ $deactivationRequest->requester->name ?? 'System' }}</dd>

                                <dt class="col-sm-4">Created:</dt>
                                <dd class="col-sm-8">{{ $deactivationRequest->created_at->format('M d, Y H:i') }}</dd>

                                @if($deactivationRequest->approved_at)
                                <dt class="col-sm-4">Approved At:</dt>
                                <dd class="col-sm-8">{{ $deactivationRequest->approved_at->format('M d, Y H:i') }}</dd>

                                <dt class="col-sm-4">Approved By:</dt>
                                <dd class="col-sm-8">{{ $deactivationRequest->approver->name ?? 'N/A' }}</dd>
                                @endif
                            </dl>
                        </div>

                        <div class="col-md-6">
                            <h5>Reason for Deactivation:</h5>
                            <p class="text-muted">{{ $deactivationRequest->reason }}</p>

                            @if($deactivationRequest->notes)
                            <h5>Notes:</h5>
                            <p class="text-muted">{{ $deactivationRequest->notes }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                @if($deactivationRequest->status === 'pending')
                <div class="card-footer">
                    @can('review-deactivations')
                    <form action="{{ route('admin.deactivation.review', $deactivationRequest) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-search"></i> Send to Admin-I Review
                        </button>
                    </form>
                    @endcan

                    @can('approve-deactivations')
                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#approveModal">
                        <i class="fas fa-check"></i> Approve & Send OTP
                    </button>

                    <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#rejectModal">
                        <i class="fas fa-times"></i> Reject
                    </button>
                    @endcan
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('admin.deactivation.approve', $deactivationRequest) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h4 class="modal-title">Approve Deactivation Request</h4>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="notes">Approval Notes (Optional)</label>
                        <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="Add any notes for approval..."></textarea>
                    </div>
                    <p class="text-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        This will generate an OTP that must be verified to complete the deactivation.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Approve & Send OTP</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('admin.deactivation.reject', $deactivationRequest) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h4 class="modal-title">Reject Deactivation Request</h4>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="rejection_reason">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea name="rejection_reason" id="rejection_reason" class="form-control" rows="3" required placeholder="Provide reason for rejection..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject Request</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
