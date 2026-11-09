<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\Hash;

#[Fillable(['cedula', 'username','name', 'email', 'password', 'id_time_token','id_expiry_month', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Métodos obligatorios para JWTSubject
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function getPasswordIsDefaultAttribute()
    {
        return Hash::check($this->cedula, $this->password);
    }

    public function passwordHistories(){
        return $this->hasMany(PasswordHistory::class);
    }

    public function expiryMonth(){
        return $this->belongsTo(ExpiryMonth::class, 'id_expiry_month', 'id_expiry_month');
    }
    
    public function timeToken(){
        return $this->belongsTo(TimeToken::class, 'id_time_token', 'id_time_token');
    }
}
