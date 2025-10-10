<?php

namespace App\Events;

use App\Models\Driver;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DriverKycCompleted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Driver $driver;
    public array $completionData;

    /**
     * Create a new event instance.
     */
    public function __construct(Driver $driver, array $completionData = [])
    {
        $this->driver = $driver;
        $this->completionData = array_merge([
            'completed_at' => now()->toISOString(),
            'completion_method' => 'web_form',
            'documents_uploaded' => $this->getUploadedDocuments($driver),
            'steps_completed' => $this->getCompletedSteps($driver),
        ], $completionData);
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin-notifications'),
            new PrivateChannel('driver.' . $this->driver->id),
        ];
    }

    /**
     * Get uploaded documents summary.
     */
    private function getUploadedDocuments(Driver $driver): array
    {
        return $driver->documents()
            ->whereIn('document_type', ['driver_license_scan', 'national_id', 'passport_photo'])
            ->get()
            ->map(function($doc) {
                return [
                    'type' => $doc->document_type,
                    'uploaded_at' => $doc->created_at->toISOString(),
                    'file_size' => $doc->ocr_data['file_size'] ?? null,
                    'mime_type' => $doc->ocr_data['mime_type'] ?? null,
                ];
            })
            ->toArray();
    }

    /**
     * Get completed steps summary.
     */
    private function getCompletedSteps(Driver $driver): array
    {
        return [
            'step_1' => [
                'completed' => !is_null($driver->kyc_step_1_completed_at),
                'completed_at' => $driver->kyc_step_1_completed_at?->toISOString(),
                'data' => [
                    'driver_license_number' => $driver->driver_license_number,
                    'date_of_birth' => $driver->date_of_birth?->format('Y-m-d'),
                ]
            ],
            'step_2' => [
                'completed' => !is_null($driver->kyc_step_2_completed_at),
                'completed_at' => $driver->kyc_step_2_completed_at?->toISOString(),
                'data' => [
                    'full_name' => $driver->full_name,
                    'phone' => $driver->phone,
                    'email' => $driver->email,
                ]
            ],
            'step_3' => [
                'completed' => !is_null($driver->kyc_step_3_completed_at),
                'completed_at' => $driver->kyc_step_3_completed_at?->toISOString(),
                'documents_count' => count($this->completionData['documents_uploaded'])
            ]
        ];
    }
}