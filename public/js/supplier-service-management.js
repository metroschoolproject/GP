// ── DATA ──────────────────────────────────────────────────────
const ICON  = { Venue:'🏛️', Accessories:'✨', Dress:'👗', Decoration:'🌸', Food:'🍽️', Package:'🎀', Studio:'📸', Makeup:'💄', Photography:'📸', Catering:'🍽️', Decor:'🌸', Music:'🎵', Others:'✨' };

const serviceManagementConfig = window.serviceManagementConfig || {};
const serviceManagementUrls = serviceManagementConfig.urls || {};
const DATA_URLROOT = (serviceManagementConfig.urls?.data || '').replace(/\/supplierServices\/serviceManagementData$/, '');
const PAGE_SIZE = Number(serviceManagementConfig.pageSize || 24);
const PACKAGES_AVAILABLE = serviceManagementConfig.initialData?.meta?.supplier_packages_available !== false;
const INITIAL_TAB = PACKAGES_AVAILABLE && serviceManagementConfig.initialTab === 'packages' ? 'packages' : 'services';

let currentTab = INITIAL_TAB, currentFilter = 'All', statusFilter = 'all', nextId = 200;
let editingSvcId = null, editingPkgId = null;
let currentSort = 'newest'; // newest, name_asc, price_asc, price_desc
let viewMode = localStorage.getItem('smViewMode') || 'grid';

// ── TOAST ───────────────────────────────────────────────────────
function showToast(message, type) {
  type = type || 'error';
  const toast = document.getElementById('smToast');
  if (!toast) {
    const div = document.createElement('div');
    div.id = 'smToast';
    div.style.cssText = 'position:fixed;bottom:24px;left:50%;transform:translateX(-50%);z-index:9999;padding:10px 20px;border-radius:12px;font-size:13px;font-weight:600;max-width:420px;text-align:center;box-shadow:0 8px 30px rgba(0,0,0,0.18);transition:all 0.3s ease;pointer-events:none;opacity:0;';
    document.body.appendChild(div);
    return showToast(message, type);
  }
  toast.textContent = message;
  toast.style.opacity = '1';
  toast.style.transform = 'translateX(-50%) translateY(0)';
  toast.style.background = type === 'success' ? '#166534' : type === 'info' ? '#1e40af' : '#991b1b';
  toast.style.color = '#fff';
  clearTimeout(toast._timer);
  toast._timer = setTimeout(function(){
    toast.style.opacity = '0';
    toast.style.transform = 'translateX(-50%) translateY(8px)';
  }, 3500);
}

function normalizeServiceItem(item) {
  if (!item || typeof item !== 'object') return null;
  const rawPriceMin = Number(item.price_min ?? item.priceMin ?? item.price ?? 0);
  const priceMin = Number.isFinite(rawPriceMin) ? rawPriceMin : 0;
  const rawPriceMax = Number(item.price_max ?? item.priceMax ?? priceMin);
  const priceMax = Number.isFinite(rawPriceMax) ? Math.max(priceMin, rawPriceMax) : priceMin;

  return {
    ...item,
    id: Number(item.id),
    name: item.name || 'Untitled Service',
    price: Number(item.price ?? priceMin ?? 0),
    price_min: priceMin,
    price_max: priceMax,
    category: item.category || 'Others',
    status: item.status === 'inactive' ? 'inactive' : 'active',
    desc: item.desc || item.description || '',
    img: item.img || item.thumbnail_url || '',
    capacity: Number(item.capacity || 1),
    min_lead_days: Math.max(0, Number(item.min_lead_days || 0)),
    venue_rooms: Array.isArray(item.venue_rooms) ? item.venue_rooms : []
  };
}

function normalizePackageItem(item) {
  if (!item || typeof item !== 'object') return null;

  return {
    ...item,
    id: Number(item.id),
    name: item.name || 'Untitled Package',
    price: Number(item.price || item.total_price || 0),
    categories: Array.isArray(item.categories) ? item.categories : [],
    status: item.status === 'inactive' ? 'inactive' : 'active',
    desc: item.desc || item.description || '',
    img: item.img || item.thumbnail_url || ''
  };
}

let services = (serviceManagementConfig.initialData?.services || []).map(normalizeServiceItem).filter(Boolean);
let packages = (serviceManagementConfig.initialData?.packages || []).map(normalizePackageItem).filter(Boolean);
let serviceCategories = (serviceManagementConfig.initialData?.categories || [])
  .map(category => category.name || category)
  .filter(Boolean);
let pagingMeta = serviceManagementConfig.initialData?.meta || {};
let isLoadingMore = false;

if (!serviceCategories.length) {
  serviceCategories = ['Accessories', 'Decoration', 'Dress', 'Food', 'Package', 'Studio', 'Venue'];
}

function escapeHtml(value) {
  return String(value).replace(/[&<>"']/g, char => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;'
  }[char]));
}

function categoryOptionHtml(category) {
  const safeCategory = escapeHtml(category);
  return `<option value="${safeCategory}">${safeCategory}</option>`;
}

function packageCheckboxHtml(category, className) {
  const safeCategory = escapeHtml(category);
  return `<label class="cb-card"><input type="checkbox" value="${safeCategory}" class="${className}" onchange="updateCpCount()"/><span class="cb-box"></span>${safeCategory}</label>`;
}

function renderCategoryControls() {
  var csSelect = document.getElementById('csCategory');
  var createCategoryCards = document.getElementById('createCategoryCards');
  var epCategoryList = document.getElementById('epCategoryList');
  var cpCategoryList = document.getElementById('cpCategoryList');
  var nonVenueCategories = serviceCategories.filter(function(category){ return category !== 'Venue'; });
  var selectableCategories = nonVenueCategories.length ? nonVenueCategories : serviceCategories;

  if (csSelect) {
    csSelect.innerHTML = '<option value="">Choose category...</option>' + serviceCategories.map(categoryOptionHtml).join('');
  }

  if (createCategoryCards) {
    var categoryMeta = {
      Venue: ['⌂', 'Rooms, halls and event spaces'],
      Decoration: ['✦', 'Themes, styling and décor'],
      Dress: ['♢', 'Wedding and formal wear'],
      Accessories: ['◌', 'Jewellery and finishing pieces'],
      Food: ['◇', 'Catering, cakes and dining'],
      Studio: ['□', 'Photo and video services'],
      Package: ['▦', 'Bundled service offerings']
    };
    createCategoryCards.innerHTML = serviceCategories.map(function(category, index) {
      var meta = categoryMeta[category] || ['＋', 'Specialist wedding service'];
      return '<button type="button" class="create-category-card" data-create-category-index="' + index + '" data-create-category="' + escapeHtml(category) + '">' +
        '<span class="create-category-icon" aria-hidden="true">' + meta[0] + '</span>' +
        '<strong>' + escapeHtml(category) + '</strong><small>' + meta[1] + '</small></button>';
    }).join('');
    createCategoryCards.querySelectorAll('[data-create-category-index]').forEach(function(card) {
      card.addEventListener('click', function() {
        selectCreateCategory(serviceCategories[Number(card.dataset.createCategoryIndex)] || '');
      });
    });
  }

  if (epCategoryList) {
    epCategoryList.innerHTML = serviceCategories.map(function(category){ return packageCheckboxHtml(category, 'ep-cb'); }).join('');
  }

  if (cpCategoryList) {
    cpCategoryList.innerHTML = serviceCategories.map(function(category){ return packageCheckboxHtml(category, 'cp-cb'); }).join('');
  }
}

async function apiRequest(url, payload = null) {
  if (!url) {
    throw new Error('Service management endpoint is not configured.');
  }

  const response = await fetch(url, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest'
    },
    body: payload ? JSON.stringify(payload) : '{}'
  });

  const responseText = await response.text();
  let data = null;

  try {
    data = responseText ? JSON.parse(responseText) : {};
  } catch (error) {
    const jsonStart = responseText.indexOf('{');
    const jsonEnd = responseText.lastIndexOf('}');
    if (jsonStart >= 0 && jsonEnd > jsonStart) {
      try {
        data = JSON.parse(responseText.slice(jsonStart, jsonEnd + 1));
      } catch (nestedError) {
        data = null;
      }
    }
  }

  if (!data) {
    const detail = responseText
      .replace(/<[^>]*>/g, ' ')
      .replace(/\s+/g, ' ')
      .trim()
      .slice(0, 220);
    throw new Error(detail || 'Server returned an invalid response. Please check the PHP error shown on the page or server log.');
  }

  if (!response.ok || data.status === 'error') {
    throw new Error(data.message || 'Request failed.');
  }
  return data;
}

function upsertItem(list, item) {
  if (!item || typeof item !== 'object') {
    throw new Error('Saved item was not returned by the server.');
  }

  const index = list.findIndex(existing => Number(existing.id) === Number(item.id));
  if (index >= 0) list[index] = item;
  else list.unshift(item);
}

function clearFieldValues(ids) {
  ids.forEach(id => {
    const field = document.getElementById(id);
    if (field) field.value = '';
  });
}

function normalizeNumberInput(input, forceMin = false) {
  if (!input || input.type !== 'number' || input.value === '') return;

  const min = input.min === '' ? 0 : Number(input.min);
  const fallbackMin = Number.isFinite(min) ? min : 0;
  let value = input.value.replace(/[^\d.]/g, '');
  const firstDot = value.indexOf('.');

  if (firstDot >= 0) {
    value = value.slice(0, firstDot + 1) + value.slice(firstDot + 1).replace(/\./g, '');
  }

  if (forceMin && value !== '' && Number(value) < fallbackMin) {
    value = String(fallbackMin);
  }

  input.value = value;
}

function installNonNegativeNumberGuards() {
  document.querySelectorAll('input[type="number"]').forEach(input => {
    if (input.min === '') input.min = '0';
    input.inputMode = input.step && input.step.includes('.') ? 'decimal' : 'numeric';
  });

  document.addEventListener('keydown', event => {
    const input = event.target;
    if (!(input instanceof HTMLInputElement) || input.type !== 'number') return;
    if (['-', '+', 'e', 'E'].includes(event.key)) event.preventDefault();
  });

  document.addEventListener('input', event => {
    const input = event.target;
    if (!(input instanceof HTMLInputElement) || input.type !== 'number') return;
    normalizeNumberInput(input);
  });

  document.addEventListener('blur', event => {
    const input = event.target;
    if (!(input instanceof HTMLInputElement) || input.type !== 'number') return;
    normalizeNumberInput(input, true);
  }, true);
}

function priceRangePayload(prefix) {
  const minInput = document.getElementById(prefix + 'PriceMin');
  const maxInput = document.getElementById(prefix + 'PriceMax');
  const fallbackInput = document.getElementById(prefix + 'Price');
  const min = parseFloat(minInput?.value || fallbackInput?.value || '0');
  const maxValue = maxInput?.value;
  const max = maxValue === undefined || maxValue === '' ? min : parseFloat(maxValue);
  const priceMin = Number.isFinite(min) ? Math.max(0, min) : 0;
  const priceMax = Number.isFinite(max) ? Math.max(priceMin, max) : priceMin;

  return {
    price: priceMin,
    price_min: priceMin,
    price_max: priceMax,
    package_price: priceMin,
    customize_price: priceMax
  };
}

function isPriceRangeValid(prefix) {
  const minInput = document.getElementById(prefix + 'PriceMin');
  const maxInput = document.getElementById(prefix + 'PriceMax');
  const min = parseFloat(minInput?.value || '0');
  const max = parseFloat(maxInput?.value || minInput?.value || '0');

  return Number.isFinite(min) && Number.isFinite(max) && max >= min;
}

function roomListId(prefix) {
  return prefix + 'RoomsList';
}

let venueRoomPhotoCounter = 0;

function venueRoomRowHtml(prefix, room = {}) {
  const id = Number(room.id || 0);
  const name = escapeHtml(room.name || '');
  const capacity = room.capacity ?? '';
  const priceMin = room.price_min ?? room.package_price ?? room.price ?? '';
  const priceMax = room.price_max ?? room.customize_price ?? priceMin;
  const startTime = escapeHtml(String(room.start_time || room.available_start_time || '09:00').slice(0, 5));
  const endTime = escapeHtml(String(room.end_time || room.available_end_time || '17:00').slice(0, 5));
  const minLeadDays = room.min_lead_days ?? '';
  const photoUrl = room.photo_url || '';
  const photoUid = ++venueRoomPhotoCounter;
  const photoInputId = `roomPhotoInput_${photoUid}`;
  const photoPreviewId = `roomPhotoPreview_${photoUid}`;
  const photoDataId = `roomPhotoData_${photoUid}`;
  const photoPreviewHtml = photoUrl
    ? `<img src="${escapeHtml(photoUrl)}" style="width:100%;height:100%;object-fit:cover;border-radius:6px"/>`
    : `<span style="font-size:11px;color:#aaa">Hall photo</span>`;

  return `
    <div class="room-row ${prefix}-venue-room" data-room-id="${id}">
      <input type="hidden" class="room-id" value="${id || ''}">
      <input type="hidden" class="room-photo-data" id="${photoDataId}" value="${escapeHtml(photoUrl)}">
      <div class="room-row-inner">
        <div>
          <label class="fm-label">Room name</label>
          <input type="text" value="${name}" placeholder="e.g. Grand Hall" class="room-name fm-input" style="font-size:12px;padding:7px 8px"/>
        </div>
        <div>
          <label class="fm-label">Capacity</label>
          <input type="number" min="1" value="${capacity}" placeholder="300" class="room-capacity fm-input" style="font-size:12px;padding:7px 8px"/>
        </div>
        <div>
          <label class="fm-label">Package price</label>
          <input type="number" min="0" step="0.01" value="${priceMin}" placeholder="3500" class="room-price room-price-min fm-input" style="font-size:12px;padding:7px 8px"/>
        </div>
        <div>
          <label class="fm-label">Customize price</label>
          <input type="number" min="0" step="0.01" value="${priceMax}" placeholder="6000" class="room-price-max fm-input" style="font-size:12px;padding:7px 8px"/>
        </div>
        <div>
          <label class="fm-label">Start time</label>
          <input type="time" lang="en-GB" value="${startTime}" class="room-start-time fm-input" style="font-size:12px;padding:7px 8px"/>
        </div>
        <div>
          <label class="fm-label">End time</label>
          <input type="time" lang="en-GB" value="${endTime}" class="room-end-time fm-input" style="font-size:12px;padding:7px 8px"/>
        </div>
        <div>
          <label class="fm-label">Min. notice (days)</label>
          <input type="number" min="0" max="365" value="${minLeadDays}" placeholder="Leave blank to use service default" class="room-min-lead-days fm-input" style="font-size:12px;padding:7px 8px"/>
        </div>
        <div>
          <label class="fm-label">Hall photo</label>
          <label for="${photoInputId}" id="${photoPreviewId}" style="display:flex;align-items:center;justify-content:center;width:100%;height:60px;border:1.5px dashed #d1c9c0;border-radius:6px;cursor:pointer;overflow:hidden;background:#faf8f5">${photoPreviewHtml}</label>
          <input type="file" id="${photoInputId}" accept="image/*" class="absolute opacity-0 pointer-events-none w-px h-px" onchange="onRoomPhotoChange(this,'${photoDataId}','${photoPreviewId}')"/>
        </div>
        <button type="button" onclick="removeVenueRoom(this, '${prefix}')" class="room-row-remove" title="Remove">&times;</button>
      </div>
    </div>
  `;
}

function renderVenueRooms(prefix, rooms = []) {
  const list = document.getElementById(roomListId(prefix));
  if (!list) return;

  const rows = Array.isArray(rooms) && rooms.length ? rooms : [{}];
  list.innerHTML = rows.map(room => venueRoomRowHtml(prefix, room)).join('');
}

function addVenueRoom(prefix) {
  const list = document.getElementById(roomListId(prefix));
  if (!list) return;

  list.insertAdjacentHTML('beforeend', venueRoomRowHtml(prefix));
}

function removeVenueRoom(button, prefix) {
  const list = document.getElementById(roomListId(prefix));
  const row = button.closest('.' + prefix + '-venue-room');
  if (!list || !row) return;

  if (list.querySelectorAll('.' + prefix + '-venue-room').length <= 1) {
    row.querySelectorAll('input').forEach(input => input.value = '');
    return;
  }

  row.remove();
}

function collectVenueRooms(prefix) {
  const rows = Array.from(document.querySelectorAll('.' + prefix + '-venue-room'));

  return rows.map(row => {
    const name = row.querySelector('.room-name')?.value.trim() || '';
    const capacity = parseInt(row.querySelector('.room-capacity')?.value || '0', 10);
    const priceMin = parseFloat(row.querySelector('.room-price-min')?.value || row.querySelector('.room-price')?.value || '0');
    const rawPriceMax = row.querySelector('.room-price-max')?.value;
    const priceMax = rawPriceMax === undefined || rawPriceMax === '' ? priceMin : parseFloat(rawPriceMax);
    const startTime = row.querySelector('.room-start-time')?.value || '09:00';
    const endTime = row.querySelector('.room-end-time')?.value || '17:00';
    const minLeadDaysRaw = row.querySelector('.room-min-lead-days')?.value.trim();
    const minLeadDays = minLeadDaysRaw === '' ? null : parseInt(minLeadDaysRaw || '0', 10);
    const packagePrice = Number.isFinite(priceMin) && priceMin > 0 ? priceMin : 0;
    const customizePrice = Number.isFinite(priceMax) && priceMax >= packagePrice ? priceMax : packagePrice;

    const photoData = row.querySelector('.room-photo-data')?.value || null;
    return {
      id: row.querySelector('.room-id')?.value || null,
      name,
      capacity: Number.isFinite(capacity) && capacity > 0 ? capacity : 1,
      price: packagePrice,
      price_min: packagePrice,
      price_max: customizePrice,
      package_price: packagePrice,
      customize_price: customizePrice,
      start_time: startTime,
      end_time: endTime,
      min_lead_days: minLeadDays,
      photo_url: photoData || null,
    };
  }).filter(room => room.name || room.capacity > 1 || room.price_min > 0 || room.price_max > 0);
}

function onRoomPhotoChange(input, dataId, previewId) {
  const file = input.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = e => {
    const dataUrl = e.target.result;
    const dataInput = document.getElementById(dataId);
    if (dataInput) dataInput.value = dataUrl;
    const preview = document.getElementById(previewId);
    if (preview) preview.innerHTML = `<img src="${dataUrl}" style="width:100%;height:100%;object-fit:cover;border-radius:6px"/>`;
  };
  reader.readAsDataURL(file);
}

function venueRoomPriceRange(prefix) {
  const rooms = collectVenueRooms(prefix);
  const packagePrices = rooms.map(room => Number(room.price_min || room.price || 0)).filter(price => price > 0);
  const customizePrices = rooms.map(room => Number(room.price_max || room.price_min || room.price || 0)).filter(price => price > 0);
  const priceMin = packagePrices.length ? Math.min(...packagePrices) : 0;
  const priceMax = customizePrices.length ? Math.max(...customizePrices) : priceMin;

  return {
    price: priceMin,
    price_min: priceMin,
    price_max: Math.max(priceMin, priceMax),
    package_price: priceMin,
    customize_price: Math.max(priceMin, priceMax)
  };
}

function venueRoomMaxCapacity(prefix) {
  return collectVenueRooms(prefix).reduce((max, room) => Math.max(max, Number(room.capacity || 0)), 1);
}

function validateVenueRooms(prefix) {
  const rows = Array.from(document.querySelectorAll('.' + prefix + '-venue-room'));
  const invalidTimeRow = rows.find(row => {
    const start = row.querySelector('.room-start-time')?.value || '';
    const end = row.querySelector('.room-end-time')?.value || '';
    return start && end && end <= start;
  });

  if (invalidTimeRow) {
    showToast('Room end time must be later than the start time.', 'error');
    return false;
  }

  const invalidPriceRow = rows.find(row => {
    const min = parseFloat(row.querySelector('.room-price-min')?.value || '0');
    const max = parseFloat(row.querySelector('.room-price-max')?.value || row.querySelector('.room-price-min')?.value || '0');
    return Number.isFinite(min) && Number.isFinite(max) && max < min;
  });

  if (invalidPriceRow) {
    showToast('Room customize price must be greater than or equal to package price.', 'error');
    return false;
  }

  const rooms = collectVenueRooms(prefix);
  if (!rooms.length) {
    showToast('Please add at least one room or hall.', 'error');
    return false;
  }

  const missingPriceRoom = rooms.find(room => room.price_min <= 0 || room.price_max <= 0);
  if (missingPriceRoom) {
    showToast('Please fill in package price and customize price for every room.', 'error');
    return false;
  }

  return true;
}

// ── DECORATION STYLES ────────────────────────────────────────
let decorationStyleCounter = 0;

function decorationStyleRowHtml(prefix, style = {}) {
  const uid = ++decorationStyleCounter;
  const name = escapeHtml(style.name || '');
  const packagePrice = style.package_price ?? style.price ?? '';
  const customizePrice = style.customize_price ?? style.price ?? '';
  const photoUrl = style.photo_url || '';
  const photoInputId = `stylePhotoInput_${uid}`;
  const photoPreviewId = `stylePhotoPreview_${uid}`;
  const photoDataId = `stylePhotoData_${uid}`;
  const photoPreviewHtml = photoUrl
    ? `<img src="${escapeHtml(photoUrl)}" style="width:100%;height:100%;object-fit:cover;border-radius:6px"/>`
    : `<span style="font-size:11px;color:#aaa">Style photo</span>`;
  return `
    <div class="${prefix}-style-row" style="display:grid;grid-template-columns:88px minmax(0,1fr) 130px 130px 28px;gap:8px;align-items:center">
      <input type="hidden" class="style-photo-data" id="${photoDataId}" value="${escapeHtml(photoUrl)}">
      <label for="${photoInputId}" id="${photoPreviewId}" style="display:flex;align-items:center;justify-content:center;width:88px;height:58px;border:1.5px dashed #d1c9c0;border-radius:6px;cursor:pointer;overflow:hidden;background:#faf8f5">${photoPreviewHtml}</label>
      <input type="file" id="${photoInputId}" accept="image/*" class="absolute opacity-0 pointer-events-none w-px h-px" onchange="onDecorationStylePhotoChange(this,'${photoDataId}','${photoPreviewId}')"/>
      <input type="text" value="${name}" placeholder="e.g. Balloon arch" class="fm-input style-name" style="font-size:13px;padding:6px 8px"/>
      <input type="number" min="0" step="0.01" value="${packagePrice}" placeholder="Package price" class="fm-input style-package-price" style="font-size:12px;padding:6px 8px"/>
      <input type="number" min="0" step="0.01" value="${customizePrice}" placeholder="Customize price" class="fm-input style-customize-price" style="font-size:12px;padding:6px 8px"/>
      <button type="button" onclick="removeDecorationStyle(this,'${prefix}')" class="room-row-remove" title="Remove">&times;</button>
    </div>
  `;
}

function collectDecorationStyles(prefix) {
  const rows = Array.from(document.querySelectorAll('.' + prefix + '-style-row'));
  return rows.map(row => ({
    name: row.querySelector('.style-name')?.value.trim() || '',
    price: parseFloat(row.querySelector('.style-package-price')?.value || row.querySelector('.style-price')?.value || '0') || 0,
    package_price: parseFloat(row.querySelector('.style-package-price')?.value || row.querySelector('.style-price')?.value || '0') || 0,
    customize_price: parseFloat(row.querySelector('.style-customize-price')?.value || row.querySelector('.style-price')?.value || '0') || 0,
    photo_url: row.querySelector('.style-photo-data')?.value || null,
  })).filter(s => s.name !== '');
}

function renderDecorationStyles(prefix, styles = []) {
  const list = document.getElementById(prefix + 'StylesList');
  if (!list) return;
  const rows = styles.length ? styles : [{}];
  list.innerHTML = rows.map(s => decorationStyleRowHtml(prefix, s)).join('');
}

function addDecorationStyle(prefix) {
  const list = document.getElementById(prefix + 'StylesList');
  if (!list) return;
  list.insertAdjacentHTML('beforeend', decorationStyleRowHtml(prefix));
}

function removeDecorationStyle(btn, prefix) {
  const list = document.getElementById(prefix + 'StylesList');
  const row = btn.closest('.' + prefix + '-style-row');
  if (!list || !row) return;
  if (list.querySelectorAll('.' + prefix + '-style-row').length <= 1) {
    row.querySelectorAll('input').forEach(i => i.value = '');
    return;
  }
  row.remove();
}

function collectDecorationStylesOld(prefix) {
  const rows = Array.from(document.querySelectorAll('.' + prefix + '-style-row'));
  return rows.map(row => ({
    name: row.querySelector('.style-name')?.value.trim() || '',
    price: parseFloat(row.querySelector('.style-package-price')?.value || row.querySelector('.style-price')?.value || '0') || 0,
    package_price: parseFloat(row.querySelector('.style-package-price')?.value || row.querySelector('.style-price')?.value || '0') || 0,
    customize_price: parseFloat(row.querySelector('.style-customize-price')?.value || row.querySelector('.style-price')?.value || '0') || 0,
    photo_url: row.querySelector('.style-photo-data')?.value || null,
  })).filter(s => s.name !== '');
}

function onDecorationStylePhotoChange(input, dataId, previewId) {
  const file = input.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = e => {
    const dataUrl = e.target.result;
    const dataInput = document.getElementById(dataId);
    if (dataInput) dataInput.value = dataUrl;
    const preview = document.getElementById(previewId);
    if (preview) preview.innerHTML = `<img src="${dataUrl}" style="width:100%;height:100%;object-fit:cover;border-radius:6px"/>`;
  };
  reader.readAsDataURL(file);
}

function validateDecorationStyles(prefix) {
  const styles = collectDecorationStyles(prefix);
  if (!styles.length) { showToast('Add at least one decoration style with a name.', 'error'); return false; }
  const missingPrice = styles.find(s => s.price <= 0);
  if (missingPrice) { showToast('Each decoration style needs a price greater than zero.', 'error'); return false; }
  return true;
}

// ── RENTAL PRICING ────────────────────────────────────────────
function collectRentalPricing(prefix) {
  const borrowPackagePrice = parseFloat(document.getElementById(prefix + 'BorrowPackagePrice')?.value || document.getElementById(prefix + 'BorrowPrice')?.value || '0') || 0;
  const borrowCustomizeRaw = parseFloat(document.getElementById(prefix + 'BorrowCustomizePrice')?.value || '0') || 0;
  const borrowCustomizePrice = borrowCustomizeRaw > 0 ? Math.max(borrowPackagePrice, borrowCustomizeRaw) : borrowPackagePrice;
  const returnDays = parseInt(document.getElementById(prefix + 'ReturnDays')?.value || '0') || 0;
  const buyPackagePrice = parseFloat(document.getElementById(prefix + 'BuyPackagePrice')?.value || document.getElementById(prefix + 'BuyPrice')?.value || '0') || 0;
  const buyCustomizeRaw = parseFloat(document.getElementById(prefix + 'BuyCustomizePrice')?.value || '0') || 0;
  const buyCustomizePrice = buyCustomizeRaw > 0 ? Math.max(buyPackagePrice, buyCustomizeRaw) : buyPackagePrice;
  return {
    borrow_package_price: borrowPackagePrice > 0 ? borrowPackagePrice : null,
    borrow_customize_price: borrowCustomizePrice > 0 ? borrowCustomizePrice : null,
    borrow_price: borrowPackagePrice > 0 ? borrowPackagePrice : null,
    return_days: borrowPackagePrice > 0 && returnDays > 0 ? returnDays : null,
    buy_package_price: buyPackagePrice > 0 ? buyPackagePrice : null,
    buy_customize_price: buyCustomizePrice > 0 ? buyCustomizePrice : null,
    buy_price: buyPackagePrice > 0 ? buyPackagePrice : null,
  };
}

function validateRentalPricing(prefix) {
  const rental = collectRentalPricing(prefix);
  if (!rental.borrow_package_price && !rental.buy_package_price) {
    showToast('Add a borrow package price, a buy package price, or both.', 'error');
    return false;
  }
  return true;
}

// ── CATEGORY-SPECIFIC SECTION TOGGLE ─────────────────────────
function onOthersCategoryChange(category) {
  const isDecoration = category === 'Decoration';
  var isRental = (category === 'Dress' || category === 'Accessories' || category === 'Attire') || category === 'Attire';
  document.getElementById('oStandardPriceFields')?.classList.toggle('hidden', isDecoration || isRental);
  document.getElementById('oDecorationExtras')?.classList.toggle('hidden', !isDecoration);
  document.getElementById('oRentalExtras')?.classList.toggle('hidden', !isRental);
  if (isDecoration && !document.querySelectorAll('.o-style-row').length) renderDecorationStyles('o');
}

// ── ATTIRE ITEMS ────────────────────────────────────────────
function addAttireItem(prefix) {
  var list = document.getElementById(prefix + 'AttireItemsList');
  if (!list) return;
  list.insertAdjacentHTML('beforeend', attireItemRowHtml(prefix));
}

function removeAttireItem(button, prefix) {
  var list = document.getElementById(prefix + 'AttireItemsList');
  var row = button.closest('.' + prefix + '-attire-item');
  if (!list || !row) return;
  if (list.querySelectorAll('.' + prefix + '-attire-item').length <= 1) {
    row.querySelectorAll('input').forEach(function(input) { input.value = ''; });
    return;
  }
  row.remove();
}

function attireItemRowHtml(prefix) {
  return '<div class="' + prefix + '-attire-item room-card" style="margin-bottom:10px">' +
    '<input class="room-id" type="hidden" value="">' +
    '<div class="modal-grid-2">' +
    '<div><label class="fm-label">Name</label><input class="' + prefix + '-attire-name fm-input" type="text" placeholder="e.g. Traditional Wedding Dress"></div>' +
    '<div><label class="fm-label">Photo <small>(optional)</small></label><input class="' + prefix + '-attire-photo fm-input" type="file" accept="image/*" onchange="onAttirePhotoChange(this,\'' + prefix + '\')"></div>' +
    '</div>' +
    '<div class="modal-grid-2" style="margin-top:8px">' +
    '<div><label class="fm-label">Borrow Package Price</label><input class="' + prefix + '-attire-borrow-pkg fm-input" type="number" min="0" step="0.01" placeholder="e.g. 250,000"></div>' +
    '<div><label class="fm-label">Borrow Customize Price</label><input class="' + prefix + '-attire-borrow-cust fm-input" type="number" min="0" step="0.01" placeholder="e.g. 350,000"></div>' +
    '</div>' +
    '<div class="modal-grid-2" style="margin-top:8px">' +
    '<div><label class="fm-label">Buy Package Price</label><input class="' + prefix + '-attire-buy-pkg fm-input" type="number" min="0" step="0.01" placeholder="e.g. 850,000"></div>' +
    '<div><label class="fm-label">Buy Customize Price</label><input class="' + prefix + '-attire-buy-cust fm-input" type="number" min="0" step="0.01" placeholder="e.g. 1,200,000"></div>' +
    '</div>' +
    '<div class="modal-grid-2" style="margin-top:8px">' +
    '<div><label class="fm-label">Return days</label><input class="' + prefix + '-attire-return-days fm-input" type="number" min="1" step="1" placeholder="e.g. 3"></div>' +
    '</div>' +
    '<div class="attire-photo-data-wrap"><input class="' + prefix + '-attire-photo-data" type="hidden" value=""></div>' +
    '<button type="button" onclick="removeAttireItem(this,\'' + prefix + '\')" class="item-remove-btn">' +
      '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>' +
      'Remove item' +
    '</button>' +
    '</div>';
}

function onAttirePhotoChange(input, prefix) {
  var file = input.files[0];
  if (!file) return;
  var reader = new FileReader();
  reader.onload = function(e) {
    var row = input.closest('.' + prefix + '-attire-item');
    if (row) {
      var dataInput = row.querySelector('.' + prefix + '-attire-photo-data');
      if (dataInput) dataInput.value = e.target.result;
    }
  };
  reader.readAsDataURL(file);
}

function collectAttireItems(prefix) {
  var rows = Array.from(document.querySelectorAll('.' + prefix + '-attire-item'));
  return rows.map(function(row) {
    var name = (row.querySelector('.' + prefix + '-attire-name')?.value || '').trim();
    return {
      id: row.querySelector('.room-id')?.value || null,
      name: name,
      photo_url: row.querySelector('.' + prefix + '-attire-photo-data')?.value || null,
      borrow_package_price: parseFloat(row.querySelector('.' + prefix + '-attire-borrow-pkg')?.value || '0') || null,
      borrow_customize_price: parseFloat(row.querySelector('.' + prefix + '-attire-borrow-cust')?.value || '0') || null,
      buy_package_price: parseFloat(row.querySelector('.' + prefix + '-attire-buy-pkg')?.value || '0') || null,
      buy_customize_price: parseFloat(row.querySelector('.' + prefix + '-attire-buy-cust')?.value || '0') || null,
      return_days: parseInt(row.querySelector('.' + prefix + '-attire-return-days')?.value || '0', 10) || null,
    };
  }).filter(function(item) { return item.name; });
}

function validateAttireItems(prefix) {
  var items = collectAttireItems(prefix);
  if (!items.length) {
    showToast('Please add at least one item (dress, suit, accessory) with a name.', 'error');
    return false;
  }
  var hasPriced = items.some(function(item) {
    var b = (Number(item.borrow_package_price) || 0) > 0 || (Number(item.borrow_customize_price) || 0) > 0;
    var y = (Number(item.buy_package_price) || 0) > 0 || (Number(item.buy_customize_price) || 0) > 0;
    return b || y;
  });
  if (!hasPriced) {
    showToast('At least one item needs a borrow price, buy price, or both.', 'error');
    return false;
  }
  return true;
}

function renderAttireItems(prefix, items) {
  items = items || [];
  var list = document.getElementById(prefix + 'AttireItemsList');
  if (!list) return;
  list.innerHTML = '';
  if (!items.length) {
    addAttireItem(prefix);
    return;
  }
  items.forEach(function(item) {
    addAttireItem(prefix);
    var rows = list.querySelectorAll('.' + prefix + '-attire-item');
    var row = rows[rows.length - 1];
    if (!row) return;
    row.querySelector('.room-id').value = item.id || '';
    row.querySelector('.' + prefix + '-attire-name').value = item.name || '';
    row.querySelector('.' + prefix + '-attire-photo-data').value = item.photo_url || '';
    row.querySelector('.' + prefix + '-attire-borrow-pkg').value = item.borrow_package_price ?? '';
    row.querySelector('.' + prefix + '-attire-borrow-cust').value = item.borrow_customize_price ?? '';
    row.querySelector('.' + prefix + '-attire-buy-pkg').value = item.buy_package_price ?? '';
    row.querySelector('.' + prefix + '-attire-buy-cust').value = item.buy_customize_price ?? '';
    row.querySelector('.' + prefix + '-attire-return-days').value = item.return_days ?? '';
  });
}

function serviceFormPayload(prefix, category) {
  const isVenue = category === 'Venue';
  const isDecoration = category === 'Decoration';
  var isRental = (category === 'Dress' || category === 'Accessories' || category === 'Attire') || category === 'Attire';
  const rooms = isVenue ? collectVenueRooms(prefix) : [];
  const priceRange = isVenue ? venueRoomPriceRange(prefix) : (isDecoration || isRental ? { price: 0, price_min: 0, price_max: 0, package_price: 0, customize_price: 0 } : priceRangePayload(prefix));
  const minLeadDaysEl = document.getElementById(prefix + 'MinLeadDays');
  const minLeadDaysValue = minLeadDaysEl ? minLeadDaysEl.value.trim() : '';
  const minLeadDays = minLeadDaysValue === '' ? 0 : Math.max(0, Math.min(365, parseInt(minLeadDaysValue) || 0));

  return {
    name: document.getElementById(prefix + 'Name').value.trim(),
    desc: document.getElementById(prefix + 'Desc').value.trim(),
    ...priceRange,
    category,
    status: 'active',
    img: document.getElementById(prefix + 'ImgData').value,
    capacity: isVenue ? venueRoomMaxCapacity(prefix) : (parseInt(document.getElementById(prefix + 'Capacity')?.value || '1') || 1),
    type: document.getElementById(prefix + 'Type')?.value || '',
    timeslot: document.getElementById(prefix + 'TimeSlot')?.value.trim() || '',
    venue: document.getElementById(prefix + 'Venue')?.value.trim() || '',
    venue_location: document.getElementById(prefix + 'Location')?.value.trim() || '',
    min_lead_days: minLeadDays,
    default_start_time: document.getElementById(prefix + 'DefaultStartTime')?.value || null,
    default_end_time: document.getElementById(prefix + 'DefaultEndTime')?.value || null,
    rooms,
    decoration_styles: isDecoration ? collectDecorationStyles(prefix) : [],
    rental_pricing: isRental ? collectRentalPricing(prefix) : null,
    attire_items: isRental ? collectAttireItems(prefix) : [],
    attire_items_replace: isRental,
  };
}

function formatPrice(value) {
  const amount = Number(value);
  return Number.isFinite(amount) ? amount.toLocaleString() : '0';
}

function formatPriceRange(item) {
  const min = Number(item.price_min ?? item.priceMin ?? item.price ?? 0);
  const max = Number(item.price_max ?? item.priceMax ?? min);

  if (!Number.isFinite(max) || max <= min) {
    return formatPrice(min);
  }

  return `${formatPrice(min)} - ${formatPrice(max)}`;
}

function serviceDetailUrl(id) {
  return (serviceManagementUrls.serviceDetail || '#') + encodeURIComponent(id);
}

function setViewMode(mode) {
  viewMode = mode === 'table' ? 'table' : 'grid';
  localStorage.setItem('smViewMode', viewMode);

  const gridBtn = document.getElementById('viewGrid');
  const tableBtn = document.getElementById('viewTable');
  const gridEl = document.getElementById('cardsGrid');
  const tableEl = document.getElementById('tableWrap');

  if (gridBtn) gridBtn.classList.toggle('active', viewMode === 'grid');
  if (tableBtn) tableBtn.classList.toggle('active', viewMode === 'table');
  if (gridBtn) gridBtn.setAttribute('aria-pressed', String(viewMode === 'grid'));
  if (tableBtn) tableBtn.setAttribute('aria-pressed', String(viewMode === 'table'));
  if (gridEl) {
    gridEl.classList.toggle('hidden', viewMode === 'table');
    gridEl.style.display = viewMode === 'table' ? 'none' : '';
  }
  if (tableEl) {
    tableEl.classList.toggle('hidden', viewMode === 'grid');
    tableEl.style.display = viewMode === 'grid' ? 'none' : '';
  }
}

function navigateToServiceDetail(id) {
  const url = serviceDetailUrl(id);
  if (!url || url === '#') return;

  if (window.top && window.top !== window) {
    window.top.location.href = url;
    return;
  }

  window.location.href = url;
}

function svcTableRow(item) {
  item = normalizeServiceItem(item);
  if (!item) return '';

  const name = escapeHtml(item.name || 'Untitled');
  const img = item.img
    ? `<img src="${escapeHtml(item.img)}" class="td-name-img" alt="">`
    : '<div class="td-name-img" style="display:grid;place-items:center;color:#A8A29E;font-size:14px">Photo</div>';
  const isActive = item.status === 'active';

  return `
    <tr>
      <td><span class="td-status ${isActive ? 'is-active' : ''}">${isActive ? 'Published' : 'Draft'}</span></td>
      <td><div class="td-name">${img}<span class="td-name-text">${name}</span></div></td>
      <td class="td-cat">${escapeHtml(item.category || '-')}</td>
      <td class="td-price">MMK ${formatPriceRange(item)}</td>
      <td class="td-actions"><a href="${serviceDetailUrl(item.id)}" class="sm-card-link">Edit</a></td>
    </tr>
  `;
}

function pkgTableRow(item) {
  item = normalizePackageItem(item);
  if (!item) return '';

  const name = escapeHtml(item.name || 'Untitled');
  const categories = Array.isArray(item.categories) && item.categories.length ? item.categories.join(' · ') : 'Package';
  const img = item.img
    ? `<img src="${escapeHtml(item.img)}" class="td-name-img" alt="">`
    : '<div class="td-name-img" style="display:grid;place-items:center;color:#A8A29E;font-size:14px">Photo</div>';
  const isActive = item.status === 'active';

  return `
    <tr>
      <td><span class="td-status ${isActive ? 'is-active' : ''}">${isActive ? 'Published' : 'Draft'}</span></td>
      <td><div class="td-name">${img}<span class="td-name-text">${name}</span></div></td>
      <td class="td-cat">${escapeHtml(categories)}</td>
      <td class="td-price">MMK ${formatPrice(item.price)}</td>
      <td class="td-actions"><button type="button" class="sm-card-link" onclick="openEditPackage(${item.id})">Edit</button></td>
    </tr>
  `;
}

// ── UNIFIED CREATE SERVICE MODAL ─────────────────────────────
let currentCreateCategory = '';
let currentCreateStep = 1;

function selectCreateCategory(cat) {
  currentCreateCategory = cat;
  var select = document.getElementById('csCategory');
  if (select) select.value = cat;
  document.querySelectorAll('[data-create-category]').forEach(function(card) {
    card.classList.toggle('is-selected', card.dataset.createCategory === cat);
    card.setAttribute('aria-pressed', card.dataset.createCategory === cat ? 'true' : 'false');
  });
  var detailCategory = document.getElementById('createDetailCategory');
  if (detailCategory) detailCategory.textContent = cat || 'your category';
  toggleCategoryFields(cat);
  updateCreateFlowControls();
}

function toggleCategoryFields(category) {
  var isVenue = category === 'Venue';
  var isDecoration = category === 'Decoration';
  var isRental = (category === 'Dress' || category === 'Accessories' || category === 'Attire') || category === 'Attire';

  var standard = document.getElementById('csStandardPriceFields');
  var venueExtras = document.getElementById('csVenueExtras');
  var decoExtras = document.getElementById('csDecorationExtras');
  var rentalExtras = document.getElementById('csRentalExtras');
  var attireList = document.getElementById('csAttireItemsList');

  if (standard) standard.classList.toggle('hidden-section', isVenue || isDecoration || isRental);
  if (attireList) {
    if (isRental && !attireList.querySelectorAll('.cs-attire-item').length) {
      addAttireItem('cs');
    }
  }
  if (venueExtras) venueExtras.classList.toggle('hidden-section', !isVenue);
  if (decoExtras) decoExtras.classList.toggle('hidden-section', !isDecoration);
  if (rentalExtras) rentalExtras.classList.toggle('hidden-section', !isRental);

  if (isDecoration && !document.querySelectorAll('.cs-style-row').length) renderDecorationStyles('cs');
}

function onCreateCategoryChange(value) {
  selectCreateCategory(value);
}
function onOthersCategoryChange(value) { onCreateCategoryChange(value); }

function openCreateServiceModal() {
  resetCreateForm();
  document.getElementById('createServiceModal').classList.remove('hidden');
  renderCategoryControls();
  currentCreateStep = 1;
  selectCreateCategory('');
  showCreateStep(1);
}

function closeCreateServiceModal() {
  document.getElementById('createServiceModal').classList.add('hidden');
}

function resetCreateForm() {
  ['csName','csDesc','csPriceMin','csPriceMax','csCapacity','csImgData','csVenue','csLocation',
   'csBorrowPackagePrice','csBorrowCustomizePrice','csBuyPackagePrice','csBuyCustomizePrice','csReturnDays'].forEach(function(id){
    var el = document.getElementById(id); if (el) el.value = '';
  });
  var typeEl = document.getElementById('csType'); if (typeEl) typeEl.value = '';
  var leadEl = document.getElementById('csMinLeadDays'); if (leadEl) leadEl.value = '0';
  var startEl = document.getElementById('csDefaultStartTime'); if (startEl) startEl.value = '';
  var endEl = document.getElementById('csDefaultEndTime'); if (endEl) endEl.value = '';
  resetImgBox('csImgBox', true);
  renderVenueRooms('cs');
  var stylesList = document.getElementById('csStylesList'); if (stylesList) stylesList.innerHTML = '';
  currentCreateCategory = '';
}

function showCreateStep(step) {
  currentCreateStep = Math.max(1, Math.min(3, Number(step) || 1));
  document.querySelectorAll('[data-create-step]').forEach(function(panel) {
    panel.classList.toggle('is-active', Number(panel.dataset.createStep) === currentCreateStep);
  });
  document.querySelectorAll('[data-create-step-button]').forEach(function(button) {
    var buttonStep = Number(button.dataset.createStepButton);
    button.classList.toggle('is-active', buttonStep === currentCreateStep);
    button.classList.toggle('is-complete', buttonStep < currentCreateStep);
  });

  var subtitles = {
    1: 'Start by choosing what you provide.',
    2: 'Add the information customers need at a glance.',
    3: 'Complete pricing, availability, and category details.'
  };
  var subtitle = document.getElementById('createServiceSubtitle');
  if (subtitle) subtitle.textContent = subtitles[currentCreateStep];
  updateCreateFlowControls();

  var body = document.querySelector('#createServiceModal .create-service-body');
  if (body) body.scrollTop = 0;
}

function updateCreateFlowControls() {
  var back = document.getElementById('createBackBtn');
  var next = document.getElementById('createNextBtn');
  var save = document.getElementById('createSaveBtn');
  var summary = document.getElementById('createStepSummary');
  if (back) back.classList.toggle('hidden', currentCreateStep === 1);
  if (next) {
    next.classList.toggle('hidden', currentCreateStep === 3);
    next.disabled = currentCreateStep === 1 && !currentCreateCategory;
  }
  if (save) save.classList.toggle('hidden', currentCreateStep !== 3);
  if (summary) {
    summary.textContent = currentCreateStep === 1
      ? (currentCreateCategory ? currentCreateCategory + ' selected' : 'Choose one category to continue')
      : currentCreateStep === 2
        ? 'Step 2 of 3 · ' + currentCreateCategory
        : 'Ready to create · ' + currentCreateCategory;
  }
}

function validateCreateEssentials() {
  var name = document.getElementById('csName');
  if (!name || !name.value.trim()) {
    showToast('Add a service name before continuing.', 'error');
    if (name) name.focus();
    return false;
  }
  return true;
}

function validateDefaultEventTime(prefix) {
  const start = document.getElementById(prefix + 'DefaultStartTime');
  const end = document.getElementById(prefix + 'DefaultEndTime');
  if (!start || !end) return true;

  const startValue = start.value.trim();
  const endValue = end.value.trim();
  if (!startValue && !endValue) return true;

  if (!startValue || !endValue) {
    showToast('Add both default event start and end time, or leave both blank.', 'error');
    (startValue ? end : start).focus();
    return false;
  }

  if (startValue >= endValue) {
    showToast('Default event end time must be later than the start time.', 'error');
    end.focus();
    return false;
  }

  return true;
}

function nextCreateStep() {
  if (currentCreateStep === 1 && !currentCreateCategory) {
    showToast('Choose a service category first.', 'error');
    return;
  }
  if (currentCreateStep === 2 && !validateCreateEssentials()) return;
  showCreateStep(currentCreateStep + 1);
}

function previousCreateStep() {
  showCreateStep(currentCreateStep - 1);
}

function goToCreateStep(step) {
  step = Number(step);
  if (step >= currentCreateStep) return;
  showCreateStep(step);
}

async function saveCreateService() {
  var category = document.getElementById('csCategory').value || currentCreateCategory;
  var name = document.getElementById('csName').value.trim();
  if (!name) { showCreateStep(2); showToast('Please fill in the service name.', 'error'); return; }
  if (!validateDefaultEventTime('cs')) { showCreateStep(3); return; }

  if (category === 'Venue') {
    if (!validateVenueRooms('cs')) return;
  } else if (category === 'Decoration') {
    if (!validateDecorationStyles('cs')) return;
  } else if (category === 'Dress' || category === 'Accessories' || category === 'Attire') {
    if (!validateAttireItems('cs')) return;
  } else {
    var priceMin = parseFloat(document.getElementById('csPriceMin').value);
    var priceMax = parseFloat(document.getElementById('csPriceMax').value || document.getElementById('csPriceMin').value);
    if (isNaN(priceMin) || isNaN(priceMax)) { showToast('Please fill in package price and customize price.', 'error'); return; }
    if (priceMax < priceMin) { showToast('Customize price must be ≥ package price.', 'error'); return; }
  }

  try {
    var result = await apiRequest(serviceManagementUrls.serviceCreate, serviceFormPayload('cs', category));
    upsertItem(services, result.item);
    closeCreateServiceModal(); currentTab = 'services'; switchTab('services');
  } catch (error) { showToast(error.message, 'error'); }
}

// ── RENDER ────────────────────────────────────────────────────
function render() {
  const grid  = document.getElementById('cardsGrid');
  const empty = document.getElementById('emptyState');
  const emptyMessage = document.getElementById('emptyStateMessage');
  const emptyActionLabel = document.getElementById('emptyStateActionLabel');
  let items = currentTab === 'services' ? services : packages;
  const searchActive = searchQuery !== '';

  items = items.filter(Boolean).filter(i => {
    const catOk = currentTab === 'services'
      ? (currentFilter === 'All' || i.category === currentFilter)
      : (currentFilter === 'All' || (i.categories||[]).includes(currentFilter));
    const statusOk = statusFilter === 'all' || i.status === statusFilter;
    return catOk && statusOk;
  });

  // Client-side sort (server returns newest-first for searches)
  if (!searchActive) {
    if (currentSort === 'name_asc') items.sort((a,b) => (a.name||'').localeCompare(b.name||''));
    else if (currentSort === 'price_asc') items.sort((a,b) => (a.price||0) - (b.price||0));
    else if (currentSort === 'price_desc') items.sort((a,b) => (b.price||0) - (a.price||0));
    else items.sort((a,b) => (b.id||0) - (a.id||0));
  }

  if (!grid || !empty) return;

  grid.innerHTML = '';
  const tableBody = document.getElementById('tableBody');
  if (tableBody) tableBody.innerHTML = '';
  empty.classList.add('hidden');

  // Sort bar
  const sortWrap = document.getElementById('smSortWrap');
  if (sortWrap) sortWrap.classList.toggle('hidden', items.length === 0);
  // Highlight active sort
  document.querySelectorAll('.sort-btn').forEach(b => b.classList.remove('is-active'));
  const activeSortBtn = document.getElementById('sort-'+currentSort.replace('_','-'));
  if (activeSortBtn) {
    activeSortBtn.classList.add('is-active');
  }

  if (!items.length) {
    if (searchActive) {
      if (emptyMessage) emptyMessage.textContent = 'No results for “' + searchQuery + '”';
      if (emptyActionLabel) {
        emptyActionLabel.textContent = 'Clear search';
      }
    } else if (currentTab === 'packages') {
      if (emptyMessage) emptyMessage.textContent = 'How about creating a package right now?';
      if (emptyActionLabel) emptyActionLabel.textContent = 'Create Package';
    } else {
      if (emptyMessage) emptyMessage.textContent = 'How about creating a service right now?';
      if (emptyActionLabel) emptyActionLabel.textContent = 'Create Service';
    }
    empty.classList.remove('hidden');
  } else {
    items.forEach(item => grid.insertAdjacentHTML('beforeend', currentTab === 'services' ? svcCard(item) : pkgCard(item)));
    if (tableBody) {
      tableBody.innerHTML = '';
      items.forEach(item => tableBody.insertAdjacentHTML('beforeend', currentTab === 'services' ? svcTableRow(item) : pkgTableRow(item)));
    }
  }
  setViewMode(viewMode);
  updateLoadMoreControl();
}

document.getElementById('emptyStateAction')?.addEventListener('click', () => {
  if (searchQuery) {
    const searchInput = document.getElementById('searchInput');
    if (searchInput) searchInput.value = '';
    searchQuery = '';
    services = [];
    packages = [];
    pagingMeta = {};
    renderEmpty();
    loadMoreCurrentTab();
    return;
  }
  if (currentTab === 'packages') {
    openPackageModal();
    return;
  }
  openCreateServiceModal();
});

function updateLoadMoreControl() {
  const wrap  = document.getElementById('loadMoreWrap');
  const button = document.getElementById('loadMoreBtn');
  const allLoaded = document.getElementById('allLoadedMsg');
  if (!wrap || !button) return;

  const meta = pagingMeta[currentTab] || {};
  const currentCount = currentTab === 'services' ? services.length : packages.length;
  const hasMore = Boolean(meta.has_more) || (Number(meta.total || 0) > currentCount);

  if (currentCount === 0) {
    wrap.classList.add('hidden');
    if (allLoaded) allLoaded.classList.add('hidden');
    return;
  }

  if (hasMore) {
    wrap.classList.remove('hidden');
    button.disabled = isLoadingMore;
    button.textContent = isLoadingMore ? 'Loading…' : 'Load more';
    if (allLoaded) allLoaded.classList.add('hidden');
  } else {
    wrap.classList.add('hidden');
    if (allLoaded) {
      allLoaded.classList.remove('hidden');
      allLoaded.textContent = 'Showing all ' + currentCount + ' ' + (currentTab === 'services' ? 'services' : 'packages') + '.';
    }
  }
}

let searchQuery = '';

async function loadMoreCurrentTab() {
  if (isLoadingMore) return;

  const list = currentTab === 'services' ? services : packages;
  isLoadingMore = true;
  updateLoadMoreControl();

  try {
    const result = await apiRequest(serviceManagementUrls.data, {
      tab: currentTab,
      limit: PAGE_SIZE,
      offset: list.length,
      search: searchQuery
    });
    const data = result.data || {};

    if (currentTab === 'services') {
      (data.services || []).map(normalizeServiceItem).filter(Boolean).forEach(item => upsertItem(services, item));
    } else {
      (data.packages || []).map(normalizePackageItem).filter(Boolean).forEach(item => upsertItem(packages, item));
    }

    pagingMeta[currentTab] = data.meta?.[currentTab] || pagingMeta[currentTab] || {};
    render();
  } catch (error) {
    showToast(error.message, 'error');
  } finally {
    isLoadingMore = false;
    updateLoadMoreControl();
  }
}

// ── DEBOUNCED SEARCH ─────────────────────────────────────────────
let searchDebounceTimer = null;
function onSearchInput() {
  clearTimeout(searchDebounceTimer);
  searchDebounceTimer = setTimeout(() => {
    const input = document.getElementById('searchInput');
    searchQuery = (input?.value || '').trim();
    // Reset lists and fetch fresh from server
    services = [];
    packages = [];
    pagingMeta = {};
    if (searchQuery === '') {
      // Reload page to get initial server data without search
      renderEmpty();
      loadMoreCurrentTab();
    } else {
      renderEmpty();
      loadMoreCurrentTab();
    }
  }, 300);
}

function renderEmpty() {
  const grid = document.getElementById('cardsGrid');
  if (grid) grid.innerHTML = '';
  const tableBody = document.getElementById('tableBody');
  if (tableBody) tableBody.innerHTML = '';
  document.getElementById('emptyState')?.classList.add('hidden');
}

function svcCard(item) {
  item = normalizeServiceItem(item);
  if (!item) return '';
  const icon = ICON[item.category] || '✨';
  const isActive = item.status === 'active';
  const name = escapeHtml(item.name || 'Untitled service');
  const category = escapeHtml(item.category || 'Service');
  const description = escapeHtml(item.desc || 'No description added yet.');
  const media = item.img
    ? `<img src="${escapeHtml(item.img)}" alt="${name}"/>`
    : `<div class="sm-card-placeholder" aria-hidden="true">${icon}</div>`;

  return `
  <article role="link" tabindex="0" onclick="navigateToServiceDetail(${item.id})" onkeydown="if(event.key==='Enter'||event.key===' '){event.preventDefault();navigateToServiceDetail(${item.id})}" class="service-card sm-service-card cursor-pointer">
    <div class="sm-card-media">
      ${media}
      <span class="sm-card-status ${isActive ? 'is-active' : ''}">${isActive ? 'Published' : 'Draft'}</span>
    </div>
    <div class="sm-card-body">
      <div class="sm-card-topline">
        <span class="sm-card-category">${category}</span>
        <span class="sm-card-price">MMK ${formatPriceRange(item)}</span>
      </div>
      <h3 class="sm-card-title">${name}</h3>
      <p class="sm-card-description line-clamp-2">${description}</p>
      <div class="sm-card-footer">
        <a href="${serviceDetailUrl(item.id)}" class="sm-card-link" onclick="event.stopPropagation()">Manage details →</a>
        <div class="sm-card-actions">
          <button type="button" onclick="event.stopPropagation();openEditService(${item.id})" class="sm-icon-btn" title="Edit service" aria-label="Edit ${name}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
          </button>
          <button type="button" onclick="event.stopPropagation();deleteService(${item.id})" class="sm-icon-btn is-danger" title="Delete service" aria-label="Delete ${name}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
          </button>
        </div>
      </div>
    </div>
  </article>`;
}

function pkgCard(item) {
  item = normalizePackageItem(item);
  if (!item) return '';
  const categories = item.categories || [];
  const icons = categories.map(category => ICON[category] || '🎀').join(' ');
  const isActive = item.status === 'active';
  const name = escapeHtml(item.name || 'Untitled package');
  const description = escapeHtml(item.desc || 'No description added yet.');
  const categorySummary = escapeHtml(categories.length ? categories.join(' · ') : 'Package');
  const media = item.img
    ? `<img src="${escapeHtml(item.img)}" alt="${name}"/>`
    : `<div class="sm-card-placeholder" aria-hidden="true">${icons || '🎀'}</div>`;

  return `
  <article class="service-card sm-service-card">
    <div class="sm-card-media">
      ${media}
      <span class="sm-card-status ${isActive ? 'is-active' : ''}">${isActive ? 'Published' : 'Draft'}</span>
    </div>
    <div class="sm-card-body">
      <div class="sm-card-topline">
        <span class="sm-card-category">${categorySummary}</span>
        <span class="sm-card-price">MMK ${formatPrice(item.price)}</span>
      </div>
      <h3 class="sm-card-title">${name}</h3>
      <p class="sm-card-description line-clamp-2">${description}</p>
      <div class="sm-card-footer">
        <button type="button" class="sm-card-link" onclick="openEditPackage(${item.id})">Manage package →</button>
        <div class="sm-card-actions">
          <button type="button" onclick="openEditPackage(${item.id})" class="sm-icon-btn" title="Edit package" aria-label="Edit ${name}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
          </button>
          <button type="button" onclick="deletePackage(${item.id})" class="sm-icon-btn is-danger" title="Delete package" aria-label="Delete ${name}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
          </button>
        </div>
      </div>
    </div>
  </article>`;
}

// ── TABS ──────────────────────────────────────────────────────
function switchTab(tab) {
  if (tab === 'packages' && !PACKAGES_AVAILABLE) {
    tab = 'services';
  }
  currentTab = tab;
  document.querySelectorAll('.tab-btn').forEach(b => {
    b.classList.remove('active','text-gray-800','border-gray-800');
    b.classList.add('text-gray-400','border-transparent');
  });
  const el = document.getElementById('tab-'+tab);
  if (el) {
    el.classList.add('active','text-gray-800','border-gray-800');
    el.classList.remove('text-gray-400','border-transparent');
  }
  document.querySelectorAll('[role="tab"]').forEach(tabButton => {
    tabButton.setAttribute('aria-selected', String(tabButton === el));
  });
  const btnLabel = document.getElementById('headerCreateBtnLabel');
  if (btnLabel) {
    btnLabel.textContent = tab === 'packages' ? 'Create Package' : 'Add Service';
  }
  currentFilter = 'All';
  render();
}

function handleHeaderCreateClick() {
  if (currentTab === 'packages') {
    openPackageModal();
    return;
  }
  openCreateServiceModal();
}

// ── STATUS FILTER ─────────────────────────────────────────────
function setStatusFilter(f) {
  statusFilter = f;
  document.querySelectorAll('.sf-btn').forEach(b => {
    b.classList.remove('bg-custom-primary','text-white');
    b.classList.add('bg-white','text-gray-500');
  });
  const el = document.getElementById('sf-'+f);
  if (el) {
    el.classList.add('bg-custom-primary','text-white');
    el.classList.remove('bg-white','text-gray-500');
  }
  render();
}

// ── CLOSE ALL MODALS ──────────────────────────────────────────
function closeAll() {
  if(currentCropperInstance) { currentCropperInstance.destroy(); currentCropperInstance = null; }
  ['createServiceModal','editServiceModal','editPackageModal','createPackageModal']
    .forEach(id => document.getElementById(id).classList.add('hidden'));
}

function closeServiceTypeModal() { closeCreateServiceModal(); }
function closeTypeModal() { closeCreateServiceModal(); }
function openServiceTypeModal() { openCreateServiceModal(); }

function confirmDeleteModal({ title = 'Delete item?', message = 'This action cannot be undone.', requireType = null } = {}) {
  const modal = document.getElementById('deleteConfirmModal');
  const titleEl = document.getElementById('deleteConfirmTitle');
  const messageEl = document.getElementById('deleteConfirmMessage');
  const cancelBtn = document.getElementById('deleteConfirmCancel');
  const actionBtn = document.getElementById('deleteConfirmAction');
  const typeInput = document.getElementById('deleteConfirmTypeInput');
  const typeWrap = document.getElementById('deleteConfirmTypeWrap');

  if (!modal || !cancelBtn || !actionBtn) {
    return Promise.resolve(false);
  }

  if (titleEl) titleEl.textContent = title;
  if (messageEl) messageEl.textContent = message;
  actionBtn.textContent = title.toLowerCase().includes('package') ? 'Delete package' : 'Delete service';
  if (typeWrap) typeWrap.classList.toggle('hidden', !requireType);
  if (typeInput) { typeInput.value = ''; typeInput.placeholder = requireType ? 'Type "' + requireType + '" to confirm' : ''; }
  actionBtn.disabled = !!requireType;
  actionBtn.classList.toggle('opacity-50', !!requireType);
  modal.classList.remove('hidden');

  // Type-to-confirm handler
  const onTypeInput = requireType ? function(){
    actionBtn.disabled = typeInput.value.trim() !== requireType;
    actionBtn.classList.toggle('opacity-50', actionBtn.disabled);
  } : null;
  if (onTypeInput && typeInput) {
    typeInput.addEventListener('input', onTypeInput);
  }

  return new Promise(resolve => {
    let resolved = false;

    function finish(value) {
      if (resolved) return;
      resolved = true;
      modal.classList.add('hidden');
      cancelBtn.removeEventListener('click', onCancel);
      actionBtn.removeEventListener('click', onConfirm);
      if (typeInput) typeInput.removeEventListener('input', onTypeInput);
      modal.removeEventListener('click', onBackdrop);
      document.removeEventListener('keydown', onKeydown);
      resolve(value);
    }

    function onCancel() { finish(false); }
    function onConfirm() { finish(true); }
    function onBackdrop(event) {
      if (event.target === modal) finish(false);
    }
    function onKeydown(event) {
      if (event.key === 'Escape') finish(false);
    }

    cancelBtn.addEventListener('click', onCancel);
    actionBtn.addEventListener('click', onConfirm);
    modal.addEventListener('click', onBackdrop);
    document.addEventListener('keydown', onKeydown);
    actionBtn.focus();
  });
}

// ── ADD VENUE / OTHERS — deprecated stubs for compat ──────────
function openAddVenue() { openCreateServiceModal(); selectCreateCategory('Venue'); }
function openAddOthers(selectedCategory) { openCreateServiceModal(); if (selectedCategory) selectCreateCategory(selectedCategory); }
async function saveVenue() { await saveCreateService(); }
async function saveOthers() { await saveCreateService(); }

// ── EDIT SERVICE ──────────────────────────────────────────────
function openEditService(id) {
  const item=services.find(s=>s.id===id); if (!item) return;
  editingSvcId=id;
  document.getElementById('esName').value=item.name;
  document.getElementById('esDesc').value=item.desc||'';
  document.getElementById('esPriceMin').value=item.price_min ?? item.price;
  document.getElementById('esPriceMax').value=item.price_max ?? item.price_min ?? item.price;
  document.getElementById('esMinLeadDays').value=item.min_lead_days ?? '';
  document.getElementById('esDefaultStartTime').value=item.default_start_time ?? '';
  document.getElementById('esDefaultEndTime').value=item.default_end_time ?? '';
  document.getElementById('esImgData').value=item.img||'';
  if (item.img) renderConfirmedImage(item.img, item.img, 'esImgInput', 'esImgBox', 'esImgData', 16/9);
  else resetImgBox('esImgBox', true);
  const venueExtras = document.getElementById('esVenueExtras');
  const decoExtras = document.getElementById('esDecorationExtras');
  const rentalExtras = document.getElementById('esRentalExtras');
  const isVenueEdit = item.category === 'Venue';
  const isDecoEdit = item.category === 'Decoration';
  const isRentalEdit = item.category === 'Dress' || item.category === 'Accessories' || item.category === 'Attire';
  venueExtras?.classList.toggle('hidden', !isVenueEdit);
  decoExtras?.classList.toggle('hidden', !isDecoEdit);
  rentalExtras?.classList.toggle('hidden', !isRentalEdit);
  document.getElementById('esServicePriceFields')?.classList.toggle('hidden', isVenueEdit || isDecoEdit || isRentalEdit);
  document.getElementById('esDefaultTimeRow')?.classList.toggle('hidden', isVenueEdit);
  if (isVenueEdit) {
    document.getElementById('esType').value=item.type||'';
    document.getElementById('esVenue').value=item.venue||'';
    document.getElementById('esLocation').value=item.venue_location||'';
    renderVenueRooms('es', item.venue_rooms || []);
  }
  if (isDecoEdit) {
    renderDecorationStyles('es', item.decoration_styles || []);
  }
  if (isRentalEdit) {
    const rp = item.rental_pricing || {};
    document.getElementById('esBorrowPackagePrice').value = rp.borrow_package_price ?? rp.borrow_price ?? '';
    document.getElementById('esBorrowCustomizePrice').value = rp.borrow_customize_price ?? rp.borrow_price ?? '';
    document.getElementById('esReturnDays').value = rp.return_days ?? '';
    document.getElementById('esBuyPackagePrice').value = rp.buy_package_price ?? rp.buy_price ?? '';
    document.getElementById('esBuyCustomizePrice').value = rp.buy_customize_price ?? rp.buy_price ?? '';
    renderAttireItems('es', item.attire_items || []);
  }
  document.getElementById('editServiceModal').classList.remove('hidden');
}
async function updateService() {
  const item=services.find(s=>s.id===editingSvcId); if (!item) return;
  const isVenueUpd = item.category === 'Venue';
  const isDecoUpd = item.category === 'Decoration';
  const isRentalUpd = item.category === 'Dress' || item.category === 'Accessories' || item.category === 'Attire';
  if (!isVenueUpd && !isDecoUpd && !isRentalUpd && !isPriceRangeValid('es')) { showToast('Customize price must be ≥ package price.', 'error'); return; }
  if (!validateDefaultEventTime('es')) return;
  if (isVenueUpd && !validateVenueRooms('es')) return;
  if (isDecoUpd && !validateDecorationStyles('es')) return;
  if (isRentalUpd && !validateAttireItems('es')) return;
  const priceRange = isVenueUpd ? venueRoomPriceRange('es') : (isDecoUpd || isRentalUpd ? { price: 0, price_min: 0, price_max: 0, package_price: 0, customize_price: 0 } : priceRangePayload('es'));
  const minLeadDaysEl = document.getElementById('esMinLeadDays');
  const minLeadDaysValue = minLeadDaysEl ? minLeadDaysEl.value.trim() : '';
  const minLeadDays = minLeadDaysValue === '' ? 0 : Math.max(0, Math.min(365, parseInt(minLeadDaysValue) || 0));
  const payload = {
    name: document.getElementById('esName').value.trim()||item.name,
    desc: document.getElementById('esDesc').value.trim(),
    category: item.category || 'Others',
    status: item.status || 'inactive',
    ...priceRange,
    img: document.getElementById('esImgData').value,
    capacity: isVenueUpd ? venueRoomMaxCapacity('es') : (parseInt(document.getElementById('esCapacity')?.value || item.capacity || '1') || 1),
    type: document.getElementById('esType')?.value || item.type || '',
    venue: document.getElementById('esVenue')?.value.trim() || item.venue || '',
    venue_location: document.getElementById('esLocation')?.value.trim() || item.venue_location || '',
    min_lead_days: minLeadDays,
    default_start_time: document.getElementById('esDefaultStartTime')?.value || null,
    default_end_time: document.getElementById('esDefaultEndTime')?.value || null,
    rooms: isVenueUpd ? collectVenueRooms('es') : [],
    rooms_replace: isVenueUpd,
    decoration_styles: isDecoUpd ? collectDecorationStyles('es') : [],
    rental_pricing: isRentalUpd ? collectRentalPricing('es') : null,
    attire_items: isRentalUpd ? collectAttireItems('es') : [],
    attire_items_replace: isRentalUpd,
  };
  try {
    const result = await apiRequest(serviceManagementUrls.serviceUpdate + editingSvcId, payload);
    upsertItem(services, result.item);
    closeAll(); render();
  } catch (error) { showToast(error.message, 'error'); }
}

async function deleteService(id) {
  const item=services.find(s=>s.id===id); if (!item) return;
  const confirmed = await confirmDeleteModal({
    title: 'Delete service?',
    message: `Delete "${item.name}"? This removes the service from your supplier list. This action cannot be undone.`
  });
  if (!confirmed) return;
  try {
    await apiRequest(serviceManagementUrls.serviceDelete + id);
    services=services.filter(s=>Number(s.id)!==Number(id));
    closeAll();
    render();
    showToast('Service deleted.', 'success');
  } catch (error) { showToast(error.message, 'error'); }
}

function deleteCurrentService() {
  if (editingSvcId===null) return;
  deleteService(editingSvcId);
}

// ── EDIT PACKAGE ──────────────────────────────────────────────
function openEditPackage(id) {
  const item=packages.find(p=>p.id===id); if (!item) return;
  editingPkgId=id;
  document.getElementById('epName').value=item.name;
  document.getElementById('epDesc').value=item.desc||'';
  document.getElementById('epPrice').value=item.price;
  document.getElementById('epImgData').value=item.img||'';
  if (item.img) renderConfirmedImage(item.img, item.img, 'epImgInput', 'epImgBox', 'epImgData', 16/9);
  else resetImgBox('epImgBox', true);
  document.querySelectorAll('.ep-cb').forEach(checkbox=>{
    checkbox.checked=(item.categories||[]).includes(checkbox.value);
  });
  document.getElementById('editPackageModal').classList.remove('hidden');
}
async function updatePackage() {
  const item=packages.find(p=>p.id===editingPkgId); if (!item) return;
  const payload = {
    ...item,
    name: document.getElementById('epName').value.trim()||item.name,
    desc: document.getElementById('epDesc').value.trim(),
    price: parseFloat(document.getElementById('epPrice').value)||item.price,
    img: document.getElementById('epImgData').value,
    categories: Array.from(document.querySelectorAll('.ep-cb:checked')).map(c=>c.value)
  };
  try {
    const result = await apiRequest(serviceManagementUrls.packageUpdate + editingPkgId, payload);
    upsertItem(packages, result.item);
    closeAll(); render();
  } catch (error) { showToast(error.message, 'error'); }
}

async function deletePackage(id) {
  const item=packages.find(p=>p.id===id); if (!item) return;
  const confirmed = await confirmDeleteModal({
    title: 'Delete package?',
    message: `Delete "${item.name}"? This removes the package from your supplier list.`,
    requireType: item.name
  });
  if (!confirmed) return;
  try {
    await apiRequest(serviceManagementUrls.packageDelete + id);
    packages=packages.filter(p=>Number(p.id)!==Number(id));
    closeAll(); render();
  } catch (error) { showToast(error.message, 'error'); }
}

function deleteCurrentPackage() {
  if (editingPkgId===null) return;
  deletePackage(editingPkgId);
}

// ── CREATE PACKAGE ────────────────────────────────────────────
function openPackageModal() {
  ['cpName','cpDesc','cpPrice','cpImgData'].forEach(id=>document.getElementById(id).value='');
  document.querySelectorAll('.cp-cb').forEach(c=>c.checked=false);
  resetImgBox('cpImgBox', true);
  updateCpCount();
  document.getElementById('createPackageModal').classList.remove('hidden');
}
function updateCpCount() {
  const n=document.querySelectorAll('.cp-cb:checked').length;
  document.getElementById('cpCount').textContent=n+' selected';
}
async function savePackage() {
  const name=document.getElementById('cpName').value.trim(), price=parseFloat(document.getElementById('cpPrice').value);
  if (!name||isNaN(price)) { showToast('Please fill in package name and price.', 'error'); return; }
  try {
    const result = await apiRequest(serviceManagementUrls.packageCreate, {
      name,
      price,
      desc: document.getElementById('cpDesc').value.trim(),
      img: document.getElementById('cpImgData').value,
      categories: Array.from(document.querySelectorAll('.cp-cb:checked')).map(c=>c.value),
      status: 'active'
    });
    upsertItem(packages, result.item);
    closeAll(); currentTab='packages'; switchTab('packages');
  } catch (error) { showToast(error.message, 'error'); }
}

// ── TOGGLE STATUS ────────────────────────────────────────────
async function toggleStatus(id, type) {
  const list=type==='service'?services:packages;
  const item=list.find(i=>i.id===id);
  if (!item) return;
  const nextStatus = item.status==='active'?'inactive':'active';
  try {
    if (type === 'service' && nextStatus === 'active') {
      const result = await apiRequest((serviceManagementUrls.servicePublishRequest || serviceManagementUrls.serviceStatus) + id, {});
      showToast(result.message || 'Done', 'success');
      upsertItem(list, { ...item, status: 'inactive' });
      render();
      return;
    }

    const url = type === 'service' ? serviceManagementUrls.serviceStatus : serviceManagementUrls.packageStatus;
    const result = await apiRequest(url + id, { status: nextStatus });
    upsertItem(list, result.item);
    render();
  } catch (error) { showToast(error.message, 'error'); }
}

// ── PHOTO HANDLING EXTENSIONS ────────────────────────────────
let currentCropperInstance = null;

function triggerBrowse(e, inputId) {
  if (e.target.closest('.adjust-overlay-bar') || e.target.closest('.edit-icon-btn')) return;
  if (e.currentTarget && e.currentTarget.dataset.mode === 'adjusting') return;
  e.preventDefault();
  e.stopPropagation();
  const input = document.getElementById(inputId);
  if (!input) return;
  input.value = '';
  input.click();
}

function hasCropperSupport() { return typeof Cropper !== 'undefined'; }

function destroyCurrentCropper() {
  if (currentCropperInstance) {
    currentCropperInstance.destroy();
    currentCropperInstance = null;
  }
}

function renderConfirmedImage(dataURL, sourceImageURL, inputId, boxId, dataId, aspectRatio) {
  const box = document.getElementById(boxId);
  box.removeAttribute('for');
  box.dataset.masterSource = sourceImageURL || dataURL;
  box.dataset.mode = 'confirmed';
  document.getElementById(dataId).value = dataURL;
  box.style.border = 'none';

  const adjustButton = hasCropperSupport() ? `
        <button type="button" 
          onclick="reopenAdjustmentFrameworkFromBox('${inputId}', '${boxId}', '${dataId}', ${aspectRatio})"
          class="p-1.5 hover:bg-white/20 rounded-lg text-white transition flex items-center justify-center" 
          title="Adjust Position">
          <svg class="w-4 h-4 text-stone-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
          </svg>
        </button>
        <div class="h-4 w-px bg-white/20"></div>` : '';

  box.innerHTML = `
    <div class="img-upload-container w-full h-full relative group">
      <img src="${dataURL}" class="w-full h-full object-contain rounded-xl"/>
      <div class="edit-icon-btn absolute top-2 right-2 flex items-center gap-1.5 bg-black/70 backdrop-blur-md p-1 rounded-xl shadow-lg border border-white/10" onclick="event.stopPropagation()">
        ${adjustButton}
        <button type="button" onclick="uploadDifferentPhoto('${inputId}')" class="p-1.5 hover:bg-white/20 rounded-lg text-white transition flex items-center justify-center">
          <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 8H18"/></svg>
        </button>
      </div>
    </div>
  `;
}

function uploadDifferentPhoto(inputId) {
  const input = document.getElementById(inputId);
  input.value = '';
  input.click();
}

function initAdjustmentFramework(inputId, boxId, dataId, aspectRatio) {
  const file = document.getElementById(inputId).files[0];
  if (!file) return;

  const reader = new FileReader();
  reader.onload = e => {
    const box = document.getElementById(boxId);
    box.removeAttribute('for');
    box.dataset.mode = 'adjusting';
    box.style.border = 'none';
    
    destroyCurrentCropper();

    if (!hasCropperSupport()) {
      renderConfirmedImage(e.target.result, e.target.result, inputId, boxId, dataId, aspectRatio);
      return;
    }

    box.innerHTML = `
      <div class="w-full h-full relative bg-black flex items-center justify-center">
        <img id="${boxId}_cropTarget" src="${e.target.result}" class="max-w-full max-h-full block">
        <div class="adjust-overlay-bar absolute top-2 right-2 flex items-center gap-1 bg-black/75 backdrop-blur-md px-2 py-1.5 rounded-xl z-30 shadow-lg border border-white/10" onclick="event.stopPropagation()">
          <button type="button" onclick="cropperAction('move')" class="p-1.5 hover:bg-white/20 rounded-lg text-white text-xs font-medium flex items-center gap-1 transition"><span>Adjust Position</span></button>
          <button type="button" onclick="cropperAction('zoomIn')" class="p-1.5 hover:bg-white/20 rounded-lg text-white transition">+</button>
          <button type="button" onclick="cropperAction('zoomOut')" class="p-1.5 hover:bg-white/20 rounded-lg text-white transition">-</button>
          <button type="button" onclick="finalizeAdjustment('${boxId}_cropTarget', '${dataId}', '${boxId}', '${inputId}', ${aspectRatio})" class="ml-1 px-2.5 py-1 bg-custom-primary text-white rounded-md text-xs font-bold transition shadow-sm">Apply</button>
        </div>
      </div>
    `;

    try {
      currentCropperInstance = new Cropper(document.getElementById(`${boxId}_cropTarget`), {
        aspectRatio: aspectRatio,
        viewMode: 1,
        dragMode: 'crop',
        autoCropArea: 0.85,
        restore: false,
        guides: true,
        center: true,
        highlight: true,
        cropBoxMovable: true,
        cropBoxResizable: true,
        toggleDragModeOnDblclick: true,
        zoomable: true,
        zoomOnWheel: true,
        movable: true,
        responsive: true
      });
    } catch (error) {
      destroyCurrentCropper();
      renderConfirmedImage(e.target.result, e.target.result, inputId, boxId, dataId, aspectRatio);
    }
  };
  reader.readAsDataURL(file);
}

function cropperAction(action) {
  if (!currentCropperInstance) return;
  if (action === 'move') currentCropperInstance.setDragMode('move');
  if (action === 'zoomIn') currentCropperInstance.zoom(0.1);
  if (action === 'zoomOut') currentCropperInstance.zoom(-0.1);
}

function finalizeAdjustment(imgTargetId, dataId, boxId, inputId, aspectRatio) {
  if (!currentCropperInstance) return;
  const canvas = currentCropperInstance.getCroppedCanvas();
  if (!canvas) return;
  const dataURL = canvas.toDataURL('image/jpeg', 0.85);
  const masterSource = currentCropperInstance.element.src || dataURL;
  destroyCurrentCropper();
  renderConfirmedImage(dataURL, masterSource, inputId, boxId, dataId, aspectRatio);
}

function reopenAdjustmentFrameworkFromBox(inputId, boxId, dataId, aspectRatio) {
  const box = document.getElementById(boxId);
  const sourceImageURL = box.dataset.masterSource || document.getElementById(dataId).value;
  if (sourceImageURL) reopenAdjustmentFramework(sourceImageURL, inputId, boxId, dataId, aspectRatio);
}

function reopenAdjustmentFramework(sourceImageURL, inputId, boxId, dataId, aspectRatio) {
  if (!hasCropperSupport()) return;
  destroyCurrentCropper();
  
  const box = document.getElementById(boxId);
  box.removeAttribute('for');
  box.dataset.mode = 'adjusting';

  box.innerHTML = `
    <div class="w-full h-full relative bg-black flex items-center justify-center">
      <img id="${boxId}_cropTarget" src="${sourceImageURL}" class="max-w-full max-h-full block">
      <div class="adjust-overlay-bar absolute top-2 right-2 flex items-center gap-1 bg-black/75 backdrop-blur-md px-2 py-1.5 rounded-xl z-30 shadow-lg border border-white/10" onclick="event.stopPropagation()">
        <button type="button" onclick="cancelAdjustmentFromBox('${dataId}', '${boxId}', '${inputId}', ${aspectRatio})" class="px-2.5 py-1 bg-gray-600 text-white rounded-md text-xs font-medium transition">Cancel</button>
        <button type="button" onclick="finalizeAdjustment('${boxId}_cropTarget', '${dataId}', '${boxId}', '${inputId}', ${aspectRatio})" class="px-2.5 py-1 bg-custom-primary text-white rounded-md text-xs font-bold transition shadow-sm">Apply</button>
      </div>
    </div>
  `;

  try {
    currentCropperInstance = new Cropper(document.getElementById(`${boxId}_cropTarget`), {
      aspectRatio: aspectRatio,
      viewMode: 1,
      dragMode: 'crop',
      autoCropArea: 0.85,
      restore: false,
      guides: true,
      center: true,
      highlight: true,
      cropBoxMovable: true,
      cropBoxResizable: true,
      toggleDragModeOnDblclick: true,
      zoomable: true,
      zoomOnWheel: true,
      movable: true,
      responsive: true
    });
  } catch (error) {
    destroyCurrentCropper();
    renderConfirmedImage(sourceImageURL, sourceImageURL, inputId, boxId, dataId, aspectRatio);
  }
}

function cancelAdjustmentFromBox(dataId, boxId, inputId, aspectRatio) {
  const box = document.getElementById(boxId);
  destroyCurrentCropper();
  const savedData = document.getElementById(dataId).value;
  renderConfirmedImage(savedData, box.dataset.masterSource || savedData, inputId, boxId, dataId, aspectRatio);
}

function resetImgBox(boxId, wide) {
  const box = document.getElementById(boxId);
  box.style.border = '';
  delete box.dataset.masterSource;
  box.dataset.mode = 'empty';
  const inputMap = { csImgBox: 'csImgInput', venueImgBox: 'venueImgInput', othersImgBox: 'othersImgInput', esImgBox: 'esImgInput', epImgBox: 'epImgInput', cpImgBox: 'cpImgInput' };
  box.setAttribute('for', inputMap[boxId]);

  if (wide) {
    box.innerHTML = `<div class="img-zone-icon">+</div><span class="img-zone-label">Add a photo</span>`;
  }
}

// ── cb-card toggle on label click ──
document.addEventListener('change', function(e) {
  if (e.target.matches('.cb-card input[type="checkbox"]')) {
    e.target.closest('.cb-card')?.classList.toggle('checked', e.target.checked);
  }
});

// Close when overlay is clicked
['createServiceModal','editServiceModal','editPackageModal','createPackageModal'].forEach(function(id){
  document.getElementById(id).addEventListener('click', function(e){ if(e.target===this) closeAll(); });
});

// ── Search input binding ───────────────────────────────────────
const searchEl = document.getElementById('searchInput');
if (searchEl) {
  searchEl.addEventListener('input', onSearchInput);
  // Clear search on Escape
  searchEl.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      searchEl.value = '';
      searchQuery = '';
      services = [];
      packages = [];
      pagingMeta = {};
      renderEmpty();
      loadMoreCurrentTab();
    }
  });
}

installNonNegativeNumberGuards();
renderCategoryControls();
if (!PACKAGES_AVAILABLE) {
  const packageTab = document.getElementById('tab-packages');
  if (packageTab) packageTab.hidden = true;
}
setViewMode(viewMode);
switchTab(currentTab);
