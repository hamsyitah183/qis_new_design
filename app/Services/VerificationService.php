<?php

namespace App\Services;

use App\Models\ApprovedPublic;
use App\Models\UserAttachment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VerificationService
{
    public function uploadVerificationAttachment(
        string $userId,
        array $filesByDocId,
        array $documentTypes = [],
        array $validFrom = [],
        array $validUntil = []
    ) {
        try {
            DB::beginTransaction();

            $uploaded = [];

            foreach ($filesByDocId as $docId => $files) {
                $docType = $documentTypes[$docId] ?? null;
                $from = $validFrom[$docId] ?? null;
                $until = $validUntil[$docId] ?? null;

                foreach ($files as $file) {
                    if (!$file) {
                        continue;
                    }

                    // Generate a unique filename
                    $filename = time() . '_' . Str::random(6) . '_' . $file->getClientOriginalName();
                    
                    // Relative path on the 'public' disk (under user_attachments/)
                    $relativePath = 'user_attachments/' . $filename;
                    
                    // Store the file
                    $stored = Storage::disk('public')->put($relativePath, file_get_contents($file));
                    if (!$stored) {
                        throw new \Exception("Failed to store file: {$file->getClientOriginalName()}");
                    }

                    // Build the public URL path (starts with /storage/ because of the symbolic link)
                    $publicPath = '/storage/' . $relativePath;

                    // Create database record with the full public path
                    $attachment = UserAttachment::create([
                        'user_id' => $userId,
                        'document_type' => $docType,
                        'file_path' => $publicPath,   // e.g. '/storage/user_attachments/123456_abc_file.pdf'
                        'file_type' => $file->getClientMimeType(),
                        'file_size' => $file->getSize(),
                        'original_file_name' => $file->getClientOriginalName(),
                        'valid_from' => $from,
                        'valid_until' => $until,
                    ]);

                    $uploaded[] = $attachment;
                }
            }

            // Update approval status
            $verification = ApprovedPublic::firstOrNew(['user_id' => $userId]);
            $verification->status = 'waiting for approval';
            $verification->save();

            DB::commit();

            return [
                'success' => true,
                'message' => 'Files uploaded successfully.',
                'attachments' => $uploaded,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Verification upload failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage(),
            ];
        }
    }
}