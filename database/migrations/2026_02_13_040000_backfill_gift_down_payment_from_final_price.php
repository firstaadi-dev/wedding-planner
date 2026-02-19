<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class BackfillGiftDownPaymentFromFinalPrice extends Migration
{
    public $withinTransaction = false;


    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('UPDATE gifts SET down_payment = COALESCE(paid_amount, 0)');

        DB::statement(
            "UPDATE expenses SET down_payment = COALESCE(paid_amount, 0), amount = COALESCE(paid_amount, 0), remaining_amount = 0 " .
            "WHERE entry_mode = 'auto' AND source_type = 'gift'"
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // No-op: this is a data backfill migration.
    }
}
