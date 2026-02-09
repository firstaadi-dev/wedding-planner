<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guest extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'side',
        'event_type',
        'sort_order',
        'attendance_status',
        'phone',
        'notes',
    ];
}
