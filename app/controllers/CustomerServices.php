<?php

class CustomerServices extends Controller
{
    public function service()
    {
        $catalogModel = $this->model('CustomerServiceCatalog');
        $filters = $this->filtersFromRequest();

        // Cart count for header badge
        $cartCount = 0;
        $userId = $_SESSION['session_uid'] ?? null;
        if ($userId) {
            $cartModel = $this->model('CartModel');
            $cartCount = $cartModel->getCartCount($userId);
        }

        $this->view('main/service', [
            'catalog' => $catalogModel->getServicePageData($filters),
            'filters' => $filters,
            'cartCount' => $cartCount,
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
        if ($packageId > 0 && $packageItemId > 0) {
            $packageModel = $this->model('PlatformPackage');
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
        }

        $view = strtolower((string)($service['category'] ?? '')) === 'venue'
            ? 'main/venue_detail'
            : 'main/other_service_detail';

        $this->view($view, [
            'service' => $service,
            'selectedDate' => $service['selected_date'] ?? '',
        ]);
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
        $filters = [
            'search' => $search,
            'sort' => $sort,
            'category' => $category,
        ];

        $packageTypes = $packageModel->getPackageTypes($filters);
        $categories = $packageModel->getPackageCategories($filters);

        // Check active filters
        $hasActiveFilters = $search !== '' || $category !== 'all';

        // Cart count for header badge
        $cartCount = 0;
        $userId = $_SESSION['session_uid'] ?? null;
        if ($userId) {
            $cartModel = $this->model('CartModel');
            $cartCount = $cartModel->getCartCount($userId);
        }

        $this->view('main/packages', [
            'packages' => $packageTypes,
            'cartCount' => $cartCount,
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
        ]);
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
}
