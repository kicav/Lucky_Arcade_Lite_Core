<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('wallets:reconcile')->dailyAt('02:00')->withoutOverlapping(30);
Schedule::command('arcade:backup --keep=14')->dailyAt('02:20')->withoutOverlapping(60);
Schedule::command('arcade:verify-entries --limit=500')->dailyAt('03:00')->withoutOverlapping(60);
Schedule::command('queue:prune-failed --hours=168')->dailyAt('03:30')->withoutOverlapping(30);
