@extends('drivers.layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Driver Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('styles')
<style>
    /* Unified button styles for dashboard */
    .btn-drivelink-primary {
        background: var(--drivelink-gradient-primary);
        border: none;
        border-radius: var(--drivelink-border-radius);
        padding: 0.75rem 1.5rem;
        font-weight: 500;
        color: white;
        transition: var(--drivelink-transition);
    }

    .btn-drivelink-primary:hover {
        transform: translateY(-1px);
        box-shadow: var(--drivelink-box-shadow-lg);
        color: white;
    }

    .btn-drivelink-outline {
        background: transparent;
        border: 2px solid var(--drivelink-secondary);
        border-radius: var(--drivelink-border-radius);
        padding: 0.75rem 1.5rem;
        font-weight: 500;
        color: var(--drivelink-secondary);
        transition: var(--drivelink-transition);
    }

    .btn-drivelink-outline:hover {
        background: var(--drivelink-secondary);
        color: white;
    }

    /* Enhanced cards with unified styling */
    .card {
        border-radius: var(--drivelink-border-radius);
        box-shadow: var(--drivelink-box-shadow);
        border: 1px solid rgba(0,0,0,0.125);
        transition: var(--drivelink-transition);
    }

    .card:hover {
        box-shadow: var(--drivelink-box-shadow-lg);
        transform: translateY(-2px);
    }

    .card-header.bg-light {
        background: var(--drivelink-light) !important;
        border-bottom: 1px solid rgba(0,0,0,0.125);
    }

    /* Badge styles */
    .badge {
        font-weight: 500;
    }
</style>
@endsection

@section('content')
<div class="row">
    <!-- Profile Completion Alert -->
    @if($profileCompleteness < 80)
    <div class="col-12">
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-start">
                <div class="me-3">
                    <i class="fas fa-exclamation-triangle fa-lg" aria-hidden="true"></i>
                </div>
                <div class="flex-grow-1">
                    <h6 class="alert-heading">Complete Your Profile</h6>
                    <p class="mb-2">Your profile is {{ $profileCompleteness }}% complete. Complete your profile to get verified and start receiving job matches.</p>
                    <div class="progress mb-2" style="height: 8px;">
                        <div class="progress-bar bg-warning" 
                             role="progressbar" 
                             style="width: {{ $profileCompleteness }}%"
                             aria-valuenow="{{ $profileCompleteness }}" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                        </div>
                    </div>
                    <a href="{{ route('driver.profile.edit') }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-edit me-1" aria-hidden="true"></i>
                        Complete Profile
                    </a>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    </div>
    @endif

    <!-- KYC Status Card -->
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                @php
                    $kycProgress = $driver->getKycProgressPercentage();
                    $kycStatus = $driver->kyc_status ?? 'not_started';
                    $currentStep = $driver->getCurrentKycStep();
                @endphp
                
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center flex-grow-1">
                        <div class="me-3">
                            @if($kycStatus === 'completed')
                                <div class="rounded-circle d-flex align-items-center justify-content-center" 
                                     style="width: 56px; height: 56px; background: var(--drivelink-success);">
                                    <i class="fas fa-check-circle text-white fa-lg" aria-hidden="true"></i>
                                </div>
                            @elseif($kycStatus === 'in_progress')
                                <div class="rounded-circle d-flex align-items-center justify-content-center" 
                                     style="width: 56px; height: 56px; background: var(--drivelink-warning);">
                                    <i class="fas fa-clock text-white fa-lg" aria-hidden="true"></i>
                                </div>
                            @elseif($kycStatus === 'rejected')
                                <div class="rounded-circle d-flex align-items-center justify-content-center" 
                                     style="width: 56px; height: 56px; background: var(--drivelink-danger);">
                                    <i class="fas fa-exclamation-triangle text-white fa-lg" aria-hidden="true"></i>
                                </div>
                            @else
                                <div class="rounded-circle d-flex align-items-center justify-content-center" 
                                     style="width: 56px; height: 56px; background: var(--drivelink-secondary);">
                                    <i class="fas fa-shield-alt text-white fa-lg" aria-hidden="true"></i>
                                </div>
                            @endif
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">KYC Verification Status</h6>
                                    <div class="d-flex align-items-center mb-2">
                                        @if($kycStatus === 'completed')
                                            <span class="badge bg-success me-2">Completed</span>
                                            <span class="text-muted small">Under admin review</span>
                                        @elseif($kycStatus === 'in_progress')
                                            <span class="badge bg-warning me-2">In Progress</span>
                                            <span class="text-muted small">Step {{ $currentStep }} of 3</span>
                                        @elseif($kycStatus === 'rejected')
                                            <span class="badge bg-danger me-2">Rejected</span>
                                            <span class="text-muted small">{{ $driver->kyc_retry_count ?? 0 }}/3 attempts used</span>
                                        @else
                                            <span class="badge bg-secondary me-2">Not Started</span>
                                            <span class="text-muted small">Complete KYC to get verified</span>
                                        @endif
                                    </div>
                                    <div class="progress" style="height: 8px; width: 200px;">
                                        <div class="progress-bar 
                                            {{ $kycStatus === 'completed' ? 'bg-success' : 
                                               ($kycStatus === 'rejected' ? 'bg-danger' : 'bg-primary') }}" 
                                             role="progressbar" 
                                             style="width: {{ $kycProgress }}%"
                                             aria-valuenow="{{ $kycProgress }}" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                        </div>
                                    </div>
                                    <small class="text-muted">{{ $kycProgress }}% complete</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-end">
                        @if($kycStatus === 'completed')
                            <a href="{{ route('driver.kyc.summary') }}" class="btn btn-outline-success btn-sm">
                                <i class="fas fa-eye me-1" aria-hidden="true"></i>
                                View Status
                            </a>
                        @elseif($kycStatus === 'in_progress')
                            @if($currentStep === 1)
                                <a href="{{ route('driver.kyc.step1') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-arrow-right me-1" aria-hidden="true"></i>
                                    Continue Step 1
                                </a>
                            @elseif($currentStep === 2)
                                <a href="{{ route('driver.kyc.step2') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-arrow-right me-1" aria-hidden="true"></i>
                                    Continue Step 2
                                </a>
                            @elseif($currentStep === 3)
                                <a href="{{ route('driver.kyc.step3') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-clipboard-check me-1" aria-hidden="true"></i>
                                    Complete KYC
                                </a>
                            @endif
                        @elseif($kycStatus === 'rejected')
                            @if(($driver->kyc_retry_count ?? 0) < 3)
                                <a href="{{ route('driver.kyc.retry') }}" class="btn btn-warning btn-sm">
                                    <i class="fas fa-redo me-1" aria-hidden="true"></i>
                                    Retry KYC
                                </a>
                            @else
                                <button class="btn btn-outline-secondary btn-sm" disabled>
                                    <i class="fas fa-ban me-1" aria-hidden="true"></i>
                                    Contact Support
                                </button>
                            @endif
                        @else
                            <a href="{{ route('driver.kyc.index') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-play me-1" aria-hidden="true"></i>
                                Start KYC
                            </a>
                        @endif
                    </div>
                </div>
                
                @if($kycStatus === 'rejected' && $driver->kyc_rejection_reason)
                    <div class="mt-3 p-3 bg-danger bg-opacity-10 border border-danger border-opacity-25 rounded">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-exclamation-circle text-danger me-2 mt-1"></i>
                            <div>
                                <strong class="text-danger">Rejection Reason:</strong>
                                <p class="mb-0 text-dark mt-1">{{ $driver->kyc_rejection_reason }}</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row">
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card">
            <div class="card-body text-center">
                <div class="mb-3">
                    <i class="fas fa-briefcase fa-2x" style="color: var(--drivelink-primary);" aria-hidden="true"></i>
                </div>
                <h3 class="mb-1" id="totalJobs">{{ $stats['total_jobs'] }}</h3>
                <p class="text-muted mb-0">Total Jobs</p>
                <small class="text-success">
                    <i class="fas fa-arrow-up me-1" aria-hidden="true"></i>
                    +{{ $stats['jobs_this_month'] }} this month
                </small>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card">
            <div class="card-body text-center">
                <div class="mb-3">
                    <i class="fas fa-check-circle fa-2x" style="color: var(--drivelink-success);" aria-hidden="true"></i>
                </div>
                <h3 class="mb-1" id="completedJobs">{{ $stats['completed_jobs'] }}</h3>
                <p class="text-muted mb-0">Completed Jobs</p>
                <small class="text-muted">
                    {{ $stats['total_jobs'] > 0 ? round(($stats['completed_jobs'] / $stats['total_jobs']) * 100, 1) : 0 }}% completion rate
                </small>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card">
            <div class="card-body text-center">
                <div class="mb-3">
                    <i class="fas fa-dollar-sign fa-2x" style="color: var(--drivelink-info);" aria-hidden="true"></i>
                </div>
                <h3 class="mb-1" id="totalEarnings">${{ number_format($stats['total_earnings'], 2) }}</h3>
                <p class="text-muted mb-0">Total Earnings</p>
                <small class="text-info">
                    <i class="fas fa-arrow-up me-1" aria-hidden="true"></i>
                    ${{ number_format($stats['earnings_this_month'], 2) }} this month
                </small>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card">
            <div class="card-body text-center">
                <div class="mb-3">
                    <i class="fas fa-star fa-2x" style="color: var(--drivelink-warning);" aria-hidden="true"></i>
                </div>
                <h3 class="mb-1" id="averageRating">{{ number_format($stats['average_rating'], 1) }}</h3>
                <p class="text-muted mb-0">Average Rating</p>
                <div class="mt-1">
                    @for($i = 1; $i <= 5; $i++)
                        @if($i <= $stats['average_rating'])
                            <i class="fas fa-star text-warning" aria-hidden="true"></i>
                        @else
                            <i class="far fa-star text-warning" aria-hidden="true"></i>
                        @endif
                    @endfor
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="row">
    <!-- Recent Activity -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-clock me-2" aria-hidden="true"></i>
                        Recent Activity
                    </h5>
                    <button class="btn btn-outline-secondary btn-sm" onclick="refreshActivity()" 
                            aria-label="Refresh activity">
                        <i class="fas fa-sync-alt" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div id="recentActivity">
                    @forelse($recentMatches as $match)
                        <div class="d-flex align-items-start mb-3 pb-3 border-bottom">
                            <div class="me-3">
                                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center" 
                                     style="width: 40px; height: 40px;">
                                    <i class="fas fa-handshake text-white" aria-hidden="true"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">New Job Match</h6>
                                <p class="mb-1 text-muted">
                                    Matched with {{ $match->companyRequest->company->name ?? 'Company' }}
                                </p>
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1" aria-hidden="true"></i>
                                    {{ $match->created_at->diffForHumans() }}
                                </small>
                            </div>
                            <span class="badge bg-{{ $match->status === 'active' ? 'success' : ($match->status === 'pending' ? 'warning' : 'secondary') }}">
                                {{ ucfirst($match->status) }}
                            </span>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-3" aria-hidden="true"></i>
                            <h6 class="text-muted">No recent activity</h6>
                            <p class="text-muted mb-0">Your job matches and updates will appear here.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="fas fa-bolt me-2" aria-hidden="true"></i>
                    Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @php
                        $kycStatus = $driver->kyc_status ?? 'not_started';
                        $currentStep = $driver->getCurrentKycStep();
                    @endphp
                    
                    @if($kycStatus !== 'completed')
                        @if($kycStatus === 'not_started')
                            <a href="{{ route('driver.kyc.index') }}" class="btn btn-drivelink-primary">
                                <i class="fas fa-shield-alt me-2" aria-hidden="true"></i>
                                Start KYC Verification
                            </a>
                        @elseif($kycStatus === 'in_progress')
                            @if($currentStep === 1)
                                <a href="{{ route('driver.kyc.step1') }}" class="btn btn-warning">
                                    <i class="fas fa-arrow-right me-2" aria-hidden="true"></i>
                                    Continue KYC Step 1
                                </a>
                            @elseif($currentStep === 2)
                                <a href="{{ route('driver.kyc.step2') }}" class="btn btn-warning">
                                    <i class="fas fa-arrow-right me-2" aria-hidden="true"></i>
                                    Continue KYC Step 2
                                </a>
                            @elseif($currentStep === 3)
                                <a href="{{ route('driver.kyc.step3') }}" class="btn btn-warning">
                                    <i class="fas fa-clipboard-check me-2" aria-hidden="true"></i>
                                    Complete KYC
                                </a>
                            @endif
                        @elseif($kycStatus === 'rejected')
                            @if(($driver->kyc_retry_count ?? 0) < 3)
                                <a href="{{ route('driver.kyc.retry') }}" class="btn btn-danger">
                                    <i class="fas fa-redo me-2" aria-hidden="true"></i>
                                    Retry KYC Process
                                </a>
                            @endif
                        @endif
                    @else
                        <a href="{{ route('driver.jobs.index') }}" class="btn btn-drivelink-primary">
                            <i class="fas fa-search me-2" aria-hidden="true"></i>
                            Find Available Jobs
                        </a>
                    @endif
                    
                    <a href="{{ route('driver.profile.edit') }}" class="btn btn-drivelink-outline">
                        <i class="fas fa-user-edit me-2" aria-hidden="true"></i>
                        Update Profile
                    </a>
                    
                    <a href="{{ route('driver.documents') }}" class="btn btn-drivelink-outline">
                        <i class="fas fa-file-upload me-2" aria-hidden="true"></i>
                        Manage Documents
                    </a>
                    
                    @if($kycStatus !== 'not_started')
                        <a href="{{ route('driver.kyc.index') }}" class="btn btn-outline-info">
                            <i class="fas fa-shield-alt me-2" aria-hidden="true"></i>
                            KYC Status
                        </a>
                    @endif
                </div>
                
                <!-- Availability Toggle -->
                <div class="mt-4 p-3 bg-light rounded">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">Availability Status</h6>
                            <small class="text-muted">Toggle your availability for new jobs</small>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="availabilityToggle"
                                   {{ $driver->available ? 'checked' : '' }}
                                   aria-label="Toggle job availability">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Notifications -->
        <div class="driver-card mt-4">
            <div class="driver-card-header">
                <h5 class="mb-0">
                    <i class="fas fa-bell me-2" aria-hidden="true"></i>
                    Notifications
                </h5>
            </div>
            <div class="card-body">
                <div id="notifications">
                    <!-- Notifications will be loaded via AJAX -->
                    <div class="text-center py-3">
                        <div class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></div>
                        <span class="ms-2">Loading notifications...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load notifications
    loadNotifications();
    
    // Availability toggle
    const availabilityToggle = document.getElementById('availabilityToggle');
    if (availabilityToggle) {
        availabilityToggle.addEventListener('change', function() {
            updateAvailability(this.checked);
        });
    }
    
    // Auto-refresh stats every 30 seconds
    setInterval(function() {
        refreshStats();
    }, 30000);
});

function loadNotifications() {
    fetch('{{ route("driver.notifications") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderNotifications(data.notifications);
            } else {
                document.getElementById('notifications').innerHTML = 
                    '<p class="text-muted text-center mb-0">Failed to load notifications</p>';
            }
        })
        .catch(error => {
            console.error('Error loading notifications:', error);
            document.getElementById('notifications').innerHTML = 
                '<p class="text-muted text-center mb-0">Error loading notifications</p>';
        });
}

function renderNotifications(notifications) {
    const container = document.getElementById('notifications');
    
    if (notifications.length === 0) {
        container.innerHTML = `
            <div class="text-center py-3">
                <i class="fas fa-bell-slash fa-2x text-muted mb-2"></i>
                <p class="text-muted mb-0">No notifications</p>
            </div>
        `;
        return;
    }
    
    const html = notifications.slice(0, 3).map(notification => `
        <div class="d-flex align-items-start mb-3 pb-3 ${notifications.indexOf(notification) < notifications.length - 1 ? 'border-bottom' : ''}">
            <div class="me-3">
                <div class="rounded-circle bg-${notification.color} d-flex align-items-center justify-content-center" 
                     style="width: 32px; height: 32px;">
                    <i class="${notification.icon} text-white fa-sm" aria-hidden="true"></i>
                </div>
            </div>
            <div class="flex-grow-1">
                <h6 class="mb-1 fs-6">${notification.title}</h6>
                <p class="mb-1 text-muted small">${notification.message}</p>
                <small class="text-muted">
                    <i class="fas fa-clock me-1" aria-hidden="true"></i>
                    ${timeAgo(notification.time)}
                </small>
            </div>
            ${!notification.read ? '<div class="bg-primary rounded-circle" style="width: 8px; height: 8px;"></div>' : ''}
        </div>
    `).join('');
    
    container.innerHTML = html;
}

function updateAvailability(available) {
    const toggle = document.getElementById('availabilityToggle');
    toggle.disabled = true;
    
    fetch('{{ route("driver.dashboard.availability") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ available: available })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
        } else {
            // Revert toggle state
            toggle.checked = !available;
            showToast('Failed to update availability', 'error');
        }
    })
    .catch(error => {
        console.error('Error updating availability:', error);
        toggle.checked = !available;
        showToast('Network error. Please try again.', 'error');
    })
    .finally(() => {
        toggle.disabled = false;
    });
}

function refreshStats() {
    fetch('{{ route("driver.dashboard.stats") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update stats with animation
                updateStatWithAnimation('totalJobs', data.stats.total_jobs);
                updateStatWithAnimation('completedJobs', data.stats.completed_jobs);
                updateStatWithAnimation('totalEarnings', '$' + parseFloat(data.stats.total_earnings).toFixed(2));
                updateStatWithAnimation('averageRating', parseFloat(data.stats.average_rating).toFixed(1));
            }
        })
        .catch(error => console.error('Error refreshing stats:', error));
}

function updateStatWithAnimation(elementId, newValue) {
    const element = document.getElementById(elementId);
    if (element && element.textContent !== newValue.toString()) {
        element.style.transform = 'scale(1.1)';
        element.style.transition = 'transform 0.2s ease';
        
        setTimeout(() => {
            element.textContent = newValue;
            element.style.transform = 'scale(1)';
        }, 100);
    }
}

function refreshActivity() {
    const button = event.target.closest('button');
    const icon = button.querySelector('i');
    icon.classList.add('fa-spin');
    
    fetch('{{ route("driver.dashboard.activity") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderActivity(data.activities);
                showToast('Activity refreshed', 'success');
            }
        })
        .catch(error => {
            console.error('Error refreshing activity:', error);
            showToast('Failed to refresh activity', 'error');
        })
        .finally(() => {
            icon.classList.remove('fa-spin');
        });
}

function renderActivity(activities) {
    // Implementation would go here to update the activity list
    console.log('Rendering activities:', activities);
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

function timeAgo(date) {
    const now = new Date();
    const time = new Date(date);
    const diffInSeconds = Math.floor((now - time) / 1000);
    
    if (diffInSeconds < 60) return 'Just now';
    if (diffInSeconds < 3600) return Math.floor(diffInSeconds / 60) + ' minutes ago';
    if (diffInSeconds < 86400) return Math.floor(diffInSeconds / 3600) + ' hours ago';
    return Math.floor(diffInSeconds / 86400) + ' days ago';
}
</script>
@endsection