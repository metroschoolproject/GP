<?php

class Supplier extends Controller
{
    private $supplierProfileModel;
    private $paymentModel;
    private $notificationModel;
    private $serviceManagementModel;

    public function __construct()
    {
        $this->supplierProfileModel = $this->model('SupplierProfile');
        $this->paymentModel = $this->model('Payment');
        $this->notificationModel = $this->model('Notification');
        $this->serviceManagementModel = $this->model('SupplierServiceManager');
    }

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
            'message' => ''
        ];

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $data = [
                'email' => htmlspecialchars(trim($_POST['email'] ?? ($_SESSION['pending_register_email'] ?? '')), ENT_QUOTES, 'UTF-8'),
                'business_name' => htmlspecialchars(trim($_POST['business_name'] ?? ''), ENT_QUOTES, 'UTF-8'),
                'business_description' => htmlspecialchars(trim($_POST['business_description'] ?? ''), ENT_QUOTES, 'UTF-8'),
                'phone' => htmlspecialchars(trim($_POST['phone'] ?? ''), ENT_QUOTES, 'UTF-8'),
                'business_address' => htmlspecialchars(trim($_POST['business_address'] ?? ''), ENT_QUOTES, 'UTF-8'),
                'category_prompt' => htmlspecialchars(trim($_POST['category_prompt'] ?? ''), ENT_QUOTES, 'UTF-8'),
                'category_ids' => array_map('intval', $_POST['category_ids'] ?? []),
                'category_source' => 'manual',
                'business_url' => htmlspecialchars(trim($_POST['business_url'] ?? ''), ENT_QUOTES, 'UTF-8'),
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
                !$this->hasUploadedCoverPhoto() ||
                !$this->hasUploadedBusinessLicense()
            ) {
                $data['submitted'] = false;
                $data['message'] = 'Please fill all required supplier information.';
            } elseif (!filter_var(htmlspecialchars_decode($data['business_url'], ENT_QUOTES), FILTER_VALIDATE_URL)) {
                $data['submitted'] = false;
                $data['message'] = 'Please enter a valid business URL.';
            } elseif (!$this->supplierProfileModel->areValidCategories($data['category_ids'])) {
                $data['submitted'] = false;
                $data['message'] = 'Please choose at least one valid business category.';
            } elseif (!$this->isValidCoverPhoto($_FILES['cover_photo'])) {
                $data['submitted'] = false;
                $data['message'] = 'Please upload a valid cover photo under 5MB.';
            } elseif (!$this->isValidBusinessLicense($_FILES['business_license'])) {
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
                    $coverUrl = $this->storeSupplierDocument(
                        $_FILES['cover_photo'],
                        (int)$saved['supplier_id'],
                        $data['business_name'],
                        'cover-photo'
                    );

                    if (
                        !$coverUrl ||
                        !$this->supplierProfileModel->saveSupplierDocument((int)$saved['supplier_id'], $coverUrl, 'cover_photo')
                    ) {
                        $data['submitted'] = true;
                        $data['message'] = 'Your supplier information was saved, but the cover photo could not be uploaded. Please try again.';

                        if ($isAjax) {
                            $this->jsonResponse([
                                'status' => 'error',
                                'message' => $data['message']
                            ], 422);
                        }

                        $this->view('supplier/onboarding', $data);
                        return;
                    }

                    $licenseUrl = $this->storeSupplierDocument(
                        $_FILES['business_license'],
                        (int)$saved['supplier_id'],
                        $data['business_name'],
                        'business-license'
                    );

                    if (
                        !$licenseUrl ||
                        !$this->supplierProfileModel->saveSupplierDocument((int)$saved['supplier_id'], $licenseUrl, 'business_license')
                    ) {
                        $data['submitted'] = true;
                        $data['message'] = 'Your supplier information was saved, but the business license could not be uploaded. Please try again.';

                        if ($isAjax) {
                            $this->jsonResponse([
                                'status' => 'error',
                                'message' => $data['message']
                            ], 422);
                        }

                        $this->view('supplier/onboarding', $data);
                        return;
                    }

                    if ($isAjax) {
                        $this->notificationModel->notifyAdmins(
                            'New supplier application',
                            $data['business_name'] . ' submitted a supplier application.',
                            'approval',
                            'supplier',
                            (int)$saved['supplier_id']
                        );

                        $this->jsonResponse([
                            'status' => 'success',
                            'message' => 'Your supplier application was submitted.',
                            'redirect' => URLROOT . '/supplier/pending'
                        ]);
                    }

                    $this->notificationModel->notifyAdmins(
                        'New supplier application',
                        $data['business_name'] . ' submitted a supplier application.',
                        'approval',
                        'supplier',
                        (int)$saved['supplier_id']
                    );

                    redirect('supplier/pending');
                }

                $data['submitted'] = true;
                $data['message'] = 'We could not save the supplier information. Please sign in again and try one more time.';
            }

            if ($isAjax) {
                $this->jsonResponse([
                    'status' => 'error',
                    'message' => $data['message'] ?: 'Please check your supplier information and try again.'
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

    private function hasUploadedCoverPhoto()
    {
        return isset($_FILES['cover_photo']) && ($_FILES['cover_photo']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
    }

    private function hasUploadedBusinessLicense()
    {
        return isset($_FILES['business_license']) && ($_FILES['business_license']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
    }

    private function isValidCoverPhoto($file)
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return false;
        }

        if (($file['size'] ?? 0) > 5 * 1024 * 1024) {
            return false;
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        return in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp'], true);
    }

    private function isValidBusinessLicense($file)
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return false;
        }

        if (($file['size'] ?? 0) > 5 * 1024 * 1024) {
            return false;
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        return in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'], true);
    }

    private function storeCoverPhoto($file, $supplierId, $businessName, $serviceId)
    {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        $extensions = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];
        $extension = $extensions[$mimeType] ?? null;

        if (!$extension) {
            return false;
        }

        $supplierFolder = $supplierId . '-' . $this->slugify($businessName);
        $relativeDir = 'uploads/suppliers/' . $supplierFolder . '/services/' . $serviceId . '/cover';
        $absoluteDir = dirname(APPROOT) . '/public/' . $relativeDir;

        if (!is_dir($absoluteDir) && !mkdir($absoluteDir, 0755, true)) {
            return false;
        }

        $filename = 'cover-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $extension;
        $absolutePath = $absoluteDir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $absolutePath)) {
            return false;
        }

        return IMG_ROOT . '/' . $relativeDir . '/' . $filename;
    }

    private function storeSupplierDocument($file, $supplierId, $businessName, $documentType)
    {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        $extensions = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'application/pdf' => 'pdf',
        ];
        $extension = $extensions[$mimeType] ?? null;

        if (!$extension) {
            return false;
        }

        $supplierFolder = $supplierId . '-' . $this->slugify($businessName);
        $relativeDir = 'uploads/suppliers/' . $supplierFolder . '/documents';
        $absoluteDir = dirname(APPROOT) . '/public/' . $relativeDir;

        if (!is_dir($absoluteDir) && !mkdir($absoluteDir, 0755, true)) {
            return false;
        }

        $filename = $this->slugify($documentType) . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $extension;
        $absolutePath = $absoluteDir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $absolutePath)) {
            return false;
        }

        return IMG_ROOT . '/' . $relativeDir . '/' . $filename;
    }

    private function slugify($value)
    {
        $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $value), '-'));

        return $slug !== '' ? $slug : 'supplier';
    }

    public function notificationsJson()
    {
        $this->jsonResponse([
            'unread_count' => $this->notificationModel->getUnreadCount($this->currentUserId()),
            'notifications' => $this->notificationModel->getLatest($this->currentUserId(), 8),
        ]);
    }

    public function markNotificationRead($notificationId = null)
    {
        if (!$notificationId) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Notification id is required.'], 422);
        }

        $this->notificationModel->markRead((int)$notificationId, $this->currentUserId());
        $this->jsonResponse(['status' => 'success']);
    }

    private function currentUserId()
    {
        return isset($_SESSION['session_uid']) ? (int)$_SESSION['session_uid'] : null;
    }

    private function jsonResponse($payload, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($payload);
        exit;
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
            'email' => $_SESSION['session_email'] ?? $_SESSION['pending_register_email'] ?? ''
        ]);
    }

    public function dashboard()
    {
        $userId = $_SESSION['session_uid'] ?? $_SESSION['pending_register_user_id'] ?? null;

        if (!$userId) {
            redirect('users/login');
        }

        $supplier = $this->supplierProfileModel->getByUserId($userId);

        if (!$supplier) {
            redirect('supplier/onboarding');
        }

        $status = strtolower($supplier['status'] ?? '');

        if ($status === 'pending') {
            redirect('supplier/pending');
        }

        if (!in_array($status, ['approved', 'verified'], true)) {
            $this->view('supplier/dashboard_locked', [
                'supplier' => $supplier,
                'payment' => null,
                'lockState' => 'profile_not_approved'
            ]);
            return;
        }

        $payment = $this->paymentModel->getLatestSupplierFeePayment((int)$supplier['supplier_id']);
        $hasPaid = strtolower($supplier['payment_status'] ?? '') === 'paid' ||
            $this->paymentModel->hasSuccessfulSupplierFeePayment((int)$supplier['supplier_id']);

        if (!$hasPaid) {
            $this->view('supplier/dashboard_locked', [
                'supplier' => $supplier,
                'payment' => $payment,
                'lockState' => $payment && strtolower($payment['status'] ?? '') === 'pending'
                    ? 'payment_pending'
                    : 'payment_required'
            ]);
            return;
        }

        $this->view('supplier/dashboard', [
            'supplier' => $supplier,
            'payment' => $payment,
            'dashboardData' => $this->supplierProfileModel->getDashboardData((int)$supplier['supplier_id']),
        ]);
    }

    public function services()
    {
        $userId = $_SESSION['session_uid'] ?? $_SESSION['pending_register_user_id'] ?? null;

        if (!$userId) {
            redirect('users/login');
        }

        $supplier = $this->supplierProfileModel->getByUserId($userId);

        if (!$supplier) {
            redirect('supplier/onboarding');
        }

        $status = strtolower($supplier['status'] ?? '');

        if ($status === 'pending') {
            redirect('supplier/pending');
        }

        if (!in_array($status, ['approved', 'verified'], true)) {
            $this->view('supplier/dashboard_locked', [
                'supplier' => $supplier,
                'payment' => null,
                'lockState' => 'profile_not_approved'
            ]);
            return;
        }

        $payment = $this->paymentModel->getLatestSupplierFeePayment((int)$supplier['supplier_id']);
        $hasPaid = strtolower($supplier['payment_status'] ?? '') === 'paid' ||
            $this->paymentModel->hasSuccessfulSupplierFeePayment((int)$supplier['supplier_id']);

        if (!$hasPaid) {
            $this->view('supplier/dashboard_locked', [
                'supplier' => $supplier,
                'payment' => $payment,
                'lockState' => $payment && strtolower($payment['status'] ?? '') === 'pending'
                    ? 'payment_pending'
                    : 'payment_required'
            ]);
            return;
        }

        $this->view('supplier/service_management', [
            'supplier' => $supplier,
            'payment' => $payment,
            'dashboardData' => $this->supplierProfileModel->getDashboardData((int)$supplier['supplier_id']),
            'serviceManagementData' => $this->serviceManagementModel->getInitialData((int)$supplier['supplier_id']),
        ]);
    }

    public function serviceManagementData()
    {
        $supplier = $this->authorizedSupplierForServiceManagement();
        $this->jsonResponse([
            'status' => 'success',
            'data' => $this->serviceManagementModel->getInitialData((int)$supplier['supplier_id']),
        ]);
    }

    public function serviceDetail($serviceId = null)
    {
        $supplier = $this->authorizedSupplierForServicePage();
        $serviceId = (int)$serviceId;

        if ($serviceId <= 0) {
            redirect('supplier/services');
        }

        $service = $this->serviceManagementModel->getServiceDetail((int)$supplier['supplier_id'], $serviceId);

        if (!$service) {
            redirect('supplier/services');
        }

        $this->view('supplier/service_detail', [
            'supplier' => $supplier,
            'service' => $service,
            'dashboardData' => $this->supplierProfileModel->getDashboardData((int)$supplier['supplier_id']),
        ]);
    }

    public function serviceCreate()
    {
        $supplier = $this->authorizedSupplierForServiceManagement();
        $data = $this->servicePayload((int)$supplier['supplier_id'], 'service');

        if ($data['name'] === '' || (float)$data['price'] < 0) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Service name and price are required.'], 422);
        }

        try {
            $service = $this->serviceManagementModel->createService((int)$supplier['supplier_id'], $data);
        } catch (Throwable $e) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Could not save service. ' . $e->getMessage()], 500);
        }

        if (!$service) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Service was saved but could not be loaded.'], 500);
        }

        $this->jsonResponse(['status' => 'success', 'item' => $service]);
    }

    public function serviceUpdate($serviceId = null)
    {
        $supplier = $this->authorizedSupplierForServiceManagement();
        $serviceId = (int)$serviceId;

        if ($serviceId <= 0) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Service id is required.'], 422);
        }

        $data = $this->servicePayload((int)$supplier['supplier_id'], 'service');
        try {
            $service = $this->serviceManagementModel->updateService((int)$supplier['supplier_id'], $serviceId, $data);
        } catch (Throwable $e) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Could not update service. ' . $e->getMessage()], 500);
        }

        if (!$service) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Service not found.'], 404);
        }

        $this->jsonResponse(['status' => 'success', 'item' => $service]);
    }

    public function serviceDelete($serviceId = null)
    {
        $supplier = $this->authorizedSupplierForServiceManagement();
        $serviceId = (int)$serviceId;

        if ($serviceId <= 0) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Service id is required.'], 422);
        }

        try {
            $deleted = $this->serviceManagementModel->deleteService((int)$supplier['supplier_id'], $serviceId);
        } catch (Throwable $e) {
            $this->jsonResponse(['status' => 'error', 'message' => 'This service is already used by bookings and cannot be deleted.'], 409);
        }

        $this->jsonResponse(['status' => $deleted ? 'success' : 'error']);
    }

    public function serviceStatus($serviceId = null)
    {
        $supplier = $this->authorizedSupplierForServiceManagement();
        $payload = $this->jsonPayload();
        $service = $this->serviceManagementModel->setServiceStatus(
            (int)$supplier['supplier_id'],
            (int)$serviceId,
            ($payload['status'] ?? 'active') === 'active'
        );

        if (!$service) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Service not found.'], 404);
        }

        $this->jsonResponse(['status' => 'success', 'item' => $service]);
    }

    public function serviceMediaCreate($serviceId = null)
    {
        $supplier = $this->authorizedSupplierForServiceManagement();
        $serviceId = (int)$serviceId;
        $payload = $this->jsonPayload();

        if ($serviceId <= 0) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Service id is required.'], 422);
        }

        $fileUrl = $this->storeServiceImageFromPayload($payload['img'] ?? '', (int)$supplier['supplier_id'], 'media');

        if ($fileUrl === '') {
            $this->jsonResponse(['status' => 'error', 'message' => 'Please upload a valid image.'], 422);
        }

        $media = $this->serviceManagementModel->addServiceMedia((int)$supplier['supplier_id'], $serviceId, $fileUrl, 'image');

        if (!$media) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Service not found.'], 404);
        }

        $this->jsonResponse(['status' => 'success', 'media' => $media]);
    }

    public function serviceMediaDelete($serviceId = null, $mediaId = null)
    {
        $supplier = $this->authorizedSupplierForServiceManagement();
        $serviceId = (int)$serviceId;
        $mediaId = (int)$mediaId;

        if ($serviceId <= 0 || $mediaId <= 0) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Service and media ids are required.'], 422);
        }

        $deleted = $this->serviceManagementModel->deleteServiceMedia((int)$supplier['supplier_id'], $serviceId, $mediaId);

        $this->jsonResponse(['status' => $deleted ? 'success' : 'error']);
    }

    public function serviceAvailabilitySave($serviceId = null)
    {
        $supplier = $this->authorizedSupplierForServiceManagement();
        $serviceId = (int)$serviceId;

        if ($serviceId <= 0) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Service id is required.'], 422);
        }

        $availability = $this->serviceManagementModel->saveWeeklyAvailability(
            (int)$supplier['supplier_id'],
            $serviceId,
            $this->jsonPayload()
        );

        if (!$availability) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Service not found.'], 404);
        }

        $this->jsonResponse(['status' => 'success', 'availability' => $availability]);
    }

    public function serviceAvailabilityOverrideSave($serviceId = null)
    {
        $supplier = $this->authorizedSupplierForServiceManagement();
        $serviceId = (int)$serviceId;

        if ($serviceId <= 0) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Service id is required.'], 422);
        }

        $availability = $this->serviceManagementModel->saveDateOverride(
            (int)$supplier['supplier_id'],
            $serviceId,
            $this->jsonPayload()
        );

        if (!$availability) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Please choose a valid date.'], 422);
        }

        $this->jsonResponse(['status' => 'success', 'availability' => $availability]);
    }

    public function serviceAvailabilityOverrideDelete($serviceId = null, $overrideId = null)
    {
        $supplier = $this->authorizedSupplierForServiceManagement();
        $deleted = $this->serviceManagementModel->deleteDateOverride(
            (int)$supplier['supplier_id'],
            (int)$serviceId,
            (int)$overrideId
        );

        $this->jsonResponse(['status' => $deleted ? 'success' : 'error']);
    }

    public function serviceAvailabilityPreview($serviceId = null)
    {
        $supplier = $this->authorizedSupplierForServiceManagement();
        $payload = $this->jsonPayload();
        $preview = $this->serviceManagementModel->previewSlots(
            (int)$supplier['supplier_id'],
            (int)$serviceId,
            $payload['date'] ?? ''
        );

        $this->jsonResponse(['status' => 'success', 'preview' => $preview]);
    }

    public function packageCreate()
    {
        $supplier = $this->authorizedSupplierForServiceManagement();
        $data = $this->servicePayload((int)$supplier['supplier_id'], 'package');

        if ($data['name'] === '' || (float)$data['price'] < 0) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Package name and price are required.'], 422);
        }

        try {
            $package = $this->serviceManagementModel->createPackage((int)$supplier['supplier_id'], $data);
        } catch (Throwable $e) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Could not save package. ' . $e->getMessage()], 500);
        }

        if (!$package) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Package was saved but could not be loaded.'], 500);
        }

        $this->jsonResponse(['status' => 'success', 'item' => $package]);
    }

    public function packageUpdate($packageId = null)
    {
        $supplier = $this->authorizedSupplierForServiceManagement();
        $packageId = (int)$packageId;

        if ($packageId <= 0) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Package id is required.'], 422);
        }

        $data = $this->servicePayload((int)$supplier['supplier_id'], 'package');
        try {
            $package = $this->serviceManagementModel->updatePackage((int)$supplier['supplier_id'], $packageId, $data);
        } catch (Throwable $e) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Could not update package. ' . $e->getMessage()], 500);
        }

        if (!$package) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Package not found.'], 404);
        }

        $this->jsonResponse(['status' => 'success', 'item' => $package]);
    }

    public function packageDelete($packageId = null)
    {
        $supplier = $this->authorizedSupplierForServiceManagement();
        $packageId = (int)$packageId;

        if ($packageId <= 0) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Package id is required.'], 422);
        }

        $deleted = $this->serviceManagementModel->deletePackage((int)$supplier['supplier_id'], $packageId);
        $this->jsonResponse(['status' => $deleted ? 'success' : 'error']);
    }

    public function packageStatus($packageId = null)
    {
        $supplier = $this->authorizedSupplierForServiceManagement();
        $payload = $this->jsonPayload();
        $package = $this->serviceManagementModel->setPackageStatus(
            (int)$supplier['supplier_id'],
            (int)$packageId,
            ($payload['status'] ?? 'active') === 'active'
        );

        if (!$package) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Package not found.'], 404);
        }

        $this->jsonResponse(['status' => 'success', 'item' => $package]);
    }

    private function authorizedSupplierForServiceManagement()
    {
        return $this->authorizedSupplierForServicePage(true);
    }

    private function authorizedSupplierForServicePage($json = false)
    {
        $userId = $_SESSION['session_uid'] ?? $_SESSION['pending_register_user_id'] ?? null;

        if (!$userId) {
            if ($json) {
                $this->jsonResponse(['status' => 'error', 'message' => 'Please login again.'], 401);
            }
            redirect('users/login');
        }

        $supplier = $this->supplierProfileModel->getByUserId($userId);

        if (!$supplier) {
            if ($json) {
                $this->jsonResponse(['status' => 'error', 'message' => 'Supplier profile not found.'], 404);
            }
            redirect('supplier/onboarding');
        }

        $status = strtolower($supplier['status'] ?? '');
        $hasPaid = strtolower($supplier['payment_status'] ?? '') === 'paid' ||
            $this->paymentModel->hasSuccessfulSupplierFeePayment((int)$supplier['supplier_id']);

        if (!in_array($status, ['approved', 'verified'], true) || !$hasPaid) {
            if ($json) {
                $this->jsonResponse(['status' => 'error', 'message' => 'Supplier dashboard is locked.'], 403);
            }
            $this->view('supplier/dashboard_locked', [
                'supplier' => $supplier,
                'payment' => null,
                'lockState' => 'profile_not_approved'
            ]);
            exit;
        }

        return $supplier;
    }

    private function jsonPayload()
    {
        $payload = json_decode(file_get_contents('php://input'), true);

        return is_array($payload) ? $payload : [];
    }

    private function servicePayload($supplierId, $type)
    {
        $payload = $this->jsonPayload();
        $payload['name'] = htmlspecialchars(trim((string)($payload['name'] ?? '')), ENT_QUOTES, 'UTF-8');
        $payload['desc'] = htmlspecialchars(trim((string)($payload['desc'] ?? $payload['description'] ?? '')), ENT_QUOTES, 'UTF-8');
        $payload['price'] = max(0, (float)($payload['price'] ?? 0));
        $payload['status'] = ($payload['status'] ?? 'active') === 'inactive' ? 'inactive' : 'active';
        $payload['img'] = $this->storeServiceImageFromPayload($payload['img'] ?? '', $supplierId, $type);

        if (isset($payload['categories']) && is_array($payload['categories'])) {
            $payload['categories'] = array_values(array_filter(array_map(function ($category) {
                return htmlspecialchars(trim((string)$category), ENT_QUOTES, 'UTF-8');
            }, $payload['categories'])));
        }

        return $payload;
    }

    private function storeServiceImageFromPayload($imageData, $supplierId, $type)
    {
        $imageData = (string)$imageData;

        if ($imageData === '' || strpos($imageData, 'data:image/') !== 0) {
            return $imageData;
        }

        if (!preg_match('/^data:image\/(png|jpe?g|webp);base64,(.+)$/i', $imageData, $matches)) {
            return '';
        }

        $extension = strtolower($matches[1]) === 'jpeg' ? 'jpg' : strtolower($matches[1]);
        $binary = base64_decode($matches[2], true);

        if ($binary === false || strlen($binary) > 6 * 1024 * 1024) {
            return '';
        }

        $relativeDir = 'uploads/suppliers/' . (int)$supplierId . '/service-management/' . $this->slugify($type);
        $absoluteDir = dirname(APPROOT) . '/public/' . $relativeDir;

        if (!is_dir($absoluteDir) && !mkdir($absoluteDir, 0755, true)) {
            return '';
        }

        $filename = date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $extension;

        if (file_put_contents($absoluteDir . '/' . $filename, $binary) === false) {
            return '';
        }

        return IMG_ROOT . '/' . $relativeDir . '/' . $filename;
    }
}
