@extends('layouts.admin')

@section('title', 'Rides Monitor')
@section('page-title', 'Live Ride Monitor')

@section('content')

{{-- ── FILTER ────────────────────────────────────────────────────────── --}}
<div class="admin-table-card mb-4" style="padding:20px 24px;">
  <form method="GET" action="{{ route('admin.rides') }}" class="row g-2 align-items-end">
    <div class="col-sm-4">
      <label class="form-label" style="font-size:12px;font-weight:600;color:#888;">STATUS</label>
      <select name="status" class="form-select form-select-sm">
        <option value="">All Statuses</option>
        @foreach(['active','full','completed','cancelled'] as $s)
          <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
        @endforeach
      </select>
    </div>
    <div class="col-sm-2">
      <button type="submit" class="btn btn-sm w-100" style="background:#0D3B1F;color:#fff;">
        <i class="fa-solid fa-filter me-1"></i> Filter
      </button>
    </div>
    @if(request('status'))
      <div class="col-sm-2">
        <a href="{{ route('admin.rides') }}" class="btn btn-sm btn-outline-secondary w-100">Clear</a>
      </div>
    @endif
  </form>
</div>

{{-- ── RIDES TABLE ──────────────────────────────────────────────────── --}}
<div class="admin-table-card">
  <div class="admin-table-header">
    <div class="admin-table-title">
      <i class="fa-solid fa-car-side me-2"></i> Rides
      <span class="badge bg-secondary ms-2" style="font-size:12px;">{{ $rides->total() }}</span>
    </div>
  </div>

  <div class="table-responsive">
    <table class="chariot-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Driver</th>
          <th>Route</th>
          <th>Seats</th>
          <th>Status</th>
          <th>Departure</th>
          <th>Posted</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        @forelse($rides as $ride)
          <tr>
            <td style="color:#aaa;font-size:12px;">{{ $ride->id }}</td>
            <td>
              <div style="font-weight:600;font-size:13px;">{{ $ride->driver->name ?? '—' }}</div>
              <div style="font-size:11px;color:#999;">{{ $ride->driver->phone ?? '' }}</div>
            </td>
            <td>
              <div style="font-size:12px;">
                <span class="text-muted">{{ $ride->fromZone->name ?? '?' }}</span>
                <i class="fa-solid fa-arrow-right mx-1 text-muted" style="font-size:9px;"></i>
                <strong>{{ $ride->toZone->name ?? '?' }}</strong>
              </div>
            </td>
            <td>
              <div class="d-flex align-items-center gap-1">
                <div style="font-size:13px;font-weight:600;">{{ $ride->available_seats }}</div>
                <div style="font-size:11px;color:#aaa;">/ {{ $ride->total_seats }}</div>
              </div>
              <div style="font-size:10px;color:#aaa;">available</div>
            </td>
            <td><span class="badge bg-{{ $ride->statusClass() }}">{{ $ride->statusLabel() }}</span></td>
            <td style="font-size:12px;color:#666;">
              {{ $ride->departing_at ? $ride->departing_at->format('d M, H:i') : '—' }}
            </td>
            <td style="font-size:12px;color:#888;">{{ $ride->created_at->diffForHumans() }}</td>
            <td>
              @if(in_array($ride->status, ['active', 'full']))
                <form action="{{ route('admin.rides.cancel', $ride) }}" method="POST"
                      onsubmit="return confirm('Cancel this ride?')">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-danger">
                    <i class="fa-solid fa-ban me-1"></i>Cancel
                  </button>
                </form>
              @else
                <span style="font-size:12px;color:#ccc;">—</span>
              @endif
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8" class="text-center text-muted py-5">
              <i class="fa-solid fa-route fa-2x mb-2 d-block"></i>
              No rides found.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  @if($rides->hasPages())
    <div class="px-4 py-3 border-top">
      {{ $rides->links() }}
    </div>
  @endif
</div>
@endsection
