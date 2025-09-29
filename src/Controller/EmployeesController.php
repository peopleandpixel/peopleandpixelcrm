<?php

declare(strict_types=1);

namespace App\Controller;

use App\Domain\Employee as EmployeeDTO;
use App\Domain\Schemas;
use JetBrains\PhpStorm\NoReturn;
use function redirect;
use function render;

class EmployeesController
{
    public function __construct(private readonly object $employeesStore) {}

    public function view(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $item = $id ? $this->employeesStore->get($id) : null;
        if (!$item) { redirect('/employees'); }
        $schema = Schemas::get('employees');
        $fields = array_map(fn($f) => ['name' => $f['name'], 'label' => $f['label'] ?? $f['name']], $schema['fields']);
        array_unshift($fields, ['name' => 'id', 'label' => 'ID']);
        $fields[] = ['name' => 'created_at', 'label' => __('Created')];
        render('entity_view', [
            'title' => __('Employee') . ': ' . ($item['name'] ?? ('#' . $id)),
            'fields' => $fields,
            'item' => $item,
            'back_url' => url('/employees'),
            'edit_url' => url('/employees/edit', ['id' => $id])
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

        $items = $this->employeesStore->all();

        if ($q !== '') {
            $needle = mb_strtolower($q);
            $items = array_values(array_filter($items, function($it) use ($needle) {
                foreach (['name','email','phone','role','notes'] as $field) {
                    $v = (string)($it[$field] ?? '');
                    if ($v !== '' && str_contains(mb_strtolower($v), $needle)) {
                        return true;
                    }
                }
                return false;
            }));
        }

        $allowed = ['name','email','role','hired_at'];
        if (!in_array($sort, $allowed, true)) { $sort = 'name'; }
        usort($items, function($a, $b) use ($sort, $dir) {
            $va = (string)($a[$sort] ?? '');
            $vb = (string)($b[$sort] ?? '');
            $cmp = strcmp($va, $vb);
            return $dir === 'asc' ? $cmp : -$cmp;
        });

        $total = count($items);
        $offset = ($page - 1) * $per;
        $paged = array_slice($items, $offset, $per);

        $schema = Schemas::get('employees');
        render('employees_list', [
            'employees' => $paged,
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
        $schema = Schemas::get('employees');
        render('employees_add', [
            'fields' => $schema['fields'],
            'cancel_url' => url('/employees')
        ]);
    }

    public function create(): void
    {
        $dto = EmployeeDTO::fromInput($_POST);
        $errors = $dto->validate();
        if (!empty($errors)) {
            $error = 'Please fix the highlighted errors.';
            $schema = Schemas::get('employees');
            render('employees_add', ['error' => $error, 'errors' => $errors, 'fields' => $schema['fields'], 'cancel_url' => url('/employees')] + $dto->toArray());
            return;
        }
        $this->employeesStore->add($dto->toArray() + [
            'created_at' => \App\Util\Dates::nowAtom(),
        ]);
        redirect('/employees');
    }

    public function editForm(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $employee = $id ? $this->employeesStore->get($id) : null;
        if (!$employee) { redirect('/employees'); }
        $schema = Schemas::get('employees');
        render('employees_add', ['edit' => true, 'employee' => $employee, 'fields' => $schema['fields'], 'cancel_url' => url('/employees')] + $employee);
    }

    public function update(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) { redirect('/employees'); }
        $dto = EmployeeDTO::fromInput($_POST);
        $errors = $dto->validate();
        print $errors;
        if (!empty($errors)) {
            $error = 'Please fix the highlighted errors.';
            $employee = $this->employeesStore->get($id) ?? [];
            $schema = Schemas::get('employees');
            render('employees_add', ['error' => $error, 'errors' => $errors, 'employee' => $employee, 'fields' => $schema['fields'], 'cancel_url' => url('/employees')] + $dto->toArray());
            return;
        }
        $this->employeesStore->update($id, $dto->toArray());
        redirect('/employees');
    }

    #[NoReturn]
    public function delete(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) { $this->employeesStore->delete($id); }
        redirect('/employees');
    }
}
