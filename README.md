# School Violation System (VioTrack)

Web and mobile platform for recording student violations, scheduling hearings, generating reports, and notifying deans — built for **I-Link CST** student affairs workflows.

## Stack

| Layer | Technology |
|-------|------------|
| Backend | Laravel 12, Sanctum, Filament, DomPDF, Maatwebsite Excel |
| Web UI | Blade, Tailwind, Inertia (auth), Livewire |
| Real-time | Laravel Reverb |
| Automation | n8n webhooks |
| Mobile | Flutter (`viotrack_flutter/`) |
| AI | Ollama + handbook RAG (`AiService`) |

## Roles

| Role | Access |
|------|--------|
| **super_admin** | Full access: users, hearings, violations catalog, email logs |
| **admin** | Record cases, manage students, reports, attachments (no user/hearing admin) |
| **dean** | Department-scoped read access + dean dashboard + mobile app |

Public registration is **disabled by default**. Create staff accounts as super admin under **User Accounts**.

## Quick start

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install && npm run build
php artisan serve
```

Default seeded logins (change in production):

| Email | Role | Password |
|-------|------|----------|
| admin@ilinkCST.edu | super_admin | password |
| dean@ilinkCST.edu | admin | password |

See `database/seeders/DeanSeeder.php` for college dean accounts.

### Development (all services)

```bash
composer dev
```

Runs HTTP server, queue worker, Vite, and Reverb together.

## Configuration

Key `.env` variables:

```env
APP_URL=http://localhost:8000

# Staff self-registration (keep false in production)
REGISTRATION_ENABLED=false

SCHOOL_NAME="I-Link CST"

# n8n
N8N_WEBHOOK_URL=
N8N_WEBHOOK_SECRET=

# AI assistant (Ollama)
OLLAMA_BASE_URL=http://127.0.0.1:11434
OLLAMA_MODEL=llama3.2

# Broadcasting / Reverb
REVERB_APP_ID=
REVERB_APP_KEY=
REVERB_APP_SECRET=
```

Departments are centralized in `config/school.php` (shortcut → full name). Update that file when colleges change.

## Mobile app (Flutter)

```bash
cd viotrack_flutter
flutter pub get
flutter run --dart-define=API_BASE_URL=http://YOUR_LAN_IP:8000/api
```

Use your machine's LAN IP when testing on a physical device. Android emulator: `http://10.0.2.2:8000/api`.

## Testing

```bash
php artisan test
```

Includes authorization, registration lockdown, and mobile API department scoping tests.

## Security notes

- Policies enforce access on students, cases, users, hearings, handbooks, and email logs.
- Deans are scoped to their department on web lists and mobile API.
- Mobile login is limited to staff roles and rate-limited.
- Remove or protect debug utilities before production deploy.

## Project structure

```
app/
  Http/Controllers/   # Web + API
  Policies/           # Role-based authorization
  Services/           # AI, n8n
  Support/            # DepartmentResolver
config/school.php     # Departments + registration flag
viotrack_flutter/     # Dean mobile app
n8n/                  # Workflow export
```

## License

MIT (Laravel framework components retain their respective licenses).
