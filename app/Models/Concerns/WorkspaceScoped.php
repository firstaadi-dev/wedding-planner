<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait WorkspaceScoped
{
    public static function bootWorkspaceScoped()
    {
        static::addGlobalScope('workspace', function (Builder $builder) {
            if (!app()->bound('currentWorkspace')) {
                return;
            }

            $workspace = app('currentWorkspace');
            $workspaceId = (int) ($workspace->id ?? 0);
            if ($workspaceId <= 0) {
                return;
            }

            $builder->where($builder->qualifyColumn('workspace_id'), $workspaceId);
        });

        static::creating(function ($model) {
            if (!empty($model->workspace_id)) {
                return;
            }

            if (!app()->bound('currentWorkspace')) {
                return;
            }

            $workspace = app('currentWorkspace');
            $workspaceId = (int) ($workspace->id ?? 0);
            if ($workspaceId > 0) {
                $model->workspace_id = $workspaceId;
            }
        });
    }
}
