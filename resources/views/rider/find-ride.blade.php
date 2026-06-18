<!-- ===================================================================
  [C]  rider/find-ride.blade.php
  ====================================================================
  Cut everything between the [C] markers into:
      resources/views/rider/find-ride.blade.php
  Route    : GET /rider/find-ride    (RiderController@findRide)
  Middleware: auth, verified.member, is.rider
  Data from controller:
      $zones      — Collection<Zone>  all active zones
      $toZone     — int|null          pre-selected zone from ?to_zone=
  ================================================================== -->
<!-- ======================== [C] START ============================= -->
@extends('layouts.app')

@section('title', 'Find a Ride')

@push('styles')
<style>
  /* ── STICKY FILTER BAR ── */
  .filter-bar {
    position: sticky;
    top: 56px;
    z-index: 200;
    background: var(--bg-card);
    border-bottom: 1px solid var(--border-color);
    padding: var(--sp-3) var(--sp-4);
    box-shadow: var(--shadow-sm);
  }

  .filter-inner {
    max-width: 860px;
    margin: 0 auto;
    display: flex;
    flex-wrap: wrap;
    gap: var(--sp-2);
    align-items: flex-end;
  }

  .filter-field { flex: 1; min-width: 120px; }

  .filter-label {
    font-size: var(--text-xs);
    font-weight: var(--fw-semi);
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: var(--ls-wider);
    margin-bottom: var(--sp-1);
    display: flex;
    align-items: center;
    gap: 4px;
  }

  .filter-label i { font-size: 10px; }

  .filter-sep {
    font-size: 1.2rem;
    color: var(--clr-gold-mid);
    padding-bottom: 6px;
    flex-shrink: 0;
    align-self: flex-end;
  }

  .filter-action { flex-shrink: 0; align-self: flex-end; }

  /* ── RESULTS TOOLBAR ── */
  .results-toolbar {
    max-width: 860px;
    margin: 0 auto;
    padding: var(--sp-4) var(--sp-4) var(--sp-2);
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: var(--sp-2);
  }

  .results-count {
    font-size: var(--text-sm);
    font-weight: var(--fw-semi);
    color: var(--text-secondary);
  }

  .results-count strong { color: var(--text-primary); }

  /* View toggle pills */
  .view-toggle {
    display: flex;
    background: var(--bg-card-alt);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-pill);
    padding: 3px;
    gap: 2px;
  }

  .view-toggle-btn {
    padding: var(--sp-1) var(--sp-3);
    border: none;
    border-radius: var(--radius-pill);
    font-size: var(--text-xs);
    font-weight: var(--fw-semi);
    cursor: pointer;
    background: transparent;
    color: var(--text-secondary);
    transition: var(--transition-fast);
    display: flex;
    align-items: center;
    gap: var(--sp-1);
  }

  .view-toggle-btn.is-active {
    background: var(--bg-card);
    color: var(--clr-green-dark);
    box-shadow: var(--shadow-xs);
  }

  [data-theme="dark"] .view-toggle-btn.is-active { color: var(--clr-gold-mid); }

  /* ── RIDE LIST ── */
  .ride-list-wrap {
    max-width: 860px;
    margin: 0 auto;
    padding: 0 var(--sp-4) var(--sp-8);
  }

  /* ── MAP VIEW (alternative) ── */
  .ride-map-wrap {
    display: none; /* toggled by JS */
    max-width: 860px;
    margin: 0 auto;
    padding: 0 var(--sp-4) var(--sp-8);
  }

  #findRideMap {
    width: 100%;
    height: 480px;
    border-radius: var(--radius-card);
    overflow: hidden;
    border: 1px solid var(--border-color);
  }

  @media (min-width: 768px) {
    #findRideMap { height: 600px; }
  }

  /* ── LOADING OVERLAY ── */
  .list-loading {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: var(--sp-4);
    padding: var(--sp-12);
    color: var(--text-secondary);
    font-size: var(--text-sm);
  }

  /* ── RIDE CARD — request modal trigger ── */
  .request-modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.55);
    z-index: 1060;
    display: flex;
    align-items: flex-end;
    justify-content: center;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.28s ease;
    backdrop-filter: blur(3px);
  }

  .request-modal-backdrop.is-open {
    opacity: 1;
    pointer-events: all;
  }

  .request-modal {
    background: var(--bg-card);
    border-radius: 24px 24px 0 0;
    padding: var(--sp-6) var(--sp-5) calc(var(--sp-6) + env(safe-area-inset-bottom,0px));
    width: 100%;
    max-width: 520px;
    transform: translateY(100%);
    transition: transform 0.35s cubic-bezier(0.4,0,0.2,1);
    box-shadow: 0 -8px 40px rgba(0,0,0,0.25);
  }

  .request-modal-backdrop.is-open .request-modal {
    transform: translateY(0);
  }

  .request-modal-handle {
    width: 36px; height: 4px;
    background: var(--clr-gray-300);
    border-radius: var(--radius-pill);
    margin: 0 auto var(--sp-5);
  }

  .request-driver-row {
    display: flex;
    align-items: center;
    gap: var(--sp-3);
    padding: var(--sp-3) 0;
    border-bottom: 1px solid var(--border-color);
    margin-bottom: var(--sp-4);
  }

  .request-route-pill {
    display: inline-flex;
    align-items: center;
    gap: var(--sp-2);
    background: var(--bg-card-alt);
    border-radius: var(--radius-pill);
    padding: var(--sp-2) var(--sp-4);
    font-size: var(--text-sm);
    font-weight: var(--fw-medium);
    color: var(--text-secondary);
    margin-bottom: var(--sp-4);
    width: 100%;
    justify-content: center;
  }

  @media (min-width: 640px) {
    .request-modal {
      border-radius: 24px;
      margin: auto;
      max-width: 460px;
      transform: scale(0.92);
      opacity: 0;
    }
    .request-modal-backdrop.is-open .request-modal {
      transform: scale(1);
      opacity: 1;
    }
  }
</style>
@endpush

@section('content')

<!-- ── FILTER BAR ── -->
<div class="filter-bar">
  <div class="filter-inner">

    <div class="filter-field">
      <div class="filter-label">
        <i class="fa-solid fa-location-dot" style="color:var(--clr-gold-mid)"></i>
        From
      </div>
      <select class="select-chariot" id="fromZoneSelect" aria-label="From zone">
        <option value="">My location</option>
        @foreach($zones as $zone)
          <option value="{{ $zone->id }}">{{ $zone->name }}</option>
        @endforeach
      </select>
    </div>

    <div class="filter-sep">
      <i class="fa-solid fa-arrow-right"></i>
    </div>

    <div class="filter-field">
      <div class="filter-label">
        <i class="fa-solid fa-flag-checkered" style="color:var(--clr-green-mid)"></i>
        Destination
      </div>
      <select class="select-chariot" id="toZoneSelect" aria-label="Destination zone">
        <option value="">Any zone</option>
        @foreach($zones as $zone)
          <option value="{{ $zone->id }}"
            {{ (isset($toZone) && $toZone == $zone->id) ? 'selected' : '' }}>
            {{ $zone->name }}
          </option>
        @endforeach
      </select>
    </div>

    <div class="filter-action">
      <button class="btn-chariot btn-primary-c" id="findRidesBtn" aria-label="Search for rides">
        <i class="fa-solid fa-magnifying-glass"></i>
        <span class="d-none d-sm-inline">Search</span>
      </button>
    </div>

  </div>
</div>

<!-- ── RESULTS TOOLBAR ── -->
<div class="results-toolbar">
  <div class="results-count">
    <span id="rideCountLabel">Looking for rides…</span>
  </div>

  <div class="view-toggle" role="group" aria-label="View mode">
    <button class="view-toggle-btn is-active" data-view="list">
      <i class="fa-solid fa-list-ul"></i> List
    </button>
    <button class="view-toggle-btn" data-view="map">
      <i class="fa-solid fa-map"></i> Map
    </button>
  </div>
</div>

<!-- ── RIDE LIST ── -->
<div class="ride-list-wrap" id="rideListView">
  <div id="rideListContainer">
    <!-- Skeleton loading state -->
    <div class="list-loading" id="rideListSkeleton">
      <div class="chariot-spinner"></div>
      <span>Finding rides near you…</span>
    </div>
  </div>
</div>

<!-- ── MAP VIEW (alternative) ── -->
<div class="ride-map-wrap" id="rideMapView">
  <div id="findRideMap" role="img" aria-label="Ride locations map"></div>
</div>

<!-- ── REQUEST RIDE MODAL ── -->
<div class="request-modal-backdrop" id="requestModalBackdrop" role="dialog"
     aria-modal="true" aria-labelledby="requestModalTitle">
  <div class="request-modal" id="requestModal">
    <div class="request-modal-handle"></div>

    <h2 class="text-lg fw-bold text-primary-c mb-1" id="requestModalTitle">Request to Join</h2>
    <p class="text-sm text-muted-c mb-4">The driver will confirm your request.</p>

    <!-- Driver info row — filled by JS -->
    <div class="request-driver-row">
      <div class="chariot-avatar avatar-md" id="modalAvatar">JK</div>
      <div>
        <div class="fw-semi text-base text-primary-c" id="modalDriverName">Driver Name</div>
        <div class="text-xs text-muted-c" id="modalVehicle">Vehicle info</div>
      </div>
      <span class="badge-chariot active ms-auto" id="modalSeats">? seats</span>
    </div>

    <!-- Route pill -->
    <div class="request-route-pill" id="modalRoute">
      <i class="fa-solid fa-location-dot" style="color:var(--clr-gold-mid)"></i>
      <span id="modalFrom">—</span>
      <i class="fa-solid fa-arrow-right" style="color:var(--clr-gray-400)"></i>
      <i class="fa-solid fa-flag-checkered" style="color:var(--clr-green-mid)"></i>
      <span id="modalTo">—</span>
    </div>

    <!-- Pickup note -->
    <div class="form-group-c mb-4">
      <label class="form-label-c" for="pickupNote">
        Pickup note <span class="label-optional">(optional)</span>
      </label>
      <div class="input-icon-wrap">
        <i class="fa-solid fa-note-sticky input-icon-left"></i>
        <textarea
          class="input-chariot has-icon-left"
          id="pickupNote"
          placeholder="e.g. I'm near the fountain, wearing a red cap…"
          rows="2"
          maxlength="200"
          style="resize:none"
        ></textarea>
      </div>
    </div>

    <!-- Actions -->
    <div class="d-flex gap-3">
      <button class="btn-chariot btn-ghost-c flex-1" id="modalCancelBtn">
        Cancel
      </button>
      <button class="btn-chariot btn-primary-c flex-1" id="modalConfirmBtn">
        <i class="fa-solid fa-hand"></i>
        Send Request
      </button>
    </div>
  </div>
</div>

@endsection

@push('map-css')
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
@endpush

@push('map-js')
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endpush

@push('realtime-js')
  <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
@endpush

@push('scripts')
<script>
/* ================================================================
   rider/find-ride.blade.php — page script
   ================================================================ */

document.addEventListener('DOMContentLoaded', function () {

  let selectedRideId   = null;
  let selectedRideData = null;

  /* ── 1. AUTO-SEARCH on load ── */
  Chariot.Rider.findRides();

  /* Zone changes + button re-trigger search */
  document.getElementById('toZoneSelect')?.addEventListener('change',
    () => Chariot.Rider.findRides());
  document.getElementById('findRidesBtn')?.addEventListener('click',
    () => Chariot.Rider.findRides());


  /* ── 2. VIEW TOGGLE (List / Map) ── */
  const listView = document.getElementById('rideListView');
  const mapView  = document.getElementById('rideMapView');
  let   mapInited = false;

  document.querySelectorAll('.view-toggle-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.view-toggle-btn')
        .forEach(b => b.classList.remove('is-active'));
      btn.classList.add('is-active');

      if (btn.dataset.view === 'map') {
        listView.style.display = 'none';
        mapView.style.display  = '';

        if (!mapInited) {
          Chariot.Map.init('findRideMap');
          mapInited = true;
          /* Put driver markers on this map too */
          (Chariot.Rider.rides || []).forEach(ride => {
            const p = ride.driver?.driver_profile;
            if (p?.current_lat) {
              Chariot.Map.setDriverMarker(ride.driver.id,
                parseFloat(p.current_lat),
                parseFloat(p.current_lng),
                { name: ride.driver.name,
                  vehicle: `${p.vehicle_color ?? ''} ${p.vehicle_model ?? ''}`.trim(),
                  seats: ride.available_seats,
                  status: p.status }
              );
            }
          });
        } else {
          setTimeout(() => Chariot.Map.map?.invalidateSize(), 80);
        }
      } else {
        listView.style.display = '';
        mapView.style.display  = 'none';
      }
    });
  });


  /* ── 3. REQUEST MODAL — open ── */
  /* chariot.js Rider._buildRideCard injects:
       onclick="openRequestModal(rideId)"
     We expose it on window here. */
  window.openRequestModal = function (rideId) {
    const ride   = (Chariot.Rider.rides || []).find(r => r.id == rideId);
    if (!ride) return;

    selectedRideId   = rideId;
    selectedRideData = ride;

    const driver  = ride.driver || {};
    const profile = driver.driver_profile || {};
    const initials = (driver.name || 'D').slice(0, 2).toUpperCase();

    document.getElementById('modalAvatar').textContent     = initials;
    document.getElementById('modalDriverName').textContent = driver.name ?? 'Driver';
    document.getElementById('modalVehicle').textContent    =
      `${profile.vehicle_color ?? ''} ${profile.vehicle_model ?? ''} · ${profile.plate_number ?? ''}`.trim();
    document.getElementById('modalSeats').textContent      =
      `${ride.available_seats} seat${ride.available_seats !== 1 ? 's' : ''} left`;
    document.getElementById('modalFrom').textContent = ride.from_zone?.name ?? '—';
    document.getElementById('modalTo').textContent   = ride.to_zone?.name   ?? '—';
    document.getElementById('pickupNote').value      = '';

    document.getElementById('requestModalBackdrop').classList.add('is-open');
    document.getElementById('pickupNote').focus();
  };

  /* Close modal */
  const closeModal = () => {
    document.getElementById('requestModalBackdrop').classList.remove('is-open');
    selectedRideId = null;
  };

  document.getElementById('modalCancelBtn')?.addEventListener('click', closeModal);
  document.getElementById('requestModalBackdrop')?.addEventListener('click', e => {
    if (e.target === e.currentTarget) closeModal();
  });

  /* Escape key */
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeModal();
  });

  /* ── 4. REQUEST MODAL — confirm send ── */
  document.getElementById('modalConfirmBtn')?.addEventListener('click', async function () {
    if (!selectedRideId) return;

    const note = document.getElementById('pickupNote').value.trim();
    Chariot.Buttons.setLoading(this, true);

    try {
      await Chariot.Util.post(`/api/rides/${selectedRideId}/request`, { pickup_note: note });
      closeModal();
      Chariot.Toast.success('Request sent! Waiting for driver.', 'Request Sent 🎉');

      /* Disable the request button on that card */
      const card = document.querySelector(`.ride-card[data-ride-id="${selectedRideId}"]`);
      if (card) {
        const btn = card.querySelector('.btn-request-ride');
        if (btn) {
          btn.disabled     = true;
          btn.innerHTML    = '<i class="fa-solid fa-clock"></i> Pending…';
          btn.style.opacity = '0.7';
        }
      }

      /* Navigate to my-rides after short delay */
      setTimeout(() => window.location.href = '{{ route("rider.my-rides") }}', 2000);

    } catch (err) {
      Chariot.Toast.error(err.message || 'Could not send request. Try again.');
    } finally {
      Chariot.Buttons.setLoading(this, false);
    }
  });


  /* ── 5. OVERRIDE Rider._buildRideCard to use modal instead of prompt ── */
  /* We patch the request button onclick in the rendered cards. */
  const patchCards = () => {
    document.querySelectorAll('.btn-request-ride[data-ride-id]').forEach(btn => {
      /* Remove the old listener set by chariot.js and add our modal opener */
      const rideId = btn.dataset.rideId;
      const clone  = btn.cloneNode(true);
      btn.parentNode.replaceChild(clone, btn);
      clone.addEventListener('click', () => openRequestModal(rideId));
    });
  };

  /* Watch #rideListContainer for DOM changes (chariot.js re-renders it) */
  const observer = new MutationObserver(patchCards);
  const listContainer = document.getElementById('rideListContainer');
  if (listContainer) {
    observer.observe(listContainer, { childList: true, subtree: false });
  }

});
</script>
@endpush
<!-- ========================= [C] END ============================== -->
