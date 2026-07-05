# Start here — Lucky Arcade Visual

## New repository

1. Upload the extracted contents to the repository root.
2. Confirm `.devcontainer/devcontainer.json` exists.
3. Create or rebuild the Codespace.
4. Check `php -v` reports PHP 8.3 or newer.
5. Run:

```bash
bash setup-linux.sh
bash run-codespaces.sh
```

6. Open port `8000` from the **PORTS** panel.

## Existing Lite Core repository

Use the Visual update ZIP and run:

```bash
bash upgrade-visual.sh
bash run-codespaces.sh
```

## Visual controls

The music-note control enables generated UI sounds. The circle control enables reduced-motion mode. Both settings stay in browser local storage.
