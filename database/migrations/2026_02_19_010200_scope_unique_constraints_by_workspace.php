<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ScopeUniqueConstraintsByWorkspace extends Migration
{
    public $withinTransaction = false;


    public function up()
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('expenses') || !\Illuminate\Support\Facades\Schema::hasTable('vendors')) {
            return;
        }

        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE expenses DROP CONSTRAINT IF EXISTS expenses_source_unique');
            DB::statement('DROP INDEX IF EXISTS expenses_source_unique');
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS expenses_workspace_source_unique ON expenses (workspace_id, source_type, source_id)');

            DB::statement("UPDATE vendors SET vendor_name = BTRIM(REGEXP_REPLACE(vendor_name, '\\s+', ' ', 'g'))");
            DB::statement("UPDATE vendors SET vendor_name = 'Vendor ' || id WHERE vendor_name IS NULL OR BTRIM(vendor_name) = ''");
            DB::statement(
                "WITH ranked AS (" .
                " SELECT id, ROW_NUMBER() OVER (" .
                "   PARTITION BY workspace_id, LOWER(vendor_name)" .
                "   ORDER BY " .
                "     CASE status WHEN 'done' THEN 2 WHEN 'in_progress' THEN 1 ELSE 0 END DESC, " .
                "     updated_at DESC, id DESC" .
                " ) AS rn FROM vendors" .
                ") " .
                "DELETE FROM vendors v USING ranked r WHERE v.id = r.id AND r.rn > 1"
            );

            DB::statement('DROP INDEX IF EXISTS vendors_name_ci_unique_idx');
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS vendors_workspace_name_ci_unique_idx ON vendors (workspace_id, LOWER(vendor_name))');

            return;
        }

        try {
            DB::statement('DROP INDEX expenses_source_unique');
        } catch (\Throwable $e) {
            // ignore
        }

        try {
            DB::statement('CREATE UNIQUE INDEX expenses_workspace_source_unique ON expenses (workspace_id, source_type, source_id)');
        } catch (\Throwable $e) {
            // ignore
        }

        try {
            DB::statement('DROP INDEX vendors_name_ci_unique_idx');
        } catch (\Throwable $e) {
            // ignore
        }

        try {
            DB::statement('CREATE UNIQUE INDEX vendors_workspace_name_ci_unique_idx ON vendors (workspace_id, vendor_name)');
        } catch (\Throwable $e) {
            // ignore
        }
    }

    public function down()
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS vendors_workspace_name_ci_unique_idx');
            DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS vendors_name_ci_unique_idx ON vendors (LOWER(vendor_name))');

            DB::statement('DROP INDEX IF EXISTS expenses_workspace_source_unique');
            DB::statement(<<<'SQL'
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM pg_constraint
        WHERE conname = 'expenses_source_unique'
          AND conrelid = 'expenses'::regclass
    ) THEN
        ALTER TABLE expenses
            ADD CONSTRAINT expenses_source_unique UNIQUE (source_type, source_id);
    END IF;
END
$$;
SQL);

            return;
        }

        DB::statement('DROP INDEX vendors_workspace_name_ci_unique_idx');
        DB::statement('CREATE UNIQUE INDEX vendors_name_ci_unique_idx ON vendors (vendor_name)');

        DB::statement('DROP INDEX expenses_workspace_source_unique');
        DB::statement('CREATE UNIQUE INDEX expenses_source_unique ON expenses (source_type, source_id)');
    }
}
