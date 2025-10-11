@extends('layouts.company')

@section('title', 'Find Drivers - Company Portal')

@section('page-title', 'Find Drivers')

@section('breadcrumbs')
    <li class="breadcrumb-item"><a href="{{ route('company.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Find Drivers</li>
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-1">Driver Marketplace</h4>
                <p class="text-muted mb-0">Browse and match with verified drivers</p>
            </div>
            <div class="text-end">
                <span class="badge badge-success fs-6 px-3 py-2">
                    <i class="fas fa-users me-1"></i>{{ number_format($drivers->total()) }} Available Drivers
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Search and Filters -->
<div class="row mb-4">
    <div class="col-12">
        <div class="company-card">
            <div class="company-card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label for="location" class="form-label">Location</label>
                        <select name="location" id="location" class="form-select">
                            <option value="">All Locations</option>
                            @foreach($states as $state)
                                <option value="{{ $state }}" {{ request('location') === $state ? 'selected' : '' }}>
                                    {{ $state }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="experience" class="form-label">Experience</label>
                        <select name="experience" id="experience" class="form-select">
                            <option value="">Any Experience</option>
                            <option value="1" {{ request('experience') === '1' ? 'selected' : '' }}>1+ years</option>
                            <option value="3" {{ request('experience') === '3' ? 'selected' : '' }}>3+ years</option>
                            <option value="5" {{ request('experience') === '5' ? 'selected' : '' }}>5+ years</option>
                            <option value="10" {{ request('experience') === '10' ? 'selected' : '' }}>10+ years</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="vehicle_type" class="form-label">Vehicle Type</label>
                        <select name="vehicle_type" id="vehicle_type" class="form-select">
                            <option value="">All Vehicle Types</option>
                            <option value="car" {{ request('vehicle_type') === 'car' ? 'selected' : '' }}>Car</option>
                            <option value="truck" {{ request('vehicle_type') === 'truck' ? 'selected' : '' }}>Truck</option>
                            <option value="bus" {{ request('vehicle_type') === 'bus' ? 'selected' : '' }}>Bus</option>
                            <option value="motorcycle" {{ request('vehicle_type') === 'motorcycle' ? 'selected' : '' }}>Motorcycle</option>
                            <option value="tricycle" {{ request('vehicle_type') === 'tricycle' ? 'selected' : '' }}>Tricycle</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="search" class="form-label">Search</label>
                        <div class="input-group">
                            <input type="text" name="search" id="search" class="form-control"
                                   placeholder="Search by name or driver ID..."
                                   value="{{ request('search') }}">
                            <button type="submit" class="btn btn-company-primary">
                                <i class="fas fa-search"></i>
                            </button>
                            <a href="{{ route('company.matching.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Drivers Grid -->
<div class="row">
    @if($drivers->count() > 0)
        @foreach($drivers as $driver)
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="company-card driver-card h-100">
                    <div class="company-card-body">
                        <!-- Driver Header -->
                        <div class="d-flex align-items-start mb-3">
                            <div class="driver-avatar me-3">
                                @if($driver->profile_picture)
                                    <img src="{{ asset('storage/' . $driver->profile_picture) }}"
                                         alt="{{ $driver->first_name }} {{ $driver->surname }}"
                                         class="rounded-circle" style="width: 50px; height: 50px; object-fit: cover;">
                                @else
                                    <div class="avatar-placeholder rounded-circle d-flex align-items-center justify-content-center"
                                         style="width: 50px; height: 50px; background: var(--company-primary); color: white;">
                                        <i class="fas fa-user"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $driver->first_name }} {{ $driver->surname }}</h6>
                                <small class="text-muted d-block">{{ $driver->driver_id }}</small>
                                <div class="mt-1">
                                    <span class="badge badge-success badge-sm">
                                        <i class="fas fa-check-circle me-1"></i>Verified
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Driver Info -->
                        <div class="driver-info mb-3">
                            <div class="row text-center">
                                <div class="col-4">
                                    <small class="text-muted d-block">Experience</small>
                                    <strong>{{ $driver->years_of_experience ?? 0 }} yrs</strong>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted d-block">Rating</small>
                                    <strong>
                                        @if($driver->performance && $driver->performance->average_rating)
                                            {{ number_format($driver->performance->average_rating, 1) }}
                                            <i class="fas fa-star text-warning"></i>
                                        @else
                                            N/A
                                        @endif
                                    </strong>
                                </div>
                                <div class="col-4">
                                    <small class="text-muted d-block">Jobs</small>
                                    <strong>{{ $driver->performance ? $driver->performance->total_jobs : 0 }}</strong>
                                </div>
                            </div>
                        </div>

                        <!-- Location & Vehicle Types -->
                        <div class="mb-3">
                            <small class="text-muted">
                                <i class="fas fa-map-marker-alt me-1"></i>{{ $driver->state }}, {{ $driver->lga }}
                            </small>
                            @if($driver->vehicle_types)
                                <div class="mt-2">
                                    <small class="text-muted d-block mb-1">Vehicle Types:</small>
                                    <div>
                                        @foreach(json_decode($driver->vehicle_types, true) ?? [] as $vehicle)
                                            <span class="badge badge-outline badge-sm me-1">{{ ucfirst($vehicle) }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex gap-2">
                            <a href="{{ route('company.matching.show', $driver) }}"
                               class="btn btn-outline-primary btn-sm flex-fill">
                                <i class="fas fa-eye me-1"></i>View Profile
                            </a>
                            <button type="button" class="btn btn-company-primary btn-sm flex-fill"
                                    onclick="initiateMatch({{ $driver->id }}, '{{ $driver->first_name }} {{ $driver->surname }}')">
                                <i class="fas fa-handshake me-1"></i>Match
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

        <!-- Pagination -->
        <div class="col-12">
            <div class="d-flex justify-content-center mt-4">
                {{ $drivers->appends(request()->query())->links() }}
            </div>
        </div>
    @else
        <div class="col-12">
            <div class="company-card">
                <div class="company-card-body text-center py-5">
                    <i class="fas fa-search fa-4x text-muted mb-4"></i>
                    <h4 class="text-muted mb-3">No drivers found</h4>
                    <p class="text-muted mb-4">
                        @if(request()->hasAny(['location', 'experience', 'vehicle_type', 'search']))
                            No drivers match your current search criteria. Try adjusting your filters.
                        @else
                            There are currently no verified drivers available in the marketplace.
                        @endif
                    </p>
                    @if(request()->hasAny(['location', 'experience', 'vehicle_type', 'search']))
                        <a href="{{ route('company.matching.index') }}" class="btn btn-company-primary">
                            <i class="fas fa-times me-2"></i>Clear Filters
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Match Initiation Modal -->
<div class="modal fade" id="matchModal" tabindex="-1" aria-labelledby="matchModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="matchModalLabel">Initiate Match</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="matchForm" method="POST">
                @csrf
                <div class="modal-body">
                    <p>Send a match request to <strong id="driverName"></strong>?</p>

                    <div class="mb-3">
                        <label for="request_id" class="form-label">Select Your Request <span class="text-danger">*</span></label>
                        <select name="request_id" id="request_id" class="form-select" required>
                            <option value="">Choose a request...</option>
                            <!-- This will be populated via AJAX or passed from controller -->
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="match_notes" class="form-label">Message to Driver (Optional)</label>
                        <textarea name="notes" id="match_notes" class="form-control" rows="3"
                                  placeholder="Add a personal message to the driver..."></textarea>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        The driver will be notified of your interest and can accept or decline the match.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-company-primary">Send Match Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('js')
<script>
function initiateMatch(driverId, driverName) {
    document.getElementById('driverName').textContent = driverName;
    document.getElementById('matchForm').action = '{{ route("company.matching.initiate") }}';

    // Populate request dropdown (you might want to load this via AJAX)
    const requestSelect = document.getElementById('request_id');
    requestSelect.innerHTML = '<option value="">Loading requests...</option>';

    // For now, we'll assume requests are passed or load via AJAX
    // In a real implementation, you'd fetch active requests for this company
    fetchActiveRequests().then(requests => {
        requestSelect.innerHTML = '<option value="">Choose a request...</option>';
        requests.forEach(request => {
            const option = document.createElement('option');
            option.value = request.id;
            option.textContent = request.position_title;
            requestSelect.appendChild(option);
        });
    });

    new bootstrap.Modal(document.getElementById('matchModal')).show();
}

async function fetchActiveRequests() {
    // This would be an AJAX call to get active requests
    // For now, return empty array - implement as needed
    return [];
}
</script>
@endsection
