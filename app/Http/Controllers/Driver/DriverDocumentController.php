<?php

namespace App\Http\Controllers\Driver;

use App\Http\Controllers\Controller;
use App\Models\DriverDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class DriverDocumentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:driver');
    }

    /**
     * Upload document for the authenticated driver
     */
    public function uploadDocument(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'document_file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240', // 10MB max
            'document_type' => 'required|string|in:nin,license_front,license_back,profile_picture,passport_photo,employment_letter,service_certificate,vehicle_papers,insurance,other',
            'description' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $driver = Auth::guard('driver')->user();
            $file = $request->file('document_file');

            // Read file content as binary
            $fileContent = file_get_contents($file->getRealPath());

            // Create document record with binary content
            $document = DriverDocument::create([
                'driver_id' => $driver->id,
                'document_type' => $request->input('document_type'),
                'file_content' => $fileContent,
                'verification_status' => 'pending',
                'description' => $request->input('description'),
                'uploaded_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Document uploaded successfully and pending review',
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
     * Get documents for the authenticated driver
     */
    public function getDocuments()
    {
        $driver = Auth::guard('driver')->user();

        $documents = DriverDocument::where('driver_id', $driver->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'documents' => $documents
        ]);
    }

    /**
     * Download/view a document (returns binary content)
     */
    public function downloadDocument(DriverDocument $document)
    {
        try {
            $driver = Auth::guard('driver')->user();

            // Verify document belongs to driver
            if ($document->driver_id !== $driver->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document not found'
                ], 404);
            }

            if (!$document->file_content) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document content not available'
                ], 404);
            }

            // Return binary content with appropriate headers
            return response($document->file_content, 200, [
                'Content-Type' => 'application/octet-stream',
                'Content-Disposition' => 'attachment; filename="' . $document->document_type . '_' . $document->id . '.bin"'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to download document: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a document (only if pending)
     */
    public function deleteDocument(DriverDocument $document)
    {
        try {
            $driver = Auth::guard('driver')->user();

            // Verify document belongs to driver
            if ($document->driver_id !== $driver->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document not found'
                ], 404);
            }

            // Only allow deletion if pending
            if ($document->verification_status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete document that has been reviewed'
                ], 403);
            }

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
}
