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
}
