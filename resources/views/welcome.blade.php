{{--
=======================================================================
  welcome.blade.php  —  RCCG Camp Chariot  |  Landing / Marketing Page
  Route  : GET /                            |  PageController@welcome
  Guard  : guest (redirect to /rider/home or /driver/dashboard if auth)
  Assets : chariot.css  +  chariot.js  (loaded here, scoped below)
=======================================================================
  DEPENDENCIES LOADED ON THIS PAGE
  ─────────────────────────────────────────────────────────────────────
  ✅ Google Fonts       — Cinzel + DM Sans (used every page)
  ✅ Bootstrap 5.3 CSS  — layout utilities
  ✅ Bootstrap 5.3 JS   — navbar collapse, no dropdowns needed here
  ✅ Font Awesome 6.5   — icons throughout
  ✅ chariot.css        — global design system
  ✅ chariot.js         — Theme + Nav + Toast + Buttons (boots via DOMContentLoaded)

  ❌ Leaflet CSS/JS     — only on: rider/home.blade.php, rider/find-ride.blade.php
  ❌ Laravel Echo       — only on: rider/home, rider/find-ride, rider/my-rides,
                                   driver/dashboard, driver/requests
  ❌ Pusher JS          — same as Echo above
=======================================================================
--}}
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <meta name="description" content="RCCG Camp Chariot — Smart mobility and ride-sharing for RCCG Camp Ground. Find rides, share seats, move together.">
  <meta name="theme-color" content="#0D3B1F">

  {{-- CSRF token — not needed on this page (no forms), but set globally --}}
  <meta name="csrf-token" content="{{ csrf_token() }}">

  {{--
    user-id / user-role / api-token / pusher-* meta tags:
    NOT needed on this (guest) page.
    They are set in layouts/app.blade.php for authenticated pages.
    Leaving them out keeps this page lean.
  --}}

  <title>Chariot — Smart Mobility for the Saints | RCCG Camp</title>

  {{-- ═══════════════════════════════════════════════════════════
       FONTS — used globally, load on every page
       ═══════════════════════════════════════════════════════════ --}}
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;900&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,300&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">

  {{-- ═══════════════════════════════════════════════════════════
       BOOTSTRAP 5.3 CSS — used globally
       ═══════════════════════════════════════════════════════════ --}}
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css">

  {{-- ═══════════════════════════════════════════════════════════
       FONT AWESOME 6.5 — used globally
       ═══════════════════════════════════════════════════════════ --}}
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  {{-- ═══════════════════════════════════════════════════════════
       CHARIOT GLOBAL CSS — used globally
       ═══════════════════════════════════════════════════════════ --}}
  <link rel="stylesheet" href="{{ asset('css/chariot.css') }}">

  {{-- ═══════════════════════════════════════════════════════════
       ❌ LEAFLET CSS — NOT on this page
       Load only on: rider/home, rider/find-ride
       <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
       ═══════════════════════════════════════════════════════════ --}}

  {{-- ══════════════════════════════════════════════════════════
       PAGE-SPECIFIC STYLES — welcome.blade.php only
       ══════════════════════════════════════════════════════════ --}}
  <style>
    /* ── Landing: body has no padding-top (no sticky navbar here) ── */
    body {
      overflow-x: hidden;
    }

    /* ════════════════════════════════════════════
       MINIMAL NAVBAR (landing only — not the full
       app navbar, no auth controls needed)
       ════════════════════════════════════════════ */
    .landing-nav {
      position: fixed;
      top: 0; left: 0; right: 0;
      height: 60px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 var(--sp-5);
      z-index: 1040;
      transition: background 0.4s ease, box-shadow 0.4s ease;
    }

    .landing-nav.is-scrolled {
      background: rgba(7, 26, 15, 0.96);
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
      box-shadow: 0 2px 24px rgba(0,0,0,0.4);
      border-bottom: 1px solid rgba(201, 162, 39, 0.2);
    }

    .landing-nav-brand {
      display: flex;
      align-items: center;
      gap: var(--sp-3);
      text-decoration: none;
    }

    .landing-nav-brand img {
      width: 34px;
      height: 34px;
      filter: drop-shadow(0 2px 6px rgba(201,162,39,0.4));
    }

    .landing-nav-brand-name {
      font-family: var(--font-display);
      font-size: var(--text-base);
      font-weight: var(--fw-black);
      color: var(--clr-gold-mid);
      letter-spacing: var(--ls-widest);
    }

    .landing-nav-links {
      display: flex;
      align-items: center;
      gap: var(--sp-2);
    }

    .landing-nav-link {
      color: rgba(255,255,255,0.75);
      font-size: var(--text-sm);
      font-weight: var(--fw-medium);
      text-decoration: none;
      padding: var(--sp-2) var(--sp-3);
      border-radius: var(--radius-md);
      transition: var(--transition-fast);
    }

    .landing-nav-link:hover {
      color: white;
      background: rgba(255,255,255,0.08);
    }

    .landing-nav-cta {
      padding: var(--sp-2) var(--sp-5);
      background: linear-gradient(135deg, var(--clr-gold-dark), var(--clr-gold-bright));
      color: var(--clr-green-darkest) !important;
      font-weight: var(--fw-bold);
      border-radius: var(--radius-pill);
      font-size: var(--text-sm);
      box-shadow: var(--shadow-gold);
      transition: var(--transition-base);
    }

    .landing-nav-cta:hover {
      transform: translateY(-1px);
      box-shadow: var(--shadow-gold-lg);
      color: var(--clr-green-darkest) !important;
    }

    /* ════════════════════════════════════════════
       HERO SECTION
       ════════════════════════════════════════════ */
    .hero-section {
      min-height: 100vh;
      position: relative;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      text-align: center;
      padding: 100px var(--sp-5) var(--sp-16);
      overflow: hidden;
    }

    /* Deep layered background */
    .hero-bg {
      position: absolute;
      inset: 0;
      background:
        radial-gradient(ellipse 80% 60% at 50% 0%, rgba(21,84,40,0.35) 0%, transparent 70%),
        radial-gradient(ellipse 60% 50% at 20% 100%, rgba(201,162,39,0.08) 0%, transparent 60%),
        radial-gradient(ellipse 60% 50% at 80% 100%, rgba(13,59,31,0.2) 0%, transparent 60%),
        linear-gradient(180deg, #071A0F 0%, #030d06 60%, #0A0A0A 100%);
      z-index: 0;
    }

    /* Animated grid overlay */
    .hero-grid {
      position: absolute;
      inset: 0;
      background-image:
        linear-gradient(rgba(201,162,39,0.035) 1px, transparent 1px),
        linear-gradient(90deg, rgba(201,162,39,0.035) 1px, transparent 1px);
      background-size: 48px 48px;
      z-index: 1;
      mask-image: radial-gradient(ellipse 90% 70% at 50% 50%, black 0%, transparent 100%);
      -webkit-mask-image: radial-gradient(ellipse 90% 70% at 50% 50%, black 0%, transparent 100%);
    }

    /* Floating gold particles */
    .hero-particles {
      position: absolute;
      inset: 0;
      z-index: 2;
      pointer-events: none;
      overflow: hidden;
    }

    .particle {
      position: absolute;
      width: 3px;
      height: 3px;
      border-radius: 50%;
      background: var(--clr-gold-mid);
      opacity: 0;
      animation: floatParticle linear infinite;
    }

    @keyframes floatParticle {
      0%   { opacity: 0; transform: translateY(0) scale(0); }
      10%  { opacity: 0.6; transform: translateY(-20px) scale(1); }
      90%  { opacity: 0.3; }
      100% { opacity: 0; transform: translateY(-180px) scale(0.5); }
    }

    /* Hero content sits above bg layers */
    .hero-content {
      position: relative;
      z-index: 10;
      max-width: 760px;
      width: 100%;
    }

    .hero-logo-wrap {
      margin-bottom: var(--sp-6);
      animation: fadeUp 0.8s ease both;
    }

    .hero-logo {
      width: 96px;
      height: 96px;
      margin: 0 auto;
      filter: drop-shadow(0 8px 40px rgba(201,162,39,0.45));
      transition: filter 0.3s;
    }

    .hero-logo:hover {
      filter: drop-shadow(0 12px 52px rgba(201,162,39,0.7));
    }

    .hero-eyebrow {
      display: inline-flex;
      align-items: center;
      gap: var(--sp-2);
      font-size: var(--text-xs);
      font-weight: var(--fw-semi);
      letter-spacing: var(--ls-widest);
      text-transform: uppercase;
      color: var(--clr-gold-mid);
      background: rgba(201,162,39,0.08);
      border: 1px solid rgba(201,162,39,0.2);
      border-radius: var(--radius-pill);
      padding: var(--sp-1) var(--sp-4);
      margin-bottom: var(--sp-5);
      animation: fadeUp 0.8s 0.05s ease both;
    }

    .hero-eyebrow::before {
      content: '';
      width: 6px;
      height: 6px;
      border-radius: 50%;
      background: var(--clr-gold-mid);
      animation: dotPulse 2s infinite;
    }

    .hero-title {
      font-family: var(--font-display);
      font-size: clamp(3rem, 14vw, 6.5rem);
      font-weight: var(--fw-black);
      color: var(--clr-gold-mid);
      letter-spacing: 0.12em;
      line-height: 0.95;
      margin-bottom: var(--sp-5);
      text-shadow:
        0 0 80px rgba(201,162,39,0.25),
        0 0 120px rgba(201,162,39,0.1);
      animation: fadeUp 0.8s 0.1s ease both;
    }

    .hero-tagline {
      font-size: clamp(var(--text-md), 3.5vw, var(--text-xl));
      font-weight: var(--fw-light);
      color: rgba(255,255,255,0.88);
      letter-spacing: var(--ls-wide);
      margin-bottom: var(--sp-3);
      animation: fadeUp 0.8s 0.2s ease both;
    }

    .hero-sub {
      font-size: var(--text-xs);
      font-weight: var(--fw-semi);
      color: rgba(255,255,255,0.3);
      letter-spacing: var(--ls-widest);
      text-transform: uppercase;
      margin-bottom: var(--sp-10);
      animation: fadeUp 0.8s 0.3s ease both;
    }

    .hero-actions {
      display: flex;
      flex-wrap: wrap;
      gap: var(--sp-3);
      justify-content: center;
      animation: fadeUp 0.8s 0.4s ease both;
    }

    /* Primary CTA — gold, large */
    .btn-hero-primary {
      display: inline-flex;
      align-items: center;
      gap: var(--sp-3);
      padding: var(--sp-4) var(--sp-8);
      background: linear-gradient(135deg, var(--clr-gold-dark), var(--clr-gold-bright));
      color: var(--clr-green-darkest);
      font-family: var(--font-body);
      font-size: var(--text-base);
      font-weight: var(--fw-bold);
      border-radius: var(--radius-pill);
      text-decoration: none;
      box-shadow: var(--shadow-gold), 0 0 40px rgba(201,162,39,0.15);
      transition: var(--transition-smooth);
      letter-spacing: 0.01em;
      position: relative;
      overflow: hidden;
      border: none;
      cursor: pointer;
    }

    .btn-hero-primary::before {
      content: '';
      position: absolute;
      top: 0; left: -100%;
      width: 60%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.25), transparent);
      transform: skewX(-20deg);
      animation: btnSheen 3.5s ease-in-out infinite;
    }

    @keyframes btnSheen {
      0%   { left: -100%; }
      60%  { left: 140%; }
      100% { left: 140%; }
    }

    .btn-hero-primary:hover {
      transform: translateY(-3px);
      box-shadow: var(--shadow-gold-lg), 0 0 60px rgba(201,162,39,0.25);
      color: var(--clr-green-darkest);
    }

    .btn-hero-primary:active { transform: translateY(-1px); }

    /* Secondary CTA — ghost */
    .btn-hero-secondary {
      display: inline-flex;
      align-items: center;
      gap: var(--sp-3);
      padding: var(--sp-4) var(--sp-8);
      background: rgba(255,255,255,0.05);
      color: rgba(255,255,255,0.82);
      font-family: var(--font-body);
      font-size: var(--text-base);
      font-weight: var(--fw-medium);
      border-radius: var(--radius-pill);
      text-decoration: none;
      border: 1.5px solid rgba(255,255,255,0.15);
      transition: var(--transition-base);
      letter-spacing: 0.01em;
    }

    .btn-hero-secondary:hover {
      background: rgba(255,255,255,0.1);
      border-color: rgba(255,255,255,0.3);
      color: white;
      transform: translateY(-2px);
    }

    /* Scroll indicator */
    .hero-scroll-hint {
      position: absolute;
      bottom: var(--sp-8);
      left: 50%;
      transform: translateX(-50%);
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: var(--sp-2);
      color: rgba(255,255,255,0.25);
      font-size: var(--text-xs);
      letter-spacing: var(--ls-widest);
      text-transform: uppercase;
      z-index: 10;
      animation: fadeIn 1s 1s ease both;
    }

    .scroll-mouse {
      width: 22px;
      height: 34px;
      border: 1.5px solid rgba(255,255,255,0.2);
      border-radius: 11px;
      display: flex;
      align-items: flex-start;
      justify-content: center;
      padding-top: 6px;
    }

    .scroll-wheel {
      width: 3px;
      height: 7px;
      border-radius: 2px;
      background: rgba(255,255,255,0.35);
      animation: scrollWheel 2s ease-in-out infinite;
    }

    @keyframes scrollWheel {
      0%, 100% { transform: translateY(0); opacity: 0.8; }
      50%       { transform: translateY(6px); opacity: 0.2; }
    }

    /* Hero bottom shimmer line */
    .hero-bottom-line {
      position: absolute;
      bottom: 0; left: 0; right: 0;
      height: 1px;
      background: linear-gradient(90deg, transparent, rgba(201,162,39,0.4), transparent);
      z-index: 10;
    }

    /* ════════════════════════════════════════════
       STATS STRIP
       ════════════════════════════════════════════ */
    .stats-strip {
      background: linear-gradient(135deg, var(--clr-green-deep) 0%, var(--clr-green-darkest) 100%);
      border-top: 1px solid rgba(201,162,39,0.15);
      border-bottom: 1px solid rgba(201,162,39,0.15);
      padding: var(--sp-8) var(--sp-5);
    }

    .stats-strip-grid {
      max-width: 900px;
      margin: 0 auto;
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 1px;
      background: rgba(201,162,39,0.12);
      border-radius: var(--radius-lg);
      overflow: hidden;
      border: 1px solid rgba(201,162,39,0.12);
    }

    @media (min-width: 576px) {
      .stats-strip-grid { grid-template-columns: repeat(4, 1fr); }
    }

    .stat-item {
      background: rgba(13,59,31,0.8);
      padding: var(--sp-5) var(--sp-4);
      text-align: center;
    }

    .stat-num {
      font-family: var(--font-display);
      font-size: clamp(var(--text-2xl), 5vw, var(--text-4xl));
      font-weight: var(--fw-black);
      color: var(--clr-gold-mid);
      line-height: 1;
      margin-bottom: var(--sp-1);
      letter-spacing: var(--ls-tight);
    }

    .stat-label {
      font-size: var(--text-xs);
      font-weight: var(--fw-semi);
      color: rgba(255,255,255,0.45);
      letter-spacing: var(--ls-widest);
      text-transform: uppercase;
    }

    /* ════════════════════════════════════════════
       HOW IT WORKS
       ════════════════════════════════════════════ */
    .how-section {
      background: #060e08;
      padding: var(--sp-16) var(--sp-5);
      position: relative;
      overflow: hidden;
    }

    /* Subtle radial glow top-right */
    .how-section::before {
      content: '';
      position: absolute;
      top: -100px; right: -100px;
      width: 500px; height: 500px;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(21,84,40,0.12) 0%, transparent 70%);
      pointer-events: none;
    }

    .section-header {
      text-align: center;
      max-width: 560px;
      margin: 0 auto var(--sp-12);
    }

    .section-eyebrow {
      display: inline-block;
      font-size: var(--text-xs);
      font-weight: var(--fw-semi);
      letter-spacing: var(--ls-widest);
      text-transform: uppercase;
      color: var(--clr-gold-mid);
      margin-bottom: var(--sp-3);
    }

    .section-title {
      font-family: var(--font-display);
      font-size: clamp(var(--text-2xl), 5vw, var(--text-4xl));
      font-weight: var(--fw-black);
      color: white;
      letter-spacing: var(--ls-tight);
      line-height: var(--lh-tight);
      margin-bottom: var(--sp-4);
    }

    .section-desc {
      font-size: var(--text-base);
      color: rgba(255,255,255,0.5);
      line-height: var(--lh-relaxed);
    }

    .how-steps-grid {
      max-width: 900px;
      margin: 0 auto;
      display: grid;
      grid-template-columns: 1fr;
      gap: var(--sp-4);
      position: relative;
    }

    @media (min-width: 640px) {
      .how-steps-grid { grid-template-columns: repeat(3, 1fr); }
    }

    /* Connector line between steps (desktop only) */
    @media (min-width: 640px) {
      .how-steps-grid::before {
        content: '';
        position: absolute;
        top: 52px;
        left: calc(33% - 12px);
        right: calc(33% - 12px);
        height: 1px;
        background: linear-gradient(90deg, var(--clr-gold-mid), rgba(201,162,39,0.2), var(--clr-gold-mid));
        z-index: 0;
      }
    }

    .how-step-card {
      background: rgba(255,255,255,0.025);
      border: 1px solid rgba(201,162,39,0.1);
      border-radius: var(--radius-xl);
      padding: var(--sp-6);
      text-align: center;
      transition: var(--transition-smooth);
      position: relative;
      z-index: 1;
    }

    .how-step-card:hover {
      background: rgba(201,162,39,0.04);
      border-color: rgba(201,162,39,0.4);
      transform: translateY(-6px);
      box-shadow: 0 20px 40px rgba(0,0,0,0.4), 0 0 40px rgba(201,162,39,0.06);
    }

    .how-step-number {
      font-family: var(--font-display);
      font-size: var(--text-xs);
      font-weight: var(--fw-black);
      color: rgba(201,162,39,0.35);
      letter-spacing: var(--ls-widest);
      text-transform: uppercase;
      margin-bottom: var(--sp-4);
    }

    .how-step-icon-wrap {
      width: 72px;
      height: 72px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--clr-gold-dark), var(--clr-gold-bright));
      color: var(--clr-green-darkest);
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto var(--sp-5);
      font-size: 1.5rem;
      box-shadow: var(--shadow-gold), 0 0 0 8px rgba(201,162,39,0.06);
      transition: var(--transition-base);
    }

    .how-step-card:hover .how-step-icon-wrap {
      box-shadow: var(--shadow-gold-lg), 0 0 0 12px rgba(201,162,39,0.08);
      transform: scale(1.05);
    }

    .how-step-title {
      font-size: var(--text-base);
      font-weight: var(--fw-bold);
      color: white;
      margin-bottom: var(--sp-2);
    }

    .how-step-desc {
      font-size: var(--text-sm);
      color: rgba(255,255,255,0.45);
      line-height: var(--lh-relaxed);
    }

    /* ════════════════════════════════════════════
       FOR RIDERS  vs  FOR DRIVERS — Two-column
       ════════════════════════════════════════════ */
    .features-section {
      background: var(--clr-gray-950);
      padding: var(--sp-16) var(--sp-5);
      position: relative;
    }

    .features-grid {
      max-width: 960px;
      margin: 0 auto;
      display: grid;
      grid-template-columns: 1fr;
      gap: var(--sp-5);
    }

    @media (min-width: 768px) {
      .features-grid { grid-template-columns: 1fr 1fr; }
    }

    .feature-card {
      border-radius: var(--radius-2xl);
      padding: var(--sp-8) var(--sp-6);
      position: relative;
      overflow: hidden;
    }

    .feature-card.rider-card {
      background: linear-gradient(135deg, rgba(13,59,31,0.9) 0%, rgba(21,84,40,0.6) 100%);
      border: 1px solid rgba(30,122,60,0.3);
    }

    .feature-card.driver-card {
      background: linear-gradient(135deg, rgba(92,69,0,0.7) 0%, rgba(139,110,26,0.4) 100%);
      border: 1px solid rgba(201,162,39,0.25);
    }

    /* Card decorative circle */
    .feature-card::before {
      content: '';
      position: absolute;
      top: -60px; right: -60px;
      width: 200px; height: 200px;
      border-radius: 50%;
      opacity: 0.08;
    }

    .feature-card.rider-card::before  { background: var(--clr-green-mid); }
    .feature-card.driver-card::before { background: var(--clr-gold-mid); }

    .feature-tag {
      display: inline-flex;
      align-items: center;
      gap: var(--sp-2);
      padding: var(--sp-1) var(--sp-3);
      border-radius: var(--radius-pill);
      font-size: var(--text-xs);
      font-weight: var(--fw-bold);
      letter-spacing: var(--ls-wide);
      text-transform: uppercase;
      margin-bottom: var(--sp-5);
    }

    .feature-card.rider-card .feature-tag {
      background: rgba(30,122,60,0.2);
      color: var(--clr-green-light);
      border: 1px solid rgba(30,122,60,0.3);
    }

    .feature-card.driver-card .feature-tag {
      background: rgba(201,162,39,0.15);
      color: var(--clr-gold-mid);
      border: 1px solid rgba(201,162,39,0.25);
    }

    .feature-card-title {
      font-family: var(--font-display);
      font-size: clamp(var(--text-xl), 3.5vw, var(--text-3xl));
      font-weight: var(--fw-black);
      color: white;
      letter-spacing: var(--ls-tight);
      line-height: var(--lh-tight);
      margin-bottom: var(--sp-3);
    }

    .feature-card-desc {
      font-size: var(--text-sm);
      color: rgba(255,255,255,0.55);
      line-height: var(--lh-relaxed);
      margin-bottom: var(--sp-6);
    }

    .feature-list {
      list-style: none;
      padding: 0;
      margin: 0 0 var(--sp-7);
      display: flex;
      flex-direction: column;
      gap: var(--sp-3);
    }

    .feature-list li {
      display: flex;
      align-items: flex-start;
      gap: var(--sp-3);
      font-size: var(--text-sm);
      color: rgba(255,255,255,0.7);
      line-height: var(--lh-snug);
    }

    .feature-list li i {
      font-size: 0.75rem;
      margin-top: 3px;
      flex-shrink: 0;
    }

    .feature-card.rider-card .feature-list li i  { color: var(--clr-green-light); }
    .feature-card.driver-card .feature-list li i { color: var(--clr-gold-mid); }

    /* ════════════════════════════════════════════
       TRUST / WHY CHARIOT
       ════════════════════════════════════════════ */
    .trust-section {
      background: #060e08;
      padding: var(--sp-16) var(--sp-5);
      text-align: center;
    }

    .trust-pills {
      display: flex;
      flex-wrap: wrap;
      gap: var(--sp-3);
      justify-content: center;
      max-width: 700px;
      margin: 0 auto var(--sp-10);
    }

    .trust-pill {
      display: inline-flex;
      align-items: center;
      gap: var(--sp-2);
      padding: var(--sp-2) var(--sp-4);
      background: rgba(255,255,255,0.04);
      border: 1px solid rgba(255,255,255,0.08);
      border-radius: var(--radius-pill);
      color: rgba(255,255,255,0.65);
      font-size: var(--text-sm);
      font-weight: var(--fw-medium);
      transition: var(--transition-base);
    }

    .trust-pill i { color: var(--clr-gold-mid); font-size: 0.85rem; }

    .trust-pill:hover {
      background: rgba(201,162,39,0.06);
      border-color: rgba(201,162,39,0.2);
      color: rgba(255,255,255,0.9);
    }

    /* ════════════════════════════════════════════
       FINAL CTA SECTION
       ════════════════════════════════════════════ */
    .cta-section {
      position: relative;
      overflow: hidden;
      padding: var(--sp-20) var(--sp-5);
      background: linear-gradient(
        180deg,
        var(--clr-green-darkest) 0%,
        var(--clr-green-deep) 50%,
        var(--clr-green-darkest) 100%
      );
      border-top: 2px solid var(--clr-gold-mid);
      text-align: center;
    }

    .cta-section::before {
      content: '';
      position: absolute;
      inset: 0;
      background-image:
        linear-gradient(rgba(201,162,39,0.03) 1px, transparent 1px),
        linear-gradient(90deg, rgba(201,162,39,0.03) 1px, transparent 1px);
      background-size: 40px 40px;
      pointer-events: none;
    }

    .cta-glow {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      width: 600px;
      height: 300px;
      background: radial-gradient(ellipse, rgba(201,162,39,0.08) 0%, transparent 70%);
      pointer-events: none;
    }

    .cta-title {
      font-family: var(--font-display);
      font-size: clamp(var(--text-2xl), 6vw, var(--text-4xl));
      font-weight: var(--fw-black);
      color: white;
      letter-spacing: var(--ls-tight);
      line-height: var(--lh-tight);
      margin-bottom: var(--sp-3);
      position: relative;
      z-index: 1;
    }

    .cta-title span { color: var(--clr-gold-mid); }

    .cta-sub {
      font-size: var(--text-base);
      color: rgba(255,255,255,0.55);
      margin-bottom: var(--sp-8);
      position: relative;
      z-index: 1;
    }

    .cta-actions {
      display: flex;
      flex-wrap: wrap;
      gap: var(--sp-4);
      justify-content: center;
      position: relative;
      z-index: 1;
    }

    /* ════════════════════════════════════════════
       FOOTER
       ════════════════════════════════════════════ */
    .landing-footer {
      background: var(--clr-black);
      padding: var(--sp-8) var(--sp-5);
      border-top: 1px solid rgba(201,162,39,0.08);
    }

    .landing-footer-inner {
      max-width: 900px;
      margin: 0 auto;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: var(--sp-4);
      text-align: center;
    }

    @media (min-width: 640px) {
      .landing-footer-inner {
        flex-direction: row;
        justify-content: space-between;
        text-align: left;
      }
    }

    .footer-brand {
      display: flex;
      align-items: center;
      gap: var(--sp-3);
    }

    .footer-brand img { width: 28px; height: 28px; }

    .footer-brand-name {
      font-family: var(--font-display);
      font-size: var(--text-base);
      font-weight: var(--fw-black);
      color: var(--clr-gold-mid);
      letter-spacing: var(--ls-wider);
    }

    .footer-copy {
      font-size: var(--text-xs);
      color: rgba(255,255,255,0.2);
      letter-spacing: 0.02em;
    }

    .footer-links {
      display: flex;
      gap: var(--sp-5);
    }

    .footer-link {
      font-size: var(--text-xs);
      color: rgba(255,255,255,0.3);
      text-decoration: none;
      transition: color 0.2s;
    }

    .footer-link:hover { color: var(--clr-gold-mid); }

    /* ════════════════════════════════════════════
       SCROLL-TRIGGERED REVEAL
       ════════════════════════════════════════════ */
    .reveal {
      opacity: 0;
      transform: translateY(32px);
      transition: opacity 0.65s cubic-bezier(0.4,0,0.2,1),
                  transform 0.65s cubic-bezier(0.4,0,0.2,1);
    }

    .reveal.is-visible {
      opacity: 1;
      transform: translateY(0);
    }

    .reveal-delay-1 { transition-delay: 0.1s; }
    .reveal-delay-2 { transition-delay: 0.2s; }
    .reveal-delay-3 { transition-delay: 0.3s; }
    .reveal-delay-4 { transition-delay: 0.4s; }

    /* ════════════════════════════════════════════
       MOBILE TWEAKS
       ════════════════════════════════════════════ */
    @media (max-width: 576px) {
      .hero-actions { flex-direction: column; align-items: center; }
      .btn-hero-primary,
      .btn-hero-secondary { width: 100%; max-width: 320px; justify-content: center; }
      .landing-nav-link.d-hide-sm { display: none; }
    }
  </style>
</head>

<body>
  {{-- ═══════════════════════════════════════════════════════════════
       LANDING NAVBAR
       No auth controls — just brand + Login + Get Started
       ═══════════════════════════════════════════════════════════════ --}}
  <nav class="landing-nav" id="landingNav">
    <a href="{{ url('/') }}" class="landing-nav-brand">
      <img src="{{ asset('images/chariot-logo.png') }}" alt="Chariot" width="34" height="34">
      <span class="landing-nav-brand-name">CHARIOT</span>
    </a>

    <div class="landing-nav-links">
      <a href="#how-it-works" class="landing-nav-link d-hide-sm">How it works</a>
      <a href="#for-drivers"  class="landing-nav-link d-hide-sm">For Drivers</a>
      <a href="{{ route('login') }}"    class="landing-nav-link">Sign in</a>
      <a href="{{ route('register') }}" class="landing-nav-cta landing-nav-link ms-1">
        Get Started
      </a>
    </div>
  </nav>


  {{-- ═══════════════════════════════════════════════════════════════
       HERO
       ═══════════════════════════════════════════════════════════════ --}}
  <section class="hero-section" id="hero">
    <div class="hero-bg"></div>
    <div class="hero-grid"></div>

    {{-- Floating particles (JS-generated for random positions) --}}
    <div class="hero-particles" id="heroParticles"></div>

    <div class="hero-content">

      <div class="hero-logo-wrap">
        <img
          src="{{ asset('images/chariot-logo.png') }}"
          alt="RCCG Camp Chariot"
          class="hero-logo"
          width="96"
          height="96"
        >
      </div>

      <div class="hero-eyebrow">
        <i class="fa-solid fa-church" style="font-size:10px"></i>
        RCCG Camp Ground · Smart Mobility
      </div>

      <h1 class="hero-title">CHARIOT</h1>

      <p class="hero-tagline">Ride with Purpose.</p>
      <p class="hero-sub">Smart mobility for the saints</p>

      <div class="hero-actions">
        <a href="{{ route('register') }}" class="btn-hero-primary">
          <i class="fa-solid fa-car"></i>
          Start Your Journey
        </a>
        <a href="#how-it-works" class="btn-hero-secondary">
          <i class="fa-solid fa-circle-play"></i>
          See How It Works
        </a>
      </div>
    </div>

    {{-- Scroll hint --}}
    <div class="hero-scroll-hint">
      <div class="scroll-mouse">
        <div class="scroll-wheel"></div>
      </div>
      <span>Scroll</span>
    </div>

    <div class="hero-bottom-line"></div>
  </section>


  {{-- ═══════════════════════════════════════════════════════════════
       STATS STRIP
       ═══════════════════════════════════════════════════════════════ --}}
  <section class="stats-strip">
    <div class="stats-strip-grid reveal">
      <div class="stat-item">
        <div class="stat-num">10M+</div>
        <div class="stat-label">Camp Attendees</div>
      </div>
      <div class="stat-item">
        <div class="stat-num">Zero</div>
        <div class="stat-label">Cost to Ride</div>
      </div>
      <div class="stat-item">
        <div class="stat-num">Live</div>
        <div class="stat-label">Location Sharing</div>
      </div>
      <div class="stat-item">
        <div class="stat-num">Trusted</div>
        <div class="stat-label">Verified Members</div>
      </div>
    </div>
  </section>


  {{-- ═══════════════════════════════════════════════════════════════
       HOW IT WORKS
       ═══════════════════════════════════════════════════════════════ --}}
  <section class="how-section" id="how-it-works">
    <div class="container-fluid px-0" style="max-width:960px; margin:0 auto">

      <div class="section-header reveal">
        <span class="section-eyebrow">Simple Process</span>
        <h2 class="section-title">Move smarter<br>in three steps</h2>
        <p class="section-desc">
          No waiting, no confusion. Find a trusted ride heading your way
          and get there together — all within the camp grounds.
        </p>
      </div>

      <div class="how-steps-grid">

        <div class="how-step-card reveal reveal-delay-1">
          <div class="how-step-number">Step 01</div>
          <div class="how-step-icon-wrap">
            <i class="fa-solid fa-user-plus"></i>
          </div>
          <h3 class="how-step-title">Register & Verify</h3>
          <p class="how-step-desc">
            Create your account as a rider or driver. Verified RCCG members
            only — your safety comes first.
          </p>
        </div>

        <div class="how-step-card reveal reveal-delay-2">
          <div class="how-step-number">Step 02</div>
          <div class="how-step-icon-wrap">
            <i class="fa-solid fa-location-dot"></i>
          </div>
          <h3 class="how-step-title">Find Nearby Rides</h3>
          <p class="how-step-desc">
            See available vehicles on a live map heading toward your destination
            zone within the camp ground.
          </p>
        </div>

        <div class="how-step-card reveal reveal-delay-3">
          <div class="how-step-number">Step 03</div>
          <div class="how-step-icon-wrap">
            <i class="fa-solid fa-handshake"></i>
          </div>
          <h3 class="how-step-title">Request & Ride</h3>
          <p class="how-step-desc">
            Send a ride request, get confirmed by the driver, and move together.
            Safe, coordinated, community-driven.
          </p>
        </div>

      </div>
    </div>
  </section>


  {{-- ═══════════════════════════════════════════════════════════════
       FOR RIDERS  vs  FOR DRIVERS
       ═══════════════════════════════════════════════════════════════ --}}
  <section class="features-section" id="for-drivers">
    <div class="container-fluid px-0" style="max-width:960px; margin:0 auto">

      <div class="section-header reveal" style="margin-bottom: var(--sp-10)">
        <span class="section-eyebrow">Built for Everyone</span>
        <h2 class="section-title">Whether you're riding<br>or driving</h2>
      </div>

      <div class="features-grid">

        {{-- RIDERS card --}}
        <div class="feature-card rider-card reveal reveal-delay-1">
          <div class="feature-tag">
            <i class="fa-solid fa-user"></i>
            For Riders
          </div>
          <h3 class="feature-card-title">Get where you're going, together.</h3>
          <p class="feature-card-desc">
            No more standing at the roadside. Discover available rides heading
            your direction — instantly.
          </p>
          <ul class="feature-list">
            <li>
              <i class="fa-solid fa-circle-check"></i>
              View drivers near you on a live map
            </li>
            <li>
              <i class="fa-solid fa-circle-check"></i>
              Filter rides by destination zone
            </li>
            <li>
              <i class="fa-solid fa-circle-check"></i>
              Request to join with one tap
            </li>
            <li>
              <i class="fa-solid fa-circle-check"></i>
              Get real-time confirmation from driver
            </li>
            <li>
              <i class="fa-solid fa-circle-check"></i>
              See vehicle details before boarding
            </li>
          </ul>
          <a href="{{ route('register') }}" class="btn-hero-secondary" style="display:inline-flex; font-size:var(--text-sm)">
            <i class="fa-solid fa-arrow-right"></i>
            Find a Ride Now
          </a>
        </div>

        {{-- DRIVERS card --}}
        <div class="feature-card driver-card reveal reveal-delay-2">
          <div class="feature-tag">
            <i class="fa-solid fa-car"></i>
            For Drivers
          </div>
          <h3 class="feature-card-title">Fill your empty seats. Bless others.</h3>
          <p class="feature-card-desc">
            You're already driving through camp. Help fellow saints move — and
            reduce the congestion for everyone.
          </p>
          <ul class="feature-list">
            <li>
              <i class="fa-solid fa-circle-check"></i>
              Toggle availability with one tap
            </li>
            <li>
              <i class="fa-solid fa-circle-check"></i>
              Post your route and available seats
            </li>
            <li>
              <i class="fa-solid fa-circle-check"></i>
              Receive and manage ride requests
            </li>
            <li>
              <i class="fa-solid fa-circle-check"></i>
              Accept or decline — full control
            </li>
            <li>
              <i class="fa-solid fa-circle-check"></i>
              Your location updates automatically
            </li>
          </ul>
          <a href="{{ route('register') }}?role=driver" class="btn-hero-primary" style="display:inline-flex; font-size:var(--text-sm)">
            <i class="fa-solid fa-car"></i>
            Register as Driver
          </a>
        </div>

      </div>
    </div>
  </section>


  {{-- ═══════════════════════════════════════════════════════════════
       TRUST SIGNALS
       ═══════════════════════════════════════════════════════════════ --}}
  <section class="trust-section">
    <div class="container-fluid" style="max-width:800px; margin:0 auto">

      <div class="section-header reveal" style="margin-bottom:var(--sp-8)">
        <span class="section-eyebrow">Why Chariot</span>
        <h2 class="section-title">Built on trust,<br>powered by community</h2>
      </div>

      <div class="trust-pills reveal">
        <div class="trust-pill">
          <i class="fa-solid fa-shield-halved"></i>
          Verified Members Only
        </div>
        <div class="trust-pill">
          <i class="fa-solid fa-location-dot"></i>
          Live GPS Tracking
        </div>
        <div class="trust-pill">
          <i class="fa-solid fa-bolt"></i>
          Real-Time Matching
        </div>
        <div class="trust-pill">
          <i class="fa-solid fa-lock"></i>
          Secure &amp; Private
        </div>
        <div class="trust-pill">
          <i class="fa-solid fa-people-group"></i>
          Community Driven
        </div>
        <div class="trust-pill">
          <i class="fa-solid fa-church"></i>
          RCCG Camp Exclusive
        </div>
        <div class="trust-pill">
          <i class="fa-solid fa-mobile-screen"></i>
          No App Download
        </div>
        <div class="trust-pill">
          <i class="fa-solid fa-coins"></i>
          Completely Free
        </div>
      </div>

    </div>
  </section>


  {{-- ═══════════════════════════════════════════════════════════════
       FINAL CTA
       ═══════════════════════════════════════════════════════════════ --}}
  <section class="cta-section">
    <div class="cta-glow"></div>

    <div class="container-fluid" style="max-width:700px; margin:0 auto; position:relative; z-index:1">
      <div class="reveal">
        {{-- Gold divider --}}
        <div style="width:48px; height:3px; background: var(--clr-gold-mid); border-radius:999px; margin: 0 auto var(--sp-6)"></div>

        <h2 class="cta-title">
          Ready to <span>ride with</span><br>purpose?
        </h2>
        <p class="cta-sub">
          Join the movement. Smarter travel starts with you.
        </p>

        <div class="cta-actions">
          <a href="{{ route('register') }}" class="btn-hero-primary">
            <i class="fa-solid fa-car"></i>
            Create Free Account
          </a>
          <a href="{{ route('login') }}" class="btn-hero-secondary">
            <i class="fa-solid fa-right-to-bracket"></i>
            Sign In
          </a>
        </div>
      </div>
    </div>
  </section>


  {{-- ═══════════════════════════════════════════════════════════════
       FOOTER
       ═══════════════════════════════════════════════════════════════ --}}
  <footer class="landing-footer">
    <div class="landing-footer-inner">
      <div class="footer-brand">
        <img src="{{ asset('images/chariot-logo.png') }}" alt="Chariot" width="28" height="28">
        <span class="footer-brand-name">CHARIOT</span>
      </div>

      <p class="footer-copy">
        &copy; {{ date('Y') }} RCCG Camp Chariot &mdash; Smart Mobility for the Saints
      </p>

      <div class="footer-links">
        <a href="{{ route('login') }}"    class="footer-link">Sign In</a>
        <a href="{{ route('register') }}" class="footer-link">Register</a>
      </div>
    </div>
  </footer>


  {{-- ═══════════════════════════════════════════════════════════════
       BOOTSTRAP 5 JS — needed globally (modal/collapse/dropdown)
       ═══════════════════════════════════════════════════════════════ --}}
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>

  {{-- ═══════════════════════════════════════════════════════════════
       ❌ LEAFLET JS — NOT on this page
       Load only on: rider/home.blade.php, rider/find-ride.blade.php
       <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
       ═══════════════════════════════════════════════════════════════ --}}

  {{-- ═══════════════════════════════════════════════════════════════
       ❌ PUSHER JS — NOT on this page
       Load only on: rider/home, rider/find-ride, rider/my-rides,
                     driver/dashboard, driver/requests
       <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
       ═══════════════════════════════════════════════════════════════ --}}

  {{-- ═══════════════════════════════════════════════════════════════
       ❌ LARAVEL ECHO — NOT on this page
       Load only on pages with real-time listeners (same as Pusher above)
       <script src="{{ asset('js/echo.js') }}"></script>
       ═══════════════════════════════════════════════════════════════ --}}

  {{-- ═══════════════════════════════════════════════════════════════
       CHARIOT GLOBAL JS — loads on every page
       Boots: Theme + Nav + Toast + Buttons + Forms (DOMContentLoaded)
       Role-specific modules (Rider/Driver/Admin) only activate when
       their meta[name="user-role"] tag is present in the <head>.
       On this guest page: only Theme.init() and Nav.init() run.
       ═══════════════════════════════════════════════════════════════ --}}
  <script src="{{ asset('js/chariot.js') }}"></script>

  {{-- ═══════════════════════════════════════════════════════════════
       PAGE-SPECIFIC SCRIPT — welcome.blade.php only
       ═══════════════════════════════════════════════════════════════ --}}
  <script>
    /* ──────────────────────────────────────────────────────────
       1. NAVBAR — glass effect on scroll
       ────────────────────────────────────────────────────────── */
    (function () {
      const nav = document.getElementById('landingNav');
      if (!nav) return;

      const onScroll = () => {
        nav.classList.toggle('is-scrolled', window.scrollY > 40);
      };

      window.addEventListener('scroll', onScroll, { passive: true });
      onScroll(); /* run once on load */
    })();


    /* ──────────────────────────────────────────────────────────
       2. SMOOTH SCROLL — anchor links (#how-it-works, #for-drivers)
       ────────────────────────────────────────────────────────── */
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function (e) {
        const target = document.querySelector(this.getAttribute('href'));
        if (!target) return;
        e.preventDefault();
        const offset = 72; /* account for fixed nav height */
        const top = target.getBoundingClientRect().top + window.pageYOffset - offset;
        window.scrollTo({ top, behavior: 'smooth' });
      });
    });


    /* ──────────────────────────────────────────────────────────
       3. SCROLL REVEAL — Intersection Observer
       Adds .is-visible to any .reveal element when it enters viewport
       ────────────────────────────────────────────────────────── */
    (function () {
      const els = document.querySelectorAll('.reveal');
      if (!els.length) return;

      const observer = new IntersectionObserver(
        (entries) => {
          entries.forEach(entry => {
            if (entry.isIntersecting) {
              entry.target.classList.add('is-visible');
              observer.unobserve(entry.target); /* fire once */
            }
          });
        },
        { threshold: 0.12, rootMargin: '0px 0px -40px 0px' }
      );

      els.forEach(el => observer.observe(el));
    })();


    /* ──────────────────────────────────────────────────────────
       4. HERO PARTICLES — spawn floating gold dots
       ────────────────────────────────────────────────────────── */
    (function () {
      const container = document.getElementById('heroParticles');
      if (!container) return;

      const PARTICLE_COUNT = 22;

      for (let i = 0; i < PARTICLE_COUNT; i++) {
        const p = document.createElement('span');
        p.className = 'particle';

        /* Random position */
        const left     = Math.random() * 100;
        const top      = 20 + Math.random() * 70;
        const duration = 6 + Math.random() * 10;   /* 6–16s */
        const delay    = Math.random() * 8;          /* 0–8s stagger */
        const size     = 2 + Math.random() * 3;      /* 2–5px */

        p.style.cssText = `
          left: ${left}%;
          top: ${top}%;
          width: ${size}px;
          height: ${size}px;
          animation-duration: ${duration}s;
          animation-delay: ${delay}s;
        `;

        container.appendChild(p);
      }
    })();


    /* ──────────────────────────────────────────────────────────
       5. STATS COUNTER ANIMATION
       Counts up numbers when they enter viewport
       ────────────────────────────────────────────────────────── */
    (function () {
      /* Only animate numeric stat values */
      const statNums = document.querySelectorAll('.stat-num');

      const numericStats = [
        { el: statNums[0], target: 10, suffix: 'M+',   prefix: '' },
        /* Index 1 "Zero", 2 "Live", 3 "Trusted" are text — skip */
      ];

      const animateCount = (el, target, suffix, prefix, duration = 1800) => {
        const start     = performance.now();
        const startVal  = 0;

        const step = (now) => {
          const elapsed  = now - start;
          const progress = Math.min(elapsed / duration, 1);
          /* Ease out cubic */
          const eased    = 1 - Math.pow(1 - progress, 3);
          const current  = Math.round(startVal + (target - startVal) * eased);
          el.textContent = `${prefix}${current}${suffix}`;
          if (progress < 1) requestAnimationFrame(step);
        };

        requestAnimationFrame(step);
      };

      const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            numericStats.forEach(({ el, target, suffix, prefix }) => {
              animateCount(el, target, suffix, prefix);
            });
            observer.disconnect();
          }
        });
      }, { threshold: 0.5 });

      /* Observe the stats strip */
      const strip = document.querySelector('.stats-strip');
      if (strip) observer.observe(strip);
    })();


    /* ──────────────────────────────────────────────────────────
       6. THEME TOGGLE — already handled by chariot.js Chariot.Theme
       But on this public landing page there's no theme toggle button.
       The page is intentionally always dark (it's a marketing page
       against a dark bg). Chariot.Theme still reads localStorage
       for when the user navigates to the app — no action needed here.
       ────────────────────────────────────────────────────────── */

  </script>

</body>
</html>
