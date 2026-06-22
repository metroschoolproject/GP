<?php

require_once APPROOT . '/traits/JsonResponseTrait.php';
require_once APPROOT . '/services/EmailService.php';
require_once APPROOT . '/services/PaymentGatewayService.php';
require_once APPROOT . '/services/PayoutService.php';

class Booking extends Controller
{
    use JsonResponseTrait;

    private BookingModel $bookingModel;
    private CartModel $cartModel;
    private SupplierProfile $supplierProfileModel;
    private Notification $notificationModel;
    private ?int $userId;
    /**
     * A supplier may self-decline a confirmed package booking only while the
     * event is at least this many days away — enough lead time for the admin to
     * find a replacement. Inside the window they must contact admin instead.
     */
    private const PACKAGE_DECLINE_CUTOFF_DAYS = 7;

    public function __construct()
    {
        $this->bookingModel = $this->model('BookingModel');
        $this->cartModel = $this->model('CartModel');
        $this->supplierProfileModel = $this->model('SupplierProfile');
        $this->notificationModel = $this->model('Notification');
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

    private function buildPackageSchedules(array $items, array $eventDetails): array
    {
        $packageSchedules = [];
        $bookingId = (int)($items[0]['booking_id'] ?? 0);
        $activePackageLines = [];
        if ($bookingId > 0) {
            foreach ($this->bookingModel->getBookingSuppliers($bookingId) as $supplierLine) {
                $packageItemId = (int)($supplierLine['package_item_id'] ?? 0);
                if (
                    $packageItemId > 0
                    && !in_array(
                        (string)($supplierLine['status'] ?? ''),
                        ['replaced', 'rejected', 'cancelled'],
                        true
                    )
                ) {
                    $activePackageLines[$packageItemId] = $supplierLine;
                }
            }
        }

        foreach ($items as $item) {
            if (($item['item_type'] ?? '') !== 'package') {
                continue;
            }

            $event = null;
            foreach ($eventDetails as $detail) {
                if ((int)($detail['booking_item_id'] ?? 0) === (int)($item['id'] ?? 0)) {
                    $event = $detail;
                    break;
                }
            }

            $event ??= $eventDetails[0] ?? null;
            $eventDate = trim((string)($event['event_date'] ?? ''));
            if ($eventDate === '') {
                continue;
            }

            $schedule = $this->cartModel->getPackageEventSchedule(
                (int)($item['item_id'] ?? 0),
                $eventDate
            );
            foreach ($schedule as &$serviceEvent) {
                $packageItemId = (int)($serviceEvent['package_item_id'] ?? 0);
                $liveLine = $activePackageLines[$packageItemId] ?? null;
                if (!$liveLine) {
                    continue;
                }
                $serviceEvent['service_id'] = (int)($liveLine['service_id'] ?? 0);
                $serviceEvent['service_name'] = $liveLine['service_name'] ?? $serviceEvent['service_name'];
                $serviceEvent['supplier_id'] = (int)($liveLine['supplier_id'] ?? 0);
                $serviceEvent['supplier_name'] = $liveLine['shop_name'] ?? $serviceEvent['supplier_name'];
                $serviceEvent['category_id'] = (int)($liveLine['category_id'] ?? 0);
                $serviceEvent['category_name'] = $liveLine['category_name'] ?? $serviceEvent['category_name'];
                $serviceEvent['item_price'] = (float)($liveLine['item_price'] ?? 0);
                $serviceEvent['supplier_status'] = $liveLine['status'] ?? 'pending';
                $serviceEvent['is_replacement'] = !empty($liveLine['replacement_request_id']);
            }
            unset($serviceEvent);
            $packageSchedules[(int)($item['id'] ?? 0)] = $schedule;
        }

        return $packageSchedules;
    }

    private function notifyAdminsOfDepositSubmission(int $bookingId, float $amount = 0): void
    {
        $bookingRef = $this->bookingModel->generateBookingRef($bookingId);
        $amountText = $amount > 0 ? ' for ' . $this->money($amount) : '';

        $this->notificationModel->notifyAdmins(
            'Deposit Proof Submitted',
            'A customer submitted deposit payment proof' . $amountText . ' for booking ' . $bookingRef . '. Please verify it.',
            'payment',
            'booking',
            $bookingId
        );
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
            'depositPercent' => BOOKING_DEPOSIT_PERCENT,
            'venueService' => $venueService, // Pass to view
        ]);
    }

    public function createPost(): void
    {
        $this->ensureAuthenticated();
        $this->requireCsrf();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Method not allowed'], 405);
        }

        $transactionStarted = false;
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
        // Track which itemsData entries are add-ons (inherit parent's event_detail)
        $isAddonItem = [];

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
            $isAddon = !empty($item['package_cart_item_id']);

            // For fullday items (packages and fullday services), time slots are not required.
            // Three-layer resolution: resolved_start_time (from cart) → CATEGORY_DEFAULT_TIMES → 00:00/23:59
            $isFullday = ($item['booking_type'] ?? 'fullday') === 'fullday';
            if (($item['item_type'] ?? '') === 'package' && $itemDate !== '') {
                $packageSchedule = $this->cartModel->getPackageEventSchedule(
                    (int)($item['item_id'] ?? 0),
                    $itemDate
                );
                if (!empty($packageSchedule)) {
                    $starts = array_column($packageSchedule, 'start_time');
                    $ends = array_column($packageSchedule, 'end_time');
                    sort($starts);
                    rsort($ends);
                    $itemStartTime = (string)($starts[0] ?? $itemStartTime);
                    $itemEndTime = (string)($ends[0] ?? $itemEndTime);
                }
            }
            if ($isFullday && empty($itemStartTime)) {
                $categoryId = (int)($item['category_id'] ?? 0);
                $categoryTimes = defined('CATEGORY_DEFAULT_TIMES') ? (CATEGORY_DEFAULT_TIMES[$categoryId] ?? null) : null;
                $itemStartTime = $item['resolved_start_time'] ?? ($categoryTimes['start'] ?? '00:00:00');
                $itemEndTime   = $item['resolved_end_time']   ?? ($categoryTimes['end']   ?? '23:59:59');
            }

            // Add-on items inherit event details from their parent package.
            // Skip independent validation — only validate for standalone items.
            if (!$isAddon) {
                if (empty($itemDate)) {
                    $currentItemErrors[] = 'Date is required';
                } elseif (!$this->isDateAllowedByLeadTime($itemDate, $minLeadDays)) {
                    $currentItemErrors[] = $this->leadTimeMessage($minLeadDays);
                }
                if (!$isFullday && empty($itemStartTime)) {
                    $currentItemErrors[] = 'Time slot is required';
                }
                if (!$isFullday && empty($itemEndTime)) {
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
            }

            if (!empty($currentItemErrors)) {
                $itemErrors[] = $itemName . ': ' . implode(', ', $currentItemErrors);
                continue;
            }

            $basePrice = (float)($item['cart_price'] ?? $item['price_min'] ?? $item['price_max'] ?? 0);
            $isGuestPriced = $this->isGuestPricedService($item);
            $itemPrice = $isGuestPriced ? $basePrice * $itemGuests : $basePrice;

            // Collect per-item details (with fallback to shared defaults).
            // Add-on items do NOT get their own event_details row — they inherit
            // from the parent package at display time.
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
            $isAddonItem[] = $isAddon;
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

        // PRE-FLIGHT: gather every package service with no slot left on its
        // chosen date so the customer sees all conflicts at once, before we
        // open a transaction. Advisory only — reserveServiceSlot() is still
        // the authoritative race guard inside the transaction below.
        $unavailable = [];
        $packageServices = []; // full service list per package for UX
        foreach ($items as $i => $item) {
            if (($item['item_type'] ?? '') !== 'package') {
                continue;
            }
            if (!empty($item['package_cart_item_id'])) {
                continue; // add-ons inherit the parent package's schedule
            }
            $pkgDate = trim($_POST['item_date'][$i] ?? '') ?: trim((string)($item['selected_date'] ?? ''));
            if ($pkgDate === '') {
                continue;
            }
            $pkgId = (int)($item['item_id'] ?? 0);
            // Full schedule for all services in this package
            $schedule = $this->cartModel->getPackageEventSchedule($pkgId, $pkgDate);
            $services = [];
            foreach ($schedule as $s) {
                $isAvailable = (bool)($s['is_available'] ?? false);
                $srv = [
                    'service_id' => (int)($s['service_id'] ?? 0),
                    'service_name' => (string)($s['service_name'] ?? 'Service'),
                    'is_available' => $isAvailable,
                    'booking_type' => (string)($s['booking_type'] ?? ''),
                ];
                if (!$isAvailable) {
                    $srv['message'] = $s['availability_message'] ?? 'No time slots available on this date';
                    $srv['alternatives'] = $this->cartModel->findAlternativePackageDates(
                        $pkgId,
                        (int)$s['service_id'],
                        $pkgDate
                    );
                }
                $services[] = $srv;
            }
            $packageServices[] = [
                'package_id' => $pkgId,
                'package_name' => (string)($item['name'] ?? $item['service_name'] ?? 'Package'),
                'date' => $pkgDate,
                'services' => $services,
            ];

            foreach ($this->cartModel->getUnavailablePackageServices($pkgId, $pkgDate) as $u) {
                $u['alternatives'] = $this->cartModel->findAlternativePackageDates(
                    $pkgId,
                    (int)$u['service_id'],
                    $pkgDate
                );
                $unavailable[] = $u;
            }
        }
        if (!empty($unavailable)) {
            // Find dates where ALL package services are available simultaneously
            $allAvailableDates = [];
            foreach ($packageServices as $ps) {
                $pkgId = (int)($ps['package_id'] ?? 0);
                $pkgDate = $ps['date'] ?? '';
                if ($pkgId > 0 && $pkgDate !== '') {
                    $allAvailableDates[$pkgId] = $this->cartModel->findAlternativePackageDatesAllAvailable(
                        $pkgId,
                        $pkgDate,
                        5,
                        90
                    );
                }
            }
            $this->jsonResponse([
                'error'               => "Some package services aren't available on your selected date.",
                'unavailable'         => $unavailable,
                'packageServices'     => $packageServices,
                'allAvailableDates'   => $allAvailableDates,
            ], 422);
        }

        $this->bookingModel->beginTransaction();
        $transactionStarted = true;

        // CREATE BOOKING
        $cartId = $this->cartModel->getOrCreateCart($this->userId);
        $bookingId = $this->bookingModel->createDraftFromCart($this->userId, $cartId, $adjustedTotal);
        
        if (!$bookingId) {
            throw new RuntimeException('Could not create booking');
        }
        
        // INSERT BOOKING ITEMS (and get back IDs)
        $bookingItemIds = $this->bookingModel->insertBookingItems($bookingId, $this->userId, $itemPrices);
        if (!$bookingItemIds) {
            throw new RuntimeException('Could not save booking items');
        }
        if (!$this->bookingModel->reserveBookingItemSlots($bookingId)) {
            throw new RuntimeException('One of the selected service slots is no longer available.');
        }
        
        // INSERT EVENT DETAILS — only for non-addon items.
        // Add-ons inherit event details from their parent package.
        $nonAddonItemsData = [];
        $nonAddonBookingItemIds = [];
        foreach ($itemsData as $idx => $data) {
            if (empty($isAddonItem[$idx])) {
                $nonAddonItemsData[] = $data;
                $nonAddonBookingItemIds[] = $bookingItemIds[$idx] ?? null;
            }
        }
        if (!empty($nonAddonItemsData)) {
            if (!$this->bookingModel->insertEventDetails($bookingId, $nonAddonItemsData, $nonAddonBookingItemIds)) {
                throw new RuntimeException('Could not save event details');
            }
        }

        // RESERVE PER-SERVICE TIME SLOTS for every service inside each package.
        // Slot-type services get dedicated service_time_slot rows so their
        // calendar availability is tracked independently.
        foreach ($items as $i => $item) {
            if (($item['item_type'] ?? '') !== 'package') {
                continue;
            }
            if (!empty($item['package_cart_item_id'])) {
                continue;
            }
            $pkgDate = trim($_POST['item_date'][$i] ?? '') ?: trim((string)($item['selected_date'] ?? ''));
            if ($pkgDate !== '') {
                $packageSchedule = $this->cartModel->getPackageEventSchedule(
                    (int)($item['item_id'] ?? 0),
                    $pkgDate
                );
                if (!empty($packageSchedule)) {
                    if ($this->bookingModel->reservePackageServiceSlots($bookingId, $pkgDate, $packageSchedule) === false) {
                        $fail = $this->bookingModel->getLastUnavailableService();
                        if (!$fail) {
                            // Not an availability conflict (e.g. the slot-reservation
                            // write failed) — route to the generic 500 handler rather
                            // than emit a 422 with an empty unavailable list.
                            throw new RuntimeException('Could not reserve package service slots.');
                        }
                        $fail['alternatives'] = $this->cartModel->findAlternativePackageDates(
                            (int)($item['item_id'] ?? 0),
                            (int)$fail['service_id'],
                            $pkgDate
                        );
                        throw new SlotUnavailableException([$fail]);
                    }
                }
            }
        }

        // LINK SUPPLIERS
        if (!$this->bookingModel->insertBookingSuppliers($bookingId)) {
            throw new RuntimeException('Could not assign suppliers');
        }
        
        // CLEAR CART & LOG
        $this->bookingModel->clearCart($this->userId);
        $this->bookingModel->logStatusChange($bookingId, null, 'draft', $this->userId);

        $customerName = $_SESSION['session_name'] ?? 'A customer';
        $itemList = array_map(fn($item) => $item['service_name'] ?? 'a service', $items);
        $serviceNames = implode(', ', $itemList);

        // FORK: package bookings go straight to payment; custom/mixed require supplier approval first
        if ($this->bookingModel->isPackageBooking($bookingId)) {
            $this->bookingModel->updateStatus($bookingId, 'pending_payment');
            $this->bookingModel->logStatusChange($bookingId, 'draft', 'pending_payment', $this->userId);
            $this->bookingModel->commit();
            $transactionStarted = false;

            // Send booking confirmation email to customer
            $userData = $this->getUserData();
            if (!empty($userData['email'])) {
                $emailService = new EmailService();
                $emailService->sendBookingConfirmation(
                    ['name' => $userData['name'], 'email' => $userData['email']],
                    ['id' => $bookingId, 'total_amount' => $adjustedTotal ?: $total],
                    $items
                );
            }

            $this->jsonResponse([
                'success' => true,
                'booking_id' => $bookingId,
                'redirect' => URLROOT . '/booking/pay/' . $bookingId,
            ]);
        } else {
            $this->bookingModel->updateStatus($bookingId, 'pending_supplier_response');
            $this->bookingModel->logStatusChange($bookingId, 'draft', 'pending_supplier_response', $this->userId);
            $this->bookingModel->setSupplierResponseDeadline($bookingId, '+48 hours');
            $this->bookingModel->commit();
            $transactionStarted = false;

            $this->notificationModel->notifyBookingSuppliers(
                $bookingId,
                'New Booking Request',
                $customerName . ' is requesting: ' . $serviceNames . '. Please accept or decline within 48 hours.',
                'booking'
            );
            $this->notificationModel->notifyAdmins(
                'New Custom Booking Request',
                $customerName . ' created a custom or mixed booking for: ' . $serviceNames . '. Supplier responses are pending.',
                'booking',
                'booking',
                $bookingId
            );

            // Email each supplier about the new booking request
            $supplierEmails = $this->bookingModel->getSupplierEmailsForBooking($bookingId);
            if (!empty($supplierEmails)) {
                $emailService = new EmailService();
                foreach ($supplierEmails as $supplier) {
                    $emailService->sendNewBookingRequest($supplier, $customerName, $items, $bookingId);
                }
            }

            $this->jsonResponse([
                'success' => true,
                'booking_id' => $bookingId,
                'redirect' => URLROOT . '/booking/detail/' . $bookingId,
            ]);
        }
        } catch (SlotUnavailableException $e) {
            if ($transactionStarted) {
                $this->bookingModel->rollBack();
            }
            $this->jsonResponse([
                'error'       => "Some package services aren't available on your selected date.",
                'unavailable' => $e->services,
            ], 422);
        } catch (Throwable $e) {
            if ($transactionStarted) {
                $this->bookingModel->rollBack();
            }
            error_log('Booking creation failed: ' . $e->getMessage());
            $this->jsonResponse([
                'error' => 'Booking could not be created. Please review availability and try again.',
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
        $deposit = $total * (BOOKING_DEPOSIT_PERCENT / 100);
        $feePercent = get_platform_fee_percent();
        $platformFee = round($total * ($feePercent / 100), 2);
        $depositWithFee = round($deposit + $platformFee, 2);

        // Update booking to pending_payment
        if ($booking['status'] === 'draft') {
            $this->bookingModel->updateStatus($bookingId, 'pending_payment');
            $this->bookingModel->logStatusChange($bookingId, 'draft', 'pending_payment', $this->userId);
        }

        $this->view('booking/paymentMethods', [
            'booking' => $booking,
            'items' => $items,
            'total' => $total,
            'deposit' => $deposit,
            'depositPercent' => BOOKING_DEPOSIT_PERCENT,
            'balance' => $total - $deposit,
            'bookingRef' => $this->bookingModel->generateBookingRef($bookingId),
            'platformFee' => $platformFee,
            'platformFeePercent' => $feePercent,
            'depositWithFee' => $depositWithFee,
        ]);
    }

    /**
     * Handle manual bank transfer form submission (POST).
     */
    public function submitManualPayment(): void
    {
        $this->ensureAuthenticated();
        $this->requireCsrf(false);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('booking/myBookings');
            return;
        }

        $bookingId    = (int)($_POST['booking_id'] ?? 0);
        $bankName     = trim($_POST['bank_name'] ?? '');
        $accountName  = trim($_POST['account_name'] ?? '');
        $transactionRef = trim($_POST['transaction_ref'] ?? '');
        $paidAmount   = (float)str_replace(',', '', $_POST['paid_amount'] ?? '0');
        $paidAt       = date('Y-m-d H:i:s'); // auto-set to now (field removed from UI)
        $mobileNumber = trim($_POST['mobile_number'] ?? '');

        $allowed = ['KBZ Pay', 'Wave Money', 'AYA Pay', 'Yoma Bank', 'CB Bank', 'Visa / MasterCard'];

        if (
            $bookingId <= 0
            || !in_array($bankName, $allowed, true)
            || $accountName === ''
            || $transactionRef === ''
            || $paidAmount <= 0
            || $mobileNumber === ''
        ) {
            $_SESSION['booking_payment_flash'] = 'Please fill in all required fields.';
            redirect('booking/pay/' . $bookingId);
            return;
        }

        $booking = $this->bookingModel->getBookingById($bookingId);
        if (!$booking || (int)$booking['user_id'] !== $this->userId) {
            redirect('booking/myBookings');
            return;
        }

        if (!in_array($booking['status'] ?? '', ['draft', 'pending_payment'], true)) {
            redirect('booking/detail/' . $bookingId);
            return;
        }

        $expectedDeposit = round((float)$booking['total_amount'] * (BOOKING_DEPOSIT_PERCENT / 100), 2);
        $platformFee = round((float)$booking['total_amount'] * (get_platform_fee_percent() / 100), 2);
        $totalDue = round($expectedDeposit + $platformFee, 2);
        if (abs($paidAmount - $totalDue) > 0.01) {
            $_SESSION['booking_payment_flash'] = 'The payment amount must include the deposit (' . number_format($expectedDeposit, 0) . ' MMK) + platform fee (' . number_format($platformFee, 0) . ' MMK).';
            redirect('booking/pay/' . $bookingId);
            return;
        }

        $slipFile = $_FILES['slip_image'] ?? null;
        if (!$slipFile || ($slipFile['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            $_SESSION['booking_payment_flash'] = 'Please upload your payment slip or receipt.';
            redirect('booking/pay/' . $bookingId);
            return;
        }

        if (($slipFile['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            $_SESSION['booking_payment_flash'] = $this->paymentSlipUploadErrorMessage((int)$slipFile['error']);
            redirect('booking/pay/' . $bookingId);
            return;
        }

        if (($slipFile['size'] ?? 0) > 10 * 1024 * 1024) {
            $_SESSION['booking_payment_flash'] = 'Your file is too large (' . number_format($slipFile['size'] / 1024 / 1024, 1) . ' MB). Please upload a file under 10MB.';
            redirect('booking/pay/' . $bookingId);
            return;
        }

        $slipPath = $this->storePaymentSlip($slipFile);
        if ($slipPath === '') {
            $_SESSION['booking_payment_flash'] = 'Invalid file type. Please upload a JPG, PNG, WebP, or PDF file.';
            redirect('booking/pay/' . $bookingId);
            return;
        }

        $ok = $this->bookingModel->submitPaymentSlip(
            $bookingId,
            $slipPath,
            $transactionRef,
            $bankName,
            $accountName,
            $mobileNumber,
            $paidAmount,
            $paidAt,
            $platformFee,
            $expectedDeposit
        );

        if (!$ok) {
            $_SESSION['booking_payment_flash'] = 'Could not save your payment proof. Please try again.';
            redirect('booking/pay/' . $bookingId);
            return;
        }

        $this->notificationModel->notifyBookingCustomer(
            $bookingId,
            'Payment Proof Submitted',
            'Your bank transfer details have been received. Our team will verify and confirm shortly.',
            'payment'
        );

        $this->notifyAdminsOfDepositSubmission($bookingId, $paidAmount);

        $_SESSION['booking_payment_flash'] = 'Your payment proof has been submitted. We will verify and confirm your booking shortly.';
        redirect('booking/detail/' . $bookingId);
    }

    /**
     * Customer-facing page to approve + pay the price difference for a pricier
     * supplier replacement proposed by admin.
     */
    public function payReplacementDelta($replacementId = null): void
    {
        $this->ensureAuthenticated();
        $replacementId = (int)$replacementId;

        $r = $this->bookingModel->getReplacementForCustomer($replacementId);
        if (!$r || (int)$r['user_id'] !== $this->userId) {
            redirect('booking/myBookings');
            return;
        }
        if (($r['status'] ?? '') !== 'pending_customer') {
            // Already handled (paid/verified/expired) — send them to the booking.
            redirect('booking/detail/' . (int)$r['booking_id']);
            return;
        }
        if (!empty($r['customer_approved_at']) || !empty($r['delta_payment_slip'])) {
            redirect('booking/detail/' . (int)$r['booking_id']);
            return;
        }

        $this->view('booking/replacementDelta', [
            'replacement' => $r,
            'delta'       => (float)($r['price_delta'] ?? 0),
            'bookingRef'  => $this->bookingModel->generateBookingRef((int)$r['booking_id']),
            'flash'       => $_SESSION['booking_payment_flash'] ?? null,
        ]);
        unset($_SESSION['booking_payment_flash']);
    }

    /** Customer submits bank-transfer proof for a replacement delta (POST). */
    public function submitReplacementDelta(): void
    {
        $this->ensureAuthenticated();
        $this->requireCsrf(false);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('booking/myBookings');
            return;
        }

        $replacementId  = (int)($_POST['replacement_id'] ?? 0);
        $bankName       = trim($_POST['bank_name'] ?? '');
        $accountName    = trim($_POST['account_name'] ?? '');
        $transactionRef = trim($_POST['transaction_ref'] ?? '');
        $paidAmount     = (float)str_replace(',', '', $_POST['paid_amount'] ?? '0');
        $mobileNumber   = trim($_POST['mobile_number'] ?? '');
        $allowed = ['KBZ Pay', 'Wave Money', 'AYA Pay', 'Yoma Bank', 'CB Bank', 'Visa / MasterCard'];

        $r = $this->bookingModel->getReplacementForCustomer($replacementId);
        if (!$r || (int)$r['user_id'] !== $this->userId) {
            redirect('booking/myBookings');
            return;
        }
        if (($r['status'] ?? '') !== 'pending_customer' || (int)($r['delta_payment_id'] ?? 0) <= 0) {
            redirect('booking/detail/' . (int)$r['booking_id']);
            return;
        }

        $expectedDelta = round((float)($r['price_delta'] ?? 0), 2);
        if (!in_array($bankName, $allowed, true) || $accountName === '' || $transactionRef === '' || $mobileNumber === ''
            || $expectedDelta <= 0 || abs($paidAmount - $expectedDelta) > 0.01) {
            $_SESSION['booking_payment_flash'] = 'Please fill in all required fields.';
            redirect('booking/payReplacementDelta/' . $replacementId);
            return;
        }

        $slipPath = '';
        if (!empty($_FILES['slip_image']['name']) && ($_FILES['slip_image']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $slipPath = $this->storePaymentSlip($_FILES['slip_image']);
        }
        if ($slipPath === '') {
            $_SESSION['booking_payment_flash'] = 'Please upload a valid payment proof.';
            redirect('booking/payReplacementDelta/' . $replacementId);
            return;
        }

        $ok = $this->bookingModel->recordReplacementDeltaSlip(
            (int)$r['delta_payment_id'], $slipPath, $bankName, $accountName,
            $mobileNumber, $transactionRef, $paidAmount
        );
        if (!$ok) {
            $_SESSION['booking_payment_flash'] = 'Could not save your payment proof. Please try again.';
            redirect('booking/payReplacementDelta/' . $replacementId);
            return;
        }

        // Mark the customer's approval; admin verification finalizes the swap.
        $this->bookingModel->updateReplacement($replacementId, ['customer_approved_at' => date('Y-m-d H:i:s')]);

        $this->notificationModel->notifyAdmins(
            'Replacement Delta Paid — Verify',
            'Customer paid the difference for booking #' . (int)$r['booking_id'] . '. Verify the payment to finalize the replacement.',
            'payment',
            'booking',
            (int)$r['booking_id']
        );
        $this->notificationModel->notifyBookingCustomer(
            (int)$r['booking_id'],
            'Replacement Payment Submitted',
            'Thanks! We received your payment proof for the replacement and will confirm shortly.',
            'payment'
        );

        $_SESSION['booking_payment_flash'] = 'Your payment proof has been submitted. We will confirm your replacement shortly.';
        redirect('booking/detail/' . (int)$r['booking_id']);
    }

    public function rejectReplacement($replacementId = null): void
    {
        $this->ensureAuthenticated();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('booking/myBookings');
        }
        $this->requireCsrf(false);
        $replacementId = (int)($replacementId ?: ($_POST['replacement_id'] ?? 0));
        $replacement = $this->bookingModel->rejectReplacementByCustomer($replacementId, (int)$this->userId);
        if (!$replacement) {
            $_SESSION['booking_payment_flash'] = 'This replacement proposal can no longer be rejected.';
            redirect('booking/myBookings');
        }
        $bookingId = (int)$replacement['booking_id'];
        $this->notificationModel->notifyAdmins(
            'Replacement Re-pick Needed',
            'The customer rejected the pricier replacement for booking #' . $bookingId . '. Please choose another option.',
            'booking',
            'booking',
            $bookingId
        );
        $this->notificationModel->notifyBookingCustomer(
            $bookingId,
            'Replacement Proposal Declined',
            'We will look for another replacement option. No payment was taken.',
            'booking'
        );
        redirect('booking/detail/' . $bookingId);
    }

    private function storePaymentSlip(array $file): string
    {
        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];

        // Detect MIME — fall back to extension-based check if finfo is unreliable
        $mimeType = mime_content_type($file['tmp_name']);
        if (!in_array($mimeType, $allowed, true)) {
            // Retry with file extension (some phones produce files finfo misidentifies)
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $extMap = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp', 'pdf' => 'application/pdf'];
            $mimeType = $extMap[$ext] ?? $mimeType;
        }

        if (!in_array($mimeType, $allowed, true)) {
            return '';
        }

        if ($file['size'] > 10 * 1024 * 1024) {
            return '';
        }

        $extMap = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'application/pdf' => 'pdf'];
        $ext = $extMap[$mimeType] ?? 'jpg';
        $relDir = 'uploads/payment-slips/' . date('Y/m');
        $absDir = dirname(APPROOT) . '/public/' . $relDir;

        if (!is_dir($absDir)) {
            mkdir($absDir, 0755, true);
        }

        $filename = 'slip-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $ext;

        if (move_uploaded_file($file['tmp_name'], $absDir . '/' . $filename)) {
            return 'public/' . $relDir . '/' . $filename;
        }

        return '';
    }

    private function paymentSlipUploadErrorMessage(int $errorCode): string
    {
        if (in_array($errorCode, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) {
            return 'Your file is too large. Please upload a file under 10MB.';
        }

        return 'Could not upload your payment slip. Please choose the file again and try once more.';
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

    public function getPackageSchedule(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Method not allowed'], 405);
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $packageId = (int)($input['package_id'] ?? 0);
        $date = trim((string)($input['date'] ?? ''));
        $selectedDate = DateTimeImmutable::createFromFormat('!Y-m-d', $date);

        if ($packageId <= 0 || !$selectedDate) {
            $this->jsonResponse(['error' => 'Invalid package or event date'], 400);
        }

        $schedule = $this->cartModel->getPackageEventSchedule($packageId, $date);
        if (empty($schedule)) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'No package services are available to schedule.',
            ], 404);
        }

        $requiredLeadDays = 0;
        foreach ($schedule as $service) {
            $requiredLeadDays = max($requiredLeadDays, (int)($service['min_lead_days'] ?? 0));
        }
        $earliestDate = (new DateTimeImmutable('today'))->add(new DateInterval('P' . $requiredLeadDays . 'D'));
        if ($selectedDate < $earliestDate) {
            $this->jsonResponse([
                'success' => false,
                'error' => 'This package requires ' . $requiredLeadDays . ' days advance notice. Earliest available: ' . $earliestDate->format('M j, Y'),
                'earliest_date' => $earliestDate->format('Y-m-d'),
            ], 422);
        }

        $starts = array_column($schedule, 'start_time');
        $ends = array_column($schedule, 'end_time');
        sort($starts);
        rsort($ends);

        $this->jsonResponse([
            'success' => true,
            'schedule' => $schedule,
            'start_time' => $starts[0] ?? null,
            'end_time' => $ends[0] ?? null,
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
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 12;
        $offset = ($page - 1) * $perPage;

        $bookings = $this->bookingModel->getCustomerBookings($this->userId, $filter, $perPage, $offset);
        $totalCount = $this->bookingModel->getCustomerBookingsCount($this->userId, $filter);

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
            'currentPage' => $page,
            'totalPages' => max(1, (int)ceil($totalCount / $perPage)),
            'totalCount' => $totalCount,
            'perPage' => $perPage,
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
        $depositPayment = $this->bookingModel->getDepositPayment($bookingId);
        $packageSchedules = $this->buildPackageSchedules($items, $eventDetails);

        $reviewModel = $this->model('ReviewModel');
        $isCompleted = ($booking['status'] ?? '') === 'completed';
        $canReview = $isCompleted && $reviewModel->canReview($this->userId, $bookingId);
        $existingReview = $isCompleted ? $reviewModel->getByBooking($bookingId) : null;
        $canEditReview = $existingReview ? $reviewModel->isWithinEditWindow((int)$existingReview['id']) : false;

        // Pricier supplier replacement awaiting the customer's approval + payment.
        $pendingReplacement = $this->bookingModel->getPendingCustomerReplacement($bookingId);
        $replacementHistory = $this->bookingModel->getReplacementHistory($bookingId);
        $refund = $this->bookingModel->getBookingRefund($bookingId);

        $this->view('booking/detail', [
            'booking' => $booking,
            'items' => $items,
            'eventDetails' => $eventDetails,
            'suppliers' => $suppliers,
            'logs' => $logs,
            'vouchers' => $vouchers,
            'bookingRef' => $bookingRef,
            'depositPercent' => BOOKING_DEPOSIT_PERCENT,
            'depositPayment' => $depositPayment ?: [],
            'packageSchedules' => $packageSchedules,
            'canReview' => $canReview,
            'existingReview' => $existingReview,
            'canEditReview' => $canEditReview,
            'pendingReplacement' => $pendingReplacement ?: null,
            'replacementHistory' => $replacementHistory,
            'refund' => $refund ?: null,
            'platformFeePercent' => get_platform_fee_percent(),
        ]);
    }

    /* ─── Vouchers (Customer) ─────────────────────────────────── */

    public function vouchers(): void
    {
        $this->ensureAuthenticated();

        $filter = trim($_GET['status'] ?? 'all');
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 12;
        $offset = ($page - 1) * $perPage;

        $vouchers = $this->bookingModel->getCustomerVouchers($this->userId, $filter, $perPage, $offset);
        $totalCount = $this->bookingModel->getCustomerVouchersCount($this->userId, $filter);

        $this->view('booking/vouchers', [
            'vouchers' => $vouchers,
            'activeFilter' => $filter,
            'currentPage' => $page,
            'totalPages' => max(1, (int)ceil($totalCount / $perPage)),
            'totalCount' => $totalCount,
            'perPage' => $perPage,
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

        if (in_array($booking['status'], ['cancelled', 'cancellation_requested', 'completed'], true)) {
            redirect('booking/detail/' . $bookingId);
            return;
        }

        $items = $this->bookingModel->getBookingItems($bookingId);
        $bookingRef = $this->bookingModel->generateBookingRef($bookingId);
        $refundEstimate = $this->bookingModel->calculateRefund($bookingId);

        $this->view('booking/cancel', [
            'booking' => $booking,
            'items' => $items,
            'bookingRef' => $bookingRef,
            'depositPercent' => BOOKING_DEPOSIT_PERCENT,
            'refundEstimate' => $refundEstimate ?: null,
            'platformFeePercent' => get_platform_fee_percent(),
        ]);
    }

    public function submitCancellation(): void
    {
        $this->ensureAuthenticated();
        $this->requireCsrf();

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

        if (in_array($booking['status'] ?? '', ['cancelled', 'cancellation_requested', 'completed'], true)) {
            $this->jsonResponse(['error' => 'This booking can no longer be cancelled.'], 400);
        }

        if (!$this->bookingModel->requestCancellation($bookingId, $reason)) {
            $this->jsonResponse(['error' => 'Could not submit cancellation request. Please try again.'], 500);
        }

        $customer = $this->getUserData();
        $customerName = $customer['name'] ?? 'A customer';
        $bookingRef = $this->bookingModel->generateBookingRef($bookingId);
        $isPackage = $this->bookingModel->isPackageBooking($bookingId);

        if ($isPackage) {
            // Package bookings: admin-mediated (existing flow)
            $this->notificationModel->notifyAdmins(
                'Cancellation Request — ' . $bookingRef,
                $customerName . ' has requested cancellation of booking ' . $bookingRef . '. Reason: ' . $reason,
                'booking', 'booking', $bookingId
            );

            $this->notificationModel->notifyBookingSuppliers(
                $bookingId,
                'Cancellation Request — ' . $bookingRef,
                $customerName . ' has requested cancellation of booking ' . $bookingRef . '. Reason: ' . $reason . '. Please stop any work in progress. Admin will review and finalize.',
                'booking'
            );

            $emailService = new EmailService();
            $admins = $this->bookingModel->getAdminEmails();
            foreach ($admins as $admin) {
                $emailService->sendAdminCancellationRequest($admin, $customer, $booking, $reason);
            }
            $suppliers = $this->bookingModel->getSupplierEmailsForBooking($bookingId);
            foreach ($suppliers as $supplier) {
                $emailService->sendSupplierCancellationRequest($supplier, $customer, $booking, $reason);
            }
        } else {
            // Customize bookings: supplier reviews first
            $this->notificationModel->notifyBookingSuppliers(
                $bookingId,
                'Cancellation Request — ' . $bookingRef,
                $customerName . ' has requested cancellation of booking ' . $bookingRef . '. Reason: ' . $reason . '. Please review and approve or decline this request.',
                'booking'
            );

            // Notify admin for awareness (they'll act after supplier approves)
            $this->notificationModel->notifyAdmins(
                'Cancellation Request (Pending Supplier Review) — ' . $bookingRef,
                $customerName . ' has requested cancellation of customize booking ' . $bookingRef . '. The supplier has been asked to review first.',
                'booking', 'booking', $bookingId
            );

            $emailService = new EmailService();
            $suppliers = $this->bookingModel->getSupplierEmailsForBooking($bookingId);
            foreach ($suppliers as $supplier) {
                $emailService->sendSupplierCancellationRequest($supplier, $customer, $booking, $reason);
            }
        }

        $this->jsonResponse([
            'success' => true,
            'message' => 'Your cancellation request has been submitted.',
        ]);
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
            if (
                (int)$s['supplier_id'] === $supplierId
                && !in_array((string)($s['status'] ?? ''), ['replaced', 'rejected', 'cancelled'], true)
            ) {
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

        // This supplier's own service rows (one per package service line) — used
        // for per-service decline / replacement display.
        $myServiceRows = array_values(array_filter(
            $suppliers,
            static fn($s) =>
                (int)($s['supplier_id'] ?? 0) === $supplierId
                && !in_array((string)($s['status'] ?? ''), ['replaced', 'rejected', 'cancelled'], true)
        ));

        $items = $this->bookingModel->getBookingItemsForSupplier($bookingId, $supplierId);
        $booking['supplier_total_amount'] = array_sum(array_map(static function ($item) {
            return (float)($item['price'] ?? 0);
        }, $items));
        $eventDetails = $this->bookingModel->getEventDetails($bookingId);
        $bookingRef = $this->bookingModel->generateBookingRef($bookingId);

        // Pass per-service time schedules for any package items so suppliers
        // can see the time windows assigned to their services.
        $allItems = $this->bookingModel->getBookingItems($bookingId);
        $packageSchedules = $this->buildPackageSchedules($allItems, $eventDetails);

        // Fetch cancellation reason if booking is in cancellation_requested
        $cancellationReason = '';
        if (($booking['status'] ?? '') === 'cancellation_requested') {
            $cancellationReason = $this->bookingModel->getCancellationReason($bookingId);
        }

        $this->view('supplier/bookingDetail', [
            'booking' => $booking,
            'items' => $items,
            'eventDetails' => $eventDetails,
            'suppliers' => $suppliers,
            'bookingRef' => $bookingRef,
            'supplierStatus' => $currentSupplierStatus ?? 'pending',
            'supplierRowId' => $currentSupplierRowId ?? 0,
            'supplierId' => $supplierId,
            'depositPercent' => BOOKING_DEPOSIT_PERCENT,
            'packageSchedules' => $packageSchedules,
            'isPackage' => $this->bookingModel->isPackageBooking($bookingId),
            'declineCutoffDays' => self::PACKAGE_DECLINE_CUTOFF_DAYS,
            'myServiceRows' => $myServiceRows,
            'cancellationReason' => $cancellationReason,
        ]);
    }

    /**
     * Supplier accept/decline booking (AJAX POST).
     */
    public function supplierRespond(): void
    {
        $this->requireCsrf();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Method not allowed'], 405);
        }

        $supplierId = $this->currentSupplierId();
        if ($supplierId <= 0) {
            $this->jsonResponse(['error' => 'Unauthorized'], 401);
        }

        $bookingId = (int)($_POST['booking_id'] ?? 0);
        $action = trim($_POST['action'] ?? ''); // 'accept' or 'decline'
        $declineReason = trim($_POST['reason'] ?? '');

        if ($bookingId <= 0 || !in_array($action, ['accept', 'decline'], true)) {
            $this->jsonResponse(['error' => 'Invalid request'], 400);
        }

        $booking = $this->bookingModel->getBookingById($bookingId);
        if (!$booking) {
            $this->jsonResponse(['error' => 'Booking not found'], 404);
        }

        $isPendingSupplierResponse = in_array(($booking['status'] ?? ''), ['pending_supplier_response', 'suppliers_responding'], true);
        $activeRepl = $this->bookingModel->getActiveReplacementForSupplier($bookingId, $supplierId);

        // GATE: Custom-flow bookings can be responded to before payment; otherwise payment must be verified
        if (!$isPendingSupplierResponse && !$this->bookingModel->isPaymentVerified($bookingId)) {
            $this->jsonResponse(['error' => 'Booking payment has not been verified yet. Supplier cannot respond.'], 403);
            return;
        }
        if (
            !$isPendingSupplierResponse
            && $activeRepl
            && !$this->bookingModel->isReplacementDeltaVerified($activeRepl)
        ) {
            $this->jsonResponse([
                'error' => 'The replacement price-difference payment is still awaiting verification. Please respond after admin verification.'
            ], 403);
            return;
        }

        // A supplier may have several service rows on one booking (one per
        // package service line). When the UI targets a specific service it
        // sends booking_supplier_id; otherwise we act on the supplier's first
        // row (custom pre-payment flow, where the booking is single-service).
        $rowIdParam = (int)($_POST['booking_supplier_id'] ?? 0);
        $suppliers = $this->bookingModel->getBookingSuppliers($bookingId);
        $rowId = 0;
        $shopName = '';
        $supplierRow = [];
        foreach ($suppliers as $s) {
            if ((int)$s['supplier_id'] !== $supplierId) {
                continue;
            }
            if ($rowIdParam > 0) {
                if ((int)$s['id'] === $rowIdParam) {
                    $rowId = (int)$s['id'];
                    $shopName = $s['shop_name'] ?? 'A supplier';
                    $supplierRow = $s;
                    break;
                }
                continue;
            }
            $rowId = (int)$s['id'];
            $shopName = $s['shop_name'] ?? 'A supplier';
            $supplierRow = $s;
            break;
        }

        if ($rowId <= 0) {
            $this->jsonResponse(['error' => 'Not associated with this booking'], 403);
        }

        // A decline on a CONFIRMED package booking does not cancel the booking —
        // it opens an admin-driven supplier replacement instead (see
        // .claude/plans/admin-supplier-replacement-on-decline.md). The custom
        // pre-payment flow keeps the original cancel behaviour.
        $isReplaceFlow = $action === 'decline'
            && !$isPendingSupplierResponse
            && $this->bookingModel->isPackageBooking($bookingId);

        // Rules for a supplier self-declining a confirmed package booking:
        // reason is mandatory, and it must be far enough before the event that
        // the admin can still arrange a replacement.
        if ($isReplaceFlow) {
            if ($declineReason === '') {
                $this->jsonResponse(['error' => 'Please give a reason so the admin can arrange a replacement.'], 422);
                return;
            }

            $daysUntilEvent = $this->bookingModel->daysUntilFirstEvent($bookingId);
            if ($daysUntilEvent !== null && $daysUntilEvent < self::PACKAGE_DECLINE_CUTOFF_DAYS) {
                $this->jsonResponse([
                    'error' => 'This event is only ' . max(0, $daysUntilEvent) . ' day(s) away. Declines must be at least '
                        . self::PACKAGE_DECLINE_CUTOFF_DAYS . ' days before the event — please contact admin directly.'
                ], 422);
                return;
            }
        }

        if ($isReplaceFlow && $activeRepl) {
            // A replacement supplier declined too — terminal for this row; the
            // replacement request goes back to the admin queue for a re-pick.
            $newStatus = 'rejected';
            if (!$this->bookingModel->updateSupplierStatus($rowId, 'rejected', ['pending', 'confirmed'])) {
                $this->jsonResponse(['error' => 'This supplier response was already handled.'], 409);
            }
            $this->bookingModel->logStatusChange($bookingId, null, 'replacement_declined', null, 'Replacement supplier declined; re-pick needed');
        } elseif ($isReplaceFlow) {
            $newStatus = 'needs_replacement';
            if (!$this->bookingModel->markSupplierNeedsReplacement($rowId)) {
                $this->jsonResponse(['error' => 'This supplier response was already handled.'], 409);
            }
            $this->bookingModel->logStatusChange($bookingId, null, 'supplier_needs_replacement', null, 'Supplier declined; awaiting admin replacement');
        } else {
            $newStatus = $action === 'accept' ? 'confirmed' : 'rejected';
            $itemStatus = $action === 'accept' ? 'accepted' : 'cancelled';

            $allowedSupplierStates = ['pending'];
            if (!$this->bookingModel->updateSupplierStatus($rowId, $newStatus, $allowedSupplierStates)) {
                $this->jsonResponse(['error' => 'This supplier response was already handled.'], 409);
            }

            $this->bookingModel->updateBookingItemsStatusBySupplier($bookingId, $supplierId, $itemStatus);
            $this->bookingModel->logStatusChange($bookingId, null, 'supplier_' . $newStatus, null, 'Supplier ' . $action . 'ed booking');
        }

        // Gather customer info and first item details for emails (fetched once, used below)
        $customerInfo   = $this->bookingModel->getCustomerForBooking($bookingId);
        $bookingItems   = $this->bookingModel->getBookingItems($bookingId);
        $firstItem      = $bookingItems[0] ?? [];
        $firstItemName  = $firstItem['service_name'] ?? $firstItem['package_name'] ?? 'your service';
        $firstItemDate  = !empty($firstItem['booking_date'])
            ? date('l, M j, Y', strtotime($firstItem['booking_date']))
            : 'your selected date';

        // Custom-flow: handle booking-level status transition
        if ($isPendingSupplierResponse) {
            if ($action === 'accept') {
                // Advance booking only once ALL suppliers have accepted
                if ($this->bookingModel->allSuppliersAccepted($bookingId)) {
                    $this->bookingModel->updateStatus($bookingId, 'pending_payment');
                    $this->bookingModel->logStatusChange($bookingId, 'pending_supplier_response', 'pending_payment', null, 'All suppliers accepted');
                    $this->notificationModel->notifyBookingCustomer(
                        $bookingId,
                        'Supplier Accepted — Please Pay',
                        $shopName . ' accepted your booking request. Please complete your ' . BOOKING_DEPOSIT_PERCENT . '% deposit to confirm.',
                        'booking'
                    );
                    if (!empty($customerInfo['email'])) {
                        $emailService = new EmailService();
                        $emailService->sendSupplierAccepted($customerInfo, $shopName, $firstItemName, $firstItemDate, $bookingId);
                    }
                }
            } elseif ($action === 'decline') {
                $this->bookingModel->updateStatus($bookingId, 'cancelled');
                $this->bookingModel->logStatusChange($bookingId, 'pending_supplier_response', 'cancelled', null, 'Supplier declined');
                $this->bookingModel->releaseBookingSlots($bookingId);
                $this->bookingModel->cancelAllSuppliers($bookingId);
                $this->notificationModel->notifyBookingCustomer(
                    $bookingId,
                    'Booking Request Declined',
                    $shopName . ' is unavailable for your requested dates. Please search for another supplier.',
                    'booking'
                );
                if (!empty($customerInfo['email'])) {
                    $emailService = new EmailService();
                    $emailService->sendSupplierDeclined($customerInfo, $shopName, $firstItemName, $firstItemDate, $bookingId);
                }
            }

            $this->jsonResponse([
                'success' => true,
                'new_status' => $newStatus,
                'message' => $action === 'accept' ? 'Booking accepted!' : 'Booking declined.',
            ]);
            return;
        }

        // Original post-payment flow: notify customer of supplier's decision
        if ($action === 'accept') {
            // If this is the replacement supplier accepting, resolve the request
            // and reconfirm the booking once no replacement work remains.
            if ($activeRepl) {
                $this->bookingModel->updateReplacement((int)$activeRepl['id'], [
                    'status' => 'accepted',
                    'resolved_at' => date('Y-m-d H:i:s'),
                ]);
                if ($this->bookingModel->bookingReplacementsResolved($bookingId)) {
                    $this->bookingModel->updateStatus($bookingId, 'confirmed');
                    $this->bookingModel->logStatusChange($bookingId, 'replacement_pending', 'confirmed', null, 'Replacement supplier accepted');
                } else {
                    // This service is resolved, but other replacement requests
                    // on the same booking are still awaiting action.
                    $this->bookingModel->updateStatus($bookingId, 'replacement_pending');
                    $this->bookingModel->logStatusChange(
                        $bookingId,
                        null,
                        'replacement_supplier_accepted',
                        null,
                        $shopName . ' accepted; other replacements are still pending'
                    );
                }
            }
            $this->notificationModel->notifyBookingCustomer(
                $bookingId,
                'Booking Accepted',
                $shopName . ' has accepted your booking! Your service is confirmed.',
                'booking'
            );
            if (!empty($customerInfo['email'])) {
                $emailService = new EmailService();
                $emailService->sendSupplierAccepted($customerInfo, $shopName, $firstItemName, $firstItemDate, $bookingId);
            }
        } elseif ($isReplaceFlow) {
            // Package booking: keep the booking alive and route to admin re-pick.
            if ($activeRepl) {
                // A replacement supplier backed out — reopen the existing request.
                // Reverse any already-paid price difference and remember the
                // rejected service so it isn't offered again (must run BEFORE we
                // clear new_service_id / price_delta below).
                $rejectedServiceId = (int)($activeRepl['new_service_id'] ?? 0);
                $this->bookingModel->reverseReplacementDeltaIfPaid((int)$activeRepl['id']);
                $this->bookingModel->appendRejectedService((int)$activeRepl['id'], $rejectedServiceId);
                $this->bookingModel->updateReplacement((int)$activeRepl['id'], [
                    'status' => 'declined_again',
                    'new_supplier_id' => null,
                    'new_service_id' => null,
                    'new_price' => null,
                    'price_delta' => null,
                ]);
            } else {
                // First decline by the original supplier — open a new request.
                $this->bookingModel->createReplacementRequest($bookingId, $supplierRow, $declineReason);
            }
            $this->bookingModel->updateStatus($bookingId, 'replacement_pending');
            $this->bookingModel->logStatusChange($bookingId, 'confirmed', 'replacement_pending', null, $shopName . ' declined; replacement needed');

            // Alert admins to pick a replacement.
            $this->notificationModel->notifyAdmins(
                'Supplier Replacement Needed',
                $shopName . ' declined booking #' . $bookingId . '. Please choose a replacement supplier.',
                'booking',
                'booking',
                $bookingId
            );
            // Reassure the customer — no action required from them yet.
            $this->notificationModel->notifyBookingCustomer(
                $bookingId,
                'Arranging a Replacement',
                $shopName . ' is unavailable for your date. We are arranging a replacement for you — no action needed right now.',
                'booking'
            );
        } else {
            $this->notificationModel->notifyBookingCustomer(
                $bookingId,
                'Booking Declined',
                $shopName . ' has declined your booking. You may need to find an alternative service.',
                'booking'
            );
            if (!empty($customerInfo['email'])) {
                $emailService = new EmailService();
                $emailService->sendSupplierDeclined($customerInfo, $shopName, $firstItemName, $firstItemDate, $bookingId);
            }
        }

        $this->jsonResponse([
            'success' => true,
            'new_status' => $newStatus,
            'message' => $action === 'accept'
                ? 'Booking accepted!'
                : ($isReplaceFlow ? 'Declined. A replacement will be arranged.' : 'Booking declined.'),
        ]);
    }

    /**
     * Supplier responds to a customer cancellation request (customize bookings).
     * POST: booking_id, action ('approve'|'decline'), optional reason
     */
    public function supplierCancellationRespond(): void
    {
        $this->requireCsrf();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Method not allowed'], 405);
        }

        $supplierId = $this->currentSupplierId();
        if ($supplierId <= 0) {
            $this->jsonResponse(['error' => 'Unauthorized'], 401);
        }

        $bookingId = (int)($_POST['booking_id'] ?? 0);
        $action = trim($_POST['action'] ?? '');
        $reason = trim($_POST['reason'] ?? '');

        if ($bookingId <= 0 || !in_array($action, ['approve', 'decline'], true)) {
            $this->jsonResponse(['error' => 'Invalid request'], 400);
        }

        if ($action === 'decline' && $reason === '') {
            $this->jsonResponse(['error' => 'Please provide a reason for declining the cancellation.'], 422);
        }

        $result = $this->bookingModel->supplierRespondToCancellation($bookingId, $supplierId, $action, $reason);
        if ($result === '') {
            $this->jsonResponse(['error' => 'Could not process your response. The cancellation may have already been handled.'], 400);
        }

        $customerInfo = $this->bookingModel->getCustomerForBooking($bookingId);
        $bookingRef = $this->bookingModel->generateBookingRef($bookingId);

        if ($result === 'approved') {
            // Notify customer
            $this->notificationModel->notifyBookingCustomer(
                $bookingId,
                'Cancellation Approved by Supplier — ' . $bookingRef,
                'Your supplier has approved your cancellation request for booking ' . $bookingRef . '. Admin will review and process your refund.',
                'booking'
            );

            // Notify admin to finalize
            $this->notificationModel->notifyAdmins(
                'Supplier Approved Cancellation — ' . $bookingRef,
                'The supplier has approved the cancellation for booking ' . $bookingRef . '. Please review and finalize.',
                'booking', 'booking', $bookingId
            );

            if (!empty($customerInfo['email'])) {
                $emailService = new EmailService();
                $emailService->sendSupplierApprovedCancellation($customerInfo, $bookingId, $bookingRef);
            }
        } else {
            // Declined
            $this->notificationModel->notifyBookingCustomer(
                $bookingId,
                'Cancellation Declined by Supplier — ' . $bookingRef,
                'Your supplier has declined your cancellation request for booking ' . $bookingRef . '. Reason: ' . $reason,
                'booking'
            );

            // Notify admin for awareness
            $this->notificationModel->notifyAdmins(
                'Supplier Declined Cancellation — ' . $bookingRef,
                'The supplier declined the cancellation for booking ' . $bookingRef . '. Reason: ' . $reason,
                'booking', 'booking', $bookingId
            );

            if (!empty($customerInfo['email'])) {
                $emailService = new EmailService();
                $emailService->sendSupplierDeclinedCancellation($customerInfo, $bookingId, $bookingRef, $reason);
            }
        }

        $this->jsonResponse([
            'success' => true,
            'result' => $result,
            'message' => $result === 'approved'
                ? 'You have approved the cancellation request.'
                : 'You have declined the cancellation request.',
        ]);
    }

    /**
     * Supplier propose reschedule (AJAX POST).
     */
    public function supplierProposeReschedule(): void
    {
        $this->requireCsrf();
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
        $this->requireRole('admin');
        $filter = trim($_GET['status'] ?? 'all');
        $search = trim($_GET['search'] ?? '');
        $sort = trim($_GET['sort'] ?? 'event_asc');
        $dateFrom = trim($_GET['date_from'] ?? '');
        $dateTo = trim($_GET['date_to'] ?? '');
        $allowedSorts = ['event_asc', 'event_desc', 'created_desc', 'created_asc', 'total_desc', 'total_asc'];
        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'event_asc';
        }
        $validDate = static function (string $value): string {
            $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
            return $date && $date->format('Y-m-d') === $value ? $value : '';
        };
        $dateFrom = $validDate($dateFrom);
        $dateTo = $validDate($dateTo);
        if ($dateFrom !== '' && $dateTo !== '' && $dateFrom > $dateTo) {
            [$dateFrom, $dateTo] = [$dateTo, $dateFrom];
        }
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 15;

        $totalCount = $this->bookingModel->getAllBookingsCount($filter, $search, $dateFrom, $dateTo);
        $totalPages = max(1, (int)ceil($totalCount / $perPage));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $perPage;
        $bookings = $this->bookingModel->getAllBookings(
            $filter,
            $search,
            $perPage,
            $offset,
            $sort,
            $dateFrom,
            $dateTo
        );
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
            'sort' => $sort,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalCount' => $totalCount,
            'perPage' => $perPage,
        ]);
    }

    /**
     * Admin booking detail.
     */
    public function adminBookingDetail(int $bookingId): void
    {
        $this->requireRole('admin');
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
        $packageSchedules = $this->buildPackageSchedules($items, $eventDetails);

        $refund = $this->bookingModel->getBookingRefund($bookingId);
        $refundEstimate = $this->bookingModel->calculateRefund($bookingId);

        $this->view('admin/bookingDetail', [
            'booking' => $booking,
            'items' => $items,
            'suppliers' => $suppliers,
            'eventDetails' => $eventDetails,
            'logs' => $logs,
            'payments' => $payments,
            'vouchers' => $vouchers,
            'bookingRef' => $bookingRef,
            'packageSchedules' => $packageSchedules,
            'depositPercent' => BOOKING_DEPOSIT_PERCENT,
            'refund' => $refund ?: null,
            'refundEstimate' => $refundEstimate ?: null,
        ]);
    }

    /* ─── Supplier replacement (admin-driven) ─────────────────────
     * See .claude/plans/admin-supplier-replacement-on-decline.md
     */

    /** Admin queue of bookings awaiting a replacement pick. */
    public function adminReplacementQueue(): void
    {
        $this->requireRole('admin');
        $replacements = $this->bookingModel->getPendingReplacements();
        foreach ($replacements as &$r) {
            $r['booking_ref'] = $this->bookingModel->generateBookingRef((int)$r['booking_id']);
        }
        unset($r);

        $this->view('admin/replacementQueue', [
            'replacements' => $replacements,
        ]);
    }

    /** Admin candidate picker for one replacement request. */
    public function adminReplacementPicker($replacementId = null): void
    {
        $this->requireRole('admin');
        $replacementId = (int)$replacementId;
        $replacement = $this->bookingModel->getReplacement($replacementId);
        if (!$replacement) {
            redirect('admin/replacementQueue');
            return;
        }
        $candidates = $this->bookingModel->findReplacementCandidates($replacementId);

        $this->view('admin/replacementPicker', [
            'replacement' => $replacement,
            'candidates'  => $candidates,
            'bookingRef'  => $this->bookingModel->generateBookingRef((int)$replacement['booking_id']),
            'maxUpchargePct' => defined('MAX_REPLACEMENT_UPCHARGE_PCT') ? MAX_REPLACEMENT_UPCHARGE_PCT : 25,
        ]);
    }

    /**
     * Admin assigns a chosen candidate (AJAX POST).
     *  - same/cheaper price -> swap immediately (platform absorbs).
     *  - pricier (within cap) -> propose to customer + create delta payment;
     *    the swap is finalized when admin verifies that delta payment.
     */
    public function adminAssignReplacement(): void
    {
        $adminId = $this->requireRole('admin', true);
        $this->requireCsrf();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Method not allowed'], 405);
        }
        $replacementId = (int)($_POST['replacement_id'] ?? 0);
        $newServiceId  = (int)($_POST['service_id'] ?? 0);
        if ($replacementId <= 0 || $newServiceId <= 0) {
            $this->jsonResponse(['error' => 'Invalid request'], 400);
        }

        $replacement = $this->bookingModel->getReplacement($replacementId);
        if (!$replacement || !in_array($replacement['status'], ['pending_admin', 'declined_again', 'rejected_by_customer'], true)) {
            $this->jsonResponse(['error' => 'Replacement not open for assignment'], 409);
        }

        // Validate the chosen service is a real candidate (category, date,
        // availability). Price is not capped — over-budget picks are allowed and
        // routed to the customer-approval (propose + pay delta) path below.
        $candidates = $this->bookingModel->findReplacementCandidates($replacementId);
        $chosen = null;
        foreach ($candidates as $c) {
            if ((int)$c['service_id'] === $newServiceId) {
                $chosen = $c;
                break;
            }
        }
        if (!$chosen) {
            $this->jsonResponse(['error' => 'That service is not an eligible replacement.'], 422);
        }

        $bookingId = (int)$replacement['booking_id'];
        $oldPrice  = (float)($replacement['old_price'] ?? 0);
        $newPrice  = (float)($chosen['price'] ?? 0);
        $delta     = round($newPrice - $oldPrice, 2);

        // Record the pick.
        $this->bookingModel->updateReplacement($replacementId, [
            'new_supplier_id' => (int)$chosen['supplier_id'],
            'new_service_id'  => $newServiceId,
            'new_price'       => $newPrice,
            'price_delta'     => $delta,
            'chosen_by_admin_id' => $adminId,
        ]);

        if ($delta <= 0) {
            // Auto-swap; platform absorbs any saving.
            $this->bookingModel->updateReplacement($replacementId, ['requires_customer_approval' => 0]);
            if (!$this->bookingModel->performReplacementSwap($replacementId, false)) {
                $this->jsonResponse(['error' => $this->bookingModel->getReplacementSwapError()], 500);
            }
            $this->bookingModel->setSupplierResponseDeadline($bookingId, '+48 hours');
            $newSupplierUserId = $this->bookingModel->getSupplierUserId((int)$chosen['supplier_id']);
            if ($newSupplierUserId > 0) {
                $this->notificationModel->notifyUser(
                    $newSupplierUserId,
                    'New Package Booking — Please Respond',
                    'You have been assigned to a package booking as a replacement. Please accept or decline within 48 hours.',
                    'booking',
                    'booking',
                    $bookingId
                );
            }
            $this->notificationModel->notifyBookingCustomer(
                $bookingId,
                'Replacement Arranged',
                ($chosen['shop_name'] ?? 'A new supplier') . ' has been assigned to your booking at no extra cost.',
                'booking'
            );
            $this->jsonResponse(['success' => true, 'mode' => 'auto', 'message' => 'Replacement assigned.']);
            return;
        }

        // Pricier: propose to customer + open a pending delta payment.
        $paymentId = $this->bookingModel->createReplacementDeltaPayment($bookingId, $delta);
        $this->bookingModel->updateReplacement($replacementId, [
            'requires_customer_approval' => 1,
            'delta_payment_id' => $paymentId,
            'status' => 'pending_customer',
            'proposed_at' => date('Y-m-d H:i:s'),
        ]);
        $this->notificationModel->notifyBookingCustomer(
            $bookingId,
            'Replacement Needs Your Approval',
            ($chosen['shop_name'] ?? 'A new supplier') . ' is available but costs ' . $this->money($delta) .
            ' more. Approve and pay the difference to confirm the replacement.',
            'booking'
        );
        $this->jsonResponse([
            'success' => true,
            'mode' => 'pending_customer',
            'delta' => $delta,
            'message' => 'Proposal sent to customer for approval + payment.',
        ]);
    }

    /**
     * Admin verifies the customer's delta payment for a pricier replacement,
     * which finalizes the swap (AJAX POST).
     */
    public function adminVerifyReplacementPayment(): void
    {
        $adminId = $this->requireRole('admin', true);
        $this->requireCsrf();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Method not allowed'], 405);
        }
        $replacementId = (int)($_POST['replacement_id'] ?? 0);
        $replacement = $this->bookingModel->getReplacement($replacementId);
        if (!$replacement || $replacement['status'] !== 'pending_customer') {
            $this->jsonResponse(['error' => 'No pending-customer replacement to verify'], 409);
        }
        $payment = $this->bookingModel->getPaymentById((int)($replacement['delta_payment_id'] ?? 0));
        $expectedDelta = round((float)($replacement['price_delta'] ?? 0), 2);
        if (
            !$payment
            || ($payment['type'] ?? '') !== 'replacement_delta'
            || ($payment['status'] ?? '') !== 'pending'
            || empty($payment['payment_slip_path'])
            || abs((float)($payment['paid_amount'] ?? 0) - $expectedDelta) > 0.01
        ) {
            $this->jsonResponse(['error' => 'A valid matching payment proof is required before verification.'], 422);
        }

        $bookingId = (int)$replacement['booking_id'];
        $this->bookingModel->updateReplacement($replacementId, ['customer_approved_at' => date('Y-m-d H:i:s')]);

        if (!$this->bookingModel->performReplacementSwap($replacementId, true)) {
            $this->jsonResponse(['error' => $this->bookingModel->getReplacementSwapError()], 500);
        }
        if (!$this->bookingModel->markPaymentSuccess((int)$payment['id'], $adminId)) {
            $this->jsonResponse(['error' => 'Payment was already reviewed.'], 409);
        }
        $this->bookingModel->setSupplierResponseDeadline($bookingId, '+48 hours');
        $newSupplierUserId = $this->bookingModel->getSupplierUserId((int)($replacement['new_supplier_id'] ?? 0));
        if ($newSupplierUserId > 0) {
            $this->notificationModel->notifyUser(
                $newSupplierUserId,
                'New Package Booking — Please Respond',
                'You have been assigned to a package booking as a replacement. Please accept or decline within 48 hours.',
                'booking',
                'booking',
                $bookingId
            );
        }
        $this->notificationModel->notifyBookingCustomer(
            $bookingId,
            'Replacement Confirmed',
            'Your replacement supplier is confirmed. Thank you for the additional payment.',
            'booking'
        );
        $this->jsonResponse(['success' => true, 'message' => 'Payment verified; replacement finalized.']);
    }

    /**
     * Admin cancel booking (AJAX POST).
     */
    public function adminCancelBooking(): void
    {
        $adminId = $this->requireRole('admin', true);
        $this->requireCsrf();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Method not allowed'], 405);
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

    /* ─── Refund Queue (Admin) ────────────────────────────────── */

    /**
     * Admin refund queue page — shows pending and processing refunds.
     */
    public function adminRefundQueue(): void
    {
        $this->requireRole('admin');
        $refunds = $this->bookingModel->getRefundQueue();
        $stats = $this->bookingModel->getRefundStats();

        // Enrich with booking refs
        foreach ($refunds as &$r) {
            $r['booking_ref'] = $this->bookingModel->generateBookingRef((int)$r['booking_id']);
        }
        unset($r);

        $this->view('admin/refundQueue', [
            'refunds' => $refunds,
            'stats' => $stats,
        ]);
    }

    /**
     * Admin submits refund proof (processing stage).
     */
    public function adminProcessRefund(): void
    {
        $adminId = $this->requireRole('admin', true);
        $this->requireCsrf();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Method not allowed'], 405);
        }

        $refundId = (int)($_POST['refund_id'] ?? 0);
        $transactionRef = trim((string)($_POST['transaction_ref'] ?? ''));
        $bankName = trim((string)($_POST['bank_name'] ?? ''));
        $note = trim((string)($_POST['note'] ?? ''));

        if ($refundId <= 0) {
            $this->jsonResponse(['error' => 'Invalid refund ID.'], 400);
        }

        $refund = $this->bookingModel->getRefundById($refundId);
        if (!$refund) {
            $this->jsonResponse(['error' => 'Refund not found.'], 404);
        }

        if (!in_array($refund['status'], ['pending', 'processing'], true)) {
            $this->jsonResponse(['error' => 'This refund has already been processed.'], 400);
        }

        // Handle proof of transfer upload
        $slipPath = '';
        if (!empty($_FILES['slip_image']) && $_FILES['slip_image']['error'] === UPLOAD_ERR_OK) {
            $slipPath = $this->storePaymentSlip($_FILES['slip_image']);
            if (!$slipPath) {
                $this->jsonResponse(['error' => 'Invalid file. Upload JPG, PNG, WebP, or PDF under 10MB.'], 422);
            }
        }

        // If no new upload, keep existing slip path
        if (!$slipPath && !empty($refund['refund_slip_path'])) {
            $slipPath = $refund['refund_slip_path'];
        }

        if (!$this->bookingModel->processRefund($refundId, $adminId, $transactionRef, $bankName, $slipPath, $note)) {
            $this->jsonResponse(['error' => 'Could not process refund.'], 500);
        }

        // Notify customer
        $this->notificationModel->notifyBookingCustomer(
            (int)$refund['booking_id'],
            'Refund Being Processed',
            'Your refund of ' . number_format((float)$refund['amount'], 0) . ' MMK is being processed. You will receive it shortly.',
            'booking'
        );

        $this->jsonResponse([
            'success' => true,
            'message' => 'Refund marked as processing with proof uploaded.',
        ]);
    }

    /**
     * Admin marks a refund as completed (money actually sent to customer).
     */
    public function adminCompleteRefund(): void
    {
        $adminId = $this->requireRole('admin', true);
        $this->requireCsrf();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Method not allowed'], 405);
        }

        $refundId = (int)($_POST['refund_id'] ?? 0);
        if ($refundId <= 0) {
            $this->jsonResponse(['error' => 'Invalid refund ID.'], 400);
        }

        $refund = $this->bookingModel->getRefundById($refundId);
        if (!$refund) {
            $this->jsonResponse(['error' => 'Refund not found.'], 404);
        }

        if (!$this->bookingModel->completeRefund($refundId, $adminId)) {
            $this->jsonResponse(['error' => 'Could not complete refund. Ensure it is in processing status with proof uploaded.'], 500);
        }

        // Notify customer
        $this->notificationModel->notifyBookingCustomer(
            (int)$refund['booking_id'],
            'Refund Completed',
            'Your refund of ' . number_format((float)$refund['amount'], 0) . ' MMK has been completed. Please check your account.',
            'booking'
        );

        $this->jsonResponse([
            'success' => true,
            'message' => 'Refund marked as completed.',
        ]);
    }

    /**
     * Admin rejects a refund request.
     */
    public function adminRejectRefund(): void
    {
        $adminId = $this->requireRole('admin', true);
        $this->requireCsrf();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Method not allowed'], 405);
        }

        $refundId = (int)($_POST['refund_id'] ?? 0);
        $reason = trim((string)($_POST['reason'] ?? ''));

        if ($refundId <= 0) {
            $this->jsonResponse(['error' => 'Invalid refund ID.'], 400);
        }
        if ($reason === '') {
            $this->jsonResponse(['error' => 'Please provide a reason.'], 400);
        }

        $refund = $this->bookingModel->getRefundById($refundId);
        if (!$refund) {
            $this->jsonResponse(['error' => 'Refund not found.'], 404);
        }

        if (!$this->bookingModel->rejectRefund($refundId, $adminId, $reason)) {
            $this->jsonResponse(['error' => 'Could not reject refund.'], 500);
        }

        // Notify customer
        $this->notificationModel->notifyBookingCustomer(
            (int)$refund['booking_id'],
            'Refund Request Rejected',
            'Your refund request has been declined. Reason: ' . $reason,
            'booking'
        );

        $this->jsonResponse([
            'success' => true,
            'message' => 'Refund rejected.',
        ]);
    }

    /**
     * Admin marks a booking as received/verified and notifies both sides.
     */
    public function adminMarkBookingReceived(): void
    {
        $adminId = $this->requireRole('admin', true);
        $this->requireCsrf();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Method not allowed'], 405);
        }

        $bookingId = (int)($_POST['booking_id'] ?? 0);
        $note = trim((string)($_POST['note'] ?? ''));

        if ($bookingId <= 0) {
            $this->jsonResponse(['error' => 'Invalid booking'], 400);
        }

        $booking = $this->bookingModel->getBookingById($bookingId);
        if (!$booking) {
            $this->jsonResponse(['error' => 'Booking not found'], 404);
        }

        $oldStatus = (string)($booking['status'] ?? 'draft');
        if (in_array($oldStatus, ['cancelled', 'completed'], true)) {
            $this->jsonResponse(['error' => 'This booking can no longer be marked as received.'], 400);
        }

        $payments = $this->bookingModel->getBookingPayments($bookingId);
        $hasPendingDeposit = false;
        foreach ($payments as $payment) {
            if (($payment['type'] ?? '') === 'deposit' && ($payment['status'] ?? '') === 'pending') {
                $hasPendingDeposit = true;
                break;
            }
        }

        if (!$hasPendingDeposit) {
            $this->jsonResponse(['error' => 'No pending deposit proof exists for this booking.'], 409);
        }
        $saved = $this->bookingModel->adminVerifyPayment($bookingId, $adminId, $note);

        if (!$saved) {
            $this->jsonResponse(['error' => 'Could not mark booking as received.'], 500);
        }

        $updatedBooking = $this->bookingModel->getBookingById($bookingId);
        $newStatus = (string)($updatedBooking['status'] ?? 'payment_verified');
        $logNote = 'Marked as received by admin' . ($note !== '' ? ': ' . $note : '');
        $this->bookingModel->logStatusChange($bookingId, $oldStatus, $newStatus, $adminId, $logNote);

        $bookingRef = $this->bookingModel->generateBookingRef($bookingId);
        $this->notificationModel->notifyBookingCustomer(
            $bookingId,
            'Booking Received',
            'Your booking ' . $bookingRef . ' has been received and is being processed by Golden Promise.',
            'booking'
        );

        $this->notificationModel->notifyBookingSuppliers(
            $bookingId,
            'Booking Received by Admin',
            'Booking ' . $bookingRef . ' has been received by admin. Please review the booking details and prepare for the next step.',
            'booking'
        );

        $this->jsonResponse([
            'success' => true,
            'message' => 'Booking marked as received. Customer and suppliers have been notified.',
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
        $this->requireCsrf();

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
        $expectedDeposit = round((float)$booking['total_amount'] * (BOOKING_DEPOSIT_PERCENT / 100), 2);
        $platformFee = round((float)$booking['total_amount'] * (get_platform_fee_percent() / 100), 2);
        $totalWithFee = round($expectedDeposit + $platformFee, 2);
        if (!$this->bookingModel->submitPaymentSlip(
            $bookingId,
            $slipPath,
            $reference,
            $paymentMethod,
            '',
            '',
            $totalWithFee,
            date('Y-m-d H:i:s'),
            $platformFee,
            $expectedDeposit
        )) {
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

        $this->notifyAdminsOfDepositSubmission($bookingId);

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
     * Supplier payment history page — all customer payments for their bookings.
     */
    public function supplierPaymentHistory(): void
    {
        $this->ensureAuthenticated();

        $supplierId = $this->currentSupplierId();
        if ($supplierId <= 0) {
            redirect('supplier/dashboard');
            return;
        }

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $payments = $this->bookingModel->getSupplierPaymentHistory($supplierId, $perPage, $offset);
        $totalCount = $this->bookingModel->getSupplierPaymentHistoryCount($supplierId);
        $totalPages = (int)ceil($totalCount / $perPage);

        // Summary stats
        $totalReceived = 0;
        $totalFees = 0;
        $approvedCount = 0;
        $pendingCount = 0;
        foreach ($payments as $p) {
            $amt = (float)($p['amount'] ?? 0);
            if (($p['status'] ?? '') === 'success') {
                $totalReceived += $amt;
                $approvedCount++;
            } elseif (($p['status'] ?? '') === 'pending') {
                $pendingCount++;
            }
            $totalFees += (float)($p['platform_fee'] ?? 0);
        }

        $this->view('supplier/paymentHistory', [
            'payments' => $payments,
            'supplierId' => $supplierId,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalCount' => $totalCount,
            'totalReceived' => $totalReceived,
            'totalFees' => $totalFees,
            'approvedCount' => $approvedCount,
            'pendingCount' => $pendingCount,
        ]);
    }

    /**
     * Request payout to supplier bank account (AJAX POST).
     */
    public function requestPayoutPost(): void
    {
        $this->ensureAuthenticated();
        $this->requireCsrf();

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

        $result = (new PayoutService())->requestAvailableBalance(
            $supplierId,
            $bankAccount,
            $bankCode,
            $amount
        );

        if (empty($result['success'])) {
            $this->jsonResponse($result, 409);
            return;
        }

        $this->jsonResponse($result + [
            'message' => 'Payout request submitted. You will receive funds within 1-2 business days.',
        ]);
    }

    /**
     * Confirm instant payment from gateway (MM QR / Visa Card).
     * Creates success payment record and moves booking to paid.
     */
    public function confirmInstantPayment(): void
    {
        $this->ensureAuthenticated();
        $this->requireCsrf();

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

        // Verify amount matches expected deposit + platform fee
        $expectedDeposit = (float)$booking['total_amount'] * (BOOKING_DEPOSIT_PERCENT / 100);
        $platformFee = round((float)$booking['total_amount'] * (get_platform_fee_percent() / 100), 2);
        $expectedTotal = round($expectedDeposit + $platformFee, 2);
        if (abs($amount - $expectedTotal) > 0.01) { // Allow 1 cent tolerance
            $this->jsonResponse(['error' => 'Payment amount mismatch.'], 400);
            return;
        }

        $gateway = new PaymentGatewayService();
        $verification = $gateway->verifyTransaction($transactionId);
        $gatewayStatus = strtolower((string)($verification['status'] ?? ''));
        $gatewayAmount = (float)($verification['amount'] ?? 0);
        if (
            empty($verification['success'])
            || !in_array($gatewayStatus, ['success', 'paid', 'completed', '0000'], true)
            || abs($gatewayAmount - $expectedDeposit) > 0.01
            || !$gateway->methodMatches($method, $verification['method'] ?? '')
        ) {
            $this->jsonResponse(['error' => 'The payment gateway could not verify this transaction.'], 422);
        }

        // Confirm instant payment (creates payment record and sets booking to 'paid' transiently)
        if (!$this->bookingModel->confirmInstantPayment($bookingId, $method, $transactionId, $gatewayAmount)) {
            $this->jsonResponse(['error' => 'Failed to confirm payment.'], 500);
            return;
        }

        $isPackage = $this->bookingModel->isPackageBooking($bookingId);
        if ($isPackage) {
            $this->bookingModel->autoConfirmAllSuppliers($bookingId);
        }
        $this->bookingModel->updateStatus($bookingId, 'confirmed', 'partial');
        $this->bookingModel->logStatusChange($bookingId, 'pending_payment', 'confirmed', $this->userId);
        $this->bookingModel->generateVouchers($bookingId);

        if ($isPackage) {
            $this->notificationModel->notifyBookingCustomer(
                $bookingId,
                'Booking Confirmed',
                'Your payment has been confirmed! Your booking is confirmed.',
                'payment'
            );
        } else {
            $this->notificationModel->notifyBookingCustomer(
                $bookingId,
                'Payment Confirmed',
                'Your payment has been confirmed! Your booking is confirmed.',
                'payment'
            );
            $this->notificationModel->notifyBookingSuppliers(
                $bookingId,
                'New Booking — Payment Confirmed',
                'A new booking with confirmed payment is ready. The booking is confirmed.',
                'booking'
            );
        }

        $this->jsonResponse([
            'success' => true,
            'message' => 'Payment confirmed! Your booking is confirmed.',
        ]);
    }
}
