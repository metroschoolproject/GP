<?php

require_once APPROOT . '/controllers/SupplierControllerSupport.php';

class SupplierServiceMedia extends SupplierControllerSupport
{
    public function serviceMediaCreate($serviceId = null)
    {
        $supplier = $this->authorizedSupplierForServiceManagement();
        $serviceId = (int)$serviceId;
        $payload = $this->jsonPayload();

        if ($serviceId <= 0) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Service id is required.'], 422);
        }

        $fileUrl = $this->uploadService->storeServiceImageFromPayload($payload['img'] ?? '', (int)$supplier['supplier_id'], 'media');

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
}
