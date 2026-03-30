<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('engagement_tasks', function (Blueprint $table) {
            $table->string('event_type', 20)->default('lamaran')->after('id');
        });

        Schema::table('gifts', function (Blueprint $table) {
            $table->string('event_type', 20)->default('lamaran')->after('id');
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->string('event_type', 20)->default('lamaran')->after('id');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->string('event_type', 20)->default('lamaran')->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('engagement_tasks', function (Blueprint $table) {
            $table->dropColumn('event_type');
        });

        Schema::table('gifts', function (Blueprint $table) {
            $table->dropColumn('event_type');
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn('event_type');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn('event_type');
        });
    }
};
