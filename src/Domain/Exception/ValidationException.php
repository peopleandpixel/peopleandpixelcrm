<?php

declare(strict_types=1);

namespace App\Domain\Exception;

class ValidationException extends DomainException
{
    /** @var array<string, string|string[]> */
    private array $errors;

    /**
     * @param array<string, string|string[]> $errors
     * @param string $message
     */
    public function __construct(array $errors = [], string $message = 'Validation failed')
    {
        parent::__construct($message);
        $this->errors = $errors;
    }

    /**
     * @return array<string, string|string[]>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
