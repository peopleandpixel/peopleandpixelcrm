<?php

declare(strict_types=1);

namespace App\Service;

/**
 * Very lightweight in-memory search across main entities.
 * Works with JSON or DB stores via ->all().
 */
final class SearchService
{
    /** @var array<int, array{type:string,id:mixed,title:string,subtitle:string,url:string,score:int}> */
    private array $index = [];
    private bool $built = false;

    public function __construct(
        private readonly object $contactsStore,
        private readonly object $tasksStore,
        private readonly object $dealsStore,
        private readonly object $projectsStore
    ) {}

    /** Build the index on first use. */
    private function ensureBuilt(): void
    {
        if ($this->built) return;
        $this->index = [];

        // Contacts
        try {
            foreach ($this->contactsStore->all() as $c) {
                $id = $c['id'] ?? null;
                $name = trim((string)($c['name'] ?? ''));
                $email = trim((string)($c['email'] ?? ''));
                $phone = trim((string)($c['phone'] ?? ''));
                $tags = $c['tags'] ?? [];
                $subtitle = implode(' · ', array_values(array_filter([$email, $phone])));
                $title = $name !== '' ? $name : ($email ?: 'Contact');
                $this->index[] = [
                    'type' => 'contact',
                    'id' => $id,
                    'title' => $title,
                    'subtitle' => $subtitle,
                    'url' => url('/contacts/view', ['id' => $id]),
                    'score' => 0,
                    '_t' => strtolower($title . ' ' . $email . ' ' . $phone . ' ' . implode(' ', is_array($tags) ? $tags : [])),
                ];
            }
        } catch (\Throwable $e) {}

        // Tasks
        try {
            foreach ($this->tasksStore->all() as $t) {
                $id = $t['id'] ?? null;
                $title = trim((string)($t['title'] ?? 'Task'));
                $desc = trim((string)($t['description'] ?? '')); // may not exist
                $projectId = $t['project_id'] ?? null;
                $subtitle = $desc !== '' ? mb_strimwidth($desc, 0, 80, '…') : '';
                $this->index[] = [
                    'type' => 'task',
                    'id' => $id,
                    'title' => $title !== '' ? $title : 'Task',
                    'subtitle' => $subtitle,
                    'url' => url('/tasks/view', ['id' => $id]),
                    'score' => 0,
                    '_t' => strtolower(($title ?: '') . ' ' . $desc . ' project:' . (string)$projectId),
                ];
            }
        } catch (\Throwable $e) {}

        // Deals (if enabled)
        try {
            foreach ($this->dealsStore->all() as $d) {
                $id = $d['id'] ?? null;
                $title = trim((string)($d['title'] ?? 'Deal'));
                $stage = trim((string)($d['stage'] ?? ''));
                $value = (string)($d['value'] ?? '');
                $subtitle = implode(' · ', array_values(array_filter([$stage, $value])));
                $this->index[] = [
                    'type' => 'deal',
                    'id' => $id,
                    'title' => $title !== '' ? $title : 'Deal',
                    'subtitle' => $subtitle,
                    'url' => url('/deals/view', ['id' => $id]),
                    'score' => 0,
                    '_t' => strtolower(($title ?: '') . ' ' . $stage . ' ' . $value),
                ];
            }
        } catch (\Throwable $e) {}

        // Projects (to help finding context)
        try {
            foreach ($this->projectsStore->all() as $p) {
                $id = $p['id'] ?? null;
                $name = trim((string)($p['name'] ?? 'Project'));
                $subtitle = trim((string)($p['status'] ?? ''));
                $this->index[] = [
                    'type' => 'project',
                    'id' => $id,
                    'title' => $name !== '' ? $name : 'Project',
                    'subtitle' => $subtitle,
                    'url' => url('/projects/view', ['id' => $id]),
                    'score' => 0,
                    '_t' => strtolower(($name ?: '') . ' ' . $subtitle),
                ];
            }
        } catch (\Throwable $e) {}

        $this->built = true;
    }

    /**
     * Search the index and return ranked results.
     * @return array<int, array{type:string,id:mixed,title:string,subtitle:string,url:string,score:int}>
     */
    public function search(string $q, int $limit = 20): array
    {
        $this->ensureBuilt();
        $q = trim($q);
        if ($q === '') return [];
        $qLower = strtolower($q);
        $tokens = preg_split('/\s+/', $qLower) ?: [];

        $results = [];
        foreach ($this->index as $row) {
            $hay = $row['_t'] ?? '';
            if ($hay === '') continue;
            $score = 0;
            // exact substring match bonus
            if (str_contains($hay, $qLower)) { $score += 10; }
            // token matches
            foreach ($tokens as $tok) {
                if ($tok === '') continue;
                if (str_contains($hay, $tok)) { $score += 3; }
            }
            if ($score > 0) {
                $copy = $row; unset($copy['_t']);
                $copy['score'] = $score;
                $results[] = $copy;
            }
        }
        usort($results, function($a, $b) {
            if ($a['score'] !== $b['score']) return $b['score'] <=> $a['score'];
            // Prefer contacts, tasks, deals then projects
            $rank = ['contact'=>3,'task'=>2,'deal'=>1,'project'=>0];
            $ra = $rank[$a['type']] ?? 0; $rb = $rank[$b['type']] ?? 0;
            if ($ra !== $rb) return $rb <=> $ra;
            return strcmp((string)$a['title'], (string)$b['title']);
        });
        if ($limit > 0 && count($results) > $limit) {
            $results = array_slice($results, 0, $limit);
        }
        return $results;
    }
}
