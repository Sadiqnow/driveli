<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reset Password - {{ config('app.name', 'DriveLink') }}</title>
    
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
        
        .reset-password-container {
            width: 100%;
            max-width: 400px;
            padding: 2rem;
        }
        
        .reset-password-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 2.5rem;
            text-align: center;
        }
        
        .reset-password-logo {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--driver-primary);
            margin-bottom: 0.5rem;
        }
        
        .form-control {
            border-radius: 12px;
            padding: 0.875rem 1rem;
            border: 2px solid #e2e8f0;
            font-size: 16px;
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
        
        .back-to-login {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e2e8f0;
        }
        
        .back-to-login a {
            color: var(--driver-primary);
            text-decoration: none;
            font-weight: 600;
        }
        
        .back-to-login a:hover {
            color: #1d4ed8;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="reset-password-container">
        <div class="reset-password-card">
            <div class="reset-password-logo">
                <i class="fas fa-lock"></i>
            </div>
            <h1 class="h4 mb-1">Reset Password</h1>
            <p class="text-muted mb-4">Enter your new password below.</p>
            
            <form method="POST" action="{{ route('driver.password.update') }}">
                @csrf
                
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">
                
                <!-- Email Address (readonly) -->
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-envelope" aria-hidden="true"></i>
                    </span>
                    <input type="email" 
                           class="form-control" 
                           value="{{ $email }}"
                           readonly>
                </div>
                
                <!-- New Password -->
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-lock" aria-hidden="true"></i>
                    </span>
                    <input type="password" 
                           id="password"
                           name="password" 
                           class="form-control @error('password') is-invalid @enderror" 
                           placeholder="New Password"
                           required 
                           autocomplete="new-password">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- Confirm Password -->
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-lock" aria-hidden="true"></i>
                    </span>
                    <input type="password" 
                           id="password_confirmation"
                           name="password_confirmation" 
                           class="form-control @error('password_confirmation') is-invalid @enderror" 
                           placeholder="Confirm New Password"
                           required 
                           autocomplete="new-password">
                    @error('password_confirmation')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- Error Messages -->
                @if ($errors->any())
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-circle me-2" aria-hidden="true"></i>
                        @foreach ($errors->all() as $error)
                            {{ $error }}<br>
                        @endforeach
                    </div>
                @endif
                
                <!-- Submit Button -->
                <button type="submit" class="btn btn-driver-primary">
                    <i class="fas fa-save me-2" aria-hidden="true"></i>
                    Reset Password
                </button>
            </form>
            
            <!-- Back to Login -->
            <div class="back-to-login">
                <p class="mb-0">
                    <a href="{{ route('driver.login') }}">
                        <i class="fas fa-arrow-left me-1"></i> Back to Login
                    </a>
                </p>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>