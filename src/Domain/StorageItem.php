<?php

declare(strict_types=1);

namespace App\Domain;

use App\Validation\Validator;
use App\Util\Sanitizer;

class StorageItem
{
    public string $sku;
    public string $name;
    public string $category;
    public int $quantity;
    public string $location;
    public string $notes;
    public int $low_stock_threshold;

    public function __construct(string $sku = '', string $name = '', string $category = '', int $quantity = 0, string $location = '', string $notes = '', int $low_stock_threshold = 0)
    {
        $this->sku = $sku;
        $this->name = $name;
        $this->category = $category;
        $this->quantity = $quantity;
        $this->location = $location;
        $this->notes = $notes;
        $this->low_stock_threshold = $low_stock_threshold;
    }

    public static function fromInput(array $in): self
    {
        $sku = Sanitizer::string($in['sku'] ?? '');
        $name = Sanitizer::string($in['name'] ?? '');
        $category = Sanitizer::string($in['category'] ?? '');
        $quantity = Sanitizer::int($in['quantity'] ?? 0);
        $location = Sanitizer::string($in['location'] ?? '');
        $notes = Sanitizer::string($in['notes'] ?? '');
        $low = Sanitizer::int($in['low_stock_threshold'] ?? 0);
        return new self($sku, $name, $category, $quantity, $location, $notes, $low);
    }

    public function validate(): array
    {
        $data = $this->toArray();
        $v = Validator::make($data);
        $v->required('name', 'Name is required.')
          ->integer('quantity', 0, null, 'Quantity must be 0 or greater.', 'quantity')
          ->integer('low_stock_threshold', 0, null, 'Threshold must be 0 or greater.', 'low_stock_threshold');
        return $v->errors();
    }

    public function toArray(): array
    {
        return [
            'sku' => $this->sku,
            'name' => $this->name,
            'category' => $this->category,
            'quantity' => $this->quantity,
            'location' => $this->location,
            'notes' => $this->notes,
            'low_stock_threshold' => $this->low_stock_threshold,
        ];
    }
}
