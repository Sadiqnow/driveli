<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Registration - {{ config('app.name') }}</title>
    
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.2.0/css/adminlte.min.css">
    
    <style>
        .register-page {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .register-box {
            width: 450px;
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
        .register-logo {
            font-size: 2.1rem;
            font-weight: 300;
            margin-bottom: 1rem;
            color: #495057;
        }
        .register-logo .logo-icon {
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
    </style>
</head>
<body class="hold-transition register-page">
<div class="register-box">
    <div class="card card-outline card-primary">
        <div class="card-header">
            <div class="register-logo">
                <i class="fas fa-user-plus logo-icon"></i>
                <strong>Drivelink</strong> Admin
            </div>
            <p class="text-muted">Create your admin account</p>
        </div>
        
        <div class="card-body">
            <form action="{{ route('admin.register.submit') }}" method="POST">
                @csrf
                
                <!-- Full Name -->
                <div class="input-group mb-3">
                    <input type="text" 
                           name="name" 
                           class="form-control @error('name') is-invalid @enderror" 
                           placeholder="Full Name"
                           value="{{ old('name') }}"
                           required 
                           autocomplete="name" 
                           autofocus>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-user"></span>
                        </div>
                    </div>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- Email Address -->
                <div class="input-group mb-3">
                    <input type="email" 
                           name="email" 
                           class="form-control @error('email') is-invalid @enderror" 
                           placeholder="Email Address"
                           value="{{ old('email') }}"
                           required 
                           autocomplete="email">
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-envelope"></span>
                        </div>
                    </div>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Phone Number -->
                <div class="input-group mb-3">
                    <input type="tel" 
                           name="phone" 
                           class="form-control @error('phone') is-invalid @enderror" 
                           placeholder="Phone Number (Optional)"
                           value="{{ old('phone') }}">
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-phone"></span>
                        </div>
                    </div>
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- Password -->
                <div class="input-group mb-3">
                    <input type="password" 
                           name="password" 
                           class="form-control @error('password') is-invalid @enderror" 
                           placeholder="Password"
                           required 
                           autocomplete="new-password"
                           minlength="8">
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- Confirm Password -->
                <div class="input-group mb-3">
                    <input type="password" 
                           name="password_confirmation" 
                           class="form-control" 
                           placeholder="Confirm Password"
                           required 
                           autocomplete="new-password"
                           minlength="8">
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                </div>
                
                <!-- Error Messages -->
                @if ($errors->any())
                    <div class="alert alert-danger mt-3">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Session Messages -->
                @if (session('error'))
                    <div class="alert alert-danger mt-3">
                        {{ session('error') }}
                    </div>
                @endif

                @if (session('status'))
                    <div class="alert alert-info mt-3">
                        {{ session('status') }}
                    </div>
                @endif
                
                <!-- Submit Button -->
                <div class="row mt-4">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-user-plus mr-2"></i>
                            Create Admin Account
                        </button>
                    </div>
                </div>
                
                <!-- Back to Login -->
                <div class="text-center mt-3">
                    <a href="{{ route('admin.login') }}" class="text-primary">
                        <i class="fas fa-arrow-left mr-1"></i>
                        Back to Login
                    </a>
                </div>
            </form>
            
            <!-- Additional Info -->
            <div class="text-center mt-4 pt-3 border-top">
                <p class="text-muted text-sm">
                    <i class="fas fa-info-circle text-info mr-1"></i>
                    @if(\App\Models\AdminUser::count() === 0)
                        First user will be Super Admin
                    @else
                        Registration available in development mode
                    @endif
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
    // Form submission animation
    $('form').on('submit', function() {
        const submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true);
        submitBtn.html('<i class="fas fa-spinner fa-spin mr-2"></i>Creating Account...');
    });
    
    // Password validation
    $('input[name="password_confirmation"]').on('keyup', function() {
        const password = $('input[name="password"]').val();
        const confirmPassword = $(this).val();
        
        if (password !== confirmPassword && confirmPassword !== '') {
            $(this).addClass('is-invalid');
            if (!$(this).next('.invalid-feedback').length) {
                $(this).after('<div class="invalid-feedback">Passwords do not match</div>');
            }
        } else {
            $(this).removeClass('is-invalid');
            $(this).next('.invalid-feedback').remove();
        }
    });
    
    // Phone number formatting (basic)
    $('input[name="phone"]').on('input', function() {
        let value = $(this).val().replace(/\D/g, '');
        if (value.startsWith('234')) {
            value = '+' + value;
        } else if (value.startsWith('0')) {
            value = '+234' + value.substring(1);
        } else if (value.length > 0 && !value.startsWith('+')) {
            value = '+234' + value;
        }
        $(this).val(value);
    });
});
</script>

</body>
</html>