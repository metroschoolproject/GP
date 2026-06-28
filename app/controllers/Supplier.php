<?php

require_once APPROOT . '/controllers/SupplierControllerSupport.php';
require_once APPROOT . '/controllers/SupplierServices.php';
require_once APPROOT . '/controllers/SupplierServiceMedia.php';
require_once APPROOT . '/controllers/SupplierAvailability.php';
require_once APPROOT . '/controllers/SupplierNotifications.php';
require_once APPROOT . '/controllers/Booking.php';
require_once APPROOT . '/services/EmailService.php';

class Supplier extends SupplierControllerSupport
{
    public function logout()
    {
        redirect('users/logout');
    }

    public function onboarding()
    {
        $userId = $_SESSION['pending_register_user_id'] ?? $_SESSION['session_uid'] ?? null;
        $supplier = $userId ? $this->supplierProfileModel->getByUserId($userId) : null;
        $isAjax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' && $supplier && strtolower($supplier['status'] ?? '') === 'pending') {
            redirect('supplier/pending');
        }

        $data = [
            'email' => $_SESSION['pending_register_email'] ?? $_SESSION['session_email'] ?? '',
            'categories' => $this->supplierProfileModel->getCategories(),
            'submitted' => false,
            'message' => '',
        ];

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $this->requireCsrf(false);
            $data = [
                'email' => trim($_POST['email'] ?? ($_SESSION['pending_register_email'] ?? '')),
                'business_name' => trim($_POST['business_name'] ?? ''),
                'business_description' => trim($_POST['business_description'] ?? ''),
                'phone' => trim($_POST['phone'] ?? ''),
                'business_address' => trim($_POST['business_address'] ?? ''),
                'category_prompt' => trim($_POST['category_prompt'] ?? ''),
                'category_ids' => array_map('intval', $_POST['category_ids'] ?? []),
                'category_source' => 'manual',
                'business_url' => trim($_POST['business_url'] ?? ''),
                'thumbnail_url' => null,
                'agreement_accepted' => !empty($_POST['agreement_accepted']),
                'agreement_accepted_at' => date('Y-m-d H:i:s'),
                'agreement_version' => 'supplier-v1',
                'categories' => $this->supplierProfileModel->getCategories(),
            ];

            if (
                $data['email'] === '' ||
                $data['business_name'] === '' ||
                $data['business_description'] === '' ||
                $data['phone'] === '' ||
                $data['business_address'] === '' ||
                $data['business_url'] === '' ||
                empty($data['category_ids']) ||
                !$data['agreement_accepted'] ||
                !$this->uploadService->hasUploaded('cover_photo') ||
                !$this->uploadService->hasUploaded('business_license')
            ) {
                $data['submitted'] = false;
                $data['message'] = 'Please fill all required supplier information.';
            } elseif (!preg_match('/^[0-9]{9,11}$/', $data['phone'])) {
                $data['submitted'] = false;
                $data['message'] = 'Please enter a valid phone number (9-11 digits).';
            } elseif (!filter_var(htmlspecialchars_decode($data['business_url'], ENT_QUOTES), FILTER_VALIDATE_URL)) {
                $data['submitted'] = false;
                $data['message'] = 'Please enter a valid business URL.';
            } elseif (!$this->supplierProfileModel->areValidCategories($data['category_ids'])) {
                $data['submitted'] = false;
                $data['message'] = 'Please choose at least one valid business category.';
            } elseif (!$this->uploadService->isValidCoverPhoto($_FILES['cover_photo'])) {
                $data['submitted'] = false;
                $data['message'] = 'Please upload a valid cover photo under 5MB.';
            } elseif (!$this->uploadService->isValidBusinessLicense($_FILES['business_license'])) {
                $data['submitted'] = false;
                $data['message'] = 'Please upload a valid business license document under 5MB.';
            } else {
                $data['user_id'] = $userId;
                try {
                    $saved = $this->supplierProfileModel->save($data);
                } catch (Throwable $e) {
                    $saved = false;
                }
                $_SESSION['supplier_profile'] = $data;

                if ($saved) {
                    if (!$this->saveSupplierDocumentOrRespond($saved, $data, 'cover_photo', 'cover-photo', 'cover photo', $isAjax)) {
                        return;
                    }

                    if (!$this->saveSupplierDocumentOrRespond($saved, $data, 'business_license', 'business-license', 'business license', $isAjax)) {
                        return;
                    }

                    $this->notificationModel->notifyAdmins(
                        'New supplier application',
                        $data['business_name'] . ' submitted a supplier application.',
                        'approval',
                        'supplier',
                        (int)$saved['supplier_id']
                    );

                    if ($isAjax) {
                        $this->jsonResponse([
                            'status' => 'success',
                            'message' => 'Your supplier application was submitted.',
                            'redirect' => URLROOT . '/supplier/pending',
                        ]);
                    }

                    redirect('supplier/pending');
                }

                $data['submitted'] = true;
                $data['message'] = 'We could not save the supplier information. Please sign in again and try one more time.';
            }

            if ($isAjax) {
                $this->jsonResponse([
                    'status' => 'error',
                    'message' => $data['message'] ?: 'Please check your supplier information and try again.',
                ], 422);
            }
        }

        $this->view('supplier/onboarding', $data);
    }

    public function suggestCategories()
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->jsonResponse(['status' => 'error', 'message' => 'Method not allowed.'], 405);
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $prompt = trim($input['prompt'] ?? '');

        if ($prompt === '') {
            $this->jsonResponse(['status' => 'error', 'message' => 'Business description is required.'], 422);
        }

        $categories = $this->supplierProfileModel->getCategories();
        $suggestion = $this->suggestCategoriesWithGemini($prompt, $categories);

        if (!$suggestion) {
            $this->jsonResponse(['status' => 'error', 'message' => 'AI suggestion is unavailable.'], 503);
        }

        $categoryIds = array_values(array_unique(array_filter(array_map('intval', $suggestion['category_ids'] ?? []), function ($categoryId) {
            return $categoryId > 0;
        })));

        if (empty($categoryIds) || !$this->supplierProfileModel->areValidCategories($categoryIds)) {
            $this->jsonResponse(['status' => 'error', 'message' => 'AI could not match a valid category.'], 422);
        }

        $this->jsonResponse([
            'status' => 'success',
            'category_ids' => $categoryIds,
            'reason' => $suggestion['reason'] ?? '',
        ]);
    }

    public function pending()
    {
        $userId = $_SESSION['session_uid'] ?? $_SESSION['pending_register_user_id'] ?? null;

        if (!$userId) {
            redirect('users/login');
        }

        $supplier = $this->supplierProfileModel->getByUserId($userId);

        if (!$supplier) {
            redirect('supplier/onboarding');
        }

        if (in_array(strtolower($supplier['status'] ?? ''), ['approved', 'verified'], true)) {
            redirect('supplier/dashboard');
        }

        $this->view('supplier/pending', [
            'supplier' => $supplier,
            'email' => $_SESSION['session_email'] ?? $_SESSION['pending_register_email'] ?? '',
        ]);
    }

    public function dashboard()
    {
        $access = $this->authorizationService->dashboardAccess();

        if (!empty($access['redirect'])) {
            redirect($access['redirect']);
        }

        if (empty($access['allowed'])) {
            $this->view('supplier/dashboard_locked', [
                'supplier' => $access['supplier'],
                'payment' => $access['payment'] ?? null,
                'lockState' => $access['lockState'] ?? 'profile_not_approved',
            ]);
            return;
        }

        $supplier = $access['supplier'];

        $this->view('supplier/dashboard', [
            'supplier' => $supplier,
            'payment' => $access['payment'],
            'dashboardData' => $this->supplierProfileModel->getDashboardData((int)$supplier['supplier_id']),
        ]);
    }

    public function services()
    {
        return $this->forwardTo(SupplierServices::class, __FUNCTION__, func_get_args());
    }

    public function calendar()
    {
        return $this->forwardTo(SupplierServices::class, __FUNCTION__, func_get_args());
    }

    public function serviceManagementData()
    {
        return $this->forwardTo(SupplierServices::class, __FUNCTION__, func_get_args());
    }

    public function serviceDetail($serviceId = null)
    {
        return $this->forwardTo(SupplierServices::class, __FUNCTION__, func_get_args());
    }

    public function serviceCalendar($serviceId = null)
    {
        return $this->forwardTo(SupplierServices::class, __FUNCTION__, func_get_args());
    }

    public function serviceCalendarData($serviceId = null)
    {
        return $this->forwardTo(SupplierServices::class, __FUNCTION__, func_get_args());
    }

    public function serviceCreate()
    {
        return $this->forwardTo(SupplierServices::class, __FUNCTION__, func_get_args());
    }

    public function serviceUpdate($serviceId = null)
    {
        return $this->forwardTo(SupplierServices::class, __FUNCTION__, func_get_args());
    }

    public function serviceDelete($serviceId = null)
    {
        return $this->forwardTo(SupplierServices::class, __FUNCTION__, func_get_args());
    }

    public function serviceStatus($serviceId = null)
    {
        return $this->forwardTo(SupplierServices::class, __FUNCTION__, func_get_args());
    }

    public function servicePublishRequest($serviceId = null)
    {
        return $this->forwardTo(SupplierServices::class, __FUNCTION__, func_get_args());
    }

    public function servicePublishStatus($serviceId = null)
    {
        return $this->forwardTo(SupplierServices::class, __FUNCTION__, func_get_args());
    }

    public function packageCreate()
    {
        return $this->forwardTo(SupplierServices::class, __FUNCTION__, func_get_args());
    }

    public function packageUpdate($packageId = null)
    {
        return $this->forwardTo(SupplierServices::class, __FUNCTION__, func_get_args());
    }

    public function packageDelete($packageId = null)
    {
        return $this->forwardTo(SupplierServices::class, __FUNCTION__, func_get_args());
    }

    public function packageStatus($packageId = null)
    {
        return $this->forwardTo(SupplierServices::class, __FUNCTION__, func_get_args());
    }

    public function serviceMediaCreate($serviceId = null)
    {
        return $this->forwardTo(SupplierServiceMedia::class, __FUNCTION__, func_get_args());
    }

    public function serviceMediaDelete($serviceId = null, $mediaId = null)
    {
        return $this->forwardTo(SupplierServiceMedia::class, __FUNCTION__, func_get_args());
    }

    public function serviceAvailabilitySave($serviceId = null)
    {
        return $this->forwardTo(SupplierAvailability::class, __FUNCTION__, func_get_args());
    }

    public function serviceAvailabilityOverrideSave($serviceId = null)
    {
        return $this->forwardTo(SupplierAvailability::class, __FUNCTION__, func_get_args());
    }

    public function serviceAvailabilityOverrideDelete($serviceId = null, $overrideId = null)
    {
        return $this->forwardTo(SupplierAvailability::class, __FUNCTION__, func_get_args());
    }

    public function venueRoomAvailabilityOverrideSave($serviceId = null)
    {
        return $this->forwardTo(SupplierAvailability::class, __FUNCTION__, func_get_args());
    }

    public function venueRoomAvailabilityOverrideDelete($serviceId = null, $overrideId = null)
    {
        return $this->forwardTo(SupplierAvailability::class, __FUNCTION__, func_get_args());
    }

    public function serviceAvailabilityPreview($serviceId = null)
    {
        return $this->forwardTo(SupplierAvailability::class, __FUNCTION__, func_get_args());
    }

    public function allServicesCapacityPreview()
    {
        return $this->forwardTo(SupplierAvailability::class, __FUNCTION__, func_get_args());
    }

    public function serviceSlotReserve($slotId = null)
    {
        return $this->forwardTo(SupplierAvailability::class, __FUNCTION__, func_get_args());
    }

    public function serviceSlotRelease($slotId = null)
    {
        return $this->forwardTo(SupplierAvailability::class, __FUNCTION__, func_get_args());
    }

    public function notificationsJson()
    {
        return $this->forwardTo(SupplierNotifications::class, __FUNCTION__, func_get_args());
    }

    public function markNotificationRead($notificationId = null)
    {
        return $this->forwardTo(SupplierNotifications::class, __FUNCTION__, func_get_args());
    }

    public function notifications()
    {
        return $this->forwardTo(SupplierNotifications::class, __FUNCTION__, func_get_args());
    }

    public function markAllNotificationsRead()
    {
        return $this->forwardTo(SupplierNotifications::class, __FUNCTION__, func_get_args());
    }

    public function notification($notificationId = null)
    {
        return $this->forwardTo(SupplierNotifications::class, __FUNCTION__, func_get_args());
    }

    public function bookings()
    {
        return $this->forwardTo(Booking::class, 'supplierBookings', func_get_args());
    }

    public function assignments()
    {
        return $this->forwardTo(Booking::class, 'supplierAssignments', func_get_args());
    }

    public function bookingDetail($bookingId = null)
    {
        return $this->forwardTo(Booking::class, 'supplierBookingDetail', [(int)$bookingId]);
    }

    public function bookingRespond()
    {
        return $this->forwardTo(Booking::class, 'supplierRespond', func_get_args());
    }

    public function bookingCancellationRespond()
    {
        return $this->forwardTo(Booking::class, 'supplierCancellationRespond', func_get_args());
    }

    public function bookingRequestCancellation()
    {
        return $this->forwardTo(Booking::class, 'supplierRequestCancellation', func_get_args());
    }

    public function paymentHistory()
    {
        return $this->forwardTo(Booking::class, 'supplierPaymentHistory', func_get_args());
    }

    public function earnings()
    {
        return $this->forwardTo(Booking::class, 'supplierEarnings', func_get_args());
    }

    public function reviews()
    {
        $supplier = $this->authorizedSupplierForServicePage();
        $supplierId = (int)$supplier['supplier_id'];

        require_once APPROOT . '/models/ReviewModel.php';
        $reviewModel = new ReviewModel();
        $stats   = $reviewModel->getSupplierStats($supplierId);
        $reviews = $reviewModel->getBySupplier($supplierId, 20, 0);

        $this->view('supplier/reviews', [
            'supplier' => $supplier,
            'stats'    => $stats,
            'reviews'  => $reviews,
        ]);
    }

    private function saveSupplierDocumentOrRespond($saved, array &$data, $field, $documentType, $label, $isAjax)
    {
        $fileUrl = $this->uploadService->storeSupplierDocument(
            $_FILES[$field],
            (int)$saved['supplier_id'],
            $data['business_name'],
            $documentType
        );

        if ($fileUrl && $this->supplierProfileModel->saveSupplierDocument((int)$saved['supplier_id'], $fileUrl, $field)) {
            return true;
        }

        $data['submitted'] = true;
        $data['message'] = 'Your supplier information was saved, but the ' . $label . ' could not be uploaded. Please try again.';

        if ($isAjax) {
            $this->jsonResponse([
                'status' => 'error',
                'message' => $data['message'],
            ], 422);
        }

        $this->view('supplier/onboarding', $data);
        return false;
    }

    private function forwardTo($controllerClass, $method, $args = [])
    {
        $controller = new $controllerClass();

        return call_user_func_array([$controller, $method], $args);
    }

    private function suggestCategoriesWithGemini($prompt, $categories)
    {
        $apiKey = defined('GEMINI_API_KEY') ? trim(GEMINI_API_KEY) : '';

        if ($apiKey === '' || !function_exists('curl_init')) {
            return false;
        }

        $categoryList = array_map(function ($category) {
            return [
                'id' => (int)$category['id'],
                'name' => (string)$category['name'],
            ];
        }, $categories);

        $requestText = "Classify this wedding supplier business text. The text can be English, Myanmar, or mixed.\n"
            . "Choose only category IDs from the provided categories. Return categories that genuinely match the business.\n\n"
            . "Supplier text:\n" . $prompt . "\n\n"
            . "Available categories:\n" . json_encode($categoryList, JSON_UNESCAPED_UNICODE);

        $payload = [
            'contents' => [[
                'parts' => [[
                    'text' => $requestText,
                ]],
            ]],
            'generationConfig' => [
                'temperature' => 0.1,
                'responseMimeType' => 'application/json',
                'responseJsonSchema' => [
                    'type' => 'object',
                    'properties' => [
                        'category_ids' => [
                            'type' => 'array',
                            'items' => ['type' => 'integer'],
                            'description' => 'Matching category IDs from the provided list only.',
                        ],
                        'reason' => [
                            'type' => 'string',
                            'description' => 'Brief reason for the category match.',
                        ],
                    ],
                    'required' => ['category_ids', 'reason'],
                ],
            ],
        ];

        $ch = curl_init('https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'x-goog-api-key: ' . $apiKey,
            ],
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_TIMEOUT => 15,
        ]);

        $response = curl_exec($ch);
        $statusCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $statusCode < 200 || $statusCode >= 300) {
            return false;
        }

        $decoded = json_decode($response, true);
        $text = $decoded['candidates'][0]['content']['parts'][0]['text'] ?? '';

        if ($text === '') {
            return false;
        }

        $suggestion = json_decode($text, true);

        return is_array($suggestion) ? $suggestion : false;
    }

    // ────────────────────────── PROFILE ──────────────────────────

    public function profile()
    {
        $userId = $this->currentUserId();
        if (!$userId) {
            redirect('users/login');
        }

        $supplier = $this->supplierProfileModel->getByUserId($userId);
        if (!$supplier) {
            redirect('supplier/onboarding');
        }

        // Split owner name into first/last for the form fields
        $fullName  = trim($supplier['owner_name'] ?? '');
        $nameParts = explode(' ', $fullName, 2);
        $firstName = $nameParts[0] ?? '';
        $lastName  = $nameParts[1] ?? '';

        // Get service count for stats
        $supplierId    = (int)($supplier['supplier_id'] ?? 0);
        $dashboardData = $this->supplierProfileModel->getDashboardData($supplierId);
        $serviceCount  = $dashboardData['stats']['total_services'] ?? 0;
        $totalBookings = $dashboardData['stats']['total_bookings'] ?? 0;
        $avgRating     = $dashboardData['stats']['average_rating'] ?? null;

        $data = [
            // Supplier info
            'supplier_id'   => (int)($supplier['supplier_id'] ?? 0),
            'shop_name'     => $supplier['shop_name'] ?? '',
            'description'   => $supplier['description'] ?? '',
            'status'        => $supplier['status'] ?? 'pending',
            'business_url'  => $supplier['business_url'] ?? '',
            'category_names' => $supplier['category_names'] ?? '',
            'payment_status' => $supplier['payment_status'] ?? 'unpaid',
            'business_license_url' => $supplier['business_license_url'] ?? null,

            // Owner info
            'user_id'    => $userId,
            'name'       => $fullName,
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'email'      => $supplier['owner_email'] ?? $_SESSION['session_email'] ?? '',
            'phone'      => $supplier['owner_phone'] ?? '',
            'address'    => $supplier['owner_address'] ?? '',
            'avatar'     => $_SESSION['session_avatar'] ?? null,
            'joined'     => !empty($supplier['created_at']) ? date('Y-m-d', strtotime($supplier['created_at'])) : '-',

            // Stats
            'service_count'  => $serviceCount,
            'total_bookings' => $totalBookings,
            'avg_rating'     => $avgRating,
        ];

        $this->view('supplier/profile/profile', $data);
    }

    /**
     * Supplier settings page.
     */
    public function settings()
    {
        $userId = $this->currentUserId();
        if (!$userId) {
            redirect('users/login');
        }

        $supplier = $this->supplierProfileModel->getByUserId($userId);
        if (!$supplier) {
            redirect('supplier/onboarding');
        }

        // Parse notification preferences (JSON column or defaults)
        $notifPrefs = [];
        if (!empty($supplier['notification_prefs'])) {
            $decoded = json_decode($supplier['notification_prefs'], true);
            if (is_array($decoded)) {
                $notifPrefs = $decoded;
            }
        }
        $defaults = [
            'new_booking' => true,
            'payment_received' => true,
            'new_review' => true,
            'publish_approved' => true,
        ];
        $notifPrefs = array_merge($defaults, $notifPrefs);

        $data = [
            'supplier_id'          => (int)($supplier['supplier_id'] ?? 0),
            'is_available'         => (int)($supplier['is_available'] ?? 0),
            'auto_accept_bookings' => (int)($supplier['auto_accept_bookings'] ?? 0),
            'min_advance_days'     => (int)($supplier['min_advance_days'] ?? 0),
            'cancellation_policy'  => $supplier['cancellation_policy'] ?? '',
            'bank_account'         => $supplier['bank_account'] ?? '',
            'bank_code'            => $supplier['bank_code'] ?? '',
            'platform_fee'         => (int) get_platform_fee_percent(),
            'notification_prefs'   => $notifPrefs,
        ];

        $this->view('supplier/settings/settings', $data);
    }

    /**
     * JSON endpoint — update supplier settings.
     */
    public function updateSettings()
    {
        header('Content-Type: application/json');

        $userId = $this->currentUserId();
        if (!$userId) {
            echo json_encode(['ok' => false, 'error' => 'Not logged in.']);
            return;
        }

        $supplier = $this->supplierProfileModel->getByUserId($userId);
        if (!$supplier) {
            echo json_encode(['ok' => false, 'error' => 'Supplier not found.']);
            return;
        }

        $supplierId = (int)($supplier['supplier_id'] ?? 0);
        $input = json_decode(file_get_contents('php://input'), true) ?? [];

        $fields = [
            'is_available'         => isset($input['is_available']) ? (int)$input['is_available'] : null,
            'auto_accept_bookings' => isset($input['auto_accept_bookings']) ? (int)$input['auto_accept_bookings'] : null,
            'min_advance_days'     => isset($input['min_advance_days']) ? max(0, (int)$input['min_advance_days']) : null,
            'cancellation_policy'  => isset($input['cancellation_policy']) ? trim($input['cancellation_policy']) : null,
            'bank_account'         => isset($input['bank_account']) ? trim($input['bank_account']) : null,
            'bank_code'            => isset($input['bank_code']) ? trim($input['bank_code']) : null,
            'notification_prefs'   => isset($input['notification_prefs']) ? json_encode($input['notification_prefs']) : null,
        ];

        // Remove nulls (fields not sent)
        $fields = array_filter($fields, fn($v) => $v !== null);

        if (empty($fields)) {
            echo json_encode(['ok' => false, 'error' => 'No data to update.']);
            return;
        }

        try {
            $sets = [];
            $params = [];
            foreach ($fields as $col => $val) {
                $sets[] = "$col = :$col";
                $params[":$col"] = $val;
            }
            $sets[] = 'updated_at = NOW()';
            $sql = 'UPDATE suppliers SET ' . implode(', ', $sets) . ' WHERE supplier_id = :sid';
            $params[':sid'] = $supplierId;

            $this->db->dbquery($sql);
            foreach ($params as $k => $v) {
                $this->db->dbbind($k, $v);
            }
            $this->db->dbexecute();

            echo json_encode(['ok' => true]);
        } catch (\Exception $e) {
            echo json_encode(['ok' => false, 'error' => 'Failed to update settings.']);
        }
    }

    /**
     * JSON endpoint — upload profile photo for supplier.
     */
    public function uploadProfilePhoto()
    {
        header('Content-Type: application/json');

        $userId = $this->currentUserId();
        if (!$userId) {
            echo json_encode(['ok' => false, 'error' => 'Not logged in.']);
            return;
        }

        if (!$this->uploadService->hasUploaded('profile_photo')) {
            echo json_encode(['ok' => false, 'error' => 'No file uploaded.']);
            return;
        }

        $file = $_FILES['profile_photo'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['ok' => false, 'error' => 'Upload error code ' . $file['error']]);
            return;
        }

        $url = $this->uploadService->storeProfilePhoto($file, (int)$userId, 'supplier/avatars');
        if ($url === '') {
            echo json_encode(['ok' => false, 'error' => 'Invalid file. Accepted: JPEG, PNG, WebP (max 5MB).']);
            return;
        }

        // Clean up old photos
        $this->uploadService->removeOldProfilePhotos((int)$userId, $url, 'supplier/avatars');

        // Persist to DB
        $userModel = $this->model('User');
        $userModel->updateAvatar((int)$userId, $url);

        // Update session for sidebar
        $_SESSION['session_avatar'] = $url;

        echo json_encode(['ok' => true, 'url' => $url]);
    }

    /**
     * JSON endpoint — remove supplier profile photo.
     */
    public function removeProfilePhoto()
    {
        header('Content-Type: application/json');

        $userId = $this->currentUserId();
        if (!$userId) {
            echo json_encode(['ok' => false, 'error' => 'Not logged in.']);
            return;
        }

        // Remove files from disk
        $this->uploadService->removeOldProfilePhotos((int)$userId, '', 'supplier/avatars');

        // Clear from DB
        $userModel = $this->model('User');
        $userModel->updateAvatar((int)$userId, '');

        // Clear session
        $_SESSION['session_avatar'] = null;

        echo json_encode(['ok' => true]);
    }

    /**
     * JSON endpoint — update supplier profile.
     * Expects JSON body: { name, email, phone, address, shop_name, description, business_url }
     */
    public function updateProfile()
    {
        header('Content-Type: application/json');

        try {
            $userId = $this->currentUserId();
            if (!$userId) {
                echo json_encode(['ok' => false, 'error' => 'Not logged in.']);
                return;
            }

            $payload = json_decode(file_get_contents('php://input'), true);
            if (!is_array($payload)) {
                echo json_encode(['ok' => false, 'error' => 'Invalid payload.']);
                return;
            }

            $name    = trim((string)($payload['name'] ?? ''));
            $email   = trim((string)($payload['email'] ?? ''));
            $phone   = trim((string)($payload['phone'] ?? ''));
            $address = trim((string)($payload['address'] ?? ''));
            $shopName    = trim((string)($payload['shop_name'] ?? ''));
            $description = trim((string)($payload['description'] ?? ''));
            $businessUrl = trim((string)($payload['business_url'] ?? ''));

            if ($name === '' || $email === '') {
                echo json_encode(['ok' => false, 'error' => 'Name and email are required.']);
                return;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['ok' => false, 'error' => 'Invalid email address.']);
                return;
            }

            $this->supplierProfileModel->updateProfile($userId, [
                'name'         => $name,
                'email'        => $email,
                'phone'        => $phone,
                'address'      => $address,
                'shop_name'    => $shopName,
                'description'  => $description,
                'business_url' => $businessUrl,
            ]);

            // Update session
            $_SESSION['session_name']  = $name;
            $_SESSION['session_email'] = $email;

            echo json_encode(['ok' => true]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'Server error. Please try again.']);
        }
    }

    /**
     * JSON endpoint — change supplier password.
     * Expects JSON body: { current_password, new_password, device? }
     */
    public function updatePassword()
    {
        header('Content-Type: application/json');

        $userId = $this->currentUserId();
        if (!$userId) {
            echo json_encode(['ok' => false, 'error' => 'Not logged in.']);
            return;
        }

        $payload = json_decode(file_get_contents('php://input'), true);
        if (!is_array($payload)) {
            echo json_encode(['ok' => false, 'error' => 'Invalid payload.']);
            return;
        }

        $current = $payload['current_password'] ?? '';
        $newPass = $payload['new_password'] ?? '';
        $device  = trim((string)($payload['device'] ?? ''));

        if ($current === '' || $newPass === '') {
            echo json_encode(['ok' => false, 'error' => 'Both password fields are required.']);
            return;
        }

        if (strlen($newPass) < 8) {
            echo json_encode(['ok' => false, 'error' => 'New password must be at least 8 characters.']);
            return;
        }

        $userModel = $this->model('User');

        if (!$userModel->verifyPassword($userId, $current)) {
            echo json_encode(['ok' => false, 'error' => 'Current password is incorrect.']);
            return;
        }

        $userModel->updatePassword($userId, $newPass);

        // Send email notification
        $deviceInfo = $device ?: ($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown device');
        $emailService = new EmailService();
        $emailService->sendPasswordChangedEmail([
            'name'  => $_SESSION['session_name'] ?? 'Supplier',
            'email' => $_SESSION['session_email'] ?? '',
        ], $deviceInfo);

        echo json_encode(['ok' => true]);
    }

    /**
     * JSON endpoint — self-service delete account (soft-delete).
     * Expects JSON body: { password }
     * Verifies password, soft-deletes the account, destroys the session.
     */
    public function deleteAccount()
    {
        header('Content-Type: application/json');

        $userId = $this->currentUserId();
        if (!$userId) {
            echo json_encode(['ok' => false, 'error' => 'Not logged in.']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['ok' => false, 'error' => 'Invalid request method.']);
            return;
        }

        $payload = json_decode(file_get_contents('php://input'), true);
        if (!is_array($payload)) {
            echo json_encode(['ok' => false, 'error' => 'Invalid payload.']);
            return;
        }

        $password = trim((string)($payload['password'] ?? ''));
        if ($password === '') {
            echo json_encode(['ok' => false, 'error' => 'Password is required.']);
            return;
        }

        $userModel = $this->model('User');

        // Check if this is an OAuth user (no password set)
        $userInfo = $userModel->getuserinfo($_SESSION['session_email'] ?? '');
        $hasPassword = !empty($userInfo['password']);

        if ($hasPassword && !$userModel->verifyPassword($userId, $password)) {
            echo json_encode(['ok' => false, 'error' => 'Password is incorrect.']);
            return;
        }

        $userModel->softDeleteAccount($userId);

        // Destroy session
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();

        echo json_encode(['ok' => true, 'redirect' => URLROOT . '/']);
    }
}
