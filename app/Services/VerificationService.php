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
                // Delete old file if it exists
                if (
                    !empty($verification->verification_attachment)
                    && file_exists(public_path($verification->verification_attachment))
                ) {
                    unlink(public_path($verification->verification_attachment));
                }

                // Destination path for new uploads
                $destinationPath = public_path('storage/app/public/verifications');

                // Create directory if not exists
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }

                // Save new file
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move($destinationPath, $filename);

                // Update the model with new file path
                $verification->verification_attachment = 'storage/app/public/verifications/' . $filename;
            }

            $verification->status = 'waiting for approval';
            $verification->save();

            return true;
        } catch (\Exception $e) {
            Log::error('Verification upload failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
