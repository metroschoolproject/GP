<?php

class Cart extends Controller
{
    private $cartModel;
    private $userId;

    public function __construct()
    {
        $this->cartModel = $this->model('CartModel');
        $this->userId = $_SESSION['session_uid'] ?? null;
    }

    /**
     * Show the cart page.
     */
    public function home()
    {
        $this->index();
    }

    public function index()
    {
        // Process any pending item that was added before login (from session)
        if ($this->userId && !empty($_SESSION['cart_pending'])) {
            $pending = $_SESSION['cart_pending'];
            unset($_SESSION['cart_pending']);
            $addonPackageId = (int)($pending['addon_package_id'] ?? 0);
            if ($addonPackageId > 0) {
                $packageCartItem = $this->cartModel->findPackageCartItem($this->userId, $addonPackageId);
                if (!$packageCartItem) {
                    $_SESSION['cart_addon_error'] = 'Add the package to your cart before selecting its add-on services.';
                    redirect('customerServices/packages');
                    return;
                }
                $pending['package_cart_item_id'] = (int)$packageCartItem['cart_item_id'];
            }
            if (
                ($pending['item_type'] ?? '') === 'service'
                && empty($pending['confirm_included_service'])
                && ($conflict = $this->cartModel->findCartPackageIncludingService($this->userId, (int)($pending['item_id'] ?? 0)))
            ) {
                $_SESSION['cart_included_service_warning'] = [
                    'item' => $pending,
                    'conflict' => $conflict,
                ];
            } else {
                $this->cartModel->addItem($this->userId, $pending);
            }
            // Don't redirect — let the user see the cart with the item
        }

        // Process guest cart cookie items after login — only when the DB
        // cart is empty so stale cookie items never pollute an active cart.
        if ($this->userId && hasGuestCart()) {
            $existingItems = $this->cartModel->getCartItems($this->userId);
            if (empty($existingItems)) {
                $guestItems = getGuestCartItems();
                clearGuestCart();
                foreach ($guestItems as $gItem) {
                    $addonPackageId = (int)($gItem['addon_package_id'] ?? 0);
                    if ($addonPackageId > 0) {
                        $packageCartItem = $this->cartModel->findPackageCartItem($this->userId, $addonPackageId);
                        if (!$packageCartItem) continue;
                        $gItem['package_cart_item_id'] = (int)$packageCartItem['cart_item_id'];
                    }
                    $this->cartModel->addItem($this->userId, $gItem);
                }
            } else {
                // DB cart already has items — discard stale guest cookie
                clearGuestCart();
            }
        }

        $items = [];
        $total = 0;
        $packageServices = [];
        $isGuest = !$this->userId;
        $guestItems = [];

        if ($this->userId) {
            $items = $this->cartModel->getCartItems($this->userId);
            $total = $this->cartModel->getCartTotal($this->userId);
            $packageServices = $this->cartModel->getCartPackageServices($this->userId);
            foreach ($items as &$item) {
                $item['included_services'] = $packageServices[(int)($item['cart_item_id'] ?? 0)] ?? [];
                if (($item['item_type'] ?? '') === 'package' && !empty($item['selected_date'])) {
                    $item['package_schedule'] = $this->cartModel->getPackageEventSchedule(
                        (int)($item['item_id'] ?? 0),
                        (string)$item['selected_date']
                    );
                } else {
                    $item['package_schedule'] = [];
                }
            }
            unset($item);
        } elseif (hasGuestCart()) {
            // Show guest cookie items so users see what they added before login
            $guestItems = getGuestCartItems();
        }

        $this->view('cart/index', [
            'items' => $items,
            'total' => $total,
            'cartCount' => count($items) + count($guestItems),
            'includedServiceWarning' => $_SESSION['cart_included_service_warning'] ?? null,
            'addonError' => $_SESSION['cart_addon_error'] ?? null,
            'isGuest' => $isGuest,
            'guestItems' => $guestItems,
        ]);
        unset($_SESSION['cart_addon_error']);
    }

    /**
     * Add an item to the cart (POST only).
     *
     * Expects: service_id, date (optional), slot_id (optional),
     *          start_time (optional), end_time (optional), price (optional)
     */
    public function add()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('customerServices/service');
            return;
        }

        $serviceId = (int)($_POST['service_id'] ?? 0);
        if ($serviceId <= 0) {
            redirect('customerServices/service');
            return;
        }

        $itemData = [
            'item_type' => 'service',
            'item_id' => $serviceId,
            'selected_date' => trim($_POST['date'] ?? '') ?: null,
            'price' => !empty($_POST['price']) ? (float)$_POST['price'] : null,
            'source' => ($_POST['source'] ?? '') === 'package' ? 'package' : 'custom',
            'slot_id' => !empty($_POST['slot_id']) ? (int)$_POST['slot_id'] : null,
            'venue_room_id' => !empty($_POST['venue_room_id']) ? (int)$_POST['venue_room_id'] : null,
            'start_time' => trim($_POST['start_time'] ?? '') ?: null,
            'end_time' => trim($_POST['end_time'] ?? '') ?: null,
            'addon_package_id' => !empty($_POST['addon_package_id']) ? (int)$_POST['addon_package_id'] : null,
            // Attire rental fields
            'attire_item_id' => !empty($_POST['attire_item_id']) ? (int)$_POST['attire_item_id'] : null,
            'rental_type' => in_array($_POST['rental_type'] ?? '', ['borrow', 'buy'], true) ? $_POST['rental_type'] : null,
            'borrow_date' => !empty($_POST['borrow_date']) ? trim($_POST['borrow_date']) : null,
            'rental_option_id' => !empty($_POST['rental_option_id']) ? (int)$_POST['rental_option_id'] : null,
            // Decoration style selection
            'decoration_style_id' => !empty($_POST['decoration_style_id']) ? (int)$_POST['decoration_style_id'] : null,
            // Food item selection
            'cake_design_id' => !empty($_POST['cake_design_id']) ? (int)$_POST['cake_design_id'] : null,
            // Guest count (shared across venue + food)
            'guest_count' => !empty($_POST['guest_count']) ? max(1, (int)$_POST['guest_count']) : null,
        ];

        // Block locked items (items in active packages can't be booked standalone)
        $packageModel = $this->model('PlatformPackage');
        $lockedItems = $packageModel->getLockedItemIds();
        if (!empty($itemData['venue_room_id']) && in_array((int)$itemData['venue_room_id'], $lockedItems['venue_room_ids'], true)) {
            $_SESSION['cart_attire_error'] = 'This venue room is only available through a package. Please browse available packages.';
            redirect('customerServices/detail/' . $serviceId);
            return;
        }
        if (!empty($itemData['attire_item_id']) && in_array((int)$itemData['attire_item_id'], $lockedItems['attire_item_ids'], true)) {
            $_SESSION['cart_attire_error'] = 'This attire item is only available through a package. Please browse available packages.';
            redirect('customerServices/detail/' . $serviceId);
            return;
        }
        if (!empty($itemData['decoration_style_id']) && in_array((int)$itemData['decoration_style_id'], $lockedItems['decoration_style_ids'], true)) {
            $_SESSION['cart_attire_error'] = 'This decoration style is only available through a package. Please browse available packages.';
            redirect('customerServices/detail/' . $serviceId);
            return;
        }
        if (!empty($itemData['cake_design_id']) && in_array((int)$itemData['cake_design_id'], $lockedItems['food_item_ids'], true)) {
            $_SESSION['cart_attire_error'] = 'This food item is only available through a package. Please browse available packages.';
            redirect('customerServices/detail/' . $serviceId);
            return;
        }

        if (
            !empty($itemData['selected_date'])
            && !$this->cartModel->isDateAllowedByLeadTime($serviceId, $itemData['selected_date'], $itemData['venue_room_id'])
        ) {
            redirect('customerServices/detail/' . $serviceId);
            return;
        }

        // Validate attire rental availability
        if (!empty($itemData['attire_item_id']) && $itemData['rental_type'] === 'borrow' && !empty($itemData['borrow_date']) && !empty($itemData['rental_option_id'])) {
            $rentalOption = $this->cartModel->getRentalOption((int)$itemData['rental_option_id']);
            if ($rentalOption) {
                $borrowDate = $itemData['borrow_date'];
                $rentalDays = (int)$rentalOption['days'];
                $bufferDays = (int)($rentalOption['buffer_days'] ?? 1);
                $returnDate = date('Y-m-d', strtotime($borrowDate . " + " . ($rentalDays - 1) . " days"));
                $bufferUntil = date('Y-m-d', strtotime($returnDate . " + " . $bufferDays . " days"));

                if (!$this->cartModel->isAttireItemAvailable((int)$itemData['attire_item_id'], $borrowDate, $bufferUntil)) {
                    $_SESSION['cart_attire_error'] = 'This item is not available for the selected dates. Please choose different dates.';
                    redirect('customerServices/detail/' . $serviceId);
                    return;
                }

                // Set selected_date to borrow_date for display
                $itemData['selected_date'] = $borrowDate;
            }
        }

        // Per-person food pricing: multiply base price by guest count
        if (!empty($itemData['cake_design_id']) && !empty($itemData['guest_count']) && $itemData['guest_count'] > 0) {
            $foodItem = $this->cartModel->getFoodItem((int)$itemData['cake_design_id']);
            if ($foodItem && ($foodItem['pricing_model'] ?? 'flat') === 'per_person') {
                $itemData['price'] = (float)($foodItem['price'] ?? 0) * (int)$itemData['guest_count'];
            }
        }

        if (!$this->userId) {
            // Not logged in — stash in session AND cookie for persistence
            $_SESSION['cart_pending'] = $itemData;
            saveGuestCartItem($itemData);
            $_SESSION['cart_redirect_after_login'] = 'cart';
            redirect('users/auth');
            return;
        }

        if (!empty($itemData['addon_package_id'])) {
            $packageCartItem = $this->cartModel->findPackageCartItem($this->userId, (int)$itemData['addon_package_id']);
            if (!$packageCartItem) {
                $_SESSION['cart_addon_error'] = 'Add the package to your cart before selecting its add-on services.';
                redirect('customerServices/packages');
                return;
            }
            $itemData['package_cart_item_id'] = (int)$packageCartItem['cart_item_id'];
        }

        if (empty($_POST['confirm_included_service'])) {
            $conflict = $this->cartModel->findCartPackageIncludingService($this->userId, $serviceId);
            if ($conflict) {
                $_SESSION['cart_included_service_warning'] = [
                    'item' => $itemData,
                    'conflict' => $conflict,
                ];
                redirect('cart');
                return;
            }
        }

        unset($_SESSION['cart_included_service_warning']);
        $this->cartModel->addItem($this->userId, $itemData);
        redirect('cart');
    }

    public function dismissIncludedReminder()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('cart');
            return;
        }

        unset($_SESSION['cart_included_service_warning']);
        redirect('cart');
    }

    public function addPackage()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('customerServices/packages');
            return;
        }

        $packageId = (int)($_POST['package_id'] ?? 0);
        if ($packageId <= 0) {
            redirect('customerServices/packages');
            return;
        }

        $packageModel = $this->model('PlatformPackage');
        $package = $packageModel->getPackageById($packageId);
        $packagePrice = $package ? (float)($package['package_price'] ?? $package['base_price'] ?? 0) : 0;

        $selectedDate = trim((string)($_POST['selected_date'] ?? ''));
        if ($selectedDate !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $selectedDate)) {
            $selectedDate = '';
        }

        $itemData = [
            'item_type' => 'package',
            'item_id' => $packageId,
            'price' => $packagePrice > 0 ? $packagePrice : (!empty($_POST['price']) ? (float)$_POST['price'] : null),
            'source' => 'package',
            'selected_date' => $selectedDate !== '' ? $selectedDate : null,
        ];

        if (!$this->userId) {
            $_SESSION['cart_pending'] = $itemData;
            saveGuestCartItem($itemData);
            $_SESSION['cart_redirect_after_login'] = 'cart';
            redirect('users/auth');
            return;
        }

        $this->cartModel->addItem($this->userId, $itemData);
        redirect('cart');
    }

    /**
     * Remove an item from the cart (POST only).
     */
    public function remove()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$this->userId) {
            redirect('cart');
            return;
        }

        $cartItemId = (int)($_POST['cart_item_id'] ?? 0);
        if ($cartItemId > 0) {
            $this->cartModel->removeItem($this->userId, $cartItemId);
        }

        redirect('cart');
    }

    /**
     * Update selected customization details for one cart item (POST only).
     */
    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !$this->userId) {
            redirect('cart');
            return;
        }

        $cartItemId = (int)($_POST['cart_item_id'] ?? 0);
        if ($cartItemId > 0) {
            $item = $this->cartModel->getCartItem($this->userId, $cartItemId);
            if (!$item || ($item['item_type'] ?? '') !== 'service') {
                redirect('cart');
                return;
            }

            $date = trim($_POST['date'] ?? '');
            $startTime = trim($_POST['start_time'] ?? '');
            $endTime = trim($_POST['end_time'] ?? '');
            $venueRoomId = !empty($item['venue_room_id']) ? (int)$item['venue_room_id'] : null;
            if (!$this->cartModel->isDateAllowedByLeadTime((int)$item['item_id'], $date, $venueRoomId)) {
                redirect('cart');
                return;
            }

            $isFullday = ($item['booking_type'] ?? 'fullday') !== 'slot';

            if ($isFullday) {
                // Fullday items: resolve time from schedule → service default → category fallback
                $serviceId = (int)($item['item_id'] ?? 0);
                $resolvedStart = null;
                $resolvedEnd = null;

                // Try service schedule for the selected date's day-of-week
                if ($date && $serviceId > 0) {
                    $scheduleRow = $this->cartModel->getServiceScheduleForDay($serviceId, $date);
                    if ($scheduleRow) {
                        $resolvedStart = $scheduleRow['open_time'] ?? null;
                        $resolvedEnd = $scheduleRow['close_time'] ?? null;
                    }
                }

                // Fallback to service defaults
                if (!$resolvedStart || !$resolvedEnd) {
                    $serviceDefaults = $this->cartModel->getServiceDefaultTimes($serviceId);
                    $resolvedStart = $resolvedStart ?: ($serviceDefaults['default_start_time'] ?? null);
                    $resolvedEnd = $resolvedEnd ?: ($serviceDefaults['default_end_time'] ?? null);
                }

                // Fallback to category defaults
                if (!$resolvedStart || !$resolvedEnd) {
                    $categoryId = $this->cartModel->getServiceCategoryId($serviceId);
                    $categoryTimes = defined('CATEGORY_DEFAULT_TIMES') ? (CATEGORY_DEFAULT_TIMES[$categoryId] ?? null) : null;
                    $resolvedStart = $resolvedStart ?: ($categoryTimes['start'] ?? '00:00:00');
                    $resolvedEnd = $resolvedEnd ?: ($categoryTimes['end'] ?? '23:59:59');
                }

                $this->cartModel->updateItemCustomization($this->userId, $cartItemId, [
                    'selected_date' => $date,
                    'slot_id' => null,
                    'start_time' => $resolvedStart,
                    'end_time' => $resolvedEnd,
                ]);
            } else {
                $slot = $this->cartModel->findAvailableSlotForServiceDate((int)$item['item_id'], $date, $startTime, $endTime);

                if (!$slot) {
                    redirect('cart');
                    return;
                }

                $this->cartModel->updateItemCustomization($this->userId, $cartItemId, [
                    'selected_date' => $date,
                    'slot_id' => $slot['slot_id'] ?? null,
                    'start_time' => $slot['start_time'],
                    'end_time' => $slot['end_time'],
                ]);
            }
        }

        redirect('cart');
    }

    /**
     * JSON endpoint: return current cart count for the nav badge.
     */
    public function cartCount()
    {
        $count = 0;
        if ($this->userId) {
            $count = $this->cartModel->getCartCount($this->userId);
        } elseif (hasGuestCart()) {
            $count = count(getGuestCartItems());
        }

        header('Content-Type: application/json');
        echo json_encode(['count' => $count]);
        exit;
    }
}
