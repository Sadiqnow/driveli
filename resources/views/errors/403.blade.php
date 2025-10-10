@extends('layouts.app')

@section('title', '403 - Forbidden')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body text-center">
                    <h1 class="display-1">403</h1>
                    <h2 class="mb-3">Forbidden</h2>
                    <p class="mb-4">You don't have permission to access this resource.</p>
                    <a href="{{ url('/') }}" class="btn btn-primary">Go Home</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection