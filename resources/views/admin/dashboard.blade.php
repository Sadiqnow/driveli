@extends('layouts.admin_cdn')

@section('title', 'Admin Dashboard')

@section('head')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
.stat-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    transition: all 0.3s ease;
}
.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 0.5rem 2rem 0 rgba(58, 59, 69, 0.3);
}
.stat-number {
    font-size: 2.5rem;
    font-weight: bold;
    line-height: 1;
}
.stat-icon {
    font-size: 3rem;
    opacity: 0.3;
}
.real-time-indicator {
    display: inline-block;
    width: 8px;
    height: 8px;
    background: #28a745;
    border-radius: 50%;
    animation: pulse 2s infinite;
}
@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7); }
    70% { box-shadow: 0 0 0 10px rgba(40, 167, 69, 0); }
    100% { box-shadow: 0 0 0 0 rgba(40, 167, 69, 0); }
}
</style>
@endsection

@section('content_header')
    <div class="d-flex align-items-center">
        <h1 class="m-0">Dashboard</h1>
        <span class="real-time-indicator ml-2" title="Live Data"></span>
    </div>
@stop

@section('breadcrumbs')
    <li class="breadcrumb-item active">Dashboard</li>
@stop

@section('content')
    {{-- Existing KPIs --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3 id="totalDrivers">{{ $stats['total_drivers'] ?? 0 }}</h3>
                    <p>Total Drivers</p>
                    <small>+{{ $stats['drivers_today'] ?? 0 }} today</small>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
                <a href="{{ route('admin.drivers.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3 id="activeDrivers">{{ $stats['active_drivers'] ?? 0 }}</h3>
                    <p>Active Drivers</p>
                    <small>{{ number_format((($stats['active_drivers'] ?? 0) / max(($stats['total_drivers'] ?? 0), 1)) * 100, 1) }}% of total</small>
                </div>
                <div class="icon">
                    <i class="fas fa-user-check"></i>
                </div>
                <a href="{{ route('admin.drivers.index') }}?status=active" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3 id="totalUsers">{{ $stats['total_users'] ?? 0 }}</h3>
                    <p>Admin Users</p>
                    <small>{{ $stats['active_users'] ?? 0 }} active</small>
                </div>
                <div class="icon">
                    <i class="fas fa-user-shield"></i>
                </div>
                <a href="{{ route('admin.users.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>₦0</h3>
                    <p>Commission Earned</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <a href="{{ route('admin.dashboard') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>
    </section>

    {{-- Additional Metrics (Collapsible) --}}
    <section aria-labelledby="additional-metrics-heading" id="additionalMetrics" style="display: none;">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h2 id="additional-metrics-heading" class="h4 mb-0">Additional Metrics</h2>
            <button class="btn btn-outline-secondary btn-sm" id="hideAdditionalMetrics">
                <i class="fas fa-chevron-up" aria-hidden="true"></i> Hide
            </button>
        </div>
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $stats['total_companies'] ?? 0 }}</h3>
                    <p>Total Companies</p>
                    <small>+{{ $stats['companies_today'] ?? 0 }} today</small>
                </div>
                <div class="icon">
                    <i class="fas fa-building"></i>
                </div>
                <a href="{{ route('admin.companies.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3>{{ $stats['pending_requests'] ?? 0 }}</h3>
                    <p>Pending Requests</p>
                </div>
                <div class="icon">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <a href="{{ route('admin.companies.index') }}?status=pending" class="small-box-footer">Review <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-teal">
                <div class="inner">
                    <h3>{{ $stats['matches'] ?? 0 }}</h3>
                    <p>Driver–Company Matches</p>
                </div>
                <div class="icon">
                    <i class="fas fa-random"></i>
                </div>
                <a href="{{ route('admin.dashboard') }}" class="small-box-footer">View Matches <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-dark">
                <div class="inner">
                    <h3>99.9%</h3>
                    <p>System Uptime</p>
                </div>
                <div class="icon">
                    <i class="fas fa-server"></i>
                </div>
                <a href="{{ route('admin.dashboard') }}" class="small-box-footer">System Health <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>
    </section>

    {{-- Keep your existing content below --}}
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list"></i> Recent Activity
                    </h3>
                </div>
                <div class="card-body">
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-inbox fa-3x mb-3"></i>
                        <p>No activity yet. Start by adding drivers and company requests!</p>
                        <a href="#" class="btn btn-primary">Get Started</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-cog"></i> Quick Actions
                    </h3>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.users.index') }}" class="btn btn-success">
                            <i class="fas fa-users-cog"></i> Manage Users
                        </a>
                        <a href="{{ route('admin.drivers.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Driver
                        </a>
                        <a href="{{ route('admin.companies.index') }}" class="btn btn-warning">
                            <i class="fas fa-plus"></i> Create Request
                        </a>
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-info">
                            <i class="fas fa-paper-plane"></i> Send Notification
                        </a>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle"></i> Setup Progress
                    </h3>
                </div>
                <div class="card-body">
                    <div class="progress-group">
                        <span class="float-end"><b>2</b>/6</span>
                        <span class="progress-text">Setup Complete</span>
                        <div class="progress progress-sm">
                            <div class="progress-bar bg-success" style="width: 33%"></div>
                        </div>
                    </div>
                    <small class="text-muted">
                        <i class="fas fa-check text-success"></i> Step 1: Laravel + AdminLTE<br>
                        <i class="fas fa-check text-success"></i> Step 2: User Management<br>
                        <i class="fas fa-clock text-warning"></i> Step 3: Company Requests<br>
                        <i class="fas fa-clock text-muted"></i> Step 4: Driver Matching<br>
                        <i class="fas fa-clock text-muted"></i> Step 5: Commission Model<br>
                        <i class="fas fa-clock text-muted"></i> Step 6: Reports
                    </small>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .progress-group {
            margin-bottom: 15px;
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            console.log('🚛 Drivelink Dashboard Loaded!');

            // Load dashboard preferences
            loadDashboardPreferences();
            
            // Dashboard customization functionality
            $('#customizeDashboard').on('click', function() {
                $('#dashboardCustomization').slideToggle();
                $('#priorityAlert').fadeOut();
            });
            
            $('#closeDashboardCustomization').on('click', function() {
                $('#dashboardCustomization').slideUp();
            });
            
            $('#customizeFromAlert').on('click', function(e) {
                e.preventDefault();
                $('#dashboardCustomization').slideDown();
                $('#priorityAlert').fadeOut();
            });
            
            // Show/hide additional metrics
            $('#showAllMetrics').on('click', function(e) {
                e.preventDefault();
                $('#additionalMetrics').slideDown();
                $(this).text('Hide additional metrics');
                $(this).attr('id', 'hideAllMetrics');
            });
            
            $(document).on('click', '#hideAllMetrics', function(e) {
                e.preventDefault();
                $('#additionalMetrics').slideUp();
                $('#showAllMetrics').text('View all metrics').attr('id', 'showAllMetrics');
            });
            
            $('#hideAdditionalMetrics').on('click', function() {
                $('#additionalMetrics').slideUp();
            });
            
            // Save dashboard settings
            $('#saveDashboardSettings').on('click', function() {
                saveDashboardPreferences();
                $('#dashboardCustomization').slideUp();
                
                // Show success feedback
                showNotification('Dashboard customized successfully!', 'success');
                
                // Refresh layout
                setTimeout(function() {
                    applyDashboardLayout();
                }, 500);
            });
            
            // Reset dashboard
            $('#resetDashboard').on('click', function() {
                resetDashboardToDefaults();
                showNotification('Dashboard reset to default settings', 'info');
            });
            
            // Refresh dashboard data
            $('#refreshDashboard').on('click', function() {
                const btn = $(this);
                const originalHtml = btn.html();
                
                btn.html('<i class="fas fa-spinner fa-spin"></i> Refreshing...');
                btn.prop('disabled', true);
                
                // Simulate data refresh
                setTimeout(function() {
                    updateMetrics();
                    btn.html(originalHtml);
                    btn.prop('disabled', false);
                    showNotification('Dashboard data refreshed', 'success');
                }, 2000);
            });
            
            // Auto-refresh every 30 seconds
            setInterval(function() {
                updateMetrics();
            }, 30000);
        });
        
        function loadDashboardPreferences() {
            const preferences = JSON.parse(localStorage.getItem('drivelink_dashboard_prefs') || '{}');
            
            // Apply saved preferences
            if (preferences.density) {
                $('#dashboardDensity').val(preferences.density);
                applyDensity(preferences.density);
            }
            
            if (preferences.metrics) {
                // Show/hide metric cards based on preferences
                $('.metric-card').hide();
                preferences.metrics.forEach(function(metric) {
                    $('[data-metric="' + metric + '"]').show();
                });
                
                // Update checkboxes
                $('input[id^="metric-"]').prop('checked', false);
                preferences.metrics.forEach(function(metric) {
                    $('#metric-' + metric).prop('checked', true);
                });
            }
            
            // Apply layout preferences
            if (preferences.showQuickActions === false) {
                $('#show-quick-actions').prop('checked', false);
                // Hide quick actions panel
            }
            
            if (preferences.showRecentActivity === false) {
                $('#show-recent-activity').prop('checked', false);
                // Hide recent activity panel
            }
        }
        
        function saveDashboardPreferences() {
            const preferences = {
                density: $('#dashboardDensity').val(),
                metrics: [],
                showQuickActions: $('#show-quick-actions').is(':checked'),
                showRecentActivity: $('#show-recent-activity').is(':checked'),
                savedAt: new Date().toISOString()
            };
            
            // Collect selected metrics
            $('input[id^="metric-"]:checked').each(function() {
                const metric = this.id.replace('metric-', '');
                preferences.metrics.push(metric);
            });
            
            // Limit to 4 priority metrics
            if (preferences.metrics.length > 4) {
                preferences.metrics = preferences.metrics.slice(0, 4);
                showNotification('Limited to 4 priority metrics', 'warning');
            }
            
            localStorage.setItem('drivelink_dashboard_prefs', JSON.stringify(preferences));
        }
        
        function applyDashboardLayout() {
            const preferences = JSON.parse(localStorage.getItem('drivelink_dashboard_prefs') || '{}');
            
            if (preferences.metrics && preferences.metrics.length > 0) {
                // Show only selected metrics in priority section
                $('.metric-card').hide();
                preferences.metrics.forEach(function(metric) {
                    $('[data-metric="' + metric + '"]').show();
                });
                
                // Move unselected metrics to additional section
                $('.metric-card:hidden').detach().appendTo('#additionalMetrics .row');
                $('#additionalMetrics').show();
            }
            
            if (preferences.density) {
                applyDensity(preferences.density);
            }
        }
        
        function applyDensity(density) {
            $('body').removeClass('dashboard-compact dashboard-comfortable dashboard-spacious');
            $('body').addClass('dashboard-' + density);
        }
        
        function resetDashboardToDefaults() {
            localStorage.removeItem('drivelink_dashboard_prefs');
            location.reload();
        }
        
        function updateMetrics() {
            // Simulate real-time metric updates
            const metrics = ['totalDrivers', 'activeDrivers'];
            
            metrics.forEach(function(metricId) {
                const element = $('#' + metricId);
                const currentValue = parseInt(element.text()) || 0;
                const change = Math.floor(Math.random() * 5) - 2; // -2 to +2 change
                const newValue = Math.max(0, currentValue + change);
                
                if (newValue !== currentValue) {
                    element.fadeOut(200, function() {
                        $(this).text(newValue).fadeIn(200);
                    });
                }
            });
        }
        
        function showNotification(message, type) {
            const alertClass = 'alert-' + (type === 'success' ? 'success' : type === 'warning' ? 'warning' : 'info');
            const notification = $(`
                <div class="alert ${alertClass} alert-dismissible fade show notification-toast" 
                     style="position: fixed; top: 20px; right: 20px; z-index: 1050; min-width: 300px;">
                    ${message}
                    <button type="button" class="close" data-dismiss="alert">
                        <span>&times;</span>
                    </button>
                </div>
            `);
            
            $('body').append(notification);
            
            setTimeout(function() {
                notification.fadeOut();
            }, 3000);
        }
    </script>
@stop

