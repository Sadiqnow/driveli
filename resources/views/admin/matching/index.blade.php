@extends('layouts.admin_cdn')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1>Matching System</h1>
                <div>
                    <a href="{{ route('admin.matching.dashboard') }}" class="btn btn-info">
                        <i class="fas fa-chart-line"></i> Dashboard
                    </a>
                    <a href="{{ route('admin.matching.matches') }}" class="btn btn-primary">
                        <i class="fas fa-handshake"></i> View Matches
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <!-- Alert Messages -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            
            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle"></i> {{ session('warning') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
            <!-- Quick Actions -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-robot"></i> Auto Matching
                            </h3>
                        </div>
                        <div class="card-body">
                            <p>Automatically match drivers with company requests based on criteria.</p>
                            <p><small class="text-muted">Available: {{ $availableDrivers->count() }} drivers, {{ $pendingRequests->count() }} pending requests</small></p>
                            <form action="{{ route('admin.matching.auto-match') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success" 
                                    @if($availableDrivers->count() == 0 || $pendingRequests->count() == 0) disabled @endif>
                                    <i class="fas fa-play"></i> Start Auto Matching
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-hand-pointer"></i> Manual Matching
                            </h3>
                        </div>
                        <div class="card-body">
                            <p>Manually review and match drivers with company requests.</p>
                            <p><small class="text-muted">Available: {{ $availableDrivers->count() }} drivers, {{ $pendingRequests->count() }} pending requests</small></p>
                            <a href="#" class="btn btn-warning" data-toggle="modal" data-target="#manualMatchModal"
                                @if($availableDrivers->count() == 0 || $pendingRequests->count() == 0) disabled @endif>
                                <i class="fas fa-edit"></i> Manual Match
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-history"></i> Recent Matching Activity
                    </h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Driver</th>
                                    <th>Company</th>
                                    <th>Status</th>
                                    <th>Type</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentMatches as $match)
                                <tr>
                                    <td>{{ $match->created_at ? $match->created_at->format('M d, Y H:i') : 'N/A' }}</td>
                                    <td>
                                        @if($match->driver && isset($match->driver->first_name))
                                            <strong>{{ $match->driver->first_name }} {{ $match->driver->surname }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $match->driver->driver_id ?? 'N/A' }}</small>
                                        @else
                                            <span class="text-muted">Driver ID: {{ $match->driver_id }}</span>
                                            <br>
                                            <small class="text-muted">Driver not found</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($match->companyRequest && $match->companyRequest->company && isset($match->companyRequest->company->name))
                                            <strong>{{ $match->companyRequest->company->name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $match->companyRequest->location ?? 'N/A' }}</small>
                                        @elseif($match->companyRequest && isset($match->companyRequest->id))
                                            <strong>Request ID: {{ $match->companyRequest->id }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $match->companyRequest->location ?? 'N/A' }}</small>
                                        @else
                                            <span class="text-muted">Request ID: {{ $match->company_request_id }}</span>
                                            <br>
                                            <small class="text-muted">Request not found</small>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $statusColors = [
                                                'pending' => 'warning',
                                                'accepted' => 'info', 
                                                'completed' => 'success',
                                                'declined' => 'danger',
                                                'cancelled' => 'secondary'
                                            ];
                                            $statusColor = $statusColors[$match->status] ?? 'secondary';
                                        @endphp
                                        <span class="badge badge-{{ $statusColor }}">
                                            {{ ucfirst($match->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($match->auto_matched)
                                            <span class="badge badge-info">
                                                <i class="fas fa-robot"></i> Auto
                                            </span>
                                        @elseif($match->matched_by_admin)
                                            <span class="badge badge-warning">
                                                <i class="fas fa-user"></i> Manual
                                            </span>
                                        @else
                                            <span class="badge badge-secondary">System</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            @if($match->status === 'pending' && $match->match_id)
                                            <form method="POST" action="{{ route('admin.matching.matches.confirm', $match->match_id) }}" style="display: inline;">
                                                @csrf
                                                @method('POST')
                                                <button type="submit" class="btn btn-success btn-sm" title="Confirm Match"
                                                        onclick="return confirm('Are you sure you want to confirm this match?')">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.matching.matches.cancel', $match->match_id) }}" style="display: inline;">
                                                @csrf
                                                @method('POST')
                                                <button type="submit" class="btn btn-danger btn-sm" 
                                                        onclick="return confirm('Are you sure you want to cancel this match?')" 
                                                        title="Cancel Match">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                            @elseif(!$match->match_id)
                                            <span class="text-muted">Invalid Match ID</span>
                                            @else
                                            <span class="text-muted">{{ ucfirst($match->status) }}</span>
                                            @endif
                                            @if($match->match_id)
                                            <button type="button" class="btn btn-info btn-sm" 
                                                    title="Match ID: {{ $match->match_id }}"
                                                    onclick="alert('Match ID: {{ $match->match_id }}')">
                                                <i class="fas fa-info"></i>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">
                                        <i class="fas fa-info-circle"></i> No recent matching activity
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Manual Match Modal -->
<div class="modal fade" id="manualMatchModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Manual Driver Matching</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.matching.manual-match') }}" method="POST" id="manualMatchForm">
                @csrf
                <div class="modal-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Select Driver</label>
                                <select name="driver_id" class="form-control" required>
                                    <option value="">Choose Driver...</option>
                                    @forelse($availableDrivers as $driver)
                                        <option value="{{ $driver->id }}">
                                            {{ $driver->first_name }} {{ $driver->surname }} 
                                            ({{ $driver->driver_id }}) - {{ $driver->phone }}
                                        </option>
                                    @empty
                                        <option value="" disabled>No drivers available</option>
                                    @endforelse
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Select Company Request</label>
                                <select name="request_id" class="form-control" required>
                                    <option value="">Choose Request...</option>
                                    @forelse($pendingRequests as $request)
                                        <option value="{{ $request->id }}">
                                            {{ $request->company->name ?? 'Unknown Company' }} - 
                                            {{ Str::limit($request->description, 50) }}
                                        </option>
                                    @empty
                                        <option value="" disabled>No pending requests</option>
                                    @endforelse
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Commission Rate (%)</label>
                                <input type="number" name="commission_rate" class="form-control" 
                                       min="0" max="100" step="0.1" value="10" 
                                       placeholder="10.0">
                                <small class="text-muted">Default is 10%</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Priority</label>
                                <select name="priority" class="form-control">
                                    <option value="Normal">Normal</option>
                                    <option value="High">High</option>
                                    <option value="Urgent">Urgent</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Match Notes</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Optional notes about this match..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Match</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
$(document).ready(function() {
    // Manual match form validation
    $('#manualMatchForm').on('submit', function(e) {
        var driverId = $('select[name="driver_id"]').val();
        var requestId = $('select[name="request_id"]').val();
        var commissionRate = $('input[name="commission_rate"]').val();
        
        if (!driverId) {
            e.preventDefault();
            alert('Please select a driver');
            return false;
        }
        
        if (!requestId) {
            e.preventDefault();
            alert('Please select a company request');
            return false;
        }
        
        if (commissionRate && (commissionRate < 0 || commissionRate > 100)) {
            e.preventDefault();
            alert('Commission rate must be between 0 and 100');
            return false;
        }
        
        // Show loading state
        var submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Creating Match...');
        
        return true;
    });
    
    // Reset modal form when closed
    $('#manualMatchModal').on('hidden.bs.modal', function() {
        $('#manualMatchForm')[0].reset();
        $('#manualMatchForm button[type="submit"]').prop('disabled', false).html('Create Match');
    });
    
    // Auto-refresh page every 30 seconds to show new matches
    setInterval(function() {
        if (!$('#manualMatchModal').hasClass('show')) {
            location.reload();
        }
    }, 30000);
});
</script>
@endsection