<?php

namespace App\Models;

use App\Models\Concerns\WorkspaceScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guest extends Model
{
    use HasFactory, WorkspaceScoped;

    protected $fillable = [
        'workspace_id',
        'name',
        'side',
        'event_type',
        'sort_order',
        'attendance_status',
        'phone',
        'notes',
    ];
}
