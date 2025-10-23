<?php

declare(strict_types=1);

namespace App\Domain;

use App\Validation\Validator;
use App\Util\Sanitizer;

class Task
{
    public const ALLOWED_STATUSES = ['open','in_progress','review','blocked','done'];

    public int $project_id; // optional, 0 means No Project
    public int $contact_id;
    public int $employee_id; // optional, 0 means Unassigned
    public string $title;
    public string $due_date; // Y-m-d or ''
    public string $done_date; // Y-m-d or ''
    public string $status; // open|in_progress|review|blocked|done
    public string $notes;
    /** @var array<int,string> */
    public array $tags = [];
    /** @var array<string,mixed> */
    public array $custom_fields = [];
    public string $tags_text = '';
    public string $custom_fields_json = '';
    // Reminders and recurrence
    public string $reminder_at = '';
    public string $last_reminded_at = '';
    public string $recurrence = 'none';

    public function __construct(int $contact_id, int $employee_id, string $title, string $due_date = '', string $status = 'open', string $notes = '', string $done_date = '', int $project_id = 0)
    {
        // Normalize numeric ids (non-negative)
        $this->contact_id = max(0, $contact_id);
        $this->employee_id = max(0, $employee_id);
        $this->project_id = max(0, $project_id);
        // Normalize text
        $this->title = trim($title);
        $this->notes = $notes;
        // Normalize dates to ISO (Y-m-d) or empty string
        $due = $due_date === '' ? '' : (\App\Util\Dates::toIsoDate($due_date) ?? $due_date);
        $done = $done_date === '' ? '' : (\App\Util\Dates::toIsoDate($done_date) ?? $done_date);
        // Normalize status
        $st = in_array($status, self::ALLOWED_STATUSES, true) ? $status : 'open';
        // Enforce invariant: when status is done, ensure done_date is set; otherwise clear it
        if ($st === 'done' && $done === '') {
            $done = (new \DateTimeImmutable('today'))->format('Y-m-d');
        }
        if ($st !== 'done') {
            $done = '';
        }
        $this->due_date = $due;
        $this->done_date = $done;
        $this->status = $st;
    }

    public static function fromInput(array $in): self
    {
        $contact_id = Sanitizer::int($in['contact_id'] ?? 0);
        $employee_id = Sanitizer::int($in['employee_id'] ?? 0);
        $project_id = Sanitizer::int($in['project_id'] ?? 0);
        $title = Sanitizer::string($in['title'] ?? '');
        $rawDue = Sanitizer::string($in['due_date'] ?? '');
        $due_date = $rawDue === '' ? '' : (\App\Util\Dates::toIsoDate($rawDue) ?? $rawDue);
        $status = Sanitizer::string($in['status'] ?? 'open');
        $notes = Sanitizer::string($in['notes'] ?? '');
        $rawDone = Sanitizer::string($in['done_date'] ?? '');
        $done_date = $rawDone === '' ? '' : (\App\Util\Dates::toIsoDate($rawDone) ?? $rawDone);
        $self = new self($contact_id, $employee_id, $title, $due_date, $status, $notes, $done_date, $project_id);
        // Reminders and recurrence
        $rawReminder = Sanitizer::string($in['reminder_at'] ?? '');
        $self->reminder_at = '';
        if ($rawReminder !== '') {
            // Accept 'Y-m-d H:i' or 'Y-m-d\TH:i' or full RFC3339; store as ATOM
            $val = $rawReminder;
            $val = str_replace('T', ' ', $val);
            $val = preg_replace('/\s+/', ' ', $val ?? '') ?? '';
            $dt = \DateTimeImmutable::createFromFormat('Y-m-d H:i', $val) ?: (function($raw){
                try { return new \DateTimeImmutable($raw); } catch (\Throwable) { return null; }
            })($rawReminder);
            if ($dt) {
                $self->reminder_at = $dt->format(DATE_ATOM);
            }
        }
        $rec = strtolower(Sanitizer::string($in['recurrence'] ?? 'none'));
        $self->recurrence = in_array($rec, ['none','daily','weekly','monthly'], true) ? $rec : 'none';
        // Tags and custom fields
        $self->tags_text = Sanitizer::string($in['tags_text'] ?? '');
        $self->tags = [];
        $rawTags = $self->tags_text;
        if ($rawTags === '' && isset($in['tags']) && is_array($in['tags'])) {
            $rawTags = implode(',', $in['tags']);
        }
        if ($rawTags !== '') {
            $parts = preg_split('/[\s,;]+/', $rawTags) ?: [];
            $tags = [];
            foreach ($parts as $t) {
                $t = trim((string)$t);
                if ($t === '') { continue; }
                $t = strip_tags($t);
                $t = mb_substr($t, 0, 50);
                $tags[] = $t;
            }
            $self->tags = array_values(array_unique($tags));
        }
        $self->custom_fields_json = Sanitizer::string($in['custom_fields_json'] ?? '');
        $self->custom_fields = [];
        if ($self->custom_fields_json !== '') {
            $decoded = json_decode($self->custom_fields_json, true);
            if (is_array($decoded)) {
                $map = [];
                foreach ($decoded as $k => $v) {
                    $key = (string)$k;
                    if ($key === '') { continue; }
                    if (is_array($v) || is_object($v)) {
                        $v = json_encode($v, JSON_UNESCAPED_UNICODE);
                    }
                    $map[$key] = $v;
                }
                $self->custom_fields = $map;
            }
        }
        return $self;
    }

    public function validate(): array
    {
        $v = Validator::make($this->toArray());
        $v->schema(\App\Domain\Schemas::get('tasks'));
        return $v->errors();
    }

    public function toArray(): array
    {
        return [
            'project_id' => $this->project_id,
            'contact_id' => $this->contact_id,
            'employee_id' => $this->employee_id,
            'title' => $this->title,
            'due_date' => $this->due_date,
            'done_date' => $this->done_date,
            'status' => $this->status,
            'notes' => $this->notes,
            'tags' => $this->tags,
            'custom_fields' => $this->custom_fields,
            'reminder_at' => $this->reminder_at,
            'last_reminded_at' => $this->last_reminded_at,
            'recurrence' => $this->recurrence,
            // echo-back helpers
            'tags_text' => $this->tags_text,
            'custom_fields_json' => $this->custom_fields_json,
        ];
    }
}
