<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Your Chariot Verification Code</title>
  <style>
    body { margin: 0; padding: 0; background: #f0f0f0; font-family: 'Segoe UI', Arial, sans-serif; }
    .wrapper { max-width: 560px; margin: 40px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
    .header { background: #0D3B1F; padding: 32px 40px; text-align: center; }
    .header h1 { color: #C9A227; margin: 0; font-size: 28px; letter-spacing: 3px; font-weight: 700; }
    .header p { color: #F5F0E8; margin: 6px 0 0; font-size: 13px; opacity: 0.8; }
    .body { padding: 40px; }
    .greeting { font-size: 16px; color: #333; margin-bottom: 16px; }
    .otp-box { background: #F5F0E8; border: 2px dashed #C9A227; border-radius: 10px; padding: 24px; text-align: center; margin: 28px 0; }
    .otp-label { font-size: 12px; color: #666; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; }
    .otp-code { font-size: 48px; font-weight: 800; color: #0D3B1F; letter-spacing: 10px; font-family: 'Courier New', monospace; }
    .otp-expiry { font-size: 13px; color: #888; margin-top: 10px; }
    .note { font-size: 13px; color: #555; line-height: 1.6; }
    .divider { border: none; border-top: 1px solid #eee; margin: 28px 0; }
    .footer { background: #f8f8f8; padding: 20px 40px; text-align: center; font-size: 12px; color: #aaa; }
  </style>
</head>
<body>
  <div class="wrapper">
    <div class="header">
      <h1>CHARIOT</h1>
      <p>RCCG Camp Ground — Ride with Purpose</p>
    </div>
    <div class="body">
      <p class="greeting">Hello, <strong>{{ $user->name }}</strong>!</p>
      <p class="note">You requested a verification code for your Chariot account. Enter the code below to complete your verification:</p>

      <div class="otp-box">
        <div class="otp-label">Your One-Time Code</div>
        <div class="otp-code">{{ $otp }}</div>
        <div class="otp-expiry">⏱ Expires in 10 minutes</div>
      </div>

      <p class="note">
        If you did not request this code, please ignore this email. Your account is safe.
      </p>

      <hr class="divider">

      <p class="note" style="font-size: 12px; color: #888;">
        Do not share this code with anyone. Chariot staff will never ask for your OTP.
      </p>
    </div>
    <div class="footer">
      &copy; {{ date('Y') }} RCCG Camp Chariot &mdash; All rights reserved.
    </div>
  </div>
</body>
</html>
