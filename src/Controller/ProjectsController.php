<?php

declare(strict_types=1);

namespace App\Controller;

use App\Domain\Project as ProjectDTO;
use App\Domain\Schemas;
use App\Util\ListSort;
use JetBrains\PhpStorm\NoReturn;
use function __;
use function redirect;
use function render;

class ProjectsController
{
    public function __construct(
        private readonly object $projectsStore,
        private readonly object $contactsStore,
        private readonly object $employeesStore,
        private readonly object $tasksStore,
    ) {}

    public function view(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $item = $id ? $this->projectsStore->get($id) : null;
        if (!$item) { redirect('/projects'); }
        // Enrich with customer name
        $contactsById = [];
        foreach ($this->contactsStore->all() as $c) { $contactsById[$c['id']] = $c; }
        $item['customer_name'] = $contactsById[$item['contact_id']]['name'] ?? '';

        $schema = Schemas::get('projects');
        $fields = array_map(fn($f) => ['name' => $f['name'], 'label' => $f['label'] ?? $f['name']], $schema['fields']);
        // Replace contact_id with customer_name for view
        foreach ($fields as &$f) { if ($f['name'] === 'contact_id') { $f['name'] = 'customer_name'; } }
        unset($f);
        array_unshift($fields, ['name' => 'id', 'label' => 'ID']);
        $fields[] = ['name' => 'created_at', 'label' => __('Created')];
        render('entity_view', [
            'title' => __('Project') . ': ' . ($item['name'] ?? ('#' . $id)),
            'fields' => $fields,
            'item' => $item,
            'back_url' => url('/projects'),
            'edit_url' => url('/projects/edit', ['id' => $id])
        ]);
    }

    public function list(): void
    {
        $path = current_path();
        $q = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
        $sort = isset($_GET['sort']) ? (string)$_GET['sort'] : 'name';
        $dir = strtolower((string)($_GET['dir'] ?? 'asc')) === 'desc' ? 'desc' : 'asc';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $per = max(1, min(100, (int)($_GET['per'] ?? 10)));

        $items = $this->projectsStore->all();
        $contactsById = [];
        foreach ($this->contactsStore->all() as $c) { $contactsById[$c['id']] = $c; }
        foreach ($items as &$it) {
            $cid = (int)($it['contact_id'] ?? 0);
            $it['customer_name'] = $contactsById[$cid]['name'] ?? '';
        }
        unset($it);

        // Filter
        if ($q !== '') {
            $needle = mb_strtolower($q);
            $items = array_values(array_filter($items, function($it) use ($needle) {
                foreach (['name','status','customer_name','description'] as $field) {
                    $v = (string)($it[$field] ?? '');
                    if ($v !== '' && str_contains(mb_strtolower($v), $needle)) { return true; }
                }
                return false;
            }));
        }

        // Sort
        $allowed = ['name','status','customer_name','start_date','end_date'];
        if (!in_array($sort, $allowed, true)) { $sort = 'name'; }
        usort($items, function($a,$b) use ($sort, $dir) {
            $va = (string)($a[$sort] ?? '');
            $vb = (string)($b[$sort] ?? '');
            $cmp = strcmp($va, $vb);
            return $dir === 'asc' ? $cmp : -$cmp;
        });

        $total = count($items);
        $offset = ($page - 1) * $per;
        $paged = array_slice($items, $offset, $per);

        $schema = Schemas::get('projects');
        render('projects_list', [
            'items' => $paged,
            'columns' => $schema['columns'],
            'total' => $total,
            'page' => $page,
            'per' => $per,
            'sort' => $sort,
            'dir' => $dir,
            'q' => $q,
            'path' => $path,
        ]);
    }

    public function newForm(): void
    {
        $schema = Schemas::get('projects');
        $fields = $this->injectOptions($schema['fields']);
        render('projects_add', [
            'fields' => $fields,
            'cancel_url' => url('/projects')
        ]);
    }

    public function create(): void
    {
        $dto = ProjectDTO::fromInput($_POST);
        $errors = $dto->validate();
        if (!empty($errors)) {
            $error = 'Please fix the highlighted errors.';
            $schema = Schemas::get('projects');
            $fields = $this->injectOptions($schema['fields']);
            render('projects_add', ['error' => $error, 'errors' => $errors, 'fields' => $fields, 'cancel_url' => url('/projects')] + $dto->toArray());
            return;
        }
        $this->projectsStore->add($dto->toArray() + [
            'created_at' => \App\Util\Dates::nowAtom(),
        ]);
        redirect('/projects');
    }

    public function editForm(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $project = $id ? $this->projectsStore->get($id) : null;
        if (!$project) { redirect('/projects'); }
        $schema = Schemas::get('projects');
        $fields = $this->injectOptions($schema['fields']);
        render('projects_add', ['edit' => true, 'project' => $project, 'fields' => $fields, 'cancel_url' => url('/projects')] + $project);
    }

    public function update(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) { redirect('/projects'); }
        $dto = ProjectDTO::fromInput($_POST);
        $errors = $dto->validate();
        if (!empty($errors)) {
            $error = 'Please fix the highlighted errors.';
            $project = $this->projectsStore->get($id) ?? [];
            $schema = Schemas::get('projects');
            $fields = $this->injectOptions($schema['fields']);
            render('projects_add', ['error' => $error, 'errors' => $errors, 'project' => $project, 'fields' => $fields, 'cancel_url' => url('/projects')] + $dto->toArray());
            return;
        }
        $this->projectsStore->update($id, $dto->toArray());
        redirect('/projects');
    }

    #[NoReturn]
    public function delete(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) { $this->projectsStore->delete($id); }
        redirect('/projects');
    }

    private function injectOptions(array $fields): array
    {
        // Fill contacts options
        $contacts = $this->contactsStore->all();
        $contactsOptions = [0 => __('Select...')];
        foreach ($contacts as $c) { $contactsOptions[(int)$c['id']] = $c['name']; }
        foreach ($fields as &$f) {
            if ($f['name'] === 'contact_id') {
                $f['options'] = $contactsOptions;
            }
        }
        unset($f);
        return $fields;
    }
}
