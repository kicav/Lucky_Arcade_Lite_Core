# Migrating from the full v1.0 edition

The update script first creates a timestamped SQLite backup. It then removes non-core source modules, runs a pruning migration, seeds the four Lite games and executes the test/reconciliation suite.

Core player accounts, balances, ledger entries, fairness seeds, audit logs and game history remain intact. High Low historical entries are retained; the game is disabled and removed from navigation.

For the cleanest schema and repository history, create a new repository from the full Lite Core package and migrate only the user/wallet/core history data after validation.
