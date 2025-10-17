@extends('layouts.admin_master')

@section('title', 'Admin User Details')

@section('content_header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ $admin->name }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.superadmin.dashboard') }}">Super Admin</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.superadmin.admins.index') }}">Admins</a></li>
                        <li class="breadcrumb-item active">{{ $admin->name }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <!-- Admin Information -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Admin Information</h3>
                        <div class="card-tools">
                            <a href="{{ route('admin.superadmin.admins.edit', $admin) }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <dl class="row">
                            <dt class="col-sm-3">Full Name:</dt>
                            <dd class="col-sm-9">{{ $admin->name }}</dd>

                            <dt class="col-sm-3">Email:</dt>
                            <dd class="col-sm-9">{{ $admin->email }}</dd>

                            <dt class="col-sm-3">Phone:</dt>
                            <dd class="col-sm-9">{{ $admin->phone ?: 'Not provided' }}</dd>

                            <dt class="col-sm-3">Status:</dt>
                            <dd class="col-sm-9">
                                <span class="badge badge-{{ $admin->status == 'Active' ? 'success' : 'secondary' }}">
                                    {{ $admin->status }}
                                </span>
                            </dd>

                            <dt class="col-sm-3">Role:</dt>
                            <dd class="col-sm-9">
                                @if($admin->roles->first())
                                    <span class="badge badge-primary">
                                        {{ $admin->roles->first()->display_name ?? $admin->roles->first()->name }}
                                    </span>
                                @else
                                    <span class="badge badge-light">No role assigned</span>
                                @endif
                            </dd>

                            <dt class="col-sm-3">Created:</dt>
                            <dd class="col-sm-9">{{ $admin->created_at->format('M d, Y H:i') }}</dd>

                            <dt class="col-sm-3">Last Updated:</dt>
                            <dd class="col-sm-9">{{ $admin->updated_at->format('M d, Y H:i') }}</dd>

                            <dt class="col-sm-3">Last Login:</dt>
                            <dd class="col-sm-9">{{ $admin->last_login_at ? $admin->last_login_at->format('M d, Y H:i') : 'Never logged in' }}</dd>

                            <dt class="col-sm-3">Email Verified:</dt>
                            <dd class="col-sm-9">
                                @if($admin->email_verified_at)
                                    <i class="fas fa-check-circle text-success"></i> Verified
                                @else
                                    <i class="fas fa-times-circle text-danger"></i> Not verified
                                @endif
                            </dd>
                        </dl>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Recent Activity</h3>
                        <div class="card-tools">
                            <a href="{{ route('admin.superadmin.audit-logs') }}?user_type=admin&user_id={{ $admin->id }}" class="btn btn-info btn-sm">
                                <i class="fas fa-external-link-alt"></i> View All
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($activities->count() > 0)
                            <div class="timeline">
                                @foreach($activities as $activity)
                                    <div class="time-label">
                                        <span class="bg-info">{{ $activity->created_at->format('M d, Y') }}</span>
                                    </div>
                                    <div>
                                        <i class="fas fa-{{ $activity->action == 'login' ? 'sign-in-alt' : 'cog' }} bg-blue"></i>
                                        <div class="timeline-item">
                                            <span class="time"><i class="fas fa-clock"></i> {{ $activity->created_at->format('H:i') }}</span>
                                            <h3 class="timeline-header">
                                                <strong>{{ ucfirst($activity->action) }}</strong>
                                                @if($activity->description)
                                                    - {{ $activity->description }}
                                                @endif
                                            </h3>
                                            <div class="timeline-body">
                                                @if($activity->ip_address)
                                                    <small class="text-muted">IP: {{ $activity->ip_address }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                <div>
                                    <i class="fas fa-clock bg-gray"></i>
                                </div>
                            </div>
                        @else
                            <p class="text-center text-muted py-4">
                                <i class="fas fa-history fa-2x mb-2"></i><br>
                                No recent activity found
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Actions Sidebar -->
            <div class="col-md-4">
                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Quick Actions</h3>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            @if($admin->status == 'Active')
                                <button type="button" class="btn btn-warning btn-sm" onclick="changeStatus('{{ $admin->id }}', 'Inactive')">
                                    <i class="fas fa-pause"></i> Deactivate
                                </button>
                            @else
                                <button type="button" class="btn btn-success btn-sm" onclick="changeStatus('{{ $admin->id }}', 'Active')">
                                    <i class="fas fa-play"></i> Activate
                                </button>
                            @endif

                            @if($admin->status != 'Flagged')
                                <button type="button" class="btn btn-warning btn-sm" onclick="flagAdmin('{{ $admin->id }}')">
                                    <i class="fas fa-flag"></i> Flag Account
                                </button>
                            @endif

                            <button type="button" class="btn btn-info btn-sm" onclick="suspendAdmin('{{ $admin->id }}')">
                                <i class="fas fa-ban"></i> Suspend Account
                            </button>

                            <button type="button" class="btn btn-danger btn-sm" onclick="deleteAdmin('{{ $admin->id }}', '{{ $admin->name }}')">
                                <i class="fas fa-trash"></i> Delete Account
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Permissions -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Permissions</h3>
                    </div>
                    <div class="card-body">
                        @if($admin->permissions->count() > 0)
                            <ul class="list-group list-group-flush">
                                @foreach($admin->permissions as $permission)
                                    <li class="list-group-item px-0">
                                        <i class="fas fa-check text-success mr-2"></i>
                                        {{ $permission->display_name ?? $permission->name }}
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-muted">No specific permissions assigned</p>
                        @endif
                    </div>
                </div>

                <!-- Role Information -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Role Information</h3>
                    </div>
                    <div class="card-body">
                        @if($admin->roles->first())
                            <dl class="row">
                                <dt class="col-sm-5">Role:</dt>
                                <dd class="col-sm-7">{{ $admin->roles->first()->display_name ?? $admin->roles->first()->name }}</dd>

                                <dt class="col-sm-5">Assigned:</dt>
                                <dd class="col-sm-7">{{ $admin->roles->first()->pivot ? $admin->roles->first()->pivot->created_at->format('M d, Y') : 'N/A' }}</dd>
                            </dl>
                        @else
                            <p class="text-muted">No role assigned</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <!-- Flag Modal -->
    <div class="modal fade" id="flagModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Flag Admin Account</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="flagForm">
                    @csrf
                    <div class="modal-body">
                        <p>Are you sure you want to flag <strong>{{ $admin->name }}</strong>?</p>
                        <div class="form-group">
                            <label for="flagReason">Reason <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="flagReason" name="reason" rows="3" required placeholder="Please provide a reason for flagging..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Flag Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Suspend Modal -->
    <div class="modal fade" id="suspendModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Suspend Admin Account</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="suspendForm">
                    @csrf
                    <div class="modal-body">
                        <p>Are you sure you want to suspend <strong>{{ $admin->name }}</strong>?</p>
                        <div class="form-group">
                            <label for="suspendReason">Reason <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="suspendReason" name="reason" rows="3" required placeholder="Please provide a reason for suspension..."></textarea>
                        </div>
                        <div class="form-group">
                            <label for="suspendDuration">Duration (days)</label>
                            <input type="number" class="form-control" id="suspendDuration" name="duration" min="1" max="365" placeholder="Leave blank for indefinite">
                            <small class="form-text text-muted">Leave blank for indefinite suspension</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-info">Suspend Account</button>
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
                    <h5 class="modal-title">Delete Admin Account</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete <strong>{{ $admin->name }}</strong>?</p>
                    <p class="text-danger">This action cannot be undone and will permanently remove the admin account.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <form id="deleteForm" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete Account</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function changeStatus(adminId, status) {
    if (!confirm(`Are you sure you want to ${status.toLowerCase()} this admin account?`)) {
        return;
    }

    $.post(`{{ url('admin/superadmin/admins') }}/${adminId}/approve`, {
        _token: '{{ csrf_token() }}',
        status: status
    })
    .done(function(response) {
        location.reload();
    })
    .fail(function(xhr) {
        alert('Error: ' + xhr.responseJSON?.message || 'Failed to change status');
    });
}

function flagAdmin(adminId) {
    $('#flagModal').modal('show');
}

function suspendAdmin(adminId) {
    $('#suspendModal').modal('show');
}

function deleteAdmin(adminId, adminName) {
    $('#deleteForm').attr('action', `{{ url('admin/superadmin/admins') }}/${adminId}`);
    $('#deleteModal').modal('show');
}

// Form submissions
$('#flagForm').on('submit', function(e) {
    e.preventDefault();
    const reason = $('#flagReason').val();

    $.post(`{{ url('admin/superadmin/admins') }}/{{ $admin->id }}/flag`, {
        _token: '{{ csrf_token() }}',
        reason: reason
    })
    .done(function(response) {
        $('#flagModal').modal('hide');
        location.reload();
    })
    .fail(function(xhr) {
        alert('Error: ' + xhr.responseJSON?.message || 'Failed to flag admin');
    });
});

$('#suspendForm').on('submit', function(e) {
    e.preventDefault();
    const reason = $('#suspendReason').val();
    const duration = $('#suspendDuration').val();

    $.post(`{{ url('admin/superadmin/admins') }}/{{ $admin->id }}/suspend`, {
        _token: '{{ csrf_token() }}',
        reason: reason,
        duration: duration
    })
    .done(function(response) {
        $('#suspendModal').modal('hide');
        location.reload();
    })
    .fail(function(xhr) {
        alert('Error: ' + xhr.responseJSON?.message || 'Failed to suspend admin');
    });
});
</script>
@endpush
