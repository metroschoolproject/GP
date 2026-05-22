<?php

class Supplier extends Controller
{
    private $supplierProfileModel;

    public function __construct()
    {
        $this->supplierProfileModel = $this->model('SupplierProfile');
    }

    public function onboarding()
    {
        $data = [
            'email' => $_SESSION['pending_register_email'] ?? '',
            'submitted' => false,
            'message' => ''
        ];

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $data = [
                'email' => htmlspecialchars(trim($_POST['email'] ?? ($_SESSION['pending_register_email'] ?? '')), ENT_QUOTES, 'UTF-8'),
                'business_name' => htmlspecialchars(trim($_POST['business_name'] ?? ''), ENT_QUOTES, 'UTF-8'),
                'service_category' => htmlspecialchars(trim($_POST['service_category'] ?? ''), ENT_QUOTES, 'UTF-8'),
                'phone' => htmlspecialchars(trim($_POST['phone'] ?? ''), ENT_QUOTES, 'UTF-8'),
                'location' => htmlspecialchars(trim($_POST['location'] ?? ''), ENT_QUOTES, 'UTF-8'),
                'description' => htmlspecialchars(trim($_POST['description'] ?? ''), ENT_QUOTES, 'UTF-8'),
            ];

            if (
                $data['email'] === '' ||
                $data['business_name'] === '' ||
                $data['service_category'] === '' ||
                $data['phone'] === '' ||
                $data['location'] === ''
            ) {
                $data['submitted'] = false;
                $data['message'] = 'Please fill all required supplier information.';
            } else {
                $data['user_id'] = $_SESSION['pending_register_user_id'] ?? null;
                $saved = $this->supplierProfileModel->save($data);
                $_SESSION['supplier_profile'] = $data;
                $data['submitted'] = true;
                $data['message'] = $saved
                    ? 'Your supplier information was received. Admin will review it soon.'
                    : 'We could not save the supplier information. Please sign in again and try one more time.';
            }
        }

        $this->view('supplier/onboarding', $data);
    }
}
