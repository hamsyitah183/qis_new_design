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
        DB::beginTransaction();
        try {
            // Get existing record or create new
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

            DB::commit();
            
            if(authUser()['type'] === 'internal') {
                $url = url('/internal/approved_publics');
            } else {
                $url = url('/profile');
            }

            $internalUser = PublicUser::where('uuid', $userId)->first();
            $actor = authUser()['user'];
            if ($internalUser->uuid !== $actor->uuid) {
                $internalUser->notify(new InternalUserEditedNotification(
                    'Your account was updated',
                    'Your account details were updated by ' . $actor->fullname,
                    $url // pass URL
                ));
            }

            return [
                'success' => true,
                'file_url' => $verification->verification_attachment,
                'message' => 'File uploaded successfully.',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Verification upload failed: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'An error occurred while uploading the file.',
                'error' => $e->getMessage(),
            ];
        }
    }
}
