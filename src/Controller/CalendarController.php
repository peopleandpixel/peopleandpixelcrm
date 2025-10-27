<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\IcsFeedService;
use App\Util\Calendar as CalendarUtil;
use App\Util\Dates;

final class CalendarController
{
    private readonly object $contactsStore;
    private readonly object $projectsStore;
    private readonly object $tasksStore;
    private readonly ?IcsFeedService $icsFeed;

    public function __construct(object $contactsStore, object $projectsStore, object $tasksStore, ?IcsFeedService $icsFeed = null)
    {
        $this->contactsStore = $contactsStore;
        $this->projectsStore = $projectsStore;
        $this->tasksStore = $tasksStore;
        $this->icsFeed = $icsFeed;
    }

    public function index(): void
    {
        render('calendar/index', [
            'title' => __('Calendar'),
        ]);
    }

    public function events(): void
    {
        $types = isset($_GET['types']) ? (string)$_GET['types'] : '';
        $filters = array_values(array_filter(array_map('trim', $types !== '' ? explode(',', $types) : [])));
        $from = isset($_GET['from']) ? (string)$_GET['from'] : null;
        $to = isset($_GET['to']) ? (string)$_GET['to'] : null;

        $contacts = $this->contactsStore->all();
        $projects = $this->projectsStore->all();
        $tasks = $this->tasksStore->all();

        $events = CalendarUtil::buildEvents($contacts, $projects, $tasks, $filters, $from, $to);
        // Merge external ICS feeds if configured
        if ($this->icsFeed) {
            try {
                $ext = $this->icsFeed->fetchEvents($from, $to);
                // If filters provided and not including 'external', respect filters
                if (!empty($filters) && !in_array('external', $filters, true)) {
                    // keep ext only if includeAll; otherwise skip
                } else {
                    $events = array_merge($events, $ext);
                }
            } catch (\Throwable) { /* ignore */ }
        }
        // Resort
        usort($events, fn($a,$b) => strcmp($a['start'], $b['start']));

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['events' => $events], JSON_UNESCAPED_UNICODE);
    }

    /**
     * ICS feed for calendar events.
     * Query params:
     * - types: csv of birthday,project,task
     * - from, to: ISO dates (Y-m-d)
     */
    public function ics(): void
    {
        $types = isset($_GET['types']) ? (string)$_GET['types'] : '';
        $filters = array_values(array_filter(array_map('trim', $types !== '' ? explode(',', $types) : [])));
        $from = isset($_GET['from']) ? (string)$_GET['from'] : null;
        $to = isset($_GET['to']) ? (string)$_GET['to'] : null;

        $contacts = $this->contactsStore->all();
        $projects = $this->projectsStore->all();
        $tasks = $this->tasksStore->all();

        $events = CalendarUtil::buildEvents($contacts, $projects, $tasks, $filters, $from, $to);
        // Merge external ICS feeds if configured
        if ($this->icsFeed) {
            try {
                $ext = $this->icsFeed->fetchEvents($from, $to);
                if (!empty($filters) && !in_array('external', $filters, true)) {
                    // skip external
                } else {
                    $events = array_merge($events, $ext);
                }
            } catch (\Throwable) { /* ignore */ }
        }
        // Resort
        usort($events, fn($a,$b) => strcmp($a['start'], $b['start']));

        // Build ICS
        $lines = [];
        $lines[] = 'BEGIN:VCALENDAR';
        $lines[] = 'VERSION:2.0';
        $lines[] = 'PRODID:-//People & Pixel//Calendar//EN';
        $now = (new \DateTimeImmutable('now'))->format('Ymd\THis\Z');
        foreach ($events as $ev) {
            $uid = self::makeUid((string)($ev['id'] ?? md5(json_encode($ev))));
            $summary = self::icsEscape((string)($ev['title'] ?? 'Event'));
            $url = isset($ev['url']) && $ev['url'] ? (string)$ev['url'] : '';
            $start = (string)$ev['start'];
            $end = (string)$ev['end'];
            $allDay = (bool)($ev['allDay'] ?? false);

            $lines[] = 'BEGIN:VEVENT';
            $lines[] = 'UID:' . $uid;
            $lines[] = 'DTSTAMP:' . $now;
            if ($allDay) {
                $lines[] = 'DTSTART;VALUE=DATE:' . str_replace('-', '', $start);
                // DTEND is non-inclusive in ICS; add +1 day for single/all-day range
                // For our all-day entries with the same start/end, add one day to end
                $dtEnd = $end !== '' ? $end : $start;
                $dtEndPlus = (new \DateTimeImmutable($dtEnd . ' 00:00:00'))->modify('+1 day')->format('Ymd');
                $lines[] = 'DTEND;VALUE=DATE:' . $dtEndPlus;
            } else {
                // Not used currently, but keep for future time-based events
                $lines[] = 'DTSTART:' . (new \DateTimeImmutable($start))->format('Ymd\THis\Z');
                $lines[] = 'DTEND:' . (new \DateTimeImmutable($end ?: $start))->format('Ymd\THis\Z');
            }
            $lines[] = 'SUMMARY:' . $summary;
            if ($url !== '') { $lines[] = 'URL:' . self::icsEscape($url); }
            $lines[] = 'END:VEVENT';
        }
        $lines[] = 'END:VCALENDAR';

        header('Content-Type: text/calendar; charset=utf-8');
        header('Content-Disposition: inline; filename="peopleandpixel.ics"');
        echo implode("\r\n", $lines) . "\r\n";
    }

    private static function icsEscape(string $text): string
    {
        $text = str_replace(['\\', ';', ',', "\n", "\r"], ['\\\\', '\\;', '\\,', '\\n', ''], $text);
        return $text;
    }

    private static function makeUid(string $id): string
    {
        // Stable UID per event scope
        return $id . '@peopleandpixel.local';
    }
}
