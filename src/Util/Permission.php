<?php

declare(strict_types=1);

namespace App\Util;

class Permission
{
    /**
     * Check if current user can perform action on entity.
     * Admin users are allowed to do everything.
     */
    public static function can(string $entity, string $action): bool
    {
        if (Auth::isAdmin()) return true;
        $u = Auth::user();
        if (!$u) return false;
        $perms = $u['permissions'] ?? [];
        if (!is_array($perms)) return false;
        $entityPerm = $perms[$entity] ?? [];
        if (!is_array($entityPerm)) return false;
        return !empty($entityPerm[$action]);
    }

    /**
     * Enforce permission based on request path and method.
     * Returns true if allowed; otherwise sends 403 response and returns false.
     */
    public static function enforce(string $method, string $path): bool
    {
        // Map paths to entity/action
        $mapping = self::mapPathToCheck($method, $path);
        if ($mapping === null) return true; // not protected by fine-grained rules
        [$entity, $action] = $mapping;
        if (!self::can($entity, $action)) {
            http_response_code(403);
            render('errors/403');
            return false;
        }
        return true;
    }

    /**
     * Return [entity, action] or null if not applicable
     * @return array{0:string,1:string}|null
     */
    public static function mapPathToCheck(string $method, string $path): ?array
    {
        $entities = ['contacts','times','tasks','employees','candidates','payments','storage'];
        foreach ($entities as $e) {
            if ($path === '/' . $e) return [$e, 'view'];
            if ($path === '/' . $e . '/view') return [$e, 'view'];
            if ($path === '/' . $e . '/new') return [$e, $method === 'POST' ? 'create' : 'create'];
            if ($path === '/' . $e . '/edit') return [$e, $method === 'POST' ? 'edit' : 'edit'];
            if ($path === '/' . $e . '/delete' && $method === 'POST') return [$e, 'delete'];
            if ($e === 'storage') {
                if ($path === '/storage/adjust' && $method === 'POST') return [$e, 'edit'];
                if ($path === '/storage/history') return [$e, 'view'];
            }
        }
        return null;
    }
}
