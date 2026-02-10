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
        'group_name',
        'group_sort_order',
        'price',
        'paid_amount',
        'down_payment',
        'sort_order',
        'link',
        'status',
        'notes',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'down_payment' => 'decimal:2',
        'group_sort_order' => 'integer',
        'sort_order' => 'integer',
        'budget' => 'decimal:2',
    ];
}
