<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime'
    ];

    public function offices() : HasMany
    {
        return $this->hasMany(Office::class);
    }

    public function verificationMails(): HasMany
    {
        return $this->hasMany(UserVerificationMail::class);
    }

    public function userRole(): HasOne
    {
        return $this->hasOne(UserRole::class);
    }

    public function twoFactorCode(): HasMany
    {
        return $this->hasMany(TwoFactorCode::class);
    }

    public function forgotPasswordMail(): HasMany
    {
        return $this->hasMany(ForgotPasswordMail::class);
    }
}
