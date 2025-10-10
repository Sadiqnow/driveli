@extends('layouts.app')

@section('title', 'Company Portal - Coming Soon')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="card">
                <div class="card-body py-5">
                    <div class="mb-4">
                        <i class="fas fa-building fa-5x text-primary mb-3"></i>
                        <h1 class="display-4 text-primary">Coming Soon</h1>
                        <h3 class="text-muted">Company Portal</h3>
                    </div>
                    
                    <p class="lead mb-4">
                        We're working hard to bring you an amazing company portal experience. 
                        Our team is putting the finishing touches on features that will help you 
                        manage your drivers and transportation needs more efficiently.
                    </p>
                    
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <i class="fas fa-users fa-2x text-success mb-2"></i>
                                    <h5>Driver Management</h5>
                                    <p class="small text-muted">Manage your driver fleet with ease</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <i class="fas fa-chart-bar fa-2x text-info mb-2"></i>
                                    <h5>Analytics</h5>
                                    <p class="small text-muted">Track performance and efficiency</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <i class="fas fa-handshake fa-2x text-warning mb-2"></i>
                                    <h5>Driver Matching</h5>
                                    <p class="small text-muted">Find the perfect drivers for your needs</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <p class="text-muted">Register your company or login to access the available features!</p>
                        <div class="d-flex gap-3 justify-content-center">
                            <a href="{{ route('company.register') }}" class="btn btn-primary btn-lg px-4">
                                <i class="fas fa-building"></i> Register Your Company
                            </a>
                            <a href="{{ route('company.login') }}" class="btn btn-outline-primary btn-lg px-4">
                                <i class="fas fa-sign-in-alt"></i> Company Login
                            </a>
                        </div>
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="row text-center">
                        <div class="col-md-6">
                            <h6><i class="fas fa-envelope text-primary"></i> Questions?</h6>
                            <p class="small">Contact us at <a href="mailto:support@drivelink.com">support@drivelink.com</a></p>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="fas fa-phone text-primary"></i> Need Help?</h6>
                            <p class="small">Call us at <a href="tel:+2341234567890">+234 123 456 7890</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
// Add some animation effects
$(document).ready(function() {
    $('.card').hide().fadeIn(1000);
    
    // Animate icons on hover
    $('.fa-users, .fa-chart-bar, .fa-handshake').hover(
        function() {
            $(this).addClass('animated pulse');
        },
        function() {
            $(this).removeClass('animated pulse');
        }
    );
});
</script>
@endsection
@endsection