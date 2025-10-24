<?php

declare(strict_types=1);

namespace App\Service;

use App\Config;
use App\Service\EmailService;
use Psr\Log\LoggerInterface;

/**
 * Automations MVP: simple rule runner (event -> conditions -> actions)
 * - Rules are stored in automations store (JSON/DB via StoreInterface)
 * - Supported actions: add_comment, send_email
 * - Supported ops: eq, contains, in
 */
final class AutomationService
{
    public function __construct(
        private readonly object $automationsStore,
        private readonly ?object $commentsStore = null,
        private readonly ?EmailService $email = null,
        private readonly ?Config $config = null,
        private readonly ?AuditService $audit = null,
        private readonly ?LoggerInterface $logger = null,
    ) {}

    /**
     * Trigger automations for an event with a payload.
     * @param array<string,mixed> $payload
     */
    public function runForEvent(string $event, array $payload): void
    {
        try {
            if (!$this->config) { return; }
            $enabled = $this->flag('AUTOMATIONS_ENABLED');
            if (!$enabled) { return; }
            $maxActions = (int)($this->config->getEnv('AUTOMATIONS_MAX_ACTIONS') ?: '10');
            $rules = $this->safeAll();
            $ran = 0; $errors = 0; $matched = 0;
            foreach ($rules as $rule) {
                if (($rule['enabled'] ?? 0) != 1) { continue; }
                if ((string)($rule['event'] ?? '') !== $event) { continue; }
                if (!$this->conditionsPass($rule['conditions'] ?? [], $payload)) { continue; }
                $matched++;
                try {
                    $count = $this->executeActions((array)($rule['actions'] ?? []), $payload, $maxActions - $ran);
                    $ran += $count;
                } catch (\Throwable $e) {
                    $errors++;
                    if ($this->logger) { $this->logger->warning('Automation actions failed: ' . $e->getMessage()); }
                }
                if ($ran >= $maxActions) { break; }
            }
            $this->audit?->record('action', 'automations', null, null, null, [
                'automation_event' => $event,
                'automation_matched' => $matched,
                'automation_actions' => $ran,
                'automation_errors' => $errors,
            ]);
        } catch (\Throwable $e) {
            // swallow errors
            if ($this->logger) { $this->logger->error('Automation run error: ' . $e->getMessage()); }
        }
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    private function safeAll(): array
    {
        try { return $this->automationsStore->all(); } catch (\Throwable) { return []; }
    }

    /**
     * @param array<int,array<string,mixed>> $conditions
     * @param array<string,mixed> $payload
     */
    private function conditionsPass(array $conditions, array $payload): bool
    {
        foreach ($conditions as $c) {
            $path = (string)($c['path'] ?? '');
            $op = strtolower((string)($c['op'] ?? 'eq'));
            $value = $c['value'] ?? null;
            $actual = $this->getByPath($payload, $path);
            $ok = match ($op) {
                'eq' => (string)$actual === (string)$value,
                'contains' => is_string($actual) && is_string($value) ? (mb_stripos($actual, $value) !== false) : false,
                'in' => is_array($value) ? in_array($actual, $value, true) : false,
                default => false,
            };
            if (!$ok) { return false; }
        }
        return true;
    }

    private function getByPath(array $data, string $path): mixed
    {
        if ($path === '' || $path === '.') { return $data; }
        $parts = explode('.', $path);
        $cur = $data;
        foreach ($parts as $p) {
            if (is_array($cur) && array_key_exists($p, $cur)) { $cur = $cur[$p]; }
            else { return null; }
        }
        return $cur;
    }

    /**
     * @param array<int,array<string,mixed>> $actions
     * @param array<string,mixed> $payload
     */
    private function executeActions(array $actions, array $payload, int $budget): int
    {
        if ($budget <= 0) { return 0; }
        $count = 0;
        foreach ($actions as $a) {
            if ($count >= $budget) { break; }
            $type = strtolower((string)($a['type'] ?? ''));
            $params = (array)($a['params'] ?? []);
            try {
                $ok = false;
                if ($type === 'add_comment') {
                    $ok = $this->actAddComment($params, $payload);
                } elseif ($type === 'send_email') {
                    $ok = $this->actSendEmail($params, $payload);
                }
                if ($ok) { $count++; }
            } catch (\Throwable $e) {
                if ($this->logger) { $this->logger->warning('Automation action error: ' . $e->getMessage()); }
            }
        }
        return $count;
    }

    /**
     * params: entity, entity_id, message
     */
    private function actAddComment(array $params, array $payload): bool
    {
        if (!$this->commentsStore) { return false; }
        $entity = isset($params['entity']) ? (string)$params['entity'] : (string)($payload['entity'] ?? '');
        $entityId = isset($params['entity_id']) ? (int)$params['entity_id'] : (int)($payload['entity_id'] ?? 0);
        $message = (string)($params['message'] ?? '');
        if ($message === '') { $message = (string)($payload['message'] ?? ''); }
        $message = $this->interpolate($message, $payload);
        if ($entity === '' || $entityId <= 0 || $message === '') { return false; }
        $rec = [
            'entity' => $entity,
            'entity_id' => $entityId,
            'parent_id' => null,
            'message' => $message,
            'mentions' => [],
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => 'automation',
        ];
        $this->commentsStore->add($rec);
        return true;
    }

    /**
     * params: to, subject, body
     */
    private function actSendEmail(array $params, array $payload): bool
    {
        if (!$this->email || !$this->config) { return false; }
        if (!$this->flag('AUTOMATIONS_ALLOW_EMAIL')) { return false; }
        $to = (string)($params['to'] ?? '');
        $subj = (string)($params['subject'] ?? '');
        $body = (string)($params['body'] ?? '');
        $subj = $this->interpolate($subj, $payload);
        $body = $this->interpolate($body, $payload);
        if ($to === '' || $subj === '' || $body === '') { return false; }
        try { $this->email->send($to, $subj, $body); return true; } catch (\Throwable) { return false; }
    }

    private function interpolate(string $text, array $payload): string
    {
        // Replace {{key}} with payload[key] (top-level only) as a safe minimal templating
        return preg_replace_callback('/\{\{\s*([A-Za-z0-9_\.]+)\s*\}\}/', function($m) use ($payload) {
            $val = $this->getByPath($payload, (string)$m[1]);
            if (is_scalar($val)) { return (string)$val; }
            return '';
        }, $text) ?? $text;
    }

    private function flag(string $name): bool
    {
        $v = (string)($this->config?->getEnv($name) ?? '');
        return in_array(strtolower($v), ['1','true','yes','on'], true);
    }
}
