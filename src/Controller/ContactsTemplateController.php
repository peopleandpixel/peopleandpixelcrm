<?php

declare(strict_types=1);

namespace App\Controller;

use App\Domain\Contact as ContactDTO;
use App\Domain\Schemas;
use App\Util\Dates;
use App\Util\Flash;
use App\Util\ListSort;
use App\Util\Uploader;
use JetBrains\PhpStorm\NoReturn;
use function __;
use function redirect;
use function render;

readonly class ContactsTemplateController
{
    public function __construct(
        private object  $contactsStore,
        private ?object $timesStore = null,
        private ?object $tasksStore = null,
    ) {}

    public function view(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $item = $id ? $this->contactsStore->get($id) : null;
        if (!$item) { redirect('/contacts'); }
        $schema = Schemas::get('contacts');
        $fields = array_map(fn($f) => ['name' => $f['name'], 'label' => $f['label'] ?? $f['name']], $schema['fields']);
        // Prepend ID and append Created if present
        array_unshift($fields, ['name' => 'id', 'label' => 'ID']);
        $fields[] = ['name' => 'created_at', 'label' => __('Created')];
        render('entity_view', [
            'title' => __('Contact') . ': ' . ($item['name'] ?? ('#' . $id)),
            'fields' => $fields,
            'item' => $item,
            'back_url' => url('/contacts'),
            'edit_url' => url('/contacts/edit', ['id' => $id])
        ]);
    }



    private static function implodeTagged(array $items): string
    {
        $lines = [];
        foreach ($items as $it) {
            $val = (string)($it['value'] ?? '');
            $tag = (string)($it['tag'] ?? '');
            if ($val === '') { continue; }
            $prefix = in_array($tag, ['business','private'], true) ? ($tag . ': ') : '';
            $lines[] = $prefix . $val;
        }
        return implode("\n", $lines);
    }

    private static function implodePhones(array $phones): string
    {
        $lines = [];
        foreach ($phones as $it) {
            $val = (string)($it['value'] ?? '');
            $tag = (string)($it['tag'] ?? '');
            $kind = (string)($it['kind'] ?? '');
            if ($val === '') { continue; }
            $prefix = [];
            if (in_array($kind, ['mobile','landline'], true)) { $prefix[] = $kind; }
            if (in_array($tag, ['business','private'], true)) { $prefix[] = $tag; }
            $pre = empty($prefix) ? '' : (implode(' ', $prefix) . ': ');
            $lines[] = $pre . $val;
        }
        return implode("\n", $lines);
    }
    public function list(): void
    {
        ListSort::getSortedList('Contact', 'contacts', $this->contactsStore, ['name','company','email','created_at'],);
    }

    public function newForm(): void
    {
        render('contacts_form', [
            'title' => __('Add Contact'),
            'form_action' => url('/contacts/new'),
            'cancel_url' => url('/contacts')
        ]);
    }

    public function create(): void
    {
        $uploaded = Uploader::saveUploadedPicture();
        if ($uploaded) { $_POST['picture'] = $uploaded; }
        $dto = ContactDTO::fromInput($_POST);
        $errors = $dto->validate();
        if (!empty($errors)) {
            $error = __('Please fix the highlighted errors.');
            $data = $dto->toArray();
            render('contacts_form', ['title' => __('Add Contact'), 'form_action' => url('/contacts/new'), 'error' => $error, 'errors' => $errors, 'cancel_url' => url('/contacts')] + $data);
            return;
        }
        $this->contactsStore->add($dto->toArray() + [
            'created_at' => Dates::nowAtom(),
        ]);
        Flash::success(__('Contact created successfully.'));
        redirect('/contacts');
    }

    public function editForm(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $contact = $id ? $this->contactsStore->get($id) : null;
        if (!$contact) { redirect('/contacts'); }
        render('contacts_form', ['title' => __('Edit Contact'), 'form_action' => url('/contacts/edit'), 'contact' => $contact, 'cancel_url' => url('/contacts')] + $contact);
    }

    public function update(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) { redirect('/contacts'); }
        $uploaded = Uploader::saveUploadedPicture();
        if ($uploaded) { $_POST['picture'] = $uploaded; }
        $dto = ContactDTO::fromInput($_POST);
        $errors = $dto->validate();
        if (!empty($errors)) {
            $error = __('Please fix the highlighted errors.');
            $contact = $this->contactsStore->get($id) ?? [];
            render('contacts_form', ['title' => __('Edit Contact'), 'form_action' => url('/contacts/edit'), 'error' => $error, 'errors' => $errors, 'contact' => $contact, 'id' => $id, 'cancel_url' => url('/contacts')] + $dto->toArray());
            return;
        }
        $this->contactsStore->update($id, $dto->toArray());
        Flash::success(__('Contact updated successfully.'));
        redirect('/contacts');
    }

    #[NoReturn]
    public function delete(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) { redirect('/contacts'); }
        // Check referential integrity: restrict delete if related records exist
        $hasTimes = false; $hasTasks = false;
        if ($this->timesStore) {
            foreach ($this->timesStore->all() as $t) { if ((int)($t['contact_id'] ?? 0) === $id) { $hasTimes = true; break; } }
        }
        if ($this->tasksStore) {
            foreach ($this->tasksStore->all() as $t) { if ((int)($t['contact_id'] ?? 0) === $id) { $hasTasks = true; break; } }
        }
        if ($hasTimes || $hasTasks) {
            $reason = $hasTimes && $hasTasks
                ? __('Cannot delete contact: referenced by times and tasks')
                : ($hasTimes ? __('Cannot delete contact: referenced by times') : __('Cannot delete contact: referenced by tasks'));
            Flash::error($reason);
            redirect('/contacts');
        }
        $this->contactsStore->delete($id);
        Flash::success(__('Contact deleted.'));
        redirect('/contacts');
    }
}
