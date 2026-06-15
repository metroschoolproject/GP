// ── DATA ──────────────────────────────────────────────────────
const BADGE = { Venue:'badge-venue', Accessories:'badge-decor', Dress:'badge-makeup', Food:'badge-catering', Package:'badge-others', Studio:'badge-photo', Makeup:'badge-makeup', Photography:'badge-photo', Catering:'badge-catering', Decor:'badge-decor', Music:'badge-music', Others:'badge-others' };
const GRAD  = { Venue:'from-amber-50 to-orange-50', Accessories:'from-yellow-50 to-amber-50', Dress:'from-rose-50 to-orange-50', Food:'from-stone-50 to-emerald-50', Package:'from-stone-50 to-zinc-50', Studio:'from-blue-50 to-stone-50', Makeup:'from-rose-50 to-orange-50', Photography:'from-blue-50 to-stone-50', Catering:'from-stone-50 to-emerald-50', Decor:'from-yellow-50 to-amber-50', Music:'from-red-50 to-stone-50', Others:'from-stone-50 to-zinc-50' };
const ICON  = { Venue:'🏛️', Accessories:'✨', Dress:'👗', Food:'🍽️', Package:'🎀', Studio:'📸', Makeup:'💄', Photography:'📸', Catering:'🍽️', Decor:'🌸', Music:'🎵', Others:'✨' };

const serviceManagementConfig = window.serviceManagementConfig || {};
const serviceManagementUrls = serviceManagementConfig.urls || {};
const PAGE_SIZE = Number(serviceManagementConfig.pageSize || 24);
const INITIAL_TAB = serviceManagementConfig.initialTab === 'packages' ? 'packages' : 'services';

let currentTab = INITIAL_TAB, currentFilter = 'All', statusFilter = 'all', nextId = 200;
let editingSvcId = null, editingPkgId = null;

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
  serviceCategories = ['Accessories', 'Dress', 'Food', 'Package', 'Studio', 'Venue'];
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
  const othersSelect = document.getElementById('oCategory');
  const epCategoryList = document.getElementById('epCategoryList');
  const cpCategoryList = document.getElementById('cpCategoryList');
  const nonVenueCategories = serviceCategories.filter(category => category !== 'Venue');
  const selectableCategories = nonVenueCategories.length ? nonVenueCategories : serviceCategories;

  if (othersSelect) {
    othersSelect.innerHTML = selectableCategories.map(categoryOptionHtml).join('');
  }

  if (epCategoryList) {
    epCategoryList.innerHTML = serviceCategories.map(category => packageCheckboxHtml(category, 'ep-cb')).join('');
  }

  if (cpCategoryList) {
    cpCategoryList.innerHTML = serviceCategories.map(category => packageCheckboxHtml(category, 'cp-cb')).join('');
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
    throw new Error('Server returned an invalid response. Please check the PHP error shown on the page or server log.');
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

function venueRoomRowHtml(prefix, room = {}) {
  const id = Number(room.id || 0);
  const name = escapeHtml(room.name || '');
  const capacity = room.capacity ?? '';
  const priceMin = room.price_min ?? room.package_price ?? room.price ?? '';
  const priceMax = room.price_max ?? room.customize_price ?? priceMin;
  const startTime = escapeHtml(String(room.start_time || room.available_start_time || '09:00').slice(0, 5));
  const endTime = escapeHtml(String(room.end_time || room.available_end_time || '17:00').slice(0, 5));
  const minLeadDays = room.min_lead_days ?? '';

  return `
    <div class="room-row ${prefix}-venue-room" data-room-id="${id}">
      <input type="hidden" class="room-id" value="${id || ''}">
      <div class="room-row-inner">
        <div>
          <label class="fm-label">Room name</label>
          <input type="text" value="${name}" placeholder="e.g. Grand Hall" class="room-name fm-input" style="font-size:13px;padding:6px 0 4px"/>
        </div>
        <div>
          <label class="fm-label">Capacity</label>
          <input type="number" min="1" value="${capacity}" placeholder="300" class="room-capacity fm-input" style="font-size:13px;padding:6px 0 4px"/>
        </div>
        <div>
          <label class="fm-label">Package price</label>
          <input type="number" min="0" step="0.01" value="${priceMin}" placeholder="3500" class="room-price room-price-min fm-input" style="font-size:13px;padding:6px 0 4px"/>
        </div>
        <div>
          <label class="fm-label">Customize price</label>
          <input type="number" min="0" step="0.01" value="${priceMax}" placeholder="6000" class="room-price-max fm-input" style="font-size:13px;padding:6px 0 4px"/>
        </div>
        <div>
          <label class="fm-label">Start time</label>
          <input type="time" lang="en-GB" value="${startTime}" class="room-start-time fm-input" style="font-size:13px;padding:6px 0 4px"/>
        </div>
        <div>
          <label class="fm-label">End time</label>
          <input type="time" lang="en-GB" value="${endTime}" class="room-end-time fm-input" style="font-size:13px;padding:6px 0 4px"/>
        </div>
        <div>
          <label class="fm-label">Min. notice (days)</label>
          <input type="number" min="0" max="365" value="${minLeadDays}" placeholder="Leave blank to use service default" class="room-min-lead-days fm-input" style="font-size:13px;padding:6px 0 4px"/>
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
      min_lead_days: minLeadDays
    };
  }).filter(room => room.name || room.capacity > 1 || room.price_min > 0 || room.price_max > 0);
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
    alert('Room end time must be later than the start time.');
    return false;
  }

  const invalidPriceRow = rows.find(row => {
    const min = parseFloat(row.querySelector('.room-price-min')?.value || '0');
    const max = parseFloat(row.querySelector('.room-price-max')?.value || row.querySelector('.room-price-min')?.value || '0');
    return Number.isFinite(min) && Number.isFinite(max) && max < min;
  });

  if (invalidPriceRow) {
    alert('Room customize price must be greater than or equal to package price.');
    return false;
  }

  const rooms = collectVenueRooms(prefix);
  if (!rooms.length) {
    alert('Please add at least one room or hall.');
    return false;
  }

  const missingPriceRoom = rooms.find(room => room.price_min <= 0 || room.price_max <= 0);
  if (missingPriceRoom) {
    alert('Please fill in package price and customize price for every room.');
    return false;
  }

  return true;
}

function serviceFormPayload(prefix, category) {
  const isVenue = category === 'Venue';
  const rooms = isVenue ? collectVenueRooms(prefix) : [];
  const priceRange = isVenue ? venueRoomPriceRange(prefix) : priceRangePayload(prefix);
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
    rooms
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

// ── SERVICE TYPE SELECTOR ──────────────────────────────────────
function openTypeSelector() {
  const modal = document.getElementById('serviceTypeModal');
  const container = document.getElementById('serviceTypeOptions');

  const TYPE_CHIPS = {
    Venue:      { emoji: '🏛️', desc: 'Ballrooms, gardens, halls' },
    Catering:   { emoji: '🍽️', desc: 'Food, beverages, service' },
    Photography:{ emoji: '📸', desc: 'Photo, video, albums' },
    Makeup:     { emoji: '💄', desc: 'Bridal, hair, styling' },
    Decor:      { emoji: '🌸', desc: 'Florals, lighting, setup' },
    Music:      { emoji: '🎵', desc: 'Band, DJ, playlist' },
    Dress:      { emoji: '👗', desc: 'Gowns, suits, accessories' },
    Studio:     { emoji: '📸', desc: 'Pre-wedding, portraits' },
    Others:     { emoji: '✨', desc: 'Any other wedding service' },
  };

  const categories = ['Venue', ...serviceCategories.filter(c => c !== 'Venue').sort()];

  container.innerHTML = categories.map(cat => {
    const chip = TYPE_CHIPS[cat] || { emoji: '✨', desc: '' };
    return `
    <div class="type-chip" data-cat="${cat}" onclick="handleTypeSelect(this)">
      <span class="type-chip-emoji">${chip.emoji}</span>
      <div>
        <div class="type-chip-text">${cat}</div>
        <div class="type-chip-desc">${chip.desc}</div>
      </div>
    </div>`;
  }).join('');

  modal.classList.remove('hidden');
}

function handleTypeSelect(el) {
  document.querySelectorAll('#serviceTypeOptions .type-chip').forEach(i => i.classList.remove('selected'));
  el.classList.add('selected');

  const cat = el.dataset.cat;
  closeTypeModal();
  cat === 'Venue' ? openAddVenue() : openAddOthers(cat);
}
function closeTypeModal() {
  document.getElementById('serviceTypeModal').classList.add('hidden');
}

// ── RENDER ────────────────────────────────────────────────────
function render() {
  const grid  = document.getElementById('cardsGrid');
  const empty = document.getElementById('emptyState');
  const emptyMessage = document.getElementById('emptyStateMessage');
  const emptyActionLabel = document.getElementById('emptyStateActionLabel');
  let items = currentTab === 'services' ? services : packages;

  items = items.filter(Boolean).filter(i => {
    const catOk = currentTab === 'services'
      ? (currentFilter === 'All' || i.category === currentFilter)
      : (currentFilter === 'All' || (i.categories||[]).includes(currentFilter));
    const statusOk = statusFilter === 'all' || i.status === statusFilter;
    return catOk && statusOk;
  });

  if (!grid || !empty) return;

  grid.innerHTML = '';
  empty.classList.add('hidden');

  // Always render the plus add card at the start if on services tab
  if (currentTab === 'services') {
    grid.insertAdjacentHTML('beforeend', plusCard());
  }

  if (!items.length && currentTab === 'packages') {
    if (emptyMessage) {
      emptyMessage.textContent = 'How about creating a package right now?';
    }
    if (emptyActionLabel) {
      emptyActionLabel.textContent = 'Create Package';
    }
    empty.classList.remove('hidden');
    updateLoadMoreControl();
    return;
  }

  items.forEach(item => grid.insertAdjacentHTML('beforeend', currentTab === 'services' ? svcCard(item) : pkgCard(item)));
  updateLoadMoreControl();
}

function plusCard() {
  return `
  <div onclick="openTypeSelector()" class="service-card rounded-xl border-2 border-dashed border-gray-300 overflow-hidden cursor-pointer transition hover:border-[#6e4e58] hover:bg-[#fbf9f6] flex items-center justify-center" style="box-shadow:0 1px 4px rgba(74,59,50,0.05); background: rgba(255,255,255,0.5); min-height:300px;">
    <div class="flex flex-col items-center justify-center gap-3 p-6">
      <div class="w-16 h-16 rounded-full border-2 border-dashed border-gray-300 flex items-center justify-center transition group-hover:border-[#6e4e58]">
        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="12" y1="5" x2="12" y2="19"/>
          <line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
      </div>
      <span class="text-sm font-semibold text-gray-400">Add Service</span>
    </div>
  </div>`;
}

document.getElementById('emptyStateAction')?.addEventListener('click', () => {
  if (currentTab === 'packages') {
    openPackageModal();
    return;
  }

  openTypeSelector();
});

function updateLoadMoreControl() {
  const wrap = document.getElementById('loadMoreWrap');
  const button = document.getElementById('loadMoreBtn');
  if (!wrap || !button) return;

  const meta = pagingMeta[currentTab] || {};
  const currentCount = currentTab === 'services' ? services.length : packages.length;
  const hasMore = Boolean(meta.has_more) || (Number(meta.total || 0) > currentCount);

  wrap.classList.toggle('hidden', !hasMore);
  button.disabled = isLoadingMore;
  button.textContent = isLoadingMore ? 'Loading...' : 'Load more';
}

async function loadMoreCurrentTab() {
  if (isLoadingMore) return;

  const list = currentTab === 'services' ? services : packages;
  isLoadingMore = true;
  updateLoadMoreControl();

  try {
    const result = await apiRequest(serviceManagementUrls.data, {
      tab: currentTab,
      limit: PAGE_SIZE,
      offset: list.length
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
    alert(error.message);
  } finally {
    isLoadingMore = false;
    updateLoadMoreControl();
  }
}

function svcCard(item) {
  item = normalizeServiceItem(item);
  if (!item) return '';
  const grad = GRAD[item.category]||'from-stone-100 to-stone-200';
  const icon = ICON[item.category]||'✨';
  const isActive = item.status === 'active';
  const imgInner = item.img
    ? `<img src="${item.img}" alt="${item.name}"/>`
    : `<div class="card-icon"><span style="font-size:2.8rem;line-height:1">${icon}</span></div>`;
  return `
  <div class="service-card rounded-xl border border-gray-100 overflow-hidden" style="box-shadow:0 1px 4px rgba(74,59,50,0.05)">
    <div class="card-img-wrap bg-gradient-to-br ${grad}">
      ${imgInner}
    </div>
    <div class="card-img-overlay"></div>
    <!-- Edit icon top-left -->
    <button onclick='openEditService(${item.id})' class="btn-card btn-edit absolute top-2.5 left-2.5 z-10 p-1.5 rounded-lg bg-black/30 backdrop-blur-sm border border-white/20 text-white hover:bg-black/50 transition">
      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
    </button>
    <button onclick='deleteService(${item.id})' class="btn-card btn-delete absolute top-2.5 left-11 z-10 p-1.5 rounded-lg bg-black/30 backdrop-blur-sm border border-white/20 text-white hover:bg-rose-50 transition" title="Delete service">
      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
    </button>
    <!-- Status pill top-right -->
    <span class="status-pill ${isActive?'active-pill':'inactive-pill'} absolute top-2.5 right-2.5 z-10">
      <span class="status-dot ${isActive?'dot-green':'dot-gray'}"></span>
      ${isActive?'Active':'Inactive'}
    </span>
    <div class="card-content p-4 flex flex-col gap-2.5 justify-end">
      <div class="flex-1 flex flex-col justify-end">
        <div class="flex items-center justify-between mb-1.5">
          <span class="text-xs font-semibold px-2 py-0.5 rounded-md ${BADGE[item.category]||''}">${item.category}</span>
        </div>
        <h3 class="font-semibold text-gray-800 text-sm leading-snug">${item.name}</h3>
        <p class="text-custom-primary font-bold text-sm mt-0.5">MMK ${formatPriceRange(item)}</p>
        ${item.desc?`<p class="text-gray-400 text-xs mt-1.5 line-clamp-2 leading-relaxed">${item.desc}</p>`:''}
      </div>
      <div class="pt-2.5 border-t border-gray-100">
        <a href="${serviceDetailUrl(item.id)}"
          class="btn-card flex w-full items-center justify-center gap-2 py-1.5 rounded-lg text-xs font-semibold bg-white/90 text-gray-800 border border-white/30 hover:bg-white">
          Manage details
        </a>
      </div>
    </div>
  </div>`;
}

function pkgCard(item) {
  item = normalizePackageItem(item);
  if (!item) return '';
  const cats = item.categories||[];
  const grad = cats.length ? (GRAD[cats[0]]||'from-stone-50 to-stone-100') : 'from-stone-50 to-stone-100';
  const icons = cats.map(c=>ICON[c]||'🎀').join(' ');
  const isActive = item.status === 'active';
  const badges = cats.map(c=>`<span class="text-xs font-semibold px-2 py-0.5 rounded-md ${BADGE[c]||''}">${c}</span>`).join('');
  const imgInner = item.img
    ? `<img src="${item.img}" alt="${item.name}"/>`
    : `<div class="card-icon"><span style="font-size:2.4rem;line-height:1;letter-spacing:0.1em">${icons||'🎀'}</span></div>`;
  return `
  <div class="service-card rounded-xl border border-gray-100 overflow-hidden" style="box-shadow:0 1px 4px rgba(74,59,50,0.05)">
    <div class="card-img-wrap bg-gradient-to-br ${grad}">
      ${imgInner}
    </div>
    <div class="card-img-overlay"></div>
    <!-- Edit icon top-left -->
    <button onclick='openEditPackage(${item.id})' class="btn-card btn-edit absolute top-2.5 left-2.5 z-10 p-1.5 rounded-lg bg-black/30 backdrop-blur-sm border border-white/20 text-white hover:bg-black/50 transition">
      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
    </button>
    <button onclick='deletePackage(${item.id})' class="btn-card btn-delete absolute top-2.5 left-11 z-10 p-1.5 rounded-lg bg-black/30 backdrop-blur-sm border border-white/20 text-white hover:bg-rose-50 transition" title="Delete package">
      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
    </button>
    <!-- Status pill top-right -->
    <span class="status-pill ${isActive?'active-pill':'inactive-pill'} absolute top-2.5 right-2.5 z-10">
      <span class="status-dot ${isActive?'dot-green':'dot-gray'}"></span>
      ${isActive?'Active':'Inactive'}
    </span>
    <div class="card-content p-4 flex flex-col gap-2.5 justify-end">
      <div class="flex-1 flex flex-col justify-end">
        <div class="mb-1.5">
          <span class="text-xs font-semibold px-2 py-0.5 rounded-md bg-stone-100 text-stone-700">Package</span>
        </div>
        <h3 class="font-semibold text-gray-800 text-sm leading-snug">${item.name}</h3>
        <p class="text-custom-primary font-bold text-sm mt-0.5">MMK ${formatPrice(item.price)}</p>
        ${item.desc?`<p class="text-gray-400 text-xs mt-1.5 line-clamp-2 leading-relaxed">${item.desc}</p>`:''}
        <div class="flex flex-wrap gap-1 mt-2">${badges}</div>
      </div>
      <div class="pt-2.5 border-t border-gray-100">
        <button type="button"
          class="btn-card w-full py-1.5 rounded-lg text-xs font-semibold bg-white/90 text-gray-800 border border-white/30">
          Package details
        </button>
      </div>
    </div>
  </div>`;
}

// ── TABS ──────────────────────────────────────────────────────
function switchTab(tab) {
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
  openServiceTypeModal();
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
  ['venueModal','othersModal','editServiceModal','editPackageModal','createPackageModal','serviceTypeModal']
    .forEach(id => document.getElementById(id).classList.add('hidden'));
}

function closeServiceTypeModal() {
  document.getElementById('serviceTypeModal').classList.add('hidden');
}

function openServiceTypeModal() {
  document.getElementById('serviceTypeModal').classList.remove('hidden');
}

function openVenueTypeModal() {
  document.getElementById('serviceTypeModal').classList.add('hidden');
  clearFieldValues(['vName','vDesc','vPriceMin','vPriceMax','vCapacity','vVenue','vLocation','vType','vImgData']);
  resetImgBox('venueImgBox', true);
  document.getElementById('venueModal').classList.remove('hidden');
}

function openOtherTypeModal() {
  document.getElementById('serviceTypeModal').classList.add('hidden');
  openAddOthers();
}

function confirmDeleteModal({ title = 'Delete item?', message = 'This action cannot be undone.' } = {}) {
  const modal = document.getElementById('deleteConfirmModal');
  const titleEl = document.getElementById('deleteConfirmTitle');
  const messageEl = document.getElementById('deleteConfirmMessage');
  const cancelBtn = document.getElementById('deleteConfirmCancel');
  const actionBtn = document.getElementById('deleteConfirmAction');

  if (!modal || !cancelBtn || !actionBtn) {
    return Promise.resolve(false);
  }

  if (titleEl) titleEl.textContent = title;
  if (messageEl) messageEl.textContent = message;
  modal.classList.remove('hidden');

  return new Promise(resolve => {
    let resolved = false;

    function finish(value) {
      if (resolved) return;
      resolved = true;
      modal.classList.add('hidden');
      cancelBtn.removeEventListener('click', onCancel);
      actionBtn.removeEventListener('click', onConfirm);
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

// ── ADD VENUE ─────────────────────────────────────────────────
function openAddVenue() {
  clearFieldValues(['vName','vDesc','vVenue','vLocation','vImgData']);
  const minLeadField = document.getElementById('vMinLeadDays');
  if (minLeadField) minLeadField.value = '0';
  document.getElementById('vType').value='';
  renderVenueRooms('v');
  resetImgBox('venueImgBox', true);
  document.getElementById('venueModal').classList.remove('hidden');
}
async function saveVenue() {
  const name=document.getElementById('vName').value.trim();
  if (!name) { alert('Please fill in service name.'); return; }
  if (!validateVenueRooms('v')) return;
  try {
    const result = await apiRequest(serviceManagementUrls.serviceCreate, serviceFormPayload('v', 'Venue'));
    upsertItem(services, result.item);
    closeAll(); currentTab='services'; switchTab('services');
  } catch (error) { alert(error.message); }
}

// ── ADD OTHERS ────────────────────────────────────────────────
function openAddOthers(selectedCategory) {
  clearFieldValues(['oName','oDesc','oPriceMin','oPriceMax','oCapacity','oImgData']);
  resetImgBox('othersImgBox', true);
  // Pre-select the category if provided
  if (selectedCategory) {
    const select = document.getElementById('oCategory');
    if (select) {
      const option = Array.from(select.options).find(o => o.value === selectedCategory);
      if (option) select.value = selectedCategory;
    }
  }
  document.getElementById('othersModal').classList.remove('hidden');
}
async function saveOthers() {
  const name=document.getElementById('oName').value.trim(), priceMin=parseFloat(document.getElementById('oPriceMin').value), priceMax=parseFloat(document.getElementById('oPriceMax').value || document.getElementById('oPriceMin').value);
  if (!name||isNaN(priceMin)||isNaN(priceMax)) { alert('Please fill in service name, package price, and customize price.'); return; }
  if (priceMax < priceMin) { alert('Customize price must be greater than or equal to package price.'); return; }
  try {
    const result = await apiRequest(serviceManagementUrls.serviceCreate, serviceFormPayload('o', document.getElementById('oCategory').value));
    upsertItem(services, result.item);
    closeAll(); currentTab='services'; switchTab('services');
  } catch (error) { alert(error.message); }
}

// ── EDIT SERVICE ──────────────────────────────────────────────
function openEditService(id) {
  const item=services.find(s=>s.id===id); if (!item) return;
  editingSvcId=id;
  document.getElementById('esName').value=item.name;
  document.getElementById('esDesc').value=item.desc||'';
  document.getElementById('esPriceMin').value=item.price_min ?? item.price;
  document.getElementById('esPriceMax').value=item.price_max ?? item.price_min ?? item.price;
  document.getElementById('esMinLeadDays').value=item.min_lead_days ?? '';
  document.getElementById('esImgData').value=item.img||'';
  if (item.img) renderConfirmedImage(item.img, item.img, 'esImgInput', 'esImgBox', 'esImgData', 16/9);
  else resetImgBox('esImgBox', true);
  const extras=document.getElementById('esVenueExtras');
  if (item.category==='Venue') {
    extras.classList.remove('hidden');
    document.getElementById('esType').value=item.type||'';
    document.getElementById('esVenue').value=item.venue||'';
    document.getElementById('esLocation').value=item.venue_location||'';
    document.getElementById('esServicePriceFields')?.classList.add('hidden');
    renderVenueRooms('es', item.venue_rooms || []);
  } else extras.classList.add('hidden');
  if (item.category!=='Venue') document.getElementById('esServicePriceFields')?.classList.remove('hidden');
  document.getElementById('editServiceModal').classList.remove('hidden');
}
async function updateService() {
  const item=services.find(s=>s.id===editingSvcId); if (!item) return;
  if (item.category !== 'Venue' && !isPriceRangeValid('es')) { alert('Customize price must be greater than or equal to package price.'); return; }
  if (item.category === 'Venue' && !validateVenueRooms('es')) return;
  const priceRange = item.category === 'Venue' ? venueRoomPriceRange('es') : priceRangePayload('es');
  const minLeadDaysEl = document.getElementById('esMinLeadDays');
  const minLeadDaysValue = minLeadDaysEl ? minLeadDaysEl.value.trim() : '';
  const minLeadDays = minLeadDaysValue === '' ? 0 : Math.max(0, Math.min(365, parseInt(minLeadDaysValue) || 0));
  const payload = {
    ...item,
    name: document.getElementById('esName').value.trim()||item.name,
    desc: document.getElementById('esDesc').value.trim(),
    ...priceRange,
    img: document.getElementById('esImgData').value,
    capacity: item.category === 'Venue' ? venueRoomMaxCapacity('es') : (parseInt(document.getElementById('esCapacity')?.value || item.capacity || '1') || 1),
    type: document.getElementById('esType')?.value || item.type || '',
    venue: document.getElementById('esVenue')?.value.trim() || item.venue || '',
    venue_location: document.getElementById('esLocation')?.value.trim() || item.venue_location || '',
    min_lead_days: minLeadDays,
    rooms: item.category === 'Venue' ? collectVenueRooms('es') : [],
    rooms_replace: item.category === 'Venue'
  };
  try {
    const result = await apiRequest(serviceManagementUrls.serviceUpdate + editingSvcId, payload);
    upsertItem(services, result.item);
    closeAll(); render();
  } catch (error) { alert(error.message); }
}

async function deleteService(id) {
  const item=services.find(s=>s.id===id); if (!item) return;
  const confirmed = await confirmDeleteModal({
    title: 'Delete service?',
    message: `Delete "${item.name}"? This removes the service from your supplier list.`
  });
  if (!confirmed) return;
  try {
    await apiRequest(serviceManagementUrls.serviceDelete + id);
    services=services.filter(s=>Number(s.id)!==Number(id));
    closeAll(); render();
  } catch (error) { alert(error.message); }
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
  } catch (error) { alert(error.message); }
}

async function deletePackage(id) {
  const item=packages.find(p=>p.id===id); if (!item) return;
  const confirmed = await confirmDeleteModal({
    title: 'Delete package?',
    message: `Delete "${item.name}"? This removes the package from your supplier list.`
  });
  if (!confirmed) return;
  try {
    await apiRequest(serviceManagementUrls.packageDelete + id);
    packages=packages.filter(p=>Number(p.id)!==Number(id));
    closeAll(); render();
  } catch (error) { alert(error.message); }
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
  if (!name||isNaN(price)) { alert('Please fill in package name and price.'); return; }
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
  } catch (error) { alert(error.message); }
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
      alert(result.message || 'Publish request sent to admin.');
      upsertItem(list, { ...item, status: 'inactive' });
      render();
      return;
    }

    const url = type === 'service' ? serviceManagementUrls.serviceStatus : serviceManagementUrls.packageStatus;
    const result = await apiRequest(url + id, { status: nextStatus });
    upsertItem(list, result.item);
    render();
  } catch (error) { alert(error.message); }
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
      <img src="${dataURL}" class="w-full h-full object-cover rounded-xl"/>
      <div class="edit-icon-btn absolute top-2 right-2 flex items-center gap-1.5 bg-black/70 backdrop-blur-md p-1 rounded-xl shadow-lg border border-white/10 transition-opacity duration-200 md:opacity-0" onclick="event.stopPropagation()">
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
        aspectRatio: aspectRatio, viewMode: 1, dragMode: 'move', autoCropArea: 1, restore: false, guides: false, center: true, highlight: false, cropBoxMovable: false, cropBoxResizable: false, toggleDragModeOnDblclick: false
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
  const canvas = currentCropperInstance.getCroppedCanvas({ width: 640, height: 360 });
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
      aspectRatio: aspectRatio, viewMode: 1, dragMode: 'move', autoCropArea: 1, restore: false, guides: false, center: true, highlight: false, cropBoxMovable: false, cropBoxResizable: false, toggleDragModeOnDblclick: false
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
  const inputMap = { venueImgBox: 'venueImgInput', othersImgBox: 'othersImgInput', esImgBox: 'esImgInput', epImgBox: 'epImgInput', cpImgBox: 'cpImgInput' };
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
['venueModal','othersModal','editServiceModal','editPackageModal','createPackageModal','serviceTypeModal'].forEach(id=>{
  document.getElementById(id).addEventListener('click', function(e){ if(e.target===this) closeAll(); });
});

installNonNegativeNumberGuards();
renderCategoryControls();
switchTab(currentTab);
