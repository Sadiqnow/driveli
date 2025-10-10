@extends('layouts.admin_cdn')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid d-flex justify-content-between">
            <h1>Request Details</h1>
            <a href="{{ route('admin.requests.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Request Information -->
                <div class="col-md-8">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Request Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Request ID:</strong> {{ $request->id }}</p>
                                    <p><strong>Company:</strong> {{ $request->company->name ?? 'N/A' }}</p>
                                    <p><strong>Request Type:</strong> {{ $request->request_type ?? 'General' }}</p>
                                    <p><strong>Status:</strong> 
                                        @if($request->status == 'pending')
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        @elseif($request->status == 'approved')
                                            <span class="badge bg-success">Approved</span>
                                        @elseif($request->status == 'rejected')
                                            <span class="badge bg-danger">Rejected</span>
                                        @else
                                            <span class="badge bg-secondary">{{ ucfirst($request->status) }}</span>
                                        @endif
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Created:</strong> {{ $request->created_at ? $request->created_at->format('d/m/Y H:i') : 'N/A' }}</p>
                                    <p><strong>Updated:</strong> {{ $request->updated_at ? $request->updated_at->format('d/m/Y H:i') : 'N/A' }}</p>
                                    @if($request->driver_id)
                                        <p><strong>Assigned Driver:</strong> {{ $request->driver->full_name ?? 'N/A' }}</p>
                                    @endif
                                </div>
                            </div>
                            
                            @if($request->description)
                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <p><strong>Description:</strong></p>
                                        <p class="bg-light p-3 rounded">{{ $request->description }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="col-md-4">
                    <div class="card card-secondary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">Actions</h3>
                        </div>
                        <div class="card-body">
                            @if($request->status == 'pending')
                                <form action="{{ route('admin.requests.approve', $request->id) }}" method="POST" class="mb-2">
                                    @csrf
                                    <button class="btn btn-success btn-block" onclick="return confirm('Approve this request?')">
                                        <i class="fas fa-check"></i> Approve Request
                                    </button>
                                </form>
                                <form action="{{ route('admin.requests.reject', $request->id) }}" method="POST" class="mb-2">
                                    @csrf
                                    <button class="btn btn-warning btn-block" onclick="return confirm('Reject this request?')">
                                        <i class="fas fa-times"></i> Reject Request
                                    </button>
                                </form>
                            @endif
                            
                            <a href="{{ route('admin.requests.edit', $request->id) }}" class="btn btn-primary btn-block mb-2">
                                <i class="fas fa-edit"></i> Edit Request
                            </a>
                            
                            <form action="{{ route('admin.requests.destroy', $request->id) }}" method="POST">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-block" onclick="return confirm('Are you sure you want to delete this request?')">
                                    <i class="fas fa-trash"></i> Delete Request
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection