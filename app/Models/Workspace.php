<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Workspace extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'owner_user_id',
        'active_event_type',
        'plan_code',
        'plan_status',
        'plan_price',
        'plan_started_at',
        'plan_expires_at',
        'grace_ends_at',
    ];

    protected $casts = [
        'plan_started_at' => 'datetime',
        'plan_expires_at' => 'datetime',
        'grace_ends_at' => 'datetime',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'workspace_user', 'workspace_id', 'user_id')
            ->withPivot(['role', 'invited_by_user_id', 'joined_at'])
            ->withTimestamps();
    }

    public function isPro(): bool
    {
        return $this->plan_code === 'pro' && $this->plan_status === 'active';
    }
}
