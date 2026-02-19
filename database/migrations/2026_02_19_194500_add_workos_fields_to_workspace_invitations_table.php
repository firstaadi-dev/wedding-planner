<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('workspace_invitations')) {
            return;
        }

        Schema::table('workspace_invitations', function (Blueprint $table) {
            if (!Schema::hasColumn('workspace_invitations', 'workos_invitation_id')) {
                $table->string('workos_invitation_id')->nullable()->after('token');
                $table->unique('workos_invitation_id', 'workspace_invitations_workos_invitation_id_unique');
            }

            if (!Schema::hasColumn('workspace_invitations', 'workos_accept_url')) {
                $table->text('workos_accept_url')->nullable()->after('workos_invitation_id');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('workspace_invitations')) {
            return;
        }

        Schema::table('workspace_invitations', function (Blueprint $table) {
            if (Schema::hasColumn('workspace_invitations', 'workos_invitation_id')) {
                $table->dropUnique('workspace_invitations_workos_invitation_id_unique');
                $table->dropColumn('workos_invitation_id');
            }

            if (Schema::hasColumn('workspace_invitations', 'workos_accept_url')) {
                $table->dropColumn('workos_accept_url');
            }
        });
    }
};
