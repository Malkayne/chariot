{{--
=======================================================================
  verify.blade.php  —  RCCG Camp Chariot  |  OTP Verification Page
  Route  : GET  /verify        → VerificationController@showForm
           POST /verify        → VerificationController@verify
           POST /resend-otp    → VerificationController@resend
  Guard  : auth
=======================================================================
--}}
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <meta name="description" content="Verify your account for RCCG Camp Chariot">
  <meta name="theme-color" content="#0D3B1F">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>Verify Account — Chariot | RCCG Camp</title>

  {{-- FONTS --}}
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;900&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,300&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">

  {{-- BOOTSTRAP 5.3 CSS --}}
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css">

  {{-- FONT AWESOME 6.5 --}}
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  {{-- CHARIOT GLOBAL CSS --}}
  <link rel="stylesheet" href="{{ asset('css/chariot.css') }}">

  <style>
    body {
      min-height: 100vh;
      background: linear-gradient(180deg, #071A0F 0%, #0A0A0A 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      padding: var(--sp-5) var(--sp-4);
    }

    .auth-container {
      width: 100%;
      max-width: 440px;
      animation: fadeUp 0.5s ease both;
    }

    @keyframes fadeUp {
      from { opacity: 0; transform: translateY(24px); }
      to   { opacity: 1; transform: translateY(0); }
    }

    .auth-logo { text-align: center; margin-bottom: var(--sp-6); }

    .auth-logo img {
      width: 72px; height: 72px;
      margin-bottom: var(--sp-4);
      filter: drop-shadow(0 4px 20px rgba(201,162,39,0.4));
    }

    .auth-logo-title {
      font-family: var(--font-display);
      font-size: var(--text-2xl);
      font-weight: var(--fw-black);
      color: var(--clr-gold-mid);
      letter-spacing: 0.12em;
      margin-bottom: var(--sp-1);
    }

    .auth-logo-subtitle { font-size: var(--text-sm); color: rgba(255,255,255,0.5); letter-spacing: var(--ls-wide); }

    .auth-card {
      background: var(--bg-card);
      border: 1px solid var(--border-color);
      border-radius: var(--radius-xl);
      padding: var(--sp-6) var(--sp-5);
      box-shadow: var(--shadow-elevated);
    }

    [data-theme="dark"] .auth-card { background: rgba(28,33,40,0.95); border-color: var(--clr-gray-700); }

    .auth-card-header { text-align: center; margin-bottom: var(--sp-6); }

    .verify-icon {
      width: 72px; height: 72px; border-radius: 50%;
      background: linear-gradient(135deg, var(--clr-gold-dark), var(--clr-gold-bright));
      color: var(--clr-green-darkest); font-size: 2rem;
      display: flex; align-items: center; justify-content: center;
      margin: 0 auto var(--sp-4); box-shadow: var(--elev-gold);
    }

    .auth-card-title {
      font-family: var(--font-display);
      font-size: var(--text-xl);
      font-weight: var(--fw-bold);
      color: var(--text-primary);
      margin-bottom: var(--sp-2);
    }

    .auth-card-desc { font-size: var(--text-sm); color: var(--text-secondary); line-height: var(--lh-relaxed); }
    .auth-card-desc strong { color: var(--clr-gold-mid); font-weight: var(--fw-semi); }

    /* Flash / error alerts */
    .auth-alert {
      border-radius: 10px; font-size: 13px;
      padding: 10px 14px; margin-bottom: 18px;
      display: flex; align-items: flex-start; gap: 10px;
    }
    .auth-alert-error   { background: rgba(220,53,69,.12);  border: 1px solid rgba(220,53,69,.25);  color: #e55368; }
    .auth-alert-success { background: rgba(25,135,84,.12);  border: 1px solid rgba(25,135,84,.25);  color: #20c77b; }
    .auth-alert-info    { background: rgba(201,162,39,.12); border: 1px solid rgba(201,162,39,.25); color: var(--clr-gold-mid); }

    /* OTP inputs */
    .otp-input-group {
      display: flex;
      gap: var(--sp-2);
      justify-content: center;
      margin-bottom: var(--sp-6);
    }

    .otp-digit {
      width: 48px; height: 56px;
      border: 2px solid var(--border-color);
      border-radius: var(--radius-input);
      font-family: var(--font-mono);
      font-size: var(--text-xl);
      font-weight: var(--fw-bold);
      text-align: center;
      background: var(--bg-card);
      color: var(--text-primary);
      transition: border-color 0.2s, box-shadow 0.2s, transform 0.15s;
    }

    .otp-digit:focus {
      outline: none;
      border-color: var(--clr-gold-mid);
      box-shadow: 0 0 0 3px rgba(201,162,39,0.2);
      transform: translateY(-2px);
    }

    .otp-digit.filled  { border-color: var(--clr-green-mid); color: var(--clr-green-mid); }
    .otp-digit.is-invalid {
      border-color: var(--clr-danger);
      animation: shake 0.4s ease;
    }

    @keyframes shake {
      0%, 100% { transform: translateX(0); }
      25% { transform: translateX(-5px); }
      75% { transform: translateX(5px); }
    }

    /* Resend section */
    .resend-section { text-align: center; margin-bottom: var(--sp-5); }
    .resend-text { font-size: var(--text-sm); color: var(--text-secondary); }
    .resend-countdown { font-family: var(--font-mono); color: var(--clr-gold-mid); font-weight: var(--fw-bold); }

    /* Primary button */
    .btn-chariot {
      width: 100%;
      padding: var(--sp-3) var(--sp-5);
      border: none;
      border-radius: var(--radius-btn);
      font-family: var(--font-body);
      font-size: var(--text-base);
      font-weight: var(--fw-bold);
      cursor: pointer;
      transition: all 0.25s;
      display: inline-flex; align-items: center; justify-content: center; gap: var(--sp-2);
    }

    .btn-primary-c {
      background: linear-gradient(135deg, var(--clr-gold-dark), var(--clr-gold-bright));
      color: var(--clr-green-darkest);
      box-shadow: var(--elev-gold);
    }

    .btn-primary-c:hover   { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(201,162,39,0.5); color: var(--clr-green-darkest); }
    .btn-primary-c:active  { transform: translateY(0); }
    .btn-primary-c:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

    .btn-spinner { display: none; }
    .btn-submitting .btn-spinner { display: inline-block; }
    .btn-submitting .btn-icon    { display: none; }

    /* Resend button */
    .btn-resend {
      background: none; border: none; padding: 0;
      color: var(--clr-gold-mid); font-size: var(--text-sm);
      font-weight: var(--fw-semi); cursor: pointer;
      transition: color 0.2s; text-decoration: underline;
    }
    .btn-resend:hover:not(:disabled) { color: var(--clr-gold-bright); }
    .btn-resend:disabled { color: var(--text-muted); cursor: not-allowed; text-decoration: none; }

    .back-link {
      text-align: center; margin-top: var(--sp-5);
      padding-top: var(--sp-5); border-top: 1px solid var(--border-color);
    }

    .back-link a {
      display: inline-flex; align-items: center; gap: var(--sp-2);
      font-size: var(--text-sm); color: var(--text-secondary);
      text-decoration: none; transition: color 0.2s;
    }

    .back-link a:hover { color: var(--clr-gold-mid); }

    @media (max-width: 480px) {
      .auth-container { max-width: 100%; }
      .auth-card { padding: var(--sp-5) var(--sp-4); }
      .otp-digit { width: 42px; height: 50px; font-size: var(--text-lg); }
      .otp-input-group { gap: var(--sp-1); }
    }
  </style>
</head>
<body>

  <div class="auth-container">
    
    {{-- Logo Section --}}
    <div class="auth-logo">
      <img src="{{ asset('images/chariot-logo.png') }}" alt="Chariot Logo" onerror="this.style.display='none'">
      <div class="auth-logo-title">CHARIOT</div>
      <div class="auth-logo-subtitle">Ride with Purpose</div>
    </div>

    {{-- Verification Card --}}
    <div class="auth-card">
      
      <div class="auth-card-header">
        <div class="verify-icon">
          <i class="fa-solid fa-shield-halved"></i>
        </div>
        <h1 class="auth-card-title">Verify Your Account</h1>
        <p class="auth-card-desc">
          @if(auth()->user()->email)
            We sent a 6-digit code to<br>
            <strong>{{ auth()->user()->email }}</strong>
          @else
            Enter the 6-digit verification code<br>
            sent to your registered contact.
          @endif
        </p>
      </div>

      {{-- ── Flash / Session Messages ────────────────────── --}}
      @if(session('success'))
        <div class="auth-alert auth-alert-success" role="alert">
          <i class="fa-solid fa-circle-check mt-1"></i>
          <span>{{ session('success') }}</span>
        </div>
      @endif

      @if(session('info'))
        <div class="auth-alert auth-alert-info" role="alert">
          <i class="fa-solid fa-circle-info mt-1"></i>
          <span>{{ session('info') }}</span>
        </div>
      @endif

      @if(session('error'))
        <div class="auth-alert auth-alert-error" role="alert">
          <i class="fa-solid fa-circle-exclamation mt-1"></i>
          <span>{{ session('error') }}</span>
        </div>
      @endif

      @if($errors->any())
        <div class="auth-alert auth-alert-error" role="alert">
          <i class="fa-solid fa-circle-exclamation mt-1"></i>
          <div>
            @foreach($errors->all() as $error)
              <div>{{ $error }}</div>
            @endforeach
          </div>
        </div>
      @endif

      {{-- ── VERIFY FORM ─────────────────────────────────── --}}
      <form
        id="verifyForm"
        action="{{ route('verify.submit') }}"
        method="POST"
      >
        @csrf

        {{-- OTP Inputs (6 boxes, assembled into hidden field on submit) --}}
        <div class="otp-input-group" id="otpInputGroup">
          @for($i = 0; $i < 6; $i++)
            <input
              type="text"
              class="otp-digit"
              maxlength="1"
              pattern="[0-9]"
              inputmode="numeric"
              aria-label="OTP digit {{ $i + 1 }}"
              autocomplete="one-time-code"
            >
          @endfor
        </div>

        {{-- Hidden field that carries the assembled OTP to the server --}}
        <input type="hidden" name="otp" id="otpValue">

        {{-- Resend Section --}}
        <div class="resend-section">
          <p class="resend-text">
            Didn't receive the code? &nbsp;
            <span class="resend-countdown" id="countdown">00:45</span>
          </p>
        </div>

        {{-- Verify Button --}}
        <button type="submit" class="btn-chariot btn-primary-c mb-3" id="verifyBtn" disabled>
          <i class="fa-solid fa-circle-check btn-icon"></i>
          <span class="btn-spinner spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
          <span class="btn-label">Verify Code</span>
        </button>

      </form>

      {{-- Resend form (separate POST form) --}}
      <form
        id="resendForm"
        action="{{ route('resend-otp') }}"
        method="POST"
        style="text-align:center;"
      >
        @csrf
        <button type="submit" class="btn-resend" id="resendBtn" disabled>
          Resend Code
        </button>
      </form>

      {{-- Back to Login --}}
      <div class="back-link">
        <a href="#" onclick="event.preventDefault(); document.getElementById('logoutForm').submit();">
          <i class="fa-solid fa-arrow-left"></i>
          Back to Login
        </a>
      </div>

    </div>

  </div>

  {{-- Hidden logout form --}}
  <form id="logoutForm" action="{{ route('logout') }}" method="POST" style="display:none">
    @csrf
  </form>

  {{-- BOOTSTRAP JS --}}
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>

  {{-- PAGE SCRIPT --}}
  <script>
  document.addEventListener('DOMContentLoaded', function () {

    /* ── OTP Input Handling ── */
    const otpInputs = document.querySelectorAll('.otp-digit');
    const otpValue  = document.getElementById('otpValue');
    const verifyBtn = document.getElementById('verifyBtn');
    const resendBtn = document.getElementById('resendBtn');

    function updateOtpValue() {
      const otp = Array.from(otpInputs).map(i => i.value).join('');
      otpValue.value = otp;
      verifyBtn.disabled = otp.length !== 6;
    }

    otpInputs.forEach(function (input, index) {

      // Digits only + auto-advance
      input.addEventListener('input', function () {
        this.value = this.value.replace(/[^0-9]/g, '');

        if (this.value.length === 1) {
          this.classList.add('filled');
          if (index < otpInputs.length - 1) otpInputs[index + 1].focus();
        } else {
          this.classList.remove('filled');
        }

        this.classList.remove('is-invalid');
        updateOtpValue();
      });

      // Backspace navigation
      input.addEventListener('keydown', function (e) {
        if (e.key === 'Backspace' && this.value === '') {
          if (index > 0) otpInputs[index - 1].focus();
        }
      });

      // Paste support
      input.addEventListener('paste', function (e) {
        e.preventDefault();
        const pasted = (e.clipboardData || window.clipboardData)
          .getData('text')
          .replace(/[^0-9]/g, '')
          .slice(0, 6);

        pasted.split('').forEach(function (digit, i) {
          if (otpInputs[i]) {
            otpInputs[i].value = digit;
            otpInputs[i].classList.add('filled');
          }
        });

        const next = Math.min(pasted.length, 5);
        otpInputs[next].focus();
        updateOtpValue();
      });
    });

    /* ── Verify form submit: add loading state ── */
    const verifyForm = document.getElementById('verifyForm');
    verifyForm.addEventListener('submit', function () {
      verifyBtn.disabled = true;
      verifyBtn.classList.add('btn-submitting');
      verifyBtn.querySelector('.btn-label').textContent = 'Verifying…';
    });

    /* ── Resend form submit: loading state ── */
    const resendForm = document.getElementById('resendForm');
    resendForm.addEventListener('submit', function () {
      resendBtn.textContent = 'Sending…';
      resendBtn.disabled = true;
    });

    /* ── Countdown Timer ── */
    const countdownEl = document.getElementById('countdown');
    let timeLeft = 45;

    function updateCountdown() {
      const minutes = Math.floor(timeLeft / 60);
      const seconds = timeLeft % 60;
      countdownEl.textContent =
        String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');

      if (timeLeft > 0) {
        timeLeft--;
        setTimeout(updateCountdown, 1000);
      } else {
        countdownEl.textContent = '';
        resendBtn.disabled = false;
      }
    }

    updateCountdown();

    /* ── Autofocus first box ── */
    otpInputs[0].focus();

  });
  </script>

</body>
</html>
