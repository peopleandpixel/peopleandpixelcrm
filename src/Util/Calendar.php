<?php

declare(strict_types=1);

namespace App\Util;

/**
 * Calendar event aggregator.
 * Produces a flat list of events (date or range) from existing data stores.
 */
final class Calendar
{
    /**
     * Build calendar events from given datasets.
     *
     * @param array $contacts array of contacts (expects keys: id?, name, birthdate)
     * @param array $projects array of projects (expects: id, name, start_date, end_date, status)
     * @param array $tasks array of tasks (expects: id, title, due_date, project_id, status)
     * @param array $filters types to include: ['birthday','project','task'] (empty => all)
     * @param string|null $from ISO date Y-m-d
     * @param string|null $to ISO date Y-m-d (inclusive)
     * @return array<int, array<string, mixed>>
     */
    public static function buildEvents(array $contacts, array $projects, array $tasks, array $filters = [], ?string $from = null, ?string $to = null): array
    {
        $includeAll = empty($filters);
        $wantBirthday = $includeAll || in_array('birthday', $filters, true);
        $wantProject = $includeAll || in_array('project', $filters, true);
        $wantTask = $includeAll || in_array('task', $filters, true);

        $events = [];

        // Normalize range
        $fromDate = $from && Dates::isValid($from, 'Y-m-d') ? $from : null;
        $toDate = $to && Dates::isValid($to, 'Y-m-d') ? $to : null;

        // Birthdays (contacts)
        if ($wantBirthday) {
            foreach ($contacts as $c) {
                $b = (string)($c['birthdate'] ?? '');
                if ($b === '' || !Dates::isValid($b, 'Y-m-d')) { continue; }
                // Create event for each year in range, or for current year if no range
                $name = (string)($c['name'] ?? '');
                $baseMonthDay = substr($b, 5); // MM-dd
                $years = [];
                if ($fromDate && $toDate) {
                    $yf = (int)substr($fromDate, 0, 4);
                    $yt = (int)substr($toDate, 0, 4);
                    for ($y = $yf; $y <= $yt; $y++) { $years[] = $y; }
                } else {
                    $years[] = (int)date('Y');
                }
                foreach ($years as $y) {
                    $date = sprintf('%04d-%s', $y, $baseMonthDay);
                    if (!self::inRange($date, $date, $fromDate, $toDate)) { continue; }
                    $events[] = [
                        'type' => 'birthday',
                        'title' => $name !== '' ? ($name . ' – Birthday') : 'Birthday',
                        'start' => $date,
                        'end' => $date,
                        'allDay' => true,
                        'color' => '#ffb347',
                        'id' => 'bday:' . ($c['id'] ?? $name . ':' . $b),
                        'url' => url('/contacts/view', ['id' => $c['id'] ?? null]),
                    ];
                }
            }
        }

        // Projects (ranges)
        if ($wantProject) {
            foreach ($projects as $p) {
                $start = (string)($p['start_date'] ?? '');
                $end = (string)($p['end_date'] ?? '');
                if ($start === '' && $end === '') { continue; }
                if ($start !== '' && !Dates::isValid($start, 'Y-m-d')) { $start = ''; }
                if ($end !== '' && !Dates::isValid($end, 'Y-m-d')) { $end = ''; }
                // If only one side present, make single-day
                $s = $start !== '' ? $start : $end;
                $e = $end !== '' ? $end : $start;
                if ($s === '' && $e === '') { continue; }
                if (!self::rangesOverlap($s, $e, $fromDate, $toDate)) { continue; }
                $events[] = [
                    'type' => 'project',
                    'title' => (string)($p['name'] ?? 'Project'),
                    'start' => $s,
                    'end' => $e,
                    'allDay' => true,
                    'color' => '#6fa8dc',
                    'id' => 'proj:' . ($p['id'] ?? md5(json_encode($p))),
                    'url' => url('/projects/view', ['id' => $p['id'] ?? null]),
                ];
            }
        }

        // Tasks (due dates)
        if ($wantTask) {
            foreach ($tasks as $t) {
                $due = (string)($t['due_date'] ?? '');
                if ($due === '' || !Dates::isValid($due, 'Y-m-d')) { continue; }
                if (!self::inRange($due, $due, $fromDate, $toDate)) { continue; }
                $events[] = [
                    'type' => 'task',
                    'title' => (string)($t['title'] ?? 'Task'),
                    'start' => $due,
                    'end' => $due,
                    'allDay' => true,
                    'color' => '#93c47d',
                    'id' => 'task:' . ($t['id'] ?? md5(json_encode($t))),
                    'url' => url('/tasks/view', ['id' => $t['id'] ?? null]),
                ];
            }
        }

        // Sort by start date then type
        usort($events, function($a, $b) {
            $cmp = strcmp($a['start'], $b['start']);
            if ($cmp !== 0) return $cmp;
            return strcmp($a['type'], $b['type']);
        });
        return $events;
    }

    private static function inRange(string $s, string $e, ?string $from, ?string $to): bool
    {
        if ($from === null && $to === null) return true;
        return self::rangesOverlap($s, $e, $from, $to);
    }

    private static function rangesOverlap(string $s, string $e, ?string $from, ?string $to): bool
    {
        $left = $from ?? $s;
        $right = $to ?? $e;
        return !($e < $left || $s > $right);
    }
}
