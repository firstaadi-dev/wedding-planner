<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkspacePaymentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'workspace_id',
        'created_by_user_id',
        'provider',
        'reference',
        'amount',
        'currency',
        'status',
        'meta',
        'paid_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'paid_at' => 'datetime',
    ];
}
