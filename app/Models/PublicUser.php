<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use App\Traits\HasApplicationActivityLog;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PublicUser extends Authenticatable implements MustVerifyEmail
{
    use Notifiable, HasRoles, HasFactory, HasActivityLog, HasApplicationActivityLog;

    protected $guard = 'public';
    protected $table = 'public_users';
    protected $guard_name = 'public';
    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'uuid',
        'fullname',
        'no_ic',
        'email',
        'account_type',
        'phone_number',
        'office_number',
        'address_1',
        'address_2',
        'postcode',
        'district',
        'state',
        'password',
        'doa_verified',
        'email_verified_at',
        'person_in_charge'
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'doa_verified' => 'boolean',
        'person_in_charge' => 'array',
    ];

    protected static function booted()
    {
        static::creating(function ($user) {
            if (empty($user->uuid)) {
                $user->uuid = (string) Str::uuid();
            }
        });

        static::created(function ($user) {
            ApprovedPublic::create([
                'user_id' => $user->uuid,
                'doa_verified' => false,
                'status' => 'pending',
                'approved_by' => null,
                'reason' => null,
            ]);
        });
    }

    public function routeNotificationForMail(): string
    {
        return $this->email;
    }

    /**
     * ApprovedPublic record (approval status)
     */
    public function approved()
    {
        return $this->hasOne(ApprovedPublic::class, 'user_id', 'uuid');
    }

    /**
     * All uploaded attachments (UserAttachment records)
     */
    public function attachments()
    {
        return $this->hasMany(UserAttachment::class, 'user_id', 'uuid');
    }

    /**
     * Latest attachment – useful for quick access
     */
    public function latestAttachment()
    {
        return $this->hasOne(UserAttachment::class, 'user_id', 'uuid')->latest();
    }

    public function districtInfo()
    {
        return $this->hasOne(District::class, 'id', 'district');
    }

    public function stateInfo()
    {
        return $this->hasOne(State::class, 'id', 'state');
    }

    public function vehicles()
    {
        return $this->hasMany(UserVehicleList::class, 'user_id', 'uuid');
    }

    // ─── Person In Charge accessors ────────────────────────────
    public function getPersonInChargeAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    public function setPersonInChargeAttribute($value)
    {
        $this->attributes['person_in_charge'] = json_encode($value);
    }

    // ─── Document verification helpers ─────────────────────────

    /**
     * Get the status of all required documents for this user.
     *
     * @return array
     */
    public function getDocumentStatuses()
    {
        $requirements = DocumentRequirement::where('module', 'user')
            ->where('is_required', true)
            ->where('is_active', true)
            ->get();

        $attachments = $this->attachments()->get()->keyBy('document_type');

        $docStatus = [];
        foreach ($requirements as $req) {
            $attachment = $attachments->get($req->name);
            if ($attachment) {
                if (!$attachment->is_read) {
                    $status = 'pending';
                } else {
                    $isExpired = $req->requires_expiry
                        && $attachment->valid_until
                        && now()->greaterThan($attachment->valid_until);
                    $status = $isExpired ? 'expired' : 'uploaded';
                }
            } else {
                $status = 'missing';
            }
            $docStatus[] = [
                'requirement' => $req,
                'attachment' => $attachment,
                'status' => $status,
            ];
        }

        return $docStatus;
    }

    /**
     * Check if the user has any missing or expired required documents.
     *
     * @return bool
     */
    public function hasMissingOrExpiredDocuments()
    {
        $statuses = $this->getDocumentStatuses();
        return collect($statuses)->contains(fn($item) => in_array($item['status'], ['missing', 'expired']));
    }

    /**
     * Check if the user is DOA‑verified and all required documents are valid.
     *
     * @return bool
     */
    public function isVerifiedAndDocumentsValid()
    {
        return $this->doa_verified && !$this->hasMissingOrExpiredDocuments();
    }
}