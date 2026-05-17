    <aside class="border-r border-r-app-sidebar bg-app-sidebar">
        <!-- Sidebar content -->
         <div class="flex h-full flex-col">
            <div class="border-b border-b-app-panel-border bg-app-panel px-5 py-5">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-app-primary text-sm font-semibold text-white shadow-sm shadow-sky-200">AJ</div>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold text-slate-800">Alex Johnson</p>
                        <p class="text-xs text-slate-500">alexjohnson@gmail.com</p>

                    </div>
                </div>

            </div>
            <div class="px-5 pt-5">
                <p class="px-3 text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Profile</p>
            </div>

            <nav class="px-4 py-3 space-y-1.5">
          

                <a href="#" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-white hover:shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-user-icon lucide-circle-user h-4 w-4 text-slate-400"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="10" r="3"/><path d="M7 20.662V19a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v1.662"/></svg>
                    <span class="flex-1">My Profile</span><span class="text-xs text-slate-400">                      
                         <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right-icon lucide-chevron-right h-4 w-4 text-slate-400"><path d="m9 18 6-6-6-6"/></svg>
                    </span>
                </a>

            </nav>

            <div class="px-5 pt-5">
                <p class="px-3 text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400">Workspace</p>
            </div>
            <nav class="px-4 py-3 space-y-1.5">
                
                <a href="#" class="flex items-center gap-3 rounded-xl bg-app-primary px-4 py-3 text-sm font-medium text-white shadow-sm shadow-sky-200/70 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-layout-dashboard-icon lucide-layout-dashboard h-4 w-4"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>                    <span class="flex-1">Dashboard</span>
                </a>
               <!-- Booking  -->
                <div class="space-y-1">
                    <button type="button" data-subnav-toggle="bookings" aria-expanded="false"
                        class="flex w-full items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-white hover:shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/></svg>
                        <span class="flex-1 text-left">Bookings</span>
                        <svg data-chevron="bookings" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400 transition-transform duration-200"><path d="m6 9 6 6 6-6"/></svg>
                    </button>
                    <div data-subnav-panel="bookings" class="hidden pl-6">
                        <div class="space-y-0.5 border-l border-slate-200/80 py-1">
                            <a href="#" class="ml-3 flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-slate-600 transition hover:bg-white hover:text-slate-900">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400"><path d="M2 5h20"/><path d="M6 12h12"/><path d="M9 19h6"/></svg>
                                <span>All bookings</span>
                            </a>
                            <a href="#" class="ml-3 flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-slate-600 transition hover:bg-white hover:text-slate-900">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400"><path d="M12 6v6l4 2"/><circle cx="12" cy="12" r="10"/></svg>
                                <span>Pending approval</span>
                                <span class="ml-auto rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold text-amber-700">5</span>
                            </a>
                            <a href="#" class="ml-3 flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-slate-600 transition hover:bg-white hover:text-slate-900">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400"><rect width="20" height="14" x="2" y="5" rx="2"/><path d="M2 10h20"/></svg>
                                <span>Payments</span>
                            </a>
                        </div>
                    </div>
                </div>             


                <!-- Supplier  -->
                <div class="space-y-1">
                    <!-- All Suppliers -->
                    <button type="button" data-subnav-toggle="suppliers" aria-expanded="false"
                        class="flex w-full items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-white hover:shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400"><path d="M15 21v-5a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v5"/><path d="M17.774 10.31a1.12 1.12 0 0 0-1.549 0 2.5 2.5 0 0 1-3.451 0 1.12 1.12 0 0 0-1.548 0 2.5 2.5 0 0 1-3.452 0 1.12 1.12 0 0 0-1.549 0 2.5 2.5 0 0 1-3.77-3.248l2.889-4.184A2 2 0 0 1 7 2h10a2 2 0 0 1 1.653.873l2.895 4.192a2.5 2.5 0 0 1-3.774 3.244"/><path d="M4 10.95V19a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8.05"/></svg>
                        <span class="flex-1 text-left">All suppliers</span>
                        <svg data-chevron="suppliers" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400 transition-transform duration-200"><path d="m6 9 6 6 6-6"/></svg>
                    </button>
                    <div data-subnav-panel="suppliers" class="hidden pl-6">
                        <div class="space-y-0.5 border-l border-slate-200/80 py-1">
                            <a href="#" class="ml-3 flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-slate-600 transition hover:bg-white hover:text-slate-900">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400"><path d="M2 5h20"/><path d="M6 12h12"/><path d="M9 19h6"/></svg>
                                <span>All suppliers</span>
                            </a>
                            <a href="#" class="ml-3 flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-slate-600 transition hover:bg-white hover:text-slate-900">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400"><path d="M3.85 8.62a4 4 0 0 1 4.78-4.77 4 4 0 0 1 6.74 0 4 4 0 0 1 4.78 4.78 4 4 0 0 1 0 6.74 4 4 0 0 1-4.77 4.78 4 4 0 0 1-6.75 0 4 4 0 0 1-4.78-4.77 4 4 0 0 1 0-6.76Z"/><path d="m9 12 2 2 4-4"/></svg>
                                <span>Top Suppliers</span>
                            </a>
                            <a href="#" class="ml-3 flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-slate-600 transition hover:bg-white hover:text-slate-900">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400"><path d="M12 6v6l4 2"/><circle cx="12" cy="12" r="10"/></svg>
                                <span>Warning list</span>
                            </a>
                            <a href="#" class="ml-3 flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-slate-600 transition hover:bg-white hover:text-slate-900">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/></svg>
                                <span>Rejected</span>
                            </a>
                            <a href="#" class="ml-3 flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-slate-600 transition hover:bg-white hover:text-slate-900">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/></svg>
                                <span>Rejected</span>
                            </a>
                        </div>
                    </div>


                </div>

                <!-- Customer  -->
                <div class="space-y-1">
                    <button type="button" data-subnav-toggle="customers" aria-expanded="false"
                        class="flex w-full items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-white hover:shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        <span class="flex-1 text-left">Customers</span>
                        <svg data-chevron="customers" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400 transition-transform duration-200"><path d="m6 9 6 6 6-6"/></svg>
                    </button>
                    <div data-subnav-panel="customers" class="hidden pl-6">
                        <div class="space-y-0.5 border-l border-slate-200/80 py-1">
                            <a href="#" class="ml-3 flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-slate-600 transition hover:bg-white hover:text-slate-900">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400"><path d="M2 5h20"/><path d="M6 12h12"/><path d="M9 19h6"/></svg>
                                <span>All customers</span>
                            </a>
                            <a href="#" class="ml-3 flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-slate-600 transition hover:bg-white hover:text-slate-900">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-rose-400"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="17" y1="8" x2="23" y2="14"/><line x1="23" y1="8" x2="17" y2="14"/></svg>
                                <span>Suspended / Banned</span>
                            </a>
                        </div>
                    </div>
                </div>
                <!-- Staff  -->
                <div class="space-y-1">
                    <button type="button" data-subnav-toggle="staff" aria-expanded="false"
                        class="flex w-full items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-white hover:shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400"><path d="M12 12h.01"/><path d="M16 6V4a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/><path d="M22 13a18.15 18.15 0 0 1-20 0"/><rect width="20" height="14" x="2" y="6" rx="2"/></svg>
                        <span class="flex-1 text-left">Staff</span>
                        <svg data-chevron="staff" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400 transition-transform duration-200"><path d="m6 9 6 6 6-6"/></svg>
                    </button>
                    <div data-subnav-panel="staff" class="hidden pl-6">
                        <div class="space-y-0.5 border-l border-slate-200/80 py-1">
                            <a href="#" class="ml-3 flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-slate-600 transition hover:bg-white hover:text-slate-900">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400"><rect width="14" height="17" x="5" y="2" rx="2"/><path d="M9 22v-4h6v4"/><path d="M8 6h.01"/><path d="M16 6h.01"/><path d="M12 6h.01"/><path d="M12 10h.01"/><path d="M12 14h.01"/><path d="M16 10h.01"/><path d="M16 14h.01"/><path d="M8 10h.01"/><path d="M8 14h.01"/></svg>
                                <span>Staff list</span>
                            </a>
                            <a href="#" class="ml-3 flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-slate-600 transition hover:bg-white hover:text-slate-900">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400"><path d="M12 2v20"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                                <span>Payroll</span>
                            </a>
                            <a href="#" class="ml-3 flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-slate-600 transition hover:bg-white hover:text-slate-900">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                                <span>Performance</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- System  -->
                <div>
                    <p class="px-3 mb-1 text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-400 mt-8">System</p>
                    <div class="space-y-1">
                        <a href="#" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-white hover:shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400"><path d="M20 12V22H4V12"/><path d="M22 7H2v5h20V7z"/><path d="M12 22V7"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/></svg>
                            <span class="flex-1">Withdrawals</span>
                        </a>
                        <a href="#" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-white hover:shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400"><path d="M10.268 21a2 2 0 0 0 3.464 0"/><path d="M3.262 15.326A1 1 0 0 0 4 17h16a1 1 0 0 0 .74-1.673C19.41 13.956 18 12.499 18 8A6 6 0 0 0 6 8c0 4.499-1.411 5.956-2.738 7.326"/></svg>
                            <span class="flex-1">Notifications</span>
                        </a>
                        <a href="#" class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-slate-700 transition hover:bg-white hover:shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400"><path d="M9.671 4.136a2.34 2.34 0 0 1 4.659 0 2.34 2.34 0 0 0 3.319 1.915 2.34 2.34 0 0 1 2.33 4.033 2.34 2.34 0 0 0 0 3.831 2.34 2.34 0 0 1-2.33 4.033 2.34 2.34 0 0 0-3.319 1.915 2.34 2.34 0 0 1-4.659 0 2.34 2.34 0 0 0-3.32-1.915 2.34 2.34 0 0 1-2.33-4.033 2.34 2.34 0 0 0 0-3.831A2.34 2.34 0 0 1 6.35 6.051a2.34 2.34 0 0 0 3.319-1.915"/><circle cx="12" cy="12" r="3"/></svg>
                            <span class="flex-1">Settings</span>
                        </a>
                    </div>
                </div>
            </nav>
            <!-- Log Out  -->
            <div class="mt-auto">
                <button
                    class="group flex w-full items-center gap-3 border border-t-app-border px-8 py-3 transition-all duration-200 hover:bg-app-sidebar-hover hover:shadow-md"
                >
                    <div class="flex h-8 w-8 items-center justify-center rounded-xl text-app-danger transition group-hover:bg-app-danger-soft">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="h-5 w-5">
                            <path d="M10 12h11"/>
                            <path d="m17 16 4-4-4-4"/>
                            <path d="M21 6.344V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-1.344"/>
                        </svg>
                    </div>

                    <div class="flex-1 text-left">
                        <p class="text-sm font-semibold text-slate-700">Log Out</p>
                    </div>

                    <div
                        class="flex h-8 w-8 items-center justify-center rounded-full text-slate-400 transition group-hover:text-app-primary"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="h-4 w-4">
                            <path d="m9 18 6-6-6-6"/>
                        </svg>
                    </div>
                </button>
            </div>
         </div>
    </aside>
    <main class="overflow-y-auto">
        <!-- HEADER -->
        <div class="sticky top-0 z-40 flex flex-col gap-4 border-b border-app-border bg-app-sidebar/95 px-6 py-[18px] backdrop-blur-sm sm:flex-row sm:items-center sm:justify-between">            
            <!-- Left -->
            <div>
                <p class="mb-1 text-[11px] font-semibold uppercase tracking-[0.18em] text-app-header-muted">
                    <span class="text-slate-800">Dashbaord</span> / Overview
                </p>
            </div>

            <!-- Right -->
            <div class="flex flex-wrap items-center gap-3">

                <!-- Search -->
                <div class="relative">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        width="24" height="24"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-app-header-muted">
                        <path d="m21 21-4.34-4.34"/>
                        <circle cx="11" cy="11" r="8"/>
                    </svg>

                    <input
                        type="search"
                        id="dashboard-search"
                        placeholder="Search bookings, suppliers..."
                        aria-keyshortcuts="Meta+K Control+K"
                        class="w-[260px] rounded-xl border border-app-border bg-white/80 py-2.5 pl-10 pr-14 text-sm text-slate-700 shadow-sm outline-none transition focus:border-app-focus focus:bg-white focus:ring-2 focus:ring-app-ring"
                    >

                    <div class="absolute right-2 top-1/2 flex -translate-y-1/2 items-center gap-1 rounded-md border border-app-border bg-app-keycap px-2 py-1 text-[10px] font-semibold text-app-header-muted">
                        <svg xmlns="http://www.w3.org/2000/svg"
                            width="24" height="24"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            class="h-3 w-3">
                            <path d="M15 6v12a3 3 0 1 0 3-3H6a3 3 0 1 0 3 3V6a3 3 0 1 0-3 3h12a3 3 0 1 0-3-3"/>
                        </svg>
                        K
                    </div>
                </div>

                <!-- Notification -->
                <button class="relative flex h-11 w-11 items-center justify-center rounded-xl border border-app-border bg-white/80 text-app-secondary shadow-sm transition hover:bg-white hover:shadow-md">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        width="24" height="24"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        class="h-5 w-5">
                        <path d="M10.268 21a2 2 0 0 0 3.464 0"/>
                        <path d="M3.262 15.326A1 1 0 0 0 4 17h16a1 1 0 0 0 .74-1.673C19.41 13.956 18 12.499 18 8A6 6 0 0 0 6 8c0 4.499-1.411 5.956-2.738 7.326"/>
                    </svg>

                    <span class="absolute right-2 top-2 h-2 w-2 rounded-full bg-rose-400"></span>
                </button>

            </div>
        </div>
      
    </main>

<script>
    lucide.createIcons();

    // Generic subnav toggle
    document.querySelectorAll('[data-subnav-toggle]').forEach(btn => {
        btn.addEventListener('click', () => {
            const key = btn.dataset.subnavToggle;
            const panel = document.querySelector(`[data-subnav-panel="${key}"]`);
            const chevron = document.querySelector(`[data-chevron="${key}"]`);
            const isExpanded = btn.getAttribute('aria-expanded') === 'true';
            btn.setAttribute('aria-expanded', !isExpanded);
            panel.classList.toggle('hidden');
            if (chevron) chevron.classList.toggle('rotate-180');
        });
    });

    const dashboardSearch = document.getElementById('dashboard-search');

    document.addEventListener('keydown', (event) => {
        const isShortcut = (event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k';

        if (!isShortcut || !dashboardSearch) {
            return;
        }

        event.preventDefault();
        dashboardSearch.focus();
        dashboardSearch.select();
    });
</script>
