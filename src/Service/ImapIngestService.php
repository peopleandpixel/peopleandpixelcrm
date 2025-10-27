<?php

declare(strict_types=1);

namespace App\Service;

use App\Config;
use Psr\Log\LoggerInterface;

/**
 * Optional IMAP email ingest. Safe no-op if not configured or IMAP extension missing.
 * Stores ingested metadata into data/emails_ingest.json
 */
final class ImapIngestService
{
    public function __construct(
        private readonly Config $config,
        private readonly LoggerInterface $logger
    ) {}

    public function isEnabled(): bool
    {
        return $this->config->getEnv('IMAP_HOST') !== '' && function_exists('imap_open');
    }

    /**
     * Fetch unread messages (lightweight headers only) and append to emails_ingest.json
     * @return array{ok:bool, ingested:int, error?:string}
     */
    public function ingestOnce(int $limit = 20): array
    {
        if (!$this->isEnabled()) {
            return ['ok' => false, 'ingested' => 0, 'error' => 'IMAP not configured or extension missing'];
        }
        $host = $this->config->getEnv('IMAP_HOST');
        $user = $this->config->getEnv('IMAP_USER');
        $pass = $this->config->getEnv('IMAP_PASS');
        $mailbox = $this->config->getEnv('IMAP_MAILBOX') ?: 'INBOX';
        $options = $this->config->getEnv('IMAP_OPTIONS') ?: '/imap/ssl/novalidate-cert';
        $mailboxPath = sprintf('{%s}%s', $host . $options, $mailbox);
        try {
            $imap = @imap_open($mailboxPath, $user, $pass, 0, 1, ['DISABLE_AUTHENTICATOR' => 'GSSAPI']);
            if ($imap === false) {
                return ['ok' => false, 'ingested' => 0, 'error' => 'imap_open failed'];
            }
            // Search unread
            $ids = @imap_search($imap, 'UNSEEN', SE_UID) ?: [];
            // Limit
            $ids = array_slice($ids, 0, $limit);
            $items = [];
            foreach ($ids as $uid) {
                $header = @imap_headerinfo($imap, imap_msgno($imap, $uid));
                if (!$header) { continue; }
                $items[] = [
                    'uid' => $uid,
                    'subject' => isset($header->subject) ? imap_mime_header_decode((string)$header->subject)[0]->text : '',
                    'from' => isset($header->from) ? ($header->from[0]->mailbox . '@' . $header->from[0]->host) : '',
                    'to' => isset($header->to) ? ($header->to[0]->mailbox . '@' . $header->to[0]->host) : '',
                    'date' => isset($header->date) ? date('c', strtotime((string)$header->date)) : date('c'),
                    'message_id' => isset($header->message_id) ? (string)$header->message_id : null,
                ];
                // Mark seen to avoid re-processing next run
                @imap_setflag_full($imap, (string)imap_msgno($imap, $uid), "\\Seen");
            }
            @imap_close($imap);
            if (!empty($items)) {
                $this->appendJson($items);
            }
            return ['ok' => true, 'ingested' => count($items)];
        } catch (\Throwable $e) {
            $this->logger->error('IMAP ingest error', ['error' => $e->getMessage()]);
            return ['ok' => false, 'ingested' => 0, 'error' => $e->getMessage()];
        }
    }

    /**
     * @param array<int,array<string,mixed>> $items
     */
    private function appendJson(array $items): void
    {
        $path = $this->config->jsonPath('emails_ingest.json');
        $existing = [];
        if (is_file($path)) {
            $json = @file_get_contents($path);
            $arr = $json !== false ? json_decode($json, true) : null;
            if (is_array($arr)) { $existing = $arr; }
        }
        // Keep max 1000 entries
        $merged = array_slice(array_merge($items, $existing), 0, 1000);
        @file_put_contents($path, json_encode($merged, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
    }
}
