<?php

declare(strict_types=1);

namespace App\Domain;

use App\Validation\Validator;
use App\Util\Sanitizer;

class Group
{
    public string $name;
    public string $color;
    public string $description;

    public function __construct(string $name = '', string $color = '', string $description = '')
    {
        $this->name = $name;
        $this->color = $color;
        $this->description = $description;
    }

    public static function fromInput(array $in): self
    {
        $name = Sanitizer::string($in['name'] ?? '');
        $color = Sanitizer::string($in['color'] ?? '');
        $description = Sanitizer::string($in['description'] ?? '');
        return new self($name, $color, $description);
    }

    public function validate(): array
    {
        $v = Validator::make($this->toArray());
        $v->required('name', __('Name is required.'));
        // Optional: validate color as hex (#RRGGBB)
        $color = $this->color;
        if ($color !== '' && !preg_match('/^#?[0-9a-fA-F]{6}$/', $color)) {
            $v->custom('color', false, __('Invalid color. Use hex like #34d399.'));
        }
        return $v->errors();
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'color' => $this->normalizeColor($this->color),
            'description' => $this->description,
        ];
    }

    private function normalizeColor(string $c): string
    {
        $c = trim($c);
        if ($c === '') return '';
        if ($c[0] !== '#') { $c = '#' . $c; }
        if (preg_match('/^#[0-9a-fA-F]{6}$/', $c)) { return strtolower($c); }
        return '';
    }
}
