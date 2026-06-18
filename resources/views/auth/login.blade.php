{{--
=======================================================================
  login.blade.php  —  RCCG Camp Chariot  |  Login Page
  Route  : GET /login  → LoginController@showForm
           POST /login → LoginController@authenticate
  Guard  : guest (redirect if auth)
=======================================================================
--}}
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <meta name="description" content="Login to RCCG Camp Chariot — Smart mobility for the saints">
  <meta name="theme-color" content="#0D3B1F">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>Login — Chariot | RCCG Camp</title>

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

  {{-- PAGE-SPECIFIC STYLES --}}
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

    .auth-logo {
      display: flex;
      flex-direction: column;
      align-items: center;
      text-align: center;
      justify-content: center;
      margin-bottom: var(--sp-6);
    }

    .auth-logo img {
      width: 72px;
      height: 72px;
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

    .auth-logo-subtitle {
      font-size: var(--text-sm);
      color: rgba(255,255,255,0.5);
      letter-spacing: var(--ls-wide);
    }

    .auth-card {
      background: var(--bg-card);
      border: 1px solid var(--border-color);
      border-radius: var(--radius-xl);
      padding: var(--sp-6) var(--sp-5);
      box-shadow: var(--shadow-elevated);
    }

    [data-theme="dark"] .auth-card {
      background: rgba(28,33,40,0.95);
      border-color: var(--clr-gray-700);
    }

    .auth-card-header {
      text-align: center;
      margin-bottom: var(--sp-5);
    }

    .auth-card-title {
      font-family: var(--font-display);
      font-size: var(--text-xl);
      font-weight: var(--fw-bold);
      color: var(--text-primary);
      margin-bottom: var(--sp-2);
    }

    .auth-card-desc {
      font-size: var(--text-sm);
      color: var(--text-secondary);
    }

    /* Flash / error alerts */
    .auth-alert {
      border-radius: 10px;
      font-size: 13px;
      padding: 10px 14px;
      margin-bottom: 18px;
      display: flex;
      align-items: flex-start;
      gap: 10px;
    }
    .auth-alert-error   { background: rgba(220,53,69,.12); border: 1px solid rgba(220,53,69,.25); color: #e55368; }
    .auth-alert-success { background: rgba(25,135,84,.12);  border: 1px solid rgba(25,135,84,.25);  color: #20c77b; }
    .auth-alert-info    { background: rgba(201,162,39,.12); border: 1px solid rgba(201,162,39,.25); color: var(--clr-gold-mid); }

    /* field error */
    .field-error {
      font-size: 12px;
      color: #e55368;
      margin-top: 5px;
      display: flex;
      align-items: center;
      gap: 4px;
    }

    .form-group-c {
      margin-bottom: var(--sp-4);
    }

    .form-label-c {
      display: flex;
      align-items: center;
      gap: var(--sp-2);
      font-size: var(--text-sm);
      font-weight: var(--fw-semi);
      color: var(--text-primary);
      margin-bottom: var(--sp-2);
    }

    .input-chariot {
      width: 100%;
      padding: var(--sp-3) var(--sp-4);
      border: 1.5px solid var(--border-color);
      border-radius: var(--radius-input);
      font-family: var(--font-body);
      font-size: var(--text-base);
      color: var(--text-primary);
      background: var(--bg-card);
      transition: border-color 0.2s, box-shadow 0.2s;
    }

    .input-chariot:focus {
      outline: none;
      border-color: var(--clr-gold-mid);
      box-shadow: 0 0 0 3px rgba(201,162,39,0.15);
    }

    .input-chariot::placeholder { color: var(--text-muted); }
    .input-chariot.is-invalid   { border-color: #e55368; }

    .password-input-wrap { position: relative; }

    .password-toggle-btn {
      position: absolute;
      right: var(--sp-3);
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      color: var(--text-muted);
      cursor: pointer;
      padding: var(--sp-1);
      font-size: var(--text-sm);
      transition: color 0.2s;
    }

    .password-toggle-btn:hover { color: var(--clr-gold-mid); }

    .form-options {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: var(--sp-5);
      font-size: var(--text-sm);
    }

    .remember-me {
      display: flex;
      align-items: center;
      gap: var(--sp-2);
      color: var(--text-secondary);
    }

    .remember-me input[type="checkbox"] {
      width: 16px;
      height: 16px;
      cursor: pointer;
      accent-color: var(--clr-gold-mid);
    }

    .forgot-password {
      color: var(--clr-gold-mid);
      text-decoration: none;
      font-weight: var(--fw-semi);
      transition: color 0.2s;
    }

    .forgot-password:hover { color: var(--clr-gold-bright); text-decoration: underline; }

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
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: var(--sp-2);
    }

    .btn-primary-c {
      background: linear-gradient(135deg, var(--clr-gold-dark), var(--clr-gold-bright));
      color: var(--clr-green-darkest);
      box-shadow: var(--elev-gold);
    }

    .btn-primary-c:hover  { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(201,162,39,0.5); color: var(--clr-green-darkest); }
    .btn-primary-c:active { transform: translateY(0); }
    .btn-primary-c:disabled { opacity: .65; cursor: not-allowed; transform: none; }

    /* loading spinner on button */
    .btn-spinner { display: none; }
    .btn-submitting .btn-spinner { display: inline-block; }
    .btn-submitting .btn-icon    { display: none; }

    .auth-footer {
      text-align: center;
      margin-top: var(--sp-5);
      padding-top: var(--sp-5);
      border-top: 1px solid var(--border-color);
    }

    .auth-footer-text { font-size: var(--text-sm); color: var(--text-secondary); }

    .auth-footer-link {
      color: var(--clr-gold-mid);
      text-decoration: none;
      font-weight: var(--fw-semi);
      transition: color 0.2s;
    }

    .auth-footer-link:hover { color: var(--clr-gold-bright); text-decoration: underline; }

    .back-home { text-align: center; margin-top: var(--sp-4); }

    .back-home a {
      display: inline-flex;
      align-items: center;
      gap: var(--sp-2);
      font-size: var(--text-sm);
      color: rgba(255,255,255,0.5);
      text-decoration: none;
      transition: color 0.2s;
    }

    .back-home a:hover { color: var(--clr-gold-mid); }

    @media (max-width: 480px) {
      .auth-container { max-width: 100%; }
      .auth-card { padding: var(--sp-5) var(--sp-4); }
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

    {{-- Login Card --}}
    <div class="auth-card">
      
      <div class="auth-card-header">
        <h1 class="auth-card-title">Welcome Back</h1>
        <p class="auth-card-desc">Ready to ride with purpose?</p>
      </div>

      {{-- ── Flash / Session Messages ──────────────────────── --}}
      @if(session('error'))
        <div class="auth-alert auth-alert-error" role="alert">
          <i class="fa-solid fa-circle-exclamation mt-1"></i>
          <span>{{ session('error') }}</span>
        </div>
      @endif

      @if(session('success'))
        <div class="auth-alert auth-alert-success" role="alert">
          <i class="fa-solid fa-circle-check mt-1"></i>
          <span>{{ session('success') }}</span>
        </div>
      @endif

      @if(session('warning') || session('info'))
        <div class="auth-alert auth-alert-info" role="alert">
          <i class="fa-solid fa-circle-info mt-1"></i>
          <span>{{ session('warning') ?? session('info') }}</span>
        </div>
      @endif

      {{-- General validation errors (e.g. from ValidationException) --}}
      @if($errors->any())
        <div class="auth-alert auth-alert-error" role="alert">
          <i class="fa-solid fa-triangle-exclamation mt-1"></i>
          <div>
            @foreach($errors->all() as $error)
              <div>{{ $error }}</div>
            @endforeach
          </div>
        </div>
      @endif

      {{-- ── LOGIN FORM ────────────────────────────────────── --}}
      <form
        id="loginForm"
        action="{{ route('login') }}"
        method="POST"
      >
        @csrf

        {{-- Phone --}}
        <div class="form-group-c">
          <label class="form-label-c" for="phone">
            <i class="fa-solid fa-phone" style="color:var(--clr-gold-mid)"></i>
            Phone Number
          </label>
          <input
            type="tel"
            id="phone"
            name="phone"
            class="input-chariot @error('phone') is-invalid @enderror"
            placeholder="e.g. 08012345678"
            value="{{ old('phone') }}"
            required
            autofocus
          >
          @error('phone')
            <div class="field-error"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div>
          @enderror
        </div>

        {{-- Password --}}
        <div class="form-group-c">
          <label class="form-label-c" for="password">
            <i class="fa-solid fa-lock" style="color:var(--clr-gold-mid)"></i>
            Password
          </label>
          <div class="password-input-wrap">
            <input
              type="password"
              id="password"
              name="password"
              class="input-chariot @error('password') is-invalid @enderror"
              placeholder="Enter your password"
              required
            >
            <button type="button" class="password-toggle-btn" id="togglePassword" aria-label="Toggle password visibility">
              <i class="fa-solid fa-eye"></i>
            </button>
          </div>
          @error('password')
            <div class="field-error"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div>
          @enderror
        </div>

        {{-- Remember me --}}
        <div class="form-options">
          <label class="remember-me">
            <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
            <span>Remember me</span>
          </label>
          <a href="#" class="forgot-password">Forgot password?</a>
        </div>

        {{-- Submit Button --}}
        <button type="submit" class="btn-chariot btn-primary-c" id="loginBtn">
          <i class="fa-solid fa-right-to-bracket btn-icon"></i>
          <span class="btn-spinner spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
          <span class="btn-label">Sign In</span>
        </button>

      </form>

      {{-- Auth Footer --}}
      <div class="auth-footer">
        <p class="auth-footer-text">
          Don't have an account? 
          <a href="{{ route('register') }}" class="auth-footer-link">Register</a>
        </p>
      </div>

    </div>

    {{-- Back to Home --}}
    <div class="back-home">
      <a href="{{ route('home') }}">
        <i class="fa-solid fa-arrow-left"></i>
        Back to Home
      </a>
    </div>

  </div>

  {{-- BOOTSTRAP JS --}}
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>

  {{-- PAGE SCRIPT --}}
  <script>
  document.addEventListener('DOMContentLoaded', function () {

    /* ── Password Toggle ── */
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput  = document.getElementById('password');

    togglePassword.addEventListener('click', function () {
      const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
      passwordInput.setAttribute('type', type);
      this.querySelector('i').classList.toggle('fa-eye');
      this.querySelector('i').classList.toggle('fa-eye-slash');
    });

    /* ── Loading state on submit ── */
    const form     = document.getElementById('loginForm');
    const loginBtn = document.getElementById('loginBtn');

    form.addEventListener('submit', function () {
      loginBtn.disabled = true;
      loginBtn.classList.add('btn-submitting');
      loginBtn.querySelector('.btn-label').textContent = 'Signing in…';
    });

  });
  </script>

</body>
</html>
