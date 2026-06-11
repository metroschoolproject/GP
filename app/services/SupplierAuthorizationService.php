<?php

class SupplierAuthorizationService
{
    private $supplierProfileModel;
    private $paymentModel;

    public function __construct($supplierProfileModel, $paymentModel)
    {
        $this->supplierProfileModel = $supplierProfileModel;
        $this->paymentModel = $paymentModel;
    }

    public function currentUserId()
    {
        $userId = $_SESSION['session_uid'] ?? $_SESSION['pending_register_user_id'] ?? null;

        return $userId ? (int)$userId : null;
    }

    public function supplierForCurrentUser()
    {
        $userId = $this->currentUserId();

        return $userId ? $this->supplierProfileModel->getByUserId($userId) : null;
    }

    public function dashboardAccess()
    {
        $userId = $this->currentUserId();

        if (!$userId) {
            return [
                'allowed' => false,
                'redirect' => 'users/login',
                'jsonStatus' => 401,
                'jsonMessage' => 'Please login again.',
            ];
        }

        $supplier = $this->supplierProfileModel->getByUserId($userId);

        if (!$supplier) {
            return [
                'allowed' => false,
                'redirect' => 'supplier/onboarding',
                'jsonStatus' => 404,
                'jsonMessage' => 'Supplier profile not found.',
            ];
        }

        $status = strtolower($supplier['status'] ?? '');

        if ($status === 'pending') {
            return [
                'allowed' => false,
                'redirect' => 'supplier/pending',
                'supplier' => $supplier,
                'jsonStatus' => 403,
                'jsonMessage' => 'Supplier dashboard is locked.',
            ];
        }

        if (!in_array($status, ['approved', 'verified'], true)) {
            return [
                'allowed' => false,
                'locked' => true,
                'supplier' => $supplier,
                'payment' => null,
                'lockState' => 'profile_not_approved',
                'jsonStatus' => 403,
                'jsonMessage' => 'Supplier dashboard is locked.',
            ];
        }

        $payment = $this->paymentModel->getLatestSupplierFeePayment((int)$supplier['supplier_id']);
        $hasPaid = strtolower($supplier['payment_status'] ?? '') === 'paid' ||
            $this->paymentModel->hasSuccessfulSupplierFeePayment((int)$supplier['supplier_id']);

        if (!$hasPaid) {
            return [
                'allowed' => false,
                'locked' => true,
                'supplier' => $supplier,
                'payment' => $payment,
                'lockState' => $payment && strtolower($payment['status'] ?? '') === 'pending'
                    ? 'payment_pending'
                    : 'payment_required',
                'jsonStatus' => 403,
                'jsonMessage' => 'Supplier dashboard is locked.',
            ];
        }

        return [
            'allowed' => true,
            'supplier' => $supplier,
            'payment' => $payment,
        ];
    }
}
