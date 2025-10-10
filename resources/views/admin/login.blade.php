<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Login - {{ config('app.name') }}</title>
    
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">
    
    <style>
        .login-page {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .login-box {
            width: 400px;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }
        .card-header {
            background: transparent;
            border-bottom: none;
            text-align: center;
            padding: 2rem 1.5rem 1rem;
        }
        .login-logo {
            font-size: 2.1rem;
            font-weight: 300;
            margin-bottom: 1rem;
            color: #495057;
        }
        .login-logo .logo-icon {
            font-size: 2.5rem;
            margin-right: 10px;
            color: #667eea;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 25px;
            padding: 12px;
            font-weight: 500;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .form-control {
            border-radius: 25px;
            padding: 12px 20px;
            border: 2px solid #e9ecef;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .icheck-primary input:checked + label::before {
            background-color: #667eea;
            border-color: #667eea;
        }
        
        /* Mobile optimizations */
        @media (max-width: 767.98px) {
            .login-box {
                width: 90%;
                max-width: 400px;
                margin: 2rem auto;
            }
            
            .login-logo {
                font-size: 1.8rem;
            }
            
            .login-logo .logo-icon {
                font-size: 2rem;
            }
            
            .card {
                margin: 0;
                box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            }
            
            .form-control {
                font-size: 16px; /* Prevent zoom on iOS */
                padding: 0.875rem 1.25rem;
            }
            
            .btn-primary {
                padding: 0.875rem;
                font-size: 1rem;
            }
            
            .input-group-text {
                padding: 0.875rem 1rem;
            }
            
            /* Ensure touch targets are adequate */
            .btn, .form-control, #password-toggle {
                min-height: 44px;
            }
            
            /* Better spacing on mobile */
            .card-body {
                padding: 2rem 1.5rem;
            }
            
            .card-header {
                padding: 2rem 1.5rem 1rem;
            }
        }
        
        /* Landscape phone */
        @media (max-width: 767.98px) and (orientation: landscape) {
            .login-box {
                margin: 1rem auto;
            }
            
            .card-header {
                padding: 1.5rem 1.5rem 0.5rem;
            }
            
            .card-body {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body class="hold-transition login-page">
<div class="login-box">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <div class="login-logo">
                <i class="fas fa-truck logo-icon"></i>
                <strong>Drivelink</strong> Admin
            </div>
            <p class="text-muted">Sign in to access your admin dashboard</p>
        </div>
        
        <div class="card-body">
            <form action="{{ route('admin.login.submit') }}" method="POST">
                @csrf
                
                <!-- Email Address -->
                <div class="input-group mb-3">
                    <label for="email" class="visually-hidden">Email Address</label>
                    <input type="email" 
                           id="email"
                           name="email" 
                           class="form-control @error('email') is-invalid @enderror" 
                           placeholder="Email Address"
                           value="{{ old('email') }}"
                           required 
                           autocomplete="email" 
                           autofocus
                           aria-describedby="email-help @error('email') email-error @enderror">
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-envelope" aria-hidden="true"></span>
                        </div>
                    </div>
                    <div id="email-help" class="visually-hidden">Enter your registered email address to sign in</div>
                    @error('email')
                        <div id="email-error" class="invalid-feedback" role="alert" aria-live="polite">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- Password -->
                <div class="input-group mb-3">
                    <label for="password" class="visually-hidden">Password</label>
                    <input type="password" 
                           id="password"
                           name="password" 
                           class="form-control @error('password') is-invalid @enderror" 
                           placeholder="Password"
                           required 
                           autocomplete="current-password"
                           aria-describedby="password-help @error('password') password-error @enderror">
                    <div class="input-group-append">
                        <button type="button" class="input-group-text btn btn-outline-secondary" 
                                aria-label="Toggle password visibility" 
                                id="password-toggle">
                            <span class="fas fa-lock" aria-hidden="true"></span>
                        </button>
                    </div>
                    <div id="password-help" class="visually-hidden">Enter your password. Click the lock icon to toggle visibility</div>
                    @error('password')
                        <div id="password-error" class="invalid-feedback" role="alert" aria-live="polite">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- Remember Me & Forgot Password -->
                <div class="row">
                    <div class="col-8">
                        <div class="icheck-primary">
                            <input type="checkbox" id="remember" name="remember">
                            <label for="remember">Remember Me</label>
                        </div>
                    </div>
                    <div class="col-4 text-right">
                        <a href="{{ route('admin.password.request') }}" class="text-sm">
                            Forgot?
                        </a>
                    </div>
                </div>
                
                <!-- Error Messages -->
                @if ($errors->any())
                    <div class="alert alert-danger mt-3" role="alert" aria-live="polite">
                        <h4 class="alert-heading visually-hidden">Login Errors</h4>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Session Status -->
                @if (session('status'))
                    <div class="alert alert-success mt-3" role="alert" aria-live="polite">
                        {{ session('status') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger mt-3" role="alert" aria-live="polite">
                        {{ session('error') }}
                    </div>
                @endif
                
                <!-- Submit Button -->
                <div class="row mt-4">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-sign-in-alt mr-2"></i>
                            Sign In
                        </button>
                    </div>
                </div>
            </form>
            
            <!-- Additional Links -->
            <div class="text-center mt-4 pt-3 border-top">
                @php
                    $adminCount = \App\Models\AdminUser::count();
                @endphp
                
                @if($adminCount === 0 || app()->environment(['local', 'testing']))
                    <p class="text-muted text-sm mb-2">
                        @if($adminCount === 0)
                            <i class="fas fa-user-plus text-warning mr-1"></i>
                            No admin users found. Create your first admin account.
                        @else
                            <i class="fas fa-user-plus text-info mr-1"></i>
                            Development mode - Registration available
                        @endif
                    </p>
                    <a href="{{ route('admin.register') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-user-plus mr-1"></i>
                        Create Admin Account
                    </a>
                    <hr class="my-3">
                @endif
                
                <p class="text-muted text-sm">
                    <i class="fas fa-shield-alt text-success mr-1"></i>
                    Secure Admin Access
                </p>
                <p class="text-muted text-xs">
                    Need help? 
                    <a href="mailto:support@drivelink.com" class="text-primary">
                        Contact Support
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- jQuery -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/js/adminlte.min.js"></script>

<script>
$(document).ready(function() {
    // Auto-focus email field
    $('input[name="email"]').focus();
    
    // Form submission animation with validation
    $('form').on('submit', function(e) {
        const form = $(this);
        const email = form.find('input[name="email"]').val().trim();
        const password = form.find('input[name="password"]').val().trim();
        
        // Basic client-side validation
        if (!email || !password) {
            e.preventDefault();
            alert('Please fill in both email and password fields.');
            return false;
        }
        
        const submitBtn = form.find('button[type="submit"]');
        submitBtn.prop('disabled', true);
        submitBtn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Signing In...');
    });
    
    // Show/hide password toggle with better accessibility
    $('#password-toggle').on('click', function() {
        const passwordInput = $('#password');
        const icon = $(this).find('i');
        
        if (passwordInput.attr('type') === 'password') {
            passwordInput.attr('type', 'text');
            icon.removeClass('fa-lock').addClass('fa-unlock');
            $(this).attr('aria-label', 'Hide password');
        } else {
            passwordInput.attr('type', 'password');
            icon.removeClass('fa-unlock').addClass('fa-lock');
            $(this).attr('aria-label', 'Show password');
        }
    });
});
</script>

</body>
</html>