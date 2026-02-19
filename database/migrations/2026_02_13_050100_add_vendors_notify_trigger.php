<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddVendorsNotifyTrigger extends Migration
{
    public $withinTransaction = false;


    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::unprepared("
            DROP TRIGGER IF EXISTS vendors_notify_trigger ON vendors;
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
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::unprepared('DROP TRIGGER IF EXISTS vendors_notify_trigger ON vendors;');
    }
}
