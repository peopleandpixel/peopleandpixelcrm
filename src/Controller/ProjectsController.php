<?php

declare(strict_types=1);

namespace App\Controller;

use App\Domain\Project as ProjectDTO;
use App\Domain\Schemas;
use App\Util\ListSort;
use App\Util\Csrf;
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
        private readonly ?\App\Service\AuditService $audit = null,
        private readonly ?object $commentsStore = null,
        private readonly ?object $followsStore = null,
    ) {}

    public function view(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $item = $id ? $this->projectsStore->get($id) : null;
        if (!$item) { redirect('/projects'); }
        if (!\App\Util\Permission::enforceRecord('projects', 'view', $item)) { return; }
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
        $comments = [];
        if ($this->commentsStore) {
            try {
                foreach ($this->commentsStore->all() as $c) {
                    if (($c['entity'] ?? '') === 'projects' && (int)($c['entity_id'] ?? 0) === $id) { $comments[] = $c; }
                }
                usort($comments, fn($a,$b) => strcmp((string)($a['created_at'] ?? ''), (string)($b['created_at'] ?? '')));
            } catch (\Throwable $e) { /* ignore */ }
        }
        // Follows info
        $isFollowing = false; $followersCount = 0;
        if ($this->followsStore) {
            try {
                $me = \App\Util\Auth::user();
                $login = $me ? strtolower((string)($me['login'] ?? '')) : '';
                foreach ($this->followsStore->all() as $f) {
                    if (($f['entity'] ?? '') === 'projects' && (int)($f['entity_id'] ?? 0) === $id) {
                        $followersCount++;
                        if ($login !== '' && strtolower((string)($f['user_login'] ?? '')) === $login) { $isFollowing = true; }
                    }
                }
            } catch (\Throwable $e) { /* ignore */ }
        }
        render('entity_view', [
            'title' => __('Project') . ': ' . ($item['name'] ?? ('#' . $id)),
            'fields' => $fields,
            'item' => $item,
            'back_url' => url('/projects'),
            'edit_url' => url('/projects/edit', ['id' => $id]),
            'comments' => $comments,
            'comments_entity' => 'projects',
            'is_following' => $isFollowing,
            'followers_count' => $followersCount,
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
        if (!\App\Util\Permission::enforceRecord('projects', 'edit', $project)) { return; }
        $schema = Schemas::get('projects');
        $fields = $this->injectOptions($schema['fields']);
        render('projects_add', ['edit' => true, 'project' => $project, 'fields' => $fields, 'cancel_url' => url('/projects')] + $project);
    }

    public function update(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) { redirect('/projects'); }
        $existing = $this->projectsStore->get($id) ?? null;
        if (!\App\Util\Permission::enforceRecord('projects', 'edit', is_array($existing) ? $existing : null)) { return; }
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
        if ($id > 0) {
            $existing = $this->projectsStore->get($id) ?? null;
            if (!\App\Util\Permission::enforceRecord('projects', 'delete', is_array($existing) ? $existing : null)) { return; }
            $this->projectsStore->delete($id);
        }
        redirect('/projects');
    }

    public function bulk(): void
    {
        $action = (string)($_POST['action'] ?? '');
        $ids = isset($_POST['ids']) && is_array($_POST['ids']) ? array_unique(array_map('intval', $_POST['ids'])) : [];
        $value = trim((string)($_POST['value'] ?? ''));
        if (empty($ids)) { \App\Util\Flash::error(__('No items selected.')); redirect('/projects'); }
        $ok = 0; $skip = 0; $fail = 0; $deleted = [];
        foreach ($ids as $id) {
            if ($id <= 0) { $skip++; continue; }
            $item = $this->projectsStore->get($id);
            if (!$item) { $skip++; continue; }
            if ($action === 'delete') {
                if (!\App\Util\Permission::enforceRecord('projects', 'delete', $item)) { $fail++; continue; }
                $before = is_array($item) ? $item : null;
                $this->projectsStore->delete($id);
                $deleted[] = $before;
                if ($this->audit) { $this->audit->record('deleted','projects',$id,$before,null,['bulk'=>1]); }
                $ok++;
            } elseif ($action === 'set_status') {
                if ($value === '') { $fail++; continue; }
                if (!\App\Util\Permission::enforceRecord('projects', 'edit', $item)) { $fail++; continue; }
                $item['status'] = $value;
                $this->projectsStore->update($id, $item);
                if ($this->audit) { $this->audit->record('action','projects',$id,null,null,['bulk'=>1,'action'=>'set_status','status'=>$value]); }
                $ok++;
            } else {
                $fail++;
            }
        }
        if (!empty($deleted)) {
            if (session_status() !== PHP_SESSION_ACTIVE) { @session_start(); }
            if (!isset($_SESSION['_bulk_undo'])) { $_SESSION['_bulk_undo'] = []; }
            $token = bin2hex(random_bytes(8));
            $_SESSION['_bulk_undo'][$token] = [ 'entity' => 'projects', 'records' => $deleted, 'at' => time() ];
            $undoUrl = url('/bulk/undo');
            $field = \App\Util\Csrf::fieldName();
            $csrf = '<input type="hidden" name="' . htmlspecialchars($field, ENT_QUOTES) . '" value="' . htmlspecialchars(\App\Util\Csrf::getToken(), ENT_QUOTES) . '">';
            $msg = __('Deleted: ') . $ok . ' · ' . __('Failed: ') . $fail . ' · ' . __('Undo available for 5 minutes.');
            $msg .= ' <form method="post" action="' . htmlspecialchars($undoUrl, ENT_QUOTES) . '" class="inline"><input type="hidden" name="token" value="' . htmlspecialchars($token, ENT_QUOTES) . '">' . $csrf . '<input type="hidden" name="entity" value="projects"><button class="btn btn-xs" type="submit">' . htmlspecialchars(__('Undo'), ENT_QUOTES) . '</button></form>';
            \App\Util\Flash::info($msg);
        } else {
            \App\Util\Flash::success(__('Updated: ') . $ok . ' · ' . __('Skipped: ') . $skip . ' · ' . __('Failed: ') . $fail);
        }
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
