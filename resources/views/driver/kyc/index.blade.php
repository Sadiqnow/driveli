@extends('layouts.driver-kyc')

@section('title', 'KYC Verification - Driver Dashboard')
@section('page-title', 'KYC Verification')
@section('page-description', 'Complete your identity verification in 3 simple steps')

@php
    $currentStep = 0; // Overview page
    $completionPercentage = $completionPercentage ?? 60;
    $step1Completed = $driver && $driver->kyc_step_1_completed_at;
    $step2Completed = $driver && $driver->kyc_step_2_completed_at;
    $step3Completed = $driver && $driver->kyc_step_3_completed_at;
@endphp

@section('content')
<!-- KYC Status Overview -->
<div class="step-info">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h6 class="mb-2">
                <i class="fas fa-shield-alt me-2" style="color: var(--drivelink-success);"></i>
                Secure Identity Verification Process
            </h6>
            <p class="mb-0">
                Complete your KYC verification to unlock all features and start working as a verified driver. 
                The process is secure, encrypted, and takes about 10-15 minutes.
            </p>
        </div>
        <div class="col-md-4 text-center">
            @php
                $statusColor = match($kycStatus['status']) {
                    'completed' => 'success',
                    'rejected' => 'danger',
                    'in_progress' => 'warning',
                    default => 'secondary'
                };
            @endphp
            <div class="d-flex align-items-center justify-content-center">
                <span class="badge bg-{{ $statusColor }} px-3 py-2 me-2">
                    <i class="fas fa-{{ $statusColor === 'success' ? 'check-circle' : ($statusColor === 'danger' ? 'times-circle' : 'clock') }} me-1"></i>
                    {{ ucfirst($kycStatus['status']) }}
                </span>
                <div class="text-center">
                    <div class="fw-bold">{{ $kycStatus['progress_percentage'] ?? 0 }}%</div>
                    <small class="text-muted">Complete</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- KYC Status Messages -->
@if($kycStatus['status'] === 'rejected')
    <div class="alert alert-danger border-start border-4 border-danger mb-4">
        <div class="d-flex align-items-center">
            <i class="fas fa-exclamation-triangle me-3" style="font-size: 1.5rem;"></i>
            <div class="flex-grow-1">
                <h6 class="mb-2">KYC Application Rejected</h6>
                <p class="mb-2">
                    Your KYC application was rejected. 
                    @if(isset($kycStatus['rejection_reason']))
                        <strong>Reason:</strong> {{ $kycStatus['rejection_reason'] }}
                    @endif
                </p>
                @if(($kycStatus['retry_count'] ?? 0) < 3)
                    <p class="mb-0">
                        You can retry the process ({{ 3 - ($kycStatus['retry_count'] ?? 0) }} attempts remaining).
                    </p>
                    <form method="POST" action="{{ route('driver.kyc.retry') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-primary btn-sm mt-2">
                            <i class="fas fa-redo me-1"></i>Retry KYC
                        </button>
                    </form>
                @else
                    <p class="mb-0">Please contact support for assistance.</p>
                @endif
            </div>
        </div>
    </div>
@elseif($kycStatus['status'] === 'completed')
    <div class="alert alert-success border-start border-4 border-success mb-4">
        <div class="d-flex align-items-center">
            <i class="fas fa-check-circle me-3" style="font-size: 1.5rem;"></i>
            <div>
                <h6 class="mb-2">KYC Successfully Completed!</h6>
                <p class="mb-0">
                    Your verification is complete and under admin review. You'll be notified once approved.
                    @if($kycStatus['completed_at'])
                        <br><small class="text-muted">Completed on {{ $kycStatus['completed_at']->format('M j, Y \a\t H:i') }}</small>
                    @endif
                </p>
            </div>
        </div>
    </div>
@elseif($kycStatus['status'] === 'in_progress')
    <div class="alert alert-info border-start border-4 border-info mb-4">
        <div class="d-flex align-items-center">
            <i class="fas fa-clock me-3" style="font-size: 1.5rem;"></i>
            <div>
                <h6 class="mb-2">KYC In Progress</h6>
                <p class="mb-0">
                    You're {{ $kycStatus['progress_percentage'] }}% through the verification process. Continue from where you left off.
                </p>
            </div>
        </div>
    </div>
@endif

<!-- KYC Steps Progress -->
<div class="step-card mb-4">
    <div class="step-card-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-1">
                    <i class="fas fa-list-ol me-2"></i>
                    KYC Steps
                </h5>
                <p class="mb-0 text-muted">Complete all three steps to finish your verification</p>
            </div>
            <div class="progress" style="width: 200px; height: 8px;">
                <div class="progress-bar bg-primary" 
                     style="width: {{ ($kycStatus['progress_percentage'] ?? 0) }}%" 
                     aria-valuenow="{{ $kycStatus['progress_percentage'] ?? 0 }}" 
                     aria-valuemin="0" 
                     aria-valuemax="100"></div>
            </div>
        </div>
    </div>
    <div class="step-card-body">
        <div class="row g-4">
            <!-- Step 1 -->
            <div class="col-lg-4">
                <div class="h-100 p-4 border rounded-3 position-relative
                    {{ $step1Completed ? 'border-success bg-success bg-opacity-10' : 
                        (in_array($kycStatus['current_step'], ['step_1', 'not_started']) ? 'border-primary bg-primary bg-opacity-10' : 'border-light bg-light') }}">
                    
                    @if($step1Completed)
                        <div class="position-absolute top-0 end-0 p-2">
                            <i class="fas fa-check-circle text-success fs-4"></i>
                        </div>
                    @endif
                    
                    <div class="text-center mb-3">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center
                            {{ $step1Completed ? 'bg-success text-white' : 
                                (in_array($kycStatus['current_step'], ['step_1', 'not_started']) ? 'bg-primary text-white' : 'bg-light text-muted') }}"
                             style="width: 60px; height: 60px; font-size: 1.5rem;">
                            @if($step1Completed)
                                <i class="fas fa-check"></i>
                            @else
                                1
                            @endif
                        </div>
                    </div>
                    
                    <div class="text-center">
                        <h6 class="fw-bold mb-2">Personal Information</h6>
                        <p class="text-muted small mb-3">
                            Basic personal details, emergency contact, and address information.
                        </p>
                        
                        @if($step1Completed)
                            <span class="badge bg-success mb-2">
                                <i class="fas fa-check me-1"></i>Completed
                            </span>
                            <br><small class="text-muted">{{ $driver->kyc_step_1_completed_at->format('M j, Y') }}</small>
                        @elseif(in_array($kycStatus['current_step'], ['step_1', 'not_started']))
                            <a href="{{ route('driver.kyc.step1') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-play me-1"></i>Start Step 1
                            </a>
                        @else
                            <span class="badge bg-light text-muted">Locked</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Step 2 -->
            <div class="col-lg-4">
                <div class="h-100 p-4 border rounded-3 position-relative
                    {{ $step2Completed ? 'border-success bg-success bg-opacity-10' : 
                        ($kycStatus['current_step'] === 'step_2' ? 'border-primary bg-primary bg-opacity-10' : 'border-light bg-light') }}">
                    
                    @if($step2Completed)
                        <div class="position-absolute top-0 end-0 p-2">
                            <i class="fas fa-check-circle text-success fs-4"></i>
                        </div>
                    @endif
                    
                    <div class="text-center mb-3">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center
                            {{ $step2Completed ? 'bg-success text-white' : 
                                ($kycStatus['current_step'] === 'step_2' ? 'bg-primary text-white' : 'bg-light text-muted') }}"
                             style="width: 60px; height: 60px; font-size: 1.5rem;">
                            @if($step2Completed)
                                <i class="fas fa-check"></i>
                            @else
                                2
                            @endif
                        </div>
                    </div>
                    
                    <div class="text-center">
                        <h6 class="fw-bold mb-2">Additional Details</h6>
                        <p class="text-muted small mb-3">
                            Identity verification, location information, and document uploads.
                        </p>
                        
                        @if($step2Completed)
                            <span class="badge bg-success mb-2">
                                <i class="fas fa-check me-1"></i>Completed
                            </span>
                            <br><small class="text-muted">{{ $driver->kyc_step_2_completed_at->format('M j, Y') }}</small>
                        @elseif($kycStatus['current_step'] === 'step_2')
                            <a href="{{ route('driver.kyc.step2') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-arrow-right me-1"></i>Continue Step 2
                            </a>
                        @elseif($step1Completed)
                            <a href="{{ route('driver.kyc.step2') }}" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-play me-1"></i>Start Step 2
                            </a>
                        @else
                            <span class="badge bg-light text-muted">
                                <i class="fas fa-lock me-1"></i>Locked
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Step 3 -->
            <div class="col-lg-4">
                <div class="h-100 p-4 border rounded-3 position-relative
                    {{ $step3Completed ? 'border-success bg-success bg-opacity-10' : 
                        ($kycStatus['current_step'] === 'step_3' ? 'border-primary bg-primary bg-opacity-10' : 'border-light bg-light') }}">
                    
                    @if($step3Completed)
                        <div class="position-absolute top-0 end-0 p-2">
                            <i class="fas fa-check-circle text-success fs-4"></i>
                        </div>
                    @endif
                    
                    <div class="text-center mb-3">
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center
                            {{ $step3Completed ? 'bg-success text-white' : 
                                ($kycStatus['current_step'] === 'step_3' ? 'bg-primary text-white' : 'bg-light text-muted') }}"
                             style="width: 60px; height: 60px; font-size: 1.5rem;">
                            @if($step3Completed)
                                <i class="fas fa-check"></i>
                            @else
                                3
                            @endif
                        </div>
                    </div>
                    
                    <div class="text-center">
                        <h6 class="fw-bold mb-2">Final Review</h6>
                        <p class="text-muted small mb-3">
                            Review all information and submit for admin verification.
                        </p>
                        
                        @if($step3Completed)
                            <span class="badge bg-success mb-2">
                                <i class="fas fa-check me-1"></i>Completed
                            </span>
                            <br><small class="text-muted">{{ $driver->kyc_step_3_completed_at->format('M j, Y') }}</small>
                        @elseif($kycStatus['current_step'] === 'step_3')
                            <a href="{{ route('driver.kyc.step3') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-clipboard-check me-1"></i>Complete Step 3
                            </a>
                        @elseif($step2Completed)
                            <a href="{{ route('driver.kyc.step3') }}" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-play me-1"></i>Start Step 3
                            </a>
                        @else
                            <span class="badge bg-light text-muted">
                                <i class="fas fa-lock me-1"></i>Locked
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions and Profile Status -->
<div class="row g-4">
    <!-- Profile Completion -->
    <div class="col-lg-6">
        <div class="step-card h-100">
            <div class="step-card-header">
                <h5 class="mb-0">
                    <i class="fas fa-user-check me-2"></i>
                    Profile Completion
                </h5>
            </div>
            <div class="step-card-body">
                <div class="progress mb-3" style="height: 12px;">
                    <div class="progress-bar 
                        {{ $completionPercentage >= 90 ? 'bg-success' : ($completionPercentage >= 70 ? 'bg-warning' : 'bg-info') }}" 
                         role="progressbar" 
                         style="width: {{ $completionPercentage }}%"
                         aria-valuenow="{{ $completionPercentage }}"
                         aria-valuemin="0"
                         aria-valuemax="100">
                        {{ $completionPercentage }}%
                    </div>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="fw-medium">{{ $completionPercentage }}% Complete</span>
                    @if($completionPercentage >= 90)
                        <span class="badge bg-success">
                            <i class="fas fa-check me-1"></i>Ready for KYC
                        </span>
                    @else
                        <span class="badge bg-warning">
                            <i class="fas fa-exclamation-triangle me-1"></i>{{ 90 - $completionPercentage }}% needed
                        </span>
                    @endif
                </div>
                
                <p class="text-muted mb-0">
                    @if($completionPercentage >= 90)
                        <i class="fas fa-check-circle text-success me-1"></i>
                        Your profile is complete enough for KYC submission.
                    @else
                        <i class="fas fa-info-circle text-info me-1"></i>
                        Complete your profile to improve job matching. You need at least 90% completion to submit KYC.
                    @endif
                </p>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="col-lg-6">
        <div class="step-card h-100">
            <div class="step-card-header">
                <h5 class="mb-0">
                    <i class="fas fa-bolt me-2"></i>
                    Quick Actions
                </h5>
            </div>
            <div class="step-card-body">
                <div class="d-grid gap-2">
                    @if(isset($kycStatus['can_perform_kyc']) && $kycStatus['can_perform_kyc'])
                        @if($kycStatus['current_step'] === 'not_started')
                            <a href="{{ route('driver.kyc.step1') }}" class="btn btn-kyc-primary">
                                <i class="fas fa-play me-2"></i>Start KYC Process
                            </a>
                        @elseif($kycStatus['current_step'] === 'step_1')
                            <a href="{{ route('driver.kyc.step1') }}" class="btn btn-kyc-primary">
                                <i class="fas fa-arrow-right me-2"></i>Continue Step 1
                            </a>
                        @elseif($kycStatus['current_step'] === 'step_2')
                            <a href="{{ route('driver.kyc.step2') }}" class="btn btn-kyc-primary">
                                <i class="fas fa-arrow-right me-2"></i>Continue Step 2
                            </a>
                        @elseif($kycStatus['current_step'] === 'step_3')
                            <a href="{{ route('driver.kyc.step3') }}" class="btn btn-kyc-primary">
                                <i class="fas fa-clipboard-check me-2"></i>Complete Final Step
                            </a>
                        @endif
                    @endif
                    
                    @if($step1Completed || $step2Completed)
                        <a href="{{ route('driver.kyc.summary') }}" class="btn btn-kyc-outline">
                            <i class="fas fa-eye me-2"></i>View Summary
                        </a>
                    @endif
                    
                    @if(isset($driver) && method_exists($driver, 'hasCompletedKyc') && $driver->hasCompletedKyc())
                        <a href="{{ route('driver.dashboard') }}" class="btn btn-success">
                            <i class="fas fa-tachometer-alt me-2"></i>Go to Dashboard
                        </a>
                    @endif
                    
                    <a href="{{ route('driver.profile.show') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-user-edit me-2"></i>Edit Profile
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Help Section -->
<div class="alert alert-light border border-info mt-4">
    <div class="d-flex align-items-start">
        <i class="fas fa-info-circle text-info me-3 mt-1" style="font-size: 1.2rem;"></i>
        <div>
            <h6 class="mb-2">Need Help with KYC?</h6>
            <p class="mb-2">Our KYC process is designed to be simple and secure. Here's what you need to know:</p>
            <ul class="mb-2">
                <li><strong>Time Required:</strong> 10-15 minutes total</li>
                <li><strong>Documents Needed:</strong> NIN, Profile photo, Address proof</li>
                <li><strong>Review Time:</strong> 24-48 hours after submission</li>
                <li><strong>Security:</strong> All data is encrypted and secure</li>
            </ul>
            <p class="mb-0">
                If you need assistance, contact our support team at 
                <a href="mailto:support@drivelink.com" class="fw-bold">support@drivelink.com</a> or 
                <a href="tel:+234-XXX-XXX-XXXX" class="fw-bold">+234-XXX-XXX-XXXX</a>.
            </p>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-refresh KYC status every 30 seconds if in progress
    @if(in_array($kycStatus['status'] ?? 'pending', ['pending', 'in_progress']))
    setInterval(function() {
        // Could add AJAX to refresh status without full page reload
        // For now, we'll keep it simple and let manual refresh handle updates
    }, 30000);
    @endif

    // Add progress animation
    const progressBars = document.querySelectorAll('.progress-bar');
    progressBars.forEach(bar => {
        const width = bar.style.width;
        bar.style.width = '0%';
        setTimeout(() => {
            bar.style.transition = 'width 1.5s ease-in-out';
            bar.style.width = width;
        }, 500);
    });
});
</script>
@endsection