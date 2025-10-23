<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\SearchService;

final class SearchController
{
    public function __construct(
        private readonly SearchService $service
    ) {}

    public function html(): void
    {
        $q = isset($_GET['q']) ? (string)$_GET['q'] : '';
        $results = $q !== '' ? $this->service->search($q, 50) : [];
        render('search/index', [
            'title' => __('Search'),
            'q' => $q,
            'results' => $results,
        ]);
    }

    public function json(): void
    {
        $q = isset($_GET['q']) ? (string)$_GET['q'] : '';
        $results = $q !== '' ? $this->service->search($q, 50) : [];
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['q' => $q, 'results' => $results], JSON_UNESCAPED_UNICODE);
    }
}
