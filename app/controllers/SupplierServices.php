<?php

require_once APPROOT . '/controllers/SupplierControllerSupport.php';

class SupplierServices extends SupplierControllerSupport
{
    private const SERVICE_PUBLISH_REQUEST_COOLDOWN_SECONDS = 7200;

    public function services()
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
        $payment = $access['payment'];

        $this->view('supplier/service_management', [
            'supplier' => $supplier,
            'payment' => $payment,
            'dashboardData' => $this->supplierProfileModel->getDashboardData((int)$supplier['supplier_id']),
            'serviceManagementData' => $this->serviceManagementModel->getInitialData((int)$supplier['supplier_id'], [
                'limit' => self::SERVICE_MANAGEMENT_PAGE_SIZE,
            ]),
        ]);
    }

    public function calendar()
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

        $this->view('supplier/calendar', [
            'supplier' => $supplier,
            'payment' => $access['payment'],
            'dashboardData' => $this->supplierProfileModel->getDashboardData((int)$supplier['supplier_id']),
            'services' => $this->serviceManagementModel->getServices((int)$supplier['supplier_id']),
            'allCapacityUrl' => URLROOT . '/supplier/allServicesCapacityPreview',
        ]);
    }

    public function serviceManagementData()
    {
        $supplier = $this->authorizedSupplierForServiceManagement();
        $payload = $this->jsonPayload();
        $tab = ($payload['tab'] ?? 'all') === 'packages' ? 'packages' : (($payload['tab'] ?? 'all') === 'services' ? 'services' : 'all');
        $limit = max(1, min(100, (int)($payload['limit'] ?? self::SERVICE_MANAGEMENT_PAGE_SIZE)));
        $offset = max(0, (int)($payload['offset'] ?? 0));
        $search = trim((string)($payload['search'] ?? ''));
        $options = [
            'service_limit' => $tab === 'packages' ? 0 : $limit,
            'package_limit' => $tab === 'services' ? 0 : $limit,
            'service_offset' => $tab === 'services' ? $offset : 0,
            'package_offset' => $tab === 'packages' ? $offset : 0,
            'search' => $search,
        ];

        if ($tab === 'all') {
            $options = ['limit' => $limit, 'search' => $search];
        }

        $this->jsonResponse([
            'status' => 'success',
            'data' => $this->serviceManagementModel->getInitialData((int)$supplier['supplier_id'], $options),
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

    public function serviceCalendar($serviceId = null)
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

        $this->view('supplier/service_calendar', [
            'supplier' => $supplier,
            'service' => $service,
            'dashboardData' => $this->supplierProfileModel->getDashboardData((int)$supplier['supplier_id']),
        ]);
    }

    public function serviceCalendarData($serviceId = null)
    {
        $supplier = $this->authorizedSupplierForServiceManagement();
        $serviceId = (int)$serviceId;
        $month = trim((string)($_GET['month'] ?? date('Y-m')));

        if ($serviceId <= 0) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Service id is required.'], 422);
        }

        $calendar = $this->serviceManagementModel->getServiceCalendarMonth(
            (int)$supplier['supplier_id'],
            $serviceId,
            $month
        );

        if (!$calendar) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Service not found.'], 404);
        }

        $this->jsonResponse(['status' => 'success', 'calendar' => $calendar]);
    }

    public function serviceCreate()
    {
        $supplier = $this->authorizedSupplierForServiceManagement();
        $data = $this->servicePayload((int)$supplier['supplier_id'], 'service');
        $this->validateDefaultEventTime($data);
        $data['status'] = 'inactive';

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
        try {
            $supplier = $this->authorizedSupplierForServiceManagement();
            $serviceId = (int)$serviceId;

            if ($serviceId <= 0) {
                $this->jsonResponse(['status' => 'error', 'message' => 'Service id is required.'], 422);
            }

            $data = $this->servicePayload((int)$supplier['supplier_id'], 'service');
            $this->validateDefaultEventTime($data);
            $existingService = $this->serviceManagementModel->getServiceDetail((int)$supplier['supplier_id'], $serviceId);

            if (($existingService['status'] ?? 'inactive') === 'inactive' && ($data['status'] ?? 'inactive') === 'active') {
                $data['status'] = 'inactive';
            }

            // Preserve published status: don't let the update accidentally
            // downgrade a published service to inactive via the payload.
            $wasActive = ($existingService['status'] ?? 'inactive') !== 'inactive';

            $service = $this->serviceManagementModel->updateService((int)$supplier['supplier_id'], $serviceId, $data);
        } catch (Throwable $e) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Could not update service. ' . $e->getMessage()], 500);
        }

        if (!$service) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Service not found.'], 404);
        }

        // Only auto-unpublish inactive services. Published (active/pending_review)
        // services stay published — they were already approved. The publish-request
        // flow handles validation for new publish attempts.
        if (!$wasActive && $this->serviceManagementModel->unpublishServiceIfIncomplete((int)$supplier['supplier_id'], $serviceId)) {
            $service['status'] = 'inactive';
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
        } catch (DomainException $e) {
            $this->jsonResponse(['status' => 'error', 'message' => $e->getMessage()], 409);
        } catch (Throwable $e) {
            error_log('Supplier service delete failed: ' . $e->getMessage());
            $this->jsonResponse(['status' => 'error', 'message' => 'Could not delete the service. Please try again.'], 500);
        }

        if (!$deleted) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Service not found.'], 404);
        }

        $this->jsonResponse(['status' => 'success']);
    }

    private function validateDefaultEventTime(array &$data): void
    {
        $start = trim((string)($data['default_start_time'] ?? ''));
        $end = trim((string)($data['default_end_time'] ?? ''));

        if ($start === '' && $end === '') {
            $data['default_start_time'] = null;
            $data['default_end_time'] = null;
            return;
        }

        if ($start === '' || $end === '') {
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'Add both default event start and end time, or leave both blank.',
            ], 422);
        }

        if (!$this->isValidClockTime($start) || !$this->isValidClockTime($end)) {
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'Default event time must use a valid time format.',
            ], 422);
        }

        $start = strlen($start) === 5 ? $start . ':00' : $start;
        $end = strlen($end) === 5 ? $end . ':00' : $end;

        if ($start >= $end) {
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'Default event end time must be later than the start time.',
            ], 422);
        }

        $data['default_start_time'] = $start;
        $data['default_end_time'] = $end;
    }

    private function isValidClockTime(string $time): bool
    {
        return (bool)preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d(?::[0-5]\d)?$/', $time);
    }

    public function serviceStatus($serviceId = null)
    {
        $supplier = $this->authorizedSupplierForServiceManagement();
        $payload = $this->jsonPayload();
        $makeActive = ($payload['status'] ?? 'active') === 'active';
        $serviceId = (int)$serviceId;
        $supplierId = (int)$supplier['supplier_id'];
        $existingService = $this->serviceManagementModel->getServiceDetail($supplierId, $serviceId);

        if (!$existingService) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Service not found.'], 404);
        }

        if ($makeActive) {
            $readiness = $this->serviceManagementModel->servicePublishReadiness($supplierId, $serviceId);

            if (empty($readiness['ready'])) {
                $this->jsonResponse([
                    'status' => 'error',
                    'message' => 'Complete the service setup before publishing.',
                    'missing' => $readiness['missing'],
                ], 422);
            }

            $cooldownMessage = $this->servicePublishCooldownMessage($serviceId, $existingService);

            if ($cooldownMessage !== '') {
                $this->jsonResponse(['status' => 'error', 'message' => $cooldownMessage], 429);
            }
        }

        $service = $this->serviceManagementModel->setServiceStatus(
            $supplierId,
            $serviceId,
            false,
            $makeActive ? 'pending_review' : 'draft'
        );

        if (!$service) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Service not found.'], 404);
        }

        if ($makeActive) {
            $supplierName = trim((string)($supplier['business_name'] ?? $supplier['shop_name'] ?? $supplier['name'] ?? 'A supplier'));
            $serviceName = trim((string)($service['name'] ?? 'a service'));

            $this->notificationModel->notifyAdmins(
                'Service publish request',
                $supplierName . ' requested publishing for "' . $serviceName . '".',
                'approval',
                'service',
                $serviceId
            );
            $this->notificationModel->notifyUser(
                $this->currentUserId(),
                'Publish request sent',
                'Your request to publish "' . $serviceName . '" was sent to admin.',
                'approval',
                'service',
                $serviceId
            );
        }

        $this->jsonResponse([
            'status' => 'success',
            'item' => $service,
            'message' => $makeActive
                ? 'Publish request sent to admin. Your service will stay hidden until it is approved.'
                : 'Service unpublished.',
        ]);
    }

    public function servicePublishRequest($serviceId = null)
    {
        $supplier = $this->authorizedSupplierForServiceManagement();
        $serviceId = (int)$serviceId;

        if ($serviceId <= 0) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Service id is required.'], 422);
        }

        $readiness = $this->serviceManagementModel->servicePublishReadiness((int)$supplier['supplier_id'], $serviceId);

        if (!$readiness) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Service not found.'], 404);
        }

        if (empty($readiness['ready'])) {
            $this->jsonResponse([
                'status' => 'error',
                'message' => 'Complete the service setup before requesting publish: ' . implode(' ', $readiness['missing']),
                'missing' => $readiness['missing'],
            ], 422);
        }

        $service = $readiness['service'];
        $cooldownMessage = $this->servicePublishCooldownMessage($serviceId, $service);

        if ($cooldownMessage !== '') {
            $this->jsonResponse(['status' => 'error', 'message' => $cooldownMessage], 429);
        }

        $this->serviceManagementModel->setServiceStatus((int)$supplier['supplier_id'], $serviceId, false, 'pending_review');
        $supplierName = trim((string)($supplier['business_name'] ?? $supplier['shop_name'] ?? $supplier['name'] ?? 'A supplier'));
        $serviceName = trim((string)($service['name'] ?? 'a service'));

        $this->notificationModel->notifyAdmins(
            'Service publish request',
            $supplierName . ' requested publishing for "' . $serviceName . '".',
            'approval',
            'service',
            $serviceId
        );
        $this->notificationModel->notifyUser(
            $this->currentUserId(),
            'Publish request sent',
            'Your request to publish "' . $serviceName . '" was sent to admin.',
            'approval',
            'service',
            $serviceId
        );

        $this->jsonResponse([
            'status' => 'success',
            'message' => 'Publish request sent to admin. Your service will stay hidden until it is approved.',
        ]);
    }

    public function servicePublishStatus($serviceId = null)
    {
        $supplier = $this->authorizedSupplierForServiceManagement();
        $serviceId = (int)$serviceId;

        if ($serviceId <= 0) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Service id is required.'], 422);
        }

        $service = $this->serviceManagementModel->getServiceDetail((int)$supplier['supplier_id'], $serviceId);

        if (!$service) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Service not found.'], 404);
        }

        $this->jsonResponse([
            'status' => 'success',
            'service_status' => $service['status'] ?? 'inactive',
            'is_live' => ($service['status'] ?? 'inactive') === 'active',
        ]);
    }

    private function servicePublishCooldownMessage($serviceId, array $service = [])
    {
        // Skip cooldown if the service was previously approved and is now back
        // in draft (e.g. supplier edited it after approval, triggering auto-unpublish).
        if (!empty($service) && ($service['is_active'] ?? 0) == 0) {
            return '';
        }

        $latest = $this->notificationModel->getLatestForReference('approval', 'service', (int)$serviceId);

        if (empty($latest['created_at'])) {
            return '';
        }

        $createdAt = strtotime((string)$latest['created_at']);

        if (!$createdAt) {
            return '';
        }

        $remainingSeconds = self::SERVICE_PUBLISH_REQUEST_COOLDOWN_SECONDS - (time() - $createdAt);

        if ($remainingSeconds <= 0) {
            return '';
        }

        $remainingMinutes = (int)ceil($remainingSeconds / 60);
        $hours = intdiv($remainingMinutes, 60);
        $minutes = $remainingMinutes % 60;
        $wait = $hours > 0
            ? $hours . ' hour' . ($hours === 1 ? '' : 's') . ($minutes > 0 ? ' ' . $minutes . ' minute' . ($minutes === 1 ? '' : 's') : '')
            : $minutes . ' minute' . ($minutes === 1 ? '' : 's');

        return 'You already sent a publish request for this service. Please wait ' . $wait . ' before requesting again.';
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
}
