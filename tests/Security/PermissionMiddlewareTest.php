<?php

declare(strict_types=1);

use App\Router;
use App\Util\Auth;
use App\Util\Permission;
use PHPUnit\Framework\TestCase;

final class PermissionMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        // Reset session
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    }

    public function testUnauthenticatedAccessRequiresLoginIsRedirectedByApp(): void
    {
        // This is an integration-level behavior handled in public/index.php middleware.
        // Here we only assert Permission::enforce returns true when mapping is null (no decision),
        // and false when a user lacks permissions for a mapped path.
        $this->assertTrue(Permission::enforce('GET', '/')); // home not protected
    }

    public function testForbiddenWhenUserLacksPermission(): void
    {
        // Fake a logged-in non-admin user with no permissions
        if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
        $_SESSION['_auth_user'] = [
            'username' => 'demo',
            'role' => 'user',
            'permissions' => [
                'employees' => [
                    'own' => ['view'=>1,'create'=>0,'edit'=>0,'delete'=>0],
                    'others' => ['view'=>0,'create'=>0,'edit'=>0,'delete'=>0],
                ],
            ],
        ];
        // Expect 403 for creating employees
        ob_start();
        $ok = Permission::enforce('GET', '/employees/new');
        $out = ob_get_clean();
        $this->assertFalse($ok, 'Should be forbidden');
        $this->assertNotEmpty($out, 'Should render 403 page');
    }
}
