#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP="$ROOT/lucky-arcade-app"
[ -f "$APP/artisan" ] || { echo "Existing Lucky Arcade app not found."; exit 1; }
STAMP="$(date +%Y%m%d-%H%M%S)"
DB="$APP/database/database.sqlite"
[ -f "$DB" ] && cp "$DB" "$DB.backup-before-lite-$STAMP"
cleanup_legacy() {
  local BASE="$1"
  rm -rf "$BASE/app/Actions/Rewards" "$BASE/app/Events" "$BASE/app/Jobs" "$BASE/app/Listeners"
  rm -f "$BASE"/app/GameEngines/HighLowEngine.php "$BASE"/app/Http/Controllers/{AchievementController,DailyRewardController,HighLowController,LeaderboardController,LiveFeedController,MissionController,NotificationController,PlayerStatsController,PromoCodeController,ReferralController,SupportTicketController,WeeklyLeagueController}.php
  rm -f "$BASE"/app/Http/Controllers/Admin/{AdminAccessController,AnalyticsController,AnnouncementController,GameRulesetController,LiveOperationsController,PromoCodeController,SecurityEventController,SimulationController,SupportTicketController,WeeklyLeagueController}.php
  rm -f "$BASE/app/Http/Middleware/TouchPresence.php" "$BASE/app/Http/Requests/PlayHighLowRequest.php"
  rm -f "$BASE"/app/Models/{Announcement,DailyGameMetric,DailyReward,LiveEvent,OperationRun,PromoCode,PromoCodeRedemption,ReferralReward,SimulationRun,SupportMessage,SupportTicket,UserAchievement,UserMission,UserNotification,UserPresence,WeeklyLeagueReward,WeeklyLeagueSettlement}.php
  rm -f "$BASE"/app/Services/{AchievementService,AnnouncementService,DailyMetricsService,LiveEventService,MissionService,PresenceService,ReferralCodeService,ReferralRewardService,WeeklyLeagueService}.php
  rm -f "$BASE"/app/Console/Commands/{ArcadeReleaseCheck,PruneLiveExperience,PruneOperationalData,RefreshDailyMetrics,SimulateGames}.php
  rm -rf "$BASE/resources/views/achievements" "$BASE/resources/views/leaderboard" "$BASE/resources/views/league" "$BASE/resources/views/missions" "$BASE/resources/views/notifications" "$BASE/resources/views/promos" "$BASE/resources/views/referrals" "$BASE/resources/views/stats" "$BASE/resources/views/support"
  rm -rf "$BASE/resources/views/admin/access" "$BASE/resources/views/admin/announcements" "$BASE/resources/views/admin/league" "$BASE/resources/views/admin/live" "$BASE/resources/views/admin/promos" "$BASE/resources/views/admin/rulesets" "$BASE/resources/views/admin/security-events" "$BASE/resources/views/admin/simulations" "$BASE/resources/views/admin/support"
  rm -f "$BASE/public/verifier.html" "$BASE/public/js/fairness-verifier.js" "$BASE/config/live.php"
  rm -f "$BASE"/tests/Feature/{AchievementTest,AdminAnalyticsTest,AdminRoleAccessTest,AnnouncementTest,DailyMetricsTest,DailyRewardTest,LeaderboardTest,LedgerExportTest,LiveFeedTest,LiveSupportTest,MissionTest,PlayerStatsTest,PresenceAndLiveOperationsTest,ProductionOperationsTest,PromoCodeTest,ReferralRewardTest,ReleaseCheckTest,SimulationRunTest,SupportTicketTest,SystemHealthTest,VersionedRulesetTest,WeeklyLeagueTest}.php
  rm -f "$BASE/tests/Unit/HighLowEngineTest.php"
}
cleanup_legacy "$ROOT/overlay"
cleanup_legacy "$APP"
cd "$APP"
rm -rf resources/views tests
cp -R "$ROOT/overlay/app/." app/
cp -R "$ROOT/overlay/bootstrap/." bootstrap/
cp -R "$ROOT/overlay/config/." config/ 2>/dev/null || true
cp -R "$ROOT/overlay/public/." public/
cp -R "$ROOT/overlay/resources/." resources/
cp -R "$ROOT/overlay/routes/." routes/
cp -R "$ROOT/overlay/tests/." tests/
cp -R "$ROOT/overlay/database/seeders/." database/seeders/
cp "$ROOT/overlay/database/migrations/2026_07_06_000008_prune_legacy_features_for_lite_core.php" database/migrations/
XDEBUG_MODE=off php artisan optimize:clear
XDEBUG_MODE=off php artisan migrate --force
XDEBUG_MODE=off php artisan db:seed --force
XDEBUG_MODE=off php artisan test
XDEBUG_MODE=off php artisan wallets:reconcile
XDEBUG_MODE=off php artisan arcade:verify-entries --limit=500
XDEBUG_MODE=off php artisan arcade:backup --keep=10
XDEBUG_MODE=off php artisan arcade:doctor
echo "Upgrade to Lite Core completed. Backup: $DB.backup-before-lite-$STAMP"
