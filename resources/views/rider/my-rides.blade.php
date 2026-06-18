<!-- ===================================================================
  [D]  rider/my-rides.blade.php
  ====================================================================
  Cut everything between the [D] markers into:
      resources/views/rider/my-rides.blade.php
  Route    : GET /rider/my-rides     (RiderController@myRides)
  Middleware: auth, verified.member, is.rider
  Data from controller:
      $pendingRequests   — Collection<RideRequest> status=pending
      $acceptedRequests  — Collection<RideRequest> status=accepted
      $historyRequests   — Collection<RideRequest> status=completed|declined|cancelled
  ================================================================== -->
<!-- ======================== [D] START ============================= -->
@extends('layouts.app')

@section('title', 'My Rides')

@push('styles')
<style>
  /* ── TAB PILLS ── */
  .tab-pills {
    display: flex;
    background: var(--bg-card-alt);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-pill);
    padding: 4px;
    gap: 3px;
    margin-bottom: var(--sp-5);
  }

  .tab-pill-btn {
    flex: 1;
    padding: var(--sp-2) var(--sp-3);
    border: none;
    border-radius: var(--radius-pill);
    font-family: var(--font-body);
    font-size: var(--text-sm);
    font-weight: var(--fw-semi);
    cursor: pointer;
    background: transparent;
    color: var(--text-secondary);
    transition: var(--transition-base);
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--sp-2);
    white-space: nowrap;
  }

  .tab-pill-btn.is-active {
    background: var(--bg-card);
    color: var(--clr-green-dark);
    box-shadow: var(--shadow-sm);
  }

  [data-theme="dark"] .tab-pill-btn.is-active { color: var(--clr-gold-mid); }

  /* Tab badge */
  .tab-count {
    min-width: 18px; height: 18px;
    padding: 0 5px;
    background: var(--clr-danger);
    color: white;
    font-size: 10px;
    font-weight: var(--fw-bold);
    border-radius: var(--radius-pill);
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }

  .tab-pill-btn.is-active .tab-count { background: var(--clr-green-dark); }
  .tab-count:empty { display: none; }

  /* ── TAB PANES ── */
  .tab-pane { display: none; }
  .tab-pane.is-active { display: block; }

  /* ── PENDING CARD ── */
  .my-ride-card {
    background: var(--bg-card);
    border: 1.5px solid var(--border-color);
    border-radius: var(--radius-card);
    margin-bottom: var(--sp-3);
    overflow: hidden;
    transition: var(--transition-base);
    animation: fadeUp 0.3s ease both;
  }

  .my-ride-card:hover { box-shadow: var(--shadow-md); }

  .my-ride-card-top {
    padding: var(--sp-4) var(--sp-5);
    border-bottom: 1px solid var(--border-color);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: var(--sp-3);
    flex-wrap: wrap;
  }

  .my-ride-driver-row {
    display: flex;
    align-items: center;
    gap: var(--sp-3);
  }

  .my-ride-driver-name {
    font-weight: var(--fw-semi);
    font-size: var(--text-base);
    color: var(--text-primary);
    line-height: 1.2;
  }

  .my-ride-vehicle {
    font-size: var(--text-xs);
    color: var(--text-muted);
    display: flex;
    align-items: center;
    gap: var(--sp-1);
    margin-top: 2px;
  }

  .my-ride-card-body {
    padding: var(--sp-4) var(--sp-5);
  }

  .my-ride-route {
    display: flex;
    align-items: center;
    gap: var(--sp-3);
    font-size: var(--text-sm);
    margin-bottom: var(--sp-3);
    flex-wrap: wrap;
  }

  .route-pt {
    display: flex;
    align-items: center;
    gap: var(--sp-2);
    font-weight: var(--fw-medium);
    color: var(--text-primary);
  }

  .my-ride-meta {
    display: flex;
    flex-wrap: wrap;
    gap: var(--sp-4);
    font-size: var(--text-xs);
    color: var(--text-secondary);
    margin-bottom: var(--sp-3);
  }

  .my-ride-meta-item {
    display: flex;
    align-items: center;
    gap: var(--sp-1);
  }

  .my-ride-meta-item i { color: var(--clr-gold-mid); font-size: 11px; }

  /* ── ACCEPTED CARD special styling ── */
  .my-ride-card.is-accepted {
    border-color: rgba(34,197,94,0.3);
  }

  .my-ride-card.is-accepted .my-ride-card-top {
    background: rgba(34,197,94,0.04);
  }

  .confirmed-banner {
    display: flex;
    align-items: center;
    gap: var(--sp-2);
    font-size: var(--text-sm);
    font-weight: var(--fw-semi);
    color: var(--clr-success-dark);
    margin-bottom: var(--sp-3);
    padding: var(--sp-2) var(--sp-3);
    background: var(--clr-success-tint);
    border-radius: var(--radius-md);
  }

  [data-theme="dark"] .confirmed-banner { color: #86EFAC; background: rgba(34,197,94,0.1); }

  .driver-contact {
    display: flex;
    align-items: center;
    gap: var(--sp-2);
    font-size: var(--text-sm);
    color: var(--text-secondary);
    margin-bottom: var(--sp-2);
  }

  .driver-contact i { color: var(--clr-gold-mid); width: 14px; text-align: center; }

  /* ── HISTORY CARD ── */
  .my-ride-card.is-history { opacity: 0.85; }
  .my-ride-card.is-history:hover { opacity: 1; }

  /* ── AUTO-REFRESH INDICATOR ── */
  .refresh-row {
    display: flex;
    align-items: center;
    gap: var(--sp-2);
    font-size: var(--text-xs);
    color: var(--text-muted);
    margin-bottom: var(--sp-4);
  }

  .refresh-dot {
    width: 6px; height: 6px;
    border-radius: 50%;
    background: var(--clr-success);
    animation: dotPulse 2s infinite;
  }
</style>
@endpush

@section('content')
<div class="chariot-content-inner">

  <!-- Page header -->
  <div class="page-title">My Rides</div>
  <p class="page-subtitle">Track your ride requests and history.</p>

  <!-- Auto-refresh indicator -->
  <div class="refresh-row">
    <div class="refresh-dot"></div>
    <span>Updates automatically · Last refresh: <span id="lastRefresh">just now</span></span>
  </div>

  <!-- ── TAB PILLS ── -->
  <div class="tab-pills" role="tablist">
    <button class="tab-pill-btn is-active" data-tab="pending"
            role="tab" aria-selected="true" aria-controls="tabPending">
      Pending
      <span class="tab-count" id="pendingTabCount">
        {{ $pendingRequests->count() ?: '' }}
      </span>
    </button>
    <button class="tab-pill-btn" data-tab="accepted"
            role="tab" aria-selected="false" aria-controls="tabAccepted">
      Accepted
      <span class="tab-count" id="acceptedTabCount">
        {{ $acceptedRequests->count() ?: '' }}
      </span>
    </button>
    <button class="tab-pill-btn" data-tab="history"
            role="tab" aria-selected="false" aria-controls="tabHistory">
      History
    </button>
  </div>

  <!-- ══ TAB: PENDING ══ -->
  <div class="tab-pane is-active" id="tabPending" role="tabpanel">
    @forelse($pendingRequests as $req)
      @php
        $ride   = $req->ride;
        $driver = $ride->driver;
        $profile = $driver->driverProfile;
        $initials = strtoupper(substr($driver->name, 0, 2));
      @endphp
      <div class="my-ride-card" data-request-id="{{ $req->id }}">
        <div class="my-ride-card-top">
          <div class="my-ride-driver-row">
            <div class="chariot-avatar">{{ $initials }}</div>
            <div>
              <div class="my-ride-driver-name">{{ $driver->name }}</div>
              <div class="my-ride-vehicle">
                <i class="fa-solid fa-car"></i>
                {{ $profile->vehicle_color }} {{ $profile->vehicle_model }}
                · {{ $profile->plate_number }}
              </div>
            </div>
          </div>
          <span class="badge-chariot pending">
            <i class="fa-solid fa-clock"></i> Pending
          </span>
        </div>
        <div class="my-ride-card-body">
          <div class="my-ride-route">
            <div class="route-pt">
              <i class="fa-solid fa-location-dot" style="color:var(--clr-gold-mid)"></i>
              {{ $ride->fromZone->name }}
            </div>
            <i class="fa-solid fa-arrow-right route-arrow"></i>
            <div class="route-pt">
              <i class="fa-solid fa-flag-checkered" style="color:var(--clr-green-mid)"></i>
              {{ $ride->toZone->name }}
            </div>
          </div>
          <div class="my-ride-meta">
            <span class="my-ride-meta-item">
              <i class="fa-solid fa-clock"></i>
              Requested {{ $req->created_at->diffForHumans() }}
            </span>
            <span class="my-ride-meta-item">
              <i class="fa-solid fa-chair"></i>
              {{ $ride->available_seats }} seats left
            </span>
          </div>
          @if($req->pickup_note)
            <div class="request-note mb-3">
              <i class="fa-solid fa-quote-left"></i>
              <span>{{ $req->pickup_note }}</span>
            </div>
          @endif
          <button
            class="btn-chariot btn-danger-c btn-sm-c"
            onclick="Chariot.Rider.cancelRequest({{ $req->id }}, this)"
          >
            <i class="fa-solid fa-xmark"></i> Cancel Request
          </button>
        </div>
      </div>
    @empty
      <div class="empty-state">
        <i class="fa-solid fa-clock-rotate-left empty-state-icon"></i>
        <div class="empty-state-title">No pending requests</div>
        <div class="empty-state-text">Requests you've sent to drivers will show up here.</div>
        <a href="{{ route('rider.find-ride') }}" class="btn-chariot btn-primary-c btn-sm-c">
          <i class="fa-solid fa-route"></i> Find a Ride
        </a>
      </div>
    @endforelse
  </div>

  <!-- ══ TAB: ACCEPTED ══ -->
  <div class="tab-pane" id="tabAccepted" role="tabpanel">
    @forelse($acceptedRequests as $req)
      @php
        $ride    = $req->ride;
        $driver  = $ride->driver;
        $profile = $driver->driverProfile;
        $initials = strtoupper(substr($driver->name, 0, 2));
      @endphp
      <div class="my-ride-card is-accepted">
        <div class="my-ride-card-top">
          <div class="my-ride-driver-row">
            <div class="chariot-avatar">{{ $initials }}</div>
            <div>
              <div class="my-ride-driver-name">{{ $driver->name }}</div>
              <div class="my-ride-vehicle">
                <i class="fa-solid fa-car"></i>
                {{ $profile->vehicle_color }} {{ $profile->vehicle_model }}
              </div>
            </div>
          </div>
          <span class="badge-chariot accepted">
            <i class="fa-solid fa-circle-check"></i> Confirmed
          </span>
        </div>
        <div class="my-ride-card-body">
          <div class="confirmed-banner">
            <i class="fa-solid fa-circle-check"></i>
            Your ride is confirmed! Be ready at the pickup point.
          </div>
          <div class="my-ride-route mb-3">
            <div class="route-pt">
              <i class="fa-solid fa-location-dot" style="color:var(--clr-gold-mid)"></i>
              {{ $ride->fromZone->name }}
            </div>
            <i class="fa-solid fa-arrow-right route-arrow"></i>
            <div class="route-pt">
              <i class="fa-solid fa-flag-checkered" style="color:var(--clr-green-mid)"></i>
              {{ $ride->toZone->name }}
            </div>
          </div>
          <div class="driver-contact">
            <i class="fa-solid fa-car"></i>
            {{ $profile->vehicle_color }} {{ $profile->vehicle_model }}
            · <strong>{{ $profile->plate_number }}</strong>
          </div>
          @if($driver->phone)
            <div class="driver-contact">
              <i class="fa-solid fa-phone"></i>
              {{ $driver->phone }}
            </div>
          @endif
          @if($ride->departing_at)
            <div class="driver-contact">
              <i class="fa-solid fa-clock"></i>
              Departing at {{ \Carbon\Carbon::parse($ride->departing_at)->format('g:i A') }}
            </div>
          @endif
        </div>
      </div>
    @empty
      <div class="empty-state">
        <i class="fa-solid fa-car empty-state-icon"></i>
        <div class="empty-state-title">No confirmed rides</div>
        <div class="empty-state-text">When a driver accepts your request it will appear here.</div>
      </div>
    @endforelse
  </div>

  <!-- ══ TAB: HISTORY ══ -->
  <div class="tab-pane" id="tabHistory" role="tabpanel">
    @forelse($historyRequests as $req)
      @php
        $ride    = $req->ride;
        $driver  = $ride->driver;
        $initials = strtoupper(substr($driver->name, 0, 2));
      @endphp
      <div class="my-ride-card is-history">
        <div class="my-ride-card-top">
          <div class="my-ride-driver-row">
            <div class="chariot-avatar avatar-sm">{{ $initials }}</div>
            <div>
              <div class="my-ride-driver-name" style="font-size:var(--text-sm)">
                {{ $driver->name }}
              </div>
              <div class="text-xs text-muted-c">
                {{ $ride->fromZone->name }} → {{ $ride->toZone->name }}
              </div>
            </div>
          </div>
          <div class="d-flex flex-column align-items-end gap-1">
            <span class="badge-chariot {{ $req->status }}">{{ $req->status }}</span>
            <span class="text-xs text-muted-c">{{ $req->created_at->format('M j, g:i A') }}</span>
          </div>
        </div>
      </div>
    @empty
      <div class="empty-state">
        <i class="fa-regular fa-clock empty-state-icon"></i>
        <div class="empty-state-title">No ride history yet</div>
        <div class="empty-state-text">Your past ride requests will appear here.</div>
      </div>
    @endforelse
  </div>

</div><!-- /content-inner -->
@endsection

@push('realtime-js')
  <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
@endpush

@push('scripts')
<script>
/* ================================================================
   rider/my-rides.blade.php — page script
   ================================================================ */

document.addEventListener('DOMContentLoaded', function () {

  /* ── 1. TAB SWITCHING ── */
  const tabs  = document.querySelectorAll('.tab-pill-btn');
  const panes = document.querySelectorAll('.tab-pane');

  tabs.forEach(btn => {
    btn.addEventListener('click', () => {
      tabs.forEach(t  => { t.classList.remove('is-active');  t.setAttribute('aria-selected','false'); });
      panes.forEach(p => p.classList.remove('is-active'));

      btn.classList.add('is-active');
      btn.setAttribute('aria-selected', 'true');
      document.getElementById(`tab${btn.dataset.tab.charAt(0).toUpperCase() + btn.dataset.tab.slice(1)}`)
        ?.classList.add('is-active');
    });
  });

  /* Auto-open accepted tab if there are accepted rides and no pending */
  const pendingCount  = parseInt(document.getElementById('pendingTabCount')?.textContent) || 0;
  const acceptedCount = parseInt(document.getElementById('acceptedTabCount')?.textContent) || 0;
  if (acceptedCount > 0 && pendingCount === 0) {
    document.querySelector('[data-tab="accepted"]')?.click();
  }


  /* ── 2. REAL-TIME — listen for status changes (Realtime module in chariot.js) ── */
  /* chariot.js Realtime._listenRideStatus() already handles this globally.
     Here we just refresh the tab counts when a change comes in. */
  const origRefresh = Chariot.Rider.refreshMyRides?.bind(Chariot.Rider);
  Chariot.Rider.refreshMyRides = async function () {
    /* Full page reload is simplest for MVP — avoids re-rendering all tabs */
    window.location.reload();
  };


  /* ── 3. AUTO-POLL every 20s as WS fallback ── */
  let pollCount = 0;
  const refreshEl = document.getElementById('lastRefresh');

  setInterval(() => {
    pollCount++;
    if (pollCount % 4 === 0) {
      /* Every 80s do a silent reload */
      window.location.reload();
    }
    /* Update "last refresh" text */
    if (refreshEl) {
      const secs = pollCount * 20;
      refreshEl.textContent = secs < 60
        ? `${secs}s ago`
        : `${Math.floor(secs / 60)}m ago`;
    }
  }, 20000);

});
</script>
@endpush
<!-- ========================= [D] END ============================== -->