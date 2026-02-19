<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddGroupSortOrderToGiftsTable extends Migration
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
            $table->unsignedInteger('group_sort_order')->default(0)->after('group_name');
        });

        DB::statement("UPDATE gifts g SET group_sort_order = grp.rn FROM (SELECT COALESCE(group_name, '') AS group_key, ROW_NUMBER() OVER (ORDER BY COALESCE(group_name, '')) AS rn FROM gifts GROUP BY COALESCE(group_name, '')) grp WHERE COALESCE(g.group_name, '') = grp.group_key");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('gifts', function (Blueprint $table) {
            $table->dropColumn('group_sort_order');
        });
    }
}
