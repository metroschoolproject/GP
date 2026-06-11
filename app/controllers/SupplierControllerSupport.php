<?php

require_once APPROOT . '/traits/JsonResponseTrait.php';
require_once APPROOT . '/services/SupplierAuthorizationService.php';
require_once APPROOT . '/services/UploadService.php';

abstract class SupplierControllerSupport extends Controller
{
    use JsonResponseTrait;

    protected const SERVICE_MANAGEMENT_PAGE_SIZE = 24;

    protected $supplierProfileModel;
    protected $paymentModel;
    protected $notificationModel;
    protected $serviceManagementModel;
    protected $authorizationService;
    protected $uploadService;

    public function __construct()
    {
        $this->supplierProfileModel = $this->model('SupplierProfile');
        $this->paymentModel = $this->model('Payment');
        $this->notificationModel = $this->model('Notification');
        $this->serviceManagementModel = $this->model('SupplierServiceManager');
        $this->authorizationService = new SupplierAuthorizationService($this->supplierProfileModel, $this->paymentModel);
        $this->uploadService = new UploadService();
    }

    protected function currentUserId()
    {
        return isset($_SESSION['session_uid']) ? (int)$_SESSION['session_uid'] : null;
    }

    protected function jsonPayload()
    {
        $payload = json_decode(file_get_contents('php://input'), true);

        return is_array($payload) ? $payload : [];
    }

    protected function authorizedSupplierForServiceManagement()
    {
        return $this->authorizedSupplierForServicePage(true);
    }

    protected function authorizedSupplierForServicePage($json = false)
    {
        $access = $this->authorizationService->dashboardAccess();

        if (!empty($access['allowed'])) {
            return $access['supplier'];
        }

        if ($json) {
            $this->jsonResponse([
                'status' => 'error',
                'message' => $access['jsonMessage'] ?? 'Supplier dashboard is locked.',
            ], $access['jsonStatus'] ?? 403);
        }

        if (!empty($access['redirect'])) {
            redirect($access['redirect']);
        }

        $this->view('supplier/dashboard_locked', [
            'supplier' => $access['supplier'],
            'payment' => $access['payment'] ?? null,
            'lockState' => $access['lockState'] ?? 'profile_not_approved',
        ]);
        exit;
    }

    protected function servicePayload($supplierId, $type)
    {
        $payload = $this->jsonPayload();
        $payload['name'] = htmlspecialchars(trim((string)($payload['name'] ?? '')), ENT_QUOTES, 'UTF-8');
        $payload['desc'] = htmlspecialchars(trim((string)($payload['desc'] ?? $payload['description'] ?? '')), ENT_QUOTES, 'UTF-8');
        $payload['price'] = max(0, (float)($payload['price'] ?? 0));
        $payload['status'] = ($payload['status'] ?? 'active') === 'inactive' ? 'inactive' : 'active';
        $payload['img'] = $this->uploadService->storeServiceImageFromPayload($payload['img'] ?? '', $supplierId, $type);

        if (isset($payload['categories']) && is_array($payload['categories'])) {
            $payload['categories'] = array_values(array_filter(array_map(function ($category) {
                return htmlspecialchars(trim((string)$category), ENT_QUOTES, 'UTF-8');
            }, $payload['categories'])));
        }

        return $payload;
    }
}
