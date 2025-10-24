<?php

declare(strict_types=1);

namespace App;

use App\Util\ErrorHandler;
use App\Util\Flash;
use InvalidArgumentException;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Twig\TwigFunction;

/**
 * Tiny service container for sharing singletons and factories.
 */
class Container
{
    /** @var array<string, callable(self): mixed> */
    private array $factories = [];
    /** @var array<string, mixed> */
    private array $instances = [];

    public function __construct()
    {
        // Register default factories
        $this->factories['config'] = function(self $c) {
            return new Config(dirname(__DIR__));
        };
        $this->factories['router'] = function(self $c) {
            return new Router();
        };
        $this->factories['logger'] = function(self $c) : LoggerInterface {
            /** @var Config $cfg */
            $cfg = $c->get('config');
            $logDir = $cfg->getLogDir();
            if (!is_dir($logDir)) {
                @mkdir($logDir, 0777, true);
            }
            $level = $cfg->getLogLevel();
            // Base app logger with daily rotation, keep 14 days
            $logger = new Logger('app');
            $logger->pushHandler(new RotatingFileHandler($logDir . '/app.log', 14, $level));
            return $logger;
        };
        // Channel-specific loggers
        $this->factories['logger.http'] = function(self $c) : LoggerInterface {
            /** @var Config $cfg */
            $cfg = $c->get('config');
            $logDir = $cfg->getLogDir();
            if (!is_dir($logDir)) {
                @mkdir($logDir, 0777, true);
            }
            $level = $cfg->getLogLevel();
            $logger = new Logger('http');
            $logger->pushHandler(new RotatingFileHandler($logDir . '/http.log', 14, $level));
            return $logger;
        };
        $this->factories['logger.security'] = function(self $c) : LoggerInterface {
            /** @var Config $cfg */
            $cfg = $c->get('config');
            $logDir = $cfg->getLogDir();
            if (!is_dir($logDir)) {
                @mkdir($logDir, 0777, true);
            }
            $level = $cfg->getLogLevel();
            $logger = new Logger('security');
            $logger->pushHandler(new RotatingFileHandler($logDir . '/security.log', 30, $level));
            return $logger;
        };
        $this->factories['errorHandler'] = function(self $c) {
            /** @var Config $cfg */
            $cfg = $c->get('config');
            /** @var LoggerInterface $logger */
            $logger = $c->get('logger.http');
            return new ErrorHandler($cfg, $logger);
        };
        // Twig environment
        $this->factories['request'] = function(self $c) {
            return \App\Http\Request::fromGlobals();
        };
        $this->factories['url'] = function(self $c) {
            /** @var \App\Http\Request $req */
            $req = $c->get('request');
            return new \App\Http\UrlGenerator($req);
        };
        $this->factories['twig'] = function(self $c) {
            /** @var Config $cfg */
            $cfg = $c->get('config');
            $templatesPath = $cfg->getTemplatesDir();
            $loader = new \Twig\Loader\FilesystemLoader($templatesPath);
            $options = [];
            $cacheDir = $cfg->getTwigCacheDir();
            $isDebug = $cfg->isDebug();
            if (!$isDebug) {
                if (!is_dir($cacheDir)) {@mkdir($cacheDir, 0777, true);}            
                $options['cache'] = $cacheDir;
            } else {
                $options['debug'] = true;
                $options['auto_reload'] = true;
            }
            // Enforce HTML auto-escaping by default
            $options['autoescape'] = 'html';
            $twig = new \Twig\Environment($loader, $options);
            // Add simple globals
            $twig->addGlobal('currentLang', I18n::getLang());
            // Flash messages available for this request
            $twig->addGlobal('flashes', Flash::consumeAll());
            // Add filters
            $twig->addFilter(new \Twig\TwigFilter('int', fn($value) => (int)$value));
            // Add functions mapping to existing helpers if present
            $twig->addFunction(new TwigFunction('__', fn(string $key, array $repl = []) => I18n::t($key, $repl)));
            $twig->addFunction(new TwigFunction('n__', fn(string $key, $count, array $repl = []) => I18n::plural($key, (int)$count, $repl + ['count' => $count])));
            $twig->addFunction(new TwigFunction('format_date', fn(\DateTimeInterface $d, int $dateType = 2, int $timeType = 0) => I18n::formatDate($d, $dateType, $timeType)));
            $twig->addFunction(new TwigFunction('format_datetime', fn(\DateTimeInterface $d, int $dateType = 2, int $timeType = 3) => I18n::formatDate($d, $dateType, $timeType)));
            $twig->addFunction(new TwigFunction('format_number', fn($n, int $style = 1, int $precision = 2) => I18n::formatNumber((float)$n, $style, $precision)));
            $twig->addFunction(new TwigFunction('csrf_field', fn() => (function(){ return \App\Util\Csrf::fieldName() ? '<input type="hidden" name="' . \App\Util\Csrf::fieldName() . '" value="' . htmlspecialchars(\App\Util\Csrf::getToken(), ENT_QUOTES, 'UTF-8') . '">': ''; })(), ['is_safe' => ['html']]) );
            $twig->addFunction(new TwigFunction('url', fn(string $path = '/', array $params = []) => url($path, $params)));
            $twig->addFunction(new TwigFunction('canonical_url', fn(?string $path = null, array $params = []) => canonical_url($path, $params)));
            $twig->addFunction(new TwigFunction('active_class', fn(string $path) => active_class($path)));
            $twig->addFunction(new TwigFunction('current_path', fn() => current_path()));
            // Auth helpers
            $twig->addFunction(new TwigFunction('is_admin', fn() => \App\Util\Auth::isAdmin()));
            $twig->addFunction(new TwigFunction('current_user', fn() => \App\Util\Auth::user()));
            $twig->addFunction(new TwigFunction('can', fn(string $entity, string $action) => \App\Util\Permission::can($entity, $action)));
            // View helpers wrappers
            $twig->addFunction(new TwigFunction('sort_link', fn(string $label, string $key, ?string $currentKey, string $currentDir, string $path, array $extraQuery = []) => \App\Util\View::sortLink($label, $key, $currentKey, $currentDir, $path, $extraQuery), ['is_safe' => ['html']]));
            $twig->addFunction(new TwigFunction('paginate', fn(int $total, int $page, int $perPage, string $path, array $extraQuery = []) => \App\Util\View::paginate($total, $page, $perPage, $path, $extraQuery), ['is_safe' => ['html']]));
            $twig->addFunction(new TwigFunction('nl2br_e', fn(?string $value) => \App\Util\View::nl2brE($value), ['is_safe' => ['html']]));
            return $twig;
        };

        // Stores per entity
        $this->registerStoreFactories();

        // Controllers
        $this->factories['homeController'] = function(self $c) {
            return new \App\Controller\HomeController();
        };
        $this->factories['contactsController'] = function(self $c) {
            return new \App\Controller\ContactsTemplateController(
                $c->get('contactsStore'),
                $c->get('timesStore'),
                $c->get('tasksStore'),
                $c->get('groupsStore'),
                $c->get('activitiesStore'),
                $c->get('auditService'),
                $c->get('commentsStore'),
                $c->get('followsStore'),
            );
        };
        $this->factories['contactsDedupeController'] = function(self $c) {
            return new \App\Controller\ContactsDedupeController(
                $c->get('contactsStore'),
                $c->get('timesStore'),
                $c->get('tasksStore'),
                $c->get('activitiesStore')
            );
        };
        $this->factories['timesController'] = function(self $c) {
            return new \App\Controller\TimesController(
                $c->get('timesStore'),
                $c->get('contactsStore'),
                $c->get('employeesStore')
            );
        };
        $this->factories['tasksRepository'] = function(self $c) {
            // Wrap the underlying store (JSON or DB) behind a repository boundary
            /** @var \App\StoreInterface $store */
            $store = $c->get('tasksStore');
            return new \App\Infrastructure\Repository\TasksRepository($store);
        };
        $this->factories['listService'] = function(self $c) {
            return new \App\Service\ListService();
        };
        $this->factories['tasksController'] = function(self $c) {
            return new \App\Controller\TasksController(
                $c->get('tasksRepository'),
                $c->get('contactsStore'),
                $c->get('employeesStore'),
                $c->get('projectsStore'),
                $c->get('timesStore'),
                $c->get('listService'),
                $c->get('commentsStore'),
                $c->get('followsStore'),
                $c->get('automationService'),
            );
        };
        $this->factories['projectsController'] = function(self $c) {
            return new \App\Controller\ProjectsController(
                $c->get('projectsStore'),
                $c->get('contactsStore'),
                $c->get('employeesStore'),
                $c->get('tasksStore'),
                $c->get('auditService'),
                $c->get('commentsStore'),
                $c->get('followsStore'),
            );
        };
        $this->factories['employeesController'] = function(self $c) {
            return new \App\Controller\EmployeesTemplateController(
                $c->get('employeesStore'),
                $c->get('usersStore')
            );
        };
        $this->factories['candidatesController'] = function(self $c) {
            return new \App\Controller\CandidatesTemplateController(
                $c->get('candidatesStore'),
                $c->get('request'),
                $c->get('url')
            );
        };
        $this->factories['paymentsController'] = function(self $c) {
            return new \App\Controller\PaymentsController(
                $c->get('paymentsStore')
            );
        };
        $this->factories['storageController'] = function(self $c) {
            return new \App\Controller\StorageController(
                $c->get('storageStore'),
                $c->get('storage_adjustmentsStore')
            );
        };
        $this->factories['usersController'] = function(self $c) {
            return new \App\Controller\UsersController(
                $c->get('usersStore')
            );
        };
        // Email controller
        $this->factories['emailController'] = function(self $c) {
            return new \App\Controller\EmailController(
                $c->get('emailService'),
                $c->get('contactsStore'),
                $c->get('activitiesStore')
            );
        };
        $this->factories['groupsController'] = function(self $c) {
            return new \App\Controller\GroupsTemplateController(
                $c->get('groupsStore')
            );
        };
        $this->factories['uploadController'] = function(self $c) {
            return new \App\Controller\UploadController();
        };
        $this->factories['filesController'] = function(self $c) {
            return new \App\Controller\FilesController();
        };
        $this->factories['passwordController'] = function(self $c) {
            return new \App\Controller\PasswordController();
        };
        // Calendar
        $this->factories['calendarController'] = function(self $c) {
            return new \App\Controller\CalendarController(
                $c->get('contactsStore'),
                $c->get('projectsStore'),
                $c->get('tasksStore')
            );
        };
        $this->factories['viewsController'] = function(self $c) {
            return new \App\Controller\ViewsController(
                $c->get('viewsStore')
            );
        };
        $this->factories['dashboardController'] = function(self $c) {
            return new \App\Controller\DashboardController(
                $c->get('tasksStore'),
                $c->get('contactsStore'),
                $c->get('storageStore')
            );
        };
        $this->factories['dealsController'] = function(self $c) {
            return new \App\Controller\DealsController(
                $c->get('dealsStore'),
                $c->get('contactsStore'),
                $c->get('auditService'),
                $c->get('commentsStore'),
                $c->get('followsStore'),
            );
        };
        // Search
        $this->factories['searchService'] = function(self $c) {
            return new \App\Service\SearchService(
                $c->get('config'),
                $c->get('contactsStore'),
                $c->get('tasksStore'),
                $c->get('dealsStore'),
                $c->get('projectsStore')
            );
        };
        $this->factories['searchController'] = function(self $c) {
            return new \App\Controller\SearchController(
                $c->get('searchService')
            );
        };
        // Reports
        $this->factories['reportService'] = function(self $c) {
            return new \App\Service\ReportService(
                $c->get('config'),
                $c->get('contactsStore'),
                $c->get('tasksStore'),
                $c->get('dealsStore'),
                $c->get('projectsStore'),
                $c->get('timesStore'),
                $c->get('paymentsStore')
            );
        };
        $this->factories['reportsController'] = function(self $c) {
            return new \App\Controller\ReportsController(
                $c->get('reportService'),
                $c->get('reportsStore')
            );
        };
        // Webhooks
        $this->factories['webhookService'] = function(self $c) {
            return new \App\Service\WebhookService(
                $c->get('config'),
                $c->get('logger')
            );
        };
        // Email
        $this->factories['emailService'] = function(self $c) {
            return new \App\Service\EmailService(
                $c->get('config')
            );
        };
        // Automations
        $this->factories['automationService'] = function(self $c) {
            return new \App\Service\AutomationService(
                $c->get('automationsStore'),
                $c->get('commentsStore'),
                $c->get('emailService'),
                $c->get('config'),
                $c->get('auditService'),
                $c->get('logger')
            );
        };
        // Comments
        $this->factories['commentsController'] = function(self $c) {
            return new \App\Controller\CommentsController(
                $c->get('commentsStore'),
                $c->get('usersStore'),
                $c->get('emailService'),
                $c->get('config'),
                $c->get('automationService'),
            );
        };
        // Follows
        $this->factories['followsController'] = function(self $c) {
            return new \App\Controller\FollowsController(
                $c->get('followsStore'),
                $c->get('commentsStore'),
                $c->get('usersStore'),
                $c->get('emailService'),
                $c->get('config')
            );
        };
        // Audit
        $this->factories['auditService'] = function(self $c) {
            return new \App\Service\AuditService(
                $c->get('auditStore')
            );
        };
        $this->factories['auditController'] = function(self $c) {
            return new \App\Controller\AuditController(
                $c->get('auditStore')
            );
        };
        // Health
        $this->factories['healthController'] = function(self $c) {
            return new \App\Controller\HealthController(
                $c->get('config')
            );
        };
        // Backups
        $this->factories['backupService'] = function(self $c) {
            return new \App\Service\BackupService($c->get('config'));
        };
        $this->factories['backupsController'] = function(self $c) {
            return new \App\Controller\BackupsController($c->get('backupService'));
        };
        // API Controller
        $this->factories['apiController'] = function(self $c) {
            return new \App\Controller\ApiController(
                $c->get('config'),
                $c->get('webhookService'),
                $c->get('auditService'),
                $c->get('contactsStore'),
                $c->get('tasksStore'),
                $c->get('dealsStore'),
                $c->get('projectsStore'),
                $c->get('timesStore'),
                $c->get('paymentsStore'),
                $c->get('employeesStore'),
                $c->get('candidatesStore'),
                $c->get('storageStore')
            );
        };
        // Bulk operations
        $this->factories['bulkController'] = function(self $c) {
            return new \App\Controller\BulkController(
                $c->get('contactsStore'),
                $c->get('dealsStore'),
                $c->get('projectsStore'),
                $c->get('auditService'),
            );
        };
    }

    public function set(string $id, callable $factory): void
    {
        $this->factories[$id] = $factory;
        unset($this->instances[$id]);
    }

    /**
     * @template T
     * @param string $id
     * @return mixed
     */
    public function get(string $id)
    {
        if (array_key_exists($id, $this->instances)) {
            return $this->instances[$id];
        }
        if (!isset($this->factories[$id])) {
            throw new InvalidArgumentException("Service '$id' not found");
        }
        $this->instances[$id] = ($this->factories[$id])($this);
        return $this->instances[$id];
    }

    private function registerStoreFactories(): void
    {
        $makeStore = function(self $c, string $name) {
            /** @var Config $cfg */
            $cfg = $c->get('config');
            if ($cfg->useDb()) {
                return new DbStore($name);
            }
            $path = $cfg->jsonPath($name . '.json');
            return new JsonStore($path);
        };
        foreach (['contacts','times','tasks','employees','candidates','payments','storage','storage_adjustments','users','projects','groups','views','deals','activities','reports','audit','comments','follows','automations'] as $entity) {
            $this->factories[$entity . 'Store'] = function(self $c) use ($makeStore, $entity) {
                $store = $makeStore($c, $entity);
                if ($entity === 'users') {
                    $this->ensureDefaultAdmin($store);
                }
                return $store;
            };
        }
    }

    private function ensureDefaultAdmin($store): void
    {
        try {
            $users = $store->all();
            $hasAdmin = false;
            foreach ($users as $u) {
                if (($u['login'] ?? '') === 'admin') { $hasAdmin = true; break; }
            }
            if ($hasAdmin) return;
            // Build full-rights permissions matrix (own and others all 1)
            $entities = ['contacts','times','tasks','employees','candidates','payments','storage','projects','deals','users','groups'];
            $permissions = [];
            foreach ($entities as $e) {
                $permissions[$e] = [
                    'own' => ['view'=>1,'create'=>1,'edit'=>1,'delete'=>1],
                    'others' => ['view'=>1,'create'=>1,'edit'=>1,'delete'=>1],
                ];
            }
            // Utilities / special sections granted as view/create appropriately
            $permissions['reports'] = [ 'own'=>['view'=>1,'create'=>1,'edit'=>1,'delete'=>1], 'others'=>['view'=>1,'create'=>1,'edit'=>1,'delete'=>1] ];
            $permissions['calendar'] = [ 'own'=>['view'=>1,'create'=>1,'edit'=>1,'delete'=>1], 'others'=>['view'=>1,'create'=>1,'edit'=>1,'delete'=>1] ];
            $permissions['search'] = [ 'own'=>['view'=>1,'create'=>1,'edit'=>1,'delete'=>1], 'others'=>['view'=>1,'create'=>1,'edit'=>1,'delete'=>1] ];
            $permissions['import'] = [ 'own'=>['view'=>1,'create'=>1,'edit'=>1,'delete'=>1], 'others'=>['view'=>1,'create'=>1,'edit'=>1,'delete'=>1] ];
            $permissions['export'] = [ 'own'=>['view'=>1,'create'=>1,'edit'=>1,'delete'=>1], 'others'=>['view'=>1,'create'=>1,'edit'=>1,'delete'=>1] ];
            $permissions['files'] = [ 'own'=>['view'=>1,'create'=>1,'edit'=>1,'delete'=>1], 'others'=>['view'=>1,'create'=>1,'edit'=>1,'delete'=>1] ];
            $permissions['api'] = [ 'own'=>['view'=>1,'create'=>1,'edit'=>1,'delete'=>1], 'others'=>['view'=>1,'create'=>1,'edit'=>1,'delete'=>1] ];
            $permissions['audit'] = [ 'own'=>['view'=>1,'create'=>1,'edit'=>1,'delete'=>1], 'others'=>['view'=>1,'create'=>1,'edit'=>1,'delete'=>1] ];
            $store->add([
                'login' => 'admin',
                'fullname' => 'Administrator',
                'email' => '',
                'role' => 'admin',
                'permissions' => $permissions,
                'must_change_password' => 1,
                'password_hash' => password_hash('admin', PASSWORD_DEFAULT),
            ]);
        } catch (\Throwable $e) {
            // ignore seeding errors
        }
    }
}
