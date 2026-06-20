<?php

require_once APPROOT . '/services/EmailService.php';

class Main extends Controller
{
    public function __construct()
    {
    }   

    public function home()
    {
        $catalogModel = $this->model('CustomerServiceCatalog');
        $packageModel = $this->model('PlatformPackage');

        $this->view('main/index', [
            'serviceCategories' => $catalogModel->getCategories(),
            'featuredPackages' => $packageModel->getFeaturedPackages(3),
        ]);
    }

    public function service()
    {
        require_once APPROOT . '/controllers/CustomerServices.php';
        $controller = new CustomerServices();
        $controller->service();
    }

    public function profile()
    {
        $userModel = $this->model('User');
        $email = $_SESSION['session_email'] ?? '';
        $user = $userModel->getuserinfo($email);

        $fullName  = trim($user['name'] ?? $_SESSION['session_name'] ?? '');
        $nameParts = explode(' ', $fullName, 2);
        $firstName = $nameParts[0] ?? '';
        $lastName  = $nameParts[1] ?? '';

        $this->view('main/profile', [
            'name'       => $fullName,
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'email'      => $email,
            'phone'      => $user['phone'] ?? '',
            'avatar'     => $_SESSION['session_avatar'] ?? $user['avatar'] ?? null,
            'joined'     => !empty($user['created_at']) ? date('Y-m-d', strtotime($user['created_at'])) : '-',
            'lastLogin'  => !empty($user['last_login']) ? date('Y-m-d h:i A', strtotime($user['last_login'])) : '-',
            'isOauth'    => !empty($user['google_id']) || !empty($user['facebook_id']),
            'hasPassword' => !empty($user['password']),
        ]);
    }

    /**
     * JSON endpoint — upload profile photo for customer.
     */
    public function uploadProfilePhoto()
    {
        header('Content-Type: application/json');

        $userId = $_SESSION['session_uid'] ?? 0;
        if (!$userId) {
            echo json_encode(['ok' => false, 'error' => 'Not logged in.']);
            return;
        }

        $uploadService = new UploadService();

        if (!$uploadService->hasUploaded('profile_photo')) {
            echo json_encode(['ok' => false, 'error' => 'No file uploaded.']);
            return;
        }

        $file = $_FILES['profile_photo'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['ok' => false, 'error' => 'Upload error code ' . $file['error']]);
            return;
        }

        $url = $uploadService->storeProfilePhoto($file, (int)$userId);
        if ($url === '') {
            echo json_encode(['ok' => false, 'error' => 'Invalid file. Accepted: JPEG, PNG, WebP (max 5MB).']);
            return;
        }

        $uploadService->removeOldProfilePhotos((int)$userId, $url);

        $userModel = $this->model('User');
        $userModel->updateAvatar((int)$userId, $url);
        $_SESSION['session_avatar'] = $url;

        echo json_encode(['ok' => true, 'url' => $url]);
    }

    /**
     * JSON endpoint — remove profile photo for customer.
     */
    public function removeProfilePhoto()
    {
        header('Content-Type: application/json');

        $userId = $_SESSION['session_uid'] ?? 0;
        if (!$userId) {
            echo json_encode(['ok' => false, 'error' => 'Not logged in.']);
            return;
        }

        $uploadService = new UploadService();
        $uploadService->removeOldProfilePhotos((int)$userId);

        $userModel = $this->model('User');
        $userModel->updateAvatar((int)$userId, '');
        $_SESSION['session_avatar'] = null;

        echo json_encode(['ok' => true]);
    }

    /**
     * JSON endpoint — update customer personal information.
     */
    public function updateProfile()
    {
        header('Content-Type: application/json');

        $userId = $_SESSION['session_uid'] ?? 0;
        if (!$userId) {
            echo json_encode(['ok' => false, 'error' => 'Not logged in.']);
            return;
        }

        $payload = json_decode(file_get_contents('php://input'), true);
        if (!is_array($payload)) {
            echo json_encode(['ok' => false, 'error' => 'Invalid payload.']);
            return;
        }

        $name  = trim((string)($payload['name'] ?? ''));
        $email = trim((string)($payload['email'] ?? ''));
        $phone = trim((string)($payload['phone'] ?? ''));

        if ($name === '' || $email === '') {
            echo json_encode(['ok' => false, 'error' => 'Name and email are required.']);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['ok' => false, 'error' => 'Invalid email address.']);
            return;
        }

        $userModel = $this->model('User');
        $userModel->updateProfile($userId, [
            'name'  => $name,
            'email' => $email,
            'phone' => $phone,
        ]);

        // Update session
        $_SESSION['session_name']  = $name;
        $_SESSION['session_email'] = $email;

        echo json_encode(['ok' => true]);
    }

    /**
     * JSON endpoint — change customer password.
     */
    /**
     * JSON endpoint — update customer password.
     * Accepts JSON: { current_password, new_password, is_oauth, device }
     * For OAuth users (is_oauth=true), current_password is not required.
     */
    public function updatePassword()
    {
        header('Content-Type: application/json');

        $userId = $_SESSION['session_uid'] ?? 0;
        if (!$userId) {
            echo json_encode(['ok' => false, 'error' => 'Not logged in.']);
            return;
        }

        $payload = json_decode(file_get_contents('php://input'), true);
        if (!is_array($payload)) {
            echo json_encode(['ok' => false, 'error' => 'Invalid payload.']);
            return;
        }

        $current = $payload['current_password'] ?? '';
        $newPass = $payload['new_password'] ?? '';
        $isOauth = !empty($payload['is_oauth']);
        $device  = trim((string)($payload['device'] ?? ''));

        if ($newPass === '') {
            echo json_encode(['ok' => false, 'error' => 'New password is required.']);
            return;
        }
        if (strlen($newPass) < 8) {
            echo json_encode(['ok' => false, 'error' => 'New password must be at least 8 characters.']);
            return;
        }

        $userModel = $this->model('User');

        // Skip current-password check for OAuth users setting a password for the first time
        if (!$isOauth) {
            if ($current === '') {
                echo json_encode(['ok' => false, 'error' => 'Current password is required.']);
                return;
            }
            if (!$userModel->verifyPassword($userId, $current)) {
                echo json_encode(['ok' => false, 'error' => 'Current password is incorrect.']);
                return;
            }
        }

        $userModel->updatePassword($userId, $newPass);

        // Send email notification
        $userInfo = $userModel->getuserinfo($_SESSION['session_email'] ?? '');
        $deviceInfo = $device ?: ($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown device');
        $emailService = new EmailService();
        $emailService->sendPasswordChangedEmail([
            'name'  => $userInfo['name'] ?? $_SESSION['session_name'] ?? '',
            'email' => $_SESSION['session_email'] ?? '',
        ], $deviceInfo);

        echo json_encode(['ok' => true]);
    }

    // ═══════════════════════════════════════════════════════════
    //  WISHLIST
    // ═══════════════════════════════════════════════════════════

    /**
     * Wishlist page — full management UI.
     */
    public function wishlist()
    {
        $userId = $_SESSION['session_uid'] ?? 0;
        if (!$userId) {
            redirect('users/auth?redirect=' . urlencode('main/wishlist'));
            return;
        }

        $wishlistModel = $this->model('WishlistModel');
        $collectionId  = !empty($_GET['collection']) ? (int)$_GET['collection'] : null;
        $data = $wishlistModel->getUserWishlist((int)$userId, $collectionId);

        $this->view('main/wishlist', array_merge($data, [
            'activeCollection' => $collectionId,
            'wishlistCount'    => $wishlistModel->getWishlistCount((int)$userId),
        ]));
    }

    /**
     * JSON — toggle a favorite item (add/remove).
     * POST body: { item_type: 'service'|'package', item_id: int, collection_id: ?int }
     */
    public function toggleWishlist()
    {
        header('Content-Type: application/json');

        $userId = $_SESSION['session_uid'] ?? 0;
        if (!$userId) {
            echo json_encode(['ok' => false, 'error' => 'Login required.']);
            return;
        }

        $payload = json_decode(file_get_contents('php://input'), true);
        $itemType     = (string)($payload['item_type'] ?? 'service');
        $itemId       = (int)($payload['item_id'] ?? 0);
        $collectionId = isset($payload['collection_id']) && $payload['collection_id'] !== null
            ? (int)$payload['collection_id'] : null;

        if (!in_array($itemType, ['service', 'package', 'supplier_package'], true) || $itemId <= 0) {
            echo json_encode(['ok' => false, 'error' => 'Invalid item.']);
            return;
        }

        $wishlistModel = $this->model('WishlistModel');
        $result = $wishlistModel->toggle((int)$userId, $itemType, $itemId, $collectionId);
        $result['ok']    = true;
        $result['count'] = $wishlistModel->getWishlistCount((int)$userId);

        echo json_encode($result);
    }

    /**
     * JSON — create a new collection.
     * POST body: { name: string }
     */
    public function collectionCreate()
    {
        header('Content-Type: application/json');

        $userId = $_SESSION['session_uid'] ?? 0;
        if (!$userId) {
            echo json_encode(['ok' => false, 'error' => 'Login required.']);
            return;
        }

        $payload = json_decode(file_get_contents('php://input'), true);
        $name = trim((string)($payload['name'] ?? ''));

        if ($name === '') {
            echo json_encode(['ok' => false, 'error' => 'Collection name is required.']);
            return;
        }

        $wishlistModel = $this->model('WishlistModel');
        $id = $wishlistModel->createCollection((int)$userId, $name);

        if ($id === -1) {
            echo json_encode(['ok' => false, 'error' => 'Maximum 20 collections reached.']);
            return;
        }
        if ($id === -2) {
            echo json_encode(['ok' => false, 'error' => 'A collection with that name already exists.']);
            return;
        }

        echo json_encode(['ok' => true, 'id' => $id, 'name' => $name]);
    }

    /**
     * JSON — rename a collection.
     * POST body: { collection_id: int, name: string }
     */
    public function collectionRename()
    {
        header('Content-Type: application/json');

        $userId = $_SESSION['session_uid'] ?? 0;
        if (!$userId) {
            echo json_encode(['ok' => false, 'error' => 'Login required.']);
            return;
        }

        $payload = json_decode(file_get_contents('php://input'), true);
        $collectionId = (int)($payload['collection_id'] ?? 0);
        $name = trim((string)($payload['name'] ?? ''));

        if ($collectionId <= 0 || $name === '') {
            echo json_encode(['ok' => false, 'error' => 'Invalid request.']);
            return;
        }

        $wishlistModel = $this->model('WishlistModel');
        $ok = $wishlistModel->renameCollection($collectionId, (int)$userId, $name);
        echo json_encode(['ok' => $ok]);
    }

    /**
     * JSON — delete a collection.
     * POST body: { collection_id: int }
     */
    public function collectionDelete()
    {
        header('Content-Type: application/json');

        $userId = $_SESSION['session_uid'] ?? 0;
        if (!$userId) {
            echo json_encode(['ok' => false, 'error' => 'Login required.']);
            return;
        }

        $payload = json_decode(file_get_contents('php://input'), true);
        $collectionId = (int)($payload['collection_id'] ?? 0);

        if ($collectionId <= 0) {
            echo json_encode(['ok' => false, 'error' => 'Invalid collection.']);
            return;
        }

        $wishlistModel = $this->model('WishlistModel');
        $ok = $wishlistModel->deleteCollection($collectionId, (int)$userId);
        echo json_encode(['ok' => $ok]);
    }

    /**
     * JSON — move a favorite item to a different collection.
     * POST body: { favorite_id: int, collection_id: ?int }
     */
    public function moveToCollection()
    {
        header('Content-Type: application/json');

        $userId = $_SESSION['session_uid'] ?? 0;
        if (!$userId) {
            echo json_encode(['ok' => false, 'error' => 'Login required.']);
            return;
        }

        $payload = json_decode(file_get_contents('php://input'), true);
        $favoriteId   = (int)($payload['favorite_id'] ?? 0);
        $collectionId = isset($payload['collection_id']) && $payload['collection_id'] !== null
            ? (int)$payload['collection_id'] : null;

        if ($favoriteId <= 0) {
            echo json_encode(['ok' => false, 'error' => 'Invalid favorite.']);
            return;
        }

        $wishlistModel = $this->model('WishlistModel');
        $ok = $wishlistModel->moveToCollection($favoriteId, $collectionId, (int)$userId);
        echo json_encode(['ok' => $ok]);
    }

    /**
     * JSON — add or update a note on a favorite item.
     * POST body: { favorite_id: int, note: string }
     */
    public function addNote()
    {
        header('Content-Type: application/json');

        $userId = $_SESSION['session_uid'] ?? 0;
        if (!$userId) {
            echo json_encode(['ok' => false, 'error' => 'Login required.']);
            return;
        }

        $payload = json_decode(file_get_contents('php://input'), true);
        $favoriteId = (int)($payload['favorite_id'] ?? 0);
        $note = (string)($payload['note'] ?? '');

        if ($favoriteId <= 0) {
            echo json_encode(['ok' => false, 'error' => 'Invalid favorite.']);
            return;
        }

        $wishlistModel = $this->model('WishlistModel');
        $ok = $wishlistModel->addNote($favoriteId, (int)$userId, $note);
        echo json_encode(['ok' => $ok]);
    }

}
