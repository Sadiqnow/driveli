<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - {{ config('app.name', 'DriveLink') }} Driver Portal</title>

    <!-- Google Fonts: Inter & Roboto (Driver-focused typography differentiation) -->
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

    <!-- Mobile-responsive CSS -->
    <link rel="stylesheet" href="{{ asset('css/mobile-admin.css') }}">

    <!-- Driver Portal Variables (Custom design system) -->
    <link rel="stylesheet" href="{{ asset('css/driver-variables.css') }}">
    
    <style>
        
        .driver-sidebar {
            background: linear-gradient(135deg, var(--driver-primary) 0%, #1d4ed8 100%);
            min-height: 100vh;
            box-shadow: 4px 0 15px rgba(0,0,0,0.1);
        }
        
        .driver-brand {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .driver-brand h4 {
            color: white;
            margin: 0;
            font-weight: 600;
        }
        
        .driver-nav {
            padding: 1rem 0;
        }
        
        .driver-nav-item {
            margin: 0.25rem 1rem;
        }
        
        .driver-nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        
        .driver-nav-link:hover,
        .driver-nav-link.active {
            background-color: rgba(255,255,255,0.1);
            color: white;
            transform: translateX(4px);
        }
        
        .driver-nav-link i {
            width: 20px;
            margin-right: 0.75rem;
        }
        
        .driver-main {
            background-color: white;
            min-height: 100vh;
        }
        
        .driver-header {
            background: white;
            border-bottom: 1px solid #e2e8f0;
            padding: 1rem 1.5rem;
        }
        
        .driver-content {
            padding: 1.5rem;
        }
        
        .driver-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border: 1px solid #e2e8f0;
            margin-bottom: 1.5rem;
        }
        
        .driver-card-header {
            padding: 1.25rem;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .driver-card-body {
            padding: 1.25rem;
        }
        
        .btn-driver-primary {
            background: var(--driver-primary);
            border-color: var(--driver-primary);
            color: white;
        }
        
        .btn-driver-primary:hover {
            background: #1d4ed8;
            border-color: #1d4ed8;
            color: white;
        }
        
        .badge-driver-status {
            padding: 0.5rem 0.75rem;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.75rem;
        }
        
        .badge-verified {
            background-color: #dcfce7;
            color: #166534;
        }
        
        .badge-pending {
            background-color: #fef3c7;
            color: #92400e;
        }
        
        .badge-rejected {
            background-color: #fee2e2;
            color: #991b1b;
        }
        
        /* Mobile responsive */
        @media (max-width: 768px) {
            .driver-sidebar {
                position: fixed;
                top: 0;
                left: -250px;
                width: 250px;
                z-index: 1050;
                transition: left 0.3s ease;
            }
            
            .driver-sidebar.show {
                left: 0;
            }
            
            .driver-main {
                margin-left: 0;
            }
            
            .mobile-menu-toggle {
                display: block !important;
            }
            
            .driver-header {
                padding-left: 4rem;
            }
        }
        
        .mobile-menu-toggle {
            display: none;
            position: fixed;
            top: 1rem;
            left: 1rem;
            z-index: 1060;
            background: var(--driver-primary);
            color: white;
            border: none;
            padding: 0.5rem;
            border-radius: 6px;
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
        <div class="driver-sidebar" id="driverSidebar">
            <div class="driver-brand">
                <h4><i class="fas fa-truck me-2"></i>DriveLink</h4>
                <small class="text-white-50">Driver Portal</small>
            </div>
            
            <nav class="driver-nav">
                <div class="driver-nav-item">
                    <a href="{{ route('driver.dashboard') }}" 
                       class="driver-nav-link {{ request()->routeIs('driver.dashboard') ? 'active' : '' }}"
                       aria-label="Dashboard">
                        <i class="fas fa-tachometer-alt" aria-hidden="true"></i>
                        Dashboard
                    </a>
                </div>
                
                <div class="driver-nav-item">
                    <a href="{{ route('driver.jobs.index') }}" 
                       class="driver-nav-link {{ request()->routeIs('driver.jobs.*') ? 'active' : '' }}"
                       aria-label="My Jobs">
                        <i class="fas fa-briefcase" aria-hidden="true"></i>
                        My Jobs
                    </a>
                </div>
                
                <div class="driver-nav-item">
                    <a href="{{ route('driver.jobs.available') }}" 
                       class="driver-nav-link {{ request()->routeIs('driver.jobs.available') ? 'active' : '' }}"
                       aria-label="Available Jobs">
                        <i class="fas fa-search" aria-hidden="true"></i>
                        Available Jobs
                    </a>
                </div>
                
                <div class="driver-nav-item">
                    <a href="{{ route('driver.profile.show') }}" 
                       class="driver-nav-link {{ request()->routeIs('driver.profile.*') ? 'active' : '' }}"
                       aria-label="My Profile">
                        <i class="fas fa-user" aria-hidden="true"></i>
                        My Profile
                    </a>
                </div>
                
                <div class="driver-nav-item">
                    <a href="{{ route('driver.kyc.index') }}" 
                       class="driver-nav-link {{ request()->routeIs('driver.kyc.*') ? 'active' : '' }}"
                       aria-label="KYC Verification">
                        <i class="fas fa-id-card" aria-hidden="true"></i>
                        KYC Verification
                        @php
                            $kycStatus = auth('driver')->check() ? auth('driver')->user()->kyc_status : 'pending';
                        @endphp
                        @if($kycStatus === 'pending' || $kycStatus === 'in_progress')
                            <span class="badge bg-warning ms-2" style="font-size: 0.6rem;">Incomplete</span>
                        @elseif($kycStatus === 'completed')
                            <span class="badge bg-success ms-2" style="font-size: 0.6rem;">Complete</span>
                        @elseif($kycStatus === 'rejected')
                            <span class="badge bg-danger ms-2" style="font-size: 0.6rem;">Rejected</span>
                        @endif
                    </a>
                </div>
                
                <hr class="my-3" style="border-color: rgba(255,255,255,0.1);">
                
                <div class="driver-nav-item">
                    <form action="{{ route('driver.logout') }}" method="POST" class="d-inline w-100">
                        @csrf
                        <button type="submit" class="driver-nav-link border-0 bg-transparent w-100 text-start"
                                aria-label="Logout">
                            <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
                            Logout
                        </button>
                    </form>
                </div>
            </nav>
        </div>

        <!-- Main content -->
        <div class="driver-main flex-grow-1">
            <!-- Header -->
            <header class="driver-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h4 mb-0">@yield('page-title', 'Driver Portal')</h1>
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
                                    2
                                </span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end" style="width: 300px;">
                                <h6 class="dropdown-header">Notifications</h6>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#">
                                    <small class="text-muted">2 hours ago</small><br>
                                    New job match available
                                </a>
                                <a class="dropdown-item" href="#">
                                    <small class="text-muted">1 day ago</small><br>
                                    Profile verification required
                                </a>
                            </div>
                        </div>
                        
                        <!-- User menu -->
                        @auth('driver')
                        <div class="dropdown">
                            <button class="btn btn-outline-primary d-flex align-items-center" 
                                    type="button" 
                                    id="userDropdown" 
                                    data-bs-toggle="dropdown"
                                    aria-label="User menu">
                                @if(auth('driver')->user()->profile_photo)
                                    <img src="{{ asset('storage/' . auth('driver')->user()->profile_photo) }}" 
                                         alt="Profile" class="rounded-circle me-2" width="24" height="24">
                                @else
                                    <i class="fas fa-user-circle me-2" aria-hidden="true"></i>
                                @endif
                                {{ auth('driver')->user()->first_name }}
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('driver.profile.show') }}">
                                    <i class="fas fa-user me-2" aria-hidden="true"></i>Profile
                                </a></li>
                                <li><a class="dropdown-item" href="{{ route('driver.profile.change-password') }}">
                                    <i class="fas fa-lock me-2" aria-hidden="true"></i>Change Password
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('driver.logout') }}" method="POST" class="d-inline">
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
            <main class="driver-content">
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
            const sidebar = document.getElementById('driverSidebar');
            
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