const serviceDetailConfig = window.serviceDetailConfig || { urls: {}, servicePayloadBase: {} };
const urls = serviceDetailConfig.urls;

function showMessage(elementId, text, success = false) {
  const element = document.getElementById(elementId);
  if (!element) return;
  element.textContent = text || '';
  element.style.display = text ? 'flex' : 'none';
  element.classList.toggle('success', Boolean(success));
  element.classList.toggle('error', !success);
}

async function jsonPost(url, payload = {}) {
  const response = await fetch(url, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest'
    },
    body: JSON.stringify(payload)
  });
  const data = await response.json();
  if (!response.ok || data.status === 'error') {
    throw new Error(data.message || 'Request failed.');
  }
  return data;
}

async function jsonGet(url) {
  const response = await fetch(url, {
    headers: {
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest'
    }
  });
  const data = await response.json();
  if (!response.ok || data.status === 'error') {
    throw new Error(data.message || 'Request failed.');
  }
  return data;
}

let publishPollTimer = null;

function setPublishedState(isLive) {
  const topbar = document.querySelector('#supplier-service-detail .topbar');
  const dot = document.getElementById('publishStatusDot');
  const text = document.getElementById('publishStatusText');
  const button = document.getElementById('publishServiceBtn');
  const buttonText = document.getElementById('publishServiceBtnText');
  const buttonIcon = button?.querySelector('i');

  if (topbar) topbar.dataset.serviceStatus = isLive ? 'active' : 'inactive';
  dot?.classList.toggle('ready', Boolean(isLive));

  if (text) text.textContent = isLive ? 'Live' : text.textContent;
  if (button) button.disabled = Boolean(isLive);
  if (buttonText && isLive) buttonText.textContent = 'Published';
  if (buttonIcon && isLive) {
    buttonIcon.className = 'ti ti-circle-check';
    buttonIcon.style.fontSize = '13px';
  }
}

function stopPublishStatusPolling() {
  if (publishPollTimer) {
    clearInterval(publishPollTimer);
    publishPollTimer = null;
  }
}

async function checkPublishStatus(showLiveMessage = false) {
  if (!urls.publishStatus) return;
  const result = await jsonGet(urls.publishStatus);

  if (result.is_live) {
    setPublishedState(true);
    stopPublishStatusPolling();
    if (showLiveMessage) {
      showMessage('publishMessage', 'Admin approved this service. It is now live for customers.', true);
    }
  }
}

function startPublishStatusPolling() {
  if (!urls.publishStatus || publishPollTimer) return;
  publishPollTimer = setInterval(() => {
    checkPublishStatus(true).catch(() => {});
  }, 10000);
}

document.getElementById('publishServiceBtn')?.addEventListener('click', async event => {
  const button = event.currentTarget;
  showMessage('publishMessage', '');
  button.disabled = true;

  try {
    const result = await jsonPost(urls.publishRequest);
    showMessage('publishMessage', result.message || 'Publish request sent to admin.', true);
    startPublishStatusPolling();
  } catch (error) {
    showMessage('publishMessage', error.message);
  } finally {
    if (button.closest('.topbar')?.dataset.serviceStatus !== 'active') {
      button.disabled = false;
    }
  }
});

checkPublishStatus(false).catch(() => {});
startPublishStatusPolling();

function fileToDataUrl(file) {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.onload = () => resolve(reader.result);
    reader.onerror = reject;
    reader.readAsDataURL(file);
  });
}

function appendMedia(media) {
  const grid = document.getElementById('mediaGrid');
  const addButton = grid?.querySelector('.photo-add');
  if (!grid || !media) return;

  const item = document.createElement('div');
  item.className = 'photo-card';
  item.dataset.mediaId = media.id;
  item.innerHTML = `
    <img src="${media.file_url}" alt="Service photo">
    <button type="button" class="del-btn" onclick="deleteServiceMedia(${media.id})"><i class="ti ti-trash" style="font-size:13px"></i></button>
  `;
  grid.insertBefore(item, addButton || null);
}

async function uploadServiceMedia(file) {
  if (!file) return;
  showMessage('mediaMessage', '');

  try {
    const img = await fileToDataUrl(file);
    const result = await jsonPost(urls.mediaCreate, { img });
    appendMedia(result.media);
    showMessage('mediaMessage', 'Photo uploaded.', true);
  } catch (error) {
    showMessage('mediaMessage', error.message);
  }
}

document.getElementById('serviceMediaInput')?.addEventListener('change', event => {
  uploadServiceMedia(event.target.files[0]);
  event.target.value = '';
});

document.querySelector('[data-media-picker]')?.addEventListener('change', event => {
  uploadServiceMedia(event.target.files[0]);
  event.target.value = '';
});

async function deleteServiceMedia(mediaId) {
  if (!mediaId || !confirm('Delete this photo?')) return;
  showMessage('mediaMessage', '');

  try {
    await jsonPost(urls.mediaDelete + encodeURIComponent(mediaId));
    document.querySelector(`[data-media-id="${mediaId}"]`)?.remove();
    showMessage('mediaMessage', 'Photo deleted.', true);
  } catch (error) {
    showMessage('mediaMessage', error.message);
  }
}

function toggleDay(checkbox) {
  const row = checkbox.closest('.availability-day-row');
  const label = row.querySelector('.toggle-label');
  const startCell = row.querySelector('.start-cell');
  const endCell = row.querySelector('.end-cell');
  const existingStart = row.dataset.start || '09:00';
  const existingEnd = row.dataset.end || '17:00';

  if (checkbox.checked) {
    label.textContent = 'Open';
    startCell.innerHTML = `<input class="time-input availability-start" type="time" value="${existingStart}">`;
    endCell.innerHTML = `<input class="time-input availability-end" type="time" value="${existingEnd}">`;
  } else {
    row.dataset.start = row.querySelector('.availability-start')?.value || existingStart;
    row.dataset.end = row.querySelector('.availability-end')?.value || existingEnd;
    label.textContent = 'Closed';
    startCell.innerHTML = '<span class="closed-indicator">-</span>';
    endCell.innerHTML = '<span class="closed-indicator">-</span>';
  }
}

document.querySelectorAll('.availability-day-row').forEach(row => {
  row.dataset.start = row.querySelector('.availability-start')?.value || '09:00';
  row.dataset.end = row.querySelector('.availability-end')?.value || '17:00';
});

document.getElementById('saveAvailabilityBtn')?.addEventListener('click', async () => {
  const weekly = Array.from(document.querySelectorAll('.availability-day-row')).map(row => {
    const open = row.querySelector('.availability-open')?.checked || false;
    return {
      day_of_week: Number(row.dataset.day),
      is_available: open,
      open_time: row.querySelector('.availability-start')?.value || row.dataset.start || '09:00',
      close_time: row.querySelector('.availability-end')?.value || row.dataset.end || '17:00'
    };
  });

  try {
    await jsonPost(urls.availabilitySave, {
      duration_minutes: document.getElementById('availabilityDuration').value,
      buffer_minutes: document.getElementById('availabilityBuffer').value,
      max_concurrent: document.getElementById('availabilityConcurrent').value,
      weekly
    });
    const openCount = weekly.filter(day => day.is_available).length;
    const badge = document.getElementById('openDaysBadge');
    if (badge) badge.textContent = openCount + ' days open';
    const heroValue = document.getElementById('heroOpenDaysValue');
    const heroSub = document.getElementById('heroOpenDaysSub');
    if (heroValue) heroValue.innerHTML = `${openCount}<span style="font-size:14px;color:var(--text-3)">/7</span>`;
    if (heroSub) heroSub.textContent = openCount > 0 ? 'Schedule active' : 'No days open';
    showMessage('availabilityMessage', 'Availability saved.', true);
  } catch (error) {
    showMessage('availabilityMessage', error.message);
  }
});

function hallCardHtml(room = {}) {
  return `
    <div class="hall-card" data-room-id="${room.id || 0}">
      <input type="hidden" class="hall-id" value="${room.id || ''}">
      <div class="hall-card-head">
        <div class="hall-card-icon"><i class="ti ti-door"></i></div>
        <button type="button" class="btn btn-icon btn-danger-ghost btn-sm" onclick="removeHall(this)"><i class="ti ti-trash" style="font-size:13px"></i></button>
      </div>
      <div class="hall-inputs">
        <div class="hall-input-group full"><label>Hall name</label><input class="hall-input hall-name" value="${room.name || ''}"></div>
        <div class="hall-input-group"><label>Capacity</label><input type="number" min="1" class="hall-input hall-capacity" value="${room.capacity || 1}"></div>
        <div class="hall-input-group"><label>Price</label><input type="number" min="0" step="0.01" class="hall-input hall-price" value="${room.price || 0}"></div>
        <div class="hall-input-group"><label>Start time</label><input type="time" class="hall-input hall-start" value="${room.start_time || '09:00'}"></div>
        <div class="hall-input-group"><label>End time</label><input type="time" class="hall-input hall-end" value="${room.end_time || '17:00'}"></div>
      </div>
      <div class="hall-time-display">9:00 AM - 5:00 PM</div>
    </div>
  `;
}

function addHall() {
  document.getElementById('hallGrid')?.insertAdjacentHTML('beforeend', hallCardHtml());
  updateHallCount();
}

function removeHall(button) {
  button.closest('.hall-card')?.remove();
  updateHallCount();
}

function updateHallCount() {
  const count = document.querySelectorAll('.hall-card').length;
  const badge = document.getElementById('hallCount');
  if (badge) badge.textContent = count + ' ' + (count === 1 ? 'hall' : 'halls');
}

function collectHalls() {
  return Array.from(document.querySelectorAll('.hall-card')).map(card => {
    const start = card.querySelector('.hall-start')?.value || '09:00';
    const end = card.querySelector('.hall-end')?.value || '17:00';
    if (start >= end) {
      throw new Error('Hall end time must be later than start time.');
    }
    return {
      id: card.querySelector('.hall-id')?.value || null,
      name: card.querySelector('.hall-name')?.value.trim() || '',
      capacity: parseInt(card.querySelector('.hall-capacity')?.value || '1', 10) || 1,
      price: parseFloat(card.querySelector('.hall-price')?.value || '0') || 0,
      start_time: start,
      end_time: end
    };
  }).filter(room => room.name || room.capacity > 1 || room.price > 0);
}

document.getElementById('saveHallsBtn')?.addEventListener('click', async () => {
  showMessage('hallMessage', '');
  try {
    await jsonPost(urls.serviceUpdate, {
      ...serviceDetailConfig.servicePayloadBase,
      rooms: collectHalls(),
      rooms_replace: true
    });
    updateHallCount();
    showMessage('hallMessage', 'Halls saved.', true);
  } catch (error) {
    showMessage('hallMessage', error.message);
  }
});

document.getElementById('saveOverrideBtn')?.addEventListener('click', async () => {
  showMessage('overrideMessage', '');
  try {
    await jsonPost(urls.overrideSave, {
      date: document.getElementById('overrideDate').value,
      type: document.getElementById('overrideType').value,
      open_time: document.getElementById('overrideOpen').value,
      close_time: document.getElementById('overrideClose').value,
      reason: document.getElementById('overrideReason').value
    });
    window.location.reload();
  } catch (error) {
    showMessage('overrideMessage', error.message);
  }
});

async function deleteOverride(overrideId) {
  if (!overrideId || !confirm('Delete this override?')) return;
  showMessage('overrideMessage', '');
  try {
    await jsonPost(urls.overrideDelete + encodeURIComponent(overrideId));
    document.querySelector(`[data-override-id="${overrideId}"]`)?.remove();
    const count = document.querySelectorAll('[data-override-id]').length;
    const badge = document.getElementById('overrideCount');
    if (badge) badge.textContent = count + ' saved';
    document.getElementById('overrideEmpty').style.display = count ? 'none' : '';
    showMessage('overrideMessage', 'Special date deleted.', true);
  } catch (error) {
    showMessage('overrideMessage', error.message);
  }
}

document.getElementById('previewSlotsBtn')?.addEventListener('click', async () => {
  const resultBox = document.getElementById('previewSlotsResult');
  resultBox.innerHTML = '<div class="preview-empty">Loading slots...</div>';
  try {
    const result = await jsonPost(urls.preview, { date: document.getElementById('previewDate').value });
    const slots = result.preview?.slots || [];
    if (!slots.length) {
      resultBox.innerHTML = '<div class="preview-empty">Closed or no available slots for this date</div>';
      return;
    }
    resultBox.innerHTML = slots.map(slot => {
      const start = String(slot.start_time || '').slice(0, 5);
      const end = String(slot.end_time || '').slice(0, 5);
      return `<span class="slot-pill">${start} - ${end} (${slot.confirmed_count}/${slot.max_concurrent})</span>`;
    }).join('');
  } catch (error) {
    resultBox.innerHTML = `<div class="preview-empty">${error.message}</div>`;
  }
});
