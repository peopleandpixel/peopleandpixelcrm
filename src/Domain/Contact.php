<?php

declare(strict_types=1);

namespace App\Domain;

use App\Validation\Validator;
use App\Util\Sanitizer;

class Contact
{
    public string $name;
    public string $company;
    public string $notes;

    // New extended fields
    /** @var array<int, array{value:string, tag:string, kind:string}> */
    public array $phones = [];
    /** @var array<int, array{value:string, tag:string}> */
    public array $emails = [];
    /** @var array<int, array{value:string, tag:string}> */
    public array $websites = [];
    /** @var array<int, array{value:string, tag:string}> */
    public array $socials = [];

    public string $birthdate = '';
    public string $picture = '';

    // For forms (textarea inputs)
    public string $phones_text = '';
    public string $emails_text = '';
    public string $websites_text = '';
    public string $socials_text = '';

    public function __construct(string $name, string $company = '', string $notes = '')
    {
        $this->name = $name;
        $this->company = $company;
        $this->notes = $notes;
    }

    public static function fromInput(array $in): self
    {
        $name = Sanitizer::string($in['name'] ?? '');
        $company = Sanitizer::string($in['company'] ?? '');
        $notes = Sanitizer::string($in['notes'] ?? '');

        $self = new self($name, $company, $notes);

        $rawBirth = Sanitizer::string($in['birthdate'] ?? '');
        $self->birthdate = $rawBirth === '' ? '' : (\App\Util\Dates::toIsoDate($rawBirth) ?? $rawBirth);
        $self->picture = Sanitizer::string($in['picture'] ?? '');

        // Prefer new array-based inputs; keep *_text for backward compatibility
        $self->phones_text = Sanitizer::string($in['phones_text'] ?? '');
        $self->emails_text = Sanitizer::string($in['emails_text'] ?? '');
        $self->websites_text = Sanitizer::string($in['websites_text'] ?? '');
        $self->socials_text = Sanitizer::string($in['socials_text'] ?? '');

        // New structured array inputs
        $self->phones = [];
        if (isset($in['phones']) && is_array($in['phones'])) {
            foreach ($in['phones'] as $row) {
                if (!is_array($row)) { continue; }
                $value = Sanitizer::string($row['value'] ?? '');
                $kind = strtolower(Sanitizer::string($row['kind'] ?? ''));
                $tag = strtolower(Sanitizer::string($row['tag'] ?? ''));
                if ($value === '') { continue; }
                if (!in_array($kind, ['mobile','landline'], true)) { $kind = 'mobile'; }
                if (!in_array($tag, ['business','private'], true)) { $tag = 'business'; }
                $self->phones[] = ['value' => $value, 'kind' => $kind, 'tag' => $tag];
            }
        }
        $self->emails = [];
        if (isset($in['emails']) && is_array($in['emails'])) {
            foreach ($in['emails'] as $row) {
                if (!is_array($row)) { continue; }
                $value = Sanitizer::string($row['value'] ?? '');
                $tag = strtolower(Sanitizer::string($row['tag'] ?? ''));
                if ($value === '') { continue; }
                if (!in_array($tag, ['business','private'], true)) { $tag = 'business'; }
                $self->emails[] = ['value' => $value, 'tag' => $tag];
            }
        }
        $self->websites = [];
        if (isset($in['websites']) && is_array($in['websites'])) {
            foreach ($in['websites'] as $row) {
                if (!is_array($row)) { continue; }
                $value = Sanitizer::string($row['value'] ?? '');
                $tag = strtolower(Sanitizer::string($row['tag'] ?? ''));
                if ($value === '') { continue; }
                if (!in_array($tag, ['business','private'], true)) { $tag = 'business'; }
                $self->websites[] = ['value' => $value, 'tag' => $tag];
            }
        }
        $self->socials = [];
        if (isset($in['socials']) && is_array($in['socials'])) {
            foreach ($in['socials'] as $row) {
                if (!is_array($row)) { continue; }
                $value = Sanitizer::string($row['value'] ?? '');
                $tag = strtolower(Sanitizer::string($row['tag'] ?? ''));
                if ($value === '') { continue; }
                if (!in_array($tag, ['business','private'], true)) { $tag = 'business'; }
                $self->socials[] = ['value' => $value, 'tag' => $tag];
            }
        }

        // Fallback to legacy textarea parsing if no structured inputs provided
        if (empty($self->phones) && $self->phones_text !== '') {
            $self->phones = self::parsePhones($self->phones_text);
        }
        if (empty($self->emails) && $self->emails_text !== '') {
            $self->emails = self::parseTaggedList($self->emails_text);
        }
        if (empty($self->websites) && $self->websites_text !== '') {
            $self->websites = self::parseTaggedList($self->websites_text);
        }
        if (empty($self->socials) && $self->socials_text !== '') {
            $self->socials = self::parseTaggedList($self->socials_text);
        }

        return $self;
    }

    /**
     * Parse comma/semicolon/newline-separated phones.
     * Accepted formats per entry:
     *  - 0176 123456 (defaults: kind=mobile, tag=business)
     *  - mobile:0176 123456
     *  - landline:06151 12345
     *  - business:0176..., private:0176...
     *  - mobile business: 0176..., landline private: 06151...
     */
    private static function parsePhones(string $input): array
    {
        $items = self::splitList($input);
        $out = [];
        foreach ($items as $raw) {
            $value = trim($raw);
            $kind = 'mobile';
            $tag = 'business';
            // Extract prefixes "mobile"/"landline" and "business"/"private"
            $parts = preg_split('/\s+/', strtolower($value));
            // Check for patterns with colon
            if (str_contains($value, ':')) {
                [$prefix, $rest] = array_map('trim', explode(':', $value, 2));
                $prefixParts = preg_split('/\s+/', strtolower($prefix));
                foreach ($prefixParts as $p) {
                    if ($p === 'mobile' || $p === 'landline') { $kind = $p; }
                    if ($p === 'business' || $p === 'private') { $tag = $p; }
                }
                $value = $rest;
            } else {
                // No colon: see if first tokens are known flags and strip them
                $flags = [];
                while (!empty($parts)) {
                    $p = $parts[0];
                    if (in_array($p, ['mobile','landline','business','private'], true)) {
                        $flags[] = $p; array_shift($parts); continue;
                    }
                    break;
                }
                foreach ($flags as $f) {
                    if ($f === 'mobile' || $f === 'landline') { $kind = $f; }
                    if ($f === 'business' || $f === 'private') { $tag = $f; }
                }
                $value = trim(implode(' ', $parts));
            }
            if ($value !== '') {
                $out[] = ['value' => $value, 'tag' => $tag, 'kind' => $kind];
            }
        }
        return $out;
    }

    /**
     * Parse entries like "business:https://example.com" or "private user@example.com".
     */
    private static function parseTaggedList(string $input): array
    {
        $items = self::splitList($input);
        $out = [];
        foreach ($items as $raw) {
            $value = trim($raw);
            $tag = 'business';
            if (str_contains($value, ':')) {
                [$prefix, $rest] = array_map('trim', explode(':', $value, 2));
                $p = strtolower($prefix);
                if ($p === 'private' || $p === 'business') { $tag = $p; $value = $rest; }
            } else {
                // Check leading token
                $lower = strtolower($value);
                if (str_starts_with($lower, 'private ')) { $tag = 'private'; $value = trim(substr($value, 8)); }
                if (str_starts_with($lower, 'business ')) { $tag = 'business'; $value = trim(substr($value, 9)); }
            }
            if ($value !== '') { $out[] = ['value' => $value, 'tag' => $tag]; }
        }
        return $out;
    }

    private static function splitList(string $input): array
    {
        $normalized = str_replace(['\r'], '', $input);
        $parts = preg_split('/[\n;,]+/', (string)$normalized) ?: [];
        return array_values(array_filter(array_map('trim', $parts), fn($s) => $s !== ''));
    }

    public function validate(): array
    {
        $data = $this->toArray();
        $v = Validator::make($data);
        $v->required('name', 'Name is required.')
          ->date('birthdate', 'Y-m-d', 'Invalid date (YYYY-MM-DD).');
        // Validate emails
        foreach ($this->emails as $idx => $e) {
            $field = 'emails_text';
            if (!filter_var($e['value'], FILTER_VALIDATE_EMAIL)) {
                $v->addError($field, 'email', 'Invalid email: ' . $e['value']);
            }
        }
        // Basic URL validation for websites/socials
        foreach (['websites' => 'websites_text', 'socials' => 'socials_text'] as $prop => $field) {
            foreach ($this->$prop as $w) {
                $val = $w['value'];
                if ($val !== '' && !preg_match('/^https?:\/\//i', $val)) {
                    $v->addError($field, 'url', 'Invalid URL (must start with http/https): ' . $val);
                }
            }
        }
        return $v->errors();
    }

    public function toArray(): array
    {
        // Provide primary flat fields for list/search convenience
        $primaryEmail = $this->emails[0]['value'] ?? '';
        $primaryPhone = $this->phones[0]['value'] ?? '';
        return [
            'name' => $this->name,
            'company' => $this->company,
            'notes' => $this->notes,
            'birthdate' => $this->birthdate,
            'picture' => $this->picture,
            'phones' => $this->phones,
            'emails' => $this->emails,
            'websites' => $this->websites,
            'socials' => $this->socials,
            // Form helper echo-back fields (not stored in DB)
            'phones_text' => $this->phones_text,
            'emails_text' => $this->emails_text,
            'websites_text' => $this->websites_text,
            'socials_text' => $this->socials_text,
            // Derived/legacy fields for backward compatibility
            'email' => $primaryEmail,
            'phone' => $primaryPhone,
        ];
    }
}
