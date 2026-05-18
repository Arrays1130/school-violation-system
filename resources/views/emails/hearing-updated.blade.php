<!DOCTYPE html>
<html>
<head>
    <title>Your Hearing Schedule has Changed</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #ed6c02;">Your Hearing Schedule has Changed</h2>
        <p>Dear {{ $hearing->case->student->first_name }} {{ $hearing->case->student->last_name }},</p>
        
        <p>The details for your scheduled hearing have been <strong>updated</strong>. Please see the new schedule below.</p>
        
        <div style="background-color: #fff3e0; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 5px solid #ed6c02;">
            <p><strong>Scheduled Date:</strong> {{ $hearing->scheduled_date->format('F d, Y') }}</p>
            <p><strong>Time:</strong> {{ $hearing->scheduled_time->format('h:i A') }}</p>
            <p><strong>Location:</strong> {{ $hearing->location }}</p>
            @if($hearing->notes)
            <p><strong>Notes:</strong> {{ $hearing->notes }}</p>
            @endif
        </div>

        <p>Please come at this new time and place.</p>
        
        <p style="margin-top: 30px; font-size: 12px; color: #777;">
            This is an automated message from the I-Link CST Violation System.<br>
            Please do not reply to this email.
        </p>
    </div>
</body>
</html>
