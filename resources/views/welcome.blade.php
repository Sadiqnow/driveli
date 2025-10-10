<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Drivelink') }} - Driver Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light">
    <div class="container-fluid">
        <div class="row min-vh-100">
            <div class="col-lg-6 d-flex align-items-center justify-content-center bg-primary text-white">
                <div class="text-center p-5">
                    <i class="fas fa-truck fa-5x mb-4"></i>
                    <h1 class="display-4 fw-bold mb-3">Drivelink</h1>
                    <p class="lead mb-4">Nigeria's Premier Driver Management Platform</p>
                    <p class="mb-0">Connecting logistics companies with qualified drivers across Lagos, Abuja, Port Harcourt, and Kano.</p>
                </div>
            </div>
            <div class="col-lg-6 d-flex align-items-center justify-content-center">
                <div class="w-100 px-5" style="max-width: 480px;">
                    <div class="text-center mb-4">
                        <h2 class="fw-bold text-dark">Join DriveLink</h2>
                        <p class="text-muted">Choose your access level</p>
                    </div>
                    
                    <!-- Driver Registration (Primary CTA) -->
                    <div class="card border-primary mb-4" style="box-shadow: 0 4px 12px rgba(0,123,255,0.15);">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-steering-wheel fa-3x text-primary mb-3"></i>
                            <h4 class="card-title text-primary mb-2">Are you a Driver?</h4>
                            <p class="card-text text-muted mb-3">
                                Register now and start receiving job opportunities from top logistics companies
                            </p>
                            <a href="{{ route('driver.register') }}" class="btn btn-primary btn-lg w-100">
                                <i class="fas fa-user-plus me-2"></i>Register New Driver
                            </a>
                            <div class="mt-2">
                                <small class="text-muted">Already have an account? 
                                    <a href="{{ route('driver.login') }}" class="text-primary">Login here</a>
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Admin Access -->
                    <div class="card border-secondary">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-cogs fa-2x text-secondary mb-3"></i>
                            <h5 class="card-title text-secondary mb-2">Admin Access</h5>
                            <p class="card-text text-muted mb-3 small">
                                Manage drivers, companies, and requests
                            </p>
                            <div class="d-grid gap-2">
                                <a href="{{ route('admin.login') }}" class="btn btn-secondary">
                                    <i class="fas fa-sign-in-alt me-2"></i>Admin Login
                                </a>
                                <a href="{{ route('admin.register') }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-user-shield me-1"></i>Create Admin Account
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- System Status -->
                    <div class="text-center mt-4">
                        <small class="text-muted">
                            <i class="fas fa-check-circle text-success me-1"></i>
                            System Status: Online
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>