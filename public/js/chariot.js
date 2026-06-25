/* =============================================================
   CHARIOT.JS — Global Application Script
   RCCG Camp Chariot · Smart Mobility for the Saints
   Version 1.0 · Vanilla JS · Bootstrap 5 + Leaflet + Echo
   Covers: Riders · Drivers · Admins
   =============================================================

   TABLE OF CONTENTS
   ─────────────────────────────────────────────────────────────
   00. NAMESPACE & CONFIG
   01. UTILITIES
       01a. DOM helpers
       01b. HTTP / API fetch wrapper
       01c. Debounce & throttle
       01d. Date & time helpers
       01e. Storage helpers
   02. THEME
       02a. Init & toggle
       02b. Map tile switcher (hook)
   03. SIDEBAR & NAVIGATION
       03a. Sidebar toggle (mobile)
       03b. Bottom nav active state
       03c. Navbar scroll behaviour
   04. TOAST NOTIFICATIONS
       04a. Toast render
       04b. Toast queue
   05. NOTIFICATIONS SYSTEM
       05a. Bell badge update
       05b. Drawer open/close
       05c. Fetch & render notifications
       05d. Mark read
   06. BUTTON RIPPLE & LOADING STATES
   07. FORM UTILITIES
       07a. Role toggle (Register)
       07b. Seat counter
       07c. Password show/hide
       07d. Input real-time validation
       07e. Form submit guard (CSRF + loading)
   08. OTP
       08a. Digit auto-advance
       08b. Paste handler
       08c. Countdown timer
       08d. Resend trigger
   09. MAP MODULE (Leaflet)
       09a. Init map
       09b. Tile sets (light/dark)
       09c. User location
       09d. Driver markers (add/update/remove)
       09e. Zone markers
       09f. Map popup builder
       09g. Bottom sheet toggle
   10. REAL-TIME (Laravel Echo + Pusher/Soketi)
       10a. Echo bootstrap
       10b. Rider — listen driver locations
       10c. Rider — listen ride status changes
       10d. Driver — listen incoming requests
       10e. Driver — connection status indicator
   11. RIDER MODULE
       11a. Find nearby rides (fetch + render)
       11b. Request a ride
       11c. Cancel request
       11d. My rides list refresh
       11e. Live status polling (fallback)
   12. DRIVER MODULE
       12a. Availability toggle
       12b. Create ride form
       12c. Cancel ride
       12d. Accept / decline request
       12e. Complete ride
       12f. Location broadcast loop
   13. ADMIN MODULE
       13a. User search & filter
       13b. Toggle user active
       13c. Verify user
       13d. Change role
       13e. Live ride table refresh
   14. PROFILE MODULE
       14a. Save profile
       14b. Theme picker sync
   15. INIT — Boot sequence on DOMContentLoaded
   ============================================================= */


/* ─────────────────────────────────────────────────────────────
   00. NAMESPACE & CONFIG
   ───────────────────────────────────────────────────────────── */

window.Chariot = window.Chariot || {};

Chariot.Config = {
  /* Pulled from <meta> tags set by Blade layout */
  apiToken:    () => Chariot.Util.meta('api-token'),
  csrfToken:   () => Chariot.Util.meta('csrf-token'),
  pusherKey:   () => Chariot.Util.meta('pusher-key'),
  pusherHost:  () => Chariot.Util.meta('pusher-host'),
  pusherPort:  () => Chariot.Util.meta('pusher-port'),
  userId:      () => Chariot.Util.meta('user-id'),
  userRole:    () => Chariot.Util.meta('user-role'),

  /* RCCG Camp approximate centre coordinate */
  campCenter: [6.8922, 3.7186],
  campZoom: 16,
  nearbyRadiusKm: 3,

  /* How often (ms) to push driver GPS to server */
  locationBroadcastInterval: 8000,

  /* How often (ms) to poll for ride updates (fallback when WS unavailable) */
  pollInterval: 15000,

  /* Toast auto-dismiss duration (ms) */
  toastDuration: 4500,
};


/* ─────────────────────────────────────────────────────────────
   01. UTILITIES
   ───────────────────────────────────────────────────────────── */

Chariot.Util = {

  /* ── 01a. DOM helpers ── */

  /** @param {string} sel @param {Element|Document} ctx */
  $(sel, ctx = document) {
    return ctx.querySelector(sel);
  },

  $$(sel, ctx = document) {
    return Array.from(ctx.querySelectorAll(sel));
  },

  /** Read <meta name="..."> content */
  meta(name) {
    const el = document.querySelector(`meta[name="${name}"]`);
    return el ? el.content : null;
  },

  /** Create element with classes and optional innerHTML */
  el(tag, classes = '', html = '') {
    const node = document.createElement(tag);
    if (classes) node.className = classes;
    if (html)    node.innerHTML = html;
    return node;
  },

  /** Show / hide elements */
  show(el) { if (el) el.style.display = ''; },
  hide(el) { if (el) el.style.display = 'none'; },
  toggle(el, force) {
    if (el) el.style.display = (force ?? el.style.display === 'none') ? '' : 'none';
  },

  /** Add / remove CSS class safely */
  addClass(el, cls)    { el?.classList.add(cls); },
  removeClass(el, cls) { el?.classList.remove(cls); },
  toggleClass(el, cls, force) { el?.classList.toggle(cls, force); },
  hasClass(el, cls)    { return el?.classList.contains(cls) ?? false; },

  /** Scroll element smoothly into view */
  scrollTo(el, offset = 80) {
    if (!el) return;
    const top = el.getBoundingClientRect().top + window.pageYOffset - offset;
    window.scrollTo({ top, behavior: 'smooth' });
  },

  /** Lock / unlock body scroll */
  lockScroll()   { document.body.classList.add('scroll-locked'); },
  unlockScroll() { document.body.classList.remove('scroll-locked'); },


  /* ── 01b. HTTP / API fetch wrapper ── */

  /**
   * Authenticated JSON fetch. Always sends CSRF + Bearer token.
   * Returns parsed JSON or throws an Error with .status and .data.
   */
  async api(url, method = 'GET', body = null) {
    const opts = {
      method,
      headers: {
        'Content-Type':  'application/json',
        'Accept':        'application/json',
        'X-CSRF-TOKEN':  Chariot.Config.csrfToken(),
        'Authorization': `Bearer ${Chariot.Config.apiToken()}`,
      },
    };

    if (body !== null) {
      opts.body = JSON.stringify(body);
    }

    let res;
    try {
      res = await fetch(url, opts);
    } catch (networkErr) {
      throw Object.assign(new Error('Network error. Check your connection.'), { status: 0 });
    }

    let data;
    try {
      data = await res.json();
    } catch {
      data = {};
    }

    if (!res.ok) {
      const msg = data?.message || data?.error || `Request failed (${res.status})`;
      throw Object.assign(new Error(msg), { status: res.status, data });
    }

    return data;
  },

  /** Convenience shorthands */
  get:    (url)          => Chariot.Util.api(url, 'GET'),
  post:   (url, body)    => Chariot.Util.api(url, 'POST',   body),
  put:    (url, body)    => Chariot.Util.api(url, 'PUT',    body),
  patch:  (url, body)    => Chariot.Util.api(url, 'PATCH',  body),
  delete: (url)          => Chariot.Util.api(url, 'DELETE'),


  /* ── 01c. Debounce & throttle ── */

  debounce(fn, delay = 300) {
    let timer;
    return (...args) => {
      clearTimeout(timer);
      timer = setTimeout(() => fn.apply(this, args), delay);
    };
  },

  throttle(fn, limit = 1000) {
    let last = 0;
    return (...args) => {
      const now = Date.now();
      if (now - last >= limit) {
        last = now;
        fn.apply(this, args);
      }
    };
  },


  /* ── 01d. Date & time helpers ── */

  timeAgo(dateStr) {
    const diff = Math.floor((Date.now() - new Date(dateStr)) / 1000);
    if (diff < 60)   return 'just now';
    if (diff < 3600) return `${Math.floor(diff / 60)} min ago`;
    if (diff < 86400) return `${Math.floor(diff / 3600)} hr ago`;
    return new Date(dateStr).toLocaleDateString();
  },

  formatTime(dateStr) {
    return new Date(dateStr).toLocaleTimeString([], {
      hour: '2-digit', minute: '2-digit'
    });
  },

  formatDateTime(dateStr) {
    return new Date(dateStr).toLocaleString([], {
      month: 'short', day: 'numeric',
      hour: '2-digit', minute: '2-digit'
    });
  },

  /** Countdown: returns "MM:SS" string from seconds remaining */
  formatCountdown(seconds) {
    const m = Math.floor(seconds / 60).toString().padStart(2, '0');
    const s = (seconds % 60).toString().padStart(2, '0');
    return `${m}:${s}`;
  },


  /* ── 01e. Storage helpers ── */

  store: {
    get(key)         { try { return JSON.parse(localStorage.getItem(key)); } catch { return null; } },
    set(key, val)    { localStorage.setItem(key, JSON.stringify(val)); },
    remove(key)      { localStorage.removeItem(key); },
    session: {
      get(key)       { try { return JSON.parse(sessionStorage.getItem(key)); } catch { return null; } },
      set(key, val)  { sessionStorage.setItem(key, JSON.stringify(val)); },
      remove(key)    { sessionStorage.removeItem(key); },
    },
  },
};

/* Alias for brevity internally */
const { $, $$, el, meta } = Chariot.Util;


/* ─────────────────────────────────────────────────────────────
   02. THEME
   ───────────────────────────────────────────────────────────── */

Chariot.Theme = {

  STORAGE_KEY: 'chariot-theme',
  current: 'light',

  /* ── 02a. Init & toggle ── */

  init() {
    const saved = Chariot.Util.store.get(this.STORAGE_KEY) || 'light';
    this.apply(saved);

    const btn = $('#themeToggle');
    btn?.addEventListener('click', () => this.toggle());
  },

  apply(theme) {
    this.current = theme;
    document.documentElement.setAttribute('data-theme', theme);
    this._updateIcon(theme);
    this._updateThemePickerUI(theme);
    Chariot.Util.store.set(this.STORAGE_KEY, theme);

    /* Notify map module to swap tiles */
    Chariot.Map?.onThemeChange?.(theme);
  },

  toggle() {
    this.apply(this.current === 'dark' ? 'light' : 'dark');
  },

  _updateIcon(theme) {
    const icon = $('#themeIcon');
    if (!icon) return;
    icon.className = theme === 'dark'
      ? 'fa-solid fa-sun fs-6'
      : 'fa-solid fa-moon fs-6';
    icon.closest('button')?.setAttribute(
      'title', theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode'
    );
  },

  _updateThemePickerUI(theme) {
    $$('.theme-option').forEach(opt => {
      const isActive = opt.dataset.theme === theme;
      opt.classList.toggle('is-active', isActive);
    });
  },
};


/* ─────────────────────────────────────────────────────────────
   03. SIDEBAR & NAVIGATION
   ───────────────────────────────────────────────────────────── */

Chariot.Nav = {

  sidebar:  null,
  overlay:  null,
  isOpen:   false,

  /* ── 03a. Sidebar toggle (mobile) ── */

  init() {
    this.sidebar = $('#chariotSidebar');
    this.overlay = $('#sidebarOverlay');

    /* Toggle button (hamburger in navbar) */
    $('#sidebarToggle')?.addEventListener('click', () => this.open());

    /* Overlay click closes */
    this.overlay?.addEventListener('click', () => this.close());

    /* Escape key closes */
    document.addEventListener('keydown', e => {
      if (e.key === 'Escape' && this.isOpen) this.close();
    });

    /* Close sidebar when a nav link is clicked (mobile) */
    $$('.sidebar-nav-item', this.sidebar).forEach(link => {
      link.addEventListener('click', () => {
        if (window.innerWidth < 768) this.close();
      });
    });

    /* ── 03b. Bottom nav active state ── */
    this._setBottomNavActive();

    /* ── 03c. Navbar scroll behaviour ── */
    this._initScrollBehaviour();
  },

  open() {
    this.isOpen = true;
    this.sidebar?.classList.add('is-open');
    this.overlay?.classList.add('is-active');
    Chariot.Util.lockScroll();
  },

  close() {
    this.isOpen = false;
    this.sidebar?.classList.remove('is-open');
    this.overlay?.classList.remove('is-active');
    Chariot.Util.unlockScroll();
  },

  _setBottomNavActive() {
    const path = window.location.pathname;
    $$('.bottom-nav-item').forEach(item => {
      const href = item.getAttribute('href') || '';
      const isActive = href !== '#' && path.startsWith(href);
      item.classList.toggle('is-active', isActive);
    });
  },

  _initScrollBehaviour() {
    let lastY = 0;
    const navbar = $('.chariot-navbar');
    if (!navbar) return;

    window.addEventListener('scroll', Chariot.Util.throttle(() => {
      const y = window.scrollY;
      /* Slightly reduce opacity when scrolled down on auth/landing pages */
      if (y > 20) {
        navbar.style.boxShadow = '0 2px 20px rgba(0,0,0,0.35)';
      } else {
        navbar.style.boxShadow = '';
      }
      lastY = y;
    }, 100));
  },
};


/* ─────────────────────────────────────────────────────────────
   04. TOAST NOTIFICATIONS
   ───────────────────────────────────────────────────────────── */

Chariot.Toast = {

  container: null,
  queue: [],

  ICONS: {
    success: 'fa-circle-check',
    error:   'fa-circle-xmark',
    warning: 'fa-triangle-exclamation',
    info:    'fa-circle-info',
  },

  init() {
    /* Create container if not in DOM */
    if (!$('#toastContainer')) {
      const wrap = el('div', 'toast-container-c');
      wrap.id = 'toastContainer';
      document.body.appendChild(wrap);
    }
    this.container = $('#toastContainer');
  },

  /* ── 04a. Toast render ── */

  /**
   * Show a toast.
   * @param {string} message
   * @param {'success'|'error'|'warning'|'info'} type
   * @param {string} [title]
   * @param {number} [duration] ms
   */
  show(message, type = 'success', title = '', duration = Chariot.Config.toastDuration) {
    const icon  = this.ICONS[type] || this.ICONS.info;
    const toast = el('div', `toast-c ${type}`);

    toast.innerHTML = `
      <i class="fa-solid ${icon} toast-icon"></i>
      <div class="toast-text">
        ${title ? `<div class="toast-title">${this._escape(title)}</div>` : ''}
        <div class="toast-msg">${this._escape(message)}</div>
      </div>
      <button class="toast-close" aria-label="Dismiss">
        <i class="fa-solid fa-xmark"></i>
      </button>
    `;

    toast.querySelector('.toast-close').addEventListener('click', () => {
      this._dismiss(toast);
    });

    this.container.appendChild(toast);

    /* Auto dismiss */
    const timer = setTimeout(() => this._dismiss(toast), duration);

    /* Pause timer on hover */
    toast.addEventListener('mouseenter', () => clearTimeout(timer));
    toast.addEventListener('mouseleave', () => {
      setTimeout(() => this._dismiss(toast), 1500);
    });

    return toast;
  },

  _dismiss(toast) {
    if (!toast || !toast.parentNode) return;
    toast.classList.add('is-leaving');
    toast.addEventListener('animationend', () => toast.remove(), { once: true });
    /* Fallback remove */
    setTimeout(() => toast.remove(), 400);
  },

  _escape(str) {
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;');
  },

  /* Convenience */
  success: (msg, title) => Chariot.Toast.show(msg, 'success', title),
  error:   (msg, title) => Chariot.Toast.show(msg, 'error',   title),
  warning: (msg, title) => Chariot.Toast.show(msg, 'warning', title),
  info:    (msg, title) => Chariot.Toast.show(msg, 'info',    title),
};


/* ─────────────────────────────────────────────────────────────
   05. NOTIFICATIONS SYSTEM
   ───────────────────────────────────────────────────────────── */

Chariot.Notifications = {

  unreadCount: 0,
  isDrawerOpen: false,
  pollTimer: null,

  /* ── 05a. Bell badge update ── */

  init() {
    const bell     = $('#notifBell');
    const backdrop = $('#notifBackdrop');
    const closeBtn = $('#notifCloseBtn');

    bell?.addEventListener('click',     () => this.openDrawer());
    backdrop?.addEventListener('click', () => this.closeDrawer());
    closeBtn?.addEventListener('click', () => this.closeDrawer());

    $('#notifMarkAll')?.addEventListener('click', () => this.markAllRead());

    /* Initial fetch */
    this.fetchCount();

    /* Poll every 30s as WS fallback */
    this.pollTimer = setInterval(() => this.fetchCount(), 30000);
  },

  setBadge(count) {
    this.unreadCount = count;
    const badge = $('#notifCount');
    if (!badge) return;
    badge.textContent = count > 99 ? '99+' : count;
    badge.classList.toggle('is-hidden', count === 0);

    /* Also update bottom nav badge if present */
    const navBadge = $('.bottom-nav-item[href*="notif"] .nav-badge');
    if (navBadge) {
      navBadge.textContent = count;
      navBadge.style.display = count > 0 ? 'flex' : 'none';
    }
  },

  async fetchCount() {
    try {
      const data = await Chariot.Util.get('/api/notifications/unread-count');
      this.setBadge(data.count ?? 0);
    } catch { /* silent — just a badge */ }
  },

  /* ── 05b. Drawer open/close ── */

  openDrawer() {
    $('#notifDrawer')?.classList.add('is-open');
    $('#notifBackdrop')?.classList.add('is-active');
    Chariot.Util.lockScroll();
    this.isDrawerOpen = true;
    this.fetchAndRender();
  },

  closeDrawer() {
    $('#notifDrawer')?.classList.remove('is-open');
    $('#notifBackdrop')?.classList.remove('is-active');
    Chariot.Util.unlockScroll();
    this.isDrawerOpen = false;
  },

  /* ── 05c. Fetch & render notifications ── */

  async fetchAndRender() {
    const list = $('#notifList');
    if (!list) return;

    list.innerHTML = `
      <div class="d-flex justify-content-center py-5">
        <div class="chariot-spinner"></div>
      </div>`;

    try {
      const data = await Chariot.Util.get('/api/notifications');
      const items = data.notifications || data;

      if (!items.length) {
        list.innerHTML = `
          <div class="notif-empty">
            <i class="fa-solid fa-bell-slash"></i>
            <span>No notifications yet</span>
          </div>`;
        return;
      }

      list.innerHTML = items.map(n => this._renderItem(n)).join('');

      /* Mark as read on click */
      $$('.notif-item[data-id]', list).forEach(item => {
        item.addEventListener('click', () => {
          this.markRead(item.dataset.id);
          item.classList.remove('is-unread');
        });
      });

      this.setBadge(items.filter(n => !n.is_read).length);

    } catch (err) {
      list.innerHTML = `
        <div class="notif-empty">
          <i class="fa-solid fa-circle-exclamation"></i>
          <span>Failed to load notifications</span>
        </div>`;
    }
  },

  _renderItem(n) {
    const typeMap = {
      ride_request: { icon: 'fa-hand',         cls: 'type-request'  },
      ride_accepted:{ icon: 'fa-circle-check',  cls: 'type-accepted' },
      ride_declined:{ icon: 'fa-circle-xmark',  cls: 'type-declined' },
      default:      { icon: 'fa-bell',          cls: 'type-info'     },
    };
    const t = typeMap[n.type] || typeMap.default;

    return `
      <div class="notif-item ${n.is_read ? '' : 'is-unread'}" data-id="${n.id}">
        <div class="notif-icon-wrap ${t.cls}">
          <i class="fa-solid ${t.icon}"></i>
        </div>
        <div class="notif-content">
          <div class="notif-title">${Chariot.Util.Util?._escape(n.title) ?? n.title}</div>
          <div class="notif-body">${n.body ?? ''}</div>
        </div>
        <div class="notif-time">${Chariot.Util.timeAgo(n.created_at)}</div>
      </div>`;
  },

  /* ── 05d. Mark read ── */

  async markRead(id) {
    try {
      await Chariot.Util.post(`/api/notifications/${id}/read`);
      if (this.unreadCount > 0) this.setBadge(this.unreadCount - 1);
    } catch { /* silent */ }
  },

  async markAllRead() {
    try {
      await Chariot.Util.post('/api/notifications/read-all');
      this.setBadge(0);
      $$('.notif-item').forEach(i => i.classList.remove('is-unread'));
    } catch (err) {
      Chariot.Toast.error('Could not mark all as read');
    }
  },

  /* Called from real-time events */
  pushNew(notification) {
    this.setBadge(this.unreadCount + 1);
    if (this.isDrawerOpen) this.fetchAndRender();
  },
};


/* ─────────────────────────────────────────────────────────────
   06. BUTTON RIPPLE & LOADING STATES
   ───────────────────────────────────────────────────────────── */

Chariot.Buttons = {

  init() {
    /* Attach ripple to all .btn-chariot elements — including future ones via delegation */
    document.addEventListener('click', e => {
      const btn = e.target.closest('.btn-chariot');
      if (!btn) return;
      this._createRipple(btn, e);
    });
  },

  _createRipple(btn, e) {
    const rect   = btn.getBoundingClientRect();
    const size   = Math.max(rect.width, rect.height);
    const x      = e.clientX - rect.left - size / 2;
    const y      = e.clientY - rect.top  - size / 2;

    const ripple = document.createElement('span');
    ripple.className = 'ripple';
    ripple.style.cssText = `
      width: ${size}px;
      height: ${size}px;
      left: ${x}px;
      top: ${y}px;
    `;

    btn.appendChild(ripple);
    ripple.addEventListener('animationend', () => ripple.remove(), { once: true });
  },

  /** Put button into loading state */
  setLoading(btn, loading = true) {
    if (!btn) return;
    if (loading) {
      btn.dataset.originalText = btn.innerHTML;
      btn.classList.add('is-loading');
      btn.disabled = true;
    } else {
      btn.innerHTML = btn.dataset.originalText || btn.innerHTML;
      btn.classList.remove('is-loading');
      btn.disabled = false;
    }
  },
};


/* ─────────────────────────────────────────────────────────────
   07. FORM UTILITIES
   ───────────────────────────────────────────────────────────── */

Chariot.Forms = {

  init() {
    this._initRoleToggle();
    this._initSeatCounter();
    this._initPasswordToggles();
    this._initInputValidation();
    this._initFormGuards();
  },

  /* ── 07a. Role toggle (Register page) ── */

  _initRoleToggle() {
    const btns        = $$('.role-toggle-btn');
    const driverWrap  = $('#driverFieldsWrap');
    const roleInput   = $('#roleInput');

    if (!btns.length) return;

    btns.forEach(btn => {
      btn.addEventListener('click', () => {
        btns.forEach(b => b.classList.remove('is-active'));
        btn.classList.add('is-active');

        const role = btn.dataset.role;
        if (roleInput) roleInput.value = role;

        if (driverWrap) {
          const isDriver = role === 'driver';
          driverWrap.classList.toggle('is-visible', isDriver);

          /* Toggle required on vehicle fields */
          $$('[data-driver-required]', driverWrap).forEach(f => {
            f.required = isDriver;
          });
        }
      });
    });
  },

  /* ── 07b. Seat counter ── */

  _initSeatCounter() {
    $$('.seat-counter').forEach(counter => {
      const decBtn  = counter.querySelector('.seat-counter-dec');
      const incBtn  = counter.querySelector('.seat-counter-inc');
      const display = counter.querySelector('.seat-counter-value');
      const input   = counter.querySelector('input[type="hidden"]');

      if (!decBtn || !incBtn || !display) return;

      const min = parseInt(counter.dataset.min ?? '1');
      const max = parseInt(counter.dataset.max ?? '20');
      let   val = parseInt(input?.value ?? display.textContent ?? min);

      const update = (n) => {
        val = Math.min(max, Math.max(min, n));
        display.textContent = val;
        if (input) input.value = val;
        decBtn.disabled = val <= min;
        incBtn.disabled = val >= max;
      };

      update(val);
      decBtn.addEventListener('click', () => update(val - 1));
      incBtn.addEventListener('click', () => update(val + 1));
    });
  },

  /* ── 07c. Password show/hide ── */

  _initPasswordToggles() {
    $$('.input-toggle-btn[data-target]').forEach(btn => {
      btn.addEventListener('click', () => {
        const input = document.getElementById(btn.dataset.target);
        if (!input) return;
        const isText   = input.type === 'text';
        input.type     = isText ? 'password' : 'text';
        const icon     = btn.querySelector('i');
        if (icon) {
          icon.className = isText
            ? 'fa-solid fa-eye'
            : 'fa-solid fa-eye-slash';
        }
      });
    });
  },

  /* ── 07d. Input real-time validation ── */

  _initInputValidation() {
    $$('.input-chariot[data-validate]').forEach(input => {
      const rules = (input.dataset.validate || '').split(',').map(s => s.trim());

      const validate = Chariot.Util.debounce(() => {
        const err = this._runRules(input, rules);
        this._setFieldState(input, err);
      }, 350);

      input.addEventListener('input',  validate);
      input.addEventListener('blur',   () => {
        const err = this._runRules(input, rules);
        this._setFieldState(input, err);
      });
    });

    /* Confirm password pairing */
    const confirmInput = $('#passwordConfirm');
    const passInput    = $('#password');
    if (confirmInput && passInput) {
      const check = () => {
        const err = confirmInput.value !== passInput.value ? 'Passwords do not match' : null;
        this._setFieldState(confirmInput, err);
      };
      confirmInput.addEventListener('input', check);
      passInput.addEventListener('input', check);
    }
  },

  _runRules(input, rules) {
    const v = input.value.trim();
    for (const rule of rules) {
      if (rule === 'required'   && !v)                    return 'This field is required';
      if (rule === 'email'      && v && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v))
                                                          return 'Enter a valid email address';
      if (rule === 'phone'      && v && !/^\+?[\d\s\-()]{7,15}$/.test(v))
                                                          return 'Enter a valid phone number';
      if (rule === 'min8'       && v && v.length < 8)     return 'Must be at least 8 characters';
      if (rule === 'plate'      && v && !/^[A-Z0-9\-]{5,10}$/i.test(v))
                                                          return 'Enter a valid plate number';
    }
    return null;
  },

  _setFieldState(input, errorMsg) {
    const wrap = input.closest('.form-group-c') || input.parentNode;
    const errEl = wrap.querySelector('.field-error');

    input.classList.toggle('is-invalid', !!errorMsg);
    input.classList.toggle('is-valid',   !errorMsg && input.value.trim().length > 0);

    if (errEl) {
      errEl.textContent = errorMsg || '';
      errEl.style.display = errorMsg ? 'flex' : 'none';
    }
  },

  /* ── 07e. Form submit guard (loading state + CSRF check) ── */

  _initFormGuards() {
    $$('form[data-loading]').forEach(form => {
      form.addEventListener('submit', e => {
        const submitBtn = form.querySelector('[type="submit"]');
        if (submitBtn) Chariot.Buttons.setLoading(submitBtn, true);

        /* Let native submit proceed — the blade will reload */
        /* Re-enable on back-navigation */
        window.addEventListener('pageshow', () => {
          Chariot.Buttons.setLoading(submitBtn, false);
        }, { once: true });
      });
    });
  },

  /** Manually validate all required fields in a form before AJAX submit */
  validateForm(form) {
    let valid = true;
    $$('[required]', form).forEach(input => {
      if (!input.value.trim()) {
        this._setFieldState(input, 'This field is required');
        valid = false;
      }
    });
    if (!valid) {
      const first = form.querySelector('.is-invalid');
      Chariot.Util.scrollTo(first);
    }
    return valid;
  },
};


/* ─────────────────────────────────────────────────────────────
   08. OTP
   ───────────────────────────────────────────────────────────── */

Chariot.OTP = {

  digits:      [],
  timerEl:     null,
  resendBtn:   null,
  countdown:   0,
  countdownFn: null,
  DURATION:    60, /* seconds */

  init() {
    this.digits    = $$('.otp-digit');
    this.timerEl   = $('#otpTimer');
    this.resendBtn = $('#otpResend');

    if (!this.digits.length) return;

    /* ── 08a. Auto-advance ── */
    this.digits.forEach((input, i) => {
      input.addEventListener('input', e => {
        /* Only allow single digit */
        input.value = input.value.replace(/\D/g, '').slice(-1);

        if (input.value) {
          input.classList.add('is-filled');
          if (i < this.digits.length - 1) {
            this.digits[i + 1].focus();
          } else {
            /* Last digit filled — auto-submit */
            this._autoSubmit();
          }
        } else {
          input.classList.remove('is-filled');
        }
      });

      input.addEventListener('keydown', e => {
        if (e.key === 'Backspace') {
          if (!input.value && i > 0) {
            this.digits[i - 1].value = '';
            this.digits[i - 1].classList.remove('is-filled');
            this.digits[i - 1].focus();
          } else {
            input.classList.remove('is-filled');
          }
        }

        if (e.key === 'ArrowLeft'  && i > 0)                   this.digits[i - 1].focus();
        if (e.key === 'ArrowRight' && i < this.digits.length-1) this.digits[i + 1].focus();
      });

      /* Click selects all */
      input.addEventListener('click', () => input.select());
    });

    /* ── 08b. Paste handler ── */
    this.digits[0].addEventListener('paste', e => {
      e.preventDefault();
      const pasted = e.clipboardData.getData('text').replace(/\D/g, '').slice(0, 6);
      pasted.split('').forEach((ch, i) => {
        if (this.digits[i]) {
          this.digits[i].value = ch;
          this.digits[i].classList.add('is-filled');
        }
      });
      const next = Math.min(pasted.length, this.digits.length - 1);
      this.digits[next].focus();
      if (pasted.length === 6) this._autoSubmit();
    });

    /* ── 08c. Countdown timer ── */
    this.startCountdown();

    /* ── 08d. Resend trigger ── */
    this.resendBtn?.addEventListener('click', () => this.resend());
  },

  startCountdown() {
    this.countdown = this.DURATION;
    if (this.resendBtn) this.resendBtn.disabled = true;

    clearInterval(this.countdownFn);
    this.countdownFn = setInterval(() => {
      this.countdown--;
      if (this.timerEl) {
        this.timerEl.textContent = `Resend in ${Chariot.Util.formatCountdown(this.countdown)}`;
      }
      if (this.countdown <= 0) {
        clearInterval(this.countdownFn);
        if (this.timerEl)   this.timerEl.textContent = '';
        if (this.resendBtn) this.resendBtn.disabled = false;
      }
    }, 1000);
  },

  async resend() {
    const btn = this.resendBtn;
    Chariot.Buttons.setLoading(btn, true);
    try {
      await Chariot.Util.post('/resend-otp');
      Chariot.Toast.success('A new code has been sent to your email.');
      this.startCountdown();
      this._clearDigits();
      this.digits[0].focus();
    } catch (err) {
      Chariot.Toast.error(err.message || 'Failed to resend. Try again.');
    } finally {
      Chariot.Buttons.setLoading(btn, false);
    }
  },

  _autoSubmit() {
    const code = this.digits.map(d => d.value).join('');
    if (code.length < 6) return;

    const form = $('#otpForm');
    const codeInput = $('#otpCodeInput');
    if (codeInput) codeInput.value = code;
    if (form) form.submit();
  },

  _clearDigits() {
    this.digits.forEach(d => {
      d.value = '';
      d.classList.remove('is-filled', 'is-error');
    });
  },

  showError() {
    this.digits.forEach(d => d.classList.add('is-error'));
    setTimeout(() => this.digits.forEach(d => d.classList.remove('is-error')), 800);
    this._clearDigits();
    this.digits[0].focus();
  },
};


/* ─────────────────────────────────────────────────────────────
   09. MAP MODULE (Leaflet)
   ───────────────────────────────────────────────────────────── */

Chariot.Map = {

  map:            null,
  userMarker:     null,
  driverMarkers:  {}, /* { userId: L.Marker } */
  zoneMarkers:    [],
  userCoords:     null,

  tileLayers: {
    light: null,
    dark:  null,
  },

  /* ── 09a. Init map ── */

  init(containerId = 'chariotMap') {
    const container = document.getElementById(containerId);
    if (!container || typeof L === 'undefined') return;

    this.map = L.map(containerId, {
      center:      Chariot.Config.campCenter,
      zoom:        Chariot.Config.campZoom,
      zoomControl: false,
    });

    /* ── 09b. Tile sets ── */
    this.tileLayers.light = L.tileLayer(
      'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
      {
        attribution: '© <a href="https://openstreetmap.org">OpenStreetMap</a> contributors',
        maxZoom: 19,
      }
    );

    this.tileLayers.dark = L.tileLayer(
      'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png',
      {
        attribution: '© <a href="https://openstreetmap.org">OpenStreetMap</a> contributors © <a href="https://carto.com/attributions">CARTO</a>',
        maxZoom: 20,
      }
    );

    /* Apply correct tile based on saved theme */
    const theme = Chariot.Util.store.get('chariot-theme') || 'light';
    this.tileLayers[theme].addTo(this.map);

    /* Custom zoom control placement */
    L.control.zoom({ position: 'bottomright' }).addTo(this.map);

    /* ── 09c. User location ── */
    this._locateUser();

    /* Map controls */
    this._bindMapControls();
  },

  /* ── 09b. Tile switcher on theme change (called from Theme.apply) ── */
  onThemeChange(theme) {
    if (!this.map) return;
    const prev = theme === 'dark' ? 'light' : 'dark';
    if (this.map.hasLayer(this.tileLayers[prev])) {
      this.map.removeLayer(this.tileLayers[prev]);
    }
    this.tileLayers[theme].addTo(this.map);
  },

  /* ── 09c. User location ── */
  _locateUser() {
    if (!navigator.geolocation) return;

    const geoOpts = {
      enableHighAccuracy: true,
      timeout: 12000,
      maximumAge: 0,   /* always request a fresh fix — no cached position */
    };

    navigator.geolocation.getCurrentPosition(
      pos => {
        const { latitude: lat, longitude: lng } = pos.coords;
        this.userCoords = { lat, lng };
        this._setUserMarker(lat, lng);
        this.map.setView([lat, lng], Chariot.Config.campZoom);

        /* Broadcast user coords to Rider module */
        Chariot.Rider?.onLocationKnown?.(lat, lng);

        /* Watch for movement — high accuracy + no cached positions */
        this._watchId = navigator.geolocation.watchPosition(
          p => {
            const { latitude: la, longitude: lo } = p.coords;
            this.userCoords = { lat: la, lng: lo };
            this._setUserMarker(la, lo);
            /* Keep Rider module in sync */
            Chariot.Rider?.onLocationKnown?.(la, lo);
            /* If driver — send to server */
            if (Chariot.Config.userRole() === 'driver') {
              Chariot.Driver?.broadcastLocation?.(la, lo);
            }
          },
          watchErr => {
            console.warn('[Chariot.Map] watchPosition error:', watchErr.message);
          },
          { enableHighAccuracy: true, maximumAge: 0, timeout: 15000 }
        );
      },
      err => {
        console.warn('[Chariot.Map] Geolocation unavailable:', err.message);
        /* Show a toast so the rider knows location is unavailable */
        if (Chariot.Config.userRole() === 'rider') {
          Chariot.Toast.warning(
            'Could not get your precise location. Enable GPS / location permissions for best results.',
            'Location Unavailable'
          );
        }
      },
      geoOpts
    );
  },

  /* Stop watching GPS (e.g. on page teardown) */
  _stopLocating() {
    if (this._watchId != null) {
      navigator.geolocation.clearWatch(this._watchId);
      this._watchId = null;
    }
  },

  _watchId: null,

  _setUserMarker(lat, lng) {
    const icon = L.divIcon({
      className: '',
      html: `<div class="marker-user"><div class="marker-user-dot"></div></div>`,
      iconSize: [24, 24],
      iconAnchor: [12, 12],
    });

    if (this.userMarker) {
      this.userMarker.setLatLng([lat, lng]);
    } else {
      this.userMarker = L.marker([lat, lng], { icon, zIndexOffset: 1000 })
        .addTo(this.map);
    }
  },

  /* ── 09d. Driver markers ── */

  /**
   * Add or update a driver marker.
   * @param {number} driverId
   * @param {number} lat
   * @param {number} lng
   * @param {object} info  { name, plate, seats, status }
   */
  setDriverMarker(driverId, lat, lng, info = {}) {
    const status = info.status || 'available';
    const icon = L.divIcon({
      className: '',
      html: `
        <div class="marker-driver ${status === 'offline' ? 'is-offline' : status === 'on_trip' ? 'is-busy' : ''}">
          <div class="marker-driver-inner">
            <i class="fa-solid fa-car"></i>
          </div>
        </div>`,
      iconSize: [44, 44],
      iconAnchor: [22, 44],
      popupAnchor: [0, -44],
    });

    if (this.driverMarkers[driverId]) {
      this.driverMarkers[driverId].setLatLng([lat, lng]);
      /* Update icon */
      this.driverMarkers[driverId].setIcon(icon);
    } else {
      const marker = L.marker([lat, lng], { icon })
        .bindPopup(this._buildDriverPopup(driverId, info), {
          closeButton: false,
          maxWidth: 240,
          className: 'chariot-popup',
        })
        .addTo(this.map);

      marker.on('click', () => {
        /* Highlight chip in bottom sheet */
        $$('.driver-chip').forEach(c => c.classList.remove('is-focused'));
        $(`.driver-chip[data-driver="${driverId}"]`)?.classList.add('is-focused');
      });

      this.driverMarkers[driverId] = marker;
    }
  },

  removeDriverMarker(driverId) {
    if (this.driverMarkers[driverId]) {
      this.map.removeLayer(this.driverMarkers[driverId]);
      delete this.driverMarkers[driverId];
    }
  },

  /* ── 09e. Zone markers ── */

  setZoneMarkers(zones = []) {
    /* Clear existing */
    this.zoneMarkers.forEach(m => this.map.removeLayer(m));
    this.zoneMarkers = [];

    zones.forEach(zone => {
      const icon = L.divIcon({
        className: '',
        html: `
          <div class="marker-zone">
            <div class="marker-zone-inner">
              <i class="fa-solid fa-map-pin"></i>
            </div>
          </div>`,
        iconSize: [32, 32],
        iconAnchor: [16, 32],
      });

      const marker = L.marker([zone.lat, zone.lng], { icon })
        .bindTooltip(zone.name, {
          permanent: false,
          direction: 'top',
          className: 'chariot-tooltip',
        })
        .addTo(this.map);

      this.zoneMarkers.push(marker);
    });
  },

  /* ── 09f. Map popup builder ── */

  _buildDriverPopup(driverId, info) {
    return `
      <div class="map-popup-inner">
        <div class="map-popup-name">${info.name ?? 'Driver'}</div>
        <div class="map-popup-detail">
          <i class="fa-solid fa-car text-gold"></i>
          ${info.vehicle ?? ''}
        </div>
        <div class="map-popup-detail">
          <i class="fa-solid fa-chair text-gold"></i>
          ${info.seats ?? '?'} seats available
        </div>
      </div>
      <div class="map-popup-footer">
        <button class="btn-chariot btn-primary-c btn-sm-c btn-block"
                onclick="Chariot.Rider.viewRide(${driverId})">
          View Ride
        </button>
      </div>`;
  },

  /* ── 09g. Bottom sheet toggle ── */

  _bindMapControls() {
    /* Locate-me button */
    $('#mapLocateBtn')?.addEventListener('click', () => {
      if (this.userCoords) {
        this.map.flyTo([this.userCoords.lat, this.userCoords.lng], 17, {
          animate: true, duration: 1.2
        });
      }
    });

    /* Bottom sheet drag handle */
    const sheet  = $('#bottomSheet');
    const handle = $('#bottomSheetHandle');

    if (!sheet || !handle) return;

    let startY   = 0;
    let startTop = 0;

    handle.addEventListener('click', () => {
      sheet.classList.toggle('is-collapsed');
    });

    /* Swipe up/down on handle to expand/collapse */
    handle.addEventListener('touchstart', e => {
      startY = e.touches[0].clientY;
    }, { passive: true });

    handle.addEventListener('touchend', e => {
      const deltaY = e.changedTouches[0].clientY - startY;
      if (deltaY < -30) sheet.classList.remove('is-collapsed'); /* swipe up */
      if (deltaY > 30)  sheet.classList.add('is-collapsed');    /* swipe down */
    }, { passive: true });
  },

  /** Pan map to coordinates */
  flyTo(lat, lng, zoom = 17) {
    this.map?.flyTo([lat, lng], zoom, { animate: true, duration: 1 });
  },
};


/* ─────────────────────────────────────────────────────────────
   10. REAL-TIME (Laravel Echo + Pusher/Soketi)
   ───────────────────────────────────────────────────────────── */

Chariot.Realtime = {

  echo:        null,
  connected:   false,

  /* ── 10a. Echo bootstrap ── */

  init() {
    const key  = Chariot.Config.pusherKey();
    const host = Chariot.Config.pusherHost();
    const port = Chariot.Config.pusherPort();

    /* Require Echo + Pusher JS — loaded via CDN in <head> */
    if (typeof Echo === 'undefined' || typeof Pusher === 'undefined') {
      console.warn('[Chariot.Realtime] Echo/Pusher not loaded. Real-time disabled.');
      return;
    }

    window.Pusher = Pusher;

    this.echo = new Echo({
      broadcaster:        'pusher',
      key:                key,
      wsHost:             host,
      wsPort:             parseInt(port) || 6001,
      wssPort:            parseInt(port) || 6001,
      forceTLS:           false,
      disableStats:       true,
      enabledTransports:  ['ws', 'wss'],
      authEndpoint:       '/broadcasting/auth',
      auth: {
        headers: {
          'Authorization': `Bearer ${Chariot.Config.apiToken()}`,
          'X-CSRF-TOKEN':  Chariot.Config.csrfToken(),
        },
      },
    });

    this.echo.connector.pusher.connection.bind('connected', () => {
      this.connected = true;
      this._setConnectionStatus(true);
    });

    this.echo.connector.pusher.connection.bind('disconnected', () => {
      this.connected = false;
      this._setConnectionStatus(false);
    });

    /* Subscribe based on role */
    const role = Chariot.Config.userRole();
    const uid  = Chariot.Config.userId();

    if (role === 'rider') {
      this._listenDriverLocations();
      this._listenRideStatus(uid);
    }

    if (role === 'driver') {
      this._listenIncomingRequests(uid);
    }
  },

  /* ── 10b. Rider — listen driver location updates ── */

  _listenDriverLocations() {
    this.echo.channel('drivers')
      .listen('.DriverLocationUpdated', data => {
        Chariot.Map.setDriverMarker(
          data.driver_id,
          parseFloat(data.lat),
          parseFloat(data.lng),
          { status: data.status }
        );
      });
  },

  /* ── 10c. Rider — listen own request status changes ── */

  _listenRideStatus(userId) {
    this.echo.private(`rider.${userId}`)
      .listen('.RideStatusChanged', data => {
        const status = data.status;
        if (status === 'accepted') {
          Chariot.Toast.success(
            `${data.driver_name ?? 'Your driver'} accepted your request!`,
            '🎉 Ride Confirmed'
          );
        } else if (status === 'declined') {
          Chariot.Toast.warning(
            'Your ride request was declined. Try another driver.',
            'Request Declined'
          );
        }
        Chariot.Notifications.pushNew(data);
        Chariot.Rider?.refreshMyRides?.();
      });
  },

  /* ── 10d. Driver — listen incoming ride requests ── */

  _listenIncomingRequests(userId) {
    this.echo.private(`driver.${userId}`)
      .listen('.RideRequested', data => {
        Chariot.Driver._showRequestAlert(data);
        Chariot.Notifications.pushNew(data);
        Chariot.Driver?.refreshRequests?.();
      });
  },

  /* ── 10e. Connection status indicator ── */

  _setConnectionStatus(online) {
    /* Update the dot element inside #wsStatus */
    const dot  = $('#wsStatusDot');
    const text = $('#wsStatusText');
    const wrap = $('#wsStatus');

    if (dot) {
      dot.className = online ? 'ws-status-dot online' : 'ws-status-dot';
    }
    if (text) {
      text.textContent = online ? 'Live' : 'Offline';
    }
    if (wrap) {
      wrap.title = online ? 'Live updates connected' : 'Live updates disconnected';
    }
  },
};


/* ─────────────────────────────────────────────────────────────
   11. RIDER MODULE
   ───────────────────────────────────────────────────────────── */

Chariot.Rider = {

  userLat:    null,
  userLng:    null,
  rides:      [],
  pollTimer:  null,
  _locationReady: false,
  _homeChipsFetched: false,

  /* Called by Map when GPS is available */
  onLocationKnown(lat, lng) {
    this.userLat = lat;
    this.userLng = lng;

    /* Update hidden form fields if present */
    const latInput = $('#userLat');
    const lngInput = $('#userLng');
    if (latInput) latInput.value = lat;
    if (lngInput) lngInput.value = lng;

    /* On the home page, load nearby driver chips once location is known */
    if ($('#driverChipsWrap') && !this._homeChipsFetched) {
      this._homeChipsFetched = true;
      this._fetchHomeDriverChips();
    }

    this._locationReady = true;
  },

  /* ── Fetch driver chips for the home page bottom sheet ── */
  async _fetchHomeDriverChips() {
    const wrap = $('#driverChipsWrap');
    if (!wrap) return;

    try {
      const params = new URLSearchParams();
      if (this.userLat) params.set('lat', this.userLat);
      if (this.userLng) params.set('lng', this.userLng);

      const data = await Chariot.Util.get(`/api/rides/nearby?${params}`);
      this.rides = data;

      /* Render chips */
      this._renderDriverChips(data);

      /* Also drop markers on the map */
      data.forEach(ride => {
        const p = ride.driver?.driver_profile;
        if (p?.current_lat) {
          Chariot.Map.setDriverMarker(
            ride.driver.id,
            parseFloat(p.current_lat),
            parseFloat(p.current_lng),
            {
              name: ride.driver.name,
              vehicle: `${p.vehicle_color ?? ''} ${p.vehicle_model ?? ''}`.trim(),
              seats: ride.available_seats,
              status: p.status,
            }
          );
        }
      });

      /* Update refresh timestamp */
      const ts = $('#nearbyRefreshTime');
      if (ts) ts.textContent = 'Updated just now';

    } catch (err) {
      /* Show a simple message instead of skeleton */
      const wrap = $('#driverChipsWrap');
      if (wrap) {
        wrap.innerHTML = `<span class="text-muted-c text-sm" style="padding:8px 0">Could not load nearby drivers.</span>`;
      }
    }
  },

  /* ── 11a. Find nearby rides ── */

  async findRides() {
    const toZone = $('#toZoneSelect')?.value;
    const btn    = $('#findRidesBtn');

    Chariot.Buttons.setLoading(btn, true);

    try {
      const params = new URLSearchParams();
      if (this.userLat) params.set('lat', this.userLat);
      if (this.userLng) params.set('lng', this.userLng);
      if (toZone)       params.set('to_zone_id', toZone);

      const data   = await Chariot.Util.get(`/api/rides/nearby?${params}`);
      this.rides   = data;
      this._renderRideList(data);
      this._renderDriverChips(data);

    } catch (err) {
      Chariot.Toast.error(err.message || 'Could not fetch rides. Try again.');
    } finally {
      Chariot.Buttons.setLoading(btn, false);
    }
  },

  _renderRideList(rides) {
    const container = $('#rideListContainer');
    if (!container) return;

    if (!rides.length) {
      container.innerHTML = `
        <div class="empty-state">
          <i class="fa-solid fa-car empty-state-icon"></i>
          <div class="empty-state-title">No rides found nearby</div>
          <div class="empty-state-text">
            No available rides are heading your way right now.
            Check back in a few minutes.
          </div>
        </div>`;
      return;
    }

    const count = $('#rideCountLabel');
    if (count) count.textContent = `${rides.length} ride${rides.length !== 1 ? 's' : ''} found nearby`;

    container.innerHTML = rides.map((ride, i) => this._buildRideCard(ride, i)).join('');

    /* Attach request buttons */
    $$('.btn-request-ride', container).forEach(btn => {
      btn.addEventListener('click', () => {
        const rideId = btn.dataset.rideId;
        this.requestRide(rideId, btn);
      });
    });
  },

  _buildRideCard(ride, index) {
    const driver  = ride.driver || {};
    const profile = driver.driver_profile || {};
    const from    = ride.from_zone?.name ?? '—';
    const to      = ride.to_zone?.name   ?? '—';
    const seats   = ride.available_seats ?? 0;
    const total   = ride.total_seats     ?? 0;
    const name    = driver.name          ?? 'Driver';
    const initials = name.slice(0, 2).toUpperCase();
    const plate   = profile.plate_number ?? '';
    const vehicle = `${profile.vehicle_color ?? ''} ${profile.vehicle_model ?? ''}`.trim();
    const time    = ride.departing_at ? Chariot.Util.formatTime(ride.departing_at) : 'Soon';

    /* Seat dots */
    const dots = Array.from({ length: total }, (_, i) =>
      `<span class="seat-dot ${i < (total - seats) ? 'taken' : 'available'}"></span>`
    ).join('');

    return `
      <div class="ride-card anim-fade-up anim-delay-${Math.min(index + 1, 5)}"
           data-ride-id="${ride.id}">
        <div class="ride-card-header">
          <div class="chariot-avatar avatar-md">${initials}</div>
          <div class="flex-1 min-w-0">
            <div class="ride-card-driver-name truncate">${name}</div>
            <div class="ride-card-vehicle">
              <i class="fa-solid fa-car"></i>
              ${vehicle} ${plate ? `· ${plate}` : ''}
            </div>
          </div>
          <span class="driver-badge"><i class="fa-solid fa-shield-halved"></i> Driver</span>
        </div>

        <div class="ride-card-route">
          <div class="route-from">
            <i class="fa-solid fa-location-dot" style="color:var(--clr-gold-mid)"></i>
            ${from}
          </div>
          <i class="fa-solid fa-arrow-right route-arrow"></i>
          <div class="route-to">
            <i class="fa-solid fa-flag-checkered" style="color:var(--clr-green-mid)"></i>
            ${to}
          </div>
        </div>

        <div class="ride-card-meta">
          <span class="ride-meta-item">
            <i class="fa-solid fa-chair"></i>
            ${seats} seat${seats !== 1 ? 's' : ''} left
          </span>
          <span class="ride-meta-item">
            <i class="fa-solid fa-clock"></i>
            ${time}
          </span>
          ${ride.distance_km ? `
          <span class="ride-meta-item">
            <i class="fa-solid fa-location-crosshairs"></i>
            ${ride.distance_km.toFixed(1)} km away
          </span>` : ''}
        </div>

        <div class="seat-indicators mb-3">
          ${dots}
          <span class="seat-count-text ${seats === 0 ? 'is-full' : ''}">
            ${seats === 0 ? 'Full' : `${seats}/${total} available`}
          </span>
        </div>

        <div class="ride-card-footer">
          ${ride.notes ? `
          <div class="text-xs text-muted-c truncate" style="flex:1">
            <i class="fa-solid fa-note-sticky me-1"></i>${ride.notes}
          </div>` : '<div></div>'}
          <button
            class="btn-chariot btn-primary-c btn-sm-c btn-request-ride"
            data-ride-id="${ride.id}"
            ${seats === 0 ? 'disabled' : ''}
          >
            ${seats === 0
              ? '<i class="fa-solid fa-ban"></i> Full'
              : '<i class="fa-solid fa-hand"></i> Request to Join'}
          </button>
        </div>
      </div>`;
  },

  _renderDriverChips(rides) {
    const wrap = $('#driverChipsWrap');
    if (!wrap) return;

    if (!rides.length) {
      wrap.innerHTML = `<span class="text-muted-c text-sm">No drivers nearby</span>`;
      return;
    }

    wrap.innerHTML = rides.slice(0, 8).map(ride => {
      const driver  = ride.driver || {};
      const initials = (driver.name || 'D').slice(0, 2).toUpperCase();
      const profile = driver.driver_profile || {};
      const dist    = ride.distance_km != null
        ? `${parseFloat(ride.distance_km).toFixed(1)} km`
        : '';

      return `
        <div class="driver-chip" data-driver="${driver.id}" data-ride="${ride.id}"
             title="${driver.name ?? ''}">
          <div class="chariot-avatar avatar-sm">${initials}</div>
          <div class="driver-chip-name">${(driver.name ?? '').split(' ')[0]}</div>
          ${dist ? `<div class="driver-chip-dist">${dist}</div>` : ''}
        </div>`;
    }).join('');

    /* Click chip — pan map + show popup */
    $$('.driver-chip', wrap).forEach(chip => {
      chip.addEventListener('click', () => {
        const ride = rides.find(r => String(r.id) === chip.dataset.ride);
        if (!ride?.driver?.driver_profile) return;
        const { current_lat: lat, current_lng: lng } = ride.driver.driver_profile;
        if (lat && lng) Chariot.Map.flyTo(parseFloat(lat), parseFloat(lng));

        /* Expand bottom sheet */
        $('#bottomSheet')?.classList.remove('is-collapsed');

        /* Scroll ride card into view */
        const card = $(`.ride-card[data-ride-id="${ride.id}"]`);
        if (card) Chariot.Util.scrollTo(card, 120);
      });
    });
  },

  /* ── 11b. Request a ride ── */

  async requestRide(rideId, btn) {
    if (typeof window.openRequestModal === 'function') {
      window.openRequestModal(rideId);
      return;
    }

    /* Optional pickup note */
    const note = prompt('Any pickup note for the driver? (optional)');
    if (note === null) return; // User clicked Cancel

    Chariot.Buttons.setLoading(btn, true);
    try {
      await Chariot.Util.post(`/api/rides/${rideId}/request`, { pickup_note: note });
      Chariot.Toast.success('Request sent! Waiting for driver to respond.', 'Request Sent');
      btn.disabled  = true;
      btn.innerHTML = '<i class="fa-solid fa-clock"></i> Pending…';
      this.refreshMyRides();
    } catch (err) {
      Chariot.Toast.error(err.message || 'Could not send request.');
    } finally {
      Chariot.Buttons.setLoading(btn, false);
    }
  },

  /* ── 11c. Cancel request ── */

  async cancelRequest(requestId, btn) {
    if (!confirm('Cancel this ride request?')) return;

    Chariot.Buttons.setLoading(btn, true);
    try {
      await Chariot.Util.delete(`/api/rides/${requestId}/cancel-request`);
      Chariot.Toast.info('Request cancelled.');
      btn.closest('.ride-card, .request-card, .my-ride-card')?.remove();
    } catch (err) {
      Chariot.Toast.error(err.message || 'Could not cancel request.');
    } finally {
      Chariot.Buttons.setLoading(btn, false);
    }
  },

  /* ── 11d. My rides list refresh ── */

  async refreshMyRides() {
    const container = $('#myRidesContainer');
    if (!container) return;

    try {
      /* Reload page section via full navigation (simple & reliable for MVP) */
      /* For AJAX-only: fetch /api/my-rides and re-render */
      const data  = await Chariot.Util.get('/api/my-requests');
      this._renderMyRides(data, container);
    } catch { /* silent — user can manually refresh */ }
  },

  _renderMyRides(requests, container) {
    if (!requests.length) {
      container.innerHTML = `
        <div class="empty-state">
          <i class="fa-solid fa-route empty-state-icon"></i>
          <div class="empty-state-title">No ride requests yet</div>
          <div class="empty-state-text">Find a nearby ride and request to join.</div>
        </div>`;
      return;
    }

    container.innerHTML = requests.map(req => {
      const ride   = req.ride || {};
      const driver = ride.driver || {};
      const from   = ride.from_zone?.name ?? '—';
      const to     = ride.to_zone?.name   ?? '—';

      return `
        <div class="ride-card" data-request-id="${req.id}">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <span class="badge-chariot ${req.status}">${req.status}</span>
            <span class="text-xs text-muted-c">${Chariot.Util.timeAgo(req.created_at)}</span>
          </div>
          <div class="ride-card-route mb-3">
            <div class="route-from">
              <i class="fa-solid fa-location-dot" style="color:var(--clr-gold-mid)"></i> ${from}
            </div>
            <i class="fa-solid fa-arrow-right route-arrow"></i>
            <div class="route-to">
              <i class="fa-solid fa-flag-checkered" style="color:var(--clr-green-mid)"></i> ${to}
            </div>
          </div>
          <div class="text-sm text-secondary-c mb-3">
            <i class="fa-solid fa-user me-2"></i>Driver: ${driver.name ?? '—'}
          </div>
          ${req.status === 'pending' ? `
          <button class="btn-chariot btn-danger-c btn-sm-c"
                  onclick="Chariot.Rider.cancelRequest(${req.id}, this)">
            <i class="fa-solid fa-xmark"></i> Cancel Request
          </button>` : ''}
        </div>`;
    }).join('');
  },

  /* ── 11e. View a specific ride (from map popup) ── */

  viewRide(driverId) {
    const ride = this.rides.find(r => r.driver?.id === driverId);
    if (!ride) return;
    const card = $(`.ride-card[data-ride-id="${ride.id}"]`);
    if (card) {
      const listBtn = $('.view-toggle-btn[data-view="list"]');
      if (listBtn) {
        listBtn.click();
      }
      $('#bottomSheet')?.classList.remove('is-collapsed');
      Chariot.Util.scrollTo(card, 120);
      card.classList.add('is-new');
      card.addEventListener('animationend', () => card.classList.remove('is-new'), { once: true });
    } else {
      window.location.href = `/rider/find-ride?ride_id=${ride.id}`;
    }
  },
};


/* ─────────────────────────────────────────────────────────────
   12. DRIVER MODULE
   ───────────────────────────────────────────────────────────── */

Chariot.Driver = {

  locationTimer:   null,
  isAvailable:     false,
  activeRideId:    null,

  init() {
    /* Read initial state from DOM */
    const statusEl = $('#driverStatus');
    if (statusEl) {
      this.isAvailable = statusEl.dataset.available === 'true';
      this.activeRideId = statusEl.dataset.activeRide || null;
    }

    this._initAvailabilityToggle();
    this._initCreateRideForm();
    this._initRequestActions();
    this._initCompleteRide();

    /* Start location broadcasting if already available */
    if (this.isAvailable) this._startLocationLoop();
  },

  /* ── 12a. Availability toggle ── */

  _initAvailabilityToggle() {
    const btn = $('#availabilityToggle');
    if (!btn) return;

    btn.addEventListener('click', () => this.toggleAvailability(btn));
  },

  async toggleAvailability(btn) {
    const currentIsAvailable = this.isAvailable;
    const nextIsAvailable = !currentIsAvailable;
    const nextStatus = nextIsAvailable ? 'available' : 'offline';

    // Disable button to prevent spam clicks during API request
    if (btn) btn.disabled = true;

    // Optimistically update status and UI instantly
    this.isAvailable = nextIsAvailable;
    this._updateAvailabilityUI(nextStatus);

    if (this.isAvailable) {
      this._startLocationLoop();
    } else {
      this._stopLocationLoop();
    }

    try {
      const data = await Chariot.Util.post('/api/driver/toggle-availability');
      
      // If server returned a state different from our optimistic state, sync it
      if (data.is_available !== this.isAvailable) {
        this.isAvailable = data.is_available;
        this._updateAvailabilityUI(data.status);
        if (this.isAvailable) {
          this._startLocationLoop();
        } else {
          this._stopLocationLoop();
        }
      }

      if (this.isAvailable) {
        Chariot.Toast.success('You are now available for rides.', 'Online');
      } else {
        Chariot.Toast.info('You are now offline.', 'Offline');
      }
    } catch (err) {
      // Revert to original state on error
      this.isAvailable = currentIsAvailable;
      const originalStatus = currentIsAvailable ? 'available' : 'offline';
      this._updateAvailabilityUI(originalStatus);
      if (this.isAvailable) {
        this._startLocationLoop();
      } else {
        this._stopLocationLoop();
      }
      Chariot.Toast.error(err.message || 'Could not update status.');
    } finally {
      if (btn) btn.disabled = false;
    }
  },

  _updateAvailabilityUI(status) {
    const btn      = $('#availabilityToggle');
    const statusEl = $('#driverStatusText');
    const dot      = $('#driverStatusDot');
    const rideForm = $('#createRideForm');
    const tipsCard = $('.tips-card');

    if (btn) {
      btn.className = btn.className.replace(/go-available|go-offline/, '');
      if (status === 'available') {
        btn.classList.add('go-offline');
        btn.innerHTML = '<i class="fa-solid fa-toggle-on"></i> Go Offline';
      } else {
        btn.classList.add('go-available');
        btn.innerHTML = '<i class="fa-solid fa-toggle-off"></i> Go Online';
      }
    }

    if (statusEl) {
      statusEl.textContent = status === 'available' ? 'ONLINE' : 'OFFLINE';
    }

    if (dot) {
      dot.className = `status-dot ${status === 'available' ? 'online' : 'offline'}`;
    }

    /* Show/hide create-ride form */
    if (rideForm) {
      rideForm.style.display = status === 'available' ? '' : 'none';
    }

    /* Show/hide tips card */
    if (tipsCard) {
      tipsCard.style.display = status === 'available' ? 'none' : '';
    }
  },

  /* ── 12b. Create ride form ── */

  _initCreateRideForm() {
    const form = $('#createRideForm');
    if (!form) return;

    form.addEventListener('submit', async e => {
      e.preventDefault();
      if (!Chariot.Forms.validateForm(form)) return;

      const btn  = form.querySelector('[type="submit"]');
      const departingAtValue = $('#departingAt', form).value?.trim();
      let departingAt = null;
      if (departingAtValue) {
        if (/^\d{2}:\d{2}$/.test(departingAtValue)) {
          const [hour, minute] = departingAtValue.split(':').map(Number);
          const now = new Date();
          const departure = new Date(now);
          departure.setHours(hour, minute, 0, 0);
          if (departure <= now) {
            departure.setDate(departure.getDate() + 1);
          }
          departingAt = departure.toISOString();
        } else {
          departingAt = departingAtValue;
        }
      }

      const body = {
        from_zone_id:   parseInt($('#fromZone', form).value),
        to_zone_id:     parseInt($('#toZone', form).value),
        available_seats: parseInt($('#rideSeats', form).value),
        departing_at:   departingAt,
        notes:          $('#rideNotes', form).value?.trim() || null,
      };

      Chariot.Buttons.setLoading(btn, true);
      try {
        const data = await Chariot.Util.post('/api/driver/create-ride', body);
        this.activeRideId = data.ride?.id;
        Chariot.Toast.success('Ride posted! Waiting for requests.', 'Ride Active');
        this._showActiveRideBanner(data.ride);
        form.reset();
        form.style.display = 'none';
      } catch (err) {
        Chariot.Toast.error(err.message || 'Could not post ride.');
      } finally {
        Chariot.Buttons.setLoading(btn, false);
      }
    });
  },

  _showActiveRideBanner(ride) {
    const banner = $('#activeRideBanner');
    if (!banner) return;

    const from = ride.from_zone?.name ?? '—';
    const to   = ride.to_zone?.name   ?? '—';

    banner.querySelector('.active-ride-from').textContent = from;
    banner.querySelector('.active-ride-to').textContent   = to;
    banner.querySelector('.active-ride-seats').textContent = ride.available_seats;
    banner.style.display = '';

    /* Animate in */
    banner.classList.add('anim-fade-up');
  },

  /* ── 12c. Cancel ride ── */

  async cancelRide(btn) {
    if (!confirm('Cancel your active ride? All pending requests will be declined.')) return;

    Chariot.Buttons.setLoading(btn, true);
    try {
      await Chariot.Util.delete('/api/driver/cancel-ride');
      this.activeRideId = null;
      Chariot.Toast.info('Ride cancelled.');
      $('#activeRideBanner')?.style.setProperty('display', 'none');
      $('#createRideForm').style.display = '';
    } catch (err) {
      Chariot.Toast.error(err.message || 'Could not cancel ride.');
    } finally {
      Chariot.Buttons.setLoading(btn, false);
    }
  },

  /* ── 12d. Accept / decline request ── */

  _initRequestActions() {
    /* Delegated — works for dynamically added cards */
    document.addEventListener('click', async e => {
      const acceptBtn  = e.target.closest('.btn-accept[data-request-id]');
      const declineBtn = e.target.closest('.btn-decline[data-request-id]');

      if (acceptBtn)  await this._respondToRequest(acceptBtn.dataset.requestId, 'accept',  acceptBtn);
      if (declineBtn) await this._respondToRequest(declineBtn.dataset.requestId, 'decline', declineBtn);
    });
  },

  async _respondToRequest(requestId, action, btn) {
    Chariot.Buttons.setLoading(btn, true);
    try {
      await Chariot.Util.post(`/api/driver/requests/${requestId}/${action}`);

      const card = btn.closest('.req-card');

      if (action === 'accept') {
        Chariot.Toast.success('Rider accepted!', 'Accepted');
        /* Replace card with accepted-rider row */
        const name = card?.querySelector('.req-rider-name')?.textContent ?? 'Rider';
        const row  = Chariot.Util.el('div', 'accepted-rider-row anim-scale-in');
        row.innerHTML = `
          <i class="fa-solid fa-circle-check" style="color:var(--clr-success)"></i>
          <span class="accepted-rider-name">${name}</span>
          <span class="accepted-rider-seat">Seat confirmed</span>`;
        card?.replaceWith(row);
        this._updateSeatCount(-1);
      } else {
        Chariot.Toast.info('Request declined.');
        card?.remove();
      }

    } catch (err) {
      Chariot.Toast.error(err.message || `Could not ${action} request.`);
    } finally {
      Chariot.Buttons.setLoading(btn, false);
    }
  },

  _updateSeatCount(delta) {
    const el = $('#availableSeatsCount');
    if (!el) return;
    const current = parseInt(el.textContent) || 0;
    el.textContent = Math.max(0, current + delta);
  },

  /* ── 12e. Complete ride ── */

  _initCompleteRide() {
    $('#completeRideBtn')?.addEventListener('click', () => this.completeRide());
  },

  async completeRide() {
    const btn = $('#completeRideBtn');
    if (!confirm('Mark this ride as completed?')) return;

    Chariot.Buttons.setLoading(btn, true);
    try {
      await Chariot.Util.post(`/api/driver/rides/${this.activeRideId}/complete`);
      Chariot.Toast.success('Ride completed! Great job.', 'Completed');
      this.activeRideId = null;

      /* Redirect to dashboard after short delay */
      setTimeout(() => window.location.href = '/driver/dashboard', 1500);
    } catch (err) {
      Chariot.Toast.error(err.message || 'Could not complete ride.');
    } finally {
      Chariot.Buttons.setLoading(btn, false);
    }
  },

  /* ── 12f. Location broadcast loop ── */

  watchId: null,

  _startLocationLoop() {
    this._stopLocationLoop();

    if (!Chariot.Map.userCoords && navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(
        pos => {
          const { latitude: lat, longitude: lng } = pos.coords;
          Chariot.Map.userCoords = { lat, lng };
          this.broadcastLocation(lat, lng);
        },
        null,
        { enableHighAccuracy: true }
      );

      this.watchId = navigator.geolocation.watchPosition(
        pos => {
          const { latitude: lat, longitude: lng } = pos.coords;
          Chariot.Map.userCoords = { lat, lng };
          this.broadcastLocation(lat, lng);
        },
        null,
        { enableHighAccuracy: true, maximumAge: 5000 }
      );
    }

    this.locationTimer = setInterval(() => {
      const coords = Chariot.Map.userCoords;
      if (coords) this.broadcastLocation(coords.lat, coords.lng);
    }, Chariot.Config.locationBroadcastInterval);
  },

  _stopLocationLoop() {
    clearInterval(this.locationTimer);
    this.locationTimer = null;
    if (this.watchId !== null) {
      navigator.geolocation.clearWatch(this.watchId);
      this.watchId = null;
    }
  },

  async broadcastLocation(lat, lng) {
    try {
      await Chariot.Util.post('/api/driver/location', { lat, lng });
    } catch { /* silent — GPS ping, non-critical */ }
  },

  /* Refresh requests list (called from Realtime) */
  async refreshRequests() {
    const container = $('#requestsContainer');
    if (!container) return;

    try {
      const data = await Chariot.Util.get('/api/my-requests-incoming');
      this._renderRequestCards(data, container);
    } catch { /* silent */ }
  },

  _renderRequestCards(requests, container) {
    if (!requests.length) {
      container.innerHTML = `
        <div class="empty-state py-5">
          <i class="fa-solid fa-hand empty-state-icon"></i>
          <div class="empty-state-title">No pending requests</div>
          <div class="empty-state-text">Requests from riders will appear here in real-time.</div>
        </div>`;
      return;
    }

    container.innerHTML = requests.map(req => {
      const rider = req.rider || {};
      const initials = (rider.name || 'R').slice(0, 2).toUpperCase();
      return `
        <div class="req-card is-pending anim-fade-up" data-id="${req.id}">
          <div class="req-card-top">
            <div class="chariot-avatar">${initials}</div>
            <div class="req-rider-info">
              <div class="req-rider-name">${rider.name ?? 'Rider'}</div>
              <div class="req-rider-time">${Chariot.Util.timeAgo(req.created_at)}</div>
            </div>
            <span class="badge-chariot pending">Pending</span>
          </div>
          ${req.pickup_note ? `
          <div class="req-note">
            <i class="fa-solid fa-quote-left"></i>
            <span>${req.pickup_note}</span>
          </div>` : ''}
          <div class="req-actions">
            <button class="btn-accept btn-chariot btn-success-c" data-request-id="${req.id}">
              <i class="fa-solid fa-circle-check"></i> Accept
            </button>
            <button class="btn-decline btn-chariot btn-danger-c" data-request-id="${req.id}">
              <i class="fa-solid fa-circle-xmark"></i> Decline
            </button>
          </div>
        </div>`;
    }).join('');
  },

  /* Show incoming request alert popup */
  _showRequestAlert(data) {
    const popup = $('#driverAlertPopup');
    if (!popup) return;

    const nameEl = popup.querySelector('.driver-alert-rider-name');
    if (nameEl) nameEl.textContent = data.rider_name ?? 'Someone';

    popup.classList.add('is-visible');

    /* Auto-hide after 8 seconds */
    setTimeout(() => popup.classList.remove('is-visible'), 8000);

    /* View button */
    popup.querySelector('.btn-view-alert')?.addEventListener('click', () => {
      popup.classList.remove('is-visible');
      window.location.href = '/driver/requests';
    }, { once: true });
  },
};


/* ─────────────────────────────────────────────────────────────
   13. ADMIN MODULE
   ───────────────────────────────────────────────────────────── */

Chariot.Admin = {

  searchTimer: null,

  init() {
    this._initUserSearch();
    this._initLiveRefresh();
  },

  /* ── 13a. User search & filter ── */

  _initUserSearch() {
    const searchInput  = $('#adminUserSearch');
    const roleFilter   = $('#adminRoleFilter');
    const statusFilter = $('#adminStatusFilter');

    const doSearch = Chariot.Util.debounce(() => this._filterUsers(), 400);

    searchInput?.addEventListener('input', doSearch);
    roleFilter?.addEventListener('change', () => this._filterUsers());
    statusFilter?.addEventListener('change', () => this._filterUsers());
  },

  _filterUsers() {
    const q      = ($('#adminUserSearch')?.value || '').toLowerCase();
    const role   = $('#adminRoleFilter')?.value   || '';
    const status = $('#adminStatusFilter')?.value || '';

    $$('.admin-user-row').forEach(row => {
      const name   = (row.dataset.name   || '').toLowerCase();
      const rData  = row.dataset.role    || '';
      const active = row.dataset.active  || '';

      const matchQ      = !q      || name.includes(q);
      const matchRole   = !role   || rData === role;
      const matchStatus = !status || active === status;

      row.style.display = (matchQ && matchRole && matchStatus) ? '' : 'none';
    });

    /* Update visible count */
    const visible = $$('.admin-user-row').filter(r => r.style.display !== 'none').length;
    const countEl = $('#visibleUserCount');
    if (countEl) countEl.textContent = visible;
  },

  /* ── 13b. Toggle user active ── */

  async toggleUser(userId, btn) {
    Chariot.Buttons.setLoading(btn, true);
    try {
      const data = await Chariot.Util.patch(`/admin/users/${userId}/toggle`);
      const row  = btn.closest('.admin-user-row, tr');

      /* Update badge in row */
      const badge = row?.querySelector('.user-active-badge');
      if (badge) {
        const isActive = data.is_active;
        badge.className = `status-dot ${isActive ? 'online' : 'offline'}`;
        badge.title     = isActive ? 'Active' : 'Inactive';
      }

      btn.textContent = data.is_active ? 'Deactivate' : 'Activate';
      Chariot.Toast.success(`User ${data.is_active ? 'activated' : 'deactivated'}.`);
    } catch (err) {
      Chariot.Toast.error(err.message || 'Action failed.');
    } finally {
      Chariot.Buttons.setLoading(btn, false);
    }
  },

  /* ── 13c. Verify user ── */

  async verifyUser(userId, btn) {
    if (!confirm('Mark this member as verified?')) return;

    Chariot.Buttons.setLoading(btn, true);
    try {
      await Chariot.Util.patch(`/admin/users/${userId}/verify`);
      const row = btn.closest('.admin-user-row, tr');

      /* Replace verify button with verified badge */
      btn.replaceWith((() => {
        const badge = el('span', 'verified-badge');
        badge.innerHTML = '<i class="fa-solid fa-circle-check"></i> Verified';
        return badge;
      })());

      row?.querySelector('.user-verified-cell')?.classList.add('text-success');
      Chariot.Toast.success('Member verified successfully.');
    } catch (err) {
      Chariot.Toast.error(err.message || 'Could not verify member.');
    } finally {
      Chariot.Buttons.setLoading(btn, false);
    }
  },

  /* ── 13d. Change role ── */

  async changeRole(userId, newRole, btn) {
    if (!confirm(`Change this user's role to ${newRole}?`)) return;

    Chariot.Buttons.setLoading(btn, true);
    try {
      await Chariot.Util.patch(`/admin/users/${userId}/role`, { role: newRole });
      Chariot.Toast.success(`Role updated to ${newRole}.`);

      const row       = btn.closest('.admin-user-row, tr');
      const roleCell  = row?.querySelector('.user-role-cell');
      if (roleCell) roleCell.textContent = newRole;
    } catch (err) {
      Chariot.Toast.error(err.message || 'Could not update role.');
    } finally {
      Chariot.Buttons.setLoading(btn, false);
    }
  },

  /* ── 13e. Live ride table refresh ── */

  _initLiveRefresh() {
    const table = $('#adminRidesTable');
    if (!table) return;

    /* Refresh rides table every 20s */
    setInterval(() => this._refreshRidesTable(), 20000);
  },

  async _refreshRidesTable() {
    try {
      const data = await Chariot.Util.get('/api/admin/rides-live');
      const tbody = $('#adminRidesTable tbody');
      if (!tbody || !data.rides) return;

      tbody.innerHTML = data.rides.map(ride => `
        <tr class="admin-user-row">
          <td class="td-name">
            <div class="chariot-avatar avatar-sm">${(ride.driver?.name ?? 'D').slice(0,2).toUpperCase()}</div>
            <div>
              <div class="name-text">${ride.driver?.name ?? '—'}</div>
              <div class="sub-text">${ride.driver?.driver_profile?.plate_number ?? ''}</div>
            </div>
          </td>
          <td>${ride.from_zone?.name ?? '—'}</td>
          <td>${ride.to_zone?.name ?? '—'}</td>
          <td>
            <span class="seat-count-text">${ride.available_seats}/${ride.total_seats}</span>
          </td>
          <td><span class="badge-chariot ${ride.status}">${ride.status}</span></td>
        </tr>`).join('');

      /* Update stat */
      const activeCount = $('#adminActiveRideCount');
      if (activeCount) activeCount.textContent = data.active_count ?? rides.length;

    } catch { /* silent */ }
  },
};


/* ─────────────────────────────────────────────────────────────
   14. PROFILE MODULE
   ───────────────────────────────────────────────────────────── */

Chariot.Profile = {

  init() {
    this._initThemePicker();
    this._initSaveProfile();
  },

  /* ── 14a. Save profile ── */

  _initSaveProfile() {
    const form = $('#profileForm');
    if (!form) return;

    form.addEventListener('submit', async e => {
      e.preventDefault();
      if (!Chariot.Forms.validateForm(form)) return;

      const btn  = form.querySelector('[type="submit"]');
      const body = {
        name:  $('#profileName')?.value.trim(),
        email: $('#profileEmail')?.value.trim() || null,
      };

      Chariot.Buttons.setLoading(btn, true);
      try {
        await Chariot.Util.put('/rider/profile', body);
        Chariot.Toast.success('Profile updated successfully.', 'Saved');

        /* Update navbar avatar initials */
        const initials = (body.name || '').slice(0, 2).toUpperCase();
        $$('.chariot-avatar.navbar-avatar').forEach(a => a.textContent = initials);
      } catch (err) {
        Chariot.Toast.error(err.message || 'Could not save profile.');
      } finally {
        Chariot.Buttons.setLoading(btn, false);
      }
    });
  },

  /* ── 14b. Theme picker sync ── */

  _initThemePicker() {
    $$('.theme-option').forEach(opt => {
      opt.addEventListener('click', () => {
        Chariot.Theme.apply(opt.dataset.theme);
      });
    });
  },
};


/* ─────────────────────────────────────────────────────────────
   15. INIT — Boot sequence on DOMContentLoaded
   ───────────────────────────────────────────────────────────── */

document.addEventListener('DOMContentLoaded', () => {

  /* ── Core (every page) ── */
  Chariot.Theme.init();
  Chariot.Nav.init();
  Chariot.Toast.init();
  Chariot.Buttons.init();
  Chariot.Forms.init();
  Chariot.Notifications.init();

  /* ── OTP page ── */
  if ($('.otp-digit')) {
    Chariot.OTP.init();
  }

  /* ── Map pages (rider home + find-ride) ── */
  if ($('#chariotMap') && typeof L !== 'undefined') {
    Chariot.Map.init('chariotMap');
  }

  /* ── Real-time (riders + drivers) ── */
  const role = Chariot.Config.userRole();
  if (role === 'rider' || role === 'driver') {
    /* Echo loads async from CDN — wait for it */
    const tryEcho = (attempts = 0) => {
      if (typeof Echo !== 'undefined') {
        Chariot.Realtime.init();
      } else if (attempts < 15) {
        setTimeout(() => tryEcho(attempts + 1), 400);
      } else {
        console.warn('[Chariot] Echo not available — real-time disabled. Using polling.');
        /* Fall back to polling */
        setInterval(() => {
          Chariot.Notifications.fetchCount();
          if (role === 'rider')  Chariot.Rider?.refreshMyRides?.();
          if (role === 'driver') Chariot.Driver?.refreshRequests?.();
        }, Chariot.Config.pollInterval);
      }
    };
    tryEcho();
  }

  /* ── Role-specific inits ── */
  if (role === 'rider') {
    /* Auto-load nearby rides on find-ride page.
       NOTE: find-ride.blade.php also calls findRides() in its own DOMContentLoaded,
       but that inline script runs AFTER chariot.js (which is loaded before @stack('scripts')).
       We guard with a flag to prevent a double-call. */
    if ($('#rideListContainer') && !Chariot.Rider._findRidesAutoStarted) {
      Chariot.Rider._findRidesAutoStarted = true;
      Chariot.Rider.findRides();

      /* Zone selects trigger re-search */
      $('#toZoneSelect')?.addEventListener('change', () => Chariot.Rider.findRides());
      $('#findRidesBtn')?.addEventListener('click',  () => Chariot.Rider.findRides());
    }
  }

  if (role === 'driver') {
    Chariot.Driver.init();

    /* Cancel ride button (delegated in case page differs) */
    document.addEventListener('click', e => {
      const btn = e.target.closest('#cancelRideBtn, .btn-cancel-ride[data-driver]');
      if (btn) Chariot.Driver.cancelRide(btn);
    });
  }

  if (role === 'admin') {
    Chariot.Admin.init();
  }

  /* ── Profile page ── */
  if ($('#profileForm')) {
    Chariot.Profile.init();
  }

  /* ── Tab pills (My Rides page) ── */
  $$('.chariot-tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const target = btn.dataset.target;

      /* Deactivate all tabs */
      $$('.chariot-tab-btn').forEach(b => b.classList.remove('is-active'));
      $$('.chariot-tab-pane').forEach(p => p.classList.remove('is-active'));

      /* Activate selected */
      btn.classList.add('is-active');
      $(target)?.classList.add('is-active');
    });
  });

  /* ── View toggle (List / Map) ── */
  $$('.view-toggle-btn').forEach(btn => {
    btn.addEventListener('click', () => {
      const view = btn.dataset.view;
      $$('.view-toggle-btn').forEach(b => b.classList.remove('is-active'));
      btn.classList.add('is-active');

      const listView = $('#rideListView');
      const mapView  = $('#rideMapView');

      if (view === 'list') {
        Chariot.Util.show(listView);
        Chariot.Util.hide(mapView);
      } else {
        Chariot.Util.hide(listView);
        Chariot.Util.show(mapView);
        /* Invalidate map size in case it was hidden on init */
        setTimeout(() => Chariot.Map.map?.invalidateSize(), 100);
      }
    });
  });

  /* ── Flash session alerts → toast ── */
  /* Blade sets data-flash-* on body if session has messages */
  const body = document.body;
  if (body.dataset.flashSuccess) Chariot.Toast.success(body.dataset.flashSuccess);
  if (body.dataset.flashError)   Chariot.Toast.error(body.dataset.flashError);
  if (body.dataset.flashInfo)    Chariot.Toast.info(body.dataset.flashInfo);
  if (body.dataset.flashWarning) Chariot.Toast.warning(body.dataset.flashWarning);
});


/* ─────────────────────────────────────────────────────────────
   EXPOSE to global window for Blade inline onclick="" handlers
   ───────────────────────────────────────────────────────────── */

window.ChariotRider  = Chariot.Rider;
window.ChariotDriver = Chariot.Driver;
window.ChariotAdmin  = Chariot.Admin;
window.ChariotToast  = Chariot.Toast;


/* =============================================================
   END OF CHARIOT.JS
   Version 1.0 · RCCG Camp Chariot
   ============================================================= */
