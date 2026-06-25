<!-- ===================================================================
  [B]  admin/dashboard.blade.php
  ====================================================================
  Cut everything between the [B] markers into:
      resources/views/admin/dashboard.blade.php
  Route    : GET /admin/dashboard    (AdminController@dashboard)
  Middleware: auth, is.admin
  Data from controller:
      $totalUsers     — int
      $totalDrivers   — int
      $totalRiders    — int
      $activeRides    — int
      $todayRequests  — int
      $pendingVerify  — int
      $recentRides    — Collection<Ride> (latest 10, with driver/zones)
      $recentRequests — Collection<RideRequest> (latest 10, with rider/ride)
  ================================================================== -->
<!-- ======================== [B] START ============================= -->
@extends('layouts.admin')

@section('title', 'Dashboard')

@push('styles')
<style>
  /* ════════════════════════════════════════════
     STATS GRID
     ════════════════════════════════════════════ */
  .admin-stats-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--sp-3);
    margin-bottom: var(--sp-6);
  }

  @media (min-width: 640px)  { .admin-stats-grid { grid-template-columns: repeat(3, 1fr); } }
  @media (min-width: 1024px) { .admin-stats-grid { grid-template-columns: repeat(6, 1fr); } }

  .asc {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-card);
    padding: var(--sp-4) var(--sp-4);
    position: relative;
    overflow: hidden;
    transition: var(--transition-base);
    cursor: default;
  }

  .asc:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-lg);
  }

  .asc.green-top { border-top: 3px solid var(--clr-green-mid); }
  .asc.gold-top  { border-top: 3px solid var(--clr-gold-mid); }
  .asc.blue-top  { border-top: 3px solid var(--clr-info); }
  .asc.red-top   { border-top: 3px solid var(--clr-danger); }
  .asc.teal-top  { border-top: 3px solid #14B8A6; }
  .asc.purple-top{ border-top: 3px solid #8B5CF6; }

  .asc-val {
    font-family: var(--font-display);
    font-size: var(--text-3xl);
    font-weight: var(--fw-black);
    color: var(--text-primary);
    line-height: 1;
    margin-bottom: var(--sp-1);
    letter-spacing: var(--ls-tight);
  }

  .asc-label {
    font-size: var(--text-xs);
    font-weight: var(--fw-semi);
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: var(--ls-widest);
    line-height: 1.3;
  }

  .asc-icon {
    position: absolute;
    bottom: var(--sp-3);
    right: var(--sp-3);
    font-size: 1.8rem;
    opacity: 0.07;
    color: var(--text-primary);
  }

  /* ════════════════════════════════════════════
     DASHBOARD GRID (2-col on lg)
     ════════════════════════════════════════════ */
  .dash-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: var(--sp-5);
  }

  @media (min-width: 1024px) {
    .dash-grid { grid-template-columns: 1fr 1fr; }
  }

  /* ── PANEL CARD ── */
  .panel {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-card);
    overflow: hidden;
  }

  .panel-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: var(--sp-4) var(--sp-5);
    border-bottom: 1px solid var(--border-color);
    background: var(--bg-card-alt);
  }

  .panel-title {
    font-size: var(--text-sm);
    font-weight: var(--fw-bold);
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: var(--sp-2);
  }

  .panel-title i { color: var(--clr-gold-mid); font-size: 0.9rem; }

  .panel-action {
    font-size: var(--text-xs);
    font-weight: var(--fw-semi);
    color: var(--clr-green-dark);
    text-decoration: none;
    transition: color 0.2s;
  }

  .panel-action:hover { color: var(--clr-gold-mid); }

  /* Compact table inside panel */
  .panel-table {
    width: 100%;
    border-collapse: collapse;
    font-size: var(--text-sm);
  }

  .panel-table thead th {
    padding: var(--sp-3) var(--sp-4);
    font-size: var(--text-xs);
    font-weight: var(--fw-semi);
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: var(--ls-wider);
    border-bottom: 1px solid var(--border-color);
    text-align: left;
    white-space: nowrap;
  }

  .panel-table tbody tr {
    border-bottom: 1px solid var(--border-color);
    transition: background 0.15s;
  }

  .panel-table tbody tr:last-child { border-bottom: none; }
  .panel-table tbody tr:hover { background: var(--bg-card-alt); }

  .panel-table td {
    padding: var(--sp-3) var(--sp-4);
    color: var(--text-primary);
    vertical-align: middle;
  }

  /* ── TIMELINE (recent requests) ── */
  .timeline { padding: var(--sp-4) var(--sp-5); }

  .timeline-item {
    display: flex;
    gap: var(--sp-3);
    padding-bottom: var(--sp-4);
    position: relative;
  }

  .timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    top: 20px;
    left: 15px;
    width: 1px;
    height: calc(100% - 8px);
    background: var(--border-color);
  }

  .timeline-dot {
    width: 30px; height: 30px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.75rem;
    flex-shrink: 0;
    position: relative;
    z-index: 1;
  }

  .timeline-dot.pending  { background: var(--clr-warning-tint); color: var(--clr-warning); }
  .timeline-dot.accepted { background: var(--clr-success-tint); color: var(--clr-success); }
  .timeline-dot.declined { background: var(--clr-danger-tint);  color: var(--clr-danger); }
  .timeline-dot.completed{ background: var(--clr-info-tint);    color: var(--clr-info); }

  [data-theme="dark"] .timeline-dot.pending  { background: rgba(245,158,11,0.12); }
  [data-theme="dark"] .timeline-dot.accepted { background: rgba(34,197,94,0.12); }
  [data-theme="dark"] .timeline-dot.declined { background: rgba(239,68,68,0.12); }
  [data-theme="dark"] .timeline-dot.completed{ background: rgba(59,130,246,0.12); }

  .timeline-content { flex: 1; min-width: 0; padding-top: 4px; }

  .timeline-main {
    font-size: var(--text-sm);
    color: var(--text-primary);
    font-weight: var(--fw-medium);
    line-height: var(--lh-snug);
    margin-bottom: 2px;
  }

  .timeline-sub {
    font-size: var(--text-xs);
    color: var(--text-muted);
    display: flex;
    align-items: center;
    gap: var(--sp-2);
    flex-wrap: wrap;
  }

  /* ── LIVE INDICATOR (auto-refresh) ── */
  .live-indicator {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 10px;
    font-weight: var(--fw-bold);
    letter-spacing: var(--ls-wider);
    color: var(--clr-success);
    background: var(--clr-success-tint);
    border: 1px solid rgba(34,197,94,0.2);
    border-radius: var(--radius-pill);
    padding: 2px var(--sp-2);
  }

  [data-theme="dark"] .live-indicator { background: rgba(34,197,94,0.1); }

  .live-indicator::before {
    content: '';
    width: 5px; height: 5px;
    border-radius: 50%;
    background: var(--clr-success);
    animation: dotPulse 2s infinite;
  }
</style>
@endpush

@section('content')

  <!-- Breadcrumb -->
  <div class="admin-breadcrumb">
    <i class="fa-solid fa-gauge-high" style="color:var(--clr-gold-mid)"></i>
    <span class="admin-breadcrumb-sep">/</span>
    <span class="admin-breadcrumb-current">Dashboard</span>
    <span class="live-indicator ms-auto">LIVE</span>
  </div>

  <!-- Page header -->
  <div class="admin-page-header">
    <div>
      <div class="admin-page-title">Overview</div>
      <div class="admin-page-sub">Real-time snapshot of camp mobility activity.</div>
    </div>
    <span class="text-xs text-muted-c" id="lastRefreshed">
      Refreshed: just now
    </span>
  </div>

  <!-- ════════════════════════════════════════
       STATS GRID
       ════════════════════════════════════════ -->
  <div class="admin-stats-grid">

    <div class="asc green-top">
      <div class="asc-val" data-count="{{ $totalUsers ?? 0 }}">{{ $totalUsers ?? 0 }}</div>
      <div class="asc-label">Total Users</div>
      <i class="fa-solid fa-users asc-icon"></i>
    </div>

    <div class="asc gold-top">
      <div class="asc-val" data-count="{{ $totalDrivers ?? 0 }}">{{ $totalDrivers ?? 0 }}</div>
      <div class="asc-label">Drivers</div>
      <i class="fa-solid fa-car asc-icon"></i>
    </div>

    <div class="asc blue-top">
      <div class="asc-val" data-count="{{ $totalRiders ?? 0 }}">{{ $totalRiders ?? 0 }}</div>
      <div class="asc-label">Riders</div>
      <i class="fa-solid fa-user asc-icon"></i>
    </div>

    <div class="asc teal-top">
      <div class="asc-val" data-count="{{ $activeRides ?? 0 }}">{{ $activeRides ?? 0 }}</div>
      <div class="asc-label">Active Rides</div>
      <i class="fa-solid fa-route asc-icon"></i>
    </div>

    <div class="asc purple-top">
      <div class="asc-val" data-count="{{ $todayRequests ?? 0 }}">{{ $todayRequests ?? 0 }}</div>
      <div class="asc-label">Today's Requests</div>
      <i class="fa-solid fa-hand asc-icon"></i>
    </div>

    <div class="asc red-top">
      <div class="asc-val" data-count="{{ $pendingVerify ?? 0 }}">{{ $pendingVerify ?? 0 }}</div>
      <div class="asc-label">Pending Verify</div>
      <i class="fa-solid fa-shield-halved asc-icon"></i>
    </div>

  </div>

  <!-- ════════════════════════════════════════
       DASHBOARD 2-COL GRID
       ════════════════════════════════════════ -->
  <div class="dash-grid">

    <!-- ── LIVE RIDES TABLE ── -->
    <div class="panel">
      <div class="panel-header">
        <div class="panel-title">
          <i class="fa-solid fa-car"></i> Active Rides
        </div>
        <a href="{{ route('admin.rides') }}" class="panel-action">View all →</a>
      </div>

      <div style="overflow-x:auto">
        <table class="panel-table" id="adminRidesTable">
          <thead>
            <tr>
              <th>Driver</th>
              <th>Route</th>
              <th>Seats</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            @forelse($recentRides as $ride)
              <tr>
                <td>
                  <div style="display:flex;align-items:center;gap:var(--sp-2)">
                    <div class="chariot-avatar avatar-sm">
                      {{ strtoupper(substr($ride->driver->name, 0, 2)) }}
                    </div>
                    <div>
                      <div style="font-weight:var(--fw-medium);font-size:var(--text-sm)">
                        {{ $ride->driver->name }}
                      </div>
                      <div style="font-size:var(--text-xs);color:var(--text-muted);font-family:var(--font-mono)">
                        {{ $ride->driver->driverProfile->plate_number ?? '—' }}
                      </div>
                    </div>
                  </div>
                </td>
                <td style="font-size:var(--text-xs)">
                  <div style="color:var(--text-secondary)">{{ $ride->fromZone->name }}</div>
                  <div style="color:var(--clr-gold-mid);margin:2px 0">↓</div>
                  <div style="color:var(--text-secondary)">{{ $ride->toZone->name }}</div>
                </td>
                <td>
                  <span style="font-family:var(--font-mono);font-size:var(--text-sm);font-weight:var(--fw-semi)">
                    {{ $ride->available_seats }}/{{ $ride->total_seats }}
                  </span>
                </td>
                <td>
                  <span class="badge-chariot {{ $ride->status }}">{{ $ride->status }}</span>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="4" style="text-align:center;padding:var(--sp-8);color:var(--text-muted);font-size:var(--text-sm)">
                  <i class="fa-solid fa-car" style="font-size:1.5rem;opacity:.2;display:block;margin-bottom:var(--sp-2)"></i>
                  No active rides right now
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    <!-- ── RECENT REQUESTS TIMELINE ── -->
    <div class="panel">
      <div class="panel-header">
        <div class="panel-title">
          <i class="fa-solid fa-clock-rotate-left"></i> Recent Requests
        </div>
        <span class="text-xs text-muted-c" id="timelineTime">—</span>
      </div>

      <div class="timeline" id="requestsTimeline">
        @forelse($recentRequests as $req)
          @php
            $dotMap = ['pending'=>'fa-clock','accepted'=>'fa-circle-check','declined'=>'fa-circle-xmark','completed'=>'fa-flag-checkered'];
          @endphp
          <div class="timeline-item">
            <div class="timeline-dot {{ $req->status }}">
              <i class="fa-solid {{ $dotMap[$req->status] ?? 'fa-circle' }}"></i>
            </div>
            <div class="timeline-content">
              <div class="timeline-main">
                {{ $req->rider->name }} →
                {{ $req->ride->toZone->name ?? '—' }}
              </div>
              <div class="timeline-sub">
                <span class="badge-chariot {{ $req->status }}" style="font-size:9px;padding:1px 6px">
                  {{ $req->status }}
                </span>
                <span>{{ $req->created_at->diffForHumans() }}</span>
              </div>
            </div>
          </div>
        @empty
          <div class="empty-state py-5">
            <i class="fa-solid fa-hand empty-state-icon"></i>
            <div class="empty-state-text">No requests yet today</div>
          </div>
        @endforelse
      </div>
    </div>

  </div><!-- /dash-grid -->

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

  /* ── 1. Animate stat counters on load ── */
  document.querySelectorAll('.asc-val[data-count]').forEach(el => {
    const target = parseInt(el.dataset.count) || 0;
    if (target === 0) return;
    const start = performance.now();
    const step  = (now) => {
      const p = Math.min((now - start) / 900, 1);
      const e = 1 - Math.pow(1 - p, 3);
      el.textContent = Math.round(target * e);
      if (p < 1) requestAnimationFrame(step);
    };
    requestAnimationFrame(step);
  });


  /* ── 2. Auto-refresh rides table every 20s ── */
  let refreshCount = 0;
  const refreshEl  = document.getElementById('lastRefreshed');
  const timelineEl = document.getElementById('timelineTime');

  const tick = () => {
    refreshCount++;
    const mins = Math.floor(refreshCount * 20 / 60);
    const secs = (refreshCount * 20) % 60;
    if (refreshEl) {
      refreshEl.textContent = `Refreshed: ${mins > 0 ? mins + 'm ' : ''}${secs}s ago`;
    }
    if (timelineEl) {
      timelineEl.textContent = `${refreshCount * 20}s ago`;
    }
    Chariot.Admin._refreshRidesTable?.();
  };

  setInterval(tick, 20000);

});
</script>
@endpush
<!-- ========================= [B] END ============================== -->
