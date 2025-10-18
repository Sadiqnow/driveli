@extends('layouts.superadmin_master')

@section('title', 'Driver Audit Trail')

@section('content')
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Driver Audit Trail</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.superadmin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.superadmin.drivers.index') }}">Drivers</a></li>
                        <li class="breadcrumb-item active">Audit Trail</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Filters -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Audit Filters</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.superadmin.drivers.audit') }}" class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Date From</label>
                                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Date To</label>
                                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Admin</label>
                                <select name="admin_id" class="form-control">
                                    <option value="">All Admins</option>
                                    @foreach($admins ?? [] as $admin)
                                        <option value="{{ $admin->id }}" {{ request('admin_id') == $admin->id ? 'selected' : '' }}>
                                            {{ $admin->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Action</label>
                                <select name="action" class="form-control">
                                    <option value="">All Actions</option>
                                    <option value="verified" {{ request('action') == 'verified' ? 'selected' : '' }}>Verified</option>
                                    <option value="rejected" {{ request('action') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                    <option value="updated" {{ request('action') == 'updated' ? 'selected' : '' }}>Updated</option>
                                    <option value="created" {{ request('action') == 'created' ? 'selected' : '' }}>Created</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="{{ route('admin.superadmin.drivers.audit') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Clear Filters
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Audit Logs -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Audit Logs</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    @if($auditLogs->count() > 0)
                        <table class="table table-hover text-nowrap">
                            <thead>
                                <tr>
                                    <th>Date/Time</th>
                                    <th>Driver</th>
                                    <th>Action</th>
                                    <th>Admin</th>
                                    <th>Details</th>
                                    <th>IP Address</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($auditLogs as $log)
                                    <tr>
                                        <td>{{ $log->updated_at->format('Y-m-d H:i:s') }}</td>
                                        <td>
                                            <a href="{{ route('admin.superadmin.drivers.show', $log->id) }}">
                                                {{ $log->first_name }} {{ $log->surname }}
                                            </a>
                                            <br>
                                            <small class="text-muted">{{ $log->driver_id }}</small>
                                        </td>
                                        <td>
                                            @if($log->verification_status == 'verified')
                                                <span class="badge badge-success">Verified</span>
                                            @elseif($log->verification_status == 'rejected')
                                                <span class="badge badge-danger">Rejected</span>
                                            @elseif($log->verification_status == 'pending')
                                                <span class="badge badge-warning">Reset to Pending</span>
                                            @else
                                                <span class="badge badge-info">Updated</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($log->verifiedBy)
                                                {{ $log->verifiedBy->name }}
                                            @else
                                                System
                                            @endif
                                        </td>
                                        <td>
                                            @if($log->verification_notes)
                                                <span title="{{ $log->verification_notes }}">
                                                    {{ Str::limit($log->verification_notes, 50) }}
                                                </span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            <small class="text-muted">-</small>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                            <p class="text-muted">No audit logs found</p>
                        </div>
                    @endif
                </div>
                @if($auditLogs->hasPages())
                    <div class="card-footer">
                        {{ $auditLogs->links() }}
                    </div>
                @endif
            </div>
        </div>
    </section>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Auto-submit form on filter change
    $('select[name="admin_id"], select[name="action"]').on('change', function() {
        $(this).closest('form').submit();
    });
});
</script>
@endsection
