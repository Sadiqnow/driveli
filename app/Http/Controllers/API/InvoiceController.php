<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\CompanyInvoice;
use App\Services\BillingService;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    protected $billingService;

    public function __construct(BillingService $billingService)
    {
        $this->billingService = $billingService;
    }

    public function index(Request $request)
    {
        $company = $request->user();

        $query = CompanyInvoice::where('company_id', $company->id)->with('companyMatch.companyRequest');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $invoices = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => $invoices,
        ]);
    }

    public function show(CompanyInvoice $invoice)
    {
        $this->authorize('view', $invoice);

        $invoice->load(['companyMatch.companyRequest', 'companyMatch.driver', 'company']);

        return response()->json([
            'status' => 'success',
            'data' => $invoice,
        ]);
    }

    public function pay(Request $request, CompanyInvoice $invoice)
    {
        $this->authorize('update', $invoice);

        if ($invoice->status !== 'pending') {
            return response()->json([
                'status' => 'error',
                'message' => 'Invoice is not payable',
            ], 400);
        }

        $request->validate([
            'payment_method' => 'required|string|in:card,bank_transfer,wallet',
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $result = $this->billingService->processPayment($invoice, $request->all());

            return response()->json([
                'status' => 'success',
                'message' => 'Payment processed successfully',
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Payment failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function download(CompanyInvoice $invoice)
    {
        $this->authorize('view', $invoice);

        // Generate PDF invoice
        $pdf = $this->billingService->generateInvoicePDF($invoice);

        return $pdf->download('invoice-' . $invoice->invoice_number . '.pdf');
    }

    public function overdue(Request $request)
    {
        $company = $request->user();

        $invoices = CompanyInvoice::where('company_id', $company->id)
            ->where('status', 'overdue')
            ->with('companyMatch.companyRequest')
            ->orderBy('due_date', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $invoices,
        ]);
    }

    public function summary(Request $request)
    {
        $company = $request->user();

        $summary = $this->billingService->getCompanyBillingSummary($company);

        return response()->json([
            'status' => 'success',
            'data' => $summary,
        ]);
    }

    public function markAsPaid(Request $request, CompanyInvoice $invoice)
    {
        $this->authorize('update', $invoice);

        $request->validate([
            'payment_date' => 'required|date',
            'payment_reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        $invoice->update([
            'status' => 'paid',
            'paid_at' => $request->payment_date,
            'payment_reference' => $request->payment_reference,
            'notes' => $request->notes,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Invoice marked as paid',
            'data' => $invoice,
        ]);
    }

    public function dispute(Request $request, CompanyInvoice $invoice)
    {
        $this->authorize('update', $invoice);

        $request->validate([
            'reason' => 'required|string|max:1000',
            'evidence' => 'nullable|array',
        ]);

        $invoice->update([
            'status' => 'disputed',
            'dispute_reason' => $request->reason,
            'dispute_evidence' => $request->evidence,
            'disputed_at' => now(),
        ]);

        // TODO: Notify admin about dispute

        return response()->json([
            'status' => 'success',
            'message' => 'Invoice dispute submitted',
            'data' => $invoice,
        ]);
    }
}
