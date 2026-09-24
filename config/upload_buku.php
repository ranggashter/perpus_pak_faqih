<?php
/**
 * Helper upload foto sampul buku.
 */

function uploadFotoBuku(array $file, string &$error)
{
    $error = '';

    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return '';
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = 'Foto gagal diunggah.';
        return false;
    }

    if ($file['size'] > 2 * 1024 * 1024) {
        $error = 'Ukuran foto maksimal 2 MB.';
        return false;
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $allowedTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    if (!isset($allowedTypes[$mime])) {
        $error = 'Format foto harus JPG, PNG, atau WEBP.';
        return false;
    }

    $uploadDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'buku';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
        $error = 'Folder upload foto tidak dapat dibuat.';
        return false;
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $allowedTypes[$mime];
    $destination = $uploadDir . DIRECTORY_SEPARATOR . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        $error = 'Foto gagal disimpan.';
        return false;
    }

    return $filename;
}

function hapusFotoBuku(?string $filename): void
{
    if (!$filename || basename($filename) !== $filename) {
        return;
    }

    $path = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'buku' . DIRECTORY_SEPARATOR . $filename;
    if (is_file($path)) {
        unlink($path);
    }
}
?>
