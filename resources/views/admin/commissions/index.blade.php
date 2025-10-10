@extends('layouts.admin_cdn')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Commissions Management</h1>
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
                            <h3 class="card-title">Commission Records</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Driver</th>
                                            <th>Match</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Paid At</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($commissions as $commission)
                                        <tr>
                                            <td>{{ $commission->id }}</td>
                                            <td>{{ $commission->driver->first_name ?? 'N/A' }} {{ $commission->driver->last_name ?? '' }}</td>
                                            <td>{{ $commission->match->id ?? 'N/A' }}</td>
                                            <td>{{ $commission->amount }}</td>
                                            <td>
                                                <span class="badge badge-{{ $commission->status === 'paid' ? 'success' : ($commission->status === 'pending' ? 'warning' : 'secondary') }}">
                                                    {{ ucfirst($commission->status) }}
                                                </span>
                                            </td>
                                            <td>{{ $commission->paid_at ? $commission->paid_at->format('M d, Y') : 'N/A' }}</td>
                                            <td>
                                                <a href="{{ route('commissions.show', $commission->id) }}" class="btn btn-sm btn-info">View</a>
                                                @if($commission->status !== 'paid')
                                                <form method="POST" action="{{ route('commissions.markAsPaid', $commission->id) }}" style="display: inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success">Mark Paid</button>
                                                </form>
                                                @endif
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="7" class="text-center">No commissions found.</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            {{ $commissions->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
