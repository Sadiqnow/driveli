@extends('layouts.admin')

@section('title', 'Deactivation Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Deactivation Management</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.deactivation.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> New Deactivation Request
                        </a>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3>{{ $stats['pending_requests'] }}</h3>
                                    <p>Pending Requests</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3>{{ $stats['approved_today'] }}</h3>
                                    <p>Approved Today</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-check"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-danger">
                                <div class="inner">
                                    <h3>{{ $stats['total_deactivated_drivers'] }}</h3>
                                    <p>Deactivated Drivers</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-user-times"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3>{{ $stats['total_deactivated_companies'] }}</h3>
                                    <p>Deactivated Companies</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-building"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Requests -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Pending Deactivation Requests</h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>User Type</th>
                                <th>User</th>
                                <th>Reason</th>
                                <th>Requested By</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pendingRequests as $request)
                            <tr>
                                <td>{{ $request->id }}</td>
                                <td>
                                    <span class="badge badge-{{ $request->user_type === 'driver' ? 'primary' : 'info' }}">
                                        {{ ucfirst($request->user_type) }}
                                    </span>
                                </td>
                                <td>
                                    @if($request->user_type === 'driver')
                                        {{ $request->user->full_name ?? 'N/A' }}
                                    @else
                                        {{ $request->user->name ?? 'N/A' }}
                                    @endif
                                </td>
                                <td>{{ Str::limit($request->reason, 50) }}</td>
                                <td>{{ $request->requester->name ?? 'System' }}</td>
                                <td>{{ $request->created_at->diffForHumans() }}</td>
                                <td>
                                    <a href="{{ route('admin.deactivation.show', $request) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                    @can('review-deactivations')
                                    <form action="{{ route('admin.deactivation.review', $request) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-warning">
                                            <i class="fas fa-search"></i> Review
                                        </button>
                                    </form>
                                    @endcan
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center">No pending requests</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Auto refresh stats every 30 seconds
    setInterval(function() {
        $.get('{{ route("api.admin.deactivation.stats") }}', function(data) {
            if (data.success) {
                // Update statistics if needed
                console.log('Stats updated:', data.data);
            }
        });
    }, 30000);
});
</script>
@endsection
