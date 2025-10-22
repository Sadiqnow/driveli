@extends('layouts.admin_cdn')

@section('title', 'Company Verification Queue')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1>Company Verification Queue</h1>
            <p class="text-muted">Manage and review company verification requests</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h4 class="mb-0">{{ $statistics['total_verifications'] ?? 0 }}</h4>
                            <p class="mb-0">Total Verifications</p>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="fas fa-building fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h4 class="mb-0">{{ $statistics['pending'] ?? 0 }}</h4>
                            <p class="mb-0">Pending Review</p>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h4 class="mb-0">{{ $statistics['under_review'] ?? 0 }}</h4>
                            <p class="mb-0">Under Review</p>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="fas fa-search fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h4 class="mb-0">{{ $statistics['approved'] ?? 0 }}</h4>
                            <p class="mb-0">Approved</p>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="row align-items-end">
                        <div class="col-md-4">
                            <label for="start_date">Start Date</label>
                            <input type="date" name="start_date" id="start_date" class="form-control"
                                   value="{{ $dateRange['start'] }}">
                        </div>
                        <div class="col-md-4">
                            <label for="end_date">End Date</label>
                            <input type="date" name="end_date" id="end_date" class="form-control"
                                   value="{{ $dateRange['end'] }}">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="{{ route('admin.verification.company-queue') }}" class="btn btn-secondary">Reset</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Pending Verifications -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Pending Verifications</h5>
                    <div>
                        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#bulkApproveModal">
                            Bulk Approve
                        </button>
                        <a href="{{ route('admin.verification.company-report') }}" class="btn btn-sm btn-info">
                            Generate Report
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($pendingVerifications->count() > 0)
                        <form id="bulkForm">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>
                                                <input type="checkbox" id="selectAll">
                                            </th>
                                            <th>Company</th>
                                            <th>Type</th>
                                            <th>Submitted</th>
                                            <th>Documents</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($pendingVerifications as $verification)
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="verification_ids[]" value="{{ $verification->id }}" class="verification-checkbox">
                                            </td>
                                            <td>
                                                <strong>{{ $verification->company->name }}</strong><br>
                                                <small class="text-muted">{{ $verification->company->email }}</small>
                                            </td>
                                            <td>
                                                <span class="badge badge-primary">{{ ucfirst(str_replace('_', ' ', $verification->verification_type)) }}</span>
                                            </td>
                                            <td>
                                                <small>{{ $verification->created_at->diffForHumans() }}</small>
                                            </td>
                                            <td>
                                                @php
                                                    $documents = json_decode($verification->submitted_documents, true) ?? [];
                                                @endphp
                                                <span class="badge badge-light">{{ count($documents) }} files</span>
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.verification.company-detail', $verification->id) }}"
                                                   class="btn btn-sm btn-primary">Review</a>
                                                <form method="POST" action="{{ route('admin.verification.company-under-review', $verification->id) }}"
                                                      style="display: inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-info">Under Review</button>
                                                </form>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </form>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-3">
                            {{ $pendingVerifications->links() }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <h5>No pending verifications</h5>
                            <p class="text-muted">All company verification requests have been processed.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Activities Sidebar -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Recent Activities</h6>
                </div>
                <div class="card-body">
                    @if($recentActivities->count() > 0)
                        <div class="timeline">
                            @foreach($recentActivities as $activity)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-{{ $activity->status === 'approved' ? 'success' : ($activity->status === 'rejected' ? 'danger' : 'info') }}"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">{{ $activity->company->name }}</h6>
                                    <p class="mb-1 text-muted">{{ ucfirst($activity->status) }} - {{ ucfirst(str_replace('_', ' ', $activity->verification_type)) }}</p>
                                    <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                                    @if($activity->verifiedBy)
                                        <br><small class="text-muted">by {{ $activity->verifiedBy->name }}</small>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted">No recent activities</p>
                    @endif
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0">Quick Stats</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong>Approval Rate:</strong> {{ $statistics['approval_rate'] ?? 0 }}%
                    </div>
                    <div class="mb-2">
                        <strong>Avg Processing Time:</strong> {{ $statistics['avg_processing_days'] ?? 'N/A' }} days
                    </div>
                    <div class="progress mb-2">
                        <div class="progress-bar bg-success" style="width: {{ $statistics['approval_rate'] ?? 0 }}%"></div>
                    </div>
                    <small class="text-muted">Based on last {{ $dateRange ? \Carbon\Carbon::parse($dateRange['start'])->diffInDays(\Carbon\Carbon::parse($dateRange['end'])) : 30 }} days</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Approve Modal -->
<div class="modal fade" id="bulkApproveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.verification.company-bulk-approve') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Bulk Approve Verifications</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes (Optional)</label>
                        <textarea name="notes" id="notes" class="form-control" rows="3"
                                  placeholder="Add notes for bulk approval..."></textarea>
                    </div>
                    <div id="selectedVerifications"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Approve Selected</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -35px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #dee2e6;
}

.timeline-content {
    background: #f8f9fa;
    padding: 10px;
    border-radius: 5px;
    border-left: 3px solid #007bff;
}

.timeline-item:not(:last-child)::after {
    content: '';
    position: absolute;
    left: -29px;
    top: 17px;
    width: 2px;
    height: calc(100% + 3px);
    background: #dee2e6;
}
</style>

@section('scripts')
<script>
// Select all functionality
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.verification-checkbox');
    checkboxes.forEach(checkbox => checkbox.checked = this.checked);
    updateSelectedVerifications();
});

// Individual checkbox change
document.querySelectorAll('.verification-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', updateSelectedVerifications);
});

function updateSelectedVerifications() {
    const selectedCheckboxes = document.querySelectorAll('.verification-checkbox:checked');
    const selectedDiv = document.getElementById('selectedVerifications');

    if (selectedCheckboxes.length > 0) {
        selectedDiv.innerHTML = `<p><strong>${selectedCheckboxes.length}</strong> verification(s) selected for bulk approval.</p>`;

        // Add hidden inputs for selected verification IDs
        selectedCheckboxes.forEach(checkbox => {
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = 'verification_ids[]';
            hiddenInput.value = checkbox.value;
            selectedDiv.appendChild(hiddenInput);
        });
    } else {
        selectedDiv.innerHTML = '<p class="text-muted">No verifications selected.</p>';
    }
}
</script>
@endsection
