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
    private bool $ftsReady = false;
    private ?\PDO $pdo = null;

    public function __construct(
        private readonly \App\Config $config,
        private readonly object $contactsStore,
        private readonly object $tasksStore,
        private readonly object $dealsStore,
        private readonly object $projectsStore
    ) {}

    /** Build the index on first use. Uses a cached index in JSON mode for performance. */
    private function ensureBuilt(): void
    {
        if ($this->built) return;

        // Try using SQLite FTS5 when DB mode is enabled and DSN points to SQLite
        if ($this->config->useDb() && $this->initSqliteFts()) {
            $this->ftsReady = true;
            $this->built = true; // no in-memory index required for FTS mode
            return;
        }

        // JSON mode or no FTS: use cached in-memory index built from stores
        $cacheFile = $this->searchCacheFile();
        $sig = $this->dataSignature();
        if ($cacheFile && is_file($cacheFile)) {
            $raw = @file_get_contents($cacheFile);
            $data = $raw ? json_decode($raw, true) : null;
            if (is_array($data) && ($data['_sig'] ?? null) === $sig && is_array($data['index'] ?? null)) {
                $this->index = $data['index'];
                $this->built = true;
                return;
            }
        }

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
                $desc = trim((string)($t['description'] ?? ''));
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

        // Persist cache for next time
        if ($cacheFile) {
            @file_put_contents($cacheFile, json_encode(['_sig' => $sig, 'index' => $this->index]));
        }

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

        if ($this->ftsReady && $this->pdo instanceof \PDO) {
            return $this->ftsSearch($q, $limit);
        }

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

    /** Build a small signature from JSON data file mtimes to detect changes. */
    private function dataSignature(): string
    {
        // Only meaningful in JSON mode
        $parts = [];
        foreach (['contacts','tasks','deals','projects'] as $name) {
            $file = $this->config->jsonPath($name . '.json');
            $mtime = @filemtime($file) ?: 0;
            $parts[] = $name . ':' . (string)$mtime;
        }
        return sha1(implode('|', $parts));
    }

    /** Return path to cached index file (JSON mode). */
    private function searchCacheFile(): ?string
    {
        $dir = $this->config->getCacheDir() . '/search';
        if (!is_dir($dir)) { @mkdir($dir, 0777, true); }
        return $dir . '/index.json';
    }

    /** Try to initialize SQLite FTS5 virtual table and populate it. */
    private function initSqliteFts(): bool
    {
        $dsn = $this->config->getDbDsn();
        if (!$dsn || !str_starts_with($dsn, 'sqlite:')) return false;
        try {
            $this->pdo = new \PDO($dsn, null, null, [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
        } catch (\Throwable $e) {
            $this->pdo = null; return false;
        }
        try {
            $this->pdo->exec("CREATE VIRTUAL TABLE IF NOT EXISTS search_index USING fts5(type, id, title, subtitle, content)");
            // Rebuild index on startup (simple approach); could optimize later
            $this->pdo->exec("DELETE FROM search_index");
            $ins = $this->pdo->prepare("INSERT INTO search_index(type,id,title,subtitle,content) VALUES(?,?,?,?,?)");

            // Contacts
            try {
                foreach ($this->contactsStore->all() as $c) {
                    $id = (string)($c['id'] ?? '');
                    $name = trim((string)($c['name'] ?? ''));
                    $email = trim((string)($c['email'] ?? ''));
                    $phone = trim((string)($c['phone'] ?? ''));
                    $tags = is_array($c['tags'] ?? null) ? implode(' ', $c['tags']) : '';
                    $title = $name !== '' ? $name : ($email ?: 'Contact');
                    $subtitle = implode(' · ', array_values(array_filter([$email, $phone])));
                    $content = strtolower(trim($title . ' ' . $email . ' ' . $phone . ' ' . $tags));
                    $ins->execute(['contact', $id, $title, $subtitle, $content]);
                }
            } catch (\Throwable $e) {}
            // Tasks
            try {
                foreach ($this->tasksStore->all() as $t) {
                    $id = (string)($t['id'] ?? '');
                    $title = trim((string)($t['title'] ?? 'Task'));
                    $desc = trim((string)($t['description'] ?? ''));
                    $subtitle = $desc !== '' ? mb_strimwidth($desc, 0, 80, '…') : '';
                    $content = strtolower(trim($title . ' ' . $desc));
                    $ins->execute(['task', $id, $title !== '' ? $title : 'Task', $subtitle, $content]);
                }
            } catch (\Throwable $e) {}
            // Deals
            try {
                foreach ($this->dealsStore->all() as $d) {
                    $id = (string)($d['id'] ?? '');
                    $title = trim((string)($d['title'] ?? 'Deal'));
                    $stage = trim((string)($d['stage'] ?? ''));
                    $value = (string)($d['value'] ?? '');
                    $subtitle = implode(' · ', array_values(array_filter([$stage, $value])));
                    $content = strtolower(trim($title . ' ' . $stage . ' ' . $value));
                    $ins->execute(['deal', $id, $title !== '' ? $title : 'Deal', $subtitle, $content]);
                }
            } catch (\Throwable $e) {}
            // Projects
            try {
                foreach ($this->projectsStore->all() as $p) {
                    $id = (string)($p['id'] ?? '');
                    $name = trim((string)($p['name'] ?? 'Project'));
                    $status = trim((string)($p['status'] ?? ''));
                    $content = strtolower(trim($name . ' ' . $status));
                    $ins->execute(['project', $id, $name !== '' ? $name : 'Project', $status, $content]);
                }
            } catch (\Throwable $e) {}
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /** Execute an FTS query using SQLite FTS5, returning normalized results. */
    private function ftsSearch(string $q, int $limit): array
    {
        if (!$this->pdo) return [];
        $limit = max(1, min(200, $limit));
        $stmt = $this->pdo->prepare("SELECT type,id,title,subtitle, bm25(search_index) AS score FROM search_index WHERE search_index MATCH :q ORDER BY score ASC LIMIT :lim");
        // Use a simple tokenized query
        $stmt->bindValue(':q', $q, \PDO::PARAM_STR);
        $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
        $ok = $stmt->execute();
        if (!$ok) return [];
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        $results = [];
        foreach ($rows as $r) {
            $url = match ($r['type']) {
                'contact' => url('/contacts/view', ['id' => $r['id']]),
                'task' => url('/tasks/view', ['id' => $r['id']]),
                'deal' => url('/deals/view', ['id' => $r['id']]),
                'project' => url('/projects/view', ['id' => $r['id']]),
                default => '#',
            };
            $results[] = [
                'type' => (string)$r['type'],
                'id' => $r['id'],
                'title' => (string)$r['title'],
                'subtitle' => (string)($r['subtitle'] ?? ''),
                'url' => $url,
                // In bm25, lower is better; convert to descending score approx
                'score' => (int)max(0, 10000 - (int)round((float)$r['score'] * 100)),
            ];
        }
        return $results;
    }
}
