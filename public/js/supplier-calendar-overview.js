/**
 * Calendar overview — mini calendar + all-services capacity panel.
 * Loaded on the supplier calendar index page.
 */
(function () {
  'use strict';

  const config = window.calendarOverviewConfig || { urls: {} };
  const capacityUrl = config.urls.capacity;

  if (!capacityUrl) return;

  /* ── helpers ─────────────────────────────────────────────── */

  function escapeHtml(v) {
    return String(v ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;')
      .replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
  }

  function localMonth(d) {
    if (!d) d = new Date();
    return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0');
  }

  function addMonths(ym, delta) {
    var parts = ym.split('-');
    var dt = new Date(+parts[0], +parts[1] - 1 + delta, 1);
    return localMonth(dt);
  }

  function monthLabel(ym) {
    var parts = ym.split('-');
    var dt = new Date(+parts[0], +parts[1] - 1, 1);
    return dt.toLocaleDateString(undefined, { month: 'long', year: 'numeric' });
  }

  function todayStr() {
    var d = new Date();
    return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
  }

  function dateLabel(dateStr) {
    var d = new Date(dateStr + 'T00:00:00');
    return d.toLocaleDateString(undefined, { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });
  }

  /* ── mini-calendar state ─────────────────────────────────── */

  var mc = {
    month: localMonth(),
    selected: null
  };

  /* ── mini-calendar rendering ─────────────────────────────── */

  function renderMiniCal() {
    var grid = document.getElementById('miniCalGrid');
    var label = document.getElementById('miniCalLabel');
    if (!grid) return;

    var parts = mc.month.split('-');
    var year = +parts[0], mon = +parts[1] - 1;
    if (label) label.textContent = monthLabel(mc.month);

    var firstDay = new Date(year, mon, 1);
    var startDow = (firstDay.getDay() + 6) % 7; // Monday=0
    var daysInMonth = new Date(year, mon + 1, 0).getDate();
    var today = todayStr();

    grid.innerHTML = '';

    // leading blanks
    for (var i = 0; i < startDow; i++) {
      var blank = document.createElement('span');
      blank.className = 'mini-day is-empty';
      grid.appendChild(blank);
    }

    for (var d = 1; d <= daysInMonth; d++) {
      var dateStr = mc.month + '-' + String(d).padStart(2, '0');
      var btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'mini-day' + (dateStr === today ? ' is-today' : '') + (dateStr === mc.selected ? ' is-selected' : '');
      btn.textContent = d;
      btn.dataset.date = dateStr;
      btn.addEventListener('click', function () {
        mc.selected = this.dataset.date;
        renderMiniCal();
        loadCapacity(this.dataset.date);
      });
      grid.appendChild(btn);
    }
  }

  /* ── capacity fetching & rendering ───────────────────────── */

  function showPanel(id) {
    ['capacityPanelEmpty', 'capacityPanelContent', 'capacityPanelLoading'].forEach(function (name) {
      var el = document.getElementById(name);
      if (el) el.hidden = name !== id;
    });
  }

  async function loadCapacity(date) {
    showPanel('capacityPanelLoading');

    try {
      var resp = await fetch(capacityUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({ date: date })
      });
      var data = await resp.json();
      if (!resp.ok || data.status === 'error') throw new Error(data.message || 'Request failed');
      renderCapacity(date, data.capacity || { date: date, services: [] });
    } catch (err) {
      showPanel('capacityPanelEmpty');
      var empty = document.getElementById('capacityPanelEmpty');
      if (empty) empty.querySelector('p').textContent = err.message;
    }
  }

  function renderCapacity(date, capacity) {
    var services = capacity.services || [];
    document.getElementById('capacityDateLabel').textContent = dateLabel(date);

    // Summary badge
    var totalRemaining = services.reduce(function (sum, s) { return sum + s.total_remaining; }, 0);
    var totalCapacity = services.reduce(function (sum, s) { return sum + s.total_capacity; }, 0);
    var badge = document.getElementById('capacitySummary');
    badge.textContent = totalRemaining + ' of ' + totalCapacity + ' slots remaining';
    badge.className = 'capacity-summary-badge' + (totalRemaining <= 0 ? ' is-depleted' : totalRemaining < totalCapacity * 0.25 ? ' is-low' : '');

    var grid = document.getElementById('capacityGrid');

    if (!services.length) {
      grid.innerHTML = '<p class="capacity-grid-empty">No services are available on this date.</p>';
      showPanel('capacityPanelContent');
      return;
    }

    grid.innerHTML = services.map(function (svc) {
      var pct = svc.total_capacity > 0 ? Math.round((svc.total_remaining / svc.total_capacity) * 100) : 0;
      var barClass = pct <= 0 ? 'is-depleted' : pct < 25 ? 'is-low' : '';
      var imgHtml = svc.img
        ? '<img src="' + escapeHtml(svc.img) + '" alt="' + escapeHtml(svc.name) + '">'
        : '<div class="svc-cap-placeholder"><i class="ti ti-photo"></i></div>';

      var slotsHtml = '';
      if (svc.slots && svc.slots.length) {
        slotsHtml = '<div class="svc-cap-slots">' + svc.slots.map(function (slot) {
          var slotFull = slot.remaining <= 0;
          return '<span class="svc-cap-slot' + (slotFull ? ' is-full' : '') + '">'
            + escapeHtml(slot.start_time) + '–' + escapeHtml(slot.end_time)
            + ' <strong>' + slot.remaining + '</strong>/' + slot.max_concurrent + '</span>';
        }).join('') + '</div>';
      }

      return '<article class="svc-cap-card">'
        + '<div class="svc-cap-media">' + imgHtml + '</div>'
        + '<div class="svc-cap-body">'
        + '<div class="svc-cap-name">' + escapeHtml(svc.name) + '</div>'
        + '<div class="svc-cap-cat">' + escapeHtml(svc.category) + '</div>'
        + '<div class="svc-cap-meter"><div class="svc-cap-bar ' + barClass + '" style="width:' + pct + '%"></div></div>'
        + '<div class="svc-cap-stats"><strong>' + svc.total_remaining + '</strong> of ' + svc.total_capacity + ' remaining</div>'
        + slotsHtml
        + '</div>'
        + '</article>';
    }).join('');

    showPanel('capacityPanelContent');
  }

  /* ── navigation ──────────────────────────────────────────── */

  document.getElementById('miniPrevBtn')?.addEventListener('click', function () {
    mc.month = addMonths(mc.month, -1);
    renderMiniCal();
  });

  document.getElementById('miniNextBtn')?.addEventListener('click', function () {
    mc.month = addMonths(mc.month, 1);
    renderMiniCal();
  });

  /* ── init ────────────────────────────────────────────────── */

  renderMiniCal();

})();
