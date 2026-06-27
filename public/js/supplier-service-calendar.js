const calendarConfig = window.serviceCalendarConfig || { urls: {}, service: {} };
const calendarUrls = calendarConfig.urls || {};

function localMonthValue(date = new Date()) {
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  return `${year}-${month}`;
}

const state = {
  month: localMonthValue(),
  calendar: null,
  selectedDay: null,
  selectedRoomId: null
};

function escapeHtml(value) {
  return String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

function calendarMessage(text, success = false) {
  const element = document.getElementById('calendarMessage');
  if (!element) return;
  element.textContent = text || '';
  element.style.display = text ? 'block' : 'none';
  element.classList.toggle('success', Boolean(success));
}

async function calendarJson(url, options = {}) {
  const response = await fetch(url, {
    headers: {
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      ...(options.headers || {})
    },
    ...options
  });
  const data = await response.json();
  if (!response.ok || data.status === 'error') {
    throw new Error(data.message || 'Request failed.');
  }
  return data;
}

async function loadCalendar(month = state.month) {
  calendarMessage('');
  const url = `${calendarUrls.data}?month=${encodeURIComponent(month)}`;
  const result = await calendarJson(url);
  state.calendar = result.calendar;
  state.month = result.calendar.month;
  renderRoomFilter();
  renderCalendar();
}

// ── Room filter ──────────────────────────────────────────────────────────

function renderRoomFilter() {
  const bar = document.getElementById('roomFilterBar');
  if (!bar) return;

  const rooms = state.calendar?.venue_rooms;
  if (!rooms || !rooms.length) {
    bar.hidden = true;
    bar.innerHTML = '';
    return;
  }

  bar.hidden = false;
  bar.innerHTML = '';

  const allBtn = document.createElement('button');
  allBtn.type = 'button';
  allBtn.className = 'room-filter-btn' + (state.selectedRoomId === null ? ' is-active' : '');
  allBtn.textContent = 'All rooms';
  allBtn.addEventListener('click', () => selectRoom(null));
  bar.appendChild(allBtn);

  rooms.forEach(room => {
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'room-filter-btn' + (state.selectedRoomId === room.id ? ' is-active' : '');
    btn.textContent = room.name || `Room ${room.id}`;
    btn.dataset.roomId = room.id;
    btn.addEventListener('click', () => selectRoom(room.id));
    bar.appendChild(btn);
  });
}

function selectRoom(roomId) {
  state.selectedRoomId = roomId;
  renderRoomFilter();
  renderCalendar();
}

// ── Helpers ──────────────────────────────────────────────────────────────

function roomStatusLabel(status) {
  return {
    open: 'Open',
    booked: 'Booked',
    unavailable: 'Away',
    lead_blocked: 'Too soon',
    closed: 'Closed'
  }[status] || 'Closed';
}

function statusLabel(status) {
  return {
    open: 'Open',
    custom_hours: 'Custom',
    booked: 'Booked',
    unavailable: 'Away',
    lead_blocked: 'Too soon',
    closed: 'Closed'
  }[status] || 'Closed';
}

function timeText(day) {
  if (!day.open_time || !day.close_time) return 'No bookable hours';
  return `${day.open_time.slice(0, 5)} - ${day.close_time.slice(0, 5)}`;
}

function roomTimeText(room) {
  if (!room.start_time || !room.end_time) return 'Closed';
  return `${room.start_time.slice(0, 5)} - ${room.end_time.slice(0, 5)}`;
}

function getDayForRoom(day) {
  if (state.selectedRoomId === null || !day.rooms) return day;
  const room = day.rooms.find(r => r.room_id === state.selectedRoomId);
  if (!room) return day;
  return {
    ...day,
    status: room.status === 'lead_blocked' ? 'closed' : room.status,
    source: room.source === 'override' ? 'override' : day.source,
    open_time: room.start_time || day.open_time,
    close_time: room.end_time || day.close_time,
    booking_count: room.booking_count,
  };
}

// ── Calendar grid ────────────────────────────────────────────────────────

function renderCalendar() {
  const grid = document.getElementById('calendarGrid');
  const label = document.getElementById('calendarMonthLabel');
  if (!grid || !state.calendar) return;

  if (label) label.textContent = state.calendar.month_label;
  grid.innerHTML = '';

  state.calendar.days.forEach(day => {
    const display = getDayForRoom(day);
    const button = document.createElement('button');
    button.type = 'button';
    const selected = state.selectedDay?.date === day.date;
    button.className = [
      'calendar-day',
      day.in_month ? '' : 'outside',
      day.is_today ? 'today' : '',
      display.booking_count > 0 ? 'has-bookings' : '',
      display.source === 'override' ? 'has-override' : '',
      selected ? 'is-selected' : ''
    ].filter(Boolean).join(' ');
    button.dataset.date = day.date;
    button.innerHTML = `
      <div class="day-head">
        <span class="day-number">${day.day}</span>
        <span class="status-pill status-${display.status}">${statusLabel(display.status)}</span>
      </div>
      <div class="day-time">${timeText(display)}</div>
      <div class="day-source">${display.source === 'override' ? 'Date override' : 'Weekly schedule'}</div>
      ${display.booking_count > 0 ? `<span class="booking-chip">${display.booking_count} booking${display.booking_count === 1 ? '' : 's'}</span>` : ''}
    `;
    button.addEventListener('click', () => {
      setFocusDay(day);
      openDayModal(day);
    });
    grid.appendChild(button);
  });

  const selectedInMonth = state.calendar.days.find(day => day.date === state.selectedDay?.date);
  setFocusDay(selectedInMonth || state.calendar.days.find(day => day.is_today) || state.calendar.days.find(day => day.in_month) || state.calendar.days[0], false);
  renderAgenda();
}

// ── Focus sidebar ────────────────────────────────────────────────────────

function formatDateLabel(dateValue) {
  const date = new Date(`${dateValue}T00:00:00`);
  return date.toLocaleDateString(undefined, { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });
}

function setFocusDay(day, rerender = true) {
  if (!day) return;
  state.selectedDay = day;

  const dateEl = document.getElementById('calendarFocusDate');
  const statusEl = document.getElementById('calendarFocusStatus');
  const metaEl = document.getElementById('calendarFocusMeta');
  const roomsEl = document.getElementById('calendarFocusRooms');

  if (dateEl) dateEl.textContent = formatDateLabel(day.date);

  const display = getDayForRoom(day);

  if (statusEl) {
    const source = display.source === 'override' ? 'Custom date rule' : 'Weekly schedule';
    statusEl.textContent = `${statusLabel(display.status)} · ${source}`;
  }

  if (metaEl) {
    metaEl.innerHTML = `
      <span>Hours <strong>${escapeHtml(timeText(display))}</strong></span>
      <span>Bookings <strong>${Number(display.booking_count || 0)}</strong></span>
      <span>Rule <strong>${display.source === 'override' ? 'Date override' : 'Default week'}</strong></span>
    `;
  }

  // Per-room breakdown in sidebar
  if (roomsEl) {
    const rooms = state.calendar?.venue_rooms;
    if (rooms && rooms.length && day.rooms && state.selectedRoomId === null) {
      roomsEl.hidden = false;
      roomsEl.innerHTML = '<div class="focus-rooms-title">Room availability</div>' +
        rooms.map(room => {
          const rd = day.rooms.find(r => r.room_id === room.id);
          if (!rd) return '';
          const minLead = room.min_lead_days != null ? room.min_lead_days : '';
          return `<div class="focus-room-row">
            <span class="focus-room-name">${escapeHtml(room.name)}</span>
            <span class="status-pill status-${rd.status} room-pill">${roomStatusLabel(rd.status)}</span>
            <span class="focus-room-time">${roomTimeText(rd)}</span>
            ${rd.booking_count > 0 ? `<span class="focus-room-bookings">${rd.booking_count} booking${rd.booking_count === 1 ? '' : 's'}</span>` : ''}
            ${minLead !== '' ? `<span class="focus-room-lead">${minLead}d notice</span>` : ''}
          </div>`;
        }).join('');
    } else {
      roomsEl.hidden = true;
      roomsEl.innerHTML = '';
    }
  }

  if (rerender) renderCalendar();
}

// ── Day modal ────────────────────────────────────────────────────────────

function openDayModal(day) {
  setFocusDay(day, false);
  const modal = document.getElementById('calendarModal');
  const override = day.override || {};
  const type = override.type || (day.status === 'closed' ? 'available' : 'unavailable');
  const isVenue = !!(state.calendar?.venue_rooms?.length);

  document.getElementById('modalDateLabel').textContent = formatDateLabel(day.date);
  document.getElementById('overrideDate').value = day.date;
  document.getElementById('overrideId').value = override.id || '';
  document.getElementById('overrideType').value = type;
  document.getElementById('overrideOpenTime').value = (override.open_time || day.open_time || '09:00').slice(0, 5);
  document.getElementById('overrideCloseTime').value = (override.close_time || day.close_time || '17:00').slice(0, 5);
  document.getElementById('overrideReason').value = override.reason || '';
  document.getElementById('clearOverrideBtn').style.display = override.id ? 'inline-flex' : 'none';

  // Room scope fields
  const scopeFields = document.getElementById('overrideScopeFields');
  const scopeSelect = document.getElementById('overrideScope');
  const roomField = document.getElementById('overrideRoomField');
  const roomSelect = document.getElementById('overrideRoomId');

  if (scopeFields && isVenue) {
    scopeFields.hidden = false;
    scopeSelect.value = state.selectedRoomId ? 'room' : 'service';
    updateOverrideScopeVisibility();

    if (roomSelect) {
      roomSelect.innerHTML = '';
      (state.calendar.venue_rooms || []).forEach(room => {
        const opt = document.createElement('option');
        opt.value = room.id;
        opt.textContent = room.name || `Room ${room.id}`;
        if (room.id === state.selectedRoomId) opt.selected = true;
        roomSelect.appendChild(opt);
      });
    }
  } else if (scopeFields) {
    scopeFields.hidden = true;
  }

  // Check if the day has a room-specific override when a room is selected
  if (state.selectedRoomId && day.rooms) {
    const roomData = day.rooms.find(r => r.room_id === state.selectedRoomId);
    if (roomData && roomData.source === 'override') {
      // Show that this room has an override (clear button)
      document.getElementById('clearOverrideBtn').style.display = 'inline-flex';
    }
  }

  renderModalBookings((day.bookings || []).filter(b =>
    state.selectedRoomId ? b.venue_room_id === state.selectedRoomId : true
  ));
  loadDayCapacity(day.date, day.status);
  updateCustomHoursVisibility();
  modal.hidden = false;
}

function updateOverrideScopeVisibility() {
  const scope = document.getElementById('overrideScope')?.value;
  const roomField = document.getElementById('overrideRoomField');
  if (roomField) roomField.hidden = scope !== 'room';
}

// ── Capacity preview ─────────────────────────────────────────────────────

async function loadDayCapacity(date, status) {
  const box = document.getElementById('modalCapacity');
  if (!box) return;

  if (!calendarUrls.preview || ['closed', 'unavailable'].includes(status)) {
    box.hidden = true;
    box.innerHTML = '';
    return;
  }

  box.hidden = false;
  box.innerHTML = '<strong>Remaining capacity</strong><p class="capacity-loading">Loading slots…</p>';

  try {
    const result = await calendarJson(calendarUrls.preview, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ date })
    });
    const slots = result.preview?.slots || [];

    if (!slots.length) {
      box.innerHTML = '<strong>Remaining capacity</strong><p class="capacity-empty">No bookable slots for this date.</p>';
      return;
    }

    box.innerHTML = '<strong>Remaining capacity</strong><div class="capacity-slots">' + slots.map(slot => {
      const start = String(slot.start_time || '').slice(0, 5);
      const end = String(slot.end_time || '').slice(0, 5);
      const total = Number(slot.max_concurrent || 0);
      const left = Math.max(0, total - Number(slot.confirmed_count || 0));
      const parts = [];
      const pkgMax = Number(slot.max_concurrent_package || 0);
      if (pkgMax > 0) parts.push(`Package ${Math.max(0, pkgMax - Number(slot.confirmed_package_count || 0))}/${pkgMax}`);
      const cusMax = Number(slot.max_concurrent_customize || 0);
      if (cusMax > 0) parts.push(`Custom ${Math.max(0, cusMax - Number(slot.confirmed_customize_count || 0))}/${cusMax}`);
      const split = parts.length ? `<span class="capacity-split">${escapeHtml(parts.join(' · '))}</span>` : '';
      return `<span class="capacity-slot${left <= 0 ? ' is-full' : ''}">${escapeHtml(start)} - ${escapeHtml(end)} <strong>${left} left</strong> of ${total}${split}</span>`;
    }).join('') + '</div>';
  } catch (error) {
    box.innerHTML = `<strong>Remaining capacity</strong><p class="capacity-empty">${escapeHtml(error.message)}</p>`;
  }
}

function renderModalBookings(bookings) {
  const list = document.getElementById('modalBookings');
  if (!list) return;

  if (!bookings.length) {
    list.hidden = true;
    list.innerHTML = '';
    return;
  }

  list.hidden = false;
  list.innerHTML = `<strong>Bookings on this date</strong>${bookings.map(booking => {
    const time = booking.start_time && booking.end_time
      ? `${booking.start_time.slice(0, 5)} - ${booking.end_time.slice(0, 5)}`
      : 'Full day';
    return `<p>#${escapeHtml(booking.booking_id)} · ${escapeHtml(booking.customer_name || 'Customer')} · ${escapeHtml(time)} · ${escapeHtml(booking.supplier_status || booking.status)}</p>`;
  }).join('')}`;
}

// ── Agenda ───────────────────────────────────────────────────────────────

function renderAgenda() {
  const agenda = document.getElementById('calendarAgenda');
  const count = document.getElementById('calendarAgendaCount');
  if (!agenda || !state.calendar) return;

  const items = state.calendar.days
    .filter(day => {
      if (!day.in_month) return false;
      const display = getDayForRoom(day);
      return display.booking_count > 0 || display.source === 'override' || ['booked', 'custom_hours', 'unavailable'].includes(display.status);
    })
    .slice(0, 8);

  if (count) count.textContent = String(items.length);

  if (!items.length) {
    agenda.innerHTML = '<p class="agenda-empty">No bookings or special date rules this month yet.</p>';
    return;
  }

  agenda.innerHTML = items.map(day => {
    const display = getDayForRoom(day);
    return `
      <button type="button" class="agenda-item" data-agenda-date="${escapeHtml(day.date)}">
        <span>${escapeHtml(formatDateLabel(day.date))}</span>
        <strong>${display.booking_count > 0 ? `${display.booking_count} booking${display.booking_count === 1 ? '' : 's'}` : escapeHtml(statusLabel(display.status))}</strong>
        <span class="agenda-status ${escapeHtml(display.status)}">${escapeHtml(statusLabel(display.status))}</span>
      </button>
    `;
  }).join('');

  agenda.querySelectorAll('[data-agenda-date]').forEach(button => {
    button.addEventListener('click', () => {
      const day = state.calendar.days.find(item => item.date === button.dataset.agendaDate);
      if (day) {
        setFocusDay(day);
        openDayModal(day);
      }
    });
  });
}

// ── Save / clear overrides ───────────────────────────────────────────────

function closeModal() {
  const modal = document.getElementById('calendarModal');
  if (modal) modal.hidden = true;
}

function updateCustomHoursVisibility() {
  const type = document.getElementById('overrideType')?.value;
  const fields = document.getElementById('customHoursFields');
  if (fields) fields.style.display = type === 'custom_hours' ? 'grid' : 'none';
}

async function saveOverride(event) {
  event.preventDefault();
  calendarMessage('');

  const scope = document.getElementById('overrideScope')?.value;
  const isRoomScope = scope === 'room' && calendarUrls.roomOverrideSave;

  const payload = {
    date: document.getElementById('overrideDate').value,
    type: document.getElementById('overrideType').value,
    open_time: document.getElementById('overrideOpenTime').value,
    close_time: document.getElementById('overrideCloseTime').value,
    reason: document.getElementById('overrideReason').value
  };

  try {
    if (isRoomScope) {
      payload.room_id = parseInt(document.getElementById('overrideRoomId')?.value, 10);
      await calendarJson(calendarUrls.roomOverrideSave, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
    } else {
      await calendarJson(calendarUrls.overrideSave, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
    }
    closeModal();
    await loadCalendar(state.month);
    calendarMessage('Date override saved.', true);
  } catch (error) {
    calendarMessage(error.message);
  }
}

async function clearOverride() {
  calendarMessage('');

  const scope = document.getElementById('overrideScope')?.value;
  const isRoomScope = scope === 'room';

  // For room-scoped overrides, find the room override ID from the current day data
  if (isRoomScope && state.selectedRoomId && state.selectedDay?.rooms && calendarUrls.roomOverrideDelete) {
    const roomData = state.selectedDay.rooms.find(r => r.room_id === state.selectedRoomId);
    if (roomData && roomData.override_id) {
      try {
        await calendarJson(calendarUrls.roomOverrideDelete + encodeURIComponent(roomData.override_id), { method: 'POST' });
        closeModal();
        await loadCalendar(state.month);
        calendarMessage('Room override cleared.', true);
      } catch (error) {
        calendarMessage(error.message);
      }
      return;
    }
  }

  // Service-level override
  const overrideId = document.getElementById('overrideId')?.value;
  if (!overrideId) return;

  try {
    await calendarJson(calendarUrls.overrideDelete + encodeURIComponent(overrideId), { method: 'POST' });
    closeModal();
    await loadCalendar(state.month);
    calendarMessage('Date override cleared.', true);
  } catch (error) {
    calendarMessage(error.message);
  }
}

// ── Event listeners ──────────────────────────────────────────────────────

document.getElementById('prevMonthBtn')?.addEventListener('click', () => {
  if (state.calendar?.prev_month) loadCalendar(state.calendar.prev_month).catch(error => calendarMessage(error.message));
});

document.getElementById('nextMonthBtn')?.addEventListener('click', () => {
  if (state.calendar?.next_month) loadCalendar(state.calendar.next_month).catch(error => calendarMessage(error.message));
});

document.getElementById('todayCalendarBtn')?.addEventListener('click', () => {
  loadCalendar(localMonthValue()).catch(error => calendarMessage(error.message));
});

document.getElementById('overrideType')?.addEventListener('change', updateCustomHoursVisibility);
document.getElementById('overrideScope')?.addEventListener('change', updateOverrideScopeVisibility);
document.getElementById('calendarOverrideForm')?.addEventListener('submit', saveOverride);
document.getElementById('clearOverrideBtn')?.addEventListener('click', clearOverride);
document.querySelectorAll('[data-close-calendar-modal]').forEach(element => {
  element.addEventListener('click', closeModal);
});

document.addEventListener('keydown', event => {
  if (event.key === 'Escape') closeModal();
});

loadCalendar().catch(error => calendarMessage(error.message));
