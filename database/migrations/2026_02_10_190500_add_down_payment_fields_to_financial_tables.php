<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddDownPaymentFieldsToFinancialTables extends Migration
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
            $table->decimal('down_payment', 15, 2)->default(0)->after('paid_amount');
        });

        Schema::table('gifts', function (Blueprint $table) {
            $table->decimal('down_payment', 15, 2)->default(0)->after('paid_amount');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->decimal('down_payment', 15, 2)->default(0)->after('paid_amount');
        });

        DB::statement('UPDATE engagement_tasks SET down_payment = COALESCE(paid_amount, 0)');
        DB::statement('UPDATE engagement_tasks SET paid_amount = COALESCE(price, paid_amount, 0)');
        DB::statement('UPDATE engagement_tasks SET remaining_amount = GREATEST(COALESCE(paid_amount, 0) - COALESCE(down_payment, 0), 0)');

        DB::statement('UPDATE gifts SET down_payment = COALESCE(paid_amount, 0)');
        DB::statement('UPDATE gifts SET paid_amount = COALESCE(price, paid_amount, 0)');

        DB::statement("UPDATE expenses SET down_payment = CASE WHEN entry_mode = 'auto' THEN COALESCE(paid_amount, 0) WHEN type = 'expense' THEN COALESCE(amount, 0) ELSE 0 END");
        DB::statement("UPDATE expenses SET paid_amount = CASE WHEN entry_mode = 'auto' THEN COALESCE(base_price, paid_amount, 0) WHEN type = 'expense' THEN COALESCE(amount, 0) ELSE 0 END");
        DB::statement("UPDATE expenses SET remaining_amount = CASE WHEN entry_mode = 'auto' THEN GREATEST(COALESCE(paid_amount, 0) - COALESCE(down_payment, 0), 0) WHEN type = 'expense' THEN 0 WHEN type = 'budget' THEN COALESCE(amount, 0) ELSE COALESCE(remaining_amount, 0) END");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn('down_payment');
        });

        Schema::table('gifts', function (Blueprint $table) {
            $table->dropColumn('down_payment');
        });

        Schema::table('engagement_tasks', function (Blueprint $table) {
            $table->dropColumn('down_payment');
        });
    }
}
