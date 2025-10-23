<?php

declare(strict_types=1);

namespace App\Controller;

use App\Domain\Schemas;
use App\Util\Csrf;
use App\Util\Flash;
use App\Http\Request;
use JetBrains\PhpStorm\NoReturn;
use function __;
use function redirect;
use function render;
use function url;
use function current_path;

final class DealsController
{
    public function __construct(
        private readonly object $dealsStore,
        private readonly object $contactsStore,
    ) {}

    public function list(): void
    {
        // Use generic list renderer with saved views, sorting, filtering
        $req = Request::fromGlobals();
        $path = current_path();
        $schema = Schemas::get('deals');

        $q = trim((string)($req->get('q') ?? ''));
        $sort = (string)($req->get('sort') ?? 'expected_close');
        $dir = strtolower((string)($req->get('dir') ?? 'asc')) === 'desc' ? 'desc' : 'asc';
        $page = max(1, (int)($req->get('page') ?? 1));
        $per = max(1, min(100, (int)($req->get('per') ?? 10)));

        $deals = $this->dealsStore->all();
        // Decorate with contact names for display
        $contactsById = [];
        foreach ($this->contactsStore->all() as $c) { $contactsById[(int)($c['id'] ?? 0)] = (string)($c['name'] ?? ''); }
        foreach ($deals as &$d) {
            $cid = (int)($d['contact_id'] ?? 0);
            $d['contact_name'] = $contactsById[$cid] ?? '';
            // normalize numeric/value fields
            $d['value'] = (float)($d['value'] ?? 0);
            $d['probability'] = (int)max(0, min(100, (int)($d['probability'] ?? 0)));
        }
        unset($d);

        // Basic filtering by q across selected fields
        if ($q !== '') {
            $needle = mb_strtolower($q);
            $filterFields = ['title','stage','contact_name','currency','expected_close'];
            $deals = array_values(array_filter($deals, function($it) use ($needle, $filterFields) {
                foreach ($filterFields as $f) {
                    $v = (string)($it[$f] ?? '');
                    if ($v !== '' && str_contains(mb_strtolower($v), $needle)) { return true; }
                }
                return false;
            }));
        }

        // Sort
        $allowedSort = ['expected_close','stage','title','contact_name','value','probability'];
        if (!in_array($sort, $allowedSort, true)) { $sort = 'expected_close'; }
        usort($deals, function($a,$b) use ($sort,$dir){
            $va = $a[$sort] ?? null; $vb = $b[$sort] ?? null;
            if ($va === $vb) { return 0; }
            if ($va === null) { return $dir === 'asc' ? 1 : -1; }
            if ($vb === null) { return $dir === 'asc' ? -1 : 1; }
            if (is_numeric($va) && is_numeric($vb)) {
                $cmp = (float)$va <=> (float)$vb;
                return $dir === 'asc' ? $cmp : -$cmp;
            }
            $cmp = strnatcasecmp((string)$va, (string)$vb);
            return $dir === 'asc' ? $cmp : -$cmp;
        });

        $total = count($deals);
        $offset = ($page - 1) * $per;
        $paged = array_slice($deals, $offset, $per);

        // Forecast rollups
        $sum = 0.0; $weighted = 0.0; $byStage = [];
        foreach ($deals as $it) {
            $v = (float)($it['value'] ?? 0);
            $p = (int)($it['probability'] ?? 0);
            $sum += $v;
            $weighted += $v * max(0, min(100, $p)) / 100.0;
            $st = (string)($it['stage'] ?? 'prospecting');
            $byStage[$st] = ($byStage[$st] ?? 0.0) + $v;
        }

        render('deals_list', [
            'schema' => 'deals',
            'type' => 'Deal',
            'columns' => $schema['columns'],
            'items' => $paged,
            'total' => $total,
            'page' => $page,
            'per' => $per,
            'q' => $q,
            'sort' => $sort,
            'dir' => $dir,
            'path' => $path,
            'rollups' => [
                'total' => $sum,
                'weighted' => $weighted,
                'byStage' => $byStage,
            ],
        ]);
    }

    public function view(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $item = $id ? $this->dealsStore->get($id) : null;
        if (!$item) { redirect('/deals'); }
        $schema = Schemas::get('deals');
        $fields = array_map(fn($f) => ['name' => $f['name'], 'label' => $f['label'] ?? $f['name']], $schema['fields']);
        array_unshift($fields, ['name' => 'id', 'label' => 'ID']);
        $fields[] = ['name' => 'created_at', 'label' => __('Created')];
        render('entity_view', [
            'title' => __('Deal') . ': ' . ($item['title'] ?? ('#' . $id)),
            'fields' => $fields,
            'item' => $item,
            'back_url' => url('/deals'),
            'edit_url' => url('/deals/edit', ['id' => $id])
        ]);
    }

    public function board(): void
    {
        $deals = $this->dealsStore->all();
        $contactsById = [];
        foreach ($this->contactsStore->all() as $c) { $contactsById[(int)($c['id'] ?? 0)] = (string)($c['name'] ?? ''); }
        foreach ($deals as &$d) { $d['contact_name'] = $contactsById[(int)($d['contact_id'] ?? 0)] ?? ''; }
        unset($d);
        // Group by stage
        $stages = self::stages();
        $grouped = [];
        foreach (array_keys($stages) as $k) { $grouped[$k] = []; }
        foreach ($deals as $it) {
            $st = (string)($it['stage'] ?? 'prospecting');
            if (!isset($grouped[$st])) { $grouped[$st] = []; }
            $grouped[$st][] = $it;
        }
        render('deals_board', [
            'stages' => $stages,
            'grouped' => $grouped,
        ]);
    }

    public function newForm(): void
    {
        $contacts = $this->contactsStore->all();
        render('deals_form', [
            'title' => __('Add Deal'),
            'form_action' => url('/deals/new'),
            'contacts' => $contacts,
            'stages' => self::stages(),
        ]);
    }

    public function editForm(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $deal = $id ? $this->dealsStore->get($id) : null;
        if (!$deal) { redirect('/deals'); }
        $contacts = $this->contactsStore->all();
        render('deals_form', [
            'title' => __('Edit Deal'),
            'form_action' => url('/deals/edit'),
            'deal' => $deal,
            'contacts' => $contacts,
            'stages' => self::stages(),
        ] + $deal);
    }

    public function create(): void
    {
        $token = $_POST[Csrf::fieldName()] ?? null;
        if (!Csrf::validate(is_string($token) ? $token : null)) { http_response_code(400); render('errors/400'); return; }
        $data = $this->normalize($_POST);
        $errors = $this->validate($data);
        if ($errors) {
            $contacts = $this->contactsStore->all();
            render('deals_form', ['title' => __('Add Deal'), 'form_action' => url('/deals/new'), 'errors' => $errors, 'contacts' => $contacts, 'stages' => self::stages()] + $data);
            return;
        }
        $this->dealsStore->add($data + ['created_at' => \App\Util\Dates::nowAtom()]);
        Flash::success(__('Deal created successfully.'));
        redirect('/deals');
    }

    public function update(): void
    {
        $token = $_POST[Csrf::fieldName()] ?? null;
        if (!Csrf::validate(is_string($token) ? $token : null)) { http_response_code(400); render('errors/400'); return; }
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) { redirect('/deals'); }
        $data = $this->normalize($_POST);
        $errors = $this->validate($data);
        if ($errors) {
            $contacts = $this->contactsStore->all();
            $deal = $this->dealsStore->get($id) ?? [];
            render('deals_form', ['title' => __('Edit Deal'), 'form_action' => url('/deals/edit'), 'errors' => $errors, 'deal' => $deal, 'contacts' => $contacts, 'stages' => self::stages(), 'id' => $id] + $data);
            return;
        }
        $this->dealsStore->update($id, $data);
        Flash::success(__('Deal updated successfully.'));
        redirect('/deals');
    }

    #[NoReturn]
    public function delete(): void
    {
        $token = $_POST[Csrf::fieldName()] ?? null;
        if (!Csrf::validate(is_string($token) ? $token : null)) { http_response_code(400); render('errors/400'); return; }
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) { $this->dealsStore->delete($id); Flash::success(__('Deal deleted.')); }
        redirect('/deals');
    }

    private static function stages(): array
    {
        return [
            'prospecting' => __('Prospecting'),
            'qualified' => __('Qualified'),
            'proposal' => __('Proposal'),
            'negotiation' => __('Negotiation'),
            'won' => __('Won'),
            'lost' => __('Lost'),
        ];
    }

    /**
     * @param array<string,mixed> $in
     * @return array<string,mixed>
     */
    private function normalize(array $in): array
    {
        $title = (string)($in['title'] ?? '');
        $contactId = (int)($in['contact_id'] ?? 0);
        $stage = (string)($in['stage'] ?? 'prospecting');
        $value = (float)($in['value'] ?? 0);
        $currency = strtoupper(trim((string)($in['currency'] ?? 'EUR')));
        $prob = (int)($in['probability'] ?? 0);
        $prob = max(0, min(100, $prob));
        $rawDate = (string)($in['expected_close'] ?? '');
        $exp = $rawDate === '' ? '' : (\App\Util\Dates::toIsoDate($rawDate) ?? $rawDate);
        $notes = (string)($in['notes'] ?? '');
        return [
            'title' => trim($title),
            'contact_id' => max(0, $contactId),
            'stage' => in_array($stage, array_keys(self::stages()), true) ? $stage : 'prospecting',
            'value' => $value,
            'currency' => $currency !== '' ? substr($currency, 0, 10) : 'EUR',
            'probability' => $prob,
            'expected_close' => $exp,
            'notes' => $notes,
        ];
    }

    /**
     * @param array<string,mixed> $data
     * @return array<string,string> field => error
     */
    private function validate(array $data): array
    {
        $errors = [];
        if (($data['title'] ?? '') === '') { $errors['title'] = __('Title is required.'); }
        $cid = (int)($data['contact_id'] ?? 0);
        if ($cid <= 0 || !$this->contactsStore->get($cid)) { $errors['contact_id'] = __('Selected contact does not exist.'); }
        $prob = (int)($data['probability'] ?? 0);
        if ($prob < 0 || $prob > 100) { $errors['probability'] = __('Probability must be between 0 and 100.'); }
        $val = (float)($data['value'] ?? 0);
        if ($val < 0) { $errors['value'] = __('Value must be non-negative.'); }
        $exp = (string)($data['expected_close'] ?? '');
        if ($exp !== '' && !\App\Util\Dates::isValid($exp, 'Y-m-d')) { $errors['expected_close'] = __('Invalid date (YYYY-MM-DD).'); }
        return $errors;
    }
}
