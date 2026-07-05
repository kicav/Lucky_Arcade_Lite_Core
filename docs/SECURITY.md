# Security model

- CSRF-protected Laravel forms
- Throttled login and game routes
- Password hashing through Laravel casts
- Optional TOTP and one-use recovery codes
- Server-side authorization for admin routes
- Wallet row locks and idempotency keys
- Immutable financial-style ledger records
- Versioned game rules and HMAC-SHA256 fairness seeds
- Security headers and admin audit logs

Never log passwords, TOTP secrets, recovery codes, session cookies, unrevealed server seeds or `APP_KEY`.
