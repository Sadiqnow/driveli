@extends('layouts.app')

@section('title', 'Feedback Submitted Successfully')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card text-center">
                <div class="card-body">
                    <div class="mb-4">
                        <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                    </div>

                    <h4 class="card-title text-success mb-3">Thank You!</h4>

                    <p class="card-text">
                        Your employment reference has been successfully submitted.
                        We appreciate your cooperation in helping us maintain high safety standards.
                    </p>

                    <div class="alert alert-info">
                        <strong>What happens next?</strong><br>
                        Your feedback will be reviewed by our team and used to verify the driver's employment history.
                        This helps ensure the safety and reliability of our transportation services.
                    </div>

                    <p class="text-muted">
                        You can now close this window or navigate away from this page.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .card {
        border: none;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .text-success {
        color: #28a745 !important;
    }

    .fas.fa-check-circle {
        color: #28a745;
    }
</style>
@endpush
