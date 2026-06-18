<!-- ===================================================================
  [B]  rider/home.blade.php
  ====================================================================
  Cut everything between the [B] markers into:
      resources/views/rider/home.blade.php
  Route    : GET /rider/home         (RiderController@home)
  Middleware: auth, verified.member, is.rider
  Data from controller:
      $zones       — Collection<Zone>   all active camp zones
      $userCoords  — ['lat'=>..,'lng'=>..] or null
  ================================================================== -->
<!-- ======================== [B] START ============================= -->
@extends('layouts.app')

@section('title', 'Map')

{{-- ── Map needs Leaflet CSS ── --}}
@push('map-css')
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
@endpush

{{-- ── Page-specific styles ── --}}
@push('styles')
<style>
  /* Home page uses the full viewport — remove inner padding */
  .chariot-content { padding-left: 0; padding-right: 0; }
  .chariot-content-inner { padding: 0; max-width: 100%; }

  /* ── MAP fills available height ── */
  #chariotMap {
    position: fixed;
    top: 56px;   /* below navbar */
    left: 0;
    right: 0;
    bottom: 64px; /* above bottom nav */
    z-index: 1;
  }

  @media (min-width: 768px) {
    #chariotMap {
      left: 240px; /* beside sidebar */
      bottom: 0;
    }
  }

  /* Leaflet overrides */
  .leaflet-control-zoom { margin-bottom: 200px !important; }

  @media (min-width: 768px) {
    .leaflet-control-zoom { margin-bottom: 48px !important; }
  }

  /* ── BOTTOM SHEET ── */
  .bottom-sheet {
    position: fixed;
    left: 0; right: 0;
    bottom: 64px;
    background: var(--bg-card);
    border-radius: 20px 20px 0 0;
    box-shadow: 0 -8px 40px rgba(0,0,0,0.18);
    z-index: 800;
    max-height: 68vh;
    overflow-y: auto;
    overscroll-behavior: contain;
    scrollbar-width: none;
    transition: transform 0.38s cubic-bezier(0.4,0,0.2,1);
  }

  .bottom-sheet::-webkit-scrollbar { display: none; }

  .bottom-sheet.is-collapsed {
    transform: translateY(calc(100% - 84px));
  }

  @media (min-width: 768px) {
    .bottom-sheet {
      left: 248px;
      bottom: 8px;
      right: auto;
      width: 380px;
      border-radius: 20px;
      max-height: calc(100vh - 80px);
    }
    .bottom-sheet.is-collapsed { transform: translateY(0); }
  }

  .sheet-handle-wrap {
    display: flex;
    justify-content: center;
    padding: 12px var(--sp-4) var(--sp-2);
    cursor: grab;
    user-select: none;
  }

  .sheet-handle {
    width: 38px; height: 4px;
    background: var(--clr-gray-300);
    border-radius: var(--radius-pill);
    transition: background 0.2s;
  }

  [data-theme="dark"] .sheet-handle { background: var(--clr-gray-600); }

  .sheet-handle-wrap:hover .sheet-handle { background: var(--clr-gold-mid); }

  .sheet-body { padding: var(--sp-1) var(--sp-4) var(--sp-5); }

  /* Sheet peek label (visible when collapsed) */
  .sheet-peek {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 var(--sp-4) var(--sp-3);
  }

  .sheet-peek-label {
    font-size: var(--text-sm);
    font-weight: var(--fw-semi);
    color: var(--text-secondary);
  }

  .sheet-peek-count {
    font-size: var(--text-sm);
    font-weight: var(--fw-bold);
    color: var(--clr-gold-mid);
  }

  /* ── ZONE SELECT ROW ── */
  .zone-row {
    display: grid;
    grid-template-columns: 1fr auto 1fr;
    align-items: center;
    gap: var(--sp-2);
    margin-bottom: var(--sp-4);
  }

  .zone-label {
    font-size: var(--text-xs);
    font-weight: var(--fw-semi);
    color: var(--text-secondary);
    letter-spacing: var(--ls-wide);
    text-transform: uppercase;
    margin-bottom: var(--sp-1);
  }

  .zone-swap-btn {
    width: 34px; height: 34px;
    border-radius: 50%;
    border: 1.5px solid var(--border-color);
    background: var(--bg-card-alt);
    color: var(--clr-gold-mid);
    display: flex; align-items: center; justify-content: center;
    cursor: pointer;
    transition: var(--transition-base);
    flex-shrink: 0;
    margin-top: 18px; /* align with selects */
  }

  .zone-swap-btn:hover {
    border-color: var(--clr-gold-mid);
    background: var(--clr-gold-tint);
    transform: rotate(180deg);
  }

  /* ── LOCATE ME MAP BUTTON ── */
  .map-btn-locate {
    position: fixed;
    right: var(--sp-4);
    bottom: calc(64px + var(--sp-4));
    z-index: 900;
    width: 44px; height: 44px;
    border-radius: 50%;
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    box-shadow: var(--shadow-lg);
    display: flex; align-items: center; justify-content: center;
    cursor: pointer;
    color: var(--clr-green-dark);
    font-size: 1.1rem;
    transition: var(--transition-base);
  }

  .map-btn-locate:hover {
    background: var(--clr-green-tint);
    border-color: var(--clr-green-dark);
    transform: scale(1.06);
  }

  @media (min-width: 768px) {
    .map-btn-locate { bottom: var(--sp-6); }
  }

  /* ── GPS STATUS CHIP ── */
  .gps-chip {
    display: inline-flex;
    align-items: center;
    gap: var(--sp-2);
    font-size: var(--text-xs);
    font-weight: var(--fw-semi);
    color: var(--text-secondary);
    background: var(--bg-card-alt);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-pill);
    padding: var(--sp-1) var(--sp-3);
    margin-bottom: var(--sp-4);
  }

  .gps-chip.is-found { color: var(--clr-success); border-color: rgba(34,197,94,0.3); }
  .gps-chip.is-found i { color: var(--clr-success); }
  .gps-chip.is-searching i { animation: spin 1.2s linear infinite; color: var(--clr-gold-mid); }

  /* ── DRIVER CHIPS SCROLL ── */
  .nearby-section-title {
    font-size: var(--text-xs);
    font-weight: var(--fw-semi);
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: var(--ls-widest);
    margin-bottom: var(--sp-3);
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .driver-chips-scroll {
    display: flex;
    gap: var(--sp-3);
    overflow-x: auto;
    padding-bottom: var(--sp-2);
    scrollbar-width: none;
    -webkit-overflow-scrolling: touch;
  }

  .driver-chips-scroll::-webkit-scrollbar { display: none; }

  /* ── CONNECTION STATUS DOT (top-right of map) ── */
  .ws-status {
    position: fixed;
    top: 64px;
    right: var(--sp-4);
    z-index: 850;
    display: flex;
    align-items: center;
    gap: 5px;
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-pill);
    padding: 4px 10px;
    font-size: 10px;
    font-weight: var(--fw-semi);
    color: var(--text-muted);
    box-shadow: var(--shadow-sm);
    pointer-events: none;
  }

  .ws-status-dot {
    width: 7px; height: 7px;
    border-radius: 50%;
    background: var(--clr-offline);
  }

  .ws-status-dot.online { background: var(--clr-online); animation: dotPulse 2s infinite; }

  @media (min-width: 768px) {
    .ws-status { top: 68px; right: calc(var(--sp-4)); }
  }
</style>
@endpush

@section('content-class', 'no-inner-pad')

@section('content')

<!-- ── CONNECTION STATUS ── -->
<div class="ws-status" id="wsStatus">
  <div class="ws-status-dot" id="wsStatusDot"></div>
  <span id="wsStatusText">Connecting…</span>
</div>

<!-- ── LEAFLET MAP ── -->
<div id="chariotMap" role="img" aria-label="Camp Ground Map"></div>

<!-- ── LOCATE ME BUTTON ── -->
<button class="map-btn-locate" id="mapLocateBtn" aria-label="Centre map on my location" title="My location">
  <i class="fa-solid fa-location-crosshairs"></i>
</button>

<!-- ── BOTTOM SHEET ── -->
<div class="bottom-sheet is-collapsed" id="bottomSheet">

  <!-- Drag handle -->
  <div class="sheet-handle-wrap" id="bottomSheetHandle">
    <div class="sheet-handle"></div>
  </div>

  <!-- Peek row (visible when collapsed) -->
  <div class="sheet-peek">
    <span class="sheet-peek-label">
      <i class="fa-solid fa-car me-1" style="color:var(--clr-gold-mid)"></i>
      Nearby drivers
    </span>
    <span class="sheet-peek-count" id="driverChipCount">—</span>
  </div>

  <!-- Expanded content -->
  <div class="sheet-body">

    <!-- GPS status -->
    <div class="gps-chip is-searching" id="gpsChip">
      <i class="fa-solid fa-circle-notch"></i>
      <span id="gpsChipText">Getting your location…</span>
    </div>

    <!-- Zone selector row -->
    <div class="zone-row">
      <div>
        <div class="zone-label"><i class="fa-solid fa-location-dot me-1" style="color:var(--clr-gold-mid)"></i>From</div>
        <select class="select-chariot" id="fromZoneSelect" aria-label="Departure zone">
          <option value="">My location</option>
          @foreach($zones as $zone)
            <option value="{{ $zone->id }}">{{ $zone->name }}</option>
          @endforeach
        </select>
      </div>

      <button class="zone-swap-btn" id="zoneSwapBtn" aria-label="Swap zones">
        <i class="fa-solid fa-arrow-right-arrow-left" style="font-size:.75rem"></i>
      </button>

      <div>
        <div class="zone-label"><i class="fa-solid fa-flag-checkered me-1" style="color:var(--clr-green-mid)"></i>Going to</div>
        <select class="select-chariot" id="toZoneSelect" aria-label="Destination zone">
          <option value="">Any zone</option>
          @foreach($zones as $zone)
            <option value="{{ $zone->id }}">{{ $zone->name }}</option>
          @endforeach
        </select>
      </div>
    </div>

    <!-- Find rides CTA -->
    <a href="{{ route('rider.find-ride') }}" class="btn-chariot btn-primary-c btn-block mb-4" id="findRidesCta">
      <i class="fa-solid fa-magnifying-glass"></i>
      Find Available Rides
    </a>

    <!-- Nearby drivers chips -->
    <div class="nearby-section-title">
      <span>Drivers Near You</span>
      <span class="text-xs text-gold" id="nearbyRefreshTime"></span>
    </div>

    <div class="driver-chips-scroll" id="driverChipsWrap">
      <!-- Skeleton chips while loading -->
      <div style="display:flex;gap:12px">
        <div style="width:84px">
          <div class="skeleton" style="width:44px;height:44px;border-radius:50%;margin:0 auto 8px"></div>
          <div class="skeleton skeleton-text" style="width:64px;margin:0 auto 4px"></div>
          <div class="skeleton skeleton-text" style="width:40px;margin:0 auto"></div>
        </div>
        <div style="width:84px">
          <div class="skeleton" style="width:44px;height:44px;border-radius:50%;margin:0 auto 8px"></div>
          <div class="skeleton skeleton-text" style="width:64px;margin:0 auto 4px"></div>
          <div class="skeleton skeleton-text" style="width:40px;margin:0 auto"></div>
        </div>
        <div style="width:84px">
          <div class="skeleton" style="width:44px;height:44px;border-radius:50%;margin:0 auto 8px"></div>
          <div class="skeleton skeleton-text" style="width:64px;margin:0 auto 4px"></div>
          <div class="skeleton skeleton-text" style="width:40px;margin:0 auto"></div>
        </div>
      </div>
    </div>

  </div><!-- /sheet-body -->
</div><!-- /bottom-sheet -->

@endsection


{{-- ── Leaflet JS ── --}}
@push('map-js')
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endpush

{{-- ── Pusher + Echo for real-time driver location updates ── --}}
@push('realtime-js')
  <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
@endpush

@push('scripts')
<script>
/* ================================================================
   rider/home.blade.php — page script
   Leaflet is loaded. Chariot.Map + Chariot.Realtime boot via
   chariot.js DOMContentLoaded (checks for #chariotMap + userRole).
   This script adds the home-specific UI interactions.
   ================================================================ */

document.addEventListener('DOMContentLoaded', function () {

  /* ── 1. BOTTOM SHEET — handle tap & touch swipe ── */
  const sheet  = document.getElementById('bottomSheet');
  const handle = document.getElementById('bottomSheetHandle');
  let touchStartY = 0;

  handle.addEventListener('click', () => {
    sheet.classList.toggle('is-collapsed');
  });

  handle.addEventListener('touchstart', e => {
    touchStartY = e.touches[0].clientY;
  }, { passive: true });

  handle.addEventListener('touchend', e => {
    const delta = e.changedTouches[0].clientY - touchStartY;
    if (delta < -40) sheet.classList.remove('is-collapsed');
    if (delta > 40)  sheet.classList.add('is-collapsed');
  }, { passive: true });

  /* Tap anywhere on map collapses sheet */
  document.getElementById('chariotMap')?.addEventListener('click', () => {
    if (!sheet.classList.contains('is-collapsed')) {
      sheet.classList.add('is-collapsed');
    }
  });


  /* ── 2. ZONE SWAP ── */
  document.getElementById('zoneSwapBtn')?.addEventListener('click', () => {
    const from = document.getElementById('fromZoneSelect');
    const to   = document.getElementById('toZoneSelect');
    const tmp  = from.value;
    from.value = to.value;
    to.value   = tmp;
  });


  /* ── 3. FIND RIDES CTA — append zone param to URL ── */
  document.getElementById('findRidesCta')?.addEventListener('click', function (e) {
    const toZone = document.getElementById('toZoneSelect').value;
    if (toZone) {
      e.preventDefault();
      window.location.href = `{{ route('rider.find-ride') }}?to_zone=${toZone}`;
    }
  });


  /* ── 4. GPS CHIP — updated by Chariot.Map when location is known ── */
  const origOnLocation = Chariot.Rider.onLocationKnown.bind(Chariot.Rider);
  Chariot.Rider.onLocationKnown = function (lat, lng) {
    origOnLocation(lat, lng);

    const chip  = document.getElementById('gpsChip');
    const label = document.getElementById('gpsChipText');
    if (chip && label) {
      chip.className  = 'gps-chip is-found';
      label.innerHTML = `<i class="fa-solid fa-circle-check me-1"></i>Location detected`;
    }

    /* Expand sheet once location is known */
    sheet.classList.remove('is-collapsed');
  };


  /* ── 5. DRIVER CHIPS count update ── */
  /* chariot.js Rider._renderDriverChips() populates #driverChipsWrap.
     Watch for DOM changes and update the peek count. */
  const chipsWrap   = document.getElementById('driverChipsWrap');
  const peekCount   = document.getElementById('driverChipCount');
  const refreshTime = document.getElementById('nearbyRefreshTime');

  if (chipsWrap && peekCount) {
    const mo = new MutationObserver(() => {
      const count = chipsWrap.querySelectorAll('.driver-chip').length;
      peekCount.textContent = count > 0 ? `${count} available` : 'None nearby';
      if (refreshTime) refreshTime.textContent = 'Updated just now';
    });
    mo.observe(chipsWrap, { childList: true, subtree: true });
  }


  /* ── 6. WS STATUS TEXT ── */
  /* chariot.js Realtime._setConnectionStatus() updates #wsStatus dot.
     Mirror the status in the text label. */
  const wsText = document.getElementById('wsStatusText');
  const wsDot  = document.getElementById('wsStatusDot');

  /* Poll dot class to update text (simple, avoids tight coupling) */
  setInterval(() => {
    if (!wsDot || !wsText) return;
    const online = wsDot.classList.contains('online');
    wsText.textContent = online ? 'Live' : 'Offline';
  }, 3000);

});
</script>
@endpush
<!-- ========================= [B] END ============================== -->