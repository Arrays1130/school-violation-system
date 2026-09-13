# VioTrack × n8n — Capstone Automation

Presentation-ready automation for the VioTrack school violation lifecycle.

## What the panel will see

```text
Violation recorded
   → Hearing scheduled
      → GSO hours assigned
         → GSO DTR completed → OSA handoff
            → Case closed
```

Each stage fires a Laravel webhook into **n8n**, which:

1. Routes the event
2. Builds email + SMS copy
3. Sends notices (when credentials are configured)
4. Returns an audit JSON response (great for live Executions demo)

## Import the workflow

1. Open n8n (local or cloud)
2. **Workflows → Import from File**
3. Choose [`VioTrack-Capstone-Automation.json`](VioTrack-Capstone-Automation.json)
4. Configure:
   - **SMTP** credential on `Send Email Notice`
   - Env vars in n8n:
     - `SEMAPHORE_API_KEY` (optional SMS)
     - `SEMAPHORE_SENDER` (optional)
     - `VIOTRACK_FROM_EMAIL` (optional)
5. Click **Listen for test event** / activate the workflow
6. Copy the Production Webhook URL

## Laravel `.env`

```env
N8N_WEBHOOK_URL=http://localhost:5678/webhook/viotrack-automation
# For n8n cloud, use the production HTTPS webhook URL from the imported workflow
```

After changing `.env`:

```bash
php artisan config:clear
php artisan n8n:test
```

`docker/start.sh` already runs the queue worker path your app uses; make sure queue jobs are processed so webhooks leave Laravel.

## Events Laravel sends

| `event_type` | When |
|---|---|
| `violation_recorded` | New case / escalation |
| `hearing_scheduled` | Hearing created/updated |
| `gso_sanction_assigned` | OSA assigns community service hours |
| `gso_sanction_completed` | GSO taps Complete in mobile app |
| `case_closed` | OSA closes the case |

Legacy export [`school_violation_workflow.json`](school_violation_workflow.json) is kept for reference; use **VioTrack Capstone Automation** for defense.

## 3-minute defense script

1. Open n8n **Executions** beside VioTrack web/app.
2. Record a minor violation → show `violation_recorded` execution.
3. Schedule a hearing → `hearing_scheduled`.
4. Assign GSO service hours → `gso_sanction_assigned`.
5. On GSO app: Time In / Out / **Complete** → `gso_sanction_completed`.
6. Close case on web → `case_closed`.
7. Point to sticky notes on the canvas: stages 1–5 = full compliance pipeline.

## Security notes

- Do **not** hardcode SMS API keys in the workflow JSON.
- Prefer n8n environment variables / credentials.
- Keep `N8N_WEBHOOK_URL` out of public screenshots if it includes secrets.
