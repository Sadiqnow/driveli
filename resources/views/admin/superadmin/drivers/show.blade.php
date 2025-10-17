@extends('layouts.admin_master')

@section('title', 'Superadmin - Driver Details')

@section('content_header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Driver Details</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.superadmin.dashboard') }}">Super Admin</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.superadmin.drivers.index') }}">Drivers</a></li>
                        <li class="breadcrumb-item active">{{ $driver->driver_id }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid">
        <!-- Action Buttons -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="btn-group" role="group">
                    <a href="{{ route('admin.superadmin.drivers.edit', $driver) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Edit Driver
                    </a>

                    @if($driver->verification_status == 'pending')
                        <button type="button" class="btn btn-success" onclick="approveDriver({{ $driver->id }})">
                            <i class="fas fa-check"></i> Approve
                        </button>
                        <button type="button" class="btn btn-danger" onclick="rejectDriver({{ $driver->id }})">
                            <i class="fas fa-times"></i> Reject
                        </button>
                    @endif

                    @if($driver->status != 'flagged')
                        <button type="button" class="btn btn-warning" onclick="flagDriver({{ $driver->id }})">
                            <i class="fas fa-flag"></i> Flag
                        </button>
                    @else
                        <button type="button" class="btn btn-info" onclick="restoreDriver({{ $driver->id }})">
                            <i class="fas fa-undo"></i> Restore
                        </button>
                    @endif

                    <button type="button" class="btn btn-dark" onclick="deleteDriver({{ $driver->id }}, '{{ $driver->full_name }}')">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </div>

                <div class="float-right">
                    <a href="{{ route('admin.superadmin.drivers.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
        </div>

        <!-- Status Badges -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="d-flex flex-wrap gap-2">
                    <span class="badge badge-{{ $driver->status == 'active' ? 'success' : ($driver->status == 'inactive' ? 'warning' : 'danger') }} badge-lg">
                        <i class="fas fa-circle"></i> Status: {{ ucfirst($driver->status) }}
                    </span>
                    <span class="badge badge-{{ $driver->verification_status == 'verified' ? 'success' : ($driver->verification_status == 'pending' ? 'warning' : 'danger') }} badge-lg">
                        <i class="fas fa-shield-alt"></i> Verification: {{ ucfirst($driver->verification_status) }}
                    </span>
                    <span class="badge badge-{{ $driver->kyc_status == 'completed' ? 'success' : ($driver->kyc_status == 'in_progress' ? 'info' : 'secondary') }} badge-lg">
                        <i class="fas fa-id-card"></i> KYC: {{ ucfirst(str_replace('_', ' ', $driver->kyc_status)) }}
                    </span>
                    @if($driver->verified_at)
                        <span class="badge badge-success badge-lg">
                            <i class="fas fa-calendar-check"></i> Verified: {{ $driver->verified_at->format('M d, Y') }}
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Basic Information -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-user"></i> Basic Information</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <dl class="row">
                                    <dt class="col-sm-4">Driver ID:</dt>
                                    <dd class="col-sm-8">{{ $driver->driver_id }}</dd>

                                    <dt class="col-sm-4">Full Name:</dt>
                                    <dd class="col-sm-8">{{ $driver->full_name }}</dd>

                                    <dt class="col-sm-4">First Name:</dt>
                                    <dd class="col-sm-8">{{ $driver->first_name }}</dd>

                                    <dt class="col-sm-4">Surname:</dt>
                                    <dd class="col-sm-8">{{ $driver->surname }}</dd>

                                    <dt class="col-sm-4">Email:</dt>
                                    <dd class="col-sm-8">{{ $driver->email }}</dd>

                                    <dt class="col-sm-4">Phone:</dt>
                                    <dd class="col-sm-8">{{ $driver->phone }}</dd>
                                </dl>
                            </div>
                            <div class="col-md-6">
                                <dl class="row">
                                    <dt class="col-sm-4">Date of Birth:</dt>
                                    <dd class="col-sm-8">{{ $driver->date_of_birth ? $driver->date_of_birth->format('M d, Y') : 'Not provided' }}</dd>

                                    <dt class="col-sm-4">Gender:</dt>
                                    <dd class="col-sm-8">{{ $driver->gender ? ucfirst($driver->gender) : 'Not specified' }}</dd>

                                    <dt class="col-sm-4">Created:</dt>
                                    <dd class="col-sm-8">{{ $driver->created_at->format('M d, Y H:i') }}</dd>

                                    <dt class="col-sm-4">Last Updated:</dt>
                                    <dd class="col-sm-8">{{ $driver->updated_at->format('M d, Y H:i') }}</dd>

                                    @if($driver->verified_by)
                                        <dt class="col-sm-4">Verified By:</dt>
                                        <dd class="col-sm-8">{{ $driver->verifiedBy->name ?? 'Unknown' }}</dd>
                                    @endif
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Information -->
                @if($driver->employmentHistory || $driver->guarantors || $driver->documents)
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-info-circle"></i> Additional Information</h3>
                    </div>
                    <div class="card-body">
                        <!-- Employment History -->
                        @if($driver->employmentHistory && $driver->employmentHistory->count() > 0)
                            <h5>Employment History</h5>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Employer</th>
                                            <th>Position</th>
                                            <th>Start Date</th>
                                            <th>End Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($driver->employmentHistory as $employment)
                                            <tr>
                                                <td>{{ $employment->employer_name }}</td>
                                                <td>{{ $employment->position }}</td>
                                                <td>{{ $employment->start_date?->format('M Y') }}</td>
                                                <td>{{ $employment->end_date?->format('M Y') ?: 'Current' }}</td>
                                                <td>
                                                    <span class="badge badge-{{ $employment->is_current ? 'success' : 'secondary' }}">
                                                        {{ $employment->is_current ? 'Current' : 'Previous' }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif

                        <!-- Guarantors -->
                        @if($driver->guarantors && $driver->guarantors->count() > 0)
                            <h5 class="mt-4">Guarantors</h5>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Phone</th>
                                            <th>Email</th>
                                            <th>Relationship</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($driver->guarantors as $guarantor)
                                            <tr>
                                                <td>{{ $guarantor->full_name }}</td>
                                                <td>{{ $guarantor->phone }}</td>
                                                <td>{{ $guarantor->email }}</td>
                                                <td>{{ $guarantor->relationship }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif

                        <!-- Documents -->
                        @if($driver->documents && $driver->documents->count() > 0)
                            <h5 class="mt-4">Documents</h5>
                            <div class="row">
                                @foreach($driver->documents as $document)
                                    <div class="col-md-4 mb-3">
                                        <div class="card">
                                            <div class="card-body text-center">
                                                <i class="fas fa-file-{{ $document->document_type == 'license' ? 'certificate' : 'image' }} fa-2x text-primary"></i>
                                                <h6 class="card-title">{{ ucfirst(str_replace('_', ' ', $document->document_type)) }}</h6>
                                                <p class="card-text small">{{ $document->file_name }}</p>
                                                <span class="badge badge-{{ $document->verification_status == 'verified' ? 'success' : ($document->verification_status == 'pending' ? 'warning' : 'danger') }}">
                                                    {{ ucfirst($document->verification_status) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="col-md-4">
                <!-- Quick Stats -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-chart-bar"></i> Quick Stats</h3>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="description-block">
                                    <span class="description-header">{{ $driver->performance->completed_rides ?? 0 }}</span>
                                    <span class="description-text">COMPLETED RIDES</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="description-block">
                                    <span class="description-header">{{ number_format($driver->performance->rating ?? 0, 1) }}</span>
                                    <span class="description-text">RATING</span>
                                </div>
                            </div>
                        </div>
                        <div class="row text-center mt-3">
                            <div class="col-6">
                                <div class="description-block">
                                    <span class="description-header">{{ $driver->performance->total_earnings ?? 0 }}</span>
                                    <span class="description-text">TOTAL EARNINGS</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="description-block">
                                    <span class="description-header">{{ $driver->performance->success_rate ?? 0 }}%</span>
                                    <span class="description-text">SUCCESS RATE</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-history"></i> Recent Activity</h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <!-- This would be populated with actual activity data -->
                            <div class="list-group-item">
                                <i class="fas fa-user-plus text-success"></i>
                                <small class="text-muted ml-2">Driver created {{ $driver->created_at->diffForHumans() }}</small>
                            </div>
                            @if($driver->verified_at)
                                <div class="list-group-item">
                                    <i class="fas fa-check-circle text-success"></i>
                                    <small class="text-muted ml-2">Verified {{ $driver->verified_at->diffForHumans() }}</small>
                                </div>
                            @endif
                            <div class="list-group-item">
                                <i class="fas fa-edit text-info"></i>
                                <small class="text-muted ml-2">Last updated {{ $driver->updated_at->diffForHumans() }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals (same as index view) -->
    <!-- Approve Modal -->
    <div class="modal fade" id="approveModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Approve Driver</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="approveForm">
                    @csrf
                    <div class="modal-body">
                        <p>Are you sure you want to approve <strong>{{ $driver->full_name }}</strong>?</p>
                        <div class="form-group">
                            <label for="approvalNotes">Approval Notes (Optional)</label>
                            <textarea class="form-control" id="approvalNotes" name="notes" rows="3" placeholder="Add any notes about this approval..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Approve Driver</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reject Driver</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="rejectForm">
                    @csrf
                    <div class="modal-body">
                        <p>Are you sure you want to reject <strong>{{ $driver->full_name }}</strong>?</p>
                        <div class="form-group">
                            <label for="rejectionReason">Rejection Reason <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="rejectionReason" name="reason" rows="3" required placeholder="Please provide a reason for rejection..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Reject Driver</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Flag Modal -->
    <div class="modal fade" id="flagModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Flag Driver</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="flagForm">
                    @csrf
                    <div class="modal-body">
                        <p>Are you sure you want to flag <strong>{{ $driver->full_name }}</strong>?</p>
                        <div class="form-group">
                            <label for="flagReason">Flag Reason <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="flagReason" name="reason" rows="3" required placeholder="Please provide a reason for flagging..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Flag Driver</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Driver</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete <strong>{{ $driver->full_name }}</strong>?</p>
                    <p class="text-danger">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <form id="deleteForm" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete Driver</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
.badge-lg {
    font-size: 0.875rem;
    padding: 0.5rem 0.75rem;
}

.description-block {
    margin-bottom: 1rem;
}

.description-header {
    font-size: 1.5rem;
    font-weight: bold;
    color: #007bff;
}

.description-text {
    font-size: 0.875rem;
    color: #6c757d;
    text-transform: uppercase;
}

.dl-horizontal dt {
    text-align: left;
    width: auto;
    margin-right: 1rem;
}

.dl-horizontal dd {
    margin-left: 0;
}

.card-title {
    margin: 0;
}

.gap-2 {
    gap: 0.5rem;
}

.list-group-item {
    padding: 0.75rem 1.25rem;
}
</style>
@endpush

@push('scripts')
<script>
// Modal functions
function approveDriver(driverId) {
    $('#approveModal').modal('show');
}

function rejectDriver(driverId) {
    $('#rejectModal').modal('show');
}

function flagDriver(driverId) {
    $('#flagModal').modal('show');
}

function deleteDriver(driverId, driverName) {
    $('#deleteForm').attr('action', `{{ url('admin/superadmin/drivers') }}/${driverId}`);
    $('#deleteModal').modal('show');
}

// Form submissions
$('#approveForm').on('submit', function(e) {
    e.preventDefault();
    const notes = $('#approvalNotes').val();

    $.post(`{{ url('admin/superadmin/drivers') }}/${{ $driver->id }}/approve`, {
        _token: '{{ csrf_token() }}',
        notes: notes
    })
    .done(function(response) {
        location.reload();
    })
    .fail(function(xhr) {
        alert('Error: ' + xhr.responseJSON?.message || 'Failed to approve driver');
    });
});

$('#rejectForm').on('submit', function(e) {
    e.preventDefault();
    const reason = $('#rejectionReason').val();

    $.post(`{{ url('admin/superadmin/drivers') }}/${{ $driver->id }}/reject`, {
        _token: '{{ csrf_token() }}',
        reason: reason
    })
    .done(function(response) {
        location.reload();
    })
    .fail(function(xhr) {
        alert('Error: ' + xhr.responseJSON?.message || 'Failed to reject driver');
    });
});

$('#flagForm').on('submit', function(e) {
    e.preventDefault();
    const reason = $('#flagReason').val();

    $.post(`{{ url('admin/superadmin/drivers') }}/${{ $driver->id }}/flag`, {
        _token: '{{ csrf_token() }}',
        reason: reason
    })
    .done(function(response) {
        location.reload();
    })
    .fail(function(xhr) {
        alert('Error: ' + xhr.responseJSON?.message || 'Failed to flag driver');
    });
});
</script>
@endpush
