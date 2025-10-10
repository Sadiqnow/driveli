@extends('layouts.admin_cdn')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1>Driver Matches</h1>
                <div>
                    <a href="{{ route('admin.matching.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Matching
                    </a>
                    <a href="{{ route('admin.matching.dashboard') }}" class="btn btn-info">
                        <i class="fas fa-chart-line"></i> Dashboard
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <!-- Filter Section -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-filter"></i> Filter Matches
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.matching.matches') }}">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="status" class="form-control">
                                        <option value="">All Statuses</option>
                                        <option value="pending">Pending</option>
                                        <option value="confirmed">Confirmed</option>
                                        <option value="active">Active</option>
                                        <option value="completed">Completed</option>
                                        <option value="cancelled">Cancelled</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Date Range</label>
                                    <select name="date_range" class="form-control">
                                        <option value="">All Time</option>
                                        <option value="today">Today</option>
                                        <option value="week">This Week</option>
                                        <option value="month">This Month</option>
                                        <option value="quarter">This Quarter</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Driver</label>
                                    <input type="text" name="driver_search" class="form-control" placeholder="Search driver...">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Company</label>
                                    <input type="text" name="company_search" class="form-control" placeholder="Search company...">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                                <a href="{{ route('admin.matching.matches') }}" class="btn btn-secondary">
                                    <i class="fas fa-refresh"></i> Reset
                                </a>
                                <div class="float-right">
                                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#exportModal">
                                        <i class="fas fa-download"></i> Export
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Matches Table -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-handshake"></i> All Matches
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-info">Total: {{ $matches->total() ?? 0 }}</span>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>Match ID</th>
                                <th>Driver</th>
                                <th>Company</th>
                                <th>Request Details</th>
                                <th>Match Score</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($matches as $match)
                            <tr>
                                <td>
                                    <strong>{{ $match->match_id }}</strong>
                                </td>
                                <td>
                                    @if($match->driver)
                                        <div class="user-block">
                                            <span class="username">{{ $match->driver->first_name }} {{ $match->driver->surname }}</span>
                                            <span class="description">{{ $match->driver->driver_id }}</span>
                                        </div>
                                    @else
                                        <span class="text-muted">Driver not found</span>
                                    @endif
                                </td>
                                <td>
                                    @if($match->companyRequest && $match->companyRequest->company)
                                        <div>
                                            <strong>{{ $match->companyRequest->company->name }}</strong><br>
                                            <small class="text-muted">{{ $match->companyRequest->company->location ?? 'N/A' }}</small>
                                        </div>
                                    @elseif($match->companyRequest)
                                        <div>
                                            <strong>Request #{{ $match->companyRequest->id }}</strong><br>
                                            <small class="text-muted">Company data missing</small>
                                        </div>
                                    @else
                                        <span class="text-muted">Request not found</span>
                                    @endif
                                </td>
                                <td>
                                    @if($match->companyRequest)
                                        <div>
                                            <strong>{{ $match->companyRequest->location ?? 'N/A' }}</strong><br>
                                            <small class="text-muted">{{ Str::limit($match->companyRequest->description ?? '', 50) }}</small>
                                        </div>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="progress progress-sm">
                                        <div class="progress-bar bg-success" style="width: 85%"></div>
                                    </div>
                                    <small>85% Match</small>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $match->status_color ?? 'secondary' }}">
                                        {{ ucfirst($match->status) }}
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">{{ $match->created_at ? $match->created_at->diffForHumans() : 'N/A' }}</small>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button class="btn btn-sm btn-info" data-toggle="tooltip" title="View Details" 
                                                data-action="view" data-match-id="{{ $match->match_id }}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        @if($match->status === 'pending')
                                        <form method="POST" action="{{ route('admin.matching.matches.confirm', $match->match_id) }}" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" data-toggle="tooltip" title="Confirm Match">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.matching.matches.cancel', $match->match_id) }}" style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger" data-toggle="tooltip" title="Cancel Match"
                                                    onclick="return confirm('Are you sure you want to cancel this match?')">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fas fa-info-circle"></i>
                                    No matches found. 
                                    <a href="{{ route('admin.matching.index') }}">Create your first match</a>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <!-- Pagination would go here -->
                <div class="card-footer clearfix">
                    <div class="float-left">
                        @if($matches->count() > 0)
                            Showing {{ $matches->firstItem() }} to {{ $matches->lastItem() }} of {{ $matches->total() }} entries
                        @else
                            Showing 0 to 0 of 0 entries
                        @endif
                    </div>
                    <div class="float-right">
                        {{ $matches->links() ?? '' }}
                    </div>
                </div>
            </div>

            <!-- Match Statistics Cards -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ $stats['total'] ?? 0 }}</h3>
                            <p>Total Matches</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-handshake"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{ $stats['pending'] ?? 0 }}</h3>
                            <p>Pending Matches</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{ $stats['accepted'] ?? 0 }}</h3>
                            <p>Accepted Matches</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{ $stats['completed'] ?? 0 }}</h3>
                            <p>Completed</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Export Matches</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="form-group">
                        <label>Export Format</label>
                        <select class="form-control" name="format">
                            <option value="csv">CSV</option>
                            <option value="excel">Excel</option>
                            <option value="pdf">PDF</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Date Range</label>
                        <select class="form-control" name="date_range">
                            <option value="all">All Time</option>
                            <option value="today">Today</option>
                            <option value="week">This Week</option>
                            <option value="month">This Month</option>
                            <option value="custom">Custom Range</option>
                        </select>
                    </div>
                    <div class="form-group" id="customDateRange" style="display: none;">
                        <div class="row">
                            <div class="col-md-6">
                                <label>From Date</label>
                                <input type="date" class="form-control" name="from_date">
                            </div>
                            <div class="col-md-6">
                                <label>To Date</label>
                                <input type="date" class="form-control" name="to_date">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Include Columns</label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" checked>
                                    <label class="form-check-label">Match ID</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" checked>
                                    <label class="form-check-label">Driver Details</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" checked>
                                    <label class="form-check-label">Company Details</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" checked>
                                    <label class="form-check-label">Match Score</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" checked>
                                    <label class="form-check-label">Status</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" checked>
                                    <label class="form-check-label">Dates</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary">
                    <i class="fas fa-download"></i> Export
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Match Details Modal Template -->
<div class="modal fade" id="matchDetailsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Match Details</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Match details will be loaded here via AJAX -->
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin"></i> Loading...
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script>
$(document).ready(function() {
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Handle custom date range visibility
    $('select[name="date_range"]').change(function() {
        if ($(this).val() === 'custom') {
            $('#customDateRange').show();
        } else {
            $('#customDateRange').hide();
        }
    });
    
    // Handle match actions
    $('.btn-group .btn').click(function(e) {
        e.preventDefault();
        var action = $(this).data('action');
        var matchId = $(this).data('match-id');
        
        // Handle different actions
        switch(action) {
            case 'view':
                viewMatchDetails(matchId);
                break;
            case 'confirm':
                confirmMatch(matchId);
                break;
            case 'cancel':
                cancelMatch(matchId);
                break;
        }
    });
    
    function viewMatchDetails(matchId) {
        $('#matchDetailsModal').modal('show');
        // Load match details via AJAX
    }
    
    function confirmMatch(matchId) {
        if (confirm('Are you sure you want to confirm this match?')) {
            // Send AJAX request to confirm match
        }
    }
    
    function cancelMatch(matchId) {
        if (confirm('Are you sure you want to cancel this match?')) {
            // Send AJAX request to cancel match
        }
    }
});
</script>
@endsection