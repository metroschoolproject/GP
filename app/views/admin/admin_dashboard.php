<?php
$adminCardClass = 'rounded-card border border-app-border bg-app-input shadow-sm transition-shadow hover:shadow-card';
$adminDarkCardClass = 'relative overflow-hidden rounded-card bg-app-text shadow-xl';
$adminIconWrapClass = 'flex items-center justify-center rounded-lg shrink-0';
$adminStatLabelClass = 'text-[10px] font-semibold uppercase tracking-widest text-app-muted';
$adminSectionTitleClass = 'font-bold tracking-tight text-app-text';
$adminQueueItemClass = 'flex items-center justify-between rounded-xl border border-transparent bg-app-soft px-3 py-2.5 transition-all hover:border-app-border hover:bg-app-surface';
$adminBadgeBaseClass = 'inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide';
$adminBadgeSuccessClass = $adminBadgeBaseClass . ' bg-app-soft text-app-success';
$adminBadgeWarningClass = $adminBadgeBaseClass . ' bg-app-surface text-app-warning';
$adminBadgeAccentClass = $adminBadgeBaseClass . ' border border-app-panel-border bg-app-danger-soft text-app-primary';
$adminBadgeMutedClass = $adminBadgeBaseClass . ' border border-app-border bg-app-soft text-app-secondary';
$adminBadgeProcessingClass = $adminBadgeBaseClass . ' bg-app-ring text-app-accent';
$adminControlClass = 'flex h-8 items-center gap-1.5 rounded-xl border border-app-border bg-app-input px-3 text-xs font-semibold text-app-primary shadow-sm focus:outline-none focus:ring-2 focus:ring-app-ring';
$adminDropdownClass = 'invisible absolute right-0 top-[calc(100%+6px)] z-30 w-36 rounded-xl border border-app-border bg-app-input p-1.5 opacity-0 shadow-lg transition-all duration-150';
$adminDropdownItemClass = 'flex w-full items-center rounded-lg px-3 py-1.5 text-left text-xs font-medium text-app-primary transition hover:bg-app-soft hover:text-app-accent';
$adminDropdownItemActiveClass = 'flex w-full items-center rounded-lg bg-app-soft px-3 py-1.5 text-left text-xs font-semibold text-app-accent transition hover:bg-app-surface';
$adminTableHeadClass = 'px-5 py-2.5 text-left text-[10px] font-semibold uppercase tracking-widest text-app-muted';
$adminWideTableHeadClass = 'px-6 py-3 text-left text-[10px] font-semibold uppercase tracking-widest text-app-muted';
$adminCssVersion = file_exists(APPROOT . '/../public/css/app.css') ? filemtime(APPROOT . '/../public/css/app.css') : time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Wedding Admin Dashboard | Premium</title>
    <link rel="stylesheet" href="<?= URLROOT ?>/public/css/app.css?v=<?= $adminCssVersion ?>">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        .pkg-card {
            transition: box-shadow 0.28s ease, transform 0.28s ease;
        }
        .pkg-card:hover {
            transform: translateY(-3px);
        }
        .pkg-card img {
            transition: transform 0.5s ease;
        }
        .pkg-card:hover img { transform: scale(1.07); }

        .pkg-card .pkg-overlay {
            position: absolute;
            bottom: 0; left: 0; right: 0;
            background: linear-gradient(to top, rgba(28,25,23,0.88) 0%, rgba(28,25,23,0.0) 100%);
            transform: translateY(100%);
            transition: transform 0.35s cubic-bezier(0.34, 1.20, 0.64, 1);
        }
        .pkg-card:hover .pkg-overlay { transform: translateY(0); }

        .pkg-card .pkg-caption {
            position: absolute;
            bottom: 0; left: 0; right: 0;
            background: linear-gradient(to top, rgba(28,25,23,0.60) 0%, transparent 100%);
            pointer-events: none;
        }
        .pkg-card:hover .pkg-caption { opacity: 0; transition: opacity 0.18s ease; }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(8px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .animate-up { animation: fadeUp 0.35s ease both; }

        /* ── Scrollbar ── */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #c8b1a1; border-radius: 3px; }
    </style>
</head>
<body class="bg-app-content font-ui text-[13px] text-app-text antialiased">
    <main class="mx-auto max-w-[1600px] px-5 py-6">
        <div class="mb-4 flex flex-wrap justify-end gap-2">
            <label class="<?= $adminControlClass ?>">
                <i data-lucide="calendar-days" class="h-3 w-3 text-app-muted"></i>
                <input id="eventDateFilter" type="date" class="bg-transparent text-xs font-semibold text-app-primary focus:outline-none">
            </label>
            <div class="relative">
                <button id="eventFilterBtn" type="button" aria-expanded="false"
                    class="<?= $adminControlClass ?>">
                    <i data-lucide="calendar" class="h-3 w-3 text-app-muted"></i>
                    <span id="eventFilterLabel">This week</span>
                    <i data-lucide="chevron-down" class="h-3 w-3 text-app-muted transition-transform" id="eventFilterChevron"></i>
                </button>
                <div id="eventFilterMenu" class="<?= $adminDropdownClass ?>">
                    <button type="button" data-event-filter="today"  class="<?= $adminDropdownItemClass ?>">Today</button>
                    <button type="button" data-event-filter="week"   class="<?= $adminDropdownItemActiveClass ?>">This week</button>
                    <button type="button" data-event-filter="month"  class="<?= $adminDropdownItemClass ?>">This month</button>
                    <button type="button" data-event-filter="year"   class="<?= $adminDropdownItemClass ?>">This year</button>
                </div>
            </div>
        </div>

        <!-- ── ROW 1: KPI cards ── -->
        <section class="mb-4 grid grid-cols-1 gap-3 lg:grid-cols-4">

            <!-- Escrow Wallet -->
            <div class="<?= $adminDarkCardClass ?> p-5 text-app-white animate-up">
                <div class="relative z-10">
                    <div class="mb-4 flex items-center gap-2.5">
                        <div class="<?= $adminIconWrapClass ?> h-8 w-8 bg-app-input/10">
                            <i data-lucide="wallet" class="h-4 w-4 text-app-muted"></i>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-app-white">Escrow Wallet</p>
                            <p class="mt-0.5 text-[11px] text-app-muted">Protected wedding payments</p>
                        </div>
                    </div>
                    <div class="mt-4 grid grid-cols-2 gap-4">
                        <div>
                            <p class="<?= $adminStatLabelClass ?>">Total</p>
                            <p id="escrowTotal" class="mt-1 text-xl font-bold tracking-tight">$38,482</p>
                        </div>
                        <div class="text-right">
                            <p class="<?= $adminStatLabelClass ?>">Pending</p>
                            <p id="escrowPending" class="mt-1 text-xl font-bold tracking-tight text-app-muted">$13,260</p>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="mb-1.5 flex justify-between text-[10px] font-semibold uppercase tracking-widest text-app-muted">
                            <span><span id="escrowAvailable">$25,222</span> available</span>
                            <span id="escrowProgressPercent">0%</span>
                        </div>
                        <div class="h-1.5 overflow-hidden rounded-full bg-app-input/10">
                            <div id="escrowProgressFill" class="h-full w-0 rounded-full bg-app-accent transition-all duration-700"></div>
                        </div>
                    </div>
                </div>
                <i data-lucide="sparkles" class="absolute -right-6 -top-6 h-28 w-28 rotate-12 text-app-white/5"></i>
            </div>

            <!-- Total Revenue -->
            <div class="<?= $adminCardClass ?> p-5 animate-up">
                <div class="<?= $adminIconWrapClass ?> mb-3 h-8 w-8 bg-app-danger-soft">
                    <i data-lucide="badge-dollar-sign" class="h-4 w-4 text-app-danger"></i>
                </div>
                <p class="<?= $adminStatLabelClass ?>">Total Revenue</p>
                <h2 id="totalRevenue" class="<?= $adminSectionTitleClass ?> mt-1.5 text-2xl">$63,400</h2>
                <hr class="mb-3 mt-4 border-app-panel-border">
                <p class="<?= $adminStatLabelClass ?>">Avg Customer Spend</p>
                <p id="avgSpend" class="mt-1 text-base font-bold text-app-text">$200</p>
            </div>

            <!-- Total Bookings -->
            <div class="<?= $adminCardClass ?> p-5 animate-up">
                <div class="<?= $adminIconWrapClass ?> mb-3 h-8 w-8 bg-app-soft">
                    <i data-lucide="calendar-check" class="h-4 w-4 text-app-accent"></i>
                </div>
                <p class="<?= $adminStatLabelClass ?>">Total Bookings</p>
                <h2 id="totalBookings" class="<?= $adminSectionTitleClass ?> mt-1.5 text-2xl">317</h2>
                <hr class="mb-3 mt-4 border-app-panel-border">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <p class="<?= $adminStatLabelClass ?>">Confirmed</p>
                        <p id="confirmedBookings" class="mt-1 text-lg font-bold text-app-success">217</p>
                    </div>
                    <div>
                        <p class="<?= $adminStatLabelClass ?>">Cancelled</p>
                        <p id="cancelledBookings" class="mt-1 text-lg font-bold text-app-danger">33</p>
                    </div>
                </div>
            </div>

            <!-- Operations Queue -->
            <div class="<?= $adminCardClass ?> p-5 animate-up">
                <h3 class="mb-4 flex items-center gap-2 text-[13px] font-bold text-app-text">
                    <i data-lucide="list-checks" class="h-4 w-4 text-app-primary"></i>
                    Operations Queue
                </h3>
                <div class="space-y-2">
                    <div class="<?= $adminQueueItemClass ?>">
                        <div>
                            <p class="text-[11px] font-semibold text-app-text">Booking Confirmations</p>
                            <p class="<?= $adminStatLabelClass ?> mt-0.5">Pending review</p>
                        </div>
                        <span id="pendingBookingConfirm" class="<?= $adminBadgeWarningClass ?>">--</span>
                    </div>
                    <div class="<?= $adminQueueItemClass ?>">
                        <div>
                            <p class="text-[11px] font-semibold text-app-text">Payments</p>
                            <p class="<?= $adminStatLabelClass ?> mt-0.5">Awaiting release</p>
                        </div>
                        <span id="pendingPayments" class="<?= $adminBadgeProcessingClass ?>">--</span>
                    </div>
                    <div class="<?= $adminQueueItemClass ?>">
                        <div>
                            <p class="text-[11px] font-semibold text-app-text">Vendor Approvals</p>
                            <p class="<?= $adminStatLabelClass ?> mt-0.5">Needs action</p>
                        </div>
                        <span id="pendingVendorApproval" class="<?= $adminBadgeAccentClass ?>">--</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── ROW 2: Charts ── -->
        <section class="mb-4 grid grid-cols-1 gap-3 lg:grid-cols-12">

            <!-- Revenue Trend -->
            <div class="<?= $adminCardClass ?> p-5 lg:col-span-8 animate-up">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="<?= $adminSectionTitleClass ?> text-[13px]">Revenue Trend</h2>
                        <p class="mt-0.5 text-[11px] text-app-muted">Performance across selected packages</p>
                    </div>
                    <span class="inline-flex items-center gap-1 rounded-full border border-app-panel-border bg-app-danger-soft px-2.5 py-1 text-[10px] font-bold text-app-danger">
                        <span id="peakPeriodLabel">PEAK DAY:</span> <span id="peakMonthTag" class="ml-1">--</span>
                    </span>
                </div>
                <div class="h-[260px]">
                    <canvas id="revenueChartCanvas"></canvas>
                </div>
            </div>

            <!-- Supplier Categories -->
            <div class="<?= $adminCardClass ?> p-5 lg:col-span-4 animate-up">
                <h2 class="<?= $adminSectionTitleClass ?> text-[13px]">Supplier Categories</h2>
                <p class="mb-7 mt-0.5 text-[11px] text-app-muted">Current market distribution</p>
                <div class="mt-5 h-[320px]">
                    <canvas id="supplierPieCanvas"></canvas>
                </div>
            </div>
        </section>

        <!-- ── ROW 3: Partners / Staff / Vendor / Community ── -->
        <section class="mb-4 grid grid-cols-1 gap-3 lg:grid-cols-6">

            <!-- Top Partners -->
            <div class="<?= $adminCardClass ?> p-5 lg:col-span-2 animate-up">
                <h3 class="mb-4 flex items-center gap-2 text-[13px] font-bold text-app-text">
                    <i data-lucide="award" class="h-4 w-4 text-app-warning"></i> Top Partners
                </h3>
                <div id="topSuppliersList" class="space-y-2"></div>
            </div>

            <!-- Staff Performance -->
            <div class="<?= $adminCardClass ?> overflow-hidden lg:col-span-2 animate-up">
                <div class="p-5 pb-3">
                    <h3 class="flex items-center gap-2 text-[13px] font-bold text-app-text">
                        <i data-lucide="user-round-check" class="h-4 w-4 text-app-secondary"></i> Staff Performance
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left">
                        <thead class="border-y border-app-panel-border bg-app-soft">
                            <tr>
                                <th class="<?= $adminTableHeadClass ?>">Member</th>
                                <th class="<?= $adminTableHeadClass ?>">Bookings</th>
                                <th class="<?= $adminTableHeadClass ?>">Success</th>
                            </tr>
                        </thead>
                        <tbody id="staffTableBody"></tbody>
                    </table>
                </div>
            </div>

            <!-- Vendor Status -->
            <div class="<?= $adminCardClass ?> p-5 animate-up">
                <h3 class="mb-4 flex items-center gap-2 text-[13px] font-bold text-app-text">
                    <i data-lucide="store" class="h-4 w-4 text-app-success"></i> Vendor Status
                </h3>
                <div class="space-y-2.5">
                    <div class="flex items-center justify-between py-1 text-xs">
                        <span class="text-app-secondary">Approved</span>
                        <span id="vendorApproved" class="font-bold text-app-success">--</span>
                    </div>
                    <div class="flex items-center justify-between py-1 text-xs">
                        <span class="text-app-secondary">Pending Approval</span>
                        <span id="vendorPending" class="font-bold text-app-warning">--</span>
                    </div>
                    <div class="flex items-center justify-between py-1 text-xs">
                        <span class="text-app-secondary">Rejected</span>
                        <span id="vendorRejected" class="font-bold text-app-danger">--</span>
                    </div>
                </div>
                <hr class="my-4 border-app-panel-border">
                <div class="<?= $adminQueueItemClass ?>">
                    <span class="text-xs font-medium italic text-app-secondary">Actions Needed</span>
                    <span id="pendingVendorApproval2" class="<?= $adminBadgeAccentClass ?>">--</span>
                </div>
            </div>

            <!-- Community -->
            <div class="<?= $adminCardClass ?> p-5 animate-up">
                <h3 class="mb-4 flex items-center gap-2 text-[13px] font-bold text-app-text">
                    <i data-lucide="users" class="h-4 w-4 text-app-primary"></i> Community
                </h3>
                <div class="space-y-2">
                    <div class="<?= $adminQueueItemClass ?>">
                        <span class="text-xs font-medium text-app-secondary">Customers</span>
                        <span id="totalCustomers" class="font-bold text-app-text">--</span>
                    </div>
                    <div class="<?= $adminQueueItemClass ?>">
                        <span class="text-xs font-medium text-app-secondary">Suppliers</span>
                        <span id="totalSuppliers" class="font-bold text-app-text">--</span>
                    </div>
                    <div class="<?= $adminQueueItemClass ?>">
                        <span class="text-xs font-medium text-app-secondary">Staff Members</span>
                        <span id="totalStaffs" class="font-bold text-app-text">--</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- ── Upcoming Events Table ── -->
        <section class="mb-4 <?= $adminCardClass ?> overflow-hidden animate-up">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-app-panel-border p-5">
                <div>
                    <h2 class="<?= $adminSectionTitleClass ?> text-sm">Upcoming Wedding Events</h2>
                    <p class="mt-0.5 text-[11px] text-app-muted">Live schedule of ceremonies and receptions</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left">
                    <thead class="bg-app-soft">
                        <tr>
                            <th class="<?= $adminWideTableHeadClass ?>">Event Type</th>
                            <th class="<?= $adminWideTableHeadClass ?>">Client</th>
                            <th class="<?= $adminWideTableHeadClass ?>">Schedule</th>
                            <th class="<?= $adminWideTableHeadClass ?>">Venue</th>
                            <th class="<?= $adminWideTableHeadClass ?>">Status</th>
                        </tr>
                    </thead>
                    <tbody id="upcomingEventsBody" class="border-t border-app-panel-border"></tbody>
                </table>
            </div>
        </section>

        <!-- ── Popular Wedding Packages ── -->
        <section class="<?= $adminCardClass ?> p-5 animate-up">
            <div class="mb-5">
                <h2 class="<?= $adminSectionTitleClass ?> text-sm">Popular Wedding Packages</h2>
                <p class="mt-0.5 text-[11px] italic text-app-muted">"Sparkling Eve" is trending this week</p>
            </div>
            <div id="popularGrid" class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4"></div>
        </section>

    </main>

    <script>
        const adminClasses = {
            queueItem: <?= json_encode($adminQueueItemClass) ?>,
            badgeSuccess: <?= json_encode($adminBadgeSuccessClass) ?>,
            badgeWarning: <?= json_encode($adminBadgeWarningClass) ?>,
            badgeAccent: <?= json_encode($adminBadgeAccentClass) ?>,
            badgeMuted: <?= json_encode($adminBadgeMutedClass) ?>,
            dropdownItem: <?= json_encode($adminDropdownItemClass) ?>,
            dropdownItemActive: <?= json_encode($adminDropdownItemActiveClass) ?>,
        };

        function refreshLucideIcons() {
            if (window.lucide && typeof lucide.createIcons === "function") lucide.createIcons();
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

        function fetchDashboardData(filter = "week") {
            return new Promise((resolve) => {
                setTimeout(() => {
                    const rand = (min, max) => Math.floor(Math.random() * (max - min + 1) + min);
                    const totalBookings = 317;
                    const confirmedBookings = 217;
                    const cancelledBookings = 33;
                    const pendingBookings = Math.max(15, totalBookings - confirmedBookings - cancelledBookings);
                    const totalRevenue = 63400;
                    const avgSpend = 200;
                    const todayBookings = rand(24, 44);
                    const weekBookings = rand(82, 132);
                    const totalCustomers = 1320 + rand(-50, 90);
                    const totalSuppliers = 112 + rand(-14, 24);
                    const totalStaffs = 38 + rand(-6, 9);
                    const vendorApproved = 340 + rand(-20, 30);
                    const vendorPending = rand(18, 50);
                    const vendorRejected = rand(8, 28);
                    const pendingBookingConfirm = rand(12, 34);
                    const pendingPayments = rand(10, 30);
                    const pendingVendorApproval = rand(14, 38);
                    const escrowTotal = 38482;
                    const escrowPending = 13260;
                    const escrowAvailable = 25222;

                    const topSuppliers = [
                        { name: "Luxe Weddings Co.", bookings: rand(58, 80) },
                        { name: "Golden Moments", bookings: rand(50, 72) },
                        { name: "Elite Catering", bookings: rand(44, 64) }
                    ];

                    const staffPerformance = [
                        { name: "Hsu Lin", totalBookings: rand(50, 74), confirmed: rand(42, 62), cancelled: rand(4, 14) },
                        { name: "Michael K.", totalBookings: rand(46, 66), confirmed: rand(38, 56), cancelled: rand(5, 15) },
                        { name: "Sophia R.", totalBookings: rand(56, 80), confirmed: rand(48, 68), cancelled: rand(3, 12) }
                    ];

                    const upcomingEvents = [
                        { event: "Wedding", customer: "Yuyu", dateTime: "Today, 6:00 PM", location: "Sedona Hall", package: "Premium Bliss", status: "pending" },
                        { event: "Reception", customer: "Zenith Ltd", dateTime: "Tomorrow, 2:00 PM", location: "Grand Plaza", package: "Executive", status: "confirmed" },
                        { event: "Engagement", customer: "Emily Chen", dateTime: "May 23, 7:00 PM", location: "Sky Garden", package: "Celebration", status: "confirmed" }
                    ];

                    const revenueTrendConfig = {
                        today: {
                            labels: Array.from({ length: 24 }, (_, index) => `${index + 1}hr`),
                            base: Array.from({ length: 24 }, (_, index) => 3200 + Math.sin(index / 2.2) * 900 + index * 90),
                            peakLabel: "PEAK HOUR:"
                        },
                        week: {
                            labels: ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"],
                            base: [9200, 11800, 10400, 13700, 15600, 18900, 17100],
                            peakLabel: "PEAK DAY:"
                        },
                        month: {
                            labels: ["Week-1", "Week-2", "Week-3", "Week-4"],
                            base: [28400, 34600, 32100, 39800],
                            peakLabel: "PEAK WEEK:"
                        },
                        year: {
                            labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
                            base: [9400, 13100, 11900, 15400, 18100, 21200, 22600, 24700, 23800, 26500, 29100, 31800],
                            peakLabel: "PEAK MONTH:"
                        }
                    };
                    const revenueTrend = revenueTrendConfig[filter] || revenueTrendConfig.week;
                    const revenueSales = revenueTrend.base.map((value) => Math.max(1800, Math.floor(value + rand(-900, 1200))));
                    const peakPeriod = revenueTrend.labels[revenueSales.indexOf(Math.max(...revenueSales))];
                    const supplierCategories = {
                        labels: ["Venues", "Photo", "Catering", "Music"],
                        values: [46 + rand(-5, 7), 28 + rand(-4, 6), 22 + rand(-3, 5), 17 + rand(-3, 5)]
                    };

                    const popularPackages = [
                        { name: "Royal Wedding", image: "https://images.unsplash.com/photo-1519741497674-611481863552?w=900&h=650&fit=crop", bookings: rand(380, 620), revenue: rand(78000, 132000), rating: 4.9 },
                        { name: "Sparkling Eve", image: "https://images.unsplash.com/photo-1470337458703-46ad1756a187?w=900&h=650&fit=crop", bookings: rand(300, 540), revenue: rand(64000, 118000), rating: 4.7 },
                        { name: "Bohemian Luxe", image: "https://images.unsplash.com/photo-1519225421980-715cb0215aed?w=900&h=650&fit=crop", bookings: rand(260, 480), revenue: rand(55000, 98000), rating: 4.8 },
                        { name: "Garden Vows", image: "https://images.unsplash.com/photo-1464366400600-7168b8af9bc3?w=900&h=650&fit=crop", bookings: rand(200, 440), revenue: rand(49000, 89000), rating: 4.6 }
                    ];

                    resolve({
                        totalBookings, totalRevenue, pendingBookings, confirmedBookings, cancelledBookings,
                        avgSpend, todayBookings, weekBookings, totalCustomers, totalSuppliers, totalStaffs,
                        vendorApproved, vendorPending, vendorRejected,
                        pendingBookingConfirm, pendingPayments, pendingVendorApproval,
                        topSuppliers, staffPerformance, upcomingEvents,
                        revenueLabels: revenueTrend.labels,
                        revenueSales,
                        peakPeriod,
                        peakPeriodLabel: revenueTrend.peakLabel,
                        supplierCategories, popularPackages,
                        escrow: { total: escrowTotal, pendingRelease: escrowPending, available: escrowAvailable }
                    });
                }, 200);
            });
        }

        let revenueChart;
        let supplierChart;
        let isLoading = false;
        let currentFilter = "week";

        function currency(value) { return `$${value.toLocaleString()}`; }
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
            const setWidth = (id, value) => { const el = document.getElementById(id); if (el) el.style.width = value; };

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
            setText("escrowTotal", currency(data.escrow.total));
            setText("escrowPending", currency(data.escrow.pendingRelease));
            setText("escrowAvailable", currency(data.escrow.available));

            const escrowProgress = data.escrow.total > 0 ? Math.round((data.escrow.available / data.escrow.total) * 100) : 0;
            setWidth("escrowProgressFill", `${escrowProgress}%`);
            setText("escrowProgressPercent", `${escrowProgress}%`);

            document.getElementById("topSuppliersList").innerHTML = data.topSuppliers.map((supplier, index) => `
                <div class="${adminClasses.queueItem} gap-2.5">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-6 w-6 items-center justify-center rounded-lg flex-shrink-0 bg-app-accent text-[10px] font-bold text-app-white">${index + 1}</span>
                        <span class="text-xs font-semibold text-app-text">${supplier.name}</span>
                    </div>
                    <span class="whitespace-nowrap text-[11px] font-semibold text-app-muted">${supplier.bookings} events</span>
                </div>
            `).join("");

            document.getElementById("staffTableBody").innerHTML = data.staffPerformance.map((staff) => `
                <tr class="border-b border-app-panel-border transition-colors hover:bg-app-soft">
                    <td class="px-5 py-2.5 font-semibold text-app-text">${staff.name}</td>
                    <td class="px-5 py-2.5 text-app-secondary">${staff.totalBookings}</td>
                    <td class="px-5 py-2.5 font-bold text-app-success">${staff.confirmed}</td>
                </tr>
            `).join("");

            document.getElementById("upcomingEventsBody").innerHTML = data.upcomingEvents.map((event) => {
                const isConfirmed = event.status === "confirmed";
                const statusClass = isConfirmed ? adminClasses.badgeSuccess : adminClasses.badgeWarning;
                return `
                    <tr class="border-b border-app-panel-border transition-colors hover:bg-app-soft">
                        <td class="px-6 py-3"><span class="${adminClasses.badgeMuted}">${event.event}</span></td>
                        <td class="px-6 py-3 font-semibold text-app-text">${event.customer}</td>
                        <td class="px-6 py-3 text-app-secondary">${event.dateTime}</td>
                        <td class="px-6 py-3 text-app-secondary">${event.location}</td>
                        <td class="px-6 py-3"><span class="${statusClass}">${event.status}</span></td>
                    </tr>
                `;
            }).join("");

            document.getElementById("popularGrid").innerHTML = data.popularPackages.map((pkg) => `
                <article class="pkg-card relative overflow-hidden rounded-card border border-app-border bg-app-input shadow-sm hover:shadow-card">
                    <!-- Full-height image -->
                    <div class="relative overflow-hidden rounded-card">
                        <img src="${pkg.image}" alt="${pkg.name}" class="block h-[220px] w-full object-cover">

                        <!-- Rating badge — always visible top-right -->
                        <div class="absolute right-2.5 top-2.5 z-10 flex items-center gap-1 rounded-full bg-app-input/80 px-2 py-0.5 text-[10px] font-bold text-app-primary shadow-sm">
                            ★ ${pkg.rating}
                        </div>

                        <!-- Caption — always visible bottom, fades on hover -->
                        <div class="pkg-caption px-3.5 py-2.5">
                            <p class="text-xs font-semibold text-app-white">${pkg.name}</p>
                        </div>

                        <!-- Hover overlay: slides up from bottom -->
                        <div class="pkg-overlay flex items-end justify-between px-4 pb-4 pt-8">
                            <div>
                                <p class="text-[10px] font-semibold uppercase tracking-widest text-app-white/60">Bookings</p>
                                <p class="mt-0.5 text-[13px] font-bold text-app-white">${pkg.bookings.toLocaleString()}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[10px] font-semibold uppercase tracking-widest text-app-white/60">Revenue</p>
                                <p class="mt-0.5 text-[13px] font-bold text-app-success">${currency(pkg.revenue)}</p>
                            </div>
                        </div>
                    </div>
                </article>
            `).join("");

            if (revenueChart) revenueChart.destroy();
            if (supplierChart) supplierChart.destroy();

            const primaryColor = readUtilityColor("text-app-primary");
            const textColor = readUtilityColor("text-app-text");
            const secondaryColor = readUtilityColor("text-app-secondary");
            const mutedColor = readUtilityColor("text-app-muted");
            const gridColor = readUtilityColor("border-app-panel-border border", "borderTopColor");
            const inputColor = readUtilityColor("bg-app-input", "backgroundColor");
            const successColor = readUtilityColor("text-app-success");
            const categoryColors = [
                readUtilityColor("bg-app-ring", "backgroundColor"),
                readUtilityColor("bg-app-accent", "backgroundColor"),
                readUtilityColor("bg-app-surface", "backgroundColor"),
                readUtilityColor("bg-app-success", "backgroundColor") || successColor
            ];

            const revenueContext = document.getElementById("revenueChartCanvas").getContext("2d");
            const revenueXAxisTitle = getRevenueXAxisTitle(currentFilter);
            revenueChart = new Chart(revenueContext, {
                type: "line",
                data: {
                    labels: data.revenueLabels,
                    datasets: [{
                        data: data.revenueSales,
                        borderColor: primaryColor,
                        backgroundColor: withAlpha(primaryColor, 0.08),
                        fill: true,
                        tension: 0.4,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        pointBackgroundColor: primaryColor,
                        pointBorderColor: inputColor,
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
                                color: secondaryColor,
                                font: { size: 11, weight: "700", family: "Poppins" },
                                padding: { top: 8 }
                            },
                            ticks: { color: mutedColor, font: { size: 10, weight: "600", family: "Poppins" } }
                        },
                        y: {
                            grid: { color: gridColor },
                            border: { display: false },
                            title: {
                                display: true,
                                text: "Sales",
                                color: secondaryColor,
                                font: { size: 11, weight: "700", family: "Poppins" },
                                padding: { bottom: 8 }
                            },
                            ticks: {
                                color: mutedColor,
                                font: { size: 10, family: "Poppins" },
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

                    ctx.fillStyle = textColor;
                    ctx.font = "700 20px Poppins, sans-serif";
                    ctx.fillText(total.toLocaleString(), centerX, centerY - 8);

                    ctx.fillStyle = mutedColor;
                    ctx.font = "600 9px Poppins, sans-serif";
                    ctx.fillText("suppliers", centerX, centerY + 10);

                    meta.data.forEach((arc, index) => {
                        const value = dataset.data[index];
                        const percent = Math.round((value / total) * 100);
                        const angle = (arc.startAngle + arc.endAngle) / 2;
                        const radius = (arc.innerRadius + arc.outerRadius) / 2;
                        const x = arc.x + Math.cos(angle) * radius;
                        const y = arc.y + Math.sin(angle) * radius;

                        ctx.fillStyle = inputColor;
                        ctx.font = "700 10px Poppins, sans-serif";
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
                        backgroundColor: categoryColors,
                        borderColor: inputColor,
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
                                color: secondaryColor,
                                font: { size: 11, weight: "600", family: "Poppins" }
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
                    document.querySelectorAll("[data-event-filter]").forEach(o => {
                        o.className = o === opt ? adminClasses.dropdownItemActive : adminClasses.dropdownItem;
                    });
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
