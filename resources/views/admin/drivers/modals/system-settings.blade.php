<!-- System Settings Modal -->
<div class="modal fade" id="systemSettingsModal" tabindex="-1" aria-labelledby="systemSettingsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="systemSettingsModalLabel">
                    <i class="fas fa-cog"></i> OCR System Settings
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="systemSettingsForm" onsubmit="saveSystemSettings(event)">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6><i class="fas fa-sliders-h"></i> Processing Settings</h6>
                            
                            <div class="mb-3">
                                <label for="batchSize" class="form-label">Batch Processing Size</label>
                                <select class="form-control" id="batchSize" name="batch_size">
                                    <option value="5">5 documents per batch</option>
                                    <option value="10" selected>10 documents per batch</option>
                                    <option value="20">20 documents per batch</option>
                                    <option value="50">50 documents per batch</option>
                                </select>
                                <div class="form-text">Number of documents to process simultaneously</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="processingDelay" class="form-label">Processing Delay (seconds)</label>
                                <input type="number" class="form-control" id="processingDelay" name="processing_delay" 
                                       value="2" min="0" max="60">
                                <div class="form-text">Delay between processing batches to prevent API limits</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="retryAttempts" class="form-label">Retry Attempts</label>
                                <select class="form-control" id="retryAttempts" name="retry_attempts">
                                    <option value="1">1 attempt</option>
                                    <option value="2">2 attempts</option>
                                    <option value="3" selected>3 attempts</option>
                                    <option value="5">5 attempts</option>
                                </select>
                                <div class="form-text">Number of retry attempts for failed OCR processing</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <h6><i class="fas fa-chart-line"></i> Accuracy Thresholds</h6>
                            
                            <div class="mb-3">
                                <label for="passThreshold" class="form-label">Pass Threshold (%)</label>
                                <input type="number" class="form-control" id="passThreshold" name="pass_threshold" 
                                       value="85" min="60" max="100">
                                <div class="form-text">Minimum accuracy score to automatically pass verification</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="failThreshold" class="form-label">Fail Threshold (%)</label>
                                <input type="number" class="form-control" id="failThreshold" name="fail_threshold" 
                                       value="60" min="0" max="80">
                                <div class="form-text">Maximum accuracy score to automatically fail verification</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="manualReviewThreshold" class="form-label">Manual Review Threshold (%)</label>
                                <input type="number" class="form-control" id="manualReviewThreshold" name="manual_review_threshold" 
                                       value="70" min="60" max="85">
                                <div class="form-text">Scores in this range require manual review</div>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h6><i class="fas fa-bell"></i> Notification Settings</h6>
                            
                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" id="enableEmailNotifications" 
                                       name="enable_email_notifications" checked>
                                <label class="form-check-label" for="enableEmailNotifications">
                                    Enable email notifications for completed verifications
                                </label>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" id="enableSlackNotifications" 
                                       name="enable_slack_notifications">
                                <label class="form-check-label" for="enableSlackNotifications">
                                    Enable Slack notifications for system alerts
                                </label>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" id="enableSmsNotifications" 
                                       name="enable_sms_notifications">
                                <label class="form-check-label" for="enableSmsNotifications">
                                    Enable SMS notifications for critical failures
                                </label>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <h6><i class="fas fa-database"></i> Data Retention</h6>
                            
                            <div class="mb-3">
                                <label for="logRetentionDays" class="form-label">Log Retention (days)</label>
                                <select class="form-control" id="logRetentionDays" name="log_retention_days">
                                    <option value="30">30 days</option>
                                    <option value="60">60 days</option>
                                    <option value="90" selected>90 days</option>
                                    <option value="180">180 days</option>
                                    <option value="365">1 year</option>
                                </select>
                                <div class="form-text">How long to keep OCR processing logs</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="imageRetentionDays" class="form-label">Processed Images Retention (days)</label>
                                <select class="form-control" id="imageRetentionDays" name="image_retention_days">
                                    <option value="30">30 days</option>
                                    <option value="90">90 days</option>
                                    <option value="180" selected>180 days</option>
                                    <option value="365">1 year</option>
                                    <option value="-1">Permanent</option>
                                </select>
                                <div class="form-text">How long to keep processed document images</div>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="row">
                        <div class="col-12">
                            <h6><i class="fas fa-shield-alt"></i> Security & Compliance</h6>
                            
                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" id="enableAuditLog" 
                                       name="enable_audit_log" checked>
                                <label class="form-check-label" for="enableAuditLog">
                                    Enable comprehensive audit logging for all OCR operations
                                </label>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" id="requireTwoFactorOverride" 
                                       name="require_two_factor_override">
                                <label class="form-check-label" for="requireTwoFactorOverride">
                                    Require two-factor authentication for manual overrides
                                </label>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" id="enableDataEncryption" 
                                       name="enable_data_encryption" checked>
                                <label class="form-check-label" for="enableDataEncryption">
                                    Enable encryption for stored OCR data and results
                                </label>
                            </div>
                            
                            <div class="mb-3">
                                <label for="adminPassword" class="form-label">
                                    <i class="fas fa-lock"></i> Admin Password *
                                </label>
                                <input type="password" class="form-control" id="adminPasswordSettings" 
                                       name="admin_password" placeholder="Enter your password to save settings" required>
                                <div class="form-text">Password required to modify system settings</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-warning" onclick="resetToDefaults()">
                        <i class="fas fa-undo"></i> Reset to Defaults
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>