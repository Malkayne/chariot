<!-- ===================================================================
  [C]  admin/users.blade.php
  ====================================================================
  Cut everything between the [C] markers into:
      resources/views/admin/users.blade.php
  Route    : GET /admin/users        (AdminController@users)
  Middleware: auth, is.admin
  Data from controller:
      $users     — LengthAwarePaginator<User> (20/page, with driverProfile)
      $total     — int
      $roles     — ['rider','driver','admin']
  ================================================================== -->
<!-- ======================== [C] START ============================= -->
@extends('layouts.admin')

@section('title', 'Users')

@push('styles')
<style>
  /* ════════════════════════════════════════════
     FILTER BAR
     ════════════════════════════════════════════ */
  .users-filter-bar {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-card);
    padding: var(--sp-4) var(--sp-5);
    margin-bottom: var(--sp-4);
    display: flex;
    flex-wrap: wrap;
    gap: var(--sp-3);
    align-items: center;
  }

  .filter-search-wrap {
    flex: 1 1 200px;
    max-width: 340px;
    position: relative;
  }

  .filter-search-wrap .input-chariot {
    padding-left: 2.5rem;
    padding-top: var(--sp-2);
    padding-bottom: var(--sp-2);
    font-size: var(--text-sm);
  }

  .filter-search-wrap i {
    position: absolute;
    left: var(--sp-3);
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-muted);
    font-size: 0.85rem;
    pointer-events: none;
  }

  .filter-select-sm {
    flex: 1 1 130px;
    max-width: 180px;
    padding: var(--sp-2) var(--sp-4);
    padding-right: 2.25rem;
    font-size: var(--text-sm);
  }

  .filter-result-count {
    margin-left: auto;
    font-size: var(--text-xs);
    color: var(--text-muted);
    white-space: nowrap;
    align-self: center;
    flex-shrink: 0;
  }

  .filter-result-count strong { color: var(--text-primary); }

  @media (min-width: 1024px) {
    .users-filter-bar {
      flex-wrap: nowrap;
    }
    .filter-search-wrap {
      max-width: 280px;
    }
    .filter-select-sm {
      flex: 0 0 auto;
      width: 140px;
      min-width: 140px;
    }
  }

  /* ════════════════════════════════════════════
     USERS TABLE
     ════════════════════════════════════════════ */
  .users-table-wrap {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-card);
    overflow: hidden;
  }

  .admin-table {
    width: 100%;
    border-collapse: collapse;
    font-size: var(--text-sm);
  }

  .admin-table thead {
    background: var(--bg-card-alt);
    border-bottom: 2px solid var(--border-color);
  }

  .admin-table thead th {
    padding: var(--sp-3) var(--sp-4);
    font-size: var(--text-xs);
    font-weight: var(--fw-semi);
    color: var(--text-muted);
    text-transform: uppercase;
    letter-spacing: var(--ls-widest);
    text-align: left;
    white-space: nowrap;
  }

  .admin-table thead th button {
    background: none;
    border: none;
    color: inherit;
    font: inherit;
    letter-spacing: inherit;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 4px;
  }

  .admin-table tbody tr {
    border-bottom: 1px solid var(--border-color);
    transition: background 0.15s;
  }

  .admin-table tbody tr:last-child { border-bottom: none; }

  .admin-table tbody tr:hover { background: var(--bg-card-alt); }

  .admin-table td {
    padding: var(--sp-3) var(--sp-4);
    vertical-align: middle;
    color: var(--text-primary);
  }

  /* Sticky actions column */
  .admin-table .td-actions {
    white-space: nowrap;
    text-align: right;
  }

  /* Name cell */
  .td-user-name {
    display: flex;
    align-items: center;
    gap: var(--sp-3);
    min-width: 180px;
  }

  .td-user-details { min-width: 0; }

  .td-user-name-text {
    font-weight: var(--fw-medium);
    color: var(--text-primary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 160px;
  }

  .td-user-phone {
    font-size: var(--text-xs);
    color: var(--text-muted);
    font-family: var(--font-mono);
  }

  /* Table action buttons */
  .tbl-btn {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 10px;
    border-radius: var(--radius-sm);
    font-size: var(--text-xs);
    font-weight: var(--fw-semi);
    border: 1.5px solid transparent;
    cursor: pointer;
    background: none;
    transition: var(--transition-fast);
    white-space: nowrap;
  }

  .tbl-btn.verify {
    color: var(--clr-success-dark);
    border-color: rgba(34,197,94,0.35);
  }
  .tbl-btn.verify:hover { background: var(--clr-success-tint); }

  .tbl-btn.deactivate {
    color: var(--clr-danger);
    border-color: rgba(239,68,68,0.35);
  }
  .tbl-btn.deactivate:hover { background: var(--clr-danger-tint); }

  .tbl-btn.activate {
    color: var(--clr-info);
    border-color: rgba(59,130,246,0.35);
  }
  .tbl-btn.activate:hover { background: var(--clr-info-tint); }

  .tbl-btn.promote {
    color: var(--clr-gold-dark);
    border-color: rgba(201,162,39,0.35);
  }
  .tbl-btn.promote:hover { background: var(--clr-gold-pale); }

  [data-theme="dark"] .tbl-btn.verify:hover     { background: rgba(34,197,94,0.1); }
  [data-theme="dark"] .tbl-btn.deactivate:hover { background: rgba(239,68,68,0.1); }
  [data-theme="dark"] .tbl-btn.activate:hover   { background: rgba(59,130,246,0.1); }
  [data-theme="dark"] .tbl-btn.promote:hover    { background: rgba(201,162,39,0.08); }

  /* ── TABLE MOBILE SCROLL ── */
  .users-table-scroll { overflow-x: auto; }
  @media (max-width: 767px) { .admin-table { min-width: 700px; } }

  /* ── PAGINATION ── */
  .admin-table-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: var(--sp-3) var(--sp-5);
    border-top: 1px solid var(--border-color);
    flex-wrap: wrap;
    gap: var(--sp-3);
  }

  .admin-table-footer .page-link {
    background: var(--bg-card);
    border-color: var(--border-color);
    color: var(--text-secondary);
    font-size: var(--text-sm);
    border-radius: var(--radius-md) !important;
    padding: var(--sp-2) var(--sp-3);
  }

  .admin-table-footer .page-item.active .page-link {
    background: var(--clr-green-deep);
    border-color: var(--clr-green-deep);
    color: white;
  }
</style>
@endpush

@section('content')

  <!-- Breadcrumb -->
  <div class="admin-breadcrumb">
    <a href="{{ route('admin.dashboard') }}"><i class="fa-solid fa-gauge-high" style="color:var(--clr-gold-mid)"></i></a>
    <span class="admin-breadcrumb-sep">/</span>
    <span class="admin-breadcrumb-current">Users</span>
    <span class="ms-auto text-xs text-muted-c">
      <strong>{{ $total ?? 0 }}</strong> total users
    </span>
  </div>

  <!-- Page header -->
  <div class="admin-page-header">
    <div>
      <div class="admin-page-title">User Management</div>
      <div class="admin-page-sub">Verify members, manage roles and account status.</div>
    </div>
  </div>

  <!-- ════════════════════════════════════════
       FILTER BAR
       ════════════════════════════════════════ -->
  <div class="users-filter-bar">
    <div class="filter-search-wrap">
      <i class="fa-solid fa-magnifying-glass"></i>
      <input
        type="search"
        class="input-chariot"
        id="adminUserSearch"
        placeholder="Search name or phone…"
        autocomplete="off"
        aria-label="Search users"
      >
    </div>

    <select class="select-chariot filter-select-sm" id="adminRoleFilter" aria-label="Filter by role">
      <option value="">All roles</option>
      <option value="rider">Rider</option>
      <option value="driver">Driver</option>
      <option value="admin">Admin</option>
    </select>

    <select class="select-chariot filter-select-sm" id="adminStatusFilter" aria-label="Filter by status">
      <option value="">All status</option>
      <option value="1">Active</option>
      <option value="0">Inactive</option>
    </select>

    <select class="select-chariot filter-select-sm" id="adminVerifyFilter" aria-label="Filter by verification">
      <option value="">All</option>
      <option value="1">Verified</option>
      <option value="0">Unverified</option>
    </select>

    <div class="filter-result-count">
      Showing <strong id="visibleUserCount">{{ $users->count() }}</strong>
      of <strong>{{ $total ?? 0 }}</strong>
    </div>
  </div>

  <!-- ════════════════════════════════════════
       USERS TABLE
       ════════════════════════════════════════ -->
  <div class="users-table-wrap">
    <div class="users-table-scroll">
      <table class="admin-table" id="usersTable">
        <thead>
          <tr>
            <th>User</th>
            <th>Role</th>
            <th>Verified</th>
            <th>Status</th>
            <th>Joined</th>
            <th style="text-align:right">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($users as $user)
            <tr class="admin-user-row"
                data-id="{{ $user->id }}"
                data-name="{{ strtolower($user->name) }}"
                data-phone="{{ $user->phone }}"
                data-role="{{ $user->role }}"
                data-active="{{ $user->is_active ? '1' : '0' }}"
                data-verified="{{ $user->is_verified ? '1' : '0' }}">

              <!-- Name cell -->
              <td>
                <div class="td-user-name">
                  <div class="chariot-avatar avatar-sm">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                  </div>
                  <div class="td-user-details">
                    <div class="td-user-name-text" title="{{ $user->name }}">
                      {{ $user->name }}
                    </div>
                    <div class="td-user-phone">{{ $user->phone }}</div>
                  </div>
                </div>
              </td>

              <!-- Role -->
              <td>
                <span class="driver-badge user-role-cell">
                  {{ ucfirst($user->role) }}
                </span>
              </td>

              <!-- Verified -->
              <td class="user-verified-cell">
                @if($user->is_verified)
                  <span class="verified-badge">
                    <i class="fa-solid fa-circle-check"></i> Verified
                  </span>
                @else
                  <span class="badge-chariot pending" style="font-size:10px">
                    Pending
                  </span>
                @endif
              </td>

              <!-- Active status -->
              <td>
                <div style="display:flex;align-items:center;gap:6px">
                  <span class="status-dot user-active-badge {{ $user->is_active ? 'online' : 'offline' }}"
                        title="{{ $user->is_active ? 'Active' : 'Inactive' }}">
                  </span>
                  <span style="font-size:var(--text-xs);color:var(--text-secondary)">
                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                  </span>
                </div>
              </td>

              <!-- Joined -->
              <td style="font-size:var(--text-xs);color:var(--text-muted);white-space:nowrap">
                {{ $user->created_at->format('M j, Y') }}
              </td>

              <!-- Actions -->
              <td class="td-actions">
                <div style="display:flex;gap:var(--sp-1);justify-content:flex-end;flex-wrap:wrap">
                  @if($user->id !== auth()->id())
                    @if(!$user->is_verified)
                      <button
                        class="tbl-btn verify"
                        onclick="AdminUsers.verify({{ $user->id }}, this)"
                        title="Verify member"
                      >
                        <i class="fa-solid fa-circle-check"></i> Verify
                      </button>
                    @endif

                    @if($user->is_active)
                      <button
                        class="tbl-btn deactivate"
                        onclick="AdminUsers.toggleActive({{ $user->id }}, this)"
                        title="Deactivate account"
                      >
                        <i class="fa-solid fa-ban"></i> Deactivate
                      </button>
                    @else
                      <button
                        class="tbl-btn activate"
                        onclick="AdminUsers.toggleActive({{ $user->id }}, this)"
                        title="Reactivate account"
                      >
                        <i class="fa-solid fa-circle-play"></i> Activate
                      </button>
                    @endif

                    @if($user->role === 'rider')
                      <button
                        class="tbl-btn promote"
                        onclick="AdminUsers.makeDriver({{ $user->id }}, this)"
                        title="Promote to Driver"
                      >
                        <i class="fa-solid fa-car"></i> → Driver
                      </button>
                    @endif
                  @else
                    <span style="font-size:var(--text-xs);color:var(--text-muted);font-style:italic;padding:var(--sp-1) var(--sp-2)">
                      Current User
                    </span>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" style="text-align:center;padding:var(--sp-10);color:var(--text-muted)">
                <i class="fa-solid fa-users" style="font-size:2rem;opacity:.2;display:block;margin-bottom:var(--sp-3)"></i>
                No users found
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <!-- Pagination footer -->
    @if($users->hasPages())
      <div class="admin-table-footer">
        <div class="text-xs text-muted-c">
          Showing {{ $users->firstItem() }}–{{ $users->lastItem() }} of {{ $users->total() }}
        </div>
        {{ $users->links('pagination::bootstrap-5') }}
      </div>
    @endif
  </div>

@endsection

@push('scripts')
<script>
/* ================================================================
   admin/users.blade.php — page script
   ================================================================ */

document.addEventListener('DOMContentLoaded', function () {

  /* ── CLIENT-SIDE FILTER ── */
  const searchEl  = document.getElementById('adminUserSearch');
  const roleEl    = document.getElementById('adminRoleFilter');
  const statusEl  = document.getElementById('adminStatusFilter');
  const verifyEl  = document.getElementById('adminVerifyFilter');
  const countEl   = document.getElementById('visibleUserCount');

  const filterRows = Chariot.Util.debounce(() => {
    const q       = (searchEl?.value || '').toLowerCase().trim();
    const role    = roleEl?.value   || '';
    const status  = statusEl?.value || '';
    const verify  = verifyEl?.value || '';
    let   visible = 0;

    document.querySelectorAll('.admin-user-row').forEach(row => {
      const nameMatch   = !q      || row.dataset.name.includes(q) || row.dataset.phone.includes(q);
      const roleMatch   = !role   || row.dataset.role    === role;
      const statusMatch = !status || row.dataset.active   === status;
      const verifyMatch = !verify || row.dataset.verified === verify;
      const show        = nameMatch && roleMatch && statusMatch && verifyMatch;

      row.style.display = show ? '' : 'none';
      if (show) visible++;
    });

    if (countEl) countEl.textContent = visible;
  }, 250);

  searchEl?.addEventListener('input',  filterRows);
  roleEl?.addEventListener('change',   filterRows);
  statusEl?.addEventListener('change', filterRows);
  verifyEl?.addEventListener('change', filterRows);
});


/* ── ADMIN USERS ACTIONS (global scope for onclick) ── */
window.AdminUsers = {

  verify(userId, btn) {
    AdminConfirm.show({
      title:    'Verify this member?',
      body:     'This marks the user as a verified RCCG member. This action cannot be undone.',
      iconType: 'success',
      okLabel:  'Verify',
      okClass:  'btn-secondary-c',
      onOk: async () => {
        Chariot.Buttons.setLoading(btn, true);
        try {
          await Chariot.Util.patch(`/admin/users/${userId}/verify`);
          const row = btn.closest('tr');

          /* Replace verify button with badge */
          btn.replaceWith((() => {
            const b = document.createElement('span');
            b.className = 'verified-badge';
            b.innerHTML = '<i class="fa-solid fa-circle-check"></i> Verified';
            return b;
          })());

          /* Update verified cell */
          const vCell = row?.querySelector('.user-verified-cell');
          if (vCell) {
            vCell.innerHTML = '<span class="verified-badge"><i class="fa-solid fa-circle-check"></i> Verified</span>';
          }

          /* Update data attr */
          row?.setAttribute('data-verified', '1');

          Chariot.Toast.success('Member verified.', 'Done');
        } catch (err) {
          Chariot.Toast.error(err.message || 'Could not verify member.');
          Chariot.Buttons.setLoading(btn, false);
        }
      }
    });
  },

  toggleActive(userId, btn) {
    const row      = btn.closest('tr');
    const isActive = row?.dataset.active === '1';

    AdminConfirm.show({
      title:    isActive ? 'Deactivate this user?' : 'Activate this user?',
      body:     isActive
        ? 'The user will be locked out of their account immediately.'
        : 'The user will be able to log in again.',
      iconType: isActive ? 'danger' : 'success',
      okLabel:  isActive ? 'Deactivate' : 'Activate',
      okClass:  isActive ? 'btn-danger-c' : 'btn-secondary-c',
      onOk: async () => {
        Chariot.Buttons.setLoading(btn, true);
        try {
          const data = await Chariot.Util.patch(`/admin/users/${userId}/toggle`);
          const nowActive = data.is_active;

          row?.setAttribute('data-active', nowActive ? '1' : '0');

          /* Update status dot */
          const dot  = row?.querySelector('.user-active-badge');
          const label = dot?.nextElementSibling;
          if (dot) {
            dot.className = `status-dot user-active-badge ${nowActive ? 'online' : 'offline'}`;
            dot.title     = nowActive ? 'Active' : 'Inactive';
          }
          if (label) label.textContent = nowActive ? 'Active' : 'Inactive';

          /* Swap button */
          btn.className   = `tbl-btn ${nowActive ? 'deactivate' : 'activate'}`;
          btn.title       = nowActive ? 'Deactivate account' : 'Reactivate account';
          btn.innerHTML   = nowActive
            ? '<i class="fa-solid fa-ban"></i> Deactivate'
            : '<i class="fa-solid fa-circle-play"></i> Activate';
          btn.setAttribute('onclick', `AdminUsers.toggleActive(${userId}, this)`);

          Chariot.Toast.success(`User ${nowActive ? 'activated' : 'deactivated'}.`);
        } catch (err) {
          Chariot.Toast.error(err.message || 'Action failed.');
        } finally {
          Chariot.Buttons.setLoading(btn, false);
        }
      }
    });
  },

  makeDriver(userId, btn) {
    AdminConfirm.show({
      title:    'Promote to Driver?',
      body:     'The user will gain driver access and can register a vehicle. Their role changes to "driver".',
      iconType: 'warn',
      okLabel:  'Promote',
      okClass:  'btn-primary-c',
      onOk: async () => {
        Chariot.Buttons.setLoading(btn, true);
        try {
          await Chariot.Util.patch(`/admin/users/${userId}/role`, { role: 'driver' });
          const row = btn.closest('tr');

          /* Update role badge */
          const roleCell = row?.querySelector('.user-role-cell');
          if (roleCell) roleCell.textContent = 'Driver';
          row?.setAttribute('data-role', 'driver');

          /* Remove promote button */
          btn.remove();

          Chariot.Toast.success('User promoted to driver.');
        } catch (err) {
          Chariot.Toast.error(err.message || 'Could not update role.');
          Chariot.Buttons.setLoading(btn, false);
        }
      }
    });
  }
};
</script>
@endpush
<!-- ========================= [C] END ============================== -->