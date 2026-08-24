<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovedPublic extends Model
{
    protected $table = 'approved_publics';

    // Removed 'verification_attachment' from fillable – it's an accessor now
    protected $fillable = [
        'user_id',
        'doa_verified',
        'approved_by',
        'status',
        'reason',
    ];

    // --------------------------------------------------------------
    // Relationships
    // --------------------------------------------------------------

    public function publicUser()
    {
        return $this->belongsTo(PublicUser::class, 'user_id', 'uuid');
    }

    public function approver()
    {
        return $this->belongsTo(InternalUser::class, 'approved_by', 'uuid');
    }

    /**
     * Get only identification document attachments for the user via the PublicUser relation.
     */
    public function userAttachments()
    {
        return $this->hasManyThrough(
            UserAttachment::class,
            PublicUser::class,
            'uuid',          // foreign key on PublicUser (matches user_id on ApprovedPublic)
            'user_id',       // foreign key on UserAttachment
            'user_id',       // local key on ApprovedPublic
            'uuid'           // local key on PublicUser
        )
        ->where('document_type', 'Identification Documents (IC / Passport)');
    }
    // --------------------------------------------------------------
    // Accessor that returns the latest verification attachment
    // --------------------------------------------------------------

    /**
     * Get the latest identification document attachment for the user.
     */
    public function verificationAttachments()
    {
        return $this->userAttachments()
            ->latest('created_at');
    }
}