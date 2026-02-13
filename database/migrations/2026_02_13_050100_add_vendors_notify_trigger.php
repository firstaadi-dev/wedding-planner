<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddVendorsNotifyTrigger extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared("
            CREATE TRIGGER vendors_notify_trigger
                AFTER INSERT OR UPDATE OR DELETE ON vendors
                FOR EACH ROW EXECUTE FUNCTION notify_table_change();
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP TRIGGER IF EXISTS vendors_notify_trigger ON vendors;');
    }
}
