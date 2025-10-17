<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Drivers;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;

class ExportDriversCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'drivers:export
                            {--format=csv : Export format (csv, excel)}
                            {--status= : Filter by status (active, inactive, flagged)}
                            {--verification= : Filter by verification status (pending, verified, rejected)}
                            {--kyc= : Filter by KYC status (pending, in_progress, completed, rejected)}
                            {--date-from= : Filter from date (YYYY-MM-DD)}
                            {--date-to= : Filter to date (YYYY-MM-DD)}
                            {--filename= : Custom filename}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export drivers data to CSV or Excel format';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting driver export...');

        // Build query with filters
        $query = Drivers::with(['verifiedBy', 'performance', 'documents']);

        if ($this->option('status')) {
            $query->where('status', $this->option('status'));
        }

        if ($this->option('verification')) {
            $query->where('verification_status', $this->option('verification'));
        }

        if ($this->option('kyc')) {
            $query->where('kyc_status', $this->option('kyc'));
        }

        if ($this->option('date-from')) {
            $query->whereDate('created_at', '>=', $this->option('date-from'));
        }

        if ($this->option('date-to')) {
            $query->whereDate('created_at', '<=', $this->option('date-to'));
        }

        $drivers = $query->get();

        if ($drivers->isEmpty()) {
            $this->warn('No drivers found matching the criteria.');
            return;
        }

        $format = $this->option('format');
        $filename = $this->option('filename') ?: 'drivers_export_' . now()->format('Y-m-d_H-i-s');

        $this->info("Exporting {$drivers->count()} drivers...");

        switch ($format) {
            case 'csv':
                $this->exportToCsv($drivers, $filename);
                break;
            case 'excel':
                $this->exportToExcel($drivers, $filename);
                break;
            default:
                $this->error('Unsupported format. Use csv or excel.');
                return;
        }

        $this->info('Export completed successfully!');
    }

    /**
     * Export drivers to CSV format
     */
    private function exportToCsv(Collection $drivers, string $filename)
    {
        // Create CSV content manually since League\Csv may not be available
        $csvContent = '';

        // Add headers
        $csvContent .= implode(',', array_map(function($header) {
            return '"' . str_replace('"', '""', $header) . '"';
        }, $this->getExportHeaders())) . "\n";

        // Add data rows
        foreach ($drivers as $driver) {
            $row = $this->formatDriverForExport($driver);
            $csvContent .= implode(',', array_map(function($field) {
                return '"' . str_replace('"', '""', $field ?? '') . '"';
            }, $row)) . "\n";
        }

        // Save to storage
        $path = 'exports/' . $filename . '.csv';
        Storage::put($path, $csvContent);

        $this->info("CSV file saved to: storage/app/{$path}");
        $this->info("Download URL: /storage/{$path}");
    }

    /**
     * Export drivers to Excel format (using CSV for now, can be enhanced with proper Excel library)
     */
    private function exportToExcel(Collection $drivers, string $filename)
    {
        // For now, export as CSV with .xlsx extension
        // Can be enhanced with libraries like PhpSpreadsheet for true Excel format
        $this->exportToCsv($drivers, $filename);
        $oldPath = 'exports/' . $filename . '.csv';
        $newPath = 'exports/' . $filename . '.xlsx';

        Storage::move($oldPath, $newPath);
        $this->info("Excel file saved to: storage/app/{$newPath}");
    }

    /**
     * Get export headers
     */
    private function getExportHeaders(): array
    {
        return [
            'Driver ID',
            'First Name',
            'Surname',
            'Full Name',
            'Email',
            'Phone',
            'Date of Birth',
            'Gender',
            'Status',
            'Verification Status',
            'KYC Status',
            'KYC Step',
            'License Number',
            'License Class',
            'License Expiry Date',
            'State of Origin',
            'LGA of Origin',
            'Residential Address',
            'Residence State',
            'Residence LGA',
            'Bank Name',
            'Account Number',
            'Account Name',
            'BVN',
            'Employment Status',
            'Current Employer',
            'Years of Experience',
            'Vehicle Types',
            'Available for Night Shifts',
            'Available for Weekends',
            'Profile Completion %',
            'Verified By',
            'Verified At',
            'Created At',
            'Updated At',
        ];
    }

    /**
     * Format driver data for export
     */
    private function formatDriverForExport(Drivers $driver): array
    {
        return [
            $driver->driver_id,
            $driver->first_name,
            $driver->surname,
            $driver->full_name,
            $driver->email,
            $driver->phone,
            $driver->date_of_birth?->format('Y-m-d'),
            $driver->gender,
            $driver->status,
            $driver->verification_status,
            $driver->kyc_status,
            $driver->kyc_step,
            $driver->license_number,
            $driver->license_class,
            $driver->license_expiry_date?->format('Y-m-d'),
            $driver->originState?->name,
            $driver->originLga?->name,
            $driver->residenceLocation?->address,
            $driver->residenceState?->name,
            $driver->residenceLga?->name,
            $driver->primaryBankingDetail?->bank?->name,
            $driver->primaryBankingDetail?->account_number,
            $driver->primaryBankingDetail?->account_name,
            $driver->bvn,
            $driver->is_working ? 'Employed' : 'Unemployed',
            $driver->currentEmployment?->company_name,
            $driver->years_of_experience,
            is_array($driver->vehicle_types) ? implode(', ', $driver->vehicle_types) : $driver->vehicle_types,
            $driver->available_for_night_shifts ? 'Yes' : 'No',
            $driver->available_for_weekend_work ? 'Yes' : 'No',
            $driver->profile_completion_percentage,
            $driver->verifiedBy?->name,
            $driver->verified_at?->format('Y-m-d H:i:s'),
            $driver->created_at?->format('Y-m-d H:i:s'),
            $driver->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
