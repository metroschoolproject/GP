<?php

require_once APPROOT . '/controllers/SupplierControllerSupport.php';

class SupplierNotifications extends SupplierControllerSupport
{
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

    public function notifications()
    {
        $this->view('supplier/notifications', [
            'notifications' => $this->notificationModel->getAll($this->currentUserId(), 80),
            'unreadCount' => $this->notificationModel->getUnreadCount($this->currentUserId()),
            'message' => $_SESSION['supplier_flash'] ?? '',
        ]);
        unset($_SESSION['supplier_flash']);
    }

    public function notification($notificationId = null)
    {
        if (!$notificationId) {
            redirect('supplier/notifications');
        }

        $notification = $this->notificationModel->getById((int)$notificationId, $this->currentUserId());

        if (!$notification) {
            redirect('supplier/notifications');
        }

        $this->notificationModel->markRead((int)$notificationId, $this->currentUserId());
        $referenceType = (string)($notification['reference_type'] ?? '');
        $referenceId = (int)($notification['reference_id'] ?? 0);

        if ($referenceType === 'booking' && $referenceId > 0) {
            redirect('supplier/bookingDetail/' . $referenceId);
        }

        if ($referenceType === 'service' && $referenceId > 0) {
            redirect('supplier/serviceDetail/' . $referenceId);
        }

        redirect('supplier/dashboard');
    }
}
