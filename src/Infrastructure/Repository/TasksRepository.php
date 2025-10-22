<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Domain\Repository\TasksRepositoryInterface;
use App\StoreInterface;

/**
 * Persistence-agnostic Tasks repository that delegates to a StoreInterface.
 *
 * The Container already toggles between JsonStore and DbStore based on Config,
 * so this wrapper provides a stable domain boundary while preserving current
 * functionality.
 */
final class TasksRepository implements TasksRepositoryInterface
{
    public function __construct(private StoreInterface $store)
    {
    }

    public function all(): array
    {
        return $this->store->all();
    }

    public function paginate(int $limit, int $offset = 0, ?string $sort = 'id', string $dir = 'asc', array $filters = []): array
    {
        return $this->store->paginate($limit, $offset, $sort, $dir, $filters);
    }

    public function get(int $id): ?array
    {
        return $this->store->get($id);
    }

    public function find(int $id): ?array
    {
        return $this->store->find($id);
    }

    public function add(array $item): array
    {
        return $this->store->add($item);
    }

    public function update(int $id, array $fields): ?array
    {
        return $this->store->update($id, $fields);
    }

    public function delete(int $id): bool
    {
        return $this->store->delete($id);
    }

    public function withTransaction(callable $callback)
    {
        return $this->store->withTransaction($callback);
    }
}
