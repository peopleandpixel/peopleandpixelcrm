<?php

declare(strict_types=1);

use App\Util\Permission;
use PHPUnit\Framework\TestCase;

final class PermissionRecordTest extends TestCase
{
    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        // Default: enable object-level for all core entities
        $_ENV['PERMISSIONS_OBJECT_LEVEL'] = '';
    }

    public function testOwnerAllowedOthersDenied(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
        $_SESSION['_auth_user'] = [
            'id' => 5,
            'username' => 'demo',
            'role' => 'user',
            'permissions' => [
                'contacts' => [
                    'own' => ['view'=>1,'create'=>1,'edit'=>1,'delete'=>1],
                    'others' => ['view'=>0,'create'=>0,'edit'=>0,'delete'=>0],
                ],
            ],
        ];
        $own = ['id'=>10,'owner_user_id'=>5];
        $other = ['id'=>11,'owner_user_id'=>2];
        $this->assertTrue(Permission::canOnRecord('contacts','edit',$own));
        $this->assertFalse(Permission::canOnRecord('contacts','edit',$other));
        // EnforceRecord should render 403 for others
        ob_start();
        $ok = Permission::enforceRecord('contacts','edit',$other);
        $out = ob_get_clean();
        $this->assertFalse($ok);
        $this->assertNotEmpty($out);
    }

    public function testAdminBypassesChecks(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
        $_SESSION['_auth_user'] = [
            'id' => 1,
            'username' => 'admin',
            'role' => 'admin',
            'permissions' => [],
        ];
        $record = ['id'=>1,'owner_user_id'=>999];
        $this->assertTrue(Permission::canOnRecord('contacts','delete',$record));
        $this->assertTrue(Permission::enforceRecord('contacts','delete',$record));
    }

    public function testEntityLevelWhenObjectLevelDisabled(): void
    {
        // Disable object-level for contacts explicitly
        $_ENV['PERMISSIONS_OBJECT_LEVEL'] = 'tasks,projects';
        if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
        $_SESSION['_auth_user'] = [
            'id' => 2,
            'username' => 'demo2',
            'role' => 'user',
            'permissions' => [
                'contacts' => [
                    'own' => ['view'=>0,'create'=>0,'edit'=>0,'delete'=>0],
                    'others' => ['view'=>1,'create'=>0,'edit'=>0,'delete'=>0],
                ],
            ],
        ];
        $other = ['id'=>22,'owner_user_id'=>999];
        // Object-level disabled for contacts: enforceRecord should fall back to entity-level → allow view others
        $this->assertTrue(Permission::enforceRecord('contacts','view',$other));
    }
}
