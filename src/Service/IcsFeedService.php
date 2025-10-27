<?php

declare(strict_types=1);

namespace App\Service;

use App\Config;
use Psr\Log\LoggerInterface;

/**
 * Fetch and parse external ICS feeds (read-only) and expose as internal calendar events.
 */
final class IcsFeedService
{
    public function __construct(
        private readonly Config $config,
        private readonly LoggerInterface $logger
    ) {}

    /**
     * Fetch events from configured ICS feeds.
     * Env: ICS_FEEDS = comma-separated list of URLs or file paths.
     * Cache TTL: 15 minutes.
     *
     * @return array<int, array<string,mixed>>
     */
    public function fetchEvents(?string $from = null, ?string $to = null): array
    {
        $feedsCsv = trim($this->config->getEnv('ICS_FEEDS'));
        if ($feedsCsv === '') { return []; }
        $urls = array_values(array_filter(array_map('trim', explode(',', $feedsCsv)), fn($u) => $u !== ''));
        $all = [];
        foreach ($urls as $url) {
            try {
                $events = $this->fetchOne($url);
                foreach ($events as $ev) {
                    // Filter by range if provided
                    $s = (string)($ev['start'] ?? '');
                    $e = (string)($ev['end'] ?? $s);
                    if ($from && $s < $from) { if ($e < $from) continue; }
                    if ($to && $s > $to) { continue; }
                    $all[] = $ev;
                }
            } catch (\Throwable $e) {
                $this->logger->warning('ICS feed fetch failed', ['url' => $url, 'error' => $e->getMessage()]);
            }
        }
        // sort by start
        usort($all, fn($a,$b) => strcmp((string)$a['start'], (string)$b['start']));
        return $all;
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    private function fetchOne(string $url): array
    {
        $cacheKey = md5($url);
        $cacheDir = $this->config->getCacheDir() . '/ics';
        if (!is_dir($cacheDir)) { @mkdir($cacheDir, 0777, true); }
        $cacheFile = $cacheDir . '/' . $cacheKey . '.json';
        $ttl = 15 * 60; // 15 minutes
        if (is_file($cacheFile) && (time() - filemtime($cacheFile)) < $ttl) {
            $json = @file_get_contents($cacheFile);
            if ($json !== false) {
                $arr = json_decode($json, true);
                if (is_array($arr)) { return $arr; }
            }
        }
        $raw = $this->getContents($url);
        if ($raw === '') { return []; }
        $events = $this->parseIcs($raw, $url);
        // cache
        @file_put_contents($cacheFile, json_encode($events));
        return $events;
    }

    private function getContents(string $url): string
    {
        // Allow file paths
        if (is_file($url)) {
            $data = @file_get_contents($url);
            return $data === false ? '' : $data;
        }
        $opts = [
            'http' => [
                'method' => 'GET',
                'header' => "User-Agent: PeopleAndPixel/1.0\r\nAccept: text/calendar, */*\r\n",
                'timeout' => 8,
            ]
        ];
        $ctx = stream_context_create($opts);
        $data = @file_get_contents($url, false, $ctx);
        return $data === false ? '' : $data;
    }

    /**
     * Minimal ICS parser: extracts VEVENT DTSTART/DTEND/SUMMARY/URL/UID/ALLDAY
     * and maps to internal event format type=external.
     * @return array<int,array<string,mixed>>
     */
    private function parseIcs(string $ics, string $source): array
    {
        // Unfold RFC5545 lines (lines starting with space or tab continue previous)
        $ics = str_replace(["\r\n", "\r"], "\n", $ics);
        $lines = preg_split('/\n/', $ics) ?: [];
        $unfolded = [];
        foreach ($lines as $line) {
            if ($line !== '' && ($line[0] === ' ' || $line[0] === "\t")) {
                $unfolded[count($unfolded)-1] .= substr($line, 1);
            } else {
                $unfolded[] = $line;
            }
        }
        $events = [];
        $in = false;
        $cur = [];
        foreach ($unfolded as $ln) {
            if (str_starts_with($ln, 'BEGIN:VEVENT')) { $in = true; $cur = []; continue; }
            if (str_starts_with($ln, 'END:VEVENT')) {
                if (!empty($cur)) {
                    $events[] = $this->eventFromParsed($cur, $source);
                }
                $in = false; $cur = [];
                continue;
            }
            if (!$in) { continue; }
            // Split name;params:value
            $pos = strpos($ln, ':');
            if ($pos === false) { continue; }
            $nameParams = substr($ln, 0, $pos);
            $value = substr($ln, $pos + 1);
            $nameParts = explode(';', $nameParams);
            $name = strtoupper($nameParts[0] ?? '');
            $params = [];
            foreach (array_slice($nameParts, 1) as $p) {
                $kv = explode('=', $p, 2);
                if (count($kv) === 2) { $params[strtoupper($kv[0])] = $kv[1]; }
            }
            $cur[$name] = ['value' => $value, 'params' => $params];
        }
        // Filter out nulls
        $events = array_values(array_filter($events, fn($e) => isset($e['start'])));
        return $events;
    }

    /**
     * @param array<string,array{value:string,params:array<string,string>}> $map
     * @return array<string,mixed>
     */
    private function eventFromParsed(array $map, string $source): array
    {
        $summary = $map['SUMMARY']['value'] ?? 'Event';
        $url = $map['URL']['value'] ?? '';
        $uid = $map['UID']['value'] ?? null;
        // DTSTART/DTEND may be DATE or DATE-TIME; detect VALUE=DATE param
        $isAllDay = false;
        $startRaw = $map['DTSTART']['value'] ?? null;
        $endRaw = $map['DTEND']['value'] ?? null;
        if ($startRaw === null) { return []; }
        $s = $this->normalizeDate($startRaw);
        $e = $endRaw ? $this->normalizeDate($endRaw) : $s;
        // If DTSTART/DTEND are dates without times, ICS DTEND is exclusive: subtract one day for end display
        if ($this->isDateOnly($startRaw)) {
            $isAllDay = true;
            if ($endRaw) {
                $e = $this->formatYmd($this->dateModify($e, '-1 day'));
            }
        }
        $id = $uid ?: 'ext:' . md5($summary . '|' . $s . '|' . (string)$e . '|' . $source);
        return [
            'type' => 'external',
            'title' => $summary,
            'start' => $s,
            'end' => $e,
            'allDay' => $isAllDay || $this->isDateOnly($startRaw),
            'color' => '#b4a7d6',
            'id' => $id,
            'url' => $url,
        ];
    }

    private function isDateOnly(string $raw): bool
    {
        // Date-only like 20250101
        return preg_match('/^\d{8}$/', $raw) === 1;
    }

    private function normalizeDate(string $raw): string
    {
        // Accept YYYYMMDD or ISO datetime; return Y-m-d for calendar list
        if (preg_match('/^(\d{4})(\d{2})(\d{2})$/', $raw, $m)) {
            return sprintf('%s-%s-%s', $m[1], $m[2], $m[3]);
        }
        // Date-time: 20250101T130000Z or without Z
        if (preg_match('/^(\d{4})(\d{2})(\d{2})T(\d{2})(\d{2})(\d{2})Z?$/', $raw, $m)) {
            return sprintf('%s-%s-%s', $m[1], $m[2], $m[3]);
        }
        // Fallback: try strtotime
        $ts = strtotime($raw);
        if ($ts !== false) { return date('Y-m-d', $ts); }
        return date('Y-m-d');
    }

    private function dateModify(string $ymd, string $mod): \DateTimeImmutable
    {
        $dt = \DateTimeImmutable::createFromFormat('Y-m-d', $ymd) ?: new \DateTimeImmutable($ymd);
        return $dt->modify($mod);
    }

    private function formatYmd(\DateTimeImmutable $dt): string
    {
        return $dt->format('Y-m-d');
    }
}
