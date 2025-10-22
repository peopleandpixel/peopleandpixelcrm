<?php

declare(strict_types=1);

namespace App\Controller;

use App\Domain\Candidate as CandidateDTO;
use App\Domain\Schemas;
use App\Http\Request;
use App\Http\UrlGenerator;
use App\Util\Dates;
use App\Util\ListSort;
use JetBrains\PhpStorm\NoReturn;
use function redirect;
use function render;

readonly class CandidatesTemplateController implements TemplateControllerInterface
{
    public function __construct(
        private object       $candidatesStore,
        private Request      $request,
        private UrlGenerator $url
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
        ListSort::getSortedList($this->request, 'Candidate', 'candidates', $this->candidatesStore);
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
