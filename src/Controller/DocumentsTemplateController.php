<?php

declare(strict_types=1);

namespace App\Controller;

use App\Domain\Schemas;
use App\Http\Request;
use App\Util\ListSort;
use App\Util\Upload;
use App\Util\Flash;
use JetBrains\PhpStorm\NoReturn;
use function __;
use function redirect;
use function render;

readonly class DocumentsTemplateController
{
    public function __construct(private object $documentsStore) {}

    public function list(): void
    {
        ListSort::getSortedList(Request::fromGlobals(), 'Document', 'documents', $this->documentsStore);
    }

    public function view(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $item = $id ? $this->documentsStore->get($id) : null;
        if (!$item) { redirect('/documents'); }
        $schema = Schemas::get('documents');
        $fields = array_map(fn($f) => ['name' => $f['name'], 'label' => $f['label'] ?? $f['name']], $schema['fields']);
        array_unshift($fields, ['name' => 'id', 'label' => 'ID']);
        // Show file link and meta in view
        $fields[] = ['name' => 'mime', 'label' => __('Type')];
        $fields[] = ['name' => 'size', 'label' => __('Size')];
        $fields[] = ['name' => 'created_at', 'label' => __('Created')];
        render('entity_view', [
            'title' => __('Document') . ': ' . ($item['title'] ?? ('#' . $id)),
            'fields' => $fields,
            'item' => $item,
            'back_url' => url('/documents'),
            'edit_url' => url('/documents/edit', ['id' => $id])
        ]);
    }

    public function newForm(): void
    {
        $schema = Schemas::get('documents');
        render('documents_form', [
            'title' => __('Add Document'),
            'form_action' => url('/documents/new'),
            'fields' => $schema['fields'],
            'cancel_url' => url('/documents')
        ]);
    }

    public function create(): void
    {
        $data = $this->extractDocumentData($_POST, $_FILES);
        if (isset($data['error'])) {
            $schema = Schemas::get('documents');
            render('documents_form', [
                'title' => __('Add Document'),
                'form_action' => url('/documents/new'),
                'error' => $data['error'],
                'fields' => $schema['fields'],
                'cancel_url' => url('/documents')
            ] + $data);
            return;
        }
        $data['created_at'] = \App\Util\Dates::nowAtom();
        $this->documentsStore->add($data);
        Flash::success(__('Document created successfully.'));
        redirect('/documents');
    }

    public function editForm(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $doc = $id ? $this->documentsStore->get($id) : null;
        if (!$doc) { redirect('/documents'); }
        $schema = Schemas::get('documents');
        render('documents_form', [
            'title' => __('Edit Document'),
            'form_action' => url('/documents/edit'),
            'fields' => $schema['fields'],
            'cancel_url' => url('/documents'),
            'document' => $doc,
        ] + $doc);
    }

    public function update(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) { redirect('/documents'); }
        $existing = $this->documentsStore->get($id) ?? [];
        $data = $this->extractDocumentData($_POST, $_FILES, $existing);
        if (isset($data['error'])) {
            $schema = Schemas::get('documents');
            render('documents_form', [
                'title' => __('Edit Document'),
                'form_action' => url('/documents/edit'),
                'error' => $data['error'],
                'fields' => $schema['fields'],
                'cancel_url' => url('/documents'),
                'document' => $existing,
            ] + $data);
            return;
        }
        $this->documentsStore->update($id, $data);
        Flash::success(__('Document updated successfully.'));
        redirect('/documents');
    }

    #[NoReturn]
    public function delete(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) { $this->documentsStore->delete($id); }
        Flash::success(__('Document deleted.'));
        redirect('/documents');
    }

    /**
     * @param array $post
     * @param array $files
     * @param array $existing
     * @return array
     */
    private function extractDocumentData(array $post, array $files, array $existing = []): array
    {
        $title = trim((string)($post['title'] ?? ''));
        if ($title === '') {
            return ['error' => __('validation.required'), 'title' => $title] + $post;
        }
        $entity = (string)($post['entity'] ?? '');
        $entity_id = (int)($post['entity_id'] ?? 0);
        $notes = (string)($post['notes'] ?? '');

        $fileUrl = (string)($post['file_url'] ?? '');
        $mime = (string)($existing['mime'] ?? '');
        $size = (int)($existing['size'] ?? 0);

        // Handle uploaded file if present
        if (isset($files['file']) && is_array($files['file']) && (int)($files['file']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK) {
            $res = Upload::handle('file', [
                'allowed_mime' => [
                    'application/pdf' => 'pdf',
                    'application/msword' => 'doc',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
                    'application/vnd.ms-excel' => 'xls',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
                    'text/plain' => 'txt',
                    'text/csv' => 'csv',
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/gif' => 'gif',
                    'image/webp' => 'webp',
                ],
                'max_size' => 20 * 1024 * 1024, // 20MB
                'subdir' => 'docs',
            ]);
            if (!$res['ok']) {
                return ['error' => __('Upload failed: {error}', ['error' => (string)($res['error'] ?? 'unknown')])] + $post;
            }
            $fileUrl = (string)$res['url'];
            $mime = $this->detectMimeFromUrl($res['url']);
            $size = (int)($res['size'] ?? 0);
        }

        return [
            'title' => $title,
            'file_url' => $fileUrl,
            'entity' => $entity,
            'entity_id' => $entity_id,
            'notes' => $notes,
            'mime' => $mime,
            'size' => $size,
        ];
    }

    private function detectMimeFromUrl(string $url): string
    {
        $ext = strtolower(pathinfo(parse_url($url, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));
        return match($ext) {
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'txt' => 'text/plain',
            'csv' => 'text/csv',
            'jpg','jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            default => ''
        };
    }
}
