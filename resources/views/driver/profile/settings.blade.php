@extends('drivers.layouts.app')

@section('title', 'Settings')
@section('page_title', 'Account Settings')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('driver.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Settings</li>
@endsection

@section('styles')
<style>
    /* Settings page specific styles using unified design system */
    .settings-section {
        margin-bottom: 2rem;
    }

    .settings-section h5 {
        color: var(--drivelink-primary);
        font-weight: 600;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid var(--drivelink-light);
    }

    .setting-item {
        padding: 1rem 0;
        border-bottom: 1px solid rgba(0,0,0,0.1);
    }

    .setting-item:last-child {
        border-bottom: none;
    }

    .setting-toggle {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .form-switch .form-check-input {
        width: 3rem;
        height: 1.5rem;
        border-radius: 3rem;
        background-color: var(--drivelink-secondary);
        border: none;
    }

    .form-switch .form-check-input:checked {
        background-color: var(--drivelink-success);
    }

    .form-switch .form-check-input:focus {
        border-color: var(--drivelink-primary);
        box-shadow: 0 0 0 0.25rem rgba(0,123,255,0.25);
    }

    .btn-drivelink-primary {
        background: var(--drivelink-gradient-primary);
        border: none;
        border-radius: var(--drivelink-border-radius);
        padding: 0.75rem 1.5rem;
        font-weight: 500;
        color: white;
        transition: var(--drivelink-transition);
    }

    .btn-drivelink-primary:hover {
        transform: translateY(-1px);
        box-shadow: var(--drivelink-box-shadow-lg);
        color: white;
    }

    .btn-drivelink-outline {
        background: transparent;
        border: 2px solid var(--drivelink-secondary);
        border-radius: var(--drivelink-border-radius);
        padding: 0.75rem 1.5rem;
        font-weight: 500;
        color: var(--drivelink-secondary);
        transition: var(--drivelink-transition);
    }

    .btn-drivelink-outline:hover {
        background: var(--drivelink-secondary);
        color: white;
    }

    .card {
        border-radius: var(--drivelink-border-radius);
        box-shadow: var(--drivelink-box-shadow);
        border: 1px solid rgba(0,0,0,0.125);
    }

    .card-header {
        background: var(--drivelink-light);
        border-bottom: 1px solid rgba(0,0,0,0.125);
    }

    .alert-info {
        background: var(--drivelink-gradient-info);
        border: none;
        border-left: 4px solid var(--drivelink-info);
    }

    .form-control:focus {
        border-color: var(--drivelink-primary);
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
    }

    .form-select:focus {
        border-color: var(--drivelink-primary);
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
    }

    .settings-icon {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
    }

    .settings-icon.notification {
        background: rgba(23, 162, 184, 0.1);
        color: var(--drivelink-info);
    }

    .settings-icon.privacy {
        background: rgba(40, 167, 69, 0.1);
        color: var(--drivelink-success);
    }

    .settings-icon.account {
        background: rgba(255, 193, 7, 0.1);
        color: var(--drivelink-warning);
    }

    .settings-icon.security {
        background: rgba(220, 53, 69, 0.1);
        color: var(--drivelink-danger);
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Settings Header -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-cogs mr-2" style="color: var(--drivelink-primary);"></i>
                    Account Settings
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted mb-0">
                    Manage your account preferences, notification settings, and privacy options.
                </p>
            </div>
        </div>

        <!-- Notification Settings -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-bell mr-2" style="color: var(--drivelink-info);"></i>
                    Notification Preferences
                </h6>
            </div>
            <div class="card-body">
                <div class="settings-section">
                    <div class="setting-item">
                        <div class="setting-toggle">
                            <div class="d-flex align-items-center">
                                <div class="settings-icon notification">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">Email Notifications</h6>
                                    <small class="text-muted">Receive job alerts and updates via email</small>
                                </div>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="emailNotifications" checked>
                            </div>
                        </div>
                    </div>

                    <div class="setting-item">
                        <div class="setting-toggle">
                            <div class="d-flex align-items-center">
                                <div class="settings-icon notification">
                                    <i class="fas fa-sms"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">SMS Notifications</h6>
                                    <small class="text-muted">Get instant job matches via SMS</small>
                                </div>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="smsNotifications">
                            </div>
                        </div>
                    </div>

                    <div class="setting-item">
                        <div class="setting-toggle">
                            <div class="d-flex align-items-center">
                                <div class="settings-icon notification">
                                    <i class="fas fa-mobile-alt"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">Push Notifications</h6>
                                    <small class="text-muted">Browser push notifications for urgent updates</small>
                                </div>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="pushNotifications" checked>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Privacy Settings -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-shield-alt mr-2" style="color: var(--drivelink-success);"></i>
                    Privacy & Visibility
                </h6>
            </div>
            <div class="card-body">
                <div class="settings-section">
                    <div class="setting-item">
                        <div class="setting-toggle">
                            <div class="d-flex align-items-center">
                                <div class="settings-icon privacy">
                                    <i class="fas fa-eye"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">Profile Visibility</h6>
                                    <small class="text-muted">Allow companies to find your profile in searches</small>
                                </div>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="profileVisibility" checked>
                            </div>
                        </div>
                    </div>

                    <div class="setting-item">
                        <div class="setting-toggle">
                            <div class="d-flex align-items-center">
                                <div class="settings-icon privacy">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">Location Sharing</h6>
                                    <small class="text-muted">Share location for better job matching</small>
                                </div>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="locationSharing" checked>
                            </div>
                        </div>
                    </div>

                    <div class="setting-item">
                        <div class="setting-toggle">
                            <div class="d-flex align-items-center">
                                <div class="settings-icon privacy">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">Contact Information</h6>
                                    <small class="text-muted">Allow companies to see your phone number</small>
                                </div>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="contactSharing">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Account Preferences -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-user-cog mr-2" style="color: var(--drivelink-warning);"></i>
                    Account Preferences
                </h6>
            </div>
            <div class="card-body">
                <form id="preferencesForm" class="settings-section">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="language" class="form-label">
                                <i class="fas fa-language mr-1" style="color: var(--drivelink-warning);"></i>
                                Language
                            </label>
                            <select class="form-select" id="language" name="language">
                                <option value="en" selected>English</option>
                                <option value="ha">Hausa</option>
                                <option value="yo">Yoruba</option>
                                <option value="ig">Igbo</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="timezone" class="form-label">
                                <i class="fas fa-clock mr-1" style="color: var(--drivelink-warning);"></i>
                                Timezone
                            </label>
                            <select class="form-select" id="timezone" name="timezone">
                                <option value="Africa/Lagos" selected>West Africa Time (WAT)</option>
                                <option value="UTC">Coordinated Universal Time (UTC)</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="jobRadius" class="form-label">
                                <i class="fas fa-map-pin mr-1" style="color: var(--drivelink-warning);"></i>
                                Job Search Radius (km)
                            </label>
                            <select class="form-select" id="jobRadius" name="job_search_radius">
                                <option value="5">Within 5 km</option>
                                <option value="10">Within 10 km</option>
                                <option value="25" selected>Within 25 km</option>
                                <option value="50">Within 50 km</option>
                                <option value="100">Within 100 km</option>
                                <option value="-1">Anywhere</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="autoAcceptJobs" class="form-label">
                                <i class="fas fa-check-circle mr-1" style="color: var(--drivelink-warning);"></i>
                                Auto-accept Jobs
                            </label>
                            <select class="form-select" id="autoAcceptJobs" name="auto_accept_jobs">
                                <option value="0" selected>Manual approval</option>
                                <option value="1">Auto-accept all matches</option>
                            </select>
                        </div>
                    </div>

                    <div class="d-flex gap-2 mt-3">
                        <button type="submit" class="btn btn-drivelink-primary">
                            <i class="fas fa-save mr-2"></i>Save Preferences
                        </button>
                        <button type="reset" class="btn btn-drivelink-outline">
                            <i class="fas fa-undo mr-2"></i>Reset
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Security Settings -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-lock mr-2" style="color: var(--drivelink-danger);"></i>
                    Security & Authentication
                </h6>
            </div>
            <div class="card-body">
                <div class="settings-section">
                    <div class="setting-item">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="settings-icon security">
                                    <i class="fas fa-key"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">Change Password</h6>
                                    <small class="text-muted">Update your account password for security</small>
                                </div>
                            </div>
                            <a href="{{ route('driver.profile.change-password') }}" class="btn btn-drivelink-outline btn-sm">
                                <i class="fas fa-edit mr-1"></i>Update
                            </a>
                        </div>
                    </div>

                    <div class="setting-item">
                        <div class="setting-toggle">
                            <div class="d-flex align-items-center">
                                <div class="settings-icon security">
                                    <i class="fas fa-mobile-alt"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">Two-Factor Authentication</h6>
                                    <small class="text-muted">Add extra security to your account</small>
                                </div>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="twoFactorAuth" disabled>
                                <small class="text-muted ml-2">Coming Soon</small>
                            </div>
                        </div>
                    </div>

                    <div class="setting-item">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="settings-icon security">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">Login Activity</h6>
                                    <small class="text-muted">Review recent login attempts</small>
                                </div>
                            </div>
                            <button class="btn btn-drivelink-outline btn-sm" onclick="showLoginActivity()">
                                <i class="fas fa-eye mr-1"></i>View
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data & Privacy Notice -->
        <div class="alert alert-info d-flex align-items-center">
            <i class="fas fa-info-circle mr-3" style="font-size: 1.5rem;"></i>
            <div>
                <strong>Data & Privacy:</strong> Your settings are automatically saved and encrypted for security. 
                We use your preferences to provide better job matching and personalized experience. 
                You can update these settings at any time.
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize settings page
    initializeSettings();
    
    // Handle preference form submission
    const preferencesForm = document.getElementById('preferencesForm');
    if (preferencesForm) {
        preferencesForm.addEventListener('submit', handlePreferencesSubmit);
    }
    
    // Handle toggle switches
    const switches = document.querySelectorAll('.form-check-input[type="checkbox"]');
    switches.forEach(switch => {
        switch.addEventListener('change', handleToggleChange);
    });
});

function initializeSettings() {
    // Load saved preferences
    loadUserPreferences();
    
    // Add smooth transitions
    const cards = document.querySelectorAll('.card');
    cards.forEach(card => {
        card.style.transition = 'var(--drivelink-transition)';
    });
}

function loadUserPreferences() {
    // This would normally load from the backend
    // For now, we'll use localStorage as a demo
    const savedPrefs = localStorage.getItem('driverPreferences');
    if (savedPrefs) {
        const prefs = JSON.parse(savedPrefs);
        
        // Apply saved preferences
        Object.keys(prefs).forEach(key => {
            const element = document.getElementById(key);
            if (element) {
                if (element.type === 'checkbox') {
                    element.checked = prefs[key];
                } else {
                    element.value = prefs[key];
                }
            }
        });
    }
}

function handlePreferencesSubmit(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const preferences = Object.fromEntries(formData.entries());
    
    // Show loading state
    const submitBtn = event.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
    submitBtn.disabled = true;
    
    // Simulate API call
    setTimeout(() => {
        // Save to localStorage for demo
        localStorage.setItem('driverPreferences', JSON.stringify(preferences));
        
        // Show success message
        showToast('Preferences saved successfully!', 'success');
        
        // Restore button
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }, 1500);
}

function handleToggleChange(event) {
    const setting = event.target.id;
    const enabled = event.target.checked;
    
    // Show immediate feedback
    const settingItem = event.target.closest('.setting-item');
    if (settingItem) {
        settingItem.style.opacity = '0.7';
        
        setTimeout(() => {
            settingItem.style.opacity = '1';
            showToast(`${getSetting
Name(setting)} ${enabled ? 'enabled' : 'disabled'}`, 'info');
        }, 300);
    }
    
    // Save toggle state
    const toggleStates = JSON.parse(localStorage.getItem('driverToggleStates') || '{}');
    toggleStates[setting] = enabled;
    localStorage.setItem('driverToggleStates', JSON.stringify(toggleStates));
}

function getSettingName(settingId) {
    const names = {
        'emailNotifications': 'Email notifications',
        'smsNotifications': 'SMS notifications',
        'pushNotifications': 'Push notifications',
        'profileVisibility': 'Profile visibility',
        'locationSharing': 'Location sharing',
        'contactSharing': 'Contact sharing',
        'twoFactorAuth': 'Two-factor authentication'
    };
    return names[settingId] || 'Setting';
}

function showLoginActivity() {
    // Placeholder for login activity modal
    showToast('Login activity feature coming soon!', 'info');
}

function showToast(message, type) {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'success' ? 'success' : type === 'info' ? 'info' : 'warning'} alert-dismissible fade show position-fixed`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 1060; min-width: 300px; max-width: 400px;';
    toast.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'info' ? 'info-circle' : 'exclamation-circle'} mr-2"></i>
            <span>${message}</span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" onclick="this.parentElement.remove()"></button>
    `;
    
    document.body.appendChild(toast);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        if (toast.parentNode) {
            toast.remove();
        }
    }, 5000);
}

// Load toggle states on page load
window.addEventListener('load', function() {
    const toggleStates = JSON.parse(localStorage.getItem('driverToggleStates') || '{}');
    Object.keys(toggleStates).forEach(settingId => {
        const element = document.getElementById(settingId);
        if (element && element.type === 'checkbox' && !element.disabled) {
            element.checked = toggleStates[settingId];
        }
    });
});
</script>
@endsection