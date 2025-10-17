@extends('layouts.admin_master')

@section('title', 'Audit Logs')

@section('content_header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Audit Logs</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.superadmin.index') }}">Super Admin</a></li>
                        <li class="breadcrumb-item active">Audit Logs</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid">
        <!-- Filters -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Filter Audit Logs</h3>
            </div>
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label for="action" class="form-label">Action</label>
                        <select name="action" id="action" class="form-control">
                            <option value="">All Actions</option>
                            <option value="created" {{ request('action') == 'created' ? 'selected' : '' }}>Created</option>
                            <option value="updated" {{ request('action') == 'updated' ? 'selected' : '' }}>Updated</option>
                            <option value="deleted" {{ request('action') == 'deleted' ? 'selected' : '' }}>Deleted</option>
                            <option value="login" {{ request('action') == 'login' ? 'selected' : '' }}>Login</option>
                            <option value="logout" {{ request('action') == 'logout' ? 'selected' : '' }}>Logout</option>
                            <option value="verified" {{ request('action') == 'verified' ? 'selected' : '' }}>Verified</option>
                            <option value="rejected" {{ request('action') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="user_type" class="form-label">User Type</label>
                        <select name="user_type" id="user_type" class="form-control">
                            <option value="">All Users</option>
                            <option value="App\Models\AdminUser" {{ request('user_type') == 'App\Models\AdminUser' ? 'selected' : '' }}>Admin Users</option>
                            <option value="App\Models\User" {{ request('user_type') == 'App\Models\User' ? 'selected' : '' }}>Regular Users</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="date_from" class="form-label">From Date</label>
                        <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="date_to" class="form-label">To Date</label>
                        <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        <a href="{{ route('admin.superadmin.audit-logs') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Clear Filters
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Audit Logs Table -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Activity Logs</h3>
                <div class="card-tools">
                    <span class="badge badge-info">{{ $activities->total() }} total activities</span>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Action</th>
                            <th>Description</th>
                            <th>IP Address</th>
                            <th>Timestamp</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activities as $activity)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="text-avatar mr-2" style="width: 32px; height: 32px; border-radius: 50%; background: #007bff; color: white; display: flex; align-items: center; justify-content: center; font-weight: bold;">
                                        {{ substr($activity->user ? $activity->user->name : 'U', 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="font-weight-bold">{{ $activity->user ? $activity->user->name : 'Unknown User' }}</div>
                                        <small class="text-muted">{{ class_basename($activity->user_type) }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-{{ \App\Services\ActivityLogger::getActionColor($activity->action) }}">
                                    <i class="{{ \App\Services\ActivityLogger::getActionIcon($activity->action) }} mr-1"></i>
                                    {{ ucfirst($activity->action) }}
                                </span>
                            </td>
                            <td>{{ $activity->description }}</td>
                            <td>
                                <code>{{ $activity->ip_address }}</code>
                            </td>
                            <td>
                                <span title="{{ $activity->created_at->format('Y-m-d H:i:s') }}">
                                    {{ $activity->created_at->diffForHumans() }}
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-info" onclick="showActivityDetails({{ $activity->id }})">
                                    <i class="fas fa-eye"></i> View
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                <p class="text-muted">No audit logs found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer">
                {{ $activities->appends(request()->query())->links() }}
            </div>
        </div>
    </div>

    <!-- Activity Details Modal -->
    <div class="modal fade" id="activityDetailsModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Activity Details</h4>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="activityDetailsContent">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function showActivityDetails(activityId) {
    // This would typically make an AJAX call to get detailed activity info
    // For now, we'll show a placeholder
    $('#activityDetailsContent').html(`
        <div class="text-center">
            <i class="fas fa-spinner fa-spin fa-2x"></i>
            <p>Loading activity details...</p>
        </div>
    `);

    $('#activityDetailsModal').modal('show');

    // Simulate loading (replace with actual AJAX call)
    setTimeout(() => {
        $('#activityDetailsContent').html(`
            <div class="row">
                <div class="col-md-6">
                    <h5>Basic Information</h5>
                    <table class="table table-sm">
                        <tr><td><strong>Activity ID:</strong></td><td>${activityId}</td></tr>
                        <tr><td><strong>Action:</strong></td><td>Sample Action</td></tr>
                        <tr><td><strong>Timestamp:</strong></td><td>2024-01-01 12:00:00</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h5>Additional Data</h5>
                    <pre class="bg-light p-2 rounded"><code>{
  "user_id": 1,
  "ip_address": "192.168.1.1",
  "user_agent": "Mozilla/5.0..."
}</code></pre>
                </div>
            </div>
        `);
    }, 500);
}

$(document).ready(function() {
    // Auto-refresh functionality could be added here
    console.log('Audit Logs page loaded');
});
</script>
@endpush
