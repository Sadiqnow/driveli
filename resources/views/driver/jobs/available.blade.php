@extends('drivers.layouts.app')

@section('title', 'Available Jobs')
@section('page_title', 'Available Jobs')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('driver.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Available Jobs</li>
@endsection

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Available Jobs</h4>
                    <div>
                        <a href="{{ route('driver.dashboard') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                        <button class="btn btn-primary" onclick="refreshJobs()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <!-- Availability Status -->
                    <div class="alert alert-info mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">
                                    <i class="fas fa-toggle-{{ auth('driver')->user()->available ?? false ? 'on' : 'off' }} text-{{ auth('driver')->user()->available ?? false ? 'success' : 'secondary' }}"></i>
                                    You are currently {{ auth('driver')->user()->available ?? false ? 'available' : 'unavailable' }} for jobs
                                </h6>
                                <small>Toggle your availability status to receive job matches</small>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="availabilityToggle"
                                       {{ auth('driver')->user()->available ?? false ? 'checked' : '' }}>
                            </div>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <select class="form-control" id="locationFilter">
                                <option value="">All Locations</option>
                                <option value="lagos">Lagos</option>
                                <option value="abuja">Abuja</option>
                                <option value="kano">Kano</option>
                                <option value="ibadan">Ibadan</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-control" id="typeFilter">
                                <option value="">All Job Types</option>
                                <option value="delivery">Delivery</option>
                                <option value="ride">Ride Sharing</option>
                                <option value="logistics">Logistics</option>
                                <option value="courier">Courier</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-control" id="payFilter">
                                <option value="">All Pay Ranges</option>
                                <option value="1000-5000">₦1,000 - ₦5,000</option>
                                <option value="5000-10000">₦5,000 - ₦10,000</option>
                                <option value="10000+">₦10,000+</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-outline-primary w-100" onclick="applyFilters()">
                                <i class="fas fa-filter"></i> Apply Filters
                            </button>
                        </div>
                    </div>

                    <!-- Jobs List -->
                    <div id="jobsList">
                        <!-- Sample Job Cards -->
                        <div class="job-card mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="mb-1">
                                                    <i class="fas fa-box text-primary"></i>
                                                    Package Delivery - Lagos Island to Mainland
                                                </h6>
                                                <span class="badge bg-success">₦8,500</span>
                                            </div>
                                            <p class="text-muted mb-2">
                                                <i class="fas fa-building"></i> 
                                                <strong>ABC Logistics Ltd</strong>
                                            </p>
                                            <p class="mb-2">
                                                <i class="fas fa-map-marker-alt text-danger"></i>
                                                Pickup: Victoria Island → Delivery: Ikeja
                                            </p>
                                            <div class="d-flex align-items-center text-muted">
                                                <small class="me-3">
                                                    <i class="fas fa-clock"></i> 
                                                    Posted 2 hours ago
                                                </small>
                                                <small class="me-3">
                                                    <i class="fas fa-route"></i> 
                                                    ~25km
                                                </small>
                                                <small class="me-3">
                                                    <i class="fas fa-weight"></i> 
                                                    Light package
                                                </small>
                                                <small>
                                                    <i class="fas fa-star text-warning"></i>
                                                    4.8 company rating
                                                </small>
                                            </div>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <div class="mb-2">
                                                <span class="badge bg-info">Delivery</span>
                                                <span class="badge bg-secondary">Urgent</span>
                                            </div>
                                            <button class="btn btn-primary btn-sm mb-1 w-100">
                                                <i class="fas fa-hand-paper"></i> Apply for Job
                                            </button>
                                            <button class="btn btn-outline-secondary btn-sm w-100">
                                                <i class="fas fa-eye"></i> View Details
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="job-card mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="mb-1">
                                                    <i class="fas fa-car text-success"></i>
                                                    Ride Service - Airport Transfer
                                                </h6>
                                                <span class="badge bg-success">₦12,000</span>
                                            </div>
                                            <p class="text-muted mb-2">
                                                <i class="fas fa-building"></i> 
                                                <strong>QuickRide Services</strong>
                                            </p>
                                            <p class="mb-2">
                                                <i class="fas fa-map-marker-alt text-danger"></i>
                                                Pickup: Murtala Mohammed Airport → Delivery: Lekki Phase 1
                                            </p>
                                            <div class="d-flex align-items-center text-muted">
                                                <small class="me-3">
                                                    <i class="fas fa-clock"></i> 
                                                    Posted 5 hours ago
                                                </small>
                                                <small class="me-3">
                                                    <i class="fas fa-route"></i> 
                                                    ~45km
                                                </small>
                                                <small class="me-3">
                                                    <i class="fas fa-users"></i> 
                                                    2 passengers
                                                </small>
                                                <small>
                                                    <i class="fas fa-star text-warning"></i>
                                                    4.5 company rating
                                                </small>
                                            </div>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <div class="mb-2">
                                                <span class="badge bg-warning">Ride</span>
                                                <span class="badge bg-primary">Premium</span>
                                            </div>
                                            <button class="btn btn-primary btn-sm mb-1 w-100">
                                                <i class="fas fa-hand-paper"></i> Apply for Job
                                            </button>
                                            <button class="btn btn-outline-secondary btn-sm w-100">
                                                <i class="fas fa-eye"></i> View Details
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div id="emptyState" class="text-center py-5" style="display: none;">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No jobs available</h5>
                            <p class="text-muted">Check back later or adjust your filters to see more opportunities.</p>
                            <button class="btn btn-primary" onclick="refreshJobs()">
                                <i class="fas fa-sync-alt"></i> Refresh Jobs
                            </button>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4">
                        <nav>
                            <ul class="pagination">
                                <li class="page-item disabled">
                                    <a class="page-link" href="#" tabindex="-1">Previous</a>
                                </li>
                                <li class="page-item active">
                                    <a class="page-link" href="#">1</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">2</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">3</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">Next</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.job-card .card {
    border-left: 4px solid #007bff;
    transition: all 0.2s ease;
}

.job-card .card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    transform: translateY(-2px);
}

.badge {
    font-size: 0.75em;
}

.form-check-input {
    width: 2rem;
    height: 1rem;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Availability toggle functionality
    const availabilityToggle = document.getElementById('availabilityToggle');
    if (availabilityToggle) {
        availabilityToggle.addEventListener('change', function() {
            updateAvailability(this.checked);
        });
    }
});

function updateAvailability(available) {
    // This would make an AJAX call to update availability
    console.log('Updating availability to:', available);
    
    // Show loading state
    const toggle = document.getElementById('availabilityToggle');
    toggle.disabled = true;
    
    // Simulate API call
    setTimeout(() => {
        toggle.disabled = false;
        
        // Update UI
        const statusText = available ? 'available' : 'unavailable';
        const alertDiv = document.querySelector('.alert-info');
        alertDiv.innerHTML = `
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1">
                        <i class="fas fa-toggle-${available ? 'on' : 'off'} text-${available ? 'success' : 'secondary'}"></i>
                        You are currently ${statusText} for jobs
                    </h6>
                    <small>Toggle your availability status to receive job matches</small>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" 
                           type="checkbox" 
                           id="availabilityToggle"
                           ${available ? 'checked' : ''}>
                </div>
            </div>
        `;
        
        // Show toast notification
        showToast(`You are now ${statusText} for jobs`, 'success');
    }, 1000);
}

function refreshJobs() {
    const button = event.target;
    const icon = button.querySelector('i');
    
    // Show loading
    icon.classList.add('fa-spin');
    button.disabled = true;
    
    // Simulate refresh
    setTimeout(() => {
        icon.classList.remove('fa-spin');
        button.disabled = false;
        showToast('Jobs refreshed', 'info');
    }, 1500);
}

function applyFilters() {
    const location = document.getElementById('locationFilter').value;
    const type = document.getElementById('typeFilter').value;
    const pay = document.getElementById('payFilter').value;
    
    console.log('Applying filters:', { location, type, pay });
    
    // This would filter the jobs based on the selected criteria
    showToast('Filters applied', 'info');
}

function showToast(message, type) {
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'success' ? 'success' : 'info'} alert-dismissible fade show position-fixed`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 1060; min-width: 300px;';
    toast.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'info-circle'} me-2"></i>
            <span>${message}</span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        if (toast.parentNode) {
            toast.remove();
        }
    }, 3000);
}
</script>
@endsection