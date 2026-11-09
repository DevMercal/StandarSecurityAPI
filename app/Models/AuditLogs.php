<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLogs extends Model
{
    /** @use HasFactory<\Database\Factories\AuditLogsFactory> */
    use HasFactory;

    protected $fillable = [
        'entity',
        'entity_id',
        'action',
        'old_values',
        'new_values',
        'user_id',
        'ip_address'
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array'
    ];

    public function causer(){
        return $this->belongsTo(User::class, 'user_id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
}
