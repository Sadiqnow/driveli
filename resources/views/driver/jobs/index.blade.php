@extends('drivers.layouts.app')

@section('title', 'My Jobs')
@section('page_title', 'My Jobs')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('driver.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">My Jobs</li>
@endsection

@section('content')
<div class="row">
    <!-- Current Jobs -->
    <div class="col-lg-6">
        <div class="driver-card">
            <div class="driver-card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-play me-2" aria-hidden="true"></i>
                        Current Jobs
                    </h5>
                    <span class="badge bg-primary">{{ $currentJobs->count() }}</span>
                </div>
            </div>
            <div class="driver-card-body">
                @forelse($currentJobs as $job)
                    <div class="job-item mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $job->title ?? 'Job Match' }}</h6>
                                <p class="text-muted mb-1">
                                    <i class="fas fa-building me-1"></i>
                                    {{ $job->companyRequest->company->name ?? 'Company' }}
                                </p>
                                <div class="d-flex align-items-center text-muted small">
                                    <span class="me-3">
                                        <i class="fas fa-clock me-1"></i>
                                        {{ $job->created_at->format('M d, H:i') }}
                                    </span>
                                    <span class="badge bg-{{ $job->status === 'accepted' ? 'success' : 'warning' }}">
                                        {{ ucfirst($job->status) }}
                                    </span>
                                </div>
                            </div>
                            <div class="ms-3">
                                <a href="{{ route('driver.jobs.show', $job) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4">
                        <i class="fas fa-tasks fa-3x text-muted mb-3" aria-hidden="true"></i>
                        <h6 class="text-muted">No current jobs</h6>
                        <p class="text-muted mb-0">You don't have any jobs in progress.</p>
                        <a href="{{ route('driver.jobs.available') }}" class="btn btn-primary btn-sm mt-2">
                            <i class="fas fa-search me-1"></i>
                            Browse Available Jobs
                        </a>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Available Jobs -->
    <div class="col-lg-6">
        <div class="driver-card">
            <div class="driver-card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-clock me-2" aria-hidden="true"></i>
                        Available Jobs
                    </h5>
                    <div>
                        <span class="badge bg-warning me-2">{{ $availableJobs->count() }}</span>
                        <a href="{{ route('driver.jobs.available') }}" class="btn btn-driver-primary btn-sm">
                            <i class="fas fa-eye me-1"></i>
                            View All
                        </a>
                    </div>
                </div>
            </div>
            <div class="driver-card-body">
                @forelse($availableJobs->take(3) as $job)
                    <div class="job-item mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $job->title ?? 'New Job Match' }}</h6>
                                <p class="text-muted mb-1">
                                    <i class="fas fa-building me-1"></i>
                                    {{ $job->companyRequest->company->name ?? 'Company' }}
                                </p>
                                <div class="d-flex align-items-center text-muted small">
                                    <span class="me-3">
                                        <i class="fas fa-clock me-1"></i>
                                        {{ $job->created_at->format('M d, H:i') }}
                                    </span>
                                    <span class="badge bg-success">
                                        Available
                                    </span>
                                </div>
                            </div>
                            <div class="ms-3">
                                <div class="d-flex gap-1">
                                    <button class="btn btn-success btn-sm" onclick="acceptJob({{ $job->id }})">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button class="btn btn-outline-secondary btn-sm" onclick="viewJob({{ $job->id }})">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4">
                        <i class="fas fa-inbox fa-3x text-muted mb-3" aria-hidden="true"></i>
                        <h6 class="text-muted">No available jobs</h6>
                        <p class="text-muted mb-0">Check back later for new opportunities.</p>
                        <button class="btn btn-outline-primary btn-sm mt-2" onclick="refreshJobs()">
                            <i class="fas fa-sync-alt me-1"></i>
                            Refresh
                        </button>
                    </div>
                @endforelse

                @if($availableJobs->count() > 3)
                    <div class="text-center mt-3">
                        <a href="{{ route('driver.jobs.available') }}" class="btn btn-driver-primary btn-sm">
                            <i class="fas fa-plus me-1"></i>
                            View {{ $availableJobs->count() - 3 }} More Jobs
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mt-4">
    <div class="col-12">
        <div class="driver-card">
            <div class="driver-card-header">
                <h5 class="mb-0">
                    <i class="fas fa-bolt me-2" aria-hidden="true"></i>
                    Quick Actions
                </h5>
            </div>
            <div class="driver-card-body">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <a href="{{ route('driver.jobs.available') }}" class="btn btn-driver-primary w-100">
                            <i class="fas fa-search me-1"></i>
                            Browse All Jobs
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="{{ route('driver.profile.documents') }}" class="btn btn-outline-info w-100">
                            <i class="fas fa-file-alt me-1"></i>
                            My Documents
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <a href="{{ route('driver.dashboard') }}" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-tachometer-alt me-1"></i>
                            Dashboard
                        </a>
                    </div>
                    <div class="col-md-3 mb-2">
                        <button class="btn btn-outline-secondary w-100" onclick="refreshJobs()">
                            <i class="fas fa-sync-alt me-1"></i>
                            Refresh Jobs
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
function acceptJob(jobId) {
    if (confirm('Are you sure you want to accept this job?')) {
        fetch(`/driver/jobs/${jobId}/accept`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Job accepted successfully!', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast('Failed to accept job. Please try again.', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred. Please try again.', 'error');
        });
    }
}

function viewJob(jobId) {
    window.location.href = `/driver/jobs/${jobId}`;
}

function refreshJobs() {
    const button = event.target;
    const icon = button.querySelector('i');
    
    // Show loading state
    icon.classList.add('fa-spin');
    button.disabled = true;
    
    // Simulate refresh
    setTimeout(() => {
        location.reload();
    }, 1000);
}

function showToast(message, type) {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 1060; min-width: 300px;';
    toast.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
            <span>${message}</span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        if (toast.parentNode) {
            toast.remove();
        }
    }, 5000);
}
</script>
@endsection