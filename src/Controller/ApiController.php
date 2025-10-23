<?php

declare(strict_types=1);

namespace App\Controller;

use App\Config;
use App\Http\ApiAuth;
use App\Service\WebhookService;

final class ApiController
{
    /** @var array<string,object> */
    private array $stores;

    public function __construct(
        private readonly Config $config,
        private readonly WebhookService $webhook,
        private readonly \App\Service\AuditService $audit,
        object $contactsStore,
        object $tasksStore,
        object $dealsStore,
        object $projectsStore,
        object $timesStore,
        object $paymentsStore,
        object $employeesStore,
        object $candidatesStore,
        object $storageStore
    ) {
        $this->stores = [
            'contacts' => $contactsStore,
            'tasks' => $tasksStore,
            'deals' => $dealsStore,
            'projects' => $projectsStore,
            'times' => $timesStore,
            'payments' => $paymentsStore,
            'employees' => $employeesStore,
            'candidates' => $candidatesStore,
            'storage' => $storageStore,
        ];
    }

    public function list(string $entity): void
    {
        if (!ApiAuth::enforceToken($this->config)) { return; }
        $store = $this->store($entity);
        if (!$store) { $this->notFound(); return; }
        $all = $store->all();
        // basic filtering
        $q = isset($_GET['q']) ? (string)$_GET['q'] : '';
        $filters = $_GET['filter'] ?? [];
        if (!is_array($filters)) { $filters = []; }
        $items = array_values(array_filter($all, function($row) use ($q, $filters) {
            if ($q !== '') {
                $hay = strtolower(json_encode($row) ?: '');
                if (!str_contains($hay, strtolower($q))) return false;
            }
            foreach ($filters as $k => $v) {
                if (!isset($row[$k])) return false;
                if ((string)$row[$k] !== (string)$v) return false;
            }
            return true;
        }));
        // sorting
        $sort = isset($_GET['sort']) ? (string)$_GET['sort'] : 'id';
        $dir = strtolower((string)($_GET['dir'] ?? 'asc')) === 'desc' ? -1 : 1;
        usort($items, function($a,$b) use ($sort,$dir){
            $va = $a[$sort] ?? null; $vb = $b[$sort] ?? null;
            $cmp = ($va == $vb) ? 0 : (($va < $vb) ? -1 : 1);
            return $dir * $cmp;
        });
        // pagination
        $page = max(1, (int)($_GET['page'] ?? 1));
        $per = min(200, max(1, (int)($_GET['per_page'] ?? 50)));
        $total = count($items);
        $items = array_slice($items, ($page-1)*$per, $per);
        $this->json(['ok' => true, 'items' => $items, 'page' => $page, 'per_page' => $per, 'total' => $total]);
    }

    public function get(string $entity): void
    {
        if (!ApiAuth::enforceToken($this->config)) { return; }
        $id = isset($_GET['id']) ? (string)$_GET['id'] : '';
        $store = $this->store($entity);
        if (!$store) { $this->notFound(); return; }
        $item = $this->findById($store->all(), $id);
        if (!$item) { $this->notFound(); return; }
        $this->json(['ok' => true, 'item' => $item]);
    }

    public function create(string $entity): void
    {
        if (!ApiAuth::enforceToken($this->config)) { return; }
        $store = $this->store($entity);
        if (!$store) { $this->notFound(); return; }
        $data = $this->readJsonBody();
        if (!is_array($data)) { $this->badRequest('Invalid JSON'); return; }
        $created = $store->add($data);
        // Audit + Webhook
        $this->audit->record('created', $entity, $created['id'] ?? null, null, is_array($created) ? $created : null, ['source' => 'api']);
        $this->webhook->emit('created', $entity, $created);
        $this->json(['ok' => true, 'item' => $created], 201);
    }

    public function update(string $entity): void
    {
        if (!ApiAuth::enforceToken($this->config)) { return; }
        $store = $this->store($entity);
        if (!$store) { $this->notFound(); return; }
        $id = isset($_GET['id']) ? (string)$_GET['id'] : '';
        $existing = $this->findById($store->all(), $id);
        if (!$existing) { $this->notFound(); return; }
        $data = $this->readJsonBody();
        if (!is_array($data)) { $this->badRequest('Invalid JSON'); return; }
        $data['id'] = $existing['id'];
        $updated = $store->update($data);
        // Audit + Webhook
        $this->audit->record('updated', $entity, $existing['id'] ?? null, is_array($existing) ? $existing : null, is_array($updated) ? $updated : null, ['source' => 'api']);
        $this->webhook->emit('updated', $entity, $updated, ['before' => $existing]);
        $this->json(['ok' => true, 'item' => $updated]);
    }

    public function delete(string $entity): void
    {
        if (!ApiAuth::enforceToken($this->config)) { return; }
        $store = $this->store($entity);
        if (!$store) { $this->notFound(); return; }
        $id = isset($_GET['id']) ? (string)$_GET['id'] : '';
        $existing = $this->findById($store->all(), $id);
        if (!$existing) { $this->notFound(); return; }
        $store->delete((string)$existing['id']);
        // Audit + Webhook
        $this->audit->record('deleted', $entity, $existing['id'] ?? null, is_array($existing) ? $existing : null, null, ['source' => 'api']);
        $this->webhook->emit('deleted', $entity, ['id' => $existing['id']]);
        $this->json(['ok' => true]);
    }

    private function store(string $entity): ?object
    {
        return $this->stores[$entity] ?? null;
    }

    private function findById(array $items, string $id): ?array
    {
        foreach ($items as $row) {
            if ((string)($row['id'] ?? '') === $id) return $row;
        }
        return null;
    }

    private function readJsonBody(): mixed
    {
        $raw = file_get_contents('php://input');
        if ($raw === false) return null;
        return json_decode($raw, true);
    }

    private function json(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    private function notFound(): void
    {
        $this->json(['error' => 'Not Found', 'code' => 404], 404);
    }

    private function badRequest(string $msg): void
    {
        $this->json(['error' => $msg, 'code' => 400], 400);
    }
}
