# VioTrack × n8n — Capstone Automation

Presentation-ready automation for the VioTrack school violation lifecycle.

## Online setup (recommended for defense)

### 1) Host n8n online
1. Go to [https://app.n8n.cloud](https://app.n8n.cloud) and create / open a workspace (free trial works for demo).
2. **Workflows → Import from File** → choose `VioTrack-Capstone-Automation.json`.
3. Open **VioTrack Webhook** node → switch to **Production URL** → copy it.  
   It should look like:  
   `https://YOUR-INSTANCE.app.n8n.cloud/webhook/viotrack-automation`  
   (must be `/webhook/`, **not** `/webhook-test/`)
4. Toggle the workflow **Active** (ON).
5. Optional: attach SMTP credential on `Send Email Notice`; set n8n env `SEMAPHORE_API_KEY` for SMS.

### 2) Point Render (VioTrack) to that webhook
In Render Dashboard → your web service → **Environment**:

```env
N8N_WEBHOOK_URL=https://YOUR-INSTANCE.app.n8n.cloud/webhook/viotrack-automation
```

Save → wait for redeploy (or Manual Deploy).

### 3) Verify
Trigger a real action in production (record violation / assign GSO hours) and open n8n → **Executions**.

---

## Pipeline the panel will see

```text
Violation recorded
   → Hearing scheduled
      → GSO hours assigned
         → GSO DTR completed → OSA handoff
            → Case closed
```

## Events Laravel sends

| `event_type` | When |
|---|---|
| `violation_recorded` | New case / escalation |
| `hearing_scheduled` | Hearing created/updated |
| `gso_sanction_assigned` | OSA assigns community service hours |
| `gso_sanction_completed` | GSO taps Complete in mobile app |
| `case_closed` | OSA closes the case |

## 3-minute defense script

1. Open n8n **Executions** beside VioTrack.
2. Record a violation → `violation_recorded`.
3. Schedule hearing → `hearing_scheduled`.
4. Assign GSO hours → `gso_sanction_assigned`.
5. GSO app Complete → `gso_sanction_completed`.
6. Close case → `case_closed`.

## Security

- Do not hardcode SMS API keys in the workflow JSON.
- Use `/webhook/` production URL while the workflow is Active.
