const serviceDetailConfig = window.serviceDetailConfig || { urls: {}, servicePayloadBase: {} };
const urls = serviceDetailConfig.urls;

function escapeHtml(value) {
  return String(value ?? '').replace(/[&<>"']/g, char => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;'
  }[char]));
}

function showMessage(elementId, text, success = false, title = '') {
  const element = document.getElementById(elementId);
  if (!element) return;

  if (elementId === 'publishMessage' && text) {
    element.innerHTML = `
      <div class="sd-publish-toast-icon"><i class="ti ${success ? 'ti-send' : 'ti-alert-triangle'}"></i></div>
      <div class="sd-publish-toast-copy">
        <strong>${escapeHtml(title || (success ? 'Changes saved' : 'Request needs attention'))}</strong>
        <p>${escapeHtml(text)}</p>
      </div>
      <button type="button" class="sd-publish-toast-close" aria-label="Dismiss notification">&times;</button>
    `;
    element.querySelector('.sd-publish-toast-close')?.addEventListener('click', () => {
      showMessage(elementId, '');
    }, { once: true });
  } else {
    element.textContent = text || '';
  }

  element.style.display = text ? 'flex' : 'none';
  element.classList.toggle('success', Boolean(success));
  element.classList.toggle('error', !success);
  element.style.alignItems = 'center';
}

function currentServiceMinLeadDays() {
  const element = document.getElementById('availabilityMinLeadDays');
  return Math.max(0, Math.min(365, parseInt(element?.value || '0', 10) || 0));
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

function setPublishedState(isLive, readinessText = '') {
  const detail = document.getElementById('supplier-service-detail');
  const dot = document.getElementById('publishStatusDot');
  const text = document.getElementById('publishStatusText');
  const button = document.getElementById('publishServiceBtn');
  const buttonText = document.getElementById('publishServiceBtnText');
  const buttonIcon = button?.querySelector('i');
  const infoStatus = document.getElementById('serviceInfoStatus');

  if (detail) detail.dataset.serviceStatus = isLive ? 'active' : 'inactive';
  dot?.classList.toggle('is-live', Boolean(isLive));

  if (text) text.textContent = isLive ? 'Live' : (readinessText || 'Draft');
  if (button) {
    button.disabled = false;
    button.classList.toggle('btn-primary', !isLive);
    button.classList.toggle('btn-outline', isLive);
    button.classList.toggle('sd-unpublish-btn', isLive);
  }
  if (buttonText) buttonText.textContent = isLive ? 'Unpublish' : 'Request publish';
  if (buttonIcon) {
    buttonIcon.className = isLive ? 'ti ti-eye-off' : 'ti ti-send';
    buttonIcon.style.fontSize = '13px';
  }
  if (infoStatus) {
    infoStatus.style.color = isLive ? 'var(--success)' : 'var(--text-3)';
    infoStatus.innerHTML = `<i class="ti ${isLive ? 'ti-circle-check-filled' : 'ti-file-pencil'}" style="font-size:13px"></i><span>${isLive ? 'Published' : 'Draft'}</span>`;
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
  const detail = document.getElementById('supplier-service-detail');
  const isLive = detail?.dataset.serviceStatus === 'active';
  showMessage('publishMessage', '');

  if (isLive && !window.confirm('Unpublish this service? Customers will no longer see or book it until admin approves a new publish request.')) {
    return;
  }

  button.disabled = true;

  try {
    if (isLive) {
      const result = await jsonPost(urls.serviceStatus, { status: 'inactive' });
      const draftReady = detail?.dataset.draftReadiness === 'ready';
      setPublishedState(false, draftReady ? 'Draft · Ready to publish' : 'Draft · Needs attention');
      stopPublishStatusPolling();
      showMessage('publishMessage', result.message || 'Service unpublished.', true, 'Moved to draft');
    } else {
      const result = await jsonPost(urls.publishRequest);
      showMessage('publishMessage', result.message || 'Publish request sent to admin.', true, 'Publish request sent');
      startPublishStatusPolling();
    }
  } catch (error) {
    showMessage('publishMessage', error.message);
  } finally {
    button.disabled = false;
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
  const addButton = grid?.querySelector('.sd-gallery-add');
  if (!grid || !media) return;

  const item = document.createElement('div');
  item.className = 'sd-gallery-item';
  item.dataset.mediaId = media.id;
  item.innerHTML = `
    <img src="${media.file_url}" alt="Service photo">
    <button type="button" class="sd-gallery-del" onclick="deleteServiceMedia(${media.id})"><i class="ti ti-trash" style="font-size:13px"></i></button>
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

let decorationStyleCounter = 0;

function decorationStyleCardHtml(style = {}) {
  const uid = ++decorationStyleCounter;
  const photoUrl = style.photo_url || '';
  const photoPreview = photoUrl
    ? `<img src="${escapeHtml(photoUrl)}" alt="Decoration style photo">`
    : '<i class="ti ti-photo"></i><span>Style photo</span>';

  return `
    <div class="sd-decoration-style-card">
      <input type="hidden" class="decoration-style-photo-url" value="${escapeHtml(photoUrl)}">
      <div class="sd-decoration-style-photo">
        <label class="sd-decoration-style-preview ${photoUrl ? '' : 'is-empty'}" for="decorationStylePhoto${uid}">
          ${photoPreview}
        </label>
        <input id="decorationStylePhoto${uid}" type="file" accept="image/*" class="decoration-style-photo-input" style="display:none">
      </div>
      <div class="sd-decoration-style-fields">
        <div class="sd-hall-fg full"><label>Style name</label><input class="sd-hall-input decoration-style-name" value="${escapeHtml(style.name || '')}" placeholder="e.g. Balloon arch"></div>
        <div class="sd-hall-fg full"><label>Price</label><input type="number" min="0" step="0.01" class="sd-hall-input decoration-style-price" value="${style.price ?? ''}" placeholder="MMK"></div>
      </div>
      <button type="button" class="btn btn-icon btn-danger-ghost btn-sm sd-decoration-style-remove" title="Remove style"><i class="ti ti-trash" style="font-size:13px"></i></button>
    </div>
  `;
}

function updateDecorationStyleCount() {
  const count = document.querySelectorAll('.sd-decoration-style-card').length;
  const badge = document.getElementById('decorationStyleCount');
  if (badge) badge.textContent = count + ' ' + (count === 1 ? 'style' : 'styles');
}

function renderDecorationStyles(styles = []) {
  const grid = document.getElementById('decorationStyleGrid');
  if (!grid) return;
  const rows = styles.length ? styles : [{}];
  grid.innerHTML = rows.map(style => decorationStyleCardHtml(style)).join('');
  updateDecorationStyleCount();
}

function addDecorationStyle() {
  const grid = document.getElementById('decorationStyleGrid');
  if (!grid) return;
  grid.insertAdjacentHTML('beforeend', decorationStyleCardHtml());
  updateDecorationStyleCount();
}

function collectDecorationStyles() {
  return Array.from(document.querySelectorAll('.sd-decoration-style-card')).map(card => ({
    name: card.querySelector('.decoration-style-name')?.value.trim() || '',
    price: parseFloat(card.querySelector('.decoration-style-price')?.value || '0') || 0,
    photo_url: card.querySelector('.decoration-style-photo-url')?.value || null
  })).filter(style => style.name !== '');
}

document.getElementById('addDecorationStyleBtn')?.addEventListener('click', addDecorationStyle);

document.getElementById('decorationStyleGrid')?.addEventListener('click', event => {
  const removeButton = event.target.closest('.sd-decoration-style-remove');
  if (!removeButton) return;
  const grid = document.getElementById('decorationStyleGrid');
  const card = removeButton.closest('.sd-decoration-style-card');
  if (!grid || !card) return;

  if (grid.querySelectorAll('.sd-decoration-style-card').length <= 1) {
    card.querySelectorAll('input').forEach(input => { input.value = ''; });
    const preview = card.querySelector('.sd-decoration-style-preview');
    if (preview) {
      preview.classList.add('is-empty');
      preview.innerHTML = '<i class="ti ti-photo"></i><span>Style photo</span>';
    }
  } else {
    card.remove();
  }

  updateDecorationStyleCount();
});

document.getElementById('decorationStyleGrid')?.addEventListener('change', async event => {
  const input = event.target.closest('.decoration-style-photo-input');
  if (!input || !input.files?.[0]) return;

  const card = input.closest('.sd-decoration-style-card');
  const hidden = card?.querySelector('.decoration-style-photo-url');
  const preview = card?.querySelector('.sd-decoration-style-preview');

  try {
    const dataUrl = await fileToDataUrl(input.files[0]);
    if (hidden) hidden.value = dataUrl;
    if (preview) {
      preview.classList.remove('is-empty');
      preview.innerHTML = `<img src="${dataUrl}" alt="Decoration style photo preview">`;
    }
  } catch (error) {
    showMessage('decorationStyleMessage', 'Could not read style photo. Please try another image.');
  } finally {
    input.value = '';
  }
});

document.getElementById('saveDecorationStylesBtn')?.addEventListener('click', async () => {
  showMessage('decorationStyleMessage', '');
  try {
    const styles = collectDecorationStyles();
    if (!styles.length) {
      throw new Error('Add at least one decoration style with a name.');
    }
    if (styles.some(style => style.price <= 0)) {
      throw new Error('Each decoration style needs a price greater than zero.');
    }

    const result = await jsonPost(urls.serviceUpdate, {
      ...serviceDetailConfig.servicePayloadBase,
      min_lead_days: currentServiceMinLeadDays(),
      decoration_styles: styles
    });
    const savedService = result.item || {};
    const savedStyles = Array.isArray(savedService.decoration_styles) ? savedService.decoration_styles : styles;
    serviceDetailConfig.decorationStyles = savedStyles;
    serviceDetailConfig.servicePayloadBase.price = savedService.price ?? serviceDetailConfig.servicePayloadBase.price;
    serviceDetailConfig.servicePayloadBase.price_min = savedService.price_min ?? serviceDetailConfig.servicePayloadBase.price_min;
    serviceDetailConfig.servicePayloadBase.price_max = savedService.price_max ?? serviceDetailConfig.servicePayloadBase.price_max;
    renderDecorationStyles(savedStyles);
    updateServiceInfoFromService(savedService);
    showMessage('decorationStyleMessage', 'Decoration styles saved.', true);
  } catch (error) {
    showMessage('decorationStyleMessage', error.message);
  }
});

renderDecorationStyles(Array.isArray(serviceDetailConfig.decorationStyles) ? serviceDetailConfig.decorationStyles : []);

// ── FOOD ITEMS (CAKES) ────────────────────────────────────────────
let foodItemCounter = 0;

function foodItemCardHtml(item = {}) {
  const uid = ++foodItemCounter;
  const photoUrl = item.photo_url || '';
  const photoPreview = photoUrl
    ? `<img src="${escapeHtml(photoUrl)}" alt="Cake photo">`
    : '<i class="ti ti-photo"></i><span>Photo</span>';

  return `
    <div class="sd-food-item-card">
      <input type="hidden" class="food-item-photo-url" value="${escapeHtml(photoUrl)}">
      <div class="sd-food-item-photo">
        <label class="sd-food-item-preview ${photoUrl ? '' : 'is-empty'}" for="foodItemPhoto${uid}">
          ${photoPreview}
        </label>
        <input id="foodItemPhoto${uid}" type="file" accept="image/*" class="food-item-photo-input" style="display:none">
      </div>
      <div class="sd-food-item-fields">
        <div class="sd-hall-fg full"><label>Cake name</label><input class="sd-hall-input food-item-name" value="${escapeHtml(item.name || '')}" placeholder="e.g. Chocolate Wedding Cake"></div>
        <div class="sd-hall-fg full"><label>Description</label><input class="sd-hall-input food-item-description" value="${escapeHtml(item.description || '')}" placeholder="e.g. 3-tier, serves 100"></div>
        <div class="sd-hall-fg"><label>Package price</label><input type="number" min="0" step="0.01" class="sd-hall-input food-item-package-price" value="${item.package_price ?? item.price ?? ''}" placeholder="MMK"></div>
        <div class="sd-hall-fg"><label>Customize price</label><input type="number" min="0" step="0.01" class="sd-hall-input food-item-customize-price" value="${item.customize_price ?? item.price ?? ''}" placeholder="MMK"></div>
      </div>
      <button type="button" class="btn btn-icon btn-danger-ghost btn-sm sd-food-item-remove" title="Remove"><i class="ti ti-trash" style="font-size:13px"></i></button>
    </div>
  `;
}

function updateFoodItemCount() {
  const count = document.querySelectorAll('.sd-food-item-card').length;
  const badge = document.getElementById('foodItemCount');
  if (badge) badge.textContent = count + ' ' + (count === 1 ? 'item' : 'items');
}

function renderFoodItems(items = []) {
  const grid = document.getElementById('foodItemGrid');
  if (!grid) return;
  const rows = items.length ? items : [{}];
  grid.innerHTML = rows.map(item => foodItemCardHtml(item)).join('');
  updateFoodItemCount();
}

function addFoodItem() {
  const grid = document.getElementById('foodItemGrid');
  if (!grid) return;
  grid.insertAdjacentHTML('beforeend', foodItemCardHtml());
  updateFoodItemCount();
}

function collectFoodItems() {
  return Array.from(document.querySelectorAll('.sd-food-item-card')).map(card => ({
    name: card.querySelector('.food-item-name')?.value.trim() || '',
    description: card.querySelector('.food-item-description')?.value.trim() || '',
    price: parseFloat(card.querySelector('.food-item-package-price')?.value || '0') || 0,
    package_price: parseFloat(card.querySelector('.food-item-package-price')?.value || '0') || 0,
    customize_price: parseFloat(card.querySelector('.food-item-customize-price')?.value || '0') || 0,
    photo_url: card.querySelector('.food-item-photo-url')?.value || null
  })).filter(item => item.name !== '');
}

document.getElementById('addFoodItemBtn')?.addEventListener('click', addFoodItem);

document.getElementById('foodItemGrid')?.addEventListener('click', event => {
  const removeButton = event.target.closest('.sd-food-item-remove');
  if (!removeButton) return;
  const grid = document.getElementById('foodItemGrid');
  const card = removeButton.closest('.sd-food-item-card');
  if (!grid || !card) return;

  if (grid.querySelectorAll('.sd-food-item-card').length <= 1) {
    card.querySelectorAll('input').forEach(input => { if (input.type !== 'hidden') input.value = ''; });
    const preview = card.querySelector('.sd-food-item-preview');
    if (preview) {
      preview.classList.add('is-empty');
      preview.innerHTML = '<i class="ti ti-photo"></i><span>Photo</span>';
    }
  } else {
    card.remove();
  }

  updateFoodItemCount();
});

document.getElementById('foodItemGrid')?.addEventListener('change', async event => {
  const input = event.target.closest('.food-item-photo-input');
  if (!input || !input.files?.[0]) return;

  const card = input.closest('.sd-food-item-card');
  const hidden = card?.querySelector('.food-item-photo-url');
  const preview = card?.querySelector('.sd-food-item-preview');

  try {
    const dataUrl = await fileToDataUrl(input.files[0]);
    if (hidden) hidden.value = dataUrl;
    if (preview) {
      preview.classList.remove('is-empty');
      preview.innerHTML = `<img src="${dataUrl}" alt="Cake photo preview">`;
    }
  } catch (error) {
    showMessage('foodItemMessage', 'Could not read photo. Please try another image.');
  } finally {
    input.value = '';
  }
});

document.getElementById('saveFoodItemsBtn')?.addEventListener('click', async () => {
  showMessage('foodItemMessage', '');
  try {
    const items = collectFoodItems();
    if (!items.length) {
      throw new Error('Add at least one cake item with a name.');
    }
    if (items.some(item => item.price <= 0)) {
      throw new Error('Each cake item needs a price greater than zero.');
    }

    const result = await jsonPost(urls.serviceUpdate, {
      ...serviceDetailConfig.servicePayloadBase,
      min_lead_days: currentServiceMinLeadDays(),
      food_items: items
    });
    const savedService = result.item || {};
    const savedItems = Array.isArray(savedService.food_items) ? savedService.food_items : items;
    serviceDetailConfig.foodItems = savedItems;
    serviceDetailConfig.servicePayloadBase.price = savedService.price ?? serviceDetailConfig.servicePayloadBase.price;
    serviceDetailConfig.servicePayloadBase.price_min = savedService.price_min ?? serviceDetailConfig.servicePayloadBase.price_min;
    serviceDetailConfig.servicePayloadBase.price_max = savedService.price_max ?? serviceDetailConfig.servicePayloadBase.price_max;
    renderFoodItems(savedItems);
    updateServiceInfoFromService(savedService);
    showMessage('foodItemMessage', 'Cake items saved.', true);
  } catch (error) {
    showMessage('foodItemMessage', error.message);
  }
});

renderFoodItems(Array.isArray(serviceDetailConfig.foodItems) ? serviceDetailConfig.foodItems : []);

// ── ATTIRE ITEMS ────────────────────────────────────────────────
let attireDrafts = [];
let activeAttireIndex = 0;

function normalizeAttireItem(item = {}) {
  const options = Array.isArray(item.rental_options) ? item.rental_options : [];
  return {
    id: item.id || 0,
    name: item.name || '',
    photo_url: item.photo_url || '',
    borrow_package_price: item.borrow_package_price ?? '',
    borrow_customize_price: item.borrow_customize_price ?? '',
    buy_package_price: item.buy_package_price ?? '',
    buy_customize_price: item.buy_customize_price ?? '',
    return_days: item.return_days ?? '',
    buffer_days: item.buffer_days ?? 1,
    rental_options: options.map(normalizeRentalOption)
  };
}

function normalizeRentalOption(opt = {}) {
  return {
    days: opt.days ?? '',
    price: opt.price ?? '',
    customize_price: opt.customize_price ?? ''
  };
}

function attireHasBorrowPrice(item) {
  if (Array.isArray(item.rental_options) && item.rental_options.length > 0) {
    return true;
  }
  return Number(item.borrow_package_price) > 0 || Number(item.borrow_customize_price) > 0;
}

function attireHasBuyPrice(item) {
  return Number(item.buy_package_price) > 0 || Number(item.buy_customize_price) > 0;
}

function attireOfferLabel(item) {
  const offers = [];
  if (attireHasBorrowPrice(item)) offers.push('Borrow');
  if (attireHasBuyPrice(item)) offers.push('Buy');
  return offers.length ? offers.join(' + ') : 'Needs pricing';
}

function updateAttireItemCount() {
  const count = attireDrafts.length;
  const badge = document.getElementById('attireItemCount');
  if (badge) badge.textContent = count + ' ' + (count === 1 ? 'dress' : 'items');
}

function attireNavHtml() {
  return attireDrafts.map((item, index) => {
    const itemName = item.name.trim() || `New dress ${index + 1}`;
    const photo = item.photo_url
      ? `<img src="${escapeHtml(item.photo_url)}" alt="">`
      : '<i class="ti ti-hanger"></i>';
    const ready = attireHasBorrowPrice(item) || attireHasBuyPrice(item);

    return `
      <button type="button" class="sd-attire-nav-item ${index === activeAttireIndex ? 'is-active' : ''}" data-attire-index="${index}" aria-pressed="${index === activeAttireIndex}">
        <span class="sd-attire-nav-thumb ${item.photo_url ? '' : 'is-empty'}">${photo}</span>
        <span class="sd-attire-nav-copy">
          <strong>${escapeHtml(itemName)}</strong>
          <small>${escapeHtml(attireOfferLabel(item))}</small>
        </span>
        <span class="sd-attire-nav-status ${ready ? 'is-ready' : ''}" title="${ready ? 'Pricing added' : 'Pricing needed'}"></span>
      </button>
    `;
  }).join('');
}

function rentalOptionsHtml(options = []) {
  if (!options.length) {
    return '<p style="color:var(--text-3);font-size:12px;margin:0">No rental durations added yet. Add at least one.</p>';
  }
  return options.map((opt, i) => `
    <div class="sd-rental-option-row" data-rental-index="${i}" style="display:flex;gap:8px;align-items:flex-end;margin-bottom:6px">
      <div class="sd-hall-fg" style="flex:0 0 100px">
        <label>Days</label>
        <input type="number" min="1" step="1" class="sd-hall-input" data-rental-field="days" value="${escapeHtml(opt.days)}" placeholder="3">
      </div>
      <div class="sd-hall-fg" style="flex:1">
        <label>Package price</label>
        <div class="sd-attire-money-input"><input type="number" min="0" step="0.01" class="sd-hall-input" data-rental-field="price" value="${escapeHtml(opt.price)}" placeholder="0"><span>MMK</span></div>
      </div>
      <div class="sd-hall-fg" style="flex:1">
        <label>Customize price</label>
        <div class="sd-attire-money-input"><input type="number" min="0" step="0.01" class="sd-hall-input" data-rental-field="customize_price" value="${escapeHtml(opt.customize_price)}" placeholder="0"><span>MMK</span></div>
      </div>
      <button type="button" class="btn btn-icon btn-danger-ghost btn-sm" data-remove-rental-option="${i}" title="Remove" style="margin-bottom:2px"><i class="ti ti-x" style="font-size:14px"></i></button>
    </div>
  `).join('');
}

function attireEditorHtml(item, index) {
  const itemName = item.name.trim() || `New dress ${index + 1}`;
  const photoPreview = item.photo_url
    ? `<img src="${escapeHtml(item.photo_url)}" alt="${escapeHtml(itemName)} photo">`
    : '<i class="ti ti-photo-plus"></i><strong>Add the dress photo</strong><span>A clear portrait photo works best</span>';

  return `
    <aside class="sd-attire-wardrobe">
      <div class="sd-attire-wardrobe-head">
        <div>
          <span>Your wardrobe</span>
          <strong>${attireDrafts.length} ${attireDrafts.length === 1 ? 'dress' : 'dresses'}</strong>
        </div>
      </div>
      <div class="sd-attire-nav-list">${attireNavHtml()}</div>
      <button type="button" class="sd-attire-add-card" data-attire-add><i class="ti ti-plus"></i> Add another dress</button>
    </aside>
    <section class="sd-attire-editor">
      <div class="sd-attire-editor-head">
        <div>
          <span class="sd-attire-editor-kicker">Editing dress ${index + 1}</span>
          <h3>${escapeHtml(itemName)}</h3>
          <p>Photo, borrowing, and buying details for this dress.</p>
        </div>
        <button type="button" class="btn btn-icon btn-danger-ghost btn-sm" data-attire-remove title="Remove dress" aria-label="Remove ${escapeHtml(itemName)}"><i class="ti ti-trash" style="font-size:14px"></i></button>
      </div>
      <div class="sd-attire-editor-grid">
        <div class="sd-attire-photo-stage">
          <div class="sd-attire-preview ${item.photo_url ? '' : 'is-empty'}">${photoPreview}</div>
          <label class="sd-attire-photo-action">
            <i class="ti ti-upload"></i>
            <span>${item.photo_url ? 'Replace photo' : 'Upload photo'}</span>
            <input type="file" accept="image/*" class="attire-item-photo-input" style="display:none">
          </label>
        </div>
        <div class="sd-attire-fields">
          <div class="sd-hall-fg full">
            <label>Dress / item name</label>
            <input class="sd-hall-input" data-attire-field="name" value="${escapeHtml(item.name)}" placeholder="e.g. Long Sleeve Bridal Gown">
          </div>
          <div class="sd-attire-price-group">
            <div class="sd-attire-group-head"><i class="ti ti-clock"></i><span>Rental options</span><small>Duration tiers with pricing</small></div>
            <div class="sd-rental-options-list" data-rental-options>
              ${rentalOptionsHtml(item.rental_options || [])}
            </div>
            <button type="button" class="btn btn-outline btn-sm" data-add-rental-option style="margin-top:8px"><i class="ti ti-plus" style="font-size:12px"></i> Add duration</button>
            <div class="sd-hall-fg sd-attire-return" style="margin-top:12px"><label>Buffer days after return</label><div class="sd-attire-return-input"><input type="number" min="0" step="1" class="sd-hall-input" data-attire-field="buffer_days" value="${escapeHtml(item.buffer_days ?? 1)}" placeholder="1"><span>days</span></div><small style="color:var(--text-3);font-size:11px">Blocked for cleaning/alteration after return</small></div>
          </div>
          <div class="sd-attire-price-group">
            <div class="sd-attire-group-head buy"><i class="ti ti-shopping-bag"></i><span>Buy pricing</span><small>Customer keeps it</small></div>
            <div class="sd-attire-pair">
              <div class="sd-hall-fg"><label>Package price</label><div class="sd-attire-money-input"><input type="number" min="0" step="0.01" class="sd-hall-input" data-attire-field="buy_package_price" value="${escapeHtml(item.buy_package_price)}" placeholder="0"><span>MMK</span></div></div>
              <div class="sd-hall-fg"><label>Customize price</label><div class="sd-attire-money-input"><input type="number" min="0" step="0.01" class="sd-hall-input" data-attire-field="buy_customize_price" value="${escapeHtml(item.buy_customize_price)}" placeholder="0"><span>MMK</span></div></div>
            </div>
          </div>
        </div>
      </div>
    </section>
  `;
}

function renderAttireItems(items = null) {
  const manager = document.getElementById('attireItemGrid');
  if (!manager) return;
  if (Array.isArray(items)) {
    attireDrafts = (items.length ? items : [{}]).map(normalizeAttireItem);
    activeAttireIndex = 0;
  }
  if (!attireDrafts.length) attireDrafts = [normalizeAttireItem()];
  activeAttireIndex = Math.max(0, Math.min(activeAttireIndex, attireDrafts.length - 1));
  manager.innerHTML = attireEditorHtml(attireDrafts[activeAttireIndex], activeAttireIndex);
  updateAttireItemCount();
}

function addAttireItem() {
  attireDrafts.push(normalizeAttireItem());
  activeAttireIndex = attireDrafts.length - 1;
  renderAttireItems();
  document.querySelector('[data-attire-field="name"]')?.focus();
}

function collectAttireItems() {
  return attireDrafts.map(item => {
    const rentalOptions = (item.rental_options || [])
      .filter(opt => parseInt(opt.days || '0', 10) > 0 && parseFloat(opt.price || '0') > 0)
      .map(opt => ({
        days: parseInt(opt.days, 10),
        price: parseFloat(opt.price),
        customize_price: parseFloat(opt.customize_price || '0') || null
      }));
    return {
      id: item.id || 0,
      name: item.name.trim(),
      photo_url: item.photo_url || null,
      borrow_package_price: parseFloat(item.borrow_package_price || '0') || null,
      borrow_customize_price: parseFloat(item.borrow_customize_price || '0') || null,
      buy_package_price: parseFloat(item.buy_package_price || '0') || null,
      buy_customize_price: parseFloat(item.buy_customize_price || '0') || null,
      return_days: parseInt(item.return_days || '0', 10) || null,
      buffer_days: parseInt(item.buffer_days || '1', 10) || 1,
      rental_options: rentalOptions
    };
  }).filter(item => item.name !== '');
}

document.getElementById('addAttireItemBtn')?.addEventListener('click', addAttireItem);

document.getElementById('attireItemGrid')?.addEventListener('input', event => {
  const field = event.target.closest('[data-attire-field]');
  if (!field || !attireDrafts[activeAttireIndex]) return;
  attireDrafts[activeAttireIndex][field.dataset.attireField] = field.value;

  const manager = document.getElementById('attireItemGrid');
  const title = manager?.querySelector('.sd-attire-editor-head h3');
  if (field.dataset.attireField === 'name' && title) {
    title.textContent = field.value.trim() || `New dress ${activeAttireIndex + 1}`;
  }
  const navList = manager?.querySelector('.sd-attire-nav-list');
  if (navList) navList.innerHTML = attireNavHtml();
});

document.getElementById('attireItemGrid')?.addEventListener('click', event => {
  const navItem = event.target.closest('[data-attire-index]');
  if (navItem) {
    activeAttireIndex = Number(navItem.dataset.attireIndex);
    renderAttireItems();
    return;
  }
  if (event.target.closest('[data-attire-add]')) {
    addAttireItem();
    return;
  }
  if (event.target.closest('[data-attire-remove]')) {
    const item = attireDrafts[activeAttireIndex];
    const itemName = item?.name.trim() || `dress ${activeAttireIndex + 1}`;
    if (item?.name.trim() && !window.confirm(`Remove "${itemName}" from this collection?`)) return;
    if (attireDrafts.length === 1) {
      attireDrafts[0] = normalizeAttireItem();
      activeAttireIndex = 0;
    } else {
      attireDrafts.splice(activeAttireIndex, 1);
      activeAttireIndex = Math.min(activeAttireIndex, attireDrafts.length - 1);
    }
    renderAttireItems();
  }
});

document.getElementById('attireItemGrid')?.addEventListener('change', async event => {
  const input = event.target.closest('.attire-item-photo-input');
  if (!input || !input.files?.[0]) return;

  try {
    const dataUrl = await fileToDataUrl(input.files[0]);
    attireDrafts[activeAttireIndex].photo_url = dataUrl;
    renderAttireItems();
  } catch (error) {
    showMessage('attireItemMessage', 'Could not read item photo. Please try another image.');
  } finally {
    input.value = '';
  }
});

// Rental option event handlers
document.getElementById('attireItemGrid')?.addEventListener('click', event => {
  const addBtn = event.target.closest('[data-add-rental-option]');
  if (addBtn) {
    if (!attireDrafts[activeAttireIndex].rental_options) {
      attireDrafts[activeAttireIndex].rental_options = [];
    }
    attireDrafts[activeAttireIndex].rental_options.push(normalizeRentalOption());
    renderAttireItems();
    return;
  }
  const removeBtn = event.target.closest('[data-remove-rental-option]');
  if (removeBtn) {
    const idx = Number(removeBtn.dataset.removeRentalOption);
    if (attireDrafts[activeAttireIndex].rental_options) {
      attireDrafts[activeAttireIndex].rental_options.splice(idx, 1);
      renderAttireItems();
    }
    return;
  }
});

document.getElementById('attireItemGrid')?.addEventListener('input', event => {
  const field = event.target.closest('[data-rental-field]');
  if (!field || !attireDrafts[activeAttireIndex]) return;
  const row = field.closest('[data-rental-index]');
  if (!row) return;
  const idx = Number(row.dataset.rentalIndex);
  if (!attireDrafts[activeAttireIndex].rental_options) {
    attireDrafts[activeAttireIndex].rental_options = [];
  }
  if (attireDrafts[activeAttireIndex].rental_options[idx]) {
    attireDrafts[activeAttireIndex].rental_options[idx][field.dataset.rentalField] = field.value;
  }
});

document.getElementById('saveAttireItemsBtn')?.addEventListener('click', async () => {
  showMessage('attireItemMessage', '');
  try {
    const items = collectAttireItems();
    if (!items.length) {
      throw new Error('Add at least one attire item with a name.');
    }
    const hasNoPrice = items.some(item =>
      (!item.rental_options || item.rental_options.length === 0) &&
      (!item.buy_package_price || item.buy_package_price <= 0) &&
      (!item.buy_customize_price || item.buy_customize_price <= 0)
    );
    if (hasNoPrice) {
      throw new Error('Each attire item needs rental options (duration + price) or a buy price.');
    }

    const result = await jsonPost(urls.serviceUpdate, {
      ...serviceDetailConfig.servicePayloadBase,
      min_lead_days: currentServiceMinLeadDays(),
      attire_items: items,
      attire_items_replace: true
    });
    const savedService = result.item || {};
    const savedItems = Array.isArray(savedService.attire_items) ? savedService.attire_items : items;
    serviceDetailConfig.attireItems = savedItems;
    serviceDetailConfig.servicePayloadBase.price = savedService.price ?? serviceDetailConfig.servicePayloadBase.price;
    serviceDetailConfig.servicePayloadBase.price_min = savedService.price_min ?? serviceDetailConfig.servicePayloadBase.price_min;
    serviceDetailConfig.servicePayloadBase.price_max = savedService.price_max ?? serviceDetailConfig.servicePayloadBase.price_max;
    renderAttireItems(savedItems);
    const attireCount = document.getElementById('serviceInfoAttireCount');
    if (attireCount) attireCount.textContent = String(savedItems.length);
    updateServiceInfoFromService(savedService);
    showMessage('attireItemMessage', 'Dress collection saved.', true);
  } catch (error) {
    showMessage('attireItemMessage', error.message);
  }
});

renderAttireItems(Array.isArray(serviceDetailConfig.attireItems) ? serviceDetailConfig.attireItems : []);

function toggleDay(checkbox) {
  const card = checkbox.closest('.sd-day-card');
  const closedEl = card.querySelector('.sd-day-closed');
  const timeWrap = card.querySelector('.sd-day-time');
  const existingStart = card.dataset.start || '09:00';
  const existingEnd = card.dataset.end || '17:00';

  if (checkbox.checked) {
    card.classList.remove('is-closed');
    timeWrap.innerHTML = `
      <input class="time-input availability-start" type="time" value="${existingStart}">
      <span style="display:block;font-size:9px;color:var(--text-3);line-height:1">to</span>
      <input class="time-input availability-end" type="time" value="${existingEnd}">
    `;
  } else {
    card.dataset.start = card.querySelector('.availability-start')?.value || existingStart;
    card.dataset.end = card.querySelector('.availability-end')?.value || existingEnd;
    card.classList.add('is-closed');
    timeWrap.innerHTML = '<span class="sd-day-closed">Closed</span>';
  }
}

document.querySelectorAll('.sd-day-card').forEach(card => {
  card.dataset.start = card.querySelector('.availability-start')?.value || '09:00';
  card.dataset.end = card.querySelector('.availability-end')?.value || '17:00';
});

document.getElementById('saveAvailabilityBtn')?.addEventListener('click', async () => {
  const weekly = Array.from(document.querySelectorAll('.sd-day-card')).map(row => {
    const open = row.querySelector('.availability-open')?.checked || false;
    return {
      day_of_week: Number(row.dataset.day),
      is_available: open,
      open_time: row.querySelector('.availability-start')?.value || row.dataset.start || '09:00',
      close_time: row.querySelector('.availability-end')?.value || row.dataset.end || '17:00'
    };
  });

  try {
    const concurrentElement = document.getElementById('availabilityConcurrent');
    const concurrentPackageEl = document.getElementById('availabilityConcurrentPackage');
    const concurrentCustomizeEl = document.getElementById('availabilityConcurrentCustomize');
    const durationEl = document.getElementById('availabilityDuration');
    const bufferEl = document.getElementById('availabilityBuffer');
    const minLeadDays = currentServiceMinLeadDays();
    await jsonPost(urls.availabilitySave, {
      duration_minutes: durationEl ? durationEl.value : 60,
      buffer_minutes: bufferEl ? bufferEl.value : 0,
      max_concurrent: concurrentElement ? concurrentElement.value : 1,
      max_concurrent_package: concurrentPackageEl ? concurrentPackageEl.value : 0,
      max_concurrent_customize: concurrentCustomizeEl ? concurrentCustomizeEl.value : 0,
      min_lead_days: minLeadDays,
      weekly
    });
    serviceDetailConfig.servicePayloadBase.min_lead_days = minLeadDays;
    const infoMinLeadDays = document.getElementById('serviceInfoMinLeadDays');
    if (infoMinLeadDays) infoMinLeadDays.textContent = minLeadDays + ' days';
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
  const packagePrice = room.price_min ?? room.package_price ?? room.price ?? 0;
  const customizePrice = room.price_max ?? room.customize_price ?? packagePrice;
  const minLeadDays = room.min_lead_days ?? '';
  const photoUrl = room.photo_url || '';
  const photoPreview = photoUrl
    ? `<img src="${escapeHtml(photoUrl)}" alt="${escapeHtml((room.name || 'Hall') + ' photo')}">`
    : '<i class="ti ti-photo"></i><span>Hall photo</span>';

  return `
    <div class="sd-hall-card" data-room-id="${room.id || 0}">
      <input type="hidden" class="hall-id" value="${room.id || ''}">
      <input type="hidden" class="hall-photo-url" value="${escapeHtml(photoUrl)}">
      <div class="sd-hall-head">
        <div class="sd-hall-head-left">
          <div class="sd-hall-icon"><i class="ti ti-door"></i></div>
        </div>
        <button type="button" class="btn btn-icon btn-danger-ghost btn-sm" onclick="removeHall(this)"><i class="ti ti-trash" style="font-size:13px"></i></button>
      </div>
      <div class="sd-hall-photo">
        <div class="sd-hall-photo-preview ${photoUrl ? '' : 'is-empty'}">${photoPreview}</div>
        <label class="sd-hall-photo-btn">
          <i class="ti ti-upload"></i>
          <span>${photoUrl ? 'Change photo' : 'Add photo'}</span>
          <input type="file" accept="image/*" class="hall-photo-input" style="display:none">
        </label>
      </div>
      <div class="sd-hall-fields">
        <div class="sd-hall-fg full"><label>Hall name</label><input class="sd-hall-input hall-name" value="${escapeHtml(room.name || '')}"></div>
        <div class="sd-hall-fg"><label>Capacity</label><input type="number" min="1" class="sd-hall-input hall-capacity" value="${room.capacity || 1}"></div>
        <div class="sd-hall-fg"><label>Package price</label><input type="number" min="0" step="0.01" class="sd-hall-input hall-price hall-price-min" value="${packagePrice}"></div>
        <div class="sd-hall-fg"><label>Customize price</label><input type="number" min="0" step="0.01" class="sd-hall-input hall-price-max" value="${customizePrice}"></div>
        <div class="sd-hall-fg"><label>Start time</label><input type="time" lang="en-GB" class="sd-hall-input hall-start" value="${room.start_time || '09:00'}"></div>
        <div class="sd-hall-fg"><label>End time</label><input type="time" lang="en-GB" class="sd-hall-input hall-end" value="${room.end_time || '17:00'}"></div>
        <div class="sd-hall-fg"><label>Min. notice (days)</label><input type="number" min="0" max="365" class="sd-hall-input hall-min-lead-days" value="${minLeadDays}" placeholder="Use service default"></div>
      </div>
      <div class="sd-hall-time">9:00 AM - 5:00 PM</div>
    </div>
  `;
}

function addHall() {
  document.getElementById('hallGrid')?.insertAdjacentHTML('beforeend', hallCardHtml());
  updateHallCount();
}

function removeHall(button) {
  button.closest('.sd-hall-card')?.remove();
  updateHallCount();
}

function updateHallCount() {
  const count = document.querySelectorAll('.sd-hall-card').length;
  const badge = document.getElementById('hallCount');
  if (badge) badge.textContent = count + ' ' + (count === 1 ? 'hall' : 'halls');
  const infoHalls = document.getElementById('serviceInfoHalls');
  if (infoHalls) infoHalls.textContent = String(count);
}

function formatMoney(value) {
  const amount = Number(value || 0);
  return amount.toLocaleString(undefined, { maximumFractionDigits: 0 }) + ' MMK';
}

function normalizeTimeValue(value) {
  return String(value || '').slice(0, 5) || '09:00';
}

function formatTimeLabel(value) {
  const time = normalizeTimeValue(value);
  const parts = time.split(':').map(Number);
  if (parts.length < 2 || Number.isNaN(parts[0]) || Number.isNaN(parts[1])) return time;
  const date = new Date();
  date.setHours(parts[0], parts[1], 0, 0);
  return date.toLocaleTimeString(undefined, { hour: 'numeric', minute: '2-digit' });
}

function syncSavedHalls(savedRooms = []) {
  const cards = Array.from(document.querySelectorAll('.sd-hall-card'));
  cards.forEach((card, index) => {
    const room = savedRooms[index];
    if (!room) return;
    const roomId = room.id || 0;
    card.dataset.roomId = String(roomId);
    const idInput = card.querySelector('.hall-id');
    if (idInput) idInput.value = roomId ? String(roomId) : '';
    const start = normalizeTimeValue(room.start_time || card.querySelector('.hall-start')?.value || '09:00');
    const end = normalizeTimeValue(room.end_time || card.querySelector('.hall-end')?.value || '17:00');
    const startInput = card.querySelector('.hall-start');
    const endInput = card.querySelector('.hall-end');
    const minLeadInput = card.querySelector('.hall-min-lead-days');
    const timeDisplay = card.querySelector('.sd-hall-time');
    if (startInput) startInput.value = start;
    if (endInput) endInput.value = end;
    if (minLeadInput) minLeadInput.value = room.min_lead_days ?? '';
    if (timeDisplay) timeDisplay.textContent = formatTimeLabel(start) + ' - ' + formatTimeLabel(end);
  });
}

function syncOverrideRoomOptions(savedRooms = []) {
  const select = document.getElementById('overrideRoom');
  if (!select) return;
  const current = select.value;
  select.innerHTML = '<option value="">Choose hall</option>' + savedRooms.map(room => {
    const id = room.id || '';
    const name = String(room.name || 'Hall').replace(/[&<>"']/g, char => ({
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#039;'
    }[char]));
    return `<option value="${id}">${name}</option>`;
  }).join('');
  if (current && savedRooms.some(room => String(room.id || '') === current)) {
    select.value = current;
  }
}

function updateServiceInfoFromService(service = {}) {
  const rooms = Array.isArray(service.venue_rooms) ? service.venue_rooms : [];
  const infoHalls = document.getElementById('serviceInfoHalls');
  const infoVenue = document.getElementById('serviceInfoVenue');
  const infoConcurrent = document.getElementById('serviceInfoConcurrent');
  const infoMinLeadDays = document.getElementById('serviceInfoMinLeadDays');
  const infoPackagePrice = document.getElementById('serviceInfoPackagePrice');
  const infoCustomizePrice = document.getElementById('serviceInfoCustomizePrice');
  const infoBorrowPackagePrice = document.getElementById('serviceInfoBorrowPackagePrice');
  const infoBorrowCustomizePrice = document.getElementById('serviceInfoBorrowCustomizePrice');
  const infoBuyPackagePrice = document.getElementById('serviceInfoBuyPackagePrice');
  const infoBuyCustomizePrice = document.getElementById('serviceInfoBuyCustomizePrice');

  if (infoHalls) infoHalls.textContent = String(rooms.length || document.querySelectorAll('.sd-hall-card').length);
  if (infoVenue && (service.venue_name || service.venue)) infoVenue.textContent = service.venue_name || service.venue;
  if (infoConcurrent) {
    const maxCapacity = rooms.reduce((max, room) => Math.max(max, Number(room.capacity || 0)), 0);
    infoConcurrent.textContent = String(maxCapacity || service.capacity || service.max_concurrent || infoConcurrent.textContent);
  }
  if (infoMinLeadDays && service.min_lead_days !== undefined) {
    infoMinLeadDays.textContent = Number(service.min_lead_days || 0) + ' days';
  }
  if (infoPackagePrice && (service.price_min || service.price)) {
    infoPackagePrice.textContent = formatMoney(service.price_min || service.price);
  }
  if (infoCustomizePrice && service.price_max !== undefined) {
    infoCustomizePrice.textContent = formatMoney(service.price_max);
  }

  // Update rental pricing in service info sidebar if present
  const rental = service.rental_pricing;
  if (rental) {
    const borrowPackage = rental.borrow_package_price ?? rental.borrow_price;
    const borrowCustomize = rental.borrow_customize_price ?? rental.borrow_price;
    const buyPackage = rental.buy_package_price ?? rental.buy_price;
    const buyCustomize = rental.buy_customize_price ?? rental.buy_price;
    if (infoBorrowPackagePrice && borrowPackage != null) {
      infoBorrowPackagePrice.textContent = borrowPackage > 0 ? formatMoney(borrowPackage) : '—';
    }
    if (infoBorrowCustomizePrice && borrowCustomize != null) {
      infoBorrowCustomizePrice.textContent = borrowCustomize > 0 ? formatMoney(borrowCustomize) : '—';
    }
    if (infoBuyPackagePrice && buyPackage != null) {
      infoBuyPackagePrice.textContent = buyPackage > 0 ? formatMoney(buyPackage) : '—';
    }
    if (infoBuyCustomizePrice && buyCustomize != null) {
      infoBuyCustomizePrice.textContent = buyCustomize > 0 ? formatMoney(buyCustomize) : '—';
    }
  }
}

function collectHalls() {
  return Array.from(document.querySelectorAll('.sd-hall-card')).map(card => {
    const start = card.querySelector('.hall-start')?.value || '09:00';
    const end = card.querySelector('.hall-end')?.value || '17:00';
    if (start >= end) {
      throw new Error('Hall end time must be later than start time.');
    }
    const priceMin = parseFloat(card.querySelector('.hall-price-min')?.value || card.querySelector('.hall-price')?.value || '0') || 0;
    const priceMax = parseFloat(card.querySelector('.hall-price-max')?.value || String(priceMin)) || priceMin;
    if (priceMax < priceMin) {
      throw new Error('Hall customize price must be greater than or equal to package price.');
    }
    if (priceMin <= 0 || priceMax <= 0) {
      throw new Error('Please fill in package price and customize price for every hall.');
    }
    const minLeadDaysRaw = card.querySelector('.hall-min-lead-days')?.value.trim();
    const minLeadDays = minLeadDaysRaw === '' ? null : Math.max(0, Math.min(365, parseInt(minLeadDaysRaw || '0', 10) || 0));
    return {
      id: card.querySelector('.hall-id')?.value || null,
      name: card.querySelector('.hall-name')?.value.trim() || '',
      capacity: parseInt(card.querySelector('.hall-capacity')?.value || '1', 10) || 1,
      price: priceMin,
      price_min: priceMin,
      price_max: priceMax,
      package_price: priceMin,
      customize_price: priceMax,
      start_time: start,
      end_time: end,
      min_lead_days: minLeadDays,
      photo_url: card.querySelector('.hall-photo-url')?.value || null
    };
  }).filter(room => room.name || room.capacity > 1 || room.price_min > 0 || room.price_max > 0);
}

document.getElementById('hallGrid')?.addEventListener('change', async event => {
  const input = event.target.closest('.hall-photo-input');
  if (!input || !input.files?.[0]) return;

  const card = input.closest('.sd-hall-card');
  const hidden = card?.querySelector('.hall-photo-url');
  const preview = card?.querySelector('.sd-hall-photo-preview');
  const label = card?.querySelector('.sd-hall-photo-btn span');

  try {
    const dataUrl = await fileToDataUrl(input.files[0]);
    if (hidden) hidden.value = dataUrl;
    if (preview) {
      preview.classList.remove('is-empty');
      preview.innerHTML = `<img src="${dataUrl}" alt="Hall photo preview">`;
    }
    if (label) label.textContent = 'Change photo';
  } catch (error) {
    showMessage('hallMessage', 'Could not read hall photo. Please try another image.');
  } finally {
    input.value = '';
  }
});

document.getElementById('saveHallsBtn')?.addEventListener('click', async () => {
  showMessage('hallMessage', '');
  try {
    const result = await jsonPost(urls.serviceUpdate, {
      ...serviceDetailConfig.servicePayloadBase,
      min_lead_days: currentServiceMinLeadDays(),
      rooms: collectHalls(),
      rooms_replace: true
    });
    const savedService = result.item || {};
    syncSavedHalls(savedService.venue_rooms || []);
    syncOverrideRoomOptions(savedService.venue_rooms || []);
    updateServiceInfoFromService(savedService);
    updateHallCount();
    showMessage('hallMessage', 'Halls saved.', true);
  } catch (error) {
    showMessage('hallMessage', error.message);
  }
});

function setOverrideButtonMode(isEditing) {
  const button = document.getElementById('saveOverrideBtn');
  if (!button) return;
  button.innerHTML = isEditing
    ? '<i class="ti ti-calendar-check" style="font-size:12px"></i> Update override'
    : '<i class="ti ti-calendar-plus" style="font-size:12px"></i> Add override';
}

function updateOverrideScopeState() {
  const scope = document.getElementById('overrideScope')?.value || 'service';
  const room = document.getElementById('overrideRoom');
  if (room) room.disabled = scope !== 'room';
}

function updateOverrideCount() {
  const count = document.querySelectorAll('[data-override-id]').length;
  const badge = document.getElementById('overrideCount');
  const empty = document.getElementById('overrideEmpty');
  if (badge) badge.textContent = count + ' saved';
  if (empty) empty.style.display = count ? 'none' : '';
}

function editOverride(row) {
  if (!row) return;
  const date = row.dataset.overrideDate || '';
  const type = row.dataset.overrideType || 'unavailable';
  const open = row.dataset.overrideOpen || '09:00';
  const close = row.dataset.overrideClose || '17:00';
  const reason = row.dataset.overrideReason || '';
  const scope = row.dataset.overrideScope || 'service';
  const roomId = row.dataset.overrideRoomId || '';

  const dateInput = document.getElementById('overrideDate');
  const typeInput = document.getElementById('overrideType');
  const openInput = document.getElementById('overrideOpen');
  const closeInput = document.getElementById('overrideClose');
  const reasonInput = document.getElementById('overrideReason');
  const scopeInput = document.getElementById('overrideScope');
  const roomInput = document.getElementById('overrideRoom');

  if (dateInput) dateInput.value = date;
  if (typeInput) typeInput.value = type;
  if (openInput) openInput.value = open;
  if (closeInput) closeInput.value = close;
  if (reasonInput) reasonInput.value = reason;
  if (scopeInput) scopeInput.value = scope;
  if (roomInput) roomInput.value = roomId;
  updateOverrideScopeState();

  document.querySelectorAll('.sd-override-item.is-editing').forEach(item => item.classList.remove('is-editing'));
  row.classList.add('is-editing');
  setOverrideButtonMode(true);
  showMessage('overrideMessage', 'Editing special date. Save to update it.', true);
  dateInput?.focus({ preventScroll: true });
}

window.editOverride = editOverride;

document.getElementById('overrideScope')?.addEventListener('change', updateOverrideScopeState);
updateOverrideScopeState();
updateOverrideCount();

document.getElementById('saveOverrideBtn')?.addEventListener('click', async () => {
  showMessage('overrideMessage', '');
  const scope = document.getElementById('overrideScope')?.value || 'service';
  const roomId = document.getElementById('overrideRoom')?.value || '';
  try {
    if (scope === 'room' && !roomId) {
      throw new Error('Please choose a hall.');
    }

    await jsonPost(scope === 'room' ? urls.roomOverrideSave : urls.overrideSave, {
      date: document.getElementById('overrideDate').value,
      type: document.getElementById('overrideType').value,
      open_time: document.getElementById('overrideOpen').value,
      close_time: document.getElementById('overrideClose').value,
      reason: document.getElementById('overrideReason').value,
      room_id: roomId
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
    document.querySelector(`[data-override-scope="service"][data-override-id="${overrideId}"]`)?.remove();
    updateOverrideCount();
    showMessage('overrideMessage', 'Special date deleted.', true);
  } catch (error) {
    showMessage('overrideMessage', error.message);
  }
}

async function deleteRoomOverride(overrideId) {
  if (!overrideId || !confirm('Delete this hall override?')) return;
  showMessage('overrideMessage', '');
  try {
    await jsonPost(urls.roomOverrideDelete + encodeURIComponent(overrideId));
    document.querySelector(`[data-override-scope="room"][data-override-id="${overrideId}"]`)?.remove();
    updateOverrideCount();
    showMessage('overrideMessage', 'Hall special date deleted.', true);
  } catch (error) {
    showMessage('overrideMessage', error.message);
  }
}

window.deleteOverride = deleteOverride;
window.deleteRoomOverride = deleteRoomOverride;

// ── FOCUSED SERVICE WORKSPACE ──────────────────────────────────
const serviceWorkspaceTabs = Array.from(document.querySelectorAll('[data-service-tab]'));
const serviceWorkspacePanels = Array.from(document.querySelectorAll('[data-service-panel]'));
const serviceWorkspaceStorageKey = `supplierServiceTab:${window.location.pathname}`;

function activateServiceWorkspaceTab(tabName, moveFocus = false) {
  const requestedTab = serviceWorkspaceTabs.find(tab => tab.dataset.serviceTab === tabName);
  const activeTab = requestedTab || serviceWorkspaceTabs[0];
  if (!activeTab) return;

  const activeName = activeTab.dataset.serviceTab;
  serviceWorkspaceTabs.forEach(tab => {
    const isActive = tab === activeTab;
    tab.classList.toggle('is-active', isActive);
    tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
    tab.tabIndex = isActive ? 0 : -1;
  });
  serviceWorkspacePanels.forEach(panel => {
    const isActive = panel.dataset.servicePanel === activeName;
    panel.classList.toggle('is-active', isActive);
    panel.hidden = !isActive;
  });

  try {
    window.sessionStorage.setItem(serviceWorkspaceStorageKey, activeName);
  } catch (error) {
    // Storage may be unavailable in privacy-restricted browsers.
  }

  if (moveFocus) activeTab.focus();
}

serviceWorkspaceTabs.forEach((tab, index) => {
  tab.addEventListener('click', () => activateServiceWorkspaceTab(tab.dataset.serviceTab));
  tab.addEventListener('keydown', event => {
    if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) return;
    event.preventDefault();
    let nextIndex = index;
    if (event.key === 'ArrowLeft') nextIndex = (index - 1 + serviceWorkspaceTabs.length) % serviceWorkspaceTabs.length;
    if (event.key === 'ArrowRight') nextIndex = (index + 1) % serviceWorkspaceTabs.length;
    if (event.key === 'Home') nextIndex = 0;
    if (event.key === 'End') nextIndex = serviceWorkspaceTabs.length - 1;
    activateServiceWorkspaceTab(serviceWorkspaceTabs[nextIndex].dataset.serviceTab, true);
  });
});

document.querySelector('[data-review-attention]')?.addEventListener('click', event => {
  const nextTab = event.currentTarget.dataset.reviewAttention || 'overview';
  activateServiceWorkspaceTab(nextTab);
  document.querySelector('.sd-workspace-tabs')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
});

let initialServiceWorkspaceTab = 'overview';
try {
  initialServiceWorkspaceTab = window.sessionStorage.getItem(serviceWorkspaceStorageKey) || 'overview';
} catch (error) {
  // Use the overview when session storage is unavailable.
}
activateServiceWorkspaceTab(initialServiceWorkspaceTab);
