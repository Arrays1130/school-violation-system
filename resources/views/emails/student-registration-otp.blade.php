<!DOCTYPE html>
<html>
<head>
    <title>Your OTP Code</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f7f6; padding: 20px; color: #333;">
    <div style="max-w: 500px; margin: 0 auto; background-color: #fff; border-radius: 10px; padding: 30px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); text-align: center;">
        <h2 style="color: #4f46e5; margin-top: 0;">Registration Verification</h2>
        <p style="font-size: 16px; line-height: 1.5; color: #555;">
            Thank you for registering. Please use the following One-Time Password (OTP) to complete your registration. This code will expire in 10 minutes.
        </p>
        <div style="margin: 30px 0;">
            <span style="font-size: 32px; font-weight: bold; letter-spacing: 5px; color: #1e293b; background-color: #f8fafc; padding: 15px 30px; border-radius: 8px; border: 1px solid #e2e8f0;">
                {{ $otp }}
            </span>
        </div>
        <p style="font-size: 14px; color: #888; margin-bottom: 0;">
            If you did not request this, please ignore this email.
        </p>
    </div>
</body>
</html>
