<?php
$dashboardCardClass = 'rounded-card border border-app-border bg-app-input p-4 shadow-sm';
$dashboardCompactCardClass = 'rounded-card border border-app-border bg-app-input p-3 shadow-sm';
$dashboardMenuButtonClass = 'flex h-8 items-center gap-1.5 rounded-xl border border-app-border bg-app-input px-3 text-xs font-semibold text-app-primary shadow-sm hover:bg-app-soft hover:text-app-accent focus:outline-none focus:ring-2 focus:ring-app-ring';
$dashboardMenuItemClass = 'flex w-full items-center rounded-lg px-3 py-1.5 text-left text-xs font-medium text-app-primary hover:bg-app-soft hover:text-app-accent';
$dashboardMenuItemActiveClass = 'flex w-full items-center rounded-lg bg-app-soft px-3 py-1.5 text-left text-xs font-semibold text-app-accent hover:bg-app-surface';
$dashboardFilterTabClass = 'filter-tab rounded-full px-4 py-1.5 text-xs font-semibold border border-app-border transition-all';
$dashboardTableHeadClass = 'text-left py-2 px-2 text-[10px] uppercase tracking-wider text-app-muted font-semibold whitespace-nowrap';
?>

<style>
  .scroll-hint { position: relative; }
  .scroll-hint.can-scroll::after { content: ''; position: absolute; top: 0; right: 0; bottom: 0; width: 32px; background: linear-gradient(to right, transparent, rgba(255,255,255,0.9)); pointer-events: none; z-index: 2; }
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
  }
</style>

<div class="supplier-dashboard-overview mx-auto max-w-[1600px] px-4 py-5 font-ui text-[13px] text-app-text antialiased">

       

        <!-- MAIN LAYOUT: left col (stats + charts) | right col (calendar + events) -->
        <section class="mb-4 grid grid-cols-1 gap-3 lg:grid-cols-[1fr_300px] lg:items-stretch">
        <!-- LEFT: stat cards + charts -->
        <div class="flex flex-col gap-3 h-full">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">

            <!-- Escrow Wallet -->
            <div class="relative overflow-hidden rounded-card bg-app-text p-4 text-app-white shadow-xl">
                <div class="relative z-10">
                    <div class="mb-3 flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-app-input/10 text-app-white">
                            <svg class="h-4 w-4"  xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 12V8H6a2 2 0 0 1-2-2c0-1.1.9-2 2-2h12v4"/><path d="M4 6v12c0 1.1.9 2 2 2h14v-4"/><path d="M18 12a2 2 0 0 0-2 2c0 1.1.9 2 2 2h4v-4h-4z"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-app-white">Escrow Wallet</p>
                            <p class="text-[11px] text-app-muted">Protected payments</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <div>
                            <p class="text-[10px] uppercase tracking-widest text-app-muted">Total</p>
                            <p id="escrowTotal" class="mt-1 text-xl font-bold tracking-tight">$38,482</p>
                        </div>
                        <div class="text-right">
                            <p class="text-[10px] uppercase tracking-widest text-app-muted">Pending</p>
                            <p id="escrowPending" class="mt-1 text-xl font-bold tracking-tight">$13,260</p>
                        </div>
                    </div>
                    <div>
                        <div class="mb-1.5 flex justify-between text-[10px] font-bold uppercase tracking-widest text-app-muted">
                            <span><span id="escrowAvailable">$25,222</span> available</span>
                            <span id="escrowProgressPercent">0%</span>
                        </div>
                        <div class="h-1.5 rounded-full bg-app-input/10">
                            <div id="escrowProgressFill" class="h-full w-0 rounded-full bg-app-accent"></div>
                        </div>
                    </div>
                </div>
                <svg class="absolute -right-6 -top-6 h-24 w-24 text-app-white/5 rotate-12"  xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3-1.9 5.8a2 2 0 0 1-1.287 1.288L3 12l5.8 1.9a2 2 0 0 1 1.288 1.287L12 21l1.9-5.8a2 2 0 0 1 1.287-1.288L21 12l-5.8-1.9a2 2 0 0 1-1.288-1.287Z"/></svg>
            </div>

            <!-- Total Revenue -->
            <div class="<?= $dashboardCardClass ?>">
                <div class="mb-3 flex h-8 w-8 items-center justify-center rounded-lg bg-app-danger-soft text-app-danger">
                    <svg class="h-4 w-4"  xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3.85 8.62a4 4 0 0 1 4.78-4.77 4 4 0 0 1 6.74 0 4 4 0 0 1 4.78 4.78 4 4 0 0 1 0 6.74 4 4 0 0 1-4.77 4.78 4 4 0 0 1-6.75 0 4 4 0 0 1-4.78-4.77 4 4 0 0 1 0-6.76Z"/><path d="M12 7v10"/><path d="M14.35 9A2 2 0 0 0 12 7.5h-1a2 2 0 0 0 0 4h2a2 2 0 0 1 0 4h-1a2 2 0 0 1-2.35-1.5"/></svg>
                </div>
                <p class="text-xs font-medium text-app-secondary">Total Revenue</p>
                <h2 id="totalRevenue" class="mt-1.5 text-2xl font-bold tracking-tighter text-app-text">$63,400</h2>
                <div class="mt-3 border-t border-app-panel-border pt-3">
                    <p class="text-[10px] uppercase tracking-widest text-app-muted">Avg Spend</p>
                    <p id="avgSpend" class="mt-0.5 text-base font-bold text-app-text">$200</p>
                </div>
            </div>

            <!-- Total Bookings -->
            <div class="<?= $dashboardCardClass ?>">
                <div class="mb-3 flex h-8 w-8 items-center justify-center rounded-lg bg-app-soft text-app-accent">
                    <svg class="h-4 w-4"  xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/><path d="m9 16 2 2 4-4"/></svg>
                </div>
                <p class="text-xs font-medium text-app-secondary">Total Bookings</p>
                <h2 id="totalBookings" class="mt-1.5 text-2xl font-bold tracking-tighter text-app-text">317</h2>
                <div class="mt-3 grid grid-cols-2 gap-2 border-t border-app-panel-border pt-3">
                    <div>
                        <p class="text-[10px] uppercase tracking-widest text-app-muted">Confirmed</p>
                        <p id="confirmedBookings" class="mt-0.5 text-lg font-bold text-app-success">217</p>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase tracking-widest text-app-muted">Cancelled</p>
                        <p id="cancelledBookings" class="mt-0.5 text-lg font-bold text-app-danger">33</p>
                    </div>
                </div>
            </div>

            </div><!-- end stat cards inner -->

            <!-- CHARTS: two side by side inside left col -->
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 flex-1">

                <!-- Revenue Chart -->
                <div class="<?= $dashboardCompactCardClass ?> flex flex-col">
                    <div class="mb-2 flex items-start justify-between">
                        <div>
                            <h3 class="text-xs font-bold text-app-text">Revenue Chart</h3>
                            <p class="text-[10px] text-app-muted">Monthly revenue</p>
                        </div>
                        <span class="self-start inline-flex items-center gap-1 bg-app-danger-soft text-app-danger font-bold text-[10px] px-2.5 py-1 rounded-full border border-app-border">PEAK: FEB</span>
                    </div>
                    <div class="chart-container min-h-28 flex-1">
                        <canvas id="revenueChart"></canvas>
                    </div>
                    <div class="mt-2 flex flex-wrap items-center gap-3 border-t border-app-panel-border pt-2">
                        <div class="flex items-center gap-1.5">
                            <span class="inline-block h-2.5 w-2.5 rounded-full bg-app-primary"></span>
                            <span class="text-[10px] font-medium text-app-secondary">Revenue</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="inline-block h-2.5 w-4 rounded-full bg-app-primary/10 ring-1 ring-app-panel-border"></span>
                            <span class="text-[10px] font-medium text-app-secondary">Filled area</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="inline-block h-2.5 w-2.5 rounded-full bg-app-danger-soft ring-1 ring-app-primary"></span>
                            <span class="text-[10px] font-medium text-app-secondary">Peak month</span>
                        </div>
                    </div>
                </div>

                <!-- Booking Trends -->
                <div class="<?= $dashboardCompactCardClass ?> flex flex-col">
                    <div class="mb-2 flex items-start justify-between">
                        <div>
                            <h3 class="text-xs font-bold text-app-text">Booking Trends</h3>
                            <p class="text-[10px] text-app-muted">Monthly bookings</p>
                        </div>
                        <span class="self-start inline-flex items-center gap-1 bg-app-danger-soft text-app-danger font-bold text-[10px] px-2.5 py-1 rounded-full border border-app-border">PEAK: FEB</span>
                    </div>
                    <div class="chart-container min-h-28 flex-1">
                        <canvas id="bookingChart"></canvas>
                    </div>
                    <div class="mt-2 flex flex-wrap items-center gap-3 border-t border-app-panel-border pt-2">
                        <div class="flex items-center gap-1.5">
                            <span class="inline-block h-2.5 w-2.5 rounded-sm bg-app-ring ring-1 ring-app-panel-border"></span>
                            <span class="text-[10px] font-medium text-app-secondary">Bookings</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="inline-block h-2.5 w-2.5 rounded-sm bg-app-danger-soft"></span>
                            <span class="text-[10px] font-medium text-app-secondary">Peak month</span>
                        </div>
                    </div>
                </div>
            </div><!-- end charts -->

        </div><!-- end left col -->

            <!-- RIGHT: Calendar + Upcoming Events -->
            <div class="flex flex-col gap-3 h-full">

                <!-- Calendar -->
                <div class="<?= $dashboardCardClass ?>">
                    <div class="mb-3 flex items-center justify-between">
                        <button id="calPrev" class="flex h-7 w-7 items-center justify-center rounded-lg hover:bg-app-soft transition-colors">
                            <svg class="h-3.5 w-3.5 text-app-secondary"  xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                        </button>
                        <h3 class="text-xs font-bold text-app-text" id="calendarMonthLabel">January</h3>
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

                <!-- Upcoming Events (below calendar) -->
                <div class="<?= $dashboardCardClass ?> flex flex-col flex-1">
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="text-xs font-bold text-app-text">Upcoming Events</h3>
                        <button class="text-xs font-medium text-app-primary hover:text-app-accent">View all</button>
                    </div>
                    <div id="weddingBookingsList" class="space-y-2 flex-1 overflow-y-auto">
                        <!-- rendered by JS -->
                    </div>
                </div>
            </div><!-- end right col -->
        </section>

        <!-- PAYMENT STATUS -->
        <section class="mb-4">
            <div class="<?= $dashboardCardClass ?>">
                <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                    <h3 class="text-sm font-bold text-app-text">Payment status</h3>
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
                                <th class="<?= $dashboardTableHeadClass ?>">Payout Type</th>
                                <th class="<?= $dashboardTableHeadClass ?>">Total</th>
                                <th class="<?= $dashboardTableHeadClass ?>">Paid</th>
                                <th class="<?= $dashboardTableHeadClass ?>">Escrow Status</th>
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
            <div class="<?= $dashboardCardClass ?>">
                <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                    <h3 class="text-sm font-bold text-app-text">Withdraw History</h3>
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
  /* ── helpers ── */
  function currency(v) { return `$${Number(v).toLocaleString()}`; }

  /* ── mock data ── */
  const paymentData = [
    { id: 1, bookingId: "BK-001", customer: "Aye Aye Chan", eventDate: "May 25, 2026", method: "KBZ Pay",  payout: "Full Paid",  total: 2400, paid: 2400, escrow: "Held in Escrow" },
    { id: 2, bookingId: "BK-002", customer: "Zin Mar Aung", eventDate: "Jun 3, 2026",  method: "CB Pay",   payout: "Half Paid",  total: 3200, paid: 1600, escrow: "Partially Released" },
    { id: 3, bookingId: "BK-003", customer: "Mya Sandar",   eventDate: "Jun 15, 2026", method: "Wave Pay", payout: "Pending",    total: 1800, paid: 0,    escrow: "Held in Escrow" },
    { id: 4, bookingId: "BK-004", customer: "Su Su Hlaing",  eventDate: "Jul 2, 2026",  method: "KBZ Pay",  payout: "Full Paid",  total: 4100, paid: 4100, escrow: "Released" },
    { id: 5, bookingId: "BK-005", customer: "Nan Ei Phyo",   eventDate: "Jul 8, 2026",  method: "AYA Pay",  payout: "Half Paid",  total: 2750, paid: 1375, escrow: "Partially Released" },
  ];

  /* ── wedding bookings list ── */
  function renderWeddingBookings() {
    const el = document.getElementById("weddingBookingsList");
    if (!el) return;
    // Filter only booked (rose/confirmed) wedding-type entries from paymentData
    const weddings = paymentData.filter(r => r.payout === "Full Paid" || r.payout === "Half Paid");
    if (weddings.length === 0) {
      el.innerHTML = `<p class="text-[10px] text-app-muted text-center py-4">No wedding bookings.</p>`;
      return;
    }
    el.innerHTML = weddings.map(r => {
      const statusColor = r.payout === "Full Paid" ? "bg-app-soft text-app-success" : "bg-app-surface text-app-warning";
      const statusLabel = r.payout === "Full Paid" ? "Confirmed" : "Pending";
      return `
        <div class="flex items-center gap-2.5 rounded-xl bg-app-soft p-2.5">
          <div class="flex h-7 w-7 items-center justify-center rounded-lg bg-app-danger-soft text-app-danger shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-xs font-semibold text-app-text truncate">${r.customer}</p>
            <p class="text-[10px] text-app-secondary">${r.eventDate}</p>
          </div>
          <span class="shrink-0 text-[9px] font-bold px-1.5 py-0.5 rounded-full ${statusColor}">${statusLabel}</span>
        </div>`;
    }).join("");
  }

  const withdrawData = [
    { id: "WD-0021", date: "May 10, 2026", amount: 5000,  bank: "KBZ Bank",  account: "**** 4892", status: "completed"  },
    { id: "WD-0020", date: "Apr 28, 2026", amount: 3200,  bank: "CB Bank",   account: "**** 7741", status: "completed"  },
    { id: "WD-0019", date: "Apr 14, 2026", amount: 8500,  bank: "AYA Bank",  account: "**** 3310", status: "processing" },
    { id: "WD-0018", date: "Mar 30, 2026", amount: 1200,  bank: "KBZ Bank",  account: "**** 4892", status: "failed"     },
    { id: "WD-0017", date: "Mar 12, 2026", amount: 6000,  bank: "CB Bank",   account: "**** 7741", status: "completed"  },
  ];

  /* ── payment table ── */
  let currentPaymentFilter = "all";

  function payoutBadge(type) {
    const t = type.toLowerCase();
    if (t === "pending")   return `<span class="rounded-full bg-app-surface px-2 py-0.5 text-[10px] font-semibold text-app-warning">${type}</span>`;
    if (t === "full paid") return `<span class="rounded-full bg-app-soft px-2 py-0.5 text-[10px] font-semibold text-app-success">${type}</span>`;
    if (t === "half paid") return `<span class="rounded-full bg-app-surface px-2 py-0.5 text-[10px] font-semibold text-app-secondary">${type}</span>`;
    return type;
  }

  function escrowBadge(status) {
    const s = status.toLowerCase();
    if (s === "released")             return `<span class="text-app-success font-medium">${status}</span>`;
    if (s === "partially released")   return `<span class="text-app-warning font-medium">${status}</span>`;
    return `<span class="text-app-secondary">${status}</span>`;
  }

  function filterPayments(status) {
    currentPaymentFilter = status;
    document.querySelectorAll(".filter-tab").forEach(btn => {
      const isActive = btn.dataset.status === status;
      btn.classList.toggle("bg-app-primary", isActive);
      btn.classList.toggle("text-app-white", isActive);
      btn.classList.toggle("text-app-secondary", !isActive);
      btn.classList.toggle("bg-app-soft", !isActive);
    });
    renderPaymentTable();
  }

  function renderPaymentTable() {
    const tbody = document.getElementById("paymentTableBody");
    const noRows = document.getElementById("noPaymentRows");
    const rows = currentPaymentFilter === "all"
      ? paymentData
      : paymentData.filter(r => r.payout.toLowerCase() === currentPaymentFilter);

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
        <td class="py-2.5 px-2">${payoutBadge(r.payout)}</td>
        <td class="py-2.5 px-2 font-medium text-app-text">${currency(r.total)}</td>
        <td class="py-2.5 px-2 font-medium text-app-text">${currency(r.paid)}</td>
        <td class="py-2.5 px-2">${escrowBadge(r.escrow)}</td>
      </tr>
    `).join("");
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
  function initCharts() {
    const months = ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];
    const revenueVals = [4200, 6800, 5100, 4700, 5500, 3200, 4900, 5800, 4100, 3800, 4600, 5200];
    const bookingVals = [28, 45, 34, 29, 38, 21, 32, 41, 27, 24, 31, 36];
    const primaryColor = readUtilityColor("text-app-primary");
    const mutedColor = readUtilityColor("text-app-muted");
    const gridColor = readUtilityColor("border-app-panel-border border", "borderTopColor");
    const barColor = readUtilityColor("bg-app-ring", "backgroundColor");
    const peakColor = readUtilityColor("bg-app-danger-soft", "backgroundColor");
    const revenuePeakIndex = revenueVals.indexOf(Math.max(...revenueVals));
    const bookingPeakIndex = bookingVals.indexOf(Math.max(...bookingVals));

    // Revenue: line chart
    new Chart(document.getElementById("revenueChart"), {
      type: "line",
      data: {
        labels: months,
        datasets: [{
          label: "Sales",
          data: revenueVals,
          borderColor: primaryColor,
          backgroundColor: withAlpha(primaryColor, 0.08),
          borderWidth: 2,
          pointRadius: 3,
          pointBackgroundColor: revenueVals.map((_, index) => index === revenuePeakIndex ? peakColor : primaryColor),
          pointBorderColor: revenueVals.map((_, index) => index === revenuePeakIndex ? primaryColor : primaryColor),
          pointBorderWidth: revenueVals.map((_, index) => index === revenuePeakIndex ? 2 : 0),
          tension: 0.4,
          fill: true
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          x: {
            grid: { display: false },
            ticks: { font: { size: 10 }, color: mutedColor },
            title: { display: true, text: "Month", color: mutedColor, font: { size: 10, weight: "600" } }
          },
          y: {
            grid: { color: gridColor },
            ticks: {
              font: { size: 10 },
              color: mutedColor,
              callback: (value) => `$${Number(value).toLocaleString()}`
            },
            title: { display: true, text: "Revenue (USD)", color: mutedColor, font: { size: 10, weight: "600" } }
          }
        }
      }
    });

    // Booking: bar chart (unchanged)
    new Chart(document.getElementById("bookingChart"), {
      type: "bar",
      data: {
        labels: months,
        datasets: [{
          label: "Bookings",
          data: bookingVals,
          backgroundColor: bookingVals.map((_, index) => index === bookingPeakIndex ? peakColor : barColor),
          borderRadius: 4,
          borderSkipped: false
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          x: {
            grid: { display: false },
            ticks: { font: { size: 10 }, color: mutedColor },
            title: { display: true, text: "Month", color: mutedColor, font: { size: 10, weight: "600" } }
          },
          y: {
            grid: { color: gridColor },
            ticks: { font: { size: 10 }, color: mutedColor, precision: 0 },
            title: { display: true, text: "Bookings", color: mutedColor, font: { size: 10, weight: "600" } }
          }
        }
      }
    });
  }

  /* ── calendar ── */
  const monthNames = ["January","February","March","April","May","June","July","August","September","October","November","December"];
  const today = new Date();
  let calYear = today.getFullYear();
  let calMonth = today.getMonth();

  // Booked/pending data keyed by "YYYY-M-D"
  const bookedMap = {
    [`${today.getFullYear()}-${today.getMonth()}-2`]: "Wedding: Aye Aye Chan",
    [`${today.getFullYear()}-${today.getMonth()}-9`]: "Wedding: Zin Mar Aung",
    [`${today.getFullYear()}-${today.getMonth()}-16`]: "Venue: Grand Palace Hall",
    [`${today.getFullYear()}-${today.getMonth()}-23`]: "Wedding: Su Su Hlaing",
  };
  const pendingMap = {
    [`${today.getFullYear()}-${today.getMonth()}-5`]: "Pending: Mya Sandar",
    [`${today.getFullYear()}-${today.getMonth()}-12`]: "Pending: Nan Ei Phyo",
    [`${today.getFullYear()}-${today.getMonth()}-19`]: "Pending: Review session",
  };

  function renderAvailabilityCalendar() {
    const grid = document.getElementById("availabilityCalendarGrid");
    const label = document.getElementById("calendarMonthLabel");
    const tooltip = document.getElementById("calTooltip");
    const tooltipText = document.getElementById("calTooltipText");
    if (!grid) return;

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
    return new Promise(resolve => setTimeout(() => resolve({
      totalBookings: 317, confirmedBookings: 217, cancelledBookings: 33,
      totalRevenue: 63400, avgSpend: 200,
      escrow: { total: 38482, pendingRelease: 13260, available: 25222 }
    }), 200));
  }

  function renderDashboard(data) {
    const set = (id, v) => { const el = document.getElementById(id); if (el) el.innerText = v; };
    set("totalBookings", data.totalBookings.toLocaleString());
    set("totalRevenue", currency(data.totalRevenue));
    set("confirmedBookings", data.confirmedBookings);
    set("cancelledBookings", data.cancelledBookings);
    set("avgSpend", currency(data.avgSpend));
    set("escrowTotal", currency(data.escrow.total));
    set("escrowPending", currency(data.escrow.pendingRelease));
    set("escrowAvailable", currency(data.escrow.available));
    const pct = data.escrow.total > 0 ? Math.round((data.escrow.available / data.escrow.total) * 100) : 0;
    const fill = document.getElementById("escrowProgressFill");
    if (fill) fill.style.width = `${pct}%`;
    set("escrowProgressPercent", `${pct}%`);
  }

  document.addEventListener("DOMContentLoaded", () => {

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
      opt.addEventListener("click", () => { payLbl.innerText = sectionLabels[opt.dataset.paymentFilter]; closePayMenu(); });
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
