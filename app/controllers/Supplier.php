<?php

require_once APPROOT . '/controllers/SupplierControllerSupport.php';
require_once APPROOT . '/controllers/SupplierServices.php';
require_once APPROOT . '/controllers/SupplierServiceMedia.php';
require_once APPROOT . '/controllers/SupplierAvailability.php';
require_once APPROOT . '/controllers/SupplierNotifications.php';

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
                !$this->uploadService->hasUploaded('cover_photo') ||
                !$this->uploadService->hasUploaded('business_license')
            ) {
                $data['submitted'] = false;
                $data['message'] = 'Please fill all required supplier information.';
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

    public function serviceManagementData()
    {
        return $this->forwardTo(SupplierServices::class, __FUNCTION__, func_get_args());
    }

    public function serviceDetail($serviceId = null)
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

    public function serviceAvailabilityPreview($serviceId = null)
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
}
