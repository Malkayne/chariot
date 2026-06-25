{{--
=======================================================================
  register.blade.php  —  RCCG Camp Chariot  |  Registration Page
  Route  : GET /register  → RegisterController@showForm
           POST /register → RegisterController@store
  Guard  : guest (redirect if auth)
=======================================================================
--}}
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <meta name="description" content="Register for RCCG Camp Chariot — Smart mobility for the saints">
  <meta name="theme-color" content="#0D3B1F">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>Register — Chariot | RCCG Camp</title>

  @include('partials.seo-meta')

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
      background: white;
      padding: 6px;
      border-radius: 12px;
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

    .auth-card-desc { font-size: var(--text-sm); color: var(--text-secondary); }

    /* Flash / error alerts */
    .auth-alert {
      border-radius: 10px; font-size: 13px;
      padding: 10px 14px; margin-bottom: 18px;
      display: flex; align-items: flex-start; gap: 10px;
    }
    .auth-alert-error   { background: rgba(220,53,69,.12);  border: 1px solid rgba(220,53,69,.25);  color: #e55368; }
    .auth-alert-success { background: rgba(25,135,84,.12);  border: 1px solid rgba(25,135,84,.25);  color: #20c77b; }
    .auth-alert-info    { background: rgba(201,162,39,.12); border: 1px solid rgba(201,162,39,.25); color: var(--clr-gold-mid); }

    .field-error {
      font-size: 12px; color: #e55368;
      margin-top: 5px; display: flex; align-items: center; gap: 4px;
    }

    /* Role Toggle */
    .role-toggle-group {
      display: flex;
      background: var(--clr-gray-100);
      border-radius: var(--radius-pill);
      padding: 4px; gap: 4px;
      margin-bottom: var(--sp-5);
    }

    [data-theme="dark"] .role-toggle-group { background: var(--clr-gray-700); }

    .role-toggle-btn {
      flex: 1;
      padding: var(--sp-2) var(--sp-4);
      border: none;
      border-radius: var(--radius-pill);
      font-family: var(--font-body);
      font-size: var(--text-sm);
      font-weight: var(--fw-semi);
      cursor: pointer;
      background: transparent;
      color: var(--text-secondary);
      transition: all 0.25s;
      display: flex; align-items: center; justify-content: center; gap: var(--sp-2);
    }

    .role-toggle-btn:hover { color: var(--text-primary); }

    .role-toggle-btn.active {
      background: linear-gradient(135deg, var(--clr-green-dark), var(--clr-green-mid));
      color: white;
      box-shadow: var(--elev-2);
    }

    .form-group-c { margin-bottom: var(--sp-4); }

    .form-label-c {
      display: flex; align-items: center; gap: var(--sp-2);
      font-size: var(--text-sm); font-weight: var(--fw-semi);
      color: var(--text-primary); margin-bottom: var(--sp-2);
    }

    .label-optional {
      font-size: var(--text-xs);
      color: var(--text-muted);
      font-weight: var(--fw-normal);
      margin-left: auto;
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

    .select-chariot {
      width: 100%;
      padding: var(--sp-3) var(--sp-4);
      border: 1.5px solid var(--border-color);
      border-radius: var(--radius-input);
      font-family: var(--font-body);
      font-size: var(--text-base);
      color: var(--text-primary);
      background: var(--bg-card);
      cursor: pointer;
      transition: border-color 0.2s, box-shadow 0.2s;
    }

    .select-chariot:focus {
      outline: none;
      border-color: var(--clr-gold-mid);
      box-shadow: 0 0 0 3px rgba(201,162,39,0.15);
    }

    .select-chariot.is-invalid { border-color: #e55368; }

    .password-input-wrap { position: relative; }

    .password-toggle-btn {
      position: absolute; right: var(--sp-3); top: 50%;
      transform: translateY(-50%);
      background: none; border: none;
      color: var(--text-muted); cursor: pointer;
      padding: var(--sp-1); font-size: var(--text-sm);
      transition: color 0.2s;
    }

    .password-toggle-btn:hover { color: var(--clr-gold-mid); }

    /* Vehicle fields slide in/out */
    #vehicleFields {
      overflow: hidden;
      max-height: 0;
      opacity: 0;
      transition: max-height 0.4s ease, opacity 0.3s ease;
    }

    #vehicleFields.visible {
      max-height: 700px;
      opacity: 1;
    }

    .vehicle-section-divider {
      display: flex; align-items: center;
      gap: var(--sp-3); margin: var(--sp-5) 0;
      color: var(--text-muted); font-size: var(--text-xs);
      font-weight: var(--fw-semi); letter-spacing: var(--ls-wide);
      text-transform: uppercase;
    }

    .vehicle-section-divider::before,
    .vehicle-section-divider::after {
      content: ''; flex: 1; height: 1px; background: var(--border-color);
    }

    /* Seat counter */
    .seat-counter { display: flex; align-items: center; gap: var(--sp-2); }

    .seat-counter-btn {
      width: 36px; height: 36px;
      border: 1.5px solid var(--border-color);
      border-radius: var(--radius-md);
      background: var(--bg-card);
      color: var(--text-primary);
      cursor: pointer;
      display: flex; align-items: center; justify-content: center;
      transition: all 0.2s;
    }

    .seat-counter-btn:hover { border-color: var(--clr-gold-mid); color: var(--clr-gold-mid); }

    .seat-counter-value {
      width: 48px; text-align: center;
      font-family: var(--font-display);
      font-size: var(--text-lg);
      font-weight: var(--fw-bold);
      color: var(--text-primary);
    }

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

    .btn-primary-c:hover  { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(201,162,39,0.5); color: var(--clr-green-darkest); }
    .btn-primary-c:active { transform: translateY(0); }
    .btn-primary-c:disabled { opacity: .65; cursor: not-allowed; transform: none; }

    .btn-spinner { display: none; }
    .btn-submitting .btn-spinner { display: inline-block; }
    .btn-submitting .btn-icon    { display: none; }

    .auth-footer {
      text-align: center; margin-top: var(--sp-5);
      padding-top: var(--sp-5); border-top: 1px solid var(--border-color);
    }

    .auth-footer-text { font-size: var(--text-sm); color: var(--text-secondary); }

    .auth-footer-link {
      color: var(--clr-gold-mid); text-decoration: none;
      font-weight: var(--fw-semi); transition: color 0.2s;
    }

    .auth-footer-link:hover { color: var(--clr-gold-bright); text-decoration: underline; }

    .back-home { text-align: center; margin-top: var(--sp-4); }

    .back-home a {
      display: inline-flex; align-items: center; gap: var(--sp-2);
      font-size: var(--text-sm); color: rgba(255,255,255,0.5);
      text-decoration: none; transition: color 0.2s;
    }

    .back-home a:hover { color: var(--clr-gold-mid); }

    /* Client-side password match hint */
    .password-hint {
      font-size: 11px;
      margin-top: 4px;
    }

    .password-hint.match    { color: #20c77b; }
    .password-hint.no-match { color: #e55368; }

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

    {{-- Registration Card --}}
    <div class="auth-card">
      
      <div class="auth-card-header">
        <h1 class="auth-card-title">Create Account</h1>
        <p class="auth-card-desc">Join the camp mobility network</p>
      </div>

      {{-- ── Flash Messages ──────────────────────────────────── --}}
      @if(session('error'))
        <div class="auth-alert auth-alert-error" role="alert">
          <i class="fa-solid fa-circle-exclamation mt-1"></i>
          <span>{{ session('error') }}</span>
        </div>
      @endif

      {{-- Field-level errors banner --}}
      @if($errors->any())
        <div class="auth-alert auth-alert-error" role="alert">
          <i class="fa-solid fa-triangle-exclamation mt-1"></i>
          <div>
            <strong>Please correct the following:</strong>
            <ul class="mb-0 ps-3 mt-1">
              @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        </div>
      @endif

      {{-- ── REGISTER FORM ──────────────────────────────────── --}}
      <form
        id="registerForm"
        action="{{ route('register') }}"
        method="POST"
      >
        @csrf

        {{-- Role Toggle --}}
        <div class="role-toggle-group">
          <button type="button" class="role-toggle-btn {{ old('role', 'rider') === 'rider' ? 'active' : '' }}" data-role="rider" id="roleRider">
            <i class="fa-solid fa-user"></i>
            Rider
          </button>
          <button type="button" class="role-toggle-btn {{ old('role') === 'driver' ? 'active' : '' }}" data-role="driver" id="roleDriver">
            <i class="fa-solid fa-car"></i>
            Driver
          </button>
        </div>

        {{-- Hidden role input --}}
        <input type="hidden" name="role" id="roleInput" value="{{ old('role', 'rider') }}">

        {{-- Full Name --}}
        <div class="form-group-c">
          <label class="form-label-c" for="name">
            <i class="fa-solid fa-user" style="color:var(--clr-gold-mid)"></i>
            Full Name
          </label>
          <input
            type="text"
            id="name"
            name="name"
            class="input-chariot @error('name') is-invalid @enderror"
            placeholder="Enter your full name"
            value="{{ old('name') }}"
            required
          >
          @error('name')
            <div class="field-error"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div>
          @enderror
        </div>

        {{-- Phone Number --}}
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
            placeholder="+234 XXX XXX XXXX"
            value="{{ old('phone') }}"
            required
          >
          @error('phone')
            <div class="field-error"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div>
          @enderror
        </div>

        {{-- Email (Required) --}}
        <div class="form-group-c">
          <label class="form-label-c" for="email">
            <i class="fa-solid fa-envelope" style="color:var(--clr-gold-mid)"></i>
            Email
            <span class="text-danger">*</span>
          </label>
          <input
            type="email"
            id="email"
            name="email"
            class="input-chariot @error('email') is-invalid @enderror"
            placeholder="your@email.com"
            value="{{ old('email') }}"
            required
          >
          @error('email')
            <div class="field-error"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div>
          @enderror
        </div>

        {{-- RCCG Member ID --}}
        <div class="form-group-c">
          <label class="form-label-c" for="rccg_member_id">
            <i class="fa-solid fa-id-card" style="color:var(--clr-gold-mid)"></i>
            RCCG Member ID
            <span class="label-optional">(optional)</span>
          </label>
          <input
            type="text"
            id="rccg_member_id"
            name="rccg_member_id"
            class="input-chariot @error('rccg_member_id') is-invalid @enderror"
            placeholder="e.g. RCCG/2024/001"
            value="{{ old('rccg_member_id') }}"
          >
          @error('rccg_member_id')
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
              placeholder="Create a password (min 6 chars)"
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

        {{-- Confirm Password --}}
        <div class="form-group-c">
          <label class="form-label-c" for="password_confirmation">
            <i class="fa-solid fa-lock" style="color:var(--clr-gold-mid)"></i>
            Confirm Password
          </label>
          <div class="password-input-wrap">
            <input
              type="password"
              id="password_confirmation"
              name="password_confirmation"
              class="input-chariot"
              placeholder="Confirm your password"
              required
            >
            <button type="button" class="password-toggle-btn" id="togglePasswordConfirm" aria-label="Toggle password visibility">
              <i class="fa-solid fa-eye"></i>
            </button>
          </div>
          <div class="password-hint" id="passwordHint"></div>
        </div>

        {{-- Vehicle Fields (Driver Only) --}}
        <div id="vehicleFields" class="{{ old('role') === 'driver' ? 'visible' : '' }}">
          <div class="vehicle-section-divider">
            <span>Driver Information</span>
          </div>

          {{-- Vehicle Type --}}
          <div class="form-group-c">
            <label class="form-label-c" for="vehicle_type">
              <i class="fa-solid fa-car" style="color:var(--clr-gold-mid)"></i>
              Vehicle Type
            </label>
            <select class="select-chariot @error('vehicle_type') is-invalid @enderror" id="vehicle_type" name="vehicle_type">
              <option value="">Select vehicle type…</option>
              @foreach(['car' => 'Car', 'bus' => 'Bus', 'keke' => 'Keke Napep', 'van' => 'Van', 'suv' => 'SUV'] as $val => $label)
                <option value="{{ $val }}" {{ old('vehicle_type') === $val ? 'selected' : '' }}>{{ $label }}</option>
              @endforeach
            </select>
            @error('vehicle_type')
              <div class="field-error"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div>
            @enderror
          </div>

          {{-- Vehicle Model --}}
          <div class="form-group-c">
            <label class="form-label-c" for="vehicle_model">
              <i class="fa-solid fa-car-side" style="color:var(--clr-gold-mid)"></i>
              Vehicle Model
            </label>
            <input
              type="text"
              id="vehicle_model"
              name="vehicle_model"
              class="input-chariot @error('vehicle_model') is-invalid @enderror"
              placeholder="e.g. Toyota Camry"
              value="{{ old('vehicle_model') }}"
            >
            @error('vehicle_model')
              <div class="field-error"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div>
            @enderror
          </div>

          {{-- Plate Number --}}
          <div class="form-group-c">
            <label class="form-label-c" for="plate_number">
              <i class="fa-solid fa-hashtag" style="color:var(--clr-gold-mid)"></i>
              Plate Number
            </label>
            <input
              type="text"
              id="plate_number"
              name="plate_number"
              class="input-chariot @error('plate_number') is-invalid @enderror"
              placeholder="e.g. ABC 123 XY"
              value="{{ old('plate_number') }}"
              style="text-transform:uppercase"
            >
            @error('plate_number')
              <div class="field-error"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div>
            @enderror
          </div>

          {{-- Vehicle Color --}}
          <div class="form-group-c">
            <label class="form-label-c" for="vehicle_color">
              <i class="fa-solid fa-palette" style="color:var(--clr-gold-mid)"></i>
              Vehicle Color
            </label>
            <input
              type="text"
              id="vehicle_color"
              name="vehicle_color"
              class="input-chariot @error('vehicle_color') is-invalid @enderror"
              placeholder="e.g. White"
              value="{{ old('vehicle_color') }}"
            >
            @error('vehicle_color')
              <div class="field-error"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div>
            @enderror
          </div>

          {{-- Total Seats --}}
          <div class="form-group-c">
            <label class="form-label-c">
              <i class="fa-solid fa-chair" style="color:var(--clr-gold-mid)"></i>
              Total Seats
            </label>
            <div class="seat-counter" data-min="1" data-max="15">
              <button type="button" class="seat-counter-btn seat-counter-dec" aria-label="Decrease seats">
                <i class="fa-solid fa-minus" style="font-size:.7rem"></i>
              </button>
              <div class="seat-counter-value" id="seatCountDisplay">{{ old('total_seats', 4) }}</div>
              <button type="button" class="seat-counter-btn seat-counter-inc" aria-label="Increase seats">
                <i class="fa-solid fa-plus" style="font-size:.7rem"></i>
              </button>
              <input type="hidden" id="total_seats" name="total_seats" value="{{ old('total_seats', 4) }}">
            </div>
            @error('total_seats')
              <div class="field-error"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div>
            @enderror
          </div>
        </div>

        {{-- Submit Button --}}
        <button type="submit" class="btn-chariot btn-primary-c" id="registerBtn">
          <i class="fa-solid fa-user-plus btn-icon"></i>
          <span class="btn-spinner spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
          <span class="btn-label">Create Account</span>
        </button>

      </form>

      {{-- Auth Footer --}}
      <div class="auth-footer">
        <p class="auth-footer-text">
          Already have an account? 
          <a href="{{ route('login') }}" class="auth-footer-link">Sign In</a>
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

  {{-- PAGE SCRIPT - UX only, no form blocking --}}
  <script>
  document.addEventListener('DOMContentLoaded', function () {

    /* ── Role Toggle ── */
    const roleRider    = document.getElementById('roleRider');
    const roleDriver   = document.getElementById('roleDriver');
    const roleInput    = document.getElementById('roleInput');
    const vehicleFields = document.getElementById('vehicleFields');

    roleRider.addEventListener('click', function () {
      roleRider.classList.add('active');
      roleDriver.classList.remove('active');
      roleInput.value = 'rider';
      vehicleFields.classList.remove('visible');
    });

    roleDriver.addEventListener('click', function () {
      roleDriver.classList.add('active');
      roleRider.classList.remove('active');
      roleInput.value = 'driver';
      vehicleFields.classList.add('visible');
    });

    /* ── Password Toggles ── */
    function makeToggle(btnId, inputId) {
      const btn   = document.getElementById(btnId);
      const input = document.getElementById(inputId);
      if (!btn || !input) return;
      btn.addEventListener('click', function () {
        const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
        input.setAttribute('type', type);
        this.querySelector('i').classList.toggle('fa-eye');
        this.querySelector('i').classList.toggle('fa-eye-slash');
      });
    }

    makeToggle('togglePassword', 'password');
    makeToggle('togglePasswordConfirm', 'password_confirmation');

    /* ── Live password-match hint ── */
    const passwordInput  = document.getElementById('password');
    const confirmInput   = document.getElementById('password_confirmation');
    const passwordHint   = document.getElementById('passwordHint');

    function checkMatch() {
      const pw = passwordInput.value;
      const cf = confirmInput.value;
      if (!cf) { passwordHint.textContent = ''; return; }
      if (pw === cf) {
        passwordHint.className = 'password-hint match';
        passwordHint.innerHTML = '<i class="fa-solid fa-check me-1"></i>Passwords match';
      } else {
        passwordHint.className = 'password-hint no-match';
        passwordHint.innerHTML = '<i class="fa-solid fa-xmark me-1"></i>Passwords do not match';
      }
    }

    confirmInput.addEventListener('input', checkMatch);
    passwordInput.addEventListener('input', checkMatch);

    /* ── Seat Counter ── */
    const seatCounter = document.querySelector('.seat-counter');
    if (seatCounter) {
      const seatDec     = seatCounter.querySelector('.seat-counter-dec');
      const seatInc     = seatCounter.querySelector('.seat-counter-inc');
      const seatDisplay = document.getElementById('seatCountDisplay');
      const seatInput   = document.getElementById('total_seats');
      const minSeats    = parseInt(seatCounter.dataset.min) || 1;
      const maxSeats    = parseInt(seatCounter.dataset.max) || 15;

      seatDec.addEventListener('click', function () {
        let current = parseInt(seatInput.value);
        if (current > minSeats) {
          seatInput.value = current - 1;
          seatDisplay.textContent = current - 1;
        }
      });

      seatInc.addEventListener('click', function () {
        let current = parseInt(seatInput.value);
        if (current < maxSeats) {
          seatInput.value = current + 1;
          seatDisplay.textContent = current + 1;
        }
      });
    }

    /* ── Loading state on submit (client-side password check first) ── */
    const form        = document.getElementById('registerForm');
    const registerBtn = document.getElementById('registerBtn');

    form.addEventListener('submit', function (e) {
      const pw = passwordInput.value;
      const cf = confirmInput.value;

      // Client-side guard: mismatched passwords (server also validates)
      if (pw !== cf) {
        e.preventDefault();
        passwordHint.className = 'password-hint no-match';
        passwordHint.innerHTML = '<i class="fa-solid fa-xmark me-1"></i>Passwords do not match';
        confirmInput.focus();
        return;
      }

      // Show loading state
      registerBtn.disabled = true;
      registerBtn.classList.add('btn-submitting');
      registerBtn.querySelector('.btn-label').textContent = 'Creating account…';
    });

  });
  </script>

</body>
</html>
