<?php

declare(strict_types=1);

namespace App\Domain;

use App\Validation\Validator;
use App\Util\Sanitizer;

class Task
{
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
        $this->contact_id = $contact_id;
        $this->employee_id = $employee_id;
        $this->title = $title;
        $this->due_date = $due_date;
        $this->status = $status;
        $this->notes = $notes;
        $this->done_date = $done_date;
        $this->project_id = $project_id;
    }

    public static function fromInput(array $in): self
    {
        $contact_id = Sanitizer::int($in['contact_id'] ?? 0);
        $employee_id = Sanitizer::int($in['employee_id'] ?? 0);
        $project_id = Sanitizer::int($in['project_id'] ?? 0);
        $title = Sanitizer::string($in['title'] ?? '');
        $due_date = Sanitizer::string($in['due_date'] ?? '');
        $status = Sanitizer::string($in['status'] ?? 'open');
        $notes = Sanitizer::string($in['notes'] ?? '');
        $done_date = Sanitizer::string($in['done_date'] ?? '');
        return new self($contact_id, $employee_id, $title, $due_date, $status, $notes, $done_date, $project_id);
    }

    public function validate(): array
    {
        $v = Validator::make($this->toArray());
        $v->required('title', 'Title is required.')
          ->enum('status', ['open','in_progress','review','blocked','done'], 'Invalid status.')
          ->date('due_date')
          ->date('done_date');
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
