# Start here

## New Lite Core repository

1. Upload all files to a new GitHub repository.
2. Confirm `.devcontainer/devcontainer.json` exists.
3. Create a Codespace.
4. Run `bash setup-linux.sh`.
5. Run `bash run-codespaces.sh`.
6. Open forwarded port `8000`.

## Existing v1.0 repository

1. Stop the Laravel server.
2. Upload and extract the Lite Core update package at the repository root.
3. Run `chmod +x upgrade-lite-core.sh run-codespaces.sh`.
4. Run `bash upgrade-lite-core.sh`.
5. Run `bash run-codespaces.sh`.

The pre-upgrade database backup is retained beside `database.sqlite`.
