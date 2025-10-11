<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - {{ config('app.name', 'DriveLink') }} Company Portal</title>

    <!-- Google Fonts: Inter & Roboto (Company-focused typography) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css">

    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">

    <!-- overlayScrollbars CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/overlayscrollbars/1.13.3/css/OverlayScrollbars.min.css">

    <!-- DriveLink Design System -->
    <link rel="stylesheet" href="{{ asset('css/drivelink-ui.css') }}">

    <!-- Company Portal Variables (Custom design system) -->
    <link rel="stylesheet" href="{{ asset('css/company-variables.css') }}">

    <style>
        :root {
            --company-primary: #2563eb;
            --company-secondary: #1e40af;
            --company-accent: #3b82f6;
            --company-success: #059669;
            --company-warning: #d97706;
            --company-danger: #dc2626;
            --company-info: #0891b2;
            --company-light: #f8fafc;
            --company-dark: #1e293b;
        }

        .company-sidebar {
            background: linear-gradient(135deg, var(--company-primary) 0%, var(--company-secondary) 100%);
            min-height: 100vh;
            box-shadow: 4px 0 15px rgba(0,0,0,0.1);
        }

        .company-brand {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .company-brand h4 {
            color: white;
            margin: 0;
            font-weight: 600;
        }

        .company-nav {
            padding: 1rem 0;
        }

        .company-nav-item {
            margin: 0.25rem 1rem;
        }

        .company-nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .company-nav-link:hover,
        .company-nav-link.active {
            background-color: rgba(255,255,255,0.1);
            color: white;
            transform: translateX(4px);
        }

        .company-nav-link i {
            width: 20px;
            margin-right: 0.75rem;
        }

        .company-main {
            background-color: white;
            min-height: 100vh;
        }

        .company-header {
            background: white;
            border-bottom: 1px solid #e2e8f0;
            padding: 1rem 1.5rem;
        }

        .company-content {
            padding: 1.5rem;
        }

        .company-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border: 1px solid #e2e8f0;
            margin-bottom: 1.5rem;
        }

        .company-card-header {
            padding: 1.25rem;
            border-bottom: 1px solid #e2e8f0;
            background: #f8fafc;
            border-radius: 12px 12px 0 0;
        }

        .company-card-body {
            padding: 1.25rem;
        }

        .btn-company-primary {
            background: var(--company-primary);
            border-color: var(--company-primary);
            color: white;
        }

        .btn-company-primary:hover {
            background: var(--company-secondary);
            border-color: var(--company-secondary);
            color: white;
        }

        .badge-company-status {
            padding: 0.5rem 0.75rem;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.75rem;
        }

        .badge-active {
            background-color: #dcfce7;
            color: #166534;
        }

        .badge-pending {
            background-color: #fef3c7;
            color: #92400e;
        }

        .badge-completed {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .badge-cancelled {
            background-color: #fee2e2;
            color: #991b1b;
        }

        /* Mobile responsive */
        @media (max-width: 768px) {
            .company-sidebar {
                position: fixed;
                top: 0;
                left: -250px;
                width: 250px;
                z-index: 1050;
                transition: left 0.3s ease;
            }

            .company-sidebar.show {
                left: 0;
            }

            .company-main {
                margin-left: 0;
            }

            .mobile-menu-toggle {
                display: block !important;
            }

            .company-header {
                padding-left: 4rem;
            }
        }

        .mobile-menu-toggle {
            display: none;
            position: fixed;
            top: 1rem;
            left: 1rem;
            z-index: 1060;
            background: var(--company-primary);
            color: white;
            border: none;
            padding: 0.5rem;
            border-radius: 6px;
        }

        /* Stats cards */
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .stats-card.success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }

        .stats-card.warning {
            background: linear-gradient(135deg, #fcb045 0%, #fd1d1d 100%);
        }

        .stats-card.info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .stats-number {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .stats-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }
    </style>

    @yield('css')
</head>
<body>
    <div class="d-flex">
        <!-- Mobile menu toggle -->
        <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Toggle navigation menu">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Sidebar -->
        <div class="company-sidebar" id="companySidebar">
            <div class="company-brand">
                <h4><i class="fas fa-building me-2"></i>DriveLink</h4>
                <small class="text-white-50">Company Portal</small>
            </div>

            <nav class="company-nav">
                <div class="company-nav-item">
                    <a href="{{ route('company.dashboard') }}"
                       class="company-nav-link {{ request()->routeIs('company.dashboard') ? 'active' : '' }}"
                       aria-label="Dashboard">
                        <i class="fas fa-tachometer-alt" aria-hidden="true"></i>
                        Dashboard
                    </a>
                </div>

                <div class="company-nav-item">
                    <a href="{{ route('company.requests.index') }}"
                       class="company-nav-link {{ request()->routeIs('company.requests.*') ? 'active' : '' }}"
                       aria-label="My Requests">
                        <i class="fas fa-clipboard-list" aria-hidden="true"></i>
                        My Requests
                    </a>
                </div>

                <div class="company-nav-item">
                    <a href="{{ route('company.matching.index') }}"
                       class="company-nav-link {{ request()->routeIs('company.matching.*') ? 'active' : '' }}"
                       aria-label="Driver Matching">
                        <i class="fas fa-handshake" aria-hidden="true"></i>
                        Driver Matching
                    </a>
                </div>

                <div class="company-nav-item">
                    <a href="{{ route('company.jobs.index') }}"
                       class="company-nav-link {{ request()->routeIs('company.jobs.*') ? 'active' : '' }}"
                       aria-label="Job Tracking">
                        <i class="fas fa-tasks" aria-hidden="true"></i>
                        Job Tracking
                    </a>
                </div>

                <div class="company-nav-item">
                    <a href="{{ route('company.reports.index') }}"
                       class="company-nav-link {{ request()->routeIs('company.reports.*') ? 'active' : '' }}"
                       aria-label="Reports">
                        <i class="fas fa-chart-bar" aria-hidden="true"></i>
                        Reports
                    </a>
                </div>

                <div class="company-nav-item">
                    <a href="{{ route('company.profile.index') }}"
                       class="company-nav-link {{ request()->routeIs('company.profile.*') ? 'active' : '' }}"
                       aria-label="Company Profile">
                        <i class="fas fa-building" aria-hidden="true"></i>
                        Profile
                    </a>
                </div>

                <hr class="my-3" style="border-color: rgba(255,255,255,0.1);">

                <div class="company-nav-item">
                    <form action="{{ route('company.logout') }}" method="POST" class="d-inline w-100">
                        @csrf
                        <button type="submit" class="company-nav-link border-0 bg-transparent w-100 text-start"
                                aria-label="Logout">
                            <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
                            Logout
                        </button>
                    </form>
                </div>
            </nav>
        </div>

        <!-- Main content -->
        <div class="company-main flex-grow-1">
            <!-- Header -->
            <header class="company-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h4 mb-0">@yield('page-title', 'Company Portal')</h1>
                        @hasSection('breadcrumbs')
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0 mt-1">
                                    @yield('breadcrumbs')
                                </ol>
                            </nav>
                        @endif
                    </div>

                    <div class="d-flex align-items-center">
                        <!-- Notifications -->
                        <div class="dropdown me-3">
                            <button class="btn btn-outline-secondary position-relative"
                                    type="button"
                                    id="notificationsDropdown"
                                    data-bs-toggle="dropdown"
                                    aria-label="Notifications">
                                <i class="fas fa-bell" aria-hidden="true"></i>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    {{ auth('company')->user()->unread_notifications ?? 0 }}
                                </span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end" style="width: 300px;">
                                <h6 class="dropdown-header">Notifications</h6>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#">
                                    <small class="text-muted">2 hours ago</small><br>
                                    New driver match available
                                </a>
                                <a class="dropdown-item" href="#">
                                    <small class="text-muted">1 day ago</small><br>
                                    Request approved by admin
                                </a>
                            </div>
                        </div>

                        <!-- User menu -->
                        @auth('company')
                        <div class="dropdown">
                            <button class="btn btn-outline-primary d-flex align-items-center"
                                    type="button"
                                    id="userDropdown"
                                    data-bs-toggle="dropdown"
                                    aria-label="Company menu">
                                <i class="fas fa-building me-2" aria-hidden="true"></i>
                                {{ auth('company')->user()->name }}
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('company.profile.index') }}">
                                    <i class="fas fa-building me-2" aria-hidden="true"></i>Company Profile
                                </a></li>
                                <li><a class="dropdown-item" href="{{ route('company.profile.settings') }}">
                                    <i class="fas fa-cog me-2" aria-hidden="true"></i>Settings
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('company.logout') }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="fas fa-sign-out-alt me-2" aria-hidden="true"></i>Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                        @endauth
                    </div>
                </div>
            </header>

            <!-- Content -->
            <main class="company-content">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2" aria-hidden="true"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2" aria-hidden="true"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <h6 class="alert-heading">Please fix the following errors:</h6>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Mobile menu toggle
            const mobileToggle = document.getElementById('mobileMenuToggle');
            const sidebar = document.getElementById('companySidebar');

            if (mobileToggle && sidebar) {
                mobileToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('show');
                });

                // Close sidebar when clicking outside on mobile
                document.addEventListener('click', function(e) {
                    if (window.innerWidth <= 768) {
                        if (!sidebar.contains(e.target) && !mobileToggle.contains(e.target)) {
                            sidebar.classList.remove('show');
                        }
                    }
                });
            }

            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
                alerts.forEach(function(alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);
        });
    </script>

    @yield('js')
</body>
</html>
