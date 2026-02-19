<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PDO;
use RuntimeException;

class SqliteSnapshotSeeder extends Seeder
{
    /**
     * Tables to copy from SQLite snapshot into active connection.
     *
     * @var string[]
     */
    protected $tables = [
        'guests',
        'engagement_tasks',
        'gifts',
        'expenses',
    ];

    public function run()
    {
        $sqlitePath = env('SQLITE_SEED_PATH', database_path('database.sqlite'));
        if (!file_exists($sqlitePath)) {
            throw new RuntimeException('SQLite source file tidak ditemukan: '.$sqlitePath);
        }

        $sqlite = new PDO('sqlite:'.$sqlitePath);
        $sqlite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $workspaceId = (int) DB::table('workspaces')->orderBy('id')->value('id');
        if ($workspaceId <= 0) {
            $workspaceId = (int) DB::table('workspaces')->insertGetId([
                'name' => 'Snapshot Workspace',
                'active_event_type' => 'lamaran',
                'plan_code' => 'free',
                'plan_status' => 'active',
                'plan_price' => 0,
                'plan_started_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::transaction(function () use ($sqlite, $workspaceId) {
            foreach ($this->tables as $table) {
                if (!Schema::hasTable($table)) {
                    continue;
                }

                $stmt = $sqlite->query('SELECT * FROM '.$table.' ORDER BY id');
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                DB::table($table)->delete();
                if (empty($rows)) {
                    continue;
                }

                $targetColumns = Schema::getColumnListing($table);
                $insertRows = [];
                foreach ($rows as $row) {
                    // Do not keep old SQLite primary keys to avoid sequence mismatch in PostgreSQL.
                    unset($row['id']);

                    $filtered = [];
                    foreach ($targetColumns as $column) {
                        if ($column === 'id') {
                            continue;
                        }
                        if (array_key_exists($column, $row)) {
                            $filtered[$column] = $row[$column];
                        }
                    }

                    if (in_array('workspace_id', $targetColumns, true) && !array_key_exists('workspace_id', $filtered)) {
                        $filtered['workspace_id'] = $workspaceId;
                    }
                    if (in_array('event_type', $targetColumns, true) && !array_key_exists('event_type', $filtered)) {
                        $filtered['event_type'] = 'lamaran';
                    }

                    if (!empty($filtered)) {
                        $insertRows[] = $filtered;
                    }
                }

                if (!empty($insertRows)) {
                    DB::table($table)->insert($insertRows);
                }
            }
        });
    }
}
