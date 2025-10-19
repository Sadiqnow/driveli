@extends('layouts.superadmin')

@section('title', 'Audit Trail Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Audit Trail Details</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.superadmin.audit-trails.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th>ID</th>
                                    <td>{{ $auditTrail->id }}</td>
                                </tr>
                                <tr>
                                    <th>Action Type</th>
                                    <td>
                                        <span class="badge badge-{{ $auditTrail->action_type == 'assign' ? 'success' : ($auditTrail->action_type == 'revoke' ? 'danger' : 'warning') }}">
                                            {{ ucfirst($auditTrail->action_type) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>User</th>
                                    <td>{{ $auditTrail->user ? $auditTrail->user->name : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Role</th>
                                    <td>{{ $auditTrail->role ? $auditTrail->role->display_name : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Target User</th>
                                    <td>{{ $auditTrail->targetUser ? $auditTrail->targetUser->name : 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th>IP Address</th>
                                    <td>{{ $auditTrail->ip_address }}</td>
                                </tr>
                                <tr>
                                    <th>Created At</th>
                                    <td>{{ $auditTrail->created_at->format('M d, Y H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>Updated At</th>
                                    <td>{{ $auditTrail->updated_at->format('M d, Y H:i:s') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5>Description</h5>
                                </div>
                                <div class="card-body">
                                    <p>{{ $auditTrail->description }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
