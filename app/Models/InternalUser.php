<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Sanctum\HasApiTokens;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class InternalUser extends Authenticatable implements MustVerifyEmail
{
    //
    use Notifiable, HasRoles, HasRoles;

    protected $table = 'internal_users';
    protected $guard_name = 'internal';
    protected $fillable = ['name', 'username', 'email', 'phone', 'position', 'office', 'password', 'no_ic'];
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
}
