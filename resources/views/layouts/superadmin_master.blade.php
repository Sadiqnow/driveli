<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - @yield('title', 'Super Admin Panel')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">

    @yield('head')

    <style>
        /* Super Admin Theme Colors */
        :root {
            --superadmin-primary: #8B5CF6;
            --superadmin-secondary: #A855F7;
            --superadmin-accent: #C084FC;
            --superadmin-dark: #581C87;
            --superadmin-light: #F3E8FF;
        }

        .sidebar-dark-primary .nav-sidebar>.nav-item>.nav-link.active,
        .sidebar-dark-primary .nav-sidebar>.nav-item>.nav-link:hover {
            background-color: rgba(139, 92, 246, 0.2);
            color: #E9D5FF;
            border-left: 3px solid var(--superadmin-primary);
        }

        .content-wrapper {
            background-color: #f8fafc;
        }

        .main-sidebar {
            background: linear-gradient(180deg, #581C87 0%, #7C3AED 50%, #8B5CF6 100%);
            box-shadow: 2px 0 10px rgba(139, 92, 246, 0.3);
        }

        .brand-link {
            background: linear-gradient(90deg, #581C87 0%, #7C3AED 100%);
            color: white !important;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .brand-link:hover {
            background: linear-gradient(90deg, #7C3AED 0%, #8B5CF6 100%) !important;
        }

        .btn-superadmin {
            background: linear-gradient(135deg, var(--superadmin-primary) 0%, var(--superadmin-secondary) 100%);
            border: none;
            color: white;
        }

        .btn-superadmin:hover {
            background: linear-gradient(135deg, var(--superadmin-secondary) 0%, var(--superadmin-accent) 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(139, 92, 246, 0.3);
        }

        .card-superadmin {
            border-left: 4px solid var(--superadmin-primary);
            box-shadow: 0 2px 10px rgba(139, 92, 246, 0.1);
        }

        .card-superadmin .card-header {
            background: linear-gradient(90deg, var(--superadmin-light) 0%, rgba(139, 92, 246, 0.1) 100%);
            border-bottom: 1px solid rgba(139, 92, 246, 0.2);
        }

        .badge-superadmin {
            background: linear-gradient(135deg, var(--superadmin-primary) 0%, var(--superadmin-secondary) 100%);
            color: white;
        }

        .text-superadmin {
            color: var(--superadmin-primary) !important;
        }

        .alert-superadmin {
            border-left: 4px solid var(--superadmin-primary);
            background: linear-gradient(90deg, var(--superadmin-light) 0%, rgba(139, 92, 246, 0.05) 100%);
            border-color: rgba(139, 92, 246, 0.2);
        }

        .superadmin-glow {
            box-shadow: 0 0 20px rgba(139, 92, 246, 0.3);
        }

        /* Enhanced Form Controls */
        .form-control:focus {
            border-color: var(--superadmin-primary);
            box-shadow: 0 0 0 0.2rem rgba(139, 92, 246, 0.25);
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--superadmin-primary);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--superadmin-secondary);
        }

        /* Loading animation */
        .loading {
            position: relative;
            color: transparent !important;
        }

        .loading::after {
            content: '';
            position: absolute;
            width: 16px;
            height: 16px;
            top: 50%;
            left: 50%;
            margin-left: -8px;
            margin-top: -8px;
            border: 2px solid var(--superadmin-primary);
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Super Admin specific styles */
        .superadmin-banner {
            background: linear-gradient(135deg, var(--superadmin-primary) 0%, var(--superadmin-secondary) 100%);
            color: white;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .superadmin-banner h4 {
            margin: 0;
            font-weight: 600;
        }

        .superadmin-banner p {
            margin: 0.5rem 0 0 0;
            opacity: 0.9;
        }

        /* Enhanced table styles */
        .table-superadmin thead th {
            background: linear-gradient(90deg, var(--superadmin-light) 0%, rgba(139, 92, 246, 0.1) 100%);
            border-bottom: 2px solid var(--superadmin-primary);
            color: var(--superadmin-dark);
            font-weight: 600;
        }

        .table-superadmin tbody tr:hover {
            background-color: var(--superadmin-light);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .main-sidebar {
                transform: translateX(-100%);
            }

            .main-sidebar.show {
                transform: translateX(0);
            }

            .content-wrapper {
                margin-left: 0 !important;
            }

            .superadmin-banner {
                padding: 0.75rem;
            }
        }
    </style>

    @yield('css')
</head>
<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">

        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button">
                        <i class="fas fa-crown text-superadmin"></i>
                    </a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    <a href="{{ route('admin.superadmin.dashboard') }}" class="nav-link">Super Admin Dashboard</a>
                </li>
            </ul>

            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <!-- Super Admin Status Indicator -->
                <li class="nav-item">
                    <span class="badge badge-superadmin mr-3">
                        <i class="fas fa-crown"></i> SUPER ADMIN
                    </span>
                </li>

                <!-- Notifications Dropdown Menu -->
                <li class="nav-item dropdown">
                    <a class="nav-link" data-toggle="dropdown" href="#">
                        <i class="far fa-bell"></i>
                        <span class="badge badge-danger navbar-badge">15</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                        <span class="dropdown-item dropdown-header">15 System Notifications</span>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item">
                            <i class="fas fa-shield-alt mr-2 text-superadmin"></i> Security Alert
                            <span class="float-right text-muted text-sm">3 mins</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item">
                            <i class="fas fa-users mr-2 text-superadmin"></i> New Admin Registration
                            <span class="float-right text-muted text-sm">12 hours</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item">
                            <i class="fas fa-chart-line mr-2 text-superadmin"></i> System Performance Report
                            <span class="float-right text-muted text-sm">2 days</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item dropdown-footer">See All Notifications</a>
                    </div>
                </li>

                <!-- User Dropdown Menu -->
                <li class="nav-item dropdown">
                    <a class="nav-link" data-toggle="dropdown" href="#">
                        <i class="far fa-user"></i>
                        <span class="d-none d-md-inline">{{ auth('admin')->user()->name ?? 'Super Admin' }}</span>
                        <i class="fas fa-crown text-superadmin ml-1"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                        <span class="dropdown-item dropdown-header">
                            {{ auth('admin')->user()->name ?? 'Super Admin' }}
                            <br><small class="text-superadmin"><i class="fas fa-crown"></i> Super Administrator</small>
                        </span>
                        <div class="dropdown-divider"></div>
                        <a href="{{ route('admin.superadmin.profile') }}" class="dropdown-item">
                            <i class="fas fa-user mr-2"></i> Profile
                        </a>
                        <a href="{{ route('admin.superadmin.settings') }}" class="dropdown-item">
                            <i class="fas fa-cog mr-2"></i> System Settings
                        </a>
                        <a href="{{ route('admin.superadmin.audit-logs') }}" class="dropdown-item">
                            <i class="fas fa-history mr-2"></i> Audit Logs
                        </a>
                        <div class="dropdown-divider"></div>
                        <form method="POST" action="{{ route('admin.superadmin.logout') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="dropdown-item border-0 bg-transparent w-100 text-start">
                                <i class="fas fa-sign-out-alt mr-2"></i> Logout
                            </button>
                        </form>
                    </div>
                </li>
            </ul>
        </nav>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- Brand Logo -->
            <a href="{{ route('admin.superadmin.dashboard') }}" class="brand-link">
                <img src="{{ asset('images/logo.png') }}" alt="Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
                <span class="brand-text font-weight-light">
                    <i class="fas fa-crown"></i> {{ config('app.name', 'SuperAdmin') }}
                </span>
            </a>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Sidebar user panel (optional) -->
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="image">
                        <img src="{{ asset('images/avatar.png') }}" class="img-circle elevation-2" alt="User Image">
                    </div>
                    <div class="info">
                        <a href="#" class="d-block">{{ auth('admin')->user()->name ?? 'Super Admin' }}</a>
                        <small class="text-superadmin">
                            <i class="fas fa-crown"></i> Super Administrator
                        </small>
                    </div>
                </div>

                <!-- Sidebar Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                        <!-- Dashboard -->
                        <li class="nav-item">
                            <a href="{{ route('admin.superadmin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.superadmin.dashboard*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>

                        <!-- Super Admin Exclusive Features -->
                        <li class="nav-item {{ request()->routeIs('admin.superadmin.system*') ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ request()->routeIs('admin.superadmin.system*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-cogs text-superadmin"></i>
                                <p>
                                    System Control
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('admin.superadmin.system.settings') }}" class="nav-link {{ request()->routeIs('admin.superadmin.system.settings') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon text-superadmin"></i>
                                        <p>Global Settings</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.superadmin.system.backup') }}" class="nav-link {{ request()->routeIs('admin.superadmin.system.backup') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon text-superadmin"></i>
                                        <p>System Backup</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.superadmin.system.maintenance') }}" class="nav-link {{ request()->routeIs('admin.superadmin.system.maintenance') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon text-superadmin"></i>
                                        <p>Maintenance Mode</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- Admin Management -->
                        <li class="nav-item {{ request()->routeIs('admin.superadmin.admins*') ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ request()->routeIs('admin.superadmin.admins*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-user-shield text-superadmin"></i>
                                <p>
                                    Admin Management
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('admin.superadmin.admins.index') }}" class="nav-link {{ request()->routeIs('admin.superadmin.admins.index') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>All Admins</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.superadmin.admins.create') }}" class="nav-link {{ request()->routeIs('admin.superadmin.admins.create') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Create Admin</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.superadmin.admins.roles') }}" class="nav-link {{ request()->routeIs('admin.superadmin.admins.roles') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Roles & Permissions</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- Driver Management -->
                        <li class="nav-item {{ request()->routeIs('admin.superadmin.drivers*') ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ request()->routeIs('admin.superadmin.drivers*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-car"></i>
                                <p>
                                    Driver Management
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('admin.superadmin.drivers.index') }}" class="nav-link {{ request()->routeIs('admin.superadmin.drivers.index') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>All Drivers</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.superadmin.drivers.create') }}" class="nav-link {{ request()->routeIs('admin.superadmin.drivers.create') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Create Driver</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.superadmin.drivers.verification') }}" class="nav-link {{ request()->routeIs('admin.superadmin.drivers.verification') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Verification Dashboard</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.superadmin.drivers.kyc-review') }}" class="nav-link {{ request()->routeIs('admin.superadmin.drivers.kyc-review') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>KYC Review</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.superadmin.drivers.bulk-operations') }}" class="nav-link {{ request()->routeIs('admin.superadmin.drivers.bulk-operations') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Bulk Operations</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.superadmin.drivers.analytics') }}" class="nav-link {{ request()->routeIs('admin.superadmin.drivers.analytics') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Analytics</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- Company Oversight -->
                        <li class="nav-item {{ request()->routeIs('admin.superadmin.companies*') ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ request()->routeIs('admin.superadmin.companies*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-building text-superadmin"></i>
                                <p>
                                    Company Oversight
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('admin.superadmin.companies.index') }}" class="nav-link {{ request()->routeIs('admin.superadmin.companies.index') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>All Companies</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.superadmin.companies.approvals') }}" class="nav-link {{ request()->routeIs('admin.superadmin.companies.approvals') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Company Approvals</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.superadmin.companies.audit') }}" class="nav-link {{ request()->routeIs('admin.superadmin.companies.audit') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Company Audit</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- System Monitoring -->
                        <li class="nav-item {{ request()->routeIs('admin.superadmin.monitoring*') ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ request()->routeIs('admin.superadmin.monitoring*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-chart-line text-superadmin"></i>
                                <p>
                                    System Monitoring
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('admin.superadmin.monitoring.performance') }}" class="nav-link {{ request()->routeIs('admin.superadmin.monitoring.performance') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Performance Metrics</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.superadmin.monitoring.security') }}" class="nav-link {{ request()->routeIs('admin.superadmin.monitoring.security') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Security Dashboard</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.superadmin.monitoring.logs') }}" class="nav-link {{ request()->routeIs('admin.superadmin.monitoring.logs') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>System Logs</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- Audit & Compliance -->
                        <li class="nav-item {{ request()->routeIs('admin.superadmin.audit*') || request()->routeIs('admin.superadmin.audit-trails*') ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ request()->routeIs('admin.superadmin.audit*') || request()->routeIs('admin.superadmin.audit-trails*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-shield-alt text-superadmin"></i>
                                <p>
                                    Audit & Compliance
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('admin.superadmin.audit-trails.index') }}" class="nav-link {{ request()->routeIs('admin.superadmin.audit-trails*') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Audit Trails</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.superadmin.audit.logs') }}" class="nav-link {{ request()->routeIs('admin.superadmin.audit.logs') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Audit Logs</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.superadmin.audit.reports') }}" class="nav-link {{ request()->routeIs('admin.superadmin.audit.reports') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Compliance Reports</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.superadmin.audit.backup') }}" class="nav-link {{ request()->routeIs('admin.superadmin.audit.backup') ? 'active' : '' }}">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Data Backup</p>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- Settings -->
                        <li class="nav-item">
                            <a href="{{ route('admin.superadmin.settings') }}" class="nav-link {{ request()->routeIs('admin.superadmin.settings*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-cog"></i>
                                <p>Settings</p>
                            </a>
                        </li>
                    </ul>
                </nav>
                <!-- /.sidebar-menu -->
            </div>
            <!-- /.sidebar -->
        </aside>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Super Admin Banner -->
            <div class="superadmin-banner">
                <h4><i class="fas fa-crown"></i> Super Administrator Portal</h4>
                <p>Advanced system control and management capabilities</p>
            </div>

            <!-- Content Header (Page header) -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">@yield('title', 'Dashboard')</h1>
                        </div><!-- /.col -->
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="{{ route('admin.superadmin.dashboard') }}">Super Admin</a></li>
                                @yield('breadcrumbs')
                            </ol>
                        </div><!-- /.col -->
                    </div><!-- /.row -->
                </div><!-- /.container-fluid -->
            </div>
            <!-- /.content-header -->

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    @yield('content')
                </div><!-- /.container-fluid -->
            </section>
            <!-- /.content -->
        </div>
        <!-- /.content-wrapper -->

        <!-- Control Sidebar -->
        <aside class="control-sidebar control-sidebar-dark">
            <!-- Control sidebar content goes here -->
            <div class="p-3">
                <h5>Quick System Actions</h5>
                <div class="mb-3">
                    <button class="btn btn-superadmin btn-sm btn-block" onclick="systemMaintenance()">
                        <i class="fas fa-tools"></i> Maintenance Mode
                    </button>
                </div>
                <div class="mb-3">
                    <button class="btn btn-superadmin btn-sm btn-block" onclick="systemBackup()">
                        <i class="fas fa-download"></i> Quick Backup
                    </button>
                </div>
                <div class="mb-3">
                    <button class="btn btn-superadmin btn-sm btn-block" onclick="clearCache()">
                        <i class="fas fa-broom"></i> Clear Cache
                    </button>
                </div>
            </div>
        </aside>
        <!-- /.control-sidebar -->

        <!-- Main Footer -->
        <footer class="main-footer">
            <strong>&copy; {{ date('Y') }} <a href="#">{{ config('app.name', 'Laravel') }}</a>.</strong>
            All rights reserved.
            <div class="float-right d-none d-sm-inline-block">
                <b>Super Admin Panel</b> v2.0.0
            </div>
        </footer>
    </div>
    <!-- ./wrapper -->

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AdminLTE App -->
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

    @yield('scripts')

    <script>
        $(document).ready(function() {
            // Initialize tooltips
            $('[data-toggle="tooltip"]').tooltip();

            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);

            // Confirm delete actions
            $('.delete-btn').click(function(e) {
                if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                    e.preventDefault();
                }
            });

            // Loading states for forms
            $('form').submit(function() {
                const submitBtn = $(this).find('button[type="submit"]');
                submitBtn.prop('disabled', true).addClass('loading');
            });

            // Super Admin specific confirmations
            $('.superadmin-action').click(function(e) {
                const action = $(this).data('action') || 'perform this action';
                if (!confirm(`As a Super Administrator, are you sure you want to ${action}? This may affect the entire system.`)) {
                    e.preventDefault();
                }
            });
        });

        // Super Admin specific functions
        function systemMaintenance() {
            if (confirm('Enter/Exit Maintenance Mode? This will affect all users.')) {
                // Implementation would go here
                alert('Maintenance mode toggled');
            }
        }

        function systemBackup() {
            if (confirm('Create system backup? This may take a few minutes.')) {
                // Implementation would go here
                alert('Backup initiated');
            }
        }

        function clearCache() {
            if (confirm('Clear all system caches?')) {
                // Implementation would go here
                alert('Cache cleared');
            }
        }
    </script>
</body>
</html>
