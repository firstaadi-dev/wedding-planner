<?php

namespace App\Models;

use App\Models\Concerns\EventTypeScoped;
use App\Models\Concerns\WorkspaceScoped;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory, WorkspaceScoped, EventTypeScoped;

    protected $fillable = [
        'workspace_id',
        'event_type',
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
