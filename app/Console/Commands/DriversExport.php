<?php

namespace App\Console\Commands;

use App\Models\Drivers as Driver; // Using normalized Driver model
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use League\Csv\Writer;
use Symfony\Component\Console\Helper\ProgressBar;

class DriversExport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'drivers:export
                            {--format=csv : Export format (csv, json, xml)}
                            {--status= : Filter by driver status}
                            {--verification= : Filter by verification status}
                            {--kyc= : Filter by KYC status}
                            {--date-from= : Filter from date (Y-m-d)}
                            {--date-to= : Filter to date (Y-m-d)}
                            {--path= : Custom export path}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export driver data with various filters and formats';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $format = $this->option('format');
        $status = $this->option('status');
        $verification = $this->option('verification');
        $kyc = $this->option('kyc');
        $dateFrom = $this->option('date-from');
        $dateTo = $this->option('date-to');
        $customPath = $this->option('path');

        // Build query with filters
        $query = Driver::with([
            'documents',
            'performance',
            'bankingDetails',
            'nextOfKin',
            'categoryRequirements'
        ]);

        if ($status) {
            $query->where('status', $status);
        }

        if ($verification) {
            $query->where('verification_status', $verification);
        }

        if ($kyc) {
            $query->where('kyc_status', $kyc);
        }

        if ($dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        $drivers = $query->get();

        if ($drivers->isEmpty()) {
            $this->error('No drivers found matching the specified criteria.');
            return Command::FAILURE;
        }

        $this->info("Found {$drivers->count()} drivers to export.");
        $this->info("Exporting in {$format} format...");

        // Create progress bar
        $progressBar = $this->output->createProgressBar($drivers->count());
        $progressBar->start();

        $exportData = [];
        foreach ($drivers as $driver) {
            $exportData[] = $this->formatDriverData($driver);
            $progressBar->advance();
        }
        $progressBar->finish();
        $this->newLine();

        // Export based on format
        $filename = 'drivers_export_' . now()->format('Y-m-d_H-i-s');
        $path = $customPath ?: storage_path('exports');

        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }

        switch ($format) {
            case 'csv':
                $this->exportToCsv($exportData, $path . '/' . $filename . '.csv');
                break;
            case 'json':
                $this->exportToJson($exportData, $path . '/' . $filename . '.json');
                break;
            case 'xml':
                $this->exportToXml($exportData, $path . '/' . $filename . '.xml');
                break;
            default:
                $this->error("Unsupported format: {$format}");
                return Command::FAILURE;
        }

        $this->info("Export completed successfully!");
        $this->info("File saved to: {$path}/{$filename}.{$format}");

        return Command::SUCCESS;
    }

    /**
     * Format driver data for export
     */
    private function formatDriverData(Driver $driver): array
    {
        return [
            'driver_id' => $driver->driver_id,
            'first_name' => $driver->first_name,
            'middle_name' => $driver->middle_name,
            'surname' => $driver->surname,
            'full_name' => $driver->full_name,
            'email' => $driver->email,
            'phone' => $driver->phone,
            'phone_2' => $driver->phone_2,
            'date_of_birth' => $driver->date_of_birth,
            'gender' => $driver->gender,
            'religion' => $driver->religion,
            'blood_group' => $driver->blood_group,
            'height_meters' => $driver->height_meters,
            'disability_status' => $driver->disability_status,
            'status' => $driver->status,
            'verification_status' => $driver->verification_status,
            'kyc_status' => $driver->kyc_status,
            'is_active' => $driver->is_active,
            'is_available' => $driver->is_available,
            'verified_at' => $driver->verified_at,
            'verified_by' => $driver->verified_by,
            'verification_notes' => $driver->verification_notes,
            'profile_completion_percentage' => $driver->profile_completion_percentage,
            'last_active_at' => $driver->last_active_at,
            'created_at' => $driver->created_at,
            'updated_at' => $driver->updated_at,

            // Performance data
            'performance_rating' => $driver->performance?->rating,
            'total_jobs' => $driver->performance?->total_jobs,
            'total_earnings' => $driver->performance?->total_earnings,
            'completion_rate' => $driver->performance?->completion_rate,

            // Document counts
            'total_documents' => $driver->documents->count(),
            'approved_documents' => $driver->documents->where('verification_status', 'approved')->count(),
            'pending_documents' => $driver->documents->where('verification_status', 'pending')->count(),
            'rejected_documents' => $driver->documents->where('verification_status', 'rejected')->count(),

            // Banking info (masked for security)
            'has_banking_details' => $driver->bankingDetails->isNotEmpty(),
            'bank_name' => $driver->bankingDetails->first()?->bank_name,

            // Next of kin
            'has_next_of_kin' => $driver->nextOfKin->isNotEmpty(),
            'next_of_kin_name' => $driver->nextOfKin->first()?->full_name,

            // Category requirements
            'category_requirements_count' => $driver->categoryRequirements->count(),
        ];
    }

    /**
     * Export data to CSV format
     */
    private function exportToCsv(array $data, string $filePath): void
    {
        $csv = Writer::createFromPath($filePath, 'w+');
        $csv->setDelimiter(',');
        $csv->setEnclosure('"');
        $csv->setEscape('\\');

        // Add headers
        if (!empty($data)) {
            $csv->insertOne(array_keys($data[0]));
        }

        // Add data rows
        foreach ($data as $row) {
            $csv->insertOne($row);
        }
    }

    /**
     * Export data to JSON format
     */
    private function exportToJson(array $data, string $filePath): void
    {
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        file_put_contents($filePath, $json);
    }

    /**
     * Export data to XML format
     */
    private function exportToXml(array $data, string $filePath): void
    {
        $xml = new \SimpleXMLElement('<drivers/>');

        foreach ($data as $driver) {
            $driverXml = $xml->addChild('driver');
            foreach ($driver as $key => $value) {
                $driverXml->addChild($key, htmlspecialchars($value ?? ''));
            }
        }

        $xml->asXML($filePath);
    }
}
