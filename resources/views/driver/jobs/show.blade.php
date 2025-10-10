@extends('drivers.layouts.app')

@section('title', 'Job Details')
@section('page_title', 'Job Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('driver.jobs.index') }}">Jobs</a></li>
    <li class="breadcrumb-item active">Job Details</li>
@endsection

@section('content')
<div class="row">
    <div class="col-lg-8">
        <!-- Job Information -->
        <div class="driver-card">
            <div class="driver-card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-briefcase me-2" aria-hidden="true"></i>
                        Job Information
                    </h5>
                    <span class="badge bg-{{ 
                        $match->status === 'pending' ? 'warning' : 
                        ($match->status === 'accepted' ? 'success' : 
                        ($match->status === 'completed' ? 'primary' : 'secondary'))
                    }}">
                        {{ ucfirst($match->status) }}
                    </span>
                </div>
            </div>
            <div class="driver-card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="mb-3">Company Details</h6>
                        <div class="mb-2">
                            <strong>Company:</strong>
                            <span class="ms-2">{{ $match->company->name ?? 'Company Name' }}</span>
                        </div>
                        <div class="mb-2">
                            <strong>Location:</strong>
                            <span class="ms-2">{{ $match->company->location ?? 'Location not specified' }}</span>
                        </div>
                        <div class="mb-2">
                            <strong>Contact:</strong>
                            <span class="ms-2">{{ $match->company->email ?? 'Not provided' }}</span>
                        </div>
                        @if($match->company->phone)
                        <div class="mb-2">
                            <strong>Phone:</strong>
                            <span class="ms-2">{{ $match->company->phone }}</span>
                        </div>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <h6 class="mb-3">Job Details</h6>
                        <div class="mb-2">
                            <strong>Match Score:</strong>
                            <span class="ms-2">
                                {{ $match->match_score ?? 'N/A' }}%
                                @if($match->match_score)
                                    <div class="progress mt-1" style="height: 6px;">
                                        <div class="progress-bar bg-success" 
                                             style="width: {{ $match->match_score }}%"></div>
                                    </div>
                                @endif
                            </span>
                        </div>
                        <div class="mb-2">
                            <strong>Created:</strong>
                            <span class="ms-2">{{ $match->created_at->format('M d, Y \a\t g:i A') }}</span>
                        </div>
                        @if($match->accepted_at)
                        <div class="mb-2">
                            <strong>Accepted:</strong>
                            <span class="ms-2">{{ $match->accepted_at->format('M d, Y \a\t g:i A') }}</span>
                        </div>
                        @endif
                        @if($match->completed_at)
                        <div class="mb-2">
                            <strong>Completed:</strong>
                            <span class="ms-2">{{ $match->completed_at->format('M d, Y \a\t g:i A') }}</span>
                        </div>
                        @endif
                    </div>
                </div>
                
                @if($match->company->description)
                <hr>
                <h6>Company Description</h6>
                <p class="text-muted">{{ $match->company->description }}</p>
                @endif
                
                @if($match->notes)
                <hr>
                <h6>Additional Notes</h6>
                <p class="text-muted">{{ $match->notes }}</p>
                @endif
            </div>
        </div>
        
        <!-- Job Requirements & Preferences -->
        @if($match->requirements || $match->preferences)
        <div class="driver-card mt-4">
            <div class="driver-card-header">
                <h5 class="mb-0">
                    <i class="fas fa-list-check me-2" aria-hidden="true"></i>
                    Requirements & Preferences
                </h5>
            </div>
            <div class="driver-card-body">
                <div class="row">
                    @if($match->requirements)
                    <div class="col-md-6">
                        <h6 class="text-danger">
                            <i class="fas fa-exclamation-triangle me-1" aria-hidden="true"></i>
                            Requirements
                        </h6>
                        <ul class="list-unstyled">
                            @foreach(json_decode($match->requirements, true) ?? [] as $requirement)
                            <li class="mb-1">
                                <i class="fas fa-check-circle text-success me-2" aria-hidden="true"></i>
                                {{ $requirement }}
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    
                    @if($match->preferences)
                    <div class="col-md-6">
                        <h6 class="text-primary">
                            <i class="fas fa-heart me-1" aria-hidden="true"></i>
                            Preferences
                        </h6>
                        <ul class="list-unstyled">
                            @foreach(json_decode($match->preferences, true) ?? [] as $preference)
                            <li class="mb-1">
                                <i class="fas fa-star text-warning me-2" aria-hidden="true"></i>
                                {{ $preference }}
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>
    
    <!-- Actions Sidebar -->
    <div class="col-lg-4">
        <!-- Job Actions -->
        <div class="driver-card">
            <div class="driver-card-header">
                <h5 class="mb-0">
                    <i class="fas fa-cogs me-2" aria-hidden="true"></i>
                    Job Actions
                </h5>
            </div>
            <div class="driver-card-body">
                @if($match->status === 'pending')
                    <div class="d-grid gap-2">
                        <form action="{{ route('driver.jobs.accept', $match) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success w-100" 
                                    onclick="return confirm('Are you sure you want to accept this job?')">
                                <i class="fas fa-check me-2" aria-hidden="true"></i>
                                Accept Job
                            </button>
                        </form>
                        
                        <form action="{{ route('driver.jobs.decline', $match) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger w-100" 
                                    onclick="return confirm('Are you sure you want to decline this job?')">
                                <i class="fas fa-times me-2" aria-hidden="true"></i>
                                Decline Job
                            </button>
                        </form>
                    </div>
                @elseif(in_array($match->status, ['accepted', 'in_progress']))
                    <div class="d-grid gap-2">
                        <form action="{{ route('driver.jobs.complete', $match) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success w-100" 
                                    onclick="return confirm('Mark this job as complete?')">
                                <i class="fas fa-check-double me-2" aria-hidden="true"></i>
                                Mark Complete
                            </button>
                        </form>
                        
                        <button class="btn btn-outline-primary w-100" onclick="contactCompany()">
                            <i class="fas fa-phone me-2" aria-hidden="true"></i>
                            Contact Company
                        </button>
                    </div>
                @elseif($match->status === 'completed')
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2" aria-hidden="true"></i>
                        <strong>Job Completed!</strong>
                        <br>
                        <small>Completed on {{ $match->completed_at->format('M d, Y \a\t g:i A') }}</small>
                    </div>
                @elseif($match->status === 'declined')
                    <div class="alert alert-secondary">
                        <i class="fas fa-times-circle me-2" aria-hidden="true"></i>
                        <strong>Job Declined</strong>
                        <br>
                        <small>Declined on {{ $match->declined_at->format('M d, Y \a\t g:i A') }}</small>
                    </div>
                @endif
                
                <hr>
                
                <div class="d-grid">
                    <a href="{{ route('driver.jobs.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2" aria-hidden="true"></i>
                        Back to Jobs
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Job Timeline -->
        <div class="driver-card mt-4">
            <div class="driver-card-header">
                <h5 class="mb-0">
                    <i class="fas fa-clock me-2" aria-hidden="true"></i>
                    Job Timeline
                </h5>
            </div>
            <div class="driver-card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker bg-primary"></div>
                        <div class="timeline-content">
                            <h6 class="mb-1">Job Created</h6>
                            <small class="text-muted">{{ $match->created_at->format('M d, Y \a\t g:i A') }}</small>
                        </div>
                    </div>
                    
                    @if($match->accepted_at)
                    <div class="timeline-item">
                        <div class="timeline-marker bg-success"></div>
                        <div class="timeline-content">
                            <h6 class="mb-1">Job Accepted</h6>
                            <small class="text-muted">{{ $match->accepted_at->format('M d, Y \a\t g:i A') }}</small>
                        </div>
                    </div>
                    @endif
                    
                    @if($match->completed_at)
                    <div class="timeline-item">
                        <div class="timeline-marker bg-primary"></div>
                        <div class="timeline-content">
                            <h6 class="mb-1">Job Completed</h6>
                            <small class="text-muted">{{ $match->completed_at->format('M d, Y \a\t g:i A') }}</small>
                        </div>
                    </div>
                    @endif
                    
                    @if($match->declined_at)
                    <div class="timeline-item">
                        <div class="timeline-marker bg-danger"></div>
                        <div class="timeline-content">
                            <h6 class="mb-1">Job Declined</h6>
                            <small class="text-muted">{{ $match->declined_at->format('M d, Y \a\t g:i A') }}</small>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
function contactCompany() {
    const company = @json($match->company);
    
    let contactInfo = 'Contact Information:\n\n';
    contactInfo += `Company: ${company.name}\n`;
    contactInfo += `Email: ${company.email || 'Not provided'}\n`;
    if (company.phone) {
        contactInfo += `Phone: ${company.phone}\n`;
    }
    contactInfo += `Location: ${company.location || 'Not specified'}\n`;
    
    alert(contactInfo);
}
</script>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 10px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -25px;
    top: 4px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #dee2e6;
}

.timeline-content {
    padding-left: 5px;
}
</style>
@endsection