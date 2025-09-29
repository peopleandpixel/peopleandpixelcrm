<?php

declare(strict_types=1);

namespace App\Util;

final class View
{
    public static function e(null|string|int|float $value): string
    {
        return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
    }

    public static function nl2brE(?string $value): string
    {
        return nl2br(self::e($value));
    }

    /**
     * Generate a sort link that toggles direction for the given key.
     *
     * @param string $label Visible label
     * @param string $key Sort key (e.g., 'name')
     * @param string|null $currentKey Current sort key from request
     * @param string $currentDir 'asc'|'desc'
     * @param string $path Base path
     */
    public static function sortLink(string $label, string $key, ?string $currentKey, string $currentDir, string $path, array $extraQuery = []): string
    {
        $dir = 'asc';
        if ($currentKey === $key) {
            $dir = strtolower($currentDir) === 'asc' ? 'desc' : 'asc';
        }
        $qs = http_build_query($extraQuery + ['sort' => $key, 'dir' => $dir]);
        $href = $path . ($qs ? ('?' . $qs) : '');
        $arrow = '';
        if ($currentKey === $key) {
            $arrow = $currentDir === 'asc' ? ' ▲' : ' ▼';
        }
        return '<a href="' . self::e($href) . '">' . self::e($label) . $arrow . '</a>';
    }

    /**
     * Render simple pagination links.
     */
    public static function paginate(int $total, int $page, int $perPage, string $path, array $extraQuery = []): string
    {
        $pages = (int)ceil(max(1, $total) / max(1, $perPage));
        if ($pages <= 1) { return ''; }
        $html = '<nav class="pagination">';
        for ($p = 1; $p <= $pages; $p++) {
            $qs = http_build_query($extraQuery + ['page' => $p, 'per' => $perPage]);
            $href = $path . ($qs ? ('?' . $qs) : '');
            $cls = $p === $page ? ' class="active"' : '';
            $html .= '<a' . $cls . ' href="' . self::e($href) . '">' . $p . '</a>';
        }
        $html .= '</nav>';
        return $html;
    }
}
