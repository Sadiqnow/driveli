@extends('layouts.admin_cdn')

@section('title', 'Request Matches')

@section('content_header')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center">
        <h1>Matches for Request #{{ $request->id }}</h1>
        <div>
            <a href="{{ route('admin.requests.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Requests
            </a>
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createMatchModal">
                <i class="fas fa-plus"></i> Create New Match
            </button>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Request Details Card -->
    <div class="card mb-4">
        <div class="card-header">
            <h3 class="card-title">Request Details</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Company:</strong> {{ $request->company->name ?? 'N/A' }}</p>
                    <p><strong>Request Type:</strong> {{ $request->request_type ?? 'General' }}</p>
                    <p><strong>Status:</strong> 
                        <span class="badge badge-{{ $request->status_badge }}">
                            {{ ucfirst($request->status) }}
                        </span>
                    </p>
                </div>
                <div class="col-md-6">
                    <p><strong>Created:</strong> {{ $request->created_at ? $request->created_at->format('d/m/Y H:i') : 'N/A' }}</p>
                    <p><strong>Priority:</strong> {{ $request->priority ?? 'Normal' }}</p>
                    <p><strong>Position:</strong> {{ $request->position_title ?? 'N/A' }}</p>
                </div>
            </div>
            @if($request->description)
            <div class="row mt-3">
                <div class="col-12">
                    <p><strong>Description:</strong></p>
                    <p class="text-muted">{{ $request->description }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Matches Table -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Driver Matches ({{ $matches->count() }})</h3>
        </div>
        <div class="card-body table-responsive">
            @if($matches->count() > 0)
            <table class="table table-bordered table-striped align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Match ID</th>
                        <th>Driver</th>
                        <th>Contact</th>
                        <th>Commission Rate</th>
                        <th>Status</th>
                        <th>Matched Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($matches as $match)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $match->match_id ?? 'N/A' }}</td>
                        <td>
                            <div class="driver-info">
                                <strong>{{ $match->driver->first_name ?? 'N/A' }} {{ $match->driver->last_name ?? '' }}</strong>
                                <br>
                                <small class="text-muted">ID: {{ $match->driver->id ?? 'N/A' }}</small>
                            </div>
                        </td>
                        <td>
                            <div class="contact-info">
                                <i class="fas fa-phone"></i> {{ $match->driver->phone ?? 'N/A' }}
                                <br>
                                <i class="fas fa-envelope"></i> {{ $match->driver->email ?? 'N/A' }}
                            </div>
                        </td>
                        <td>{{ $match->commission_rate ?? 0 }}%</td>
                        <td>
                            @php
                                $statusClass = match($match->status ?? 'pending') {
                                    'pending' => 'warning',
                                    'confirmed' => 'success', 
                                    'accepted' => 'info',
                                    'rejected' => 'danger',
                                    'cancelled' => 'secondary',
                                    default => 'secondary'
                                };
                            @endphp
                            <span class="badge badge-{{ $statusClass }}">
                                {{ ucfirst($match->status ?? 'pending') }}
                            </span>
                        </td>
                        <td>{{ $match->matched_at ? $match->matched_at->format('d/m/Y H:i') : 'N/A' }}</td>
                        <td>
                            <div class="btn-group" role="group">
                                @if(($match->status ?? 'pending') === 'pending')
                                    <form method="POST" action="{{ route('admin.matching.matches.confirm', $match->id) }}" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm" 
                                                onclick="return confirm('Confirm this match?')" 
                                                title="Confirm Match">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                @endif
                                
                                <form method="POST" action="{{ route('admin.matching.matches.cancel', $match->id) }}" style="display:inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-danger btn-sm" 
                                            onclick="return confirm('Cancel this match?')" 
                                            title="Cancel Match">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="text-center py-5">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <h5>No matches found</h5>
                <p class="text-muted">There are no driver matches for this request yet.</p>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#createMatchModal">
                    <i class="fas fa-plus"></i> Create First Match
                </button>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Create Match Modal -->
<div class="modal fade" id="createMatchModal" tabindex="-1" role="dialog" aria-labelledby="createMatchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createMatchModalLabel">Create New Match</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" action="{{ route('admin.requests.match', $request->id) }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="driver_id">Select Driver</label>
                        <select name="driver_id" id="driver_id" class="form-control" required>
                            <option value="">-- Select a Driver --</option>
                            <!-- Will be populated via AJAX -->
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="commission_rate">Commission Rate (%)</label>
                        <input type="number" name="commission_rate" id="commission_rate" 
                               class="form-control" min="0" max="100" step="0.1" 
                               value="10" required>
                        <small class="form-text text-muted">Enter the commission percentage (0-100)</small>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create Match
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
$(document).ready(function() {
    // Load available drivers when modal opens
    $('#createMatchModal').on('show.bs.modal', function () {
        loadAvailableDrivers();
    });
});

function loadAvailableDrivers() {
    const select = $('#driver_id');
    select.html('<option value="">Loading drivers...</option>');
    
    $.get(`{{ route('admin.requests.available-drivers') }}`, {
        request_id: {{ $request->id }}
    })
    .done(function(response) {
        if (response.success) {
            // For this example, we'll need to modify the backend to return driver options
            // For now, let's populate with a basic structure
            select.html('<option value="">-- Select a Driver --</option>');
            // Add available drivers here
        } else {
            select.html('<option value="">No drivers available</option>');
        }
    })
    .fail(function() {
        select.html('<option value="">Error loading drivers</option>');
    });
}
</script>
@endsection