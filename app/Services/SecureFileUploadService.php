<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Secure File Upload Service
 * 
 * Handles secure file uploads with comprehensive validation and security checks
 */
class SecureFileUploadService
{
    private const ALLOWED_MIME_TYPES = [
        'image' => ['image/jpeg', 'image/png', 'image/jpg'],
        'document' => ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf']
    ];

    private const MAX_FILE_SIZES = [
        'image' => 2 * 1024 * 1024,    // 2MB
        'document' => 5 * 1024 * 1024, // 5MB
    ];

    private const DANGEROUS_EXTENSIONS = [
        'php', 'phtml', 'php3', 'php4', 'php5', 'phar',
        'exe', 'bat', 'cmd', 'com', 'pif', 'scr',
        'js', 'jar', 'vbs', 'wsf', 'wsh',
        'asp', 'aspx', 'jsp', 'py', 'rb', 'pl',
        'sh', 'bash', 'cgi', 'htaccess'
    ];

    /**
     * Validate and upload a file securely
     */
    public function uploadFile(UploadedFile $file, string $category = 'image', string $folder = 'uploads', string $customName = null): array
    {
        $this->validateFile($file, $category);
        $this->performSecurityChecks($file);
        $filename = $this->generateSecureFilename($file, $customName);
        $path = $file->storeAs($folder, $filename, 'public');
        $fullPath = storage_path('app/public/' . $path);
        $this->performPostUploadChecks($fullPath, $category);

        return [
            'success' => true,
            'path' => $path,
            'filename' => $filename,
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'original_name' => $file->getClientOriginalName(),
        ];
    }

    private function validateFile(UploadedFile $file, string $category): void
    {
        if (!$file->isValid()) {
            throw new \Exception('File upload failed: ' . $file->getErrorMessage());
        }

        $maxSize = self::MAX_FILE_SIZES[$category] ?? self::MAX_FILE_SIZES['image'];
        if ($file->getSize() > $maxSize) {
            $maxSizeMB = round($maxSize / (1024 * 1024), 1);
            throw new \Exception("File size exceeds maximum allowed size of {$maxSizeMB}MB");
        }

        $allowedMimeTypes = self::ALLOWED_MIME_TYPES[$category] ?? self::ALLOWED_MIME_TYPES['image'];
        if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
            throw new \Exception('File type not allowed');
        }

        $extension = strtolower($file->getClientOriginalExtension());
        if (in_array($extension, self::DANGEROUS_EXTENSIONS)) {
            throw new \Exception('File extension not allowed for security reasons');
        }

        if ($category === 'image') {
            $this->validateImageFile($file);
        }
    }

    private function validateImageFile(UploadedFile $file): void
    {
        $imageInfo = @getimagesize($file->getRealPath());
        
        if ($imageInfo === false) {
            throw new \Exception('Invalid image file or corrupted image');
        }

        list($width, $height) = $imageInfo;
        if ($width > 4000 || $height > 4000) {
            throw new \Exception('Image dimensions too large. Maximum 4000x4000 pixels');
        }

        if ($width < 50 || $height < 50) {
            throw new \Exception('Image dimensions too small. Minimum 50x50 pixels');
        }
    }

    private function performSecurityChecks(UploadedFile $file): void
    {
        $content = file_get_contents($file->getRealPath());
        
        if (strpos($content, '<?php') !== false || 
            strpos($content, '<?=') !== false || 
            strpos($content, '<script') !== false ||
            strpos($content, 'eval(') !== false) {
            throw new \Exception('File contains potentially malicious content');
        }
    }

    private function generateSecureFilename(UploadedFile $file, string $customName = null): string
    {
        $extension = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $file->getClientOriginalExtension()));
        
        if ($customName) {
            $baseName = preg_replace('/[^a-zA-Z0-9_-]/', '', $customName);
        } else {
            $baseName = Str::random(32);
        }
        
        return $baseName . '_' . time() . '.' . $extension;
    }

    private function performPostUploadChecks(string $fullPath, string $category): void
    {
        chmod($fullPath, 0644);

        if ($category === 'image') {
            $this->sanitizeImageFile($fullPath);
        }
    }

    private function sanitizeImageFile(string $fullPath): void
    {
        $imageInfo = @getimagesize($fullPath);
        if (!$imageInfo) return;

        $imageType = $imageInfo[2];
        
        try {
            switch ($imageType) {
                case IMAGETYPE_JPEG:
                    $image = @imagecreatefromjpeg($fullPath);
                    if ($image) {
                        imagejpeg($image, $fullPath, 85);
                        imagedestroy($image);
                    }
                    break;
                    
                case IMAGETYPE_PNG:
                    $image = @imagecreatefrompng($fullPath);
                    if ($image) {
                        imagepng($image, $fullPath, 6);
                        imagedestroy($image);
                    }
                    break;
            }
        } catch (\Exception $e) {
            unlink($fullPath);
            throw new \Exception('Image file validation failed during sanitization');
        }
    }

    public function deleteFile(string $path): bool
    {
        return Storage::disk('public')->exists($path) ? Storage::disk('public')->delete($path) : false;
    }
}