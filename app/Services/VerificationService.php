<?php

namespace App\Services;

use App\Models\ApprovedPublic;
use App\Models\PublicUser;
use App\Notifications\InternalUserEditedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VerificationService
{
    public function uploadVerificationAttachment($userId, $file)
    {
        try {
            $verification = ApprovedPublic::firstOrNew(['user_id' => $userId]);

            if ($file) {
                // Delete old file if exists
                if (
                    !empty($verification->verification_attachment)
                    && file_exists(public_path($verification->verification_attachment))
                ) {
                    unlink(public_path($verification->verification_attachment));
                }

                $destinationPath = public_path('storage/app/public/verifications');

                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }

                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move($destinationPath, $filename);

                $verification->verification_attachment = 'storage/app/public/verifications/' . $filename;
            }

            $verification->status = 'waiting for approval';

            // authUser()['user']->verification_attachment = 'storage/app/public/verifications/' . $filename;
            $verification->save();

            

            return [
                'success' => true,
                'message' => 'File uploaded successfully.',
                'file_url' => asset($verification->verification_attachment)
            ];
        } catch (\Exception $e) {
            Log::error('Verification upload failed: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ];
        }
    }
}
