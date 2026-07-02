# Production deployment (Render)

This guide covers deploying VioTrack to [Render](https://render.com) or similar PHP hosts.

## Pre-deploy checklist

1. Run tests locally: `php artisan test`
2. Build frontend: `npm run build`
3. Build dean web app (optional): `scripts/build-dean-web.ps1`
4. Ensure `public/brand_logo.png` exists (committed in repo)

## Environment variables

Set these on Render (or in `.env` for other hosts):

```env
APP_NAME="VioTrack"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-app.onrender.com

# Database (Render PostgreSQL or external MySQL)
DB_CONNECTION=pgsql
DB_HOST=
DB_PORT=5432
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=

# Sessions & cache (use database or redis in production)
SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database

# Staff registration — keep disabled
REGISTRATION_ENABLED=false

# Case attachments — use s3 for persistent storage on ephemeral disks
ATTACHMENTS_DISK=local
# For S3:
# ATTACHMENTS_DISK=s3
# AWS_ACCESS_KEY_ID=
# AWS_SECRET_ACCESS_KEY=
# AWS_DEFAULT_REGION=
# AWS_BUCKET=

# Mail (password reset, notifications)
MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=
MAIL_FROM_NAME="${APP_NAME}"

# FCM push (dean mobile app)
FCM_SERVER_KEY=

# Optional: n8n automation
N8N_WEBHOOK_URL=
N8N_WEBHOOK_SECRET=
```

## Render web service

The included `Dockerfile` runs:

1. `composer install --no-dev`
2. `npm install && npm run build`
3. On start: `php artisan migrate --force`, `php artisan app:seed-if-empty`, `php artisan serve`

**Build command:** Docker (automatic from `Dockerfile`)

**Start command:** Defined in `Dockerfile` CMD

**Health check:** `GET /health` — expect `{"status":"ok"}`

## Queue worker (recommended)

Email, SMS, and notifications are queued. On Render, add a **Background Worker** service:

```bash
php artisan queue:work --sleep=3 --tries=3 --max-time=3600
```

Use the same env vars as the web service.

## Storage on ephemeral hosts

Render free-tier disks are ephemeral. Uploaded case attachments are lost on redeploy unless you use S3:

1. Create an S3 bucket (or compatible storage)
2. Set `ATTACHMENTS_DISK=s3` and AWS credentials
3. Redeploy

## Post-deploy verification

```bash
curl -s https://your-app.onrender.com/health
curl -I https://your-app.onrender.com/login
curl -I https://your-app.onrender.com/brand_logo.png
```

Manual checks:

- [ ] Login page loads with blue Sign In button
- [ ] Staff can log in and reach dashboard
- [ ] Dean mobile API login works (`POST /api/login`)
- [ ] Ziggy routes have no Filament/Livewire entries
- [ ] Attachments upload and download (if using S3, verify persistence after redeploy)

## Migrations

Migrations run automatically on container start. For zero-downtime deploys, run migrations manually before switching traffic:

```bash
php artisan migrate --force
```

## Troubleshooting

| Symptom | Fix |
|---------|-----|
| 500 on dashboard | Check `storage/logs/laravel.log`; ensure `APP_KEY` is set |
| Assets 404 | Run `npm run build`; verify `public/build` is in the image |
| Attachments missing after redeploy | Set `ATTACHMENTS_DISK=s3` |
| Emails not sent | Add queue worker; verify `MAIL_*` and `QUEUE_CONNECTION` |
| Dean app 404 | Run `scripts/build-dean-web.ps1` before Docker build |
