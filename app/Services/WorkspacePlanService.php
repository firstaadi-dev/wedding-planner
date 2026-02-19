<?php

namespace App\Services;

use App\Models\Workspace;

class WorkspacePlanService
{
    public function planKey(Workspace $workspace): string
    {
        if ($workspace->plan_code === 'pro' && $workspace->plan_status === 'active') {
            return 'pro';
        }

        return 'free';
    }

    public function limits(Workspace $workspace): array
    {
        $planKey = $this->planKey($workspace);

        return config('plans.' . $planKey, config('plans.free'));
    }

    public function isPro(Workspace $workspace): bool
    {
        return $this->planKey($workspace) === 'pro';
    }

    public function getLimitFor(Workspace $workspace, string $key): ?int
    {
        $limits = $this->limits($workspace);
        $value = $limits[$key] ?? null;

        if ($value === null) {
            return null;
        }

        return max((int) $value, 0);
    }

    public function allowsCreation(Workspace $workspace, string $key, int $currentCount, int $adding = 1): bool
    {
        $limit = $this->getLimitFor($workspace, $key);
        if ($limit === null) {
            return true;
        }

        return ($currentCount + max($adding, 0)) <= $limit;
    }
}
