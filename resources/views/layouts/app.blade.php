<!-- ===================================================================
  [A]  layouts/app.blade.php
  ====================================================================
  Cut everything between the [A] markers into:
      resources/views/layouts/app.blade.php
  ================================================================== -->
<!-- ======================== [A] START ============================= -->
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <meta name="csrf-token"  content="{{ csrf_token() }}">
  <meta name="api-token"   content="{{ auth()->user()->getOrCreateWebToken() }}">
  <meta name="user-id"     content="{{ auth()->id() }}">
  <meta name="user-role"   content="{{ auth()->user()->role }}">
  <meta name="pusher-key"  content="{{ config('broadcasting.connections.pusher.key') }}">
  <meta name="pusher-host" content="{{ config('broadcasting.connections.pusher.options.host') }}">
  <meta name="pusher-port" content="{{ config('broadcasting.connections.pusher.options.port') }}">

  <title>@yield('title', 'Chariot') — RCCG Camp Chariot</title>

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;900&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,300&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">

  <!-- Bootstrap 5 — every page -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css">

  <!-- Font Awesome 6 — every page -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  <!--
    Leaflet CSS — map pages only (rider/home, rider/find-ride).
    Injected via @stack('map-css') from child pages.
    Non-map pages push nothing — zero cost.
  -->
  @stack('map-css')

  <!-- Chariot global styles — every page -->
  <link rel="stylesheet" href="{{ asset('css/chariot.css') }}">

  <!-- Per-page styles pushed from child views -->
  @stack('styles')

  <!-- Flash messages forwarded to JS via data attributes on <body> -->
</head>

<body
  data-flash-success="{{ session('success') }}"
  data-flash-error="{{ session('error') }}"
  data-flash-info="{{ session('info') }}"
  data-flash-warning="{{ session('warning') }}"
>

  <!-- ── SIDEBAR OVERLAY (mobile) ───────────────────────────── -->
  <div class="sidebar-overlay" id="sidebarOverlay"></div>

  <!-- ── SIDEBAR ────────────────────────────────────────────── -->
  <aside class="chariot-sidebar" id="chariotSidebar">
    <a href="{{ route('rider.home') }}" class="sidebar-brand">
      <img src="{{ asset('images/chariot-logo.png') }}" alt="Chariot" width="36" height="36">
      <div>
        <div class="sidebar-brand-text">CHARIOT</div>
        <div class="sidebar-brand-tagline">Ride with Purpose</div>
      </div>
    </a>

    <ul class="sidebar-nav" role="navigation">
      @if(auth()->user()->role === 'rider')
        <li>
          <a href="{{ route('rider.home') }}"
             class="sidebar-nav-item {{ request()->routeIs('rider.home') ? 'is-active' : '' }}">
            <i class="fa-solid fa-map"></i> Map
          </a>
        </li>
        <li>
          <a href="{{ route('rider.find-ride') }}"
             class="sidebar-nav-item {{ request()->routeIs('rider.find-ride') ? 'is-active' : '' }}">
            <i class="fa-solid fa-route"></i> Find a Ride
          </a>
        </li>
        <li>
          <a href="{{ route('rider.my-rides') }}"
             class="sidebar-nav-item {{ request()->routeIs('rider.my-rides') ? 'is-active' : '' }}">
            <i class="fa-solid fa-clock-rotate-left"></i> My Rides
          </a>
        </li>
        <li>
          <a href="{{ route('rider.profile') }}"
             class="sidebar-nav-item {{ request()->routeIs('rider.profile') ? 'is-active' : '' }}">
            <i class="fa-solid fa-user"></i> Profile
          </a>
        </li>

      @elseif(auth()->user()->role === 'driver')
        <li>
          <a href="{{ route('driver.dashboard') }}"
             class="sidebar-nav-item {{ request()->routeIs('driver.dashboard') ? 'is-active' : '' }}">
            <i class="fa-solid fa-gauge-high"></i> Dashboard
          </a>
        </li>
        <li>
          <a href="{{ route('driver.requests') }}"
             class="sidebar-nav-item {{ request()->routeIs('driver.requests') ? 'is-active' : '' }}">
            <i class="fa-solid fa-hand"></i> Requests
          </a>
        </li>
        <li>
          <a href="{{ route('driver.history') }}"
             class="sidebar-nav-item {{ request()->routeIs('driver.history') ? 'is-active' : '' }}">
            <i class="fa-solid fa-clock-rotate-left"></i> History
          </a>
        </li>
        <li>
          <a href="{{ route('driver.profile') }}"
             class="sidebar-nav-item {{ request()->routeIs('driver.profile') ? 'is-active' : '' }}">
            <i class="fa-solid fa-user"></i> Profile
          </a>
        </li>
      @endif
    </ul>

    <div class="sidebar-footer">
      <div class="sidebar-user">
        <div class="chariot-avatar avatar-sm">
          {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
        </div>
        <div>
          <div class="sidebar-user-name">{{ auth()->user()->name }}</div>
          <div class="sidebar-user-role">{{ auth()->user()->role }}</div>
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

  <!-- ── TOP NAVBAR ──────────────────────────────────────────── -->
  <nav class="chariot-navbar" id="mainNav" role="navigation" aria-label="Main navigation">
    <div class="navbar-left">
      <!-- Hamburger (mobile only) -->
      <button class="navbar-toggle" id="sidebarToggle" aria-label="Open menu">
        <i class="fa-solid fa-bars"></i>
      </button>

      <!-- Brand -->
      <a href="{{ auth()->user()->role === 'driver' ? route('driver.dashboard') : route('rider.home') }}"
         class="navbar-brand-link">
        <img src="{{ asset('images/chariot-logo.png') }}" alt="Chariot" width="30" height="30">
        <span class="navbar-brand-name d-none d-sm-inline">CHARIOT</span>
      </a>
    </div>

    <div class="navbar-right">
      <!-- Notification bell -->
      <button class="navbar-icon-btn" id="notifBell" aria-label="Notifications">
        <i class="fa-solid fa-bell"></i>
        <span class="notif-badge is-hidden" id="notifCount">0</span>
      </button>

      <!-- Theme toggle -->
      <button class="navbar-icon-btn" id="themeToggle" aria-label="Toggle theme" title="Toggle dark mode">
        <i class="fa-solid fa-moon fs-6" id="themeIcon"></i>
      </button>

      <!-- Avatar dropdown -->
      <div class="dropdown">
        <div class="chariot-avatar navbar-avatar" data-bs-toggle="dropdown"
             aria-expanded="false" role="button" tabindex="0"
             aria-label="User menu">
          {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
        </div>
        <ul class="dropdown-menu dropdown-menu-end chariot-dropdown mt-2">
          <li class="dropdown-header">
            <div class="user-name">{{ auth()->user()->name }}</div>
            <div class="user-role">{{ ucfirst(auth()->user()->role) }}</div>
          </li>
          <li>
            <a class="dropdown-item" href="{{ auth()->user()->role === 'driver' ? route('driver.profile') : route('rider.profile') }}">
              <i class="fa-solid fa-user"></i> Profile
            </a>
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

  <!-- ── NOTIFICATION DRAWER ────────────────────────────────── -->
  <div class="notif-backdrop" id="notifBackdrop"></div>
  <aside class="notif-drawer" id="notifDrawer" aria-label="Notifications">
    <div class="notif-drawer-header">
      <span class="notif-drawer-title">Notifications</span>
      <button class="notif-close-btn" id="notifCloseBtn" aria-label="Close notifications">
        <i class="fa-solid fa-xmark"></i>
      </button>
    </div>
    <div class="notif-actions-row">
      <span class="text-xs text-muted-c" id="notifUnreadLabel">—</span>
      <button class="notif-mark-all" id="notifMarkAll">Mark all read</button>
    </div>
    <div class="notif-list" id="notifList">
      <div class="notif-empty">
        <i class="fa-solid fa-bell-slash"></i>
        <span>No notifications yet</span>
      </div>
    </div>
  </aside>

  <!-- ── TOAST CONTAINER ────────────────────────────────────── -->
  <div class="toast-container-c" id="toastContainer" aria-live="polite"></div>

  <!-- ── DRIVER INCOMING REQUEST ALERT POPUP ────────────────── -->
  <!-- Only renders content for drivers — riders see an empty div  -->
  @if(auth()->user()->role === 'driver')
  <div class="driver-alert-popup" id="driverAlertPopup" role="alert" aria-live="assertive">
    <div class="driver-alert-icon"><i class="fa-solid fa-bell"></i></div>
    <div class="driver-alert-title">New Ride Request!</div>
    <div class="driver-alert-body">
      <span class="driver-alert-rider-name">Someone</span> wants to join your ride.
    </div>
    <div class="driver-alert-actions">
      <button class="btn-view-alert btn-chariot btn-primary-c btn-sm-c flex-1">
        <i class="fa-solid fa-eye"></i> View Request
      </button>
      <button class="btn-chariot btn-ghost-c btn-sm-c"
              onclick="document.getElementById('driverAlertPopup').classList.remove('is-visible')">
        Dismiss
      </button>
    </div>
  </div>
  @endif

  <!-- ── MAIN CONTENT (child pages inject here) ─────────────── -->
  <main class="chariot-content @yield('content-class')"
        id="mainContent"
        role="main">
    @yield('content')
  </main>

  <!-- ── BOTTOM NAVIGATION (mobile only) ───────────────────── -->
  <nav class="chariot-bottom-nav" id="bottomNav" role="navigation" aria-label="Bottom navigation">
    @if(auth()->user()->role === 'rider')
      <a href="{{ route('rider.home') }}"
         class="bottom-nav-item {{ request()->routeIs('rider.home') ? 'is-active' : '' }}">
        <i class="fa-solid fa-map"></i>
        <span>Map</span>
      </a>
      <a href="{{ route('rider.find-ride') }}"
         class="bottom-nav-item {{ request()->routeIs('rider.find-ride') ? 'is-active' : '' }}">
        <i class="fa-solid fa-route"></i>
        <span>Find Ride</span>
      </a>
      <a href="{{ route('rider.my-rides') }}"
         class="bottom-nav-item {{ request()->routeIs('rider.my-rides') ? 'is-active' : '' }}">
        <i class="fa-solid fa-clock-rotate-left"></i>
        <span>My Rides</span>
        {{-- Unread badge: show pending requests count --}}
        @if(isset($pendingCount) && $pendingCount > 0)
          <span class="nav-badge">{{ $pendingCount }}</span>
        @endif
      </a>
      <a href="{{ route('rider.profile') }}"
         class="bottom-nav-item {{ request()->routeIs('rider.profile') ? 'is-active' : '' }}">
        <i class="fa-solid fa-user"></i>
        <span>Profile</span>
      </a>

    @elseif(auth()->user()->role === 'driver')
      <a href="{{ route('driver.dashboard') }}"
         class="bottom-nav-item {{ request()->routeIs('driver.dashboard') ? 'is-active' : '' }}">
        <i class="fa-solid fa-gauge-high"></i>
        <span>Dashboard</span>
      </a>
      <a href="{{ route('driver.requests') }}"
         class="bottom-nav-item {{ request()->routeIs('driver.requests') ? 'is-active' : '' }}">
        <i class="fa-solid fa-hand"></i>
        <span>Requests</span>
      </a>
      <a href="{{ route('driver.history') }}"
         class="bottom-nav-item {{ request()->routeIs('driver.history') ? 'is-active' : '' }}">
        <i class="fa-solid fa-clock-rotate-left"></i>
        <span>History</span>
      </a>
      <a href="{{ route('driver.profile') }}"
         class="bottom-nav-item {{ request()->routeIs('driver.profile') ? 'is-active' : '' }}">
        <i class="fa-solid fa-user"></i>
        <span>Profile</span>
      </a>
    @endif
  </nav>

  <!-- ══════════════════════════════════════════════════════════
       SCRIPTS — bottom of layout
       ══════════════════════════════════════════════════════════ -->

  <!-- Bootstrap 5 JS — every page -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>

  <!--
    Leaflet JS — map pages only.
    rider/home and rider/find-ride push this via @push('map-js').
    All other pages push nothing.
  -->
  @stack('map-js')

  <!--
    Pusher JS + Laravel Echo — real-time pages only.
    rider/home, rider/find-ride, rider/my-rides push these
    via @push('realtime-js').
  -->
  @stack('realtime-js')

  <!-- Chariot global JS — every authenticated page -->
  <script src="{{ asset('js/chariot.js') }}"></script>

  <!-- Per-page scripts pushed from child views -->
  @stack('scripts')

</body>
</html>
<!-- ========================= [A] END ============================== -->