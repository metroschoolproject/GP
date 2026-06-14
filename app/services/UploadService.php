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
        if (!$this->isValidFile($file, ['image/jpeg', 'image/png', 'image/webp'], 6 * 1024 * 1024)) {
            return '';
        }

        $mimeType = $this->mimeType($file);
        $extension = $this->extensionForMime($mimeType);

        if (!$extension) {
            return '';
        }

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
        return is_dir($absoluteDir) || mkdir($absoluteDir, 0755, true);
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
}
