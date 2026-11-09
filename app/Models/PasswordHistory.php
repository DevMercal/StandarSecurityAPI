<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasswordHistory extends Model
{
    /** @use HasFactory<\Database\Factories\PasswordHistoryFactory> */
    use HasFactory;

    protected $primaryKey = 'id_password_history';

    protected $fillable = [
        'password',
        'user_id'
    ];
}
