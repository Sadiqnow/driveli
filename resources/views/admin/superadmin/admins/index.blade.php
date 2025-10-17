@extends('layouts.admin_master')

@section('title', 'SuperAdmin - Admin Management')

@section('content_header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Admin Management</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.superadmin.dashboard') }}">Super Admin</a></li>
                        <li class="breadcrumb-item active">Admins</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid">
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-lg-2 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $admins->total() }}</h3>
                        <p>Total Admins</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-users-cog"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $admins->where('status', 'Active')->count() }}</h3>
                        <p>Active</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-check"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $admins->where('status', 'Inactive')->count() }}</h3>
                        <p>Inactive</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-user-times"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ $admins->where('status', 'Suspended')->count() }}</h3>
                        <p>Suspended</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-ban"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-6">
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h3>{{ $admins->where('status', 'Rejected')->count() }}</h3>
                        <p>Rejected</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-6">
                <div class="small-box bg-secondary">
                    <div class="inner">
                        <h3>{{ $admins->where('status', 'Flagged')->count() }}</h3>
                        <p>Flagged</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-flag"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters and Actions -->
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-6">
                        <h3 class="card-title">Admins</h3>
                    </div>
                    <div class="col-md-6 text-right">
                        <a href="{{ route('admin.superadmin.admins.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Admin
                        </a>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card-header">
                <form method="GET" class="form-inline">
                    <div class="form-group mr-3">
                        <input type="text" name="search" class="form-control" placeholder="Search admins..." value="{{ request('search') }}">
                    </div>
                    <div class="form-group mr-3">
                        <select name="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="Active" {{ request('status') == 'Active' ? 'selected' : '' }}>Active</option>
                            <option value="Inactive" {{ request('status') == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                            <option value="Suspended" {{ request('status') == 'Suspended' ? 'selected' : '' }}>Suspended</option>
                            <option value="Rejected" {{ request('status') == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                            <option value="Flagged" {{ request('status') == 'Flagged' ? 'selected' : '' }}>Flagged</option>
                        </select>
                    </div>
                    <div class="form-group mr-3">
                        <select name="role" class="form-control">
                            <option value="">All Roles</option>
                            <option value="Super Admin" {{ request('role') == 'Super Admin' ? 'selected' : '' }}>Super Admin</option>
                            <option value="Admin" {{ request('role') == 'Admin' ? 'selected' : '' }}>Admin</option>
                            <option value="Moderator" {{ request('role') == 'Moderator' ? 'selected' : '' }}>Moderator</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-secondary mr-2">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="{{ route('admin.superadmin.admins.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </form>
            </div>

            <!-- Bulk Actions -->
            <div class="card-header">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="select-all">
                            <label class="form-check-label" for="select-all">Select All</label>
                        </div>
                    </div>
                    <div class="col-md-6 text-right">
                        <div class="btn-group" id="bulk-actions" style="display: none;">
                            <button type="button" class="btn btn-success btn-sm" onclick="bulkAction('activate')">
                                <i class="fas fa-check"></i> Bulk Activate
                            </button>
                            <button type="button" class="btn btn-warning btn-sm" onclick="bulkAction('deactivate')">
                                <i class="fas fa-ban"></i> Bulk Deactivate
                            </button>
                            <button type="button" class="btn btn-danger btn-sm" onclick="bulkAction('delete')">
                                <i class="fas fa-trash"></i> Bulk Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Admins Table -->
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th width="30">
                                <input type="checkbox" id="select-all-header">
                            </th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($admins as $admin)
                            <tr>
                                <td>
                                    <input type="checkbox" class="admin-checkbox" value="{{ $admin->id }}">
                                </td>
                                <td>{{ $admin->name }}</td>
                                <td>{{ $admin->email }}</td>
                                <td>{{ $admin->phone }}</td>
                                <td>
                                    <span class="badge badge-{{ $admin->role == 'Super Admin' ? 'danger' : ($admin->role == 'Admin' ? 'primary' : 'warning') }}">
                                        {{ $admin->role }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $admin->status == 'Active' ? 'success' : ($admin->status == 'Inactive' ? 'warning' : 'danger') }}">
                                        {{ $admin->status }}
                                    </span>
                                </td>
                                <td>{{ $admin->created_at->format('M d, Y') }}</td>
                                <td>
                                    <div class="btn-group">
                                        <a href="{{ route('admin.superadmin.admins.show', $admin) }}" class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.superadmin.admins.edit', $admin) }}" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-secondary btn-sm dropdown-toggle" data-toggle="dropdown">
                                                <i class="fas fa-cogs"></i>
                                            </button>
                                            <div class="dropdown-menu">
                                                @if($admin->status == 'Active')
                                                    <a class="dropdown-item" href="#" onclick="suspendAdmin({{ $admin->id }})">
                                                        <i class="fas fa-ban text-warning"></i> Suspend
                                                    </a>
                                                    <a class="dropdown-item" href="#" onclick="flagAdmin({{ $admin->id }})">
                                                        <i class="fas fa-flag text-danger"></i> Flag
                                                    </a>
                                                @elseif($admin->status == 'Suspended')
                                                    <a class="dropdown-item" href="#" onclick="approveAdmin({{ $admin->id }})">
                                                        <i class="fas fa-check text-success"></i> Approve
                                                    </a>
                                                @elseif($admin->status == 'Flagged')
                                                    <a class="dropdown-item" href="#" onclick="approveAdmin({{ $admin->id }})">
                                                        <i class="fas fa-undo text-info"></i> Restore
                                                    </a>
                                                @else
                                                    <a class="dropdown-item" href="#" onclick="approveAdmin({{ $admin->id }})">
                                                        <i class="fas fa-check text-success"></i> Approve
                                                    </a>
                                                @endif
                                                <div class="dropdown-divider"></div>
                                                <a class="dropdown-item text-danger" href="#" onclick="deleteAdmin({{ $admin->id }}, '{{ $admin->name }}')">
                                                    <i class="fas fa-trash"></i> Delete
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="fas fa-users fa-2x text-muted mb-2"></i>
                                    <p class="text-muted">No admins found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($admins->hasPages())
                <div class="card-footer">
                    {{ $admins->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Modals -->
    <!-- Approve Modal -->
    <div class="modal fade" id="approveModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Approve Admin</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="approveForm">
                    @csrf
                    <div class="modal-body">
                        <p>Are you sure you want to approve <strong id="approveAdminName"></strong>?</p>
                        <div class="form-group">
                            <label for="approvalNotes">Approval Notes (Optional)</label>
                            <textarea class="form-control" id="approvalNotes" name="notes" rows="3" placeholder="Add any notes about this approval..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Approve Admin</button>
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
                    <h5 class="modal-title">Suspend Admin</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="suspendForm">
                    @csrf
                    <div class="modal-body">
                        <p>Are you sure you want to suspend <strong id="suspendAdminName"></strong>?</p>
                        <div class="form-group">
                            <label for="suspensionReason">Suspension Reason <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="suspensionReason" name="reason" rows="3" required placeholder="Please provide a reason for suspension..."></textarea>
                        </div>
                        <div class="form-group">
                            <label for="suspensionDuration">Duration (days)</label>
                            <input type="number" class="form-control" id="suspensionDuration" name="duration" min="1" max="365" placeholder="Leave empty for indefinite">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Suspend Admin</button>
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
                    <h5 class="modal-title">Flag Admin</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="flagForm">
                    @csrf
                    <div class="modal-body">
                        <p>Are you sure you want to flag <strong id="flagAdminName"></strong>?</p>
                        <div class="form-group">
                            <label for="flagReason">Flag Reason <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="flagReason" name="reason" rows="3" required placeholder="Please provide a reason for flagging..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Flag Admin</button>
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
                    <h5 class="modal-title">Delete Admin</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete <strong id="deleteAdminName"></strong>?</p>
                    <p class="text-danger">This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <form id="deleteForm" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Delete Admin</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
.table th {
    border-top: none;
    font-weight: 600;
    font-size: 0.875rem;
}

.badge {
    font-size: 0.75rem;
}

.btn-group .btn {
    margin-right: 2px;
}

.dropdown-menu {
    min-width: 150px;
}

.modal-body p {
    margin-bottom: 1rem;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Select all functionality
    $('#select-all, #select-all-header').on('change', function() {
        $('.admin-checkbox').prop('checked', $(this).prop('checked'));
        toggleBulkActions();
    });

    $('.admin-checkbox').on('change', function() {
        toggleBulkActions();
        updateSelectAllState();
    });

    function toggleBulkActions() {
        const checkedBoxes = $('.admin-checkbox:checked').length;
        $('#bulk-actions').toggle(checkedBoxes > 0);
    }

    function updateSelectAllState() {
        const totalBoxes = $('.admin-checkbox').length;
        const checkedBoxes = $('.admin-checkbox:checked').length;
        $('#select-all, #select-all-header').prop('checked', totalBoxes > 0 && checkedBoxes === totalBoxes);
    }
});

// Modal functions
let currentAdminId = null;

function approveAdmin(adminId) {
    // Get admin name via AJAX or from data attribute
    $('#approveAdminName').text('this admin');
    currentAdminId = adminId;
    $('#approveModal').modal('show');
}

function suspendAdmin(adminId) {
    $('#suspendAdminName').text('this admin');
    currentAdminId = adminId;
    $('#suspendModal').modal('show');
}

function flagAdmin(adminId) {
    $('#flagAdminName').text('this admin');
    currentAdminId = adminId;
    $('#flagModal').modal('show');
}

function deleteAdmin(adminId, adminName) {
    $('#deleteAdminName').text(adminName);
    $('#deleteForm').attr('action', `{{ url('admin/superadmin/admins') }}/${adminId}`);
    $('#deleteModal').modal('show');
}

// Form submissions
$('#approveForm').on('submit', function(e) {
    e.preventDefault();
    const notes = $('#approvalNotes').val();

    $.post(`{{ url('admin/superadmin/admins') }}/${currentAdminId}/approve`, {
        _token: '{{ csrf_token() }}',
        notes: notes
    })
    .done(function(response) {
        $('#approveModal').modal('hide');
        location.reload();
    })
    .fail(function(xhr) {
        alert('Error: ' + xhr.responseJSON?.message || 'Failed to approve admin');
    });
});

$('#suspendForm').on('submit', function(e) {
    e.preventDefault();
    const reason = $('#suspensionReason').val();
    const duration = $('#suspensionDuration').val();

    $.post(`{{ url('admin/superadmin/admins') }}/${currentAdminId}/suspend`, {
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

$('#flagForm').on('submit', function(e) {
    e.preventDefault();
    const reason = $('#flagReason').val();

    $.post(`{{ url('admin/superadmin/admins') }}/${currentAdminId}/flag`, {
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

// Bulk actions
function bulkAction(action) {
    const selectedIds = $('.admin-checkbox:checked').map(function() {
        return $(this).val();
    }).get();

    if (selectedIds.length === 0) {
        alert('Please select at least one admin');
        return;
    }

    if (!confirm(`Are you sure you want to ${action} ${selectedIds.length} admin(s)?`)) {
        return;
    }

    // Show loading
    const btn = event.target;
    const originalHtml = $(btn).html();
    $(btn).html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);

    let url, data = {
        _token: '{{ csrf_token() }}',
        admin_ids: selectedIds
    };

    switch(action) {
        case 'activate':
            url = '{{ route("admin.superadmin.admins.bulk-activate") }}';
            break;
        case 'deactivate':
            url = '{{ route("admin.superadmin.admins.bulk-deactivate") }}';
            break;
        case 'delete':
            url = '{{ route("admin.superadmin.admins.bulk-delete") }}';
            break;
    }

    $.post(url, data)
    .done(function(response) {
        location.reload();
    })
    .fail(function(xhr) {
        alert('Error: ' + xhr.responseJSON?.message || `Failed to bulk ${action} admins`);
    })
    .always(function() {
        $(btn).html(originalHtml).prop('disabled', false);
    });
}
</script>
@endpush
