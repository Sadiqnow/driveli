<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - {{ config('app.name', 'DriveLink') }} KYC Portal</title>

    <!-- Google Font: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css">
    
    <style>
        :root {
            --drivelink-primary: #3b82f6;
            --drivelink-secondary: #64748b;
            --drivelink-success: #10b981;
            --drivelink-warning: #f59e0b;
            --drivelink-danger: #ef4444;
            --drivelink-info: #06b6d4;
            --drivelink-light: #f8fafc;
            --drivelink-dark: #1e293b;
            --drivelink-border-radius: 8px;
            --drivelink-transition: all 0.2s ease;
            --drivelink-box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            --drivelink-box-shadow-lg: 0 10px 15px rgba(0,0,0,0.1);
            --drivelink-gradient-primary: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
            --drivelink-gradient-success: linear-gradient(135deg, #10b981 0%, #059669 100%);
            --drivelink-gradient-info: linear-gradient(135deg, #e0f2fe 0%, #b3e5fc 100%);
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--drivelink-light);
            color: var(--drivelink-dark);
        }
        
        .kyc-layout {
            min-height: 100vh;
            display: flex;
        }
        
        .kyc-sidebar {
            width: 280px;
            background: var(--drivelink-gradient-primary);
            box-shadow: 4px 0 15px rgba(0,0,0,0.1);
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
        }
        
        .kyc-brand {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            text-align: center;
        }
        
        .kyc-brand h4 {
            color: white;
            margin: 0;
            font-weight: 600;
        }
        
        .kyc-brand small {
            color: rgba(255,255,255,0.8);
        }
        
        .kyc-progress {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .kyc-progress h6 {
            color: white;
            margin-bottom: 1rem;
            font-weight: 500;
        }
        
        .progress-step {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            color: rgba(255,255,255,0.7);
            transition: var(--drivelink-transition);
        }
        
        .progress-step.active {
            color: white;
        }
        
        .progress-step.completed {
            color: #10b981;
        }
        
        .step-icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 0.75rem;
            font-size: 0.875rem;
            background: rgba(255,255,255,0.1);
            border: 2px solid rgba(255,255,255,0.3);
        }
        
        .progress-step.active .step-icon {
            background: white;
            color: var(--drivelink-primary);
            border-color: white;
        }
        
        .progress-step.completed .step-icon {
            background: #10b981;
            color: white;
            border-color: #10b981;
        }
        
        .kyc-nav {
            padding: 1rem 0;
        }
        
        .kyc-nav-item {
            margin: 0.25rem 1rem;
        }
        
        .kyc-nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            border-radius: var(--drivelink-border-radius);
            transition: var(--drivelink-transition);
        }
        
        .kyc-nav-link:hover,
        .kyc-nav-link.active {
            background-color: rgba(255,255,255,0.1);
            color: white;
            transform: translateX(4px);
        }
        
        .kyc-nav-link.disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .kyc-nav-link i {
            width: 20px;
            margin-right: 0.75rem;
        }
        
        .kyc-main {
            margin-left: 280px;
            flex: 1;
            background-color: white;
            min-height: 100vh;
        }
        
        .kyc-header {
            background: white;
            border-bottom: 1px solid #e2e8f0;
            padding: 1.5rem 2rem;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .kyc-content {
            padding: 2rem;
        }
        
        .step-card {
            background: white;
            border-radius: 12px;
            box-shadow: var(--drivelink-box-shadow);
            border: 1px solid #e2e8f0;
            margin-bottom: 2rem;
        }
        
        .step-card-header {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid #e2e8f0;
            background: var(--drivelink-gradient-info);
        }
        
        .step-card-body {
            padding: 2rem;
        }
        
        .btn-kyc-primary {
            background: var(--drivelink-gradient-primary);
            border: none;
            border-radius: var(--drivelink-border-radius);
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            color: white;
            transition: var(--drivelink-transition);
        }
        
        .btn-kyc-primary:hover {
            transform: translateY(-2px);
            box-shadow: var(--drivelink-box-shadow-lg);
            color: white;
        }
        
        .btn-kyc-outline {
            background: transparent;
            border: 2px solid var(--drivelink-secondary);
            border-radius: var(--drivelink-border-radius);
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            color: var(--drivelink-secondary);
            transition: var(--drivelink-transition);
        }
        
        .btn-kyc-outline:hover {
            background: var(--drivelink-secondary);
            color: white;
        }
        
        .form-control {
            border-radius: var(--drivelink-border-radius);
            border: 1px solid #d1d5db;
            padding: 0.75rem 1rem;
            transition: var(--drivelink-transition);
        }
        
        .form-control:focus {
            border-color: var(--drivelink-primary);
            box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
        }
        
        .form-label {
            font-weight: 500;
            color: var(--drivelink-dark);
            margin-bottom: 0.5rem;
        }
        
        .required::after {
            content: ' *';
            color: var(--drivelink-danger);
        }
        
        .alert {
            border-radius: var(--drivelink-border-radius);
            border: none;
        }
        
        /* Mobile responsive */
        @media (max-width: 768px) {
            .kyc-sidebar {
                position: fixed;
                left: -280px;
                transition: left 0.3s ease;
            }
            
            .kyc-sidebar.show {
                left: 0;
            }
            
            .kyc-main {
                margin-left: 0;
            }
            
            .mobile-menu-toggle {
                display: block !important;
            }
            
            .kyc-header {
                padding-left: 4rem;
            }
            
            .kyc-content {
                padding: 1rem;
            }
            
            .step-card-header,
            .step-card-body {
                padding: 1.5rem;
            }
        }
        
        .mobile-menu-toggle {
            display: none;
            position: fixed;
            top: 1rem;
            left: 1rem;
            z-index: 1100;
            background: var(--drivelink-primary);
            color: white;
            border: none;
            padding: 0.75rem;
            border-radius: var(--drivelink-border-radius);
            box-shadow: var(--drivelink-box-shadow);
        }
        
        .step-info {
            background: var(--drivelink-gradient-info);
            border: none;
            border-left: 4px solid var(--drivelink-info);
            border-radius: var(--drivelink-border-radius);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .completion-badge {
            background: var(--drivelink-gradient-success);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.875rem;
        }
        
        /* Progress animation */
        @keyframes progressPulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .progress-step.active .step-icon {
            animation: progressPulse 2s infinite;
        }
        
        /* Form enhancements */
        .form-floating > label {
            padding: 1rem;
        }
        
        .form-floating > .form-control {
            padding-top: 1.625rem;
            padding-bottom: 1.625rem;
        }
        
        .invalid-feedback {
            display: block;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
        
        .valid-feedback {
            display: block;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }
    </style>
    
    @yield('styles')
</head>
<body>
    <!-- Mobile menu toggle -->
    <button class="mobile-menu-toggle" id="mobileMenuToggle" aria-label="Toggle KYC navigation menu">
        <i class="fas fa-bars"></i>
    </button>

    <div class="kyc-layout">
        <!-- Sidebar -->
        <div class="kyc-sidebar" id="kycSidebar">
            <div class="kyc-brand">
                <h4><i class="fas fa-id-card me-2"></i>KYC Verification</h4>
                <small>DriveLink Driver Portal</small>
            </div>
            
            <div class="kyc-progress">
                <h6><i class="fas fa-chart-line me-2"></i>Progress</h6>
                @php
                    $currentStep = $currentStep ?? 1;
                    $driver = auth('driver')->user();
                    $step1Completed = $driver && $driver->kyc_step_1_completed_at;
                    $step2Completed = $driver && $driver->kyc_step_2_completed_at;
                    $step3Completed = $driver && $driver->kyc_step_3_completed_at;
                @endphp
                
                <div class="progress-step {{ $currentStep >= 1 ? 'active' : '' }} {{ $step1Completed ? 'completed' : '' }}">
                    <div class="step-icon">
                        @if($step1Completed)
                            <i class="fas fa-check"></i>
                        @else
                            1
                        @endif
                    </div>
                    <div>
                        <div class="fw-medium">Personal Information</div>
                        <small>Basic details & identity</small>
                    </div>
                </div>
                
                <div class="progress-step {{ $currentStep >= 2 ? 'active' : '' }} {{ $step2Completed ? 'completed' : '' }}">
                    <div class="step-icon">
                        @if($step2Completed)
                            <i class="fas fa-check"></i>
                        @else
                            2
                        @endif
                    </div>
                    <div>
                        <div class="fw-medium">Additional Details</div>
                        <small>Address & emergency contact</small>
                    </div>
                </div>
                
                <div class="progress-step {{ $currentStep >= 3 ? 'active' : '' }} {{ $step3Completed ? 'completed' : '' }}">
                    <div class="step-icon">
                        @if($step3Completed)
                            <i class="fas fa-check"></i>
                        @else
                            3
                        @endif
                    </div>
                    <div>
                        <div class="fw-medium">Final Review</div>
                        <small>Submit for verification</small>
                    </div>
                </div>
            </div>
            
            <nav class="kyc-nav">
                <div class="kyc-nav-item">
                    <a href="{{ route('driver.kyc.index') }}" 
                       class="kyc-nav-link {{ request()->routeIs('driver.kyc.index') ? 'active' : '' }}">
                        <i class="fas fa-tachometer-alt"></i>
                        Overview
                    </a>
                </div>
                
                <div class="kyc-nav-item">
                    <a href="{{ route('driver.kyc.step1') }}" 
                       class="kyc-nav-link {{ request()->routeIs('driver.kyc.step1*') ? 'active' : '' }}">
                        <i class="fas fa-user"></i>
                        Step 1: Personal Info
                        @if($step1Completed)
                            <i class="fas fa-check-circle text-success ms-auto"></i>
                        @endif
                    </a>
                </div>
                
                <div class="kyc-nav-item">
                    <a href="{{ route('driver.kyc.step2') }}" 
                       class="kyc-nav-link {{ request()->routeIs('driver.kyc.step2*') ? 'active' : '' }} {{ !$step1Completed ? 'disabled' : '' }}">
                        <i class="fas fa-id-card"></i>
                        Step 2: Details
                        @if($step2Completed)
                            <i class="fas fa-check-circle text-success ms-auto"></i>
                        @elseif(!$step1Completed)
                            <i class="fas fa-lock ms-auto"></i>
                        @endif
                    </a>
                </div>
                
                <div class="kyc-nav-item">
                    <a href="{{ route('driver.kyc.step3') }}" 
                       class="kyc-nav-link {{ request()->routeIs('driver.kyc.step3*') ? 'active' : '' }} {{ !$step2Completed ? 'disabled' : '' }}">
                        <i class="fas fa-clipboard-check"></i>
                        Step 3: Review
                        @if($step3Completed)
                            <i class="fas fa-check-circle text-success ms-auto"></i>
                        @elseif(!$step2Completed)
                            <i class="fas fa-lock ms-auto"></i>
                        @endif
                    </a>
                </div>
                
                <hr style="border-color: rgba(255,255,255,0.1); margin: 1rem;">
                
                <div class="kyc-nav-item">
                    <a href="{{ route('driver.dashboard') }}" class="kyc-nav-link">
                        <i class="fas fa-arrow-left"></i>
                        Back to Dashboard
                    </a>
                </div>
            </nav>
        </div>

        <!-- Main content -->
        <div class="kyc-main">
            <!-- Header -->
            <header class="kyc-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h4 mb-1">@yield('page-title', 'KYC Verification')</h1>
                        <p class="text-muted mb-0">@yield('page-description', 'Complete your identity verification')</p>
                    </div>
                    
                    <div class="d-flex align-items-center">
                        @if($step1Completed && $step2Completed && $step3Completed)
                            <span class="completion-badge">
                                <i class="fas fa-check-circle me-1"></i>
                                KYC Complete
                            </span>
                        @else
                            <div class="text-end">
                                <div class="fw-medium">Step {{ $currentStep }} of 3</div>
                                <div class="progress" style="width: 100px; height: 6px;">
                                    <div class="progress-bar bg-primary" 
                                         style="width: {{ ($currentStep / 3) * 100 }}%"></div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </header>

            <!-- Content -->
            <main class="kyc-content">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        {{ session('warning') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <h6 class="alert-heading mb-2">Please fix the following errors:</h6>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
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
            const sidebar = document.getElementById('kycSidebar');
            
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
            
            // Auto-hide alerts after 8 seconds
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
                alerts.forEach(function(alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 8000);

            // Prevent navigation to locked steps
            document.querySelectorAll('.kyc-nav-link.disabled').forEach(function(link) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    alert('Please complete the previous steps first.');
                });
            });
        });
    </script>
    
    @yield('scripts')
</body>
</html>