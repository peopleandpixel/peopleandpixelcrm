<?php

declare(strict_types=1);

namespace App\Controller;

use App\Util\Calendar as CalendarUtil;

final class CalendarController
{
    private readonly object $contactsStore;
    private readonly object $projectsStore;
    private readonly object $tasksStore;

    public function __construct(object $contactsStore, object $projectsStore, object $tasksStore)
    {
        $this->contactsStore = $contactsStore;
        $this->projectsStore = $projectsStore;
        $this->tasksStore = $tasksStore;
    }

    public function index(): void
    {
        render('calendar/index', [
            'title' => __('Calendar'),
        ]);
    }

    public function events(): void
    {
        $types = isset($_GET['types']) ? (string)$_GET['types'] : '';
        $filters = array_values(array_filter(array_map('trim', $types !== '' ? explode(',', $types) : [])));
        $from = isset($_GET['from']) ? (string)$_GET['from'] : null;
        $to = isset($_GET['to']) ? (string)$_GET['to'] : null;

        $contacts = $this->contactsStore->all();
        $projects = $this->projectsStore->all();
        $tasks = $this->tasksStore->all();

        $events = CalendarUtil::buildEvents($contacts, $projects, $tasks, $filters, $from, $to);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['events' => $events], JSON_UNESCAPED_UNICODE);
    }
}
