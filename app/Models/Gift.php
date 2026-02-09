<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gift extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'brand',
        'price',
        'paid_amount',
        'link',
        'status',
        'notes',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'budget' => 'decimal:2',
    ];
}
