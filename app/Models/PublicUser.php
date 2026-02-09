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
    public $incrementing = false; // important for UUID
    protected $keyType = 'string';

    protected $fillable = ['uuid', 'fullname', 'no_ic', 'email', 'account_type',
     'phone_number', 'office_number', 'address_1', 'address_2', 'postcode', 'district', 'state', 'password', 'doa_verified', 'verification_attachment', 'email_verified_at'];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'doa_verified' => 'boolean',
    ];

    protected static function booted()
    {
        // Generate UUID before creating
        static::creating(function ($user) {
            if (empty($user->uuid)) {
                $user->uuid = (string) Str::uuid();
            }
        });

        // Create approved_publics row AFTER user is created
        static::created(function ($user) {
            ApprovedPublic::create([
                'user_id' => $user->uuid,
                'doa_verified' => false,
                'verification_attachment' => null,
                'status' => 'pending', // or default status you want
                'approved_by' => null,
                'reason' => null,
            ]);
        });
    }

    public function approved()
    {
        return $this->hasOne(ApprovedPublic::class, 'user_id', 'uuid');
    }
}
