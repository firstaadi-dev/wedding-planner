<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('users') || Schema::hasColumn('users', 'workos_user_id')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('workos_user_id')->nullable()->unique()->after('id');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('users') || !Schema::hasColumn('users', 'workos_user_id')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_workos_user_id_unique');
            $table->dropColumn('workos_user_id');
        });
    }
};
