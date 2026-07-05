# Architecture

```text
Browser
  -> Visual layer
       -> Blade-rendered state
       -> CSS scenes and motion
       -> Vanilla JavaScript animation
       -> Local SVG artwork and generated audio
  -> Nginx / Laravel
       -> Auth and account controls
       -> Four versioned game engines
       -> Wallet + immutable ledger
       -> Fairness verification
       -> Small admin console
  -> PostgreSQL
  -> Redis (cache and session)
```

The application remains a Laravel monolith. A bet locks the player and wallet rows, validates game and account limits, creates the settled game entry, records debit and payout ledger entries, increments the fairness nonce and commits atomically.

The visual layer receives only the settled outcome and animates toward it. It never generates, modifies or predicts a game result.
