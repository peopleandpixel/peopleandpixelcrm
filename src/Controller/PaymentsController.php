<?php

declare(strict_types=1);

namespace App\Controller;

use App\Domain\Payment as PaymentDTO;
use App\Domain\Schemas;
use JetBrains\PhpStorm\NoReturn;
use function redirect;
use function render;

class PaymentsController
{
    public function __construct(private readonly object $paymentsStore) {}

    public function view(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $item = $id ? $this->paymentsStore->get($id) : null;
        if (!$item) { redirect('/payments'); }
        $schema = Schemas::get('payments');
        $fields = array_map(fn($f) => ['name' => $f['name'], 'label' => $f['label'] ?? $f['name']], $schema['fields']);
        array_unshift($fields, ['name' => 'id', 'label' => 'ID']);
        $fields[] = ['name' => 'created_at', 'label' => __('Created')];
        render('entity_view', [
            'title' => __('Payment') . ': ' . ($item['date'] ?? ('#' . $id)),
            'fields' => $fields,
            'item' => $item,
            'back_url' => url('/payments'),
            'edit_url' => url('/payments/edit', ['id' => $id])
        ]);
    }

    public function list(): void
    {
        $path = current_path();
        $q = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
        $sort = isset($_GET['sort']) ? (string)$_GET['sort'] : 'date';
        $dir = strtolower((string)($_GET['dir'] ?? 'desc')) === 'asc' ? 'asc' : 'desc';
        $page = max(1, (int)($_GET['page'] ?? 1));
        $per = max(1, min(100, (int)($_GET['per'] ?? 10)));

        $items = $this->paymentsStore->all();

        // Filter
        if ($q !== '') {
            $needle = mb_strtolower($q);
            $items = array_values(array_filter($items, function($it) use ($needle) {
                foreach (['date','type','counterparty','description','category','tags'] as $field) {
                    $v = (string)($it[$field] ?? '');
                    if ($v !== '' && str_contains(mb_strtolower($v), $needle)) {
                        return true;
                    }
                }
                $amt = (string)($it['amount'] ?? '');
                if ($amt !== '' && str_contains(mb_strtolower((string)$amt), $needle)) { return true; }
                return false;
            }));
        }

        // Sort
        $allowed = ['date','type','amount','counterparty','category'];
        if (!in_array($sort, $allowed, true)) { $sort = 'date'; }
        usort($items, function($a, $b) use ($sort, $dir) {
            if ($sort === 'amount') {
                $va = (float)($a['amount'] ?? 0);
                $vb = (float)($b['amount'] ?? 0);
                $cmp = $va <=> $vb;
            } else {
                $va = (string)($a[$sort] ?? '');
                $vb = (string)($b[$sort] ?? '');
                $cmp = strcmp($va, $vb);
            }
            return $dir === 'asc' ? $cmp : -$cmp;
        });

        $total = count($items);
        $offset = ($page - 1) * $per;
        $paged = array_slice($items, $offset, $per);

        $schema = Schemas::get('payments');
        render('payments_list', [
            'payments' => $paged,
            'columns' => $schema['columns'],
            'total' => $total,
            'page' => $page,
            'per' => $per,
            'sort' => $sort,
            'dir' => $dir,
            'q' => $q,
            'path' => $path,
        ]);
    }

    public function newForm(): void
    {
        $schema = Schemas::get('payments');
        render('payments_add', [
            'fields' => $schema['fields'],
            'cancel_url' => url('/payments')
        ]);
    }

    public function create(): void
    {
        $dto = PaymentDTO::fromInput($_POST);
        $errors = $dto->validate();
        if (!empty($errors)) {
            $error = 'Please fix the highlighted errors.';
            $schema = Schemas::get('payments');
            render('payments_add', ['error' => $error, 'errors' => $errors, 'fields' => $schema['fields'], 'cancel_url' => url('/payments')] + $dto->toArray());
            return;
        }
        $this->paymentsStore->add($dto->toArray() + [
            'created_at' => \App\Util\Dates::nowAtom(),
        ]);
        redirect('/payments');
    }

    public function editForm(): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $payment = $id ? $this->paymentsStore->get($id) : null;
        if (!$payment) { redirect('/payments'); }
        $schema = Schemas::get('payments');
        render('payments_add', ['edit' => true, 'payment' => $payment, 'fields' => $schema['fields'], 'cancel_url' => url('/payments')] + $payment);
    }

    public function update(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) { redirect('/payments'); }
        $dto = PaymentDTO::fromInput($_POST);
        $errors = $dto->validate();
        if (!empty($errors)) {
            $error = 'Please fix the highlighted errors.';
            $payment = $this->paymentsStore->get($id) ?? [];
            $schema = Schemas::get('payments');
            render('payments_add', ['error' => $error, 'errors' => $errors, 'payment' => $payment, 'fields' => $schema['fields'], 'cancel_url' => url('/payments')] + $dto->toArray());
            return;
        }
        $this->paymentsStore->update($id, $dto->toArray());
        redirect('/payments');
    }

    #[NoReturn]
    public function delete(): void
    {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) { $this->paymentsStore->delete($id); }
        redirect('/payments');
    }

    public function exportCsv(): void
    {
        $payments = $this->paymentsStore->all();
        usort($payments, fn($a,$b) => strcmp($a['date'] ?? '', $b['date'] ?? ''));
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="payments.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['id','date','type','amount','counterparty','description','category','tags']);
        foreach ($payments as $p) {
            fputcsv($out, [
                (int)($p['id'] ?? 0),
                (string)($p['date'] ?? ''),
                (string)($p['type'] ?? ''),
                (float)($p['amount'] ?? 0),
                (string)($p['counterparty'] ?? ''),
                (string)($p['description'] ?? ''),
                (string)($p['category'] ?? ''),
                (string)($p['tags'] ?? ''),
            ]);
        }
        fclose($out);
        exit;
    }
}
