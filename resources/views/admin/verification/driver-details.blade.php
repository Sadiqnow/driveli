@extends('layouts.admin_cdn')

@section('title', 'Driver Verification Details')

@section('content')
<div class="container-fluid">
    <!-- Driver Information Header -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h4>{{ $driver->first_name }} {{ $driver->last_name }}</h4>
                            <p class="text-muted mb-1">{{ $driver->email }}</p>
                            <p class="text-muted mb-1">Phone: {{ $driver->phone_number }}</p>
                            <p class="text-muted">NIN: {{ $driver->nin ?? 'Not provided' }}</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <span class="badge badge-{{ $driver->verification_status === 'verified' ? 'success' : ($driver->verification_status === 'failed' ? 'danger' : 'warning') }} badge-lg">
                                {{ ucfirst(str_replace('_', ' ', $driver->verification_status)) }}
                            </span>
                            <h3 class="mt-2">Score: {{ number_format($driver->overall_verification_score ?? 0, 1) }}%</h3>
                            <p class="text-muted">
                                Started: {{ $driver->verification_started_at ? $driver->verification_started_at->format('M d, Y H:i') : 'Not started' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Verification Components -->
    <div class="row">
        <!-- Individual Verification Results -->
        <div class="col-md-8">
            <!-- NIN Verification -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">NIN Verification</h6>
                </div>
                <div class="card-body">
                    @php
                        $ninVerification = $verifications->where('verification_type', 'nin')->first();
                    @endphp
                    @if($ninVerification)
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Status:</strong> 
                                    <span class="badge badge-{{ $ninVerification->status === 'completed' ? 'success' : ($ninVerification->status === 'failed' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($ninVerification->status) }}
                                    </span>
                                </p>
                                <p><strong>Score:</strong> {{ number_format($ninVerification->verification_score ?? 0, 1) }}%</p>
                                <p><strong>Last Attempt:</strong> {{ $ninVerification->last_attempt_at ? \Carbon\Carbon::parse($ninVerification->last_attempt_at)->format('M d, Y H:i') : 'N/A' }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Attempts:</strong> {{ $ninVerification->attempt_count }}</p>
                                @if($ninVerification->notes)
                                    <p><strong>Notes:</strong> {{ $ninVerification->notes }}</p>
                                @endif
                            </div>
                        </div>
                        @if($ninVerification->verification_data)
                            <div class="mt-3">
                                <h6>Verification Details:</h6>
                                <pre class="bg-light p-2 small">{{ json_encode(json_decode($ninVerification->verification_data), JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        @endif
                    @else
                        <p class="text-muted">NIN verification not performed</p>
                    @endif
                </div>
            </div>

            <!-- License Verification -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">Driver's License Verification</h6>
                </div>
                <div class="card-body">
                    @php
                        $licenseVerification = $verifications->where('verification_type', 'license')->first();
                    @endphp
                    @if($licenseVerification)
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Status:</strong> 
                                    <span class="badge badge-{{ $licenseVerification->status === 'completed' ? 'success' : ($licenseVerification->status === 'failed' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($licenseVerification->status) }}
                                    </span>
                                </p>
                                <p><strong>Score:</strong> {{ number_format($licenseVerification->verification_score ?? 0, 1) }}%</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>License Number:</strong> {{ $driver->drivers_license_number ?? 'Not provided' }}</p>
                                <p><strong>License Class:</strong> {{ $driver->license_class ?? 'Not provided' }}</p>
                            </div>
                        </div>
                    @else
                        <p class="text-muted">License verification not performed</p>
                    @endif
                </div>
            </div>

            <!-- BVN Verification -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">BVN Verification</h6>
                </div>
                <div class="card-body">
                    @php
                        $bvnVerification = $verifications->where('verification_type', 'bvn')->first();
                    @endphp
                    @if($bvnVerification)
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Status:</strong> 
                                    <span class="badge badge-{{ $bvnVerification->status === 'completed' ? 'success' : ($bvnVerification->status === 'failed' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($bvnVerification->status) }}
                                    </span>
                                </p>
                                <p><strong>Score:</strong> {{ number_format($bvnVerification->verification_score ?? 0, 1) }}%</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>BVN:</strong> {{ $driver->bvn ? substr($driver->bvn, 0, 3) . '****' . substr($driver->bvn, -3) : 'Not provided' }}</p>
                            </div>
                        </div>
                    @else
                        <p class="text-muted">BVN verification not performed</p>
                    @endif
                </div>
            </div>

            <!-- Document OCR Results -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">Document OCR Results</h6>
                </div>
                <div class="card-body">
                    @if($ocrResults && $ocrResults->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Document Type</th>
                                        <th>Status</th>
                                        <th>Confidence</th>
                                        <th>Provider</th>
                                        <th>Processed</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($ocrResults as $result)
                                    <tr>
                                        <td>{{ ucfirst($result->document_type) }}</td>
                                        <td>
                                            <span class="badge badge-{{ $result->processing_status === 'completed' ? 'success' : ($result->processing_status === 'failed' ? 'danger' : 'warning') }}">
                                                {{ ucfirst($result->processing_status) }}
                                            </span>
                                        </td>
                                        <td>{{ number_format($result->confidence_score ?? 0, 1) }}%</td>
                                        <td>{{ $result->ocr_provider }}</td>
                                        <td>{{ \Carbon\Carbon::parse($result->created_at)->format('M d, H:i') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">No OCR results available</p>
                    @endif
                </div>
            </div>

            <!-- Referee Verifications -->
            <div class="card mb-3">
                <div class="card-header">
                    <h6 class="mb-0">Referee Verifications</h6>
                </div>
                <div class="card-body">
                    @if($refereeVerifications && $refereeVerifications->count() > 0)
                        @foreach($refereeVerifications as $referee)
                        <div class="border-bottom pb-3 mb-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>{{ $referee->referee_name }}</strong></p>
                                    <p class="text-muted">{{ $referee->referee_occupation }}</p>
                                    <p class="text-muted">Relationship: {{ $referee->relationship_to_driver }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Status:</strong> 
                                        <span class="badge badge-{{ $referee->verification_status === 'verified' ? 'success' : ($referee->verification_status === 'failed' ? 'danger' : 'warning') }}">
                                            {{ ucfirst($referee->verification_status) }}
                                        </span>
                                    </p>
                                    <p><strong>Score:</strong> {{ number_format($referee->credibility_score ?? 0, 1) }}%</p>
                                    <p><strong>Years Known:</strong> {{ $referee->years_known }} years</p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <p class="text-muted">No referee verifications available</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Actions Sidebar -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Actions</h6>
                </div>
                <div class="card-body">
                    @if($driver->verification_status === 'requires_manual_review')
                        <!-- Approve Button -->
                        <button type="button" class="btn btn-success btn-block mb-2" data-bs-toggle="modal" data-bs-target="#approveModal">
                            <i class="fas fa-check"></i> Approve Verification
                        </button>
                        
                        <!-- Reject Button -->
                        <button type="button" class="btn btn-danger btn-block mb-2" data-bs-toggle="modal" data-bs-target="#rejectModal">
                            <i class="fas fa-times"></i> Reject Verification
                        </button>
                    @endif

                    @if($driver->verification_status === 'failed')
                        <form method="POST" action="{{ route('admin.verification.retry', $driver->id) }}">
                            @csrf
                            <button type="submit" class="btn btn-warning btn-block mb-2">
                                <i class="fas fa-redo"></i> Retry Verification
                            </button>
                        </form>
                    @endif

                    <a href="{{ route('admin.verification.dashboard') }}" class="btn btn-secondary btn-block">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>

            <!-- API Logs -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0">Recent API Calls</h6>
                </div>
                <div class="card-body">
                    @if($apiLogs && $apiLogs->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Provider</th>
                                        <th>Status</th>
                                        <th>Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($apiLogs->take(5) as $log)
                                    <tr>
                                        <td>{{ strtoupper($log->api_provider) }}</td>
                                        <td>
                                            <span class="badge badge-{{ $log->is_successful ? 'success' : 'danger' }}">
                                                {{ $log->is_successful ? 'Success' : 'Failed' }}
                                            </span>
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($log->created_at)->format('M d H:i') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">No API logs available</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.verification.approve', $driver->id) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Approve Verification</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="override_score" class="form-label">Override Score (Optional)</label>
                        <input type="number" name="override_score" id="override_score" class="form-control" 
                               min="0" max="100" step="0.1" value="{{ $driver->overall_verification_score }}">
                        <div class="form-text">Leave blank to use current score</div>
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea name="notes" id="notes" class="form-control" rows="3" 
                                  placeholder="Add notes for approval..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Approve</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.verification.reject', $driver->id) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Reject Verification</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="rejection_reason" class="form-label">Rejection Reason *</label>
                        <textarea name="rejection_reason" id="rejection_reason" class="form-control" rows="4" 
                                  required placeholder="Please provide a detailed reason for rejection..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection