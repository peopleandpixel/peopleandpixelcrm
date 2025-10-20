<?php

declare(strict_types=1);

namespace App\Util;

class Permission
{
    /**
     * Check if current user can perform action on entity (no ownership context).
     * Admin users are allowed to do everything.
     * Backward compatible with old flat matrix.
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
        // New structure: own.* and others.* sections
        if (isset($entityPerm['own']) || isset($entityPerm['others'])) {
            // If any of own/others allows non-contextual action, treat as allowed for listing/new without specific record
            $own = (int)($entityPerm['own'][$action] ?? 0) === 1;
            $others = (int)($entityPerm['others'][$action] ?? 0) === 1;
            return $own || $others;
        }
        // Legacy flat matrix
        return !empty($entityPerm[$action]);
    }

    /**
     * Check permission on a specific record based on ownership.
     * The record may contain owner_user_id; if missing, treated as "others".
     * @param array<string,mixed>|null $record
     */
    public static function canOnRecord(string $entity, string $action, ?array $record): bool
    {
        if (Auth::isAdmin()) return true;
        $u = Auth::user();
        if (!$u) return false;
        $uid = (int)($u['id'] ?? 0);
        $ownerId = (int)($record['owner_user_id'] ?? 0);
        $isOwner = $ownerId > 0 && $ownerId === $uid;
        $perms = $u['permissions'] ?? [];
        $entityPerm = is_array($perms[$entity] ?? null) ? $perms[$entity] : [];
        if (isset($entityPerm['own']) || isset($entityPerm['others'])) {
            $section = $isOwner ? 'own' : 'others';
            return (int)($entityPerm[$section][$action] ?? 0) === 1;
        }
        // Legacy: fall back to flat
        return (int)($entityPerm[$action] ?? 0) === 1;
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
