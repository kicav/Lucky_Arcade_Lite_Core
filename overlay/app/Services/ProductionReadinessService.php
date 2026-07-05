<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Throwable;

final class ProductionReadinessService
{
    /** @return array<int, array{label:string,status:string,message:string}> */
    public function checks(): array
    {
        $checks = [];
        try {
            DB::select('select 1');
            $checks[] = ['label' => 'Database', 'status' => 'ok', 'message' => 'Connection successful.'];
        } catch (Throwable $e) {
            $checks[] = ['label' => 'Database', 'status' => 'error', 'message' => $e->getMessage()];
        }
        $checks[] = ['label' => 'Application key', 'status' => filled(config('app.key')) ? 'ok' : 'error', 'message' => filled(config('app.key')) ? 'Configured.' : 'Missing APP_KEY.'];
        $checks[] = ['label' => 'Storage', 'status' => is_writable(storage_path()) ? 'ok' : 'error', 'message' => is_writable(storage_path()) ? 'Writable.' : 'Not writable.'];
        $checks[] = ['label' => 'Debug mode', 'status' => config('app.debug') ? 'warning' : 'ok', 'message' => config('app.debug') ? 'Disable APP_DEBUG in production.' : 'Disabled.'];
        $checks[] = ['label' => 'Database engine', 'status' => config('database.default') === 'pgsql' ? 'ok' : 'warning', 'message' => config('database.default') === 'pgsql' ? 'PostgreSQL selected.' : 'SQLite is intended for local development only.'];
        return $checks;
    }
}
