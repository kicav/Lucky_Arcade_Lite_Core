# Core database

Application tables:

- `users`
- `wallets`
- `games`
- `game_rulesets`
- `fairness_seeds`
- `game_entries`
- `ledger_entries`
- `audit_logs`
- `security_events`

Laravel also creates session, cache, queue and password-reset tables. PostgreSQL is required for a shared production deployment; SQLite remains convenient for a single-user Codespace.
