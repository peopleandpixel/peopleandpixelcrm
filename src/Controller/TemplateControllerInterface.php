<?php

namespace App\Controller;

use JetBrains\PhpStorm\NoReturn;

interface TemplateControllerInterface
{

    public function view(): void;
    public function list(): void;
    public function create(): void;
    public function update(): void;
    #[NoReturn]
    public function delete(): void;
}