<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EngagementTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'vendor',
        'price',
        'paid_amount',
        'remaining_amount',
        'task_status',
        'start_date',
        'due_date',
        'finish_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'start_date' => 'date',
        'due_date' => 'date',
        'finish_date' => 'date',
    ];
}
