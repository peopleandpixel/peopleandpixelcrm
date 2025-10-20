<?php

declare(strict_types=1);

namespace App;

use App\Util\ErrorHandler;
use App\Util\Flash;
use InvalidArgumentException;
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
            $projectRoot = $cfg->getProjectRoot();
            $logDir = rtrim($projectRoot, '/') . '/var/log';
            if (!is_dir($logDir)) {
                @mkdir($logDir, 0777, true);
            }
            $logFile = $logDir . '/app.log';
            $level = $cfg->getLogLevel();
            $logger = new Logger('app');
            $logger->pushHandler(new StreamHandler($logFile, $level));
            return $logger;
        };
        $this->factories['errorHandler'] = function(self $c) {
            /** @var Config $cfg */
            $cfg = $c->get('config');
            /** @var LoggerInterface $logger */
            $logger = $c->get('logger');
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
            $templatesPath = rtrim($cfg->getProjectRoot(), '/') . '/templates';
            $loader = new \Twig\Loader\FilesystemLoader($templatesPath);
            $options = [];
            $cacheDir = rtrim($cfg->getProjectRoot(), '/') . '/var/cache/twig';
            $isDebug = $cfg->isDebug();
            if (!$isDebug) {
                if (!is_dir($cacheDir)) {@mkdir($cacheDir, 0777, true);}            
                $options['cache'] = $cacheDir;
            } else {
                $options['debug'] = true;
                $options['auto_reload'] = true;
            }
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
            $twig->addFunction(new TwigFunction('format_date', fn(\DateTimeInterface $d, int $dateType = \IntlDateFormatter::MEDIUM, int $timeType = \IntlDateFormatter::NONE) => I18n::formatDate($d, $dateType, $timeType)));
            $twig->addFunction(new TwigFunction('format_datetime', fn(\DateTimeInterface $d, int $dateType = \IntlDateFormatter::MEDIUM, int $timeType = \IntlDateFormatter::SHORT) => I18n::formatDate($d, $dateType, $timeType)));
            $twig->addFunction(new TwigFunction('format_number', fn($n, int $style = \NumberFormatter::DECIMAL, int $precision = 2) => I18n::formatNumber((float)$n, $style, $precision)));
            $twig->addFunction(new TwigFunction('csrf_field', fn() => (function(){ return \App\Util\Csrf::fieldName() ? '<input type="hidden" name="' . \App\Util\Csrf::fieldName() . '" value="' . htmlspecialchars(\App\Util\Csrf::getToken(), ENT_QUOTES, 'UTF-8') . '">': ''; })(), ['is_safe' => ['html']]) );
            $twig->addFunction(new TwigFunction('url', fn(string $path = '/', array $params = []) => url($path, $params)));
            $twig->addFunction(new TwigFunction('canonical_url', fn(?string $path = null, array $params = []) => canonical_url($path, $params)));
            $twig->addFunction(new TwigFunction('active_class', fn(string $path) => active_class($path)));
            $twig->addFunction(new TwigFunction('current_path', fn() => current_path()));
            // Auth helpers
            $twig->addFunction(new TwigFunction('is_admin', fn() => \App\Util\Auth::isAdmin()));
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
                $c->get('tasksStore')
            );
        };
        $this->factories['timesController'] = function(self $c) {
            return new \App\Controller\TimesController(
                $c->get('timesStore'),
                $c->get('contactsStore'),
                $c->get('employeesStore')
            );
        };
        $this->factories['tasksController'] = function(self $c) {
            return new \App\Controller\TasksController(
                $c->get('tasksStore'),
                $c->get('contactsStore'),
                $c->get('employeesStore'),
                $c->get('projectsStore'),
                $c->get('timesStore')
            );
        };
        $this->factories['projectsController'] = function(self $c) {
            return new \App\Controller\ProjectsController(
                $c->get('projectsStore'),
                $c->get('contactsStore'),
                $c->get('employeesStore'),
                $c->get('tasksStore')
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
        $this->factories['uploadController'] = function(self $c) {
            return new \App\Controller\UploadController();
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
        foreach (['contacts','times','tasks','employees','candidates','payments','storage','storage_adjustments','users','projects'] as $entity) {
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
            $entities = ['contacts','times','tasks','employees','candidates','payments','storage'];
            $permissions = [];
            foreach ($entities as $e) {
                $permissions[$e] = [
                    'own' => ['view'=>1,'create'=>1,'edit'=>1,'delete'=>1],
                    'others' => ['view'=>1,'create'=>1,'edit'=>1,'delete'=>1],
                ];
            }
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
