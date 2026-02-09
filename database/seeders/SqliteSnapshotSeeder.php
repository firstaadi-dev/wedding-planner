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

        DB::transaction(function () use ($sqlite) {
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
