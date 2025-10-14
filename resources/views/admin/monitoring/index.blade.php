@extends('layouts.admin')

@section('title', 'Driver Monitoring Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Driver Monitoring Dashboard</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-primary btn-sm" id="refreshDashboard">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="card-body">
                    <div class="row" id="statsRow">
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3 id="activeDriversCount">{{ $dashboard['active_drivers'] ?? 0 }}</h3>
                                    <p>Active Drivers</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-success">
                                <h3 id="locationsCount">{{ $dashboard['total_locations_recorded'] ?? 0 }}</h3>
                                <p>Locations Recorded (24h)</p>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-warning">
                                <h3 id="suspiciousCount">{{ count($dashboard['suspicious_activities'] ?? []) }}</h3>
                                <p>Suspicious Activities</p>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-danger">
                                <h3 id="traceAlertsCount">0</h3>
                                <p>Trace Alerts</p>
                            </div>
                        </div>
                    </div>

                    <!-- Real-time Map -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Real-time Driver Locations</h4>
                                    <div class="card-tools">
                                        <div class="input-group input-group-sm" style="width: 200px;">
                                            <input type="number" class="form-control" id="mapMinutes" value="30" min="5" max="1440">
                                            <div class="input-group-append">
                                                <span class="input-group-text">minutes</span>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-secondary btn-sm ml-2" id="refreshMap">
                                            <i class="fas fa-map-marker-alt"></i> Refresh Map
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="driverMap" style="height: 500px; width: 100%;"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Locations Table -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Recent Driver Activity</h4>
                                </div>
                                <div class="card-body table-responsive p-0">
                                    <table class="table table-hover text-nowrap">
                                        <thead>
                                            <tr>
                                                <th>Driver</th>
                                                <th>Location</th>
                                                <th>Device Info</th>
                                                <th>Recorded At</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="recentLocationsTable">
                                            @forelse($dashboard['recent_locations'] ?? [] as $location)
                                            <tr>
                                                <td>{{ $location->driver->full_name ?? 'Unknown' }}</td>
                                                <td>
                                                    <small>
                                                        {{ number_format($location->latitude, 6) }},
                                                        {{ number_format($location->longitude, 6) }}
                                                    </small>
                                                </td>
                                                <td>{{ $location->device_info ?? 'N/A' }}</td>
                                                <td>{{ $location->recorded_at->diffForHumans() }}</td>
                                                <td>
                                                    <a href="{{ route('admin.monitoring.driver', $location->driver_id) }}"
                                                       class="btn btn-sm btn-info">
                                                        <i class="fas fa-eye"></i> Monitor
                                                    </a>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="5" class="text-center">No recent locations</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Trace Alerts Section -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Active Trace Alerts</h4>
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-warning btn-sm" id="refreshAlerts">
                                            <i class="fas fa-bell"></i> Refresh Alerts
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body table-responsive p-0">
                                    <table class="table table-hover text-nowrap">
                                        <thead>
                                            <tr>
                                                <th>Driver</th>
                                                <th>Alert Type</th>
                                                <th>Severity</th>
                                                <th>Triggered</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="traceAlertsTable">
                                            <!-- Alerts will be loaded via AJAX -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />

<script>
$(document).ready(function() {
    let map;
    let markers = [];

    // Initialize map
    initializeMap();

    // Load initial data
    loadDashboardData();
    loadTraceAlerts();

    // Refresh dashboard button
    $('#refreshDashboard').click(function() {
        loadDashboardData();
    });

    // Refresh map button
    $('#refreshMap').click(function() {
        loadMapData();
    });

    // Refresh alerts button
    $('#refreshAlerts').click(function() {
        loadTraceAlerts();
    });

    // Auto refresh every 30 seconds
    setInterval(function() {
        loadDashboardData();
        loadMapData();
        loadTraceAlerts();
    }, 30000);

    function initializeMap() {
        map = L.map('driverMap').setView([6.5244, 3.3792], 10); // Lagos coordinates

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);
    }

    function loadDashboardData() {
        $.get('{{ route("api.admin.monitoring.dashboard") }}', function(data) {
            if (data.success) {
                $('#activeDriversCount').text(data.data.active_drivers);
                $('#locationsCount').text(data.data.total_locations_recorded);
                $('#suspiciousCount').text(data.data.suspicious_activities.length);
            }
        });
    }

    function loadMapData() {
        const minutes = $('#mapMinutes').val();

        $.get('{{ route("api.admin.monitoring.locations") }}', { minutes: minutes }, function(data) {
            if (data.success) {
                updateMapMarkers(data.data);
            }
        });
    }

    function updateMapMarkers(locations) {
        // Clear existing markers
        markers.forEach(marker => map.removeLayer(marker));
        markers = [];

        locations.forEach(function(location) {
            const marker = L.marker([location.latitude, location.longitude])
                .addTo(map)
                .bindPopup(`
                    <b>${location.driver_name}</b><br>
                    Recorded: ${new Date(location.recorded_at).toLocaleString()}<br>
                    <a href="{{ url('admin/monitoring/driver') }}/${location.driver_id}" class="btn btn-sm btn-primary">Monitor Driver</a>
                `);

            markers.push(marker);
        });

        // Fit map to show all markers
        if (markers.length > 0) {
            const group = new L.featureGroup(markers);
            map.fitBounds(group.getBounds().pad(0.1));
        }
    }

    function loadTraceAlerts() {
        $.get('{{ route("api.admin.monitoring.alerts") }}', function(data) {
            if (data.success) {
                updateAlertsTable(data.data);
                $('#traceAlertsCount').text(data.data.length);
            }
        });
    }

    function updateAlertsTable(alerts) {
        const tbody = $('#traceAlertsTable');
        tbody.empty();

        if (alerts.length === 0) {
            tbody.append('<tr><td colspan="5" class="text-center">No active alerts</td></tr>');
            return;
        }

        alerts.forEach(function(alert) {
            const severityClass = getSeverityClass(alert.severity);
            const row = `
                <tr>
                    <td>${alert.driver_name}</td>
                    <td>${alert.alert_type_description}</td>
                    <td><span class="badge badge-${severityClass}">${alert.severity}</span></td>
                    <td>${new Date(alert.triggered_at).toLocaleString()}</td>
                    <td>
                        <a href="{{ url('admin/monitoring/driver') }}/${alert.driver_id}" class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i> View
                        </a>
                        <button type="button" class="btn btn-sm btn-success resolve-alert" data-alert-id="${alert.id}">
                            <i class="fas fa-check"></i> Resolve
                        </button>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });

        // Bind resolve alert buttons
        $('.resolve-alert').click(function() {
            const alertId = $(this).data('alert-id');
            resolveAlert(alertId);
        });
    }

    function getSeverityClass(severity) {
        const classes = {
            'critical': 'danger',
            'high': 'warning',
            'medium': 'info',
            'low': 'secondary'
        };
        return classes[severity] || 'secondary';
    }

    function resolveAlert(alertId) {
        if (confirm('Are you sure you want to resolve this alert?')) {
            $.post(`{{ url('admin/monitoring/alerts') }}/${alertId}/resolve`, {
                _token: '{{ csrf_token() }}',
                notes: 'Resolved via dashboard'
            }, function(data) {
                if (data.success) {
                    loadTraceAlerts();
                    alert('Alert resolved successfully');
                } else {
                    alert('Failed to resolve alert');
                }
            });
        }
    }
});
</script>
@endsection
@endsection
