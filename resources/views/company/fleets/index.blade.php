@extends('company.layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0"><i class="bi bi-truck"></i> Fleet Management</h2>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createFleetModal">
                <i class="bi bi-plus-circle"></i> Add Fleet
            </button>
        </div>
    </div>
</div>

<!-- Fleets Grid -->
<div class="row">
    @if($fleets->count() > 0)
        @foreach($fleets as $fleet)
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-header bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">{{ $fleet->name }}</h6>
                            <span class="badge bg-{{ $fleet->status === 'active' ? 'success' : 'secondary' }}">
                                {{ ucfirst($fleet->status) }}
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-2">{{ $fleet->description }}</p>

                        <div class="row text-center">
                            <div class="col-6">
                                <div class="border-end">
                                    <h4 class="text-primary mb-0">{{ $fleet->vehicles->count() }}</h4>
                                    <small class="text-muted">Vehicles</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <h4 class="text-success mb-0">{{ $fleet->vehicles->where('status', 'active')->count() }}</h4>
                                <small class="text-muted">Active</small>
                            </div>
                        </div>

                        @if($fleet->manager_name)
                            <div class="mt-3">
                                <small class="text-muted">Manager: {{ $fleet->manager_name }}</small>
                            </div>
                        @endif
                    </div>
                    <div class="card-footer">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('company.fleets.show', $fleet) }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-eye"></i> View
                            </a>
                            <div>
                                <button type="button" class="btn btn-outline-secondary btn-sm me-1"
                                        onclick="editFleet({{ $fleet->id }})">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm"
                                        onclick="deleteFleet({{ $fleet->id }})">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @else
        <div class="col-12">
            <div class="text-center py-5">
                <i class="bi bi-truck" style="font-size: 3rem; color: #6c757d;"></i>
                <h5 class="mt-3">No Fleets Found</h5>
                <p class="text-muted">Create your first fleet to start managing vehicles.</p>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createFleetModal">
                    <i class="bi bi-plus-circle"></i> Create Fleet
                </button>
            </div>
        </div>
    @endif
</div>

<!-- Pagination -->
@if($fleets->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $fleets->links() }}
    </div>
@endif

<!-- Create Fleet Modal -->
<div class="modal fade" id="createFleetModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Fleet</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createFleetForm" method="POST" action="{{ route('company.fleets.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Fleet Name *</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="manager_name" class="form-label">Manager Name</label>
                            <input type="text" class="form-control" id="manager_name" name="manager_name">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="manager_phone" class="form-label">Manager Phone</label>
                            <input type="tel" class="form-control" id="manager_phone" name="manager_phone">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="manager_email" class="form-label">Manager Email</label>
                            <input type="email" class="form-control" id="manager_email" name="manager_email">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="base_location" class="form-label">Base Location</label>
                        <input type="text" class="form-control" id="base_location" name="base_location">
                    </div>

                    <div class="mb-3">
                        <label for="operating_regions" class="form-label">Operating Regions</label>
                        <input type="text" class="form-control" id="operating_regions" name="operating_regions"
                               placeholder="e.g., Lagos, Abuja, Port Harcourt">
                        <small class="text-muted">Separate multiple regions with commas</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Fleet</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Fleet Modal -->
<div class="modal fade" id="editFleetModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Fleet</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editFleetForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <!-- Form fields will be populated by JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Fleet</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function editFleet(fleetId) {
    fetch(`/company/fleets/${fleetId}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const fleet = data.data.fleet;
                const form = document.getElementById('editFleetForm');
                form.action = `/company/fleets/${fleetId}`;

                const modalBody = form.querySelector('.modal-body');
                modalBody.innerHTML = `
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_name" class="form-label">Fleet Name *</label>
                            <input type="text" class="form-control" id="edit_name" name="name" value="${fleet.name}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_manager_name" class="form-label">Manager Name</label>
                            <input type="text" class="form-control" id="edit_manager_name" name="manager_name" value="${fleet.manager_name || ''}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3">${fleet.description || ''}</textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_manager_phone" class="form-label">Manager Phone</label>
                            <input type="tel" class="form-control" id="edit_manager_phone" name="manager_phone" value="${fleet.manager_phone || ''}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_manager_email" class="form-label">Manager Email</label>
                            <input type="email" class="form-control" id="edit_manager_email" name="manager_email" value="${fleet.manager_email || ''}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_base_location" class="form-label">Base Location</label>
                        <input type="text" class="form-control" id="edit_base_location" name="base_location" value="${fleet.base_location || ''}">
                    </div>
                    <div class="mb-3">
                        <label for="edit_operating_regions" class="form-label">Operating Regions</label>
                        <input type="text" class="form-control" id="edit_operating_regions" name="operating_regions" value="${fleet.operating_regions || ''}">
                    </div>
                `;

                const editModal = new bootstrap.Modal(document.getElementById('editFleetModal'));
                editModal.show();
            }
        })
        .catch(error => console.error('Error loading fleet:', error));
}

function deleteFleet(fleetId) {
    if (confirm('Are you sure you want to delete this fleet? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/company/fleets/${fleetId}`;

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken;
        form.appendChild(csrfInput);

        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);

        document.body.appendChild(form);
        form.submit();
    }
}

// Reset form when modal is hidden
document.getElementById('createFleetModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('createFleetForm').reset();
});
</script>
@endpush
@endsection
