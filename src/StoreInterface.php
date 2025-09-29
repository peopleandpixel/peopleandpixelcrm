<?php

namespace App;

/**
 * Persistence abstraction for entity stores.
 *
 * Implementations should provide at least in-memory/JSON or DB-backed storage
 * with a uniform API used by controllers.
 */
interface StoreInterface
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(): array;

    /**
     * Paginated listing with optional exact-match filters and simple sorting.
     * @param array<string, mixed> $filters Exact-match filters on fields (implementation may ignore unknown fields)
     * @return array{items: array<int, array<string, mixed>>, total: int, limit: int, offset: int, sort?: string, dir?: string, filters?: array<string,mixed>}
     */
    public function paginate(int $limit, int $offset = 0, ?string $sort = 'id', string $dir = 'asc', array $filters = []): array;

    /**
     * @param array<string, mixed> $item
     * @return array<string, mixed>
     */
    public function add(array $item): array;

    /**
     * Replace all items with the provided list.
     * @param array<int, array<string, mixed>> $items
     */
    public function saveAll(array $items): void;

    /**
     * Optional convenience: find an item by id (alias of get()).
     * @return array<string, mixed>|null
     */
    public function find(int $id): ?array;

    /**
     * Get an item by id.
     * @return array<string, mixed>|null
     */
    public function get(int $id): ?array;

    /**
     * Update an item by id with provided fields.
     * Implementations MAY support optimistic concurrency by respecting a provided 'version' field in $fields.
     * @param array<string, mixed> $fields
     * @return array<string, mixed>|null
     */
    public function update(int $id, array $fields): ?array;

    /**
     * Delete an item by id.
     */
    public function delete(int $id): bool;

    /**
     * Execute a set of operations atomically if supported by the backend.
     * Implementations should guarantee either all-or-nothing semantics for the callback body.
     * The callback may return a value which will be returned to the caller.
     * @template T
     * @param callable():T $callback
     * @return T
     */
    public function withTransaction(callable $callback);
}
