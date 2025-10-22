<?php

declare(strict_types=1);

namespace App\Domain\Repository;

/**
 * Tasks repository boundary.
 *
 * This refines the generic StoreInterface to the needs of the Tasks aggregate
 * without leaking the underlying persistence (JSON/DB).
 */
interface TasksRepositoryInterface
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(): array;

    /**
     * @param array<string, mixed> $filters
     * @return array{items: array<int, array<string, mixed>>, total: int, limit: int, offset: int, sort?: string, dir?: string, filters?: array<string,mixed>}
     */
    public function paginate(int $limit, int $offset = 0, ?string $sort = 'id', string $dir = 'asc', array $filters = []): array;

    /**
     * @return array<string, mixed>|null
     */
    public function get(int $id): ?array;

    /**
     * Alias of get()
     * @return array<string, mixed>|null
     */
    public function find(int $id): ?array;

    /**
     * @param array<string, mixed> $item
     * @return array<string, mixed>
     */
    public function add(array $item): array;

    /**
     * @param array<string, mixed> $fields
     * @return array<string, mixed>|null
     */
    public function update(int $id, array $fields): ?array;

    public function delete(int $id): bool;

    /**
     * @template T
     * @param callable():T $callback
     * @return T
     */
    public function withTransaction(callable $callback);
}
