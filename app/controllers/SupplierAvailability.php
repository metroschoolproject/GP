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

        $unpublished = $this->serviceManagementModel->unpublishServiceIfIncomplete((int)$supplier['supplier_id'], $serviceId);

        $this->jsonResponse(['status' => 'success', 'availability' => $availability, 'unpublished' => $unpublished]);
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

    public function venueRoomAvailabilityOverrideSave($serviceId = null)
    {
        $supplier = $this->authorizedSupplierForServiceManagement();
        $serviceId = (int)$serviceId;

        if ($serviceId <= 0) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Service id is required.'], 422);
        }

        $service = $this->serviceManagementModel->saveVenueRoomDateOverride(
            (int)$supplier['supplier_id'],
            $serviceId,
            $this->jsonPayload()
        );

        if (!$service) {
            $this->jsonResponse(['status' => 'error', 'message' => 'Please choose a valid hall and date.'], 422);
        }

        $this->jsonResponse(['status' => 'success', 'item' => $service]);
    }

    public function venueRoomAvailabilityOverrideDelete($serviceId = null, $overrideId = null)
    {
        $supplier = $this->authorizedSupplierForServiceManagement();
        $deleted = $this->serviceManagementModel->deleteVenueRoomDateOverride(
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

    public function allServicesCapacityPreview()
    {
        $supplier = $this->authorizedSupplierForServiceManagement();
        $payload = $this->jsonPayload();
        $capacity = $this->serviceManagementModel->fetchAllServicesCapacity(
            (int)$supplier['supplier_id'],
            $payload['date'] ?? ''
        );

        $this->jsonResponse(['status' => 'success', 'capacity' => $capacity]);
    }
}
