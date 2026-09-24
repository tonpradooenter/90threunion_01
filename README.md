# 90th SISAT Reunion — Pilot

Laravel 13 + Filament 5 + PostgreSQL application for the 90th SISAT reunion. The pilot uses the same application and migration files on Vercel/Neon and on the later college server. It never seeds a default administrator or password.

## Pilot flow

1. A customer signs up with email or phone, or uses Google/LINE when OAuth credentials are configured.
2. The customer reserves a souvenir or one of 600 dining tables. A table includes a selected dining menu and concert admission for eight guests; dietary restrictions can be noted. Zones A–J each have 60 tables. The floor plan follows the supplied event PDF; its bottom-right repeated “G” label is interpreted as J.
3. A reservation holds inventory for 30 minutes while waiting for a slip. Finance manually approves, requests a corrected slip, or rejects it. Rejection and expiry release inventory.
4. Approval creates one pickup QR for a souvenir order or eight individual admission QR codes for a table. Gate staff scan admission; souvenir staff scan pickup. Each QR is redeemed online once.
5. A support officer may create an order for an existing account. The customer sees the same order in their account.

## Local setup

Requirements: PHP 8.4 with `pdo_pgsql`, `pdo_sqlite`, `intl`, `mbstring`, `gd`, `zip`; Composer 2; Node.js 22+.

```sh
composer install
npm ci
npm run build
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed --class=DemoSeeder
php artisan serve
```

`DemoSeeder` adds three sample products and 600 tables with placeholder prices. It adds **no users**. A real person signs up through `/register`, signs in, and generates a 10-minute proof code on `/account`; then a trusted operator runs `php artisan reunion:bootstrap-admin person@example.com PROOF_CODE` once. The super admin assigns roles to other registered accounts in `/admin/users`.

Run `php artisan test` for the flow and role checks. The pilot uses SQLite locally only for development; the deployed database is PostgreSQL. Concurrent reservation behavior must also be checked against Neon before the pilot is considered verified.

## Deployment

See [PILOT_RUNBOOK.md](PILOT_RUNBOOK.md). Vercel uses `Dockerfile.vercel` and FrankenPHP; the college deployment will use the same Laravel code and migrations. Secrets belong in the Vercel environment or protected local release environment, never in Git. `.env`, `.env.*`, SQLite files, and Vercel metadata are ignored.

The pilot accepts **test slips only** until the organizers set a real bank account and complete production security/recovery checks. The pilot is not an authorization to process real payments or live event admission.
