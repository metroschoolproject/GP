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
  selectedDay: null
};

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
  renderCalendar();
}

function statusLabel(status) {
  return {
    open: 'Open',
    custom_hours: 'Custom',
    booked: 'Booked',
    unavailable: 'Away',
    closed: 'Closed'
  }[status] || 'Closed';
}

function timeText(day) {
  if (!day.open_time || !day.close_time) return 'No bookable hours';
  return `${day.open_time.slice(0, 5)} - ${day.close_time.slice(0, 5)}`;
}

function renderCalendar() {
  const grid = document.getElementById('calendarGrid');
  const label = document.getElementById('calendarMonthLabel');
  if (!grid || !state.calendar) return;

  if (label) label.textContent = state.calendar.month_label;
  grid.innerHTML = '';

  state.calendar.days.forEach(day => {
    const button = document.createElement('button');
    button.type = 'button';
    button.className = `calendar-day ${day.in_month ? '' : 'outside'} ${day.is_today ? 'today' : ''}`;
    button.dataset.date = day.date;
    button.innerHTML = `
      <div class="day-head">
        <span class="day-number">${day.day}</span>
        <span class="status-pill status-${day.status}">${statusLabel(day.status)}</span>
      </div>
      <div class="day-time">${timeText(day)}</div>
      <div class="day-source">${day.source === 'override' ? 'Date override' : 'Weekly schedule'}</div>
      ${day.booking_count > 0 ? `<span class="booking-chip">${day.booking_count} booking${day.booking_count === 1 ? '' : 's'}</span>` : ''}
    `;
    button.addEventListener('click', () => openDayModal(day));
    grid.appendChild(button);
  });
}

function formatDateLabel(dateValue) {
  const date = new Date(`${dateValue}T00:00:00`);
  return date.toLocaleDateString(undefined, { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });
}

function openDayModal(day) {
  state.selectedDay = day;
  const modal = document.getElementById('calendarModal');
  const override = day.override || {};
  const type = override.type || (day.status === 'closed' ? 'available' : 'unavailable');

  document.getElementById('modalDateLabel').textContent = formatDateLabel(day.date);
  document.getElementById('overrideDate').value = day.date;
  document.getElementById('overrideId').value = override.id || '';
  document.getElementById('overrideType').value = type;
  document.getElementById('overrideOpenTime').value = (override.open_time || day.open_time || '09:00').slice(0, 5);
  document.getElementById('overrideCloseTime').value = (override.close_time || day.close_time || '17:00').slice(0, 5);
  document.getElementById('overrideReason').value = override.reason || '';
  document.getElementById('clearOverrideBtn').style.display = override.id ? 'inline-flex' : 'none';

  renderModalBookings(day.bookings || []);
  updateCustomHoursVisibility();
  modal.hidden = false;
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
    return `<p>#${booking.booking_id} · ${booking.customer_name || 'Customer'} · ${time} · ${booking.supplier_status || booking.status}</p>`;
  }).join('')}`;
}

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

  try {
    await calendarJson(calendarUrls.overrideSave, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        date: document.getElementById('overrideDate').value,
        type: document.getElementById('overrideType').value,
        open_time: document.getElementById('overrideOpenTime').value,
        close_time: document.getElementById('overrideCloseTime').value,
        reason: document.getElementById('overrideReason').value
      })
    });
    closeModal();
    await loadCalendar(state.month);
    calendarMessage('Date override saved.', true);
  } catch (error) {
    calendarMessage(error.message);
  }
}

async function clearOverride() {
  const overrideId = document.getElementById('overrideId')?.value;
  if (!overrideId) return;
  calendarMessage('');

  try {
    await calendarJson(calendarUrls.overrideDelete + encodeURIComponent(overrideId), { method: 'POST' });
    closeModal();
    await loadCalendar(state.month);
    calendarMessage('Date override cleared.', true);
  } catch (error) {
    calendarMessage(error.message);
  }
}

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
document.getElementById('calendarOverrideForm')?.addEventListener('submit', saveOverride);
document.getElementById('clearOverrideBtn')?.addEventListener('click', clearOverride);
document.querySelectorAll('[data-close-calendar-modal]').forEach(element => {
  element.addEventListener('click', closeModal);
});

document.addEventListener('keydown', event => {
  if (event.key === 'Escape') closeModal();
});

loadCalendar().catch(error => calendarMessage(error.message));
