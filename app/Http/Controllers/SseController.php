<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PDO;

class SseController extends Controller
{
    public function stream(Request $request)
    {
        $clientId = $request->query('client_id', '');
        $workspaceId = (int) $request->query('workspace_id', 0);

        if ($workspaceId <= 0 && app()->bound('currentWorkspace')) {
            $workspace = app('currentWorkspace');
            $workspaceId = (int) ($workspace->id ?? 0);
        }

        if ($workspaceId <= 0) {
            abort(403, 'Workspace tidak valid untuk real-time stream.');
        }

        return response()->stream(function () use ($clientId, $workspaceId) {
            set_time_limit(0);

            while (ob_get_level() > 0) {
                ob_end_flush();
            }

            $pdo = $this->createListenConnection();
            $pdo->exec('LISTEN table_changes');

            $eventId = 0;
            $lastHeartbeat = time();
            $heartbeatInterval = 20;

            while (true) {
                if (connection_aborted()) {
                    break;
                }

                $result = $pdo->pgsqlGetNotify(PDO::FETCH_ASSOC, 1000);

                if ($result !== false) {
                    $payload = json_decode($result['payload'], true);
                    $payloadWorkspaceId = (int) ($payload['workspace_id'] ?? ($payload['data']['workspace_id'] ?? 0));

                    if ($payload
                        && ($payload['client_id'] ?? '') !== $clientId
                        && $payloadWorkspaceId === $workspaceId
                    ) {
                        $eventId++;
                        echo "id: {$eventId}\n";
                        echo "event: table_change\n";
                        echo "data: {$result['payload']}\n\n";
                        $this->flushOutput();
                    }
                }

                $now = time();
                if ($now - $lastHeartbeat >= $heartbeatInterval) {
                    echo ": heartbeat\n\n";
                    $this->flushOutput();
                    $lastHeartbeat = $now;
                }
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    private function createListenConnection(): PDO
    {
        $host = config('database.connections.pgsql.host');
        $port = config('database.connections.pgsql.port');
        $dbname = config('database.connections.pgsql.database');
        $username = config('database.connections.pgsql.username');
        $password = config('database.connections.pgsql.password');
        $sslmode = config('database.connections.pgsql.sslmode', 'require');

        $dsn = "pgsql:host={$host};port={$port};dbname={$dbname};sslmode={$sslmode}";

        return new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 60,
        ]);
    }

    private function flushOutput(): void
    {
        if (ob_get_level() > 0) {
            ob_flush();
        }
        flush();
    }
}
