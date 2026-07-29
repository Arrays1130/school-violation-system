<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password</title>
</head>
<body style="margin:0;padding:0;background:#f3f4f6;font-family:Arial,Helvetica,sans-serif;">
    <div style="max-width:520px;margin:32px auto;background:#fff;border-radius:12px;padding:32px;box-shadow:0 4px 12px rgba(0,0,0,.06);">
        <h1 style="margin:0 0 12px;font-size:22px;color:#111827;">Reset Password</h1>
        <p style="margin:0 0 16px;color:#4b5563;line-height:1.5;">
            You are receiving this email because we received a password reset request for your account.
        </p>
        <p style="margin:0 0 24px;">
            <a href="{{ $url }}"
               style="display:inline-block;background:#1d4ed8;color:#fff;text-decoration:none;padding:12px 20px;border-radius:10px;font-weight:bold;">
                Reset Password
            </a>
        </p>
        <p style="margin:0 0 8px;color:#6b7280;font-size:13px;line-height:1.5;">
            This password reset link will expire in {{ $expireMinutes }} minutes.
        </p>
        <p style="margin:0;color:#6b7280;font-size:13px;line-height:1.5;">
            If you did not request a password reset, no further action is required.
        </p>
    </div>
</body>
</html>
