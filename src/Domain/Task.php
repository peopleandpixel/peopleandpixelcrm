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
        return new self($contact_id, $employee_id, $title, $due_date, $status, $notes, $done_date, $project_id);
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
        ];
    }
}
