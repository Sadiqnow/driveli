@extends('company.layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0"><i class="bi bi-receipt"></i> Billing & Invoices</h2>
            <a href="{{ route('company.invoices.summary') }}" class="btn btn-outline-primary">
                <i class="bi bi-bar-chart"></i> Billing Summary
            </a>
        </div>
    </div>
</div>

<!-- Billing Summary Cards -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="card bg-info text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title mb-1">{{ $summary['pending_invoices'] ?? 0 }}</h5>
                        <p class="card-text mb-0">Pending</p>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-clock-history" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card bg-success text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title mb-1">{{ $summary['paid_invoices'] ?? 0 }}</h5>
                        <p class="card-text mb-0">Paid</p>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-check-circle" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card bg-warning text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title mb-1">{{ $summary['overdue_invoices'] ?? 0 }}</h5>
                        <p class="card-text mb-0">Overdue</p>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-exclamation-triangle" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-3">
        <div class="card bg-primary text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h5 class="card-title mb-1">₦{{ number_format($summary['total_outstanding'] ?? 0, 2) }}</h5>
                        <p class="card-text mb-0">Outstanding</p>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-cash" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
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
                <button type="submit" class="btn btn-primary me-2">
                    <i class="bi bi-search"></i> Filter
                </button>
                <a href="{{ route('company.invoices.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-x-circle"></i> Clear
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
        @if($invoices->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Request</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Due Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoices as $invoice)
                            <tr class="{{ $invoice->status === 'overdue' ? 'table-danger' : ($invoice->status === 'pending' ? 'table-warning' : '') }}">
                                <td>
                                    <strong>{{ $invoice->invoice_number }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $invoice->created_at->format('M d, Y') }}</small>
                                </td>
                                <td>
                                    {{ $invoice->companyMatch->companyRequest->request_id ?? 'N/A' }}
                                    <br>
                                    <small class="text-muted">
                                        {{ Str::limit($invoice->companyMatch->companyRequest->pickup_location ?? '', 30) }}
                                        →
                                        {{ Str::limit($invoice->companyMatch->companyRequest->dropoff_location ?? '', 30) }}
                                    </small>
                                </td>
                                <td>
                                    <strong>₦{{ number_format($invoice->amount, 2) }}</strong>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $invoice->status === 'paid' ? 'success' : ($invoice->status === 'pending' ? 'warning' : ($invoice->status === 'overdue' ? 'danger' : 'secondary')) }}">
                                        {{ ucfirst($invoice->status) }}
                                    </span>
                                </td>
                                <td>
                                    {{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : 'N/A' }}
                                    @if($invoice->status === 'overdue')
                                        <br>
                                        <small class="text-danger">{{ $invoice->due_date->diffInDays(now()) }} days overdue</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('company.invoices.show', $invoice) }}" class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                        @if($invoice->status === 'pending')
                                            <button type="button" class="btn btn-success btn-sm" onclick="payInvoice({{ $invoice->id }})">
                                                <i class="bi bi-credit-card"></i> Pay
                                            </button>
                                        @endif
                                        @if(in_array($invoice->status, ['pending', 'overdue']))
                                            <button type="button" class="btn btn-outline-warning btn-sm" onclick="disputeInvoice({{ $invoice->id }})">
                                                <i class="bi bi-exclamation-triangle"></i> Dispute
                                            </button>
                                        @endif
                                        <a href="{{ route('company.invoices.download', $invoice) }}" class="btn btn-outline-secondary btn-sm">
                                            <i class="bi bi-download"></i> PDF
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-3">
                {{ $invoices->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-receipt" style="font-size: 3rem; color: #6c757d;"></i>
                <h5 class="mt-3">No Invoices Found</h5>
                <p class="text-muted">Your invoices will appear here once you have completed transport requests.</p>
            </div>
        @endif
    </div>
</div>

<!-- Pay Invoice Modal -->
<div class="modal fade" id="payInvoiceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pay Invoice</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="payInvoiceForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="payment_method" class="form-label">Payment Method</label>
                        <select class="form-select" id="payment_method" name="payment_method" required>
                            <option value="">Select Method</option>
                            <option value="card">Credit/Debit Card</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="wallet">Digital Wallet</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="reference" class="form-label">Reference/Transaction ID</label>
                        <input type="text" class="form-control" id="reference" name="reference">
                        <small class="text-muted">Optional: Enter payment reference for tracking</small>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Process Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Dispute Invoice Modal -->
<div class="modal fade" id="disputeInvoiceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Dispute Invoice</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="disputeInvoiceForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="dispute_reason" class="form-label">Reason for Dispute *</label>
                        <textarea class="form-control" id="dispute_reason" name="reason" rows="4" required
                                  placeholder="Please explain why you are disputing this invoice..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="evidence" class="form-label">Supporting Evidence</label>
                        <textarea class="form-control" id="evidence" name="evidence" rows="3"
                                  placeholder="Any additional information or evidence..."></textarea>
                        <small class="text-muted">Optional: Provide any supporting documentation or details</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Submit Dispute</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function payInvoice(invoiceId) {
    const form = document.getElementById('payInvoiceForm');
    form.action = `/company/invoices/${invoiceId}/pay`;

    const payModal = new bootstrap.Modal(document.getElementById('payInvoiceModal'));
    payModal.show();
}

function disputeInvoice(invoiceId) {
    const form = document.getElementById('disputeInvoiceForm');
    form.action = `/company/invoices/${invoiceId}/dispute`;

    const disputeModal = new bootstrap.Modal(document.getElementById('disputeInvoiceModal'));
    disputeModal.show();
}

// Reset forms when modals are hidden
document.getElementById('payInvoiceModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('payInvoiceForm').reset();
});

document.getElementById('disputeInvoiceModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('disputeInvoiceForm').reset();
});
</script>
@endpush
@endsection
