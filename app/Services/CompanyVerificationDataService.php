<?php

namespace App\Services;

use App\Models\CompanyVerification;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CompanyVerificationDataService
{
    /**
     * Get verification queue data for index page
     */
    public function getQueueData(array $dateRange): array
    {
        return [
            'pending_verifications' => $this->getPendingVerifications(),
            'recent_activities' => $this->getRecentActivities($dateRange),
        ];
    }

    /**
     * Get verification details for show page
     */
    public function getVerificationDetails(int $verificationId): array
    {
        $verification = CompanyVerification::with('company', 'verifiedBy')->findOrFail($verificationId);

        return [
            'verification' => $verification,
            'history' => $this->getVerificationHistory($verification->company_id),
            'ocr_results' => $this->getOcrResults($verification->company_id),
        ];
    }

    /**
     * Get pending verifications
     */
    private function getPendingVerifications()
    {
        return CompanyVerification::with('company')
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
    }

    /**
     * Get recent verification activities
     */
    private function getRecentActivities(array $dateRange)
    {
        return CompanyVerification::with('company', 'verifiedBy')
            ->whereBetween('created_at', [
                Carbon::parse($dateRange['start']),
                Carbon::parse($dateRange['end'])
            ])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
    }

    /**
     * Get verification history for a company
     */
    private function getVerificationHistory(int $companyId)
    {
        return CompanyVerification::where('company_id', $companyId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get OCR results for a company
     */
    private function getOcrResults(int $companyId)
    {
        return DB::table('company_ocr_results')
            ->where('company_id', $companyId)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
