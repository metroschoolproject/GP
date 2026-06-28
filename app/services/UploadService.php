<?php

class UploadService
{
    public function hasUploaded($field)
    {
        return isset($_FILES[$field]) && ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
    }

    public function isValidCoverPhoto($file)
    {
        return $this->isValidFile($file, ['image/jpeg', 'image/png', 'image/webp'], 5 * 1024 * 1024);
    }

    public function isValidBusinessLicense($file)
    {
        return $this->isValidFile($file, ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'], 5 * 1024 * 1024);
    }

    public function storeSupplierDocument($file, $supplierId, $businessName, $documentType)
    {
        $mimeType = $this->mimeType($file);
        $extension = $this->extensionForMime($mimeType, true);

        if (!$extension) {
            return false;
        }

        $supplierFolder = (int)$supplierId . '-' . $this->slugify($businessName);
        $relativeDir = 'uploads/suppliers/' . $supplierFolder . '/documents';
        $absoluteDir = dirname(APPROOT) . '/public/' . $relativeDir;

        if (!$this->ensureDirectory($absoluteDir)) {
            return false;
        }

        $basename = $this->slugify($documentType) . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(4));
        $filename = $basename . '.' . $extension;
        $absolutePath = $absoluteDir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $absolutePath)) {
            return false;
        }

        if (strpos($mimeType, 'image/') === 0) {
            $optimizedFilename = $this->createOptimizedImageVariant($absolutePath, $absoluteDir, $basename, $mimeType, 1280, 720);
            if ($optimizedFilename) {
                return IMG_ROOT . '/' . $relativeDir . '/' . $optimizedFilename;
            }
        }

        return IMG_ROOT . '/' . $relativeDir . '/' . $filename;
    }

    public function storeServiceImageFromPayload($imageData, $supplierId, $type)
    {
        $imageData = (string)$imageData;

        if ($imageData === '' || strpos($imageData, 'data:image/') !== 0) {
            return $imageData;
        }

        if (!preg_match('/^data:image\/(png|jpe?g|webp);base64,(.+)$/i', $imageData, $matches)) {
            return '';
        }

        $extension = strtolower($matches[1]) === 'jpeg' ? 'jpg' : strtolower($matches[1]);
        $binary = base64_decode($matches[2], true);

        if ($binary === false || strlen($binary) > 6 * 1024 * 1024) {
            return '';
        }

        $relativeDir = 'uploads/suppliers/' . (int)$supplierId . '/service-management/' . $this->slugify($type);
        $absoluteDir = dirname(APPROOT) . '/public/' . $relativeDir;

        if (!$this->ensureDirectory($absoluteDir) || !is_writable($absoluteDir)) {
            return '';
        }

        $basename = date('YmdHis') . '-' . bin2hex(random_bytes(4));
        $filename = $basename . '.' . $extension;
        $absolutePath = $absoluteDir . '/' . $filename;

        if (@file_put_contents($absolutePath, $binary) === false) {
            return '';
        }

        $mimeType = 'image/' . ($extension === 'jpg' ? 'jpeg' : $extension);
        $optimizedFilename = $this->createOptimizedImageVariant($absolutePath, $absoluteDir, $basename, $mimeType, 960, 540);

        return IMG_ROOT . '/' . $relativeDir . '/' . ($optimizedFilename ?: $filename);
    }

    public function storePackageImage($file)
    {
        $error = $file['error'] ?? UPLOAD_ERR_NO_FILE;
        if ($error !== UPLOAD_ERR_OK) {
            return '';
        }

        $extension = $this->extensionFromFilename($file['name'] ?? '');
        if (!$extension) {
            return '';
        }

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($extension, $allowedExtensions, true)) {
            return '';
        }

        $mimeType = 'image/' . ($extension === 'jpg' ? 'jpeg' : $extension);

        $relativeDir = 'uploads/admin/packages';
        $absoluteDir = dirname(APPROOT) . '/public/' . $relativeDir;

        if (!$this->ensureDirectory($absoluteDir) || !is_writable($absoluteDir)) {
            return '';
        }

        $basename = date('YmdHis') . '-' . bin2hex(random_bytes(4));
        $filename = $basename . '.' . $extension;
        $absolutePath = $absoluteDir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $absolutePath)) {
            return '';
        }

        $optimizedFilename = $this->createOptimizedImageVariant($absolutePath, $absoluteDir, $basename, $mimeType, 960, 540);

        return IMG_ROOT . '/' . $relativeDir . '/' . ($optimizedFilename ?: $filename);
    }

    /**
     * Validate a package image upload by file extension only.
     * Returns '' if the file is valid.
     */
    public function validatePackageImage($file): string
    {
        $error = $file['error'] ?? UPLOAD_ERR_NO_FILE;

        if ($error === UPLOAD_ERR_NO_FILE) {
            return ''; // No file uploaded — not an error (image is optional)
        }

        if ($error === UPLOAD_ERR_INI_SIZE) {
            $limit = ini_get('upload_max_filesize');
            return "Image exceeds the server upload limit ($limit). Please increase PHP upload_max_filesize.";
        }

        if ($error !== UPLOAD_ERR_OK) {
            return 'The image upload failed. Please try again.';
        }

        $extension = $this->extensionFromFilename($file['name'] ?? '');
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        if (!$extension || !in_array($extension, $allowedExtensions, true)) {
            $ext = $extension ?: strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
            return $ext
                ? "Image format not supported (.$ext). Please use JPG, PNG, or WebP."
                : 'Could not determine image format. Please use JPG, PNG, or WebP.';
        }

        return '';
    }

    public function slugify($value)
    {
        $slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $value), '-'));

        return $slug !== '' ? $slug : 'supplier';
    }

    private function isValidFile($file, $allowedMimeTypes, $maxBytes)
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return false;
        }

        if (($file['size'] ?? 0) > $maxBytes) {
            return false;
        }

        return in_array($this->mimeType($file), $allowedMimeTypes, true);
    }

    /**
     * Extract and normalize a file extension from an uploaded file's original name.
     * Returns lowercase extension without dot, or '' if unrecognised.
     */
    private function extensionFromFilename(string $name): string
    {
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        // Normalize common aliases
        if ($ext === 'jpeg') return 'jpg';
        return $ext;
    }

    private function mimeType($file)
    {
        $finfo = new finfo(FILEINFO_MIME_TYPE);

        return $finfo->file($file['tmp_name']);
    }

    private function extensionForMime($mimeType, $allowPdf = false)
    {
        $extensions = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];

        if ($allowPdf) {
            $extensions['application/pdf'] = 'pdf';
        }

        return $extensions[$mimeType] ?? null;
    }

    private function ensureDirectory($absoluteDir)
    {
        if (!is_dir($absoluteDir)) {
            if (!mkdir($absoluteDir, 0777, true)) {
                return false;
            }
        }
        // Ensure directory and parents are writable (handles dirs created by CLI with 0755)
        $uploadsRoot = dirname(APPROOT) . '/public/uploads';
        $dir = $absoluteDir;
        while ($dir && strpos($dir, $uploadsRoot) === 0 && $dir !== $uploadsRoot) {
            @chmod($dir, 0777);
            $dir = dirname($dir);
        }
        @chmod($uploadsRoot, 0777);
        return true;
    }

    private function createOptimizedImageVariant($sourcePath, $targetDir, $basename, $mimeType, $maxWidth, $maxHeight)
    {
        if (!function_exists('imagewebp') || !function_exists('imagescale')) {
            return null;
        }

        $source = $this->imageResourceFromFile($sourcePath, $mimeType);

        if (!$source) {
            return null;
        }

        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);

        if ($sourceWidth <= 0 || $sourceHeight <= 0) {
            imagedestroy($source);
            return null;
        }

        $scale = min(1, $maxWidth / $sourceWidth, $maxHeight / $sourceHeight);
        $targetWidth = max(1, (int)floor($sourceWidth * $scale));
        $targetHeight = max(1, (int)floor($sourceHeight * $scale));
        $image = $scale < 1 ? imagescale($source, $targetWidth, $targetHeight, IMG_BICUBIC) : $source;

        if (!$image) {
            imagedestroy($source);
            return null;
        }

        $optimizedFilename = $basename . '-optimized.webp';
        $saved = imagewebp($image, $targetDir . '/' . $optimizedFilename, 82);

        if ($image !== $source) {
            imagedestroy($image);
        }
        imagedestroy($source);

        return $saved ? $optimizedFilename : null;
    }

    private function imageResourceFromFile($path, $mimeType)
    {
        if ($mimeType === 'image/jpeg' && function_exists('imagecreatefromjpeg')) {
            return @imagecreatefromjpeg($path);
        }

        if ($mimeType === 'image/png' && function_exists('imagecreatefrompng')) {
            return @imagecreatefrompng($path);
        }

        if ($mimeType === 'image/webp' && function_exists('imagecreatefromwebp')) {
            return @imagecreatefromwebp($path);
        }

        return null;
    }

    /**
     * Upload payment slip for manual verification (KBZ Pay / AYA Bank).
     * Returns relative path or false on failure.
     */
    public function uploadPaymentSlip($file, int $bookingId): string|false
    {
        $mimeType = $this->mimeType($file);
        $extension = $this->extensionForMime($mimeType, true);

        // Allow images and PDFs for payment slips
        $validTypes = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];
        if (!in_array($mimeType, $validTypes, true) || !$extension) {
            return false;
        }

        // Limit file size to 5MB
        if ($file['size'] > 5 * 1024 * 1024) {
            return false;
        }

        $relativeDir = 'uploads/payments/slips/' . date('Y/m');
        $absoluteDir = dirname(APPROOT) . '/public/' . $relativeDir;

        if (!$this->ensureDirectory($absoluteDir)) {
            return false;
        }

        $basename = 'slip-' . $bookingId . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(4));
        $filename = $basename . '.' . $extension;
        $absolutePath = $absoluteDir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $absolutePath)) {
            return false;
        }

        return $relativeDir . '/' . $filename;
    }

    /**
     * Upload payout proof (admin bank transfer screenshot).
     * Accepts JPEG / PNG / WebP / PDF, max 5 MB.
     * Returns relative path from public/ or false on failure.
     */
    public function uploadPayoutProof($file, int $supplierId): string|false
    {
        $mimeType = $this->mimeType($file);
        $extension = $this->extensionForMime($mimeType, true);

        $validTypes = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];
        if (!in_array($mimeType, $validTypes, true) || !$extension) {
            return false;
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            return false;
        }

        $relativeDir = 'uploads/payouts/proofs/' . date('Y/m');
        $absoluteDir = dirname(APPROOT) . '/public/' . $relativeDir;

        if (!$this->ensureDirectory($absoluteDir)) {
            return false;
        }

        $basename = 'payout-' . $supplierId . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(4));
        $filename = $basename . '.' . $extension;
        $absolutePath = $absoluteDir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $absolutePath)) {
            return false;
        }

        return $relativeDir . '/' . $filename;
    }

    /**
     * Store a profile photo.
     * Accepts JPEG / PNG / WebP, max 5 MB.
     * $subDirectory: relative path from public/uploads/, e.g. 'admin/avatars' or 'supplier/avatars'.
     * Returns full IMG_ROOT URL on success, empty string on failure.
     */
    public function storeProfilePhoto($file, int $userId, string $subDirectory = 'admin/avatars'): string
    {
        if (!$this->isValidFile($file, ['image/jpeg', 'image/png', 'image/webp'], 5 * 1024 * 1024)) {
            return '';
        }

        $mimeType  = $this->mimeType($file);
        $extension = $this->extensionForMime($mimeType);

        if (!$extension) {
            return '';
        }

        $relativeDir = 'uploads/' . $subDirectory;
        $absoluteDir = dirname(APPROOT) . '/public/' . $relativeDir;

        if (!$this->ensureDirectory($absoluteDir) || !is_writable($absoluteDir)) {
            return '';
        }

        $basename    = 'avatar-' . $userId . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(4));
        $filename    = $basename . '.' . $extension;
        $absolutePath = $absoluteDir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $absolutePath)) {
            return '';
        }

        // Create an optimized square-cropped variant for avatars (400×400)
        $optimizedFilename = $this->createOptimizedImageVariant(
            $absolutePath, $absoluteDir, $basename, $mimeType, 400, 400
        );

        return IMG_ROOT . '/' . $relativeDir . '/' . ($optimizedFilename ?: $filename);
    }

    /**
     * Remove old avatar files for a user (called before saving a new one).
     * $subDirectory: relative path from public/uploads/, e.g. 'admin/avatars' or 'supplier/avatars'.
     */
    public function removeOldProfilePhotos(int $userId, string $keepUrl = '', string $subDirectory = 'admin/avatars'): void
    {
        $dir = dirname(APPROOT) . '/public/uploads/' . $subDirectory . '/';
        if (!is_dir($dir)) return;

        $pattern = 'avatar-' . $userId . '-';
        $keepBasename = $keepUrl ? basename(parse_url($keepUrl, PHP_URL_PATH) ?: '') : '';

        foreach (glob($dir . $pattern . '*') as $file) {
            if ($keepBasename && basename($file) === $keepBasename) continue;
            @unlink($file);
        }
    }
}
