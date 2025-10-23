<?php

declare(strict_types=1);

namespace App\Service;

use App\Util\Auth;

/**
 * Append-only audit log service.
 */
final class AuditService
{
    public function __construct(
        private readonly object $auditStore
    ) {}

    /**
     * Record an audit entry.
     * @param string $event created|updated|deleted|action
     * @param string $entity e.g., tasks
     * @param string|int|null $itemId
     * @param array|null $before optional previous state (for updates/deletes)
     * @param array|null $after optional new state (for creates/updates)
     * @param array<string,mixed> $extra optional extra fields
     */
    public function record(string $event, string $entity, string|int|null $itemId, ?array $before = null, ?array $after = null, array $extra = []): void
    {
        $user = Auth::user();
        $entry = [
            'event' => $event,
            'entity' => $entity,
            'item_id' => $itemId !== null ? (string)$itemId : null,
            'user_id' => $user['id'] ?? null,
            'username' => $user['login'] ?? ($user['username'] ?? null),
            'role' => $user['role'] ?? null,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
            'ua' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'at' => date('c'),
        ];
        if ($before !== null) { $entry['before'] = $this->slim($before); }
        if ($after !== null) { $entry['after'] = $this->slim($after); }
        foreach ($extra as $k => $v) { $entry[$k] = $v; }
        try {
            $this->auditStore->add($entry);
        } catch (\Throwable $e) {
            // Swallow errors to avoid breaking the main flow
        }
    }

    /** Reduce potentially huge arrays. */
    private function slim(array $data): array
    {
        // Keep at most 30 scalar fields; truncate strings
        $out = [];
        $i = 0;
        foreach ($data as $k => $v) {
            if ($i >= 30) break;
            if (is_array($v) || is_object($v)) continue;
            $s = (string)$v;
            if (strlen($s) > 500) { $s = substr($s, 0, 500) . '…'; }
            $out[(string)$k] = $s;
            $i++;
        }
        return $out;
    }
}
