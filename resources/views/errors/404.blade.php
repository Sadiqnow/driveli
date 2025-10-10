@extends('layouts.app')

@section('title', '404 - Page Not Found')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body text-center">
                    <h1 class="display-1">404</h1>
                    <h2 class="mb-3">Page Not Found</h2>
                    <p class="mb-4">Sorry, the page you are looking for could not be found.</p>
                    <a href="{{ url('/') }}" class="btn btn-primary">Go Home</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection