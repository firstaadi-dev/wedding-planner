<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddWorkspaceAndEventContextToPlannerTables extends Migration
{
    public $withinTransaction = false;


    public function up()
    {
        if (Schema::hasTable('guests') && !Schema::hasColumn('guests', 'workspace_id')) {
            Schema::table('guests', function (Blueprint $table) {
                $table->unsignedBigInteger('workspace_id')->nullable()->after('id');
            });
        }
        $this->safeIndex('guests', function (Blueprint $table) {
            $table->index(['workspace_id', 'event_type', 'side', 'sort_order', 'id'], 'guests_workspace_event_side_sort_idx');
        });

        if (Schema::hasTable('engagement_tasks')) {
            Schema::table('engagement_tasks', function (Blueprint $table) {
                if (!Schema::hasColumn('engagement_tasks', 'workspace_id')) {
                    $table->unsignedBigInteger('workspace_id')->nullable()->after('id');
                }
                if (!Schema::hasColumn('engagement_tasks', 'event_type')) {
                    $table->string('event_type', 20)->default('lamaran')->after('workspace_id');
                }
            });
        }
        $this->safeIndex('engagement_tasks', function (Blueprint $table) {
            $table->index(['workspace_id', 'event_type', 'task_status', 'due_date', 'start_date'], 'tasks_workspace_event_status_due_idx');
        });

        if (Schema::hasTable('gifts')) {
            Schema::table('gifts', function (Blueprint $table) {
                if (!Schema::hasColumn('gifts', 'workspace_id')) {
                    $table->unsignedBigInteger('workspace_id')->nullable()->after('id');
                }
                if (!Schema::hasColumn('gifts', 'event_type')) {
                    $table->string('event_type', 20)->default('lamaran')->after('workspace_id');
                }
            });
        }
        $this->safeIndex('gifts', function (Blueprint $table) {
            $table->index(['workspace_id', 'event_type', 'group_sort_order', 'sort_order', 'id'], 'gifts_workspace_event_group_sort_idx');
        });

        if (Schema::hasTable('vendors')) {
            Schema::table('vendors', function (Blueprint $table) {
                if (!Schema::hasColumn('vendors', 'workspace_id')) {
                    $table->unsignedBigInteger('workspace_id')->nullable()->after('id');
                }
                if (!Schema::hasColumn('vendors', 'event_type')) {
                    $table->string('event_type', 20)->default('lamaran')->after('workspace_id');
                }
            });
        }
        $this->safeIndex('vendors', function (Blueprint $table) {
            $table->index(['workspace_id', 'event_type', 'group_sort_order', 'vendor_name', 'id'], 'vendors_workspace_event_group_sort_idx');
        });

        if (Schema::hasTable('expenses')) {
            Schema::table('expenses', function (Blueprint $table) {
                if (!Schema::hasColumn('expenses', 'workspace_id')) {
                    $table->unsignedBigInteger('workspace_id')->nullable()->after('id');
                }
                if (!Schema::hasColumn('expenses', 'event_type')) {
                    $table->string('event_type', 20)->default('lamaran')->after('workspace_id');
                }
            });
        }
        $this->safeIndex('expenses', function (Blueprint $table) {
            $table->index(['workspace_id', 'event_type', 'entry_mode', 'type'], 'expenses_workspace_event_mode_idx');
        });

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'current_workspace_id')) {
            $this->safeIndex('users', function (Blueprint $table) {
                $table->index('current_workspace_id', 'users_current_workspace_idx');
            });
        }

        if (!Schema::hasTable('workspaces')) {
            return;
        }

        $workspaceId = (int) DB::table('workspaces')->orderBy('id')->value('id');
        if ($workspaceId <= 0) {
            $workspaceId = (int) DB::table('workspaces')->insertGetId([
                'name' => 'Default Workspace',
                'active_event_type' => 'lamaran',
                'plan_code' => 'free',
                'plan_status' => 'active',
                'plan_price' => 0,
                'plan_started_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (Schema::hasTable('guests') && Schema::hasColumn('guests', 'workspace_id')) {
            DB::table('guests')->whereNull('workspace_id')->update(['workspace_id' => $workspaceId]);
        }
        if (Schema::hasTable('engagement_tasks') && Schema::hasColumn('engagement_tasks', 'workspace_id')) {
            DB::table('engagement_tasks')->whereNull('workspace_id')->update(['workspace_id' => $workspaceId]);
        }
        if (Schema::hasTable('engagement_tasks') && Schema::hasColumn('engagement_tasks', 'event_type')) {
            DB::table('engagement_tasks')->whereNull('event_type')->update(['event_type' => 'lamaran']);
        }
        if (Schema::hasTable('gifts') && Schema::hasColumn('gifts', 'workspace_id')) {
            DB::table('gifts')->whereNull('workspace_id')->update(['workspace_id' => $workspaceId]);
        }
        if (Schema::hasTable('gifts') && Schema::hasColumn('gifts', 'event_type')) {
            DB::table('gifts')->whereNull('event_type')->update(['event_type' => 'lamaran']);
        }
        if (Schema::hasTable('vendors') && Schema::hasColumn('vendors', 'workspace_id')) {
            DB::table('vendors')->whereNull('workspace_id')->update(['workspace_id' => $workspaceId]);
        }
        if (Schema::hasTable('vendors') && Schema::hasColumn('vendors', 'event_type')) {
            DB::table('vendors')->whereNull('event_type')->update(['event_type' => 'lamaran']);
        }
        if (Schema::hasTable('expenses') && Schema::hasColumn('expenses', 'workspace_id')) {
            DB::table('expenses')->whereNull('workspace_id')->update(['workspace_id' => $workspaceId]);
        }
        if (Schema::hasTable('expenses') && Schema::hasColumn('expenses', 'event_type')) {
            DB::table('expenses')->whereNull('event_type')->update(['event_type' => 'lamaran']);
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'current_workspace_id')) {
            DB::table('users')->whereNull('current_workspace_id')->update(['current_workspace_id' => $workspaceId]);
        }

        $driver = DB::connection()->getDriverName();
        if ($driver === 'pgsql') {
            $this->safeStatement('ALTER TABLE guests ALTER COLUMN workspace_id SET NOT NULL');
            $this->safeStatement('ALTER TABLE engagement_tasks ALTER COLUMN workspace_id SET NOT NULL');
            $this->safeStatement('ALTER TABLE gifts ALTER COLUMN workspace_id SET NOT NULL');
            $this->safeStatement('ALTER TABLE vendors ALTER COLUMN workspace_id SET NOT NULL');
            $this->safeStatement('ALTER TABLE expenses ALTER COLUMN workspace_id SET NOT NULL');
        }
    }

    public function down()
    {
        if (Schema::hasTable('users')) {
            $this->safeDropIndex('users', 'users_current_workspace_idx');
        }

        if (Schema::hasTable('expenses')) {
            $this->safeDropIndex('expenses', 'expenses_workspace_event_mode_idx');
            Schema::table('expenses', function (Blueprint $table) {
                $drop = [];
                if (Schema::hasColumn('expenses', 'workspace_id')) {
                    $drop[] = 'workspace_id';
                }
                if (Schema::hasColumn('expenses', 'event_type')) {
                    $drop[] = 'event_type';
                }
                if (!empty($drop)) {
                    $table->dropColumn($drop);
                }
            });
        }

        if (Schema::hasTable('vendors')) {
            $this->safeDropIndex('vendors', 'vendors_workspace_event_group_sort_idx');
            Schema::table('vendors', function (Blueprint $table) {
                $drop = [];
                if (Schema::hasColumn('vendors', 'workspace_id')) {
                    $drop[] = 'workspace_id';
                }
                if (Schema::hasColumn('vendors', 'event_type')) {
                    $drop[] = 'event_type';
                }
                if (!empty($drop)) {
                    $table->dropColumn($drop);
                }
            });
        }

        if (Schema::hasTable('gifts')) {
            $this->safeDropIndex('gifts', 'gifts_workspace_event_group_sort_idx');
            Schema::table('gifts', function (Blueprint $table) {
                $drop = [];
                if (Schema::hasColumn('gifts', 'workspace_id')) {
                    $drop[] = 'workspace_id';
                }
                if (Schema::hasColumn('gifts', 'event_type')) {
                    $drop[] = 'event_type';
                }
                if (!empty($drop)) {
                    $table->dropColumn($drop);
                }
            });
        }

        if (Schema::hasTable('engagement_tasks')) {
            $this->safeDropIndex('engagement_tasks', 'tasks_workspace_event_status_due_idx');
            Schema::table('engagement_tasks', function (Blueprint $table) {
                $drop = [];
                if (Schema::hasColumn('engagement_tasks', 'workspace_id')) {
                    $drop[] = 'workspace_id';
                }
                if (Schema::hasColumn('engagement_tasks', 'event_type')) {
                    $drop[] = 'event_type';
                }
                if (!empty($drop)) {
                    $table->dropColumn($drop);
                }
            });
        }

        if (Schema::hasTable('guests')) {
            $this->safeDropIndex('guests', 'guests_workspace_event_side_sort_idx');
            if (Schema::hasColumn('guests', 'workspace_id')) {
                Schema::table('guests', function (Blueprint $table) {
                    $table->dropColumn('workspace_id');
                });
            }
        }
    }

    private function safeIndex(string $table, \Closure $callback): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        try {
            Schema::table($table, $callback);
        } catch (\Throwable $e) {
            // ignore when index already exists on this branch
        }
    }

    private function safeDropIndex(string $table, string $index): void
    {
        try {
            Schema::table($table, function (Blueprint $table) use ($index) {
                $table->dropIndex($index);
            });
        } catch (\Throwable $e) {
            // ignore when index does not exist
        }
    }

    private function safeStatement(string $sql): void
    {
        try {
            DB::statement($sql);
        } catch (\Throwable $e) {
            // ignore when statement is not applicable for this branch state
        }
    }
}
