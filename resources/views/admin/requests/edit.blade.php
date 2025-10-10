@extends('layouts.admin_cdn')

@section('title', 'Edit Request')

@section('content_header', 'Edit Request')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ url('admin/requests') }}">Requests</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">Edit Request #{{ $request->id }}</h3>
            </div>
            
            <form action="{{ route('admin.requests.update', $request->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="form-group">
                        <label for="company_id">Company <span class="text-danger">*</span></label>
                        <select class="form-control @error('company_id') is-invalid @enderror" id="company_id" name="company_id" required>
                            <option value="">Select Company</option>
                            @if(isset($companies))
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}" {{ old('company_id', $request->company_id) == $company->id ? 'selected' : '' }}>
                                        {{ $company->name }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        @error('company_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="request_type">Request Type</label>
                        <select class="form-control @error('request_type') is-invalid @enderror" id="request_type" name="request_type">
                            <option value="">Select Request Type</option>
                            <option value="hire" {{ old('request_type', $request->request_type) == 'hire' ? 'selected' : '' }}>Hire Driver</option>
                            <option value="replacement" {{ old('request_type', $request->request_type) == 'replacement' ? 'selected' : '' }}>Driver Replacement</option>
                            <option value="general" {{ old('request_type', $request->request_type) == 'general' ? 'selected' : '' }}>General Request</option>
                        </select>
                        @error('request_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="driver_id">Assign Driver (Optional)</label>
                        <select class="form-control @error('driver_id') is-invalid @enderror" id="driver_id" name="driver_id">
                            <option value="">Select Driver</option>
                            @if(isset($drivers))
                                @foreach($drivers as $driver)
                                    <option value="{{ $driver->id }}" {{ old('driver_id', $request->driver_id) == $driver->id ? 'selected' : '' }}>
                                        {{ $driver->full_name }} ({{ $driver->phone }})
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        @error('driver_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="4" 
                                  placeholder="Enter request details...">{{ old('description', $request->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="status">Status</label>
                        <select class="form-control @error('status') is-invalid @enderror" id="status" name="status">
                            <option value="pending" {{ old('status', $request->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ old('status', $request->status) == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ old('status', $request->status) == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Request
                    </button>
                    <a href="{{ route('admin.requests.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection