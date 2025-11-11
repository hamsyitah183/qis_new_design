<?php

namespace App\Models;

use App\Traits\HasActivityLog;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Models\Role;

class InternalUser extends Authenticatable implements MustVerifyEmail
{
    //
    
    use Notifiable, HasRoles, HasRoles, HasActivityLog;

    protected $table = 'internal_users';
    protected $guard_name = 'internal';
    protected $fillable = ['fullname', 'username', 'email', 'phone', 'position', 'office', 'password', 'no_ic'];
    protected $hidden = ['password', 'remember_token'];

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

    // public function roles()
    // {
    //     return $this->belongsToMany(Role::class, 'internal_user_roles', 'user_id', 'role_id');
    // }
}
