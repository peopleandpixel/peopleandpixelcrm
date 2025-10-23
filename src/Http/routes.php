<?php

declare(strict_types=1);

use App\Container;
use App\Router;
use App\Util\Auth;
use App\Controller\HomeController;
use App\Controller\InstallerController;
use App\Controller\ContactsTemplateController;
use App\Controller\TimesController;
use App\Controller\TasksController;
use App\Controller\EmployeesTemplateController;
use App\Controller\CandidatesTemplateController;
use App\Controller\PaymentsController;
use App\Controller\StorageController;
use App\Controller\ExportController;
use App\Controller\ImportController;
use App\Controller\UploadController;

/**
 * Register application routes.
 */
return static function (Container $container, Router $router): void {
    // 404 handler
    $router->setNotFoundHandler(function(string $path, string $method) {
        http_response_code(404);
        render('errors/404', ['path' => $path, 'method' => $method]);
    });

    // 405 handler
    $router->setMethodNotAllowedHandler(function(string $path, array $allowed) {
        http_response_code(405);
        render('errors/405', ['path' => $path, 'allowed' => $allowed]);
    });

    // Installer
    $router->get('/install', function() use ($container) {
        InstallerController::form($container->get('config'));
    });
    $router->post('/install', function() use ($container) {
        InstallerController::submit($container->get('config'));
    });

    // Home → start page shows Dashboard
    $router->get('/', [$container->get('dashboardController'), 'index']);

    // Dashboard (explicit path kept for direct linking)
    $router->get('/dashboard', [$container->get('dashboardController'), 'index']);

    // Global Search
    $router->get('/search', [$container->get('searchController'), 'html']);
    $router->get('/search.json', [$container->get('searchController'), 'json']);

    // Reports
    $router->get('/reports', [$container->get('reportsController'), 'list']);
    $router->get('/reports/new', [$container->get('reportsController'), 'newForm']);
    $router->post('/reports/new', [$container->get('reportsController'), 'create']);
    $router->get('/reports/run', [$container->get('reportsController'), 'run']);
    $router->get('/reports/export.csv', [$container->get('reportsController'), 'exportCsv']);

    // Auth
    $router->get('/login', function() {
        $error = $_GET['error'] ?? null;
        $return = isset($_GET['return']) ? (string)$_GET['return'] : '/';
        render('login', ['error' => is_string($error) ? $error : null, 'return' => $return]);
    });
    $router->post('/login', function() use ($container) {
        $config = $container->get('config');
        $username = isset($_POST['username']) ? (string)$_POST['username'] : '';
        $password = isset($_POST['password']) ? (string)$_POST['password'] : '';
        $return = isset($_POST['return']) ? (string)$_POST['return'] : '/';
        if (Auth::login($config, $username, $password)) {
            $u = Auth::user();
            if ($u && !empty($u['must_change_password'])) {
                redirect(url('/password/change', ['return' => $return]));
            }
            redirect($return ?: '/');
        }
        $q = http_build_query(['error' => __('Invalid username or password'), 'return' => $return]);
        redirect('/login?' . $q);
    });

    // Password change
    $router->get('/password/change', [$container->get('passwordController'), 'form']);
    $router->post('/password/change', function() use ($container) { ($container->get('passwordController'))->submit($container->get('usersStore')); });
    $router->get('/logout', function() {
        Auth::logout();
        redirect('/');
    });

    // Generic upload endpoint (for AJAX file uploads)
    $router->post('/upload', [$container->get('uploadController'), 'handle']);

    // Saved views
    $router->post('/views/save', [$container->get('viewsController'), 'save']);
    $router->post('/views/delete', [$container->get('viewsController'), 'delete']);

    // Secure file serving from var/uploads
    $router->get('/files/{subdir}/{file}', [$container->get('filesController'), 'serve']);

    // Contacts
    $router->get('/contacts', function() use ($container) {
        $cfg = $container->get('config');
        if (!$cfg->useDb()) {
            send_list_cache_headers([$cfg->jsonPath('contacts.json')], 60);
        }
        ($container->get('contactsController'))->list();
    });
    $router->get('/contacts/view', [$container->get('contactsController'), 'view']);
    $router->get('/contacts/new', [$container->get('contactsController'), 'newForm']);
    $router->post('/contacts/new', [$container->get('contactsController'), 'create']);
    $router->get('/contacts/edit', [$container->get('contactsController'), 'editForm']);
    $router->post('/contacts/edit', [$container->get('contactsController'), 'update']);
    $router->post('/contacts/delete', [$container->get('contactsController'), 'delete']);
    // Contact activities
    $router->post('/contacts/activity/add', [$container->get('contactsController'), 'addNote']);

    // Times
    $router->get('/times', function() use ($container) {
        $cfg = $container->get('config');
        if (!$cfg->useDb()) {
            send_list_cache_headers([$cfg->jsonPath('times.json'), $cfg->jsonPath('contacts.json'), $cfg->jsonPath('employees.json')], 60);
        }
        ($container->get('timesController'))->list();
    });
    $router->get('/times/new', [$container->get('timesController'), 'newForm']);
    $router->get('/times/view', [$container->get('timesController'), 'view']);
    $router->get('/times/running', [$container->get('timesController'), 'running']);
    $router->post('/times/new', [$container->get('timesController'), 'create']);

    // Tasks
    $router->get('/tasks', function() use ($container) {
        $cfg = $container->get('config');
        if (!$cfg->useDb()) {
            send_list_cache_headers([$cfg->jsonPath('tasks.json'), $cfg->jsonPath('contacts.json'), $cfg->jsonPath('employees.json')], 60);
        }
        ($container->get('tasksController'))->list();
    });
    $router->get('/tasks/new', [$container->get('tasksController'), 'newForm']);
    $router->get('/tasks/view', [$container->get('tasksController'), 'view']);
    $router->post('/tasks/new', [$container->get('tasksController'), 'create']);

    // Deals
    $router->get('/deals', function() use ($container) {
        $cfg = $container->get('config');
        if (!$cfg->useDb()) {
            send_list_cache_headers([$cfg->jsonPath('deals.json'), $cfg->jsonPath('contacts.json')], 60);
        }
        ($container->get('dealsController'))->list();
    });
    $router->get('/deals/board', [$container->get('dealsController'), 'board']);
    $router->get('/deals/new', [$container->get('dealsController'), 'newForm']);
    $router->post('/deals/new', [$container->get('dealsController'), 'create']);
    $router->get('/deals/view', function() use ($container) {
        // simple entity view rendering using schema
        ($container->get('dealsController'))->view();
    });
    $router->get('/deals/edit', [$container->get('dealsController'), 'editForm']);
    $router->post('/deals/edit', [$container->get('dealsController'), 'update']);
    $router->post('/deals/delete', [$container->get('dealsController'), 'delete']);

    // Projects
    $router->get('/projects', function() use ($container) {
        $cfg = $container->get('config');
        if (!$cfg->useDb()) {
            send_list_cache_headers([$cfg->jsonPath('projects.json'), $cfg->jsonPath('contacts.json')], 60);
        }
        ($container->get('projectsController'))->list();
    });
    $router->get('/projects/new', [$container->get('projectsController'), 'newForm']);
    $router->get('/projects/view', [$container->get('projectsController'), 'view']);
    $router->post('/projects/new', [$container->get('projectsController'), 'create']);

    // Employees
    $router->get('/employees', function() use ($container) {
        $cfg = $container->get('config');
        if (!$cfg->useDb()) {
            send_list_cache_headers([$cfg->jsonPath('employees.json')], 120);
        }
        ($container->get('employeesController'))->list();
    });
    $router->get('/employees/view', [$container->get('employeesController'), 'view']);
    $router->get('/employees/new', [$container->get('employeesController'), 'newForm']);
    $router->post('/employees/new', [$container->get('employeesController'), 'create']);

    // Candidates
    $router->get('/candidates', function() use ($container) {
        $cfg = $container->get('config');
        if (!$cfg->useDb()) { send_list_cache_headers([$cfg->jsonPath('candidates.json')], 120); }
        ($container->get('candidatesController'))->list();
    });
    $router->get('/candidates/view', [$container->get('candidatesController'), 'view']);
    $router->get('/candidates/new', [$container->get('candidatesController'), 'newForm']);
    $router->post('/candidates/new', [$container->get('candidatesController'), 'create']);

    // Payments
    $router->get('/payments', function() use ($container) {
        $cfg = $container->get('config');
        if (!$cfg->useDb()) { send_list_cache_headers([$cfg->jsonPath('payments.json')], 120); }
        ($container->get('paymentsController'))->list();
    });
    $router->get('/payments/export.csv', [$container->get('paymentsController'), 'exportCsv']);
    $router->get('/payments/new', [$container->get('paymentsController'), 'newForm']);
    $router->get('/payments/view', [$container->get('paymentsController'), 'view']);
    $router->post('/payments/new', [$container->get('paymentsController'), 'create']);

    // Storage
    $router->get('/storage', function() use ($container) {
        $cfg = $container->get('config');
        if (!$cfg->useDb()) { send_list_cache_headers([$cfg->jsonPath('storage.json')], 60); }
        ($container->get('storageController'))->list();
    });

    // Calendar
    $router->get('/calendar', [$container->get('calendarController'), 'index']);
    $router->get('/calendar/events', [$container->get('calendarController'), 'events']);
    $router->get('/calendar/ics', [$container->get('calendarController'), 'ics']);
    $router->get('/storage/new', [$container->get('storageController'), 'newForm']);
    $router->get('/storage/view', [$container->get('storageController'), 'view']);
    $router->post('/storage/new', function() use ($container) { ($container->get('storageController'))->create($container->get('storageStore')); });
    $router->post('/storage/adjust', function() use ($container) { ($container->get('storageController'))->adjust($container->get('storageStore'), $container->get('storage_adjustmentsStore'), $container->get('config')); });
    $router->get('/storage/history', function() use ($container) { $cfg = $container->get('config'); if (!$cfg->useDb()) { send_list_cache_headers([$cfg->jsonPath('storage.json'), $cfg->jsonPath('storage_adjustments.json')], 60); } ($container->get('storageController'))->history($container->get('storageStore'), $container->get('storage_adjustmentsStore')); });

    // Edit & Delete
    $router->get('/times/edit', [$container->get('timesController'), 'editForm']);
    $router->post('/times/edit', [$container->get('timesController'), 'update']);
    $router->post('/times/delete', [$container->get('timesController'), 'delete']);

    $router->get('/tasks/edit', [$container->get('tasksController'), 'editForm']);
    $router->post('/tasks/edit', [$container->get('tasksController'), 'update']);
    $router->post('/tasks/delete', [$container->get('tasksController'), 'delete']);
    $router->post('/tasks/move', [$container->get('tasksController'), 'move']);
    // Task time tracking
    $router->post('/tasks/time/start', [$container->get('tasksController'), 'timeStart']);
    $router->post('/tasks/time/stop', [$container->get('tasksController'), 'timeStop']);

    $router->get('/projects/edit', [$container->get('projectsController'), 'editForm']);
    $router->post('/projects/edit', [$container->get('projectsController'), 'update']);
    $router->post('/projects/delete', [$container->get('projectsController'), 'delete']);

    $router->get('/employees/edit', [$container->get('employeesController'), 'editForm']);
    $router->post('/employees/edit', [$container->get('employeesController'), 'update']);
    $router->post('/employees/delete', [$container->get('employeesController'), 'delete']);

    $router->get('/candidates/edit', [$container->get('candidatesController'), 'editForm']);
    $router->post('/candidates/edit', [$container->get('candidatesController'), 'update']);
    $router->post('/candidates/delete', [$container->get('candidatesController'), 'delete']);

    $router->get('/payments/edit', [$container->get('paymentsController'), 'editForm']);
    $router->post('/payments/edit', [$container->get('paymentsController'), 'update']);
    $router->post('/payments/delete', [$container->get('paymentsController'), 'delete']);

    $router->get('/storage/edit', [$container->get('storageController'), 'editForm']);
    $router->post('/storage/edit', [$container->get('storageController'), 'update']);
    $router->post('/storage/delete', [$container->get('storageController'), 'delete']);

    // Export
    $router->get('/export/contacts.json', function() use ($container) { ExportController::json($container, 'contacts'); });
    $router->get('/export/contacts.csv', function() use ($container) { ExportController::csv($container, 'contacts'); });
    $router->get('/export/times.json', function() use ($container) { ExportController::json($container, 'times'); });
    $router->get('/export/times.csv', function() use ($container) { ExportController::csv($container, 'times'); });
    $router->get('/export/tasks.json', function() use ($container) { ExportController::json($container, 'tasks'); });
    $router->get('/export/tasks.csv', function() use ($container) { ExportController::csv($container, 'tasks'); });
    $router->get('/export/employees.json', function() use ($container) { ExportController::json($container, 'employees'); });
    $router->get('/export/employees.csv', function() use ($container) { ExportController::csv($container, 'employees'); });
    $router->get('/export/candidates.json', function() use ($container) { ExportController::json($container, 'candidates'); });
    $router->get('/export/candidates.csv', function() use ($container) { ExportController::csv($container, 'candidates'); });
    $router->get('/export/payments.json', function() use ($container) { ExportController::json($container, 'payments'); });
    $router->get('/export/payments.csv', function() use ($container) { ExportController::csv($container, 'payments'); });
    $router->get('/export/storage.json', function() use ($container) { ExportController::json($container, 'storage'); });
    $router->get('/export/storage.csv', function() use ($container) { ExportController::csv($container, 'storage'); });

    // Import
    $router->get('/import', [ImportController::class, 'form']);
    $router->post('/import', function() use ($container) { ImportController::submit($container); });

    // Admin - Users management
    $router->get('/admin/users', [$container->get('usersController'), 'list']);
    $router->get('/admin/users/new', [$container->get('usersController'), 'newForm']);
    $router->post('/admin/users/new', [$container->get('usersController'), 'create']);
    $router->get('/admin/users/edit', [$container->get('usersController'), 'editForm']);
    $router->post('/admin/users/edit', [$container->get('usersController'), 'update']);
    $router->post('/admin/users/delete', [$container->get('usersController'), 'delete']);

    // Audit log
    $router->get('/audit', [$container->get('auditController'), 'list']);

    // Public REST API
    // Lists
    $router->get('/api/{entity}', function(string $entity) use ($container) {
        ($container->get('apiController'))->list($entity);
    });
    // Read single
    $router->get('/api/{entity}/{id}', function(string $entity, string $id) use ($container) {
        $_GET['id'] = $id; ($container->get('apiController'))->get($entity);
    });
    // Create
    $router->post('/api/{entity}', function(string $entity) use ($container) {
        ($container->get('apiController'))->create($entity);
    });
    // Update
    $router->post('/api/{entity}/{id}', function(string $entity, string $id) use ($container) {
        // Allow POST to update for environments that block PUT/PATCH
        $_GET['id'] = $id; ($container->get('apiController'))->update($entity);
    });
    $router->put('/api/{entity}/{id}', function(string $entity, string $id) use ($container) {
        $_GET['id'] = $id; ($container->get('apiController'))->update($entity);
    });
    $router->patch('/api/{entity}/{id}', function(string $entity, string $id) use ($container) {
        $_GET['id'] = $id; ($container->get('apiController'))->update($entity);
    });
    // Delete
    $router->delete('/api/{entity}/{id}', function(string $entity, string $id) use ($container) {
        $_GET['id'] = $id; ($container->get('apiController'))->delete($entity);
    });
};
