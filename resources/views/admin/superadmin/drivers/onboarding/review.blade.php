@extends('layouts.admin_master')

@section('title', 'Superadmin - Review Driver Application')

@section('content_header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Review Driver Application</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.superadmin.dashboard') }}">Super Admin</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.superadmin.drivers.index') }}">Drivers</a></li>
                        <li class="breadcrumb-item active">Review</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid">
        <!-- Application Status -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h4 class="mb-1">{{ $driver->full_name }} ({{ $driver->driver_id }})</h4>
                                <p class="text-muted mb-2">Application submitted on {{ $driver->updated_at->format('M d, Y H:i') }}</p>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar bg-success" role="progressbar"
                                         style="width: {{ $progress['overall_progress'] }}%"
                                         aria-valuenow="{{ $progress['overall_progress'] }}"
                                         aria-valuemin="0" aria-valuemax="100">
                                        {{ $progress['overall_progress'] }}% Complete
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-right">
                                <span class="badge badge-lg badge-{{ $driver->verification_status === 'verified' ? 'success' : ($driver->verification_status === 'rejected' ? 'danger' : 'warning') }}">
                                    Status: {{ ucfirst($driver->verification_status) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Application Details -->
            <div class="col-md-8">
                <!-- Personal Information -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-user"></i> Personal Information
                            <span class="badge badge-{{ $progress['breakdown']['personal_info']['completed'] ? 'success' : 'warning' }} ml-2">
                                {{ $progress['breakdown']['personal_info']['completed'] ? 'Complete' : 'Incomplete' }}
                            </span>
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <dl class="row">
                                    <dt class="col-sm-5">Full Name:</dt>
                                    <dd class="col-sm-7">{{ $driver->full_name }}</dd>

                                    <dt class="col-sm-5">Date of Birth:</dt>
                                    <dd class="col-sm-7">{{ $driver->personalInfo?->date_of_birth?->format('M d, Y') ?: 'Not provided' }}</dd>

                                    <dt class="col-sm-5">Gender:</dt>
                                    <dd class="col-sm-7">{{ $driver->personalInfo?->gender ? ucfirst($driver->personalInfo->gender) : 'Not specified' }}</dd>

                                    <dt class="col-sm-5">Nationality:</dt>
                                    <dd class="col-sm-7">{{ $driver->personalInfo?->nationality ?: 'Not specified' }}</dd>
                                </dl>
                            </div>
                            <div class="col-md-6">
                                <dl class="row">
                                    <dt class="col-sm-5">Email:</dt>
                                    <dd class="col-sm-7">{{ $driver->email }}</dd>

                                    <dt class="col-sm-5">Phone:</dt>
                                    <dd class="col-sm-7">{{ $driver->phone }}</dd>

                                    <dt class="col-sm-5">Address:</dt>
                                    <dd class="col-sm-7">{{ $driver->personalInfo?->address ?: 'Not provided' }}</dd>

                                    <dt class="col-sm-5">Marital Status:</dt>
                                    <dd class="col-sm-7">{{ $driver->personalInfo?->marital_status ? ucfirst($driver->personalInfo->marital_status) : 'Not specified' }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Emergency Contact -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-phone"></i> Emergency Contact
                            <span class="badge badge-{{ $progress['breakdown']['contact_info']['completed'] ? 'success' : 'warning' }} ml-2">
                                {{ $progress['breakdown']['contact_info']['completed'] ? 'Complete' : 'Incomplete' }}
                            </span>
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($driver->personalInfo)
                            <div class="row">
                                <div class="col-md-6">
                                    <dl class="row">
                                        <dt class="col-sm-5">Name:</dt>
                                        <dd class="col-sm-7">{{ $driver->personalInfo->name ?: 'Not provided' }}</dd>

                                        <dt class="col-sm-5">Relationship:</dt>
                                        <dd class="col-sm-7">{{ $driver->personalInfo->relationship ? ucfirst($driver->personalInfo->relationship) : 'Not specified' }}</dd>
                                    </dl>
                                </div>
                                <div class="col-md-6">
                                    <dl class="row">
                                        <dt class="col-sm-5">Phone:</dt>
                                        <dd class="col-sm-7">{{ $driver->personalInfo->phone ?: 'Not provided' }}</dd>

                                        <dt class="col-sm-5">Email:</dt>
                                        <dd class="col-sm-7">{{ $driver->personalInfo->email ?: 'Not provided' }}</dd>
                                    </dl>
                                </div>
                            </div>
                        @else
                            <p class="text-muted">No emergency contact information provided.</p>
                        @endif
                    </div>
                </div>

                <!-- Documents -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-file-upload"></i> Documents
                            <span class="badge badge-{{ $progress['breakdown']['documents']['completed'] ? 'success' : 'warning' }} ml-2">
                                {{ $progress['breakdown']['documents']['completed'] ? 'Complete' : 'Incomplete' }}
                            </span>
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($driver->documents->count() > 0)
                            <div class="row">
                                @foreach($driver->documents as $document)
                                    <div class="col-md-4 mb-3">
                                        <div class="card h-100">
                                            <div class="card-body text-center">
                                                <i class="fas fa-{{ $document->document_type === 'profile_picture' ? 'user-circle' : 'file' }} fa-2x text-primary mb-2"></i>
                                                <h6 class="card-title">{{ ucfirst(str_replace('_', ' ', $document->document_type)) }}</h6>
                                                <p class="card-text small text-muted">{{ $document->file_name }}</p>
                                                <span class="badge badge-{{ $document->verification_status === 'approved' ? 'success' : ($document->verification_status === 'pending' ? 'warning' : 'danger') }}">
                                                    {{ ucfirst($document->verification_status) }}
                                                </span>
                                                @if($document->document_path)
                                                    <br>
                                                    <a href="{{ asset('storage/' . $document->document_path) }}" target="_blank" class="btn btn-sm btn-outline-primary mt-2">
                                                        <i class="fas fa-eye"></i> View
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted">No documents uploaded.</p>
                        @endif
                    </div>
                </div>

                <!-- Banking Information -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-university"></i> Banking Information
                            <span class="badge badge-{{ $progress['breakdown']['banking']['completed'] ? 'success' : 'warning' }} ml-2">
                                {{ $progress['breakdown']['banking']['completed'] ? 'Complete' : 'Incomplete' }}
                            </span>
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($driver->primaryBankingDetail)
                            <div class="row">
                                <div class="col-md-6">
                                    <dl class="row">
                                        <dt class="col-sm-5">Account Name:</dt>
                                        <dd class="col-sm-7">{{ $driver->primaryBankingDetail->account_name }}</dd>

                                        <dt class="col-sm-5">Account Number:</dt>
                                        <dd class="col-sm-7">{{ $driver->primaryBankingDetail->account_number }}</dd>

                                        <dt class="col-sm-5">Bank Name:</dt>
                                        <dd class="col-sm-7">{{ $driver->primaryBankingDetail->bank_name }}</dd>
                                    </dl>
                                </div>
                                <div class="col-md-6">
                                    <dl class="row">
                                        <dt class="col-sm-5">Account Type:</dt>
                                        <dd class="col-sm-7">{{ $driver->primaryBankingDetail->account_type ? ucfirst($driver->primaryBankingDetail->account_type) : 'Not specified' }}</dd>

                                        <dt class="col-sm-5">Verification Status:</dt>
                                        <dd class="col-sm-7">
                                            <span class="badge badge-{{ $driver->primaryBankingDetail->is_verified ? 'success' : 'warning' }}">
                                                {{ $driver->primaryBankingDetail->is_verified ? 'Verified' : 'Pending' }}
                                            </span>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        @else
                            <p class="text-muted">No banking information provided.</p>
                        @endif
                    </div>
                </div>

                <!-- Professional Information -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-briefcase"></i> Professional Information
                            <span class="badge badge-{{ $progress['breakdown']['professional']['completed'] ? 'success' : 'warning' }} ml-2">
                                {{ $progress['breakdown']['professional']['completed'] ? 'Complete' : 'Incomplete' }}
                            </span>
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($driver->performance)
                            <div class="row">
                                <div class="col-md-6">
                                    <dl class="row">
                                        <dt class="col-sm-5">License Number:</dt>
                                        <dd class="col-sm-7">{{ $driver->performance->license_number ?: 'Not provided' }}</dd>

                                        <dt class="col-sm-5">License Expiry:</dt>
                                        <dd class="col-sm-7">{{ $driver->performance->license_expiry_date?->format('M d, Y') ?: 'Not provided' }}</dd>

                                        <dt class="col-sm-5">Experience:</dt>
                                        <dd class="col-sm-7">{{ $driver->performance->years_of_experience ? $driver->performance->years_of_experience . ' years' : 'Not specified' }}</dd>
                                    </dl>
                                </div>
                                <div class="col-md-6">
                                    <dl class="row">
                                        <dt class="col-sm-5">Vehicle Type:</dt>
                                        <dd class="col-sm-7">{{ $driver->performance->vehicle_type ? ucfirst($driver->performance->vehicle_type) : 'Not specified' }}</dd>

                                        <dt class="col-sm-5">Jobs Completed:</dt>
                                        <dd class="col-sm-7">{{ $driver->performance->total_jobs_completed ?? 0 }}</dd>

                                        <dt class="col-sm-5">Average Rating:</dt>
                                        <dd class="col-sm-7">{{ number_format($driver->performance->average_rating ?? 0, 1) }}/5.0</dd>
                                    </dl>
                                </div>
                            </div>
                        @else
                            <p class="text-muted">No professional information provided.</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Review Actions Sidebar -->
            <div class="col-md-4">
                <!-- Review Decision -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-gavel"></i> Review Decision
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.superadmin.drivers.onboarding.review.process', $driver) }}">
                            @csrf

                            <div class="form-group">
                                <label for="decision">Decision <span class="text-danger">*</span></label>
                                <select class="form-control @error('decision') is-invalid @enderror" id="decision" name="decision" required>
                                    <option value="">Select Decision</option>
                                    <option value="approve" {{ old('decision') == 'approve' ? 'selected' : '' }}>Approve Application</option>
                                    <option value="reject" {{ old('decision') == 'reject' ? 'selected' : '' }}>Reject Application</option>
                                </select>
                                @error('decision')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="notes">Review Notes <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="4"
                                          placeholder="Provide detailed feedback about your decision..." required>{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">These notes will be sent to the driver</small>
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-success btn-block" id="submitBtn">
                                    <i class="fas fa-check"></i> Submit Review
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Application Checklist -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-clipboard-check"></i> Application Checklist
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="checklist">
                            @foreach($progress['breakdown'] as $stepKey => $stepData)
                                <div class="checklist-item mb-2">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-{{ $stepData['completed'] ? 'check-circle text-success' : 'circle text-muted' }} mr-2"></i>
                                        <span class="{{ $stepData['completed'] ? '' : 'text-muted' }}">{{ $stepData['name'] }}</span>
                                    </div>
                                    <small class="text-muted ml-4">{{ $stepData['progress'] }}% complete</small>
                                </div>
                            @endforeach
                        </div>

                        <hr>

                        <div class="text-center">
                            <div class="h5 mb-0">{{ $progress['overall_progress'] }}%</div>
                            <small class="text-muted">Overall Completion</small>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-bolt"></i> Quick Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('admin.superadmin.drivers.show', $driver) }}" class="btn btn-outline-info btn-block mb-2">
                            <i class="fas fa-eye"></i> View Full Profile
                        </a>
                        <a href="{{ route('admin.superadmin.drivers.edit', $driver) }}" class="btn btn-outline-warning btn-block mb-2">
                            <i class="fas fa-edit"></i> Edit Driver
                        </a>
                        <button type="button" class="btn btn-outline-secondary btn-block" onclick="printApplication()">
                            <i class="fas fa-print"></i> Print Application
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
.badge-lg {
    font-size: 0.875rem;
    padding: 0.5rem 0.75rem;
}

.checklist-item {
    padding: 0.25rem 0;
}

.checklist-item i {
    width: 16px;
}

.dl-horizontal dt {
    text-align: left;
    width: auto;
    margin-right: 1rem;
    font-weight: 600;
}

.dl-horizontal dd {
    margin-left: 0;
}

.card-title {
    margin: 0;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Form validation
    $('form').on('submit', function(e) {
        const decision = $('#decision').val();
        const notes = $('#notes').val().trim();

        if (!decision) {
            e.preventDefault();
            alert('Please select a decision.');
            return false;
        }

        if (!notes) {
            e.preventDefault();
            alert('Please provide review notes.');
            return false;
        }

        // Confirm decision
        const decisionText = decision === 'approve' ? 'approve' : 'reject';
        if (!confirm(`Are you sure you want to ${decisionText} this application? This action cannot be undone.`)) {
            e.preventDefault();
            return false;
        }

        // Disable submit button to prevent double submission
        $('#submitBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
    });

    // Auto-resize textarea
    $('#notes').on('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });
});

function printApplication() {
    window.print();
}
</script>
@endpush
