# Production checklist

1. Copy `.env.production.example` to `.env.production`.
2. Generate an `APP_KEY` and use strong database credentials.
3. Set the real HTTPS `APP_URL`.
4. Start `docker-compose.production.yml`.
5. Run `php artisan arcade:doctor --strict` inside the app container.
6. Change the seeded admin password and enable TOTP.
7. Schedule encrypted off-host PostgreSQL backups and test restoration.
8. Keep `APP_DEBUG=false` and restrict the admin interface.

The included stack uses PostgreSQL 17, Redis 7.4, PHP-FPM and Nginx.
