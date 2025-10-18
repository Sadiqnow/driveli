<div class="row">
    <div class="col-md-12">
        <div class="alert alert-superadmin">
            <h5><i class="fas fa-shield-alt"></i> KYC (Know Your Customer) Verification</h5>
            <p class="mb-0">Complete the KYC process to verify the driver's identity and ensure compliance with regulatory requirements.</p>
        </div>
    </div>
</div>

<!-- KYC Status Overview -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card card-superadmin">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-clipboard-check text-superadmin"></i> KYC Verification Status
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="text-center">
                            <div class="kyc-status-icon mb-2">
                                <i class="fas fa-id-card fa-2x {{ $driver->kyc_status === 'completed' ? 'text-success' : 'text-muted' }}"></i>
                            </div>
                            <h6>Personal Info</h6>
                            <span class="badge {{ $driver->kyc_status === 'completed' ? 'badge-success' : 'badge-secondary' }}">
                                {{ $driver->kyc_status === 'completed' ? 'Completed' : 'Pending' }}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <div class="kyc-status-icon mb-2">
                                <i class="fas fa-file-upload fa-2x {{ $driver->documents->where('type', 'id_card')->isNotEmpty() ? 'text-success' : 'text-muted' }}"></i>
                            </div>
                            <h6>ID Documents</h6>
                            <span class="badge {{ $driver->documents->where('type', 'id_card')->isNotEmpty() ? 'badge-success' : 'badge-secondary' }}">
                                {{ $driver->documents->where('type', 'id_card')->isNotEmpty() ? 'Uploaded' : 'Pending' }}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <div class="kyc-status-icon mb-2">
                                <i class="fas fa-address-card fa-2x {{ $driver->documents->where('type', 'license')->isNotEmpty() ? 'text-success' : 'text-muted' }}"></i>
                            </div>
                            <h6>License</h6>
                            <span class="badge {{ $driver->documents->where('type', 'license')->isNotEmpty() ? 'badge-success' : 'badge-secondary' }}">
                                {{ $driver->documents->where('type', 'license')->isNotEmpty() ? 'Uploaded' : 'Pending' }}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="text-center">
                            <div class="kyc-status-icon mb-2">
                                <i class="fas fa-robot fa-2x {{ $driver->ocr_verified_at ? 'text-success' : 'text-muted' }}"></i>
                            </div>
                            <h6>OCR Verification</h6>
                            <span class="badge {{ $driver->ocr_verified_at ? 'badge-success' : 'badge-secondary' }}">
                                {{ $driver->ocr_verified_at ? 'Verified' : 'Pending' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- KYC Form Sections -->
<div class="row">
    <!-- Personal Information Verification -->
    <div class="col-md-6">
        <div class="card card-superadmin">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-user-check text-superadmin"></i> Personal Information Verification
                </h5>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label for="kyc_personal_verified">Personal Information Verified</label>
                    <select class="form-control @error('kyc_personal_verified') is-invalid @enderror"
                            id="kyc_personal_verified" name="kyc_personal_verified">
                        <option value="0" {{ old('kyc_personal_verified', $driver->kyc_personal_verified ?? 0) == 0 ? 'selected' : '' }}>Not Verified</option>
                        <option value="1" {{ old('kyc_personal_verified', $driver->kyc_personal_verified ?? 0) == 1 ? 'selected' : '' }}>Verified</option>
                    </select>
                    @error('kyc_personal_verified')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="kyc_personal_notes">Verification Notes</label>
                    <textarea class="form-control @error('kyc_personal_notes') is-invalid @enderror"
                              id="kyc_personal_notes" name="kyc_personal_notes" rows="3"
                              placeholder="Notes about personal information verification">{{ old('kyc_personal_notes', $driver->kyc_personal_notes) }}</textarea>
                    @error('kyc_personal_notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>

    <!-- Document Verification -->
    <div class="col-md-6">
        <div class="card card-superadmin">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-file-check text-superadmin"></i> Document Verification
                </h5>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label for="kyc_documents_verified">Documents Verified</label>
                    <select class="form-control @error('kyc_documents_verified') is-invalid @enderror"
                            id="kyc_documents_verified" name="kyc_documents_verified">
                        <option value="0" {{ old('kyc_documents_verified', $driver->kyc_documents_verified ?? 0) == 0 ? 'selected' : '' }}>Not Verified</option>
                        <option value="1" {{ old('kyc_documents_verified', $driver->kyc_documents_verified ?? 0) == 1 ? 'selected' : '' }}>Verified</option>
                    </select>
                    @error('kyc_documents_verified')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="kyc_document_notes">Document Verification Notes</label>
                    <textarea class="form-control @error('kyc_document_notes') is-invalid @enderror"
                              id="kyc_document_notes" name="kyc_document_notes" rows="3"
                              placeholder="Notes about document verification">{{ old('kyc_document_notes', $driver->kyc_document_notes) }}</textarea>
                    @error('kyc_document_notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>
</div>

<!-- OCR Verification Section -->
<div class="row">
    <div class="col-md-12">
        <div class="card card-superadmin">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-robot text-superadmin"></i> OCR Verification Results
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="ocr_verified">OCR Verification Status</label>
                            <select class="form-control @error('ocr_verified') is-invalid @enderror"
                                    id="ocr_verified" name="ocr_verified">
                                <option value="0" {{ old('ocr_verified', $driver->ocr_verified ?? 0) == 0 ? 'selected' : '' }}>Not Verified</option>
                                <option value="1" {{ old('ocr_verified', $driver->ocr_verified ?? 0) == 1 ? 'selected' : '' }}>Verified</option>
                                <option value="2" {{ old('ocr_verified', $driver->ocr_verified ?? 0) == 2 ? 'selected' : '' }}>Failed</option>
                            </select>
                            @error('ocr_verified')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="ocr_confidence_score">OCR Confidence Score</label>
                            <input type="number" step="0.01" min="0" max="1"
                                   class="form-control @error('ocr_confidence_score') is-invalid @enderror"
                                   id="ocr_confidence_score" name="ocr_confidence_score"
                                   value="{{ old('ocr_confidence_score', $driver->ocr_confidence_score) }}"
                                   placeholder="0.00 - 1.00">
                            @error('ocr_confidence_score')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="ocr_notes">OCR Verification Notes</label>
                    <textarea class="form-control @error('ocr_notes') is-invalid @enderror"
                              id="ocr_notes" name="ocr_notes" rows="3"
                              placeholder="Details about OCR verification results">{{ old('ocr_notes', $driver->ocr_notes) }}</textarea>
                    @error('ocr_notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Overall KYC Status -->
<div class="row">
    <div class="col-md-12">
        <div class="card card-superadmin">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-check-circle text-superadmin"></i> Overall KYC Status
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="kyc_status">KYC Status <span class="text-danger">*</span></label>
                            <select class="form-control @error('kyc_status') is-invalid @enderror"
                                    id="kyc_status" name="kyc_status" required>
                                <option value="">Select Status</option>
                                <option value="pending" {{ old('kyc_status', $driver->kyc_status ?? 'pending') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="in_review" {{ old('kyc_status', $driver->kyc_status) == 'in_review' ? 'selected' : '' }}>In Review</option>
                                <option value="completed" {{ old('kyc_status', $driver->kyc_status) == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="failed" {{ old('kyc_status', $driver->kyc_status) == 'failed' ? 'selected' : '' }}>Failed</option>
                                <option value="bypassed" {{ old('kyc_status', $driver->kyc_status) == 'bypassed' ? 'selected' : '' }}>Bypassed (Super Admin)</option>
                            </select>
                            @error('kyc_status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="kyc_verified_by">Verified By</label>
                            <input type="text" class="form-control" id="kyc_verified_by"
                                   value="{{ auth('admin')->user()->name ?? 'System' }}" readonly>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="kyc_final_notes">Final KYC Notes</label>
                    <textarea class="form-control @error('kyc_final_notes') is-invalid @enderror"
                              id="kyc_final_notes" name="kyc_final_notes" rows="3"
                              placeholder="Final notes and decision rationale">{{ old('kyc_final_notes', $driver->kyc_final_notes) }}</textarea>
                    @error('kyc_final_notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="alert alert-superadmin">
                    <i class="fas fa-info-circle"></i>
                    <strong>Super Admin Note:</strong> As a Super Administrator, you have the authority to bypass KYC requirements if necessary.
                    However, bypassing should only be done for legitimate business reasons and will be logged in the audit trail.
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-update KYC status based on individual verifications
    function updateOverallKycStatus() {
        const personalVerified = $('#kyc_personal_verified').val() == '1';
        const documentsVerified = $('#kyc_documents_verified').val() == '1';
        const ocrVerified = $('#ocr_verified').val() == '1';

        let overallStatus = 'pending';

        if (personalVerified && documentsVerified && ocrVerified) {
            overallStatus = 'completed';
        } else if (personalVerified || documentsVerified || ocrVerified) {
            overallStatus = 'in_review';
        }

        $('#kyc_status').val(overallStatus);
    }

    // Bind change events
    $('#kyc_personal_verified, #kyc_documents_verified, #ocr_verified').on('change', updateOverallKycStatus);

    // Super Admin bypass warning
    $('#kyc_status').on('change', function() {
        if ($(this).val() === 'bypassed') {
            if (!confirm('You are bypassing KYC verification requirements. This action will be logged. Continue?')) {
                $(this).val('pending');
            }
        }
    });

    // OCR confidence score validation
    $('#ocr_confidence_score').on('input', function() {
        const value = parseFloat($(this).val());
        if (value < 0 || value > 1) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });
});
</script>
@endpush

<style>
.kyc-status-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    background: rgba(139, 92, 246, 0.1);
}

.card-superadmin {
    border-left: 4px solid #8B5CF6;
    box-shadow: 0 2px 10px rgba(139, 92, 246, 0.1);
}

.alert-superadmin {
    border-left: 4px solid #8B5CF6;
    background: linear-gradient(90deg, #F3E8FF 0%, rgba(139, 92, 246, 0.05) 100%);
    border-color: rgba(139, 92, 246, 0.2);
}

.text-superadmin {
    color: #8B5CF6 !important;
}
</style>
