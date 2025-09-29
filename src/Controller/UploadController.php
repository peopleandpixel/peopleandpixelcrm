<?php

declare(strict_types=1);

namespace App\Controller;

use App\Util\Upload;

final class UploadController
{
    public function handle(): void
    {
        // Basic CSRF: if helper/class exists, try to validate; otherwise proceed (security hardening is a separate task)
        if (class_exists('App\\Util\\Csrf')) {
            $name = \App\Util\Csrf::fieldName();
            $token = $_POST[$name] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
            if (!\App\Util\Csrf::validate((string)$token)) {
                http_response_code(400);
                header('Content-Type: application/json');
                echo json_encode(['ok' => false, 'error' => 'csrf']);
                return;
            }
        }
        $res = Upload::handle('file');
        header('Content-Type: application/json');
        if (!$res['ok']) {
            http_response_code(400);
        }
        echo json_encode($res);
    }
}
