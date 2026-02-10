<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPerformanceIndexesForPlannerActions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('guests', function (Blueprint $table) {
            $table->index(['event_type', 'side', 'sort_order', 'id'], 'guests_event_side_sort_id_idx');
            $table->index(['event_type', 'attendance_status'], 'guests_event_attendance_idx');
        });

        Schema::table('engagement_tasks', function (Blueprint $table) {
            $table->index(['task_status', 'due_date', 'start_date', 'created_at'], 'tasks_status_due_start_created_idx');
        });

        Schema::table('gifts', function (Blueprint $table) {
            $table->index(['group_sort_order', 'group_name', 'sort_order', 'id'], 'gifts_group_sort_name_item_idx');
            $table->index(['status', 'id'], 'gifts_status_id_idx');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->index(['entry_mode', 'type'], 'expenses_entry_mode_type_idx');
            $table->index(['entry_mode', 'created_at', 'id'], 'expenses_entry_mode_created_id_idx');
            $table->index(['entry_mode', 'updated_at', 'id'], 'expenses_entry_mode_updated_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropIndex('expenses_entry_mode_updated_id_idx');
            $table->dropIndex('expenses_entry_mode_created_id_idx');
            $table->dropIndex('expenses_entry_mode_type_idx');
        });

        Schema::table('gifts', function (Blueprint $table) {
            $table->dropIndex('gifts_status_id_idx');
            $table->dropIndex('gifts_group_sort_name_item_idx');
        });

        Schema::table('engagement_tasks', function (Blueprint $table) {
            $table->dropIndex('tasks_status_due_start_created_idx');
        });

        Schema::table('guests', function (Blueprint $table) {
            $table->dropIndex('guests_event_attendance_idx');
            $table->dropIndex('guests_event_side_sort_id_idx');
        });
    }
}
