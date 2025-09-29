<?php

declare(strict_types=1);

namespace App\Controller;

use App\StoreInterface;
use App\Util\Auth;
use App\Util\Flash;

class UsersController
{
    private StoreInterface $store;

    public function __construct(StoreInterface $usersStore)
    {
        $this->store = $usersStore;
    }

    public function list(): void
    {
        if (!Auth::isAdmin()) { http_response_code(403); render('errors/403'); return; }
        $users = $this->store->all();
        render('admin/users_list', ['users' => $users]);
    }

    public function newForm(): void
    {
        if (!Auth::isAdmin()) { http_response_code(403); render('errors/403'); return; }
        $user = [
            'login' => '', 'fullname' => '', 'email' => '', 'role' => 'user', 'permissions' => []
        ];
        render('admin/users_form', ['user' => $user, 'action' => 'create']);
    }

    public function create(): void
    {
        if (!Auth::isAdmin()) { http_response_code(403); render('errors/403'); return; }
        $data = $this->sanitize($_POST);
        if ($data['login'] === '' || $data['password'] === '') {
            Flash::error(__('Login and password are required'));
            render('admin/users_form', ['user' => $data, 'action' => 'create']);
            return;
        }
        // Ensure unique login
        foreach ($this->store->all() as $u) {
            if (($u['login'] ?? '') === $data['login']) {
                Flash::error(__('Login already exists'));
                render('admin/users_form', ['user' => $data, 'action' => 'create']);
                return;
            }
        }
        $user = [
            'login' => $data['login'],
            'fullname' => $data['fullname'],
            'email' => $data['email'],
            'role' => $data['role'],
            'permissions' => $data['permissions'],
            'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
        ];
        $this->store->add($user);
        Flash::success(__('User created'));
        redirect('/admin/users');
    }

    public function editForm(): void
    {
        if (!Auth::isAdmin()) { http_response_code(403); render('errors/403'); return; }
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $user = $this->store->get($id);
        if (!$user) { http_response_code(404); render('errors/404', ['path' => '/admin/users/edit', 'method' => 'GET']); return; }
        render('admin/users_form', ['user' => $user, 'action' => 'edit']);
    }

    public function update(): void
    {
        if (!Auth::isAdmin()) { http_response_code(403); render('errors/403'); return; }
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $orig = $this->store->get($id);
        if (!$orig) { http_response_code(404); render('errors/404', ['path' => '/admin/users/edit', 'method' => 'POST']); return; }
        $data = $this->sanitize($_POST);
        // Prevent changing to duplicate login
        foreach ($this->store->all() as $u) {
            if ((int)($u['id'] ?? 0) !== $id && ($u['login'] ?? '') === $data['login']) {
                Flash::error(__('Login already exists'));
                render('admin/users_form', ['user' => $orig + $data, 'action' => 'edit']);
                return;
            }
        }
        $fields = [
            'login' => $data['login'],
            'fullname' => $data['fullname'],
            'email' => $data['email'],
            'role' => $data['role'],
            'permissions' => $data['permissions'],
        ];
        if ($data['password'] !== '') {
            $fields['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        $this->store->update($id, $fields);
        Flash::success(__('User updated'));
        redirect('/admin/users');
    }

    public function delete(): void
    {
        if (!Auth::isAdmin()) { http_response_code(403); render('errors/403'); return; }
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $user = $this->store->get($id);
        if ($user && ($user['role'] ?? '') === 'admin') {
            Flash::error(__('Cannot delete admin user'));
            redirect('/admin/users');
            return;
        }
        $this->store->delete($id);
        Flash::success(__('User deleted'));
        redirect('/admin/users');
    }

    /**
     * @param array<string,mixed> $in
     * @return array{login:string,fullname:string,email:string,role:string,permissions:array<string,array<string,int>>,password:string}
     */
    private function sanitize(array $in): array
    {
        $entities = ['contacts','times','tasks','employees','candidates','payments','storage'];
        $perms = [];
        foreach ($entities as $e) {
            $perms[$e] = [
                'view' => isset($in['perm'][$e]['view']) ? 1 : 0,
                'create' => isset($in['perm'][$e]['create']) ? 1 : 0,
                'edit' => isset($in['perm'][$e]['edit']) ? 1 : 0,
                'delete' => isset($in['perm'][$e]['delete']) ? 1 : 0,
            ];
        }
        return [
            'login' => trim((string)($in['login'] ?? '')),
            'fullname' => trim((string)($in['fullname'] ?? '')),
            'email' => trim((string)($in['email'] ?? '')),
            'role' => in_array(($in['role'] ?? 'user'), ['user','admin','viewer'], true) ? (string)$in['role'] : 'user',
            'permissions' => $perms,
            'password' => (string)($in['password'] ?? ''),
        ];
    }
}
