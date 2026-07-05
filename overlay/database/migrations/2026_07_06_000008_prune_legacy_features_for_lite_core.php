<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach ([
            'simulation_runs', 'live_events', 'user_presences', 'weekly_league_rewards',
            'weekly_league_settlements', 'support_messages', 'support_tickets',
            'promo_code_redemptions', 'promo_codes', 'user_missions', 'user_achievements',
            'referral_rewards', 'announcements', 'daily_game_metrics', 'operation_runs',
            'user_notifications', 'daily_rewards',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        if (Schema::hasTable('games')) {
            $highLow = DB::table('games')->where('code', 'highlow')->first();
            if ($highLow) {
                $hasHistory = Schema::hasTable('game_entries')
                    && DB::table('game_entries')->where('game_id', $highLow->id)->exists();

                if ($hasHistory) {
                    DB::table('games')->where('id', $highLow->id)->update(['enabled' => false]);
                } else {
                    if (Schema::hasTable('game_rulesets')) {
                        DB::table('game_rulesets')->where('game_id', $highLow->id)->delete();
                    }
                    DB::table('games')->where('id', $highLow->id)->delete();
                }
            }
        }
    }

    public function down(): void
    {
        // Lite Core intentionally removes non-core feature tables. Restore from the
        // automatic pre-upgrade backup if a rollback to the full edition is needed.
    }
};
