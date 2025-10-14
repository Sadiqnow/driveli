@extends('layouts.admin')

@section('title', 'Monitor Driver: ' . ($driver->full_name ?? 'Unknown'))

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        Monitoring: {{ $driver->full_name ?? 'Unknown Driver' }}
                        <span class="badge badge-success ml-2" id="monitoringStatus">Active</span>
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.monitoring.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                        <button type="button" class="btn btn-warning btn-sm" id="sendChallenge">
                            <i class="fas fa-shield-alt"></i> Send OTP Challenge
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Driver Info -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-4">Driver ID:</dt>
                                <dd class="col-sm-8">{{ $driver->id }}</dd>

                                <dt class="col-sm-4">Phone:</dt>
                                <dd class="col-sm-8">{{ $driver->phone ?? 'N/A' }}</dd>

                                <dt class="col-sm-4">Status:</dt>
                                <dd class="col-sm-8">
                                    <span class="badge badge-{{ $driver->is_current ? 'success' : 'danger' }}">
                                        {{ $driver->is_current ? 'Active' : 'Inactive' }}
                                    </span>
                                </dd>
                            </dl>
                        </div>
                        <div class="col-md-6">
                            <dl class="row">
                                <dt class="col-sm-4">Last Location:</dt>
                                <dd class="col-sm-8" id="lastLocation">Loading...</dd>

                                <dt class="col-sm-4">Last Activity:</dt>
                                <dd class="col-sm-8" id="lastActivity">Loading...</dd>

                                <dt class="col-sm-4">Device Info:</dt>
                                <dd class="col-sm-8" id="deviceInfo">Loading...</dd>
                            </dl>
                        </div>
                    </div>

                    <!-- Real-time Map for this driver -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Driver Location History</h4>
                                    <div class="card-tools">
                                        <select class="form-control form-control-sm" id="timeRange" style="width: auto;">
                                            <option value="30">Last 30 minutes</option>
                                            <option value="60">Last hour</option>
                                            <option value="120">Last 2 hours</option>
                                            <option value="480">Last 8 hours</option>
                                            <option value="1440">Last 24 hours</option>
                                        </select>
                                        <button type="button" class="btn btn-primary btn-sm ml-2" id="refreshLocations">
                                            <i class="fas fa-sync-alt"></i> Refresh
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div id="driverLocationMap" style="height: 400px; width: 100%;"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Activity Log -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Recent Activity</h4>
                                </div>
                                <div class="card-body table-responsive p-0">
                                    <table class="table table-hover text-nowrap">
                                        <thead>
                                            <tr>
                                                <th>Activity</th>
                                                <th>Description</th>
                                                <th>Location</th>
                                                <th>Timestamp</th>
                                            </tr>
                                        </thead>
                                        <tbody id="activityTable">
                                            <!-- Activity will be loaded via AJAX -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Suspicious Activity Detection -->
                    <div class="row mt-4" id="suspiciousActivitySection" style="display: none;">
                        <div class="col-12">
                            <div class="card border-warning">
                                <div class="card-header bg-warning">
                                    <h4 class="card-title text-white">
                                        <i class="fas fa-exclamation-triangle"></i> Suspicious Activity Detected
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <div id="suspiciousActivityContent">
                                        <!-- Suspicious activity details will be loaded here -->
                                    </div>
                                    <div class="mt-3">
                                        <button type="button" class="btn btn-warning" id="sendChallengeBtn">
                                            <i class="fas fa-shield-alt"></i> Send OTP Challenge
                                        </button>
                                        <button type="button" class="btn btn-info" id="viewLocationHistory">
                                            <i class="fas fa-history"></i> View Location History
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar with quick actions -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Quick Actions</h4>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-primary" id="viewFullProfile">
                            <i class="fas fa-user"></i> View Full Profile
                        </button>
                        <button type="button" class="btn btn-info" id="viewDocuments">
                            <i class="fas fa-file-alt"></i> View Documents
                        </button>
                        <button type="button" class="btn btn-warning" id="viewPerformance">
                            <i class="fas fa-chart-line"></i> View Performance
                        </button>
                        <button type="button" class="btn btn-danger" id="deactivateDriver">
                            <i class="fas fa-user-times"></i> Deactivate Driver
                        </button>
                    </div>
                </div>
            </div>

            <!-- Trace Alerts for this driver -->
            <div class="card mt-3">
                <div class="card-header">
                    <h4 class="card-title">Active Alerts</h4>
                </div>
                <div class="card-body" id="driverAlerts">
                    <p class="text-muted">No active alerts</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- OTP Challenge Modal -->
<div class="modal fade" id="otpChallengeModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Send OTP Challenge</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="otpChallengeForm">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="challengeReason">Reason for Challenge</label>
                        <textarea name="reason" id="challengeReason" class="form-control" rows="3" required
                                  placeholder="Describe why you're sending this OTP challenge..."></textarea>
                    </div>
                    <p class="text-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        This will send an OTP to the driver for verification.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Send OTP Challenge</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />

<script>
$(document).ready(function() {
    const driverId = {{ $driver->id }};
    let map;
    let locationMarkers = [];
    let routePolyline;

    // Initialize map
    initializeMap();

    // Load initial data
    loadDriverData();
    loadLocations();
    loadActivityLog();
    checkSuspiciousActivity();

    // Event listeners
    $('#timeRange').change(function() {
        loadLocations();
    });

    $('#refreshLocations').click(function() {
        loadLocations();
    });

    $('#sendChallenge, #sendChallengeBtn').click(function() {
        $('#otpChallengeModal').modal('show');
    });

    $('#otpChallengeForm').submit(function(e) {
        e.preventDefault();
        sendOTPChallenge();
    });

    // Quick actions
    $('#viewFullProfile').click(function() {
        window.location.href = '{{ route("admin.drivers.show", $driver->id) }}';
    });

    $('#viewDocuments').click(function() {
        window.location.href = '{{ route("admin.drivers.documents", $driver->id) }}';
    });

    $('#viewPerformance').click(function() {
        window.location.href = '{{ route("admin.reports.driver-performance") }}?driver_id=' + driverId;
    });

    $('#deactivateDriver').click(function() {
        if (confirm('Are you sure you want to deactivate this driver?')) {
            window.location.href = '{{ route("admin.deactivation.create") }}?user_type=driver&user_id=' + driverId;
        }
    });

    // Auto refresh every 30 seconds
    setInterval(function() {
        loadDriverData();
        loadLocations();
        checkSuspiciousActivity();
    }, 30000);

    function initializeMap() {
        map = L.map('driverLocationMap').setView([6.5244, 3.3792], 12);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);
    }

    function loadDriverData() {
        $.get(`{{ url('admin/monitoring/driver') }}/${driverId}/data`, function(data) {
            if (data.success) {
                updateDriverInfo(data.data);
            }
        });
    }

    function updateDriverInfo(data) {
        if (data.last_location) {
            $('#lastLocation').html(`
                ${data.last_location.latitude.toFixed(6)}, ${data.last_location.longitude.toFixed(6)}<br>
                <small class="text-muted">${new Date(data.last_location.recorded_at).toLocaleString()}</small>
            `);
        } else {
            $('#lastLocation').text('No location data');
        }

        $('#lastActivity').text(data.last_activity ? new Date(data.last_activity).toLocaleString() : 'Unknown');
        $('#deviceInfo').text(data.device_info || 'Unknown');
        $('#monitoringStatus').text(data.is_being_monitored ? 'Being Monitored' : 'Active');
    }

    function loadLocations() {
        const minutes = $('#timeRange').val();

        $.get(`{{ url('admin/monitoring/driver') }}/${driverId}/locations`, { minutes: minutes }, function(data) {
            if (data.success) {
                updateLocationMap(data.data);
            }
        });
    }

    function updateLocationMap(locations) {
        // Clear existing markers and route
        locationMarkers.forEach(marker => map.removeLayer(marker));
        if (routePolyline) {
            map.removeLayer(routePolyline);
        }

        locationMarkers = [];
        const coordinates = [];

        locations.forEach(function(location, index) {
            coordinates.push([location.latitude, location.longitude]);

            const isLatest = index === 0;
            const markerColor = isLatest ? 'red' : 'blue';
            const markerIcon = L.divIcon({
                className: 'custom-marker',
                html: `<div style="background-color: ${markerColor}; width: 12px; height: 12px; border-radius: 50%; border: 2px solid white;"></div>`,
                iconSize: [12, 12]
            });

            const marker = L.marker([location.latitude, location.longitude], { icon: markerIcon })
                .addTo(map)
                .bindPopup(`
                    <b>${isLatest ? 'Current' : 'Previous'} Location</b><br>
                    Coordinates: ${location.latitude.toFixed(6)}, ${location.longitude.toFixed(6)}<br>
                    Accuracy: ${location.accuracy || 'N/A'}m<br>
                    Time: ${new Date(location.recorded_at).toLocaleString()}<br>
                    Device: ${location.device_info || 'Unknown'}
                `);

            locationMarkers.push(marker);
        });

        // Draw route line
        if (coordinates.length > 1) {
            routePolyline = L.polyline(coordinates, {
                color: 'blue',
                weight: 3,
                opacity: 0.7
            }).addTo(map);
        }

        // Fit map to show all locations
        if (coordinates.length > 0) {
            const bounds = L.latLngBounds(coordinates);
            map.fitBounds(bounds.pad(0.1));
        }
    }

    function loadActivityLog() {
        $.get(`{{ url('admin/monitoring/driver') }}/${driverId}/activity`, function(data) {
            if (data.success) {
                updateActivityTable(data.data);
            }
        });
    }

    function updateActivityTable(activities) {
        const tbody = $('#activityTable');
        tbody.empty();

        if (activities.length === 0) {
            tbody.append('<tr><td colspan="4" class="text-center">No recent activity</td></tr>');
            return;
        }

        activities.forEach(function(activity) {
            const row = `
                <tr>
                    <td><span class="badge badge-info">${activity.action}</span></td>
                    <td>${activity.description}</td>
                    <td>
                        ${activity.metadata && activity.metadata.last_known_location ?
                            `${activity.metadata.last_known_location.latitude.toFixed(4)}, ${activity.metadata.last_known_location.longitude.toFixed(4)}` :
                            'N/A'
                        }
                    </td>
                    <td>${new Date(activity.created_at).toLocaleString()}</td>
                </tr>
            `;
            tbody.append(row);
        });
    }

    function checkSuspiciousActivity() {
        $.get(`{{ url('admin/monitoring/driver') }}/${driverId}/suspicious`, function(data) {
            if (data.success && data.suspicious) {
                showSuspiciousActivity(data.suspicious);
            } else {
                $('#suspiciousActivitySection').hide();
            }
        });
    }

    function showSuspiciousActivity(suspicious) {
        const content = $('#suspiciousActivityContent');
        content.html(`
            <p><strong>Suspicious patterns detected:</strong></p>
            <ul>
                ${suspicious.map(pattern => `<li>${pattern.replace('_', ' ').toUpperCase()}</li>`).join('')}
            </ul>
            <p class="text-warning">This driver may require additional verification.</p>
        `);
        $('#suspiciousActivitySection').show();
    }

    function sendOTPChallenge() {
        const formData = new FormData(document.getElementById('otpChallengeForm'));

        $.post(`{{ url('admin/monitoring/driver') }}/${driverId}/challenge`, {
            _token: '{{ csrf_token() }}',
            reason: formData.get('reason')
        }, function(data) {
            if (data.success) {
                $('#otpChallengeModal').modal('hide');
                alert('OTP challenge sent successfully');
            } else {
                alert('Failed to send OTP challenge');
            }
        });
    }
});
</script>
@endsection
@endsection
