<?php

declare(strict_types=1);

namespace App\Controller;

use App\Domain\Contact as ContactDTO;
use App\Domain\Schemas;
use App\Util\Dates;
use App\Util\Flash;
use JetBrains\PhpStorm\NoReturn;
use function __;
use function redirect;
use function render;

class ContactsController
{
    public function __construct(
        private readonly object $contactsStore,
        private readonly ?object $timesStore = null,
        private readonly ?object $tasksStore = null,
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

    private static function saveUploadedPicture(): ?string
    {
        if (!isset($_FILES['picture_file']) || !is_array($_FILES['picture_file'])) {
            return null;
        }
        $f = $_FILES['picture_file'];
        if (($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return null; // nothing uploaded
        }
        // Basic validations
        $tmp = (string)($f['tmp_name'] ?? '');
        $size = (int)($f['size'] ?? 0);
        if (!is_uploaded_file($tmp)) { return null; }
        if ($size <= 0 || $size > 5 * 1024 * 1024) { // 5MB limit
            return null;
        }
        // Detect mime/type using finfo if available; otherwise fall back to exif_imagetype()/getimagesize()
        $mime = '';
        if (function_exists('finfo_open')) {
            $finfo = @finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo) {
                $mime = (string)@finfo_file($finfo, $tmp);
                @finfo_close($finfo);
            }
        }
        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/gif'  => 'gif',
            'image/webp' => 'webp',
        ];
        $ext = '';
        if ($mime !== '' && isset($allowed[$mime])) {
            $ext = $allowed[$mime];
        } else {
            // Try exif_imagetype
            $type = function_exists('exif_imagetype') ? @exif_imagetype($tmp) : false;
            if ($type) {
                $map = [
                    IMAGETYPE_JPEG => 'jpg',
                    IMAGETYPE_PNG  => 'png',
                    IMAGETYPE_GIF  => 'gif',
                    IMAGETYPE_WEBP => 'webp',
                ];
                if (isset($map[$type])) { $ext = $map[$type]; }
            }
            if ($ext === '') {
                // Last resort: getimagesize
                $info = @getimagesize($tmp);
                if (is_array($info) && isset($info[2])) {
                    $type = (int)$info[2];
                    $map = [
                        IMAGETYPE_JPEG => 'jpg',
                        IMAGETYPE_PNG  => 'png',
                        IMAGETYPE_GIF  => 'gif',
                        IMAGETYPE_WEBP => 'webp',
                    ];
                    if (isset($map[$type])) { $ext = $map[$type]; }
                }
            }
        }
        if ($ext === '') { return null; }
        // Build upload path
        $root = dirname(__DIR__, 2);
        $uploadDir = $root . '/public/uploads';
        if (!is_dir($uploadDir)) { @mkdir($uploadDir, 0777, true); }
        // Safe filename
        $rand = bin2hex(random_bytes(8));
        $namePart = pathinfo((string)($f['name'] ?? ''), PATHINFO_FILENAME);
        $namePart = preg_replace('/[^a-zA-Z0-9_-]+/', '-', (string)$namePart) ?: 'img';
        $fileName = date('Ymd_His') . '_' . $rand . '_' . $namePart . '.' . $ext;
        $dest = $uploadDir . '/' . $fileName;
        if (!move_uploaded_file($tmp, $dest)) { return null; }
        // Return public path
        return '/uploads/' . $fileName;
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
        $path = current_path();
        $q = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
        $sort = isset($_GET['sort']) ? (string)$_GET['sort'] : 'name';
        $dir = strtolower((string)($_GET['dir'] ?? 'asc')) === 'desc' ? 'desc' : 'asc';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $per = max(1, min(100, (int)($_GET['per'] ?? 10)));

        $items = $this->contactsStore->all();

        if ($q !== '') {
            $needle = mb_strtolower($q);
            $items = array_values(array_filter($items, function($it) use ($needle) {
                foreach (['name','email','company','phone','notes'] as $field) {
                    $v = (string)($it[$field] ?? '');
                    if ($v !== '' && str_contains(mb_strtolower($v), $needle)) {
                        return true;
                    }
                }
                return false;
            }));
        }

        $allowed = ['name','company','email','created_at'];
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

        $schema = Schemas::get('contacts');
        render('contacts_list', [
            'contacts' => $paged,
            'total' => $total,
            'page' => $page,
            'per' => $per,
            'sort' => $sort,
            'dir' => $dir,
            'q' => $q,
            'path' => $path,
            'columns' => $schema['columns']
        ]);
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
        $uploaded = self::saveUploadedPicture();
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
        $uploaded = self::saveUploadedPicture();
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
