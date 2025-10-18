@extends('layouts.admin_master')

@section('title', 'Superadmin - OCR Verification Dashboard')

@section('content_header')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">OCR Verification Dashboard</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.superadmin.dashboard') }}">Super Admin</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.superadmin.drivers.index') }}">Drivers</a></li>
                        <li class="breadcrumb-item active">OCR Verification</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid">
        <!-- OCR Statistics Cards -->
        <div class="row mb-4">
            <div class="col-lg-2 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $stats['total_processed'] ?? 0 }}</h3>
                        <p>Total Processed</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <a href="{{ route('admin.superadmin.drivers.index', ['ocr_status' => 'all']) }}" class="small-box-footer">
                        View All <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-2 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $stats['passed'] ?? 0 }}</h3>
                        <p>Passed</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <a href="{{ route('admin.superadmin.drivers.index', ['ocr_status' => 'passed']) }}" class="small-box-footer">
                        View Passed <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-2 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $stats['pending'] ?? 0 }}</h3>
                        <p>Pending</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <a href="{{ route('admin.superadmin.drivers.index', ['ocr_status' => 'pending']) }}" class="small-box-footer">
                        View Pending <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-2 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>{{ $stats['failed'] ?? 0 }}</h3>
                        <p>Failed</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <a href="{{ route('admin.superadmin.drivers.index', ['ocr_status' => 'failed']) }}" class="small-box-footer">
                        View Failed <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-2 col-6">
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h3>{{ number_format($stats['accuracy_rate'] ?? 0, 1) }}%</h3>
                        <p>Accuracy Rate</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-bullseye"></i>
                    </div>
                    <div class="small-box-footer">&nbsp;</div>
                </div>
            </div>
            <div class="col-lg-2 col-6">
                <div class="small-box bg-secondary">
                    <div class="inner">
                        <h3>{{ $stats['daily_processed'] ?? 0 }}</h3>
                        <p>Processed Today</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div class="small-box-footer">&nbsp;</div>
                </div>
            </div>
        </div>

        <!-- OCR Performance Metrics -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-chart-line mr-1"></i>
                            OCR Performance Overview
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6 text-center">
                                <div class="text-success">
                                    <span class="h4">{{ number_format((($stats['passed'] ?? 0) / max(($stats['total_processed'] ?? 1), 1)) * 100, 1) }}%</span>
                                </div>
                                <div class="text-muted">Success Rate</div>
                            </div>
                            <div class="col-6 text-center">
                                <div class="text-info">
                                    <span class="h4">{{ $stats['processing_speed'] ?? 0 }}s</span>
                                </div>
                                <div class="text-muted">Avg Processing Time</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-tasks mr-1"></i>
                            Queue Status
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6 text-center">
                                <div class="text-warning">
                                    <span class="h4">{{ $stats['queue_size'] ?? 0 }}</span>
                                </div>
                                <div class="text-muted">In Queue</div>
                            </div>
                            <div class="col-6 text-center">
                                <div class="text-primary">
                                    <span class="h4">{{ $stats['processing_speed'] ?? 0 }}</span>
                                </div>
                                <div class="text-muted">Processing</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters and Actions -->
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-6">
                        <h3 class="card-title">OCR Verification Queue</h3>
                    </div>
                    <div class="col-md-6 text-right">
                        <button type="button" class="btn btn-success" onclick="bulkOCRVerification()">
                            <i class="fas fa-play"></i> Process Queue
                        </button>
                        <button type="button" class="btn btn-info" onclick="exportOCRReport()">
                            <i class="fas fa-download"></i> Export Report
                        </button>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card-header">
                <form method="GET" action="{{ route('admin.superadmin.drivers.ocr-verification') }}" class="row g-2">
                    <div class="col-md-3">
                        <label for="ocr-search" class="visually-hidden">Search drivers</label>
                        <input type="text"
                               id="ocr-search"
                               name="search"
                               class="form-control"
                               placeholder="Search by name, ID..."
                               value="{{ request('search') }}"
                               aria-describedby="ocr-search-help">
                        <div id="ocr-search-help" class="visually-hidden">Enter text to search for drivers by name or ID</div>
                    </div>
                    <div class="col-md-2">
                        <label for="ocr-status-filter" class="visually-hidden">Filter by OCR status</label>
                        <select id="ocr-status-filter" name="ocr_status" class="form-control" aria-label="Filter by OCR verification status">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('ocr_status')=='pending' ? 'selected' : '' }}>Pending</option>
                            <option value="passed" {{ request('ocr_status')=='passed' ? 'selected' : '' }}>Passed</option>
                            <option value="failed" {{ request('ocr_status')=='failed' ? 'selected' : '' }}>Failed</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="document-type-filter" class="visually-hidden">Filter by document type</label>
                        <select id="document-type-filter" name="document_type" class="form-control" aria-label="Filter by document type">
                            <option value="">All Documents</option>
                            <option value="nin" {{ request('document_type')=='nin' ? 'selected' : '' }}>NIN</option>
                            <option value="frsc" {{ request('document_type')=='frsc' ? 'selected' : '' }}>FRSC License</option>
                            <option value="both" {{ request('document_type')=='both' ? 'selected' : '' }}>Both</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-success w-100" aria-label="Search OCR verifications">
                            <i class="fas fa-search" aria-hidden="true"></i>
                            <span class="visually-hidden">Search</span>
                        </button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('admin.superadmin.drivers.ocr-verification') }}" class="btn btn-secondary w-100">Reset</a>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-info w-100" data-toggle="modal" data-target="#bulkOCRModal">
                            <i class="fas fa-tasks"></i>
                        </button>
                    </div>
                </form>
            </div>

            <!-- OCR Verification Table -->
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th width="30">
                                <input type="checkbox" id="select-all-ocr">
                            </th>
                            <th>Driver ID</th>
                            <th>Name</th>
                            <th>Document Type</th>
                            <th>OCR Status</th>
                            <th>Match Score</th>
                            <th>Processing Time</th>
                            <th>Submitted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($drivers ?? [] as $driver)
                            <tr>
                                <td>
                                    <input type="checkbox" class="ocr-checkbox" value="{{ $driver->id }}">
                                </td>
                                <td>
                                    <a href="{{ route('admin.superadmin.drivers.show', $driver) }}">
                                        {{ $driver->driver_id }}
                                    </a>
                                </td>
                                <td>{{ $driver->full_name }}</td>
                                <td>
                                    @if($driver->nin_document && $driver->frsc_document)
                                        <span class="badge badge-info">NIN + FRSC</span>
                                    @elseif($driver->nin_document)
                                        <span class="badge badge-primary">NIN</span>
                                    @elseif($driver->frsc_document)
                                        <span class="badge badge-secondary">FRSC</span>
                                    @else
                                        <span class="badge badge-warning">None</span>
                                    @endif
                                </td>
                                <td>
                                    @if($driver->ocr_verification_status == 'passed')
                                        <span class="badge badge-success">
                                            <i class="fas fa-check"></i> Passed
                                        </span>
                                    @elseif($driver->ocr_verification_status == 'failed')
                                        <span class="badge badge-danger">
                                            <i class="fas fa-times"></i> Failed
                                        </span>
                                    @else
                                        <span class="badge badge-warning">
                                            <i class="fas fa-clock"></i> Pending
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($driver->nin_ocr_match_score || $driver->frsc_ocr_match_score)
                                        <div class="text-center">
                                            @if($driver->nin_ocr_match_score)
                                                <small class="d-block">NIN: {{ $driver->nin_ocr_match_score }}%</small>
                                            @endif
                                            @if($driver->frsc_ocr_match_score)
                                                <small class="d-block">FRSC: {{ $driver->frsc_ocr_match_score }}%</small>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($driver->ocr_processed_at)
                                        <span class="text-muted">{{ $driver->ocr_processed_at->diffForHumans() }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>{{ $driver->created_at->format('M d, Y') }}</td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-info btn-sm" onclick="viewOCRDetails({{ $driver->id }})">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        @if($driver->ocr_verification_status == 'pending')
                                            <button type="button" class="btn btn-success btn-sm" onclick="processOCR({{ $driver->id }})">
                                                <i class="fas fa-play"></i>
                                            </button>
                                        @endif
                                        <button type="button" class="btn btn-warning btn-sm" onclick="manualOCRReview({{ $driver->id }})">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <i class="fas fa-file-alt fa-2x text-muted mb-2"></i>
                                    <p class="text-muted">No OCR verifications found</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if(isset($drivers) && $drivers->hasPages())
                <div class="card-footer">
                    {{ $drivers->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Bulk OCR Modal -->
    <div class="modal fade" id="bulkOCRModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Bulk OCR Verification</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="bulkOCRForm">
                    @csrf
                    <div class="modal-body">
                        <p>Process OCR verification for selected drivers?</p>
                        <div id="selectedOCRDrivers"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Process OCR</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- OCR Details Modal -->
    <div class="modal fade" id="ocrDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">OCR Verification Details</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="ocrDetailsContent">
                    <!-- Content loaded via AJAX -->
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Select all functionality
    $('#select-all-ocr').on('change', function() {
        $('.ocr-checkbox').prop('checked', $(this).prop('checked'));
    });

    $('.ocr-checkbox').on('change', function() {
        const totalBoxes = $('.ocr-checkbox').length;
        const checkedBoxes = $('.ocr-checkbox:checked').length;
        $('#select-all-ocr').prop('checked', totalBoxes > 0 && checkedBoxes === totalBoxes);
    });
});

// OCR Functions
function viewOCRDetails(driverId) {
    $.get(`{{ url('admin/superadmin/drivers') }}/${driverId}/ocr-details`)
        .done(function(response) {
            $('#ocrDetailsContent').html(response.html);
            $('#ocrDetailsModal').modal('show');
        })
        .fail(function() {
            alert('Failed to load OCR details');
        });
}

function processOCR(driverId) {
    if (!confirm('Process OCR verification for this driver?')) return;

    $.post(`{{ url('admin/superadmin/drivers') }}/${driverId}/ocr-verify`, {
        _token: '{{ csrf_token() }}'
    })
    .done(function(response) {
        if (response.success) {
            location.reload();
        } else {
            alert(response.message || 'OCR processing failed');
        }
    })
    .fail(function(xhr) {
        alert('Error: ' + xhr.responseJSON?.message || 'OCR processing failed');
    });
}

function manualOCRReview(driverId) {
    // Redirect to manual review page
    window.location.href = `{{ url('admin/superadmin/drivers') }}/${driverId}/ocr-manual-review`;
}

function bulkOCRVerification() {
    const selectedIds = $('.ocr-checkbox:checked').map(function() {
        return $(this).val();
    }).get();

    if (selectedIds.length === 0) {
        alert('Please select at least one driver');
        return;
    }

    $('#selectedOCRDrivers').html(`<p><strong>${selectedIds.length}</strong> driver(s) selected for OCR processing.</p>`);
    $('#bulkOCRModal').modal('show');
}

$('#bulkOCRForm').on('submit', function(e) {
    e.preventDefault();

    const selectedIds = $('.ocr-checkbox:checked').map(function() {
        return $(this).val();
    }).get();

    $.post('{{ route("admin.superadmin.drivers.bulk-ocr-verification") }}', {
        _token: '{{ csrf_token() }}',
        driver_ids: selectedIds
    })
    .done(function(response) {
        $('#bulkOCRModal').modal('hide');
        location.reload();
    })
    .fail(function(xhr) {
        alert('Error: ' + xhr.responseJSON?.message || 'Bulk OCR processing failed');
    });
});

function exportOCRReport() {
    const filters = new URLSearchParams(window.location.search);
    window.location.href = '{{ route("admin.superadmin.drivers.ocr-export") }}?' + filters.toString();
}
</script>
@endpush
