@extends('layouts.admin_cdn')

@section('title', 'System Settings')

@section('content_header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">System Settings</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.superadmin.dashboard') }}">Super Admin</a></li>
                        <li class="breadcrumb-item active">Settings</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <!-- Alert Messages -->
    <div id="alert-container"></div>

    <!-- System Information Card -->
    <div class="card card-info mb-4">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>System Information</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-sm table-borderless">
                        <tr><td><strong>Application Name:</strong></td><td>{{ $systemInfo['app_name'] }}</td></tr>
                        <tr><td><strong>Version:</strong></td><td>{{ $systemInfo['app_version'] }}</td></tr>
                        <tr><td><strong>Laravel Version:</strong></td><td>{{ $systemInfo['laravel_version'] }}</td></tr>
                        <tr><td><strong>PHP Version:</strong></td><td>{{ $systemInfo['php_version'] }}</td></tr>
                        <tr><td><strong>Environment:</strong></td><td><span class="badge badge-{{ $systemInfo['environment'] === 'production' ? 'success' : 'warning' }}">{{ ucfirst($systemInfo['environment']) }}</span></td></tr>
                        <tr><td><strong>Timezone:</strong></td><td>{{ $systemInfo['timezone'] }}</td></tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm table-borderless">
                        <tr><td><strong>Database:</strong></td><td>{{ $systemInfo['database_connection'] }}</td></tr>
                        <tr><td><strong>Cache Driver:</strong></td><td>{{ $systemInfo['cache_driver'] }}</td></tr>
                        <tr><td><strong>Queue Driver:</strong></td><td>{{ $systemInfo['queue_driver'] }}</td></tr>
                        <tr><td><strong>Mail Driver:</strong></td><td>{{ $systemInfo['mail_driver'] }}</td></tr>
                        <tr><td><strong>Maintenance Mode:</strong></td><td><span class="badge badge-{{ $systemInfo['maintenance_mode'] ? 'danger' : 'success' }}">{{ $systemInfo['maintenance_mode'] ? 'Enabled' : 'Disabled' }}</span></td></tr>
                        <tr><td><strong>Debug Mode:</strong></td><td><span class="badge badge-{{ $systemInfo['debug_mode'] ? 'warning' : 'success' }}">{{ $systemInfo['debug_mode'] ? 'Enabled' : 'Disabled' }}</span></td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Settings Form -->
    <form id="settings-form">
        @csrf

        <!-- Settings Navigation Tabs -->
        <div class="card">
            <div class="card-header p-0 border-bottom-0">
                <ul class="nav nav-tabs" id="settings-tabs" role="tablist">
                    @foreach($settingsGroups as $groupKey => $groupName)
                        <li class="nav-item">
                            <a class="nav-link {{ $loop->first ? 'active' : '' }}" id="{{ $groupKey }}-tab"
                               data-toggle="tab" href="#{{ $groupKey }}" role="tab"
                               aria-controls="{{ $groupKey }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                                <i class="fas fa-{{ getTabIcon($groupKey) }} mr-1"></i>
                                {{ $groupName }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="card-body">
                <div class="tab-content" id="settings-tab-content">
                    @foreach($settingsGroups as $groupKey => $groupName)
                        <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                             id="{{ $groupKey }}" role="tabpanel" aria-labelledby="{{ $groupKey }}-tab">

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">{{ $groupName }}</h5>
                                <div class="btn-group" role="group">
                                    @if(in_array($groupKey, ['integration']))
                                        <button type="button" class="btn btn-sm btn-info test-connection"
                                                data-group="{{ $groupKey }}">
                                            <i class="fas fa-plug mr-1"></i> Test Connection
                                        </button>
                                    @endif
                                    <button type="button" class="btn btn-sm btn-warning reset-group"
                                            data-group="{{ $groupKey }}">
                                        <i class="fas fa-undo mr-1"></i> Reset to Default
                                    </button>
                                </div>
                            </div>

                            <div class="row">
                                @if(isset($settings[$groupKey]))
                                    @foreach($settings[$groupKey] as $settingKey => $settingData)
                                        <div class="col-md-6 mb-3">
                                            <div class="form-group">
                                                <label for="{{ $groupKey }}_{{ $settingKey }}">
                                                    {{ ucwords(str_replace('_', ' ', $settingKey)) }}
                                                    @if(isset($settingData['description']))
                                                        <i class="fas fa-info-circle text-muted ml-1"
                                                           data-toggle="tooltip"
                                                           title="{{ $settingData['description'] }}"></i>
                                                    @endif
                                                </label>

                                                @if($settingData['type'] === 'boolean')
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox"
                                                               class="custom-control-input"
                                                               id="{{ $groupKey }}_{{ $settingKey }}"
                                                               name="settings[{{ $groupKey }}][{{ $settingKey }}]"
                                                               value="1"
                                                               {{ $settingData['value'] ? 'checked' : '' }}>
                                                        <label class="custom-control-label"
                                                               for="{{ $groupKey }}_{{ $settingKey }}">
                                                            {{ $settingData['value'] ? 'Enabled' : 'Disabled' }}
                                                        </label>
                                                    </div>
                                                @elseif($settingData['type'] === 'array')
                                                    <textarea class="form-control"
                                                              id="{{ $groupKey }}_{{ $settingKey }}"
                                                              name="settings[{{ $groupKey }}][{{ $settingKey }}]"
                                                              rows="3"
                                                              placeholder="Enter values separated by commas">{{ is_array($settingData['value']) ? implode(', ', $settingData['value']) : $settingData['value'] }}</textarea>
                                                    <small class="form-text text-muted">Separate multiple values with commas</small>
                                                @elseif(in_array($settingKey, ['password', 'api_key', 'secret']))
                                                    <div class="input-group">
                                                        <input type="password"
                                                               class="form-control"
                                                               id="{{ $groupKey }}_{{ $settingKey }}"
                                                               name="settings[{{ $groupKey }}][{{ $settingKey }}]"
                                                               value="{{ $settingData['value'] }}"
                                                               placeholder="Enter {{ str_replace('_', ' ', $settingKey) }}">
                                                        <div class="input-group-append">
                                                            <button class="btn btn-outline-secondary toggle-password" type="button">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                @else
                                                    <input type="{{ $settingData['type'] === 'integer' || $settingData['type'] === 'float' ? 'number' : 'text' }}"
                                                           class="form-control"
                                                           id="{{ $groupKey }}_{{ $settingKey }}"
                                                           name="settings[{{ $groupKey }}][{{ $settingKey }}]"
                                                           value="{{ $settingData['value'] }}"
                                                           {{ $settingData['type'] === 'float' ? 'step=0.01' : '' }}
                                                           placeholder="Enter {{ str_replace('_', ' ', $settingKey) }}">
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="col-12">
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle mr-2"></i>
                                            No settings available for this group yet. Settings will be created automatically when you first save them.
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="card-footer">
                <div class="row">
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-success" id="save-settings">
                            <i class="fas fa-save mr-1"></i> Save Settings
                        </button>
                        <button type="button" class="btn btn-secondary ml-2" id="reset-form">
                            <i class="fas fa-undo mr-1"></i> Reset Form
                        </button>
                    </div>
                    <div class="col-md-6 text-right">
                        <small class="text-muted">
                            <i class="fas fa-info-circle mr-1"></i>
                            Changes will take effect immediately. Some settings may require a system restart.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@stop

@section('css')
<style>
    .nav-tabs .nav-link {
        border-top-left-radius: 0.25rem;
        border-top-right-radius: 0.25rem;
    }

    .nav-tabs .nav-link.active {
        font-weight: 600;
    }

    .custom-control-label::after {
        transition: transform 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .form-group label {
        font-weight: 500;
        color: #495057;
    }

    .card-header .nav-tabs {
        margin-bottom: -1px;
    }

    .card-header .nav-tabs .nav-link {
        border-bottom: 1px solid transparent;
        margin-bottom: -1px;
    }

    .card-header .nav-tabs .nav-link.active {
        border-color: #dee2e6 #dee2e6 #fff;
        background-color: #fff;
    }

    .tab-content {
        min-height: 400px;
    }

    .loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(255, 255, 255, 0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();

    // Toggle password visibility
    $('.toggle-password').on('click', function() {
        const input = $(this).parent().prev('input');
        const icon = $(this).find('i');

        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // Handle form submission
    $('#settings-form').on('submit', function(e) {
        e.preventDefault();

        const submitBtn = $('#save-settings');
        const originalText = submitBtn.html();

        // Show loading state
        submitBtn.prop('disabled', true)
                 .html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');

        // Prepare form data
        const formData = new FormData(this);

        // Handle checkboxes (unchecked checkboxes don't get submitted)
        $('.custom-control-input[type="checkbox"]').each(function() {
            if (!this.checked) {
                formData.append(this.name, '0');
            }
        });

        // Handle array fields
        $('textarea[name*="["]').each(function() {
            const value = $(this).val().split(',').map(v => v.trim()).filter(v => v.length > 0);
            formData.set(this.name, JSON.stringify(value));
        });

        $.ajax({
            url: '{{ route("admin.superadmin.settings.update") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showAlert('success', response.message);

                    // Update switch labels
                    $('.custom-control-input[type="checkbox"]').each(function() {
                        const label = $(this).next('.custom-control-label');
                        label.text(this.checked ? 'Enabled' : 'Disabled');
                    });
                } else {
                    showAlert('error', response.message || 'Failed to update settings');
                }
            },
            error: function(xhr) {
                let message = 'An error occurred while updating settings';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                showAlert('error', message);
            },
            complete: function() {
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });

    // Reset form
    $('#reset-form').on('click', function() {
        if (confirm('Are you sure you want to reset all changes? This will reload the page.')) {
            location.reload();
        }
    });

    // Reset group to defaults
    $('.reset-group').on('click', function() {
        const group = $(this).data('group');
        const groupName = $(this).closest('.tab-pane').find('h5').text();

        if (confirm(`Are you sure you want to reset all settings in "${groupName}" to their default values? This action cannot be undone.`)) {
            const btn = $(this);
            const originalText = btn.html();

            btn.prop('disabled', true)
               .html('<i class="fas fa-spinner fa-spin mr-1"></i> Resetting...');

            $.ajax({
                url: '{{ route("admin.superadmin.settings.reset") }}',
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    group: group
                },
                success: function(response) {
                    if (response.success) {
                        showAlert('success', response.message);
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        showAlert('error', response.message || 'Failed to reset settings');
                    }
                },
                error: function(xhr) {
                    let message = 'An error occurred while resetting settings';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    }
                    showAlert('error', message);
                },
                complete: function() {
                    btn.prop('disabled', false).html(originalText);
                }
            });
        }
    });

    // Test API connection
    $('.test-connection').on('click', function() {
        const group = $(this).data('group');
        const btn = $(this);
        const originalText = btn.html();

        btn.prop('disabled', true)
           .html('<i class="fas fa-spinner fa-spin mr-1"></i> Testing...');

        // Test multiple APIs for integration group
        const apiTypes = ['nimc', 'frsc', 'sms', 'ocr'];
        let testResults = [];
        let completedTests = 0;

        apiTypes.forEach(apiType => {
            $.ajax({
                url: '{{ route("admin.superadmin.settings.test-api") }}',
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    api_type: apiType
                },
                success: function(response) {
                    testResults.push({
                        api: apiType.toUpperCase(),
                        success: response.success,
                        message: response.message
                    });
                },
                error: function(xhr) {
                    testResults.push({
                        api: apiType.toUpperCase(),
                        success: false,
                        message: 'Connection failed'
                    });
                },
                complete: function() {
                    completedTests++;
                    if (completedTests === apiTypes.length) {
                        // All tests completed, show results
                        let resultHtml = '<h6>API Connection Test Results:</h6><ul>';
                        testResults.forEach(result => {
                            const icon = result.success ? 'fa-check text-success' : 'fa-times text-danger';
                            resultHtml += `<li><i class="fas ${icon} mr-1"></i> ${result.api}: ${result.message}</li>`;
                        });
                        resultHtml += '</ul>';

                        showAlert('info', resultHtml);
                        btn.prop('disabled', false).html(originalText);
                    }
                }
            });
        });
    });

    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' :
                          type === 'error' ? 'alert-danger' :
                          type === 'warning' ? 'alert-warning' : 'alert-info';

        const icon = type === 'success' ? 'fa-check-circle' :
                     type === 'error' ? 'fa-exclamation-circle' :
                     type === 'warning' ? 'fa-exclamation-triangle' : 'fa-info-circle';

        const alert = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                <i class="fas ${icon} mr-2"></i>
                ${message}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        `;

        $('#alert-container').html(alert);

        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            $('.alert').fadeOut();
        }, 5000);
    }
});

// Add CSRF token to all AJAX requests
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
</script>
@stop

@php
function getTabIcon($groupKey) {
    $icons = [
        'general' => 'cog',
        'security' => 'shield-alt',
        'commission' => 'money-bill-wave',
        'notification' => 'bell',
        'integration' => 'plug',
        'verification' => 'check-double',
        'system' => 'server'
    ];
    return $icons[$groupKey] ?? 'cog';
}
@endphp
