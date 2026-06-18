<!-- ===================================================================
  [A]  driver/dashboard.blade.php
  ====================================================================
  Cut everything between the [A] markers into:
      resources/views/driver/dashboard.blade.php
  Route    : GET /driver/dashboard    (DriverDashboardController@index)
  Middleware: auth, verified.member, is.driver
  Data from controller:
      $user          — Auth user with driverProfile eager-loaded
      $profile       — $user->driverProfile
      $zones         — Collection<Zone>   all active camp zones
      $activeRide    — Ride|null          current active ride if any
      $todayTrips    — int                completed rides today
      $todayRiders   — int                total riders served today
      $pendingCount  — int                pending requests on active ride
  ================================================================== -->
<!-- ======================== [A] START ============================= -->
@extends('layouts.app')

@section('title', 'Driver Dashboard')

@push('styles')
<style>
  /* ════════════════════════════════════════════
     AVAILABILITY CARD
     ════════════════════════════════════════════ */
  .avail-card {
    background: linear-gradient(
      140deg,
      var(--clr-green-deep) 0%,
      var(--clr-green-darkest) 100%
    );
    border-radius: var(--radius-xl);
    padding: var(--sp-6) var(--sp-5);
    margin-bottom: var(--sp-4);
    position: relative;
    overflow: hidden;
    border: 1px solid rgba(201,162,39,0.18);
  }

  /* Decorative rings */
  .avail-card::before {
    content: '';
    position: absolute;
    top: -70px; right: -70px;
    width: 220px; height: 220px;
    border-radius: 50%;
    border: 1px solid rgba(201,162,39,0.07);
    pointer-events: none;
  }

  .avail-card::after {
    content: '';
    position: absolute;
    bottom: -90px; left: -40px;
    width: 260px; height: 260px;
    border-radius: 50%;
    border: 1px solid rgba(201,162,39,0.05);
    pointer-events: none;
  }

  .avail-card-inner { position: relative; z-index: 1; }

  /* Status line */
  .avail-status-line {
    display: flex;
    align-items: center;
    gap: var(--sp-2);
    margin-bottom: var(--sp-5);
  }

  .avail-status-label {
    font-size: var(--text-xs);
    font-weight: var(--fw-semi);
    color: rgba(255,255,255,0.5);
    letter-spacing: var(--ls-wider);
    text-transform: uppercase;
  }

  .avail-status-value {
    font-size: var(--text-sm);
    font-weight: var(--fw-bold);
    color: white;
    letter-spacing: var(--ls-wide);
  }

  /* Toggle button */
  .avail-toggle-btn {
    width: 100%;
    padding: var(--sp-4) var(--sp-5);
    border: none;
    border-radius: var(--radius-lg);
    font-family: var(--font-display);
    font-size: var(--text-md);
    font-weight: var(--fw-bold);
    letter-spacing: var(--ls-wider);
    cursor: pointer;
    transition: all 0.32s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--sp-3);
    margin-bottom: var(--sp-4);
  }

  .avail-toggle-btn.go-available {
    background: linear-gradient(135deg, var(--clr-gold-dark), var(--clr-gold-bright));
    color: var(--clr-green-darkest);
    box-shadow: var(--shadow-gold);
  }

  .avail-toggle-btn.go-available:hover {
    box-shadow: var(--shadow-gold-lg);
    filter: brightness(1.05);
    transform: translateY(-1px);
  }

  .avail-toggle-btn.go-offline {
    background: rgba(255,255,255,0.07);
    color: rgba(255,255,255,0.65);
    border: 1.5px solid rgba(255,255,255,0.12);
  }

  .avail-toggle-btn.go-offline:hover {
    background: rgba(239,68,68,0.1);
    border-color: rgba(239,68,68,0.3);
    color: #FCA5A5;
  }

  .avail-toggle-btn:active { transform: scale(0.98); }

  /* Ripple on toggle */
  .avail-toggle-btn::before {
    content: '';
    position: absolute;
    inset: 0;
    background: rgba(255,255,255,0.15);
    transform: scale(0);
    border-radius: inherit;
    opacity: 0;
    transition: transform 0.5s ease, opacity 0.5s ease;
  }

  .avail-toggle-btn:active::before {
    transform: scale(1);
    opacity: 0;
  }

  /* Vehicle info strip */
  .avail-vehicle-strip {
    display: flex;
    align-items: center;
    gap: var(--sp-3);
    padding-top: var(--sp-4);
    border-top: 1px solid rgba(255,255,255,0.07);
  }

  .avail-vehicle-icon {
    width: 36px; height: 36px;
    border-radius: var(--radius-md);
    background: rgba(201,162,39,0.12);
    border: 1px solid rgba(201,162,39,0.2);
    display: flex; align-items: center; justify-content: center;
    color: var(--clr-gold-mid);
    font-size: 1rem;
    flex-shrink: 0;
  }

  .avail-vehicle-name {
    font-size: var(--text-sm);
    font-weight: var(--fw-semi);
    color: rgba(255,255,255,0.88);
    line-height: 1.2;
  }

  .avail-vehicle-plate {
    font-size: var(--text-xs);
    font-family: var(--font-mono);
    color: rgba(255,255,255,0.4);
    margin-top: 2px;
  }

  /* Seat count badge on vehicle strip */
  .avail-seat-badge {
    margin-left: auto;
    display: inline-flex;
    align-items: center;
    gap: var(--sp-1);
    font-size: var(--text-xs);
    font-weight: var(--fw-semi);
    color: var(--clr-gold-mid);
    background: rgba(201,162,39,0.1);
    border: 1px solid rgba(201,162,39,0.2);
    border-radius: var(--radius-pill);
    padding: 2px var(--sp-2);
    flex-shrink: 0;
  }

  /* ════════════════════════════════════════════
     CREATE RIDE FORM CARD
     ════════════════════════════════════════════ */
  .create-ride-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-card);
    overflow: hidden;
    margin-bottom: var(--sp-4);
    animation: fadeUp 0.3s ease both;
  }

  .create-ride-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: var(--sp-4) var(--sp-5);
    border-bottom: 1px solid var(--border-color);
    background: var(--clr-gold-tint);
  }

  [data-theme="dark"] .create-ride-header {
    background: rgba(201,162,39,0.06);
  }

  .create-ride-header-title {
    display: flex;
    align-items: center;
    gap: var(--sp-2);
    font-size: var(--text-base);
    font-weight: var(--fw-bold);
    color: var(--clr-gold-dark);
  }

  [data-theme="dark"] .create-ride-header-title { color: var(--clr-gold-mid); }

  .create-ride-body { padding: var(--sp-5); }

  /* Zone row — From → To */
  .ride-zone-row {
    display: grid;
    grid-template-columns: 1fr auto 1fr;
    align-items: flex-end;
    gap: var(--sp-2);
    margin-bottom: var(--sp-4);
  }

  .ride-zone-sep {
    padding-bottom: 10px;
    color: var(--clr-gold-mid);
    font-size: 0.85rem;
    flex-shrink: 0;
    text-align: center;
  }

  /* Seats + time row */
  .ride-meta-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--sp-3);
    margin-bottom: var(--sp-4);
  }

  /* ════════════════════════════════════════════
     ACTIVE RIDE BANNER
     ════════════════════════════════════════════ */
  .active-ride-card {
    background: linear-gradient(
      135deg,
      var(--clr-success-dark) 0%,
      #0f7a35 100%
    );
    border-radius: var(--radius-xl);
    padding: var(--sp-5);
    margin-bottom: var(--sp-4);
    position: relative;
    overflow: hidden;
    border: 1px solid rgba(34,197,94,0.25);
    animation: fadeUp 0.35s ease both;
  }

  .active-ride-card::before {
    content: '';
    position: absolute;
    top: -40px; right: -40px;
    width: 160px; height: 160px;
    border-radius: 50%;
    border: 1px solid rgba(255,255,255,0.08);
    pointer-events: none;
  }

  .active-ride-label {
    display: inline-flex;
    align-items: center;
    gap: var(--sp-2);
    font-size: var(--text-xs);
    font-weight: var(--fw-bold);
    color: rgba(255,255,255,0.7);
    letter-spacing: var(--ls-widest);
    text-transform: uppercase;
    margin-bottom: var(--sp-3);
  }

  .active-ride-label::before {
    content: '';
    width: 7px; height: 7px;
    border-radius: 50%;
    background: var(--clr-gold-bright);
    animation: dotPulse 2s infinite;
  }

  .active-ride-route {
    display: flex;
    align-items: center;
    gap: var(--sp-3);
    font-size: var(--text-base);
    font-weight: var(--fw-semi);
    color: white;
    margin-bottom: var(--sp-4);
    flex-wrap: wrap;
  }

  .active-ride-route .rpt {
    display: flex;
    align-items: center;
    gap: var(--sp-2);
    background: rgba(255,255,255,0.1);
    border-radius: var(--radius-md);
    padding: var(--sp-2) var(--sp-3);
    font-size: var(--text-sm);
    flex-shrink: 0;
  }

  .active-ride-route .rarrow {
    color: rgba(255,255,255,0.45);
    font-size: var(--text-sm);
  }

  .active-ride-stats {
    display: flex;
    gap: var(--sp-4);
    margin-bottom: var(--sp-4);
    flex-wrap: wrap;
  }

  .active-stat {
    display: flex;
    align-items: center;
    gap: var(--sp-2);
    font-size: var(--text-sm);
    color: rgba(255,255,255,0.75);
  }

  .active-stat i { color: rgba(255,255,255,0.45); font-size: 0.85rem; }

  .active-stat strong { color: white; font-weight: var(--fw-semi); }

  .active-ride-actions { display: flex; gap: var(--sp-3); position: relative; z-index: 1; }

  .btn-view-reqs {
    flex: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: var(--sp-2);
    padding: var(--sp-3) var(--sp-4);
    background: rgba(255,255,255,0.14);
    border: 1.5px solid rgba(255,255,255,0.22);
    border-radius: var(--radius-md);
    color: white;
    font-size: var(--text-sm);
    font-weight: var(--fw-semi);
    text-decoration: none;
    transition: var(--transition-fast);
  }

  .btn-view-reqs:hover {
    background: rgba(255,255,255,0.22);
    color: white;
  }

  .btn-cancel-active {
    display: inline-flex;
    align-items: center;
    gap: var(--sp-2);
    padding: var(--sp-3) var(--sp-4);
    background: rgba(239,68,68,0.12);
    border: 1.5px solid rgba(239,68,68,0.28);
    border-radius: var(--radius-md);
    color: #FCA5A5;
    font-size: var(--text-sm);
    font-weight: var(--fw-semi);
    cursor: pointer;
    transition: var(--transition-fast);
    white-space: nowrap;
  }

  .btn-cancel-active:hover {
    background: rgba(239,68,68,0.22);
    color: white;
  }

  /* Pending requests count badge on banner */
  .pending-badge-banner {
    display: inline-flex;
    align-items: center;
    gap: var(--sp-2);
    background: rgba(201,162,39,0.18);
    border: 1px solid rgba(201,162,39,0.35);
    border-radius: var(--radius-pill);
    padding: 3px var(--sp-3);
    font-size: var(--text-xs);
    font-weight: var(--fw-bold);
    color: var(--clr-gold-bright);
    animation: pulseBadge 2.5s infinite;
  }

  /* ════════════════════════════════════════════
     TODAY'S STATS STRIP
     ════════════════════════════════════════════ */
  .today-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1px;
    background: var(--border-color);
    border-radius: var(--radius-card);
    overflow: hidden;
    border: 1px solid var(--border-color);
    margin-bottom: var(--sp-4);
  }

  .today-stat-item {
    background: var(--bg-card);
    padding: var(--sp-4) var(--sp-3);
    text-align: center;
  }

  .today-stat-val {
    font-family: var(--font-display);
    font-size: var(--text-2xl);
    font-weight: var(--fw-black);
    color: var(--text-primary);
    line-height: 1;
    margin-bottom: 2px;
  }

  .today-stat-lbl {
    font-size: 10px;
    font-weight: var(--fw-semi);
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: var(--ls-wider);
  }

  /* ════════════════════════════════════════════
     QUICK TIPS CARD (shown only when offline)
     ════════════════════════════════════════════ */
  .tips-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-card);
    padding: var(--sp-5);
    margin-bottom: var(--sp-4);
  }

  .tips-title {
    font-size: var(--text-sm);
    font-weight: var(--fw-semi);
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: var(--ls-widest);
    margin-bottom: var(--sp-4);
    display: flex;
    align-items: center;
    gap: var(--sp-2);
  }

  .tips-title i { color: var(--clr-gold-mid); }

  .tip-row {
    display: flex;
    align-items: flex-start;
    gap: var(--sp-3);
    padding: var(--sp-3) 0;
    border-bottom: 1px solid var(--border-color);
    font-size: var(--text-sm);
    color: var(--text-secondary);
    line-height: var(--lh-snug);
  }

  .tip-row:last-child { border-bottom: none; padding-bottom: 0; }

  .tip-icon {
    width: 28px; height: 28px;
    border-radius: var(--radius-sm);
    background: var(--clr-gold-tint);
    display: flex; align-items: center; justify-content: center;
    color: var(--clr-gold-dark);
    font-size: 0.8rem;
    flex-shrink: 0;
  }

  [data-theme="dark"] .tip-icon { background: rgba(201,162,39,0.1); color: var(--clr-gold-mid); }
</style>
@endpush

@section('content')
<div class="chariot-content-inner">

  <!-- ── DRIVER STATUS META (read by chariot.js Driver.init()) ── -->
  <div id="driverStatus"
       data-available="{{ $profile->is_available ? 'true' : 'false' }}"
       data-status="{{ $profile->status }}"
       data-active-ride="{{ $activeRide?->id ?? '' }}"
       style="display:none">
  </div>

  <!-- ── PAGE HEADING ── -->
  <div class="d-flex align-items-center justify-content-between mb-4">
    <div>
      <div class="page-title">Dashboard</div>
      <p class="page-subtitle mb-0">Manage your availability and rides.</p>
    </div>
    <!-- WS live indicator -->
    <div style="display:flex;align-items:center;gap:6px;font-size:var(--text-xs);color:var(--text-muted)">
      <div class="ws-status-dot" id="wsStatusDot" style="width:8px;height:8px;border-radius:50%;background:var(--clr-offline);flex-shrink:0"></div>
      <span id="wsStatusText">Connecting…</span>
    </div>
  </div>

  <!-- ════════════════════════════════════════
       AVAILABILITY CARD
       ════════════════════════════════════════ -->
  <div class="avail-card">
    <div class="avail-card-inner">

      <div class="avail-status-line">
        <span class="status-dot {{ $profile->status === 'available' || $profile->status === 'on_trip' ? 'online' : 'offline' }}"
              id="driverStatusDot"></span>
        <span class="avail-status-label">You are:</span>
        <span class="avail-status-value" id="driverStatusText">
          @if($profile->status === 'available') AVAILABLE
          @elseif($profile->status === 'on_trip') ON A TRIP
          @else OFFLINE
          @endif
        </span>
      </div>

      @if($profile->status === 'available' || $profile->status === 'on_trip')
        <button
          class="avail-toggle-btn go-offline"
          id="availabilityToggle"
          aria-label="Go offline"
        >
          <i class="fa-solid fa-toggle-on"></i>
          Go Offline
        </button>
      @else
        <button
          class="avail-toggle-btn go-available"
          id="availabilityToggle"
          aria-label="Go available"
        >
          <i class="fa-solid fa-toggle-off"></i>
          Go Available
        </button>
      @endif

      <div class="avail-vehicle-strip">
        <div class="avail-vehicle-icon">
          <i class="fa-solid fa-car"></i>
        </div>
        <div>
          <div class="avail-vehicle-name">
            {{ $profile->vehicle_color }} {{ $profile->vehicle_model }}
          </div>
          <div class="avail-vehicle-plate">{{ $profile->plate_number }}</div>
        </div>
        <div class="avail-seat-badge">
          <i class="fa-solid fa-chair"></i>
          {{ $profile->total_seats }} seats
        </div>
      </div>

    </div>
  </div>


  <!-- ════════════════════════════════════════
       ACTIVE RIDE BANNER
       (shown when driver has a live ride posted)
       ════════════════════════════════════════ -->
  <div class="active-ride-card" id="activeRideBanner"
       style="{{ $activeRide ? '' : 'display:none' }}">

    <div class="active-ride-label">Active Ride</div>

    <div class="active-ride-route">
      <div class="rpt">
        <i class="fa-solid fa-location-dot" style="color:var(--clr-gold-bright)"></i>
        <span class="active-ride-from">{{ $activeRide?->fromZone?->name ?? '—' }}</span>
      </div>
      <i class="fa-solid fa-arrow-right rarrow"></i>
      <div class="rpt">
        <i class="fa-solid fa-flag-checkered"></i>
        <span class="active-ride-to">{{ $activeRide?->toZone?->name ?? '—' }}</span>
      </div>
    </div>

    <div class="active-ride-stats">
      <div class="active-stat">
        <i class="fa-solid fa-chair"></i>
        <strong><span id="availableSeatsCount">{{ $activeRide?->available_seats ?? 0 }}</span></strong>
        seats left
      </div>
      <div class="active-stat">
        <i class="fa-solid fa-hand"></i>
        <strong>{{ $pendingCount ?? 0 }}</strong> pending
        @if(($pendingCount ?? 0) > 0)
          <span class="pending-badge-banner">
            <i class="fa-solid fa-bell"></i>
            {{ $pendingCount }} waiting
          </span>
        @endif
      </div>
      @if($activeRide?->departing_at)
        <div class="active-stat">
          <i class="fa-solid fa-clock"></i>
          {{ \Carbon\Carbon::parse($activeRide->departing_at)->format('g:i A') }}
        </div>
      @endif
    </div>

    <div class="active-ride-actions">
      <a href="{{ route('driver.requests') }}" class="btn-view-reqs">
        <i class="fa-solid fa-hand"></i>
        View Requests
      </a>
      <button class="btn-cancel-active" id="cancelRideBtn"
              onclick="Chariot.Driver.cancelRide(this)">
        <i class="fa-solid fa-xmark"></i>
        Cancel Ride
      </button>
    </div>

  </div>


  <!-- ════════════════════════════════════════
       CREATE RIDE FORM
       (shown when available + no active ride)
       ════════════════════════════════════════ -->
  <div class="create-ride-card" id="createRideForm"
       style="{{ ($profile->is_available && !$activeRide) ? '' : 'display:none' }}">

    <div class="create-ride-header">
      <div class="create-ride-header-title">
        <i class="fa-solid fa-route"></i>
        Post a Ride
      </div>
      <span class="text-xs text-muted-c">Fill in your route and seats</span>
    </div>

    <div class="create-ride-body">
      <form id="createRideFormEl" novalidate>
        @csrf

        <!-- Zone row -->
        <div class="ride-zone-row">
          <div class="form-group-c mb-0">
            <label class="form-label-c" for="fromZone">
              <i class="fa-solid fa-location-dot" style="color:var(--clr-gold-mid)"></i>
              From Zone
            </label>
            <select class="select-chariot" id="fromZone" required data-driver-required>
              <option value="">Select zone…</option>
              @foreach($zones as $zone)
                <option value="{{ $zone->id }}">{{ $zone->name }}</option>
              @endforeach
            </select>
          </div>

          <div class="ride-zone-sep">
            <i class="fa-solid fa-arrow-right"></i>
          </div>

          <div class="form-group-c mb-0">
            <label class="form-label-c" for="toZone">
              <i class="fa-solid fa-flag-checkered" style="color:var(--clr-green-mid)"></i>
              To Zone
            </label>
            <select class="select-chariot" id="toZone" required data-driver-required>
              <option value="">Select zone…</option>
              @foreach($zones as $zone)
                <option value="{{ $zone->id }}">{{ $zone->name }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <!-- Seats + Time -->
        <div class="ride-meta-row">
          <div class="form-group-c mb-0">
            <label class="form-label-c">
              <i class="fa-solid fa-chair" style="color:var(--clr-gold-mid)"></i>
              Seats Available
            </label>
            <div class="seat-counter" data-min="1" data-max="{{ $profile->total_seats }}">
              <button type="button" class="seat-counter-btn seat-counter-dec" aria-label="Decrease seats">
                <i class="fa-solid fa-minus" style="font-size:.7rem"></i>
              </button>
              <div class="seat-counter-value" id="seatCountDisplay">
                {{ min(4, $profile->total_seats) }}
              </div>
              <button type="button" class="seat-counter-btn seat-counter-inc" aria-label="Increase seats">
                <i class="fa-solid fa-plus" style="font-size:.7rem"></i>
              </button>
              <input type="hidden" id="rideSeats" value="{{ min(4, $profile->total_seats) }}">
            </div>
          </div>

          <div class="form-group-c mb-0">
            <label class="form-label-c" for="departingAt">
              <i class="fa-solid fa-clock" style="color:var(--clr-gold-mid)"></i>
              Departing At
              <span class="label-optional">(optional)</span>
            </label>
            <input
              type="time"
              id="departingAt"
              class="input-chariot"
              placeholder="e.g. 14:30"
            >
          </div>
        </div>

        <!-- Notes -->
        <div class="form-group-c">
          <label class="form-label-c" for="rideNotes">
            <i class="fa-solid fa-note-sticky" style="color:var(--clr-gold-mid)"></i>
            Note <span class="label-optional">(optional)</span>
          </label>
          <input
            type="text"
            id="rideNotes"
            class="input-chariot"
            placeholder="e.g. Starting from Holy Ghost Zone exit…"
            maxlength="150"
          >
        </div>

        <button type="submit" class="btn-chariot btn-primary-c btn-block">
          <i class="fa-solid fa-paper-plane"></i>
          Post My Ride
        </button>

      </form>
    </div>
  </div>


  <!-- ════════════════════════════════════════
       TODAY'S STATS STRIP
       ════════════════════════════════════════ -->
  <div class="section-label">Today's Summary</div>
  <div class="today-stats">
    <div class="today-stat-item">
      <div class="today-stat-val" style="color:var(--clr-green-mid)">{{ $todayTrips ?? 0 }}</div>
      <div class="today-stat-lbl">Trips</div>
    </div>
    <div class="today-stat-item">
      <div class="today-stat-val" style="color:var(--clr-gold-mid)">{{ $todayRiders ?? 0 }}</div>
      <div class="today-stat-lbl">Riders</div>
    </div>
    <div class="today-stat-item">
      <div class="today-stat-val" style="color:var(--clr-success)">
        <i class="fa-solid fa-shield-halved" style="font-size:1.6rem"></i>
      </div>
      <div class="today-stat-lbl">Trusted</div>
    </div>
  </div>


  <!-- ════════════════════════════════════════
       QUICK TIPS (shown only when offline)
       ════════════════════════════════════════ -->
  @if($profile->status === 'offline')
  <div class="tips-card">
    <div class="tips-title">
      <i class="fa-solid fa-lightbulb"></i>
      Driver Tips
    </div>
    <div class="tip-row">
      <div class="tip-icon"><i class="fa-solid fa-toggle-on"></i></div>
      <span>Toggle <strong>Go Available</strong> when you're ready to accept ride requests.</span>
    </div>
    <div class="tip-row">
      <div class="tip-icon"><i class="fa-solid fa-route"></i></div>
      <span>Post your route so riders heading the same way can find and request to join you.</span>
    </div>
    <div class="tip-row">
      <div class="tip-icon"><i class="fa-solid fa-bell"></i></div>
      <span>You'll receive an alert here and on the <a href="{{ route('driver.requests') }}" style="color:var(--clr-gold-mid)">Requests</a> page when someone wants to join.</span>
    </div>
    <div class="tip-row">
      <div class="tip-icon"><i class="fa-solid fa-shield-halved"></i></div>
      <span>Only verified RCCG members can request your rides — everyone is trusted.</span>
    </div>
  </div>
  @endif

</div><!-- /content-inner -->
@endsection

@push('realtime-js')
  <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
@endpush

@push('scripts')
<script>
/* ================================================================
   driver/dashboard.blade.php — page script
   chariot.js Driver.init() boots from the meta#driverStatus element.
   This script handles dashboard-specific UI wiring.
   ================================================================ */

document.addEventListener('DOMContentLoaded', function () {

  /* ── 1. WS STATUS LABEL ── */
  const wsDot  = document.getElementById('wsStatusDot');
  const wsText = document.getElementById('wsStatusText');

  setInterval(() => {
    if (!wsDot || !wsText) return;
    const isOnline = wsDot.classList.contains('online');
    wsText.textContent     = isOnline ? 'Live updates on' : 'Reconnecting…';
    wsDot.style.background = isOnline
      ? 'var(--clr-online)'
      : 'var(--clr-offline)';
  }, 3000);


  /* ── 2. CREATE RIDE FORM — wire submit to chariot.js Driver ── */
  /* chariot.js Driver._initCreateRideForm() already listens on
     #createRideFormEl. We just need the custom show-banner callback. */
  const origShowBanner = Chariot.Driver._showActiveRideBanner?.bind(Chariot.Driver);
  Chariot.Driver._showActiveRideBanner = function (ride) {
    const from = ride.from_zone?.name ?? '—';
    const to   = ride.to_zone?.name   ?? '—';

    const banner = document.getElementById('activeRideBanner');
    if (banner) {
      const fromEl = banner.querySelector('.active-ride-from');
      const toEl   = banner.querySelector('.active-ride-to');
      const seatsEl = document.getElementById('availableSeatsCount');
      if (fromEl)  fromEl.textContent  = from;
      if (toEl)    toEl.textContent    = to;
      if (seatsEl) seatsEl.textContent = ride.available_seats;
      banner.style.display = '';
    }

    /* Hide create form */
    document.getElementById('createRideForm').style.display = 'none';

    if (origShowBanner) origShowBanner(ride);
  };


  /* ── 3. TIME INPUT — default to current time + 5min ── */
  const timeInput = document.getElementById('departingAt');
  if (timeInput && !timeInput.value) {
    const now  = new Date(Date.now() + 5 * 60000);
    const hh   = now.getHours().toString().padStart(2, '0');
    const mm   = now.getMinutes().toString().padStart(2, '0');
    timeInput.value = `${hh}:${mm}`;
  }


  /* ── 4. SHOW/HIDE create form based on availability toggle ── */
  /* chariot.js Driver._updateAvailabilityUI already toggles
     #createRideForm display. This is additional polish only. */
  document.getElementById('availabilityToggle')?.addEventListener('click', () => {
    /* Brief delay to let chariot.js update state first */
    setTimeout(() => {
      const isAvailable = Chariot.Driver.isAvailable;
      const hasRide     = !!Chariot.Driver.activeRideId;
      const createForm  = document.getElementById('createRideForm');
      const banner      = document.getElementById('activeRideBanner');

      if (createForm) createForm.style.display = (isAvailable && !hasRide) ? '' : 'none';
      if (banner)     banner.style.display      = hasRide ? '' : 'none';
    }, 500);
  });

});
</script>
@endpush
<!-- ========================= [A] END ============================== -->