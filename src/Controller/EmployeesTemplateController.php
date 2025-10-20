<?php

declare(strict_types=1);

namespace App\Controller;

use App\Domain\Employee as EmployeeDTO;
use App\Domain\Schemas;
use App\Util\ListSort;
use JetBrains\PhpStorm\NoReturn;
use function redirect;
use function render;

readonly class EmployeesTemplateController
{
    public function __construct(private object $employeesStore) {}

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
        ListSort::getSortedList('Employee', 'employees', $this->employeesStore, ['name','email','role','hired_at']);
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
