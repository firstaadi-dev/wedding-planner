<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait EventTypeScoped
{
    public static function bootEventTypeScoped()
    {
        static::addGlobalScope('event_type', function (Builder $builder) {
            if (!app()->bound('currentWorkspace')) {
                return;
            }

            $workspace = app('currentWorkspace');
            $eventType = (string) ($workspace->active_event_type ?? '');
            if ($eventType === '') {
                return;
            }

            $builder->where($builder->qualifyColumn('event_type'), $eventType);
        });

        static::creating(function ($model) {
            if (!empty($model->event_type)) {
                return;
            }

            if (!app()->bound('currentWorkspace')) {
                return;
            }

            $workspace = app('currentWorkspace');
            $eventType = (string) ($workspace->active_event_type ?? 'lamaran');
            $model->event_type = $eventType;
        });
    }
}
