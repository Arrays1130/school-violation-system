<!DOCTYPE html>
<html>
<head>
    <title>Hearing Scheduled</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #1976d2;">Hearing Notice</h2>
        <p>Dear {{ $hearing->case->student->first_name }} {{ $hearing->case->student->last_name }},</p>
        
        <p>A hearing has been scheduled regarding your violation case.</p>
        
        <div style="background-color: #e3f2fd; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <p><strong>Scheduled Date:</strong> {{ $hearing->scheduled_at->format('F d, Y') }}</p>
            <p><strong>Time:</strong> {{ $hearing->scheduled_at->format('h:i A') }}</p>
            <p><strong>Location:</strong> {{ $hearing->venue }}</p>
            @if($hearing->notes)
            <p><strong>Notes:</strong> {{ $hearing->notes }}</p>
            @endif
        </div>

        <p>Your presence is required. Failure to appear may result in further sanctions.</p>
        
        <p style="margin-top: 30px; font-size: 12px; color: #777;">
            This is an automated message from the I-Link CST Violation System.<br>
            Please do not reply to this email.
        </p>
    </div>
</body>
</html>
