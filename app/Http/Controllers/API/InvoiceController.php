<?php

namespace App\Http\Controllers\API;

use App\Helpers\DrivelinkHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Http\Resources\InvoiceResource;
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

        return DrivelinkHelper::respondJson('success', 'Invoices retrieved successfully', InvoiceResource::collection($invoices));
    }

    public function show(CompanyInvoice $invoice)
    {
        $this->authorize('view', $invoice);

        $invoice->load(['companyMatch.companyRequest', 'companyMatch.driver', 'company']);

        return DrivelinkHelper::respondJson('success', 'Invoice retrieved successfully', new InvoiceResource($invoice));
    }

    public function pay(Request $request, CompanyInvoice $invoice)
    {
        $this->authorize('update', $invoice);

        if ($invoice->status !== 'pending') {
            return DrivelinkHelper::respondJson('error', 'Invoice is not payable', null, 400);
        }

        $request->validate([
            'payment_method' => 'required|string|in:card,bank_transfer,wallet',
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $result = $this->billingService->processPayment($invoice, $request->all());

            return DrivelinkHelper::respondJson('success', 'Payment processed successfully', $result);
        } catch (\Exception $e) {
            return DrivelinkHelper::respondJson('error', 'Payment failed: ' . $e->getMessage(), null, 500);
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

        return DrivelinkHelper::respondJson('success', 'Overdue invoices retrieved successfully', InvoiceResource::collection($invoices));
    }

    public function summary(Request $request)
    {
        $company = $request->user();

        $summary = $this->billingService->getCompanyBillingSummary($company);

        return DrivelinkHelper::respondJson('success', 'Billing summary retrieved successfully', $summary);
    }

    public function markAsPaid(UpdateInvoiceRequest $request, CompanyInvoice $invoice)
    {
        $this->authorize('update', $invoice);

        $invoice->update(array_merge($request->validated(), [
            'status' => 'paid',
            'paid_at' => $request->payment_date,
        ]));

        return DrivelinkHelper::respondJson('success', 'Invoice marked as paid', new InvoiceResource($invoice));
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

        return DrivelinkHelper::respondJson('success', 'Invoice dispute submitted', new InvoiceResource($invoice));
    }
}
