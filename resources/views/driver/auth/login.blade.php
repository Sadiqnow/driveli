<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Driver Login - {{ config('app.name', 'DriveLink') }}</title>
    
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
            --driver-primary: #2563eb;
            --driver-secondary: #64748b;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 2rem;
        }
        
        .login-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 2.5rem;
            text-align: center;
        }
        
        .login-logo {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--driver-primary);
            margin-bottom: 0.5rem;
        }
        
        .login-subtitle {
            color: var(--driver-secondary);
            margin-bottom: 2rem;
        }
        
        .form-control {
            border-radius: 12px;
            padding: 0.875rem 1rem;
            border: 2px solid #e2e8f0;
            font-size: 16px; /* Prevent zoom on iOS */
            transition: border-color 0.2s ease;
        }
        
        .form-control:focus {
            border-color: var(--driver-primary);
            box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.25);
        }
        
        .input-group {
            margin-bottom: 1.25rem;
        }
        
        .input-group-text {
            border: 2px solid #e2e8f0;
            border-right: none;
            border-radius: 12px 0 0 12px;
            background-color: #f8fafc;
            padding: 0.875rem 1rem;
        }
        
        .input-group .form-control {
            border-left: none;
            border-radius: 0 12px 12px 0;
        }
        
        .btn-driver-primary {
            background: linear-gradient(135deg, var(--driver-primary) 0%, #1d4ed8 100%);
            border: none;
            border-radius: 12px;
            padding: 0.875rem 2rem;
            font-weight: 600;
            color: white;
            width: 100%;
            transition: transform 0.2s ease;
        }
        
        .btn-driver-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3);
            color: white;
        }
        
        .form-check-input:checked {
            background-color: var(--driver-primary);
            border-color: var(--driver-primary);
        }
        
        .forgot-password {
            color: var(--driver-primary);
            text-decoration: none;
            font-size: 0.875rem;
        }
        
        .forgot-password:hover {
            color: #1d4ed8;
            text-decoration: underline;
        }
        
        .register-link {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e2e8f0;
        }
        
        .register-link a {
            color: var(--driver-primary);
            text-decoration: none;
            font-weight: 600;
        }
        
        .register-link a:hover {
            color: #1d4ed8;
            text-decoration: underline;
        }
        
        /* Mobile optimizations */
        @media (max-width: 767.98px) {
            .login-container {
                padding: 1rem;
            }
            
            .login-card {
                padding: 2rem 1.5rem;
            }
            
            .login-logo {
                font-size: 2rem;
            }
        }
        
        /* Loading state */
        .btn-loading {
            position: relative;
            color: transparent !important;
        }
        
        .btn-loading::after {
            content: '';
            position: absolute;
            width: 20px;
            height: 20px;
            top: 50%;
            left: 50%;
            margin-left: -10px;
            margin-top: -10px;
            border: 2px solid #ffffff;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-logo">
                <i class="fas fa-truck"></i>
            </div>
            <h1 class="h4 mb-1">DriveLink Driver</h1>
            <p class="login-subtitle">Sign in to your driver account</p>
            
            <form method="POST" action="{{ route('driver.login.submit') }}" id="loginForm">
                @csrf
                
                <!-- Email Address -->
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-envelope" aria-hidden="true"></i>
                    </span>
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
                    @error('email')
                        <div id="email-error" class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- Password -->
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-lock" aria-hidden="true"></i>
                    </span>
                    <input type="password" 
                           id="password"
                           name="password" 
                           class="form-control @error('password') is-invalid @enderror" 
                           placeholder="Password"
                           required 
                           autocomplete="current-password"
                           aria-describedby="password-help @error('password') password-error @enderror">
                    @error('password')
                        <div id="password-error" class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- Remember Me & Forgot Password -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember" name="remember">
                        <label class="form-check-label text-sm" for="remember">
                            Remember me
                        </label>
                    </div>
                    <a href="{{ route('driver.password.request') }}" class="forgot-password">
                        Forgot password?
                    </a>
                </div>
                
                <!-- Error Messages -->
                @if (session('error'))
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-circle me-2" aria-hidden="true"></i>
                        {{ session('error') }}
                    </div>
                @endif

                @if (session('status'))
                    <div class="alert alert-success" role="alert">
                        <i class="fas fa-check-circle me-2" aria-hidden="true"></i>
                        {{ session('status') }}
                    </div>
                @endif
                
                <!-- Submit Button -->
                <button type="submit" class="btn btn-driver-primary" id="loginButton">
                    <i class="fas fa-sign-in-alt me-2" aria-hidden="true"></i>
                    Sign In
                </button>
            </form>
            
            <!-- Registration Link -->
            <div class="register-link">
                <p class="mb-0 text-sm">
                    Don't have an account? 
                    <a href="{{ route('driver.register') }}">Register as a driver</a>
                </p>
            </div>
            
            <!-- Help Link -->
            <div class="mt-3">
                <p class="mb-0 text-xs text-muted">
                    Need help? <a href="mailto:support@drivelink.com" class="text-primary">Contact Support</a>
                </p>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('loginForm');
            const loginButton = document.getElementById('loginButton');
            
            form.addEventListener('submit', function(e) {
                // Add loading state
                loginButton.classList.add('btn-loading');
                loginButton.disabled = true;
                
                // Basic client-side validation
                const email = form.querySelector('input[name="email"]').value.trim();
                const password = form.querySelector('input[name="password"]').value.trim();
                
                if (!email || !password) {
                    e.preventDefault();
                    loginButton.classList.remove('btn-loading');
                    loginButton.disabled = false;
                    
                    // Show error
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'alert alert-danger mt-3';
                    errorDiv.innerHTML = '<i class="fas fa-exclamation-circle me-2"></i>Please fill in both email and password fields.';
                    
                    // Remove existing errors
                    const existingError = form.querySelector('.alert-danger');
                    if (existingError) {
                        existingError.remove();
                    }
                    
                    form.appendChild(errorDiv);
                    return false;
                }
            });
            
            // Auto-focus email field
            const emailField = document.getElementById('email');
            if (emailField) {
                emailField.focus();
            }
            
            // Show/hide password functionality could be added here
            // Enhanced accessibility for screen readers
            const inputs = form.querySelectorAll('input');
            inputs.forEach(input => {
                input.addEventListener('invalid', function() {
                    this.setAttribute('aria-invalid', 'true');
                });
                
                input.addEventListener('input', function() {
                    if (this.validity.valid) {
                        this.removeAttribute('aria-invalid');
                    }
                });
            });
        });
    </script>
</body>
</html>