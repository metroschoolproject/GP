<?php

require_once APPROOT . '/controllers/SupplierControllerSupport.php';

class SupplierServices extends SupplierControllerSupport
{
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

    public function serviceManagementData()
    {
        $supplier = $this->authorizedSupplierForServiceManagement();
        $payload = $this->jsonPayload();
        $tab = ($payload['tab'] ?? 'all') === 'packages' ? 'packages' : (($payload['tab'] ?? 'all') === 'services' ? 'services' : 'all');
        $limit = max(1, min(100, (int)($payload['limit'] ?? self::SERVICE_MANAGEMENT_PAGE_SIZE)));
        $offset = max(0, (int)($payload['offset'] ?? 0));
        $options = [
            'service_limit' => $tab === 'packages' ? 0 : $limit,
            'package_limit' => $tab === 'services' ? 0 : $limit,
            'service_offset' => $tab === 'services' ? $offset : 0,
            'package_offset' => $tab === 'packages' ? $offset : 0,
        ];

        if ($tab === 'all') {
            $options = ['limit' => $limit];
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
