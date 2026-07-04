<?php

require_once APPROOT . '/controllers/SupplierControllerSupport.php';

class SupplierNotifications extends SupplierControllerSupport
{
    public function notificationsJson()
    {
        $this->jsonResponse([
            'unread_count' => $this->notificationModel->getUnreadCount($this->currentUserId(), true),
            'notifications' => $this->notificationModel->getLatest($this->currentUserId(), 8, true),
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
        $allowedTypes = ['all', 'booking', 'payment', 'approval', 'system'];
        $allowedStates = ['all', 'unread'];
        $requestedType = (string)($_GET['type'] ?? 'all');
        $requestedState = (string)($_GET['state'] ?? 'all');
        $filters = [
            'type' => in_array($requestedType, $allowedTypes, true) ? $requestedType : 'all',
            'state' => in_array($requestedState, $allowedStates, true) ? $requestedState : 'all',
            'search' => trim((string)($_GET['search'] ?? '')),
        ];
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        $userId = $this->currentUserId();
        $totalCount = $this->notificationModel->getAdminInboxCount($userId, $filters);
        $totalPages = max(1, (int)ceil($totalCount / $perPage));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $perPage;

        $this->view('supplier/notifications', [
            'notifications' => $this->notificationModel->getAdminInbox($userId, $filters, $perPage, $offset),
            'stats' => $this->notificationModel->getAdminInboxStats($userId),
            'filters' => $filters,
            'message' => $_SESSION['supplier_flash'] ?? '',
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalCount' => $totalCount,
            'perPage' => $perPage,
        ]);
        unset($_SESSION['supplier_flash']);
    }

    public function markAllNotificationsRead()
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->jsonResponse(['status' => 'error', 'message' => 'Method not allowed.'], 405);
        }

        $this->notificationModel->markAllRead($this->currentUserId());
        $_SESSION['supplier_flash'] = 'All notifications marked as read.';
        redirect('supplier/notifications');
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
