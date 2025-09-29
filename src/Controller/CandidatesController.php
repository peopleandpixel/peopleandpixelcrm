<?php

declare(strict_types=1);

namespace App\Controller;

use App\Domain\Candidate as CandidateDTO;
use App\Domain\Schemas;
use App\Http\Request;
use App\Http\UrlGenerator;
use App\Util\Dates;
use JetBrains\PhpStorm\NoReturn;
use function redirect;
use function render;

class CandidatesController
{
    public function __construct(
        private readonly object $candidatesStore,
        private readonly Request $request,
        private readonly UrlGenerator $url
    ) {}

    public function view(): void
    {
        $id = (int)($this->request->get('id') ?? 0);
        $item = $id ? $this->candidatesStore->get($id) : null;
        if (!$item) { redirect('/candidates'); }
        $schema = Schemas::get('candidates');
        $fields = array_map(fn($f) => ['name' => $f['name'], 'label' => $f['label'] ?? $f['name']], $schema['fields']);
        array_unshift($fields, ['name' => 'id', 'label' => 'ID']);
        $fields[] = ['name' => 'created_at', 'label' => __('Created')];
        render('entity_view', [
            'title' => __('Candidate') . ': ' . ($item['name'] ?? ('#' . $id)),
            'fields' => $fields,
            'item' => $item,
            'back_url' => $this->url->url('/candidates'),
            'edit_url' => $this->url->url('/candidates/edit', ['id' => $id])
        ]);
    }

    public function list(): void
    {
        $path = $this->request->path();
        $q = ($this->request->get('q') !== null) ? trim((string)$this->request->get('q')) : '';
        $sort = ($this->request->get('sort') !== null) ? (string)$this->request->get('sort') : 'name';
        $dir = strtolower((string)($this->request->get('dir') ?? 'asc')) === 'desc' ? 'desc' : 'asc';
        $page = max(1, (int)($this->request->get('page') ?? 1));
        $per = max(1, min(100, (int)($this->request->get('per') ?? 10)));

        $items = $this->candidatesStore->all();

        // Filter
        if ($q !== '') {
            $needle = mb_strtolower($q);
            $items = array_values(array_filter($items, function($it) use ($needle) {
                foreach (['name','email','position','status','phone','notes'] as $field) {
                    $v = (string)($it[$field] ?? '');
                    if ($v !== '' && str_contains(mb_strtolower($v), $needle)) {
                        return true;
                    }
                }
                return false;
            }));
        }

        // Sort
        $allowed = ['name','position','status','email','created_at'];
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

        $schema = Schemas::get('candidates');
        render('candidates_list', [
            'candidates' => $paged,
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
        $schema = Schemas::get('candidates');
        render('candidates_add', [
            'fields' => $schema['fields'],
            'cancel_url' => $this->url->url('/candidates')
        ]);
    }

    public function create(): void
    {
        $dto = CandidateDTO::fromInput($this->request->body());
        $errors = $dto->validate();
        if (!empty($errors)) {
            $error = 'Please fix the highlighted errors.';
            $schema = Schemas::get('candidates');
            render('candidates_add', ['error' => $error, 'errors' => $errors, 'fields' => $schema['fields'], 'cancel_url' => $this->url->url('/candidates')] + $dto->toArray());
            return;
        }
        $this->candidatesStore->add($dto->toArray() + [
            'created_at' => Dates::nowAtom(),
        ]);
        redirect('/candidates');
    }

    public function editForm(): void
    {
        $id = (int)($this->request->get('id') ?? 0);
        $candidate = $id ? $this->candidatesStore->get($id) : null;
        if (!$candidate) { redirect('/candidates'); }
        $schema = Schemas::get('candidates');
        render('candidates_add', ['edit' => true, 'candidate' => $candidate, 'fields' => $schema['fields'], 'cancel_url' => $this->url->url('/candidates')] + $candidate);
    }

    public function update(): void
    {
        $id = (int)($this->request->post('id') ?? 0);
        if ($id <= 0) { redirect('/candidates'); }
        $dto = CandidateDTO::fromInput($this->request->body());
        $errors = $dto->validate();
        if (!empty($errors)) {
            $error = 'Please fix the highlighted errors.';
            $candidate = $this->candidatesStore->get($id) ?? [];
            $schema = Schemas::get('candidates');
            render('candidates_add', ['error' => $error, 'errors' => $errors, 'candidate' => $candidate, 'fields' => $schema['fields'], 'cancel_url' => $this->url->url('/candidates')] + $dto->toArray());
            return;
        }
        $this->candidatesStore->update($id, $dto->toArray());
        redirect('/candidates');
    }

    #[NoReturn]
    public function delete(): void
    {
        $id = (int)($this->request->post('id') ?? 0);
        if ($id > 0) { $this->candidatesStore->delete($id); }
        redirect('/candidates');
    }
}
