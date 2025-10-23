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
     * @param string $event e.g., 'created','updated','deleted'
     * @param string $entity e.g., 'tasks'
     * @param mixed $payload Arbitrary payload (array recommended)
     * @param array|null $context Optional extra fields
     */
    public function emit(string $event, string $entity, $payload, ?array $context = null): void
    {
        $urls = $this->endpoints();
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
                $this->postJson($url, $json, $secret);
            } catch (\Throwable $e) {
                $this->logger->error('Webhook delivery failed', ['url' => $url, 'error' => $e->getMessage()]);
            }
        }
    }

    /** @return array<int,string> */
    private function endpoints(): array
    {
        $v = $this->config->getEnv('WEBHOOKS');
        if ($v === '') return [];
        $parts = array_map('trim', explode(',', $v));
        return array_values(array_filter($parts, fn($u) => $u !== ''));
    }

    private function postJson(string $url, string $json, string $secret): void
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
            ]
        ];
        $ctx = stream_context_create($opts);
        @file_get_contents($url, false, $ctx);
    }
}
