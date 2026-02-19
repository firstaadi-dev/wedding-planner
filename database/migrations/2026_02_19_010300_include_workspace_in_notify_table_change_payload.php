<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class IncludeWorkspaceInNotifyTableChangePayload extends Migration
{
    public $withinTransaction = false;

    public function up()
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::unprepared(<<<'SQL'
CREATE OR REPLACE FUNCTION notify_table_change() RETURNS trigger AS $$
DECLARE
    payload JSON;
    record_data JSON;
    record_id BIGINT;
    record_workspace_id BIGINT;
    client_id TEXT;
BEGIN
    client_id := coalesce(current_setting('app.client_id', true), '');

    IF (TG_OP = 'DELETE') THEN
        record_id := OLD.id;
        record_workspace_id := OLD.workspace_id;
        record_data := NULL;
    ELSE
        record_id := NEW.id;
        record_workspace_id := NEW.workspace_id;
        record_data := row_to_json(NEW);
    END IF;

    payload := json_build_object(
        'table', TG_TABLE_NAME,
        'operation', TG_OP,
        'record_id', record_id,
        'workspace_id', record_workspace_id,
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
$$ LANGUAGE plpgsql;
SQL);
    }

    public function down()
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::unprepared(<<<'SQL'
CREATE OR REPLACE FUNCTION notify_table_change() RETURNS trigger AS $$
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
$$ LANGUAGE plpgsql;
SQL);
    }
}
