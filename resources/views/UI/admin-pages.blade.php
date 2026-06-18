{{--
=======================================================================
  ADMIN PAGES — All 4 views + admin layout in one file
  Cut at the marked dividers into individual blade files when ready.
  =======================================================================
  FILE MAP
  ─────────────────────────────────────────────────────────────────────
  [A] layouts/admin.blade.php        — admin shell (wider sidebar,
                                        no bottom-nav, full content width)
  [B] admin/dashboard.blade.php      — stats overview + live rides table
                                        + recent requests timeline
  [C] admin/users.blade.php          — user list + search/filter +
                                        verify / toggle / role actions
  [D] admin/rides.blade.php          — all rides table + status filters
                                        + cancel action
  [E] admin/zones.blade.php          — zone/landmark CRUD + map preview
  =======================================================================
  MIDDLEWARE : auth, is.admin
  NO bottom-nav. NO realtime push (polling only). Wide sidebar (260px).
  =======================================================================
--}}




<!-- ===================================================================
  [A]  layouts/admin.blade.php
  ====================================================================
  Cut everything between the [A] markers into:
      resources/views/layouts/admin.blade.php
  ================================================================== -->
<!-- ======================== [A] START ============================= -->
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token"  content="{{ csrf_token() }}">
  <meta name="api-token"   content="{{ auth()->user()->createToken('admin-session')->plainTextToken }}">
  <meta name="user-id"     content="{{ auth()->id() }}">
  <meta name="user-role"   content="admin">

  <title>Admin — @yield('title', 'Dashboard') | Chariot</title>

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;900&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,300&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">

  <!-- Bootstrap 5 -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css">

  <!-- Font Awesome 6 -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  <!--
    Leaflet — only on admin/zones.blade.php (map preview).
    Injected via @stack('map-css').
  -->
  @stack('map-css')

  <!-- Chariot global CSS -->
  <link rel="stylesheet" href="{{ asset('css/chariot.css') }}">

  <!-- Admin layout overrides -->
  <style>
    /* ════════════════════════════════════════════
       ADMIN LAYOUT SHELL
       Wider sidebar (260px), no bottom-nav,
       full-width content area up to 1280px
       ════════════════════════════════════════════ */

    /* Admin sidebar is 260px wide */
    .chariot-sidebar { width: 260px; }

    @media (min-width: 768px) {
      .chariot-content { margin-left: 260px; }
    }

    /* Admin content stretches wider */
    .chariot-content-inner {
      max-width: 1280px;
      padding: var(--sp-6) var(--sp-5);
    }

    @media (min-width: 1400px) {
      .chariot-content-inner { padding: var(--sp-8); }
    }

    /* No bottom-nav space needed */
    .chariot-content { padding-bottom: var(--sp-10); }

    /* ── ADMIN SIDEBAR extras ── */
    .admin-sidebar-section {
      padding: var(--sp-5) var(--sp-5) var(--sp-1);
      font-size: 10px;
      font-weight: var(--fw-bold);
      letter-spacing: var(--ls-widest);
      text-transform: uppercase;
      color: rgba(255,255,255,0.18);
    }

    /* ── ADMIN TOPBAR ── */
    /* Breadcrumb row inside content area */
    .admin-breadcrumb {
      display: flex;
      align-items: center;
      gap: var(--sp-2);
      font-size: var(--text-xs);
      color: var(--text-muted);
      margin-bottom: var(--sp-5);
    }

    .admin-breadcrumb a {
      color: var(--text-muted);
      text-decoration: none;
    }

    .admin-breadcrumb a:hover { color: var(--clr-gold-mid); }

    .admin-breadcrumb-sep { color: var(--border-color); }

    .admin-breadcrumb-current {
      color: var(--text-primary);
      font-weight: var(--fw-medium);
    }

    /* ── ADMIN PAGE HEADER ── */
    .admin-page-header {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: var(--sp-4);
      margin-bottom: var(--sp-6);
      flex-wrap: wrap;
    }

    .admin-page-title {
      font-size: var(--text-2xl);
      font-weight: var(--fw-bold);
      color: var(--text-primary);
      letter-spacing: var(--ls-tight);
      margin-bottom: var(--sp-1);
    }

    .admin-page-sub {
      font-size: var(--text-sm);
      color: var(--text-secondary);
    }

    /* ── CONFIRM MODAL (shared) ── */
    .confirm-modal-backdrop {
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.6);
      z-index: 9000;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: var(--sp-4);
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.25s ease;
      backdrop-filter: blur(4px);
    }

    .confirm-modal-backdrop.is-open {
      opacity: 1;
      pointer-events: all;
    }

    .confirm-modal {
      background: var(--bg-card);
      border-radius: var(--radius-xl);
      border: 1px solid var(--border-color);
      padding: var(--sp-6);
      width: 100%;
      max-width: 400px;
      box-shadow: var(--shadow-xl);
      transform: scale(0.92);
      transition: transform 0.28s cubic-bezier(0.34,1.56,0.64,1);
    }

    .confirm-modal-backdrop.is-open .confirm-modal {
      transform: scale(1);
    }

    .confirm-modal-icon {
      width: 52px; height: 52px;
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-size: 1.4rem;
      margin-bottom: var(--sp-4);
    }

    .confirm-modal-icon.warn  { background: var(--clr-warning-tint); color: var(--clr-warning); }
    .confirm-modal-icon.danger { background: var(--clr-danger-tint);  color: var(--clr-danger); }
    .confirm-modal-icon.success{ background: var(--clr-success-tint); color: var(--clr-success); }

    .confirm-modal-title {
      font-size: var(--text-lg);
      font-weight: var(--fw-bold);
      color: var(--text-primary);
      margin-bottom: var(--sp-2);
    }

    .confirm-modal-body {
      font-size: var(--text-sm);
      color: var(--text-secondary);
      line-height: var(--lh-relaxed);
      margin-bottom: var(--sp-5);
    }

    .confirm-modal-actions {
      display: flex;
      gap: var(--sp-3);
    }
  </style>

  <!-- Per-page styles -->
  @stack('styles')
</head>

<body
  data-flash-success="{{ session('success') }}"
  data-flash-error="{{ session('error') }}"
>

  <!-- ── SIDEBAR OVERLAY (mobile) ── -->
  <div class="sidebar-overlay" id="sidebarOverlay"></div>

  <!-- ══════════════════════════════════════════════════════════
       ADMIN SIDEBAR
       ══════════════════════════════════════════════════════════ -->
  <aside class="chariot-sidebar admin-sidebar" id="chariotSidebar">

    <a href="{{ route('admin.dashboard') }}" class="sidebar-brand">
      <img src="{{ asset('images/chariot-logo.png') }}" alt="Chariot" width="36" height="36">
      <div>
        <div class="sidebar-brand-text">CHARIOT</div>
        <div class="sidebar-brand-tagline">Admin Panel</div>
      </div>
    </a>

    <ul class="sidebar-nav" role="navigation">

      <li class="admin-sidebar-section">Overview</li>

      <li>
        <a href="{{ route('admin.dashboard') }}"
           class="sidebar-nav-item {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}">
          <i class="fa-solid fa-gauge-high"></i> Dashboard
        </a>
      </li>

      <li class="admin-sidebar-section">Management</li>

      <li>
        <a href="{{ route('admin.users') }}"
           class="sidebar-nav-item {{ request()->routeIs('admin.users') ? 'is-active' : '' }}">
          <i class="fa-solid fa-users"></i> Users
          @php $pendingVerify = \App\Models\User::where('is_verified', false)->count(); @endphp
          @if($pendingVerify > 0)
            <span class="sidebar-badge">{{ $pendingVerify }}</span>
          @endif
        </a>
      </li>

      <li>
        <a href="{{ route('admin.rides') }}"
           class="sidebar-nav-item {{ request()->routeIs('admin.rides') ? 'is-active' : '' }}">
          <i class="fa-solid fa-car"></i> Rides
        </a>
      </li>

      <li>
        <a href="{{ route('admin.zones') }}"
           class="sidebar-nav-item {{ request()->routeIs('admin.zones') ? 'is-active' : '' }}">
          <i class="fa-solid fa-map-pin"></i> Zones
        </a>
      </li>

    </ul>

    <div class="sidebar-footer">
      <div class="sidebar-user">
        <div class="chariot-avatar avatar-sm">
          {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
        </div>
        <div>
          <div class="sidebar-user-name">{{ auth()->user()->name }}</div>
          <div class="sidebar-user-role">Administrator</div>
        </div>
      </div>
      <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button type="submit" class="sidebar-logout">
          <i class="fa-solid fa-right-from-bracket"></i> Logout
        </button>
      </form>
    </div>
  </aside>

  <!-- ══════════════════════════════════════════════════════════
       TOP NAVBAR
       ══════════════════════════════════════════════════════════ -->
  <nav class="chariot-navbar" id="mainNav" role="navigation">
    <div class="navbar-left">
      <button class="navbar-toggle" id="sidebarToggle" aria-label="Open menu">
        <i class="fa-solid fa-bars"></i>
      </button>
      <a href="{{ route('admin.dashboard') }}" class="navbar-brand-link">
        <img src="{{ asset('images/chariot-logo.png') }}" alt="Chariot" width="30" height="30">
        <span class="navbar-brand-name d-none d-sm-inline">CHARIOT</span>
      </a>
      <!-- Admin badge in navbar -->
      <span style="
        font-size:10px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;
        background:rgba(201,162,39,0.15);border:1px solid rgba(201,162,39,0.3);
        color:var(--clr-gold-mid);border-radius:var(--radius-pill);
        padding:2px 8px;flex-shrink:0;
      ">ADMIN</span>
    </div>

    <div class="navbar-right">
      <!-- Theme toggle -->
      <button class="navbar-icon-btn" id="themeToggle" aria-label="Toggle theme">
        <i class="fa-solid fa-moon fs-6" id="themeIcon"></i>
      </button>
      <!-- Avatar dropdown -->
      <div class="dropdown">
        <div class="chariot-avatar navbar-avatar"
             data-bs-toggle="dropdown" role="button" tabindex="0">
          {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
        </div>
        <ul class="dropdown-menu dropdown-menu-end chariot-dropdown mt-2">
          <li class="dropdown-header">
            <div class="user-name">{{ auth()->user()->name }}</div>
            <div class="user-role">Administrator</div>
          </li>
          <li><hr class="dropdown-divider m-1"></li>
          <li>
            <form action="{{ route('logout') }}" method="POST">
              @csrf
              <button type="submit" class="dropdown-item text-danger">
                <i class="fa-solid fa-right-from-bracket"></i> Logout
              </button>
            </form>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- ── TOAST CONTAINER ── -->
  <div class="toast-container-c" id="toastContainer" aria-live="polite"></div>

  <!-- ── SHARED CONFIRM MODAL ── -->
  <div class="confirm-modal-backdrop" id="confirmModalBackdrop">
    <div class="confirm-modal">
      <div class="confirm-modal-icon warn" id="confirmModalIcon">
        <i class="fa-solid fa-triangle-exclamation" id="confirmModalIconEl"></i>
      </div>
      <div class="confirm-modal-title" id="confirmModalTitle">Are you sure?</div>
      <div class="confirm-modal-body"  id="confirmModalBody">This action cannot be undone.</div>
      <div class="confirm-modal-actions">
        <button class="btn-chariot btn-ghost-c flex-1" id="confirmModalCancel">Cancel</button>
        <button class="btn-chariot btn-danger-c flex-1" id="confirmModalOk">Confirm</button>
      </div>
    </div>
  </div>

  <!-- ── MAIN CONTENT ── -->
  <main class="chariot-content" id="mainContent" role="main">
    <div class="chariot-content-inner">
      @yield('content')
    </div>
  </main>

  <!-- Bootstrap 5 JS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>

  <!-- Leaflet (zones page only) -->
  @stack('map-js')

  <!-- Chariot global JS -->
  <script src="{{ asset('js/chariot.js') }}"></script>

  <!-- Shared admin confirm-modal helper -->
  <script>
    /* ── Global confirm modal helper ── */
    /* Usage: AdminConfirm.show({ title, body, iconType, onOk }) */
    window.AdminConfirm = {
      _resolve: null,
      show({ title = 'Are you sure?', body = '', iconType = 'warn', okLabel = 'Confirm', okClass = 'btn-danger-c', onOk } = {}) {
        const backdrop = document.getElementById('confirmModalBackdrop');
        const iconEl   = document.getElementById('confirmModalIcon');
        const iconI    = document.getElementById('confirmModalIconEl');
        const titleEl  = document.getElementById('confirmModalTitle');
        const bodyEl   = document.getElementById('confirmModalBody');
        const okBtn    = document.getElementById('confirmModalOk');

        const iconMap = {
          warn:    { cls: 'warn',    icon: 'fa-triangle-exclamation' },
          danger:  { cls: 'danger',  icon: 'fa-circle-xmark' },
          success: { cls: 'success', icon: 'fa-circle-check' },
        };

        const t = iconMap[iconType] || iconMap.warn;
        iconEl.className      = `confirm-modal-icon ${t.cls}`;
        iconI.className       = `fa-solid ${t.icon}`;
        titleEl.textContent   = title;
        bodyEl.textContent    = body;
        okBtn.textContent     = okLabel;
        okBtn.className       = `btn-chariot ${okClass} flex-1`;

        backdrop.classList.add('is-open');
        Chariot.Util.lockScroll();

        const close = () => {
          backdrop.classList.remove('is-open');
          Chariot.Util.unlockScroll();
        };

        document.getElementById('confirmModalCancel').onclick = close;
        backdrop.onclick = (e) => { if (e.target === backdrop) close(); };
        okBtn.onclick = () => { close(); onOk?.(); };
      }
    };
  </script>

  <!-- Per-page scripts -->
  @stack('scripts')

</body>
</html>
<!-- ========================= [A] END ============================== -->




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
    align-items: flex-end;
  }

  .filter-search-wrap {
    flex: 1;
    min-width: 200px;
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
    padding: var(--sp-2) var(--sp-4);
    padding-right: 2.25rem;
    font-size: var(--text-sm);
    min-width: 130px;
  }

  .filter-result-count {
    margin-left: auto;
    font-size: var(--text-xs);
    color: var(--text-muted);
    white-space: nowrap;
    align-self: center;
  }

  .filter-result-count strong { color: var(--text-primary); }

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




<!-- ===================================================================
  [E]  admin/zones.blade.php
  ====================================================================
  Cut everything between the [E] markers into:
      resources/views/admin/zones.blade.php
  Route    : GET  /admin/zones       (AdminController@zones)
           : POST /admin/zones       (AdminController@storeZone)
           : PUT  /admin/zones/{id}  (AdminController@updateZone)
           : DEL  /admin/zones/{id}  (AdminController@deleteZone)
  Middleware: auth, is.admin
  Data from controller:
      $zones — Collection<Zone>  all zones ordered by name
  ================================================================== -->
<!-- ======================== [E] START ============================= -->
@extends('layouts.admin')

@section('title', 'Zones')

@push('map-css')
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
@endpush

@push('styles')
<style>
  /* ════════════════════════════════════════════
     ZONES PAGE LAYOUT — 2-col on lg
     ════════════════════════════════════════════ */
  .zones-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: var(--sp-5);
  }

  @media (min-width: 1024px) {
    .zones-grid { grid-template-columns: 1fr 1fr; }
  }

  /* ════════════════════════════════════════════
     ZONE FORM CARD
     ════════════════════════════════════════════ */
  .zone-form-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-card);
    overflow: hidden;
  }

  .zone-form-header {
    padding: var(--sp-4) var(--sp-5);
    border-bottom: 1px solid var(--border-color);
    background: var(--clr-gold-tint);
    display: flex;
    align-items: center;
    gap: var(--sp-2);
    font-size: var(--text-sm);
    font-weight: var(--fw-bold);
    color: var(--clr-gold-dark);
  }

  [data-theme="dark"] .zone-form-header {
    background: rgba(201,162,39,0.07);
    color: var(--clr-gold-mid);
  }

  .zone-form-body { padding: var(--sp-5); }

  /* Coord row */
  .coord-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--sp-3);
  }

  /* Pick on map button */
  .btn-pick-map {
    display: inline-flex;
    align-items: center;
    gap: var(--sp-2);
    font-size: var(--text-xs);
    font-weight: var(--fw-semi);
    color: var(--clr-green-dark);
    background: var(--clr-green-tint);
    border: 1px solid rgba(30,122,60,0.2);
    border-radius: var(--radius-md);
    padding: var(--sp-2) var(--sp-3);
    cursor: pointer;
    transition: var(--transition-fast);
    margin-bottom: var(--sp-4);
  }

  .btn-pick-map:hover {
    background: rgba(30,122,60,0.12);
    border-color: rgba(30,122,60,0.35);
  }

  [data-theme="dark"] .btn-pick-map {
    background: rgba(30,122,60,0.12);
  }

  /* ════════════════════════════════════════════
     ZONE LIST
     ════════════════════════════════════════════ */
  .zone-list-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-card);
    overflow: hidden;
  }

  .zone-list-header {
    padding: var(--sp-4) var(--sp-5);
    border-bottom: 1px solid var(--border-color);
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: var(--text-sm);
    font-weight: var(--fw-bold);
    color: var(--text-primary);
    background: var(--bg-card-alt);
  }

  .zone-list-body {
    max-height: 480px;
    overflow-y: auto;
    scrollbar-width: thin;
  }

  .zone-item {
    display: flex;
    align-items: center;
    gap: var(--sp-3);
    padding: var(--sp-3) var(--sp-5);
    border-bottom: 1px solid var(--border-color);
    transition: background 0.15s;
  }

  .zone-item:last-child { border-bottom: none; }
  .zone-item:hover { background: var(--bg-card-alt); }

  .zone-item-icon {
    width: 36px; height: 36px;
    border-radius: var(--radius-md);
    background: var(--clr-green-tint);
    border: 1px solid rgba(30,122,60,0.15);
    display: flex; align-items: center; justify-content: center;
    color: var(--clr-green-dark);
    font-size: 0.9rem;
    flex-shrink: 0;
  }

  [data-theme="dark"] .zone-item-icon {
    background: rgba(30,122,60,0.12);
  }

  .zone-item-info { flex: 1; min-width: 0; }

  .zone-item-name {
    font-size: var(--text-sm);
    font-weight: var(--fw-semi);
    color: var(--text-primary);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .zone-item-coords {
    font-size: 10px;
    color: var(--text-muted);
    font-family: var(--font-mono);
    margin-top: 2px;
  }

  .zone-item-code {
    font-size: 10px;
    font-weight: var(--fw-bold);
    color: var(--clr-gold-dark);
    background: var(--clr-gold-pale);
    border-radius: var(--radius-sm);
    padding: 1px 6px;
    flex-shrink: 0;
  }

  [data-theme="dark"] .zone-item-code {
    background: rgba(201,162,39,0.1);
    color: var(--clr-gold-mid);
  }

  .zone-item-status {
    width: 7px; height: 7px;
    border-radius: 50%;
    flex-shrink: 0;
  }

  .zone-item-status.active   { background: var(--clr-success); }
  .zone-item-status.inactive { background: var(--clr-gray-400); }

  .zone-item-actions {
    display: flex;
    gap: var(--sp-1);
    flex-shrink: 0;
  }

  .zone-action-btn {
    width: 28px; height: 28px;
    border-radius: var(--radius-sm);
    border: 1.5px solid var(--border-color);
    background: none;
    color: var(--text-muted);
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.75rem;
    transition: var(--transition-fast);
  }

  .zone-action-btn:hover { border-color: var(--clr-gold-mid); color: var(--clr-gold-mid); }
  .zone-action-btn.del:hover { border-color: var(--clr-danger); color: var(--clr-danger); }

  /* ════════════════════════════════════════════
     MAP PREVIEW (right panel on desktop)
     ════════════════════════════════════════════ */
  .zone-map-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-card);
    overflow: hidden;
  }

  .zone-map-header {
    padding: var(--sp-3) var(--sp-5);
    border-bottom: 1px solid var(--border-color);
    font-size: var(--text-sm);
    font-weight: var(--fw-semi);
    color: var(--text-primary);
    background: var(--bg-card-alt);
    display: flex;
    align-items: center;
    gap: var(--sp-2);
  }

  #zoneMapPreview {
    width: 100%;
    height: 420px;
  }

  @media (min-width: 1024px) {
    #zoneMapPreview { height: 520px; }
  }
</style>
@endpush

@section('content')

  <!-- Breadcrumb -->
  <div class="admin-breadcrumb">
    <a href="{{ route('admin.dashboard') }}"><i class="fa-solid fa-gauge-high" style="color:var(--clr-gold-mid)"></i></a>
    <span class="admin-breadcrumb-sep">/</span>
    <span class="admin-breadcrumb-current">Zones</span>
    <span class="ms-auto text-xs text-muted-c">
      {{ $zones->count() }} zone{{ $zones->count() !== 1 ? 's' : '' }} configured
    </span>
  </div>

  <!-- Page header -->
  <div class="admin-page-header">
    <div>
      <div class="admin-page-title">Zone Management</div>
      <div class="admin-page-sub">Define camp landmarks and pickup zones used in ride routing.</div>
    </div>
  </div>

  <!-- ════════════════════════════════════════
       TOP ROW: Form + Zone List
       ════════════════════════════════════════ -->
  <div class="zones-grid" style="margin-bottom:var(--sp-5)">

    <!-- ── ADD / EDIT ZONE FORM ── -->
    <div class="zone-form-card">
      <div class="zone-form-header">
        <i class="fa-solid fa-map-pin"></i>
        <span id="zoneFormTitle">Add New Zone</span>
      </div>
      <div class="zone-form-body">
        <form id="zoneForm" novalidate>
          @csrf
          <input type="hidden" id="zoneId">

          <div class="form-group-c">
            <label class="form-label-c" for="zoneName">Zone Name</label>
            <div class="input-icon-wrap">
              <i class="fa-solid fa-map-pin input-icon-left"></i>
              <input
                type="text"
                id="zoneName"
                class="input-chariot has-icon-left"
                placeholder="e.g. Main Auditorium"
                required
                data-validate="required"
                maxlength="80"
              >
            </div>
            <div class="field-error" style="display:none"></div>
          </div>

          <div class="form-group-c">
            <label class="form-label-c" for="zoneCode">Short Code</label>
            <div class="input-icon-wrap">
              <i class="fa-solid fa-hashtag input-icon-left"></i>
              <input
                type="text"
                id="zoneCode"
                class="input-chariot has-icon-left"
                placeholder="e.g. MAIN_AUD"
                required
                data-validate="required"
                maxlength="20"
                style="text-transform:uppercase;font-family:var(--font-mono)"
              >
            </div>
            <div class="field-hint">Uppercase, no spaces. Used internally.</div>
            <div class="field-error" style="display:none"></div>
          </div>

          <div class="form-group-c">
            <label class="form-label-c" for="zoneDesc">
              Description <span class="label-optional">(optional)</span>
            </label>
            <input
              type="text"
              id="zoneDesc"
              class="input-chariot"
              placeholder="Brief description of the location"
              maxlength="120"
            >
          </div>

          <!-- Pick on map instruction -->
          <button type="button" class="btn-pick-map" id="btnPickOnMap">
            <i class="fa-solid fa-crosshairs"></i>
            Click map to set coordinates
          </button>

          <div class="coord-row">
            <div class="form-group-c mb-0">
              <label class="form-label-c" for="zoneLat">Latitude</label>
              <input
                type="number"
                id="zoneLat"
                class="input-chariot"
                placeholder="6.8922"
                step="0.0001"
                required
                data-validate="required"
              >
            </div>
            <div class="form-group-c mb-0">
              <label class="form-label-c" for="zoneLng">Longitude</label>
              <input
                type="number"
                id="zoneLng"
                class="input-chariot"
                placeholder="3.7186"
                step="0.0001"
                required
                data-validate="required"
              >
            </div>
          </div>

          <div class="divider"></div>

          <div class="d-flex gap-3">
            <button type="submit" class="btn-chariot btn-primary-c flex-1" id="zoneSubmitBtn">
              <i class="fa-solid fa-plus"></i>
              <span id="zoneSubmitLabel">Add Zone</span>
            </button>
            <button type="button" class="btn-chariot btn-ghost-c" id="zoneCancelEditBtn"
                    style="display:none" onclick="AdminZones.cancelEdit()">
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- ── ZONE LIST ── -->
    <div class="zone-list-card">
      <div class="zone-list-header">
        <span><i class="fa-solid fa-list me-2" style="color:var(--clr-gold-mid)"></i>All Zones</span>
        <span class="text-xs text-muted-c">{{ $zones->count() }} total</span>
      </div>
      <div class="zone-list-body" id="zoneListBody">
        @forelse($zones as $zone)
          <div class="zone-item" data-zone-id="{{ $zone->id }}">
            <div class="zone-item-icon">
              <i class="fa-solid fa-map-pin"></i>
            </div>
            <div class="zone-item-info">
              <div class="zone-item-name" title="{{ $zone->name }}">{{ $zone->name }}</div>
              <div class="zone-item-coords">
                {{ number_format($zone->lat, 4) }}, {{ number_format($zone->lng, 4) }}
              </div>
            </div>
            <div class="zone-item-code">{{ $zone->short_code }}</div>
            <div class="zone-item-status {{ $zone->is_active ? 'active' : 'inactive' }}"
                 title="{{ $zone->is_active ? 'Active' : 'Inactive' }}">
            </div>
            <div class="zone-item-actions">
              <button
                class="zone-action-btn"
                title="Edit zone"
                onclick="AdminZones.edit({{ $zone->id }}, '{{ addslashes($zone->name) }}', '{{ $zone->short_code }}', '{{ addslashes($zone->description ?? '') }}', {{ $zone->lat }}, {{ $zone->lng }})"
              >
                <i class="fa-solid fa-pen"></i>
              </button>
              <button
                class="zone-action-btn del"
                title="Delete zone"
                onclick="AdminZones.delete({{ $zone->id }}, '{{ addslashes($zone->name) }}', this)"
              >
                <i class="fa-solid fa-trash"></i>
              </button>
            </div>
          </div>
        @empty
          <div class="empty-state py-8">
            <i class="fa-solid fa-map-pin empty-state-icon"></i>
            <div class="empty-state-title">No zones yet</div>
            <div class="empty-state-text">Add your first camp zone using the form.</div>
          </div>
        @endforelse
      </div>
    </div>

  </div>

  <!-- ════════════════════════════════════════
       MAP PREVIEW
       ════════════════════════════════════════ -->
  <div class="zone-map-card">
    <div class="zone-map-header">
      <i class="fa-solid fa-map" style="color:var(--clr-gold-mid)"></i>
      Zone Map Preview
      <span class="ms-auto text-xs text-muted-c">Click to set zone coordinates</span>
    </div>
    <div id="zoneMapPreview" role="img" aria-label="Zone map"></div>
  </div>

@endsection

@push('map-js')
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endpush

@push('scripts')
<script>
/* ================================================================
   admin/zones.blade.php — page script
   ================================================================ */

/* ── ZONE DATA from blade (passed to JS for map init) ── */
const ZONES_DATA = @json($zones->map(fn($z) => [
  'id'   => $z->id,
  'name' => $z->name,
  'code' => $z->short_code,
  'lat'  => (float) $z->lat,
  'lng'  => (float) $z->lng,
  'active' => $z->is_active,
]));

document.addEventListener('DOMContentLoaded', function () {

  /* ── 1. INIT LEAFLET MAP ── */
  const campCenter = [6.8922, 3.7186];
  const map = L.map('zoneMapPreview', {
    center: campCenter,
    zoom: 15,
  });

  /* Tile based on theme */
  const getLightTile = () => L.tileLayer(
    'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
    { attribution: '© OpenStreetMap contributors', maxZoom: 19 }
  );
  const getDarkTile = () => L.tileLayer(
    'https://tiles.stadiamaps.com/tiles/alidade_smooth_dark/{z}/{x}/{y}{r}.png',
    { attribution: '© Stadia Maps', maxZoom: 20 }
  );

  let currentTile = getLightTile().addTo(map);

  /* Swap on theme change */
  const origThemeApply = Chariot.Theme.apply.bind(Chariot.Theme);
  Chariot.Theme.apply = function(theme) {
    origThemeApply(theme);
    map.removeLayer(currentTile);
    currentTile = (theme === 'dark' ? getDarkTile() : getLightTile()).addTo(map);
  };

  /* Apply current theme */
  const savedTheme = localStorage.getItem('chariot-theme') || 'light';
  if (savedTheme === 'dark') {
    map.removeLayer(currentTile);
    currentTile = getDarkTile().addTo(map);
  }


  /* ── 2. PLOT EXISTING ZONES ── */
  const zoneMarkers = {};

  const zoneIcon = (active) => L.divIcon({
    className: '',
    html: `<div style="
      width:32px;height:32px;border-radius:50% 50% 50% 4px;transform:rotate(-45deg);
      background:${active ? 'var(--clr-green-dark)' : 'var(--clr-gray-400)'};
      display:flex;align-items:center;justify-content:center;
      border:2px solid white;box-shadow:0 3px 10px rgba(0,0,0,0.25);
    ">
      <i class="fa-solid fa-map-pin" style="transform:rotate(45deg);color:white;font-size:.7rem"></i>
    </div>`,
    iconSize: [32, 32],
    iconAnchor: [16, 32],
    popupAnchor: [0, -36],
  });

  ZONES_DATA.forEach(zone => {
    const marker = L.marker([zone.lat, zone.lng], { icon: zoneIcon(zone.active) })
      .bindPopup(`<div style="padding:8px 10px;min-width:140px">
        <div style="font-weight:600;font-size:13px;margin-bottom:2px">${zone.name}</div>
        <div style="font-size:11px;color:var(--text-muted);font-family:monospace">${zone.code}</div>
        <div style="font-size:10px;color:var(--text-muted);margin-top:4px">${zone.lat.toFixed(4)}, ${zone.lng.toFixed(4)}</div>
      </div>`, { closeButton: false })
      .addTo(map);

    zoneMarkers[zone.id] = marker;
  });

  /* Fit map to markers if any zones exist */
  if (ZONES_DATA.length > 0) {
    const bounds = L.latLngBounds(ZONES_DATA.map(z => [z.lat, z.lng]));
    map.fitBounds(bounds, { padding: [40, 40] });
  }


  /* ── 3. CLICK MAP TO SET COORDINATES ── */
  let pickMode = false;
  const pickBtn = document.getElementById('btnPickOnMap');

  pickBtn?.addEventListener('click', () => {
    pickMode = !pickMode;
    pickBtn.style.background = pickMode ? 'rgba(201,162,39,0.15)' : '';
    pickBtn.style.borderColor = pickMode ? 'var(--clr-gold-mid)' : '';
    pickBtn.style.color = pickMode ? 'var(--clr-gold-mid)' : '';
    pickBtn.innerHTML = pickMode
      ? '<i class="fa-solid fa-crosshairs"></i> Click map now… (click again to cancel)'
      : '<i class="fa-solid fa-crosshairs"></i> Click map to set coordinates';
    map.getContainer().style.cursor = pickMode ? 'crosshair' : '';
  });

  map.on('click', (e) => {
    if (!pickMode) return;
    document.getElementById('zoneLat').value = e.latlng.lat.toFixed(6);
    document.getElementById('zoneLng').value = e.latlng.lng.toFixed(6);

    /* Add a preview marker */
    if (window._previewMarker) map.removeLayer(window._previewMarker);
    window._previewMarker = L.circleMarker([e.latlng.lat, e.latlng.lng], {
      radius: 8,
      color: 'var(--clr-gold-mid)',
      fillColor: 'var(--clr-gold-bright)',
      fillOpacity: 0.8,
      weight: 2,
    }).addTo(map);

    /* Exit pick mode */
    pickMode = false;
    pickBtn.innerHTML = '<i class="fa-solid fa-crosshairs"></i> Click map to set coordinates';
    pickBtn.style.cssText = '';
    map.getContainer().style.cursor = '';

    Chariot.Toast.info(`Coordinates set: ${e.latlng.lat.toFixed(4)}, ${e.latlng.lng.toFixed(4)}`);
  });


  /* ── 4. ZONE FORM SUBMIT ── */
  document.getElementById('zoneForm')?.addEventListener('submit', async function (e) {
    e.preventDefault();
    if (!Chariot.Forms.validateForm(this)) return;

    const zoneId = document.getElementById('zoneId').value;
    const isEdit = !!zoneId;
    const btn    = document.getElementById('zoneSubmitBtn');

    const payload = {
      name:        document.getElementById('zoneName').value.trim(),
      short_code:  document.getElementById('zoneCode').value.trim().toUpperCase(),
      description: document.getElementById('zoneDesc').value.trim() || null,
      lat:         parseFloat(document.getElementById('zoneLat').value),
      lng:         parseFloat(document.getElementById('zoneLng').value),
    };

    Chariot.Buttons.setLoading(btn, true);

    try {
      let data;
      if (isEdit) {
        data = await Chariot.Util.put(`/admin/zones/${zoneId}`, payload);
        Chariot.Toast.success('Zone updated.', 'Saved');
      } else {
        data = await Chariot.Util.post('/admin/zones', payload);
        Chariot.Toast.success('Zone added.', 'Done');
      }

      /* Reload to show updated list — simplest for MVP */
      setTimeout(() => window.location.reload(), 800);

    } catch (err) {
      Chariot.Toast.error(err.message || 'Could not save zone.');
      Chariot.Buttons.setLoading(btn, false);
    }
  });

  /* Auto-uppercase short code */
  document.getElementById('zoneCode')?.addEventListener('input', function () {
    this.value = this.value.replace(/[^A-Z0-9_]/gi, '').toUpperCase();
  });

});


/* ── ADMIN ZONES ACTIONS ── */
window.AdminZones = {

  edit(id, name, code, desc, lat, lng) {
    document.getElementById('zoneId').value   = id;
    document.getElementById('zoneName').value = name;
    document.getElementById('zoneCode').value = code;
    document.getElementById('zoneDesc').value = desc;
    document.getElementById('zoneLat').value  = lat;
    document.getElementById('zoneLng').value  = lng;

    document.getElementById('zoneFormTitle').textContent  = 'Edit Zone';
    document.getElementById('zoneSubmitLabel').textContent = 'Update Zone';
    document.getElementById('zoneSubmitBtn').innerHTML =
      '<i class="fa-solid fa-floppy-disk"></i> <span>Update Zone</span>';
    document.getElementById('zoneCancelEditBtn').style.display = '';

    /* Scroll form into view */
    document.querySelector('.zone-form-card')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
  },

  cancelEdit() {
    document.getElementById('zoneId').value   = '';
    document.getElementById('zoneForm').reset();
    document.getElementById('zoneFormTitle').textContent  = 'Add New Zone';
    document.getElementById('zoneSubmitLabel').textContent = 'Add Zone';
    document.getElementById('zoneSubmitBtn').innerHTML =
      '<i class="fa-solid fa-plus"></i> <span>Add Zone</span>';
    document.getElementById('zoneCancelEditBtn').style.display = 'none';
  },

  delete(id, name, btn) {
    AdminConfirm.show({
      title:    `Delete "${name}"?`,
      body:     'This zone will be permanently removed. Existing rides referencing it may be affected.',
      iconType: 'danger',
      okLabel:  'Delete Zone',
      okClass:  'btn-danger-c',
      onOk: async () => {
        Chariot.Buttons.setLoading(btn, true);
        try {
          await Chariot.Util.delete(`/admin/zones/${id}`);
          document.querySelector(`.zone-item[data-zone-id="${id}"]`)?.remove();
          Chariot.Toast.success(`Zone "${name}" deleted.`);
        } catch (err) {
          Chariot.Toast.error(err.message || 'Could not delete zone.');
          Chariot.Buttons.setLoading(btn, false);
        }
      }
    });
  }
};
</script>
@endpush
<!-- ========================= [E] END ============================== -->
