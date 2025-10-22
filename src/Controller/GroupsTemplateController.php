<?php

declare(strict_types=1);

namespace App\Controller;

use App\Domain\Group as GroupDTO;
use App\Domain\Schemas;
use App\Util\Flash;
use App\Util\ListSort;
use JetBrains\PhpStorm\NoReturn;
use function __;
use function redirect;
use function render;

readonly class GroupsTemplateController
{
    public function __construct(private object $groupsStore) {}

    public function list(): void
    {
        ListSort::getSortedList('Group', 'groups', $this->groupsStore, ['name','color','description','created_at']);
    }

    public function view(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $item = $id ? $this->groupsStore->get($id) : null;
        if (!$item) { redirect('/groups'); }
        $schema = Schemas::get('groups');
        $fields = array_map(fn($f) => ['name' => $f['name'], 'label' => $f['label'] ?? $f['name']], $schema['fields']);
        array_unshift($fields, ['name' => 'id', 'label' => 'ID']);
        $fields[] = ['name' => 'created_at', 'label' => __('Created')];
        render('entity_view', [
            'title' => __('Group') . ': ' . ($item['name'] ?? ('#' . $id)),
            'fields' => $fields,
            'item' => $item,
            'back_url' => url('/groups'),
            'edit_url' => url('/groups/edit', ['id' => $id])
        ]);
    }

    public function newForm(): void
    {
        $schema = Schemas::get('groups');
        render('groups_form', [
            'title' => __('Add Group'),
            'form_action' => url('/groups/new'),
            'fields' => $schema['fields'],
            'cancel_url' => url('/groups')
        ]);
    }

    public function create(): void
    {
        $dto = GroupDTO::fromInput($_POST);
        $errors = $dto->validate();
        if (!empty($errors)) {
            $error = __('Please fix the highlighted errors.');
            $schema = Schemas::get('groups');
            render('groups_form', ['title' => __('Add Group'), 'form_action' => url('/groups/new'), 'error' => $error, 'errors' => $errors, 'fields' => $schema['fields'], 'cancel_url' => url('/groups')] + $dto->toArray());
            return;
        }
        $this->groupsStore->add($dto->toArray());
        Flash::success(__('Group created successfully.'));
        redirect('/groups');
    }

    public function editForm(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $group = $id ? $this->groupsStore->get($id) : null;
        if (!$group) { redirect('/groups'); }
        $schema = Schemas::get('groups');
        render('groups_form', ['title' => __('Edit Group'), 'form_action' => url('/groups/edit'), 'group' => $group, 'fields' => $schema['fields'], 'cancel_url' => url('/groups')] + $group);
    }

    public function update(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) { redirect('/groups'); }
        $dto = GroupDTO::fromInput($_POST);
        $errors = $dto->validate();
        if (!empty($errors)) {
            $error = __('Please fix the highlighted errors.');
            $group = $this->groupsStore->get($id) ?? [];
            $schema = Schemas::get('groups');
            render('groups_form', ['title' => __('Edit Group'), 'form_action' => url('/groups/edit'), 'error' => $error, 'errors' => $errors, 'group' => $group, 'fields' => $schema['fields'], 'cancel_url' => url('/groups')] + $dto->toArray());
            return;
        }
        $this->groupsStore->update($id, $dto->toArray());
        Flash::success(__('Group updated successfully.'));
        redirect('/groups');
    }

    #[NoReturn]
    public function delete(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) { $this->groupsStore->delete($id); }
        Flash::success(__('Group deleted.'));
        redirect('/groups');
    }
}
