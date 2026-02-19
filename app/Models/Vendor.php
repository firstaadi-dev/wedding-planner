<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_name',
        'group_name',
        'group_sort_order',
        'contact_name',
        'contact_number',
        'contact_email',
        'website',
        'reference',
        'status',
    ];

    protected $casts = [
        'group_sort_order' => 'integer',
    ];
}
