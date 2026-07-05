# Architecture

```text
Browser
  -> Nginx / Laravel
       -> Auth and account controls
       -> Four versioned game engines
       -> Wallet + immutable ledger
       -> Fairness verification
       -> Small admin console
  -> PostgreSQL
  -> Redis (cache and session)
```

The application remains a Laravel monolith. A bet locks the player and wallet rows, validates game/limits, creates the settled game entry, records debit/payout ledger entries, increments the fairness nonce and commits everything atomically.
