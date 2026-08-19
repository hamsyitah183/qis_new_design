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
     * Get all attachments of the user via the PublicUser relation.
     * This is a hasManyThrough shortcut.
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
        );
    }

    // --------------------------------------------------------------
    // Accessor that replaces the old 'verification_attachment' column
    // --------------------------------------------------------------

    /**
     * Get the latest verification attachment for the user,
     * or null if none exists.
     */
    public function verificationAttachments()
    {
        return $this->userAttachments()
            ->where('document_type', 'Identification Documents (IC / Passport)')
            ->latest('created_at');
    }
}