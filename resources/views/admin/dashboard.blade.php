@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard Overview')

@section('content')

{{-- ── STAT CARDS ─────────────────────────────────────────────────── --}}
<div class="row g-4 mb-4">

  <div class="col-6 col-lg-3">
    <div class="stat-card">
      <div class="stat-icon" style="background:#eaf7f0;">
        <i class="fa-solid fa-users" style="color:#18a15f;"></i>
      </div>
      <div class="stat-value">{{ $stats['totalUsers'] }}</div>
      <div class="stat-label">Total Users</div>
    </div>
  </div>

  <div class="col-6 col-lg-3">
    <div class="stat-card">
      <div class="stat-icon" style="background:#fef5e7;">
        <i class="fa-solid fa-car" style="color:#d4820a;"></i>
      </div>
      <div class="stat-value">{{ $stats['totalDrivers'] }}</div>
      <div class="stat-label">Registered Drivers</div>
    </div>
  </div>

  <div class="col-6 col-lg-3">
    <div class="stat-card">
      <div class="stat-icon" style="background:#e8f4fd;">
        <i class="fa-solid fa-route" style="color:#1a7fd4;"></i>
      </div>
      <div class="stat-value">{{ $stats['activeRides'] }}</div>
      <div class="stat-label">Active Rides</div>
    </div>
  </div>

  <div class="col-6 col-lg-3">
    <div class="stat-card">
      <div class="stat-icon" style="background:#fdedf0;">
        <i class="fa-solid fa-bell" style="color:#e53e5a;"></i>
      </div>
      <div class="stat-value">{{ $stats['todayRequests'] }}</div>
      <div class="stat-label">Today's Requests</div>
    </div>
  </div>

</div>

{{-- ── SECONDARY STATS ──────────────────────────────────────────────── --}}
<div class="row g-4 mb-4">
  <div class="col-6 col-lg-3">
    <div class="stat-card" style="border-left: 4px solid #C9A227;">
      <div class="stat-value" style="font-size:24px;">{{ $stats['pendingVerify'] }}</div>
      <div class="stat-label"><i class="fa-solid fa-user-check me-1 text-warning"></i> Pending Verifications</div>
    </div>
  </div>
  <div class="col-6 col-lg-3">
    <div class="stat-card" style="border-left: 4px solid #0D3B1F;">
      <div class="stat-value" style="font-size:24px;">{{ $stats['totalZones'] }}</div>
      <div class="stat-label"><i class="fa-solid fa-map-pin me-1 text-success"></i> Camp Zones</div>
    </div>
  </div>
</div>

{{-- ── TWO-COLUMN: Recent Rides + Recent Users ─────────────────────── --}}
<div class="row g-4">

  {{-- Recent Rides --}}
  <div class="col-lg-7">
    <div class="admin-table-card">
      <div class="admin-table-header">
        <div class="admin-table-title"><i class="fa-solid fa-route me-2 text-success"></i>Recent Rides</div>
        <a href="{{ route('admin.rides') }}" class="btn btn-sm btn-outline-secondary">View All</a>
      </div>
      <div class="table-responsive">
        <table class="chariot-table">
          <thead>
            <tr>
              <th>Driver</th>
              <th>Route</th>
              <th>Seats</th>
              <th>Status</th>
              <th>Time</th>
            </tr>
          </thead>
          <tbody>
            @forelse($recentRides as $ride)
              <tr>
                <td><strong>{{ $ride->driver->name ?? '—' }}</strong></td>
                <td>
                  <span class="text-muted" style="font-size:12px;">{{ $ride->fromZone->name ?? '?' }}</span>
                  <i class="fa-solid fa-arrow-right mx-1 text-muted" style="font-size:10px;"></i>
                  <span style="font-size:12px;">{{ $ride->toZone->name ?? '?' }}</span>
                </td>
                <td>{{ $ride->available_seats }}/{{ $ride->total_seats }}</td>
                <td>
                  <span class="badge bg-{{ $ride->statusClass() }}">{{ $ride->statusLabel() }}</span>
                </td>
                <td style="font-size:12px;color:#888;">{{ $ride->created_at->diffForHumans() }}</td>
              </tr>
            @empty
              <tr><td colspan="5" class="text-center text-muted py-4">No rides yet.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  {{-- Recent Users --}}
  <div class="col-lg-5">
    <div class="admin-table-card">
      <div class="admin-table-header">
        <div class="admin-table-title"><i class="fa-solid fa-users me-2 text-primary"></i>New Members</div>
        <a href="{{ route('admin.users') }}" class="btn btn-sm btn-outline-secondary">View All</a>
      </div>
      <div class="table-responsive">
        <table class="chariot-table">
          <thead>
            <tr><th>Name</th><th>Role</th><th>Status</th></tr>
          </thead>
          <tbody>
            @forelse($recentUsers as $user)
              <tr>
                <td>
                  <div style="font-weight:600;font-size:13px;">{{ $user->name }}</div>
                  <div style="font-size:11px;color:#999;">{{ $user->phone }}</div>
                </td>
                <td><span class="badge-role badge-{{ $user->role }}">{{ ucfirst($user->role) }}</span></td>
                <td>
                  @if($user->is_verified)
                    <span class="badge bg-success-subtle text-success" style="font-size:11px;"><i class="fa-solid fa-check me-1"></i>Verified</span>
                  @else
                    <span class="badge bg-warning-subtle text-warning" style="font-size:11px;"><i class="fa-solid fa-clock me-1"></i>Pending</span>
                  @endif
                </td>
              </tr>
            @empty
              <tr><td colspan="3" class="text-center text-muted py-4">No users yet.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>
@endsection
