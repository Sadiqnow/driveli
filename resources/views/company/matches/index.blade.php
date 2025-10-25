@extends('company.layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0"><i class="bi bi-hand-thumbs-up"></i> Driver Matches</h2>
            <a href="{{ route('company.requests.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> New Request
            </a>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="accepted" {{ request('status') == 'accepted' ? 'selected' : '' }}>Accepted</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="negotiating" {{ request('status') == 'negotiating' ? 'selected' : '' }}>Negotiating</option>
                </select>
            </div>

            <div class="col-md-3">
                <label for="date_from" class="form-label">From Date</label>
                <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
            </div>

            <div class="col-md-3">
                <label for="date_to" class="form-label">To Date</label>
                <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
            </div>

            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="bi bi-search"></i> Filter
                </button>
                <a href="{{ route('company.matches.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> Clear
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Matches List -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Your Driver Matches</h5>
    </div>
    <div class="card-body">
        @if($matches->count() > 0)
            <div class="row">
                @foreach($matches as $match)
                    <div class="col-md-6 mb-4">
                        <div class="card h-100 border-left-{{ $match->status === 'accepted' ? 'success' : ($match->status === 'pending' ? 'warning' : 'secondary') }}">
                            <div class="card-header bg-light">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">{{ $match->companyRequest->request_id }}</h6>
                                    <span class="badge bg-{{ $match->status === 'accepted' ? 'success' : ($match->status === 'pending' ? 'warning' : 'secondary') }}">
                                        {{ ucfirst($match->status) }}
                                    </span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6>Route</h6>
                                        <p class="mb-1">
                                            <i class="bi bi-geo-alt"></i> {{ $match->companyRequest->pickup_location }}
                                        </p>
                                        <p class="mb-1">
                                            <i class="bi bi-geo-alt-fill"></i> {{ $match->companyRequest->dropoff_location ?: 'Not specified' }}
                                        </p>
                                        <p class="mb-0 text-muted">
                                            <small>{{ $match->companyRequest->vehicle_type }} • {{ ucfirst($match->companyRequest->urgency) }}</small>
                                        </p>
                                    </div>
                                    <div class="col-md-6">
                                        <h6>Driver Details</h6>
                                        <p class="mb-1">
                                            <strong>{{ $match->driver->name ?? 'N/A' }}</strong>
                                        </p>
                                        <p class="mb-1">
                                            <i class="bi bi-star-fill text-warning"></i> {{ number_format($match->driver->rating ?? 0, 1) }} rating
                                        </p>
                                        <p class="mb-0">
                                            <small>{{ $match->driver->experience_years ?? 0 }} years experience</small>
                                        </p>
                                    </div>
                                </div>

                                <hr>

                                <div class="row">
                                    <div class="col-md-6">
                                        <h6>Match Score</h6>
                                        <div class="progress mb-2">
                                            <div class="progress-bar bg-success" role="progressbar"
                                                 style="width: {{ $match->match_score }}%"
                                                 aria-valuenow="{{ $match->match_score }}"
                                                 aria-valuemin="0" aria-valuemax="100">
                                                {{ $match->match_score }}%
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <h6>Proposed Rate</h6>
                                        <p class="mb-0 text-success fw-bold">
                                            ₦{{ number_format($match->proposed_rate ?? 0, 2) }}
                                        </p>
                                        @if($match->agreed_rate)
                                            <p class="mb-0 text-muted">
                                                <small>Agreed: ₦{{ number_format($match->agreed_rate, 2) }}</small>
                                            </p>
                                        @endif
                                    </div>
                                </div>

                                @if($match->notes)
                                    <div class="mt-3">
                                        <h6>Notes</h6>
                                        <p class="mb-0 text-muted">{{ $match->notes }}</p>
                                    </div>
                                @endif
                            </div>
                            <div class="card-footer">
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('company.matches.show', $match) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-eye"></i> View Details
                                    </a>

                                    @if($match->status === 'pending')
                                        <div>
                                            <button type="button" class="btn btn-success btn-sm me-2"
                                                    onclick="acceptMatch({{ $match->id }})">
                                                <i class="bi bi-check-circle"></i> Accept
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm"
                                                    onclick="rejectMatch({{ $match->id }})">
                                                <i class="bi bi-x-circle"></i> Reject
                                            </button>
                                        </div>
                                    @elseif($match->status === 'negotiating')
                                        <button type="button" class="btn btn-warning btn-sm"
                                                onclick="negotiateMatch({{ $match->id }})">
                                            <i class="bi bi-chat-dots"></i> Negotiate
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center">
                {{ $matches->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-hand-thumbs-up" style="font-size: 3rem; color: #6c757d;"></i>
                <h5 class="mt-3">No Driver Matches Found</h5>
                <p class="text-muted">Create a transport request to start receiving driver matches.</p>
                <a href="{{ route('company.requests.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Create Request
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function acceptMatch(matchId) {
    const agreedRate = prompt('Enter agreed rate (₦):');
    if (agreedRate && !isNaN(agreedRate)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/company/matches/${matchId}/accept`;

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken;
        form.appendChild(csrfInput);

        const rateInput = document.createElement('input');
        rateInput.type = 'hidden';
        rateInput.name = 'agreed_rate';
        rateInput.value = agreedRate;
        form.appendChild(rateInput);

        document.body.appendChild(form);
        form.submit();
    }
}

function rejectMatch(matchId) {
    const reason = prompt('Reason for rejection:');
    if (reason) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/company/matches/${matchId}/reject`;

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken;
        form.appendChild(csrfInput);

        const reasonInput = document.createElement('input');
        reasonInput.type = 'hidden';
        reasonInput.name = 'reason';
        reasonInput.value = reason;
        form.appendChild(reasonInput);

        document.body.appendChild(form);
        form.submit();
    }
}

function negotiateMatch(matchId) {
    const proposedRate = prompt('Enter your proposed rate (₦):');
    const message = prompt('Enter negotiation message:');

    if (proposedRate && message) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/company/matches/${matchId}/negotiate`;

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken;
        form.appendChild(csrfInput);

        const rateInput = document.createElement('input');
        rateInput.type = 'hidden';
        rateInput.name = 'proposed_rate';
        rateInput.value = proposedRate;
        form.appendChild(rateInput);

        const messageInput = document.createElement('input');
        messageInput.type = 'hidden';
        messageInput.name = 'message';
        messageInput.value = message;
        form.appendChild(messageInput);

        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endpush
@endsection
