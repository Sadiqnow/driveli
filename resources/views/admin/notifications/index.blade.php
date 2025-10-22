@extends('layouts.admin_cdn')

@section('title', 'Notification Template Manager')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1>Notification Template Manager</h1>
            <p class="text-muted">Manage email and SMS notification templates</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h4 class="mb-0">{{ ($stats['total_sent'] ?? 0) + ($stats['total_failed'] ?? 0) }}</h4>
                            <p class="mb-0">Total Sent</p>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="fas fa-paper-plane fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h4 class="mb-0">{{ $stats['total_sent'] ?? 0 }}</h4>
                            <p class="mb-0">Successful</p>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h4 class="mb-0">{{ $stats['total_failed'] ?? 0 }}</h4>
                            <p class="mb-0">Failed</p>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="fas fa-times-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h4 class="mb-0">{{ count($templates['email'] ?? []) + count($templates['sms'] ?? []) }}</h4>
                            <p class="mb-0">Templates</p>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="fas fa-envelope fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Template Type Tabs -->
    <div class="row mb-4">
        <div class="col-md-12">
            <ul class="nav nav-tabs" id="templateTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $type === 'email' ? 'active' : '' }}" id="email-tab" data-bs-toggle="tab"
                            data-bs-target="#email-templates" type="button" role="tab">
                        <i class="fas fa-envelope"></i> Email Templates
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $type === 'sms' ? 'active' : '' }}" id="sms-tab" data-bs-toggle="tab"
                            data-bs-target="#sms-templates" type="button" role="tab">
                        <i class="fas fa-sms"></i> SMS Templates
                    </button>
                </li>
            </ul>

            <div class="tab-content mt-3" id="templateTabsContent">
                <!-- Email Templates Tab -->
                <div class="tab-pane fade {{ $type === 'email' ? 'show active' : '' }}" id="email-templates" role="tabpanel">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Email Templates</h5>
                            <a href="{{ route('admin.notifications.create', ['type' => 'email']) }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> New Email Template
                            </a>
                        </div>
                        <div class="card-body">
                            @if(isset($templates['email']) && $templates['email']->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Subject</th>
                                                <th>Status</th>
                                                <th>Created By</th>
                                                <th>Created</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($templates['email'] as $template)
                                            <tr>
                                                <td>
                                                    <strong>{{ $template->name }}</strong>
                                                </td>
                                                <td>{{ Str::limit($template->subject, 50) }}</td>
                                                <td>
                                                    <span class="badge badge-{{ $template->is_active ? 'success' : 'secondary' }}">
                                                        {{ $template->is_active ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </td>
                                                <td>{{ $template->creator->name ?? 'System' }}</td>
                                                <td>{{ $template->created_at->format('M d, Y') }}</td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <button class="btn btn-sm btn-info" onclick="previewTemplate('email', '{{ $template->name }}')">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-warning" onclick="testTemplate('email', '{{ $template->name }}')">
                                                            <i class="fas fa-paper-plane"></i>
                                                        </button>
                                                        <a href="{{ route('admin.notifications.edit', ['type' => 'email', 'id' => $template->id]) }}" class="btn btn-sm btn-primary">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <form method="POST" action="{{ route('admin.notifications.destroy', ['type' => 'email', 'id' => $template->id]) }}"
                                                              style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this template?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-danger">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-envelope fa-3x text-muted mb-3"></i>
                                    <h5>No Email Templates</h5>
                                    <p class="text-muted">Create your first email template to get started.</p>
                                    <a href="{{ route('admin.notifications.create', ['type' => 'email']) }}" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Create Email Template
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- SMS Templates Tab -->
                <div class="tab-pane fade {{ $type === 'sms' ? 'show active' : '' }}" id="sms-templates" role="tabpanel">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">SMS Templates</h5>
                            <a href="{{ route('admin.notifications.create', ['type' => 'sms']) }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> New SMS Template
                            </a>
                        </div>
                        <div class="card-body">
                            @if(isset($templates['sms']) && $templates['sms']->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Message Preview</th>
                                                <th>Status</th>
                                                <th>Created By</th>
                                                <th>Created</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($templates['sms'] as $template)
                                            <tr>
                                                <td>
                                                    <strong>{{ $template->name }}</strong>
                                                </td>
                                                <td>{{ Str::limit($template->body, 50) }}</td>
                                                <td>
                                                    <span class="badge badge-{{ $template->is_active ? 'success' : 'secondary' }}">
                                                        {{ $template->is_active ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </td>
                                                <td>{{ $template->creator->name ?? 'System' }}</td>
                                                <td>{{ $template->created_at->format('M d, Y') }}</td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <button class="btn btn-sm btn-info" onclick="previewTemplate('sms', '{{ $template->name }}')">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-warning" onclick="testTemplate('sms', '{{ $template->name }}')">
                                                            <i class="fas fa-paper-plane"></i>
                                                        </button>
                                                        <a href="{{ route('admin.notifications.edit', ['type' => 'sms', 'id' => $template->id]) }}" class="btn btn-sm btn-primary">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <form method="POST" action="{{ route('admin.notifications.destroy', ['type' => 'sms', 'id' => $template->id]) }}"
                                                              style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this template?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-danger">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-sms fa-3x text-muted mb-3"></i>
                                    <h5>No SMS Templates</h5>
                                    <p class="text-muted">Create your first SMS template to get started.</p>
                                    <a href="{{ route('admin.notifications.create', ['type' => 'sms']) }}" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Create SMS Template
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Notification Logs -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Recent Notification Logs</h5>
                </div>
                <div class="card-body">
                    <div id="notificationLogs">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Loading notification logs...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Template Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="previewContent">
                <!-- Preview content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Test Modal -->
<div class="modal fade" id="testModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Send Test Notification</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="testForm">
                    <div class="mb-3">
                        <label for="testRecipient" class="form-label">Recipient</label>
                        <input type="text" class="form-control" id="testRecipient" placeholder="email@domain.com or phone number" required>
                    </div>
                    <div class="mb-3">
                        <label for="testVariables" class="form-label">Variables (JSON)</label>
                        <textarea class="form-control" id="testVariables" rows="3" placeholder='{"name": "John Doe", "company": "ABC Corp"}'></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="sendTestNotification()">Send Test</button>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
let currentTestType = '';
let currentTestTemplate = '';

// Load notification logs on page load
document.addEventListener('DOMContentLoaded', function() {
    loadNotificationLogs();
});

// Load recent notification logs
function loadNotificationLogs() {
    fetch('{{ route("admin.notifications.logs") }}')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('notificationLogs');
            if (data.logs && data.logs.length > 0) {
                let html = '<div class="table-responsive"><table class="table table-sm"><thead><tr><th>Type</th><th>Recipient</th><th>Template</th><th>Status</th><th>Sent At</th></tr></thead><tbody>';

                data.logs.forEach(log => {
                    html += `
                        <tr>
                            <td><span class="badge badge-${log.type === 'email' ? 'primary' : 'info'}">${log.type.toUpperCase()}</span></td>
                            <td>${log.recipient}</td>
                            <td>${log.template_name || 'N/A'}</td>
                            <td><span class="badge badge-${getStatusBadgeClass(log.status)}">${log.status}</span></td>
                            <td><small>${log.sent_at ? new Date(log.sent_at).toLocaleString() : 'N/A'}</small></td>
                        </tr>
                    `;
                });

                html += '</tbody></table></div>';
                container.innerHTML = html;
            } else {
                container.innerHTML = '<div class="text-center py-4"><i class="fas fa-history fa-3x text-muted mb-3"></i><h5>No notification logs</h5><p class="text-muted">Notification activity will appear here.</p></div>';
            }
        })
        .catch(error => {
            console.error('Error loading logs:', error);
            document.getElementById('notificationLogs').innerHTML = '<div class="alert alert-danger">Error loading notification logs</div>';
        });
}

// Preview template
function previewTemplate(type, templateName) {
    const variables = prompt('Enter variables as JSON (optional):', '{"name": "John Doe"}');
    if (variables === null) return;

    let variablesObj;
    try {
        variablesObj = JSON.parse(variables);
    } catch (e) {
        alert('Invalid JSON format');
        return;
    }

    fetch('{{ route("admin.notifications.preview") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            type: type,
            template_name: templateName,
            variables: variablesObj
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const modal = new bootstrap.Modal(document.getElementById('previewModal'));
            const content = document.getElementById('previewContent');

            let html = `<div class="row">
                <div class="col-md-6">
                    <h6>Template Information</h6>
                    <p><strong>Type:</strong> ${type.toUpperCase()}</p>
                    <p><strong>Name:</strong> ${templateName}</p>
                    <p><strong>Variables:</strong></p>
                    <pre class="bg-light p-2 small">${JSON.stringify(data.variables, null, 2)}</pre>
                </div>
                <div class="col-md-6">
                    <h6>Rendered Content</h6>`;

            if (type === 'email') {
                html += `<p><strong>Subject:</strong> ${data.content.subject}</p>
                    <p><strong>Body:</strong></p>
                    <div class="border p-2 bg-light">${data.content.body}</div>`;
            } else {
                html += `<p><strong>Message:</strong></p>
                    <div class="border p-2 bg-light">${data.content.body}</div>`;
            }

            html += `</div></div>`;

            content.innerHTML = html;
            modal.show();
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error previewing template:', error);
        alert('Error previewing template');
    });
}

// Test template
function testTemplate(type, templateName) {
    currentTestType = type;
    currentTestTemplate = templateName;

    const modal = new bootstrap.Modal(document.getElementById('testModal'));
    document.getElementById('testRecipient').value = '';
    document.getElementById('testVariables').value = '{"name": "Test User"}';
    modal.show();
}

// Send test notification
function sendTestNotification() {
    const recipient = document.getElementById('testRecipient').value;
    const variables = document.getElementById('testVariables').value;

    if (!recipient) {
        alert('Please enter a recipient');
        return;
    }

    let variablesObj;
    try {
        variablesObj = JSON.parse(variables || '{}');
    } catch (e) {
        alert('Invalid JSON format for variables');
        return;
    }

    fetch('{{ route("admin.notifications.test") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            type: currentTestType,
            template_name: currentTestTemplate,
            recipient: recipient,
            variables: variablesObj
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.message) {
            alert(data.message);
            bootstrap.Modal.getInstance(document.getElementById('testModal')).hide();
            loadNotificationLogs(); // Refresh logs
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error sending test notification:', error);
        alert('Error sending test notification');
    });
}

// Helper function for status badge classes
function getStatusBadgeClass(status) {
    switch (status) {
        case 'sent':
        case 'delivered':
            return 'success';
        case 'failed':
            return 'danger';
        case 'pending':
            return 'warning';
        default:
            return 'secondary';
    }
}
</script>
@endsection
