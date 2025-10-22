<?php

declare(strict_types=1);

namespace App\Util;

final class Upload
{
    /**
     * Handle a general file upload from $_FILES.
     *
     * @param string $field Key in $_FILES
     * @param array{allowed_mime?: array<string,string>, max_size?: int, subdir?: string} $opts
     *                       allowed_mime: map of mime => extension (empty = allow common images)
     *                       max_size: bytes (default 5MB)
     *                       subdir: subdirectory under public/uploads (e.g., 'images')
     * @return array{ok:bool, url?:string, path?:string, name?:string, size?:int, error?:string}
     */
    public static function handle(string $field = 'file', array $opts = []): array
    {
        if (!isset($_FILES[$field]) || !is_array($_FILES[$field])) {
            return ['ok' => false, 'error' => 'no_file'];
        }
        $f = $_FILES[$field];
        $err = (int)($f['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($err !== UPLOAD_ERR_OK) {
            $map = [
                UPLOAD_ERR_INI_SIZE => 'too_large',
                UPLOAD_ERR_FORM_SIZE => 'too_large',
                UPLOAD_ERR_PARTIAL => 'partial',
                UPLOAD_ERR_NO_FILE => 'no_file',
                UPLOAD_ERR_NO_TMP_DIR => 'no_tmp_dir',
                UPLOAD_ERR_CANT_WRITE => 'cant_write',
                UPLOAD_ERR_EXTENSION => 'extension_blocked',
            ];
            return ['ok' => false, 'error' => $map[$err] ?? ('error_' . $err)];
        }
        $tmp = (string)($f['tmp_name'] ?? '');
        $size = (int)($f['size'] ?? 0);
        if (!is_uploaded_file($tmp)) {
            return ['ok' => false, 'error' => 'not_uploaded_file'];
        }
        $max = (int)($opts['max_size'] ?? (5 * 1024 * 1024));
        if ($size <= 0 || $size > $max) {
            return ['ok' => false, 'error' => 'size_out_of_range'];
        }
        $allowed = $opts['allowed_mime'] ?? [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
            'image/webp' => 'webp',
        ];
        $mime = '';
        if (function_exists('finfo_open')) {
            $finfo = @finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo) {
                $mime = (string)@finfo_file($finfo, $tmp);
                @finfo_close($finfo);
            }
        }
        $ext = '';
        if ($mime !== '' && isset($allowed[$mime])) {
            $ext = $allowed[$mime];
        } else {
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
        }
        if ($ext === '') {
            return ['ok' => false, 'error' => 'invalid_type'];
        }
        $root = dirname(__DIR__, 2);
        $sub = trim((string)($opts['subdir'] ?? ''), '/');
        $uploadDir = $root . '/var/uploads' . ($sub !== '' ? '/' . $sub : '');
        if (!is_dir($uploadDir)) { @mkdir($uploadDir, 0777, true); }
        $rand = bin2hex(random_bytes(8));
        $namePart = pathinfo((string)($f['name'] ?? ''), PATHINFO_FILENAME);
        $namePart = preg_replace('/[^a-zA-Z0-9_-]+/', '-', (string)$namePart) ?: 'file';
        $fileName = date('Ymd_His') . '_' . $rand . '_' . $namePart . '.' . $ext;
        $dest = $uploadDir . '/' . $fileName;
        if (!@move_uploaded_file($tmp, $dest)) {
            return ['ok' => false, 'error' => 'move_failed'];
        }
        $public = '/files' . ($sub !== '' ? '/' . $sub : '') . '/' . $fileName;
        return [
            'ok' => true,
            'url' => $public,
            'path' => $dest,
            'name' => (string)($f['name'] ?? $fileName),
            'size' => $size,
        ];
    }
}
