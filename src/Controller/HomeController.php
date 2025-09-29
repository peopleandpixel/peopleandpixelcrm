<?php

declare(strict_types=1);

namespace App\Controller;

use function render;

class HomeController
{
    public function index(): void
    {
        render('home');
    }
}
