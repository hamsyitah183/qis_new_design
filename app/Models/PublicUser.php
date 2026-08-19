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

    // Removed 'verification_attachment' – it's not a column anymore
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
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'doa_verified' => 'boolean',
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
                // 'verification_attachment' => null, // removed – no longer a column
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
}