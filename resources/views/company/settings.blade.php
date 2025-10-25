@extends('company.layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h2 class="mb-0"><i class="bi bi-gear" aria-hidden="true"></i> Settings</h2>
        <p class="text-muted mt-2">Manage your account settings and preferences</p>
    </div>
</div>

<div class="row">
    <!-- Profile Settings -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-person"></i> Profile Information</h5>
            </div>
            <div class="card-body">
                <form id="profileForm" method="POST" action="{{ route('company.profile.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Company Name *</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', Auth::guard('company')->user()->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email Address *</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', Auth::guard('company')->user()->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', Auth::guard('company')->user()->phone) }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="website" class="form-label">Website</label>
                            <input type="url" class="form-control @error('website') is-invalid @enderror" id="website" name="website" value="{{ old('website', Auth::guard('company')->user()->website) }}" placeholder="https://example.com">
                            @error('website')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="3">{{ old('address', Auth::guard('company')->user()->address) }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Company Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4" placeholder="Brief description of your company...">{{ old('description', Auth::guard('company')->user()->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="logo" class="form-label">Company Logo</label>
                        <input type="file" class="form-control @error('logo') is-invalid @enderror" id="logo" name="logo" accept="image/*">
                        <div class="form-text">Upload a new logo (JPEG, PNG, max 2MB)</div>
                        @if(Auth::guard('company')->user()->logo)
                            <div class="mt-2">
                                <img src="{{ asset('storage/' . Auth::guard('company')->user()->logo) }}" alt="Current logo" class="img-thumbnail" style="max-width: 100px;">
                            </div>
                        @endif
                        @error('logo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary" id="updateProfileBtn">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        Update Profile
                    </button>
                </form>
            </div>
        </div>

        <!-- Password Settings -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-shield-lock"></i> Change Password</h5>
            </div>
            <div class="card-body">
                <form id="passwordForm" method="POST" action="{{ route('company.profile.password') }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password *</label>
                        <input type="password" class="form-control @error('current_password') is-invalid @enderror" id="current_password" name="current_password" required>
                        @error('current_password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">New Password *</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required minlength="8">
                            <div class="form-text">Minimum 8 characters</div>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="password_confirmation" class="form-label">Confirm New Password *</label>
                            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-warning" id="changePasswordBtn">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        Change Password
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Notification Settings -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-bell"></i> Notifications</h5>
            </div>
            <div class="card-body">
                <form id="notificationsForm" method="POST" action="{{ route('company.profile.notifications') }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="email_notifications" name="email_notifications" value="1" {{ Auth::guard('company')->user()->email_notifications ? 'checked' : '' }}>
                        <label class="form-check-label" for="email_notifications">
                            <strong>Email Notifications</strong>
                            <br><small class="text-muted">Receive notifications via email</small>
                        </label>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="sms_notifications" name="sms_notifications" value="1" {{ Auth::guard('company')->user()->sms_notifications ? 'checked' : '' }}>
                        <label class="form-check-label" for="sms_notifications">
                            <strong>SMS Notifications</strong>
                            <br><small class="text-muted">Receive notifications via SMS</small>
                        </label>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="match_notifications" name="match_notifications" value="1" {{ Auth::guard('company')->user()->match_notifications ? 'checked' : '' }}>
                        <label class="form-check-label" for="match_notifications">
                            <strong>Match Notifications</strong>
                            <br><small class="text-muted">Get notified when drivers match your requests</small>
                        </label>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="invoice_notifications" name="invoice_notifications" value="1" {{ Auth::guard('company')->user()->invoice_notifications ? 'checked' : '' }}>
                        <label class="form-check-label" for="invoice_notifications">
                            <strong>Invoice Notifications</strong>
                            <br><small class="text-muted">Receive notifications about invoices and payments</small>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-success" id="updateNotificationsBtn">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        Update Preferences
                    </button>
                </form>
            </div>
        </div>

        <!-- Account Security -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-shield-check"></i> Security</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6>Two-Factor Authentication</h6>
                    <p class="text-muted small">Add an extra layer of security to your account</p>
                    @if(Auth::guard('company')->user()->two_factor_enabled)
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle"></i> Two-factor authentication is enabled
                        </div>
                        <button type="button" class="btn btn-outline-danger btn-sm" id="disable2FABtn">
                            Disable 2FA
                        </button>
                    @else
                        <button type="button" class="btn btn-outline-primary btn-sm" id="enable2FABtn">
                            Enable 2FA
                        </button>
                    @endif
                </div>

                <hr>

                <div class="mb-3">
                    <h6>Login History</h6>
                    <p class="text-muted small">View recent login activity</p>
                    <button type="button" class="btn btn-outline-info btn-sm" id="viewLoginHistoryBtn">
                        View History
                    </button>
                </div>

                <hr>

                <div class="mb-0">
                    <h6 class="text-danger">Danger Zone</h6>
                    <p class="text-muted small">Irreversible actions</p>
                    <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                        Delete Account
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 2FA Setup Modal -->
<div class="modal fade" id="enable2FAModal" tabindex="-1" aria-labelledby="enable2FAModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="enable2FAModalLabel">Enable Two-Factor Authentication</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="qrCodeContainer" class="text-center mb-3">
                    <!-- QR Code will be loaded here -->
                </div>
                <p class="text-muted small">Scan the QR code with your authenticator app, then enter the verification code below.</p>
                <div class="mb-3">
                    <label for="verificationCode" class="form-label">Verification Code</label>
                    <input type="text" class="form-control text-center" id="verificationCode" maxlength="6" placeholder="000000">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="verify2FABtn">Verify & Enable</button>
            </div>
        </div>
    </div>
</div>

<!-- Login History Modal -->
<div class="modal fade" id="loginHistoryModal" tabindex="-1" aria-labelledby="loginHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="loginHistoryModalLabel">Login History</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="loginHistoryContent">
                    <!-- Login history will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Account Modal -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger" id="deleteAccountModalLabel">Delete Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <strong>Warning:</strong> This action cannot be undone. All your data, requests, matches, and invoices will be permanently deleted.
                </div>
                <p>Type "DELETE" to confirm:</p>
                <input type="text" class="form-control" id="deleteConfirmation" placeholder="DELETE">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteAccountBtn" disabled>Delete Account</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Profile form submission
    document.getElementById('profileForm').addEventListener('submit', function(e) {
        const submitBtn = document.getElementById('updateProfileBtn');
        submitBtn.disabled = true;
        submitBtn.querySelector('.spinner-border').classList.remove('d-none');
    });

    // Password form submission
    document.getElementById('passwordForm').addEventListener('submit', function(e) {
        const submitBtn = document.getElementById('changePasswordBtn');
        submitBtn.disabled = true;
        submitBtn.querySelector('.spinner-border').classList.remove('d-none');
    });

    // Notifications form submission
    document.getElementById('notificationsForm').addEventListener('submit', function(e) {
        const submitBtn = document.getElementById('updateNotificationsBtn');
        submitBtn.disabled = true;
        submitBtn.querySelector('.spinner-border').classList.remove('d-none');
    });

    // 2FA functionality
    document.getElementById('enable2FABtn').addEventListener('click', function() {
        enable2FA();
    });

    document.getElementById('verify2FABtn').addEventListener('click', function() {
        const code = document.getElementById('verificationCode').value;
        if (code.length === 6) {
            verify2FA(code);
        } else {
            showToast('Please enter a valid 6-digit code', 'danger');
        }
    });

    // Login history
    document.getElementById('viewLoginHistoryBtn').addEventListener('click', function() {
        loadLoginHistory();
    });

    // Delete account confirmation
    document.getElementById('deleteConfirmation').addEventListener('input', function() {
        const confirmBtn = document.getElementById('confirmDeleteAccountBtn');
        confirmBtn.disabled = this.value !== 'DELETE';
    });

    function enable2FA() {
        fetch('/company/profile/enable-2fa', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('qrCodeContainer').innerHTML = `<img src="${data.qr_code_url}" alt="QR Code" class="img-fluid">`;
                const modal = new bootstrap.Modal(document.getElementById('enable2FAModal'));
                modal.show();
            } else {
                showToast('Failed to setup 2FA', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred while setting up 2FA', 'danger');
        });
    }

    function verify2FA(code) {
        fetch('/company/profile/verify-2fa', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ code: code })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Two-factor authentication enabled successfully!', 'success');
                bootstrap.Modal.getInstance(document.getElementById('enable2FAModal')).hide();
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(data.message || 'Invalid verification code', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred while verifying 2FA', 'danger');
        });
    }

    function loadLoginHistory() {
        fetch('/company/profile/login-history')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let html = '<div class="table-responsive"><table class="table table-sm"><thead><tr><th>Date</th><th>IP Address</th><th>Device</th></tr></thead><tbody>';
                data.data.forEach(login => {
                    html += `<tr>
                        <td>${new Date(login.created_at).toLocaleString()}</td>
                        <td>${login.ip_address}</td>
                        <td>${login.user_agent || 'Unknown'}</td>
                    </tr>`;
                });
                html += '</tbody></table></div>';
                document.getElementById('loginHistoryContent').innerHTML = html;

                const modal = new bootstrap.Modal(document.getElementById('loginHistoryModal'));
                modal.show();
            } else {
                showToast('Failed to load login history', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred while loading login history', 'danger');
        });
    }

    function showToast(message, type = 'info') {
        const toastContainer = document.getElementById('toastContainer') || createToastContainer();

        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type} border-0`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');

        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;

        toastContainer.appendChild(toast);
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();

        toast.addEventListener('hidden.bs.toast', () => {
            toast.remove();
        });
    }

    function createToastContainer() {
        const container = document.createElement('div');
        container.id = 'toastContainer';
        container.className = 'toast-container position-fixed top-0 end-0 p-3';
        container.style.zIndex = '9999';
        document.body.appendChild(container);
        return container;
    }
});
</script>
@endpush
@endsection
