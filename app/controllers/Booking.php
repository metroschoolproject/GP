<?php

require_once APPROOT . '/traits/JsonResponseTrait.php';

class Booking extends Controller
{
    use JsonResponseTrait;

    private BookingModel $bookingModel;
    private CartModel $cartModel;
    private SupplierProfile $supplierProfileModel;
    private Notification $notificationModel;
    private PaymentGatewayService $paymentGateway;
    private ?int $userId;
    private const DEPOSIT_PERCENT = 10;

    public function __construct()
    {
        $this->bookingModel = $this->model('BookingModel');
        $this->cartModel = $this->model('CartModel');
        $this->supplierProfileModel = $this->model('SupplierProfile');
        $this->notificationModel = $this->model('Notification');
        require_once APPROOT . '/services/PaymentGatewayService.php';
        $this->paymentGateway = new PaymentGatewayService();
        $this->userId = $_SESSION['session_uid'] ?? null;
    }

    /* ─── Helper ──────────────────────────────────────────────── */

    private function ensureAuthenticated(): void
    {
        if (!$this->userId) {
            redirect('users/auth');
            exit;
        }
    }

    private function money($v): string
    {
        return number_format((float)$v, 0) . ' MMK';
    }

    private function plain($v): string
    {
        $text = (string)$v;
        for ($i = 0; $i < 10; $i++) {
            $decoded = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            if ($decoded === $text) break;
            $text = $decoded;
        }
        return $text;
    }

    private function h($v): string
    {
        return htmlspecialchars($this->plain($v), ENT_QUOTES, 'UTF-8');
    }

    /* ─── Step 1: Confirm Booking (GET + POST) ──────────────────── */

    public function create(): void
    {
        $this->ensureAuthenticated();

        $items = $this->cartModel->getCartItems($this->userId);
        $total = $this->cartModel->getCartTotal($this->userId);

        if (empty($items)) {
            redirect('cart');
            return;
        }

        $user = $this->getUserData();
        
        // ===== NEW: Find venue service =====
        $venueService = null;
        foreach ($items as $item) {
            // Check if this item is a venue category service
            if (strtolower($item['category_name'] ?? '') === 'venue' || 
                strtolower($item['category_name'] ?? '') === 'venue & catering') {
                $venueService = [
                    'service_id' => (int)($item['service_id'] ?? 0),
                    'name' => $item['service_name'],
                    'location' => $item['service_location'] ?? $item['service_name'], // Use service location if available
                ];
                break;
            }
        }
        // ===== END =====

        $this->view('booking/create', [
            'items' => $items,
            'total' => (float)$total,
            'cartCount' => count($items),
            'user' => $user,
            'depositPercent' => self::DEPOSIT_PERCENT,
            'venueService' => $venueService, // Pass to view
        ]);
    }

    public function createPost(): void
    {
        $this->ensureAuthenticated();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Method not allowed'], 405);
        }

        try {
        
        $items = $this->cartModel->getCartItems($this->userId);
        $total = $this->cartModel->getCartTotal($this->userId);
        
        if (empty($items)) {
            $this->jsonResponse(['error' => 'Cart is empty'], 400);
        }
        
        // PARSE PER-ITEM DATA
        $itemsData = [];
        $itemPrices = [];
        $adjustedTotal = 0.0;
        $itemErrors = [];
        
        foreach ($items as $i => $item) {
            $itemDate = trim($_POST['item_date'][$i] ?? '') ?: trim((string)($item['selected_date'] ?? ''));
            $itemStartTime = trim($_POST['item_start_time'][$i] ?? '') ?: trim((string)($item['start_time'] ?? ''));
            $itemEndTime = trim($_POST['item_end_time'][$i] ?? '') ?: trim((string)($item['end_time'] ?? ''));
            $itemGuests = (int)($_POST['item_guests'][$i] ?? 0);
            $itemLocation = trim($_POST['item_location'][$i] ?? '');
            $itemPhone = trim($_POST['item_contact_phone'][$i] ?? '');
            $itemContactName = trim($_POST['item_contact_name'][$i] ?? '');
            $itemName = $item['service_name'] ?? 'Service';
            $minLeadDays = max(0, (int)($item['min_lead_days'] ?? 0));
            $currentItemErrors = [];
            
            // Required details for suppliers before payment.
            if (empty($itemDate)) {
                $currentItemErrors[] = 'Date is required';
            } elseif (!$this->isDateAllowedByLeadTime($itemDate, $minLeadDays)) {
                $currentItemErrors[] = $this->leadTimeMessage($minLeadDays);
            }
            if (empty($itemStartTime)) {
                $currentItemErrors[] = 'Time slot is required';
            }
            if (empty($itemEndTime)) {
                $currentItemErrors[] = 'Time slot end time is required';
            }
            if (empty($itemContactName)) {
                $currentItemErrors[] = 'Contact name is required';
            }
            if (empty($itemPhone)) {
                $currentItemErrors[] = 'Contact phone is required';
            }
            if (empty($itemLocation)) {
                $currentItemErrors[] = 'Location is required';
            }
            if ($itemGuests <= 0) {
                $currentItemErrors[] = 'Guest count is required';
            }
            
            if (!empty($currentItemErrors)) {
                $itemErrors[] = $itemName . ': ' . implode(', ', $currentItemErrors);
                continue;
            }

            $basePrice = (float)($item['cart_price'] ?? $item['price_min'] ?? $item['price_max'] ?? 0);
            $isGuestPriced = $this->isGuestPricedService($item);
            $itemPrice = $isGuestPriced ? $basePrice * $itemGuests : $basePrice;
            
            // Collect per-item details (with fallback to shared defaults)
            $itemsData[] = [
                'event_date' => $itemDate,
                'start_time' => $itemStartTime,
                'end_time' => $itemEndTime,
                'guest_count' => $itemGuests,
                'location' => $itemLocation,
                'phone' => $itemPhone,
                'contact_name' => $itemContactName,
                'notes' => trim($_POST['item_notes'][$i] ?? ''),
            ];
            $itemPrices[] = $itemPrice;
            $adjustedTotal += $itemPrice;
        }
        
        // Return validation errors if any
        if (!empty($itemErrors)) {
            $this->jsonResponse(['error' => implode('; ', $itemErrors)], 400);
        }

        // Validate min_lead_days for each item
        $leadTimeErrors = [];
        $today = new DateTimeImmutable('today');
        foreach ($items as $i => $item) {
            $itemDate = trim($_POST['item_date'][$i] ?? '') ?: trim((string)($item['selected_date'] ?? ''));
            if (!empty($itemDate)) {
                $selectedDate = DateTimeImmutable::createFromFormat('!Y-m-d', $itemDate);
                if ($selectedDate) {
                    $minLeadDays = (int)($item['min_lead_days'] ?? 0);
                    $minDate = $today->add(new DateInterval('P' . $minLeadDays . 'D'));

                    if ($selectedDate < $minDate) {
                        $itemName = $item['service_name'] ?? 'Service';
                        $dayWord = $minLeadDays === 1 ? 'day' : 'days';
                        $leadTimeErrors[] = $itemName . ': requires ' . $minLeadDays . ' ' . $dayWord . ' advance notice (earliest: ' . $minDate->format('M j, Y') . ')';
                    }
                }
            }
        }

        if (!empty($leadTimeErrors)) {
            $this->jsonResponse(['error' => 'Lead time requirement not met: ' . implode('; ', $leadTimeErrors)], 422);
        }

        // CREATE BOOKING
        $cartId = $this->cartModel->getOrCreateCart($this->userId);
        $bookingId = $this->bookingModel->createDraftFromCart($this->userId, $cartId, $adjustedTotal);
        
        if (!$bookingId) {
            $this->jsonResponse(['error' => 'Could not create booking'], 500);
        }
        
        // INSERT BOOKING ITEMS (and get back IDs)
        $bookingItemIds = $this->bookingModel->insertBookingItems($bookingId, $this->userId, $itemPrices);
        if (!$bookingItemIds) {
            $this->jsonResponse(['error' => 'Could not save booking items'], 500);
        }
        
        // INSERT EVENT DETAILS (with booking_item_id)
        if (!$this->bookingModel->insertEventDetails($bookingId, $itemsData, $bookingItemIds)) {
            $this->jsonResponse(['error' => 'Could not save event details'], 500);
        }
        
        // LINK SUPPLIERS
        if (!$this->bookingModel->insertBookingSuppliers($bookingId)) {
            $this->jsonResponse(['error' => 'Could not assign suppliers'], 500);
        }
        
        // CLEAR CART & LOG
        $this->bookingModel->clearCart($this->userId);
        $this->bookingModel->logStatusChange($bookingId, null, 'draft', $this->userId);

        // NOTIFY SUPPLIERS
        $customerName = $_SESSION['session_name'] ?? 'A customer';
        $itemList = array_map(fn($item) => $item['service_name'] ?? 'a service', $items);
        $serviceNames = implode(', ', $itemList);
        $this->notificationModel->notifyBookingSuppliers(
            $bookingId,
            'New Booking',
            $customerName . ' booked: ' . $serviceNames . '. Please review and confirm.',
            'booking'
        );
        
        $this->jsonResponse([
            'success' => true,
            'booking_id' => $bookingId,
            'redirect' => URLROOT . '/booking/pay/' . $bookingId,
        ]);
        } catch (Throwable $e) {
            $this->jsonResponse([
                'error' => 'Booking could not be created: ' . $e->getMessage(),
            ], 500);
        }
    }

    /* ─── Step 2: Payment (Stripe) ────────────────────────────── */

    public function pay(int $bookingId): void
    {
        $this->ensureAuthenticated();

        $booking = $this->bookingModel->getBookingById($bookingId);
        if (!$booking || (int)$booking['user_id'] !== $this->userId) {
            redirect('booking/myBookings');
            return;
        }

        if ($booking['status'] !== 'draft' && $booking['status'] !== 'pending_payment') {
            redirect('booking/myBookings');
            return;
        }

        $items = $this->bookingModel->getBookingItems($bookingId);
        $total = (float)$booking['total_amount'];
        $deposit = $total * (self::DEPOSIT_PERCENT / 100);

        // Update booking to pending_payment
        if ($booking['status'] === 'draft') {
            $this->bookingModel->updateStatus($bookingId, 'pending_payment');
            $this->bookingModel->logStatusChange($bookingId, 'draft', 'pending_payment', $this->userId);
        }

        // Render new payment methods page (supports KBZ Pay, AYA Bank, MM QR, Visa Card)
        $this->view('booking/paymentMethods', [
            'booking' => $booking,
            'items' => $items,
            'total' => $total,
            'deposit' => $deposit,
            'depositPercent' => self::DEPOSIT_PERCENT,
            'balance' => $total - $deposit,
            'bookingRef' => $this->bookingModel->generateBookingRef($bookingId),
            'stripePublishableKey' => $this->getStripePublishableKey(),
        ]);
    }

    /**
     * Process Stripe payment (AJAX POST).
     */
    public function processPayment(): void
    {
        $this->ensureAuthenticated();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Method not allowed'], 405);
        }

        $bookingId = (int)($_POST['booking_id'] ?? 0);
        $paymentMethodId = trim($_POST['payment_method_id'] ?? '');

        if ($bookingId <= 0 || $paymentMethodId === '') {
            $this->jsonResponse(['error' => 'Invalid payment data'], 400);
        }

        $booking = $this->bookingModel->getBookingById($bookingId);
        if (!$booking || (int)$booking['user_id'] !== $this->userId) {
            $this->jsonResponse(['error' => 'Booking not found'], 404);
        }

        if (!in_array($booking['status'] ?? '', ['draft', 'pending_payment'], true)) {
            $this->jsonResponse(['error' => 'This booking is not awaiting payment.'], 400);
        }

        $total = (float)$booking['total_amount'];
        $deposit = $total * (self::DEPOSIT_PERCENT / 100);

        try {
            $stripe = $this->getStripeClient();

            // Create a PaymentIntent for the deposit amount (in cents)
            $intent = \Stripe\PaymentIntent::create([
                'amount' => (int)round($deposit * 100), // cents
                'currency' => 'myr',
                'payment_method' => $paymentMethodId,
                'confirmation_method' => 'manual',
                'confirm' => true,
                'description' => 'Booking #' . $this->bookingModel->generateBookingRef($bookingId),
                'metadata' => [
                    'booking_id' => (string)$bookingId,
                    'user_id' => (string)$this->userId,
                ],
                'return_url' => URLROOT . '/booking/success/' . $bookingId,
            ]);

            if ($intent->status === 'succeeded') {
                // Payment succeeded immediately
                $this->handleSuccessfulPayment($bookingId, $deposit, $intent->id);
                return;
            } elseif ($intent->status === 'requires_action') {
                // 3D Secure required
                $this->jsonResponse([
                    'requires_action' => true,
                    'payment_intent_client_secret' => $intent->client_secret,
                ]);
                return;
            }

            $this->jsonResponse(['error' => 'Unexpected payment status: ' . $intent->status], 400);
        } catch (\Stripe\Exception\CardException $e) {
            $this->jsonResponse([
                'error' => $e->getMessage() ?: 'Your card was declined. Please try a different card.',
                'decline_code' => $e->getDeclineCode(),
            ], 402);
        } catch (\Throwable $e) {
            $this->jsonResponse([
                'error' => 'Payment processing error. Your card has not been charged. Please try again.',
            ], 500);
        }
    }

    public function startGatewayPayment(): void
    {
        $this->ensureAuthenticated();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Method not allowed'], 405);
        }

        $bookingId = (int)($_POST['booking_id'] ?? 0);
        $method = trim($_POST['payment_method'] ?? '');

        if ($bookingId <= 0 || !in_array($method, ['mm-qr', 'visa-card'], true)) {
            $this->jsonResponse(['error' => 'Please choose a valid payment method.'], 400);
        }

        $booking = $this->bookingModel->getBookingById($bookingId);
        if (!$booking || (int)$booking['user_id'] !== $this->userId) {
            $this->jsonResponse(['error' => 'Booking not found'], 404);
        }

        if (!in_array($booking['status'] ?? '', ['draft', 'pending_payment'], true)) {
            $this->jsonResponse(['error' => 'This booking is not awaiting payment.'], 400);
        }

        if (($booking['status'] ?? '') === 'draft') {
            $this->bookingModel->updateStatus($bookingId, 'pending_payment');
            $this->bookingModel->logStatusChange($bookingId, 'draft', 'pending_payment', $this->userId);
        }

        $deposit = (float)$booking['total_amount'] * (self::DEPOSIT_PERCENT / 100);
        $gatewayMethod = $method === 'mm-qr' ? 'mm_qr' : 'credit_card';
        $localMethod = $method === 'mm-qr' ? '2c2p_mmqr' : '2c2p_card';

        $paymentId = $this->bookingModel->createPayment($bookingId, $deposit, 'deposit', $localMethod);
        if (!$paymentId) {
            $this->jsonResponse(['error' => 'Could not create payment record. Please try again.'], 500);
        }

        $returnUrl = URLROOT . '/booking/gatewayPaymentReturn/' . $bookingId . '?payment_id=' . $paymentId;
        $backendReturnUrl = URLROOT . '/booking/gatewayPaymentReturn/' . $bookingId . '?payment_id=' . $paymentId;
        $result = $this->paymentGateway->createPaymentIntent($paymentId, $deposit, $gatewayMethod, $returnUrl, $backendReturnUrl);

        if (!($result['success'] ?? false)) {
            if (defined('PAYMENT_GATEWAY_SANDBOX') && PAYMENT_GATEWAY_SANDBOX && (($result['response']['respCode'] ?? '') === '9007')) {
                $transactionRef = 'SANDBOX-' . strtoupper(bin2hex(random_bytes(4))) . '-' . $paymentId;
                $this->confirmGatewayPaymentRecord($booking, $paymentId, $deposit, $transactionRef);
                $this->jsonResponse([
                    'success' => true,
                    'redirect' => URLROOT . '/booking/success/' . $bookingId,
                    'message' => 'Sandbox payment approved locally because 2C2P rejected the configured test credentials.',
                    'sandbox_fallback' => true,
                ]);
            }

            $this->bookingModel->updatePaymentStatus($paymentId, 'failed');
            $this->jsonResponse([
                'error' => $result['error'] ?? '2C2P sandbox could not start payment.',
            ], 502);
        }

        $this->jsonResponse([
            'success' => true,
            'redirect' => $result['payment_url'],
        ]);
    }

    public function gatewayPaymentReturn(int $bookingId): void
    {
        $this->ensureAuthenticated();

        $paymentId = (int)($_GET['payment_id'] ?? 0);
        $payload = trim($_POST['payload'] ?? $_GET['payload'] ?? '');

        $booking = $this->bookingModel->getBookingById($bookingId);
        if (!$booking || (int)$booking['user_id'] !== $this->userId || $paymentId <= 0) {
            redirect('booking/myBookings');
            return;
        }

        if ($payload === '') {
            $_SESSION['booking_payment_flash'] = 'Payment is pending. If you completed sandbox payment, wait a moment and refresh.';
            redirect('booking/detail/' . $bookingId);
            return;
        }

        $gatewayResponse = $this->paymentGateway->decodeGatewayPayload($payload);
        if (!$gatewayResponse) {
            $_SESSION['booking_payment_flash'] = 'Could not verify payment gateway response.';
            redirect('booking/detail/' . $bookingId);
            return;
        }

        if (($gatewayResponse['respCode'] ?? '') !== '0000') {
            $this->bookingModel->updatePaymentStatus($paymentId, 'failed');
            $_SESSION['booking_payment_flash'] = 'Payment failed: ' . ($gatewayResponse['respDesc'] ?? 'Please try again.');
            redirect('booking/pay/' . $bookingId);
            return;
        }

        $transactionRef = $gatewayResponse['tranRef']
            ?? $gatewayResponse['transactionID']
            ?? $gatewayResponse['approvalCode']
            ?? ($gatewayResponse['invoiceNo'] ?? '');

        $deposit = (float)$booking['total_amount'] * (self::DEPOSIT_PERCENT / 100);
        $this->confirmGatewayPaymentRecord($booking, $paymentId, $deposit, (string)$transactionRef);

        redirect('booking/success/' . $bookingId);
    }

    private function confirmGatewayPaymentRecord(array $booking, int $paymentId, float $deposit, string $transactionRef): void
    {
        $bookingId = (int)($booking['id'] ?? 0);

        $this->bookingModel->confirmPayment($paymentId, $transactionRef);
        $this->bookingModel->updatePaidAmount($bookingId, $deposit);
        $this->bookingModel->updateStatus($bookingId, 'paid', 'partial');
        $this->bookingModel->logStatusChange($bookingId, $booking['status'] ?? 'pending_payment', 'paid', $this->userId);
        $this->bookingModel->generateVouchers($bookingId);

        $this->notificationModel->notifyBookingCustomer(
            $bookingId,
            'Payment Confirmed',
            'Your deposit of ' . $this->money($deposit) . ' has been confirmed.',
            'payment'
        );
        $this->notificationModel->notifyBookingSuppliers(
            $bookingId,
            'Deposit Paid',
            'The customer has paid the deposit. Please review and confirm the booking.',
            'booking'
        );
    }

    /**
     * Confirm payment after 3D Secure authentication.
     */
    public function confirmPayment(): void
    {
        $this->ensureAuthenticated();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Method not allowed'], 405);
        }

        $paymentIntentId = trim($_POST['payment_intent_id'] ?? '');
        $bookingId = (int)($_POST['booking_id'] ?? 0);

        if ($paymentIntentId === '' || $bookingId <= 0) {
            $this->jsonResponse(['error' => 'Invalid data'], 400);
        }

        try {
            $stripe = $this->getStripeClient();
            $intent = \Stripe\PaymentIntent::retrieve($paymentIntentId);

            if ($intent->status === 'succeeded') {
                // Get proper booking amount from metadata or booking record
                $booking = $this->bookingModel->getBookingById($bookingId);
                if (!$booking || (int)$booking['user_id'] !== $this->userId) {
                    $this->jsonResponse(['error' => 'Booking not found'], 404);
                }
                $total = (float)$booking['total_amount'];
                $deposit = $total * (self::DEPOSIT_PERCENT / 100);

                $this->handleSuccessfulPayment($bookingId, $deposit, $intent->id);
                return;
            } elseif ($intent->status === 'requires_confirmation') {
                $intent->confirm();
                if ($intent->status === 'succeeded') {
                    $booking = $this->bookingModel->getBookingById($bookingId);
                    if (!$booking || (int)$booking['user_id'] !== $this->userId) {
                        $this->jsonResponse(['error' => 'Booking not found'], 404);
                    }
                    $total = (float)$booking['total_amount'];
                    $deposit = $total * (self::DEPOSIT_PERCENT / 100);
                    $this->handleSuccessfulPayment($bookingId, $deposit, $intent->id);
                    return;
                }
                $this->jsonResponse(['requires_action' => true, 'payment_intent_client_secret' => $intent->client_secret]);
                return;
            }

            $this->jsonResponse(['error' => 'Payment not completed. Status: ' . $intent->status], 400);
        } catch (\Throwable $e) {
            $this->jsonResponse(['error' => 'Could not confirm payment: ' . $e->getMessage()], 500);
        }
    }

    private function handleSuccessfulPayment(int $bookingId, float $amount, string $transactionRef): void
    {
        $booking = $this->bookingModel->getBookingById($bookingId);
        if (!$booking || (int)$booking['user_id'] !== $this->userId) {
            $this->jsonResponse(['error' => 'Booking not found'], 404);
        }

        if (($booking['payment_status'] ?? '') === 'paid' || (float)($booking['paid_amount'] ?? 0) >= $amount) {
            $this->jsonResponse([
                'success' => true,
                'redirect' => URLROOT . '/booking/success/' . $bookingId,
            ]);
        }

        // Create payment record
        $paymentId = $this->bookingModel->createPayment($bookingId, $amount, 'deposit', 'stripe');
        if ($paymentId) {
            $this->bookingModel->confirmPayment($paymentId, $transactionRef);
        }

        // Update booking status
        $this->bookingModel->updatePaidAmount($bookingId, $amount);
        $this->bookingModel->updateStatus($bookingId, 'paid', 'partial');
        $this->bookingModel->logStatusChange($bookingId, $booking['status'] ?? 'pending_payment', 'paid', $this->userId);

        // Generate vouchers
        $this->bookingModel->generateVouchers($bookingId);

        // Notify customer
        $this->notificationModel->notifyBookingCustomer(
            $bookingId,
            'Payment Received',
            'Your deposit of ' . $this->money($amount) . ' has been received. The suppliers will now confirm your booking.',
            'booking'
        );

        // Notify suppliers
        $this->notificationModel->notifyBookingSuppliers(
            $bookingId,
            'Deposit Paid',
            'The customer has paid the deposit. Please review and confirm the booking.',
            'booking'
        );

        $this->jsonResponse([
            'success' => true,
            'redirect' => URLROOT . '/booking/success/' . $bookingId,
        ]);
    }

    /* ─── Available Slots ──────────────── */

    public function getAvailableSlots(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Method not allowed'], 405);
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        $serviceId = (int)($input['service_id'] ?? 0);
        $date = trim($input['date'] ?? '');
        
        if ($serviceId <= 0 || empty($date)) {
            $this->jsonResponse(['error' => 'Invalid input'], 400);
        }

        $selectedDate = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        $today = new DateTimeImmutable('today');

        if (!$selectedDate) {
            $this->jsonResponse(['error' => 'Invalid date format'], 400);
            return;
        }

        // Check min_lead_days requirement
        $minLeadDays = $this->cartModel->getServiceMinLeadDays($serviceId);
        $minDate = $today->add(new DateInterval('P' . $minLeadDays . 'D'));

        if ($selectedDate < $minDate) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'This service requires ' . $minLeadDays . ' day' . ($minLeadDays === 1 ? '' : 's') . ' advance notice',
                'min_lead_days' => $minLeadDays,
                'earliest_date' => $minDate->format('Y-m-d')
            ], 400);
            return;
        }

        // Also handle past dates for consistency
        if ($selectedDate < $today && $minLeadDays === 0) {
            $this->jsonResponse([
                'success' => true,
                'slots' => [],
                'message' => 'No slots available for this date'
            ]);
            return;
        }

        $slots = $this->cartModel->getAvailableSlotsForServiceDate($serviceId, $date);
        
        if (empty($slots)) {
            $this->jsonResponse([
                'success' => true,
                'slots' => [],
                'message' => 'No slots available for this date'
            ]);
            return;
        }
        
        $formatted = [];
        foreach ($slots as $slot) {
            $formatted[] = [
                'slot_id' => $slot['slot_id'] ?? null,
                'start_time' => $slot['start_time'],
                'end_time' => $slot['end_time'],
                'display' => $slot['display'],
                'available' => (int)$slot['available']
            ];
        }
        
        $this->jsonResponse([
            'success' => true,
            'slots' => $formatted
        ]);
    }

    private function isGuestPricedService(array $item): bool
    {
        $category = strtolower((string)($item['category_name'] ?? ''));
        $name = strtolower((string)($item['service_name'] ?? ''));

        return str_contains($category, 'makeup')
            || str_contains($category, 'make up')
            || str_contains($name, 'makeup')
            || str_contains($name, 'make up');
    }

    private function isDateAllowedByLeadTime(string $date, int $minLeadDays): bool
    {
        $selectedDate = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        if (!$selectedDate) {
            return false;
        }

        return $selectedDate >= $this->earliestBookingDate($minLeadDays);
    }

    private function earliestBookingDate(int $minLeadDays): DateTimeImmutable
    {
        $minLeadDays = max(0, $minLeadDays);
        return (new DateTimeImmutable('today'))->modify('+' . $minLeadDays . ' days');
    }

    private function leadTimeMessage(int $minLeadDays): string
    {
        $earliest = $this->earliestBookingDate($minLeadDays)->format('M j, Y');
        if ($minLeadDays <= 0) {
            return 'Please choose today or a future date.';
        }

        return 'This service must be booked at least ' . $minLeadDays . ' day' . ($minLeadDays === 1 ? '' : 's') . ' in advance. Earliest date: ' . $earliest;
    }

    /* ─── Booking Status Poll (for success page) ──────────────── */

    public function status(int $bookingId): void
    {
        $this->ensureAuthenticated();

        $booking = $this->bookingModel->getBookingById($bookingId);
        if (!$booking || (int)$booking['user_id'] !== $this->userId) {
            $this->jsonResponse(['error' => 'Not found'], 404);
        }

        $this->jsonResponse([
            'status' => $booking['status'],
            'payment_status' => $booking['payment_status'],
        ]);
    }

    /* ─── Success Page ────────────────────────────────────────── */

    public function success(int $bookingId): void
    {
        $this->ensureAuthenticated();

        $booking = $this->bookingModel->getBookingById($bookingId);
        if (!$booking || (int)$booking['user_id'] !== $this->userId) {
            redirect('booking/myBookings');
            return;
        }

        $items = $this->bookingModel->getBookingItems($bookingId);
        $bookingRef = $this->bookingModel->generateBookingRef($bookingId);

        $this->view('booking/success', [
            'booking' => $booking,
            'items' => $items,
            'bookingRef' => $bookingRef,
        ]);
    }

    /* ─── My Bookings (Customer) ──────────────────────────────── */

    public function myBookings(): void
    {
        $this->ensureAuthenticated();

        $filter = trim($_GET['status'] ?? 'all');
        $bookings = $this->bookingModel->getCustomerBookings($this->userId, $filter);

        // Enrich each booking with items count and booking ref
        $enriched = [];
        foreach ($bookings as $b) {
            $b['booking_ref'] = $this->bookingModel->generateBookingRef((int)$b['id']);
            $b['items'] = $this->bookingModel->getBookingItems((int)$b['id']);
            $b['total_amount'] = (float)$b['total_amount'];
            $b['paid_amount'] = (float)$b['paid_amount'];
            $enriched[] = $b;
        }

        $this->view('booking/myBookings', [
            'bookings' => $enriched,
            'activeFilter' => $filter,
        ]);
    }

    public function notificationsJson(): void
    {
        $this->ensureAuthenticated();

        $this->jsonResponse([
            'unread_count' => $this->notificationModel->getUnreadCount($this->userId),
            'notifications' => $this->notificationModel->getLatest($this->userId, 8),
        ]);
    }

    public function markNotificationRead($notificationId = null): void
    {
        $this->ensureAuthenticated();

        if (!$notificationId) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Notification id is required.'], 422);
        }

        $this->notificationModel->markRead((int)$notificationId, $this->userId);
        $this->jsonResponse(['status' => 'success']);
    }

    /* ─── Booking Detail (Customer) ─────────────────────────────── */

    public function detail(int $bookingId): void
    {
        $this->ensureAuthenticated();

        $booking = $this->bookingModel->getBookingById($bookingId);
        if (!$booking || (int)$booking['user_id'] !== $this->userId) {
            redirect('booking/myBookings');
            return;
        }

        $items = $this->bookingModel->getBookingItems($bookingId);
        $eventDetails = $this->bookingModel->getEventDetails($bookingId);
        $suppliers = $this->bookingModel->getBookingSuppliers($bookingId);
        $logs = $this->bookingModel->getStatusLogs($bookingId);
        $vouchers = $this->bookingModel->getBookingVouchers($bookingId);
        $bookingRef = $this->bookingModel->generateBookingRef($bookingId);

        $this->view('booking/detail', [
            'booking' => $booking,
            'items' => $items,
            'eventDetails' => $eventDetails,
            'suppliers' => $suppliers,
            'logs' => $logs,
            'vouchers' => $vouchers,
            'bookingRef' => $bookingRef,
            'depositPercent' => self::DEPOSIT_PERCENT,
        ]);
    }

    /* ─── Vouchers (Customer) ─────────────────────────────────── */

    public function vouchers(): void
    {
        $this->ensureAuthenticated();

        $filter = trim($_GET['status'] ?? 'all');
        $vouchers = $this->bookingModel->getCustomerVouchers($this->userId, $filter);

        $this->view('booking/vouchers', [
            'vouchers' => $vouchers,
            'activeFilter' => $filter,
        ]);
    }

    /* ─── Cancellation Request (Customer) ─────────────────────── */

    public function cancel(int $bookingId): void
    {
        $this->ensureAuthenticated();

        $booking = $this->bookingModel->getBookingById($bookingId);
        if (!$booking || (int)$booking['user_id'] !== $this->userId) {
            redirect('booking/myBookings');
            return;
        }

        if (in_array($booking['status'], ['cancelled', 'completed'], true)) {
            redirect('booking/detail/' . $bookingId);
            return;
        }

        $items = $this->bookingModel->getBookingItems($bookingId);
        $bookingRef = $this->bookingModel->generateBookingRef($bookingId);

        $this->view('booking/cancel', [
            'booking' => $booking,
            'items' => $items,
            'bookingRef' => $bookingRef,
            'depositPercent' => self::DEPOSIT_PERCENT,
        ]);
    }

    public function submitCancellation(): void
    {
        $this->ensureAuthenticated();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Method not allowed'], 405);
        }

        $bookingId = (int)($_POST['booking_id'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');

        if ($bookingId <= 0 || $reason === '') {
            $this->jsonResponse(['error' => 'Please provide a reason for cancellation'], 400);
        }

        $booking = $this->bookingModel->getBookingById($bookingId);
        if (!$booking || (int)$booking['user_id'] !== $this->userId) {
            $this->jsonResponse(['error' => 'Booking not found'], 404);
        }

        if (in_array($booking['status'] ?? '', ['cancelled', 'completed'], true)) {
            $this->jsonResponse(['error' => 'This booking can no longer be cancelled.'], 400);
        }

        if (!$this->bookingModel->requestCancellation($bookingId, $reason)) {
            $this->jsonResponse(['error' => 'Could not submit cancellation request. Please try again.'], 500);
        }

        $this->jsonResponse([
            'success' => true,
            'message' => 'Your cancellation request has been submitted.',
        ]);
    }

    /* ─── Stripe Helpers ─────────────────────────────────────── */

    private function getStripePublishableKey(): string
    {
        // In production, load from config
        $key = getenv('STRIPE_PUBLISHABLE_KEY') ?: 'pk_test_51R8J48A8prqB0d4XosOJQOAU01iXIJhXT5XoE7tIQBc8QELkQtqFRVhNqnqI6ELB7TAUQyP5WFK5yMfx7Z6ctQQW003ImAlGHD';
        return $key ?: '';
    }

    private function getStripeClient(): \Stripe\StripeClient
    {
        $secretKey = getenv('STRIPE_SECRET_KEY') ?: 'sk_test_51R8J48A8prqB0d4XNMGtyys8dyQ8sC0Nd30N3tIp6M9wxqCaQ23LqIYYRrLb1Nsy3NqTTdpFVVV2qVwaxGV3wUJ2001ZNCpLtd';
        return new \Stripe\StripeClient($secretKey);
    }

    /* ─── User Helper ────────────────────────────────────────── */

    private function getUserData(): array
    {
        $db = new Database();
        $db->dbquery("SELECT name, email, phone FROM users WHERE user_id = :uid LIMIT 1");
        $db->dbbind(':uid', $this->userId, PDO::PARAM_INT);
        $user = $db->getsingledata();

        return [
            'name' => $user['name'] ?? '',
            'email' => $user['email'] ?? '',
            'phone' => $user['phone'] ?? '',
        ];
    }

    private function currentSupplierId(): int
    {
        $supplierId = (int)($_SESSION['supplier_id'] ?? 0);
        if ($supplierId > 0) {
            return $supplierId;
        }

        $userId = (int)($_SESSION['session_uid'] ?? 0);
        if ($userId <= 0) {
            return 0;
        }

        $supplier = $this->supplierProfileModel->getByUserId($userId);
        $supplierId = (int)($supplier['supplier_id'] ?? 0);
        if ($supplierId > 0) {
            $_SESSION['supplier_id'] = $supplierId;
        }

        return $supplierId;
    }

    /* ─── Supplier Booking Views ──────────────────────────────── */

    /**
     * Supplier booking dashboard.
     */
    public function supplierBookings(): void
    {
        // This is mounted under the supplier namespace via Supplier controller
        // We use the supplier session ID from $_SESSION
        $supplierId = $this->currentSupplierId();
        if ($supplierId <= 0) {
            redirect('supplier/dashboard');
            return;
        }

        $filter = trim($_GET['status'] ?? 'all');
        $search = trim($_GET['search'] ?? '');
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        // Fetch bookings based on search or normal view
        if (!empty($search)) {
            $bookings = $this->bookingModel->searchSupplierBookings($supplierId, $search, $filter, $perPage, $offset);
            $totalCount = $this->bookingModel->searchSupplierBookingsCount($supplierId, $search, $filter);
        } else {
            $bookings = $this->bookingModel->getSupplierBookingsWithPagination($supplierId, $filter, $perPage, $offset);
            $totalCount = $this->bookingModel->getSupplierBookingsCount($supplierId, $filter);
        }

        $totalPages = ceil($totalCount / $perPage);
        $stats = $this->bookingModel->getSupplierStats($supplierId);
        $performanceMetrics = $this->bookingModel->getSupplierPerformanceMetrics($supplierId);
        $upcomingBookings = $this->bookingModel->getSupplierUpcomingBookings($supplierId);

        // Enrich bookings with items and ref
        $enriched = [];
        foreach ($bookings as $b) {
            $b['booking_ref'] = $this->bookingModel->generateBookingRef((int)$b['id']);
            $b['items'] = $this->bookingModel->getBookingItemsForSupplier((int)$b['id'], $supplierId);
            $b['total_amount'] = (float)($b['supplier_total_amount'] ?? $b['total_amount']);
            $enriched[] = $b;
        }

        require_once APPROOT . '/controllers/SupplierControllerSupport.php';
        // Render using a view that expects the supplier layout
        $this->view('supplier/bookings', [
            'bookings' => $enriched,
            'stats' => $stats,
            'performanceMetrics' => $performanceMetrics,
            'upcomingBookings' => $upcomingBookings,
            'activeFilter' => $filter,
            'supplierId' => $supplierId,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalCount' => $totalCount,
            'perPage' => $perPage,
            'searchQuery' => $search,
        ]);
    }

    /**
     * Supplier booking detail.
     */
    public function supplierBookingDetail(int $bookingId): void
    {
        $supplierId = $this->currentSupplierId();
        if ($supplierId <= 0) {
            redirect('supplier/dashboard');
            return;
        }

        $booking = $this->bookingModel->getBookingById($bookingId);
        if (!$booking) {
            redirect('supplier/bookings');
            return;
        }

        // Verify this supplier is associated
        $suppliers = $this->bookingModel->getBookingSuppliers($bookingId);
        $isAssociated = false;
        foreach ($suppliers as $s) {
            if ((int)$s['supplier_id'] === $supplierId) {
                $isAssociated = true;
                $currentSupplierStatus = $s['status'];
                $currentSupplierRowId = (int)$s['id'];
                break;
            }
        }

        if (!$isAssociated) {
            redirect('supplier/bookings');
            return;
        }

        $items = $this->bookingModel->getBookingItemsForSupplier($bookingId, $supplierId);
        $booking['supplier_total_amount'] = array_sum(array_map(static function ($item) {
            return (float)($item['price'] ?? 0);
        }, $items));
        $eventDetails = $this->bookingModel->getEventDetails($bookingId);
        $bookingRef = $this->bookingModel->generateBookingRef($bookingId);

        $this->view('supplier/bookingDetail', [
            'booking' => $booking,
            'items' => $items,
            'eventDetails' => $eventDetails,
            'suppliers' => $suppliers,
            'bookingRef' => $bookingRef,
            'supplierStatus' => $currentSupplierStatus ?? 'pending',
            'supplierRowId' => $currentSupplierRowId ?? 0,
            'supplierId' => $supplierId,
            'depositPercent' => self::DEPOSIT_PERCENT,
        ]);
    }

    /**
     * Supplier accept/decline booking (AJAX POST).
     */
    public function supplierRespond(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Method not allowed'], 405);
        }

        $supplierId = $this->currentSupplierId();
        if ($supplierId <= 0) {
            $this->jsonResponse(['error' => 'Unauthorized'], 401);
        }

        $bookingId = (int)($_POST['booking_id'] ?? 0);
        $action = trim($_POST['action'] ?? ''); // 'accept' or 'decline'

        if ($bookingId <= 0 || !in_array($action, ['accept', 'decline'], true)) {
            $this->jsonResponse(['error' => 'Invalid request'], 400);
        }

        // GATE: Verify payment before allowing response
        if (!$this->bookingModel->isPaymentVerified($bookingId)) {
            $this->jsonResponse(['error' => 'Booking payment has not been verified yet. Supplier cannot respond.'], 403);
            return;
        }

        $suppliers = $this->bookingModel->getBookingSuppliers($bookingId);
        $rowId = 0;
        foreach ($suppliers as $s) {
            if ((int)$s['supplier_id'] === $supplierId) {
                $rowId = (int)$s['id'];
                break;
            }
        }

        if ($rowId <= 0) {
            $this->jsonResponse(['error' => 'Not associated with this booking'], 403);
        }

        $newStatus = $action === 'accept' ? 'confirmed' : 'rejected';
        $itemStatus = $action === 'accept' ? 'accepted' : 'cancelled';

        if (!$this->bookingModel->updateSupplierStatus($rowId, $newStatus)) {
            $this->jsonResponse(['error' => 'Could not update status'], 500);
        }

        // Update the booking items for this supplier
        $this->bookingModel->updateBookingItemsStatusBySupplier($bookingId, $supplierId, $itemStatus);

        // Log
        $this->bookingModel->logStatusChange($bookingId, null, 'supplier_' . $newStatus, null, 'Supplier ' . $action . 'ed booking');

        // Find supplier name for notifications
        $shopName = '';
        foreach ($suppliers as $s) {
            if ((int)$s['supplier_id'] === $supplierId) {
                $shopName = $s['shop_name'] ?? 'A supplier';
                break;
            }
        }

        // NOTIFY CUSTOMER
        if ($action === 'accept') {
            $this->notificationModel->notifyBookingCustomer(
                $bookingId,
                'Booking Accepted',
                $shopName . ' has accepted your booking! Your service is confirmed.',
                'booking'
            );
        } elseif ($action === 'decline') {
            $this->notificationModel->notifyBookingCustomer(
                $bookingId,
                'Booking Declined',
                $shopName . ' has declined your booking. You may need to find an alternative service.',
                'booking'
            );
        }

        $this->jsonResponse([
            'success' => true,
            'new_status' => $newStatus,
            'message' => $action === 'accept' ? 'Booking accepted!' : 'Booking declined.',
        ]);
    }

    /**
     * Supplier propose reschedule (AJAX POST).
     */
    public function supplierProposeReschedule(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Method not allowed'], 405);
        }

        $supplierId = $this->currentSupplierId();
        if ($supplierId <= 0) {
            $this->jsonResponse(['error' => 'Unauthorized'], 401);
        }

        $bookingId = (int)($_POST['booking_id'] ?? 0);
        $proposedDate = trim($_POST['proposed_date'] ?? '');
        $proposedStartTime = trim($_POST['proposed_start_time'] ?? '');
        $proposedEndTime = trim($_POST['proposed_end_time'] ?? '');
        $reason = trim($_POST['reason'] ?? '');

        if ($bookingId <= 0 || !$proposedDate || !$proposedStartTime || !$proposedEndTime) {
            $this->jsonResponse(['error' => 'Please provide proposed date and time'], 400);
        }

        // Validate date is in the future
        $proposed = DateTimeImmutable::createFromFormat('!Y-m-d', $proposedDate);
        $today = new DateTimeImmutable('today');
        if (!$proposed || $proposed < $today) {
            $this->jsonResponse(['error' => 'Proposed date must be in the future'], 400);
        }

        // Check supplier is associated with booking
        $suppliers = $this->bookingModel->getBookingSuppliers($bookingId);
        $isAssociated = false;
        foreach ($suppliers as $s) {
            if ((int)$s['supplier_id'] === $supplierId) {
                $isAssociated = true;
                break;
            }
        }

        if (!$isAssociated) {
            $this->jsonResponse(['error' => 'Not associated with this booking'], 403);
        }

        // Store reschedule proposal
        $proposalNote = sprintf(
            "Supplier proposed reschedule to %s from %s to %s. Reason: %s",
            $proposedDate,
            $proposedStartTime,
            $proposedEndTime,
            $reason ?: 'No reason provided'
        );

        if (!$this->bookingModel->logStatusChange($bookingId, null, 'reschedule_proposed', null, $proposalNote)) {
            $this->jsonResponse(['error' => 'Could not submit reschedule proposal'], 500);
        }

        // Notify customer
        $this->notificationModel->notifyBookingCustomer(
            $bookingId,
            'Reschedule Proposed',
            sprintf(
                'A supplier has proposed a new schedule: %s from %s to %s. Reason: %s. Please review from your booking detail page.',
                $proposedDate,
                $proposedStartTime,
                $proposedEndTime,
                $reason ?: 'No reason provided'
            ),
            'booking'
        );

        $this->jsonResponse([
            'success' => true,
            'message' => 'Reschedule proposal sent to customer. They will review and confirm shortly.',
        ]);
    }

    /* ─── Admin Booking Views ─────────────────────────────────── */

    /**
     * Admin booking management page.
     */
    public function adminBookings(): void
    {
        $filter = trim($_GET['status'] ?? 'all');
        $search = trim($_GET['search'] ?? '');
        $bookings = $this->bookingModel->getAllBookings($filter, $search);
        $stats = $this->bookingModel->getAdminStats();

        $enriched = [];
        foreach ($bookings as $b) {
            $b['booking_ref'] = $this->bookingModel->generateBookingRef((int)$b['id']);
            $b['total_amount'] = (float)$b['total_amount'];
            $b['paid_amount'] = (float)$b['paid_amount'];
            $enriched[] = $b;
        }

        $this->view('admin/bookings', [
            'bookings' => $enriched,
            'stats' => $stats,
            'activeFilter' => $filter,
            'search' => $search,
        ]);
    }

    /**
     * Admin booking detail.
     */
    public function adminBookingDetail(int $bookingId): void
    {
        $booking = $this->bookingModel->getBookingById($bookingId);
        if (!$booking) {
            redirect('admin/bookings');
            return;
        }

        $items = $this->bookingModel->getBookingItems($bookingId);
        $suppliers = $this->bookingModel->getBookingSuppliers($bookingId);
        $eventDetails = $this->bookingModel->getEventDetails($bookingId);
        $logs = $this->bookingModel->getStatusLogs($bookingId);
        $payments = $this->bookingModel->getBookingPayments($bookingId);
        $vouchers = $this->bookingModel->getBookingVouchers($bookingId);
        $bookingRef = $this->bookingModel->generateBookingRef($bookingId);

        $this->view('admin/bookingDetail', [
            'booking' => $booking,
            'items' => $items,
            'suppliers' => $suppliers,
            'eventDetails' => $eventDetails,
            'logs' => $logs,
            'payments' => $payments,
            'vouchers' => $vouchers,
            'bookingRef' => $bookingRef,
            'depositPercent' => self::DEPOSIT_PERCENT,
        ]);
    }

    /**
     * Admin cancel booking (AJAX POST).
     */
    public function adminCancelBooking(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Method not allowed'], 405);
        }

        $adminId = (int)($_SESSION['session_uid'] ?? 0);
        if ($adminId <= 0) {
            $this->jsonResponse(['error' => 'Unauthorized'], 401);
        }

        $bookingId = (int)($_POST['booking_id'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');
        $refundDeposit = !empty($_POST['refund_deposit']);

        if ($bookingId <= 0 || $reason === '') {
            $this->jsonResponse(['error' => 'Please provide a reason.'], 400);
        }

        if (!$this->bookingModel->adminCancelBooking($bookingId, $reason, $adminId, $refundDeposit)) {
            $this->jsonResponse(['error' => 'Could not cancel booking.'], 500);
        }

        // Notify customer
        $this->notificationModel->notifyBookingCustomer(
            $bookingId,
            'Booking Cancelled by Admin',
            'Your booking has been cancelled by the administrator. Reason: ' . $reason . ($refundDeposit ? ' Your deposit will be refunded.' : ''),
            'booking'
        );

        // Notify suppliers
        $this->notificationModel->notifyBookingSuppliers(
            $bookingId,
            'Booking Cancelled',
            'A booking has been cancelled by the administrator. Reason: ' . $reason,
            'booking'
        );

        $this->jsonResponse([
            'success' => true,
            'message' => 'Booking cancelled successfully.',
        ]);
    }

    /* ─── Payment Submission (Manual & Instant Methods) ──────────────── */

    /**
     * Submit payment slip for manual verification (KBZ Pay / AYA Bank).
     * Handles file upload and creates pending payment record.
     */
    public function submitPaymentSlip(): void
    {
        $this->ensureAuthenticated();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Method not allowed'], 405);
        }

        $bookingId = (int)($_POST['booking_id'] ?? 0);
        $paymentMethod = trim($_POST['payment_method'] ?? '');
        $reference = trim($_POST['reference'] ?? '');
        $slipFile = $_FILES['slip_image'] ?? null;

        if ($bookingId <= 0 || !in_array($paymentMethod, ['KBZ Pay', 'AYA Bank'], true) || $reference === '') {
            $this->jsonResponse(['error' => 'Invalid payment data'], 400);
            return;
        }

        $booking = $this->bookingModel->getBookingById($bookingId);
        if (!$booking || (int)$booking['user_id'] !== $this->userId) {
            $this->jsonResponse(['error' => 'Booking not found'], 404);
            return;
        }

        if ($booking['status'] !== 'pending_payment') {
            $this->jsonResponse(['error' => 'This booking is not awaiting payment.'], 400);
            return;
        }

        // Upload slip image
        if (!$slipFile || $slipFile['error'] !== UPLOAD_ERR_OK) {
            $this->jsonResponse(['error' => 'Please upload a payment slip image.'], 400);
            return;
        }

        $uploadService = new UploadService();
        $slipPath = $uploadService->uploadPaymentSlip($slipFile, $bookingId);
        if (!$slipPath) {
            $this->jsonResponse(['error' => 'Failed to upload slip. Please try again.'], 500);
            return;
        }

        // Submit payment slip
        if (!$this->bookingModel->submitPaymentSlip($bookingId, $slipPath, $reference, $paymentMethod)) {
            $this->jsonResponse(['error' => 'Failed to submit payment. Please try again.'], 500);
            return;
        }

        // Update booking status to payment_submitted
        $this->bookingModel->logStatusChange($bookingId, 'pending_payment', 'payment_submitted', $this->userId);

        // Notify customer
        $this->notificationModel->notifyBookingCustomer(
            $bookingId,
            'Payment Submitted',
            'Your payment slip has been received. Admin will verify within 2 hours.',
            'payment'
        );

        $this->jsonResponse([
            'success' => true,
            'message' => 'Payment slip submitted successfully. Please wait for admin verification.',
        ]);
    }

    /* ─── Supplier Earnings & Payouts ─────────────────────────────── */

    /**
     * Supplier earnings dashboard.
     */
    public function supplierEarnings(): void
    {
        $this->ensureAuthenticated();

        $supplierId = $this->currentSupplierId();
        if ($supplierId <= 0) {
            redirect('supplier/dashboard');
            return;
        }

        // Get supplier earnings summary
        $earnings = $this->bookingModel->getSupplierEarnings($supplierId);

        // Get payout history with pagination
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 15;
        $offset = ($page - 1) * $perPage;
        $payouts = $this->bookingModel->getSupplierPayouts($supplierId, $perPage, $offset);

        // Count total payouts
        $this->db = new Database();
        $this->db->dbquery(
            "SELECT COUNT(*) as total FROM payments
             WHERE supplier_id = :sid AND type = 'payout'"
        );
        $this->db->dbbind(':sid', $supplierId, PDO::PARAM_INT);
        $countResult = $this->db->getsingledata();
        $totalPayouts = (int)($countResult['total'] ?? 0);
        $totalPages = ceil($totalPayouts / $perPage);

        // Get supplier info for bank account details
        $supplier = $this->supplierProfileModel->getById($supplierId);

        require_once APPROOT . '/controllers/SupplierControllerSupport.php';
        $this->view('supplier/earnings', [
            'earnings' => $earnings,
            'payouts' => $payouts,
            'supplier' => $supplier,
            'supplierId' => $supplierId,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalPayouts' => $totalPayouts,
        ]);
    }

    /**
     * Request payout to supplier bank account (AJAX POST).
     */
    public function requestPayoutPost(): void
    {
        $this->ensureAuthenticated();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Method not allowed'], 405);
            return;
        }

        $supplierId = $this->currentSupplierId();
        if ($supplierId <= 0) {
            $this->jsonResponse(['error' => 'Unauthorized'], 401);
            return;
        }

        $bankAccount = trim($_POST['bank_account'] ?? '');
        $bankCode = trim($_POST['bank_code'] ?? '');
        $amount = (float)($_POST['amount'] ?? 0);

        if ($bankAccount === '' || $bankCode === '' || $amount <= 0) {
            $this->jsonResponse(['error' => 'Please provide bank details and amount'], 400);
            return;
        }

        // Validate bank code is supported
        $supportedBanks = ['AYA', 'KBZ', 'AGD', 'CBD', 'MYBANK'];
        if (!in_array($bankCode, $supportedBanks, true)) {
            $this->jsonResponse(['error' => 'Bank not supported'], 400);
            return;
        }

        // Get pending payouts amount
        $this->db = new Database();
        $this->db->dbquery(
            "SELECT COALESCE(SUM(amount), 0) as pending_amount FROM payments
             WHERE supplier_id = :sid AND type = 'payout' AND status = 'pending'"
        );
        $this->db->dbbind(':sid', $supplierId, PDO::PARAM_INT);
        $result = $this->db->getsingledata();
        $pendingAmount = (float)($result['pending_amount'] ?? 0);

        if ($amount > $pendingAmount) {
            $this->jsonResponse(['error' => 'Requested amount exceeds pending payouts'], 400);
            return;
        }

        // Create payout request (mark payments as processing)
        $this->db->dbquery(
            "UPDATE payments SET status = 'processing'
             WHERE supplier_id = :sid AND type = 'payout' AND status = 'pending'
             LIMIT :limit"
        );
        $this->db->dbbind(':sid', $supplierId, PDO::PARAM_INT);
        $this->db->dbbind(':limit', (int)ceil($amount / 1000), PDO::PARAM_INT); // Approximate count

        if (!$this->db->dbexecute()) {
            $this->jsonResponse(['error' => 'Failed to create payout request'], 500);
            return;
        }

        // TODO: Integrate with payment gateway for actual disbursement
        // $gatewayService = new PaymentGatewayService();
        // $result = $gatewayService->createSupplierPayout($supplierId, $amount, $bankAccount, $bankCode);

        $this->jsonResponse([
            'success' => true,
            'message' => 'Payout request submitted. You will receive funds within 1-2 business days.',
            'payout_id' => uniqid('PAYOUT_'),
        ]);
    }

    /**
     * Confirm instant payment from gateway (MM QR / Visa Card).
     * Creates success payment record and moves booking to paid.
     */
    public function confirmInstantPayment(): void
    {
        $this->ensureAuthenticated();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Method not allowed'], 405);
        }

        $bookingId = (int)($_POST['booking_id'] ?? 0);
        $method = trim($_POST['method'] ?? '');
        $transactionId = trim($_POST['transaction_id'] ?? '');
        $amount = (float)($_POST['amount'] ?? 0);

        if ($bookingId <= 0 || !in_array($method, ['MM QR', 'Visa', 'Mastercard'], true) || $transactionId === '' || $amount <= 0) {
            $this->jsonResponse(['error' => 'Invalid payment data'], 400);
            return;
        }

        $booking = $this->bookingModel->getBookingById($bookingId);
        if (!$booking || (int)$booking['user_id'] !== $this->userId) {
            $this->jsonResponse(['error' => 'Booking not found'], 404);
            return;
        }

        if ($booking['status'] !== 'pending_payment') {
            $this->jsonResponse(['error' => 'This booking is not awaiting payment.'], 400);
            return;
        }

        // Verify amount matches expected deposit
        $expectedDeposit = (float)$booking['total_amount'] * (self::DEPOSIT_PERCENT / 100);
        if (abs($amount - $expectedDeposit) > 0.01) { // Allow 1 cent tolerance
            $this->jsonResponse(['error' => 'Payment amount mismatch.'], 400);
            return;
        }

        // Confirm instant payment.
        if (!$this->bookingModel->confirmInstantPayment($bookingId, $method, $transactionId, $amount)) {
            $this->jsonResponse(['error' => 'Failed to confirm payment.'], 500);
            return;
        }

        $this->bookingModel->logStatusChange($bookingId, 'pending_payment', 'paid', $this->userId);

        // Notify customer
        $this->notificationModel->notifyBookingCustomer(
            $bookingId,
            'Payment Confirmed',
            'Your payment has been confirmed! Suppliers will now review your booking.',
            'payment'
        );

        // Notify suppliers
        $this->notificationModel->notifyBookingSuppliers(
            $bookingId,
            'New Booking — Payment Verified',
            'A new booking with confirmed payment is ready for your review.',
            'booking'
        );

        $this->jsonResponse([
            'success' => true,
            'message' => 'Payment confirmed! Suppliers will review your booking shortly.',
        ]);
    }
}
