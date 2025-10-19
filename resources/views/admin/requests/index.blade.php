@extends('layouts.admin_cdn')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1>Company Requests</h1>
                <a href="{{ route('admin.requests.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Request
                </a>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <!-- Search & Filters -->
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.requests.index') }}" class="row g-2">
                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control" placeholder="Search by request ID, company..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <select name="status" class="form-control">
                                <option value="">All Status</option>
                                <option value="pending" {{ request('status')=='pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ request('status')=='approved' ? 'selected' : '' }}>Approved</option>
                                <option value="rejected" {{ request('status')=='rejected' ? 'selected' : '' }}>Rejected</option>
                                <option value="cancelled" {{ request('status')=='cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-success w-100"><i class="fas fa-search"></i> Filter</button>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('admin.requests.index') }}" class="btn btn-secondary w-100">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Requests Table -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">All Company Requests</h3>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Request ID</th>
                                <th>Company</th>
                                <th>Request Type</th>
                                <th>Status</th>
                                <th>Created Date</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($requests as $request)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $request->id }}</td>
                                <td>{{ $request->company->name ?? 'N/A' }}</td>
                                <td>{{ $request->request_type ?? 'General' }}</td>
                                <td>
                                    @if($request->status == 'pending')
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @elseif($request->status == 'approved')
                                        <span class="badge bg-success">Approved</span>
                                    @elseif($request->status == 'rejected')
                                        <span class="badge bg-danger">Rejected</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($request->status) }}</span>
                                    @endif
                                </td>
                                <td>{{ $request->created_at ? $request->created_at->format('d/m/Y H:i') : 'N/A' }}</td>
                                <td class="text-center">
                                    <a href="{{ route('admin.requests.show', $request->id) }}" class="btn btn-info btn-sm" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.requests.edit', $request->id) }}" class="btn btn-primary btn-sm" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if($request->status == 'pending')
                                        @can('approve_requests')
                                        <form action="{{ route('admin.requests.approve', $request->id) }}" method="POST" style="display:inline-block">
                                            @csrf
                                            <button class="btn btn-success btn-sm" onclick="return confirm('Approve this request?')" title="Approve">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                        @endcan
                                        @can('reject_requests')
                                        <form action="{{ route('admin.requests.reject', $request->id) }}" method="POST" style="display:inline-block">
                                            @csrf
                                            <button class="btn btn-warning btn-sm" onclick="return confirm('Reject this request?')" title="Reject">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                        @endcan
                                    @endif
                                    <form action="{{ route('admin.requests.destroy', $request->id) }}" method="POST" style="display:inline-block">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this request?')" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center">No requests found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    @if(isset($requests) && method_exists($requests, 'links'))
                        {{ $requests->links('pagination::bootstrap-5') }}
                    @endif
                </div>
            </div>
        </div>
    </section>
</div>
@endsection