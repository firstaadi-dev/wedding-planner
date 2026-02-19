<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Auth\Events\Registered;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class RegisteredUserController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'password' => Hash::make($validated['password']),
        ]);

        $workspace = null;
        try {
            $workspace = Workspace::create([
                'name' => trim($validated['name']) . "'s Workspace",
                'owner_user_id' => $user->id,
                'active_event_type' => 'lamaran',
                'plan_code' => 'free',
                'plan_status' => 'active',
                'plan_price' => 0,
                'plan_started_at' => now(),
            ]);

            DB::table('workspace_user')->insert([
                'workspace_id' => $workspace->id,
                'user_id' => $user->id,
                'role' => 'owner',
                'joined_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $user->forceFill(['current_workspace_id' => $workspace->id])->save();
        } catch (QueryException $e) {
            if ($workspace) {
                Workspace::query()->where('id', $workspace->id)->delete();
            }
            User::query()->where('id', $user->id)->delete();

            Log::error('Workspace bootstrap failed during registration', [
                'sql_state' => $e->getCode(),
                'db_message' => $e->getMessage(),
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            throw $e;
        }

        event(new Registered($user));
        Auth::login($user);

        return redirect()->route('verification.notice');
    }
}
