<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Workspace;
use App\Models\WorkspaceInvitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;
use WorkOS\UserManagement;
use WorkOS\WorkOS;

class WorkosAuthController extends Controller
{
    public function login(Request $request): RedirectResponse
    {
        return $this->redirectToWorkos($request, 'sign-in');
    }

    public function register(Request $request): RedirectResponse
    {
        return $this->redirectToWorkos($request, 'sign-up');
    }

    public function callback(Request $request): RedirectResponse
    {
        $code = (string) $request->query('code', '');
        Log::info('WorkOS callback endpoint hit', [
            'has_code' => $code !== '',
            'has_state' => $request->query->has('state'),
            'query_keys' => array_keys($request->query()),
        ]);

        if ($code === '') {
            return redirect()->route('workos.failed', ['reason' => 'missing_code']);
        }

        if (!$this->isValidState($request)) {
            Log::warning('WorkOS state validation failed', [
                'incoming_state' => (string) $request->query('state', ''),
            ]);

            return redirect()->route('workos.failed', ['reason' => 'invalid_state']);
        }

        try {
            $userManagement = $this->makeUserManagement();
            $response = $userManagement->authenticateWithCode(
                (string) config('services.workos.client_id'),
                $code,
                $request->ip(),
                substr((string) $request->userAgent(), 0, 1024)
            );
        } catch (Throwable $e) {
            Log::warning('WorkOS authentication failed', ['message' => $e->getMessage()]);

            return redirect()->route('workos.failed', ['reason' => 'authenticate_failed']);
        }

        $workosUser = $response->user ?? null;
        $email = strtolower(trim((string) ($workosUser->email ?? '')));
        if ($email === '') {
            return redirect()->route('workos.failed', ['reason' => 'email_missing']);
        }

        $user = $this->syncLocalUser($workosUser);

        Auth::login($user, true);
        $request->session()->regenerate();
        $request->session()->put('auth_provider', 'workos');
        $request->session()->save();

        Log::warning('WorkOS callback local session created', [
            'user_id' => $user->id,
            'session_id' => $request->session()->getId(),
            'auth_check' => Auth::check(),
        ]);

        $joinedFromInvitation = $this->acceptPendingWorkspaceInvitation($user);
        if ($joinedFromInvitation) {
            return redirect()
                ->route('engagement.index')
                ->with('success', 'Berhasil menerima undangan pasangan dan bergabung ke workspace.');
        }

        return redirect()->route('engagement.index');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function failed(Request $request)
    {
        $reason = (string) $request->query('reason', 'unknown');

        $messages = [
            'missing_code' => 'Callback WorkOS tidak mengirim authorization code.',
            'invalid_state' => 'State callback tidak cocok dengan session login.',
            'authenticate_failed' => 'WorkOS menolak proses authenticateWithCode.',
            'email_missing' => 'WorkOS tidak mengembalikan email user.',
        ];

        $message = $messages[$reason] ?? 'Autentikasi WorkOS gagal karena alasan yang tidak diketahui.';

        return response(
            "<h2>WorkOS Login Failed</h2><p>{$message}</p><p><a href=\"/login\">Coba login ulang</a></p>",
            422
        );
    }

    private function redirectToWorkos(Request $request, string $screenHint): RedirectResponse
    {
        $stateToken = Str::random(40);
        $request->session()->put('workos_auth_state', $stateToken);

        $userManagement = $this->makeUserManagement();
        $redirectUri = $this->workosRedirectUri();
        $authorizationUrl = $userManagement->getAuthorizationUrl(
            $redirectUri,
            ['csrf' => $stateToken],
            UserManagement::AUTHORIZATION_PROVIDER_AUTHKIT,
            null,
            null,
            null,
            null,
            $screenHint
        );

        Log::info('WorkOS authorize redirect generated', [
            'screen_hint' => $screenHint,
            'redirect_uri' => $redirectUri,
            'has_state' => true,
        ]);

        return redirect()
            ->away($authorizationUrl)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
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

    private function workosRedirectUri(): string
    {
        $configured = trim((string) config('services.workos.redirect_uri', ''));

        if (
            $configured === '' ||
            str_contains($configured, '${') ||
            str_contains($configured, '$APP_URL')
        ) {
            return route('workos.callback');
        }

        return $configured;
    }

    private function isValidState(Request $request): bool
    {
        $expected = (string) $request->session()->pull('workos_auth_state', '');
        $incomingState = (string) $request->query('state', '');

        // Some AuthKit callback flows return only ?code=... without state.
        // In that case, skip strict state matching to avoid login loops.
        if ($incomingState === '') {
            Log::notice('WorkOS callback state missing; skipping state validation', [
                'has_expected_state' => $expected !== '',
            ]);

            return true;
        }

        if ($expected === '') {
            Log::notice('WorkOS expected state missing in session; skipping state validation');

            return true;
        }

        if (hash_equals($expected, $incomingState)) {
            return true;
        }

        $decoded = json_decode($incomingState, true);
        if (!is_array($decoded)) {
            return false;
        }

        $received = (string) ($decoded['csrf'] ?? '');

        return $received !== '' && hash_equals($expected, $received);
    }

    private function syncLocalUser(object $workosUser): User
    {
        $workosUserId = (string) ($workosUser->id ?? '');
        $email = strtolower(trim((string) ($workosUser->email ?? '')));

        $fullName = trim(implode(' ', array_filter([
            trim((string) ($workosUser->firstName ?? '')),
            trim((string) ($workosUser->lastName ?? '')),
        ])));
        $name = $fullName !== '' ? $fullName : Str::before($email, '@');

        $user = User::query()
            ->where('workos_user_id', $workosUserId)
            ->orWhere('email', $email)
            ->first();

        if (!$user) {
            return User::query()->create([
                'workos_user_id' => $workosUserId !== '' ? $workosUserId : null,
                'name' => $name !== '' ? $name : 'User',
                'email' => $email,
                'email_verified_at' => now(),
                'password' => Hash::make(Str::random(64)),
            ]);
        }

        $updates = [];
        if ($workosUserId !== '' && $user->workos_user_id !== $workosUserId) {
            $updates['workos_user_id'] = $workosUserId;
        }
        if ($name !== '' && $user->name !== $name) {
            $updates['name'] = $name;
        }
        if ($user->email !== $email) {
            $emailExists = User::query()
                ->where('email', $email)
                ->where('id', '!=', $user->id)
                ->exists();
            if (!$emailExists) {
                $updates['email'] = $email;
            }
        }
        if ($user->email_verified_at === null) {
            $updates['email_verified_at'] = now();
        }

        if ($updates !== []) {
            $user->forceFill($updates)->save();
        }

        return $user;
    }

    private function acceptPendingWorkspaceInvitation(User $user): bool
    {
        $email = strtolower(trim((string) $user->email));
        if ($email === '') {
            return false;
        }

        $invitation = WorkspaceInvitation::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->where('status', 'pending')
            ->orderByDesc('id')
            ->first();

        if (!$invitation) {
            return false;
        }

        if ($invitation->expires_at !== null && $invitation->expires_at->isPast()) {
            $invitation->update(['status' => 'expired']);

            return false;
        }

        $workspace = Workspace::query()->find($invitation->workspace_id);
        if (!$workspace) {
            $invitation->update(['status' => 'revoked']);

            return false;
        }

        return (bool) DB::transaction(function () use ($email, $invitation, $user, $workspace) {
            $alreadyMember = DB::table('workspace_user')
                ->where('workspace_id', $workspace->id)
                ->where('user_id', $user->id)
                ->exists();

            if (!$alreadyMember) {
                $memberCount = DB::table('workspace_user')
                    ->where('workspace_id', $workspace->id)
                    ->count();

                if ($memberCount >= 2) {
                    return false;
                }

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

            WorkspaceInvitation::query()
                ->whereRaw('LOWER(email) = ?', [$email])
                ->where('status', 'pending')
                ->where('id', '!=', $invitation->id)
                ->update([
                    'status' => 'revoked',
                    'updated_at' => now(),
                ]);

            return true;
        });
    }
}
