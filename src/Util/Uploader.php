<?php

namespace App\Util;

class Uploader
{

    public static function saveUploadedPicture(): ?string
    {
        if (!isset($_FILES['picture_file']) || !is_array($_FILES['picture_file'])) {
            return null;
        }
        $f = $_FILES['picture_file'];
        if (($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return null; // nothing uploaded
        }
        // Basic validations
        $tmp = (string)($f['tmp_name'] ?? '');
        $size = (int)($f['size'] ?? 0);
        if (!is_uploaded_file($tmp)) { return null; }
        if ($size <= 0 || $size > 5 * 1024 * 1024) { // 5MB limit
            return null;
        }
        // Detect mime/type using finfo if available; otherwise fall back to exif_imagetype()/getimagesize()
        $mime = '';
        if (function_exists('finfo_open')) {
            $finfo = @finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo) {
                $mime = (string)@finfo_file($finfo, $tmp);
                @finfo_close($finfo);
            }
        }
        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
            'image/webp' => 'webp',
        ];
        $ext = '';
        if ($mime !== '' && isset($allowed[$mime])) {
            $ext = $allowed[$mime];
        } else {
            // Try exif_imagetype
            $type = function_exists('exif_imagetype') ? @exif_imagetype($tmp) : false;
            if ($type) {
                $map = [
                    IMAGETYPE_JPEG => 'jpg',
                    IMAGETYPE_PNG  => 'png',
                    IMAGETYPE_GIF  => 'gif',
                    IMAGETYPE_WEBP => 'webp',
                ];
                if (isset($map[$type])) { $ext = $map[$type]; }
            }
            if ($ext === '') {
                // Last resort: getimagesize
                $info = @getimagesize($tmp);
                if (is_array($info) && isset($info[2])) {
                    $type = (int)$info[2];
                    $map = [
                        IMAGETYPE_JPEG => 'jpg',
                        IMAGETYPE_PNG  => 'png',
                        IMAGETYPE_GIF  => 'gif',
                        IMAGETYPE_WEBP => 'webp',
                    ];
                    if (isset($map[$type])) { $ext = $map[$type]; }
                }
            }
        }
        if ($ext === '') { return null; }
        // Build upload path
        $root = dirname(__DIR__, 2);
        $uploadDir = $root . '/var/uploads/images';
        if (!is_dir($uploadDir)) { @mkdir($uploadDir, 0777, true); }
        // Safe filename
        $rand = bin2hex(random_bytes(8));
        $namePart = pathinfo((string)($f['name'] ?? ''), PATHINFO_FILENAME);
        $namePart = preg_replace('/[^a-zA-Z0-9_-]+/', '-', (string)$namePart) ?: 'img';
        $fileName = date('Ymd_His') . '_' . $rand . '_' . $namePart . '.' . $ext;
        $dest = $uploadDir . '/' . $fileName;
        if (!move_uploaded_file($tmp, $dest)) { return null; }
        // Return served path
        return '/files/images/' . $fileName;
    }

}