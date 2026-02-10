<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateTableChangeNotifyTriggers extends Migration
{
    public function up()
    {
        DB::unprepared("
            CREATE OR REPLACE FUNCTION notify_table_change() RETURNS trigger AS \$\$
            DECLARE
                payload JSON;
                record_data JSON;
                record_id BIGINT;
                client_id TEXT;
            BEGIN
                client_id := coalesce(current_setting('app.client_id', true), '');

                IF (TG_OP = 'DELETE') THEN
                    record_id := OLD.id;
                    record_data := NULL;
                ELSE
                    record_id := NEW.id;
                    record_data := row_to_json(NEW);
                END IF;

                payload := json_build_object(
                    'table', TG_TABLE_NAME,
                    'operation', TG_OP,
                    'record_id', record_id,
                    'data', record_data,
                    'client_id', client_id
                );

                PERFORM pg_notify('table_changes', payload::text);

                IF (TG_OP = 'DELETE') THEN
                    RETURN OLD;
                ELSE
                    RETURN NEW;
                END IF;
            END;
            \$\$ LANGUAGE plpgsql;
        ");

        $tables = ['guests', 'engagement_tasks', 'gifts', 'expenses'];

        foreach ($tables as $table) {
            DB::unprepared("
                CREATE TRIGGER {$table}_notify_trigger
                    AFTER INSERT OR UPDATE OR DELETE ON {$table}
                    FOR EACH ROW EXECUTE FUNCTION notify_table_change();
            ");
        }
    }

    public function down()
    {
        $tables = ['guests', 'engagement_tasks', 'gifts', 'expenses'];

        foreach ($tables as $table) {
            DB::unprepared("DROP TRIGGER IF EXISTS {$table}_notify_trigger ON {$table};");
        }

        DB::unprepared("DROP FUNCTION IF EXISTS notify_table_change();");
    }
}
