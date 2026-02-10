<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'type',
        'entry_mode',
        'source_type',
        'source_id',
        'amount',
        'base_price',
        'paid_amount',
        'down_payment',
        'remaining_amount',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'base_price' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'down_payment' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
    ];
}
