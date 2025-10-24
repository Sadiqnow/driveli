<?php

namespace App\Services;

use Illuminate\Support\Facades\Response;

class VerificationReportService
{
    /**
     * Generate and download CSV report
     */
    public function downloadCsvReport(array $dateRange): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $verifications = $this->getVerificationsForReport($dateRange);

        $filename = 'driver_verification_report_' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\""
        ];

        return Response::stream(function() use ($verifications) {
            $file = fopen('php://output', 'w');

            // CSV headers
            fputcsv($file, [
                'Driver Name',
                'Email',
                'Verification Type',
                'Status',
                'Score',
                'Verified Date',
                'Verified By',
                'Notes'
            ]);

            // CSV data
            foreach ($verifications as $verification) {
                fputcsv($file, [
                    ($verification->first_name ?? '') . ' ' . ($verification->last_name ?? ''),
                    $verification->email ?? 'N/A',
                    $verification->verification_type,
                    $verification->status,
                    $verification->verification_score ?? 'N/A',
                    $verification->verified_at ? $verification->verified_at->format('Y-m-d H:i:s') : 'N/A',
                    $verification->verified_by ?? 'N/A',
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
        return \Illuminate\Support\Facades\DB::table('driver_verifications')
            ->join('drivers', 'driver_verifications.driver_id', '=', 'drivers.id')
            ->select([
                'driver_verifications.*',
                'drivers.first_name',
                'drivers.last_name',
                'drivers.email'
            ])
            ->whereBetween('driver_verifications.created_at', [
                \Carbon\Carbon::parse($dateRange['start']),
                \Carbon\Carbon::parse($dateRange['end'])
            ])
            ->orderBy('driver_verifications.created_at', 'desc')
            ->get();
    }
}
