<?php

declare(strict_types=1);

namespace App\Controller;

use App\Config;
use App\Util\EnvWriter;
use function render;
use function redirect;

class InstallerController
{
    public static function form(Config $config): void
    {
        // Pre-fill defaults
        $defaults = [
            'admin_user' => $config->getEnv('ADMIN_USER') ?: 'admin',
            'admin_pass' => $config->getEnv('ADMIN_PASS') ?: '',
            'storage'    => $config->useDb() ? 'db' : 'json',
            'db_dsn'     => $config->getDbDsn() ?: self::defaultSqliteDsn($config),
        ];
        render('install', ['defaults' => $defaults]);
    }

    public static function submit(Config $config): void
    {
        $adminUser = isset($_POST['admin_user']) ? trim((string)$_POST['admin_user']) : '';
        $adminPass = isset($_POST['admin_pass']) ? (string)$_POST['admin_pass'] : '';
        $storage   = isset($_POST['storage']) ? (string)$_POST['storage'] : 'json';
        $dbDsn     = isset($_POST['db_dsn']) ? trim((string)$_POST['db_dsn']) : '';

        $errors = [];
        if ($adminUser === '') { $errors[] = 'Admin username is required'; }
        if ($adminPass === '') { $errors[] = 'Admin password is required'; }
        if ($storage !== 'json' && $storage !== 'db') { $errors[] = 'Invalid storage option'; }
        if ($storage === 'db' && $dbDsn === '') { $dbDsn = self::defaultSqliteDsn($config); }

        if (!empty($errors)) {
            render('install', ['errors' => $errors, 'defaults' => [
                'admin_user' => $adminUser,
                'admin_pass' => $adminPass,
                'storage'    => $storage,
                'db_dsn'     => $dbDsn,
            ]]);
            return;
        }

        // Prepare env vars to write
        $vars = [
            'ADMIN_USER' => $adminUser,
            'ADMIN_PASS' => $adminPass,
            'USE_DB'     => $storage === 'db' ? '1' : '0',
            'INSTALLED'  => '1',
        ];
        if ($storage === 'db') {
            $vars['DB_DSN'] = $dbDsn;
        }

        $envFile = dirname(__DIR__, 2) . '/.env';
        EnvWriter::write($envFile, $vars);

        // Reload env for current request so user can login immediately
        foreach ($vars as $k => $v) {
            $_ENV[$k] = $v;
            putenv($k . '=' . $v);
        }

        redirect('/login');
    }

    private static function defaultSqliteDsn(Config $config): string
    {
        $dataDir = $config->getDataDir();
        if (!is_dir($dataDir)) @mkdir($dataDir, 0777, true);
        $dbFile = rtrim($dataDir, '/') . '/app.sqlite';
        return 'sqlite:' . $dbFile;
    }
}
