<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class InternalUser extends Authenticatable implements MustVerifyEmail
{
    use Notifiable, HasRoles, HasActivityLog, HasApiTokens;

    protected $guard = 'internal';

    protected $table = 'internal_users';
    protected $guard_name = 'internal';
    protected $fillable = ['uuid', 'fullname', 'username', 'email', 'phone_number', 'position', 'office', 'branch', 'password', 'no_ic'];
    protected $hidden = ['password', 'remember_token'];

    protected $primaryKey = 'uuid';
    public $incrementing = false; // important for UUID
    protected $keyType = 'string';

    public function hasVerifiedEmail()
    {
        return !is_null($this->email_verified_at);
    }

    public function markEmailAsVerified()
    {
        $this->email_verified_at = now();
        $this->save();
    }

    protected static function booted()
    {
        static::creating(function ($user) {
            if (empty($user->uuid)) {
                $user->uuid = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    public function approvalsGiven()
    {
        return $this->hasMany(ApprovedPublic::class, 'approved_by', 'uuid');
    }

    public function receivesBroadcastNotificationsOn()
    {
        return 'private-internal-user.' . $this->uuid;
    }

    public function boundaryOfficer()
    {
        return $this->hasOne(
            BoundaryOfficer::class,
            'user_id', // boundary_officers.user_id
            'uuid'     // internal_users.uuid
        );
    }

    public function routeNotificationForMail(): string
    {
        return $this->email;
    }

    // public function roles()
    // {
    //     return $this->belongsToMany(Role::class, 'internal_user_roles', 'user_id', 'role_id');
    // }
}
