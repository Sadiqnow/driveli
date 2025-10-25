@extends('company.layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="mb-0"><i class="bi bi-receipt" aria-hidden="true"></i> Invoices & Billing</h2>
            <div class="btn-group" role="group" aria-label="Invoice actions">
                <button type="button" class="btn btn-outline-primary" id="downloadAllBtn" aria-label="Download all invoices">
                    <i class="bi bi-download" aria-hidden="true"></i> Download All
                </button>
            </div>
        </div>
        <p class="text-muted mt-2">View and manage your invoices and payment history</p>
    </div>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <x-ui.stats-widget
            title="Total Invoices"
            :value="$stats['total_invoices'] ?? 0"
            icon="bi bi-receipt"
            variant="primary"
        />
    </div>
    <div class="col-md-3 mb-3">
        <x-ui.stats-widget
            title="Paid"
            :value="$stats['paid_invoices'] ?? 0"
            icon="bi bi-check-circle"
            variant="success"
        />
    </div>
    <div class="col-md-3 mb-3">
        <x-ui.stats-widget
            title="Pending"
            :value="$stats['pending_invoices'] ?? 0"
            icon="bi bi-clock"
            variant="warning"
        />
    </div>
    <div class="col-md-3 mb-3">
        <x-ui.stats-widget
            title="Overdue"
            :value="$stats['overdue_invoices'] ?? 0"
            icon="bi bi-exclamation-triangle"
            variant="danger"
        />
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-funnel"></i> Filters</h5>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3" role="search" aria-label="Filter invoices">
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>

            <div class="col-md-3">
                <label for="date_from" class="form-label">From Date</label>
                <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
            </div>

            <div class="col-md-3">
                <label for="date_to" class="form-label">To Date</label>
                <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
            </div>

            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2" aria-label="Apply filters">
                    <i class="bi bi-search" aria-hidden="true"></i> Filter
                </button>
                <a href="{{ route('company.invoices.index') }}" class="btn btn-outline-secondary" aria-label="Clear all filters">
                    <i class="bi bi-x-circle" aria-hidden="true"></i> Clear
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Invoices Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Your Invoices</h5>
        <div class="btn-group" role="group" aria-label="Table actions">
            <button type="button" class="btn btn-sm btn-outline-primary" id="refreshBtn" aria-label="Refresh invoice data">
                <i class="bi bi-arrow-clockwise" aria-hidden="true"></i> Refresh
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover" id="invoicesTable" role="table" aria-label="Invoices table">
                <thead class="table-dark">
                    <tr>
                        <th scope="col" aria-sort="none">Invoice #</th>
                        <th scope="col" aria-sort="none">Request</th>
                        <th scope="col" aria-sort="none">Amount</th>
                        <th scope="col" aria-sort="none">Status</th>
                        <th scope="col" aria-sort="none">Due Date</th>
                        <th scope="col" aria-sort="none">Created</th>
                        <th scope="col" aria-sort="none">Actions</th>
                    </tr>
                </thead>
                <tbody id="invoicesTableBody">
                    @forelse($invoices as $invoice)
                    <tr data-invoice-id="{{ $invoice->id }}">
                        <td>
                            <strong>{{ $invoice->invoice_number }}</strong>
                        </td>
                        <td>
                            <a href="{{ route('company.requests.show', $invoice->companyRequest) }}" class="text-decoration-none">
                                {{ $invoice->companyRequest->request_id ?? 'N/A' }}
                            </a>
                        </td>
                        <td>
                            <strong class="text-primary">₦{{ number_format($invoice->amount, 2) }}</strong>
                        </td>
                        <td>
                            <span class="badge
                                @if($invoice->status == 'paid') bg-success
                                @elseif($invoice->status == 'pending') bg-warning
                                @elseif($invoice->status == 'overdue') bg-danger
                                @else bg-secondary
                                @endif">
                                {{ ucfirst($invoice->status) }}
                            </span>
                        </td>
                        <td>
                            @if($invoice->due_date)
                                {{ $invoice->due_date->format('M d, Y') }}
                                @if($invoice->due_date->isPast() && $invoice->status !== 'paid')
                                    <small class="text-danger d-block">Overdue</small>
                                @endif
                            @else
                                N/A
                            @endif
                        </td>
                        <td>{{ $invoice->created_at->format('M d, Y') }}</td>
                        <td>
                            <div class="btn-group" role="group" aria-label="Invoice actions">
                                <button type="button" class="btn btn-sm btn-outline-primary view-invoice" data-invoice-id="{{ $invoice->id }}" aria-label="View invoice details">
                                    <i class="bi bi-eye" aria-hidden="true"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary download-invoice" data-invoice-id="{{ $invoice->id }}" aria-label="Download invoice">
                                    <i class="bi bi-download" aria-hidden="true"></i>
                                </button>
                                @if($invoice->status == 'pending' || $invoice->status == 'overdue')
                                <button type="button" class="btn btn-sm btn-outline-success pay-invoice" data-invoice-id="{{ $invoice->id }}" aria-label="Pay invoice">
                                    <i class="bi bi-credit-card" aria-hidden="true"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="bi bi-receipt" style="font-size: 2rem;" aria-hidden="true"></i>
                            <p class="mb-0 mt-2">No invoices found.</p>
                            <p class="text-muted">Invoices will appear here once you have completed transport requests.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($invoices->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $invoices->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Pay Invoice Modal -->
<x-ui.modal id="payInvoiceModal" title="Pay Invoice" size="md">
    <div id="invoiceDetails">
        <div class="text-center mb-4">
            <h5 id="invoiceNumber"></h5>
            <p class="text-muted mb-1">Amount Due</p>
            <h3 class="text-primary" id="invoiceAmount"></h3>
        </div>

        <form id="payInvoiceForm">
            <div class="mb-3">
                <label for="paymentMethod" class="form-label">Payment Method</label>
                <select class="form-select" id="paymentMethod" required>
                    <option value="">Select Payment Method</option>
                    <option value="card">Credit/Debit Card</option>
                    <option value="bank">Bank Transfer</option>
                    <option value="wallet">Digital Wallet</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="paymentNotes" class="form-label">Notes (Optional)</label>
                <textarea class="form-control" id="paymentNotes" rows="2" placeholder="Add any payment notes..."></textarea>
            </div>

            <div class="alert alert-info">
                <i class="bi bi-info-circle" aria-hidden="true"></i>
                <strong>Secure Payment:</strong> Your payment information is encrypted and secure.
            </div>
        </form>
    </div>

    <x-slot name="footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-success" id="confirmPaymentBtn">
            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
            Pay Invoice
        </button>
    </x-slot>
</x-ui.modal>

<!-- Invoice Details Modal -->
<x-ui.modal id="invoiceDetailsModal" title="Invoice Details" size="lg">
    <div id="invoiceContent">
        <!-- Invoice content will be loaded here -->
    </div>

    <x-slot name="footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="downloadInvoiceBtn">
            <i class="bi bi-download" aria-hidden="true"></i> Download PDF
        </button>
    </x-slot>
</x-ui.modal>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentInvoiceId = null;

    // View invoice handler
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('view-invoice') || e.target.closest('.view-invoice')) {
            e.preventDefault();
            const button = e.target.classList.contains('view-invoice') ? e.target : e.target.closest('.view-invoice');
            const invoiceId = button.getAttribute('data-invoice-id');
            viewInvoice(invoiceId);
        }
    });

    // Download invoice handler
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('download-invoice') || e.target.closest('.download-invoice')) {
            e.preventDefault();
            const button = e.target.classList.contains('download-invoice') ? e.target : e.target.closest('.download-invoice');
            const invoiceId = button.getAttribute('data-invoice-id');
            downloadInvoice(invoiceId);
        }
    });

    // Pay invoice handler
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('pay-invoice') || e.target.closest('.pay-invoice')) {
            e.preventDefault();
            const button = e.target.classList.contains('pay-invoice') ? e.target : e.target.closest('.pay-invoice');
            const invoiceId = button.getAttribute('data-invoice-id');
            openPaymentModal(invoiceId);
        }
    });

    // Download all invoices
    document.getElementById('downloadAllBtn').addEventListener('click', function() {
        const currentUrl = new URL(window.location);
        const params = currentUrl.searchParams;

        let downloadUrl = '/company/invoices/download/all';
        if (params.toString()) {
            downloadUrl += '?' + params.toString();
        }

        window.open(downloadUrl, '_blank');
    });

    // Refresh button
    document.getElementById('refreshBtn').addEventListener('click', function() {
        this.innerHTML = '<i class="bi bi-arrow-clockwise spinning" aria-hidden="true"></i> Refreshing...';
        this.disabled = true;

        refreshInvoices().finally(() => {
            this.innerHTML = '<i class="bi bi-arrow-clockwise" aria-hidden="true"></i> Refresh';
            this.disabled = false;
        });
    });

    // Confirm payment
    document.getElementById('confirmPaymentBtn').addEventListener('click', function() {
        const paymentMethod = document.getElementById('paymentMethod').value;
        const notes = document.getElementById('paymentNotes').value;

        if (!paymentMethod) {
            showToast('Please select a payment method', 'danger');
            return;
        }

        this.disabled = true;
        this.querySelector('.spinner-border').classList.remove('d-none');

        processPayment(currentInvoiceId, paymentMethod, notes);
    });

    // Download from modal
    document.getElementById('downloadInvoiceBtn').addEventListener('click', function() {
        if (currentInvoiceId) {
            downloadInvoice(currentInvoiceId);
        }
    });

    function viewInvoice(invoiceId) {
        window.location.href = `/company/invoices/${invoiceId}`;
    }

    function downloadInvoice(invoiceId) {
        window.open(`/company/invoices/${invoiceId}/download`, '_blank');
    }

    function openPaymentModal(invoiceId) {
        currentInvoiceId = invoiceId;

        // Fetch invoice details
        fetch(`/api/company/invoices/${invoiceId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const invoice = data.data;
                document.getElementById('invoiceNumber').textContent = invoice.invoice_number;
                document.getElementById('invoiceAmount').textContent = '₦' + parseFloat(invoice.amount).toLocaleString('en-NG', { minimumFractionDigits: 2 });

                const modal = new bootstrap.Modal(document.getElementById('payInvoiceModal'));
                modal.show();
            } else {
                showToast('Failed to load invoice details', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred while loading invoice details', 'danger');
        });
    }

    function processPayment(invoiceId, paymentMethod, notes) {
        fetch(`/company/invoices/${invoiceId}/pay`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                payment_method: paymentMethod,
                notes: notes
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Payment processed successfully!', 'success');
                bootstrap.Modal.getInstance(document.getElementById('payInvoiceModal')).hide();
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(data.message || 'Payment processing failed', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred while processing payment', 'danger');
        })
        .finally(() => {
            document.getElementById('confirmPaymentBtn').disabled = false;
            document.getElementById('confirmPaymentBtn').querySelector('.spinner-border').classList.add('d-none');
        });
    }

    function refreshInvoices() {
        return fetch(window.location.href, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (response.ok) {
                showToast('Invoices refreshed successfully!', 'success');
            }
        })
        .catch(error => {
            console.error('Error refreshing invoices:', error);
            showToast('Failed to refresh invoices', 'danger');
        });
    }

    function showToast(message, type = 'info') {
        const toastContainer = document.getElementById('toastContainer') || createToastContainer();

        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type} border-0`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');

        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;

        toastContainer.appendChild(toast);
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();

        toast.addEventListener('hidden.bs.toast', () => {
            toast.remove();
        });
    }

    function createToastContainer() {
        const container = document.createElement('div');
        container.id = 'toastContainer';
        container.className = 'toast-container position-fixed top-0 end-0 p-3';
        container.style.zIndex = '9999';
        document.body.appendChild(container);
        return container;
    }
});
</script>

<style>
.spinning {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
@endpush
@endsection
