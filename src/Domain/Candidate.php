<?php

declare(strict_types=1);

namespace App\Domain;

use App\Validation\Validator;
use App\Util\Sanitizer;

class Candidate
{
    public string $name;
    public string $email;
    public string $phone;
    public string $position;
    public string $status; // new|in_review|interview|offer|hired|rejected
    public string $notes;

    public function __construct(string $name, string $email = '', string $phone = '', string $position = '', string $status = 'new', string $notes = '')
    {
        $this->name = $name;
        $this->email = $email;
        $this->phone = $phone;
        $this->position = $position;
        $this->status = $status;
        $this->notes = $notes;
    }

    public static function fromInput(array $in): self
    {
        $name = Sanitizer::string($in['name'] ?? '');
        $email = Sanitizer::string($in['email'] ?? '');
        $phone = Sanitizer::string($in['phone'] ?? '');
        $position = Sanitizer::string($in['position'] ?? '');
        $status = strtolower(Sanitizer::string($in['status'] ?? 'new'));
        $allowed = ['new','in_review','interview','offer','hired','rejected'];
        if (!in_array($status, $allowed, true)) { $status = 'new'; }
        $notes = Sanitizer::string($in['notes'] ?? '');
        return new self($name, $email, $phone, $position, $status, $notes);
    }

    public function validate(): array
    {
        $v = Validator::make($this->toArray());
        $v->required('name', 'Name is required.')
          ->email('email', 'Invalid email.')
          ->enum('status', ['new','in_review','interview','offer','hired','rejected'], 'Invalid status.');
        return $v->errors();
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'position' => $this->position,
            'status' => $this->status,
            'notes' => $this->notes,
        ];
    }
}
