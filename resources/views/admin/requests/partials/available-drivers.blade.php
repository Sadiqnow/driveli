<div class="row">
    <div class="col-12">
        <h5 class="mb-3">Available Drivers for Request #{{ $companyRequest->id }}</h5>
    </div>
</div>

<div class="row">
    @forelse($drivers as $driver)
    <div class="col-md-6 col-lg-4 mb-3">
        <div class="card driver-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h6 class="card-title mb-0">{{ $driver->first_name }} {{ $driver->last_name }}</h6>
                    <span class="badge bg-success">Available</span>
                </div>
                
                <div class="driver-details">
                    <p class="card-text small text-muted mb-1">
                        <i class="fas fa-id-card"></i> ID: {{ $driver->id }}
                    </p>
                    <p class="card-text small text-muted mb-1">
                        <i class="fas fa-phone"></i> {{ $driver->phone ?? 'N/A' }}
                    </p>
                    <p class="card-text small text-muted mb-1">
                        <i class="fas fa-envelope"></i> {{ $driver->email ?? 'N/A' }}
                    </p>
                    <p class="card-text small text-muted mb-1">
                        <i class="fas fa-map-marker-alt"></i> {{ $driver->state ?? 'N/A' }}
                    </p>
                    <p class="card-text small text-muted mb-2">
                        <i class="fas fa-check-circle"></i> Status: {{ ucfirst($driver->verification_status ?? 'pending') }}
                    </p>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        Joined: {{ $driver->created_at ? $driver->created_at->format('M Y') : 'N/A' }}
                    </small>
                    <button type="button" 
                            class="btn btn-primary btn-sm assign-driver-btn" 
                            data-driver-id="{{ $driver->id }}"
                            data-driver-name="{{ $driver->first_name }} {{ $driver->last_name }}"
                            data-request-id="{{ $companyRequest->id }}">
                        <i class="fas fa-user-plus"></i> Assign
                    </button>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="alert alert-info text-center">
            <i class="fas fa-info-circle"></i> No available drivers found at the moment.
        </div>
    </div>
    @endforelse
</div>

@if($drivers->count() > 0)
<div class="row mt-3">
    <div class="col-12">
        <div class="d-flex justify-content-between">
            <button type="button" class="btn btn-secondary" onclick="loadMoreDrivers()">
                <i class="fas fa-sync"></i> Refresh List
            </button>
            <button type="button" class="btn btn-success" onclick="autoAssignBest()">
                <i class="fas fa-magic"></i> Auto Assign Best Match
            </button>
        </div>
    </div>
</div>
@endif

<style>
.driver-card {
    transition: transform 0.2s;
}

.driver-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.assign-driver-btn:hover {
    transform: scale(1.05);
}
</style>

<script>
$(document).ready(function() {
    // Handle driver assignment
    $('.assign-driver-btn').click(function() {
        const driverId = $(this).data('driver-id');
        const driverName = $(this).data('driver-name');
        const requestId = $(this).data('request-id');
        
        if (confirm(`Are you sure you want to assign ${driverName} to this request?`)) {
            assignDriverToRequest(requestId, driverId, driverName);
        }
    });
});

function assignDriverToRequest(requestId, driverId, driverName) {
    // Show loading state
    const btn = $(`.assign-driver-btn[data-driver-id="${driverId}"]`);
    const originalText = btn.html();
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Assigning...');
    
    // Make AJAX request to assign driver
    $.post(`/admin/requests/${requestId}/match`, {
        driver_id: driverId,
        commission_rate: 10, // Default commission rate
        _token: $('meta[name="csrf-token"]').attr('content')
    })
    .done(function(response) {
        if (response.success) {
            toastr.success(`${driverName} has been successfully assigned!`);
            // Remove the assigned driver from the list
            btn.closest('.col-md-6').fadeOut(300);
        } else {
            toastr.error(response.message || 'Failed to assign driver');
            btn.prop('disabled', false).html(originalText);
        }
    })
    .fail(function(xhr) {
        const message = xhr.responseJSON?.message || 'Failed to assign driver';
        toastr.error(message);
        btn.prop('disabled', false).html(originalText);
    });
}

function loadMoreDrivers() {
    location.reload();
}

function autoAssignBest() {
    if (confirm('This will automatically assign the best available driver. Continue?')) {
        const firstDriver = $('.assign-driver-btn').first();
        if (firstDriver.length) {
            firstDriver.click();
        } else {
            toastr.info('No drivers available for auto-assignment');
        }
    }
}
</script>