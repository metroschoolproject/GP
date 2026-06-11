<?php

require_once APPROOT . '/controllers/SupplierControllerSupport.php';

class SupplierAvailability extends SupplierControllerSupport
{
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

    public function serviceSlotReserve($slotId = null)
    {
        $this->authorizedSupplierForServiceManagement();
        $reserved = $this->serviceManagementModel->reserveServiceSlot((int)$slotId);

        if (!$reserved) {
            $this->jsonResponse(['status' => 'error', 'message' => 'This slot is already full or unavailable.'], 409);
        }

        $this->jsonResponse(['status' => 'success']);
    }

    public function serviceSlotRelease($slotId = null)
    {
        $this->authorizedSupplierForServiceManagement();
        $released = $this->serviceManagementModel->releaseServiceSlot((int)$slotId);

        $this->jsonResponse(['status' => $released ? 'success' : 'error']);
    }
}
