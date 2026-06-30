<?php
$dashboardSearchPlaceholder = $dashboardSearchPlaceholder ?? 'Search bookings, services, payments...';
$dashboardSearchValue = $dashboardSearchValue ?? ($_GET['search'] ?? '');
$dashboardSearchEndpoint = $dashboardSearchEndpoint ?? URLROOT . '/supplier/globalSearch';
?>
<div class="dashboard-global-search relative" id="globalSearchWrap">
    <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-app-header-muted" aria-hidden="true"></i>
    <input
        type="text"
        id="dashboard-search"
        value="<?= htmlspecialchars((string)$dashboardSearchValue, ENT_QUOTES, 'UTF-8') ?>"
        placeholder="<?= htmlspecialchars($dashboardSearchPlaceholder, ENT_QUOTES, 'UTF-8') ?>"
        aria-label="<?= htmlspecialchars($dashboardSearchPlaceholder, ENT_QUOTES, 'UTF-8') ?>"
        aria-keyshortcuts="Meta+K Control+K"
        autocomplete="off"
        class="h-10 w-[260px] rounded-xl border border-app-border bg-white/80 pl-10 pr-14 text-sm text-app-text shadow-sm outline-none transition focus:border-app-focus focus:bg-white focus:ring-2 focus:ring-app-ring"
    >
    <span class="dashboard-search-shortcut pointer-events-none absolute right-2 top-1/2 inline-flex -translate-y-1/2 items-center gap-1 rounded-md border border-app-border bg-app-keycap px-2 py-1 text-[10px] font-semibold text-app-header-muted" aria-hidden="true">
        <i data-lucide="command" class="h-3 w-3"></i>
        K
    </span>

    <!-- Live search dropdown -->
    <div id="gsDropdown" class="absolute right-0 top-full z-50 mt-2 hidden w-[420px] overflow-hidden rounded-xl border border-app-border/70 bg-white shadow-xl">
        <!-- Loading -->
        <div id="gsLoading" class="hidden px-5 py-6 text-center">
            <div class="mx-auto h-5 w-5 animate-spin rounded-full border-2 border-app-border border-t-app-primary"></div>
        </div>

        <!-- No results -->
        <div id="gsNoResults" class="hidden px-5 py-8 text-center">
            <p class="text-xs font-medium text-app-muted">No results found</p>
        </div>

        <!-- Results -->
        <div id="gsResults" class="max-h-[420px] overflow-y-auto overscroll-contain hidden py-1"></div>
    </div>
</div>

<style>
    .gs-section + .gs-section { border-top: 1px solid var(--color-app-border, #e5e7eb); }
    .gs-section-head {
        padding: 7px 16px 3px;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .1em;
        text-transform: uppercase;
        color: var(--color-app-header-muted, #9ca3af);
    }
    .gs-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 16px;
        cursor: pointer;
        text-decoration: none;
        transition: background 100ms;
    }
    .gs-item:hover, .gs-item.is-active {
        background: var(--color-app-surface, #f9f5f0);
    }
    .gs-item-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .gs-item-icon svg { width: 14px; height: 14px; }
    .gs-item-title {
        font-size: 13px;
        font-weight: 600;
        color: var(--color-app-text, #1f2937);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .gs-item-sub {
        font-size: 11px;
        color: var(--color-app-muted, #6b7280);
        margin-top: 1px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .gs-item-arrow {
        width: 12px;
        height: 12px;
        flex-shrink: 0;
        color: var(--color-app-header-muted, #9ca3af);
        margin-left: auto;
        opacity: 0;
        transition: opacity 100ms;
    }
    .gs-item:hover .gs-item-arrow,
    .gs-item.is-active .gs-item-arrow { opacity: 1; }
</style>

<script>
(function() {
    var wrap       = document.getElementById('globalSearchWrap');
    var input      = document.getElementById('dashboard-search');
    var dropdown   = document.getElementById('gsDropdown');
    var loadingEl  = document.getElementById('gsLoading');
    var noResults  = document.getElementById('gsNoResults');
    var resultsEl  = document.getElementById('gsResults');

    var debounceTimer = null;
    var currentQuery  = '';
    var activeIdx     = -1;
    var allUrls       = [];

    var META = {
        booking:      { label: 'Bookings',       color: 'bg-emerald-50 text-emerald-600', icon: 'calendar-check' },
        service:      { label: 'Services',       color: 'bg-blue-50 text-blue-600',       icon: 'briefcase-business' },
        supplier:     { label: 'Suppliers',      color: 'bg-orange-50 text-orange-600',   icon: 'store' },
        customer:     { label: 'Customers',      color: 'bg-teal-50 text-teal-600',       icon: 'user' },
        payment:      { label: 'Payments',       color: 'bg-amber-50 text-amber-600',     icon: 'banknote' },
        refund:       { label: 'Refunds',        color: 'bg-rose-50 text-rose-600',       icon: 'wallet' },
        review:       { label: 'Reviews',        color: 'bg-yellow-50 text-yellow-600',   icon: 'star' },
        package:      { label: 'Packages',       color: 'bg-indigo-50 text-indigo-600',   icon: 'package' },
        notification: { label: 'Notifications',  color: 'bg-purple-50 text-purple-600',   icon: 'bell' },
    };
    var TYPE_ORDER = ['booking','service','supplier','customer','payment','refund','review','package','notification'];

    function openDropdown()  { dropdown.classList.remove('hidden'); }
    function closeDropdown() { dropdown.classList.add('hidden'); resultsEl.innerHTML = ''; allUrls = []; activeIdx = -1; }

    /* --- input events --- */
    input.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        activeIdx = -1;
        var q = input.value.trim();
        if (q.length < 2) { closeDropdown(); return; }
        debounceTimer = setTimeout(function() { runSearch(q); }, 220);
    });

    input.addEventListener('focus', function() {
        var q = input.value.trim();
        if (q.length >= 2 && resultsEl.children.length) openDropdown();
    });

    /* --- keyboard nav --- */
    input.addEventListener('keydown', function(e) {
        if (!allUrls.length && e.key !== 'Escape') return;
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            setActive(Math.min(activeIdx + 1, allUrls.length - 1));
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            setActive(Math.max(activeIdx - 1, -1));
        } else if (e.key === 'Enter' && activeIdx >= 0) {
            e.preventDefault();
            window.location.href = allUrls[activeIdx];
        } else if (e.key === 'Escape') {
            closeDropdown();
            input.blur();
        }
    });

    /* --- Cmd+K focus --- */
    document.addEventListener('keydown', function(e) {
        if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
            e.preventDefault();
            input.focus();
            input.select();
        }
    });

    /* --- click outside closes --- */
    document.addEventListener('click', function(e) {
        if (!wrap.contains(e.target)) closeDropdown();
    });

    function setActive(idx) {
        var items = resultsEl.querySelectorAll('.gs-item');
        items.forEach(function(el) { el.classList.remove('is-active'); });
        activeIdx = idx;
        if (idx >= 0 && items[idx]) {
            items[idx].classList.add('is-active');
            items[idx].scrollIntoView({ block: 'nearest' });
        }
    }

    /* --- fetch --- */
    function runSearch(q) {
        currentQuery = q;
        showState('loading');
        openDropdown();

        fetch('<?= $dashboardSearchEndpoint ?>?q=' + encodeURIComponent(q), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(r) { return r.json(); })
        .then(function(d) {
            if (d.query !== currentQuery) return;
            if (!d.results || !d.results.length) { showState('empty'); return; }
            render(d.results);
        })
        .catch(function() { showState('empty'); });
    }

    function showState(s) {
        loadingEl.classList.toggle('hidden', s !== 'loading');
        noResults.classList.toggle('hidden', s !== 'empty');
        resultsEl.classList.toggle('hidden', s !== 'results');
        if (s !== 'results') { resultsEl.innerHTML = ''; allUrls = []; }
    }

    /* --- render --- */
    function render(results) {
        showState('results');
        resultsEl.innerHTML = '';
        allUrls = [];
        activeIdx = -1;

        var grouped = {};
        results.forEach(function(r) { (grouped[r.type] = grouped[r.type] || []).push(r); });

        TYPE_ORDER.forEach(function(type) {
            var items = grouped[type];
            if (!items || !items.length) return;
            var m = META[type] || { label: type, color: 'bg-gray-50 text-gray-600', icon: 'circle' };

            var sec = document.createElement('div');
            sec.className = 'gs-section';
            sec.innerHTML = '<div class="gs-section-head">' + esc(m.label) + '</div>';

            items.forEach(function(item) {
                allUrls.push(item.url);
                var a = document.createElement('a');
                a.href = item.url;
                a.className = 'gs-item';
                a.innerHTML =
                    '<div class="gs-item-icon ' + esc(m.color) + '"><i data-lucide="' + esc(item.icon || m.icon) + '"></i></div>' +
                    '<div style="min-width:0;flex:1">' +
                        '<div class="gs-item-title">' + esc(item.title) + '</div>' +
                        (item.subtitle ? '<div class="gs-item-sub">' + esc(item.subtitle) + '</div>' : '') +
                    '</div>' +
                    '<svg class="gs-item-arrow" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 4l4 4-4 4"/></svg>';
                sec.appendChild(a);
            });
            resultsEl.appendChild(sec);
        });

        if (window.lucide) lucide.createIcons();
    }

    function esc(s) { if (!s) return ''; var d = document.createElement('div'); d.appendChild(document.createTextNode(s)); return d.innerHTML; }
})();
</script>
