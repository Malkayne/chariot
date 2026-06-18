@extends('layouts.admin')

@section('title', 'Users')
@section('page-title', 'User Management')

@section('content')

{{-- ── FILTERS ───────────────────────────────────────────────────────── --}}
<div class="admin-table-card mb-4" style="padding:20px 24px;">
  <form method="GET" action="{{ route('admin.users') }}" class="row g-2 align-items-end">
    <div class="col-sm-4">
      <label class="form-label" style="font-size:12px;font-weight:600;color:#888;">SEARCH</label>
      <input type="text" name="search" class="form-control form-control-sm"
             placeholder="Name, phone or email…" value="{{ request('search') }}">
    </div>
    <div class="col-sm-3">
      <label class="form-label" style="font-size:12px;font-weight:600;color:#888;">ROLE</label>
      <select name="role" class="form-select form-select-sm">
        <option value="">All Roles</option>
        <option value="rider"  {{ request('role') === 'rider'  ? 'selected' : '' }}>Rider</option>
        <option value="driver" {{ request('role') === 'driver' ? 'selected' : '' }}>Driver</option>
        <option value="admin"  {{ request('role') === 'admin'  ? 'selected' : '' }}>Admin</option>
      </select>
    </div>
    <div class="col-sm-3">
      <div class="form-check mt-3">
        <input type="checkbox" class="form-check-input" name="unverified" id="unverifiedCheck"
               {{ request('unverified') ? 'checked' : '' }}>
        <label class="form-check-label" for="unverifiedCheck" style="font-size:13px;">
          Unverified only
        </label>
      </div>
    </div>
    <div class="col-sm-2">
      <button type="submit" class="btn btn-sm w-100" style="background:#0D3B1F;color:#fff;">
        <i class="fa-solid fa-magnifying-glass me-1"></i> Filter
      </button>
    </div>
  </form>
</div>

{{-- ── USERS TABLE ──────────────────────────────────────────────────── --}}
<div class="admin-table-card">
  <div class="admin-table-header">
    <div class="admin-table-title">
      <i class="fa-solid fa-users me-2"></i>
      Users <span class="badge bg-secondary ms-2" style="font-size:12px;">{{ $users->total() }}</span>
    </div>
  </div>
  <div class="table-responsive">
    <table class="chariot-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Member</th>
          <th>Phone</th>
          <th>Role</th>
          <th>Verified</th>
          <th>Active</th>
          <th>Joined</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($users as $user)
          <tr>
            <td style="color:#aaa;font-size:12px;">{{ $user->id }}</td>
            <td>
              <div style="font-weight:600;font-size:14px;">{{ $user->name }}</div>
              <div style="font-size:11px;color:#999;">{{ $user->email ?? 'No email' }}</div>
              @if($user->rccg_member_id)
                <div style="font-size:10px;color:#bbb;">ID: {{ $user->rccg_member_id }}</div>
              @endif
            </td>
            <td style="font-size:13px;">{{ $user->phone }}</td>
            <td><span class="badge-role badge-{{ $user->role }}">{{ ucfirst($user->role) }}</span></td>
            <td>
              @if($user->is_verified)
                <span class="text-success"><i class="fa-solid fa-circle-check"></i></span>
              @else
                <span class="text-danger"><i class="fa-solid fa-circle-xmark"></i></span>
              @endif
            </td>
            <td>
              @if($user->is_active)
                <span class="text-success"><i class="fa-solid fa-circle-check"></i></span>
              @else
                <span class="text-danger"><i class="fa-solid fa-circle-xmark"></i></span>
              @endif
            </td>
            <td style="font-size:12px;color:#888;">{{ $user->created_at->format('d M Y') }}</td>
            <td>
              <div class="dropdown">
                <button class="btn btn-sm btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                  Actions
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                  {{-- Verify --}}
                  @unless($user->is_verified)
                    <li>
                      <form action="{{ route('admin.users.verify', $user) }}" method="POST">
                        @csrf @method('PATCH')
                        <button type="submit" class="dropdown-item text-success">
                          <i class="fa-solid fa-user-check me-2"></i>Verify Member
                        </button>
                      </form>
                    </li>
                  @endunless

                  {{-- Toggle active --}}
                  <li>
                    <form action="{{ route('admin.users.toggle', $user) }}" method="POST">
                      @csrf @method('PATCH')
                      <button type="submit" class="dropdown-item {{ $user->is_active ? 'text-danger' : 'text-success' }}">
                        <i class="fa-solid fa-{{ $user->is_active ? 'ban' : 'circle-play' }} me-2"></i>
                        {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                      </button>
                    </form>
                  </li>

                  {{-- Change role --}}
                  <li><hr class="dropdown-divider"></li>
                  <li><span class="dropdown-item-text text-muted" style="font-size:11px;">Change Role</span></li>
                  @foreach(['rider', 'driver', 'admin'] as $r)
                    @if($user->role !== $r)
                      <li>
                        <form action="{{ route('admin.users.role', $user) }}" method="POST">
                          @csrf @method('PATCH')
                          <input type="hidden" name="role" value="{{ $r }}">
                          <button type="submit" class="dropdown-item">
                            <i class="fa-solid fa-arrow-right me-2"></i>Make {{ ucfirst($r) }}
                          </button>
                        </form>
                      </li>
                    @endif
                  @endforeach
                </ul>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="8" class="text-center text-muted py-5">
              <i class="fa-solid fa-users-slash fa-2x mb-2 d-block"></i>
              No users found matching your filters.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  @if($users->hasPages())
    <div class="px-4 py-3 border-top">
      {{ $users->links() }}
    </div>
  @endif
</div>
@endsection
