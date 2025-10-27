<?php

declare(strict_types=1);

namespace App\Domain;

use App\Validation\Validator;
use App\Util\Sanitizer;
use App\Util\Phone;

class Employee
{
    public string $name;
    public string $email;
    public string $phone;
    public string $role;
    public float $salary;
    public string $hired_at; // Y-m-d or ''
    public string $notes;

    public function __construct(string $name, string $email = '', string $phone = '', string $role = '', float $salary = 0.0, string $hired_at = '', string $notes = '')
    {
        $this->name = $name;
        $this->email = $email;
        $this->phone = $phone;
        $this->role = $role;
        $this->salary = $salary;
        $this->hired_at = $hired_at;
        $this->notes = $notes;
    }

    public static function fromInput(array $in): self
    {
        $name = Sanitizer::string($in['name'] ?? '');
        $email = Sanitizer::string($in['email'] ?? '');
        $phone = Sanitizer::string($in['phone'] ?? '');
        $phone = $phone !== '' ? (Phone::normalizeE164($phone) ?: $phone) : '';
        $role = Sanitizer::string($in['role'] ?? '');
        $salary = Sanitizer::float($in['salary'] ?? 0);
        $rawHiredAt = Sanitizer::string($in['hired_at'] ?? '');
        // Normalize to ISO date if provided; keep empty string if not
        $hired_at = $rawHiredAt === '' ? '' : (\App\Util\Dates::toIsoDate($rawHiredAt) ?? $rawHiredAt);
        $notes = Sanitizer::string($in['notes'] ?? '');
        return new self($name, $email, $phone, $role, $salary, $hired_at, $notes);
    }

    public function validate(): array
    {
        $data = $this->toArray();
        $data['salary'] = $this->salary; // ensure numeric
        $v = Validator::make($data);
        $v->required('name', 'Name is required.')
          ->email('email', 'Invalid email.')
          ->number('salary', 0, null, 'Salary must be 0 or greater.', 'salary')
          ->date('hired_at', 'Y-m-d', 'Invalid date (YYYY-MM-DD).');
        return $v->errors();
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role,
            'salary' => $this->salary,
            'hired_at' => $this->hired_at,
            'notes' => $this->notes,
        ];
    }
}
