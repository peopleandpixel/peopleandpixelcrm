<?php

declare(strict_types=1);

namespace App\Domain;

/**
 * Central schema provider for dynamic list and form rendering.
 * Each entity schema defines:
 * - fields: list of field definitions for add/edit forms
 * - columns: list of columns for list view
 * A field definition supports keys: name, label, type, required, options (for select), placeholder, rows (for textarea)
 */
final class Schemas
{
    /**
     * Simple in-process cache for schema definitions to avoid repeated allocations per request.
     * @var array<string, array{fields: array<int, array>, columns: array<int, array>}>|null
     */
    private static ?array $cache = null;

    /** @return array<string, array{fields: array<int, array>, columns: array<int, array>}> */
    public static function all(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }
        self::$cache = [
            'documents' => [
                'fields' => [
                    ['name' => 'title', 'label' => __('Title'), 'type' => 'text', 'required' => true],
                    ['name' => 'file', 'label' => __('File'), 'type' => 'file', 'required' => false],
                    ['name' => 'file_url', 'label' => __('File URL'), 'type' => 'text', 'placeholder' => '/files/uploads/yourfile.ext'],
                    ['name' => 'entity', 'label' => __('Assigned to'), 'type' => 'select', 'options' => [
                        '' => __('— Select —'),
                        'contacts' => __('Contact'),
                        'projects' => __('Project'),
                        'tasks' => __('Task'),
                        'deals' => __('Deal'),
                    ]],
                    ['name' => 'entity_id', 'label' => __('Record ID'), 'type' => 'number'],
                    ['name' => 'notes', 'label' => __('Notes'), 'type' => 'textarea', 'rows' => 3],
                ],
                'columns' => [
                    ['name' => 'title', 'label' => __('Title')],
                    ['name' => 'entity', 'label' => __('Entity')],
                    ['name' => 'entity_id', 'label' => __('Record ID')],
                    ['name' => 'mime', 'label' => __('Type')],
                    ['name' => 'size', 'label' => __('Size')],
                    ['name' => 'created_at', 'label' => __('Created')],
                ],
            ],
            'contacts' => [
                'fields' => [
                    ['name' => 'name', 'label' => __('Name'), 'type' => 'text', 'required' => true],
                    ['name' => 'company', 'label' => __('Company'), 'type' => 'text'],
                    ['name' => 'birthdate', 'label' => __('Birth date'), 'type' => 'date'],
                    ['name' => 'picture', 'label' => __('Picture URL'), 'type' => 'text', 'placeholder' => 'https://...'],
                    ['name' => 'phones_text', 'label' => __('Phones (one per line; prefix with mobile/landline and/or business/private)'), 'type' => 'textarea', 'rows' => 3, 'placeholder' => "mobile business: 0176 123456\nlandline private: 06151 12345"],
                    ['name' => 'emails_text', 'label' => __('Emails (one per line; prefix with business/private)'), 'type' => 'textarea', 'rows' => 3, 'placeholder' => "business: user@company.com\nprivate: user@gmail.com"],
                    ['name' => 'websites_text', 'label' => __('Websites (one per line; prefix with business/private)'), 'type' => 'textarea', 'rows' => 2, 'placeholder' => "https://example.com"],
                    ['name' => 'socials_text', 'label' => __('Social profiles (one per line; prefix with business/private)'), 'type' => 'textarea', 'rows' => 2, 'placeholder' => "https://linkedin.com/in/username"],
                    ['name' => 'tags_text', 'label' => __('Tags (comma separated)'), 'type' => 'text', 'placeholder' => __('comma,separated')],
                    ['name' => 'custom_fields_json', 'label' => __('Custom fields (JSON map)'), 'type' => 'textarea', 'rows' => 3, 'placeholder' => '{"priority": "vip", "source": "web"}'],
                    ['name' => 'notes', 'label' => __('Notes'), 'type' => 'textarea', 'rows' => 4],
                ],
                'columns' => [
                    ['name' => 'picture', 'label' => __('Picture')],
                    ['name' => 'name', 'label' => __('Name')],
                    ['name' => 'company', 'label' => __('Company')],
                    ['name' => 'tags', 'label' => __('Tags')],
                    ['name' => 'email', 'label' => __('Email')],
                    ['name' => 'created_at', 'label' => __('Created')],
                ],
            ],
            'candidates' => [
                'fields' => [
                    ['name' => 'name', 'label' => __('Name'), 'type' => 'text', 'required' => true],
                    ['name' => 'position', 'label' => __('Position'), 'type' => 'text'],
                    ['name' => 'status', 'label' => __('Status'), 'type' => 'select', 'options' => [
                        'new' => __('New'),
                        'in_review' => __('In review'),
                        'interview' => __('Interview'),
                        'offer' => __('Offer'),
                        'hired' => __('Hired'),
                        'rejected' => __('Rejected'),
                    ]],
                    ['name' => 'email', 'label' => __('Email'), 'type' => 'email'],
                    ['name' => 'phone', 'label' => __('Phone'), 'type' => 'tel'],
                    ['name' => 'notes', 'label' => __('Notes'), 'type' => 'textarea', 'rows' => 4],
                ],
                'columns' => [
                    ['name' => 'name', 'label' => __('Name')],
                    ['name' => 'position', 'label' => __('Position')],
                    ['name' => 'status', 'label' => __('Status')],
                    ['name' => 'email', 'label' => __('Email')],
                ],
            ],
            'storage' => [
                'fields' => [
                    ['name' => 'name', 'label' => __('Name'), 'type' => 'text', 'required' => true],
                    ['name' => 'category', 'label' => __('Category'), 'type' => 'text'],
                    ['name' => 'location', 'label' => __('Location'), 'type' => 'text'],
                    ['name' => 'quantity', 'label' => __('Quantity'), 'type' => 'number'],
                    ['name' => 'low_stock_threshold', 'label' => __('Low stock threshold'), 'type' => 'number'],
                    ['name' => 'notes', 'label' => __('Notes'), 'type' => 'textarea', 'rows' => 3],
                ],
                'columns' => [
                    ['name' => 'name', 'label' => __('Name')],
                    ['name' => 'category', 'label' => __('Category')],
                    ['name' => 'location', 'label' => __('Location')],
                    ['name' => 'quantity', 'label' => __('Qty')],
                ],
            ],
            'employees' => [
                'fields' => [
                    ['name' => 'name', 'label' => __('Name'), 'type' => 'text', 'required' => true],
                    ['name' => 'email', 'label' => __('Email'), 'type' => 'email'],
                    ['name' => 'phone', 'label' => __('Phone'), 'type' => 'tel'],
                    ['name' => 'role', 'label' => __('Role'), 'type' => 'text'],
                    ['name' => 'salary', 'label' => __('Salary'), 'type' => 'number'],
                    ['name' => 'hired_at', 'label' => __('Hired At'), 'type' => 'date'],
                    ['name' => 'notes', 'label' => __('Notes'), 'type' => 'textarea', 'rows' => 3],
                ],
                'columns' => [
                    ['name' => 'name', 'label' => __('Name')],
                    ['name' => 'role', 'label' => __('Role')],
                    ['name' => 'email', 'label' => __('Email')],
                    ['name' => 'hired_at', 'label' => __('Hired')],
                ],
            ],
            'payments' => [
                'fields' => [
                    ['name' => 'date', 'label' => __('Date'), 'type' => 'date', 'required' => true],
                    ['name' => 'type', 'label' => __('Type'), 'type' => 'select', 'options' => [
                        'income' => __('Income'),
                        'expense' => __('Expense'),
                    ]],
                    ['name' => 'amount', 'label' => __('Amount'), 'type' => 'number', 'required' => true],
                    ['name' => 'counterparty', 'label' => __('Counterparty'), 'type' => 'text'],
                    ['name' => 'description', 'label' => __('Description'), 'type' => 'textarea', 'rows' => 3],
                    ['name' => 'category', 'label' => __('Category'), 'type' => 'text'],
                    ['name' => 'tags', 'label' => __('Tags'), 'type' => 'text', 'placeholder' => __('comma,separated')],
                ],
                'columns' => [
                    ['name' => 'date', 'label' => __('Date')],
                    ['name' => 'type', 'label' => __('Type')],
                    ['name' => 'amount', 'label' => __('Amount')],
                    ['name' => 'counterparty', 'label' => __('Counterparty')],
                    ['name' => 'category', 'label' => __('Category')],
                ],
            ],
            'times' => [
                'fields' => [
                    ['name' => 'contact_id', 'label' => __('Contact'), 'type' => 'select', 'required' => true, 'options' => []],
                    ['name' => 'employee_id', 'label' => __('Employee'), 'type' => 'select', 'options' => []],
                    ['name' => 'date', 'label' => __('Date'), 'type' => 'date', 'required' => true],
                    ['name' => 'start_time', 'label' => __('Start'), 'type' => 'time'],
                    ['name' => 'end_time', 'label' => __('End'), 'type' => 'time'],
                    ['name' => 'hours', 'label' => __('Hours'), 'type' => 'number'],
                    ['name' => 'description', 'label' => __('Notes'), 'type' => 'textarea', 'rows' => 3],
                ],
                'columns' => [
                    ['name' => 'date', 'label' => __('Date')],
                    ['name' => 'contact_name', 'label' => __('Contact')],
                    ['name' => 'start_time', 'label' => __('Start')],
                    ['name' => 'end_time', 'label' => __('End')],
                    ['name' => 'hours', 'label' => __('Hours')],
                ],
            ],
            'tasks' => [
                'fields' => [
                    ['name' => 'project_id', 'label' => __('Project'), 'type' => 'select', 'options' => []],
                    ['name' => 'contact_id', 'label' => __('Contact'), 'type' => 'select', 'required' => true, 'options' => []],
                    ['name' => 'employee_id', 'label' => __('Employee'), 'type' => 'select', 'options' => []],
                    ['name' => 'title', 'label' => __('Title'), 'type' => 'text', 'required' => true],
                    ['name' => 'due_date', 'label' => __('Due'), 'type' => 'date'],
                    ['name' => 'reminder_at', 'label' => __('Reminder at'), 'type' => 'datetime'],
                    ['name' => 'recurrence', 'label' => __('Recurrence'), 'type' => 'select', 'options' => [
                        'none' => __('None'),
                        'daily' => __('Daily'),
                        'weekly' => __('Weekly'),
                        'monthly' => __('Monthly'),
                    ]],
                    ['name' => 'done_date', 'label' => __('Done date'), 'type' => 'date'],
                    ['name' => 'status', 'label' => __('Status'), 'type' => 'select', 'options' => [
                        'open' => __('Open'),
                        'in_progress' => __('In progress'),
                        'review' => __('In review'),
                        'blocked' => __('Blocked'),
                        'done' => __('Done'),
                    ]],
                    ['name' => 'tags_text', 'label' => __('Tags (comma separated)'), 'type' => 'text', 'placeholder' => __('comma,separated')],
                    ['name' => 'custom_fields_json', 'label' => __('Custom fields (JSON map)'), 'type' => 'textarea', 'rows' => 3, 'placeholder' => '{"priority": "high"}'],
                    ['name' => 'notes', 'label' => __('Notes'), 'type' => 'textarea', 'rows' => 3],
                ],
                'columns' => [
                    ['name' => 'title', 'label' => __('Title')],
                    ['name' => 'project_name', 'label' => __('Project')],
                    ['name' => 'contact_name', 'label' => __('Contact')],
                    ['name' => 'tags', 'label' => __('Tags')],
                    ['name' => 'due_date', 'label' => __('Due')],
                    ['name' => 'done_date', 'label' => __('Done date')],
                    ['name' => 'status', 'label' => __('Status')],
                ],
            ],
            'projects' => [
                'fields' => [
                    ['name' => 'name', 'label' => __('Name'), 'type' => 'text', 'required' => true],
                    ['name' => 'contact_id', 'label' => __('Customer'), 'type' => 'select', 'required' => true, 'options' => []],
                    ['name' => 'start_date', 'label' => __('Start date'), 'type' => 'date'],
                    ['name' => 'end_date', 'label' => __('End date'), 'type' => 'date'],
                    ['name' => 'status', 'label' => __('Status'), 'type' => 'select', 'options' => [
                        'planned' => __('Planned'),
                        'active' => __('Active'),
                        'on_hold' => __('On hold'),
                        'done' => __('Done'),
                        'cancelled' => __('Cancelled'),
                    ]],
                    ['name' => 'description', 'label' => __('Description'), 'type' => 'textarea', 'rows' => 4],
                ],
                'columns' => [
                    ['name' => 'name', 'label' => __('Name')],
                    ['name' => 'customer_name', 'label' => __('Customer')],
                    ['name' => 'status', 'label' => __('Status')],
                    ['name' => 'start_date', 'label' => __('Start')],
                    ['name' => 'end_date', 'label' => __('End')],
                ],
            ],
            'groups' => [
                'fields' => [
                    ['name' => 'name', 'label' => __('Name'), 'type' => 'text', 'required' => true],
                    ['name' => 'color', 'label' => __('Color'), 'type' => 'text', 'placeholder' => '#34d399'],
                    ['name' => 'description', 'label' => __('Description'), 'type' => 'textarea', 'rows' => 3],
                ],
                'columns' => [
                    ['name' => 'name', 'label' => __('Name')],
                    ['name' => 'color', 'label' => __('Color')],
                    ['name' => 'description', 'label' => __('Description')],
                    ['name' => 'created_at', 'label' => __('Created')],
                ],
            ],
            'deals' => [
                'fields' => [
                    ['name' => 'title', 'label' => __('Title'), 'type' => 'text', 'required' => true],
                    ['name' => 'contact_id', 'label' => __('Contact'), 'type' => 'select', 'required' => true, 'options' => []],
                    ['name' => 'stage', 'label' => __('Stage'), 'type' => 'select', 'options' => [
                        'prospecting' => __('Prospecting'),
                        'qualified' => __('Qualified'),
                        'proposal' => __('Proposal'),
                        'negotiation' => __('Negotiation'),
                        'won' => __('Won'),
                        'lost' => __('Lost'),
                    ]],
                    ['name' => 'value', 'label' => __('Value'), 'type' => 'number'],
                    ['name' => 'currency', 'label' => __('Currency'), 'type' => 'text', 'placeholder' => 'EUR'],
                    ['name' => 'probability', 'label' => __('Probability %'), 'type' => 'number'],
                    ['name' => 'expected_close', 'label' => __('Expected close'), 'type' => 'date'],
                    ['name' => 'notes', 'label' => __('Notes'), 'type' => 'textarea', 'rows' => 3],
                ],
                'columns' => [
                    ['name' => 'title', 'label' => __('Title')],
                    ['name' => 'contact_name', 'label' => __('Contact')],
                    ['name' => 'stage', 'label' => __('Stage')],
                    ['name' => 'value', 'label' => __('Value')],
                    ['name' => 'currency', 'label' => __('Curr')],
                    ['name' => 'probability', 'label' => __('Prob %')],
                    ['name' => 'expected_close', 'label' => __('Expected close')],
                ],
            ],
        ];
        return self::$cache;
    }

    /**
     * @param string $key e.g. 'contacts', 'candidates', 'storage'
     * @return array{fields: array<int, array>, columns: array<int, array>} 
     */
    public static function get(string $key): array
    {
        $all = self::all();
        return $all[$key] ?? ['fields' => [], 'columns' => []];
    }
}
