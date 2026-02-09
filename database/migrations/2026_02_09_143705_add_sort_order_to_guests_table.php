<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddSortOrderToGuestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('guests', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')->default(0)->after('event_type');
        });

        $groups = DB::table('guests')
            ->select('event_type', 'side')
            ->groupBy('event_type', 'side')
            ->get();

        foreach ($groups as $group) {
            $items = DB::table('guests')
                ->where('event_type', $group->event_type)
                ->where('side', $group->side)
                ->orderBy('id')
                ->pluck('id');

            $order = 1;
            foreach ($items as $id) {
                DB::table('guests')->where('id', $id)->update(['sort_order' => $order]);
                $order++;
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('guests', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
}
