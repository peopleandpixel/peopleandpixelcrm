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
    ) {}

    /**
     * Returns the most recent running timer (no end_time) as JSON.
     */
    public function running(): void
    {
        header('Content-Type: application/json');
        try {
            $running = null; $rid = 0;
            foreach ($this->timesStore->all() as $t) {
                if ((string)($t['end_time'] ?? '') === '') {
                    $id = (int)($t['id'] ?? 0);
                    if ($id > $rid) { $rid = $id; $running = $t; }
                }
            }
            if (!$running) {
                echo json_encode(['ok' => true, 'running' => null, 'now' => (new \DateTimeImmutable('now'))->format(DATE_ATOM)]);
                return;
            }
            $date = (string)($running['date'] ?? '');
            $start = (string)($running['start_time'] ?? '');
            // Best-effort ISO start time (local timezone)
            $isoStart = null;
            if ($date !== '' && $start !== '') {
                $isoStart = $date . 'T' . $start . ':00';
            }
            $payload = [
                'id' => (int)($running['id'] ?? 0),
                'task_id' => (int)($running['task_id'] ?? 0),
                'date' => $date,
                'start_time' => $start,
                'iso_start' => $isoStart,
                'description' => (string)($running['description'] ?? ''),
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
