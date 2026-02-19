<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddDetailFieldsToEngagementTasksTable extends Migration
{
    public $withinTransaction = false;


    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('engagement_tasks', function (Blueprint $table) {
            $table->string('vendor')->nullable()->after('title');
            $table->decimal('price', 15, 2)->nullable()->after('vendor');
            $table->decimal('paid_amount', 15, 2)->nullable()->after('price');
            $table->decimal('remaining_amount', 15, 2)->nullable()->after('paid_amount');
            $table->string('task_status')->default('not_started')->after('status');
            $table->date('start_date')->nullable()->after('task_status');
            $table->date('finish_date')->nullable()->after('due_date');
        });

        DB::table('engagement_tasks')
            ->where('status', 'done')
            ->update(['task_status' => 'done']);

        DB::table('engagement_tasks')
            ->where('status', 'pending')
            ->update(['task_status' => 'not_started']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('engagement_tasks', function (Blueprint $table) {
            $table->dropColumn([
                'vendor',
                'price',
                'paid_amount',
                'remaining_amount',
                'task_status',
                'start_date',
                'finish_date',
            ]);
        });
    }
}
