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
        $userId = $_SESSION['pending_register_user_id'] ?? $_SESSION['session_uid'] ?? null;
        $supplier = $userId ? $this->supplierProfileModel->getByUserId($userId) : null;
        $isAjax = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' && $supplier && strtolower($supplier['status'] ?? '') === 'pending') {
            redirect('supplier/pending');
        }

        $data = [
            'email' => $_SESSION['pending_register_email'] ?? $_SESSION['session_email'] ?? '',
            'categories' => $this->supplierProfileModel->getCategories(),
            'submitted' => false,
            'message' => ''
        ];

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $data = [
                'email' => htmlspecialchars(trim($_POST['email'] ?? ($_SESSION['pending_register_email'] ?? '')), ENT_QUOTES, 'UTF-8'),
                'business_name' => htmlspecialchars(trim($_POST['business_name'] ?? ''), ENT_QUOTES, 'UTF-8'),
                'business_description' => htmlspecialchars(trim($_POST['business_description'] ?? ''), ENT_QUOTES, 'UTF-8'),
                'phone' => htmlspecialchars(trim($_POST['phone'] ?? ''), ENT_QUOTES, 'UTF-8'),
                'business_address' => htmlspecialchars(trim($_POST['business_address'] ?? ''), ENT_QUOTES, 'UTF-8'),
                'category_id' => (int)($_POST['category_id'] ?? 0),
                'service_name' => htmlspecialchars(trim($_POST['service_name'] ?? ''), ENT_QUOTES, 'UTF-8'),
                'service_description' => htmlspecialchars(trim($_POST['service_description'] ?? ''), ENT_QUOTES, 'UTF-8'),
                'service_price' => trim($_POST['service_price'] ?? ''),
                'business_url' => htmlspecialchars(trim($_POST['business_url'] ?? ''), ENT_QUOTES, 'UTF-8'),
                'thumbnail_url' => null,
                'agreement_accepted' => !empty($_POST['agreement_accepted']),
                'agreement_accepted_at' => date('Y-m-d H:i:s'),
                'agreement_version' => 'supplier-v1',
                'categories' => $this->supplierProfileModel->getCategories(),
            ];

            if (
                $data['email'] === '' ||
                $data['business_name'] === '' ||
                $data['business_description'] === '' ||
                $data['phone'] === '' ||
                $data['business_address'] === '' ||
                $data['category_id'] <= 0 ||
                $data['service_name'] === '' ||
                $data['service_description'] === '' ||
                $data['service_price'] === '' ||
                $data['business_url'] === '' ||
                !is_numeric($data['service_price']) ||
                (float)$data['service_price'] < 0 ||
                !$data['agreement_accepted'] ||
                !$this->hasUploadedCoverPhoto()
            ) {
                $data['submitted'] = false;
                $data['message'] = 'Please fill all required supplier information.';
            } elseif (!filter_var(htmlspecialchars_decode($data['business_url'], ENT_QUOTES), FILTER_VALIDATE_URL)) {
                $data['submitted'] = false;
                $data['message'] = 'Please enter a valid business URL.';
            } elseif (!$this->supplierProfileModel->isValidCategory($data['category_id'])) {
                $data['submitted'] = false;
                $data['message'] = 'Please choose a valid service category.';
            } elseif (!$this->isValidCoverPhoto($_FILES['cover_photo'])) {
                $data['submitted'] = false;
                $data['message'] = 'Please upload a valid cover photo under 5MB.';
            } else {
                $data['service_price'] = number_format((float)$data['service_price'], 2, '.', '');
                $data['user_id'] = $userId;
                $saved = $this->supplierProfileModel->save($data);
                $_SESSION['supplier_profile'] = $data;

                if ($saved) {
                    $coverUrl = $this->storeCoverPhoto(
                        $_FILES['cover_photo'],
                        (int)$saved['supplier_id'],
                        $data['business_name'],
                        (int)$saved['service_id']
                    );

                    if (
                        !$coverUrl ||
                        !$this->supplierProfileModel->updateServiceThumbnail((int)$saved['service_id'], $coverUrl) ||
                        !$this->supplierProfileModel->addServiceMedia((int)$saved['service_id'], $coverUrl, 'image')
                    ) {
                        $data['submitted'] = true;
                        $data['message'] = 'Your supplier information was saved, but the cover photo could not be uploaded. Please try again.';

                        if ($isAjax) {
                            $this->jsonResponse([
                                'status' => 'error',
                                'message' => $data['message']
                            ], 422);
                        }

                        $this->view('supplier/onboarding', $data);
                        return;
                    }

                    if ($isAjax) {
                        $this->jsonResponse([
                            'status' => 'success',
                            'message' => 'Your supplier application was submitted.',
                            'redirect' => URLROOT . '/supplier/pending'
                        ]);
                    }

                    redirect('supplier/pending');
                }

                $data['submitted'] = true;
                $data['message'] = 'We could not save the supplier information. Please sign in again and try one more time.';
            }

            if ($isAjax) {
                $this->jsonResponse([
                    'status' => 'error',
                    'message' => $data['message'] ?: 'Please check your supplier information and try again.'
                ], 422);
            }
        }

        $this->view('supplier/onboarding', $data);
    }

    private function hasUploadedCoverPhoto()
    {
        return isset($_FILES['cover_photo']) && ($_FILES['cover_photo']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
    }

    private function isValidCoverPhoto($file)
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return false;
        }

        if (($file['size'] ?? 0) > 5 * 1024 * 1024) {
            return false;
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        return in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp'], true);
    }

    private function storeCoverPhoto($file, $supplierId, $businessName, $serviceId)
    {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        $extensions = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];
        $extension = $extensions[$mimeType] ?? null;

        if (!$extension) {
            return false;
        }

        $supplierFolder = $supplierId . '-' . $this->slugify($businessName);
        $relativeDir = 'uploads/suppliers/' . $supplierFolder . '/services/' . $serviceId . '/cover';
        $absoluteDir = dirname(APPROOT) . '/public/' . $relativeDir;

        if (!is_dir($absoluteDir) && !mkdir($absoluteDir, 0755, true)) {
            return false;
        }

        $filename = 'cover-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $extension;
        $absolutePath = $absoluteDir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $absolutePath)) {
            return false;
        }

        return IMG_ROOT . '/' . $relativeDir . '/' . $filename;
    }

    private function slugify($value)
    {
        $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $value), '-'));

        return $slug !== '' ? $slug : 'supplier';
    }

    private function jsonResponse($payload, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($payload);
        exit;
    }

    public function pending()
    {
        $userId = $_SESSION['session_uid'] ?? $_SESSION['pending_register_user_id'] ?? null;

        if (!$userId) {
            redirect('users/login');
        }

        $supplier = $this->supplierProfileModel->getByUserId($userId);

        if (!$supplier) {
            redirect('supplier/onboarding');
        }

        $this->view('supplier/pending', [
            'supplier' => $supplier,
            'email' => $_SESSION['session_email'] ?? $_SESSION['pending_register_email'] ?? ''
        ]);
    }
}
