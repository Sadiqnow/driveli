@extends('company.layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="mb-0"><i class="bi bi-receipt"></i> Invoices & Billing</h2>
            <button class="btn btn-success" onclick="downloadAllInvoices()" aria-label="Download all invoices">
                <i class="bi bi-download" aria-hidden="true"></i> Download All
            </button>
        </div>
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
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
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
    <div class="card-header">
        <h5 class="mb-0">Your Invoices</h5>
    </div>
    <div class="card-body">
        @if(isset($invoices) && $invoices->count() > 0)
            <x-ui.table
                :headers="[
                    ['label' => 'Invoice #', 'sortable' => true],
                    ['label' => 'Request ID', 'sortable' => true],
                    ['label' => 'Amount'],
                    ['label' => 'Status'],
                    ['label' => 'Due Date'],
                    ['label' => 'Created'],
                    ['label' => 'Actions']
                ]"
                :data="$invoices->map(function($invoice) {
                    $statusClass = match($invoice->status) {
                        'paid' => 'success',
                        'pending' => 'warning',
                        'overdue' => 'danger',
                        'cancelled' => 'secondary',
                        default => 'info'
                    };

                    return [
                        'invoice_number' => $invoice->invoice_number,
                        'request_id' => $invoice->companyRequest->request_id ?? 'N/A',
                        'amount' => '₦' . number_format($invoice->amount, 2),
                        'status' => '<span class="badge bg-' . $statusClass . '">' . ucfirst($invoice->status) . '</span>',
                        'due_date' => $invoice->due_date ? $invoice->due_date->format('M d, Y') : 'N/A',
                        'created_at' => $invoice->created_at->format('M d, Y')
                    ];
                })"
                :actions="[
                    [
                        'text' => 'View',
                        'icon' => 'bi bi-eye',
                        'class' => 'btn-outline-primary',
                        'data' => ['action' => 'view', 'id' => 'invoice_id']
                    ],
                    [
                        'text' => 'Download',
                        'icon' => 'bi bi-download',
                        'class' => 'btn-outline-info',
                        'data' => ['action' => 'download', 'id' => 'invoice_id']
                    ],
                    [
                        'text' => 'Pay',
                        'icon' => 'bi bi-credit-card',
                        'class' => 'btn-outline-success',
                        'data' => ['action' => 'pay', 'id' => 'invoice_id']
                    ]
                ]"
                empty-message="No invoices found"
            />
        @else
            <div class="text-center py-5">
                <i class="bi bi-receipt" style="font-size: 3rem; color: #6c757d;" aria-hidden="true"></i>
                <h5 class="mt-3">No Invoices Yet</h5>
                <p class="text-muted">Your invoices will appear here once you have completed transport requests.</p>
            </div>
        @endif
    </div>
</div>

<!-- Pagination -->
@if(isset($invoices) && $invoices->hasPages())
<div class="d-flex justify-content-center mt-4">
    {{ $invoices->links() }}
</div>
@endif

<!-- Invoice Details Modal -->
<x-ui.modal id="invoiceDetailsModal" title="Invoice Details" size="lg">
    <div id="invoiceDetailsContent">
        <!-- Invoice details will be loaded here -->
    </div>

    <x-slot name="footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-info" id="downloadInvoiceBtn">
            <i class="bi bi-download" aria-hidden="true"></i> Download PDF
        </button>
        <button type="button" class="btn btn-success" id="payInvoiceBtn" style="display: none;">
            <i class="bi bi-credit-card" aria-hidden="true"></i> Pay Now
        </button>
    </x-slot>
</x-ui.modal>

<!-- Payment Modal -->
<x-ui.modal id="paymentModal" title="Make Payment" size="md">
    <div id="paymentContent">
        <div class="mb-3">
            <label for="paymentAmount" class="form-label">Amount to Pay</label>
            <input type="text" class="form-control" id="paymentAmount" readonly>
        </div>
        <div class="mb-3">
            <label for="paymentMethod" class="form-label">Payment Method</label>
            <select class="form-select" id="paymentMethod">
                <option value="card">Credit/Debit Card</option>
                <option value="bank">Bank Transfer</option>
                <option value="wallet">Digital Wallet</option>
            </select>
        </div>
        <div class="alert alert-info">
            <i class="bi bi-info-circle" aria-hidden="true"></i>
            You will be redirected to a secure payment gateway to complete your transaction.
        </div>
    </div>

    <x-slot name="footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-success" id="processPaymentBtn">
            <i class="bi bi-credit-card" aria-hidden="true"></i> Proceed to Payment
        </button>
    </x-slot>
</x-ui.modal>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentInvoiceId = null;

    // Action button handlers
    document.querySelectorAll('[data-action]').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const action = this.getAttribute('data-action');
            const invoiceId = this.getAttribute('data-id') || this.closest('tr').getAttribute('data-invoice-id');

            switch(action) {
                case 'view':
                    viewInvoice(invoiceId);
                    break;
                case 'download':
                    downloadInvoice(invoiceId);
                    break;
                case 'pay':
                    initiatePayment(invoiceId);
                    break;
            }
        });
    });

    // Download all invoices
    window.downloadAllInvoices = function() {
        showToast('Downloading all invoices...', 'info');
        // Implement bulk download
        fetch('/api/company/invoices/download-all', {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => {
            if (response.ok) {
                return response.blob();
            } else {
                throw new Error('Download failed');
            }
        })
        .then(blob => {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'invoices.zip';
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
            showToast('Invoices downloaded successfully!', 'success');
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Failed to download invoices', 'danger');
        });
    };

    function viewInvoice(invoiceId) {
        currentInvoiceId = invoiceId;
        fetch(`/api/company/invoices/${invoiceId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const invoice = data.data;
                    const content = `
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Invoice Information</h6>
                                <p><strong>Invoice #:</strong> ${invoice.invoice_number}</p>
                                <p><strong>Request ID:</strong> ${invoice.companyRequest?.request_id || 'N/A'}</p>
                                <p><strong>Status:</strong>
                                    <span class="badge bg-${getStatusClass(invoice.status)}">${invoice.status}</span>
                                </p>
                                <p><strong>Created:</strong> ${new Date(invoice.created_at).toLocaleDateString()}</p>
                            </div>
                            <div class="col-md-6">
                                <h6>Payment Details</h6>
                                <p><strong>Amount:</strong> ₦${parseFloat(invoice.amount).toLocaleString()}</p>
                                <p><strong>Due Date:</strong> ${invoice.due_date ? new Date(invoice.due_date).toLocaleDateString() : 'N/A'}</p>
                                ${invoice.paid_at ? `<p><strong>Paid At:</strong> ${new Date(invoice.paid_at).toLocaleDateString()}</p>` : ''}
                            </div>
                        </div>
                        ${invoice.description ? `
                            <div class="mt-3">
                                <h6>Description</h6>
                                <p>${invoice.description}</p>
                            </div>
                        ` : ''}
                    `;

                    document.getElementById('invoiceDetailsContent').innerHTML = content;

                    const payBtn = document.getElementById('payInvoiceBtn');
                    const downloadBtn = document.getElementById('downloadInvoiceBtn');

                    if (invoice.status === 'pending' || invoice.status === 'overdue') {
                        payBtn.style.display = 'inline-block';
                        payBtn.onclick = () => initiatePayment(invoiceId);
                    } else {
                        payBtn.style.display = 'none';
                    }

                    downloadBtn.onclick = () => downloadInvoice(invoiceId);

                    const modal = new bootstrap.Modal(document.getElementById('invoiceDetailsModal'));
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

    function downloadInvoice(invoiceId) {
        showToast('Downloading invoice...', 'info');
        fetch(`/api/company/invoices/${invoiceId}/download`, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => {
            if (response.ok) {
                return response.blob();
            } else {
                throw new Error('Download failed');
            }
        })
        .then(blob => {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `invoice-${invoiceId}.pdf`;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
            showToast('Invoice downloaded successfully!', 'success');
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Failed to download invoice', 'danger');
        });
    }

    function initiatePayment(invoiceId) {
        currentInvoiceId = invoiceId;
        // Fetch invoice amount
        fetch(`/api/company/invoices/${invoiceId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('paymentAmount').value = `₦${parseFloat(data.data.amount).toLocaleString()}`;

                    const modal = new bootstrap.Modal(document.getElementById('paymentModal'));
                    modal.show();
                } else {
                    showToast('Failed to load payment details', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred while loading payment details', 'danger');
            });
    }

    // Process payment
    document.getElementById('processPaymentBtn').addEventListener('click', function() {
        const paymentMethod = document.getElementById('paymentMethod').value;

        showToast('Processing payment...', 'info');

        // Implement payment processing
        fetch(`/api/company/invoices/${currentInvoiceId}/pay`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                payment_method: paymentMethod
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (data.redirect_url) {
                    window.location.href = data.redirect_url;
                } else {
                    showToast('Payment processed successfully!', 'success');
                    bootstrap.Modal.getInstance(document.getElementById('paymentModal')).hide();
                    setTimeout(() => location.reload(), 1500);
                }
            } else {
                showToast(data.message || 'Payment failed', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('An error occurred during payment processing', 'danger');
        });
    });

    function getStatusClass(status) {
        return {
            'paid': 'success',
            'pending': 'warning',
            'overdue': 'danger',
            'cancelled': 'secondary'
        }[status] || 'info';
    }

    function showToast(message, type = 'info') {
        let toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            toastContainer.style.zIndex = '9999';
            document.body.appendChild(toastContainer);
        }

        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type} border-0`;
        toast.setAttribute('role', 'alert');
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;

        toastContainer.appendChild(toast);
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();

        toast.addEventListener('hidden.bs.toast', () => {
            toast.remove();
        });
    }
});
</script>
@endpush
@endsection
