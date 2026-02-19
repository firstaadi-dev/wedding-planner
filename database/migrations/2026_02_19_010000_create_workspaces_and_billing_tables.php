<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateWorkspacesAndBillingTables extends Migration
{
    public $withinTransaction = false;


    public function up()
    {
        if (!Schema::hasTable('workspaces')) {
            Schema::create('workspaces', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->unsignedBigInteger('owner_user_id')->nullable();
                $table->string('active_event_type', 20)->default('lamaran');
                $table->string('plan_code', 20)->default('free');
                $table->string('plan_status', 20)->default('active');
                $table->unsignedInteger('plan_price')->default(0);
                $table->timestamp('plan_started_at')->nullable();
                $table->timestamp('plan_expires_at')->nullable();
                $table->timestamp('grace_ends_at')->nullable();
                $table->timestamps();

                $table->index(['plan_code', 'plan_status'], 'workspaces_plan_idx');
            });
        }

        if (!Schema::hasTable('workspace_user')) {
            Schema::create('workspace_user', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('workspace_id');
                $table->unsignedBigInteger('user_id');
                $table->string('role', 20)->default('member');
                $table->unsignedBigInteger('invited_by_user_id')->nullable();
                $table->timestamp('joined_at')->nullable();
                $table->timestamps();

                $table->unique(['workspace_id', 'user_id'], 'workspace_user_unique');
                $table->index(['workspace_id', 'role'], 'workspace_user_role_idx');
            });
        }

        if (!Schema::hasTable('workspace_invitations')) {
            Schema::create('workspace_invitations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('workspace_id');
                $table->unsignedBigInteger('invited_by_user_id')->nullable();
                $table->unsignedBigInteger('accepted_by_user_id')->nullable();
                $table->string('email');
                $table->string('token', 128)->unique();
                $table->string('status', 20)->default('pending');
                $table->timestamp('expires_at')->nullable();
                $table->timestamp('accepted_at')->nullable();
                $table->timestamps();

                $table->index(['workspace_id', 'status'], 'workspace_invitations_workspace_status_idx');
                $table->index(['email', 'status'], 'workspace_invitations_email_status_idx');
            });
        }

        if (!Schema::hasTable('workspace_payment_transactions')) {
            Schema::create('workspace_payment_transactions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('workspace_id');
                $table->unsignedBigInteger('created_by_user_id')->nullable();
                $table->string('provider', 40)->default('sandbox');
                $table->string('reference', 120)->nullable();
                $table->unsignedInteger('amount');
                $table->string('currency', 10)->default('IDR');
                $table->string('status', 20)->default('pending');
                $table->json('meta')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->timestamps();

                $table->index(['workspace_id', 'status'], 'workspace_payments_workspace_status_idx');
                $table->index(['provider', 'reference'], 'workspace_payments_provider_ref_idx');
            });
        }

        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'current_workspace_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('current_workspace_id')->nullable()->after('remember_token');
            });
        }

        if (!Schema::hasTable('users') || !Schema::hasTable('workspaces')) {
            return;
        }

        $users = DB::table('users')
            ->select('id', 'name', 'current_workspace_id')
            ->orderBy('id')
            ->get();

        $firstWorkspaceId = (int) DB::table('workspaces')->orderBy('id')->value('id');

        foreach ($users as $user) {
            $workspaceId = (int) ($user->current_workspace_id ?? 0);

            if ($workspaceId <= 0) {
                $workspaceId = (int) DB::table('workspaces')->where('owner_user_id', $user->id)->orderBy('id')->value('id');
            }

            if ($workspaceId <= 0) {
                $workspaceId = (int) DB::table('workspaces')->insertGetId([
                    'name' => trim((string) ($user->name ?: 'Workspace')) . "'s Workspace",
                    'owner_user_id' => $user->id,
                    'active_event_type' => 'lamaran',
                    'plan_code' => 'free',
                    'plan_status' => 'active',
                    'plan_price' => 0,
                    'plan_started_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if (Schema::hasTable('workspace_user')) {
                DB::table('workspace_user')->updateOrInsert(
                    ['workspace_id' => $workspaceId, 'user_id' => $user->id],
                    [
                        'role' => 'owner',
                        'joined_at' => now(),
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }

            DB::table('users')->where('id', $user->id)->update(['current_workspace_id' => $workspaceId]);

            if ($firstWorkspaceId <= 0) {
                $firstWorkspaceId = $workspaceId;
            }
        }

        if ($firstWorkspaceId <= 0) {
            DB::table('workspaces')->insert([
                'name' => 'Default Workspace',
                'owner_user_id' => null,
                'active_event_type' => 'lamaran',
                'plan_code' => 'free',
                'plan_status' => 'active',
                'plan_price' => 0,
                'plan_started_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down()
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'current_workspace_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('current_workspace_id');
            });
        }

        Schema::dropIfExists('workspace_payment_transactions');
        Schema::dropIfExists('workspace_invitations');
        Schema::dropIfExists('workspace_user');
        Schema::dropIfExists('workspaces');
    }
}
