@extends('layouts.admin_master')

@section('title', 'Superadmin - Driver Onboarding')

@section('content_header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Driver Onboarding - {{ $driver->driver_id }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.superadmin.dashboard') }}">Super Admin</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.superadmin.drivers.index') }}">Drivers</a></li>
                        <li class="breadcrumb-item active">Onboarding</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid">
        <!-- Progress Bar -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="progress-group">
                            <div class="progress-group-header">
                                <div class="d-flex justify-content-between">
                                    <span>Onboarding Progress</span>
                                    <span>{{ $progress['overall_progress'] }}% Complete</span>
                                </div>
                            </div>
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar bg-success" role="progressbar"
                                     style="width: {{ $progress['overall_progress'] }}%"
                                     aria-valuenow="{{ $progress['overall_progress'] }}"
                                     aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>
                        </div>

                        <!-- Step Indicators -->
                        <div class="row mt-3">
                            @foreach($steps as $step)
                                <div class="col-md-2">
                                    <div class="step-indicator {{ $step['key'] === $step ? 'active' : ($progress['breakdown'][$step['key']]['completed'] ? 'completed' : 'pending') }}">
                                        <div class="step-number">
                                            @if($progress['breakdown'][$step['key']]['completed'])
                                                <i class="fas fa-check"></i>
                                            @else
                                                {{ array_search($step['key'], array_column($steps, 'key')) + 1 }}
                                            @endif
                                        </div>
                                        <div class="step-label">{{ $step['name'] }}</div>
                                        @if($progress['breakdown'][$step['key']]['completed'])
                                            <small class="text-success">{{ $progress['breakdown'][$step['key']]['progress'] }}%</small>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Main Form -->
            <div class="col-md-8">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-{{ $step === 'personal_info' ? 'user' : ($step === 'contact_info' ? 'phone' : ($step === 'documents' ? 'file-upload' : ($step === 'banking' ? 'university' : ($step === 'professional' ? 'briefcase' : 'shield-alt')))) }}"></i>
                            {{ $progress['breakdown'][$step]['name'] }}
                        </h3>
                        <div class="card-tools">
                            <span class="badge badge-{{ $progress['breakdown'][$step]['completed'] ? 'success' : 'warning' }}">
                                {{ $progress['breakdown'][$step]['progress'] }}% Complete
                            </span>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('admin.superadmin.drivers.onboarding.step.process', [$driver, $step]) }}" enctype="multipart/form-data">
                        @csrf

                        <div class="card-body">
                            @include("admin.superadmin.drivers.onboarding.steps.{$step}")
                        </div>

                        <div class="card-footer">
                            <div class="d-flex justify-content-between">
                                <div>
                                    @if($step !== 'personal_info')
                                        <button type="button" class="btn btn-secondary" onclick="goToPreviousStep()">
                                            <i class="fas fa-arrow-left"></i> Previous
                                        </button>
                                    @endif
                                    <button type="button" class="btn btn-outline-secondary" onclick="saveDraft()">
                                        <i class="fas fa-save"></i> Save Draft
                                    </button>
                                </div>

                                <div>
                                    @if($progress['overall_progress'] >= 100)
                                        <a href="{{ route('admin.superadmin.drivers.onboarding.review', $driver) }}" class="btn btn-success">
                                            <i class="fas fa-check"></i> Review & Submit
                                        </a>
                                    @else
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-arrow-right"></i> Next Step
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-md-4">
                <!-- Current Step Status -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-info-circle"></i> Step Status</h3>
                    </div>
                    <div class="card-body">
                        <h5>{{ $progress['breakdown'][$step]['name'] }}</h5>
                        <p class="text-muted">{{ $progress['breakdown'][$step]['completed'] ? 'Completed' : 'In Progress' }}</p>

                        <div class="progress mb-3">
                            <div class="progress-bar" role="progressbar"
                                 style="width: {{ $progress['breakdown'][$step]['progress'] }}%">
                            </div>
                        </div>

                        <small class="text-muted">
                            {{ $progress['breakdown'][$step]['completed_fields'] }} of {{ $progress['breakdown'][$step]['total_fields'] }} fields completed
                        </small>

                        @if(!$progress['breakdown'][$step]['completed'])
                            <div class="mt-3">
                                <h6>Missing Information:</h6>
                                <ul class="list-unstyled">
                                    @foreach($progress['breakdown'][$step]['fields'] as $fieldKey => $fieldData)
                                        @if(!$fieldData['completed'])
                                            <li><i class="fas fa-times text-danger"></i> {{ ucfirst(str_replace('_', ' ', $fieldKey)) }}</li>
                                        @endif
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-bolt"></i> Quick Actions</h3>
                    </div>
                    <div class="card-body">
                        <button type="button" class="btn btn-outline-primary btn-block mb-2" onclick="showProgressSummary()">
                            <i class="fas fa-chart-bar"></i> View Progress Summary
                        </button>
                        <a href="{{ route('admin.superadmin.drivers.show', $driver) }}" class="btn btn-outline-info btn-block mb-2">
                            <i class="fas fa-eye"></i> View Driver Profile
                        </a>
                        <button type="button" class="btn btn-outline-danger btn-block" onclick="cancelOnboarding()">
                            <i class="fas fa-times"></i> Cancel Onboarding
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Summary Modal -->
    <div class="modal fade" id="progressModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Onboarding Progress Summary</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Overall Progress</h6>
                            <div class="progress mb-3">
                                <div class="progress-bar bg-success" style="width: {{ $progress['overall_progress'] }}%">
                                    {{ $progress['overall_progress'] }}%
                                </div>
                            </div>
                            <p><strong>{{ $progress['completed_steps'] }} of {{ $progress['total_steps'] }} steps completed</strong></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Driver Information</h6>
                            <p><strong>ID:</strong> {{ $driver->driver_id }}</p>
                            <p><strong>Name:</strong> {{ $driver->full_name }}</p>
                            <p><strong>Email:</strong> {{ $driver->email }}</p>
                            <p><strong>Status:</strong> <span class="badge badge-{{ $driver->status === 'active' ? 'success' : 'warning' }}">{{ ucfirst($driver->status) }}</span></p>
                        </div>
                    </div>

                    <hr>

                    <h6>Step Details</h6>
                    <div class="row">
                        @foreach($progress['breakdown'] as $stepKey => $stepData)
                            <div class="col-md-6 mb-3">
                                <div class="border rounded p-3">
                                    <div class="d-flex justify-content-between">
                                        <strong>{{ $stepData['name'] }}</strong>
                                        <span class="badge badge-{{ $stepData['completed'] ? 'success' : 'warning' }}">
                                            {{ $stepData['completed'] ? 'Complete' : 'Pending' }}
                                        </span>
                                    </div>
                                    <div class="progress mt-2" style="height: 8px;">
                                        <div class="progress-bar {{ $stepData['completed'] ? 'bg-success' : 'bg-warning' }}"
                                             style="width: {{ $stepData['progress'] }}%">
                                        </div>
                                    </div>
                                    <small class="text-muted">{{ $stepData['progress'] }}% complete</small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
.step-indicator {
    text-align: center;
    margin-bottom: 1rem;
}

.step-number {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: #6c757d;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 0.5rem;
    font-weight: bold;
}

.step-indicator.active .step-number {
    background-color: #007bff;
}

.step-indicator.completed .step-number {
    background-color: #28a745;
}

.step-label {
    font-size: 0.875rem;
    font-weight: 500;
}

.progress-group-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
}

.card-tools .badge {
    font-size: 0.75rem;
}

.btn-outline-secondary {
    border-color: #6c757d;
    color: #6c757d;
}

.btn-outline-secondary:hover {
    background-color: #6c757d;
    border-color: #6c757d;
}
</style>
@endpush

@push('scripts')
<script>
function goToPreviousStep() {
    if (confirm('Are you sure you want to go back to the previous step? Any unsaved changes will be lost.')) {
        // This would need to be implemented based on the current step
        window.history.back();
    }
}

function saveDraft() {
    if (confirm('Save current progress as draft?')) {
        // Submit form with draft flag
        const form = document.querySelector('form');
        const draftInput = document.createElement('input');
        draftInput.type = 'hidden';
        draftInput.name = 'save_draft';
        draftInput.value = '1';
        form.appendChild(draftInput);
        form.submit();
    }
}

function showProgressSummary() {
    $('#progressModal').modal('show');
}

function cancelOnboarding() {
    if (confirm('Are you sure you want to cancel this onboarding process? All progress will be lost.')) {
        window.location.href = '{{ route("admin.superadmin.drivers.index") }}';
    }
}

// Auto-save functionality (optional)
let autoSaveTimer;
function startAutoSave() {
    clearTimeout(autoSaveTimer);
    autoSaveTimer = setTimeout(() => {
        // Could implement auto-save here
        console.log('Auto-save triggered');
    }, 30000); // 30 seconds
}

// Start auto-save on form changes
$('form input, form select, form textarea').on('input change', startAutoSave);
</script>
@endpush
