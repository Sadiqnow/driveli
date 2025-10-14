@extends('layouts.app')

@section('title', 'Employment Reference Form')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Employment Reference Request</h4>
                </div>

                <div class="card-body">
                    <div class="alert alert-info">
                        <strong>Driver Information:</strong>
                        <ul class="mb-0 mt-2">
                            <li><strong>Name:</strong> {{ $relation->driver->full_name }}</li>
                            <li><strong>Driver ID:</strong> {{ $relation->driver->driver_id }}</li>
                            <li><strong>Position:</strong> Driver</li>
                        </ul>
                    </div>

                    <form action="{{ route('employment-feedback.submit', $token) }}" method="POST">
                        @csrf

                        <h5 class="mb-3">Employment Details</h5>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="employment_start_date">Employment Start Date</label>
                                    <input type="date" class="form-control @error('employment_start_date') is-invalid @enderror"
                                           id="employment_start_date" name="employment_start_date"
                                           value="{{ old('employment_start_date') }}">
                                    @error('employment_start_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="employment_end_date">Employment End Date (if applicable)</label>
                                    <input type="date" class="form-control @error('employment_end_date') is-invalid @enderror"
                                           id="employment_end_date" name="employment_end_date"
                                           value="{{ old('employment_end_date') }}">
                                    @error('employment_end_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="performance_rating">Performance Rating <span class="text-danger">*</span></label>
                            <select class="form-control @error('performance_rating') is-invalid @enderror"
                                    id="performance_rating" name="performance_rating" required>
                                <option value="">Select Rating</option>
                                <option value="excellent" {{ old('performance_rating') == 'excellent' ? 'selected' : '' }}>Excellent</option>
                                <option value="good" {{ old('performance_rating') == 'good' ? 'selected' : '' }}>Good</option>
                                <option value="average" {{ old('performance_rating') == 'average' ? 'selected' : '' }}>Average</option>
                                <option value="poor" {{ old('performance_rating') == 'poor' ? 'selected' : '' }}>Poor</option>
                                <option value="very_poor" {{ old('performance_rating') == 'very_poor' ? 'selected' : '' }}>Very Poor</option>
                            </select>
                            @error('performance_rating')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="reason_for_leaving">Reason for Leaving (if applicable)</label>
                            <textarea class="form-control @error('reason_for_leaving') is-invalid @enderror"
                                      id="reason_for_leaving" name="reason_for_leaving" rows="3"
                                      placeholder="Please provide details about why the employee left your company">{{ old('reason_for_leaving') }}</textarea>
                            @error('reason_for_leaving')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="feedback_notes">Additional Comments</label>
                            <textarea class="form-control @error('feedback_notes') is-invalid @enderror"
                                      id="feedback_notes" name="feedback_notes" rows="4"
                                      placeholder="Any additional feedback about the employee's performance, reliability, or conduct">{{ old('feedback_notes') }}</textarea>
                            @error('feedback_notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="alert alert-warning">
                            <strong>Privacy Notice:</strong> Your feedback will be kept confidential and used only for employment verification purposes.
                        </div>

                        <div class="form-group text-center">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-paper-plane"></i> Submit Reference
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .card-header {
        border-bottom: none;
    }

    .form-group label {
        font-weight: 600;
        color: #495057;
    }

    .btn-primary {
        padding: 12px 30px;
        font-size: 16px;
    }

    .alert-info ul {
        padding-left: 20px;
    }
</style>
@endpush
