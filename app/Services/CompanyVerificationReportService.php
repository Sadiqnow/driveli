<?php

namespace App\Services;

use App\Models\CompanyVerification;
use Illuminate\Support\Facades\Response;

class CompanyVerificationReportService
{
    /**
     * Generate and download CSV report
     */
    public function downloadCsvReport(array $dateRange): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $verifications = $this->getVerificationsForReport($dateRange);

        $filename = 'company_verification_report_' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\""
        ];

        return Response::stream(function() use ($verifications) {
            $file = fopen('php://output', 'w');

            // CSV headers
            fputcsv($file, [
                'Company Name',
                'Verification Type',
                'Status',
                'Submitted Date',
                'Verified Date',
                'Verified By',
                'Notes'
            ]);

            // CSV data
            foreach ($verifications as $verification) {
                fputcsv($file, [
                    $verification->company->name ?? 'N/A',
                    $verification->verification_type,
                    $verification->status,
                    $verification->created_at->format('Y-m-d H:i:s'),
                    $verification->verified_at?->format('Y-m-d H:i:s') ?? 'N/A',
                    $verification->verifiedBy->name ?? 'N/A',
                    $verification->notes ?? ''
                ]);
            }

            fclose($file);
        }, 200, $headers);
    }

    /**
     * Get verifications for report generation
     */
    private function getVerificationsForReport(array $dateRange)
    {
        return CompanyVerification::with('company', 'verifiedBy')
            ->whereBetween('created_at', [
                \Carbon\Carbon::parse($dateRange['start']),
                \Carbon\Carbon::parse($dateRange['end'])
            ])
            ->get();
    }
}
