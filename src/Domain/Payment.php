<?php

declare(strict_types=1);

namespace App\Domain;

use App\Validation\Validator;
use App\Util\Sanitizer;

class Payment
{
    public string $date; // Y-m-d
    public string $type; // income|expense
    public float $amount;
    public string $counterparty;
    public string $description;
    public string $category;
    public string $tags; // comma-separated

    public function __construct(string $date, string $type, float $amount, string $counterparty = '', string $description = '', string $category = '', string $tags = '')
    {
        $this->date = $date;
        $this->type = $type;
        $this->amount = $amount;
        $this->counterparty = $counterparty;
        $this->description = $description;
        $this->category = $category;
        $this->tags = $tags;
    }

    public static function fromInput(array $in): self
    {
        $rawDate = Sanitizer::string($in['date'] ?? date('Y-m-d'));
        $date = \App\Util\Dates::toIsoDate($rawDate) ?? $rawDate;
        $type = strtolower(Sanitizer::string($in['type'] ?? 'expense'));
        $amount = Sanitizer::float($in['amount'] ?? 0);
        $counterparty = Sanitizer::string($in['counterparty'] ?? '');
        $description = Sanitizer::string($in['description'] ?? '');
        $category = Sanitizer::string($in['category'] ?? '');
        $tags = Sanitizer::string($in['tags'] ?? '');
        return new self($date, $type, $amount, $counterparty, $description, $category, $tags);
    }

    public function validate(): array
    {
        $data = $this->toArray();
        $data['amount'] = $this->amount; // numeric
        $v = Validator::make($data);
        $v->date('date')
          ->enum('type', ['income','expense'], 'Invalid type.')
          ->number('amount', 0.00001, null, 'Amount must be greater than 0.', 'amount');
        return $v->errors();
    }

    public function toArray(): array
    {
        return [
            'date' => $this->date,
            'type' => $this->type,
            'amount' => $this->amount,
            'counterparty' => $this->counterparty,
            'description' => $this->description,
            'category' => $this->category,
            'tags' => $this->tags,
        ];
    }
}
