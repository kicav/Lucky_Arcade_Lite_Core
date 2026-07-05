# Visual system

## Architecture

The visual layer is intentionally framework-free:

- Blade renders final state and data attributes.
- CSS provides scenes, 3D transforms, responsiveness and reduced-motion fallbacks.
- `public/js/app.js` creates local particles, controls preferences and animates the already-settled result.
- SVG artwork is stored in `public/assets/visual`.
- Web Audio oscillators create small optional effects without external audio files.

## Result boundary

The frontend never creates a result. The request flow remains:

1. Player submits a bet.
2. Laravel validates and locks the wallet.
3. The game engine generates and stores the outcome.
4. Laravel commits ledger changes.
5. The redirected page includes the stored outcome.
6. JavaScript animates to that stored outcome.

## Performance

- No frontend package manager is required.
- No remote fonts, images or JavaScript are loaded.
- SVG artwork is small and cached by the browser.
- Animations use transforms and opacity where possible.
- Reduced motion removes background particles and long result animations.

## Accessibility

- Interactive controls use native form elements.
- Visual preferences have accessible labels and `aria-pressed` state.
- Results remain in semantic text when animation is disabled.
- `prefers-reduced-motion` is respected automatically.
