<!DOCTYPE html>
<html>
<head>
    <title>New Violation Recorded</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #d32f2f;">Violation Recorded</h2>
        <p>Dear {{ $case->student->first_name }} {{ $case->student->last_name }},</p>
        
        <p>This is a notification that a violation has been recorded in your file.</p>
        
        <div style="background-color: #f9f9f9; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <p><strong>Violation:</strong> {{ $case->violation->title }}</p>
            <p><strong>Date:</strong> {{ $case->occurred_at->format('F d, Y h:i A') }}</p>
            <p><strong>Details:</strong> {{ $case->description ?? 'No additional details provided.' }}</p>
        </div>

        <p>Please report to the Discipline Office if you have any questions or to schedule a consultation.</p>
        
        <p style="margin-top: 30px; font-size: 12px; color: #777;">
            This is an automated message from the I-Link CST Violation System.<br>
            Please do not reply to this email.
        </p>
    </div>
</body>
</html>
