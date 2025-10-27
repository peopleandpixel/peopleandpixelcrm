<?php

declare(strict_types=1);

namespace App\Service;

use App\Config;
use Psr\Log\LoggerInterface;

/**
 * Privacy-first optional enrichment.
 * - Only runs if user provided API keys in env.
 * - Never overwrites existing non-empty fields by default; fills blanks only.
 * - All calls are auditable by the caller (controller records).
 */
final class EnrichmentService
{
    public function __construct(
        private readonly Config $config,
        private readonly LoggerInterface $logger
    ) {}

    public function isEnabled(): bool
    {
        return $this->hasClearbit() || $this->gravatarEnabled();
    }

    /**
     * Attempt to enrich a single contact non-destructively.
     * @param array $contact Existing contact row.
     * @return array{updated:bool, changes:array<string,mixed>, after:array<string,mixed>, providers:array<int,string>, errors:array<int,string>}
     */
    public function enrichContact(array $contact): array
    {
        $providers = [];
        $errors = [];
        $changes = [];
        $after = $contact;

        $email = trim((string)($contact['email'] ?? ''));
        // Prefer primary structured email if available
        if ($email === '' && isset($contact['emails']) && is_array($contact['emails']) && !empty($contact['emails'])) {
            $email = trim((string)($contact['emails'][0]['value'] ?? ''));
        }

        // Gravatar: avatar URL by email hash
        if ($this->gravatarEnabled() && $email !== '') {
            try {
                $avatar = $this->gravatarUrl($email);
                if (($after['picture'] ?? '') === '' && $avatar !== '') {
                    $after['picture'] = $avatar;
                    $changes['picture'] = $avatar;
                    $providers[] = 'gravatar';
                }
            } catch (\Throwable $e) {
                $errors[] = 'gravatar:' . $e->getMessage();
            }
        }

        // Clearbit: enrich name, company, socials, website if missing
        if ($this->hasClearbit() && $email !== '') {
            try {
                $providers[] = 'clearbit';
                $cb = $this->fetchClearbit($email);
                if (is_array($cb)) {
                    // Simple mapping; only fill if empty
                    $map = [
                        'name' => $cb['person']['name']['fullName'] ?? null,
                        'company' => $cb['company']['name'] ?? null,
                        'website' => $cb['person']['site'] ?? ($cb['company']['domain'] ?? null),
                        'twitter' => $cb['person']['twitter']['handle'] ?? null,
                        'linkedin' => $cb['person']['linkedin']['handle'] ?? null,
                    ];
                    // websites/socials arrays if schema uses them
                    if (($after['websites'] ?? null) === null || !is_array($after['websites'])) { $after['websites'] = $after['websites'] ?? []; }
                    if (($after['socials'] ?? null) === null || !is_array($after['socials'])) { $after['socials'] = $after['socials'] ?? []; }

                    foreach ($map as $field => $value) {
                        if ($value === null || $value === '') continue;
                        switch ($field) {
                            case 'name':
                                if (($after['name'] ?? '') === '') { $after['name'] = (string)$value; $changes['name'] = $after['name']; }
                                break;
                            case 'company':
                                if (($after['company'] ?? '') === '') { $after['company'] = (string)$value; $changes['company'] = $after['company']; }
                                break;
                            case 'website':
                                $v = (string)$value;
                                if ($v !== '') {
                                    if (!preg_match('#^https?://#i', $v) && preg_match('/^[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/', $v)) {
                                        $v = 'https://' . $v;
                                    }
                                    if ($this->listMissingContains($after['websites'], $v)) {
                                        $after['websites'][] = ['value' => $v, 'tag' => 'business'];
                                        $changes['websites'][] = ['value' => $v, 'tag' => 'business'];
                                    }
                                }
                                break;
                            case 'twitter':
                                $v = (string)$value;
                                if ($v !== '') {
                                    if ($v[0] !== '@') { $v = '@' . $v; }
                                    $url = 'https://twitter.com/' . ltrim($v, '@');
                                    if ($this->listMissingContains($after['socials'], $url)) {
                                        $after['socials'][] = ['value' => $url, 'tag' => 'business'];
                                        $changes['socials'][] = ['value' => $url, 'tag' => 'business'];
                                    }
                                }
                                break;
                            case 'linkedin':
                                $v = (string)$value;
                                if ($v !== '') {
                                    $url = str_starts_with($v, 'http') ? $v : ('https://www.linkedin.com/in/' . ltrim($v, '/'));
                                    if ($this->listMissingContains($after['socials'], $url)) {
                                        $after['socials'][] = ['value' => $url, 'tag' => 'business'];
                                        $changes['socials'][] = ['value' => $url, 'tag' => 'business'];
                                    }
                                }
                                break;
                        }
                    }
                }
            } catch (\Throwable $e) {
                $this->logger->warning('Clearbit enrich failed', ['error' => $e->getMessage()]);
                $errors[] = 'clearbit:' . $e->getMessage();
            }
        }

        $updated = !empty($changes);
        return ['updated' => $updated, 'changes' => $changes, 'after' => $after, 'providers' => $providers, 'errors' => $errors];
    }

    public static function maskEmail(string $email): string
    {
        $email = strtolower(trim($email));
        if ($email === '') return '';
        $parts = explode('@', $email, 2);
        $local = $parts[0] ?? '';
        $domain = $parts[1] ?? '';
        $hash = substr(hash('sha256', $email), 0, 10);
        $maskedLocal = mb_substr($local, 0, 1) . '***';
        return $maskedLocal . '@' . $domain . ' (#' . $hash . ')';
    }

    private function gravatarEnabled(): bool
    {
        $v = $this->config->getEnv('GRAVATAR_ENABLE');
        if ($v === '') return true; // enabled by default
        $v = strtolower(trim($v));
        return !in_array($v, ['0','false','no','off'], true);
    }

    private function gravatarUrl(string $email): string
    {
        $hash = md5(strtolower(trim($email)));
        $size = 200;
        $d = 'identicon';
        return 'https://www.gravatar.com/avatar/' . $hash . '?s=' . $size . '&d=' . $d;
    }

    private function hasClearbit(): bool
    {
        return trim($this->config->getEnv('CLEARBIT_API_KEY')) !== '';
    }

    /**
     * @return array<string,mixed>|null
     */
    private function fetchClearbit(string $email): ?array
    {
        $apiKey = trim($this->config->getEnv('CLEARBIT_API_KEY'));
        if ($apiKey === '') return null;
        $url = 'https://person.clearbit.com/v2/combined/find?email=' . rawurlencode($email);
        $auth = base64_encode($apiKey . ':');
        $opts = [
            'http' => [
                'method' => 'GET',
                'header' => "Authorization: Basic $auth\r\nUser-Agent: PeopleAndPixel/1.0\r\nAccept: application/json\r\n",
                'timeout' => 6,
                'ignore_errors' => true,
            ],
        ];
        $ctx = stream_context_create($opts);
        $resp = @file_get_contents($url, false, $ctx);
        if ($resp === false) { return null; }
        $code = 0;
        if (isset($http_response_header[0]) && preg_match('#\s(\d{3})\s#', $http_response_header[0], $m)) { $code = (int)$m[1]; }
        if ($code >= 200 && $code < 300) {
            $json = json_decode($resp, true);
            return is_array($json) ? $json : null;
        }
        if ($code === 404) { return null; }
        throw new \RuntimeException('HTTP ' . $code);
    }

    /**
     * @param array<int,array{value:string,tag:string}> $list
     */
    private function listMissingContains(array $list, string $value): bool
    {
        foreach ($list as $row) {
            if (isset($row['value']) && (string)$row['value'] === $value) return false;
        }
        return true;
    }
}
