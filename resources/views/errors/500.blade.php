@extends('layouts.app')

@section('title', '500 - Server Error')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body text-center">
                    <h1 class="display-1">500</h1>
                    <h2 class="mb-3">Server Error</h2>
                    <p class="mb-4">Sorry, something went wrong on our end.</p>
                    <a href="{{ url('/') }}" class="btn btn-primary">Go Home</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection