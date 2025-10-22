<?php

declare(strict_types=1);

namespace App\Controller;

final class FilesController
{
    /**
     * Securely stream files stored under var/uploads to the client.
     * Only allows subdirectories and filenames with safe characters.
     */
    public function serve(string $subdir, string $file): void
    {
        $subdir = trim($subdir, "/\\");
        $file = trim($file, "/\\");
        // Allow only safe characters to avoid traversal
        if ($subdir !== '' && !preg_match('#^[a-zA-Z0-9_/\-]+$#', $subdir)) {
            http_response_code(404);
            echo 'Not found';
            return;
        }
        if ($file === '' || !preg_match('#^[a-zA-Z0-9_.\-]+$#', $file)) {
            http_response_code(404);
            echo 'Not found';
            return;
        }
        // Build absolute path under var/uploads
        $root = dirname(__DIR__, 2);
        $base = $root . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'uploads';
        $path = $base
            . ($subdir !== '' ? DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $subdir) : '')
            . DIRECTORY_SEPARATOR . $file;

        // Normalize and verify it's still within base directory
        $realBase = realpath($base) ?: $base;
        $realPath = realpath($path);
        if ($realPath === false || strncmp($realPath, $realBase, strlen($realBase)) !== 0) {
            http_response_code(404);
            echo 'Not found';
            return;
        }
        if (!is_file($realPath) || !is_readable($realPath)) {
            http_response_code(404);
            echo 'Not found';
            return;
        }

        // Determine content type safely; default to application/octet-stream
        $mime = 'application/octet-stream';
        if (function_exists('finfo_open')) {
            $finfo = @finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo) {
                $detected = @finfo_file($finfo, $realPath);
                if (is_string($detected) && $detected !== '') {
                    $mime = $detected;
                }
                @finfo_close($finfo);
            }
        }

        // Security headers
        header('X-Content-Type-Options: nosniff');
        header("Content-Security-Policy: default-src 'none'; img-src * data: blob:; media-src * data: blob:");
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . (string)filesize($realPath));
        // Basic caching - allow public cache for 1 day; adjust as needed
        header('Cache-Control: public, max-age=86400');
        header('Accept-Ranges: bytes');

        // Support simple range requests (streaming large files)
        $size = filesize($realPath) ?: 0;
        $start = 0;
        $end = $size > 0 ? $size - 1 : 0;
        $status = 200;
        if (isset($_SERVER['HTTP_RANGE']) && preg_match('/bytes=(\d*)-(\d*)/', (string)$_SERVER['HTTP_RANGE'], $m)) {
            $rangeStart = $m[1] !== '' ? (int)$m[1] : 0;
            $rangeEnd = $m[2] !== '' ? (int)$m[2] : $end;
            if ($rangeStart <= $rangeEnd && $rangeEnd < $size) {
                $start = $rangeStart;
                $end = $rangeEnd;
                $status = 206;
                header('HTTP/1.1 206 Partial Content');
                header('Content-Range: bytes ' . $start . '-' . $end . '/' . $size);
                header('Content-Length: ' . (string)($end - $start + 1));
            }
        }
        if ($status === 200) {
            http_response_code(200);
        }

        $fp = @fopen($realPath, 'rb');
        if (!$fp) {
            http_response_code(500);
            echo 'Failed to open file';
            return;
        }
        try {
            if ($start > 0) {
                fseek($fp, $start);
            }
            $bytesToSend = $end - $start + 1;
            $chunk = 8192; // 8KB chunks
            while ($bytesToSend > 0 && !feof($fp)) {
                $read = ($bytesToSend > $chunk) ? $chunk : $bytesToSend;
                $buf = fread($fp, $read);
                if ($buf === false) { break; }
                echo $buf;
                flush();
                $bytesToSend -= strlen($buf);
            }
        } finally {
            fclose($fp);
        }
    }
}
