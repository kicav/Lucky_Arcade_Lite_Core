<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Services\ProductionReadinessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;
use Throwable;

class SystemController extends Controller
{
    public function index(ProductionReadinessService $readiness): View
    {
        $status = 'OK';
        try { DB::select('select 1'); } catch (Throwable $e) { $status = 'ERROR: '.$e->getMessage(); }
        $directory = storage_path('app/backups');
        $backups = File::isDirectory($directory)
            ? collect(File::files($directory))->sortByDesc(fn ($file) => $file->getMTime())->take(10)
            : collect();
        return view('admin.system.index', [
            'databaseStatus' => $status,
            'storageWritable' => is_writable(storage_path()),
            'backups' => $backups,
            'readinessChecks' => $readiness->checks(),
        ]);
    }

    public function backup(Request $request): RedirectResponse
    {
        return $this->run($request, 'arcade:backup', ['--keep' => 14], 'system.backup');
    }

    public function reconcile(Request $request): RedirectResponse
    {
        return $this->run($request, 'wallets:reconcile', [], 'system.reconcile');
    }

    private function run(Request $request, string $command, array $arguments, string $action): RedirectResponse
    {
        $code = Artisan::call($command, $arguments);
        $output = trim(Artisan::output());
        AuditLog::query()->create([
            'actor_id' => $request->user()->id, 'action' => $action,
            'subject_type' => null, 'subject_id' => null, 'before' => null,
            'after' => ['command' => $command, 'exit_code' => $code, 'output' => mb_substr($output, 0, 2000)],
            'ip_address' => $request->ip(), 'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000), 'created_at' => now(),
        ]);
        return $code === 0 ? back()->with('success', $output ?: 'Operation completed.') : back()->withErrors(['operation' => $output ?: 'Operation failed.']);
    }
}
