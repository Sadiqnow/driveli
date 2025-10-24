<?php

namespace App\Services;

use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Services\SecureFileUploadService;

class DriverFileService
{
    protected $secureUploader;

    public function __construct(SecureFileUploadService $secureUploader)
    {
        $this->secureUploader = $secureUploader;
    }

    /**
     * Handle file uploads during driver update
     */
    public function handleFileUploads(Request $request, Driver $driver): void
    {
        $uploadFields = ['profile_photo', 'passport_photograph', 'license_front_image', 'license_back_image'];

        foreach ($uploadFields as $field) {
            if ($request->hasFile($field)) {
                try {
                    $file = $request->file($field);
                    if ($file && $file->isValid()) {
                        // Validate file type
                        $allowedTypes = ['jpeg', 'jpg', 'png', 'pdf'];
                        $extension = strtolower($file->getClientOriginalExtension());

                        if (!in_array($extension, $allowedTypes)) {
                            Log::warning("Invalid file type for {$field}: {$extension}");
                            continue;
                        }

                        // Delete old file if exists
                        $oldPath = $driver->{$field === 'profile_photo' ? 'profile_picture' : $field};
                        if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                            Storage::disk('public')->delete($oldPath);
                        }

                        // Generate safe filename
                        $fileName = time() . '_' . $field . '_' . uniqid() . '.' . $extension;

                        $path = $file->storeAs('driver_documents', $fileName, 'public');

                        // Update driver with new file path
                        $updateField = $field === 'profile_photo' ? 'profile_picture' : $field;
                        $driver->update([$updateField => $path]);
                    }
                } catch (\Exception $e) {
                    Log::error("File upload failed for {$field}: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Get driver documents for viewing
     */
    public function getDriverDocuments(Driver $driver): array
    {
        // Load related data
        $driver->load(['guarantors', 'verifiedBy']);

        // Get document-related fields
        $documents = [
            'profile_photo' => $driver->profile_photo,
            'license_front_image' => $driver->license_front_image,
            'license_back_image' => $driver->license_back_image,
            'nin_document' => $driver->nin_document,
            'passport_photograph' => $driver->passport_photograph,
            'additional_documents' => $driver->additional_documents,
        ];

        return [
            'driver' => $driver,
            'documents' => $documents
        ];
    }

    /**
     * Delete a specific document from driver
     */
    public function deleteDocument(Driver $driver, string $documentType): array
    {
        try {
            $documentFields = [
                'profile_photo' => 'profile_picture',
                'license_front' => 'license_front_image',
                'license_back' => 'license_back_image',
                'nin_document' => 'nin_document',
                'passport_photo' => 'passport_photograph',
            ];

            if (!isset($documentFields[$documentType])) {
                return [
                    'success' => false,
                    'message' => 'Invalid document type.'
                ];
            }

            $fieldName = $documentFields[$documentType];
            $filePath = $driver->$fieldName;

            if (!$filePath) {
                return [
                    'success' => false,
                    'message' => 'No document found for this type.'
                ];
            }

            // Delete file from storage
            if (Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }

            // Update driver record
            $driver->update([$fieldName => null]);

            Log::info("Document deleted for driver", [
                'driver_id' => $driver->driver_id,
                'document_type' => $documentType,
                'admin_id' => auth('admin')->id()
            ]);

            return [
                'success' => true,
                'message' => ucfirst(str_replace('_', ' ', $documentType)) . ' deleted successfully.'
            ];

        } catch (\Exception $e) {
            Log::error('Document deletion failed', [
                'driver_id' => $driver->id,
                'document_type' => $documentType,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to delete document: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Upload additional documents for driver
     */
    public function uploadAdditionalDocument(Request $request, Driver $driver): array
    {
        $request->validate([
            'document_type' => 'required|string|max:50',
            'document_file' => 'required|file|mimes:jpeg,png,jpg,pdf|max:5120',
            'description' => 'nullable|string|max:255'
        ]);

        try {
            $file = $request->file('document_file');
            if (!$file->isValid()) {
                return [
                    'success' => false,
                    'message' => 'Invalid file uploaded.'
                ];
            }

            // Generate filename
            $fileName = time() . '_additional_' . $request->document_type . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

            // Store file
            $path = $file->storeAs('driver_documents', $fileName, 'public');

            // Get existing additional documents
            $additionalDocs = $driver->additional_documents ? json_decode($driver->additional_documents, true) : [];

            // Add new document
            $additionalDocs[] = [
                'type' => $request->document_type,
                'filename' => $fileName,
                'path' => $path,
                'description' => $request->description,
                'uploaded_at' => now()->toISOString(),
                'uploaded_by' => auth('admin')->id()
            ];

            // Update driver
            $driver->update([
                'additional_documents' => json_encode($additionalDocs)
            ]);

            Log::info('Additional document uploaded for driver', [
                'driver_id' => $driver->driver_id,
                'document_type' => $request->document_type,
                'admin_id' => auth('admin')->id()
            ]);

            return [
                'success' => true,
                'message' => 'Document uploaded successfully.',
                'document' => end($additionalDocs)
            ];

        } catch (\Exception $e) {
            Log::error('Additional document upload failed', [
                'driver_id' => $driver->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to upload document: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get secure document URL for viewing
     */
    public function getSecureDocumentUrl(Driver $driver, string $documentType): array
    {
        try {
            $documentFields = [
                'profile_photo' => 'profile_picture',
                'license_front' => 'license_front_image',
                'license_back' => 'license_back_image',
                'nin_document' => 'nin_document',
                'passport_photo' => 'passport_photograph',
            ];

            if (!isset($documentFields[$documentType])) {
                return [
                    'success' => false,
                    'message' => 'Invalid document type.'
                ];
            }

            $fieldName = $documentFields[$documentType];
            $filePath = $driver->$fieldName;

            if (!$filePath) {
                return [
                    'success' => false,
                    'message' => 'Document not found.'
                ];
            }

            // Check if file exists
            if (!Storage::disk('public')->exists($filePath)) {
                return [
                    'success' => false,
                    'message' => 'Document file not found on server.'
                ];
            }

            // Generate secure URL (could implement temporary signed URLs here)
            $url = asset('storage/' . $filePath);

            return [
                'success' => true,
                'url' => $url,
                'filename' => basename($filePath)
            ];

        } catch (\Exception $e) {
            Log::error('Failed to get secure document URL', [
                'driver_id' => $driver->id,
                'document_type' => $documentType,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to generate document URL.'
            ];
        }
    }

    /**
     * Validate file upload for driver documents
     */
    public function validateFileUpload(Request $request, string $fieldName): array
    {
        $errors = [];

        if ($request->hasFile($fieldName)) {
            $file = $request->file($fieldName);

            // Check if file is valid
            if (!$file->isValid()) {
                $errors[] = 'The uploaded file is not valid.';
            }

            // Check file size (5MB limit)
            if ($file->getSize() > 5242880) {
                $errors[] = 'File size must not exceed 5MB.';
            }

            // Check file type
            $allowedTypes = ['jpeg', 'jpg', 'png', 'pdf'];
            $extension = strtolower($file->getClientOriginalExtension());

            if (!in_array($extension, $allowedTypes)) {
                $errors[] = 'File type not allowed. Only JPEG, PNG, and PDF files are accepted.';
            }

            // Check for malicious content (basic check)
            $mimeType = $file->getMimeType();
            $allowedMimeTypes = [
                'image/jpeg',
                'image/png',
                'application/pdf'
            ];

            if (!in_array($mimeType, $allowedMimeTypes)) {
                $errors[] = 'File content type not allowed.';
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Clean up orphaned files (files not referenced in database)
     */
    public function cleanupOrphanedFiles(): array
    {
        try {
            $stats = [
                'scanned' => 0,
                'orphaned' => 0,
                'deleted' => 0,
                'errors' => 0
            ];

            // Get all files in driver documents directory
            $files = Storage::disk('public')->files('driver_documents');

            foreach ($files as $file) {
                $stats['scanned']++;

                // Check if file is referenced in any driver record
                $fileName = basename($file);
                $isReferenced = Driver::where(function($query) use ($file) {
                    $query->where('profile_picture', $file)
                          ->orWhere('license_front_image', $file)
                          ->orWhere('license_back_image', $file)
                          ->orWhere('nin_document', $file)
                          ->orWhere('passport_photograph', $file);
                })->exists();

                // Also check additional documents JSON
                if (!$isReferenced) {
                    $drivers = Driver::whereNotNull('additional_documents')->get();
                    foreach ($drivers as $driver) {
                        $additionalDocs = json_decode($driver->additional_documents, true);
                        if (is_array($additionalDocs)) {
                            foreach ($additionalDocs as $doc) {
                                if (isset($doc['path']) && $doc['path'] === $file) {
                                    $isReferenced = true;
                                    break 2;
                                }
                            }
                        }
                    }
                }

                if (!$isReferenced) {
                    $stats['orphaned']++;

                    try {
                        Storage::disk('public')->delete($file);
                        $stats['deleted']++;
                        Log::info('Orphaned driver document deleted', ['file' => $file]);
                    } catch (\Exception $e) {
                        $stats['errors']++;
                        Log::error('Failed to delete orphaned file', [
                            'file' => $file,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }

            return [
                'success' => true,
                'message' => "Cleanup completed. Scanned: {$stats['scanned']}, Orphaned: {$stats['orphaned']}, Deleted: {$stats['deleted']}, Errors: {$stats['errors']}",
                'stats' => $stats
            ];

        } catch (\Exception $e) {
            Log::error('File cleanup failed', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => 'File cleanup failed: ' . $e->getMessage()
            ];
        }
    }
}
