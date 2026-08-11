# Production deployment (Render — free tier)

This guide matches the live app at **https://school-violation-system.onrender.com**.

## Your actual architecture

| Layer | What you use |
|-------|----------------|
| **Hosting** | Render Web Service (free) — Docker, `school-violation-system` |
| **Database (prod)** | **Render PostgreSQL (free)** — not Laragon MySQL |
| **Database (local)** | Laragon **MySQL** — dev only |
| **Queue / cache / session (prod)** | **database** driver (no paid Redis, no paid Worker) |
| **CDN / DNS (optional)** | **Cloudflare** in front of Render (custom domain) |
| **Files (optional)** | **Cloudinary** package in repo (`CLOUDINARY_*`) or local disk |
| **Mobile API** | `https://school-violation-system.onrender.com/api` |

**Important:** Local MySQL ≠ production DB. That is normal. Laragon is for dev; Render free only offers **PostgreSQL**.

**Free tier limits:**

- Web spins down after ~15 min idle (slow first load).
- Free Postgres **expires after 30 days** — upgrade before capstone/production deadline.
- Disk is ephemeral — attachments on `local` disk are lost on redeploy unless you use Cloudinary/S3.
- Background **Worker** is a **paid** Render service — this project runs `queue:work` inside the web container instead.

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
APP_URL=https://school-violation-system.onrender.com

# Database — Render PostgreSQL (free). Local Laragon uses MySQL; prod uses Postgres.
DB_CONNECTION=pgsql
# Render injects DB_URL from the linked Postgres instance (see render.yaml).

# Queue, cache, session — database (free tier; no Redis/Worker required)
SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database

# Monitoring/log routing
LOG_CHANNEL=stack
LOG_STACK=stderr,daily
MONITORING_LOG_CHANNEL=monitoring
MONITORING_LOG_STACK=stderr,daily
DB_SLOW_QUERY_MS=500

# Staff registration — keep disabled
REGISTRATION_ENABLED=false

# Case attachments — local on free tier (lost on redeploy). Prefer Cloudinary:
ATTACHMENTS_DISK=local
# ATTACHMENTS_DISK=cloudinary
# CLOUDINARY_CLOUD_NAME=
# CLOUDINARY_API_KEY=
# CLOUDINARY_API_SECRET=

# Mail — keep Gmail SMTP for local / paid hosts
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=
MAIL_FROM_NAME="${APP_NAME}"

# REQUIRED on Render free (SMTP ports 25/465/587 are blocked).
# Free Gmail relay via Google Apps Script — same Gmail, no Resend/SendGrid.
# Deploy google-apps-script/Code.gs → paste Web app URL here:
GOOGLE_APPS_SCRIPT_URL=

# FCM push (dean mobile app)
FCM_SERVER_KEY=
# Flutter: copy viotrack_flutter/android/app/google-services.json.example → google-services.json
# after creating the Firebase Android app (package: com.viotrack.dean).

# Optional: n8n automation
N8N_WEBHOOK_URL=
N8N_WEBHOOK_SECRET=
```

## Render web service

The included `Dockerfile` runs:

1. `composer install --no-dev`
2. `npm install && npm run build`
3. PHP extensions: **pdo_mysql**, **redis** (phpredis); **pdo_pgsql** included if you ever need Postgres
4. On start: migrate, seed-if-empty, **queue worker in background**, then `artisan serve`

The web container runs `queue:work` in the same process space so you do not need a paid Render Worker on the free plan.

**Build command:** Docker (automatic from `Dockerfile`)

**Start command:** Defined in `Dockerfile` CMD

**Health check:** `GET /health` — expect `status=ok` with checks.

## Cloudflare (optional)

If your domain is on Cloudflare pointing to Render:

1. DNS: `CNAME` → `school-violation-system.onrender.com` (proxied orange cloud is OK).
2. Set `APP_URL` to your custom domain (e.g. `https://viotrack.yourschool.edu`).
3. SSL/TLS mode: **Full** (not Flexible) when Render serves HTTPS.
4. Proxies are already trusted in `bootstrap/app.php` (`trustProxies(at: '*')`).

## One-click Blueprint (`render.yaml`)

Free-tier blueprint provisions:

| Resource | Name | Purpose |
|----------|------|---------|
| PostgreSQL (free) | `viotrack-db` | Production database |
| Web (Docker, free) | `school-violation-system` | HTTP + inline queue worker |

No Redis. No paid Worker.

**Deploy steps:**

1. Push repo to GitHub/GitLab.
2. Render Dashboard → **New** → **Blueprint** → connect repo.
3. Set `APP_URL` and mail/AI/SMS secrets in the web service env.
4. Verify: `curl https://school-violation-system.onrender.com/health`

**Upgrade path (when you outgrow free):**

- Postgres: `basic-256mb` before 30-day free DB expiry.
- Add Render Redis + Worker; switch `QUEUE_CONNECTION` / `CACHE_STORE` to `redis`.

On boot the container also runs `php artisan ai:index-if-empty` in the background when embeddings are missing (requires `GEMINI_API_KEY`).
## Storage on ephemeral hosts

Render free-tier disks are ephemeral. Uploaded case attachments are lost on redeploy unless you use S3:

1. Create an S3 bucket (or compatible storage)
2. Set `ATTACHMENTS_DISK=s3` and AWS credentials
3. Redeploy

## Pre-demo checklist

Complete these before a thesis defense or public demo:

| Check | Where | Ready when |
|-------|--------|------------|
| **App name / titles** | Render env `APP_NAME=VioTrack` (also in `render.yaml`) | Browser tab says `Welcome - VioTrack`, not `Laravel` |
| **Landing page** | `https://…/` | Shows Admin, Dean Portal, and Dean App buttons |
| **Mail on Render** | `GOOGLE_APPS_SCRIPT_URL` | Password reset and case emails send (SMTP ports are blocked) |
| **Attachments** | `ATTACHMENTS_DISK=cloudinary` (or S3) + credentials | Files survive redeploy; local disk is wiped |
| **Dean push** | Firebase `google-services.json` + `FCM_SERVER_KEY` | Alerts arrive when the Dean app is closed |
| **Postgres expiry** | Render dashboard → Database | Free DB upgraded **before** the 30-day limit |
| **Cold start** | First visit after idle | Landing copy mentions a short wake-up delay |
| **Health** | `GET /health` | `"status":"ok"` and database/cache/queue true |

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
| Emails or SMS delayed | Verify worker command listens to `notifications` queue and Redis is reachable |
| Dean app 404 | Run `scripts/build-dean-web.ps1` before Docker build |
