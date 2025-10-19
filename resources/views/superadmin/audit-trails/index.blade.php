@extends('layouts.superadmin')

@section('title', 'Audit Trails')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Audit Trails</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.superadmin.audit-trails.export.csv', request()->query()) }}" class="btn btn-sm btn-success">
                            <i class="fas fa-download"></i> Export CSV
                        </a>
                        <a href="{{ route('admin.superadmin.audit-trails.export.pdf', request()->query()) }}" class="btn btn-sm btn-danger">
                            <i class="fas fa-file-pdf"></i> Export PDF
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Filters -->
                    <form method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-2">
                                <select name="action_type" class="form-control">
                                    <option value="">All Actions</option>
                                    <option value="assign" {{ request('action_type') == 'assign' ? 'selected' : '' }}>Assign</option>
                                    <option value="revoke" {{ request('action_type') == 'revoke' ? 'selected' : '' }}>Revoke</option>
                                    <option value="update" {{ request('action_type') == 'update' ? 'selected' : '' }}>Update</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="user_id" class="form-control">
                                    <option value="">All Users</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="target_user_id" class="form-control">
                                    <option value="">All Target Users</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ request('target_user_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="role_id" class="form-control">
                                    <option value="">All Roles</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>
                                            {{ $role->display_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}" placeholder="Start Date">
                            </div>
                            <div class="col-md-2">
                                <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}" placeholder="End Date">
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                                <a href="{{ route('admin.superadmin.audit-trails.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-times"></i> Clear
                                </a>
                            </div>
                        </div>
                    </form>

                    <!-- Audit Trails Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Action Type</th>
                                    <th>User</th>
                                    <th>Role</th>
                                    <th>Target User</th>
                                    <th>Description</th>
                                    <th>IP Address</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($auditTrails as $audit)
                                    <tr>
                                        <td>{{ $audit->id }}</td>
                                        <td>
                                            <span class="badge badge-{{ $audit->action_type == 'assign' ? 'success' : ($audit->action_type == 'revoke' ? 'danger' : 'warning') }}">
                                                {{ ucfirst($audit->action_type) }}
                                            </span>
                                        </td>
                                        <td>{{ $audit->user ? $audit->user->name : 'N/A' }}</td>
                                        <td>{{ $audit->role ? $audit->role->display_name : 'N/A' }}</td>
                                        <td>{{ $audit->targetUser ? $audit->targetUser->name : 'N/A' }}</td>
                                        <td>{{ Str::limit($audit->description, 50) }}</td>
                                        <td>{{ $audit->ip_address }}</td>
                                        <td>{{ $audit->created_at->format('M d, Y H:i') }}</td>
                                        <td>
                                            <a href="{{ route('admin.superadmin.audit-trails.show', $audit) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">No audit trails found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    {{ $auditTrails->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Auto-submit form on filter change
    $('select[name="action_type"], select[name="user_id"], select[name="target_user_id"], select[name="role_id"]').change(function() {
        $(this).closest('form').submit();
    });
});
</script>
@endsection
