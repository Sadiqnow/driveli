@extends('layouts.company')

@section('title', 'My Requests - Company Portal')

@section('page-title', 'My Requests')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('company.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">My Requests</li>
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-1">Driver Requests</h4>
                <p class="text-muted mb-0">Manage your driver recruitment requests</p>
            </div>
            <a href="{{ route('company.requests.create') }}" class="btn btn-company-primary">
                <i class="fas fa-plus me-2"></i>Create New Request
            </a>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="row mb-4">
    <div class="col-12">
        <div class="company-card">
            <div class="company-card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" name="search" id="search" class="form-control"
                               placeholder="Search by position title or description..."
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-outline-primary me-2">
                            <i class="fas fa-search me-1"></i>Filter
                        </button>
                        <a href="{{ route('company.requests.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>Clear
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Requests List -->
<div class="row">
    <div class="col-12">
        @if($requests->count() > 0)
            @foreach($requests as $request)
                <div class="company-card mb-3">
                    <div class="company-card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <div class="d-flex align-items-start">
                                    <div class="flex-grow-1">
                                        <h5 class="mb-1">
                                            <a href="#" class="text-decoration-none text-dark">
                                                {{ $request->position_title }}
                                            </a>
                                        </h5>
                                        <p class="text-muted mb-2">{{ Str::limit($request->description, 100) }}</p>
                                        <div class="row">
                                            <div class="col-sm-6">
                                                <small class="text-muted">
                                                    <i class="fas fa-map-marker-alt me-1"></i>{{ $request->location }}
                                                </small>
                                            </div>
                                            <div class="col-sm-6">
                                                <small class="text-muted">
                                                    <i class="fas fa-calendar me-1"></i>{{ $request->created_at->format('M d, Y') }}
                                                </small>
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <span class="badge badge-{{ $request->status_badge ?? 'secondary' }} me-2">
                                                {{ ucfirst($request->status) }}
                                            </span>
                                            @if($request->matches_count > 0)
                                                <span class="badge badge-info">
                                                    {{ $request->matches_count }} matches
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="btn-group" role="group">
                                    <a href="#" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye me-1"></i>View
                                    </a>
                                    @if($request->status === 'pending' || $request->status === 'active')
                                        <a href="#" class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-edit me-1"></i>Edit
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                                onclick="cancelRequest({{ $request->id }}, '{{ $request->position_title }}')">
                                            <i class="fas fa-times me-1"></i>Cancel
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Pagination -->
            <div class="d-flex justify-content-center">
                {{ $requests->appends(request()->query())->links() }}
            </div>
        @else
            <div class="company-card">
                <div class="company-card-body text-center py-5">
                    <i class="fas fa-clipboard-list fa-4x text-muted mb-4"></i>
                    <h4 class="text-muted mb-3">No requests found</h4>
                    <p class="text-muted mb-4">
                        @if(request()->hasAny(['status', 'search']))
                            No requests match your current filters. Try adjusting your search criteria.
                        @else
                            You haven't created any driver requests yet. Start by creating your first request.
                        @endif
                    </p>
                    <a href="{{ route('company.requests.create') }}" class="btn btn-company-primary">
                        <i class="fas fa-plus me-2"></i>Create Your First Request
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Cancel Request Modal -->
<div class="modal fade" id="cancelRequestModal" tabindex="-1" aria-labelledby="cancelRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cancelRequestModalLabel">Cancel Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="cancelRequestForm" method="POST">
                @csrf
                <div class="modal-body">
                    <p>Are you sure you want to cancel the request for <strong id="cancelRequestTitle"></strong>?</p>
                    <div class="mb-3">
                        <label for="cancellation_reason" class="form-label">Cancellation Reason <span class="text-danger">*</span></label>
                        <textarea name="cancellation_reason" id="cancellation_reason" class="form-control" rows="3"
                                  placeholder="Please provide a reason for cancelling this request..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Keep Request</button>
                    <button type="submit" class="btn btn-danger">Cancel Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('js')
<script>
function cancelRequest(requestId, title) {
    document.getElementById('cancelRequestTitle').textContent = title;
    document.getElementById('cancelRequestForm').action = `/company/requests/${requestId}/cancel`;
    new bootstrap.Modal(document.getElementById('cancelRequestModal')).show();
}
</script>
@endsection
