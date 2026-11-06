<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeToken extends Model
{
    /** @use HasFactory<\Database\Factories\TimeTokenFactory> */
    use HasFactory;
    
    protected $primaryKey = 'id_time_token';
    
    protected $fillable = [
        'time_token',
        'description',
        'is_active'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}
