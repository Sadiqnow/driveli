@extends('layouts.admin_cdn')

@section('title', 'Driver Matching Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1>Driver Matching Dashboard</h1>
            <p class="text-muted">Monitor and manage driver-company matching operations</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h4 class="mb-0" id="totalMatches">0</h4>
                            <p class="mb-0">Total Matches</p>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="fas fa-handshake fa-2x"></i>
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
                            <h4 class="mb-0" id="successfulMatches">0</h4>
                            <p class="mb-0">Successful Matches</p>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle fa-2x"></i>
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
                            <h4 class="mb-0" id="avgScore">0%</h4>
                            <p class="mb-0">Avg Match Score</p>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="fas fa-chart-line fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h4 class="mb-0" id="pendingRequests">0</h4>
                            <p class="mb-0">Pending Requests</p>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <button type="button" class="btn btn-primary btn-block" data-bs-toggle="modal" data-bs-target="#newMatchModal">
                                <i class="fas fa-plus"></i> New Match Request
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn btn-info btn-block" onclick="refreshStats()">
                                <i class="fas fa-sync"></i> Refresh Stats
                            </button>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('admin.matching.index') }}" class="btn btn-secondary btn-block">
                                <i class="fas fa-list"></i> View All Matches
                            </a>
                        </div>
                        <div class="col-md-3">
                            <button type="button" class="btn btn-success btn-block" onclick="exportMatches()">
                                <i class="fas fa-download"></i> Export Report
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Matches -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Recent Matches</h5>
                </div>
                <div class="card-body">
                    <div id="recentMatches">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Loading recent matches...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Performing Drivers -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Top Performing Drivers</h6>
                </div>
                <div class="card-body">
                    <div id="topDrivers">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Loading top drivers...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Matching Criteria -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0">Matching Criteria</h6>
                </div>
                <div class="card-body">
                    <div id="matchingCriteria">
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Loading criteria...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Match Details Modal -->
    <div class="modal fade" id="matchDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Match Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="matchDetailsContent">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <!-- New Match Request Modal -->
    <div class="modal fade" id="newMatchModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">New Match Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="newMatchForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="company_request_id" class="form-label">Company Request</label>
                                    <select name="company_request_id" id="company_request_id" class="form-select" required>
                                        <option value="">Select Company Request</option>
                                        <!-- Options will be loaded dynamically -->
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="location_lat" class="form-label">Location (Latitude)</label>
                                    <input type="number" step="any" name="location_lat" id="location_lat" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="location_lng" class="form-label">Location (Longitude)</label>
                                    <input type="number" step="any" name="location_lng" id="location_lng" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="radius" class="form-label">Search Radius (km)</label>
                                    <input type="number" name="radius" id="radius" value="50" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Additional Criteria</label>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="criteria[]" value="license_class" id="license_class">
                                        <label class="form-check-label" for="license_class">
                                            License Class
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="criteria[]" value="experience" id="experience">
                                        <label class="form-check-label" for="experience">
                                            Experience
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="criteria[]" value="rating" id="rating">
                                        <label class="form-check-label" for="rating">
                                            Rating
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="submitMatchRequest()">Find Matches</button>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
let matchStats = {};
let currentMatches = [];

// Load dashboard data on page load
document.addEventListener('DOMContentLoaded', function() {
    loadDashboardStats();
    loadRecentMatches();
    loadTopDrivers();
    loadMatchingCriteria();
});

// Load dashboard statistics
function loadDashboardStats() {
    fetch('{{ route("admin.matching.stats") }}')
        .then(response => response.json())
        .then(data => {
            document.getElementById('totalMatches').textContent = data.total_matches || 0;
            document.getElementById('successfulMatches').textContent = data.successful_matches || 0;
            document.getElementById('avgScore').textContent = Math.round(data.average_score || 0) + '%';
            document.getElementById('pendingRequests').textContent = data.pending_requests || 0;
            matchStats = data;
        })
        .catch(error => {
            console.error('Error loading stats:', error);
        });
}

// Load recent matches
function loadRecentMatches() {
    fetch('{{ route("admin.matching.recent") }}')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('recentMatches');
            if (data.matches && data.matches.length > 0) {
                let html = '<div class="table-responsive"><table class="table table-sm"><thead><tr><th>Company</th><th>Driver</th><th>Score</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead><tbody>';

                data.matches.forEach(match => {
                    html += `
                        <tr>
                            <td><strong>${match.company_name || 'N/A'}</strong></td>
                            <td>${match.driver_name || 'N/A'}</td>
                            <td><span class="badge badge-${getScoreBadgeClass(match.final_score)}">${match.final_score}%</span></td>
                            <td><span class="badge badge-${match.matched ? 'success' : 'warning'}">${match.matched ? 'Matched' : 'Not Matched'}</span></td>
                            <td><small>${new Date(match.created_at).toLocaleDateString()}</small></td>
                            <td>
                                <button class="btn btn-sm btn-info" onclick="viewMatchDetails(${match.id})">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                });

                html += '</tbody></table></div>';
                container.innerHTML = html;
            } else {
                container.innerHTML = '<div class="text-center py-4"><i class="fas fa-handshake fa-3x text-muted mb-3"></i><h5>No recent matches</h5><p class="text-muted">Matching activity will appear here.</p></div>';
            }
        })
        .catch(error => {
            console.error('Error loading matches:', error);
            document.getElementById('recentMatches').innerHTML = '<div class="alert alert-danger">Error loading recent matches</div>';
        });
}

// Load top performing drivers
function loadTopDrivers() {
    fetch('{{ route("admin.matching.top-drivers") }}')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('topDrivers');
            if (data.drivers && data.drivers.length > 0) {
                let html = '<div class="list-group list-group-flush">';

                data.drivers.slice(0, 5).forEach((driver, index) => {
                    html += `
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>${driver.name}</strong>
                                <br><small class="text-muted">${driver.total_matches} matches</small>
                            </div>
                            <div class="text-end">
                                <span class="badge badge-success">${Math.round(driver.avg_score)}%</span>
                                <br><small class="text-muted">#${index + 1}</small>
                            </div>
                        </div>
                    `;
                });

                html += '</div>';
                container.innerHTML = html;
            } else {
                container.innerHTML = '<div class="text-center py-4"><i class="fas fa-users fa-3x text-muted mb-3"></i><h5>No driver data</h5><p class="text-muted">Top drivers will appear here.</p></div>';
            }
        })
        .catch(error => {
            console.error('Error loading top drivers:', error);
            document.getElementById('topDrivers').innerHTML = '<div class="alert alert-danger">Error loading top drivers</div>';
        });
}

// Load matching criteria
function loadMatchingCriteria() {
    fetch('{{ route("admin.matching.criteria") }}')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('matchingCriteria');
            if (data.criteria && data.criteria.length > 0) {
                let html = '<div class="list-group list-group-flush">';

                data.criteria.forEach(criterion => {
                    html += `
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>${criterion.name}</strong>
                                <br><small class="text-muted">${criterion.description}</small>
                            </div>
                            <div class="text-end">
                                <span class="badge badge-primary">${criterion.weight}</span>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" ${criterion.is_active ? 'checked' : ''} disabled>
                                </div>
                            </div>
                        </div>
                    `;
                });

                html += '</div>';
                container.innerHTML = html;
            } else {
                container.innerHTML = '<div class="text-center py-4"><i class="fas fa-cogs fa-3x text-muted mb-3"></i><h5>No criteria configured</h5><p class="text-muted">Matching criteria will appear here.</p></div>';
            }
        })
        .catch(error => {
            console.error('Error loading criteria:', error);
            document.getElementById('matchingCriteria').innerHTML = '<div class="alert alert-danger">Error loading matching criteria</div>';
        });
}

// View match details
function viewMatchDetails(matchId) {
    fetch(`{{ url('/admin/matching') }}/${matchId}`)
        .then(response => response.json())
        .then(data => {
            const modal = new bootstrap.Modal(document.getElementById('matchDetailsModal'));
            const content = document.getElementById('matchDetailsContent');

            let html = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Match Information</h6>
                        <p><strong>Company:</strong> ${data.company_name || 'N/A'}</p>
                        <p><strong>Driver:</strong> ${data.driver_name || 'N/A'}</p>
                        <p><strong>Final Score:</strong> <span class="badge badge-${getScoreBadgeClass(data.final_score)}">${data.final_score}%</span></p>
                        <p><strong>Matched:</strong> <span class="badge badge-${data.matched ? 'success' : 'warning'}">${data.matched ? 'Yes' : 'No'}</span></p>
                    </div>
                    <div class="col-md-6">
                        <h6>Score Breakdown</h6>
            `;

            if (data.criteria_scores) {
                Object.entries(data.criteria_scores).forEach(([key, score]) => {
                    html += `<p><strong>${key.charAt(0).toUpperCase() + key.slice(1)}:</strong> ${score}%</p>`;
                });
            }

            html += `
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <h6>Match Details</h6>
                        <pre class="bg-light p-2 small">${JSON.stringify(data.match_details || {}, null, 2)}</pre>
                    </div>
                </div>
            `;

            content.innerHTML = html;
            modal.show();
        })
        .catch(error => {
            console.error('Error loading match details:', error);
            alert('Error loading match details');
        });
}

// Submit new match request
function submitMatchRequest() {
    const form = document.getElementById('newMatchForm');
    const formData = new FormData(form);

    const criteria = {};
    formData.getAll('criteria[]').forEach(criterion => {
        criteria[criterion] = true;
    });

    const requestData = {
        company_request_id: formData.get('company_request_id'),
        criteria: criteria,
        location: {
            lat: parseFloat(formData.get('location_lat')) || null,
            lng: parseFloat(formData.get('location_lng')) || null
        },
        radius: parseInt(formData.get('radius')) || 50
    };

    fetch('{{ route("admin.matching.find") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(requestData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`Found ${data.drivers.length} potential matches!`);
            bootstrap.Modal.getInstance(document.getElementById('newMatchModal')).hide();
            loadDashboardStats();
            loadRecentMatches();
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error submitting match request:', error);
        alert('Error submitting match request');
    });
}

// Refresh statistics
function refreshStats() {
    loadDashboardStats();
    loadRecentMatches();
    loadTopDrivers();
}

// Export matches
function exportMatches() {
    window.location.href = '{{ route("admin.matching.export") }}';
}

// Helper function for score badge classes
function getScoreBadgeClass(score) {
    if (score >= 80) return 'success';
    if (score >= 60) return 'warning';
    return 'danger';
}
</script>
@endsection
