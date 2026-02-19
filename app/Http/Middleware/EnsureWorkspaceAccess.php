<?php

namespace App\Http\Middleware;

use App\Models\Workspace;
use App\Services\WorkspacePlanService;
use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

class EnsureWorkspaceAccess
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        $workspaceId = (int) ($user->current_workspace_id ?? 0);
        if ($workspaceId <= 0) {
            $workspaceId = (int) $user->workspaces()->orderBy('workspaces.id')->value('workspaces.id');
            if ($workspaceId <= 0) {
                $workspaceId = $this->createWorkspaceForUser($user);
            }

            $user->forceFill(['current_workspace_id' => $workspaceId])->save();
        }

        $workspace = Workspace::query()->find($workspaceId);
        if (!$workspace) {
            $workspaceId = $this->createWorkspaceForUser($user);
            $user->forceFill(['current_workspace_id' => $workspaceId])->save();
            $workspace = Workspace::query()->find($workspaceId);
        }

        $membership = $user->workspaces()
            ->where('workspaces.id', $workspace->id)
            ->first();

        if (!$membership) {
            $fallbackWorkspaceId = (int) $user->workspaces()->orderBy('workspaces.id')->value('workspaces.id');
            if ($fallbackWorkspaceId <= 0) {
                $fallbackWorkspaceId = $this->createWorkspaceForUser($user);
            }

            $user->forceFill(['current_workspace_id' => $fallbackWorkspaceId])->save();
            $workspace = Workspace::query()->findOrFail($fallbackWorkspaceId);
            $membership = $user->workspaces()->where('workspaces.id', $workspace->id)->first();
        }

        app()->instance('currentWorkspace', $workspace);

        $planService = app(WorkspacePlanService::class);
        $limits = $planService->limits($workspace);
        $members = $workspace->users()->orderBy('users.id')->get(['users.id', 'users.name', 'users.email']);

        View::share('currentWorkspace', $workspace);
        View::share('currentWorkspaceRole', $membership ? ($membership->pivot->role ?? 'member') : 'member');
        View::share('workspaceLimits', $limits);
        View::share('workspaceIsPro', $planService->isPro($workspace));
        View::share('workspaceMembers', $members);

        return $next($request);
    }

    private function createWorkspaceForUser($user): int
    {
        $workspace = null;
        try {
            $workspace = Workspace::create([
                'name' => trim((string) ($user->name ?: 'Workspace')) . "'s Workspace",
                'owner_user_id' => $user->id,
                'active_event_type' => 'lamaran',
                'plan_code' => 'free',
                'plan_status' => 'active',
                'plan_price' => 0,
                'plan_started_at' => now(),
            ]);

            DB::table('workspace_user')->updateOrInsert(
                ['workspace_id' => $workspace->id, 'user_id' => $user->id],
                [
                    'role' => 'owner',
                    'joined_at' => now(),
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            return (int) $workspace->id;
        } catch (QueryException $e) {
            if ($workspace) {
                Workspace::query()->where('id', $workspace->id)->delete();
            }

            Log::error('Workspace bootstrap failed in middleware', [
                'sql_state' => $e->getCode(),
                'db_message' => $e->getMessage(),
                'user_id' => $user->id,
            ]);

            throw $e;
        }
    }
}
