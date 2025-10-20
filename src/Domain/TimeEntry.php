<?php

declare(strict_types=1);

namespace App\Domain;

use App\Util\Dates;
use App\Validation\Validator;
use App\Util\Sanitizer;

class TimeEntry
{
    public int $contact_id;
    public int $employee_id; // optional, 0 means Unassigned
    public int $task_id; // optional, 0 means not linked to a task
    public string $date; // Y-m-d
    public float $hours;
    public string $description; // notes/description
    public string $start_time; // HH:MM
    public string $end_time;   // HH:MM

    public function __construct(int $contact_id, int $employee_id, int $task_id, string $date, float $hours, string $description = '', string $start_time = '', string $end_time = '')
    {
        $this->contact_id = $contact_id;
        $this->employee_id = $employee_id;
        $this->task_id = $task_id;
        $this->date = $date;
        $this->hours = $hours;
        $this->description = $description;
        $this->start_time = $start_time;
        $this->end_time = $end_time;
    }

    public static function fromInput(array $in): self
    {
        $contact_id = Sanitizer::int($in['contact_id'] ?? 0);
        $employee_id = Sanitizer::int($in['employee_id'] ?? 0);
        $rawDate = Sanitizer::string($in['date'] ?? date('Y-m-d'));
        // Normalize to ISO (Y-m-d) using strict parsing
        $date = Dates::toIsoDate($rawDate) ?? $rawDate;
        $hours = Sanitizer::float($in['hours'] ?? 0);
        $description = Sanitizer::string($in['description'] ?? '');
        $start_time = Sanitizer::string($in['start_time'] ?? '');
        $end_time = Sanitizer::string($in['end_time'] ?? '');
        $task_id = Sanitizer::int($in['task_id'] ?? 0);

        // If start and end are provided and valid, compute hours
        $computedHours = self::computeHours($start_time, $end_time);
        if ($computedHours !== null) {
            $hours = $computedHours;
        }
        return new self($contact_id, $employee_id, $task_id, $date, $hours, $description, $start_time, $end_time);
    }

    /**
     * Validate payload. Either hours > 0, or valid start/end times that result in > 0 hours.
     */
    public function validate(): array
    {
        $v = Validator::make([
            'contact_id' => $this->contact_id,
            'employee_id' => $this->employee_id,
            'task_id' => $this->task_id,
            'date' => $this->date,
            'hours' => $this->hours,
            'description' => $this->description,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
        ]);
        $v->date('date');

        // Validate optional time fields if provided
        if ($this->start_time !== '') {
            if (!Dates::isValid($this->start_time, 'H:i')) {
                $v->addError('start_time', 'start_time', 'Invalid start time.');
            }
        }
        if ($this->end_time !== '') {
            if (!Dates::isValid($this->end_time, 'H:i')) {
                $v->addError('end_time', 'endTime', 'Invalid end time.');
            }
        }
        if ($this->start_time !== '' && $this->end_time !== '') {
            $computed = self::computeHours($this->start_time, $this->end_time);
            if ($computed === null) {
                $v->addError('end_time', 'endTime', 'End time must be after start time.');
            } elseif ($computed <= 0) {
                $v->addError('hours', 'duration', 'Duration must be greater than 0.');
            } else {
                // align hours with computed to keep consistent
                $this->hours = $computed;
            }
        }

        // If no valid start/end provided, require hours > 0
        if ($this->start_time === '' || $this->end_time === '') {
            $v->number('hours', 0.00001, null, 'Hours must be greater than 0.', 'hours');
        }
        return $v->errors();
    }

    /**
     * Compute hours float between two HH:MM times, or null if invalid.
     */
    private static function computeHours(string $start, string $end): ?float
    {
        if ($start === '' || $end === '') return null;
        $s = Dates::parseExact($start, 'H:i');
        $e = Dates::parseExact($end, 'H:i');
        if (!$s || !$e) return null;
        $diff = $e->getTimestamp() - $s->getTimestamp();
        if ($diff <= 0) return null;
        return round($diff / 3600, 2);
    }

    public function toArray(): array
    {
        return [
            'contact_id' => $this->contact_id,
            'employee_id' => $this->employee_id,
            'task_id' => $this->task_id,
            'date' => $this->date,
            'hours' => $this->hours,
            'description' => $this->description,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
        ];
    }
}
