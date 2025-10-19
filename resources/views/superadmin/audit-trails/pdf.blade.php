<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Audit Trails Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #333; border-bottom: 2px solid #333; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .badge { padding: 4px 8px; border-radius: 4px; color: white; font-size: 12px; }
        .badge-success { background-color: #28a745; }
        .badge-danger { background-color: #dc3545; }
        .badge-warning { background-color: #ffc107; color: #000; }
        .header-info { margin-bottom: 20px; }
        .header-info p { margin: 5px 0; }
    </style>
</head>
<body>
    <h1>Audit Trails Report</h1>

    <div class="header-info">
        <p><strong>Generated on:</strong> {{ now()->format('M d, Y H:i:s') }}</p>
        <p><strong>Total Records:</strong> {{ $auditTrails->count() }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Action Type</th>
                <th>User</th>
                <th>Role</th>
                <th>Target User</th>
                <th>Description</th>
                <th>IP Address</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            @foreach($auditTrails as $audit)
                <tr>
                    <td>{{ $audit->id }}</td>
                    <td>
                        <span class="badge badge-{{ $audit->action_type == 'assign' ? 'success' : ($audit->action_type == 'revoke' ? 'danger' : 'warning') }}">
                            {{ ucfirst($audit->action_type) }}
                        </span>
                    </td>
                    <td>{{ $audit->user ? $audit->user->name : 'N/A' }}</td>
                    <td>{{ $audit->role ? $audit->role->display_name : 'N/A' }}</td>
                    <td>{{ $audit->targetUser ? $audit->targetUser->name : 'N/A' }}</td>
                    <td>{{ $audit->description }}</td>
                    <td>{{ $audit->ip_address }}</td>
                    <td>{{ $audit->created_at->format('M d, Y H:i:s') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
