<?php

namespace App\Models;

use App\Models\Concerns\EventTypeScoped;
use App\Models\Concerns\WorkspaceScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EngagementTask extends Model
{
    use HasFactory, WorkspaceScoped, EventTypeScoped;

    protected $fillable = [
        'workspace_id',
        'event_type',
        'title',
        'vendor',
        'price',
        'paid_amount',
        'down_payment',
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
        'down_payment' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'start_date' => 'date',
        'due_date' => 'date',
        'finish_date' => 'date',
    ];
}
