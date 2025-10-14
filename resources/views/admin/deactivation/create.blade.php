@extends('layouts.admin')

@section('title', 'Create Deactivation Request')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Create Deactivation Request</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.deactivation.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>

                <form action="{{ route('admin.deactivation.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label for="user_type">User Type</label>
                            <select name="user_type" id="user_type" class="form-control @error('user_type') is-invalid @enderror" required>
                                <option value="">Select User Type</option>
                                <option value="driver" {{ old('user_type') === 'driver' ? 'selected' : '' }}>Driver</option>
                                <option value="company" {{ old('user_type') === 'company' ? 'selected' : '' }}>Company</option>
                            </select>
                            @error('user_type')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="user_id">Select User</label>
                            <select name="user_id" id="user_id" class="form-control @error('user_id') is-invalid @enderror" required>
                                <option value="">Select User</option>
                            </select>
                            @error('user_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="reason">Reason for Deactivation</label>
                            <textarea name="reason" id="reason" class="form-control @error('reason') is-invalid @enderror" rows="4" required>{{ old('reason') }}</textarea>
                            @error('reason')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Create Request
                        </button>
                        <a href="{{ route('admin.deactivation.index') }}" class="btn btn-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    $('#user_type').change(function() {
        var userType = $(this).val();
        var userSelect = $('#user_id');

        if (userType) {
            userSelect.prop('disabled', true);
            userSelect.html('<option value="">Loading...</option>');

            $.get('/api/admin/' + userType + 's/active', function(data) {
                userSelect.html('<option value="">Select User</option>');
                if (data.success && data.data) {
                    data.data.forEach(function(user) {
                        var name = userType === 'driver' ? user.full_name : user.name;
                        userSelect.append('<option value="' + user.id + '">' + name + '</option>');
                    });
                }
                userSelect.prop('disabled', false);
            }).fail(function() {
                userSelect.html('<option value="">Error loading users</option>');
                userSelect.prop('disabled', false);
            });
        } else {
            userSelect.html('<option value="">Select User Type First</option>');
        }
    });
});
</script>
@endsection
