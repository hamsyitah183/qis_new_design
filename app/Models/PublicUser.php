<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PublicUser extends Authenticatable implements MustVerifyEmail
{
    use Notifiable, HasRoles, HasFactory, HasActivityLog;

    protected $table = 'public_users';
    protected $guard_name = 'public';

    protected $primaryKey = 'uuid';
    public $incrementing = false; // important for UUID
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
        // 'verification_attachment',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

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
    }

    public function approved()
    {
        return $this->hasOne(ApprovedPublic::class, 'user_id', 'uuid');
    }
}
