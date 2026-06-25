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