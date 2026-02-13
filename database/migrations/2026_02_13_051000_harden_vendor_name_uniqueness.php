<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class HardenVendorNameUniqueness extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement("UPDATE vendors SET vendor_name = BTRIM(REGEXP_REPLACE(vendor_name, '\\s+', ' ', 'g'))");
            DB::statement("UPDATE vendors SET vendor_name = 'Vendor ' || id WHERE vendor_name IS NULL OR BTRIM(vendor_name) = ''");
            DB::statement("UPDATE vendors SET contact_number = NULLIF(REGEXP_REPLACE(COALESCE(contact_number, ''), '\\D+', '', 'g'), '')");

            DB::statement(
                "WITH ranked AS (" .
                " SELECT id, ROW_NUMBER() OVER (" .
                "   PARTITION BY LOWER(vendor_name) " .
                "   ORDER BY " .
                "     CASE status WHEN 'done' THEN 2 WHEN 'in_progress' THEN 1 ELSE 0 END DESC, " .
                "     (" .
                "       CASE WHEN NULLIF(BTRIM(contact_name), '') IS NULL THEN 0 ELSE 1 END +" .
                "       CASE WHEN NULLIF(BTRIM(contact_number), '') IS NULL THEN 0 ELSE 1 END +" .
                "       CASE WHEN NULLIF(BTRIM(contact_email), '') IS NULL THEN 0 ELSE 1 END +" .
                "       CASE WHEN NULLIF(BTRIM(website), '') IS NULL THEN 0 ELSE 1 END +" .
                "       CASE WHEN NULLIF(BTRIM(reference), '') IS NULL THEN 0 ELSE 1 END" .
                "     ) DESC, " .
                "     updated_at DESC, id DESC" .
                " ) AS rn FROM vendors" .
                ") " .
                "DELETE FROM vendors v USING ranked r WHERE v.id = r.id AND r.rn > 1"
            );

            DB::statement(
                "UPDATE engagement_tasks " .
                "SET vendor = NULLIF(BTRIM(REGEXP_REPLACE(vendor, '\\s+', ' ', 'g')), '') " .
                "WHERE vendor IS NOT NULL"
            );

            DB::statement(
                "UPDATE engagement_tasks t " .
                "SET vendor = v.vendor_name " .
                "FROM vendors v " .
                "WHERE t.vendor IS NOT NULL AND LOWER(t.vendor) = LOWER(v.vendor_name)"
            );

            DB::statement("CREATE UNIQUE INDEX IF NOT EXISTS vendors_name_ci_unique_idx ON vendors (LOWER(vendor_name))");
            DB::unprepared(
                "DO $$ BEGIN " .
                "IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'vendors_vendor_name_not_blank_chk') THEN " .
                "ALTER TABLE vendors ADD CONSTRAINT vendors_vendor_name_not_blank_chk CHECK (char_length(BTRIM(vendor_name)) > 0); " .
                "END IF; " .
                "END $$;"
            );

            return;
        }

        DB::statement("UPDATE vendors SET vendor_name = TRIM(vendor_name)");
        DB::statement(
            "UPDATE vendors SET contact_number = NULLIF(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(contact_number, '+', ''), '-', ''), ' ', ''), '(', ''), ')', ''), '') " .
            "WHERE contact_number IS NOT NULL"
        );
        DB::statement("UPDATE engagement_tasks SET vendor = NULLIF(TRIM(vendor), '') WHERE vendor IS NOT NULL");
        DB::statement("CREATE UNIQUE INDEX vendors_name_ci_unique_idx ON vendors (vendor_name)");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE vendors DROP CONSTRAINT IF EXISTS vendors_vendor_name_not_blank_chk');
            DB::statement('DROP INDEX IF EXISTS vendors_name_ci_unique_idx');

            return;
        }

        DB::statement('DROP INDEX vendors_name_ci_unique_idx');
    }
}
