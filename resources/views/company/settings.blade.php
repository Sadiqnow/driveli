@extends('company.layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2 class="mb-0"><i class="bi bi-gear"></i> Settings</h2>
        <p class="text-muted">Manage your company profile and preferences</p>
    </div>
</div>

<div class="row">
    <!-- Profile Settings -->
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-person"></i> Company Profile</h5>
            </div>
            <div class="card-body">
                <form id="profileForm" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="company_name" class="form-label">Company Name *</label>
                            <input type="text" class="form-control" id="company_name" name="company_name"
                                   value="{{ Auth::user()->company->name ?? '' }}" required
                                   aria-describedby="companyNameHelp">
                            <div id="companyNameHelp" class="form-text">Your registered company name</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email Address *</label>
                            <input type="email" class="form-control" id="email" name="email"
                                   value="{{ Auth::user()->email }}" required
                                   aria-describedby="emailHelp">
                            <div id="emailHelp" class="form-text">Primary contact email</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone"
                                   value="{{ Auth::user()->company->phone ?? '' }}"
                                   aria-describedby="phoneHelp">
                            <div id="phoneHelp" class="form-text">Business phone number</div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="website" class="form-label">Website</label>
                            <input type="url" class="form-control" id="website" name="website"
                                   value="{{ Auth::user()->company->website ?? '' }}"
                                   aria-describedby="websiteHelp">
                            <div id="websiteHelp" class="form-text">Company website URL</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">Business Address</label>
                        <textarea class="form-control" id="address" name="address" rows="3"
                                  aria-describedby="addressHelp">{{ Auth::user()->company->address ?? '' }}</textarea>
                        <div id="addressHelp" class="form-text">Complete business address</div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Company Description</label>
                        <textarea class="form-control" id="description" name="description" rows="4"
                                  aria-describedby="descriptionHelp">{{ Auth::user()->company->description ?? '' }}</textarea>
                        <div id="descriptionHelp" class="form-text">Brief description of your business</div>
                    </div>

                    <div class="mb-3">
                        <label for="logo" class="form-label">Company Logo</label>
                        <input type="file" class="form-control" id="logo" name="logo" accept="image/*"
                               aria-describedby="logoHelp">
                        <div id="logoHelp" class="form-text">Upload a new company logo (PNG, JPG, max 2MB)</div>
                        @if(Auth::user()->company->logo)
                            <div class="mt-2">
                                <img src="{{ asset('storage/' . Auth::user()->company->logo) }}"
                                     alt="Current logo" class="img-thumbnail" style="max-width: 100px;">
                            </div>
                        @endif
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save" aria-hidden="true"></i> Update Profile
                    </button>
                </form>
            </div>
        </div>

        <!-- Notification Settings -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-bell"></i> Notification Preferences</h5>
            </div>
            <div class="card-body">
                <form id="notificationForm">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="email_notifications" name="email_notifications" checked>
                            <label class="form-check-label" for="email_notifications">
                                Email Notifications
                            </label>
                            <div class="form-text">Receive notifications via email</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="sms_notifications" name="sms_notifications">
                            <label class="form-check-label" for="sms_notifications">
                                SMS Notifications
                            </label>
                            <div class="form-text">Receive important updates via SMS</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="match_notifications" name="match_notifications" checked>
                            <label class="form-check-label" for="match_notifications">
                                Driver Match Notifications
                            </label>
                            <div class="form-text">Get notified when drivers are matched to your requests</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="invoice_notifications" name="invoice_notifications" checked>
                            <label class="form-check-label" for="invoice_notifications">
                                Invoice Notifications
                            </label>
                            <div class="form-text">Receive notifications about invoices and payments</div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save" aria-hidden="true"></i> Save Preferences
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Security Settings -->
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-shield-lock"></i> Security</h5>
            </div>
            <div class="card-body">
                <button class="btn btn-outline-primary w-100 mb-3" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                    <i class="bi bi-key" aria-hidden="true"></i> Change Password
                </button>

                <button class="btn btn-outline-info w-100 mb-3" onclick="enable2FA()">
                    <i class="bi bi-phone" aria-hidden="true"></i> Enable Two-Factor Auth
                </button>

                <button class="btn btn-outline-warning w-100" onclick="viewLoginHistory()">
                    <i class="bi bi-clock-history" aria-hidden="true"></i> Login History
                </button>
            </div>
        </div>

        <!-- Account Status -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Account Status</h5>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <strong>Account Type:</strong> Company
                </div>
                <div class="mb-2">
                    <strong>Status:</strong>
                    <span class="badge bg-success">Active</span>
                </div>
                <div class="mb-2">
                    <strong>Member Since:</strong>
                    {{ Auth::user()->created_at->format('M d, Y') }}
                </div>
                <div class="mb-2">
                    <strong>Last Login:</strong>
                    {{ Auth::user()->last_login_at ? Auth::user()->last_login_at->format('M d, Y H:i') : 'Never' }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<x-ui.modal id="changePasswordModal" title="Change Password" size="md">
    <form id="changePasswordForm">
        <div class="mb-3">
            <label for="current_password" class="form-label">Current Password *</label>
            <input type="password" class="form-control" id="current_password" required>
        </div>
        <div class="mb-3">
            <label for="new_password" class="form-label">New Password *</label>
            <input type="password" class="form-control" id="new_password" required minlength="8">
            <div class="form-text">Must be at least 8 characters long</div>
        </div>
        <div class="mb-3">
            <label for="confirm_password" class="form-label">Confirm New Password *</label>
            <input type="password" class="form-control" id="confirm_password" required>
        </div>
    </form>

    <x-slot name="footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="updatePasswordBtn">Update Password</button>
    </x-slot>
</x-ui.modal>

<!-- Two-Factor Auth Modal -->
<x-ui.modal id="twoFAModal" title="Two-Factor Authentication" size="md">
    <div id="twoFAContent">
        <!-- 2FA setup content will be loaded here -->
    </div>

    <x-slot name="footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="enable2FABtn">Enable 2FA</button>
    </x-slot>
</x-ui.modal>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Profile form submission
    document.getElementById('profileForm').addEventListener('submit', function(e) {
        e.preventDefault();
        updateProfile();
    });

    // Notification form submission
    document.getElementById('notificationForm').addEventListener('submit', function(e) {
        e.preventDefault();
        updateNotifications();
    });

    // Change password
    document.getElementById('updatePasswordBtn').addEventListener('click', function() {
        changePassword();
    });

    // Enable 2FA
    window.enable2FA = function() {
        fetch('/api/company/2fa/setup', {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('twoFAContent').innerHTML = `
                    <div class="text-center mb-3">
                        <img src="${data.qr_code_url}" alt="QR Code" class="img-fluid">
                    </div>
                    <p>Scan this QR code with your authenticator app, then enter the verification code below:</p>
                    <div class="mb-3">
                        <label for="twofa_code" class="form-label">Verification Code</label>
                        <input type="text" class="form-control" id="twofa_code" required>
                    </div>
                `;

                document.getElementById('enable2FABtn').onclick = function() {
                    const code = document.getElementById('twofa_code').value;
                    if (code) {
                        verify2FA(code);
                    } else {
                        showToast('Please enter the verification code', 'danger');
                    }
                };

                const modal = new bootstrap.Modal(document.getElementById('twoFAModal'));
                modal.show();
            } else {
                showToast('Failed to setup 2FA', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred while setting up 2FA', 'danger');
        });
    };

    // View login history
    window.viewLoginHistory = function() {
        fetch('/api/company/login-history', {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let historyHtml = '<div class="list-group">';
                data.data.forEach(login => {
                    historyHtml += `
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <strong>${login.ip_address}</strong>
                                    <br><small class="text-muted">${new Date(login.created_at).toLocaleString()}</small>
                                </div>
                                <span class="badge bg-${login.successful ? 'success' : 'danger'}">
                                    ${login.successful ? 'Success' : 'Failed'}
                                </span>
                            </div>
                        </div>
                    `;
                });
                historyHtml += '</div>';

                // Create a simple modal for history
                const modalHtml = `
                    <div class="modal fade" id="historyModal" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Login History</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">${historyHtml}</div>
                            </div>
                        </div>
                    </div>
                `;

                document.body.insertAdjacentHTML('beforeend', modalHtml);
                const modal = new bootstrap.Modal(document.getElementById('historyModal'));
                modal.show();

                document.getElementById('historyModal').addEventListener('hidden.bs.modal', function() {
                    this.remove();
                });
            } else {
                showToast('Failed to load login history', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred while loading login history', 'danger');
        });
    };

    function updateProfile() {
        const formData = new FormData(document.getElementById('profileForm'));

        fetch('/api/company/profile', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Profile updated successfully!', 'success');
            } else {
                showToast(data.message || 'Failed to update profile', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred while updating profile', 'danger');
        });
    }

    function updateNotifications() {
        const formData = new FormData(document.getElementById('notificationForm'));

        fetch('/api/company/notifications', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: Object.fromEntries(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Notification preferences updated!', 'success');
            } else {
                showToast(data.message || 'Failed to update preferences', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred while updating preferences', 'danger');
        });
    }

    function changePassword() {
        const currentPassword = document.getElementById('current_password').value;
        const newPassword = document.getElementById('new_password').value;
        const confirmPassword = document.getElementById('confirm_password').value;

        if (newPassword !== confirmPassword) {
            showToast('New passwords do not match', 'danger');
            return;
        }

        if (newPassword.length < 8) {
            showToast('New password must be at least 8 characters long', 'danger');
            return;
        }

        fetch('/api/company/change-password', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                current_password: currentPassword,
                password: newPassword,
                password_confirmation: confirmPassword
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Password changed successfully!', 'success');
                bootstrap.Modal.getInstance(document.getElementById('changePasswordModal')).hide();
                document.getElementById('changePasswordForm').reset();
            } else {
                showToast(data.message || 'Failed to change password', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred while changing password', 'danger');
        });
    }

    function verify2FA(code) {
        fetch('/api/company/2fa/verify', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                code: code
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Two-factor authentication enabled!', 'success');
                bootstrap.Modal.getInstance(document.getElementById('twoFAModal')).hide();
            } else {
                showToast(data.message || 'Failed to enable 2FA', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred while enabling 2FA', 'danger');
        });
    }

    function showToast(message, type = 'info') {
        let toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            toastContainer.style.zIndex = '9999';
            document.body.appendChild(toastContainer);
        }

        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type} border-0`;
        toast.setAttribute('role', 'alert');
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;

        toastContainer.appendChild(toast);
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();

        toast.addEventListener('hidden.bs.toast', () => {
            toast.remove();
        });
    }
});
</script>
@endpush
@endsection
