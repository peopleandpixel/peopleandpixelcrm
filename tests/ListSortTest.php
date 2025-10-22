<?php

declare(strict_types=1);

use App\Http\Request;
use App\Util\ListSort;

require __DIR__ . '/../vendor/autoload.php';

// A tiny test harness using PHP assertions
ini_set('assert.exception', '1');

final class ArrayStore { private array $data; public function __construct(array $data){$this->data=$data;} public function all(): array { return $this->data; } }

function captureRender(callable $fn): array {
    // monkey-patch global render() function by defining it here if not exists
    if (!function_exists('render')) {
        function render(string $tpl, array $vars = []): void {
            // Store into a global for test retrieval
            $GLOBALS['__last_render'] = ['template' => $tpl, 'vars' => $vars];
        }
    }
    if (!function_exists('current_path')) {
        function current_path(): string { return '/test'; }
    }
    $GLOBALS['__last_render'] = null;
    $fn();
    return (array)($GLOBALS['__last_render'] ?? []);
}

function makeRequest(array $query): Request {
    return new Request('GET', '/test', '/test', $query, [], [], [], [], []);
}

// Dataset with nulls, numbers, and strings
$data = [
    ['id'=>1,'name'=>'Äda','email'=>'a@example.com','created_at'=>'2024-01-01','qty'=>10],
    ['id'=>2,'name'=>null,'email'=>'c@example.com','created_at'=>'2023-01-01','qty'=>2],
    ['id'=>3,'name'=>'Zoe','email'=>'b@example.com','created_at'=>'2022-01-01','qty'=>'11'],
    ['id'=>4,'name'=>'Ada','email'=>'d@example.com','created_at'=>'2025-01-01','qty'=>null],
];
$store = new ArrayStore($data);

// 1) Default sort by name asc, nulls last, stable
$out = captureRender(function() use ($store) {
    $req = makeRequest([]);
    ListSort::getSortedList($req, 'Employee', 'employees', $store);
});
assert($out['template'] === 'generic_list');
$items = $out['vars']['items'];
$names = array_map(fn($r)=>$r['name']??null, $items);
// With de-umlaut collation, Ada should come before Äda and Zoe, null last
assert($names[0] === 'Ada');
assert($names[count($names)-1] === null);

// 2) Filtering by query across schema-derived fields (name/email/etc.)
$out = captureRender(function() use ($store) {
    $req = makeRequest(['q' => 'example.com', 'per' => 100]);
    ListSort::getSortedList($req, 'Employee', 'employees', $store);
});
$items = $out['vars']['items'];
assert(count($items) === 4);
$out = captureRender(function() use ($store) {
    $req = makeRequest(['q' => 'zoe', 'per' => 100]);
    ListSort::getSortedList($req, 'Employee', 'employees', $store);
});
$items = $out['vars']['items'];
assert(count($items) === 1 && $items[0]['id'] === 3);

// 3) Numeric-aware sorting for qty
$out = captureRender(function() use ($store) {
    $req = makeRequest(['sort' => 'qty', 'dir' => 'asc', 'per' => 100]);
    ListSort::getSortedList($req, 'Storage', 'storage', $store);
});
$items = $out['vars']['items'];
$qtys = array_map(fn($r)=>$r['qty']??null, $items);
assert($qtys[0] === 2);
assert($qtys[1] === 10);
assert($qtys[2] === '11');

// 4) Pagination bounds
$out = captureRender(function() use ($store) {
    $req = makeRequest(['page' => -5, 'per' => 2]);
    ListSort::getSortedList($req, 'Employee', 'employees', $store);
});
$vars = $out['vars'];
assert($vars['page'] === 1);
assert($vars['per'] === 2);
assert($vars['total'] === 4);
