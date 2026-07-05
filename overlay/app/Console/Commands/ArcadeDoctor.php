<?php

namespace App\Console\Commands;

use App\Services\ProductionReadinessService;
use Illuminate\Console\Command;

class ArcadeDoctor extends Command
{
    protected $signature = 'arcade:doctor {--strict}';
    protected $description = 'Check the Lite Core runtime and production readiness.';

    public function handle(ProductionReadinessService $service): int
    {
        $failed = false; $warnings = false;
        foreach ($service->checks() as $check) {
            $this->line(sprintf('[%s] %s: %s', strtoupper($check['status']), $check['label'], $check['message']));
            $failed = $failed || $check['status'] === 'error';
            $warnings = $warnings || $check['status'] === 'warning';
        }
        return ($failed || ($this->option('strict') && $warnings)) ? self::FAILURE : self::SUCCESS;
    }
}
