<?php

class Upload
{
    public const ALLOWED_EXT  = ['jpg', 'jpeg', 'png', 'webp'];
    public const ALLOWED_MIME = ['image/jpeg', 'image/png', 'image/webp'];
    public const MAX_SIZE     = 2097152;

    public static function unggahGambar(?array $file, string $uploadDir): array
    {
        if (!isset($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return ['ok' => true, 'filename' => null, 'error' => null];
        }

        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            return ['ok' => false, 'filename' => null, 'error' => 'Gagal mengunggah foto produk. Silakan coba lagi.'];
        }

        if (($file['size'] ?? 0) > self::MAX_SIZE) {
            return ['ok' => false, 'filename' => null, 'error' => 'Ukuran foto maksimal 2 MB.'];
        }

        $originalName = basename((string)($file['name'] ?? ''));
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (!in_array($ext, self::ALLOWED_EXT, true)) {
            return ['ok' => false, 'filename' => null, 'error' => 'Format foto tidak valid. Gunakan file JPG, JPEG, PNG, atau WEBP.'];
        }

        if (function_exists('finfo_open') && !empty($file['tmp_name'])) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo) {
                $mime = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);
                if ($mime !== false && !in_array($mime, self::ALLOWED_MIME, true)) {
                    return ['ok' => false, 'filename' => null, 'error' => 'Tipe file foto tidak diizinkan. Gunakan gambar JPG, PNG, atau WEBP.'];
                }
            }
        }

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', $originalName);
        $newFilename = time() . '_' . $safeName;
        $targetPath = rtrim($uploadDir, '/\\') . DIRECTORY_SEPARATOR . $newFilename;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            return ['ok' => false, 'filename' => null, 'error' => 'Gagal menyimpan file foto ke server.'];
        }

        return ['ok' => true, 'filename' => $newFilename, 'error' => null];
    }

    public static function hapusGambar(?string $filename, string $uploadDir): void
    {
        $clean = trim((string)$filename);
        if ($clean === '') {
            return;
        }
        $path = rtrim($uploadDir, '/\\') . DIRECTORY_SEPARATOR . basename($clean);
        if (is_file($path)) {
            @unlink($path);
        }
    }
}
