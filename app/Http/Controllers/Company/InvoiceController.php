<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Invoice;
use App\Services\BillingService;

class InvoiceController extends Controller
{
    protected $billingService;

    public function __construct(BillingService $billingService)
    {
        $this->billingService = $billingService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $company = Auth::guard('company')->user();

        $query = Invoice::where('company_id', $company->id)->with('companyRequest');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $invoices = $query->latest()->paginate(15);

        $stats = [
            'total_invoices' => Invoice::where('company_id', $company->id)->count(),
            'paid_invoices' => Invoice::where('company_id', $company->id)->where('status', 'paid')->count(),
            'pending_invoices' => Invoice::where('company_id', $company->id)->where('status', 'pending')->count(),
            'overdue_invoices' => Invoice::where('company_id', $company->id)->where('status', 'overdue')->count(),
        ];

        return view('company.invoices', compact('invoices', 'stats'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        $invoice->load(['companyRequest', 'companyMatch.driver']);

        return view('company.invoices.show', compact('invoice'));
    }

    /**
     * Download invoice PDF
     */
    public function download(Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        try {
            $pdf = $this->billingService->generateInvoicePDF($invoice);

            return response()->streamDownload(function () use ($pdf) {
                echo $pdf->output();
            }, "invoice-{$invoice->invoice_number}.pdf");
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to generate invoice PDF']);
        }
    }

    /**
     * Process payment for invoice
     */
    public function pay(Request $request, Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        if ($invoice->status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Invoice is already paid'
            ], 400);
        }

        $request->validate([
            'payment_method' => 'required|in:card,bank,wallet'
        ]);

        try {
            // Here you would integrate with payment gateway
            // For now, we'll simulate payment processing
            $this->billingService->processPayment($invoice, [
                'payment_method' => $request->payment_method,
                'amount' => $invoice->amount
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Payment processed successfully!',
                    'redirect_url' => route('company.invoices.show', $invoice)
                ]);
            }

            return redirect()->route('company.invoices.show', $invoice)
                ->with('success', 'Payment processed successfully!');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment processing failed'
                ], 500);
            }

            return back()->withErrors(['error' => 'Payment processing failed']);
        }
    }

    /**
     * Download all invoices
     */
    public function downloadAll(Request $request)
    {
        $company = Auth::guard('company')->user();

        try {
            $invoices = Invoice::where('company_id', $company->id);

            // Apply same filters as index
            if ($request->filled('status')) {
                $invoices->where('status', $request->status);
            }

            if ($request->filled('date_from')) {
                $invoices->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $invoices->whereDate('created_at', '<=', $request->date_to);
            }

            $invoices = $invoices->get();

            if ($invoices->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No invoices found for the selected criteria'
                ], 404);
            }

            $zipFile = $this->billingService->generateBulkInvoiceZIP($invoices);

            return response()->download($zipFile, 'invoices.zip')->deleteFileAfterSend();
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate invoice archive'
            ], 500);
        }
    }
}
