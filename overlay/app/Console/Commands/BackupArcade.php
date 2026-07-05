<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use PDO;
use RuntimeException;

class BackupArcade extends Command
{
    protected $signature = 'arcade:backup {--keep=14}';
    protected $description = 'Create a consistent local database backup.';

    public function handle(): int
    {
        $directory = storage_path('app/backups');
        File::ensureDirectoryExists($directory);
        $driver = config('database.default');
        $stamp = now()->format('Ymd-His');

        if ($driver === 'sqlite') {
            $source = (string) config('database.connections.sqlite.database');
            if (! File::exists($source)) throw new RuntimeException('SQLite database file not found.');
            $target = "{$directory}/lucky-arcade-lite-{$stamp}.sqlite";
            $sourcePdo = new PDO('sqlite:'.$source);
            $quotedTarget = str_replace("'", "''", $target);
            $sourcePdo->exec("VACUUM INTO '{$quotedTarget}'");
        } elseif ($driver === 'pgsql') {
            $target = "{$directory}/lucky-arcade-lite-{$stamp}.dump";
            $connection = config('database.connections.pgsql');
            $command = sprintf(
                'PGPASSWORD=%s pg_dump --format=custom --no-owner --host=%s --port=%s --username=%s --file=%s %s 2>&1',
                escapeshellarg((string) $connection['password']), escapeshellarg((string) $connection['host']),
                escapeshellarg((string) $connection['port']), escapeshellarg((string) $connection['username']),
                escapeshellarg($target), escapeshellarg((string) $connection['database']),
            );
            exec($command, $output, $exitCode);
            if ($exitCode !== 0) throw new RuntimeException(implode("\n", $output));
        } else {
            throw new RuntimeException("Unsupported backup driver: {$driver}");
        }

        $keep = max(1, (int) $this->option('keep'));
        collect(File::files($directory))->sortByDesc(fn ($file) => $file->getMTime())->slice($keep)->each(fn ($file) => File::delete($file->getPathname()));
        $this->info('Backup created: '.basename($target));
        return self::SUCCESS;
    }
}
