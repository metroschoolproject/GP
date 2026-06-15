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
        // Process any pending item that was added before login
        if ($this->userId && !empty($_SESSION['cart_pending'])) {
            $pending = $_SESSION['cart_pending'];
            unset($_SESSION['cart_pending']);
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

        $items = [];
        $total = 0;

        if ($this->userId) {
            $items = $this->cartModel->getCartItems($this->userId);
            $total = $this->cartModel->getCartTotal($this->userId);
        }

        $this->view('cart/index', [
            'items' => $items,
            'total' => $total,
            'cartCount' => count($items),
            'includedServiceWarning' => $_SESSION['cart_included_service_warning'] ?? null,
        ]);
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
            'start_time' => trim($_POST['start_time'] ?? '') ?: null,
            'end_time' => trim($_POST['end_time'] ?? '') ?: null,
        ];

        if (!$this->userId) {
            // Not logged in — stash in session and redirect to auth
            $_SESSION['cart_pending'] = $itemData;
            $_SESSION['cart_redirect_after_login'] = 'cart';
            redirect('users/auth');
            return;
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

        $itemData = [
            'item_type' => 'package',
            'item_id' => $packageId,
            'price' => !empty($_POST['price']) ? (float)$_POST['price'] : null,
            'source' => 'package',
        ];

        if (!$this->userId) {
            $_SESSION['cart_pending'] = $itemData;
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
        }

        header('Content-Type: application/json');
        echo json_encode(['count' => $count]);
        exit;
    }
}
