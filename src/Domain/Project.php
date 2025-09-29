<?php

declare(strict_types=1);

namespace App\Domain;

use App\Validation\Validator;
use App\Util\Sanitizer;

class Project
{
    public int $contact_id; // customer id (contacts.id)
    public string $name;
    public string $description;
    public string $start_date; // Y-m-d or ''
    public string $end_date;   // Y-m-d or ''
    public string $status;     // planned|active|on_hold|done|cancelled

    public function __construct(int $contact_id, string $name, string $description = '', string $start_date = '', string $end_date = '', string $status = 'planned')
    {
        $this->contact_id = $contact_id;
        $this->name = $name;
        $this->description = $description;
        $this->start_date = $start_date;
        $this->end_date = $end_date;
        $this->status = $status;
    }

    public static function fromInput(array $in): self
    {
        $contact_id = Sanitizer::int($in['contact_id'] ?? 0);
        $name = Sanitizer::string($in['name'] ?? '');
        $description = Sanitizer::string($in['description'] ?? '');
        $start_date = Sanitizer::string($in['start_date'] ?? '');
        $end_date = Sanitizer::string($in['end_date'] ?? '');
        $status = Sanitizer::string($in['status'] ?? 'planned');
        return new self($contact_id, $name, $description, $start_date, $end_date, $status);
    }

    public function validate(): array
    {
        $v = Validator::make($this->toArray());
        $v->required('name', 'Name is required.')
          ->required('contact_id', 'Customer is required.')
          ->enum('status', ['planned','active','on_hold','done','cancelled'], 'Invalid status.')
          ->date('start_date')
          ->date('end_date');
        return $v->errors();
    }

    public function toArray(): array
    {
        return [
            'contact_id' => $this->contact_id,
            'name' => $this->name,
            'description' => $this->description,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'status' => $this->status,
        ];
    }
}
