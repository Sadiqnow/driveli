<!-- Manual OCR Override Modal -->
<div class="modal fade" id="ocrOverrideModal" tabindex="-1" aria-labelledby="ocrOverrideModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="ocrOverrideModalLabel">
                    <i class="fas fa-user-cog"></i> Manual OCR Override
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="ocrOverrideForm" onsubmit="submitOCROverride(event)">
                <div class="modal-body">
                    <input type="hidden" id="overrideDriverId" name="driver_id">
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Warning:</strong> Manual override will replace OCR verification results. This action requires admin approval and cannot be undone.
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="verificationType" class="form-label">
                                    <i class="fas fa-file-alt"></i> Document Type *
                                </label>
                                <select class="form-control" id="verificationType" name="verification_type" required>
                                    <option value="">Select document type...</option>
                                    <option value="nin">NIN Document Only</option>
                                    <option value="frsc">Driver's License Only</option>
                                    <option value="both">Both Documents</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="overrideStatus" class="form-label">
                                    <i class="fas fa-check-circle"></i> Override Status *
                                </label>
                                <select class="form-control" id="overrideStatus" name="status" required>
                                    <option value="">Select override status...</option>
                                    <option value="passed">✅ Passed (Manual Approval)</option>
                                    <option value="failed">❌ Failed (Manual Rejection)</option>
                                    <option value="pending">⏳ Pending (Reset Status)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="adminNotes" class="form-label">
                            <i class="fas fa-comment"></i> Admin Notes *
                        </label>
                        <textarea class="form-control" id="adminNotes" name="admin_notes" rows="4" 
                                  placeholder="Provide detailed explanation for this manual override. Include:&#10;• Reason for override&#10;• What was reviewed manually&#10;• Any additional verification steps taken&#10;• Supporting evidence or documentation" 
                                  required minlength="20"></textarea>
                        <div class="form-text">Minimum 20 characters required. Be specific about your reasoning.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="overrideReason" class="form-label">
                            <i class="fas fa-list"></i> Override Category
                        </label>
                        <select class="form-control" id="overrideReason" name="override_reason">
                            <option value="">Select category...</option>
                            <option value="ocr_error">OCR Processing Error</option>
                            <option value="document_quality">Poor Document Quality</option>
                            <option value="manual_verification">Manual Verification Required</option>
                            <option value="system_error">System Technical Error</option>
                            <option value="false_positive">False Positive Result</option>
                            <option value="false_negative">False Negative Result</option>
                            <option value="special_case">Special Case Exception</option>
                            <option value="other">Other (specify in notes)</option>
                        </select>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="confidenceLevel" class="form-label">
                                    <i class="fas fa-percentage"></i> Manual Confidence Level
                                </label>
                                <select class="form-control" id="confidenceLevel" name="confidence_level">
                                    <option value="">Select confidence...</option>
                                    <option value="high">High Confidence (95-100%)</option>
                                    <option value="medium">Medium Confidence (80-94%)</option>
                                    <option value="low">Low Confidence (60-79%)</option>
                                    <option value="uncertain">Uncertain (Below 60%)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="reviewMethod" class="form-label">
                                    <i class="fas fa-eye"></i> Review Method
                                </label>
                                <select class="form-control" id="reviewMethod" name="review_method">
                                    <option value="">Select method...</option>
                                    <option value="visual_inspection">Visual Document Inspection</option>
                                    <option value="cross_reference">Cross-Reference Verification</option>
                                    <option value="phone_verification">Phone Verification</option>
                                    <option value="third_party">Third-party Verification</option>
                                    <option value="manual_data_entry">Manual Data Entry</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="mb-3">
                        <label for="adminPassword" class="form-label">
                            <i class="fas fa-lock"></i> Admin Password Confirmation *
                        </label>
                        <input type="password" class="form-control" id="adminPassword" name="admin_password" 
                               placeholder="Enter your admin password to confirm this override" required>
                        <div class="form-text text-danger">
                            <i class="fas fa-shield-alt"></i> Your password is required to authorize this manual override action.
                        </div>
                    </div>
                    
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="confirmOverride" required>
                        <label class="form-check-label" for="confirmOverride">
                            <strong>I confirm that I have thoroughly reviewed the documents and take responsibility for this manual override decision.</strong>
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-warning" id="submitOverrideBtn">
                        <i class="fas fa-user-cog"></i> Apply Override
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>