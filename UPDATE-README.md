# Upgrade Lite Core to Lucky Arcade Visual

1. Stop the Laravel server.
2. Extract this package into the repository root.
3. Run:

```bash
chmod +x upgrade-visual.sh run-codespaces.sh
bash upgrade-visual.sh
bash run-codespaces.sh
```

The upgrade creates a SQLite backup, replaces only application presentation/source files, clears caches and runs the full test suite. It does not add or remove database tables.
