// ── DATA ──────────────────────────────────────────────────────
const BADGE = { Venue:'badge-venue', Accessories:'badge-decor', Dress:'badge-makeup', Food:'badge-catering', Package:'badge-others', Studio:'badge-photo', Makeup:'badge-makeup', Photography:'badge-photo', Catering:'badge-catering', Decor:'badge-decor', Music:'badge-music', Others:'badge-others' };
const GRAD  = { Venue:'from-amber-50 to-orange-50', Accessories:'from-yellow-50 to-amber-50', Dress:'from-rose-50 to-orange-50', Food:'from-stone-50 to-emerald-50', Package:'from-stone-50 to-zinc-50', Studio:'from-blue-50 to-stone-50', Makeup:'from-rose-50 to-orange-50', Photography:'from-blue-50 to-stone-50', Catering:'from-stone-50 to-emerald-50', Decor:'from-yellow-50 to-amber-50', Music:'from-red-50 to-stone-50', Others:'from-stone-50 to-zinc-50' };
const ICON  = { Venue:'🏛️', Accessories:'✨', Dress:'👗', Food:'🍽️', Package:'🎀', Studio:'📸', Makeup:'💄', Photography:'📸', Catering:'🍽️', Decor:'🌸', Music:'🎵', Others:'✨' };

const serviceManagementConfig = window.serviceManagementConfig || {};
const serviceManagementUrls = serviceManagementConfig.urls || {};
const PAGE_SIZE = Number(serviceManagementConfig.pageSize || 24);

let currentTab = 'services', currentFilter = 'All', statusFilter = 'all', nextId = 200;
let editingSvcId = null, editingPkgId = null;

function normalizeServiceItem(item) {
  if (!item || typeof item !== 'object') return null;

  return {
    ...item,
    id: Number(item.id),
    name: item.name || 'Untitled Service',
    price: Number(item.price || 0),
    category: item.category || 'Others',
    status: item.status === 'inactive' ? 'inactive' : 'active',
    desc: item.desc || item.description || '',
    img: item.img || item.thumbnail_url || '',
    capacity: Number(item.capacity || 1)
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
  return `<label class="flex items-center gap-1.5 text-sm cursor-pointer"><input type="checkbox" value="${safeCategory}" class="accent-neutral-700 ${className}" onchange="updateCpCount()"/> ${safeCategory}</label>`;
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

function serviceFormPayload(prefix, category) {
  return {
    name: document.getElementById(prefix + 'Name').value.trim(),
    desc: document.getElementById(prefix + 'Desc').value.trim(),
    price: document.getElementById(prefix + 'Price').value,
    category,
    status: 'active',
    img: document.getElementById(prefix + 'ImgData').value,
    capacity: parseInt(document.getElementById(prefix + 'Capacity')?.value || '1') || 1,
    type: document.getElementById(prefix + 'Type')?.value || '',
    timeslot: document.getElementById(prefix + 'TimeSlot')?.value.trim() || '',
    venue: document.getElementById(prefix + 'Venue')?.value.trim() || ''
  };
}

function formatPrice(value) {
  const amount = Number(value);
  return Number.isFinite(amount) ? amount.toLocaleString() : '0';
}

function serviceDetailUrl(id) {
  return (serviceManagementUrls.serviceDetail || '#') + encodeURIComponent(id);
}

// ── RENDER ────────────────────────────────────────────────────
function render() {
  const grid  = document.getElementById('cardsGrid');
  const empty = document.getElementById('emptyState');
  let items = currentTab === 'services' ? services : packages;

  items = items.filter(Boolean).filter(i => {
    const catOk = currentTab === 'services'
      ? (currentFilter === 'All' || i.category === currentFilter)
      : (currentFilter === 'All' || (i.categories||[]).includes(currentFilter));
    const statusOk = statusFilter === 'all' || i.status === statusFilter;
    return catOk && statusOk;
  });

  grid.innerHTML = '';
  if (!items.length) {
    empty.classList.remove('hidden');
    updateLoadMoreControl();
    return;
  }
  empty.classList.add('hidden');
  items.forEach(item => grid.insertAdjacentHTML('beforeend', currentTab === 'services' ? svcCard(item) : pkgCard(item)));
  updateLoadMoreControl();
}

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
        <p class="text-custom-primary font-bold text-sm mt-0.5">RM ${formatPrice(item.price)}</p>
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
        <p class="text-custom-primary font-bold text-sm mt-0.5">RM ${formatPrice(item.price)}</p>
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
  el.classList.add('active','text-gray-800','border-gray-800');
  el.classList.remove('text-gray-400','border-transparent');
  currentFilter = 'All';
  render();
}

// ── STATUS FILTER ─────────────────────────────────────────────
function setStatusFilter(f) {
  statusFilter = f;
  document.querySelectorAll('.sf-btn').forEach(b => {
    b.classList.remove('bg-custom-primary','text-white');
    b.classList.add('bg-white','border','border-gray-200','text-gray-500');
  });
  const el = document.getElementById('sf-'+f);
  el.classList.add('bg-custom-primary','text-white');
  el.classList.remove('bg-white','border','border-gray-200','text-gray-500');
  render();
}

// ── CLOSE ALL MODALS ──────────────────────────────────────────
function closeAll() {
  if(currentCropperInstance) { currentCropperInstance.destroy(); currentCropperInstance = null; }
  ['venueModal','othersModal','editServiceModal','editPackageModal','createPackageModal']
    .forEach(id => document.getElementById(id).classList.add('hidden'));
}

// ── ADD VENUE ─────────────────────────────────────────────────
function openAddVenue() {
  ['vName','vDesc','vPrice','vCapacity','vTimeSlot','vVenue','vImgData'].forEach(id => document.getElementById(id).value='');
  document.getElementById('vType').value='';
  document.querySelectorAll('input[name="vBooking"]')[0].checked=true;
  resetImgBox('venueImgBox', true);
  document.getElementById('venueModal').classList.remove('hidden');
}
async function saveVenue() {
  const name=document.getElementById('vName').value.trim(), price=parseFloat(document.getElementById('vPrice').value);
  if (!name||isNaN(price)) { alert('Please fill in service name and price.'); return; }
  try {
    const result = await apiRequest(serviceManagementUrls.serviceCreate, serviceFormPayload('v', 'Venue'));
    upsertItem(services, result.item);
    closeAll(); currentTab='services'; switchTab('services');
  } catch (error) { alert(error.message); }
}

// ── ADD OTHERS ────────────────────────────────────────────────
function openAddOthers() {
  ['oName','oDesc','oPrice','oCapacity','oTimeSlot','oImgData'].forEach(id => document.getElementById(id).value='');
  document.querySelectorAll('input[name="oBooking"]')[0].checked=true;
  document.getElementById('oTimeSlotWrap').classList.add('hidden');
  resetImgBox('othersImgBox', true);
  document.getElementById('othersModal').classList.remove('hidden');
}
async function saveOthers() {
  const name=document.getElementById('oName').value.trim(), price=parseFloat(document.getElementById('oPrice').value);
  if (!name||isNaN(price)) { alert('Please fill in service name and price.'); return; }
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
  document.getElementById('esPrice').value=item.price;
  document.getElementById('esImgData').value=item.img||'';
  if (item.img) renderConfirmedImage(item.img, item.img, 'esImgInput', 'esImgBox', 'esImgData', 16/9);
  else resetImgBox('esImgBox', true);
  const extras=document.getElementById('esVenueExtras');
  if (item.category==='Venue') {
    extras.classList.remove('hidden');
    document.getElementById('esCapacity').value=item.capacity||'';
    document.getElementById('esType').value=item.type||'';
    document.getElementById('esTimeSlot').value=item.timeslot||'';
    document.getElementById('esVenue').value=item.venue||'';
  } else extras.classList.add('hidden');
  document.getElementById('editServiceModal').classList.remove('hidden');
}
async function updateService() {
  const item=services.find(s=>s.id===editingSvcId); if (!item) return;
  const payload = {
    ...item,
    name: document.getElementById('esName').value.trim()||item.name,
    desc: document.getElementById('esDesc').value.trim(),
    price: parseFloat(document.getElementById('esPrice').value)||item.price,
    img: document.getElementById('esImgData').value,
    capacity: parseInt(document.getElementById('esCapacity')?.value || item.capacity || '1') || 1,
    type: document.getElementById('esType')?.value || item.type || '',
    timeslot: document.getElementById('esTimeSlot')?.value.trim() || item.timeslot || '',
    venue: document.getElementById('esVenue')?.value.trim() || item.venue || ''
  };
  try {
    const result = await apiRequest(serviceManagementUrls.serviceUpdate + editingSvcId, payload);
    upsertItem(services, result.item);
    closeAll(); render();
  } catch (error) { alert(error.message); }
}

async function deleteService(id) {
  const item=services.find(s=>s.id===id); if (!item) return;
  if (!confirm(`Delete "${item.name}"?`)) return;
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
  if (!confirm(`Delete "${item.name}"?`)) return;
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
  const input = document.getElementById(inputId);
  input.value = '';
  if (e.currentTarget && e.currentTarget.getAttribute('for') === inputId) return;
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
    box.innerHTML = `<svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg><span class="text-sm text-gray-500 font-medium">Upload image</span>`;
  }
}

function toggleVenueTimeSlot(val) {
  const wrap = document.getElementById('vTimeSlotWrap');
  if (val === 'timeslot') wrap.classList.remove('hidden');
  else { wrap.classList.add('hidden'); document.getElementById('vTimeSlot').value = ''; }
}

function toggleOthersTimeSlot(val) {
  const wrap = document.getElementById('oTimeSlotWrap');
  if (val === 'timeslot') wrap.classList.remove('hidden');
  else { wrap.classList.add('hidden'); document.getElementById('oTimeSlot').value = ''; }
}

// Close when overlay is clicked
['venueModal','othersModal','editServiceModal','editPackageModal','createPackageModal'].forEach(id=>{
  document.getElementById(id).addEventListener('click', function(e){ if(e.target===this) closeAll(); });
});

renderCategoryControls();
render();
