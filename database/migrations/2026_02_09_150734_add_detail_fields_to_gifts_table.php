<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddDetailFieldsToGiftsTable extends Migration
{
    public $withinTransaction = false;


    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('gifts', function (Blueprint $table) {
            $table->string('brand')->nullable()->after('name');
            $table->decimal('price', 15, 2)->nullable()->after('brand');
            $table->decimal('paid_amount', 15, 2)->nullable()->after('price');
        });

        DB::table('gifts')
            ->whereNull('price')
            ->update(['price' => DB::raw('COALESCE(budget, 0)')]);

        DB::table('gifts')
            ->whereNull('paid_amount')
            ->update(['paid_amount' => 0]);

        DB::table('gifts')
            ->where('status', 'pending')
            ->update(['status' => 'not_started']);

        DB::table('gifts')
            ->where('status', 'ordered')
            ->update(['status' => 'on_delivery']);

        DB::table('gifts')
            ->where('status', 'arrived')
            ->update(['status' => 'complete']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('gifts')
            ->where('status', 'not_started')
            ->update(['status' => 'pending']);

        DB::table('gifts')
            ->where('status', 'on_delivery')
            ->update(['status' => 'ordered']);

        DB::table('gifts')
            ->where('status', 'complete')
            ->update(['status' => 'arrived']);

        Schema::table('gifts', function (Blueprint $table) {
            $table->dropColumn([
                'brand',
                'price',
                'paid_amount',
            ]);
        });
    }
}
