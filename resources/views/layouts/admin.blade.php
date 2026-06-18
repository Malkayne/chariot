<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Admin') — Chariot Admin</title>

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

  <!-- Bootstrap 5 -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css">

  <!-- Font Awesome 6 -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  <style>
    :root {
      --forest: #0D3B1F;
      --gold: #C9A227;
      --off-white: #F5F0E8;
      --dark: #0A0A0A;
      --sidebar-w: 260px;
    }
    * { box-sizing: border-box; }
    body { margin: 0; font-family: 'DM Sans', sans-serif; background: #f4f6f9; color: #333; }

    /* Sidebar */
    .admin-sidebar {
      position: fixed; top: 0; left: 0; height: 100vh; width: var(--sidebar-w);
      background: var(--forest); display: flex; flex-direction: column;
      z-index: 100; overflow-y: auto;
    }
    .admin-sidebar-brand {
      padding: 24px 20px; border-bottom: 1px solid rgba(201,162,39,.2);
      text-decoration: none; display: flex; align-items: center; gap: 12px;
    }
    .admin-brand-icon {
      width: 40px; height: 40px; background: var(--gold); border-radius: 10px;
      display: flex; align-items: center; justify-content: center;
      font-size: 18px; color: var(--forest); font-weight: 900;
    }
    .admin-brand-name { color: var(--gold); font-family: 'Cinzel', serif; font-size: 16px; font-weight: 700; }
    .admin-brand-sub  { color: rgba(245,240,232,.5); font-size: 11px; margin-top: 2px; }

    .admin-nav { padding: 16px 0; flex: 1; }
    .admin-nav-label {
      color: rgba(201,162,39,.5); font-size: 10px; text-transform: uppercase;
      letter-spacing: 1.5px; padding: 12px 20px 4px;
    }
    .admin-nav-item {
      display: flex; align-items: center; gap: 12px;
      padding: 11px 20px; color: rgba(245,240,232,.75); text-decoration: none;
      font-size: 14px; font-weight: 500; transition: all .15s;
      border-left: 3px solid transparent;
    }
    .admin-nav-item:hover { background: rgba(201,162,39,.08); color: var(--off-white); }
    .admin-nav-item.active { background: rgba(201,162,39,.12); color: var(--gold); border-left-color: var(--gold); }
    .admin-nav-item i { width: 18px; text-align: center; font-size: 15px; }

    .admin-sidebar-footer {
      padding: 16px 20px; border-top: 1px solid rgba(201,162,39,.2);
    }
    .admin-user-info { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
    .admin-avatar {
      width: 36px; height: 36px; border-radius: 50%;
      background: var(--gold); color: var(--forest);
      display: flex; align-items: center; justify-content: center;
      font-weight: 700; font-size: 13px;
    }
    .admin-user-name { color: var(--off-white); font-size: 13px; font-weight: 600; }
    .admin-user-role { color: rgba(245,240,232,.5); font-size: 11px; }
    .admin-logout-btn {
      width: 100%; background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.1);
      color: rgba(245,240,232,.7); padding: 8px 12px; border-radius: 8px;
      font-size: 13px; cursor: pointer; display: flex; align-items: center; gap: 8px;
      transition: all .15s;
    }
    .admin-logout-btn:hover { background: rgba(255,0,0,.15); color: #ff6b6b; border-color: rgba(255,0,0,.2); }

    /* Main content */
    .admin-main { margin-left: var(--sidebar-w); min-height: 100vh; }

    .admin-topbar {
      background: #fff; padding: 16px 32px; border-bottom: 1px solid #e8ecf0;
      display: flex; align-items: center; justify-content: space-between;
      position: sticky; top: 0; z-index: 50;
    }
    .admin-topbar-title { font-size: 18px; font-weight: 700; color: var(--forest); }
    .admin-topbar-right { display: flex; align-items: center; gap: 12px; }

    .admin-content { padding: 28px 32px; }

    /* Cards */
    .stat-card {
      background: #fff; border-radius: 14px; padding: 24px;
      border: 1px solid #edf0f3; transition: box-shadow .2s;
    }
    .stat-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,.06); }
    .stat-icon {
      width: 48px; height: 48px; border-radius: 12px;
      display: flex; align-items: center; justify-content: center; font-size: 20px;
    }
    .stat-value { font-size: 32px; font-weight: 700; color: var(--forest); line-height: 1; margin: 8px 0 4px; }
    .stat-label { font-size: 13px; color: #888; font-weight: 500; }

    /* Tables */
    .admin-table-card { background: #fff; border-radius: 14px; border: 1px solid #edf0f3; overflow: hidden; }
    .admin-table-header { padding: 20px 24px; border-bottom: 1px solid #f0f2f5; display: flex; align-items: center; justify-content: space-between; }
    .admin-table-title { font-size: 16px; font-weight: 700; color: var(--forest); }
    table.chariot-table { width: 100%; border-collapse: collapse; }
    table.chariot-table th { background: #f8fafc; padding: 12px 16px; text-align: left; font-size: 12px; font-weight: 600; color: #888; text-transform: uppercase; letter-spacing: .5px; border-bottom: 1px solid #f0f2f5; }
    table.chariot-table td { padding: 12px 16px; font-size: 14px; border-bottom: 1px solid #f8f9fb; vertical-align: middle; }
    table.chariot-table tr:last-child td { border-bottom: none; }
    table.chariot-table tr:hover td { background: #fcfcfd; }

    /* Badges */
    .badge-role { padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
    .badge-rider  { background: #e8f4fd; color: #1a7fd4; }
    .badge-driver { background: #eaf7f0; color: #18a15f; }
    .badge-admin  { background: #fef5e7; color: #d4820a; }

    /* Alerts */
    .flash-alert { border-radius: 10px; font-size: 14px; }

    @media (max-width: 768px) {
      .admin-sidebar { width: 220px; transform: translateX(-100%); transition: transform .3s; }
      .admin-sidebar.open { transform: translateX(0); }
      .admin-main { margin-left: 0; }
      .admin-content { padding: 20px 16px; }
    }
  </style>
  @stack('styles')
</head>
<body>

  <!-- SIDEBAR -->
  <aside class="admin-sidebar" id="adminSidebar">
    <a href="{{ route('admin.dashboard') }}" class="admin-sidebar-brand">
      <div class="admin-brand-icon">C</div>
      <div>
        <div class="admin-brand-name">CHARIOT</div>
        <div class="admin-brand-sub">Admin Panel</div>
      </div>
    </a>

    <nav class="admin-nav">
      <div class="admin-nav-label">Overview</div>
      <a href="{{ route('admin.dashboard') }}"
         class="admin-nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
        <i class="fa-solid fa-chart-pie"></i> Dashboard
      </a>

      <div class="admin-nav-label">Management</div>
      <a href="{{ route('admin.users') }}"
         class="admin-nav-item {{ request()->routeIs('admin.users') ? 'active' : '' }}">
        <i class="fa-solid fa-users"></i> Users
      </a>
      <a href="{{ route('admin.rides') }}"
         class="admin-nav-item {{ request()->routeIs('admin.rides') ? 'active' : '' }}">
        <i class="fa-solid fa-car-side"></i> Rides
      </a>
      <a href="{{ route('admin.zones') }}"
         class="admin-nav-item {{ request()->routeIs('admin.zones') ? 'active' : '' }}">
        <i class="fa-solid fa-map-pin"></i> Zones
      </a>

      <div class="admin-nav-label">Platform</div>
      <a href="{{ route('rider.home') }}" class="admin-nav-item" target="_blank">
        <i class="fa-solid fa-arrow-up-right-from-square"></i> View App
      </a>
    </nav>

    <div class="admin-sidebar-footer">
      <div class="admin-user-info">
        <div class="admin-avatar">{{ auth()->user()->avatarInitials() }}</div>
        <div>
          <div class="admin-user-name">{{ auth()->user()->name }}</div>
          <div class="admin-user-role">Administrator</div>
        </div>
      </div>
      <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button type="submit" class="admin-logout-btn">
          <i class="fa-solid fa-right-from-bracket"></i> Sign Out
        </button>
      </form>
    </div>
  </aside>

  <!-- MAIN CONTENT -->
  <div class="admin-main">
    <div class="admin-topbar">
      <div class="d-flex align-items-center gap-3">
        <button class="btn btn-sm btn-light d-md-none" onclick="document.getElementById('adminSidebar').classList.toggle('open')">
          <i class="fa-solid fa-bars"></i>
        </button>
        <div class="admin-topbar-title">@yield('page-title', 'Dashboard')</div>
      </div>
      <div class="admin-topbar-right">
        <span class="text-muted" style="font-size:13px;">
          <i class="fa-regular fa-clock me-1"></i>{{ now()->format('D, d M Y') }}
        </span>
      </div>
    </div>

    <div class="admin-content">
      {{-- Flash Messages --}}
      @foreach(['success' => 'success', 'error' => 'danger', 'info' => 'info', 'warning' => 'warning'] as $type => $class)
        @if(session($type))
          <div class="alert alert-{{ $class }} alert-dismissible flash-alert mb-4" role="alert">
            {{ session($type) }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        @endif
      @endforeach

      @yield('content')
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
  @stack('scripts')
</body>
</html>
