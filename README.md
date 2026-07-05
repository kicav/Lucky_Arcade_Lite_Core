# Lucky Arcade Visual

Lucky Arcade Visual is the animated presentation layer for Lucky Arcade Lite Core. It keeps the same four-game Laravel backend, immutable virtual-credit ledger, versioned rules and provably-fair results while replacing the plain interface with an original responsive arcade visual system.

## Core scope

- Dice, European Roulette, Coin Flip and Lucky Slots
- Registration, login, account controls and optional TOTP
- Virtual-credit wallet with immutable ledger
- Game and wallet history
- Provably-fair seeds and historic verification
- Simple admin for players, games, history, audit and backup
- SQLite for Codespaces development
- PostgreSQL and Redis production configuration

## Visual features

- Original local SVG artwork; no remote assets or licensed game art
- Animated ambient background and responsive game lobby
- 3D-style numeric dice cube
- Animated European roulette wheel
- Double-sided 3D coin
- Animated three-reel slot machine
- Result celebration, generated sound effects and visual feedback
- Sound toggle and reduced-motion toggle stored in the browser
- `prefers-reduced-motion` support
- No change to game outcomes: the server settles first, the browser animates the stored result

## Run in GitHub Codespaces

```bash
bash setup-linux.sh
bash run-codespaces.sh
```

Open port `8000` from the Codespaces **PORTS** panel.

Demo accounts:

```text
Player: demo@example.com / Demo123!
Admin:  admin@example.com / ChangeMe123!
```

## Upgrade an existing Lite Core repository

Copy the Visual update package into the repository, extract it, then run:

```bash
chmod +x upgrade-visual.sh
bash upgrade-visual.sh
bash run-codespaces.sh
```

The upgrade backs up SQLite before replacing presentation files and runs the complete test suite.

## Production

Use PostgreSQL and Redis. See `docs/PRODUCTION.md` and `.env.production.example`.

## Safety boundary

This project uses virtual credits with no cash value. It does not include deposits, withdrawals, crypto, payment gateways or admin-controlled game outcomes.
