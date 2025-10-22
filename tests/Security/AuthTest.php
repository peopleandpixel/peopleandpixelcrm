<?php

declare(strict_types=1);

use App\Config;
use App\Util\Auth;
use PHPUnit\Framework\TestCase;

final class AuthTest extends TestCase
{
    private Config $config;

    protected function setUp(): void
    {
        $root = dirname(__DIR__, 2);
        $this->config = new Config($root);
        // Ensure test env
        $_ENV['APP_ENV'] = 'test';
        // Reset throttle file
        $ref = new ReflectionClass(Auth::class);
        $varDir = $this->config->getVarDir();
        @mkdir($varDir . '/cache', 0777, true);
        @unlink($varDir . '/cache/auth_throttle.json');
        // Reset session
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    }

    public function testSessionIdChangesOnSuccessfulLogin(): void
    {
        // Seed env admin for test
        $_ENV['ADMIN_USER'] = 'admin';
        $_ENV['ADMIN_PASS'] = 'secret';

        if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
        $before = session_id();
        $ok = Auth::login($this->config, 'admin', 'secret');
        $this->assertTrue($ok, 'Login should succeed');
        $this->assertNotSame($before, session_id(), 'Session id should change after login');
    }

    public function testThrottlingAfterFailedAttempts(): void
    {
        $_ENV['ADMIN_USER'] = 'admin';
        $_ENV['ADMIN_PASS'] = 'secret';
        // Try wrong password 6 times
        $blocked = false;
        for ($i = 0; $i < 6; $i++) {
            $ok = Auth::login($this->config, 'admin', 'wrong');
            if ($i < 5) {
                $this->assertFalse($ok, 'Should fail before lock kicks in');
            } else {
                $blocked = !$ok;
            }
        }
        $this->assertTrue($blocked, 'Should be locked after 5+ failures');
        // Now try correct password; should still be locked
        $ok2 = Auth::login($this->config, 'admin', 'secret');
        $this->assertFalse($ok2, 'Should still be locked immediately after failures');
    }
}
