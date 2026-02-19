<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddGroupingFieldsToVendorsTable extends Migration
{
    public $withinTransaction = false;


    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->string('group_name', 150)->nullable()->after('vendor_name');
            $table->unsignedInteger('group_sort_order')->default(0)->after('group_name');
            $table->index(['group_sort_order', 'group_name', 'vendor_name', 'id'], 'vendors_group_sort_name_vendor_idx');
        });

        DB::statement("UPDATE vendors v SET group_sort_order = grp.rn FROM (SELECT COALESCE(group_name, '') AS group_key, ROW_NUMBER() OVER (ORDER BY COALESCE(group_name, '')) AS rn FROM vendors GROUP BY COALESCE(group_name, '')) grp WHERE COALESCE(v.group_name, '') = grp.group_key");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropIndex('vendors_group_sort_name_vendor_idx');
            $table->dropColumn(['group_sort_order', 'group_name']);
        });
    }
}
