<?php

namespace App\Validation;

use App\Util\Dates;

class Validator
{
    private array $data;
    private array $errors = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public static function make(array $data): self
    {
        return new self($data);
    }

    public function required(string $field, string $message = 'This field is required.', string $code = 'required'): self
    {
        $value = $this->data[$field] ?? null;
        if ($value === null || (is_string($value) && trim($value) === '')) {
            $this->addError($field, $code, $message);
        }
        return $this;
    }

    public function email(string $field, string $message = 'Invalid email format.', string $code = 'email'): self
    {
        $value = $this->data[$field] ?? '';
        if ($value !== '' && filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            $this->addError($field, $code, $message);
        }
        return $this;
    }

    public function date(string $field, string $format = 'Y-m-d', string $message = 'Invalid date.', string $code = 'date'): self
    {
        $value = $this->data[$field] ?? '';
        if ($value === '') { return $this; }
        // Strict validation using DateTimeImmutable and exact format
        if (!Dates::isValid((string)$value, $format)) {
            $this->addError($field, $code, $message);
        }
        return $this;
    }

    public function number(string $field, ?float $min = null, ?float $max = null, string $message = 'Invalid number.', string $code = 'number'): self
    {
        $value = $this->data[$field] ?? null;
        if (!is_numeric($value)) {
            $this->addError($field, $code, $message);
            return $this;
        }
        $num = (float)$value;
        if ($min !== null && $num < $min) {
            $this->addError($field, $code . '.min', $message, ['min' => $min]);
        }
        if ($max !== null && $num > $max) {
            $this->addError($field, $code . '.max', $message, ['max' => $max]);
        }
        return $this;
    }

    public function integer(string $field, ?int $min = null, ?int $max = null, string $message = 'Invalid integer.', string $code = 'integer'): self
    {
        $value = $this->data[$field] ?? null;
        if ($value === null || filter_var($value, FILTER_VALIDATE_INT) === false) {
            $this->addError($field, $code, $message);
            return $this;
        }
        $num = (int)$value;
        if ($min !== null && $num < $min) {
            $this->addError($field, $code . '.min', $message, ['min' => $min]);
        }
        if ($max !== null && $num > $max) {
            $this->addError($field, $code . '.max', $message, ['max' => $max]);
        }
        return $this;
    }

    public function enum(string $field, array $allowed, string $message = 'Invalid value.', string $code = 'enum'): self
    {
        $value = $this->data[$field] ?? null;
        if ($value !== null && $value !== '' && !in_array($value, $allowed, true)) {
            $this->addError($field, $code, $message);
        }
        return $this;
    }

    public function length(string $field, ?int $min = null, ?int $max = null, string $message = 'Invalid length.', string $code = 'length'): self
    {
        $value = (string)($this->data[$field] ?? '');
        $len = mb_strlen($value);
        if ($min !== null && $len < $min) {
            $this->addError($field, $code . '.min', $message, ['min' => $min]);
        }
        if ($max !== null && $len > $max) {
            $this->addError($field, $code . '.max', $message, ['max' => $max]);
        }
        return $this;
    }

    public function addError(string $field, string $code, string $message, array $params = []): void
    {
        $this->errors[$field][] = [
            'code' => $code,
            'key' => 'validation.' . $code,
            'message' => $message,
            'params' => $params,
        ];
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function isValid(): bool
    {
        return empty($this->errors);
    }
}
