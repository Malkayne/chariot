@extends('layouts.admin')

@section('title', 'Zone Management')
@section('page-title', 'Camp Zones')

@section('content')

<div class="row g-4">

  {{-- ── ADD ZONE FORM ─────────────────────────────────────────────── --}}
  <div class="col-lg-4">
    <div class="admin-table-card p-4">
      <h6 style="font-weight:700;color:#0D3B1F;margin-bottom:20px;">
        <i class="fa-solid fa-map-pin me-2 text-warning"></i>Add New Zone
      </h6>

      <form action="{{ route('admin.zones.store') }}" method="POST">
        @csrf

        <div class="mb-3">
          <label class="form-label" style="font-size:12px;font-weight:600;color:#888;">ZONE NAME <span class="text-danger">*</span></label>
          <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                 placeholder="e.g. Main Auditorium" value="{{ old('name') }}" required>
          @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
          <label class="form-label" style="font-size:12px;font-weight:600;color:#888;">SHORT CODE <span class="text-danger">*</span></label>
          <input type="text" name="short_code" class="form-control @error('short_code') is-invalid @enderror"
                 placeholder="e.g. MAIN_AUD" value="{{ old('short_code') }}"
                 style="font-family:monospace;" required>
          @error('short_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="row g-2 mb-3">
          <div class="col-6">
            <label class="form-label" style="font-size:12px;font-weight:600;color:#888;">LATITUDE <span class="text-danger">*</span></label>
            <input type="number" step="0.0000001" name="lat"
                   class="form-control @error('lat') is-invalid @enderror"
                   placeholder="6.892200" value="{{ old('lat') }}" required>
            @error('lat') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
          <div class="col-6">
            <label class="form-label" style="font-size:12px;font-weight:600;color:#888;">LONGITUDE <span class="text-danger">*</span></label>
            <input type="number" step="0.0000001" name="lng"
                   class="form-control @error('lng') is-invalid @enderror"
                   placeholder="3.718600" value="{{ old('lng') }}" required>
            @error('lng') <div class="invalid-feedback">{{ $message }}</div> @enderror
          </div>
        </div>

        <div class="mb-4">
          <label class="form-label" style="font-size:12px;font-weight:600;color:#888;">DESCRIPTION</label>
          <textarea name="description" class="form-control" rows="3"
                    placeholder="Optional description…">{{ old('description') }}</textarea>
        </div>

        <button type="submit" class="btn w-100" style="background:#0D3B1F;color:#C9A227;font-weight:600;">
          <i class="fa-solid fa-plus me-2"></i>Add Zone
        </button>
      </form>
    </div>
  </div>

  {{-- ── ZONES LIST ────────────────────────────────────────────────── --}}
  <div class="col-lg-8">
    <div class="admin-table-card">
      <div class="admin-table-header">
        <div class="admin-table-title">
          <i class="fa-solid fa-map-pin me-2 text-warning"></i>
          All Zones <span class="badge bg-secondary ms-2">{{ $zones->count() }}</span>
        </div>
      </div>
      <div class="table-responsive">
        <table class="chariot-table">
          <thead>
            <tr>
              <th>Name</th>
              <th>Code</th>
              <th>Coordinates</th>
              <th>Status</th>
              <th>Rides</th>
            </tr>
          </thead>
          <tbody>
            @forelse($zones as $zone)
              <tr>
                <td>
                  <div style="font-weight:600;font-size:13px;">{{ $zone->name }}</div>
                  @if($zone->description)
                    <div style="font-size:11px;color:#aaa;">{{ Str::limit($zone->description, 50) }}</div>
                  @endif
                </td>
                <td>
                  <code style="background:#f4f6f9;padding:2px 6px;border-radius:4px;font-size:11px;">
                    {{ $zone->short_code }}
                  </code>
                </td>
                <td style="font-size:11px;color:#888;font-family:monospace;">
                  {{ $zone->lat }}, {{ $zone->lng }}
                </td>
                <td>
                  @if($zone->is_active)
                    <span class="badge bg-success-subtle text-success">Active</span>
                  @else
                    <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                  @endif
                </td>
                <td style="font-size:13px;">
                  {{ $zone->ridesFrom->count() + $zone->ridesTo->count() }}
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="5" class="text-center text-muted py-5">
                  <i class="fa-solid fa-map fa-2x mb-2 d-block"></i>
                  No zones yet. Add your first zone.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>
@endsection
