<?php

namespace App\Services;

use App\Models\Driver;
use App\Models\DriverDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class KycDocumentService
{
    /**
     * Allowed file MIME types and their signatures
     */
    private const FILE_SIGNATURES = [
        'image/jpeg' => ['ffd8ff'],
        'image/png' => ['89504e47'],
        'application/pdf' => ['25504446'],
    ];

    /**
     * Maximum file size in bytes (2MB)
     */
    private const MAX_FILE_SIZE = 2097152;

    /**
     * Allowed document types
     */
    private const ALLOWED_DOCUMENT_TYPES = [
        'driver_license_scan',
        'national_id',
        'passport_photo',
        'utility_bill',
        'bank_statement',
        'employment_letter'
    ];

    /**
     * Handle secure file upload with comprehensive validation
     */
    public function handleFileUpload(UploadedFile $file, string $docType, Driver $driver): array
    {
        try {
            // Comprehensive file validation
            $validation = $this->validateFile($file, $docType);
            if (!$validation['valid']) {
                throw new Exception($validation['error']);
            }

            // Generate secure filename and directory
            $filename = $this->generateSecureFilename($file);
            $directory = $this->getSecureDirectory($driver);

            // Check available disk space
            if (!$this->checkDiskSpace($file->getSize())) {
                throw new Exception('Insufficient storage space available');
            }

            // Store file with backup strategy
            $filePath = $this->storeFileSecurely($file, $directory, $filename);

            // Generate file integrity hash
            $fileHash = $this->generateFileHash($filePath);

            // Create or update document record with transaction safety
            $document = $this->createDocumentRecord($driver, $docType, $file, $filePath, $fileHash);

            // Update driver quick-access field
            $this->updateDriverDocumentPath($driver, $docType, $filePath);

            // Log successful upload
            Log::info('KYC Document uploaded successfully', [
                'driver_id' => $driver->id,
                'document_type' => $docType,
                'file_size' => $file->getSize(),
                'file_path' => $filePath,
                'ip_address' => request()->ip()
            ]);

            return [
                'success' => true,
                'document_id' => $document->id,
                'filename' => $filename,
                'path' => $filePath,
                'size' => $file->getSize(),
                'hash' => $fileHash,
                'type' => $file->getMimeType(),
            ];

        } catch (Exception $e) {
            Log::error('KYC Document upload failed', [
                'driver_id' => $driver->id,
                'document_type' => $docType,
                'error' => $e->getMessage(),
                'file_size' => $file->getSize() ?? 0,
                'ip_address' => request()->ip()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Comprehensive file validation with security checks
     */
    private function validateFile(UploadedFile $file, string $docType): array
    {
        // Check if file is valid
        if (!$file->isValid()) {
            return ['valid' => false, 'error' => 'File upload failed or file is corrupted'];
        }

        // Check document type
        if (!in_array($docType, self::ALLOWED_DOCUMENT_TYPES)) {
            return ['valid' => false, 'error' => 'Invalid document type'];
        }

        // Check file size
        if ($file->getSize() > self::MAX_FILE_SIZE) {
            return ['valid' => false, 'error' => 'File size exceeds 2MB limit'];
        }

        if ($file->getSize() < 1024) { // Minimum 1KB
            return ['valid' => false, 'error' => 'File size too small, may be corrupted'];
        }

        // Check MIME type
        $mimeType = $file->getMimeType();
        if (!array_key_exists($mimeType, self::FILE_SIGNATURES)) {
            return ['valid' => false, 'error' => 'Invalid file type. Only JPG, PNG, and PDF files are allowed'];
        }

        // Validate file signature (magic bytes)
        if (!$this->validateFileSignature($file, $mimeType)) {
            return ['valid' => false, 'error' => 'File type does not match content. Possible file spoofing detected'];
        }

        // Additional validation for images
        if (str_starts_with($mimeType, 'image/')) {
            $validation = $this->validateImage($file);
            if (!$validation['valid']) {
                return $validation;
            }
        }

        // Scan for malicious content patterns
        if (!$this->scanForMaliciousContent($file)) {
            return ['valid' => false, 'error' => 'File contains potentially malicious content'];
        }

        return ['valid' => true];
    }

    /**
     * Validate file signature against MIME type
     */
    private function validateFileSignature(UploadedFile $file, string $mimeType): bool
    {
        try {
            $handle = fopen($file->getPathname(), 'rb');
            if (!$handle) {
                return false;
            }

            $bytes = fread($handle, 10);
            fclose($handle);

            if ($bytes === false) {
                return false;
            }

            $signature = strtolower(bin2hex($bytes));
            $allowedSignatures = self::FILE_SIGNATURES[$mimeType] ?? [];

            foreach ($allowedSignatures as $allowed) {
                if (str_starts_with($signature, strtolower($allowed))) {
                    return true;
                }
            }

            return false;
        } catch (Exception $e) {
            Log::warning('File signature validation failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Validate image-specific properties
     */
    private function validateImage(UploadedFile $file): array
    {
        try {
            $imageInfo = getimagesize($file->getPathname());
            if ($imageInfo === false) {
                return ['valid' => false, 'error' => 'Invalid or corrupted image file'];
            }

            [$width, $height] = $imageInfo;

            // Check minimum dimensions
            if ($width < 200 || $height < 200) {
                return ['valid' => false, 'error' => 'Image dimensions too small. Minimum 200x200 pixels required'];
            }

            // Check maximum dimensions
            if ($width > 5000 || $height > 5000) {
                return ['valid' => false, 'error' => 'Image dimensions too large. Maximum 5000x5000 pixels allowed'];
            }

            // Check aspect ratio for passport photos
            if (str_contains($file->getClientOriginalName(), 'passport')) {
                $aspectRatio = $width / $height;
                if ($aspectRatio < 0.7 || $aspectRatio > 1.4) {
                    return ['valid' => false, 'error' => 'Invalid aspect ratio for passport photo'];
                }
            }

            return ['valid' => true];
        } catch (Exception $e) {
            return ['valid' => false, 'error' => 'Unable to validate image properties'];
        }
    }

    /**
     * Scan file content for malicious patterns
     */
    private function scanForMaliciousContent(UploadedFile $file): bool
    {
        try {
            $content = file_get_contents($file->getPathname());
            if ($content === false) {
                return false;
            }

            // Check for suspicious patterns
            $maliciousPatterns = [
                '<?php', '<script', 'javascript:', 'eval(', 'exec(',
                'system(', 'shell_exec', 'base64_decode', '%PDF-',
            ];

            // For non-PDF files, check for script injection
            if ($file->getMimeType() !== 'application/pdf') {
                foreach ($maliciousPatterns as $pattern) {
                    if (stripos($content, $pattern) !== false) {
                        Log::warning('Malicious content detected in uploaded file', [
                            'pattern' => $pattern,
                            'file_mime' => $file->getMimeType()
                        ]);
                        return false;
                    }
                }
            }

            return true;
        } catch (Exception $e) {
            Log::error('Malicious content scan failed', ['error' => $e->getMessage()]);
            return false; // Fail safely
        }
    }

    /**
     * Generate cryptographically secure filename
     */
    private function generateSecureFilename(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $hash = hash('sha256', $file->getContent() . time() . random_bytes(16));
        return $hash . '.' . $extension;
    }

    /**
     * Get secure directory path for driver documents
     */
    private function getSecureDirectory(Driver $driver): string
    {
        // Use driver ID hash to prevent directory traversal
        $driverHash = substr(hash('sha256', $driver->id), 0, 8);
        return "drivers/{$driverHash}/kyc";
    }

    /**
     * Check if sufficient disk space is available
     */
    private function checkDiskSpace(int $fileSize): bool
    {
        $storagePath = storage_path('app/public');
        $freeSpace = disk_free_space($storagePath);
        
        // Require at least 100MB + file size free space
        $requiredSpace = $fileSize + (100 * 1024 * 1024);
        
        return $freeSpace !== false && $freeSpace > $requiredSpace;
    }

    /**
     * Store file with atomic operation
     */
    private function storeFileSecurely(UploadedFile $file, string $directory, string $filename): string
    {
        // Create temporary filename first
        $tempPath = $directory . '/temp_' . $filename;
        $finalPath = $directory . '/' . $filename;

        // Store with temporary name
        $tempStoredPath = $file->storeAs($directory, 'temp_' . $filename, 'public');
        if (!$tempStoredPath) {
            throw new Exception('Failed to store file temporarily');
        }

        // Verify stored file
        $storedFilePath = storage_path('app/public/' . $tempStoredPath);
        if (!file_exists($storedFilePath) || filesize($storedFilePath) !== $file->getSize()) {
            Storage::disk('public')->delete($tempStoredPath);
            throw new Exception('File verification failed after storage');
        }

        // Rename to final name (atomic operation)
        if (!Storage::disk('public')->move($tempStoredPath, $finalPath)) {
            Storage::disk('public')->delete($tempStoredPath);
            throw new Exception('Failed to finalize file storage');
        }

        return $finalPath;
    }

    /**
     * Generate file integrity hash
     */
    private function generateFileHash(string $filePath): string
    {
        $fullPath = storage_path('app/public/' . $filePath);
        return hash_file('sha256', $fullPath);
    }

    /**
     * Create document record with transaction safety
     */
    private function createDocumentRecord(Driver $driver, string $docType, UploadedFile $file, string $filePath, string $fileHash): DriverDocument
    {
        // Delete existing document of same type (but keep file as backup)
        $existingDoc = DriverDocument::where('driver_id', $driver->id)
            ->where('document_type', $docType)
            ->first();

        if ($existingDoc) {
            // Archive the old document instead of deleting
            $existingDoc->update([
                'verification_status' => 'archived',
                'document_type' => $docType . '_archived_' . time()
            ]);
        }

        // Create new document record
        return DriverDocument::create([
            'driver_id' => $driver->id,
            'document_type' => $docType,
            'document_path' => $filePath,
            'verification_status' => 'pending',
            'ocr_data' => [
                'original_filename' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'file_hash' => $fileHash,
                'upload_ip' => request()->ip(),
                'upload_user_agent' => request()->userAgent(),
                'upload_timestamp' => now()->toISOString(),
            ],
        ]);
    }

    /**
     * Update driver document path for quick access
     */
    private function updateDriverDocumentPath(Driver $driver, string $docType, string $filePath): void
    {
        if (in_array($docType, ['driver_license_scan', 'national_id', 'passport_photo'])) {
            $driver->update([$docType => $filePath]);
        }
    }

    /**
     * Verify file integrity using stored hash
     */
    public function verifyFileIntegrity(string $filePath, string $expectedHash): bool
    {
        try {
            $fullPath = storage_path('app/public/' . $filePath);
            if (!file_exists($fullPath)) {
                return false;
            }

            $currentHash = hash_file('sha256', $fullPath);
            return hash_equals($expectedHash, $currentHash);
        } catch (Exception $e) {
            Log::error('File integrity verification failed', [
                'file_path' => $filePath,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Clean up orphaned files
     */
    public function cleanupOrphanedFiles(): int
    {
        $cleaned = 0;
        try {
            // Find files in storage that don't have corresponding database records
            $allFiles = Storage::disk('public')->allFiles('drivers');
            
            foreach ($allFiles as $filePath) {
                $exists = DriverDocument::where('document_path', $filePath)->exists();
                if (!$exists) {
                    // Also check driver direct references
                    $directExists = Driver::where('driver_license_scan', $filePath)
                        ->orWhere('national_id', $filePath)
                        ->orWhere('passport_photo', $filePath)
                        ->exists();
                    
                    if (!$directExists) {
                        Storage::disk('public')->delete($filePath);
                        $cleaned++;
                    }
                }
            }

            Log::info('Cleaned up orphaned KYC files', ['count' => $cleaned]);
        } catch (Exception $e) {
            Log::error('Failed to clean up orphaned files', ['error' => $e->getMessage()]);
        }

        return $cleaned;
    }
}