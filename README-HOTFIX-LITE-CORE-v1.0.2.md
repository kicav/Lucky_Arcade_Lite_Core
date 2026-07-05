# Lucky Arcade Lite Core v1.0.2 Hotfix

This hotfix fixes two test-suite problems after converting the full edition to Lite Core:

1. `AdminPlayerActionsTest` was a stale full-edition test left in `overlay/tests` after ZIP files were merged. It expected the removed `user_notifications` module.
2. `LiteCoreSmokeTest` used a brittle, case-sensitive assertion for the account page. The test now asserts the stable page heading `Limits and self-exclusion` using `assertSeeText()`.

The hotfix also updates `upgrade-lite-core.sh` to use a strict allowlist for the Lite Core PHPUnit suite, preventing old full-edition tests from returning on later upgrades.

No database schema, wallet balance, ledger entry, game result, or player account data is changed.
