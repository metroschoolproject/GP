<?php
$dashboardCardClass = 'supplier-admin-card p-5';
$dashboardCompactCardClass = 'supplier-admin-card p-5';
$dashboardMenuButtonClass = 'supplier-admin-control flex h-8 items-center gap-1.5 rounded-xl border px-3 text-xs font-semibold shadow-sm hover:bg-stone-50 focus:outline-none focus:ring-2 focus:ring-rose-100';
$dashboardMenuItemClass = 'flex w-full items-center rounded-lg px-3 py-1.5 text-left text-xs font-medium text-stone-600 hover:bg-rose-50 hover:text-[#6d4c5b]';
$dashboardMenuItemActiveClass = 'flex w-full items-center rounded-lg bg-rose-50 px-3 py-1.5 text-left text-xs font-semibold text-[#6d4c5b]';
$dashboardFilterTabClass = 'filter-tab rounded-full px-4 py-1.5 text-xs font-semibold border border-app-border transition-all';
$dashboardTableHeadClass = 'text-left py-2 px-2 text-[10px] uppercase tracking-wider text-app-muted font-semibold whitespace-nowrap';
?>

<style>
  .supplier-dashboard-overview {
    --supplier-admin-bg: #fbfbf9;
    --supplier-admin-card: #FFFFFF;
    --supplier-admin-border: #ead8c7;
    --supplier-admin-text: #6d4c5b;
    --supplier-admin-muted: #A8A29E;
    --supplier-admin-soft: #FAFAF9;
    --supplier-admin-accent: #6d4c5b;
    font-family: 'DM Sans', sans-serif;
    font-variant-numeric: tabular-nums;
  }
  .supplier-dashboard-overview .supplier-admin-card {
    border: 1px solid var(--supplier-admin-border);
    border-radius: 1.2rem;
    background: var(--supplier-admin-card);
    box-shadow: 0 1px 2px rgba(28, 25, 23, 0.05);
    transition: box-shadow .18s ease;
  }
  .supplier-dashboard-overview .supplier-admin-card:hover {
    box-shadow: 0 4px 12px rgba(28, 25, 23, 0.08);
  }
  .supplier-dashboard-overview .supplier-admin-page-title {
    margin: 0;
    color: #6d4c5b;
    font-family: "Playfair Display", serif;
    font-size: clamp(27px, 2.5vw, 36px);
    font-weight: 650;
    letter-spacing: -.025em;
    line-height: 1.08;
  }
  .supplier-dashboard-overview .supplier-admin-page-copy {
    margin-top: .4rem;
    color: #7b5c69;
    font-size: 12px;
    font-weight: 500;
  }
  .supplier-dashboard-overview .supplier-admin-kicker,
  .supplier-dashboard-overview .supplier-admin-stat-label {
    color: var(--supplier-admin-muted);
    font-size: 11px;
    font-weight: 650;
    letter-spacing: .01em;
  }
  .supplier-dashboard-overview .supplier-admin-kicker {
    margin-bottom: .25rem;
  }
  .supplier-dashboard-overview .supplier-admin-icon {
    display: flex;
    width: 2rem;
    height: 2rem;
    align-items: center;
    justify-content: center;
    margin-bottom: .75rem;
    border-radius: .5rem;
  }
  .supplier-dashboard-overview .supplier-admin-section-title {
    color: var(--supplier-admin-text);
    font-size: 13px;
    font-weight: 750;
    letter-spacing: -.015em;
  }
  .supplier-dashboard-overview .supplier-admin-control {
    border-color: var(--supplier-admin-border) !important;
    background: #FFFFFF !important;
    color: var(--supplier-admin-accent) !important;
  }
  .supplier-dashboard-overview .filter-tab {
    border-color: var(--supplier-admin-border) !important;
    background: var(--supplier-admin-soft);
    color: #78716C;
  }
  .supplier-dashboard-overview .filter-tab.bg-app-primary {
    border-color: var(--supplier-admin-accent) !important;
    background: var(--supplier-admin-accent) !important;
    color: #FFFFFF !important;
  }
  .supplier-dashboard-overview table thead {
    background: #FAFAF9;
  }
  .supplier-dashboard-overview table thead th {
    color: var(--supplier-admin-muted) !important;
    font-size: 10px;
    font-weight: 750;
    letter-spacing: .055em;
    text-transform: uppercase;
  }
  .supplier-dashboard-overview table tbody tr {
    border-color: var(--supplier-admin-border) !important;
    transition: background .1s ease;
  }
  .supplier-dashboard-overview table tbody tr:hover {
    background: var(--supplier-admin-soft) !important;
  }
  .supplier-dashboard-overview #weddingBookingsList > div {
    border: 1px solid transparent;
    border-radius: .75rem;
    background: var(--supplier-admin-soft);
    transition: all .12s ease;
  }
  .supplier-dashboard-overview #weddingBookingsList > div:hover {
    border-color: var(--supplier-admin-border);
    background: #F4F1EE;
  }
  .supplier-dashboard-overview canvas {
    max-width: 100%;
  }
  .supplier-dashboard-overview .supplier-admin-layout-grid {
    display: grid;
    grid-template-columns: repeat(12, minmax(0, 1fr));
    gap: .75rem;
    align-items: stretch;
  }
  .supplier-dashboard-overview .supplier-admin-primary {
    display: contents;
  }
  .supplier-dashboard-overview .supplier-admin-kpis {
    grid-column: 1 / -1;
    grid-row: 1;
  }
  .supplier-dashboard-overview .supplier-admin-charts {
    grid-column: 1 / -1;
    grid-row: 2;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: .75rem;
  }
  .supplier-dashboard-overview .supplier-admin-bottom-row {
    grid-column: 1 / -1;
    grid-row: 3;
    display: grid;
    grid-template-columns: 2fr 3fr;
    gap: .75rem;
  }
  .supplier-dashboard-overview .supplier-admin-calendar {
    min-width: 0;
  }
  .supplier-dashboard-overview .supplier-admin-upcoming {
    min-width: 0;
  }
  .supplier-dashboard-overview .supplier-admin-upcoming #weddingBookingsList {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: .5rem;
    max-height: none;
  }
  .supplier-dashboard-overview .chart-container-revenue {
    min-height: 280px;
  }
  .supplier-dashboard-overview .chart-container-booking {
    min-height: 280px;
  }
  /* Period dropdown */
  .supplier-dashboard-overview .period-dropdown-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    height: 2rem;
    border-radius: 0.75rem;
    border: 1px solid var(--supplier-admin-border);
    background: #fff;
    padding: 0 0.75rem;
    font-size: 12px;
    font-weight: 600;
    color: var(--supplier-admin-accent);
    cursor: pointer;
    transition: all .15s ease;
    white-space: nowrap;
    box-shadow: 0 1px 2px rgba(28,25,23,0.05);
  }
  .supplier-dashboard-overview .period-dropdown-btn:hover {
    background: #f5f0ec;
    border-color: #d4c4b8;
  }
  .supplier-dashboard-overview .period-dropdown-btn svg {
    transition: transform .15s ease;
  }
  .supplier-dashboard-overview .period-dropdown-menu {
    position: absolute;
    right: 0;
    top: calc(100% + 6px);
    z-index: 30;
    width: 180px;
    border-radius: 0.75rem;
    border: 1px solid var(--supplier-admin-border);
    background: #fff;
    padding: 0.375rem;
    box-shadow: 0 4px 16px rgba(28,25,23,0.1);
  }
  @keyframes supplierAdminFadeUp {
    from { opacity: 0; transform: translateY(8px); }
    to { opacity: 1; transform: translateY(0); }
  }
  .supplier-dashboard-overview .supplier-admin-animate {
    animation: supplierAdminFadeUp .35s ease both;
  }
  .scroll-hint { position: relative; }
  .scroll-hint.can-scroll::after { content: ''; position: absolute; top: 0; right: 0; bottom: 0; width: 32px; background: linear-gradient(to right, transparent, rgba(252,248,245,0.9)); pointer-events: none; z-index: 2; }
  @media (max-width: 1100px) {
    .supplier-dashboard-overview .supplier-admin-bottom-row {
      grid-template-columns: 1fr;
    }
    .supplier-dashboard-overview .supplier-admin-upcoming #weddingBookingsList {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }
  }
  @media (max-width: 900px) {
    .supplier-dashboard-overview .supplier-admin-layout-grid {
      grid-template-columns: 1fr;
    }
    .supplier-dashboard-overview .supplier-admin-kpis,
    .supplier-dashboard-overview .supplier-admin-charts,
    .supplier-dashboard-overview .supplier-admin-bottom-row {
      grid-column: 1;
      grid-row: auto;
    }
    .supplier-dashboard-overview .supplier-admin-charts {
      grid-template-columns: 1fr;
    }
    .supplier-dashboard-overview .supplier-admin-bottom-row {
      grid-template-columns: 1fr;
    }
  }
  @media (max-width: 640px) {
    .supplier-dashboard-overview { padding-left: 0.75rem !important; padding-right: 0.75rem !important; padding-top: 1rem !important; }
    .supplier-dashboard-overview .rounded-card { padding: 0.75rem !important; }
    .supplier-dashboard-overview .filter-tab { padding-left: 0.5rem; padding-right: 0.5rem; font-size: 9px; }
    .supplier-dashboard-overview .overflow-x-auto { margin-left: -0.75rem; margin-right: -0.75rem; }
    .supplier-dashboard-overview .overflow-x-auto table th,
    .supplier-dashboard-overview .overflow-x-auto table td { padding-left: 0.5rem !important; padding-right: 0.5rem !important; }
    .supplier-dashboard-overview .text-2xl { font-size: 1.25rem !important; }
    .supplier-dashboard-overview h3.text-sm { font-size: 0.8125rem !important; }
    .supplier-dashboard-overview .space-y-2 > div { padding: 0.5rem !important; }
    .supplier-dashboard-overview .gap-3 { gap: 0.75rem !important; }
    .supplier-dashboard-overview .gap-4 { gap: 0.75rem !important; }
    .supplier-dashboard-overview .px-4 { padding-left: 0.75rem !important; padding-right: 0.75rem !important; }
    .supplier-dashboard-overview .py-5 { padding-top: 0.75rem !important; padding-bottom: 0.75rem !important; }
    .supplier-dashboard-overview .chart-container-revenue { min-height: 200px !important; }
    .supplier-dashboard-overview .chart-container-booking { min-height: 200px !important; }
    .supplier-dashboard-overview .supplier-admin-upcoming #weddingBookingsList {
      grid-template-columns: 1fr;
    }
  }
</style>

<div class="supplier-dashboard-overview mx-auto max-w-[1600px] px-5 py-6 text-[13px] antialiased">

        <header class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="supplier-admin-kicker">Supplier workspace</p>
                <h1 class="supplier-admin-page-title">Business overview</h1>
                <p class="supplier-admin-page-copy">Revenue, bookings, availability, and payments at a glance.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <div class="relative">
                    <button id="periodDropdownBtn" type="button" class="period-dropdown-btn" onclick="togglePeriodDropdown()">
                        <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <span id="periodDropdownLabel">This Year</span>
                        <svg id="periodDropdownChevron" class="h-3 w-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div id="periodDropdownMenu" class="invisible opacity-0 period-dropdown-menu transition-all duration-150">
                        <button type="button" data-range="month" class="<?= $dashboardMenuItemClass ?>" onclick="selectPeriod('month', 'This Month')">This Month</button>
                        <button type="button" data-range="6months" class="<?= $dashboardMenuItemClass ?>" onclick="selectPeriod('6months', 'Last 6 Months')">Last 6 Months</button>
                        <button type="button" data-range="year" class="<?= $dashboardMenuItemActiveClass ?>" onclick="selectPeriod('year', 'This Year')">This Year</button>
                        <button type="button" data-range="all" class="<?= $dashboardMenuItemClass ?>" onclick="selectPeriod('all', 'All Time')">All Time</button>
                    </div>
                </div>
                <a href="<?= URLROOT ?>/supplier/services"
                   class="supplier-admin-control inline-flex h-8 items-center gap-1.5 rounded-xl border px-3 text-xs font-semibold shadow-sm transition hover:bg-stone-50">
                    <i data-lucide="briefcase-business" class="h-3.5 w-3.5"></i>
                    Manage services
                </a>
                <a href="<?= URLROOT ?>/supplier/calendar"
                   class="supplier-admin-control inline-flex h-8 items-center gap-1.5 rounded-xl border px-3 text-xs font-semibold shadow-sm transition hover:bg-stone-50">
                    <i data-lucide="calendar-days" class="h-3.5 w-3.5"></i>
                    Open calendar
                </a>
            </div>
        </header>

        <!-- MAIN LAYOUT: left col (stats + charts) | right col (calendar + events) -->
        <section class="supplier-admin-layout-grid mb-4">
        <!-- LEFT: stat cards + charts -->
        <div class="supplier-admin-primary">
        <div class="supplier-admin-kpis grid grid-cols-1 gap-3 sm:grid-cols-3">

            <!-- Total Revenue -->
            <div class="<?= $dashboardCardClass ?> supplier-admin-animate" style="animation-delay:.07s">
                <div class="supplier-admin-icon" style="background:#fff1f2;color:#be123c">
                    <svg class="h-4 w-4"  xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3.85 8.62a4 4 0 0 1 4.78-4.77 4 4 0 0 1 6.74 0 4 4 0 0 1 4.78 4.78 4 4 0 0 1 0 6.74 4 4 0 0 1-4.77 4.78 4 4 0 0 1-6.75 0 4 4 0 0 1-4.78-4.77 4 4 0 0 1 0-6.76Z"/><path d="M12 7v10"/><path d="M14.35 9A2 2 0 0 0 12 7.5h-1a2 2 0 0 0 0 4h2a2 2 0 0 1 0 4h-1a2 2 0 0 1-2.35-1.5"/></svg>
                </div>
                <p class="supplier-admin-stat-label">Total Revenue</p>
                <h2 id="totalRevenue" class="mt-1.5 text-2xl font-bold tracking-tighter" style="color:#6d4c5b">$63,400</h2>
                <div class="mt-4 grid grid-cols-2 gap-3 border-t pt-3" style="border-color:var(--supplier-admin-border)">
                    <div>
                        <p class="supplier-admin-stat-label">Paid out</p>
                        <p id="paidRevenue" class="mt-1 text-base font-bold" style="color:#07825f">$0</p>
                    </div>
                    <div>
                        <p class="supplier-admin-stat-label">Pending</p>
                        <p id="pendingRevenue" class="mt-1 text-base font-bold" style="color:#b45309">$0</p>
                    </div>
                </div>
            </div>

            <!-- Total Bookings -->
            <div class="<?= $dashboardCardClass ?> supplier-admin-animate" style="animation-delay:.14s">
                <div class="supplier-admin-icon" style="background:#f0f9ff;color:#0284c7">
                    <svg class="h-4 w-4"  xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><path d="m9 16 2 2 4-4"/></svg>
                </div>
                <p class="supplier-admin-stat-label">Total Bookings</p>
                <h2 id="totalBookings" class="mt-1.5 text-2xl font-bold tracking-tighter" style="color:#6d4c5b">317</h2>
                <div class="mt-4 grid grid-cols-2 gap-3 border-t pt-3" style="border-color:var(--supplier-admin-border)">
                    <div>
                        <p class="supplier-admin-stat-label">Confirmed</p>
                        <p id="confirmedBookings" class="mt-1 text-lg font-bold" style="color:#07825f">217</p>
                    </div>
                    <div>
                        <p class="supplier-admin-stat-label">Cancelled</p>
                        <p id="cancelledBookings" class="mt-1 text-lg font-bold" style="color:#c73434">33</p>
                    </div>
                </div>
            </div>

            <div class="<?= $dashboardCardClass ?> supplier-admin-animate" style="animation-delay:.21s">
                <div class="supplier-admin-icon" style="background:#fdf2f8;color:#9d174d">
                    <i data-lucide="store" class="h-4 w-4"></i>
                </div>
                <p class="supplier-admin-stat-label">Service portfolio</p>
                <h2 class="mt-1.5 text-2xl font-bold tracking-tighter" style="color:#6d4c5b"><?= number_format((int)($dashboardData['stats']['total_services'] ?? 0)) ?></h2>
                <div class="mt-4 grid grid-cols-2 gap-3 border-t pt-3" style="border-color:var(--supplier-admin-border)">
                    <div>
                        <p class="supplier-admin-stat-label">Rating</p>
                        <p class="mt-1 text-lg font-bold" style="color:#b45309"><?= number_format((float)($dashboardData['stats']['average_rating'] ?? 0), 1) ?></p>
                    </div>
                    <div>
                        <p class="supplier-admin-stat-label">Upcoming</p>
                        <p class="mt-1 text-lg font-bold" style="color:#6d4c5b"><?= number_format(count($dashboardData['upcomingBookings'] ?? [])) ?></p>
                    </div>
                </div>
            </div>

            </div><!-- end stat cards inner -->

            <!-- CHARTS: two columns -->
            <div class="supplier-admin-charts">

                <!-- Revenue Chart -->
                <div class="<?= $dashboardCardClass ?> supplier-admin-animate flex flex-col" style="animation-delay:.30s">
                    <div class="mb-3 flex items-start justify-between">
                        <div>
                            <h3 class="supplier-admin-section-title">Revenue trend</h3>
                            <p class="mt-0.5 text-[11px]" style="color:#A8A29E">Revenue performance over time</p>
                        </div>
                        <span id="revenuePeakBadge" class="self-start inline-flex items-center gap-1 bg-app-danger-soft text-app-danger font-bold text-[10px] px-2.5 py-1 rounded-full border border-app-border">—</span>
                    </div>
                    <div class="chart-container-revenue flex-1">
                        <canvas id="revenueChart"></canvas>
                    </div>
                    <div class="mt-3 flex flex-wrap items-center gap-4 border-t border-app-panel-border pt-2">
                        <div class="flex items-center gap-1.5">
                            <span class="inline-block h-2.5 w-6 rounded-full bg-app-primary/15 ring-1 ring-app-primary/30"></span>
                            <span class="text-[10px] font-medium text-app-secondary">Revenue (filled)</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="inline-block h-2.5 w-2.5 rounded-full bg-app-danger-soft ring-1 ring-app-primary"></span>
                            <span class="text-[10px] font-medium text-app-secondary">Peak</span>
                        </div>
                    </div>
                </div>

                <!-- Booking Trends -->
                <div class="<?= $dashboardCardClass ?> supplier-admin-animate flex flex-col" style="animation-delay:.37s">
                    <div class="mb-3 flex items-start justify-between">
                        <div>
                            <h3 class="supplier-admin-section-title">Booking trend</h3>
                            <p class="mt-0.5 text-[11px]" style="color:#A8A29E">Booking volume over time</p>
                        </div>
                        <span id="bookingPeakBadge" class="self-start inline-flex items-center gap-1 bg-app-danger-soft text-app-danger font-bold text-[10px] px-2.5 py-1 rounded-full border border-app-border">—</span>
                    </div>
                    <div class="chart-container-booking flex-1">
                        <canvas id="bookingChart"></canvas>
                    </div>
                    <div class="mt-3 flex flex-wrap items-center gap-4 border-t border-app-panel-border pt-2">
                        <div class="flex items-center gap-1.5">
                            <span class="inline-block h-2.5 w-2.5 rounded-sm bg-app-ring ring-1 ring-app-panel-border"></span>
                            <span class="text-[10px] font-medium text-app-secondary">Bookings</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="inline-block h-2.5 w-2.5 rounded-sm bg-app-danger-soft"></span>
                            <span class="text-[10px] font-medium text-app-secondary">Peak</span>
                        </div>
                    </div>
                </div>
            </div><!-- end charts -->

            <!-- BOTTOM ROW: Calendar + Upcoming Events side by side -->
            <div class="supplier-admin-bottom-row">

                <!-- Calendar -->
                <div class="<?= $dashboardCardClass ?> supplier-admin-calendar supplier-admin-animate" style="animation-delay:.42s">
                    <div class="mb-3 flex items-center justify-between">
                        <button id="calPrev" class="flex h-7 w-7 items-center justify-center rounded-lg hover:bg-app-soft transition-colors">
                            <svg class="h-3.5 w-3.5 text-app-secondary"  xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                        </button>
                        <h3 class="supplier-admin-section-title" id="calendarMonthLabel">January</h3>
                        <button id="calNext" class="flex h-7 w-7 items-center justify-center rounded-lg hover:bg-app-soft transition-colors">
                            <svg class="h-3.5 w-3.5 text-app-secondary"  xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                        </button>
                    </div>
                    <div class="grid grid-cols-7 gap-1 mb-1.5 text-center text-[10px] font-semibold text-app-muted">
                        <span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span><span>Su</span>
                    </div>
                    <div id="availabilityCalendarGrid" class="grid grid-cols-7 gap-1"></div>

                    <!-- Legend -->
                    <div class="mt-3 pt-3 border-t border-app-panel-border grid grid-cols-2 gap-1.5">
                        <div class="flex items-center gap-1.5">
                            <span class="inline-block h-2.5 w-2.5 rounded-sm bg-app-text shrink-0"></span>
                            <span class="text-[10px] text-app-secondary">Today</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="inline-block h-2.5 w-2.5 rounded-sm bg-app-danger-soft shrink-0"></span>
                            <span class="text-[10px] text-app-secondary">Booked</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="inline-block h-2.5 w-2.5 rounded-sm bg-app-surface shrink-0"></span>
                            <span class="text-[10px] text-app-secondary">Pending</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="inline-block h-2.5 w-2.5 rounded-sm bg-app-soft shrink-0"></span>
                            <span class="text-[10px] text-app-secondary">Available</span>
                        </div>
                    </div>
                    <!-- Day tooltip -->
                    <div id="calTooltip" class="hidden mt-2 rounded-xl bg-app-soft border border-app-panel-border p-2.5 text-[10px] text-app-secondary transition-all">
                        <p id="calTooltipText" class="font-medium"></p>
                    </div>
                </div>

                <!-- Upcoming Events -->
                <div class="<?= $dashboardCardClass ?> supplier-admin-upcoming supplier-admin-animate flex flex-col" style="animation-delay:.49s">
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="supplier-admin-section-title">Upcoming events</h3>
                        <button class="text-xs font-medium text-app-primary hover:text-app-accent">View all</button>
                    </div>
                    <div id="weddingBookingsList" class="space-y-2 flex-1 overflow-y-auto">
                        <!-- rendered by JS -->
                    </div>
                </div>
            </div><!-- end bottom row -->
        </section>

        <!-- PAYMENT STATUS -->
        <section id="payment-status" class="mb-4" style="scroll-margin-top:76px">
            <div class="<?= $dashboardCardClass ?> supplier-admin-animate" style="animation-delay:.56s">
                <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <h3 class="supplier-admin-section-title text-sm">Payment status</h3>
                        <p class="mt-0.5 text-[11px]" style="color:#A8A29E">Customer payments linked to your bookings</p>
                    </div>
                    <div class="relative">
                        <button id="paymentFilterBtn" type="button" aria-expanded="false"
                            class="<?= $dashboardMenuButtonClass ?>">
                            <svg class="h-3 w-3 text-app-muted"  xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            <span id="paymentFilterLabel">This week</span>
                            <svg class="h-3 w-3 text-app-muted transition-transform" id="paymentFilterChevron" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                        </button>
                        <div id="paymentFilterMenu" class="invisible absolute right-0 sm:right-0 left-auto sm:left-auto top-[calc(100%+6px)] z-30 w-36 origin-top scale-y-95 rounded-xl border border-app-border bg-app-input p-1.5 opacity-0 shadow-lg transition-all duration-150">
                            <button type="button" data-payment-filter="today" class="<?= $dashboardMenuItemClass ?>">Today</button>
                            <button type="button" data-payment-filter="week" class="<?= $dashboardMenuItemActiveClass ?>">This week</button>
                            <button type="button" data-payment-filter="month" class="<?= $dashboardMenuItemClass ?>">This month</button>
                            <button type="button" data-payment-filter="year" class="<?= $dashboardMenuItemClass ?>">This year</button>
                        </div>
                    </div>
                </div>

                <!-- Status Filter Tabs -->
                <div class="flex flex-wrap gap-2 mb-4 mt-3">
                    <button onclick="filterPayments('all')" data-status="all" class="<?= $dashboardFilterTabClass ?> bg-app-primary text-app-white">All</button>
                    <button onclick="filterPayments('pending')" data-status="pending" class="<?= $dashboardFilterTabClass ?> text-app-secondary bg-app-soft">Pending</button>
                    <button onclick="filterPayments('full paid')" data-status="full paid" class="<?= $dashboardFilterTabClass ?> text-app-secondary bg-app-soft">Full Paid</button>
                    <button onclick="filterPayments('half paid')" data-status="half paid" class="<?= $dashboardFilterTabClass ?> text-app-secondary bg-app-soft">Half Paid</button>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead>
                            <tr class="border-b border-app-panel-border">
                                <th class="<?= $dashboardTableHeadClass ?>">ID</th>
                                <th class="<?= $dashboardTableHeadClass ?>">Booking ID</th>
                                <th class="<?= $dashboardTableHeadClass ?>">Customer Name</th>
                                <th class="<?= $dashboardTableHeadClass ?>">Event Date</th>
                                <th class="<?= $dashboardTableHeadClass ?>">Payment Method</th>
                                <th class="<?= $dashboardTableHeadClass ?>">Total</th>
                                <th class="<?= $dashboardTableHeadClass ?>">Paid</th>
                            </tr>
                        </thead>
                        <tbody id="paymentTableBody">
                            <!-- Rows rendered by JS -->
                        </tbody>
                    </table>
                    <p id="noPaymentRows" class="hidden text-center text-xs text-app-muted py-6">No records found.</p>
                </div>
            </div>
        </section>

        <!-- WITHDRAW HISTORY -->
        <section class="mb-4">
            <div class="<?= $dashboardCardClass ?> supplier-admin-animate" style="animation-delay:.63s">
                <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <h3 class="supplier-admin-section-title text-sm">Withdrawal history</h3>
                        <p class="mt-0.5 text-[11px]" style="color:#A8A29E">Recent supplier payout activity</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="relative">
                            <button id="withdrawFilterBtn" type="button" aria-expanded="false"
                                class="<?= $dashboardMenuButtonClass ?>">
                                <svg class="h-3 w-3 text-app-muted"  xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                <span id="withdrawFilterLabel">This week</span>
                                <svg class="h-3 w-3 text-app-muted transition-transform" id="withdrawFilterChevron" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                            </button>
                            <div id="withdrawFilterMenu" class="invisible absolute right-0 top-[calc(100%+6px)] z-30 w-36 origin-top scale-y-95 rounded-xl border border-app-border bg-app-input p-1.5 opacity-0 shadow-lg transition-all duration-150">
                                <button type="button" data-withdraw-filter="today" class="<?= $dashboardMenuItemClass ?>">Today</button>
                                <button type="button" data-withdraw-filter="week" class="<?= $dashboardMenuItemActiveClass ?>">This week</button>
                                <button type="button" data-withdraw-filter="month" class="<?= $dashboardMenuItemClass ?>">This month</button>
                                <button type="button" data-withdraw-filter="year" class="<?= $dashboardMenuItemClass ?>">This year</button>
                            </div>
                        </div>
                        <button class="text-xs font-medium text-app-primary hover:text-app-accent">View all</button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead>
                            <tr class="border-b border-app-panel-border">
                                <th class="<?= $dashboardTableHeadClass ?>">Withdraw ID</th>
                                <th class="<?= $dashboardTableHeadClass ?>">Date</th>
                                <th class="<?= $dashboardTableHeadClass ?>">Amount</th>
                                <th class="<?= $dashboardTableHeadClass ?>">Bank / Method</th>
                                <th class="<?= $dashboardTableHeadClass ?>">Account No.</th>
                                <th class="<?= $dashboardTableHeadClass ?>">Status</th>
                            </tr>
                        </thead>
                        <tbody id="withdrawTableBody">
                            <!-- Rows rendered by JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

    </div>

<script>
window.supplierDashboardData = <?= json_encode([
    'stats' => $dashboardData['stats'] ?? [],
    'services' => $dashboardData['services'] ?? [],
    'upcomingBookings' => $dashboardData['upcomingBookings'] ?? [],
    'recentReviews' => $dashboardData['recentReviews'] ?? [],
    'payments' => $dashboardData['payments'] ?? [],
    'chartData' => $dashboardData['chartData'] ?? [],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;

  /* ── helpers ── */
  function currency(v) { return 'MMK ' + Number(v).toLocaleString(); }

  /* ── mock data ── */
  var d = window.supplierDashboardData || {};
  var paymentsList = (d.payments || []).map(function(p, i) {
    return {
      id: i + 1,
      bookingId: p.booking_ref || 'BK-' + String(p.booking_id).padStart(3, '0'),
      customer: p.customer_name || 'Customer',
      eventDate: p.event_date || '—',
      method: p.method || '—',
      total: parseFloat(p.total_amount || 0),
      paid: parseFloat(p.paid_amount || 0)
    };
  });
  if (paymentsList.length === 0) {
    paymentsList = [{ id: '—', bookingId: '—', customer: 'No payment records yet', eventDate: '—', method: '—', total: 0, paid: 0 }];
  }
  var paymentData = paymentsList;

  /* ── wedding bookings list ── */
  function renderWeddingBookings() {
    var el = document.getElementById("weddingBookingsList");
    if (!el) return;
    var bookings = (window.supplierDashboardData || {}).upcomingBookings || [];
    if (bookings.length === 0) {
      el.innerHTML = '<p class="text-[10px] text-app-muted text-center py-4">No upcoming bookings.</p>';
      return;
    }
    el.innerHTML = bookings.map(function(r) {
      var status = r.supplier_status || 'pending';
      var statusColor = status === 'confirmed' ? 'bg-app-soft text-app-success' : status === 'completed' ? 'bg-app-soft text-app-success' : 'bg-app-surface text-app-warning';
      var statusLabel = status.charAt(0).toUpperCase() + status.slice(1);
      var dateStr = r.booking_date || '—';
      return '<div class="flex items-center gap-2.5 rounded-xl bg-app-soft p-2.5">' +
        '<div class="flex h-7 w-7 items-center justify-center rounded-lg bg-app-danger-soft text-app-danger shrink-0">' +
          '<svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>' +
        '</div>' +
        '<div class="flex-1 min-w-0">' +
          '<p class="text-xs font-semibold text-app-text truncate">' + (r.customer_name || 'Customer').replace(/&/g,'&amp;').replace(/</g,'&lt;') + '</p>' +
          '<p class="text-[10px] text-app-secondary">' + dateStr + '</p>' +
        '</div>' +
        '<span class="shrink-0 text-[9px] font-bold px-1.5 py-0.5 rounded-full ' + statusColor + '">' + statusLabel + '</span>' +
      '</div>';
    }).join("");
  }

  var paidPayments = paymentsList.filter(function(p) { return parseFloat(p.paid) > 0; });
  var withdrawData = paidPayments.map(function(p, i) {
    return {
      id: "PAY-" + String(i + 1).padStart(4, '0'),
      date: p.eventDate,
      amount: p.paid,
      bank: p.method,
      account: "—",
      status: p.paid >= p.total ? "completed" : "processing"
    };
  });
  if (withdrawData.length === 0) {
    withdrawData = [{ id: '—', date: '—', amount: 0, bank: '—', account: '—', status: 'completed' }];
  }

  /* ── payment table ── */
  var currentPaymentFilter = 'all';
  var currentPaymentDateFilter = 'week';

  function matchesDateFilter(dateStr, filter) {
    if (filter === 'all' || !dateStr || dateStr === '—') return true;
    var d = new Date(dateStr);
    if (isNaN(d.getTime())) return true;
    var now = new Date();
    var startOfToday = new Date(now.getFullYear(), now.getMonth(), now.getDate());
    if (filter === 'today') {
      return d >= startOfToday;
    }
    if (filter === 'week') {
      var weekAgo = new Date(startOfToday);
      weekAgo.setDate(weekAgo.getDate() - 7);
      return d >= weekAgo;
    }
    if (filter === 'month') {
      return d.getMonth() === now.getMonth() && d.getFullYear() === now.getFullYear();
    }
    if (filter === 'year') {
      return d.getFullYear() === now.getFullYear();
    }
    return true;
  }

  function getFilteredPayments() {
    return paymentData.filter(function(r) {
      // Status filter
      if (currentPaymentFilter !== 'all') {
        var total = parseFloat(r.total) || 0;
        var paid = parseFloat(r.paid) || 0;
        if (currentPaymentFilter === 'pending' && paid !== 0) return false;
        if (currentPaymentFilter === 'full paid' && (total <= 0 || paid < total)) return false;
        if (currentPaymentFilter === 'half paid' && (paid <= 0 || paid >= total)) return false;
      }
      // Date filter
      if (!matchesDateFilter(r.eventDate, currentPaymentDateFilter)) return false;
      return true;
    });
  }

  function renderPaymentTable() {
    const tbody = document.getElementById("paymentTableBody");
    const noRows = document.getElementById("noPaymentRows");
    const rows = getFilteredPayments();

    if (rows.length === 0) {
      tbody.innerHTML = "";
      noRows.classList.remove("hidden");
      return;
    }
    noRows.classList.add("hidden");
    tbody.innerHTML = rows.map(r => `
      <tr class="border-b border-app-panel-border hover:bg-app-soft transition-colors">
        <td class="py-2.5 px-2 text-app-secondary">${r.id}</td>
        <td class="py-2.5 px-2 font-medium text-app-text">${r.bookingId}</td>
        <td class="py-2.5 px-2 text-app-text whitespace-nowrap">${r.customer}</td>
        <td class="py-2.5 px-2 text-app-secondary whitespace-nowrap">${r.eventDate}</td>
        <td class="py-2.5 px-2 text-app-secondary">${r.method}</td>
        <td class="py-2.5 px-2 font-medium text-app-text">${currency(r.total)}</td>
        <td class="py-2.5 px-2 font-medium text-app-text">${currency(r.paid)}</td>
      </tr>
    `).join("");
  }

  function filterPayments(status) {
    currentPaymentFilter = status;
    // Update tab active states
    var tabs = document.querySelectorAll('#payment-status [data-status]');
    tabs.forEach(function(tab) {
      if (tab.getAttribute('data-status') === status) {
        tab.className = '<?= $dashboardFilterTabClass ?> bg-app-primary text-app-white';
      } else {
        tab.className = '<?= $dashboardFilterTabClass ?> text-app-secondary bg-app-soft';
      }
    });
    renderPaymentTable();
  }

  /* ── withdraw table ── */
  function withdrawBadge(status) {
    const cls = {
      completed:  "bg-app-soft text-app-success",
      processing: "bg-app-surface text-app-secondary",
      failed:     "bg-app-danger-soft text-app-danger"
    }[status] || "";
    return `<span class="${cls} rounded-full px-2 py-0.5 text-[10px] font-semibold capitalize">${status}</span>`;
  }

  function renderWithdrawTable() {
    document.getElementById("withdrawTableBody").innerHTML = withdrawData.map(r => `
      <tr class="border-b border-app-panel-border hover:bg-app-soft transition-colors">
        <td class="py-2.5 px-2 font-medium text-app-text">${r.id}</td>
        <td class="py-2.5 px-2 text-app-secondary whitespace-nowrap">${r.date}</td>
        <td class="py-2.5 px-2 font-bold text-app-text">${currency(r.amount)}</td>
        <td class="py-2.5 px-2 text-app-secondary">${r.bank}</td>
        <td class="py-2.5 px-2 text-app-secondary">${r.account}</td>
        <td class="py-2.5 px-2">${withdrawBadge(r.status)}</td>
      </tr>
    `).join("");
  }

  function readUtilityColor(className, property = "color") {
    const probe = document.createElement("span");
    probe.className = className;
    document.body.appendChild(probe);
    const color = getComputedStyle(probe)[property];
    probe.remove();
    return color;
  }

  function withAlpha(color, alpha) {
    const rgb = color.match(/^rgb\((.+)\)$/);
    if (rgb) return `rgba(${rgb[1]}, ${alpha})`;
    return color;
  }

  /* ── charts ── */
  var revenueChartInstance = null;
  var bookingChartInstance = null;
  var currentDashboardRange = 'year';
  const PRIMARY_COLOR = "#6d4c5b";
  const MUTED_COLOR = "#A8A29E";
  const GRID_COLOR = "#f0eded";
  const BAR_COLOR = "#e7e5e4";
  const PEAK_COLOR = "#fda4af";

  function createRevenueChart(labels, data) {
    var peakIdx = data.indexOf(Math.max(...data));
    var ctx = document.getElementById("revenueChart");
    revenueChartInstance = new Chart(ctx, {
      type: "line",
      data: {
        labels: labels,
        datasets: [{
          label: "Revenue",
          data: data,
          borderColor: PRIMARY_COLOR,
          backgroundColor: withAlpha(PRIMARY_COLOR, 0.06),
          borderWidth: 2.5,
          pointRadius: data.length > 31 ? 0 : 4,
          pointHoverRadius: 6,
          pointBackgroundColor: data.map((_, i) => i === peakIdx ? PEAK_COLOR : PRIMARY_COLOR),
          pointBorderColor: data.map((_, i) => i === peakIdx ? PRIMARY_COLOR : 'transparent'),
          pointBorderWidth: data.map((_, i) => i === peakIdx ? 2 : 0),
          tension: 0.35,
          fill: true
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { intersect: false, mode: 'index' },
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor: '#fff',
            titleColor: '#44403c',
            bodyColor: '#6d4c5b',
            borderColor: '#ead8c7',
            borderWidth: 1,
            padding: 10,
            cornerRadius: 10,
            titleFont: { size: 11, weight: '600' },
            bodyFont: { size: 12, weight: '700' },
            callbacks: {
              label: function(ctx) { return 'MMK ' + Number(ctx.parsed.y).toLocaleString(); }
            }
          }
        },
        scales: {
          x: {
            grid: { display: false },
            ticks: { font: { size: 11, weight: '500' }, color: MUTED_COLOR, maxRotation: 0 },
            border: { display: false }
          },
          y: {
            grid: { color: GRID_COLOR, drawBorder: false },
            ticks: {
              font: { size: 11 },
              color: MUTED_COLOR,
              callback: function(v) { return v >= 1000000 ? (v/1000000).toFixed(1)+'M' : v >= 1000 ? (v/1000).toFixed(0)+'K' : v; },
              padding: 8
            },
            border: { display: false }
          }
        }
      }
    });
    // Update peak badge
    var peakLabel = labels[peakIdx] || '—';
    var badge = document.getElementById("revenuePeakBadge");
    if (badge) badge.textContent = data[peakIdx] > 0 ? 'PEAK: ' + peakLabel.toUpperCase() : '—';
  }

  function createBookingChart(labels, data) {
    var peakIdx = data.indexOf(Math.max(...data));
    var ctx = document.getElementById("bookingChart");
    bookingChartInstance = new Chart(ctx, {
      type: "bar",
      data: {
        labels: labels,
        datasets: [{
          label: "Bookings",
          data: data,
          backgroundColor: data.map((_, i) => i === peakIdx ? PEAK_COLOR : BAR_COLOR),
          hoverBackgroundColor: data.map((_, i) => i === peakIdx ? '#fb7185' : '#d6d3d1'),
          borderRadius: 6,
          borderSkipped: false,
          barPercentage: 0.6
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { intersect: false, mode: 'index' },
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor: '#fff',
            titleColor: '#44403c',
            bodyColor: '#6d4c5b',
            borderColor: '#ead8c7',
            borderWidth: 1,
            padding: 10,
            cornerRadius: 10,
            titleFont: { size: 11, weight: '600' },
            bodyFont: { size: 12, weight: '700' }
          }
        },
        scales: {
          x: {
            grid: { display: false },
            ticks: { font: { size: 11, weight: '500' }, color: MUTED_COLOR, maxRotation: 0 },
            border: { display: false }
          },
          y: {
            grid: { color: GRID_COLOR, drawBorder: false },
            ticks: { font: { size: 11 }, color: MUTED_COLOR, precision: 0, padding: 8 },
            border: { display: false },
            beginAtZero: true
          }
        }
      }
    });
    // Update peak badge
    var peakLabel = labels[peakIdx] || '—';
    var badge = document.getElementById("bookingPeakBadge");
    if (badge) badge.textContent = data[peakIdx] > 0 ? 'PEAK: ' + peakLabel.toUpperCase() : '—';
  }

  function initCharts() {
    var ch = (window.supplierDashboardData || {}).chartData || {};
    var labels = ch.labels || ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];
    createRevenueChart(labels, ch.revenue || labels.map(() => 0));
    createBookingChart(labels, ch.bookings || labels.map(() => 0));
  }

  /* ── Period dropdown ── */
  function togglePeriodDropdown() {
    var menu = document.getElementById('periodDropdownMenu');
    var chevron = document.getElementById('periodDropdownChevron');
    var isOpen = !menu.classList.contains('invisible');
    if (isOpen) {
      menu.classList.add('invisible', 'opacity-0');
      chevron.style.transform = '';
    } else {
      menu.classList.remove('invisible', 'opacity-0');
      chevron.style.transform = 'rotate(180deg)';
    }
  }

  function closePeriodDropdown() {
    var menu = document.getElementById('periodDropdownMenu');
    var chevron = document.getElementById('periodDropdownChevron');
    menu.classList.add('invisible', 'opacity-0');
    chevron.style.transform = '';
  }

  function selectPeriod(range, label) {
    if (range === currentDashboardRange) { closePeriodDropdown(); return; }
    currentDashboardRange = range;

    // Update label and active state
    document.getElementById('periodDropdownLabel').textContent = label;
    document.querySelectorAll('#periodDropdownMenu button').forEach(function(b) {
      b.className = '<?= $dashboardMenuItemClass ?>';
    });
    var activeBtn = document.querySelector('#periodDropdownMenu button[data-range="' + range + '"]');
    if (activeBtn) activeBtn.className = '<?= $dashboardMenuItemActiveClass ?>';
    closePeriodDropdown();

    // Fetch filtered data
    var urlRoot = '<?= URLROOT ?>';
    fetch(urlRoot + '/supplier/dashboardData?range=' + encodeURIComponent(range))
      .then(function(res) { return res.json(); })
      .then(function(data) {
        if (data.error) return;

        // Update KPI stats
        var s = data.stats || {};
        var set = function(id, v) { var el = document.getElementById(id); if (el) el.innerText = v; };
        set("totalRevenue", currency(s.total_revenue || 0));
        set("paidRevenue", currency(s.paid_revenue || 0));
        set("pendingRevenue", currency(s.pending_revenue || 0));
        set("totalBookings", (s.total_bookings || 0).toLocaleString());
        set("confirmedBookings", (s.completed_bookings || 0).toLocaleString());
        set("cancelledBookings", (s.cancelled_bookings || 0).toLocaleString());

        // Rebuild charts
        if (revenueChartInstance) { revenueChartInstance.destroy(); revenueChartInstance = null; }
        if (bookingChartInstance) { bookingChartInstance.destroy(); bookingChartInstance = null; }
        var ch = data.chartData || {};
        var labels = ch.labels || ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];
        createRevenueChart(labels, ch.revenue || labels.map(() => 0));
        createBookingChart(labels, ch.bookings || labels.map(() => 0));
      })
      .catch(function() { /* silent */ });
  }

  // Close dropdown on outside click
  document.addEventListener('click', function(e) {
    var btn = document.getElementById('periodDropdownBtn');
    var menu = document.getElementById('periodDropdownMenu');
    if (btn && !btn.contains(e.target) && menu && !menu.contains(e.target)) {
      closePeriodDropdown();
    }
  });

  /* ── calendar ── */
  const monthNames = ["January","February","March","April","May","June","July","August","September","October","November","December"];
  const today = new Date();
  let calYear = today.getFullYear();
  let calMonth = today.getMonth();

  // Build booked/pending maps from real upcoming bookings
  function buildCalendarMaps() {
    var bookedMap = {};
    var pendingMap = {};
    var bookings = (window.supplierDashboardData || {}).upcomingBookings || [];
    bookings.forEach(function(bk) {
      var date = bk.booking_date || bk.event_date || '';
      if (!date) return;
      var d = new Date(date);
      if (isNaN(d.getTime())) return;
      var key = d.getFullYear() + '-' + d.getMonth() + '-' + d.getDate();
      var label = (bk.customer_name || 'Booking') + ': ' + (bk.booking_ref || '');
      if (bk.supplier_status === 'confirmed' || bk.supplier_status === 'completed') {
        bookedMap[key] = label;
      } else {
        pendingMap[key] = label;
      }
    });
    return { booked: bookedMap, pending: pendingMap };
  }

  function renderAvailabilityCalendar() {
    const grid = document.getElementById("availabilityCalendarGrid");
    const label = document.getElementById("calendarMonthLabel");
    const tooltip = document.getElementById("calTooltip");
    const tooltipText = document.getElementById("calTooltipText");
    if (!grid) return;
    var maps = buildCalendarMaps();
    var bookedMap = maps.booked;
    var pendingMap = maps.pending;

    if (label) label.textContent = monthNames[calMonth] + " " + calYear;
    const firstDay = new Date(calYear, calMonth, 1);
    const daysInMonth = new Date(calYear, calMonth + 1, 0).getDate();
    const offset = (firstDay.getDay() + 6) % 7;
    const isCurrentMonth = calYear === today.getFullYear() && calMonth === today.getMonth();

    grid.innerHTML = "";
    for (let i = 0; i < offset; i++) grid.insertAdjacentHTML("beforeend", `<div></div>`);

    for (let day = 1; day <= daysInMonth; day++) {
      const key = `${calYear}-${calMonth}-${day}`;
      const isToday = isCurrentMonth && day === today.getDate();
      const isBooked = !!bookedMap[key];
      const isPending = !!pendingMap[key];

      let bg = "bg-app-soft text-app-success hover:bg-app-surface cursor-pointer";
      let label2 = "Available";
      if (isBooked)  { bg = "bg-app-danger-soft text-app-danger hover:bg-app-surface cursor-pointer"; label2 = bookedMap[key]; }
      if (isPending) { bg = "bg-app-surface text-app-warning hover:bg-app-soft cursor-pointer"; label2 = pendingMap[key]; }
      if (isToday)   { bg = "bg-app-text text-app-white font-bold cursor-pointer ring-2 ring-offset-1 ring-app-focus"; label2 = "Today - " + label2; }

      const div = document.createElement("div");
      div.className = `flex h-7 items-center justify-center rounded-md text-[10px] font-semibold transition-all ${bg}`;
      div.textContent = day;
      div.addEventListener("click", () => {
        tooltip.classList.remove("hidden");
        const dateStr = `${monthNames[calMonth]} ${day}, ${calYear}`;
        tooltipText.innerHTML = `<span class="text-app-muted">${dateStr}</span> - ${label2}`;
      });
      grid.appendChild(div);
    }

    // Prev/next wiring
    document.getElementById("calPrev").onclick = () => {
      if (calMonth === 0) { calMonth = 11; calYear--; } else calMonth--;
      if (tooltip) tooltip.classList.add("hidden");
      renderAvailabilityCalendar();
      };
    document.getElementById("calNext").onclick = () => {
      if (calMonth === 11) { calMonth = 0; calYear++; } else calMonth++;
      if (tooltip) tooltip.classList.add("hidden");
      renderAvailabilityCalendar();
      };
  }

  /* ── dashboard data ── */
  function fetchDashboardData() {
    var d = window.supplierDashboardData || {};
    return Promise.resolve({
      totalBookings: d.stats.total_bookings || 0,
      confirmedBookings: d.stats.completed_bookings || 0,
      cancelledBookings: d.stats.cancelled_bookings || 0,
      totalRevenue: d.stats.total_revenue || 0,
      paidRevenue: d.stats.paid_revenue || 0,
      pendingRevenue: d.stats.pending_revenue || 0,
      avgSpend: d.stats.total_bookings > 0 ? Math.round(d.stats.total_revenue / d.stats.total_bookings) : 0,
    });
  }

  function renderDashboard(data) {
    const set = (id, v) => { const el = document.getElementById(id); if (el) el.innerText = v; };
    set("totalBookings", data.totalBookings.toLocaleString());
    set("totalRevenue", currency(data.totalRevenue));
    set("paidRevenue", currency(data.paidRevenue));
    set("pendingRevenue", currency(data.pendingRevenue));
    set("confirmedBookings", data.confirmedBookings);
    set("cancelledBookings", data.cancelledBookings);
    set("avgSpend", currency(data.avgSpend));
  }

  document.addEventListener("DOMContentLoaded", () => {
    if (window.lucide && typeof window.lucide.createIcons === "function") {
      window.lucide.createIcons();
    }

    /* ── scroll hint for overflowing tables ── */
    document.querySelectorAll(".overflow-x-auto").forEach(el => {
      el.classList.add("scroll-hint");
      const check = () => el.classList.toggle("can-scroll", el.scrollWidth > el.clientWidth);
      check();
      el.addEventListener("scroll", () => { el.classList.remove("can-scroll"); });
      window.addEventListener("resize", check);
    });

    /* ── Payment Status filter dropdown ── */
    const payBtn  = document.getElementById("paymentFilterBtn");
    const payMenu = document.getElementById("paymentFilterMenu");
    const payLbl  = document.getElementById("paymentFilterLabel");
    const payChev = document.getElementById("paymentFilterChevron");
    const sectionLabels = { today:"Today", week:"This week", month:"This month", year:"This year" };

    function closePayMenu() { payMenu.classList.add("invisible","opacity-0"); payBtn.setAttribute("aria-expanded","false"); payChev.classList.remove("rotate-180"); }
    function openPayMenu()  { payMenu.classList.remove("invisible","opacity-0"); payBtn.setAttribute("aria-expanded","true"); payChev.classList.add("rotate-180"); }
    payBtn.addEventListener("click", e => { e.stopPropagation(); payBtn.getAttribute("aria-expanded")==="true" ? closePayMenu() : openPayMenu(); });
    document.querySelectorAll("[data-payment-filter]").forEach(opt => {
      opt.addEventListener("click", () => {
        var filter = opt.dataset.paymentFilter;
        currentPaymentDateFilter = filter;
        payLbl.innerText = sectionLabels[filter];
        // Update active style
        document.querySelectorAll("[data-payment-filter]").forEach(function(b) { b.className = '<?= $dashboardMenuItemClass ?>'; });
        opt.className = '<?= $dashboardMenuItemActiveClass ?>';
        closePayMenu();
        renderPaymentTable();
      });
    });
    document.addEventListener("click", e => { if (payBtn && !payBtn.contains(e.target) && !payMenu.contains(e.target)) closePayMenu(); });

    /* ── Withdraw History filter dropdown ── */
    const wdBtn  = document.getElementById("withdrawFilterBtn");
    const wdMenu = document.getElementById("withdrawFilterMenu");
    const wdLbl  = document.getElementById("withdrawFilterLabel");
    const wdChev = document.getElementById("withdrawFilterChevron");

    function closeWdMenu() { wdMenu.classList.add("invisible","opacity-0"); wdBtn.setAttribute("aria-expanded","false"); wdChev.classList.remove("rotate-180"); }
    function openWdMenu()  { wdMenu.classList.remove("invisible","opacity-0"); wdBtn.setAttribute("aria-expanded","true"); wdChev.classList.add("rotate-180"); }
    wdBtn.addEventListener("click", e => { e.stopPropagation(); wdBtn.getAttribute("aria-expanded")==="true" ? closeWdMenu() : openWdMenu(); });
    document.querySelectorAll("[data-withdraw-filter]").forEach(opt => {
      opt.addEventListener("click", () => { wdLbl.innerText = sectionLabels[opt.dataset.withdrawFilter]; closeWdMenu(); });
    });
    document.addEventListener("click", e => { if (wdBtn && !wdBtn.contains(e.target) && !wdMenu.contains(e.target)) closeWdMenu(); });

    /* init */
    renderAvailabilityCalendar();
    renderPaymentTable();
    renderWithdrawTable();
    renderWeddingBookings();
    initCharts();
    fetchDashboardData().then(renderDashboard);
  });
</script>
