# Lucky Arcade Lite Core

A deliberately small Laravel social-gaming project using **virtual credits only**. Lite Core keeps four games and the operational safeguards that matter, while removing gamification, live feeds, referral systems, support modules, leagues and analytics dashboards.

## Included

- Dice, European Roulette, Coin Flip and Lucky Slots
- Registration, login, account profile and password change
- Admin/player TOTP two-factor authentication
- Virtual-credit wallet with immutable ledger entries
- Idempotent bets, database transactions and row locks
- Versioned game rules and provably-fair seed verification
- Play history and wallet history
- Responsible-play daily limit and self-exclusion
- Small admin console: players, games, rounds, audit, backup and reconciliation
- SQLite for Codespaces; PostgreSQL + Redis production configuration

## Deliberately removed

Achievements, missions, referrals, leaderboards, promo codes, support tickets, live polling, online presence, announcements, weekly leagues, player analytics, simulation UI and complex admin roles.

## Codespaces

```bash
bash setup-linux.sh
bash run-codespaces.sh
```

Open port `8000` from the **PORTS** panel.

Demo accounts:

```text
Player: demo@example.com / Demo123!
Admin:  admin@example.com / ChangeMe123!
```

Change the admin password and enable 2FA before sharing an environment.

## Upgrade from Lucky Arcade v1.0

Use the separate update package and run:

```bash
bash upgrade-lite-core.sh
```

The script creates a timestamped SQLite backup before pruning non-core modules. Historical game entries and ledger records are preserved. An old High Low game with history is disabled rather than deleting its records.

## Production

Use PostgreSQL for primary data and Redis for cache/session. See [docs/PRODUCTION.md](docs/PRODUCTION.md).

This project does not implement deposits, withdrawals, cryptocurrency, cash prizes or transferable credits.
