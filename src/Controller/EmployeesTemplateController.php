<?php

declare(strict_types=1);

namespace App\Controller;

use App\Domain\Employee as EmployeeDTO;
use App\Domain\Schemas;
use App\Http\Request;
use App\Util\ListSort;
use JetBrains\PhpStorm\NoReturn;
use function redirect;
use function render;

readonly class EmployeesTemplateController
{
    public function __construct(private object $employeesStore, private ?\App\StoreInterface $usersStore = null) {}

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
        ListSort::getSortedList(Request::fromGlobals(), 'Employee', 'employees', $this->employeesStore);
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
        $emp = $this->employeesStore->add($dto->toArray() + [
            'created_at' => \App\Util\Dates::nowAtom(),
        ]);
        // Auto-create mapped user if users store is available
        if ($this->usersStore) {
            try {
                $this->createUserForEmployee($emp);
            } catch (\Throwable $e) {
                // ignore failures silently for now
            }
        }
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
    private function createUserForEmployee(array $emp): void
    {
        if (!$this->usersStore) return;
        $login = '';
        $email = (string)($emp['email'] ?? '');
        if ($email !== '') {
            $login = strtolower((string)preg_replace('/@.*$/', '', $email));
        }
        if ($login === '') {
            $name = (string)($emp['name'] ?? 'user');
            $login = strtolower(preg_replace('/[^a-z0-9]+/i', '.', $name));
            $login = trim($login, '.');
            if ($login === '') { $login = 'user'; }
        }
        // ensure unique login
        $existing = array_map(fn($u) => (string)($u['login'] ?? ''), $this->usersStore->all());
        $base = $login; $i = 1;
        while (in_array($login, $existing, true)) { $login = $base . $i; $i++; }
        // generate secure password
        $pwd = rtrim(strtr(base64_encode(random_bytes(12)), '+/', '-_'), '=');
        // default permissions: allow own all, others none
        $entities = ['contacts','times','tasks','employees','candidates','payments','storage'];
        $permissions = [];
        foreach ($entities as $e) {
            $permissions[$e] = [
                'own' => ['view'=>1,'create'=>1,'edit'=>1,'delete'=>1],
                'others' => ['view'=>0,'create'=>0,'edit'=>0,'delete'=>0],
            ];
        }
        $this->usersStore->add([
            'login' => $login,
            'fullname' => (string)($emp['name'] ?? ''),
            'email' => $email,
            'role' => 'user',
            'employee_id' => (int)($emp['id'] ?? 0),
            'permissions' => $permissions,
            'must_change_password' => 1,
            'password_hash' => password_hash($pwd, PASSWORD_DEFAULT),
        ]);
        \App\Util\Flash::info(__('User created for employee {name} with login {login}. Temporary password: {password}. It must be changed on first login.', ['name' => (string)($emp['name'] ?? ''), 'login' => $login, 'password' => $pwd]));
    }
}
