/**
 * Free Gmail relay for VioTrack / school-violation-system on Render free tier.
 *
 * Setup (2 minutes):
 * 1. https://script.google.com → New project
 * 2. Paste this file as Code.gs
 * 3. Deploy → New deployment → Type: Web app
 *    - Execute as: Me
 *    - Who has access: Anyone
 * 4. Copy the Web app URL into Render env: GOOGLE_APPS_SCRIPT_URL
 *
 * Uses your own Gmail (MailApp) — no Resend/SendGrid.
 */
function doPost(e) {
  try {
    var data = JSON.parse(e.postData.contents);
    var to = data.to;
    var subject = data.subject || '(no subject)';
    var body = data.body || '';

    if (!to) {
      return ContentService
        .createTextOutput(JSON.stringify({ ok: false, error: 'Missing to' }))
        .setMimeType(ContentService.MimeType.JSON);
    }

    MailApp.sendEmail({
      to: to,
      subject: subject,
      htmlBody: body,
    });

    return ContentService
      .createTextOutput(JSON.stringify({ ok: true }))
      .setMimeType(ContentService.MimeType.JSON);
  } catch (err) {
    return ContentService
      .createTextOutput(JSON.stringify({ ok: false, error: String(err) }))
      .setMimeType(ContentService.MimeType.JSON);
  }
}

function doGet() {
  return ContentService
    .createTextOutput(JSON.stringify({ ok: true, service: 'VioTrack mail relay' }))
    .setMimeType(ContentService.MimeType.JSON);
}
