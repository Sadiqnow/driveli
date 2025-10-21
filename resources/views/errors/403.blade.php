@extends('layouts.admin_master')

@section('title', 'Access Denied - Insufficient Permissions')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card card-danger">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-shield-alt"></i> Access Denied
                    </h3>
                </div>
                <div class="card-body text-center">
                    <div class="mb-4">
                        <i class="fas fa-exclamation-triangle text-danger" style="font-size: 4rem;"></i>
                    </div>
                    <h1 class="display-4 text-danger">403</h1>
                    <h3 class="mb-3">Access Denied</h3>
                    <p class="mb-4 text-muted">
                        You don't have sufficient permissions to access this resource.
                        This attempt has been logged for security monitoring.
                    </p>

                    @if(auth('admin')->check())
                        <div class="alert alert-info">
                            <strong>Logged in as:</strong> {{ auth('admin')->user()->name ?? auth('admin')->user()->email }}<br>
                            <strong>Role:</strong> {{ auth('admin')->user()->role ?? 'Not assigned' }}<br>
                            <small class="text-muted">Time: {{ now()->format('Y-m-d H:i:s') }}</small>
                        </div>
                    @endif

                    <div class="mb-4">
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-primary mr-2">
                            <i class="fas fa-home"></i> Go to Dashboard
                        </a>
                        <button onclick="history.back()" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Go Back
                        </button>
                    </div>

                    <div class="text-left">
                        <h5>What can you do?</h5>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check-circle text-success"></i> Contact your administrator to request access</li>
                            <li><i class="fas fa-check-circle text-success"></i> Check if you have the correct role assigned</li>
                            <li><i class="fas fa-check-circle text-success"></i> Verify your account permissions</li>
                        </ul>
                    </div>
                </div>
                <div class="card-footer text-muted">
                    <small>
                        If you believe this is an error, please contact the system administrator.
                        Reference ID: {{ session('error_ref', uniqid('ERR_')) }}
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-log the access attempt for additional security monitoring
document.addEventListener('DOMContentLoaded', function() {
    // Log additional client-side information if needed
    console.log('Access denied page loaded for user:', '{{ auth('admin')->id() ?? 'guest' }}');
});
</script>
@endsection
