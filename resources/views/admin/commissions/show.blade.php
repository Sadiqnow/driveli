@extends('layouts.admin_cdn')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Commission Details</h1>
                </div>
                <div class="col-sm-6">
                    <a href="{{ route('commissions.index') }}" class="btn btn-secondary float-right">Back to List</a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Commission #{{ $commission->id }}</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <dl class="row">
                                        <dt class="col-sm-4">Driver:</dt>
                                        <dd class="col-sm-8">{{ $commission->driver->first_name ?? 'N/A' }} {{ $commission->driver->last_name ?? '' }}</dd>

                                        <dt class="col-sm-4">Match:</dt>
                                        <dd class="col-sm-8">{{ $commission->match->id ?? 'N/A' }}</dd>

                                        <dt class="col-sm-4">Amount:</dt>
                                        <dd class="col-sm-8">{{ $commission->amount }}</dd>

                                        <dt class="col-sm-4">Status:</dt>
                                        <dd class="col-sm-8">
                                            <span class="badge badge-{{ $commission->status === 'paid' ? 'success' : ($commission->status === 'pending' ? 'warning' : 'secondary') }}">
                                                {{ ucfirst($commission->status) }}
                                            </span>
                                        </dd>

                                        <dt class="col-sm-4">Paid At:</dt>
                                        <dd class="col-sm-8">{{ $commission->paid_at ? $commission->paid_at->format('M d, Y H:i') : 'N/A' }}</dd>
                                    </dl>
                                </div>
                                <div class="col-md-6">
                                    <div class="text-center">
                                        @if($commission->status !== 'paid')
                                        <form method="POST" action="{{ route('commissions.markAsPaid', $commission->id) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-success">Mark as Paid</button>
                                        </form>
                                        @else
                                        <p class="text-success">This commission has been paid.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
