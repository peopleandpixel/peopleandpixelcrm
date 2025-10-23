<?php

declare(strict_types=1);

namespace App\Controller;

use JetBrains\PhpStorm\NoReturn;
use function __;
use function redirect;

class ViewsController
{
    public function __construct(private object $viewsStore) {}

    #[NoReturn]
    public function save(): void
    {
        // Basic CSRF is handled globally via middleware in forms; assume valid here.
        $entity = isset($_POST['entity']) ? (string)$_POST['entity'] : '';
        $name = isset($_POST['name']) ? trim((string)$_POST['name']) : '';
        if ($entity === '' || $name === '') {
            redirect($_POST['return'] ?? '/');
        }
        // Config fields we support for now
        $config = [
            'q' => (string)($_POST['q'] ?? ''),
            'sort' => (string)($_POST['sort'] ?? 'name'),
            'dir' => strtolower((string)($_POST['dir'] ?? 'asc')) === 'desc' ? 'desc' : 'asc',
            'per' => (int)($_POST['per'] ?? 10),
                        'tag' => (string)($_POST['tag'] ?? ''),
        ];
        if ($config['per'] < 1) { $config['per'] = 10; }
        if ($config['per'] > 100) { $config['per'] = 100; }

        $views = $this->viewsStore->all();
        $views = is_array($views) ? $views : [];
        $item = [
            'entity' => $entity,
            'name' => $name,
            'config' => $config,
        ];
        $this->viewsStore->add($item);
        $ret = (string)($_POST['return'] ?? '/');
        redirect($ret !== '' ? $ret : '/');
    }

    #[NoReturn]
    public function delete(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $this->viewsStore->delete($id);
        }
        $ret = (string)($_POST['return'] ?? '/');
        redirect($ret !== '' ? $ret : '/');
    }
}
