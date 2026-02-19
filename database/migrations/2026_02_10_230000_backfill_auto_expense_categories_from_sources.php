<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class BackfillAutoExpenseCategoriesFromSources extends Migration
{
    public $withinTransaction = false;


    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('expenses')
            ->where('entry_mode', 'auto')
            ->where('source_type', 'task')
            ->update(['category' => 'To-Do']);

        DB::statement("
            UPDATE expenses e
            SET category = COALESCE(NULLIF(TRIM(g.group_name), ''), 'Seserahan')
            FROM gifts g
            WHERE e.entry_mode = 'auto'
              AND e.source_type = 'gift'
              AND e.source_id = g.id
        ");

        DB::table('expenses')
            ->where('entry_mode', 'auto')
            ->where('source_type', 'gift')
            ->where(function ($query) {
                $query->whereNull('category')->orWhere('category', '');
            })
            ->update(['category' => 'Seserahan']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('expenses')
            ->where('entry_mode', 'auto')
            ->where('source_type', 'task')
            ->where('category', 'To-Do')
            ->update(['category' => 'To-do']);

        DB::table('expenses')
            ->where('entry_mode', 'auto')
            ->where('source_type', 'gift')
            ->update(['category' => 'Seserahan']);
    }
}

