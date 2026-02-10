<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddAutoTrackingColumnsToExpensesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->enum('entry_mode', ['manual', 'auto'])->default('manual')->after('type');
            $table->enum('source_type', ['task', 'gift'])->nullable()->after('entry_mode');
            $table->unsignedBigInteger('source_id')->nullable()->after('source_type');
            $table->decimal('base_price', 15, 2)->default(0)->after('amount');
            $table->decimal('paid_amount', 15, 2)->default(0)->after('base_price');
            $table->decimal('remaining_amount', 15, 2)->default(0)->after('paid_amount');
        });

        DB::table('expenses')->where('type', 'budget')->update([
            'base_price' => DB::raw('amount'),
            'remaining_amount' => DB::raw('amount'),
        ]);

        DB::table('expenses')->where('type', 'expense')->update([
            'paid_amount' => DB::raw('amount'),
        ]);

        Schema::table('expenses', function (Blueprint $table) {
            $table->unique(['source_type', 'source_id'], 'expenses_source_unique');
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
            $table->dropUnique('expenses_source_unique');
            $table->dropColumn([
                'entry_mode',
                'source_type',
                'source_id',
                'base_price',
                'paid_amount',
                'remaining_amount',
            ]);
        });
    }
}
