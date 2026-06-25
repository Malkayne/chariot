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
                onclick="AdminZones.edit({{ $zone->id }}, this)"
                data-name="{{ $zone->name }}"
                data-code="{{ $zone->short_code }}"
                data-desc="{{ $zone->description ?? '' }}"
                data-lat="{{ $zone->lat }}"
                data-lng="{{ $zone->lng }}"
              >
                <i class="fa-solid fa-pen"></i>
              </button>
              <button
                class="zone-action-btn del"
                title="Delete zone"
                onclick="AdminZones.delete({{ $zone->id }}, this)"
                data-name="{{ $zone->name }}"
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
@php
  $zonesData = [];
  foreach($zones as $z) {
    $zonesData[] = [
      'id' => $z->id,
      'name' => $z->name,
      'code' => $z->short_code,
      'lat' => (float) $z->lat,
      'lng' => (float) $z->lng,
      'active' => $z->is_active,
    ];
  }
@endphp
<script>
/* ================================================================
   admin/zones.blade.php — page script
   ================================================================ */

/* ── ZONE DATA from blade (passed to JS for map init) ── */
const ZONES_DATA = {!! json_encode($zonesData) !!};

/* ── ADMIN ZONES ACTIONS ── */
window.AdminZones = {

  edit(id, btn) {
    const name = btn.getAttribute('data-name');
    const code = btn.getAttribute('data-code');
    const desc = btn.getAttribute('data-desc');
    const lat  = btn.getAttribute('data-lat');
    const lng  = btn.getAttribute('data-lng');

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

  delete(id, btn) {
    const name = btn.getAttribute('data-name');
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
    'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png',
    { attribution: '© OpenStreetMap contributors, © CARTO', maxZoom: 20 }
  );

  let currentTile = getLightTile().addTo(map);

  /* Swap on theme change */
  if (Chariot && Chariot.Theme) {
    const origThemeApply = Chariot.Theme.apply.bind(Chariot.Theme);
    Chariot.Theme.apply = function(theme) {
      origThemeApply(theme);
      if (map && currentTile) {
        map.removeLayer(currentTile);
        currentTile = (theme === 'dark' ? getDarkTile() : getLightTile()).addTo(map);
      }
    };

    /* Apply current theme */
    const savedTheme = (Chariot && Chariot.Util && Chariot.Util.store)
      ? Chariot.Util.store.get('chariot-theme')
      : 'light';
    if (savedTheme === 'dark') {
      map.removeLayer(currentTile);
      currentTile = getDarkTile().addTo(map);
    }
  }


  /* ── 2. PLOT EXISTING ZONES ── */
  const zoneMarkers = {};

  const zoneIcon = (active) => L.divIcon({
    className: '',
    html: `<div style="
      width:32px;height:32px;border-radius:50% 50% 50% 4px;transform:rotate(-45deg);
      background:${active ? '#155428' : '#9ca3af'};
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
        <div style="font-size:11px;color:#666;font-family:monospace">${zone.code}</div>
        <div style="font-size:10px;color:#666;margin-top:4px">${zone.lat.toFixed(4)}, ${zone.lng.toFixed(4)}</div>
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
      color: '#C9A227',
      fillColor: '#FFD700',
      fillOpacity: 0.8,
      weight: 2,
    }).addTo(map);

    /* Exit pick mode */
    pickMode = false;
    pickBtn.innerHTML = '<i class="fa-solid fa-crosshairs"></i> Click map to set coordinates';
    pickBtn.style.cssText = '';
    map.getContainer().style.cursor = '';

    if (Chariot && Chariot.Toast) {
      Chariot.Toast.info(`Coordinates set: ${e.latlng.lat.toFixed(4)}, ${e.latlng.lng.toFixed(4)}`);
    }
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
</script>
@endpush
<!-- ========================= [E] END ============================== -->