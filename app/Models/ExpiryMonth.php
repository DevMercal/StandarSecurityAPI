<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpiryMonth extends Model
{
    /** @use HasFactory<\Database\Factories\ExpiryMonthFactory> */
    use HasFactory;

    protected $primaryKey = 'id_expiry_month';

    protected $fillable = [
        'expiry_months',
        'description'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}
