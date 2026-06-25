<!-- ===================================================================
  [D]  admin/rides.blade.php
  ====================================================================
  Cut everything between the [D] markers into:
      resources/views/admin/rides.blade.php
  Route    : GET /admin/rides        (AdminController@rides)
  Middleware: auth, is.admin
  Data from controller:
      $rides      — LengthAwarePaginator<Ride> (20/page, all statuses)
                    eager: driver.driverProfile, fromZone, toZone, requests
      $statusCounts — ['active'=>int, 'full'=>int, 'completed'=>int, 'cancelled'=>int]
  ================================================================== -->
<!-- ======================== [D] START ============================= -->
@extends('layouts.admin')

@section('title', 'Rides')

@push('styles')
<style>
  /* ════════════════════════════════════════════
     STATUS FILTER TABS
     ════════════════════════════════════════════ */
  .status-tabs {
    display: flex;
    flex-wrap: wrap;
    gap: var(--sp-2);
    margin-bottom: var(--sp-4);
  }

  .status-tab {
    display: inline-flex;
    align-items: center;
    gap: var(--sp-2);
    padding: var(--sp-2) var(--sp-4);
    border-radius: var(--radius-pill);
    font-size: var(--text-sm);
    font-weight: var(--fw-semi);
    cursor: pointer;
    border: 1.5px solid var(--border-color);
    background: var(--bg-card);
    color: var(--text-secondary);
    text-decoration: none;
    transition: var(--transition-fast);
    user-select: none;
  }

  .status-tab:hover { border-color: var(--clr-gold-mid); color: var(--clr-gold-mid); }

  .status-tab.is-active {
    background: var(--clr-green-deep);
    border-color: var(--clr-green-deep);
    color: white;
    box-shadow: var(--shadow-green);
  }

  .status-tab .tab-num {
    min-width: 20px; height: 18px;
    padding: 0 5px;
    border-radius: var(--radius-pill);
    font-size: 10px;
    font-weight: var(--fw-bold);
    display: inline-flex; align-items: center; justify-content: center;
    background: rgba(255,255,255,0.18);
  }

  .status-tab:not(.is-active) .tab-num {
    background: var(--bg-card-alt);
    color: var(--text-muted);
  }

  /* ════════════════════════════════════════════
     RIDES TABLE
     ════════════════════════════════════════════ */
  .rides-table-wrap {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-card);
    overflow: hidden;
  }

  .rides-table-scroll { overflow-x: auto; }

  @media (max-width: 1023px) {
    .rides-admin-table { min-width: 800px; }
  }

  .rides-admin-table {
    width: 100%;
    border-collapse: collapse;
    font-size: var(--text-sm);
  }

  .rides-admin-table thead {
    background: var(--bg-card-alt);
    border-bottom: 2px solid var(--border-color);
  }

  .rides-admin-table thead th {
    padding: var(--sp-3) var(--sp-4);
    font-size: var(--text-xs);
    font-weight: var(--fw-semi);
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: var(--ls-widest);
    text-align: left;
    white-space: nowrap;
  }

  .rides-admin-table tbody tr {
    border-bottom: 1px solid var(--border-color);
    transition: background 0.15s;
  }

  .rides-admin-table tbody tr:last-child { border-bottom: none; }
  .rides-admin-table tbody tr:hover { background: var(--bg-card-alt); }

  .rides-admin-table td {
    padding: var(--sp-3) var(--sp-4);
    vertical-align: middle;
    color: var(--text-primary);
  }

  /* Seats progress bar in table */
  .seats-bar-wrap { min-width: 100px; }

  .seats-bar {
    height: 5px;
    background: var(--border-color);
    border-radius: var(--radius-pill);
    overflow: hidden;
    margin-top: 4px;
  }

  .seats-bar-fill {
    height: 100%;
    border-radius: var(--radius-pill);
    background: linear-gradient(90deg, var(--clr-green-dark), var(--clr-success));
    transition: width 0.4s ease;
  }

  .seats-bar-fill.is-full { background: var(--clr-warning); }
</style>
@endpush

@section('content')

  <!-- Breadcrumb -->
  <div class="admin-breadcrumb">
    <a href="{{ route('admin.dashboard') }}"><i class="fa-solid fa-gauge-high" style="color:var(--clr-gold-mid)"></i></a>
    <span class="admin-breadcrumb-sep">/</span>
    <span class="admin-breadcrumb-current">Rides</span>
  </div>

  <!-- Page header -->
  <div class="admin-page-header">
    <div>
      <div class="admin-page-title">Ride Management</div>
      <div class="admin-page-sub">Monitor and manage all rides across the camp ground.</div>
    </div>
  </div>

  <!-- ════════════════════════════════════════
       STATUS FILTER TABS
       ════════════════════════════════════════ -->
  <div class="status-tabs">
    @php
      $currentStatus = request('status', '');
      $allCount = array_sum($statusCounts ?? []);
    @endphp

    <a href="{{ route('admin.rides') }}"
       class="status-tab {{ $currentStatus === '' ? 'is-active' : '' }}">
      All <span class="tab-num">{{ $allCount }}</span>
    </a>
    <a href="{{ route('admin.rides', ['status' => 'active']) }}"
       class="status-tab {{ $currentStatus === 'active' ? 'is-active' : '' }}">
      Active <span class="tab-num">{{ $statusCounts['active'] ?? 0 }}</span>
    </a>
    <a href="{{ route('admin.rides', ['status' => 'full']) }}"
       class="status-tab {{ $currentStatus === 'full' ? 'is-active' : '' }}">
      Full <span class="tab-num">{{ $statusCounts['full'] ?? 0 }}</span>
    </a>
    <a href="{{ route('admin.rides', ['status' => 'completed']) }}"
       class="status-tab {{ $currentStatus === 'completed' ? 'is-active' : '' }}">
      Completed <span class="tab-num">{{ $statusCounts['completed'] ?? 0 }}</span>
    </a>
    <a href="{{ route('admin.rides', ['status' => 'cancelled']) }}"
       class="status-tab {{ $currentStatus === 'cancelled' ? 'is-active' : '' }}">
      Cancelled <span class="tab-num">{{ $statusCounts['cancelled'] ?? 0 }}</span>
    </a>
  </div>

  <!-- ════════════════════════════════════════
       RIDES TABLE
       ════════════════════════════════════════ -->
  <div class="rides-table-wrap">
    <div class="rides-table-scroll">
      <table class="rides-admin-table">
        <thead>
          <tr>
            <th>Driver</th>
            <th>Route</th>
            <th>Seats</th>
            <th>Riders</th>
            <th>Posted</th>
            <th>Status</th>
            <th style="text-align:right">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($rides as $ride)
            @php
              $used      = $ride->total_seats - $ride->available_seats;
              $pct       = $ride->total_seats > 0 ? round(($used / $ride->total_seats) * 100) : 0;
              $isFull    = $ride->available_seats === 0;
              $accepted  = $ride->requests->where('status','accepted')->count();
              $pending   = $ride->requests->where('status','pending')->count();
            @endphp
            <tr>
              <!-- Driver -->
              <td>
                <div style="display:flex;align-items:center;gap:var(--sp-2)">
                  <div class="chariot-avatar avatar-sm">
                    {{ strtoupper(substr($ride->driver->name, 0, 2)) }}
                  </div>
                  <div>
                    <div style="font-weight:var(--fw-medium)">{{ $ride->driver->name }}</div>
                    <div style="font-size:var(--text-xs);color:var(--text-muted);font-family:var(--font-mono)">
                      {{ $ride->driver->driverProfile->plate_number ?? '—' }}
                    </div>
                  </div>
                </div>
              </td>

              <!-- Route -->
              <td>
                <div style="font-size:var(--text-xs);color:var(--text-secondary)">{{ $ride->fromZone->name }}</div>
                <div style="font-size:10px;color:var(--clr-gold-mid);margin:2px 0">↓</div>
                <div style="font-size:var(--text-xs);color:var(--text-secondary)">{{ $ride->toZone->name }}</div>
              </td>

              <!-- Seats -->
              <td>
                <div class="seats-bar-wrap">
                  <span style="font-family:var(--font-mono);font-size:var(--text-xs);font-weight:var(--fw-semi)">
                    {{ $used }}/{{ $ride->total_seats }}
                  </span>
                  <div class="seats-bar">
                    <div class="seats-bar-fill {{ $isFull ? 'is-full' : '' }}"
                         style="width:{{ $pct }}%">
                    </div>
                  </div>
                </div>
              </td>

              <!-- Riders -->
              <td>
                <div style="font-size:var(--text-xs)">
                  @if($accepted > 0)
                    <div style="color:var(--clr-success)">
                      <i class="fa-solid fa-circle-check" style="font-size:10px"></i>
                      {{ $accepted }} accepted
                    </div>
                  @endif
                  @if($pending > 0)
                    <div style="color:var(--clr-warning)">
                      <i class="fa-solid fa-clock" style="font-size:10px"></i>
                      {{ $pending }} pending
                    </div>
                  @endif
                  @if($accepted === 0 && $pending === 0)
                    <span style="color:var(--text-muted)">None</span>
                  @endif
                </div>
              </td>

              <!-- Posted -->
              <td style="font-size:var(--text-xs);color:var(--text-muted);white-space:nowrap">
                {{ $ride->created_at->format('M j, g:i A') }}
              </td>

              <!-- Status -->
              <td>
                <span class="badge-chariot {{ $ride->status }}">{{ $ride->status }}</span>
              </td>

              <!-- Actions -->
              <td style="text-align:right;white-space:nowrap">
                @if(in_array($ride->status, ['active','full']))
                  <button
                    class="tbl-btn deactivate"
                    onclick="AdminRides.cancel({{ $ride->id }}, this)"
                  >
                    <i class="fa-solid fa-ban"></i> Cancel
                  </button>
                @else
                  <span style="font-size:var(--text-xs);color:var(--text-muted)">—</span>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" style="text-align:center;padding:var(--sp-10);color:var(--text-muted)">
                <i class="fa-solid fa-route" style="font-size:2rem;opacity:.2;display:block;margin-bottom:var(--sp-3)"></i>
                No rides found
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    @if($rides->hasPages())
      <div style="padding:var(--sp-3) var(--sp-5);border-top:1px solid var(--border-color);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:var(--sp-3)">
        <div class="text-xs text-muted-c">
          Showing {{ $rides->firstItem() }}–{{ $rides->lastItem() }} of {{ $rides->total() }}
        </div>
        {{ $rides->links('pagination::bootstrap-5') }}
      </div>
    @endif
  </div>

@endsection

@push('scripts')
<script>
window.AdminRides = {
  cancel(rideId, btn) {
    AdminConfirm.show({
      title:    'Cancel this ride?',
      body:     'The ride will be marked as cancelled. All pending requests will be declined and accepted riders notified.',
      iconType: 'danger',
      okLabel:  'Yes, Cancel Ride',
      okClass:  'btn-danger-c',
      onOk: async () => {
        Chariot.Buttons.setLoading(btn, true);
        try {
          await Chariot.Util.patch(`/admin/rides/${rideId}/cancel`);
          const row    = btn.closest('tr');
          const badge  = row?.querySelector('.badge-chariot');
          if (badge) {
            badge.className   = 'badge-chariot cancelled';
            badge.textContent = 'cancelled';
          }
          btn.remove();
          Chariot.Toast.success('Ride cancelled.', 'Done');
        } catch (err) {
          Chariot.Toast.error(err.message || 'Could not cancel ride.');
          Chariot.Buttons.setLoading(btn, false);
        }
      }
    });
  }
};
</script>
@endpush
<!-- ========================= [D] END ============================== -->