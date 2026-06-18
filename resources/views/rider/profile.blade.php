<!-- ===================================================================
  [E]  rider/profile.blade.php
  ====================================================================
  Cut everything between the [E] markers into:
      resources/views/rider/profile.blade.php
  Route    : GET  /rider/profile     (RiderController@profile)
           : PUT  /rider/profile     (RiderController@updateProfile)
  Middleware: auth, verified.member
  Data from controller:
      $user         — Auth user with driverProfile loaded
      $totalRides   — int   total completed ride requests
      $activeRides  — int   currently accepted ride requests
  ================================================================== -->
<!-- ======================== [E] START ============================= -->
@extends('layouts.app')

@section('title', 'Profile')

@push('styles')
<style>
  /* ── PROFILE HEADER ── */
  .profile-header {
    background: linear-gradient(
      135deg,
      var(--clr-green-deep) 0%,
      var(--clr-green-darkest) 100%
    );
    border-radius: var(--radius-xl);
    padding: var(--sp-8) var(--sp-5) var(--sp-6);
    text-align: center;
    position: relative;
    overflow: hidden;
    margin-bottom: var(--sp-5);
    border: 1px solid rgba(201,162,39,0.15);
  }

  /* Decorative rings */
  .profile-header::before {
    content: '';
    position: absolute;
    top: -60px; right: -60px;
    width: 200px; height: 200px;
    border-radius: 50%;
    border: 1px solid rgba(201,162,39,0.08);
    pointer-events: none;
  }

  .profile-header::after {
    content: '';
    position: absolute;
    bottom: -80px; left: -40px;
    width: 240px; height: 240px;
    border-radius: 50%;
    border: 1px solid rgba(201,162,39,0.05);
    pointer-events: none;
  }

  .profile-avatar-wrap {
    position: relative;
    display: inline-block;
    margin-bottom: var(--sp-4);
  }

  /* Verified ring on avatar */
  .profile-avatar-wrap.is-verified::after {
    content: '\f058'; /* fa-circle-check */
    font-family: 'Font Awesome 6 Free';
    font-weight: 900;
    position: absolute;
    bottom: 2px; right: 2px;
    width: 20px; height: 20px;
    background: var(--clr-success);
    color: white;
    border-radius: 50%;
    border: 2px solid var(--clr-green-deep);
    font-size: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    line-height: 16px;
    text-align: center;
  }

  .profile-name {
    font-size: var(--text-xl);
    font-weight: var(--fw-bold);
    color: white;
    margin-bottom: var(--sp-1);
    letter-spacing: var(--ls-tight);
  }

  .profile-phone {
    font-size: var(--text-sm);
    color: rgba(255,255,255,0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--sp-2);
    margin-bottom: var(--sp-4);
  }

  .profile-badges {
    display: flex;
    justify-content: center;
    gap: var(--sp-2);
    flex-wrap: wrap;
  }

  /* ── STATS ROW ── */
  .profile-stats {
    display: flex;
    border-top: 1px solid rgba(255,255,255,0.08);
    padding-top: var(--sp-4);
    margin-top: var(--sp-4);
    position: relative;
    z-index: 1;
  }

  .profile-stat {
    flex: 1;
    text-align: center;
    padding: 0 var(--sp-2);
    border-right: 1px solid rgba(255,255,255,0.08);
  }

  .profile-stat:last-child { border-right: none; }

  .profile-stat-val {
    font-family: var(--font-display);
    font-size: var(--text-2xl);
    font-weight: var(--fw-bold);
    color: var(--clr-gold-mid);
    line-height: 1;
    margin-bottom: 2px;
  }

  .profile-stat-lbl {
    font-size: 10px;
    font-weight: var(--fw-semi);
    color: rgba(255,255,255,0.4);
    text-transform: uppercase;
    letter-spacing: var(--ls-wider);
  }

  /* ── SECTION CARD ── */
  .profile-section-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-card);
    overflow: hidden;
    margin-bottom: var(--sp-4);
  }

  .profile-section-head {
    padding: var(--sp-4) var(--sp-5);
    border-bottom: 1px solid var(--border-color);
    font-size: var(--text-sm);
    font-weight: var(--fw-semi);
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: var(--ls-widest);
    display: flex;
    align-items: center;
    gap: var(--sp-2);
  }

  .profile-section-head i { color: var(--clr-gold-mid); }

  .profile-section-body { padding: var(--sp-5); }

  /* ── THEME PICKER ── */
  .theme-picker {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--sp-3);
  }

  .theme-option {
    display: flex;
    align-items: center;
    gap: var(--sp-3);
    padding: var(--sp-3) var(--sp-4);
    border: 2px solid var(--border-color);
    border-radius: var(--radius-md);
    background: var(--bg-card-alt);
    cursor: pointer;
    font-size: var(--text-sm);
    font-weight: var(--fw-medium);
    color: var(--text-secondary);
    transition: var(--transition-base);
    user-select: none;
  }

  .theme-option:hover {
    border-color: var(--clr-gold-mid);
    color: var(--text-primary);
  }

  .theme-option.is-active {
    border-color: var(--clr-green-dark);
    color: var(--clr-green-dark);
    background: var(--clr-green-tint);
  }

  [data-theme="dark"] .theme-option.is-active {
    border-color: var(--clr-gold-mid);
    color: var(--clr-gold-mid);
    background: rgba(201,162,39,0.08);
  }

  .theme-option-icon {
    width: 32px; height: 32px;
    border-radius: var(--radius-sm);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    flex-shrink: 0;
  }

  .theme-option-icon.light-icon { background: #f0f4f0; color: var(--clr-green-dark); }
  .theme-option-icon.dark-icon  { background: var(--clr-gray-800); color: var(--clr-gold-mid); }

  /* ── DANGER ZONE ── */
  .danger-zone {
    background: rgba(239,68,68,0.04);
    border: 1px solid rgba(239,68,68,0.15);
    border-radius: var(--radius-card);
    padding: var(--sp-5);
    margin-bottom: var(--sp-8);
  }

  .danger-zone-title {
    font-size: var(--text-sm);
    font-weight: var(--fw-semi);
    color: var(--clr-danger);
    margin-bottom: var(--sp-3);
    display: flex;
    align-items: center;
    gap: var(--sp-2);
  }

  .danger-zone-desc {
    font-size: var(--text-sm);
    color: var(--text-secondary);
    margin-bottom: var(--sp-4);
  }

  /* ── SAVE SUCCESS ANIMATION ── */
  @keyframes saveCheck {
    0%   { transform: scale(0); opacity: 0; }
    60%  { transform: scale(1.2); opacity: 1; }
    100% { transform: scale(1); }
  }

  .save-check { animation: saveCheck 0.4s cubic-bezier(0.34,1.56,0.64,1) both; }
</style>
@endpush

@section('content')
<div class="chariot-content-inner">

  <!-- ── PROFILE HEADER CARD ── -->
  <div class="profile-header">
    <div class="profile-avatar-wrap {{ $user->is_verified ? 'is-verified' : '' }}">
      <div class="chariot-avatar avatar-xxl">
        {{ strtoupper(substr($user->name, 0, 2)) }}
      </div>
    </div>

    <div class="profile-name">{{ $user->name }}</div>

    <div class="profile-phone">
      <i class="fa-solid fa-phone"></i>
      {{ $user->phone }}
    </div>

    <div class="profile-badges">
      @if($user->is_verified)
        <span class="verified-badge">
          <i class="fa-solid fa-circle-check"></i> Verified Member
        </span>
      @else
        <span class="badge-chariot pending">
          <i class="fa-solid fa-clock"></i> Pending Verification
        </span>
      @endif
      <span class="driver-badge">
        <i class="fa-solid fa-user"></i>
        {{ ucfirst($user->role) }}
      </span>
    </div>

    <div class="profile-stats">
      <div class="profile-stat">
        <div class="profile-stat-val">{{ $totalRides ?? 0 }}</div>
        <div class="profile-stat-lbl">Rides</div>
      </div>
      <div class="profile-stat">
        <div class="profile-stat-val">{{ $activeRides ?? 0 }}</div>
        <div class="profile-stat-lbl">Active</div>
      </div>
      <div class="profile-stat">
        <div class="profile-stat-val">
          <i class="fa-solid fa-shield-halved" style="font-size:var(--text-lg)"></i>
        </div>
        <div class="profile-stat-lbl">Trusted</div>
      </div>
    </div>
  </div>

  <!-- ── EDIT PROFILE FORM ── -->
  <div class="profile-section-card">
    <div class="profile-section-head">
      <i class="fa-solid fa-pen"></i>
      Edit Profile
    </div>
    <div class="profile-section-body">
      <form id="profileForm" data-loading>

        <div class="form-group-c">
          <label class="form-label-c" for="profileName">Full Name</label>
          <div class="input-icon-wrap">
            <i class="fa-solid fa-user input-icon-left"></i>
            <input
              type="text"
              id="profileName"
              class="input-chariot has-icon-left"
              value="{{ $user->name }}"
              placeholder="Your full name"
              data-validate="required"
              required
            >
          </div>
          <div class="field-error" style="display:none"></div>
        </div>

        <div class="form-group-c">
          <label class="form-label-c" for="profileEmail">
            Email <span class="label-optional">(optional)</span>
          </label>
          <div class="input-icon-wrap">
            <i class="fa-solid fa-envelope input-icon-left"></i>
            <input
              type="email"
              id="profileEmail"
              class="input-chariot has-icon-left"
              value="{{ $user->email }}"
              placeholder="your@email.com"
              data-validate="email"
            >
          </div>
          <div class="field-error" style="display:none"></div>
        </div>

        <div class="form-group-c">
          <label class="form-label-c" for="profilePhone">Phone Number</label>
          <div class="input-icon-wrap">
            <i class="fa-solid fa-phone input-icon-left"></i>
            <input
              type="tel"
              id="profilePhone"
              class="input-chariot has-icon-left"
              value="{{ $user->phone }}"
              placeholder="+234 800 000 0000"
              readonly
            >
          </div>
          <div class="field-hint">Phone number cannot be changed. Contact admin.</div>
        </div>

        <div class="form-group-c">
          <label class="form-label-c" for="profileMemberId">RCCG Member ID</label>
          <div class="input-icon-wrap">
            <i class="fa-solid fa-id-card input-icon-left"></i>
            <input
              type="text"
              id="profileMemberId"
              class="input-chariot has-icon-left"
              value="{{ $user->rccg_member_id }}"
              placeholder="Your RCCG member ID"
              {{ $user->is_verified ? 'readonly' : '' }}
            >
          </div>
          @if($user->is_verified)
            <div class="field-hint">Verified — cannot be changed.</div>
          @endif
        </div>

        <button type="submit" class="btn-chariot btn-primary-c btn-block" id="profileSaveBtn">
          <i class="fa-solid fa-floppy-disk"></i>
          Save Changes
        </button>
      </form>
    </div>
  </div>

  <!-- ── APPEARANCE ── -->
  <div class="profile-section-card">
    <div class="profile-section-head">
      <i class="fa-solid fa-palette"></i>
      Appearance
    </div>
    <div class="profile-section-body">
      <div class="form-label-c mb-3">Theme</div>
      {{-- Active class is set by JS on DOMContentLoaded via localStorage --}}
      <div class="theme-picker">
        <div class="theme-option is-active" data-theme="light">
          <div class="theme-option-icon light-icon">
            <i class="fa-solid fa-sun"></i>
          </div>
          Light
        </div>
        <div class="theme-option" data-theme="dark">
          <div class="theme-option-icon dark-icon">
            <i class="fa-solid fa-moon"></i>
          </div>
          Dark
        </div>
      </div>
    </div>
  </div>

  <!-- ── ACCOUNT ── -->
  <div class="profile-section-card">
    <div class="profile-section-head">
      <i class="fa-solid fa-circle-info"></i>
      Account Info
    </div>
    <div class="profile-section-body">
      <div class="d-flex flex-column gap-3">
        <div class="d-flex justify-content-between align-items-center">
          <span class="text-sm text-secondary-c">Member since</span>
          <span class="text-sm fw-medium">{{ $user->created_at->format('M Y') }}</span>
        </div>
        <div class="d-flex justify-content-between align-items-center">
          <span class="text-sm text-secondary-c">Account role</span>
          <span class="driver-badge">{{ ucfirst($user->role) }}</span>
        </div>
        <div class="d-flex justify-content-between align-items-center">
          <span class="text-sm text-secondary-c">Verification</span>
          @if($user->is_verified)
            <span class="verified-badge"><i class="fa-solid fa-circle-check"></i> Verified</span>
          @else
            <span class="badge-chariot pending">Pending</span>
          @endif
        </div>
      </div>
    </div>
  </div>

  <!-- ── DANGER ZONE ── -->
  <div class="danger-zone">
    <div class="danger-zone-title">
      <i class="fa-solid fa-triangle-exclamation"></i> Sign Out
    </div>
    <p class="danger-zone-desc">
      You will be logged out of your Chariot account on this device.
    </p>
    <form action="{{ route('logout') }}" method="POST">
      @csrf
      <button type="submit" class="btn-chariot btn-danger-c">
        <i class="fa-solid fa-right-from-bracket"></i>
        Logout
      </button>
    </form>
  </div>

</div><!-- /content-inner -->
@endsection

@push('scripts')
<script>
/* ================================================================
   rider/profile.blade.php — page script
   ================================================================ */

document.addEventListener('DOMContentLoaded', function () {

  /* ── 1. SAVE PROFILE — AJAX ── */
  const form    = document.getElementById('profileForm');
  const saveBtn = document.getElementById('profileSaveBtn');

  form?.addEventListener('submit', async function (e) {
    e.preventDefault();
    if (!Chariot.Forms.validateForm(form)) return;

    const body = {
      name:           document.getElementById('profileName').value.trim(),
      email:          document.getElementById('profileEmail').value.trim() || null,
      rccg_member_id: document.getElementById('profileMemberId')?.value.trim() || null,
    };

    Chariot.Buttons.setLoading(saveBtn, true);

    try {
      await Chariot.Util.put('/rider/profile', body);

      /* Update navbar avatar with new initials */
      const initials = body.name.slice(0, 2).toUpperCase();
      document.querySelectorAll('.chariot-avatar.navbar-avatar, .sidebar-user .chariot-avatar')
        .forEach(el => el.textContent = initials);

      /* Animate the save button to show success */
      saveBtn.classList.remove('is-loading');
      saveBtn.disabled  = false;
      saveBtn.innerHTML = '<i class="fa-solid fa-circle-check save-check"></i> Saved!';
      saveBtn.style.background = 'linear-gradient(135deg, var(--clr-success-dark), var(--clr-success))';

      setTimeout(() => {
        saveBtn.innerHTML = '<i class="fa-solid fa-floppy-disk"></i> Save Changes';
        saveBtn.style.background = '';
      }, 2500);

      Chariot.Toast.success('Profile updated successfully.', 'Saved');

    } catch (err) {
      Chariot.Toast.error(err.message || 'Could not save profile.');
    } finally {
      Chariot.Buttons.setLoading(saveBtn, false);
    }
  });


  /* ── 2. THEME PICKER — sync with Chariot.Theme ── */
  /* chariot.js Theme.init() handles this via .theme-option[data-theme].
     We just need to set the correct is-active class on page load. */
  const currentTheme = localStorage.getItem('chariot-theme') || 'light';

  document.querySelectorAll('.theme-option').forEach(opt => {
    opt.classList.toggle('is-active', opt.dataset.theme === currentTheme);

    opt.addEventListener('click', () => {
      document.querySelectorAll('.theme-option')
        .forEach(o => o.classList.remove('is-active'));
      opt.classList.add('is-active');
      Chariot.Theme.apply(opt.dataset.theme);
    });
  });

});
</script>
@endpush
<!-- ========================= [E] END ============================== -->
