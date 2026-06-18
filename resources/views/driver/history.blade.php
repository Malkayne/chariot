<!-- ===================================================================
  [C]  driver/history.blade.php
  ====================================================================
  Cut everything between the [C] markers into:
      resources/views/driver/history.blade.php
  Route    : GET /driver/history     (DriverDashboardController@history)
  Middleware: auth, verified.member, is.driver
  Data from controller:
      $rides          — Collection<Ride> status=completed, paginated 15/page
      $totalTrips     — int
      $totalRiders    — int
      $thisWeekTrips  — int
  ================================================================== -->
<!-- ======================== [C] START ============================= -->
@extends('layouts.app')

@section('title', 'Trip History')

@push('styles')
<style>
  /* ════════════════════════════════════════════
     HISTORY SUMMARY CARD
     ════════════════════════════════════════════ */
  .history-summary {
    background: linear-gradient(135deg, var(--clr-green-deep), var(--clr-green-darkest));
    border-radius: var(--radius-xl);
    padding: var(--sp-6) var(--sp-5);
    margin-bottom: var(--sp-5);
    position: relative;
    overflow: hidden;
    border: 1px solid rgba(201,162,39,0.15);
  }

  .history-summary::before {
    content: '';
    position: absolute;
    top: -50px; right: -50px;
    width: 180px; height: 180px;
    border-radius: 50%;
    border: 1px solid rgba(201,162,39,0.07);
    pointer-events: none;
  }

  .history-summary-inner { position: relative; z-index: 1; }

  .history-summary-top {
    display: flex;
    align-items: center;
    gap: var(--sp-3);
    margin-bottom: var(--sp-5);
  }

  .history-summary-icon {
    width: 48px; height: 48px;
    border-radius: var(--radius-md);
    background: rgba(201,162,39,0.15);
    border: 1px solid rgba(201,162,39,0.25);
    display: flex; align-items: center; justify-content: center;
    color: var(--clr-gold-mid);
    font-size: 1.4rem;
    flex-shrink: 0;
  }

  .history-summary-title {
    font-size: var(--text-lg);
    font-weight: var(--fw-bold);
    color: white;
    line-height: 1.2;
  }

  .history-summary-sub {
    font-size: var(--text-sm);
    color: rgba(255,255,255,0.45);
    margin-top: 2px;
  }

  .history-stats-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1px;
    background: rgba(255,255,255,0.06);
    border-radius: var(--radius-lg);
    overflow: hidden;
  }

  .history-stat {
    background: rgba(0,0,0,0.2);
    padding: var(--sp-4) var(--sp-3);
    text-align: center;
  }

  .history-stat-val {
    font-family: var(--font-display);
    font-size: var(--text-2xl);
    font-weight: var(--fw-black);
    color: var(--clr-gold-mid);
    line-height: 1;
    margin-bottom: 2px;
  }

  .history-stat-lbl {
    font-size: 10px;
    font-weight: var(--fw-semi);
    color: rgba(255,255,255,0.35);
    text-transform: uppercase;
    letter-spacing: var(--ls-wider);
  }

  /* ════════════════════════════════════════════
     HISTORY TRIP CARD
     ════════════════════════════════════════════ */
  .history-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-card);
    margin-bottom: var(--sp-3);
    overflow: hidden;
    transition: var(--transition-base);
    animation: fadeUp 0.3s ease both;
  }

  .history-card:nth-child(1) { animation-delay: 0.04s; }
  .history-card:nth-child(2) { animation-delay: 0.08s; }
  .history-card:nth-child(3) { animation-delay: 0.12s; }
  .history-card:nth-child(4) { animation-delay: 0.16s; }
  .history-card:nth-child(5) { animation-delay: 0.20s; }

  .history-card:hover {
    box-shadow: var(--shadow-md);
    transform: translateY(-2px);
  }

  /* Green left bar */
  .history-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0;
    width: 3px;
    height: 100%;
    background: var(--clr-green-mid);
    border-radius: 3px 0 0 3px;
  }

  /* Make relative for the ::before pseudo */
  .history-card { position: relative; }

  .history-card-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: var(--sp-4) var(--sp-5) var(--sp-3) var(--sp-6);
    gap: var(--sp-3);
    flex-wrap: wrap;
  }

  .history-route {
    display: flex;
    align-items: center;
    gap: var(--sp-2);
    font-size: var(--text-sm);
    font-weight: var(--fw-semi);
    color: var(--text-primary);
    flex-wrap: wrap;
  }

  .history-route .h-arrow {
    color: var(--clr-gold-mid);
    font-size: 0.75rem;
    flex-shrink: 0;
  }

  .history-date {
    font-size: var(--text-xs);
    color: var(--text-muted);
    white-space: nowrap;
  }

  .history-card-body {
    display: flex;
    align-items: center;
    gap: var(--sp-4);
    padding: 0 var(--sp-5) var(--sp-4) var(--sp-6);
    flex-wrap: wrap;
  }

  .history-meta-item {
    display: flex;
    align-items: center;
    gap: var(--sp-2);
    font-size: var(--text-xs);
    color: var(--text-secondary);
  }

  .history-meta-item i {
    color: var(--clr-gold-mid);
    font-size: 11px;
    width: 14px;
    text-align: center;
  }

  /* Rider avatars row */
  .rider-avatars {
    display: flex;
    margin-left: auto;
  }

  .rider-avatar-mini {
    width: 24px; height: 24px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--clr-green-dark), var(--clr-green-mid));
    color: white;
    font-size: 8px;
    font-weight: var(--fw-bold);
    display: flex; align-items: center; justify-content: center;
    border: 2px solid var(--bg-card);
    margin-left: -6px;
    flex-shrink: 0;
  }

  .rider-avatar-mini:first-child { margin-left: 0; }

  .rider-avatar-more {
    background: var(--bg-card-alt);
    color: var(--text-muted);
    font-size: 9px;
    font-weight: var(--fw-semi);
  }

  /* ════════════════════════════════════════════
     PAGINATION
     ════════════════════════════════════════════ -->
  .history-pagination {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--sp-2);
    padding: var(--sp-5) 0 var(--sp-8);
  }

  /* Use Bootstrap pagination for MVP, override styling */
  .page-item .page-link {
    background: var(--bg-card);
    border-color: var(--border-color);
    color: var(--text-secondary);
    font-size: var(--text-sm);
    font-weight: var(--fw-medium);
    border-radius: var(--radius-md) !important;
    padding: var(--sp-2) var(--sp-3);
    transition: var(--transition-fast);
  }

  .page-item .page-link:hover {
    background: var(--bg-card-alt);
    border-color: var(--clr-gold-mid);
    color: var(--clr-gold-mid);
  }

  .page-item.active .page-link {
    background: var(--clr-green-deep);
    border-color: var(--clr-green-deep);
    color: white;
    box-shadow: var(--shadow-green);
  }

  .page-item.disabled .page-link { opacity: 0.4; }
</style>
@endpush

@section('content')
<div class="chariot-content-inner">

  <!-- ── PAGE HEADER ── -->
  <div class="page-title">Trip History</div>
  <p class="page-subtitle">All your completed rides.</p>

  <!-- ════════════════════════════════════════
       SUMMARY CARD
       ════════════════════════════════════════ -->
  <div class="history-summary">
    <div class="history-summary-inner">
      <div class="history-summary-top">
        <div class="history-summary-icon">
          <i class="fa-solid fa-car-side"></i>
        </div>
        <div>
          <div class="history-summary-title">Your Impact</div>
          <div class="history-summary-sub">Every trip reduces congestion in camp.</div>
        </div>
      </div>

      <div class="history-stats-row">
        <div class="history-stat">
          <div class="history-stat-val">{{ $totalTrips ?? 0 }}</div>
          <div class="history-stat-lbl">Total Trips</div>
        </div>
        <div class="history-stat">
          <div class="history-stat-val">{{ $totalRiders ?? 0 }}</div>
          <div class="history-stat-lbl">Riders Helped</div>
        </div>
        <div class="history-stat">
          <div class="history-stat-val">{{ $thisWeekTrips ?? 0 }}</div>
          <div class="history-stat-lbl">This Week</div>
        </div>
      </div>
    </div>
  </div>


  <!-- ════════════════════════════════════════
       TRIP LIST
       ════════════════════════════════════════ -->
  <div class="section-label mb-3">Past Trips</div>

  @forelse($rides as $ride)
    @php
      $accepted = $ride->requests->where('status', 'accepted');
      $count    = $accepted->count();
      $riders   = $accepted->take(3)->map(fn($r) => $r->rider);
    @endphp

    <div class="history-card">
      <div class="history-card-top">
        <div class="history-route">
          <i class="fa-solid fa-location-dot" style="color:var(--clr-gold-mid);font-size:.85rem"></i>
          {{ $ride->fromZone->name }}
          <i class="fa-solid fa-arrow-right h-arrow"></i>
          <i class="fa-solid fa-flag-checkered" style="color:var(--clr-green-mid);font-size:.85rem"></i>
          {{ $ride->toZone->name }}
        </div>
        <div class="history-date">
          {{ $ride->updated_at->format('M j, g:i A') }}
        </div>
      </div>

      <div class="history-card-body">
        <div class="history-meta-item">
          <i class="fa-solid fa-users"></i>
          {{ $count }} rider{{ $count !== 1 ? 's' : '' }}
        </div>
        <div class="history-meta-item">
          <i class="fa-solid fa-chair"></i>
          {{ $ride->total_seats - $ride->available_seats }} / {{ $ride->total_seats }} seats used
        </div>
        @if($ride->departing_at)
          <div class="history-meta-item">
            <i class="fa-solid fa-clock"></i>
            {{ \Carbon\Carbon::parse($ride->departing_at)->format('g:i A') }}
          </div>
        @endif
        <span class="badge-chariot completed ms-auto" style="flex-shrink:0">Completed</span>

        @if($count > 0)
          <div class="rider-avatars">
            @foreach($riders as $rider)
              <div class="rider-avatar-mini" title="{{ $rider->name }}">
                {{ strtoupper(substr($rider->name, 0, 2)) }}
              </div>
            @endforeach
            @if($count > 3)
              <div class="rider-avatar-mini rider-avatar-more">+{{ $count - 3 }}</div>
            @endif
          </div>
        @endif
      </div>
    </div>

  @empty
    <div class="empty-state">
      <i class="fa-solid fa-car-side empty-state-icon"></i>
      <div class="empty-state-title">No trips yet</div>
      <div class="empty-state-text">
        Complete your first ride and it will appear here.<br>
        Every trip you make helps reduce congestion on camp ground.
      </div>
      <a href="{{ route('driver.dashboard') }}" class="btn-chariot btn-primary-c btn-sm-c mt-3">
        <i class="fa-solid fa-gauge-high"></i>
        Go to Dashboard
      </a>
    </div>
  @endforelse

  <!-- ════════════════════════════════════════
       PAGINATION
       ════════════════════════════════════════ -->
  @if($rides instanceof \Illuminate\Pagination\LengthAwarePaginator && $rides->hasPages())
    <div class="history-pagination">
      {{ $rides->links('pagination::bootstrap-5') }}
    </div>
  @endif

</div><!-- /content-inner -->
@endsection

@push('scripts')
<script>
/* ================================================================
   driver/history.blade.php — page script
   Minimal — this page is static (no real-time needed).
   ================================================================ */

document.addEventListener('DOMContentLoaded', function () {

  /* ── Animate history stat values counting up ── */
  const statEls = document.querySelectorAll('.history-stat-val');

  const animateCount = (el, target, duration = 1200) => {
    if (isNaN(target) || target === 0) return;
    const start = performance.now();
    const step  = (now) => {
      const progress = Math.min((now - start) / duration, 1);
      const eased    = 1 - Math.pow(1 - progress, 3);
      el.textContent = Math.round(target * eased);
      if (progress < 1) requestAnimationFrame(step);
    };
    requestAnimationFrame(step);
  };

  /* Trigger on intersection */
  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (!entry.isIntersecting) return;
      statEls.forEach(el => {
        const val = parseInt(el.textContent);
        if (!isNaN(val)) animateCount(el, val);
      });
      observer.disconnect();
    });
  }, { threshold: 0.4 });

  const summaryCard = document.querySelector('.history-summary');
  if (summaryCard) observer.observe(summaryCard);

});
</script>
@endpush
<!-- ========================= [C] END ============================== -->