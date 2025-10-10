<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DocumentStorageService
{
    protected $documentDisk;
    protected $secureDisk;
    protected $tempDisk;
    protected $ocrResultsDisk;

    protected $allowedTypes;
    protected $maxFileSize;
    protected $documentPaths;

    public function __construct()
    {
        $this->documentDisk = Storage::disk('documents');
        $this->secureDisk = Storage::disk('secure_documents');
        $this->tempDisk = Storage::disk('temp');
        $this->ocrResultsDisk = Storage::disk('ocr_results');

        $this->allowedTypes = config('verification.file_storage.allowed_types', ['jpg', 'jpeg', 'png', 'pdf']);
        $this->maxFileSize = config('verification.file_storage.max_file_size', 10240); // KB
        $this->documentPaths = config('verification.file_storage.paths', []);
    }

    public function storeDriverDocument($driverId, UploadedFile $file, $documentType, $isSecure = false)
    {
        try {
            // Validate file
            $validation = $this->validateFile($file, $documentType);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'error' => $validation['error'],
                    'file_path' => null
                ];
            }

            // Generate unique filename
            $filename = $this->generateSecureFilename($driverId, $documentType, $file->getClientOriginalExtension());
            
            // Determine storage path
            $path = $this->getDocumentPath($documentType, $driverId);
            $fullPath = $path . '/' . $filename;

            // Choose storage disk
            $disk = $isSecure ? $this->secureDisk : $this->documentDisk;

            // Store file
            $stored = $disk->putFileAs($path, $file, $filename);

            if ($stored) {
                Log::info('Document stored successfully', [
                    'driver_id' => $driverId,
                    'document_type' => $documentType,
                    'file_path' => $fullPath,
                    'file_size' => $file->getSize(),
                    'is_secure' => $isSecure
                ]);

                return [
                    'success' => true,
                    'file_path' => $fullPath,
                    'filename' => $filename,
                    'original_name' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'storage_disk' => $isSecure ? 'secure_documents' : 'documents'
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Failed to store file',
                    'file_path' => null
                ];
            }

        } catch (\Exception $e) {
            Log::error('Document storage failed', [
                'driver_id' => $driverId,
                'document_type' => $documentType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => 'Document storage failed: ' . $e->getMessage(),
                'file_path' => null
            ];
        }
    }

    public function getDriverDocument($filePath, $isSecure = false)
    {
        try {
            $disk = $isSecure ? $this->secureDisk : $this->documentDisk;
            
            if (!$disk->exists($filePath)) {
                return [
                    'success' => false,
                    'error' => 'File not found',
                    'content' => null
                ];
            }

            $content = $disk->get($filePath);
            $mimeType = $disk->mimeType($filePath);
            $size = $disk->size($filePath);

            return [
                'success' => true,
                'content' => $content,
                'mime_type' => $mimeType,
                'file_size' => $size,
                'file_path' => $filePath
            ];

        } catch (\Exception $e) {
            Log::error('Document retrieval failed', [
                'file_path' => $filePath,
                'is_secure' => $isSecure,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'Document retrieval failed: ' . $e->getMessage(),
                'content' => null
            ];
        }
    }

    public function deleteDriverDocument($filePath, $isSecure = false)
    {
        try {
            $disk = $isSecure ? $this->secureDisk : $this->documentDisk;
            
            if (!$disk->exists($filePath)) {
                return [
                    'success' => false,
                    'error' => 'File not found'
                ];
            }

            $deleted = $disk->delete($filePath);

            if ($deleted) {
                Log::info('Document deleted successfully', [
                    'file_path' => $filePath,
                    'is_secure' => $isSecure
                ]);

                return ['success' => true];
            } else {
                return [
                    'success' => false,
                    'error' => 'Failed to delete file'
                ];
            }

        } catch (\Exception $e) {
            Log::error('Document deletion failed', [
                'file_path' => $filePath,
                'is_secure' => $isSecure,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'Document deletion failed: ' . $e->getMessage()
            ];
        }
    }

    public function storeTempFile(UploadedFile $file)
    {
        try {
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = 'processing/' . Carbon::now()->format('Y/m/d');
            
            $stored = $this->tempDisk->putFileAs($path, $file, $filename);

            if ($stored) {
                return [
                    'success' => true,
                    'file_path' => $path . '/' . $filename,
                    'full_path' => $this->tempDisk->path($path . '/' . $filename)
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Failed to store temporary file'
                ];
            }

        } catch (\Exception $e) {
            Log::error('Temporary file storage failed', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'Temporary file storage failed: ' . $e->getMessage()
            ];
        }
    }

    public function storeOCRResult($driverId, $documentType, $ocrData)
    {
        try {
            $filename = "driver_{$driverId}_{$documentType}_" . Carbon::now()->format('Y-m-d_H-i-s') . '.json';
            $path = "driver_{$driverId}/ocr_results";
            $fullPath = $path . '/' . $filename;

            $stored = $this->ocrResultsDisk->put($fullPath, json_encode($ocrData, JSON_PRETTY_PRINT));

            if ($stored) {
                return [
                    'success' => true,
                    'file_path' => $fullPath
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Failed to store OCR result'
                ];
            }

        } catch (\Exception $e) {
            Log::error('OCR result storage failed', [
                'driver_id' => $driverId,
                'document_type' => $documentType,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'OCR result storage failed: ' . $e->getMessage()
            ];
        }
    }

    public function cleanupTempFiles($olderThanHours = 24)
    {
        try {
            $cutoffTime = Carbon::now()->subHours($olderThanHours);
            $files = $this->tempDisk->allFiles('processing');
            
            $deletedCount = 0;
            foreach ($files as $file) {
                $lastModified = Carbon::createFromTimestamp($this->tempDisk->lastModified($file));
                
                if ($lastModified->lt($cutoffTime)) {
                    $this->tempDisk->delete($file);
                    $deletedCount++;
                }
            }

            Log::info('Temporary files cleanup completed', [
                'deleted_count' => $deletedCount,
                'cutoff_time' => $cutoffTime
            ]);

            return [
                'success' => true,
                'deleted_count' => $deletedCount
            ];

        } catch (\Exception $e) {
            Log::error('Temporary files cleanup failed', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'Cleanup failed: ' . $e->getMessage()
            ];
        }
    }

    protected function validateFile(UploadedFile $file, $documentType)
    {
        // Check file size
        $fileSizeKB = $file->getSize() / 1024;
        if ($fileSizeKB > $this->maxFileSize) {
            return [
                'valid' => false,
                'error' => "File size ({$fileSizeKB}KB) exceeds maximum allowed size ({$this->maxFileSize}KB)"
            ];
        }

        // Check file extension
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, $this->allowedTypes)) {
            return [
                'valid' => false,
                'error' => "File type '{$extension}' is not allowed. Allowed types: " . implode(', ', $this->allowedTypes)
            ];
        }

        // Check document type specific extensions
        $documentTypeAllowed = config("verification.file_storage.document_types.{$documentType}", $this->allowedTypes);
        if (!in_array($extension, $documentTypeAllowed)) {
            return [
                'valid' => false,
                'error' => "File type '{$extension}' is not allowed for {$documentType}. Allowed types: " . implode(', ', $documentTypeAllowed)
            ];
        }

        // Check if file is actually an image/PDF
        $mimeType = $file->getMimeType();
        $allowedMimeTypes = [
            'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/bmp',
            'application/pdf'
        ];
        
        if (!in_array($mimeType, $allowedMimeTypes)) {
            return [
                'valid' => false,
                'error' => "Invalid file format. MIME type: {$mimeType}"
            ];
        }

        return ['valid' => true];
    }

    protected function generateSecureFilename($driverId, $documentType, $extension)
    {
        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
        $random = Str::random(8);
        return "driver_{$driverId}_{$documentType}_{$timestamp}_{$random}.{$extension}";
    }

    protected function getDocumentPath($documentType, $driverId)
    {
        $basePath = $this->documentPaths[$documentType] ?? "documents/{$documentType}";
        return $basePath . "/driver_{$driverId}";
    }

    public function getStorageStats()
    {
        try {
            $stats = [
                'documents' => [
                    'total_files' => count($this->documentDisk->allFiles()),
                    'total_size' => $this->calculateDiskSize($this->documentDisk)
                ],
                'secure_documents' => [
                    'total_files' => count($this->secureDisk->allFiles()),
                    'total_size' => $this->calculateDiskSize($this->secureDisk)
                ],
                'temp_files' => [
                    'total_files' => count($this->tempDisk->allFiles()),
                    'total_size' => $this->calculateDiskSize($this->tempDisk)
                ],
                'ocr_results' => [
                    'total_files' => count($this->ocrResultsDisk->allFiles()),
                    'total_size' => $this->calculateDiskSize($this->ocrResultsDisk)
                ]
            ];

            return [
                'success' => true,
                'stats' => $stats
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Failed to get storage stats: ' . $e->getMessage()
            ];
        }
    }

    protected function calculateDiskSize($disk)
    {
        $totalSize = 0;
        $files = $disk->allFiles();
        
        foreach ($files as $file) {
            $totalSize += $disk->size($file);
        }
        
        return $totalSize; // bytes
    }
}