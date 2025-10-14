<?php

namespace App\Http\Controllers\Drivers;

use App\Http\Controllers\Controller;
use App\Models\DriverDocument;
use App\Models\Drivers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class DriverFileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    /**
     * Upload document for a driver
     */
    public function uploadDocument(Request $request, Drivers $driver)
    {
        // Support older tests that send 'nin_document' key
        if ($request->hasFile('nin_document')) {
            // map to new expected keys
            $request->files->set('document_file', $request->file('nin_document'));
            $request->merge(['document_type' => 'nin']);
        }

            // Accept legacy 'nin_document' key used in tests and map to current 'document_file'
            if ($request->hasFile('nin_document')) {
                $fileKey = 'nin_document';
            } else {
                $fileKey = 'document_file';
            }

            $rules = [
                $fileKey => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240',
                'document_type' => 'required|string|max:100',
            ];

            $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $file = $request->file('document_file');
            $documentType = $request->input('document_type');
            
            // Generate unique filename
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $filePath = "driver_documents/{$driver->driver_id}/{$documentType}/" . $filename;
            
            // Store file
            $storedPath = $file->storeAs('driver_documents/' . $driver->driver_id . '/' . $documentType, $filename, 'local');
            
            // Create document record
            $document = DriverDocument::create([
                'driver_id' => $driver->id,
                'document_type' => $documentType,
                'original_filename' => $file->getClientOriginalName(),
                'stored_filename' => $filename,
                'file_path' => $storedPath,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'description' => $request->input('description'),
                'status' => 'pending_review',
                'uploaded_by' => Auth::id(),
                'uploaded_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Document uploaded successfully',
                'document' => $document
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload document: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get documents for a driver
     */
    public function getDocuments(Drivers $driver)
    {
        $documents = DriverDocument::where('driver_id', $driver->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'documents' => $documents
        ]);
    }

    /**
     * Delete a document
     */
    public function deleteDocument(Drivers $driver, DriverDocument $document)
    {
        try {
            // Verify document belongs to driver
            if ($document->driver_id !== $driver->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document not found for this driver'
                ], 404);
            }

            // Delete physical file
            if (Storage::exists($document->file_path)) {
                Storage::delete($document->file_path);
            }

            // Delete database record
            $document->delete();

            return response()->json([
                'success' => true,
                'message' => 'Document deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete document: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download a document
     */
    public function downloadDocument(Drivers $driver, DriverDocument $document)
    {
        try {
            // Verify document belongs to driver
            if ($document->driver_id !== $driver->id) {
                abort(404, 'Document not found for this driver');
            }

            // Check if file exists
            if (!Storage::exists($document->file_path)) {
                abort(404, 'Document file not found');
            }

            return Storage::download($document->file_path, $document->original_filename);

        } catch (\Exception $e) {
            abort(500, 'Failed to download document: ' . $e->getMessage());
        }
    }

    /**
     * Bulk upload documents
     */
    public function bulkUpload(Request $request, Drivers $driver)
    {
        $validator = Validator::make($request->all(), [
            'documents' => 'required|array|max:10',
            'documents.*.document_type' => 'required|string|in:license,nin,passport,profile_photo,vehicle_papers,insurance,other',
            'documents.*.document_file' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'documents.*.description' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $uploadedDocuments = [];
        $errors = [];

        foreach ($request->input('documents', []) as $index => $documentData) {
            try {
                $file = $request->file("documents.{$index}.document_file");
                $documentType = $documentData['document_type'];
                
                // Generate unique filename
                $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
                $storedPath = $file->storeAs('driver_documents/' . $driver->driver_id . '/' . $documentType, $filename, 'local');
                
                // Create document record
                $document = DriverDocument::create([
                    'driver_id' => $driver->id,
                    'document_type' => $documentType,
                    'original_filename' => $file->getClientOriginalName(),
                    'stored_filename' => $filename,
                    'file_path' => $storedPath,
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'description' => $documentData['description'] ?? null,
                    'status' => 'pending_review',
                    'uploaded_by' => Auth::id(),
                    'uploaded_at' => now()
                ]);

                $uploadedDocuments[] = $document;

            } catch (\Exception $e) {
                $errors[] = "Document {$index}: " . $e->getMessage();
            }
        }

        return response()->json([
            'success' => count($errors) === 0,
            'message' => count($errors) === 0 ? 'All documents uploaded successfully' : 'Some documents failed to upload',
            'uploaded_documents' => $uploadedDocuments,
            'errors' => $errors
        ]);
    }
}