<?php

class CustomerServices extends Controller
{
    public function service()
    {
        $catalogModel = $this->model('CustomerServiceCatalog');
        $filters = $this->filtersFromRequest();

        // Cart count for header badge
        $cartCount = 0;
        $wishlistCount = 0;
        $wishlistServiceIds = [];
        $userId = $_SESSION['session_uid'] ?? null;
        if ($userId) {
            $cartModel = $this->model('CartModel');
            $cartCount = $cartModel->getCartCount($userId);

            $wishlistModel = $this->model('WishlistModel');
            $wishlistServiceIds = $wishlistModel->getFavoritedServiceIds((int)$userId);
            $wishlistCount = $wishlistModel->getWishlistCount((int)$userId);
        }

        $this->view('main/service', [
            'catalog' => $catalogModel->getServicePageData($filters),
            'filters' => $filters,
            'cartCount' => $cartCount,
            'wishlistServiceIds' => $wishlistServiceIds,
            'wishlistCount' => $wishlistCount,
        ]);
    }

    public function detail($serviceId = null)
    {
        $serviceId = (int)$serviceId;

        if ($serviceId <= 0) {
            redirect('customerServices/service');
        }

        $catalogModel = $this->model('CustomerServiceCatalog');
        $selectedDate = $this->validDate($_GET['date'] ?? '') ?: '';
        $service = $catalogModel->getServiceDetail($serviceId, $selectedDate);

        if (!$service) {
            redirect('customerServices/service');
        }

        $packageId = (int)($_GET['package_id'] ?? 0);
        $packageItemId = (int)($_GET['package_item_id'] ?? 0);
        $packageModel = $this->model('PlatformPackage');
        if ($packageId > 0 && $packageItemId > 0) {
            $packageContext = $packageModel->getServicePackageContext($packageId, $packageItemId, $serviceId);
            if ($packageContext) {
                $standalonePrice = (float)($service['display_price'] ?? $service['customize_price'] ?? $service['price_max'] ?? $service['price'] ?? 0);
                $packagePrice = (float)($packageContext['package_price'] ?? 0);
                $service['package_context'] = $packageContext;
                $service['standalone_price'] = $standalonePrice;
                $service['price_context'] = 'package';
                $service['display_price'] = $packagePrice;
                $service['price'] = $packagePrice;
                $service['price_min'] = $packagePrice;
                $service['price_max'] = $packagePrice;
                if (!empty($packageContext['venue_room_id']) && !empty($service['venue_rooms'])) {
                    $selectedRoomId = (int)$packageContext['venue_room_id'];
                    $service['venue_rooms'] = array_values(array_filter($service['venue_rooms'], function ($room) use ($selectedRoomId) {
                        return (int)($room['id'] ?? 0) === $selectedRoomId;
                    }));
                }
            }
        } else {
            $addonPackageId = (int)($_GET['addon_package_id'] ?? 0);
            if ($addonPackageId > 0) {
                $addonPackage = $packageModel->getPackageById($addonPackageId);
                if ($addonPackage && !empty($addonPackage['is_active']) && empty($addonPackage['deleted_at'])) {
                    $service['addon_context'] = [
                        'package_id' => (int)$addonPackage['package_id'],
                        'package_name' => $addonPackage['name'] ?? 'Wedding package',
                        'package_slug' => $addonPackage['slug'] ?? '',
                        'selected_date' => $selectedDate,
                        'selected_time' => $this->validTime($_GET['time'] ?? '') ?: '',
                    ];
                }
            }
        }

        // Wishlist state for the detail page
        $isWishlisted = false;
        $wishlistCount = 0;
        $cartCount = 0;
        $recentlyViewedServices = [];
        $recentIds = getRecentlyViewedIds();
        if (!empty($recentIds)) {
            $recentlyViewedServices = array_values(array_filter(
                fetchRecentlyViewedServices(new Database()),
                fn($recent) => (int)($recent['service_id'] ?? 0) !== $serviceId
            ));
            $recentlyViewedServices = array_slice($recentlyViewedServices, 0, 6);
        }
        $userId = $_SESSION['session_uid'] ?? null;
        if ($userId) {
            $cartModel = $this->model('CartModel');
            $cartCount = $cartModel->getCartCount((int)$userId);

            $wishlistModel = $this->model('WishlistModel');
            $isWishlisted = $wishlistModel->isFavorited((int)$userId, 'service', $serviceId);
            $wishlistCount = $wishlistModel->getWishlistCount((int)$userId);
        }

        // Get locked item IDs from active packages
        $service['locked_items'] = $packageModel->getLockedItemIds();

        $view = strtolower((string)($service['category'] ?? '')) === 'venue'
            ? 'main/venue_detail'
            : 'main/other_service_detail';

        $this->view($view, [
            'service' => $service,
            'selectedDate' => $service['selected_date'] ?? '',
            'isWishlisted' => $isWishlisted,
            'wishlistCount' => $wishlistCount,
            'cartCount' => $cartCount,
            'recentlyViewedServices' => $recentlyViewedServices,
        ]);
    }

    public function liveSearch()
    {
        header('Content-Type: application/json');

        $query = trim((string)($_GET['q'] ?? ''));
        if (strlen($query) < 2) {
            echo json_encode(['ok' => true, 'query' => $query, 'results' => []]);
            return;
        }

        $filters = $this->filtersFromRequest();
        $filters['search'] = $query;
        $filters['limit'] = 8;

        $catalogModel = $this->model('CustomerServiceCatalog');
        $services = $catalogModel->getServices($filters);
        $results = array_map(function ($service) {
            $price = (float)($service['display_price'] ?? $service['customize_price'] ?? $service['price_max'] ?? $service['price'] ?? 0);

            return [
                'id' => (int)($service['id'] ?? 0),
                'name' => (string)($service['name'] ?? 'Service'),
                'supplier' => (string)($service['supplier_name'] ?? 'Supplier'),
                'category' => (string)($service['category'] ?? ''),
                'price' => $price > 0 ? 'MMK ' . number_format($price, 0) : '',
                'url' => URLROOT . '/customerServices/detail/' . (int)($service['id'] ?? 0),
            ];
        }, $services);

        echo json_encode([
            'ok' => true,
            'query' => $query,
            'results' => $results,
        ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    }

    /**
     * AJAX endpoint: check attire item availability for a date range.
     * Returns JSON with blocked dates.
     */
    public function attireAvailability($serviceId = null)
    {
        header('Content-Type: application/json');
        $attireItemId = (int)($_GET['attire_item_id'] ?? 0);
        if ($attireItemId <= 0) {
            echo json_encode(['blocked' => []]);
            return;
        }

        $cartModel = $this->model('CartModel');
        $blocked = $cartModel->getAttireBlockedDates($attireItemId);

        // Expand blocked ranges into individual dates for the frontend
        $blockedDates = [];
        foreach ($blocked as $range) {
            $start = strtotime($range['borrow_date']);
            $end = strtotime($range['buffer_until']);
            for ($ts = $start; $ts <= $end; $ts += 86400) {
                $blockedDates[] = date('Y-m-d', $ts);
            }
        }

        echo json_encode(['blocked' => array_unique($blockedDates)]);
    }

    /**
     * ── Customer: Package type listing ──
     */
    public function packages()
    {
        $packageModel = $this->model('PlatformPackage');

        // Build filters from request
        $search = trim($_GET['q'] ?? '');
        $sort = trim($_GET['sort'] ?? 'featured');
        $category = trim($_GET['category'] ?? 'all');
        $date = $this->validDate($_GET['date'] ?? '') ?: '';
        $time = $this->validTime($_GET['time'] ?? '') ?: '';
        $filters = [
            'search' => $search,
            'sort' => $sort,
            'category' => $category,
            'date' => $date,
            'time' => $time,
        ];

        $packageTypes = $packageModel->getPackageTypes($filters);
        $categories = $packageModel->getPackageCategories($filters);

        // Check active filters
        $hasActiveFilters = $search !== '' || $category !== 'all' || $date !== '' || $time !== '';

        // Cart count for header badge
        $cartCount = 0;
        $wishlistCount = 0;
        $userId = $_SESSION['session_uid'] ?? null;
        if ($userId) {
            $cartModel = $this->model('CartModel');
            $cartCount = $cartModel->getCartCount($userId);

            $wishlistModel = $this->model('WishlistModel');
            $wishlistCount = $wishlistModel->getWishlistCount((int)$userId);
        }

        $this->view('main/packages', [
            'packages' => $packageTypes,
            'cartCount' => $cartCount,
            'wishlistCount' => $wishlistCount,
            'filters' => $filters,
            'categories' => $categories,
            'hasActiveFilters' => $hasActiveFilters,
            'totalServices' => count($packageTypes),
        ]);
    }

    /**
     * ── Customer: Single package type detail ──
     */
    public function packageDetail($slug = null)
    {
        if (!$slug) {
            redirect('customerServices/packages');
        }

        $packageModel = $this->model('PlatformPackage');
        $package = $packageModel->getPackageBySlug($slug);

        if (!$package) {
            redirect('customerServices/packages');
        }

        // Group fixed included services by category for display.
        $categoryServices = [];
        foreach (($package['services'] ?? []) as $service) {
            $catId = (int)($service['category_id'] ?? 0);
            $key = $catId > 0 ? $catId : 'other';
            if (!isset($categoryServices[$key])) {
                $categoryServices[$key] = [
                    'category_id' => $catId,
                    'category_name' => $service['category_name'] ?? 'Other',
                    'category_slug' => $service['category_slug'] ?? '',
                    'services' => [],
                    'service_count' => 0,
                ];
            }

            $categoryServices[$key]['services'][] = $service;
            $categoryServices[$key]['service_count']++;
        }

        $package['category_services'] = array_values($categoryServices);
        $package['addon_services'] = $packageModel->getAddonServices((int)$package['package_id']);

        // Cart count
        $cartCount = 0;
        $userId = $_SESSION['session_uid'] ?? null;
        if ($userId) {
            $cartModel = $this->model('CartModel');
            $cartCount = $cartModel->getCartCount($userId);
        }

        $this->view('main/package_detail', [
            'package' => $package,
            'cartCount' => $cartCount,
            'selectedDate' => $this->validDate($_GET['date'] ?? '') ?: '',
            'selectedTime' => $this->validTime($_GET['time'] ?? '') ?: '',
        ]);
    }

    /**
     * AJAX endpoint: return per-day availability for a package in a given month.
     * POST {package_id, month}  →  {days: [{date, status, ...}]}
     */
    public function packageAvailability(): void
    {
        header('Content-Type: application/json');

        $raw = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $packageId = (int)($raw['package_id'] ?? 0);
        $month = trim((string)($raw['month'] ?? '')); // e.g. "2026-07"

        if ($packageId <= 0 || !preg_match('/^\d{4}-\d{2}$/', $month)) {
            echo json_encode(['error' => 'Invalid package or month']);
            return;
        }

        $cartModel = $this->model('CartModel');
        $days = $cartModel->getPackageMonthAvailability($packageId, $month);
        echo json_encode(['days' => $days]);
    }

    private function filtersFromRequest()
    {
        $priceMin = $this->validPrice($_GET['price_min'] ?? '');
        $priceMax = $this->validPrice($_GET['price_max'] ?? '');

        if ($priceMin !== '' && $priceMax !== '' && (float)$priceMin > (float)$priceMax) {
            [$priceMin, $priceMax] = [$priceMax, $priceMin];
        }

        return [
            'search' => trim($_GET['q'] ?? ''),
            'category' => trim($_GET['category'] ?? 'all'),
            'sort' => trim($_GET['sort'] ?? 'featured'),
            'date' => $this->validDate($_GET['date'] ?? '') ?: '',
            'time' => $this->validTime($_GET['time'] ?? '') ?: '',
            'price_min' => $priceMin,
            'price_max' => $priceMax,
        ];
    }

    private function validDate($date)
    {
        $date = trim((string)$date);
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return null;
        }

        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        return $parsed && $parsed->format('Y-m-d') === $date ? $date : null;
    }

    private function validPrice($price)
    {
        $price = trim((string)$price);
        if ($price === '' || !is_numeric($price)) {
            return '';
        }

        return (string)max(0, (float)$price);
    }

    private function validTime($time)
    {
        $time = trim((string)$time);
        if (!preg_match('/^\d{2}:\d{2}$/', $time)) {
            return null;
        }

        $parsed = DateTimeImmutable::createFromFormat('!H:i', $time);
        return $parsed && $parsed->format('H:i') === $time ? $time : null;
    }
}
