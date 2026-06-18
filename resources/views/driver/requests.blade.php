<!-- ===================================================================
  [B]  driver/requests.blade.php
  ====================================================================
  Cut everything between the [B] markers into:
      resources/views/driver/requests.blade.php
  Route    : GET /driver/requests    (DriverDashboardController@requests)
  Middleware: auth, verified.member, is.driver
  Data from controller:
      $activeRide       — Ride|null
      $pendingRequests  — Collection<RideRequest> status=pending
      $acceptedRequests — Collection<RideRequest> status=accepted
      $profile          — DriverProfile
  ================================================================== -->
<!-- ======================== [B] START ============================= -->
@extends('layouts.app')

@section('title', 'Ride Requests')

@push('styles')
<style>
  /* ════════════════════════════════════════════
     ROUTE CONTEXT BAR
     ════════════════════════════════════════════ */
  .route-context-bar {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: var(--sp-3) var(--sp-5);
    margin-bottom: var(--sp-5);
    display: flex;
    align-items: center;
    gap: var(--sp-3);
    flex-wrap: wrap;
  }

  .route-context-label {
    font-size: var(--text-xs);
    font-weight: var(--fw-semi);
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: var(--ls-wider);
    flex-shrink: 0;
  }

  .route-context-pill {
    display: flex;
    align-items: center;
    gap: var(--sp-2);
    font-size: var(--text-sm);
    font-weight: var(--fw-semi);
    color: var(--text-primary);
  }

  .route-context-sep {
    color: var(--clr-gold-mid);
    flex-shrink: 0;
  }

  .seats-remaining-pill {
    margin-left: auto;
    display: inline-flex;
    align-items: center;
    gap: var(--sp-1);
    background: var(--clr-green-tint);
    border: 1px solid rgba(30,122,60,0.2);
    border-radius: var(--radius-pill);
    padding: 3px var(--sp-3);
    font-size: var(--text-xs);
    font-weight: var(--fw-bold);
    color: var(--clr-green-dark);
  }

  [data-theme="dark"] .seats-remaining-pill {
    background: rgba(30,122,60,0.15);
    color: var(--clr-green-light);
  }

  /* ════════════════════════════════════════════
     REQUEST CARD
     ════════════════════════════════════════════ */
  .req-card {
    background: var(--bg-card);
    border: 1.5px solid var(--border-color);
    border-radius: var(--radius-card);
    margin-bottom: var(--sp-3);
    overflow: hidden;
    transition: var(--transition-base);
    animation: fadeUp 0.3s ease both;
    position: relative;
  }

  .req-card:nth-child(1) { animation-delay: 0.04s; }
  .req-card:nth-child(2) { animation-delay: 0.08s; }
  .req-card:nth-child(3) { animation-delay: 0.12s; }

  /* New request flash */
  @keyframes flashNewReq {
    0%, 100% { border-color: var(--border-color); }
    40%       {
      border-color: var(--clr-gold-mid);
      box-shadow: 0 0 0 3px rgba(201,162,39,0.18);
    }
  }

  .req-card.is-new { animation: flashNewReq 1.1s ease 3; }

  .req-card-top {
    display: flex;
    align-items: center;
    gap: var(--sp-3);
    padding: var(--sp-4) var(--sp-5);
    border-bottom: 1px solid var(--border-color);
  }

  .req-rider-info { flex: 1; min-width: 0; }

  .req-rider-name {
    font-size: var(--text-base);
    font-weight: var(--fw-semi);
    color: var(--text-primary);
    line-height: 1.2;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .req-rider-time {
    font-size: var(--text-xs);
    color: var(--text-muted);
    margin-top: 2px;
    display: flex;
    align-items: center;
    gap: var(--sp-1);
  }

  /* Pickup note */
  .req-note {
    margin: var(--sp-4) var(--sp-5);
    padding: var(--sp-3);
    background: var(--clr-gold-tint);
    border-left: 3px solid var(--clr-gold-mid);
    border-radius: 0 var(--radius-md) var(--radius-md) 0;
    display: flex;
    align-items: flex-start;
    gap: var(--sp-2);
    font-size: var(--text-sm);
    color: var(--text-secondary);
    line-height: var(--lh-snug);
  }

  [data-theme="dark"] .req-note {
    background: rgba(201,162,39,0.07);
  }

  .req-note i {
    color: var(--clr-gold-mid);
    flex-shrink: 0;
    font-size: 0.8rem;
    margin-top: 2px;
  }

  /* Action buttons row */
  .req-actions {
    display: flex;
    gap: var(--sp-3);
    padding: var(--sp-4) var(--sp-5);
  }

  /* ════════════════════════════════════════════
     ACCEPTED RIDERS SECTION
     ════════════════════════════════════════════ */
  .accepted-section {
    margin-top: var(--sp-6);
    margin-bottom: var(--sp-4);
  }

  .accepted-section-title {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: var(--sp-3);
  }

  .accepted-section-label {
    font-size: var(--text-xs);
    font-weight: var(--fw-semi);
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: var(--ls-widest);
  }

  .seats-used-label {
    font-size: var(--text-xs);
    font-weight: var(--fw-bold);
    color: var(--clr-green-mid);
  }

  .accepted-rider-row {
    display: flex;
    align-items: center;
    gap: var(--sp-3);
    padding: var(--sp-3) var(--sp-4);
    background: var(--clr-success-tint);
    border-radius: var(--radius-md);
    margin-bottom: var(--sp-2);
    transition: var(--transition-fast);
    animation: fadeUp 0.3s ease both;
  }

  [data-theme="dark"] .accepted-rider-row {
    background: rgba(34,197,94,0.08);
  }

  .accepted-rider-name {
    flex: 1;
    font-size: var(--text-sm);
    font-weight: var(--fw-semi);
    color: var(--clr-success-dark);
  }

  [data-theme="dark"] .accepted-rider-name { color: #86EFAC; }

  .accepted-rider-seat {
    font-size: var(--text-xs);
    font-weight: var(--fw-medium);
    color: var(--clr-success-dark);
    opacity: 0.7;
  }

  /* ════════════════════════════════════════════
     COMPLETE RIDE BUTTON (sticky bottom)
     ════════════════════════════════════════════ */
  .complete-ride-wrap {
    position: sticky;
    bottom: calc(64px + var(--sp-3));
    left: 0;
    right: 0;
    z-index: 400;
    padding: var(--sp-3) 0;
  }

  @media (min-width: 768px) {
    .complete-ride-wrap {
      position: static;
      padding: 0;
      margin-top: var(--sp-5);
    }
  }

  .btn-complete-ride {
    width: 100%;
    padding: var(--sp-4);
    background: linear-gradient(135deg, var(--clr-green-dark), var(--clr-green-mid));
    color: white;
    border: none;
    border-radius: var(--radius-lg);
    font-family: var(--font-display);
    font-size: var(--text-base);
    font-weight: var(--fw-bold);
    letter-spacing: var(--ls-wide);
    cursor: pointer;
    transition: var(--transition-base);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--sp-3);
    box-shadow: var(--shadow-green), 0 -4px 20px rgba(0,0,0,0.08);
  }

  .btn-complete-ride:hover {
    filter: brightness(1.08);
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(21,84,40,0.45);
  }

  .btn-complete-ride:active { transform: translateY(0); }

  /* ════════════════════════════════════════════
     NO ACTIVE RIDE STATE
     ════════════════════════════════════════════ */
  .no-ride-card {
    background: var(--bg-card);
    border: 1.5px dashed var(--border-color);
    border-radius: var(--radius-xl);
    padding: var(--sp-10) var(--sp-5);
    text-align: center;
    margin-bottom: var(--sp-4);
  }

  .no-ride-icon {
    font-size: 3rem;
    color: var(--text-muted);
    opacity: 0.3;
    margin-bottom: var(--sp-4);
  }

  /* Real-time badge in header */
  .live-badge {
    display: inline-flex;
    align-items: center;
    gap: var(--sp-1);
    font-size: 10px;
    font-weight: var(--fw-bold);
    color: var(--clr-success);
    background: var(--clr-success-tint);
    border: 1px solid rgba(34,197,94,0.25);
    border-radius: var(--radius-pill);
    padding: 2px var(--sp-2);
    letter-spacing: var(--ls-wide);
  }

  [data-theme="dark"] .live-badge {
    background: rgba(34,197,94,0.1);
  }

  .live-badge::before {
    content: '';
    width: 5px; height: 5px;
    border-radius: 50%;
    background: var(--clr-success);
    animation: dotPulse 2s infinite;
  }
</style>
@endpush

@section('content')
<div class="chariot-content-inner">

  <!-- ── DRIVER STATUS META ── -->
  <div id="driverStatus"
       data-available="{{ $profile->is_available ? 'true' : 'false' }}"
       data-status="{{ $profile->status }}"
       data-active-ride="{{ $activeRide?->id ?? '' }}"
       style="display:none">
  </div>

  <!-- ── PAGE HEADER ── -->
  <div class="d-flex align-items-center justify-content-between mb-4">
    <div>
      <div class="page-title">Ride Requests</div>
      <p class="page-subtitle mb-0">Accept or decline riders for your current route.</p>
    </div>
    <span class="live-badge">LIVE</span>
  </div>

  @if($activeRide)

    <!-- ── ROUTE CONTEXT BAR ── -->
    <div class="route-context-bar">
      <span class="route-context-label">Your Route</span>
      <div class="route-context-pill">
        <i class="fa-solid fa-location-dot" style="color:var(--clr-gold-mid)"></i>
        {{ $activeRide->fromZone->name }}
      </div>
      <i class="fa-solid fa-arrow-right route-context-sep"></i>
      <div class="route-context-pill">
        <i class="fa-solid fa-flag-checkered" style="color:var(--clr-green-mid)"></i>
        {{ $activeRide->toZone->name }}
      </div>
      <div class="seats-remaining-pill">
        <i class="fa-solid fa-chair"></i>
        <span id="availableSeatsCount">{{ $activeRide->available_seats }}</span> left
      </div>
    </div>

    <!-- ════════════════════════════════════════
         PENDING REQUESTS
         ════════════════════════════════════════ -->
    <div class="section-label mb-3">
      Pending Requests
      @if($pendingRequests->count() > 0)
        <span class="ms-2" style="color:var(--clr-gold-mid);font-family:var(--font-mono)">
          ({{ $pendingRequests->count() }})
        </span>
      @endif
    </div>

    <div id="requestsContainer">
      @forelse($pendingRequests as $req)
        @php
          $rider    = $req->rider;
          $initials = strtoupper(substr($rider->name, 0, 2));
        @endphp
        <div class="req-card" data-id="{{ $req->id }}">
          <div class="req-card-top">
            <div class="chariot-avatar">{{ $initials }}</div>
            <div class="req-rider-info">
              <div class="req-rider-name">{{ $rider->name }}</div>
              <div class="req-rider-time">
                <i class="fa-solid fa-clock" style="font-size:10px"></i>
                {{ $req->created_at->diffForHumans() }}
              </div>
            </div>
            @if($rider->is_verified)
              <span class="verified-badge" title="Verified RCCG member">
                <i class="fa-solid fa-circle-check"></i>
              </span>
            @endif
          </div>

          @if($req->pickup_note)
            <div class="req-note">
              <i class="fa-solid fa-quote-left"></i>
              <span>{{ $req->pickup_note }}</span>
            </div>
          @endif

          <div class="req-actions">
            <button
              class="btn-accept btn-chariot"
              data-request-id="{{ $req->id }}"
            >
              <i class="fa-solid fa-circle-check"></i> Accept
            </button>
            <button
              class="btn-decline btn-chariot"
              data-request-id="{{ $req->id }}"
            >
              <i class="fa-solid fa-circle-xmark"></i> Decline
            </button>
          </div>
        </div>
      @empty
        <div class="empty-state py-5">
          <i class="fa-solid fa-hand empty-state-icon"></i>
          <div class="empty-state-title">No pending requests</div>
          <div class="empty-state-text">
            Requests from riders will appear here in real-time.<br>
            Make sure you're online and have posted a ride.
          </div>
        </div>
      @endforelse
    </div>

    <!-- ════════════════════════════════════════
         ACCEPTED RIDERS
         ════════════════════════════════════════ -->
    @if($acceptedRequests->count() > 0)
      <div class="accepted-section">
        <div class="accepted-section-title">
          <span class="accepted-section-label">Accepted Riders</span>
          <span class="seats-used-label">
            {{ $acceptedRequests->count() }} of
            {{ $activeRide->total_seats }} seats filled
          </span>
        </div>

        <!-- Seat progress bar -->
        <div style="height:6px;background:var(--border-color);border-radius:var(--radius-pill);margin-bottom:var(--sp-4);overflow:hidden">
          <div style="
            height:100%;
            width:{{ round(($acceptedRequests->count() / $activeRide->total_seats) * 100) }}%;
            background:linear-gradient(90deg, var(--clr-green-dark), var(--clr-success));
            border-radius:var(--radius-pill);
            transition:width 0.5s ease;
          "></div>
        </div>

        <div id="acceptedRidersList">
          @foreach($acceptedRequests as $i => $req)
            <div class="accepted-rider-row" style="animation-delay:{{ $i * 0.06 }}s">
              <i class="fa-solid fa-circle-check" style="color:var(--clr-success);flex-shrink:0"></i>
              <div class="chariot-avatar avatar-sm">
                {{ strtoupper(substr($req->rider->name, 0, 2)) }}
              </div>
              <span class="accepted-rider-name">{{ $req->rider->name }}</span>
              <span class="accepted-rider-seat">Seat {{ $i + 1 }}</span>
            </div>
          @endforeach
        </div>
      </div>
    @endif

    <!-- ════════════════════════════════════════
         COMPLETE RIDE — sticky CTA
         ════════════════════════════════════════ -->
    @if($acceptedRequests->count() > 0)
      <div class="complete-ride-wrap">
        <button class="btn-complete-ride" id="completeRideBtn"
                aria-label="Mark ride as completed">
          <i class="fa-solid fa-circle-check"></i>
          Complete Ride
        </button>
      </div>
    @endif

  @else
    <!-- ── NO ACTIVE RIDE ── -->
    <div class="no-ride-card">
      <div class="no-ride-icon">
        <i class="fa-solid fa-car-side"></i>
      </div>
      <div class="empty-state-title mb-2">No active ride</div>
      <p class="empty-state-text mb-4">
        Go available and post a ride on the dashboard to start receiving requests.
      </p>
      <a href="{{ route('driver.dashboard') }}" class="btn-chariot btn-primary-c">
        <i class="fa-solid fa-gauge-high"></i>
        Go to Dashboard
      </a>
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
   driver/requests.blade.php — page script
   chariot.js Driver._initRequestActions() handles accept/decline
   via delegated click on .btn-accept / .btn-decline[data-request-id].
   chariot.js Realtime._listenIncomingRequests() triggers refreshRequests().
   This script adds the page-specific complete-ride wire-up and
   the real-time refresh-on-new animation.
   ================================================================ */

document.addEventListener('DOMContentLoaded', function () {

  /* ── 1. COMPLETE RIDE ── */
  /* chariot.js Driver._initCompleteRide() already listens on #completeRideBtn.
     Nothing extra needed here — it redirects to dashboard on success. */


  /* ── 2. ANIMATE new request cards pushed by WebSocket ── */
  /* Override refreshRequests to add flash animation to newly added cards */
  const origRefresh = Chariot.Driver.refreshRequests?.bind(Chariot.Driver);
  Chariot.Driver.refreshRequests = async function () {
    const countBefore = document.querySelectorAll('.req-card').length;
    if (origRefresh) await origRefresh();

    /* Flash newly added cards */
    setTimeout(() => {
      const cards = document.querySelectorAll('.req-card');
      cards.forEach((card, i) => {
        if (i >= countBefore) {
          card.classList.add('is-new');
        }
      });
    }, 100);
  };


  /* ── 3. UPDATE seats-remaining count on accept/decline ── */
  /* chariot.js Driver._respondToRequest updates #availableSeatsCount.
     Mirror it in the route-context-bar as well. */
  const seatsEl = document.getElementById('availableSeatsCount');
  if (seatsEl) {
    const observer = new MutationObserver(() => {
      /* seats count updated — no extra action needed on this page */
    });
    observer.observe(seatsEl, { childList: true, characterData: true, subtree: true });
  }


  /* ── 4. AUTO-POLL as WS fallback every 15s ── */
  setInterval(() => {
    Chariot.Driver.refreshRequests?.();
  }, 15000);

});
</script>
@endpush
<!-- ========================= [B] END ============================== -->