
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Wedding Admin Dashboard | Premium</title>
    <?php $dashboardCssVersion = file_exists(APPROOT . '/../public/css/app.css') ? filemtime(APPROOT . '/../public/css/app.css') : time(); ?>
    <link rel="stylesheet" href="<?= URLROOT ?>/public/css/app.css?v=<?= $dashboardCssVersion ?>">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        :root {
            --dashboard-bg:             #F4F1EE;
            --dashboard-border:         #ead8c7;
            --dashboard-text:           #6d4c5b;
            --accent-color:             #6d4c5b;
            --dashboard-primary-soft:   rgba(154, 104, 127, 0.10);
            --dashboard-radius-control: 1rem;
            --dashboard-shadow-sm:      0 1px 3px rgba(0, 0, 0, 0.04);
            --dashboard-font:           'DM Sans', system-ui, sans-serif;
            --gold:                     #D8B46A;
            --wine-glow:               rgba(154, 104, 127, 0.10);
        }

        * { box-sizing: border-box; }

        body {
            font-family: var(--dashboard-font);
            font-size: 13.5px;
            line-height: 1.6;
            background-color: var(--dashboard-bg);
            color: var(--dashboard-text);
            -webkit-font-smoothing: antialiased;
            font-variant-numeric: tabular-nums;
        }

        main { position: relative; }

        /* ── Cards ── */
        .card {
            background: #FFFFFF;
            border: 1px solid #ead8c7;
            border-radius: 1rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            transition: box-shadow 0.3s cubic-bezier(0.19, 1, 0.22, 1);
        }
        .card:hover {
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
        }

        /* ── Typography ── */
        .dashboard-page-title {
            margin: 0;
            color: #6d4c5b;
            font-family: "Playfair Display", Georgia, serif;
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
            color: #6d4c5b;
            font-weight: 700;
            letter-spacing: -0.015em;
        }

        .stat-label {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.01em;
            text-transform: none;
            color: #A8A29E;
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
            background: #FAFAF9;
            border-radius: 0.75rem;
            padding: 0.55rem 0.75rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border: 1px solid #ead8c7;
            transition: background 0.2s ease;
        }
        .queue-item:hover {
            background: #F4F1EE;
        }

        .queue-item > div > p:first-child,
        .queue-item > span:first-child {
            color: #6d4c5b !important;
            font-weight: 600 !important;
        }

        /* ── Progress bar ── */
        .progress-track {
            height: 6px;
            border-radius: 9999px;
            background: rgba(252,248,245,0.10);
            overflow: hidden;
        }
        .progress-fill {
            height: 100%;
            border-radius: 9999px;
            background: #6d4c5b;
            transition: width 0.8s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        /* ── Tables ── */
        tbody tr { transition: background 0.2s ease; }
        tbody tr:hover { background: #FAFAF9; }

        thead th {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.055em;
            text-transform: uppercase;
            color: #A8A29E;
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
        .badge-emerald   { background: #ECFDF5; color: #065F46; }
        .badge-amber     { background: #FFFBEB; color: #92400E; }
        .badge-accent    { background: #F5F0F2; color: #6d4c5b; border: 1px solid #ead8c7; }
        .badge-muted     { background: #F5F5F4; color: #78716C; border: 1px solid #ead8c7; }
        .badge-processing{ background: #EEF2FF; color: #3730A3; }
        .badge-sky       { background: #F0F9FF; color: #0369A1; }

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
            background: #FFFFFF;
            border-radius: 1rem;
            border: 1px solid #ead8c7;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            transition: box-shadow 0.3s ease, transform 0.3s ease;
            position: relative;
        }
        .pkg-card:hover {
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.08);
            transform: translateY(-2px);
        }
        .pkg-card img {
            display: block;
            width: 100%;
            height: 220px;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        .pkg-card:hover img { transform: scale(1.05); }

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
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .animate-up { animation: fadeUp 0.5s cubic-bezier(0.19, 1, 0.22, 1) both; }

        /* ── Scrollbar ── */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #D6D3D1; border-radius: 999px; }
        ::-webkit-scrollbar-thumb:hover { background: #A8A29E; }

        /* ── Filter tab ── */
        .filter-tab-btn {
            padding: 0.3rem 0.875rem;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 600;
            border: 1px solid #ead8c7;
            background: #FAFAF9;
            color: #78716C;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .filter-tab-btn:hover { background: #F5F0F2; color: var(--accent-color); }
        .filter-tab-btn.active { background: var(--accent-color); color: #fff; border-color: var(--accent-color); }

        /* ── Dropdown menu ── */
        .dropdown-menu {
            position: absolute;
            right: 0;
            top: calc(100% + 6px);
            z-index: 30;
            width: 9rem;
            background: #FFFFFF;
            border: 1px solid #ead8c7;
            border-radius: 0.75rem;
            padding: 0.375rem;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
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
            color: #78716C;
            transition: background 0.15s ease;
            cursor: pointer;
            border: none;
            background: transparent;
        }
        .dropdown-item:hover { background: #F5F0F2; color: #6d4c5b; }
        .dropdown-item.active { background: #F5F0F2; font-weight: 600; color: #6d4c5b; }

        /* ── Admin Date Calendar Popover (matches customer service detail gp-calendar) ── */
        .venue-date-input-wrap {
            position: relative;
            min-height: 32px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border: 1px solid rgba(63, 36, 26, .18);
            border-radius: 6px;
            background: #FFF8EF;
            color: #3F241A;
            padding: 0 10px;
            font-size: 12px;
            font-weight: 800;
            cursor: pointer;
            overflow: hidden;
            box-shadow: 0 4px 14px rgba(63, 36, 26, .06);
        }
        .venue-date-input-wrap .venue-date-icon { width: 14px; height: 14px; color: #7A4E3D; }
        .venue-date-input-wrap .venue-date-chevron { width: 12px; height: 12px; color: #7A4E3D; }
        .venue-date-input-wrap .gp-calendar-input {
            position: absolute; inset: 0; opacity: 0; cursor: pointer; border: 0; width: 100%; height: 100%;
        }
        .gp-calendar-popover {
            position: absolute;
            left: 0;
            top: calc(100% + 8px);
            z-index: 60;
            width: min(250px, calc(100vw - 32px));
            padding: 12px;
            border: 1px solid rgba(63, 36, 26, .14);
            border-radius: 10px;
            background: rgba(255, 248, 239, .98);
            box-shadow: 0 24px 60px rgba(63, 36, 26, .18);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }
        .gp-calendar-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            color: #3F241A;
            font-size: 12px;
            font-weight: 900;
            margin-bottom: 9px;
        }
        .gp-calendar-nav {
            width: 22px;
            height: 22px;
            display: inline-grid;
            place-items: center;
            border: 0;
            border-radius: 7px;
            background: transparent;
            color: #7A4E3D;
            cursor: pointer;
            font-size: 14px;
            line-height: 1;
        }
        .gp-calendar-nav:hover { background: rgba(63, 36, 26, .08); }
        .gp-calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 3px;
        }
        .gp-calendar-day-name,
        .gp-calendar-day {
            display: grid;
            place-items: center;
            height: 24px;
            color: #6F5448;
            font-size: 11px;
            font-family: 'DM Sans', sans-serif;
        }
        .gp-calendar-day-name {
            color: rgba(63, 36, 26, .52);
            font-weight: 800;
        }
        .gp-calendar-day {
            border: 0;
            border-radius: 6px;
            background: transparent;
            font-weight: 800;
            cursor: pointer;
        }
        .gp-calendar-day:hover { background: rgba(122, 78, 61, .12); }
        .gp-calendar-day.is-selected {
            background: #3F241A;
            color: #FFF8EF;
        }
        .gp-calendar-day.is-today:not(.is-selected) {
            outline: 1px solid rgba(63, 36, 26, .28);
        }
        .gp-calendar-day.is-disabled {
            color: rgba(63, 36, 26, .24);
            cursor: not-allowed;
        }
        .gp-calendar-footer {
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid rgba(63, 36, 26, .10);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .gp-calendar-footer button {
            border: 0; border-radius: 999px;
            padding: 4px 10px;
            font-size: 10px;
            font-weight: 800;
            cursor: pointer;
            transition: background .12s ease;
            font-family: 'DM Sans', sans-serif;
        }
        .gp-cal-today-btn {
            background: rgba(63, 36, 26, .08);
            color: #3F241A;
        }
        .gp-cal-today-btn:hover { background: rgba(63, 36, 26, .14); }
        .gp-cal-clear-btn {
            background: transparent;
            color: #7A4E3D;
        }
        .gp-cal-clear-btn:hover { background: rgba(63, 36, 26, .08); }
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
            <div class="relative" id="adminDatePickerWrap">
                <span class="venue-date-input-wrap" id="adminDatePickerBtn">
                    <i class="venue-date-icon" data-lucide="calendar-days"></i>
                    <span class="venue-date-display" id="adminDatePickerLabel">Today</span>
                    <i class="venue-date-chevron" data-lucide="chevron-down"></i>
                </span>
                <div id="adminDatePickerPopover" class="gp-calendar-popover" hidden></div>
            </div>
            <div class="relative">
                <button id="eventFilterBtn" type="button" aria-expanded="false"
                    class="flex h-8 items-center gap-1.5 rounded-lg border border-stone-200 bg-white px-3 text-xs font-semibold shadow-sm focus:outline-none"
                    style="color:#6d4c5b">
                    <i data-lucide="calendar" class="h-3 w-3" style="color:#A8A29E"></i>
                    <span id="eventFilterLabel">This week</span>
                    <i data-lucide="chevron-down" class="h-3 w-3 transition-transform" id="eventFilterChevron" style="color:#A8A29E"></i>
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
                <h2 id="totalRevenue" class="section-title dashboard-fact mt-1.5 text-2xl" style="color:#6d4c5b">Ks 63,400</h2>
                <hr class="divider mt-4 mb-3">
                <p class="stat-label">Avg Customer Spend</p>
                <p id="avgSpend" class="dashboard-fact mt-1 text-base font-bold" style="color:#6d4c5b">Ks 200</p>
            </div>

            <!-- Total Bookings -->
            <div class="card p-5 animate-up" style="animation-delay:0.14s">
                <div class="icon-wrap mb-3 h-8 w-8" style="background:#f0f9ff">
                    <i data-lucide="calendar-check" class="h-4 w-4" style="color:#0284c7"></i>
                </div>
                <p class="stat-label">Total Bookings</p>
                <h2 id="totalBookings" class="section-title dashboard-fact mt-1.5 text-2xl" style="color:#6d4c5b">317</h2>
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
                <h3 class="mb-4 flex items-center gap-2 font-bold" style="font-size:13px;color:#6d4c5b">
                    <i data-lucide="list-checks" class="h-4 w-4" style="color:#6d4c5b"></i>
                    Operations Queue
                </h3>
                <div class="space-y-2">
                    <div class="queue-item">
                        <div>
                            <p style="font-size:11px;font-weight:600;color:#6d4c5b">Booking Confirmations</p>
                            <p class="stat-label" style="margin-top:1px">Pending review</p>
                        </div>
                        <span id="pendingBookingConfirm" class="badge badge-amber">--</span>
                    </div>
                    <div class="queue-item">
                        <div>
                            <p style="font-size:11px;font-weight:600;color:#6d4c5b">Payments</p>
                            <p class="stat-label" style="margin-top:1px">Awaiting release</p>
                        </div>
                        <span id="pendingPayments" class="badge badge-processing">--</span>
                    </div>
                    <div class="queue-item">
                        <div>
                            <p style="font-size:11px;font-weight:600;color:#6d4c5b">Vendor Approvals</p>
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
                        <h2 class="section-title" style="font-size:13px;color:#6d4c5b">Revenue Trend</h2>
                        <p class="mt-0.5" style="font-size:11px;color:#A8A29E">Performance of selected packages</p>
                    </div>
                    <span class="inline-flex items-center gap-1 rounded-full border border-app-gold/20 bg-app-gold-soft px-2.5 py-1 text-[10px] font-bold text-app-gold">
                        <span id="peakPeriodLabel">PEAK DAY:</span> <span id="peakMonthTag" class="ml-1">--</span>
                    </span>
                </div>
                <div style="height:260px">
                    <canvas id="revenueChartCanvas"></canvas>
                </div>
            </div>

            <!-- Supplier Categories -->
            <div class="card p-5 lg:col-span-4 animate-up" style="animation-delay:0.35s">
                <h2 class="section-title" style="font-size:13px;color:#6d4c5b">Supplier Categories</h2>
                <p class="mt-0.5 mb-4" style="font-size:11px;color:#A8A29E; margin-bottom: 30px;">Current market distribution</p>
                <div style="height:320px; margin-top:20px;">
                    <canvas id="supplierPieCanvas"></canvas>
                </div>
            </div>
        </section>

        <!-- ── ROW 3: Partners / Vendor / Community ── -->
        <section class="mb-4 grid grid-cols-1 gap-3 lg:grid-cols-4">

            <!-- Top Partners -->
            <div class="card p-5 lg:col-span-2 animate-up" style="animation-delay:0.42s">
                <h3 class="mb-4 flex items-center gap-2 font-bold" style="font-size:13px;color:#6d4c5b">
                    <i data-lucide="award" class="h-4 w-4" style="color:#b45309"></i> Top Partners
                </h3>
                <div id="topSuppliersList" class="space-y-2"></div>
            </div>

            <!-- Vendor Status -->
            <div class="card p-5 animate-up" style="animation-delay:0.56s">
                <h3 class="mb-4 flex items-center gap-2 font-bold" style="font-size:13px;color:#6d4c5b">
                    <i data-lucide="store" class="h-4 w-4" style="color:#12bb8b"></i> Vendor Status
                </h3>
                <div class="space-y-2.5">
                    <div class="flex items-center justify-between py-1" style="font-size:12px">
                        <span style="color:#78716C">Approved</span>
                        <span id="vendorApproved" class="font-bold" style="color:#12bb8b">--</span>
                    </div>
                    <div class="flex items-center justify-between py-1" style="font-size:12px">
                        <span style="color:#78716C">Pending Approval</span>
                        <span id="vendorPending" class="font-bold" style="color:#d4be50">--</span>
                    </div>
                    <div class="flex items-center justify-between py-1" style="font-size:12px">
                        <span style="color:#78716C">Rejected</span>
                        <span id="vendorRejected" class="font-bold" style="color:#e23535">--</span>
                    </div>
                </div>
                <hr class="divider my-4">
                <div class="queue-item">
                    <span class="font-medium italic" style="font-size:12px;color:#78716C">Actions Needed</span>
                    <span id="pendingVendorApproval2" class="badge badge-accent">--</span>
                </div>
            </div>

            <!-- Community -->
            <div class="card p-5 animate-up" style="animation-delay:0.63s">
                <h3 class="mb-4 flex items-center gap-2 font-bold" style="font-size:13px;color:#6d4c5b">
                    <i data-lucide="users" class="h-4 w-4" style="color:#6d4c5b"></i> Community
                </h3>
                <div class="space-y-2">
                    <div class="queue-item">
                        <span class="font-medium" style="font-size:12px;color:#78716C">Customers</span>
                        <span id="totalCustomers" class="font-bold" style="color:#6d4c5b">--</span>
                    </div>
                    <div class="queue-item">
                        <span class="font-medium" style="font-size:12px;color:#78716C">Suppliers</span>
                        <span id="totalSuppliers" class="font-bold" style="color:#6d4c5b">--</span>
                    </div>
                    <div class="queue-item">
                        <span class="font-medium" style="font-size:12px;color:#78716C">Staff Members</span>
                        <span id="totalStaffs" class="font-bold" style="color:#6d4c5b">--</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── Upcoming Events Table ── -->
        <section class="mb-4 card overflow-hidden animate-up" style="animation-delay:0.70s">
            <div class="p-5 flex flex-wrap items-center justify-between gap-3" style="border-bottom:1px solid var(--dashboard-border)">
                <div>
                    <h2 class="section-title" style="font-size:14px;color:#6d4c5b">Upcoming Wedding Events</h2>
                    <p class="mt-0.5" style="font-size:11px;color:#A8A29E">Live schedule of ceremonies and receptions</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left">
                    <thead style="background:#FAFAF9">
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
                <h2 class="section-title" style="font-size:14px;color:#6d4c5b">Popular Wedding Packages</h2>
                <p class="mt-0.5 italic" style="font-size:11px;color:#A8A29E">"Sparkling Eve" is trending this week</p>
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
            if (adminCalSelectedDate) {
                params.set('date', adminCalSelectedDate);
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
        let adminCalSelectedDate = null;

        function currency(value) { return `Ks ${value.toLocaleString()}`; }
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
                        <span class="flex h-6 w-6 items-center justify-center rounded-lg flex-shrink-0 font-bold text-white" style="font-size:10px;background:#D8B46A">${index + 1}</span>
                        <span class="font-semibold" style="font-size:12px;color:#6d4c5b">${supplier.name}</span>
                    </div>
                    <span class="font-semibold whitespace-nowrap" style="font-size:11px;color:#A8A29E">${supplier.bookings} events</span>
                </div>
            `).join("");

            document.getElementById("upcomingEventsBody").innerHTML = data.upcomingEvents.map((event) => {
                const isConfirmed = event.status === "confirmed";
                const statusClass = isConfirmed ? "badge-emerald" : "badge-amber";
                return `
                    <tr style="border-bottom:1px solid var(--dashboard-border)">
                        <td class="px-6 py-3"><span class="badge badge-muted">${event.event}</span></td>
                        <td class="px-6 py-3 font-semibold" style="color:#6d4c5b">${event.customer}</td>
                        <td class="px-6 py-3" style="color:#78716C">${event.dateTime}</td>
                        <td class="px-6 py-3" style="color:#78716C">${event.location}</td>
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
                        <div class="absolute right-2.5 top-2.5 flex items-center gap-1 rounded-full px-2 py-0.5 font-bold" style="font-size:10px;background:rgba(252,248,245,0.95);color:#6d4c5b;box-shadow:0 2px 8px rgba(0,0,0,0.14);z-index:2">
                            ★ ${pkg.rating}
                        </div>

                        <!-- Caption — always visible bottom, fades on hover -->
                        <div class="pkg-caption">
                            <p class="font-semibold" style="font-size:12px;color:#FFFFFF;text-shadow:0 1px 4px rgba(0,0,0,0.5)">${pkg.name}</p>
                        </div>

                        <!-- Hover overlay: slides up from bottom -->
                        <div class="pkg-overlay">
                            <div>
                                <p class="stat-label" style="color:rgba(252,248,245,0.55)">Bookings</p>
                                <p class="font-bold" style="font-size:13px;color:#FFFFFF;margin-top:2px">${pkg.bookings.toLocaleString()}</p>
                            </div>
                            <div class="text-right">
                                <p class="stat-label" style="color:rgba(252,248,245,0.55)">Revenue</p>
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
                        label: "Revenue",
                        data: data.revenueSales,
                        borderColor: "#6d4c5b",
                        backgroundColor: "rgba(154, 104, 127, 0.08)",
                        fill: true,
                        tension: 0.4,
                        pointRadius: 3,
                        pointHoverRadius: 6,
                        pointBackgroundColor: "#6d4c5b",
                        pointBorderColor: "#FFF8EF",
                        pointBorderWidth: 2,
                        borderWidth: 2.5
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
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            align: 'end',
                            labels: {
                                color: "#78716C",
                                font: { size: 11, weight: "600", family: "DM Sans" },
                                boxWidth: 12,
                                boxHeight: 2,
                                usePointStyle: false,
                                padding: 16
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            border: { display: false },
                            title: {
                                display: true,
                                text: revenueXAxisTitle,
                                color: "#A8A29E",
                                font: { size: 11, weight: "700", family: "DM Sans" },
                                padding: { top: 8 }
                            },
                            ticks: { color: "#A8A29E", font: { size: 10, weight: "600", family: "DM Sans" } }
                        },
                        y: {
                            grid: { color: "#F5F5F4" },
                            border: { display: false },
                            title: {
                                display: true,
                                text: "Revenue (Ks)",
                                color: "#A8A29E",
                                font: { size: 11, weight: "700", family: "DM Sans" },
                                padding: { bottom: 8 }
                            },
                            ticks: {
                                color: "#A8A29E",
                                font: { size: 10, weight: "500", family: "DM Sans" },
                                callback: (v) => v >= 1000 ? `${(v / 1000).toFixed(v % 1000 === 0 ? 0 : 1)}k` : v.toLocaleString()
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

                    ctx.fillStyle = "#6d4c5b";
                    ctx.font = "700 20px 'DM Sans', sans-serif";
                    ctx.fillText(total.toLocaleString(), centerX, centerY - 8);

                    ctx.fillStyle = "#9A8C84";
                    ctx.font = "600 9px 'DM Sans', sans-serif";
                    ctx.fillText("suppliers", centerX, centerY + 10);

                    meta.data.forEach((arc, index) => {
                        const value = dataset.data[index];
                        const percent = Math.round((value / total) * 100);
                        const angle = (arc.startAngle + arc.endAngle) / 2;
                        const radius = (arc.innerRadius + arc.outerRadius) / 2;
                        const x = arc.x + Math.cos(angle) * radius;
                        const y = arc.y + Math.sin(angle) * radius;

                        ctx.fillStyle = "#FFF8EF";
                        ctx.font = "700 10px 'DM Sans', sans-serif";
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
                        backgroundColor: ["#90bed7", "#d89aa0", "#D8B46A", "#8bca9d"],
                        borderColor: "#FFF8EF",
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
                                color: "#A8A29E",
                                font: { size: 11, weight: "600", family: "DM Sans" }
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

            /* ── Admin Date Calendar Picker (gp-calendar style from service detail) ── */
            const calPopover   = document.getElementById('adminDatePickerPopover');
            const calBtn       = document.getElementById('adminDatePickerBtn');
            const calLabel     = document.getElementById('adminDatePickerLabel');
            const today        = new Date();
            const MONTH_SHORT  = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

            let adminCalMonth = null;
            adminCalSelectedDate = formatDateInputValue(today);

            function updateAdminCalDisplay() {
                const todayValue = formatDateInputValue(new Date());
                if (adminCalSelectedDate === todayValue) {
                    calLabel.textContent = 'Today';
                } else if (adminCalSelectedDate) {
                    const parsed = adminCalSelectedDate.split('-').map(Number);
                    const d = new Date(parsed[0], parsed[1] - 1, parsed[2]);
                    calLabel.textContent = d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                } else {
                    calLabel.textContent = 'All dates';
                }
            }

            function renderAdminCalendar() {
                if (!adminCalMonth) return;
                const monthStart = new Date(adminCalMonth.getFullYear(), adminCalMonth.getMonth(), 1);
                const selectedValue = adminCalSelectedDate || '';
                const todayValue = formatDateInputValue(new Date());
                const daysInMonth = new Date(monthStart.getFullYear(), monthStart.getMonth() + 1, 0).getDate();
                const leadingBlanks = monthStart.getDay(); // Sunday = 0
                const monthTitle = monthStart.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
                const dayNames = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'];

                let html = '<div class="gp-calendar-head">' +
                    '<button class="gp-calendar-nav" type="button" data-cal-prev>&#8249;</button>' +
                    '<span>' + monthTitle + '</span>' +
                    '<button class="gp-calendar-nav" type="button" data-cal-next>&#8250;</button>' +
                    '</div><div class="gp-calendar-grid">';

                dayNames.forEach(d => { html += '<div class="gp-calendar-day-name">' + d + '</div>'; });
                for (let i = 0; i < leadingBlanks; i++) html += '<span></span>';
                for (let day = 1; day <= daysInMonth; day++) {
                    const value = monthStart.getFullYear() + '-' + String(monthStart.getMonth() + 1).padStart(2, '0') + '-' + String(day).padStart(2, '0');
                    const classes = ['gp-calendar-day'];
                    if (value === selectedValue) classes.push('is-selected');
                    if (value === todayValue) classes.push('is-today');
                    html += '<button class="' + classes.join(' ') + '" type="button" data-date="' + value + '">' + day + '</button>';
                }
                html += '</div>';

                // Footer with Today and Clear
                html += '<div class="gp-calendar-footer">';
                html += '<button type="button" class="gp-cal-clear-btn" data-cal-clear>Clear</button>';
                html += '<button type="button" class="gp-cal-today-btn" data-cal-today>Today</button>';
                html += '</div>';

                calPopover.innerHTML = html;
            }

            calPopover.addEventListener('click', e => {
                e.stopPropagation();
                const prev = e.target.closest('[data-cal-prev]');
                const next = e.target.closest('[data-cal-next]');
                const day  = e.target.closest('[data-date]');
                const todayBtn = e.target.closest('[data-cal-today]');
                const clearBtn = e.target.closest('[data-cal-clear]');

                if (prev) {
                    adminCalMonth = new Date(adminCalMonth.getFullYear(), adminCalMonth.getMonth() - 1, 1);
                    renderAdminCalendar();
                    return;
                }
                if (next) {
                    adminCalMonth = new Date(adminCalMonth.getFullYear(), adminCalMonth.getMonth() + 1, 1);
                    renderAdminCalendar();
                    return;
                }
                if (day) {
                    adminCalSelectedDate = day.dataset.date;
                    updateAdminCalDisplay();
                    calPopover.hidden = true;
                    loadDashboardData();
                    return;
                }
                if (todayBtn) {
                    const now = new Date();
                    adminCalMonth = new Date(now.getFullYear(), now.getMonth(), 1);
                    adminCalSelectedDate = formatDateInputValue(now);
                    updateAdminCalDisplay();
                    calPopover.hidden = true;
                    loadDashboardData();
                    return;
                }
                if (clearBtn) {
                    adminCalSelectedDate = null;
                    updateAdminCalDisplay();
                    calPopover.hidden = true;
                    loadDashboardData();
                }
            });

            calPopover.addEventListener('mousedown', e => {
                e.preventDefault();
                e.stopPropagation();
            });

            // Toggle popover
            calBtn.addEventListener('click', e => {
                e.stopPropagation();
                if (calPopover.hidden) {
                    const base = adminCalSelectedDate ? adminCalSelectedDate.split('-').map(Number) : [today.getFullYear(), today.getMonth(), today.getDate()];
                    adminCalMonth = new Date(base[0], base[1] - 1, 1);
                    renderAdminCalendar();
                    calPopover.hidden = false;
                } else {
                    calPopover.hidden = true;
                }
            });

            // Close on outside click
            document.addEventListener('click', e => {
                if (!calPopover.hidden && !calPopover.contains(e.target) && !calBtn.contains(e.target)) {
                    calPopover.hidden = true;
                }
            });

            window.addEventListener('scroll', () => {
                if (calPopover && !calPopover.hidden) calPopover.hidden = true;
            }, { passive: true });

            // Set initial label
            updateAdminCalDisplay();

            refreshLucideIcons();
            loadDashboardData();
        });
    </script>
</body>
</html>
