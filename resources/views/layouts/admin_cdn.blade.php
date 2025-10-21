<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - {{ config('app.name', 'Drivelink') }}</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css">
    <!-- AdminLTE -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">
    <!-- overlayScrollbars -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/overlayscrollbars/1.13.3/css/OverlayScrollbars.min.css">
    <!-- Mobile-responsive CSS -->
    <link rel="stylesheet" href="{{ asset('css/mobile-admin.css') }}">
    <!-- DriveLink Design System -->
    <link rel="stylesheet" href="{{ asset('css/drivelink-ui.css') }}">
    
    @yield('css')
    
    <style>
        /* Skip Navigation Accessibility */
        .skip-to-main {
            position: absolute;
            top: -40px;
            left: 6px;
            background: #000;
            color: #fff;
            padding: 8px;
            text-decoration: none;
            border-radius: 0 0 4px 4px;
            z-index: 100000;
            transition: top 0.3s;
        }
        .skip-to-main:focus {
            top: 0;
        }
        
        /* Enhanced Touch Targets for Mobile */
        .btn, .form-control, .nav-link {
            min-height: 44px;
            min-width: 44px;
        }
        
        .btn-sm {
            padding: 0.5rem 1rem;
            min-height: 40px;
        }
        
        /* Improved Color Contrast */
        .badge.bg-warning {
            background-color: #856404 !important;
            color: #fff !important;
        }
        
        .badge-warning {
            background-color: #856404 !important;
            color: #fff !important;
        }
        
        .text-muted {
            color: #495057 !important;
        }
        
        /* Enhanced Focus Indicators */
        .btn:focus, .form-control:focus, .nav-link:focus {
            outline: 2px solid #0066cc !important;
            outline-offset: 2px;
            box-shadow: 0 0 0 2px rgba(0, 102, 204, 0.25) !important;
        }
        
        /* Responsive Table Patterns */
        @media (max-width: 768px) {
            .table-responsive-stack {
                border: 0;
            }
            
            .table-responsive-stack thead {
                display: none;
            }
            
            .table-responsive-stack tr {
                display: block;
                margin-bottom: 1rem;
                border: 1px solid #dee2e6;
                border-radius: 0.5rem;
                padding: 1rem;
                background: #fff;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }
            
            .table-responsive-stack td {
                display: block;
                text-align: right;
                border: 0;
                padding: 0.5rem 0;
                border-bottom: 1px solid #eee;
            }
            
            .table-responsive-stack td:last-child {
                border-bottom: 0;
            }
            
            .table-responsive-stack td:before {
                content: attr(data-label) ": ";
                float: left;
                font-weight: bold;
                color: #495057;
            }
            
            /* Mobile Navigation Improvements */
            .navbar-nav .nav-link {
                padding: 0.75rem 1rem;
            }
            
            .main-sidebar .nav-link {
                padding: 0.75rem 1rem;
            }
            
            /* Better mobile form elements */
            .form-control {
                font-size: 16px; /* Prevents zoom on iOS */
            }
        }
        
        /* Enhanced Navigation Styles */
        .nav-sidebar .nav-header {
            font-weight: 600;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            color: #6c757d;
            margin-top: 1rem;
        }
        
        .nav-sidebar .nav-item > .nav-link {
            padding: 0.5rem 1rem;
            transition: all 0.2s ease;
            border-radius: 4px;
            margin: 2px 8px;
        }
        
        .nav-sidebar .nav-item > .nav-link:hover {
            background-color: rgba(0,123,255,0.1);
            color: #007bff;
        }
        
        .nav-sidebar .nav-item > .nav-link.active {
            background-color: #007bff;
            color: white;
            box-shadow: 0 2px 4px rgba(0,123,255,0.2);
        }
        
        .nav-treeview .nav-item > .nav-link {
            padding: 0.4rem 1rem 0.4rem 2.5rem;
            font-size: 0.9rem;
            margin: 1px 8px;
        }
        
        .nav-treeview .nav-item > .nav-link:hover {
            background-color: rgba(0,123,255,0.05);
            color: #007bff;
        }
        
        .nav-treeview .nav-item > .nav-link.active {
            background-color: rgba(0,123,255,0.15);
            color: #007bff;
            font-weight: 500;
        }
        
        .badge {
            font-size: 0.65rem;
            padding: 0.25em 0.5em;
            border-radius: 10px;
        }
        
        .nav-icon {
            width: 1.25rem;
            text-align: center;
        }
        
        /* Breadcrumb improvements */
        .content-header .breadcrumb {
            background: transparent;
            margin-bottom: 0;
            padding: 0;
        }
        
        .content-header .breadcrumb-item.active {
            color: #6c757d;
        }
        
        /* Better focus states for accessibility */
        .nav-link:focus {
            outline: 2px solid #007bff;
            outline-offset: 2px;
        }
        
        .nav-treeview .nav-link:focus {
            outline: 2px solid #007bff;
            outline-offset: -2px;
        }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
    <!-- Skip Navigation for Accessibility -->
    <a class="skip-to-main sr-only sr-only-focusable" href="#main-content" tabindex="1">Skip to main content</a>
    <div class="wrapper">
        <!-- Preloader -->
        <div class="preloader flex-column justify-content-center align-items-center">
            <img class="animation__shake" src="https://via.placeholder.com/60x60/007bff/ffffff?text=DL" alt="DriveLink company logo" height="60" width="60">
        </div>

        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="{{ url('admin/dashboard') }}" class="nav-link">Home</a>
                </li>
            </ul>

            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <!-- User Menu -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#">
                        <i class="far fa-user"></i> Admin
                    </a>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                        <a href="{{ route('admin.dashboard') }}" class="dropdown-item">
                            <i class="fas fa-user mr-2"></i> Profile
                        </a>
                        <div class="dropdown-divider"></div>
                        <form method="POST" action="{{ route('admin.logout') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="dropdown-item border-0 bg-transparent w-100 text-start">
                                <i class="fas fa-sign-out-alt mr-2"></i> Logout
                            </button>
                        </form>
                            <button type="submit" class="dropdown-item" style="border: none; background: none; width: 100%; text-align: left;">
                                <i class="fas fa-sign-out-alt mr-2"></i> Logout
                            </button>
                        </form>
                    </div>
                </li>
                <!-- Fullscreen -->
                <li class="nav-item">
                    <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                        <i class="fas fa-expand-arrows-alt"></i>
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- Brand Logo -->
            <a href="{{ url('admin/dashboard') }}" class="brand-link">
                <img src="https://via.placeholder.com/33x33/007bff/ffffff?text=DL" alt="DriveLink logo" class="brand-image img-circle elevation-3" style="opacity: .8">
                <span class="brand-text font-weight-light"><b>Drive</b>link</span>
            </a>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Sidebar Menu -->
                <nav class="mt-2" role="navigation" aria-label="Admin navigation">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                        <!-- Dashboard -->
                        <li class="nav-item">
                            <a href="{{ url('admin/dashboard') }}" 
                               class="nav-link {{ request()->is('admin/dashboard*') ? 'active' : '' }}"
                               aria-label="Dashboard overview">
                                <i class="nav-icon fas fa-tachometer-alt" aria-hidden="true"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                        
                        <!-- Drivers Section - Simplified -->
                        <li class="nav-header" role="separator">DRIVERS</li>
                        <li class="nav-item">
                            <a href="{{ route('admin.superadmin.drivers.index') }}"
                               class="nav-link {{ request()->is('admin/superadmin/drivers*') && !request()->is('admin/superadmin/drivers/create') ? 'active' : '' }}"
                               aria-label="View and manage all drivers">
                                <i class="nav-icon fas fa-users" aria-hidden="true"></i>
                                <p>All Drivers
                                    @php
                                        $pendingCount = \App\Models\Drivers::where('verification_status', 'pending')->count();
                                    @endphp
                                    @if($pendingCount > 0)
                                        <span class="badge badge-warning right">{{ $pendingCount }}</span>
                                    @endif
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.superadmin.drivers.create') }}"
                               class="nav-link {{ request()->is('admin/superadmin/drivers/create') ? 'active' : '' }}"
                               aria-label="Add new driver">
                                <i class="nav-icon fas fa-user-plus" aria-hidden="true"></i>
                                <p>Add Driver</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.drivers.verification') }}" 
                               class="nav-link {{ request()->is('admin/drivers/verification*') ? 'active' : '' }}"
                               aria-label="Driver verification and OCR processing">
                                <i class="nav-icon fas fa-user-check" aria-hidden="true"></i>
                                <p>Verification Center</p>
                            </a>
                        </li>

                        <!-- Job Management Section - Simplified -->
                        <li class="nav-header" role="separator">JOB MANAGEMENT</li>
                        <li class="nav-item">
                            <a href="{{ url('admin/companies') }}" 
                               class="nav-link {{ request()->is('admin/companies*') || request()->is('admin/requests*') ? 'active' : '' }}"
                               aria-label="Manage companies and job requests">
                                <i class="nav-icon fas fa-building" aria-hidden="true"></i>
                                <p>Companies & Requests</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ url('admin/matching') }}" 
                               class="nav-link {{ request()->is('admin/matching*') ? 'active' : '' }}"
                               aria-label="Match drivers with jobs">
                                <i class="nav-icon fas fa-handshake" aria-hidden="true"></i>
                                <p>Driver Matching</p>
                            </a>
                        </li>

                        <!-- Communications Section - Simplified -->
                        <li class="nav-header" role="separator">COMMUNICATIONS</li>
                        <li class="nav-item">
                            <a href="{{ url('admin/notifications') }}" 
                               class="nav-link {{ request()->is('admin/notifications*') ? 'active' : '' }}"
                               aria-label="Send and manage notifications">
                                <i class="nav-icon fas fa-comments" aria-hidden="true"></i>
                                <p>Messages & Notifications</p>
                            </a>
                        </li>

                        <!-- Reports Section - Simplified -->
                        <li class="nav-header" role="separator">REPORTS</li>
                        <li class="nav-item">
                            <a href="{{ url('admin/reports/dashboard') }}" 
                               class="nav-link {{ request()->is('admin/reports*') || request()->is('admin/commissions*') ? 'active' : '' }}"
                               aria-label="View analytics and reports">
                                <i class="nav-icon fas fa-chart-line" aria-hidden="true"></i>
                                <p>Analytics & Reports</p>
                            </a>
                        </li>

                        @if(auth('admin')->user()->hasRole('Super Admin'))
                        <li class="nav-header">SUPER ADMIN</li>
                        <li class="nav-item">
                            <a href="{{ route('admin.superadmin.dashboard') }}" class="nav-link {{ request()->is('admin/superadmin*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-user-shield"></i>
                                <p>SuperAdmin Dashboard</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.superadmin.users') }}" class="nav-link {{ request()->is('admin/superadmin/users*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-users-cog"></i>
                                <p>Manage Users</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="#" class="nav-link" onclick="alert('Role & Permission management system is being implemented. Currently using legacy role system.')">
                                <i class="nav-icon fas fa-user-tag"></i>
                                <p>Roles & Permissions</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.superadmin.audit-logs') }}" class="nav-link {{ request()->is('admin/superadmin/audit-logs*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-history"></i>
                                <p>Audit Logs</p>
                            </a>
                        </li>
                        @endif

                        <li class="nav-header">SETTINGS</li>
                        <li class="nav-item">
                            <a href="{{ url('admin/settings') }}" class="nav-link {{ request()->is('admin/settings*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-cog"></i>
                                <p>System Settings</p>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </aside>

        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <!-- Content Header -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">@yield('content_header')</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                @yield('breadcrumbs')
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main content -->
            <section class="content" id="main-content">
                <div class="container-fluid">
                    @include('layouts.alerts')
                    @yield('content')
                </div>
            </section>
        </div>

        <!-- Footer -->
        <footer class="main-footer">
            <strong>Copyright &copy; {{ date('Y') }} <a href="#">Drivelink</a>.</strong>
            All rights reserved.
            <div class="float-right d-none d-sm-inline-block">
                <b>Version</b> 1.0.0
            </div>
        </footer>
    </div>

    <!-- jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <!-- Bootstrap 5 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <!-- overlayScrollbars -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/overlayscrollbars/1.13.3/js/jquery.overlayScrollbars.min.js"></script>
    <!-- AdminLTE App -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @yield('js')
    
    <!-- Global UX Enhancements -->
    <script>
    $(document).ready(function() {
        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
        
        // Add loading state to all form submissions
        $('form').on('submit', function() {
            const submitBtn = $(this).find('button[type="submit"]');
            if (submitBtn.length && !submitBtn.hasClass('no-loading')) {
                const originalText = submitBtn.html();
                submitBtn.prop('disabled', true);
                submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Processing...');
                
                // Restore button after 10 seconds as fallback
                setTimeout(function() {
                    submitBtn.prop('disabled', false);
                    submitBtn.html(originalText);
                }, 10000);
            }
        });
        
        // Add confirmation to delete actions
        $('form').on('submit', function(e) {
            if ($(this).attr('action').includes('destroy') || $(this).attr('action').includes('delete')) {
                if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                    e.preventDefault();
                }
            }
        });
        
        // Initialize tooltips for better UX
        $('[data-toggle="tooltip"]').tooltip();
        
        // Add smooth scroll to anchor links
        $('a[href^="#"]').on('click', function(event) {
            const target = $($(this).attr('href'));
            if (target.length) {
                event.preventDefault();
                $('html, body').animate({
                    scrollTop: target.offset().top - 100
                }, 500);
            }
        });
    });
    </script>
</body>
</html>