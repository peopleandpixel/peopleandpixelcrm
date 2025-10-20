<?php

declare(strict_types=1);

namespace App\Controller;

use App\StoreInterface;
use App\Util\Auth;
use App\Util\Csrf;
use App\Util\Flash;

class PasswordController
{
    public function form(): void
    {
        if (!Auth::check()) { redirect('/login'); return; }
        render('password_change', [
            'return' => isset($_GET['return']) ? (string)$_GET['return'] : '/',
        ]);
    }

    public function submit(StoreInterface $usersStore): void
    {
        if (!Auth::check()) { redirect('/login'); return; }
        $token = isset($_POST[Csrf::fieldName()]) ? (string)$_POST[Csrf::fieldName()] : null;
        if (!Csrf::validate($token)) { http_response_code(400); render('errors/400'); return; }
        $u = Auth::user();
        $id = (int)($u['id'] ?? 0);
        $p1 = (string)($_POST['password'] ?? '');
        $p2 = (string)($_POST['password2'] ?? '');
        $return = isset($_POST['return']) ? (string)$_POST['return'] : '/';
        $error = null;
        if ($p1 === '' || $p2 === '') {
            $error = __('Password is required');
        } elseif ($p1 !== $p2) {
            $error = __('Passwords do not match');
        } elseif (!self::isStrongPassword($p1)) {
            $error = __('Password is too weak. Use at least 12 characters with mixed types.');
        }
        if ($error) {
            render('password_change', ['error' => $error, 'return' => $return]);
            return;
        }
        $hash = password_hash($p1, PASSWORD_DEFAULT);
        $usersStore->update($id, ['password_hash' => $hash, 'must_change_password' => 0]);
        Flash::success(__('Password updated'));
        // Refresh session flag
        if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
        if (isset($_SESSION['_auth_user']) && is_array($_SESSION['_auth_user'])) {
            $_SESSION['_auth_user']['must_change_password'] = false;
        }
        redirect($return ?: '/');
    }

    private static function isStrongPassword(string $p): bool
    {
        if (strlen($p) < 12) return false;
        $hasUpper = (bool)preg_match('/[A-Z]/', $p);
        $hasLower = (bool)preg_match('/[a-z]/', $p);
        $hasDigit = (bool)preg_match('/\d/', $p);
        $hasSymbol = (bool)preg_match('/[^A-Za-z0-9]/', $p);
        $unique = count(array_unique(str_split($p))) >= 6;
        return $hasUpper && $hasLower && $hasDigit && $hasSymbol && $unique;
    }
}
