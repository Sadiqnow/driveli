@extends('layouts.app')

@section('title', 'Company Login')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-building"></i> Company Login</h4>
                </div>
                <div class="card-body">
                    @if(session('message'))
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> {{ session('message') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <h6><i class="fas fa-exclamation-triangle"></i> Login Failed</h6>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('company.login') }}">
                        @csrf

                        <div class="form-group mb-3">
                            <label for="email"><i class="fas fa-envelope"></i> Email Address</label>
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}" 
                                   required 
                                   autofocus
                                   placeholder="Enter your company email">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="password"><i class="fas fa-lock"></i> Password</label>
                            <div class="input-group">
                                <input type="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       id="password" 
                                       name="password" 
                                       required
                                       placeholder="Enter your password">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                                        <i class="fas fa-eye" id="passwordIcon"></i>
                                    </button>
                                </div>
                            </div>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                <label class="form-check-label" for="remember">
                                    Remember me for 30 days
                                </label>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt"></i> Login to Company Portal
                            </button>
                        </div>
                    </form>

                    <hr>

                    <div class="text-center">
                        <p class="mb-2">Don't have a company account?</p>
                        <a href="{{ route('company.register') }}" class="btn btn-outline-primary">
                            <i class="fas fa-user-plus"></i> Register Your Company
                        </a>
                    </div>

                    <hr>

                    <div class="row text-center">
                        <div class="col-md-6">
                            <p class="small text-muted">
                                <i class="fas fa-question-circle"></i> Need Help?<br>
                                <a href="mailto:support@drivelink.com">support@drivelink.com</a>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="small text-muted">
                                <i class="fas fa-phone"></i> Call Support<br>
                                <a href="tel:+2341234567890">+234 123 456 7890</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Toggle password visibility
    $('#togglePassword').click(function() {
        const passwordField = $('#password');
        const passwordIcon = $('#passwordIcon');
        
        if (passwordField.attr('type') === 'password') {
            passwordField.attr('type', 'text');
            passwordIcon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            passwordField.attr('type', 'password');
            passwordIcon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // Form validation
    $('form').submit(function(e) {
        let isValid = true;
        
        // Check email
        const email = $('#email').val();
        if (!email || !email.includes('@')) {
            $('#email').addClass('is-invalid');
            isValid = false;
        } else {
            $('#email').removeClass('is-invalid');
        }
        
        // Check password
        const password = $('#password').val();
        if (!password || password.length < 6) {
            $('#password').addClass('is-invalid');
            isValid = false;
        } else {
            $('#password').removeClass('is-invalid');
        }
        
        if (!isValid) {
            e.preventDefault();
        }
    });
    
    // Remove validation errors on input
    $('#email, #password').on('input', function() {
        $(this).removeClass('is-invalid');
    });
});
</script>
@endsection