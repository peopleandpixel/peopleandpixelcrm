<?php

declare(strict_types=1);

namespace App\Service;

use App\Config;
use Psr\Log\LoggerInterface;

final class WebhookService
{
    public function __construct(
        private readonly Config $config,
        private readonly LoggerInterface $logger
    ) {}

    /**
     * Emit a webhook event to all configured endpoints.
     * Adds failed deliveries to a retry queue with exponential backoff and jitter.
     * @param string $event e.g., 'created','updated','deleted'
     * @param string $entity e.g., 'tasks'
     * @param mixed $payload Arbitrary payload (array recommended)
     * @param array|null $context Optional extra fields
     */
    public function emit(string $event, string $entity, $payload, ?array $context = null): void
    {
        $urls = $this->catalogEnabled();
        if (empty($urls)) { return; }
        $secret = trim((string)$this->config->getEnv('WEBHOOK_SECRET'));
        $body = [
            'event' => $event,
            'entity' => $entity,
            'timestamp' => date('c'),
            'payload' => $payload,
        ];
        if ($context) { $body['context'] = $context; }
        $json = json_encode($body);
        foreach ($urls as $url) {
            try {
                $ok = $this->postJson($url, $json, $secret);
                if (!$ok) {
                    $this->enqueueRetry($url, $json, 0);
                }
            } catch (\Throwable $e) {
                $this->logger->error('Webhook delivery failed', ['url' => $url, 'error' => $e->getMessage()]);
                $this->enqueueRetry($url, $json, 0);
            }
        }
    }

    /**
     * Return catalog of configured endpoints with enabled flag.
     * Env: WEBHOOKS (csv), WEBHOOKS_DISABLED (csv of urls to disable)
     * @return array<int,array{url:string,enabled:bool}>
     */
    public function catalog(): array
    {
        $all = $this->endpoints();
        $disabledCsv = trim($this->config->getEnv('WEBHOOKS_DISABLED'));
        $disabled = $disabledCsv !== '' ? array_map('trim', explode(',', $disabledCsv)) : [];
        $out = [];
        foreach ($all as $u) {
            $out[] = ['url' => $u, 'enabled' => !in_array($u, $disabled, true)];
        }
        return $out;
    }

    /**
     * Process due retries up to $max items.
     */
    public function retryDue(int $max = 10): int
    {
        $path = $this->queuePath();
        if (!is_file($path)) { return 0; }
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        $kept = [];
        $processed = 0;
        foreach ($lines as $line) {
            if ($processed >= $max) { $kept[] = $line; continue; }
            $job = json_decode($line, true);
            if (!is_array($job)) { continue; }
            $due = (int)($job['nextAt'] ?? 0);
            if ($due > time()) { $kept[] = $line; continue; }
            $ok = false;
            try {
                $ok = $this->postJson((string)$job['url'], (string)$job['body'], (string)($job['secret'] ?? ''));
            } catch (\Throwable $e) {
                $ok = false;
            }
            if (!$ok) {
                $this->reschedule($job);
            } else {
                $processed++;
            }
        }
        // Re-write queue (rescheduled jobs were appended)
        @file_put_contents($path, implode("\n", $kept) . (empty($kept) ? '' : "\n"));
        return $processed;
    }

    /** @return array<int,string> */
    private function endpoints(): array
    {
        $v = $this->config->getEnv('WEBHOOKS');
        if ($v === '') return [];
        $parts = array_map('trim', explode(',', $v));
        return array_values(array_filter($parts, fn($u) => $u !== ''));
    }

    /**
     * @return array<int,string>
     */
    private function catalogEnabled(): array
    {
        $list = [];
        $disabledCsv = trim($this->config->getEnv('WEBHOOKS_DISABLED'));
        $disabled = $disabledCsv !== '' ? array_map('trim', explode(',', $disabledCsv)) : [];
        foreach ($this->endpoints() as $u) {
            if (!in_array($u, $disabled, true)) { $list[] = $u; }
        }
        return $list;
    }

    /**
     * Perform HTTP POST. Returns true if 2xx status received.
     */
    private function postJson(string $url, string $json, string $secret): bool
    {
        $headers = [
            'Content-Type: application/json',
            'User-Agent: PeopleAndPixel/1.0',
        ];
        if ($secret !== '') {
            $sig = hash_hmac('sha256', $json, $secret);
            $headers[] = 'X-Webhook-Signature: sha256=' . $sig;
        }
        $opts = [
            'http' => [
                'method' => 'POST',
                'header' => implode("\r\n", $headers),
                'content' => $json,
                'timeout' => 5,
                'ignore_errors' => true,
            ]
        ];
        $ctx = stream_context_create($opts);
        $resp = @file_get_contents($url, false, $ctx);
        $code = 0;
        if (isset($http_response_header[0])) {
            if (preg_match('#\s(\d{3})\s#', $http_response_header[0], $m)) {
                $code = (int)$m[1];
            }
        }
        return $code >= 200 && $code < 300;
    }

    private function enqueueRetry(string $url, string $body, int $attempt): void
    {
        $job = [
            'url' => $url,
            'body' => $body,
            'secret' => (string)$this->config->getEnv('WEBHOOK_SECRET'),
            'attempt' => $attempt,
            'nextAt' => time() + $this->backoffSeconds($attempt),
        ];
        $line = json_encode($job, JSON_UNESCAPED_UNICODE);
        @mkdir(dirname($this->queuePath()), 0777, true);
        @file_put_contents($this->queuePath(), $line . "\n", FILE_APPEND);
    }

    /**
     * Reschedule an existing job by increasing attempt and computing nextAt.
     * Drops after 10 attempts.
     */
    private function reschedule(array $job): void
    {
        $attempt = (int)($job['attempt'] ?? 0) + 1;
        if ($attempt > 10) { return; }
        $job['attempt'] = $attempt;
        $job['nextAt'] = time() + $this->backoffSeconds($attempt);
        $line = json_encode($job, JSON_UNESCAPED_UNICODE);
        @file_put_contents($this->queuePath(), $line . "\n", FILE_APPEND);
    }

    private function backoffSeconds(int $attempt): int
    {
        // Exponential backoff: base 2^attempt seconds, capped at 1 hour, with +/- 10% jitter
        $base = min(3600, (int)pow(2, max(0, $attempt)));
        $jitter = (int)round($base * (mt_rand(-10, 10) / 100));
        return max(5, $base + $jitter);
    }

    private function queuePath(): string
    {
        return $this->config->getVarDir() . '/queue/webhooks.jsonl';
    }
}
