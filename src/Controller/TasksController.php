<?php

declare(strict_types=1);

namespace App\Controller;

use App\Domain\Task as TaskDTO;
use App\Domain\Schemas;
use JetBrains\PhpStorm\NoReturn;
use function __;
use function redirect;
use function render;

class TasksController
{
    public function __construct(
        private readonly object $tasksStore,
        private readonly object $contactsStore,
        private readonly object $employeesStore,
        private readonly ?object $projectsStore = null,
    ) {}

    /**
     * AJAX: Move task to another status (Kanban drag-and-drop)
     */
    public function move(): void
    {
        header('Content-Type: application/json');
        $id = (int)($_POST['id'] ?? 0);
        $status = (string)($_POST['status'] ?? '');
        $allowed = ['open','in_progress','review','blocked','done'];
        if ($id <= 0 || !in_array($status, $allowed, true)) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'bad_request']);
            return;
        }
        $task = $this->tasksStore->get($id);
        if (!$task) {
            http_response_code(404);
            echo json_encode(['ok' => false, 'error' => 'not_found']);
            return;
        }
        // Preserve other fields, only change status + done_date transitions
        $existing = $task;
        $doneDate = (string)($existing['done_date'] ?? '');
        if ($status === 'done') {
            if ($doneDate === '') {
                $doneDate = (new \DateTimeImmutable('today'))->format('Y-m-d');
            }
        } else {
            $doneDate = '';
        }
        $this->tasksStore->update($id, ['status' => $status, 'done_date' => $doneDate]);
        echo json_encode(['ok' => true, 'task' => ['id' => $id, 'status' => $status, 'done_date' => $doneDate]]);
    }

    public function view(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $item = $id ? $this->tasksStore->get($id) : null;
        if (!$item) { redirect('/tasks'); }
        $schema = Schemas::get('tasks');
        $fields = array_map(fn($f) => ['name' => $f['name'], 'label' => $f['label'] ?? $f['name']], $schema['fields']);
        array_unshift($fields, ['name' => 'id', 'label' => 'ID']);
        $fields[] = ['name' => 'created_at', 'label' => __('Created')];
        render('entity_view', [
            'title' => __('Task') . ': ' . ($item['title'] ?? ('#' . $id)),
            'fields' => $fields,
            'item' => $item,
            'back_url' => url('/tasks'),
            'edit_url' => url('/tasks/edit', ['id' => $id])
        ]);
    }

    public function list(): void
    {
        $path = current_path();
        $q = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
        $sort = isset($_GET['sort']) ? (string)$_GET['sort'] : 'due_date';
        $dir = strtolower((string)($_GET['dir'] ?? 'asc')) === 'desc' ? 'desc' : 'asc';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $per = max(1, min(100, (int)($_GET['per'] ?? 10)));

        $tasks = $this->tasksStore->all();
        $contactsById = [];
        foreach ($this->contactsStore->all() as $c) { $contactsById[$c['id']] = $c; }
        $employeesById = [];
        foreach ($this->employeesStore->all() as $e) { $employeesById[$e['id']] = $e; }
        $projectsById = [];
        if ($this->projectsStore) {
            foreach ($this->projectsStore->all() as $p) { $projectsById[$p['id']] = $p; }
        }
        $openCount = 0; $doneCount = 0;
        foreach ($tasks as &$t) {
            $cid = $t['contact_id'] ?? null;
            $t['contact_name'] = $contactsById[$cid]['name'] ?? 'Unassigned';
            $empId = $t['employee_id'] ?? 0;
            $t['employee_name'] = $empId ? ($employeesById[$empId]['name'] ?? __('Unassigned')) : __('Unassigned');
            $pid = $t['project_id'] ?? 0;
            $t['project_name'] = $pid ? ($projectsById[$pid]['name'] ?? __('No project')) : __('No project');
            $status = $t['status'] ?? 'open';
            if ($status === 'done') $doneCount++; else $openCount++;
        }
        unset($t);

        // Filter
        if ($q !== '') {
            $needle = mb_strtolower($q);
            $tasks = array_values(array_filter($tasks, function($it) use ($needle) {
                foreach (['title','status','notes','contact_name','employee_name','due_date','done_date'] as $field) {
                    $v = (string)($it[$field] ?? '');
                    if ($v !== '' && str_contains(mb_strtolower($v), $needle)) { return true; }
                }
                return false;
            }));
        }

        // Sort
        $allowed = ['due_date','status','title','contact_name'];
        if (!in_array($sort, $allowed, true)) { $sort = 'due_date'; }
        usort($tasks, function($a,$b) use ($sort, $dir) {
            $va = (string)($a[$sort] ?? '');
            $vb = (string)($b[$sort] ?? '');
            $cmp = strcmp($va, $vb);
            return $dir === 'asc' ? $cmp : -$cmp;
        });

        $total = count($tasks);
        $offset = ($page - 1) * $per;
        $paged = array_slice($tasks, $offset, $per);

        $schema = Schemas::get('tasks');
        render('tasks_list', [
            'tasks' => $paged,
            'counts' => ['open' => $openCount, 'done' => $doneCount],
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
        $contacts = $this->contactsStore->all();
        $employees = $this->employeesStore->all();
        $schema = Schemas::get('tasks');
        $projects = $this->projectsStore ? $this->projectsStore->all() : [];
        $fields = self::injectOptions($schema['fields'], $contacts, $employees, $projects);
        render('tasks_add', [
            'fields' => $fields,
            'cancel_url' => url('/tasks'),
            'contacts' => $contacts,
            'employees' => $employees,
        ]);
    }

    public function create(): void
    {
        $contacts = $this->contactsStore->all();
        $employees = $this->employeesStore->all();
        $projects = $this->projectsStore ? $this->projectsStore->all() : [];
        $dto = TaskDTO::fromInput($_POST);
        $errors = $dto->validate();
        // FK validation: contact must exist
        $data = $dto->toArray();
        $contactId = (int)($data['contact_id'] ?? 0);
        if ($contactId <= 0 || !$this->contactsStore->get($contactId)) {
            $errors['contact_id'] = __('Selected contact does not exist.');
        }
        $empId = (int)($data['employee_id'] ?? 0);
        if ($empId > 0 && !$this->employeesStore->get($empId)) {
            $errors['employee_id'] = __('Selected employee does not exist.');
        }
        $projectId = (int)($data['project_id'] ?? 0);
        if ($projectId > 0 && $this->projectsStore && !$this->projectsStore->get($projectId)) {
            $errors['project_id'] = __('Selected project does not exist.');
        }
        if (!empty($errors)) {
            $error = __('Please fix the highlighted errors.');
            $schema = Schemas::get('tasks');
            $fields = self::injectOptions($schema['fields'], $contacts, $employees, $projects);
            render('tasks_add', ['error' => $error, 'errors' => $errors, 'fields' => $fields, 'cancel_url' => url('/tasks'), 'contacts' => $contacts, 'employees' => $employees, 'projects' => $projects, 'contactId' => $contactId, 'employeeId' => $empId, 'projectId' => $projectId] + $data);
            return;
        }
        // Status transitions: set/clear done_date automatically
        $status = $data['status'] ?? 'open';
        $doneDate = (string)($data['done_date'] ?? '');
        if ($status === 'done') {
            if ($doneDate === '') {
                $doneDate = (new \DateTimeImmutable('today'))->format('Y-m-d');
            }
        } else {
            $doneDate = '';
        }
        $data['done_date'] = $doneDate;
        $this->tasksStore->add($data + [
            'created_at' => \App\Util\Dates::nowAtom(),
        ]);
        redirect('/tasks');
    }

    public function editForm(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $task = $id ? $this->tasksStore->get($id) : null;
        if (!$task) { redirect('/tasks'); }
        $contacts = $this->contactsStore->all();
        $employees = $this->employeesStore->all();
        $projects = $this->projectsStore ? $this->projectsStore->all() : [];
        $schema = Schemas::get('tasks');
        $fields = self::injectOptions($schema['fields'], $contacts, $employees, $projects);
        render('tasks_add', ['edit' => true, 'task' => $task, 'fields' => $fields, 'cancel_url' => url('/tasks'), 'contacts' => $contacts, 'employees' => $employees, 'projects' => $projects]);
    }

    public function update(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) { redirect('/tasks'); }
        $contacts = $this->contactsStore->all();
        $employees = $this->employeesStore->all();
        $projects = $this->projectsStore ? $this->projectsStore->all() : [];
        $dto = TaskDTO::fromInput($_POST);
        $errors = $dto->validate();
        // FK validation: contact must exist
        $data = $dto->toArray();
        $contactId = (int)($data['contact_id'] ?? 0);
        if ($contactId <= 0 || !$this->contactsStore->get($contactId)) {
            $errors['contact_id'] = __('Selected contact does not exist.');
        }
        $empId = (int)($data['employee_id'] ?? 0);
        if ($empId > 0 && !$this->employeesStore->get($empId)) {
            $errors['employee_id'] = __('Selected employee does not exist.');
        }
        $projectId = (int)($data['project_id'] ?? 0);
        if ($projectId > 0 && $this->projectsStore && !$this->projectsStore->get($projectId)) {
            $errors['project_id'] = __('Selected project does not exist.');
        }
        if (!empty($errors)) {
            $error = __('Please fix the highlighted errors.');
            $schema = Schemas::get('tasks');
            $fields = self::injectOptions($schema['fields'], $contacts, $employees, $projects);
            render('tasks_add', ['error' => $error, 'errors' => $errors, 'fields' => $fields, 'cancel_url' => url('/tasks'), 'contacts' => $contacts, 'employees' => $employees, 'projects' => $projects, 'contactId' => $contactId, 'employeeId' => $empId, 'projectId' => $projectId, 'task' => $this->tasksStore->get($id), 'edit' => true] + $data);
            return;
        }
        // Transitions
        $existing = $this->tasksStore->get($id) ?? [];
        $status = $data['status'] ?? 'open';
        $doneDate = (string)($data['done_date'] ?? '');
        if ($status === 'done') {
            if ($doneDate === '') {
                $doneDate = (string)($existing['done_date'] ?? '');
                if ($doneDate === '') {
                    $doneDate = (new \DateTimeImmutable('today'))->format('Y-m-d');
                }
            }
        } else {
            $doneDate = '';
        }
        $data['done_date'] = $doneDate;
        $this->tasksStore->update($id, $data);
        redirect('/tasks');
    }

    #[NoReturn]
    public function delete(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) { $this->tasksStore->delete($id); }
        redirect('/tasks');
    }

    /**
     * Injects select options for contact_id and employee_id into schema fields.
     * @param array<int, array> $fields
     * @param array<int, array> $contacts
     * @param array<int, array> $employees
     * @return array<int, array>
     */
    private static function injectOptions(array $fields, array $contacts, array $employees, array $projects = []): array
    {
        $contactOpts = ['' => __('Select contact…')];
        foreach ($contacts as $c) {
            $id = (int)($c['id'] ?? 0);
            if ($id > 0) { $contactOpts[$id] = (string)($c['name'] ?? ('#' . $id)); }
        }
        $employeeOpts = ['' => __('Unassigned')];
        foreach ($employees as $e) {
            $id = (int)($e['id'] ?? 0);
            if ($id > 0) { $employeeOpts[$id] = (string)($e['name'] ?? ('#' . $id)); }
        }
        $projectOpts = ['' => __('No project')];
        foreach ($projects as $p) {
            $id = (int)($p['id'] ?? 0);
            if ($id > 0) { $projectOpts[$id] = (string)($p['name'] ?? ('#' . $id)); }
        }
        foreach ($fields as &$f) {
            $name = $f['name'] ?? '';
            if ($name === 'contact_id') {
                $f['options'] = $contactOpts;
            } elseif ($name === 'employee_id') {
                $f['options'] = $employeeOpts;
            } elseif ($name === 'project_id') {
                $f['options'] = $projectOpts;
            }
        }
        unset($f);
        return $fields;
    }
}
