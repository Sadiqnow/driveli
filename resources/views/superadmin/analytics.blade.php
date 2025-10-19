@extends('layouts.superadmin_master')

@section('title', 'Permission Analytics Dashboard')

@section('content')
<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Permission Analytics Dashboard</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.superadmin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Analytics</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Filters -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Filters</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <label for="start_date">Start Date</label>
                                    <input type="date" id="start_date" class="form-control" value="{{ now()->subDays(30)->format('Y-m-d') }}">
                                </div>
                                <div class="col-md-3">
                                    <label for="end_date">End Date</label>
                                    <input type="date" id="end_date" class="form-control" value="{{ now()->format('Y-m-d') }}">
                                </div>
                                <div class="col-md-3">
                                    <label for="module_filter">Module</label>
                                    <select id="module_filter" class="form-control">
                                        <option value="">All Modules</option>
                                        <option value="admin">Admin</option>
                                        <option value="driver">Driver</option>
                                        <option value="company">Company</option>
                                        <option value="system">System</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label>&nbsp;</label>
                                    <div>
                                        <button id="apply_filters" class="btn btn-primary">Apply Filters</button>
                                        <button id="reset_filters" class="btn btn-secondary ml-2">Reset</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Role Summary Section -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3 id="total_roles">{{ $analytics['roles']['total_roles'] ?? 0 }}</h3>
                            <p>Total Roles</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-users-cog"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3 id="total_permissions">{{ $analytics['roles']['total_permissions'] ?? 0 }}</h3>
                            <p>Total Permissions</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-key"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3 id="total_users">{{ $analytics['roles']['total_users'] ?? 0 }}</h3>
                            <p>Total Users</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-user"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3 id="total_violations">{{ $analytics['violations']['total_last_30_days'] ?? 0 }}</h3>
                            <p>Access Violations (30d)</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="row">
                <!-- Users per Role Chart -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Users per Role</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <canvas id="usersPerRoleChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Permission Usage Chart -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Permission Usage Distribution</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <canvas id="permissionUsageChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Access Violations Timeline -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Access Violations Timeline</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <canvas id="violationsTimelineChart" style="min-height: 300px; height: 300px; max-height: 300px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Tables -->
            <div class="row">
                <!-- Most Used Permissions -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Most Used Permissions</h3>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover text-nowrap">
                                <thead>
                                    <tr>
                                        <th>Permission</th>
                                        <th>Usage Count</th>
                                        <th>Granted</th>
                                        <th>Denied</th>
                                        <th>%</th>
                                    </tr>
                                </thead>
                                <tbody id="mostUsedPermissionsTable">
                                    <!-- Data will be loaded via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Recent Violations -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Recent Access Violations</h3>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover text-nowrap">
                                <thead>
                                    <tr>
                                        <th>Module</th>
                                        <th>Resource</th>
                                        <th>IP Address</th>
                                        <th>Count</th>
                                        <th>Last Seen</th>
                                    </tr>
                                </thead>
                                <tbody id="recentViolationsTable">
                                    <!-- Data will be loaded via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Export Actions -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Export Data</h3>
                        </div>
                        <div class="card-body">
                            <div class="btn-group">
                                <button class="btn btn-info" onclick="exportData('roles', 'csv')">
                                    <i class="fas fa-download"></i> Export Roles (CSV)
                                </button>
                                <button class="btn btn-success" onclick="exportData('violations', 'csv')">
                                    <i class="fas fa-download"></i> Export Violations (CSV)
                                </button>
                                <button class="btn btn-warning" onclick="exportData('usage', 'csv')">
                                    <i class="fas fa-download"></i> Export Usage (CSV)
                                </button>
                            </div>
                            <div class="btn-group ml-2">
                                <button class="btn btn-primary" onclick="sendWeeklyReport()">
                                    <i class="fas fa-envelope"></i> Send Weekly Report
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    loadAnalyticsData();

    $('#apply_filters').click(function() {
        loadAnalyticsData();
    });

    $('#reset_filters').click(function() {
        $('#start_date').val('{{ now()->subDays(30)->format('Y-m-d') }}');
        $('#end_date').val('{{ now()->format('Y-m-d') }}');
        $('#module_filter').val('');
        loadAnalyticsData();
    });
});

function loadAnalyticsData() {
    const startDate = $('#start_date').val();
    const endDate = $('#end_date').val();
    const module = $('#module_filter').val();

    // Load roles data
    $.get('/api/analytics/roles', function(response) {
        if (response.success) {
            updateRolesChart(response.data);
        }
    });

    // Load violations data
    $.get('/api/analytics/violations', {
        start_date: startDate,
        end_date: endDate,
        module: module
    }, function(response) {
        if (response.success) {
            updateViolationsChart(response.data);
            updateViolationsTable(response.data.recent_violations);
        }
    });

    // Load usage data
    $.get('/api/analytics/usage', {
        start_date: startDate,
        end_date: endDate
    }, function(response) {
        if (response.success) {
            updateUsageChart(response.data);
            updateUsageTable(response.data.most_used_permissions);
        }
    });
}

function updateRolesChart(data) {
    const ctx = document.getElementById('usersPerRoleChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.users_per_role.map(item => item.role),
            datasets: [{
                label: 'Users per Role',
                data: data.users_per_role.map(item => item.users_count),
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

function updateUsageChart(data) {
    const ctx = document.getElementById('permissionUsageChart').getContext('2d');
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: data.most_used_permissions.map(item => item.name),
            datasets: [{
                data: data.most_used_permissions.map(item => item.usage_count),
                backgroundColor: [
                    'rgba(255, 99, 132, 0.5)',
                    'rgba(54, 162, 235, 0.5)',
                    'rgba(255, 205, 86, 0.5)',
                    'rgba(75, 192, 192, 0.5)',
                    'rgba(153, 102, 255, 0.5)',
                    'rgba(255, 159, 64, 0.5)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 205, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}

function updateViolationsChart(data) {
    const ctx = document.getElementById('violationsTimelineChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.timeline.map(item => item.date),
            datasets: [{
                label: 'Access Violations',
                data: data.timeline.map(item => item.count),
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 2,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

function updateUsageTable(permissions) {
    const tbody = $('#mostUsedPermissionsTable');
    tbody.empty();

    permissions.forEach(permission => {
        tbody.append(`
            <tr>
                <td>${permission.name}</td>
                <td>${permission.usage_count}</td>
                <td><span class="badge badge-success">${permission.granted_count || 0}</span></td>
                <td><span class="badge badge-danger">${permission.denied_count || 0}</span></td>
                <td>${permission.percentage}%</td>
            </tr>
        `);
    });
}

function updateViolationsTable(violations) {
    const tbody = $('#recentViolationsTable');
    tbody.empty();

    violations.forEach(violation => {
        tbody.append(`
            <tr>
                <td>${violation.module}</td>
                <td>${violation.resource}</td>
                <td>${violation.ip_address}</td>
                <td><span class="badge badge-warning">${violation.count}</span></td>
                <td>${new Date(violation.last_seen).toLocaleDateString()}</td>
            </tr>
        `);
    });
}

function exportData(type, format) {
    const url = '{{ route("admin.superadmin.analytics.export") }}'.replace('export', 'export') +
                `?type=${type}&format=${format}&start_date=${$('#start_date').val()}&end_date=${$('#end_date').val()}`;

    window.open(url, '_blank');
}

function sendWeeklyReport() {
    $.post('{{ route("admin.superadmin.analytics.send-weekly-report") }}', function(response) {
        if (response.success) {
            alert('Weekly report sent successfully!');
        } else {
            alert('Failed to send weekly report.');
        }
    });
}
</script>
@endsection
