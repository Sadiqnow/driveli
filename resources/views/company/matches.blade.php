@extends('company.layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="mb-0"><i class="bi bi-hand-thumbs-up"></i> Driver Matches</h2>
            <a href="{{ route('company.requests.create') }}" class="btn btn-primary" aria-label="Create new transport request">
                <i class="bi bi-plus-circle" aria-hidden="true"></i> New Request
            </a>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <x-ui.stats-widget
            title="Total Matches"
            :value="$stats['total'] ?? 0"
            icon="bi bi-hand-thumbs-up"
            variant="info"
            href="{{ route('company.matches.index') }}"
            link-text="View All"
        />
    </div>
    <div class="col-md-3 mb-3">
        <x-ui.stats-widget
            title="Pending"
            :value="$stats['pending'] ?? 0"
            icon="bi bi-clock"
            variant="warning"
        />
    </div>
    <div class="col-md-3 mb-3">
        <x-ui.stats-widget
            title="Accepted"
            :value="$stats['accepted'] ?? 0"
            icon="bi bi-check-circle"
            variant="success"
        />
    </div>
    <div class="col-md-3 mb-3">
        <x-ui.stats-widget
            title="Rejected"
            :value="$stats['rejected'] ?? 0"
            icon="bi bi-x-circle"
            variant="danger"
        />
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-funnel"></i> Filters</h5>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3" role="search" aria-label="Filter driver matches">
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
                <button type="submit" class="btn btn-primary me-2" aria-label="Apply filters">
                    <i class="bi bi-search" aria-hidden="true"></i> Filter
                </button>
                <a href="{{ route('company.matches.index') }}" class="btn btn-outline-secondary" aria-label="Clear all filters">
                    <i class="bi bi-x-circle" aria-hidden="true"></i> Clear
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Matches Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Your Driver Matches</h5>
    </div>
    <div class="card-body">
        @if(isset($matches) && $matches->count() > 0)
            <x-ui.table
                :headers="[
                    ['label' => 'Request ID', 'sortable' => true],
                    ['label' => 'Driver Name', 'sortable' => true],
                    ['label' => 'Match Score'],
                    ['label' => 'Proposed Rate'],
                    ['label' => 'Status'],
                    ['label' => 'Created'],
                    ['label' => 'Actions']
                ]"
                :data="$matches->map(function($match) {
                    return [
                        'request_id' => $match->companyRequest->request_id ?? 'N/A',
                        'driver_name' => $match->driver->name ?? 'N/A',
                        'match_score' => $match->match_score . '%',
                        'proposed_rate' => '₦' . number_format($match->proposed_rate ?? 0, 2),
                        'status' => ucfirst($match->status),
                        'created_at' => $match->created_at->format('M d, Y')
                    ];
                })"
                :actions="[
                    [
                        'text' => 'View',
                        'icon' => 'bi bi-eye',
                        'class' => 'btn-outline-primary',
                        'data' => ['action' => 'view', 'id' => 'match_id']
                    ],
                    [
                        'text' => 'Accept',
                        'icon' => 'bi bi-check-circle',
                        'class' => 'btn-outline-success',
                        'data' => ['action' => 'accept', 'id' => 'match_id']
                    ],
                    [
                        'text' => 'Reject',
                        'icon' => 'bi bi-x-circle',
                        'class' => 'btn-outline-danger',
                        'data' => ['action' => 'reject', 'id' => 'match_id']
                    ]
                ]"
                empty-message="No driver matches found"
            />
        @else
            <div class="text-center py-5">
                <i class="bi bi-hand-thumbs-up" style="font-size: 3rem; color: #6c757d;" aria-hidden="true"></i>
                <h5 class="mt-3">No Driver Matches Found</h5>
                <p class="text-muted">Create a transport request to start receiving driver matches.</p>
                <a href="{{ route('company.requests.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle" aria-hidden="true"></i> Create Request
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Pagination -->
@if(isset($matches) && $matches->hasPages())
<div class="d-flex justify-content-center mt-4">
    {{ $matches->links() }}
</div>
@endif

<!-- Accept Match Modal -->
<x-ui.modal id="acceptMatchModal" title="Accept Match" size="md">
    <form id="acceptMatchForm">
        <div class="mb-3">
            <label for="agreedRate" class="form-label">Agreed Rate (₦)</label>
            <input type="number" step="0.01" class="form-control" id="agreedRate" required>
        </div>
        <div class="mb-3">
            <label for="acceptNotes" class="form-label">Notes (Optional)</label>
            <textarea class="form-control" id="acceptNotes" rows="3"></textarea>
        </div>
    </form>

    <x-slot name="footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-success" id="confirmAcceptBtn">Accept Match</button>
    </x-slot>
</x-ui.modal>

<!-- Reject Match Modal -->
<x-ui.modal id="rejectMatchModal" title="Reject Match" size="md">
    <form id="rejectMatchForm">
        <div class="mb-3">
            <label for="rejectReason" class="form-label">Reason for Rejection</label>
            <select class="form-select" id="rejectReason" required>
                <option value="">Select Reason</option>
                <option value="rate_too_high">Rate too high</option>
                <option value="driver_unavailable">Driver unavailable</option>
                <option value="schedule_conflict">Schedule conflict</option>
                <option value="other">Other</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="rejectNotes" class="form-label">Additional Notes (Optional)</label>
            <textarea class="form-control" id="rejectNotes" rows="3"></textarea>
        </div>
    </form>

    <x-slot name="footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="confirmRejectBtn">Reject Match</button>
    </x-slot>
</x-ui.modal>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentMatchId = null;

    // Action button handlers
    document.querySelectorAll('[data-action]').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const action = this.getAttribute('data-action');
            const matchId = this.getAttribute('data-id') || this.closest('tr').getAttribute('data-match-id');

            switch(action) {
                case 'view':
                    window.location.href = `/company/matches/${matchId}`;
                    break;
                case 'accept':
                    openAcceptModal(matchId);
                    break;
                case 'reject':
                    openRejectModal(matchId);
                    break;
            }
        });
    });

    function openAcceptModal(matchId) {
        currentMatchId = matchId;
        const modal = new bootstrap.Modal(document.getElementById('acceptMatchModal'));
        modal.show();
    }

    function openRejectModal(matchId) {
        currentMatchId = matchId;
        const modal = new bootstrap.Modal(document.getElementById('rejectMatchModal'));
        modal.show();
    }

    // Accept match
    document.getElementById('confirmAcceptBtn').addEventListener('click', function() {
        const agreedRate = document.getElementById('agreedRate').value;
        const notes = document.getElementById('acceptNotes').value;

        if (!agreedRate) {
            showToast('Please enter an agreed rate', 'danger');
            return;
        }

        acceptMatch(currentMatchId, agreedRate, notes);
    });

    // Reject match
    document.getElementById('confirmRejectBtn').addEventListener('click', function() {
        const reason = document.getElementById('rejectReason').value;
        const notes = document.getElementById('rejectNotes').value;

        if (!reason) {
            showToast('Please select a reason for rejection', 'danger');
            return;
        }

        rejectMatch(currentMatchId, reason, notes);
    });

    function acceptMatch(matchId, agreedRate, notes) {
        fetch(`/api/company/matches/${matchId}/accept`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                agreed_rate: agreedRate,
                notes: notes
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Match accepted successfully!', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(data.message || 'Failed to accept match', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred while accepting the match', 'danger');
        });
    }

    function rejectMatch(matchId, reason, notes) {
        fetch(`/api/company/matches/${matchId}/reject`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                reason: reason,
                notes: notes
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Match rejected successfully!', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(data.message || 'Failed to reject match', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred while rejecting the match', 'danger');
        });
    }

    function showToast(message, type = 'info') {
        // Create toast container if it doesn't exist
        let toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            toastContainer.style.zIndex = '9999';
            document.body.appendChild(toastContainer);
        }

        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type} border-0`;
        toast.setAttribute('role', 'alert');
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;

        toastContainer.appendChild(toast);
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();

        // Remove toast after it's hidden
        toast.addEventListener('hidden.bs.toast', () => {
            toast.remove();
        });
    }
});
</script>
@endpush
@endsection
