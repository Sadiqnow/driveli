<?php

namespace App\Jobs;

use App\Models\Company;
use App\Services\BillingService;
use App\Services\CompanyService;
use App\Services\FleetService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PDF;

class GenerateReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $company;
    protected $reportType;
    protected $dateRange;
    protected $user;

    public function __construct(Company $company, string $reportType, array $dateRange = [], $user = null)
    {
        $this->company = $company;
        $this->reportType = $reportType;
        $this->dateRange = $dateRange;
        $this->user = $user;
    }

    public function handle(
        CompanyService $companyService,
        FleetService $fleetService,
        BillingService $billingService
    ) {
        try {
            Log::info("Generating {$this->reportType} report for company ID: {$this->company->id}");

            $data = $this->gatherReportData($companyService, $fleetService, $billingService);
            $pdf = $this->generatePDF($data);

            $filename = $this->storeReport($pdf);

            // Notify user that report is ready
            if ($this->user) {
                SendNotificationJob::dispatch(
                    $this->user,
                    'report_ready',
                    [
                        'report_type' => $this->reportType,
                        'filename' => $filename,
                        'download_url' => route('company.reports.download', $filename),
                    ]
                );
            }

            Log::info("Report generated successfully: {$filename}");

        } catch (\Exception $e) {
            Log::error("Error generating report: " . $e->getMessage());
            throw $e;
        }
    }

    protected function gatherReportData($companyService, $fleetService, $billingService)
    {
        $data = [
            'company' => $this->company,
            'generated_at' => now(),
            'date_range' => $this->dateRange,
        ];

        switch ($this->reportType) {
            case 'performance':
                $data['requests'] = $this->company->requests()
                    ->when($this->dateRange, function ($query) {
                        return $query->whereBetween('created_at', $this->dateRange);
                    })
                    ->with('matches')
                    ->get();

                $data['stats'] = $companyService->getDashboardData($this->company);
                break;

            case 'fleet':
                $data['fleets'] = $this->company->fleets()->with('vehicles')->get();
                $data['fleet_stats'] = [];

                foreach ($data['fleets'] as $fleet) {
                    $data['fleet_stats'][$fleet->id] = $fleetService->getFleetStats($fleet);
                }
                break;

            case 'billing':
                $data['invoices'] = $this->company->invoices()
                    ->when($this->dateRange, function ($query) {
                        return $query->whereBetween('created_at', $this->dateRange);
                    })
                    ->get();

                $data['billing_summary'] = $billingService->getCompanyBillingSummary($this->company);
                break;

            case 'comprehensive':
                $data = array_merge($data, $this->gatherReportData($companyService, $fleetService, $billingService));
                // Remove the report type specific keys and merge all data
                unset($data['requests'], $data['fleets'], $data['invoices']);
                $data['performance'] = $this->gatherReportData($companyService, $fleetService, $billingService)['requests'] ?? [];
                $data['fleet'] = $this->gatherReportData($companyService, $fleetService, $billingService)['fleets'] ?? [];
                $data['billing'] = $this->gatherReportData($companyService, $fleetService, $billingService)['invoices'] ?? [];
                break;
        }

        return $data;
    }

    protected function generatePDF($data)
    {
        $view = "reports.company.{$this->reportType}";
        return PDF::loadView($view, $data);
    }

    protected function storeReport($pdf)
    {
        $filename = "company_{$this->company->id}_{$this->reportType}_" . now()->format('Y-m-d_H-i-s') . '.pdf';
        $path = "reports/{$filename}";

        Storage::put($path, $pdf->output());

        return $filename;
    }

    public function failed(\Throwable $exception)
    {
        Log::error("GenerateReportJob failed: " . $exception->getMessage(), [
            'company_id' => $this->company->id,
            'report_type' => $this->reportType,
        ]);

        if ($this->user) {
            SendNotificationJob::dispatch(
                $this->user,
                'report_failed',
                [
                    'report_type' => $this->reportType,
                    'error' => 'Failed to generate report. Please try again.',
                ]
            );
        }
    }
}
