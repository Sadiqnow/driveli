@extends('layouts.admin_cdn')

@section('title', 'Request Queue Management')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Request Queue Management</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ url('admin/requests') }}">Requests</a></li>
                    <li class="breadcrumb-item active">Queue Management</li>
                </ol>
            </div>
        </div>
    </div>
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Queue Statistics -->
        <div class="col-12">
            <div class="row mb-4">
                <div class="col-lg-2 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{ $queueStats['pending'] ?? 0 }}</h3>
                            <p>Pending</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ $queueStats['processing'] ?? 0 }}</h3>
                            <p>Processing</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-cogs"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{ $queueStats['completed'] ?? 0 }}</h3>
                            <p>Completed</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3>{{ $queueStats['urgent'] ?? 0 }}</h3>
                            <p>Urgent</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-6">
                    <div class="small-box bg-secondary">
                        <div class="inner">
                            <h3>{{ $queueStats['overdue'] ?? 0 }}</h3>
                            <p>Overdue</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-exclamation"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-6">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <h3>{{ number_format($queueStats['avg_processing_time'] ?? 0, 1) }}h</h3>
                            <p>Avg Time</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Queue Management Controls -->
        <div class="col-12">
            <div class="card card-outline card-primary mb-4">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-tools"></i> Queue Management Tools
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-2">
                            <button class="btn btn-success btn-block" id="processNextBtn">
                                <i class="fas fa-play"></i> Process Next in Queue
                            </button>
                        </div>
                        <div class="col-md-3 mb-2">
                            <button class="btn btn-warning btn-block" id="reorderQueueBtn">
                                <i class="fas fa-sort"></i> Reorder by Priority
                            </button>
                        </div>
                        <div class="col-md-3 mb-2">
                            <button class="btn btn-info btn-block" id="batchProcessBtn">
                                <i class="fas fa-layer-group"></i> Batch Process
                            </button>
                        </div>
                        <div class="col-md-3 mb-2">
                            <button class="btn btn-secondary btn-block" id="exportQueueBtn">
                                <i class="fas fa-download"></i> Export Queue
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Queue Status Tabs -->
        <div class="col-12">
            <div class="card card-primary card-tabs">
                <div class="card-header p-0 pt-1">
                    <ul class="nav nav-tabs" id="queueTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="pending-tab" data-toggle="pill" 
                               href="#pending" role="tab" aria-controls="pending" aria-selected="true">
                                <i class="fas fa-clock"></i> Pending Queue ({{ $queueStats['pending'] ?? 0 }})
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="processing-tab" data-toggle="pill" 
                               href="#processing" role="tab" aria-controls="processing" aria-selected="false">
                                <i class="fas fa-cogs"></i> Processing ({{ $queueStats['processing'] ?? 0 }})
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="completed-tab" data-toggle="pill" 
                               href="#completed" role="tab" aria-controls="completed" aria-selected="false">
                                <i class="fas fa-check-circle"></i> Completed ({{ $queueStats['completed'] ?? 0 }})
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="urgent-tab" data-toggle="pill" 
                               href="#urgent" role="tab" aria-controls="urgent" aria-selected="false">
                                <i class="fas fa-exclamation-triangle"></i> Urgent ({{ $queueStats['urgent'] ?? 0 }})
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="queueTabsContent">
                        <!-- Pending Queue Tab -->
                        <div class="tab-pane fade show active" id="pending" role="tabpanel" aria-labelledby="pending-tab">
                            <div class="row">
                                @foreach($pendingRequests as $index => $request)
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <div class="card card-outline {{ $request->priority === 'Urgent' ? 'card-danger' : 'card-warning' }}">
                                        <div class="card-header">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h5 class="card-title mb-0">
                                                    <span class="badge badge-secondary">{{ $index + 1 }}</span>
                                                    REQ-{{ str_pad($request->id, 4, '0', STR_PAD_LEFT) }}
                                                </h5>
                                                @if($request->priority === 'Urgent')
                                                    <span class="badge badge-danger">URGENT</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <h6><strong>{{ $request->company->name }}</strong></h6>
                                            <p class="mb-1"><strong>Job:</strong> {{ $request->location ?? 'N/A' }}</p>
                                            <p class="mb-1"><strong>Location:</strong> {{ $request->location }}</p>
                                            <p class="mb-1"><strong>Salary:</strong> {{ $request->salary_range ?? 'Not specified' }}</p>
                                            <p class="mb-1"><strong>Waiting:</strong> {{ $request->created_at->diffForHumans() }}</p>
                                            
                                            <div class="progress mb-2">
                                                <div class="progress-bar bg-warning" role="progressbar" 
                                                     style="width: 10%" aria-valuenow="10" aria-valuemin="0" aria-valuemax="100">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-footer">
                                            <div class="btn-group btn-group-sm w-100" role="group">
                                                <button type="button" class="btn btn-success process-request" 
                                                        data-request-id="{{ $request->id }}">
                                                    <i class="fas fa-play"></i> Process
                                                </button>
                                                <button type="button" class="btn btn-info move-up" 
                                                        data-request-id="{{ $request->id }}" 
                                                        {{ $index === 0 ? 'disabled' : '' }}>
                                                    <i class="fas fa-arrow-up"></i>
                                                </button>
                                                <button type="button" class="btn btn-secondary move-down" 
                                                        data-request-id="{{ $request->id }}"
                                                        {{ $index === count($pendingRequests) - 1 ? 'disabled' : '' }}>
                                                    <i class="fas fa-arrow-down"></i>
                                                </button>
                                                <a href="{{ route('admin.requests.show', $request->id) }}" 
                                                   class="btn btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                                
                                @if($pendingRequests->isEmpty())
                                <div class="col-12 text-center py-5">
                                    <i class="fas fa-check-circle fa-5x text-success mb-3"></i>
                                    <h4>Queue is Empty!</h4>
                                    <p class="text-muted">No requests are currently pending in the queue.</p>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Processing Tab -->
                        <div class="tab-pane fade" id="processing" role="tabpanel" aria-labelledby="processing-tab">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Request ID</th>
                                            <th>Company</th>
                                            <th>Job Type</th>
                                            <th>Assigned To</th>
                                            <th>Started</th>
                                            <th>Progress</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($processingRequests as $request)
                                        <tr>
                                            <td>REQ-{{ str_pad($request->id, 4, '0', STR_PAD_LEFT) }}</td>
                                            <td>{{ $request->company->name }}</td>
                                            <td>{{ $request->location ?? 'N/A' }}</td>
                                            <td>{{ $request->assignedAdmin->name ?? 'Unassigned' }}</td>
                                            <td>{{ $request->updated_at->diffForHumans() }}</td>
                                            <td>
                                                <div class="progress">
                                                    <div class="progress-bar bg-info" role="progressbar" 
                                                         style="width: {{ $request->progress_percentage ?? 50 }}%">
                                                        {{ $request->progress_percentage ?? 50 }}%
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-success complete-request" 
                                                            data-request-id="{{ $request->id }}">
                                                        <i class="fas fa-check"></i> Complete
                                                    </button>
                                                    <button type="button" class="btn btn-warning pause-request" 
                                                            data-request-id="{{ $request->id }}">
                                                        <i class="fas fa-pause"></i> Pause
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Completed Tab -->
                        <div class="tab-pane fade" id="completed" role="tabpanel" aria-labelledby="completed-tab">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Request ID</th>
                                            <th>Company</th>
                                            <th>Job Type</th>
                                            <th>Completed By</th>
                                            <th>Completion Date</th>
                                            <th>Processing Time</th>
                                            <th>Rating</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($completedRequests as $request)
                                        <tr>
                                            <td>REQ-{{ str_pad($request->id, 4, '0', STR_PAD_LEFT) }}</td>
                                            <td>{{ $request->company->name }}</td>
                                            <td>{{ $request->location ?? 'N/A' }}</td>
                                            <td>{{ $request->assignedAdmin->name ?? 'System' }}</td>
                                            <td>{{ $request->completed_at->format('M d, Y H:i') }}</td>
                                            <td>{{ $request->processing_time ?? 'N/A' }}</td>
                                            <td>
                                                @if($request->rating)
                                                    @for($i = 1; $i <= 5; $i++)
                                                        @if($i <= $request->rating)
                                                            <i class="fas fa-star text-warning"></i>
                                                        @else
                                                            <i class="far fa-star text-muted"></i>
                                                        @endif
                                                    @endfor
                                                @else
                                                    <span class="text-muted">Not rated</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.requests.show', $request->id) }}" 
                                                   class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Urgent Tab -->
                        <div class="tab-pane fade" id="urgent" role="tabpanel" aria-labelledby="urgent-tab">
                            <div class="alert alert-danger">
                                <h5><i class="icon fas fa-exclamation-triangle"></i> Urgent Requests!</h5>
                                These requests require immediate attention and should be prioritized.
                            </div>
                            
                            <div class="row">
                                @foreach($urgentRequests as $request)
                                <div class="col-md-6 mb-3">
                                    <div class="card card-outline card-danger">
                                        <div class="card-header bg-danger">
                                            <h5 class="card-title text-white mb-0">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                REQ-{{ str_pad($request->id, 4, '0', STR_PAD_LEFT) }} - URGENT
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <h6><strong>{{ $request->company->name }}</strong></h6>
                                            <p><strong>Job:</strong> {{ $request->location ?? 'N/A' }}</p>
                                            <p><strong>Location:</strong> {{ $request->location }}</p>
                                            <p><strong>Submitted:</strong> {{ $request->created_at->diffForHumans() }}</p>
                                            <p><strong>Deadline:</strong> {{ $request->deadline ?? 'ASAP' }}</p>
                                        </div>
                                        <div class="card-footer">
                                            <button type="button" class="btn btn-danger btn-sm process-urgent" 
                                                    data-request-id="{{ $request->id }}">
                                                <i class="fas fa-bolt"></i> Process Immediately
                                            </button>
                                            <a href="{{ route('admin.requests.show', $request->id) }}" 
                                               class="btn btn-outline-danger btn-sm">
                                                <i class="fas fa-eye"></i> View Details
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Queue Management Modal -->
<div class="modal fade" id="queueActionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="queueActionTitle">Queue Action</h4>
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="queueActionForm">
                <div class="modal-body">
                    <input type="hidden" id="actionRequestId" name="request_id">
                    <input type="hidden" id="queueAction" name="action">
                    
                    <div id="actionContent">
                        <!-- Dynamic content based on action -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="confirmQueueAction">Confirm</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Process request
    $('.process-request, .process-urgent').click(function() {
        const requestId = $(this).data('request-id');
        showQueueActionModal(requestId, 'process', 'Process Request');
    });

    // Complete request
    $('.complete-request').click(function() {
        const requestId = $(this).data('request-id');
        showQueueActionModal(requestId, 'complete', 'Complete Request');
    });

    // Move request up/down in queue
    $('.move-up').click(function() {
        const requestId = $(this).data('request-id');
        moveRequestInQueue(requestId, 'up');
    });

    $('.move-down').click(function() {
        const requestId = $(this).data('request-id');
        moveRequestInQueue(requestId, 'down');
    });

    // Batch process
    $('#batchProcessBtn').click(function() {
        if (confirm('Process the next 5 requests in the queue?')) {
            batchProcessRequests();
        }
    });

    // Reorder queue by priority
    $('#reorderQueueBtn').click(function() {
        if (confirm('Reorder the entire queue by priority? This will move all urgent requests to the top.')) {
            reorderQueueByPriority();
        }
    });

    function showQueueActionModal(requestId, action, title) {
        $('#actionRequestId').val(requestId);
        $('#queueAction').val(action);
        $('#queueActionTitle').text(title);
        
        let content = '';
        if (action === 'process') {
            content = `
                <div class="form-group">
                    <label>Assign to Administrator:</label>
                    <select class="form-control" name="assigned_to" required>
                        <option value="">Select Administrator</option>
                        @foreach($administrators as $admin)
                        <option value="{{ $admin->id }}">{{ $admin->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Processing Notes:</label>
                    <textarea class="form-control" name="notes" rows="3" 
                              placeholder="Add any processing notes..."></textarea>
                </div>
            `;
        } else if (action === 'complete') {
            content = `
                <div class="form-group">
                    <label>Completion Notes:</label>
                    <textarea class="form-control" name="completion_notes" rows="3" 
                              placeholder="Add completion summary..." required></textarea>
                </div>
                <div class="form-group">
                    <label>Success Rating (1-5):</label>
                    <select class="form-control" name="rating">
                        <option value="5">5 - Excellent</option>
                        <option value="4">4 - Good</option>
                        <option value="3">3 - Average</option>
                        <option value="2">2 - Poor</option>
                        <option value="1">1 - Failed</option>
                    </select>
                </div>
            `;
        }
        
        $('#actionContent').html(content);
        $('#queueActionModal').modal('show');
    }

    $('#queueActionForm').submit(function(e) {
        e.preventDefault();
        
        const formData = $(this).serialize() + '&_token={{ csrf_token() }}';
        
        $.ajax({
            url: '{{ route("admin.requests.queue-action") }}',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    $('#queueActionModal').modal('hide');
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('An error occurred while processing the action.');
            }
        });
    });

    function moveRequestInQueue(requestId, direction) {
        $.ajax({
            url: '{{ route("admin.requests.move-in-queue") }}',
            method: 'POST',
            data: {
                request_id: requestId,
                direction: direction,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            }
        });
    }

    function batchProcessRequests() {
        $.ajax({
            url: '{{ route("admin.requests.batch-process") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            }
        });
    }

    function reorderQueueByPriority() {
        $.ajax({
            url: '{{ route("admin.requests.reorder-queue") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            }
        });
    }
});
</script>
@stop