<?php

namespace App;

class JsonStore implements StoreInterface
{
    private string $file;
    /**
     * Per-process, per-request cache to avoid repeated disk I/O.
     * @var array<string, array{mtime:int,data:array}>
     */
    private static array $cache = [];

    public function __construct(string $file)
    {
        $this->file = $file;
        $dir = dirname($file);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        if (!file_exists($file)) {
            // Create an empty JSON array file atomically
            $this->atomicWrite(json_encode([]));
        }
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function all(): array
    {
        $data = $this->readJsonLocked();
        return is_array($data) ? $data : [];
    }

    public function paginate(int $limit, int $offset = 0, ?string $sort = 'id', string $dir = 'asc', array $filters = []): array
    {
        $items = $this->all();
        // Apply filters (exact match)
        if ($filters) {
            $items = array_values(array_filter($items, function ($it) use ($filters) {
                foreach ($filters as $k => $v) {
                    if (!array_key_exists($k, $it)) { return false; }
                    if ($it[$k] !== $v) { return false; }
                }
                return true;
            }));
        }
        $total = count($items);
        // Sort
        if ($sort) {
            $direction = strtolower($dir) === 'desc' ? -1 : 1;
            usort($items, function ($a, $b) use ($sort, $direction) {
                $av = $a[$sort] ?? null;
                $bv = $b[$sort] ?? null;
                if ($av == $bv) return 0;
                return ($av <=> $bv) * $direction;
            });
        }
        $limit = max(1, $limit);
        $offset = max(0, $offset);
        $slice = array_slice($items, $offset, $limit);
        return [
            'items' => $slice,
            'total' => $total,
            'limit' => $limit,
            'offset' => $offset,
            'sort' => $sort,
            'dir' => $dir,
            'filters' => $filters,
        ];
    }

    /**
     * Get a single item by id or null if not found
     * @return array<string,mixed>|null
     */
    public function get(int $id): ?array
    {
        foreach ($this->all() as $it) {
            if ((int)($it['id'] ?? 0) === $id) {
                return $it;
            }
        }
        return null;
    }

    public function find(int $id): ?array
    {
        return $this->get($id);
    }

    /**
     * @param array<string,mixed> $item
     */
    public function add(array $item): array
    {
        $items = $this->all();
        $item['id'] = $item['id'] ?? $this->nextId($items);
        // set owner if not provided and a user is logged in
        if (!isset($item['owner_user_id'])) {
            $u = \App\Util\Auth::user();
            if (is_array($u)) {
                $item['owner_user_id'] = (int)($u['id'] ?? 0);
            }
        }
        // optimistic versioning and timestamps
        $now = \App\Util\Dates::nowAtom();
        $item['created_at'] = $item['created_at'] ?? $now;
        $item['updated_at'] = $item['updated_at'] ?? $now;
        $item['version'] = isset($item['version']) && is_int($item['version']) ? $item['version'] : 1;
        $items[] = $item;
        $this->saveAll($items);
        return $item;
    }

    /**
     * Update an item by id with provided fields
     * @param array<string,mixed> $fields
     */
    public function update(int $id, array $fields): ?array
    {
        $items = $this->all();
        $updated = null;
        foreach ($items as &$it) {
            if ((int)($it['id'] ?? 0) === $id) {
                // Optimistic concurrency: if caller supplied 'version', require match
                if (isset($fields['version']) && isset($it['version']) && is_int($fields['version']) && is_int($it['version'])) {
                    if ($fields['version'] !== $it['version']) {
                        // version conflict: do not update
                        return null;
                    }
                }
                foreach ($fields as $k => $v) {
                    if ($k !== 'id') {
                        $it[$k] = $v;
                    }
                }
                // bump version and set updated_at
                $it['version'] = isset($it['version']) && is_int($it['version']) ? $it['version'] + 1 : 1;
                $it['updated_at'] = \App\Util\Dates::nowAtom();
                $updated = $it;
                break;
            }
        }
        if ($updated !== null) {
            $this->saveAll($items);
        }
        return $updated;
    }

    public function delete(int $id): bool
    {
        // Restrict deletes that would violate referential integrity for known parents
        $this->assertDeletable($id);
        $items = $this->all();
        $new = [];
        $deleted = false;
        foreach ($items as $it) {
            if ((int)($it['id'] ?? 0) === $id) {
                $deleted = true;
                continue;
            }
            $new[] = $it;
        }
        if ($deleted) {
            $this->saveAll($new);
        }
        return $deleted;
    }

    /**
     * @param array<int,array<string,mixed>> $items
     */
    public function saveAll(array $items): void
    {
        // Validate referential integrity for known relationships before writing
        $this->validateReferentialIntegrity($items);
        // Encode with robust flags to avoid failures on invalid UTF-8
        $json = json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        if ($json === false) {
            // If encoding fails, do NOT overwrite the existing file. Surface an error instead.
            throw new \RuntimeException('Failed to encode JSON for save; refusing to overwrite existing data.');
        }
        $this->writeSafely($json);
    }

    /**
     * Read JSON with shared lock and integrity checks. If the main file is corrupted
     * but a valid .bak exists, restore from backup and return that data.
     * @return mixed
     */
    private function readJsonLocked()
    {
        $file = $this->file;
        // Fast path: use cached value if file mtime unchanged
        $mtime = @filemtime($file) ?: 0;
        if (isset(self::$cache[$file]) && (int)self::$cache[$file]['mtime'] === $mtime) {
            return self::$cache[$file]['data'];
        }
        $fh = @fopen($file, 'c+'); // create if missing
        if ($fh === false) {
            return [];
        }
        try {
            // Shared lock for readers
            if (!flock($fh, LOCK_SH)) {
                // If lock can't be acquired, return current in-memory best effort
                $contents = stream_get_contents($fh) ?: '';
                $data = json_decode($contents, true);
                return is_array($data) ? $data : [];
            }
            // Rewind and read
            rewind($fh);
            $contents = stream_get_contents($fh) ?: '';
            $data = json_decode($contents, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
                // Save to cache based on current mtime
                $mtimeNow = @filemtime($file) ?: 0;
                self::$cache[$file] = ['mtime' => $mtimeNow, 'data' => $data];
                return $data;
            }
        } finally {
            // Release shared lock
            if (is_resource($fh)) {
                flock($fh, LOCK_UN);
                fclose($fh);
            }
        }

        // If corrupted, try backup
        $bak = $this->file . '.bak';
        if (is_file($bak)) {
            $bakContents = @file_get_contents($bak);
            if ($bakContents !== false) {
                $bakData = json_decode($bakContents, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($bakData)) {
                    // Restore from backup atomically
                    $this->writeSafely(json_encode($bakData, JSON_PRETTY_PRINT));
                    return $bakData;
                }
            }
        }

        // Neither main nor backup is valid; start from empty but keep corrupted files for inspection
        $this->writeSafely(json_encode([], JSON_PRETTY_PRINT));
        return [];
    }

    /**
     * Perform a safe write with file locking, backup, and atomic rename.
     */
    private function writeSafely(string $json): void
    {
        $file = $this->file;
        $dir = dirname($file);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        // Acquire an exclusive lock on the target file
        $fh = fopen($file, 'c+'); // create if missing
        if ($fh === false) {
            // As a last resort attempt atomic write without locking
            $this->atomicWrite($json);
            return;
        }

        // Lock exclusively to serialize writers
        if (!flock($fh, LOCK_EX)) {
            fclose($fh);
            $this->atomicWrite($json);
            return;
        }

        try {
            // Create/refresh backup before altering the file
            rewind($fh);
            $current = stream_get_contents($fh) ?: '';
            $bakPath = $file . '.bak';
            @file_put_contents($bakPath, $current);

            // Windows-safe in-place write while holding the lock (avoid rename over open file)
            rewind($fh);
            if (!@ftruncate($fh, 0)) {
                throw new \RuntimeException('Failed to truncate file for writing');
            }
            $bytes = @fwrite($fh, $json);
            if ($bytes === false || $bytes < strlen($json)) {
                // Attempt to restore previous content before failing
                rewind($fh);
                @ftruncate($fh, 0);
                @fwrite($fh, $current);
                @fflush($fh);
                throw new \RuntimeException('Failed to write complete JSON to file');
            }
            @fflush($fh);
            if (function_exists('fsync')) {
                @fsync($fh);
            }
        } finally {
            flock($fh, LOCK_UN);
            fclose($fh);
        }
        // Update in-process cache after successful write
        $data = json_decode($json, true);
        if (is_array($data)) {
            $mtime = @filemtime($file) ?: time();
            self::$cache[$file] = ['mtime' => $mtime, 'data' => $data];
        }
    }

    /**
     * Atomic write to the main file via temp file + rename.
     */
    private function atomicWrite(string $json): void
    {
        $file = $this->file;
        $dir = dirname($file);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $tmp = $dir . DIRECTORY_SEPARATOR . basename($file) . '.tmp.' . getmypid() . '.' . uniqid('', true);
        $bytes = file_put_contents($tmp, $json, LOCK_EX);
        if ($bytes === false) {
            return; // Give up silently to avoid corrupting existing file
        }
        // Ensure contents flushed to disk
        $tfh = @fopen($tmp, 'r');
        if ($tfh) {
            @fflush($tfh);
            // fsync if available
            if (function_exists('fsync')) {
                @fsync($tfh);
            }
            fclose($tfh);
        }
        // Attempt atomic rename
        @rename($tmp, $file) || (@unlink($file) && @rename($tmp, $file));
        // Update in-process cache after atomic write
        $data = json_decode($json, true);
        if (is_array($data)) {
            $mtime = @filemtime($file) ?: time();
            self::$cache[$file] = ['mtime' => $mtime, 'data' => $data];
        }
    }

    /**
     * @param array<int,array<string,mixed>> $items
     */
    private function nextId(array $items): int
    {
        $max = 0;
        foreach ($items as $it) {
            $max = max($max, (int)($it['id'] ?? 0));
        }
        return $max + 1;
    }

    /**
     * Returns the last modification timestamp of the underlying JSON file.
     * Useful for building HTTP caching headers.
     */
    public function lastModified(): int
    {
        return @filemtime($this->file) ?: 0;
    }

    /**
     * Execute a callback while holding an exclusive lock on the JSON file.
     * This provides a transaction-like guard for multi-write operations.
     * @template T
     * @param callable():T $callback
     * @return mixed
     */
    public function withTransaction(callable $callback)
    {
        $fh = @fopen($this->file, 'c+');
        if ($fh === false) {
            // Fallback: just run the callback
            return $callback();
        }
        if (!flock($fh, LOCK_EX)) {
            fclose($fh);
            return $callback();
        }
        try {
            return $callback();
        } finally {
            flock($fh, LOCK_UN);
            fclose($fh);
        }
    }
    private function validateReferentialIntegrity(array $items): void
    {
        $entity = $this->inferEntity();
        $dir = dirname($this->file);
        // Build lookup sets from related files as needed
        if ($entity === 'times' || $entity === 'tasks') {
            $contacts = $this->loadAllFrom($dir, 'contacts');
            $employees = $this->loadAllFrom($dir, 'employees');
            $projects = $this->loadAllFrom($dir, 'projects');
            $tasks = $this->loadAllFrom($dir, 'tasks');
            $contactIds = array_column($contacts, 'id');
            $employeeIds = array_column($employees, 'id');
            $projectIds = array_column($projects, 'id');
            $taskIds = array_column($tasks, 'id');
            foreach ($items as $it) {
                if (isset($it['contact_id'])) {
                    $cid = (int)$it['contact_id'];
                    if ($cid !== 0 && !in_array($cid, $contactIds, true)) {
                        throw new \RuntimeException("Integrity error: $entity.contact_id references missing contacts.id=$cid");
                    }
                }
                if (isset($it['employee_id'])) {
                    $eid = (int)$it['employee_id'];
                    if ($eid !== 0 && !in_array($eid, $employeeIds, true)) {
                        throw new \RuntimeException("Integrity error: $entity.employee_id references missing employees.id=$eid");
                    }
                }
                if ($entity === 'tasks' && isset($it['project_id'])) {
                    $pid = (int)$it['project_id'];
                    if ($pid !== 0 && !in_array($pid, $projectIds, true)) {
                        throw new \RuntimeException("Integrity error: tasks.project_id references missing projects.id=$pid");
                    }
                }
                if ($entity === 'times' && isset($it['task_id'])) {
                    $tid = (int)$it['task_id'];
                    if ($tid !== 0 && !in_array($tid, $taskIds, true)) {
                        throw new \RuntimeException("Integrity error: times.task_id references missing tasks.id=$tid");
                    }
                }
            }
        } elseif ($entity === 'storage_adjustments') {
            $storage = $this->loadAllFrom($dir, 'storage');
            $itemIds = array_column($storage, 'id');
            foreach ($items as $it) {
                if (isset($it['item_id'])) {
                    $sid = (int)$it['item_id'];
                    if (!in_array($sid, $itemIds, true)) {
                        throw new \RuntimeException("Integrity error: storage_adjustments.item_id references missing storage.id=$sid");
                    }
                }
            }
        }
        // Other entities: no cross-file checks needed
    }

    private function assertDeletable(int $id): void
    {
        $entity = $this->inferEntity();
        $dir = dirname($this->file);
        if ($entity === 'contacts') {
            $times = $this->loadAllFrom($dir, 'times');
            foreach ($times as $t) {
                if ((int)($t['contact_id'] ?? 0) === $id) {
                    throw new \RuntimeException('Cannot delete contact: referenced by times');
                }
            }
            $tasks = $this->loadAllFrom($dir, 'tasks');
            foreach ($tasks as $t) {
                if ((int)($t['contact_id'] ?? 0) === $id) {
                    throw new \RuntimeException('Cannot delete contact: referenced by tasks');
                }
            }
        } elseif ($entity === 'employees') {
            $times = $this->loadAllFrom($dir, 'times');
            foreach ($times as $t) {
                if ((int)($t['employee_id'] ?? 0) === $id) {
                    throw new \RuntimeException('Cannot delete employee: referenced by times');
                }
            }
            $tasks = $this->loadAllFrom($dir, 'tasks');
            foreach ($tasks as $t) {
                if ((int)($t['employee_id'] ?? 0) === $id) {
                    throw new \RuntimeException('Cannot delete employee: referenced by tasks');
                }
            }
        } elseif ($entity === 'storage') {
            $adjs = $this->loadAllFrom($dir, 'storage_adjustments');
            foreach ($adjs as $a) {
                if ((int)($a['item_id'] ?? 0) === $id) {
                    throw new \RuntimeException('Cannot delete storage item: referenced by storage_adjustments');
                }
            }
        } elseif ($entity === 'projects') {
            $tasks = $this->loadAllFrom($dir, 'tasks');
            foreach ($tasks as $t) {
                if ((int)($t['project_id'] ?? 0) === $id) {
                    throw new \RuntimeException('Cannot delete project: referenced by tasks');
                }
            }
        }
    }

    private function inferEntity(): string
    {
        $base = basename($this->file);
        $name = strtolower(preg_replace('/\.json$/i', '', $base));
        return $name ?: '';
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    private function loadAllFrom(string $dir, string $entity): array
    {
        $path = rtrim($dir, '/') . '/' . $entity . '.json';
        if (!is_file($path)) { return []; }
        $store = new self($path);
        return $store->all();
    }
}
