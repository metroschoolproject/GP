
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Wedding Admin Dashboard | Premium</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700&display=swap');

        :root {
            --dashboard-bg:             #FBFBF9;
            --dashboard-border:         #e7e5e4;
            --dashboard-text:           #1c1917;
            --accent-color:             #673049;
            --dashboard-primary-soft:   #fcefe8;
            --dashboard-radius-control: 1rem;
            --dashboard-shadow-sm:      0 1px 2px rgba(28, 25, 23, 0.05);
            --dashboard-font:           'Inter', sans-serif;
        }

        * { box-sizing: border-box; }

        body {
            font-family: var(--dashboard-font);
            font-size: 13.5px;
            line-height: 1.5;
            background-color: var(--dashboard-bg);
            color: var(--dashboard-text);
            -webkit-font-smoothing: antialiased;
            font-variant-numeric: tabular-nums;
        }

        main { position: relative; }

        /* ── Cards ── */
        .card {
            background: #ffffff;
            border: 1px solid var(--dashboard-border);
            border-radius: 1.2rem;
            box-shadow: var(--dashboard-shadow-sm);
            transition: box-shadow 0.18s ease;
        }
        .card:hover { box-shadow: 0 4px 12px rgba(28,25,23,0.08); }

        /* ── Typography ── */
        .dashboard-page-title {
            margin: 0;
            color: #34232b;
            font-family: "Playfair Display", serif;
            font-size: clamp(27px, 2.5vw, 36px);
            font-weight: 650;
            letter-spacing: -0.025em;
            line-height: 1.08;
        }

        .dashboard-page-copy {
            margin-top: 0.4rem;
            color: #7b5c69;
            font-size: 12px;
            font-weight: 500;
        }

        .section-title {
            color: #292524;
            font-weight: 750;
            letter-spacing: -0.015em;
        }

        .stat-label {
            font-size: 11px;
            font-weight: 650;
            letter-spacing: 0.01em;
            text-transform: none;
            color: #78716c;
        }

        .dashboard-fact {
            font-variant-numeric: tabular-nums;
            letter-spacing: -0.025em;
        }

        #pendingBookingConfirm,
        #pendingPayments,
        #pendingVendorApproval,
        #pendingVendorApproval2,
        #vendorApproved,
        #vendorPending,
        #vendorRejected,
        #totalCustomers,
        #totalSuppliers,
        #totalStaffs {
            font-variant-numeric: tabular-nums;
        }

        /* ── Queue / list item ── */
        .queue-item {
            background: #f5f5f3;
            border-radius: 0.75rem;
            padding: 0.55rem 0.75rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border: 1px solid transparent;
            transition: all 0.12s ease;
        }
        .queue-item:hover {
            background: #eeece9;
            border-color: var(--dashboard-border);
        }

        .queue-item > div > p:first-child,
        .queue-item > span:first-child {
            color: #292524 !important;
            font-weight: 650 !important;
        }

        /* ── Progress bar ── */
        .progress-track {
            height: 6px;
            border-radius: 9999px;
            background: rgba(255,255,255,0.10);
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            border-radius: 9999px;
            background: #818cf8;
            transition: width 0.8s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        /* ── Tables ── */
        tbody tr { transition: background 0.1s ease; }
        tbody tr:hover { background: #f5f5f3; }

        thead th {
            font-size: 10px;
            font-weight: 750;
            letter-spacing: 0.055em;
            text-transform: uppercase;
            color: #78716c;
        }

        /* ── Badge pill ── */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.15rem 0.55rem;
            border-radius: 9999px;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.01em;
            text-transform: none;
        }
        .badge-emerald   { background: #d1fae5; color: #065f46; }
        .badge-amber     { background: #fef3c7; color: #92400e; }
        .badge-accent    { background: #fde8ef; color: #673049; border: 1px solid #f9c0d2; }
        .badge-muted     { background: #f3f4f6; color: #57534e; border: 1px solid #d1d5db; }
        .badge-processing{ background: #dbeafe; color: #1e40af; }
        .badge-sky       { background: #e0f2fe; color: #0369a1; }

        /* ── Icon wrap ── */
        .icon-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.5rem;
            flex-shrink: 0;
        }

        /* ── Select control ── */
        .filter-select {
            appearance: none;
            background: transparent;
            border: none;
            outline: none;
            font-family: var(--dashboard-font);
            font-size: 12px;
            font-weight: 600;
            color: var(--accent-color);
            cursor: pointer;
        }

        /* ── Package cards ── */
        .pkg-card {
            background: #ffffff;
            border-radius: 1.2rem;
            border: 1px solid var(--dashboard-border);
            overflow: hidden;
            box-shadow: var(--dashboard-shadow-sm);
            transition: box-shadow 0.28s ease, transform 0.28s ease;
            position: relative;
        }
        .pkg-card:hover {
            box-shadow: 0 12px 32px rgba(28,25,23,0.16);
            transform: translateY(-3px);
        }
        .pkg-card img {
            display: block;
            width: 100%;
            height: 220px;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        .pkg-card:hover img { transform: scale(1.07); }

        /* Slide-up overlay on hover */
        .pkg-card .pkg-overlay {
            position: absolute;
            bottom: 0; left: 0; right: 0;
            background: linear-gradient(to top, rgba(28,25,23,0.88) 0%, rgba(28,25,23,0.0) 100%);
            padding: 2rem 1rem 1rem;
            transform: translateY(100%);
            transition: transform 0.35s cubic-bezier(0.34, 1.20, 0.64, 1);
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        .pkg-card:hover .pkg-overlay { transform: translateY(0); }

        /* Caption + rating always visible */
        .pkg-card .pkg-caption {
            position: absolute;
            bottom: 0; left: 0; right: 0;
            padding: 0.625rem 0.875rem;
            background: linear-gradient(to top, rgba(28,25,23,0.60) 0%, transparent 100%);
            pointer-events: none;
        }
        .pkg-card:hover .pkg-caption { opacity: 0; transition: opacity 0.18s ease; }

        /* ── Divider ── */
        .divider { border: none; border-top: 1px solid var(--dashboard-border); }

        /* ── Staggered fade-up ── */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(8px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .animate-up { animation: fadeUp 0.35s ease both; }

        /* ── Scrollbar ── */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #d6d3d1; border-radius: 3px; }

        /* ── Filter tab ── */
        .filter-tab-btn {
            padding: 0.3rem 0.875rem;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 600;
            border: 1px solid var(--dashboard-border);
            background: #f5f5f3;
            color: #57534e;
            cursor: pointer;
            transition: all 0.12s ease;
        }
        .filter-tab-btn:hover { background: #fde8ef; color: var(--accent-color); }
        .filter-tab-btn.active { background: var(--accent-color); color: #fff; border-color: var(--accent-color); }

        /* ── Dropdown menu ── */
        .dropdown-menu {
            position: absolute;
            right: 0;
            top: calc(100% + 6px);
            z-index: 30;
            width: 9rem;
            background: #ffffff;
            border: 1px solid var(--dashboard-border);
            border-radius: 0.75rem;
            padding: 0.375rem;
            box-shadow: 0 8px 24px rgba(28,25,23,0.10);
        }
        .dropdown-item {
            display: flex;
            width: 100%;
            align-items: center;
            border-radius: 0.5rem;
            padding: 0.375rem 0.75rem;
            text-align: left;
            font-size: 12px;
            font-weight: 500;
            color: var(--accent-color);
            transition: background 0.1s;
            cursor: pointer;
            border: none;
            background: transparent;
        }
        .dropdown-item:hover { background: #fde8ef; color: #9b1c4a; }
        .dropdown-item.active { background: #fde8ef; font-weight: 600; color: #9b1c4a; }
    </style>
</head>
<body class="antialiased">
    <main class="mx-auto max-w-[1600px] px-5 py-6">
        <header class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="stat-label mb-1">Admin workspace</p>
                <h1 class="dashboard-page-title">Operations overview</h1>
                <p class="dashboard-page-copy">Bookings, revenue, suppliers, and upcoming events at a glance.</p>
            </div>
            <div class="flex flex-wrap gap-2">
            <label class="flex h-8 items-center gap-1.5 rounded-xl border border-stone-200 bg-white px-3 text-xs font-semibold shadow-sm"
                style="color:var(--accent-color)">
                <i data-lucide="calendar-days" class="h-3 w-3" style="color:#a8a29e"></i>
                <input id="eventDateFilter" type="date" class="bg-transparent text-xs font-semibold focus:outline-none"
                    style="color:var(--accent-color)">
            </label>
            <div class="relative">
                <button id="eventFilterBtn" type="button" aria-expanded="false"
                    class="flex h-8 items-center gap-1.5 rounded-xl border border-stone-200 bg-white px-3 text-xs font-semibold shadow-sm focus:outline-none"
                    style="color:var(--accent-color)">
                    <i data-lucide="calendar" class="h-3 w-3" style="color:#a8a29e"></i>
                    <span id="eventFilterLabel">This week</span>
                    <i data-lucide="chevron-down" class="h-3 w-3 transition-transform" id="eventFilterChevron" style="color:#a8a29e"></i>
                </button>
                <div id="eventFilterMenu" class="invisible dropdown-menu opacity-0 transition-all duration-150">
                    <button type="button" data-event-filter="today"  class="dropdown-item">Today</button>
                    <button type="button" data-event-filter="week"   class="dropdown-item active">This week</button>
                    <button type="button" data-event-filter="month"  class="dropdown-item">This month</button>
                    <button type="button" data-event-filter="year"   class="dropdown-item">This year</button>
                </div>
            </div>
            </div>
        </header>

        <!-- ── ROW 1: KPI cards ── -->
        <section class="mb-4 grid grid-cols-1 gap-3 lg:grid-cols-3">

            <!-- Total Revenue -->
            <div class="card p-5 animate-up" style="animation-delay:0.07s">
                <div class="icon-wrap mb-3 h-8 w-8" style="background:#fff1f2">
                    <i data-lucide="badge-dollar-sign" class="h-4 w-4" style="color:#be123c"></i>
                </div>
                <p class="stat-label">Total Revenue</p>
                <h2 id="totalRevenue" class="section-title dashboard-fact mt-1.5 text-2xl" style="color:#1c1917">$63,400</h2>
                <hr class="divider mt-4 mb-3">
                <p class="stat-label">Avg Customer Spend</p>
                <p id="avgSpend" class="dashboard-fact mt-1 text-base font-bold" style="color:#1c1917">$200</p>
            </div>

            <!-- Total Bookings -->
            <div class="card p-5 animate-up" style="animation-delay:0.14s">
                <div class="icon-wrap mb-3 h-8 w-8" style="background:#f0f9ff">
                    <i data-lucide="calendar-check" class="h-4 w-4" style="color:#0284c7"></i>
                </div>
                <p class="stat-label">Total Bookings</p>
                <h2 id="totalBookings" class="section-title dashboard-fact mt-1.5 text-2xl" style="color:#1c1917">317</h2>
                <hr class="divider mt-4 mb-3">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <p class="stat-label">Confirmed</p>
                        <p id="confirmedBookings" class="dashboard-fact mt-1 text-lg font-bold" style="color:#07825f">217</p>
                    </div>
                    <div>
                        <p class="stat-label">Cancelled</p>
                        <p id="cancelledBookings" class="dashboard-fact mt-1 text-lg font-bold" style="color:#c73434">33</p>
                    </div>
                </div>
            </div>

            <!-- Operations Queue -->
            <div class="card p-5 animate-up" style="animation-delay:0.21s">
                <h3 class="mb-4 flex items-center gap-2 font-bold" style="font-size:13px;color:#1c1917">
                    <i data-lucide="list-checks" class="h-4 w-4" style="color:#673049"></i>
                    Operations Queue
                </h3>
                <div class="space-y-2">
                    <div class="queue-item">
                        <div>
                            <p style="font-size:11px;font-weight:600;color:#1c1917">Booking Confirmations</p>
                            <p class="stat-label" style="margin-top:1px">Pending review</p>
                        </div>
                        <span id="pendingBookingConfirm" class="badge badge-amber">--</span>
                    </div>
                    <div class="queue-item">
                        <div>
                            <p style="font-size:11px;font-weight:600;color:#1c1917">Payments</p>
                            <p class="stat-label" style="margin-top:1px">Awaiting release</p>
                        </div>
                        <span id="pendingPayments" class="badge badge-processing">--</span>
                    </div>
                    <div class="queue-item">
                        <div>
                            <p style="font-size:11px;font-weight:600;color:#1c1917">Vendor Approvals</p>
                            <p class="stat-label" style="margin-top:1px">Needs action</p>
                        </div>
                        <span id="pendingVendorApproval" class="badge badge-accent">--</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── ROW 2: Charts ── -->
        <section class="mb-4 grid grid-cols-1 gap-3 lg:grid-cols-12">

            <!-- Revenue Trend -->
            <div class="card p-5 lg:col-span-8 animate-up" style="animation-delay:0.28s">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="section-title" style="font-size:13px;color:#1c1917">Revenue Trend</h2>
                        <p class="mt-0.5" style="font-size:11px;color:#a8a29e">Performance across selected packages</p>
                    </div>
                    <span class="inline-flex items-center gap-1 rounded-full border border-rose-100 bg-rose-50 px-2.5 py-1 text-[10px] font-bold text-rose-500">
                        <span id="peakPeriodLabel">PEAK DAY:</span> <span id="peakMonthTag" class="ml-1">--</span>
                    </span>
                </div>
                <div style="height:260px">
                    <canvas id="revenueChartCanvas"></canvas>
                </div>
            </div>

            <!-- Supplier Categories -->
            <div class="card p-5 lg:col-span-4 animate-up" style="animation-delay:0.35s">
                <h2 class="section-title" style="font-size:13px;color:#1c1917">Supplier Categories</h2>
                <p class="mt-0.5 mb-4" style="font-size:11px;color:#a8a29e; margin-bottom: 30px;">Current market distribution</p>
                <div style="height:320px; margin-top:20px;">
                    <canvas id="supplierPieCanvas"></canvas>
                </div>
            </div>
        </section>

        <!-- ── ROW 3: Partners / Vendor / Community ── -->
        <section class="mb-4 grid grid-cols-1 gap-3 lg:grid-cols-4">

            <!-- Top Partners -->
            <div class="card p-5 lg:col-span-2 animate-up" style="animation-delay:0.42s">
                <h3 class="mb-4 flex items-center gap-2 font-bold" style="font-size:13px;color:#1c1917">
                    <i data-lucide="award" class="h-4 w-4" style="color:#b45309"></i> Top Partners
                </h3>
                <div id="topSuppliersList" class="space-y-2"></div>
            </div>

            <!-- Vendor Status -->
            <div class="card p-5 animate-up" style="animation-delay:0.56s">
                <h3 class="mb-4 flex items-center gap-2 font-bold" style="font-size:13px;color:#1c1917">
                    <i data-lucide="store" class="h-4 w-4" style="color:#12bb8b"></i> Vendor Status
                </h3>
                <div class="space-y-2.5">
                    <div class="flex items-center justify-between py-1" style="font-size:12px">
                        <span style="color:#57534e">Approved</span>
                        <span id="vendorApproved" class="font-bold" style="color:#12bb8b">--</span>
                    </div>
                    <div class="flex items-center justify-between py-1" style="font-size:12px">
                        <span style="color:#57534e">Pending Approval</span>
                        <span id="vendorPending" class="font-bold" style="color:#d4be50">--</span>
                    </div>
                    <div class="flex items-center justify-between py-1" style="font-size:12px">
                        <span style="color:#57534e">Rejected</span>
                        <span id="vendorRejected" class="font-bold" style="color:#e23535">--</span>
                    </div>
                </div>
                <hr class="divider my-4">
                <div class="queue-item">
                    <span class="font-medium italic" style="font-size:12px;color:#57534e">Actions Needed</span>
                    <span id="pendingVendorApproval2" class="badge badge-accent">--</span>
                </div>
            </div>

            <!-- Community -->
            <div class="card p-5 animate-up" style="animation-delay:0.63s">
                <h3 class="mb-4 flex items-center gap-2 font-bold" style="font-size:13px;color:#1c1917">
                    <i data-lucide="users" class="h-4 w-4" style="color:#673049"></i> Community
                </h3>
                <div class="space-y-2">
                    <div class="queue-item">
                        <span class="font-medium" style="font-size:12px;color:#57534e">Customers</span>
                        <span id="totalCustomers" class="font-bold" style="color:#1c1917">--</span>
                    </div>
                    <div class="queue-item">
                        <span class="font-medium" style="font-size:12px;color:#57534e">Suppliers</span>
                        <span id="totalSuppliers" class="font-bold" style="color:#1c1917">--</span>
                    </div>
                    <div class="queue-item">
                        <span class="font-medium" style="font-size:12px;color:#57534e">Staff Members</span>
                        <span id="totalStaffs" class="font-bold" style="color:#1c1917">--</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── Upcoming Events Table ── -->
        <section class="mb-4 card overflow-hidden animate-up" style="animation-delay:0.70s">
            <div class="p-5 flex flex-wrap items-center justify-between gap-3" style="border-bottom:1px solid var(--dashboard-border)">
                <div>
                    <h2 class="section-title" style="font-size:14px;color:#1c1917">Upcoming Wedding Events</h2>
                    <p class="mt-0.5" style="font-size:11px;color:#a8a29e">Live schedule of ceremonies and receptions</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left">
                    <thead style="background:#f9f8f6">
                        <tr>
                            <th class="px-6 py-3">Event Type</th>
                            <th class="px-6 py-3">Client</th>
                            <th class="px-6 py-3">Schedule</th>
                            <th class="px-6 py-3">Venue</th>
                            <th class="px-6 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody id="upcomingEventsBody" style="border-top:1px solid var(--dashboard-border)"></tbody>
                </table>
            </div>
        </section>

        <!-- ── Popular Wedding Packages ── -->
        <section class="card p-5 animate-up" style="animation-delay:0.77s">
            <div class="mb-5">
                <h2 class="section-title" style="font-size:14px;color:#1c1917">Popular Wedding Packages</h2>
                <p class="mt-0.5 italic" style="font-size:11px;color:#a8a29e">"Sparkling Eve" is trending this week</p>
            </div>
            <div id="popularGrid" class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4"></div>
        </section>

    </main>

    <script>
        function refreshLucideIcons() {
            if (window.lucide && typeof lucide.createIcons === "function") lucide.createIcons();
        }

        async function fetchDashboardData(filter = "week") {
            const params = new URLSearchParams({ filter: filter });
            const dateEl = document.getElementById('eventDateFilter');
            if (dateEl && dateEl.value) {
                params.set('date', dateEl.value);
            }
            const response = await fetch('../admin/overviewData?' + params.toString());
            if (!response.ok) {
                throw new Error('Dashboard data failed to load (HTTP ' + response.status + ')');
            }
            return response.json();
        }

        let revenueChart;
        let supplierChart;
        let isLoading = false;
        let currentFilter = "week";

        function currency(value) { return `MMK ${value.toLocaleString()}`; }
        function formatDateInputValue(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, "0");
            const day = String(date.getDate()).padStart(2, "0");
            return `${year}-${month}-${day}`;
        }
        function getRevenueXAxisTitle(filter) {
            return ({ today: "Hours", week: "Days", month: "Weeks", year: "Months" }[filter] || "Days");
        }

        function renderDashboard(data) {
            const setText = (id, value) => { const el = document.getElementById(id); if (el) el.innerText = value; };

            setText("totalBookings", data.totalBookings.toLocaleString());
            setText("totalRevenue", currency(data.totalRevenue));
            setText("confirmedBookings", data.confirmedBookings);
            setText("cancelledBookings", data.cancelledBookings);
            setText("avgSpend", currency(data.avgSpend));
            setText("totalCustomers", data.totalCustomers.toLocaleString());
            setText("totalSuppliers", data.totalSuppliers);
            setText("totalStaffs", data.totalStaffs);
            setText("vendorApproved", data.vendorApproved);
            setText("vendorPending", data.vendorPending);
            setText("vendorRejected", data.vendorRejected);
            setText("pendingBookingConfirm", data.pendingBookingConfirm);
            setText("pendingPayments", data.pendingPayments);
            setText("pendingVendorApproval", data.pendingVendorApproval);
            setText("pendingVendorApproval2", data.pendingVendorApproval);
            setText("peakPeriodLabel", data.peakPeriodLabel);
            setText("peakMonthTag", data.peakPeriod);

            document.getElementById("topSuppliersList").innerHTML = data.topSuppliers.map((supplier, index) => `
                <div class="queue-item" style="gap:0.625rem">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-6 w-6 items-center justify-center rounded-lg flex-shrink-0 font-bold text-white" style="font-size:10px;background:#d89aa0">${index + 1}</span>
                        <span class="font-semibold" style="font-size:12px;color:#1c1917">${supplier.name}</span>
                    </div>
                    <span class="font-semibold whitespace-nowrap" style="font-size:11px;color:#a8a29e">${supplier.bookings} events</span>
                </div>
            `).join("");

            document.getElementById("upcomingEventsBody").innerHTML = data.upcomingEvents.map((event) => {
                const isConfirmed = event.status === "confirmed";
                const statusClass = isConfirmed ? "badge-emerald" : "badge-amber";
                return `
                    <tr style="border-bottom:1px solid var(--dashboard-border)">
                        <td class="px-6 py-3"><span class="badge badge-muted">${event.event}</span></td>
                        <td class="px-6 py-3 font-semibold" style="color:#1c1917">${event.customer}</td>
                        <td class="px-6 py-3" style="color:#57534e">${event.dateTime}</td>
                        <td class="px-6 py-3" style="color:#57534e">${event.location}</td>
                        <td class="px-6 py-3"><span class="badge ${statusClass}">${event.status}</span></td>
                    </tr>
                `;
            }).join("");

            document.getElementById("popularGrid").innerHTML = data.popularPackages.map((pkg) => `
                <article class="pkg-card">
                    <!-- Full-height image -->
                    <div class="relative overflow-hidden" style="border-radius:1.2rem">
                        <img src="${pkg.image}" alt="${pkg.name}">

                        <!-- Rating badge — always visible top-right -->
                        <div class="absolute right-2.5 top-2.5 flex items-center gap-1 rounded-full px-2 py-0.5 font-bold" style="font-size:10px;background:rgba(255,255,255,0.95);color:#673049;box-shadow:0 2px 8px rgba(0,0,0,0.14);z-index:2">
                            ★ ${pkg.rating}
                        </div>

                        <!-- Caption — always visible bottom, fades on hover -->
                        <div class="pkg-caption">
                            <p class="font-semibold" style="font-size:12px;color:#ffffff;text-shadow:0 1px 4px rgba(0,0,0,0.5)">${pkg.name}</p>
                        </div>

                        <!-- Hover overlay: slides up from bottom -->
                        <div class="pkg-overlay">
                            <div>
                                <p class="stat-label" style="color:rgba(255,255,255,0.55)">Bookings</p>
                                <p class="font-bold" style="font-size:13px;color:#ffffff;margin-top:2px">${pkg.bookings.toLocaleString()}</p>
                            </div>
                            <div class="text-right">
                                <p class="stat-label" style="color:rgba(255,255,255,0.55)">Revenue</p>
                                <p class="font-bold" style="font-size:13px;color:#6ee7b7;margin-top:2px">${currency(pkg.revenue)}</p>
                            </div>
                        </div>
                    </div>
                </article>
            `).join("");

            if (revenueChart) revenueChart.destroy();
            if (supplierChart) supplierChart.destroy();

            const revenueContext = document.getElementById("revenueChartCanvas").getContext("2d");
            const revenueXAxisTitle = getRevenueXAxisTitle(currentFilter);
            revenueChart = new Chart(revenueContext, {
                type: "line",
                data: {
                    labels: data.revenueLabels,
                    datasets: [{
                        data: data.revenueSales,
                        borderColor: "#673049",
                        backgroundColor: "rgba(103,48,73,0.08)",
                        fill: true,
                        tension: 0.4,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        pointBackgroundColor: "#673049",
                        pointBorderColor: "#fff",
                        pointBorderWidth: 2,
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 1000,
                        easing: "easeInOutQuart",
                        x: { from: 0 },
                        y: { from: (ctx) => ctx.chart.scales.y.getPixelForValue(0) }
                    },
                    plugins: { legend: { display: false } },
                    scales: {
                        x: {
                            grid: { display: false },
                            border: { display: false },
                            title: {
                                display: true,
                                text: revenueXAxisTitle,
                                color: "#57534e",
                                font: { size: 11, weight: "700", family: "Inter" },
                                padding: { top: 8 }
                            },
                            ticks: { color: "#78716c", font: { size: 10, weight: "600", family: "Inter" } }
                        },
                        y: {
                            grid: { color: "#f5f5f4" },
                            border: { display: false },
                            title: {
                                display: true,
                                text: "Sales",
                                color: "#57534e",
                                font: { size: 11, weight: "700", family: "Inter" },
                                padding: { bottom: 8 }
                            },
                            ticks: {
                                color: "#78716c",
                                font: { size: 10, weight: "500", family: "Inter" },
                                callback: (v) => `$${v / 1000}k`
                            }
                        }
                    }
                }
            });

            const supplierContext = document.getElementById("supplierPieCanvas").getContext("2d");
            const supplierDonutLabels = {
                id: "supplierDonutLabels",
                afterDatasetsDraw(chart) {
                    const { ctx } = chart;
                    const dataset = chart.data.datasets[0];
                    const total = dataset.data.reduce((sum, value) => sum + value, 0);
                    const meta = chart.getDatasetMeta(0);
                    const centerX = meta.data[0].x;
                    const centerY = meta.data[0].y;

                    ctx.save();
                    ctx.textAlign = "center";
                    ctx.textBaseline = "middle";

                    ctx.fillStyle = "#1c1917";
                    ctx.font = "700 20px Inter, sans-serif";
                    ctx.fillText(total.toLocaleString(), centerX, centerY - 8);

                    ctx.fillStyle = "#a8a29e";
                    ctx.font = "600 9px Inter, sans-serif";
                    ctx.fillText("suppliers", centerX, centerY + 10);

                    meta.data.forEach((arc, index) => {
                        const value = dataset.data[index];
                        const percent = Math.round((value / total) * 100);
                        const angle = (arc.startAngle + arc.endAngle) / 2;
                        const radius = (arc.innerRadius + arc.outerRadius) / 2;
                        const x = arc.x + Math.cos(angle) * radius;
                        const y = arc.y + Math.sin(angle) * radius;

                        ctx.fillStyle = "#FFFFFF";
                        ctx.font = "700 10px Inter, sans-serif";
                        ctx.fillText(`${percent}%`, x, y);
                    });

                    ctx.restore();
                }
            };

            supplierChart = new Chart(supplierContext, {
                type: "doughnut",
                data: {
                    labels: data.supplierCategories.labels,
                    datasets: [{
                        data: data.supplierCategories.values,
                        backgroundColor: ["#90bed7", "#d89aa0", "#e9ab91", "#8bca9d"],
                        borderColor: "#FFFFFF",
                        borderRadius: 5,
                        borderWidth: 3,
                        hoverBorderWidth: 5,
                        hoverOffset: 14,
                        spacing: 2,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        animateRotate: true,
                        animateScale: true,
                        duration: 900,
                        easing: "easeOutBack"
                    },
                    hover: { mode: "nearest", intersect: true },
                    cutout: "54%",
                    rotation: -75,
                    plugins: {
                        legend: {
                            display: true,
                            position: "bottom",
                            labels: {
                                usePointStyle: true,
                                pointStyle: "circle",
                                padding: 16,
                                color: "#78716c",
                                font: { size: 11, weight: "600", family: "Inter" }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: (context) => `${context.label}: ${context.raw}`
                            }
                        }
                    }
                },
                plugins: [supplierDonutLabels]
            });

            refreshLucideIcons();
        }

        async function loadDashboardData() {
            if (isLoading) return;
            isLoading = true;
            try {
                const data = await fetchDashboardData(currentFilter);
                renderDashboard(data);
            } finally {
                isLoading = false;
            }
        }

        document.addEventListener("DOMContentLoaded", () => {
            /* Event filter dropdown */
            const evtBtn  = document.getElementById("eventFilterBtn");
            const evtMenu = document.getElementById("eventFilterMenu");
            const evtLbl  = document.getElementById("eventFilterLabel");
            const evtChev = document.getElementById("eventFilterChevron");
            const eventDateFilter = document.getElementById("eventDateFilter");
            const labels  = { today:"Today", week:"This week", month:"This month", year:"This year" };

            function closeEvtMenu() { evtMenu.classList.add("invisible","opacity-0"); evtBtn.setAttribute("aria-expanded","false"); }
            function openEvtMenu()  { evtMenu.classList.remove("invisible","opacity-0"); evtBtn.setAttribute("aria-expanded","true"); }
            evtBtn.addEventListener("click", e => { e.stopPropagation(); evtBtn.getAttribute("aria-expanded")==="true" ? closeEvtMenu() : openEvtMenu(); });
            document.querySelectorAll("[data-event-filter]").forEach(opt => {
                opt.addEventListener("click", () => {
                    currentFilter = opt.dataset.eventFilter;
                    evtLbl.innerText = labels[currentFilter];
                    document.querySelectorAll("[data-event-filter]").forEach(o => o.classList.toggle("active", o === opt));
                    closeEvtMenu();
                    loadDashboardData();
                });
            });
            document.addEventListener("click", e => { if (!evtBtn.contains(e.target) && !evtMenu.contains(e.target)) closeEvtMenu(); });
            const today = new Date();
            const maxDate = formatDateInputValue(today);
            eventDateFilter.max = maxDate;
            eventDateFilter.value = maxDate;
            eventDateFilter.addEventListener("change", () => {
                if (eventDateFilter.value > maxDate) eventDateFilter.value = maxDate;
                loadDashboardData();
            });

            refreshLucideIcons();
            loadDashboardData();
        });
    </script>
</body>
</html>
