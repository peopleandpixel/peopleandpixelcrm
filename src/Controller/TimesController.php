<?php

declare(strict_types=1);

namespace App\Controller;

use App\Domain\TimeEntry as TimeEntryDTO;
use App\Domain\Schemas;
use JetBrains\PhpStorm\NoReturn;
use function __;
use function redirect;
use function render;

class TimesController
{
    public function __construct(
        private readonly object $timesStore,
        private readonly object $contactsStore,
        private readonly object $employeesStore,
        private readonly ?object $tasksStore = null,
        private readonly ?object $projectsStore = null,
    ) {}

    public function timerPage(): void
    {
        // Build minimal lists for assignment selectors
        $projects = [];
        if ($this->projectsStore) {
            try { $projects = $this->projectsStore->all(); } catch (\Throwable $e) { $projects = []; }
        }
        $tasks = [];
        if ($this->tasksStore) {
            try {
                foreach ($this->tasksStore->all() as $t) {
                    // Prefer open/in_progress tasks for selection
                    $st = strtolower((string)($t['status'] ?? 'open'));
                    if (in_array($st, ['open','in_progress','review','blocked'], true)) { $tasks[] = $t; }
                }
                usort($tasks, fn($a,$b) => strcmp((string)($a['title'] ?? ''), (string)($b['title'] ?? '')));
            } catch (\Throwable $e) { $tasks = []; }
        }
        // Contacts for default when creating ad-hoc times without a task
        $contacts = [];
        try { $contacts = $this->contactsStore->all(); } catch (\Throwable $e) { $contacts = []; }
        render('timer', [
            'projects' => $projects,
            'tasks' => $tasks,
            'contacts' => $contacts,
        ]);
    }

    public function timerStart(): void
    {
        $req = \request();
        $isJson = $req->wantsJson();
        try {
            $user = \App\Util\Auth::user();
            $userId = is_array($user) ? (int)($user['id'] ?? 0) : 0;
            $taskId = (int)($req->post('task_id') ?? 0);
            $contactId = (int)($req->post('contact_id') ?? 0);
            $desc = (string)($req->post('description') ?? '');
            // If task provided, enrich fields from task
            $projectId = 0; $employeeId = 0;
            if ($taskId > 0 && $this->tasksStore) {
                $task = $this->tasksStore->get($taskId);
                if ($task) {
                    $contactId = (int)($task['contact_id'] ?? $contactId);
                    $employeeId = (int)($task['employee_id'] ?? 0);
                    $projectId = (int)($task['project_id'] ?? 0);
                    if ($desc === '') { $desc = __('Time for task') . ' #' . $taskId; }
                }
            }
            if ($contactId <= 0) {
                // fallback to first contact if exists
                $allContacts = $this->contactsStore->all();
                if (!empty($allContacts)) { $contactId = (int)($allContacts[0]['id'] ?? 0); }
            }
            // Stop any existing running timer for THIS user to avoid overlaps
            $running = null; $rid = 0;
            foreach ($this->timesStore->all() as $t) {
                if ((string)($t['end_time'] ?? '') !== '') { continue; }
                if ($userId > 0 && (int)($t['owner_user_id'] ?? 0) !== $userId) { continue; }
                $id = (int)($t['id'] ?? 0);
                if ($id > $rid) { $rid = $id; $running = $t; }
            }
            if ($running) {
                $now = new \DateTimeImmutable('now');
                $end = $now->format('H:i');
                $start = (string)($running['start_time'] ?? '');
                $hours = 0.0;
                if ($start !== '') {
                    $s = \App\Util\Dates::parseExact($start, 'H:i') ?? \App\Util\Dates::parseExact($start, 'H:i:s');
                    $e = \App\Util\Dates::parseExact($end, 'H:i');
                    if ($s && $e) { $hours = max(0.01, round(($e->getTimestamp() - $s->getTimestamp())/3600, 2)); }
                }
                $this->timesStore->update($rid, ['end_time' => $end, 'hours' => $hours]);
            }
            $now = new \DateTimeImmutable('now');
            $created = $this->timesStore->add([
                'contact_id' => $contactId,
                'employee_id' => $employeeId,
                'task_id' => $taskId,
                'date' => $now->format('Y-m-d'),
                'hours' => 0.0,
                'description' => $desc,
                'start_time' => $now->format('H:i'),
                'end_time' => '',
                'created_at' => \App\Util\Dates::nowAtom(),
                'owner_user_id' => $userId,
            ]);
            if ($isJson) { \App\Http\Response::json(['ok'=>true,'time'=>$created])->send(); return; }
            redirect('/timer');
        } catch (\Throwable $e) {
            if ($isJson) { \App\Http\Response::json(['ok'=>false,'error'=>'server_error'])->send(); return; }
            http_response_code(500); render('errors/500');
        }
    }

    public function timerPause(): void
    {
        $req = \request(); $isJson = $req->wantsJson();
        try {
            $user = \App\Util\Auth::user();
            $userId = is_array($user) ? (int)($user['id'] ?? 0) : 0;
            // Find the most recent running timer for this user and close it
            $running = null; $rid = 0;
            foreach ($this->timesStore->all() as $t) {
                if ((string)($t['end_time'] ?? '') !== '') { continue; }
                if ($userId > 0 && (int)($t['owner_user_id'] ?? 0) !== $userId) { continue; }
                $id = (int)($t['id'] ?? 0); if ($id > $rid) { $rid = $id; $running = $t; }
            }
            if (!$running) { if ($isJson) { \App\Http\Response::json(['ok'=>false,'error'=>'no_running_timer'])->send(); } else { redirect('/timer'); } return; }
            $now = new \DateTimeImmutable('now');
            $end = $now->format('H:i');
            $start = (string)($running['start_time'] ?? '');
            $hours = 0.0;
            if ($start !== '') {
                $s = \App\Util\Dates::parseExact($start, 'H:i') ?? \App\Util\Dates::parseExact($start, 'H:i:s');
                $e = \App\Util\Dates::parseExact($end, 'H:i');
                if ($s && $e) { $hours = max(0.01, round(($e->getTimestamp() - $s->getTimestamp())/3600, 2)); }
            }
            $updated = $this->timesStore->update($rid, ['end_time' => $end, 'hours' => $hours]);
            if ($isJson) { \App\Http\Response::json(['ok'=>true,'time'=>$updated])->send(); return; }
            redirect('/timer');
        } catch (\Throwable $e) {
            if ($isJson) { \App\Http\Response::json(['ok'=>false,'error'=>'server_error'])->send(); return; }
            http_response_code(500); render('errors/500');
        }
    }

    public function timerResume(): void
    {
        $req = \request(); $isJson = $req->wantsJson();
        try {
            $user = \App\Util\Auth::user();
            $userId = is_array($user) ? (int)($user['id'] ?? 0) : 0;
            // Resume by starting a new segment using the latest closed entry's metadata for this user
            $last = null; $lid = 0;
            foreach ($this->timesStore->all() as $t) {
                if ($userId > 0 && (int)($t['owner_user_id'] ?? 0) !== $userId) { continue; }
                $id = (int)($t['id'] ?? 0); if ($id > $lid) { $lid = $id; $last = $t; }
            }
            if (!$last) { if ($isJson) { \App\Http\Response::json(['ok'=>false,'error'=>'nothing_to_resume'])->send(); } else { redirect('/timer'); } return; }
            // If there is already a running one for this user, do nothing
            foreach ($this->timesStore->all() as $t) {
                if ((string)($t['end_time'] ?? '') !== '') { continue; }
                if ($userId > 0 && (int)($t['owner_user_id'] ?? 0) !== $userId) { continue; }
                if ($isJson) { \App\Http\Response::json(['ok'=>true,'time'=>$t])->send(); return; }
                redirect('/timer'); return;
            }
            $now = new \DateTimeImmutable('now');
            $created = $this->timesStore->add([
                'contact_id' => (int)($last['contact_id'] ?? 0),
                'employee_id' => (int)($last['employee_id'] ?? 0),
                'task_id' => (int)($last['task_id'] ?? 0),
                'date' => $now->format('Y-m-d'),
                'hours' => 0.0,
                'description' => (string)($last['description'] ?? ''),
                'start_time' => $now->format('H:i'),
                'end_time' => '',
                'created_at' => \App\Util\Dates::nowAtom(),
                'owner_user_id' => $userId,
            ]);
            if ($isJson) { \App\Http\Response::json(['ok'=>true,'time'=>$created])->send(); return; }
            redirect('/timer');
        } catch (\Throwable $e) {
            if ($isJson) { \App\Http\Response::json(['ok'=>false,'error'=>'server_error'])->send(); return; }
            http_response_code(500); render('errors/500');
        }
    }

    public function timerStop(): void
    {
        // Same as pause, but returns explicit stopped state
        $this->timerPause();
    }

    /**
     * Returns the most recent running timer (no end_time) as JSON.
     */
    public function running(): void
    {
        header('Content-Type: application/json');
        try {
            $user = \App\Util\Auth::user();
            $userId = is_array($user) ? (int)($user['id'] ?? 0) : 0;
            $running = null; $rid = 0;
            $all = $this->timesStore->all();
            foreach ($all as $t) {
                if ((string)($t['end_time'] ?? '') !== '') { continue; }
                if ($userId > 0 && (int)($t['owner_user_id'] ?? 0) !== $userId) { continue; }
                $id = (int)($t['id'] ?? 0);
                if ($id > $rid) { $rid = $id; $running = $t; }
            }
            if (!$running) {
                echo json_encode(['ok' => true, 'running' => null, 'now' => (new \DateTimeImmutable('now'))->format(DATE_ATOM)]);
                return;
            }
            $date = (string)($running['date'] ?? '');
            $start = (string)($running['start_time'] ?? '');
            $taskId = (int)($running['task_id'] ?? 0);
            // Best-effort ISO start time (local timezone)
            $isoStart = null;
            if ($date !== '' && $start !== '') {
                // If start already includes seconds, use as-is; otherwise append :00
                $isoStart = $date . 'T' . (strlen($start) === 5 ? ($start . ':00') : $start);
            }
            // Compute per-user total time for this task
            $totalSeconds = 0;
            foreach ($all as $t) {
                if ((int)($t['task_id'] ?? 0) !== $taskId) { continue; }
                if ($userId > 0 && (int)($t['owner_user_id'] ?? 0) !== $userId) { continue; }
                $hours = (float)($t['hours'] ?? 0);
                $totalSeconds += (int)round($hours * 3600);
                // If this is the running entry and belongs to the user, add current elapsed since start_time
                if ((string)($t['end_time'] ?? '') === '' && (string)($t['start_time'] ?? '') !== '') {
                    if ($date !== '' && $start !== '') {
                        $startDt = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $date . ' ' . $start)
                                   ?: \DateTimeImmutable::createFromFormat('Y-m-d H:i', $date . ' ' . $start);
                        if ($startDt) {
                            $elapsed = max(0, (new \DateTimeImmutable('now'))->getTimestamp() - $startDt->getTimestamp());
                            $totalSeconds += $elapsed;
                        }
                    }
                }
            }
            $payload = [
                'id' => (int)($running['id'] ?? 0),
                'task_id' => $taskId,
                'date' => $date,
                'start_time' => $start,
                'iso_start' => $isoStart,
                'description' => (string)($running['description'] ?? ''),
                'user_total_seconds_for_task' => $totalSeconds,
                'user_total_hours_for_task' => $totalSeconds > 0 ? round($totalSeconds / 3600, 2) : 0.0,
            ];
            echo json_encode(['ok' => true, 'running' => $payload, 'now' => (new \DateTimeImmutable('now'))->format(DATE_ATOM)]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => 'server_error']);
        }
    }

    public function view(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $item = $id ? $this->timesStore->get($id) : null;
        if (!$item) { redirect('/times'); }
        $schema = Schemas::get('times');
        $fields = array_map(fn($f) => ['name' => $f['name'], 'label' => $f['label'] ?? $f['name']], $schema['fields']);
        array_unshift($fields, ['name' => 'id', 'label' => 'ID']);
        $fields[] = ['name' => 'created_at', 'label' => __('Created')];
        render('entity_view', [
            'title' => __('Working Time') . ' #' . $id,
            'fields' => $fields,
            'item' => $item,
            'back_url' => url('/times'),
            'edit_url' => url('/times/edit', ['id' => $id])
        ]);
    }

    public function list(): void
    {
        $path = current_path();
        $q = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
        $sort = isset($_GET['sort']) ? (string)$_GET['sort'] : 'date';
        $dir = strtolower((string)($_GET['dir'] ?? 'desc')) === 'asc' ? 'asc' : 'desc';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $per = max(1, min(100, (int)($_GET['per'] ?? 10)));

        $times = $this->timesStore->all();
        $contactsById = [];
        foreach ($this->contactsStore->all() as $c) { $contactsById[$c['id']] = $c; }
        $employeesById = [];
        foreach ($this->employeesStore->all() as $e) { $employeesById[$e['id']] = $e; }
        $totalsByContact = [];
        $totalsByMonth = [];
        foreach ($times as &$t) {
            $contactId = $t['contact_id'] ?? null;
            $t['contact_name'] = $contactsById[$contactId]['name'] ?? 'Unassigned';
            $empId = $t['employee_id'] ?? 0;
            $t['employee_name'] = $empId ? ($employeesById[$empId]['name'] ?? __('Unassigned')) : __('Unassigned');
            $h = (float)($t['hours'] ?? 0);
            $totalsByContact[$t['contact_name']] = ($totalsByContact[$t['contact_name']] ?? 0) + $h;
            $ym = substr((string)($t['date'] ?? ''), 0, 7);
            if ($ym !== '') {
                $totalsByMonth[$ym] = ($totalsByMonth[$ym] ?? 0) + $h;
            }
        }
        unset($t);

        // Filter
        if ($q !== '') {
            $needle = mb_strtolower($q);
            $times = array_values(array_filter($times, function($it) use ($needle) {
                foreach (['date','description','contact_name','employee_name','start_time','end_time'] as $field) {
                    $v = (string)($it[$field] ?? '');
                    if ($v !== '' && str_contains(mb_strtolower($v), $needle)) { return true; }
                }
                $hours = (string)($it['hours'] ?? '');
                return $hours !== '' && str_contains(mb_strtolower((string)$hours), $needle);
            }));
        }

        // Sort
        $allowed = ['date','hours','contact_name'];
        if (!in_array($sort, $allowed, true)) { $sort = 'date'; }
        usort($times, function($a,$b) use ($sort, $dir) {
            if ($sort === 'hours') {
                $va = (float)($a['hours'] ?? 0); $vb = (float)($b['hours'] ?? 0);
                $cmp = $va <=> $vb;
            } else {
                $va = (string)($a[$sort] ?? ''); $vb = (string)($b[$sort] ?? '');
                $cmp = strcmp($va, $vb);
            }
            return $dir === 'asc' ? $cmp : -$cmp;
        });

        // Keep totals sorted
        ksort($totalsByContact);
        krsort($totalsByMonth);

        $total = count($times);
        $offset = ($page - 1) * $per;
        $paged = array_slice($times, $offset, $per);

        $schema = Schemas::get('times');
        render('times_list', [
            'times' => $paged,
            'contacts' => $contactsById,
            'totalsByContact' => $totalsByContact,
            'totalsByMonth' => $totalsByMonth,
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
        $schema = Schemas::get('times');
        $fields = self::injectOptions($schema['fields'], $contacts, $employees);
        render('times_add', [
            'fields' => $fields,
            'cancel_url' => url('/times'),
            'contacts' => $contacts,
            'employees' => $employees,
        ]);
    }

    public function create(): void
    {
        $contacts = $this->contactsStore->all();
        $employees = $this->employeesStore->all();
        $dto = TimeEntryDTO::fromInput($_POST);
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
        if (!empty($errors)) {
            $error = __('Please fix the highlighted errors.');
            $schema = Schemas::get('times');
            $fields = self::injectOptions($schema['fields'], $contacts, $employees);
            render('times_add', ['error' => $error, 'errors' => $errors, 'fields' => $fields, 'cancel_url' => url('/times'), 'contacts' => $contacts, 'employees' => $employees, 'contactId' => $contactId, 'employeeId' => $empId] + $data);
            return;
        }
        $this->timesStore->add($data + [
            'created_at' => \App\Util\Dates::nowAtom(),
        ]);
        redirect('/times');
    }

    public function editForm(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $time = $id ? $this->timesStore->get($id) : null;
        if (!$time) { redirect('/times'); }
        $contacts = $this->contactsStore->all();
        $employees = $this->employeesStore->all();
        $schema = Schemas::get('times');
        $fields = self::injectOptions($schema['fields'], $contacts, $employees);
        render('times_add', ['edit' => true, 'time' => $time, 'fields' => $fields, 'cancel_url' => url('/times'), 'contacts' => $contacts, 'employees' => $employees]);
    }

    public function update(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) { redirect('/times'); }
        $contacts = $this->contactsStore->all();
        $employees = $this->employeesStore->all();
        $dto = TimeEntryDTO::fromInput($_POST);
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
        if (!empty($errors)) {
            $error = __('Please fix the highlighted errors.');
            $schema = Schemas::get('times');
            $fields = self::injectOptions($schema['fields'], $contacts, $employees);
            render('times_add', ['error' => $error, 'errors' => $errors, 'fields' => $fields, 'cancel_url' => url('/times'), 'contacts' => $contacts, 'employees' => $employees, 'contactId' => $contactId, 'employeeId' => $empId, 'time' => $this->timesStore->get($id), 'edit' => true] + $data);
            return;
        }
        $this->timesStore->update($id, $data);
        redirect('/times');
    }

    #[NoReturn]
    public function delete(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) { $this->timesStore->delete($id); }
        redirect('/times');
    }

    /**
     * Injects select options for contact_id and employee_id into schema fields.
     * @param array<int, array> $fields
     * @param array<int, array> $contacts
     * @param array<int, array> $employees
     * @return array<int, array>
     */
    private static function injectOptions(array $fields, array $contacts, array $employees): array
    {
        // Build options maps
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
        foreach ($fields as &$f) {
            $name = $f['name'] ?? '';
            if ($name === 'contact_id') {
                $f['options'] = $contactOpts;
            } elseif ($name === 'employee_id') {
                $f['options'] = $employeeOpts;
            }
        }
        unset($f);
        return $fields;
    }
}
