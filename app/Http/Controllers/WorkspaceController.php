<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use App\Models\WorkspaceInvitation;
use App\Models\WorkspacePaymentTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;
use WorkOS\UserManagement;
use WorkOS\WorkOS;

class WorkspaceController extends Controller
{
    public function switchMode(Request $request)
    {
        $validated = $request->validate([
            'event_type' => ['required', 'in:lamaran,resepsi'],
        ]);

        $workspace = $this->currentWorkspace();
        $workspace->update([
            'active_event_type' => $validated['event_type'],
        ]);

        if ($request->expectsJson() || $request->ajax() || $request->wantsJson()) {
            return response()->json([
                'message' => 'Mode global diperbarui.',
                'event_type' => $validated['event_type'],
            ]);
        }

        return back()->with('success', 'Mode global diperbarui.');
    }

    public function invitePartner(Request $request)
    {
        $workspace = $this->currentWorkspace();
        $user = Auth::user();

        $this->ensureOwner($workspace, (int) $user->id);

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        $email = strtolower(trim($validated['email']));
        if ($email === strtolower((string) $user->email)) {
            return back()->withErrors(['email' => 'Email pasangan tidak boleh sama dengan akun Anda.']);
        }

        $memberCount = DB::table('workspace_user')->where('workspace_id', $workspace->id)->count();
        if ($memberCount >= 2) {
            return back()->withErrors(['email' => 'Workspace ini sudah memiliki 2 anggota.']);
        }

        $alreadyMember = DB::table('users')
            ->join('workspace_user', 'workspace_user.user_id', '=', 'users.id')
            ->where('workspace_user.workspace_id', $workspace->id)
            ->whereRaw('LOWER(users.email) = ?', [$email])
            ->exists();

        if ($alreadyMember) {
            return back()->withErrors(['email' => 'Email tersebut sudah menjadi anggota workspace ini.']);
        }

        try {
            $userManagement = $this->makeUserManagement();
        } catch (RuntimeException $e) {
            return back()->withErrors([
                'email' => 'Konfigurasi WorkOS belum lengkap. Isi WORKOS_API_KEY dan WORKOS_CLIENT_ID.',
            ]);
        }

        $pendingInvitations = WorkspaceInvitation::query()
            ->where('workspace_id', $workspace->id)
            ->whereRaw('LOWER(email) = ?', [$email])
            ->where('status', 'pending')
            ->get();

        foreach ($pendingInvitations as $pendingInvitation) {
            if (!$pendingInvitation->workos_invitation_id) {
                continue;
            }

            try {
                $userManagement->revokeInvitation($pendingInvitation->workos_invitation_id);
            } catch (Throwable $e) {
                Log::warning('Failed to revoke previous WorkOS invitation', [
                    'invitation_id' => $pendingInvitation->id,
                    'workos_invitation_id' => $pendingInvitation->workos_invitation_id,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        WorkspaceInvitation::query()
            ->where('workspace_id', $workspace->id)
            ->whereRaw('LOWER(email) = ?', [$email])
            ->where('status', 'pending')
            ->update([
                'status' => 'revoked',
                'updated_at' => now(),
            ]);

        try {
            $workosInvitation = $userManagement->sendInvitation(
                $email,
                null,
                7,
                $user->workos_user_id ?: null,
                null
            );
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors([
                'email' => 'Gagal mengirim undangan WorkOS. Periksa konfigurasi WorkOS dan coba lagi.',
            ]);
        }

        $acceptUrl = (string) ($workosInvitation->acceptInvitationUrl ?? '');
        $invitation = WorkspaceInvitation::create([
            'workspace_id' => $workspace->id,
            'invited_by_user_id' => $user->id,
            'email' => $email,
            'token' => Str::random(64),
            'workos_invitation_id' => $workosInvitation->id ?? null,
            'workos_accept_url' => $acceptUrl !== '' ? $acceptUrl : null,
            'status' => 'pending',
            'expires_at' => $this->parseWorkosTimestamp($workosInvitation->expiresAt ?? null) ?? now()->addDays(7),
        ]);

        $successMessage = 'Undangan pasangan berhasil dikirim via WorkOS.';
        if ($acceptUrl !== '') {
            $successMessage .= ' Link undangan: ' . $acceptUrl;
        } else {
            $successMessage .= ' Cek inbox pasangan untuk membuka invitation URL.';
        }

        return back()->with('success', $successMessage)->with('workspace_invitation_id', $invitation->id);
    }

    public function acceptInvitation(Request $request, string $token)
    {
        $user = Auth::user();

        $invitation = WorkspaceInvitation::query()
            ->where('token', $token)
            ->where('status', 'pending')
            ->firstOrFail();

        if ($invitation->expires_at !== null && $invitation->expires_at->isPast()) {
            $invitation->update(['status' => 'expired']);
            return redirect()->route('engagement.index')->withErrors(['workspace' => 'Undangan sudah kedaluwarsa.']);
        }

        if (strtolower((string) $invitation->email) !== strtolower((string) $user->email)) {
            abort(403, 'Undangan ini bukan untuk email akun Anda.');
        }

        $workspace = Workspace::query()->findOrFail($invitation->workspace_id);
        $alreadyMember = DB::table('workspace_user')
            ->where('workspace_id', $workspace->id)
            ->where('user_id', $user->id)
            ->exists();
        if (!$alreadyMember) {
            $memberCount = DB::table('workspace_user')
                ->where('workspace_id', $workspace->id)
                ->count();
            if ($memberCount >= 2) {
                return redirect()->route('engagement.index')->withErrors([
                    'workspace' => 'Workspace ini sudah penuh.',
                ]);
            }
        }

        DB::transaction(function () use ($workspace, $invitation, $user) {
            $alreadyMember = DB::table('workspace_user')
                ->where('workspace_id', $workspace->id)
                ->where('user_id', $user->id)
                ->exists();

            if (!$alreadyMember) {
                DB::table('workspace_user')->insert([
                    'workspace_id' => $workspace->id,
                    'user_id' => $user->id,
                    'role' => 'member',
                    'invited_by_user_id' => $invitation->invited_by_user_id,
                    'joined_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $user->forceFill(['current_workspace_id' => $workspace->id])->save();

            $invitation->update([
                'status' => 'accepted',
                'accepted_by_user_id' => $user->id,
                'accepted_at' => now(),
            ]);
        });

        return redirect()->route('engagement.index')->with('success', 'Berhasil bergabung ke workspace pasangan.');
    }

    public function upgradeToProSandbox()
    {
        $workspace = $this->currentWorkspace();
        $user = Auth::user();

        $this->ensureOwner($workspace, (int) $user->id);

        DB::transaction(function () use ($workspace, $user) {
            WorkspacePaymentTransaction::create([
                'workspace_id' => $workspace->id,
                'created_by_user_id' => $user->id,
                'provider' => 'sandbox',
                'reference' => 'SBX-' . strtoupper(Str::random(10)),
                'amount' => 100000,
                'currency' => 'IDR',
                'status' => 'paid',
                'meta' => [
                    'note' => 'Sandbox upgrade to Pro (manual simulation)',
                ],
                'paid_at' => now(),
            ]);

            $workspace->update([
                'plan_code' => 'pro',
                'plan_status' => 'active',
                'plan_price' => 100000,
                'plan_started_at' => now(),
                'plan_expires_at' => null,
                'grace_ends_at' => null,
            ]);
        });

        return back()->with('success', 'Sandbox payment sukses. Workspace sekarang Pro (Unlimited).');
    }

    private function currentWorkspace(): Workspace
    {
        $workspace = app()->bound('currentWorkspace') ? app('currentWorkspace') : null;
        if ($workspace instanceof Workspace) {
            return $workspace;
        }

        $user = Auth::user();
        $workspaceId = (int) ($user->current_workspace_id ?? 0);

        return Workspace::query()->findOrFail($workspaceId);
    }

    private function makeUserManagement(): UserManagement
    {
        $apiKey = (string) config('services.workos.api_key', '');
        $clientId = (string) config('services.workos.client_id', '');

        if ($apiKey === '' || $clientId === '') {
            throw new RuntimeException('WORKOS_API_KEY / WORKOS_CLIENT_ID belum diisi.');
        }

        WorkOS::setApiKey($apiKey);
        WorkOS::setClientId($clientId);

        return new UserManagement();
    }

    private function parseWorkosTimestamp($timestamp): ?Carbon
    {
        if (!is_string($timestamp) || trim($timestamp) === '') {
            return null;
        }

        try {
            return Carbon::parse($timestamp);
        } catch (Throwable $e) {
            return null;
        }
    }

    private function ensureOwner(Workspace $workspace, int $userId): void
    {
        $role = DB::table('workspace_user')
            ->where('workspace_id', $workspace->id)
            ->where('user_id', $userId)
            ->value('role');

        if ($role !== 'owner') {
            abort(403, 'Hanya owner workspace yang dapat melakukan aksi ini.');
        }
    }
}
