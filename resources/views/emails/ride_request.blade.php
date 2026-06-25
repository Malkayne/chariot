<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>New Ride Request</title>
  <style>
    body { margin: 0; padding: 0; background: #f0f0f0; font-family: 'Segoe UI', Arial, sans-serif; }
    .wrapper { max-width: 560px; margin: 40px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
    .header { background: #0D3B1F; padding: 32px 40px; text-align: center; }
    .header h1 { color: #C9A227; margin: 0; font-size: 28px; letter-spacing: 3px; font-weight: 700; }
    .header p { color: #F5F0E8; margin: 6px 0 0; font-size: 13px; opacity: 0.8; }
    .body { padding: 40px; }
    .greeting { font-size: 16px; color: #333; margin-bottom: 16px; }
    .request-box { background: #F5F0E8; border: 1.5px solid #C9A227; border-radius: 10px; padding: 24px; margin: 28px 0; }
    .request-detail { font-size: 14px; color: #333; margin-bottom: 12px; }
    .request-detail strong { color: #0D3B1F; }
    .note { font-size: 13px; color: #555; line-height: 1.6; }
    .divider { border: none; border-top: 1px solid #eee; margin: 28px 0; }
    .footer { background: #f8f8f8; padding: 20px 40px; text-align: center; font-size: 12px; color: #aaa; }
    .btn-action { display: inline-block; background: #C9A227; color: #0D3B1F; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: bold; margin-top: 10px; }
  </style>
</head>
<body>
  <div class="wrapper">
    <div class="header">
      <h1>CHARIOT</h1>
      <p>RCCG Camp Ground — Ride with Purpose</p>
    </div>
    <div class="body">
      <p class="greeting">Hello, <strong>{{ $rideRequest->ride->driver->name }}</strong>!</p>
      <p class="note">You have a new ride request on Chariot. A rider wants to join your ride:</p>

      <div class="request-box">
        <div class="request-detail"><strong>Rider:</strong> {{ $rideRequest->rider->name }}</div>
        <div class="request-detail"><strong>From:</strong> {{ $rideRequest->ride->fromZone->name }}</div>
        <div class="request-detail"><strong>To:</strong> {{ $rideRequest->ride->toZone->name }}</div>
        @if($rideRequest->ride->departing_at)
          <div class="request-detail"><strong>Departing At:</strong> {{ \Carbon\Carbon::parse($rideRequest->ride->departing_at)->format('h:i A') }}</div>
        @endif
        @if($rideRequest->pickup_note)
          <div class="request-detail"><strong>Pickup Note:</strong> "<em>{{ $rideRequest->pickup_note }}</em>"</div>
        @endif
      </div>

      <p class="note">
        Please log in to your driver dashboard to accept or decline this request.
      </p>

      <div style="text-align: center;">
        <a href="{{ route('login') }}" class="btn-action">Go to Dashboard</a>
      </div>

      <hr class="divider">

      <p class="note" style="font-size: 11px; color: #888;">
        This is an automated notification. Please do not reply directly to this email.
      </p>
    </div>
    <div class="footer">
      &copy; {{ date('Y') }} RCCG Camp Chariot &mdash; All rights reserved.
    </div>
  </div>
</body>
</html>
