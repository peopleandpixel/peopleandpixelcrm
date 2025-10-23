<?php

declare(strict_types=1);

namespace App\Controller;

use App\Domain\Contact as ContactDTO;
use App\Domain\Schemas;
use App\Util\Dates;
use App\Http\Request;
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
        private ?object $groupsStore = null,
        private ?object $activitiesStore = null,
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
        // Load activities timeline for this contact
        $activities = [];
        if ($this->activitiesStore) {
            try {
                foreach ($this->activitiesStore->all() as $a) {
                    if ((int)($a['contact_id'] ?? 0) === $id) { $activities[] = $a; }
                }
            } catch (\Throwable $e) { /* ignore missing file */ }
            // Sort desc by created_at
            usort($activities, function($a, $b) {
                $da = strtotime((string)($a['created_at'] ?? '')) ?: 0;
                $db = strtotime((string)($b['created_at'] ?? '')) ?: 0;
                if ($da === $db) { return 0; }
                return $da < $db ? 1 : -1;
            });
        }
        render('entity_view', [
            'title' => __('Contact') . ': ' . ($item['name'] ?? ('#' . $id)),
            'fields' => $fields,
            'item' => $item,
            'back_url' => url('/contacts'),
            'edit_url' => url('/contacts/edit', ['id' => $id]),
            'activities' => $activities,
            'add_note_url' => url('/contacts/activity/add')
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
        ListSort::getSortedList(Request::fromGlobals(), 'Contact', 'contacts', $this->contactsStore);
    }

    public function newForm(): void
    {
        render('contacts_form', [
            'title' => __('Add Contact'),
            'form_action' => url('/contacts/new'),
            'cancel_url' => url('/contacts'),
            'groups' => $this->groupsStore ? $this->groupsStore->all() : []
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
            render('contacts_form', ['title' => __('Add Contact'), 'form_action' => url('/contacts/new'), 'error' => $error, 'errors' => $errors, 'cancel_url' => url('/contacts'), 'groups' => $this->groupsStore ? $this->groupsStore->all() : []] + $data);
            return;
        }
        $created = $this->contactsStore->add($dto->toArray() + [
            'created_at' => Dates::nowAtom(),
        ]);
        // Log activity
        if (is_array($created)) {
            $cid = (int)($created['id'] ?? 0);
            $name = (string)($created['name'] ?? '');
            $this->logActivity('contact.created', $cid, __('Contact created') . ($name !== '' ? ': ' . $name : ''));
        }
        Flash::success(__('Contact created successfully.'));
        redirect('/contacts');
    }

    public function editForm(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $contact = $id ? $this->contactsStore->get($id) : null;
        if (!$contact) { redirect('/contacts'); }
        render('contacts_form', ['title' => __('Edit Contact'), 'form_action' => url('/contacts/edit'), 'contact' => $contact, 'cancel_url' => url('/contacts'), 'groups' => $this->groupsStore ? $this->groupsStore->all() : []] + $contact);
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
            render('contacts_form', ['title' => __('Edit Contact'), 'form_action' => url('/contacts/edit'), 'error' => $error, 'errors' => $errors, 'contact' => $contact, 'id' => $id, 'cancel_url' => url('/contacts'), 'groups' => $this->groupsStore ? $this->groupsStore->all() : []] + $dto->toArray());
            return;
        }
        $this->contactsStore->update($id, $dto->toArray());
        // Log activity
        $name = (string)($dto->toArray()['name'] ?? '');
        $this->logActivity('contact.updated', $id, __('Contact updated') . ($name !== '' ? ': ' . $name : ''));
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
        // Log activity
        $this->logActivity('contact.deleted', $id, __('Contact deleted'));
        Flash::success(__('Contact deleted.'));
        redirect('/contacts');
    }

    private function logActivity(string $type, int $contactId, string $message, array $extra = []): void
    {
        if (!$this->activitiesStore) { return; }
        $user = \App\Util\Auth::user();
        $data = [
            'type' => $type,
            'contact_id' => $contactId,
            'message' => $message,
            'created_at' => Dates::nowAtom(),
            'created_by' => $user ? ($user['username'] ?? ($user['fullname'] ?? '')) : 'system',
        ] + $extra;
        try {
            $this->activitiesStore->add($data);
        } catch (\Throwable $e) {
            // ignore logging errors to not block main flow
        }
    }

    public function addNote(): void
    {
        $contactId = (int)($_POST['contact_id'] ?? 0);
        $note = trim((string)($_POST['note'] ?? ''));
        if ($contactId <= 0) { redirect('/contacts'); }
        if ($note === '') {
            Flash::error(__('Note cannot be empty.'));
            redirect(url('/contacts/view', ['id' => $contactId]));
        }
        $this->logActivity('note', $contactId, $note);
        Flash::success(__('Note added.'));
        redirect(url('/contacts/view', ['id' => $contactId]));
    }
}
