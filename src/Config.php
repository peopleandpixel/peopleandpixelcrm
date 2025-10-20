<?php

declare(strict_types=1);

namespace App;

use Monolog\Level;
use RuntimeException;

/**
 * Config service to centralize environment, paths, and locale.
 */
class Config
{
    private string $projectRoot;
    private string $dataDir;
    private string $lang;

    public function __construct(string $projectRoot)
    {
        $this->projectRoot = rtrim($projectRoot, '/');

        // Resolve data directory from env, default to <projectRoot>/data
        $envDataDir = $this->getEnv('DATA_DIR');
        $this->dataDir = $envDataDir ? rtrim($envDataDir, '/') : ($this->projectRoot . '/data');

        // Determine language from session or env DEFAULT_LANG or default to 'en'
        if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['lang'])) {
            $this->lang = (string)$_SESSION['lang'];
        } else {
            $this->lang = $this->getEnv('DEFAULT_LANG') ?: 'en';
        }

        // Fail fast if DB is explicitly enabled but DSN is missing
        if ($this->isDbExplicitlyEnabled() && !$this->getDbDsn()) {
            throw new RuntimeException("Configuration error: USE_DB is enabled but DB_DSN is not set in the environment. Set DB_DSN or disable USE_DB.");
        }
    }

    public function getProjectRoot(): string { return $this->projectRoot; }
    public function getDataDir(): string { return $this->dataDir; }

    public function getLang(): string { return $this->lang; }
    public function setLang(string $lang): void { $this->lang = $lang; }

    /**
     * App environment helpers
     */
    public function getAppEnv(): string
    {
        $env = strtolower($this->getEnv('APP_ENV') ?: 'prod');
        if (!in_array($env, ['dev','test','prod'], true)) {
            $env = 'prod';
        }
        return $env;
    }

    public function isDev(): bool { return $this->getAppEnv() === 'dev'; }
    public function isTest(): bool { return $this->getAppEnv() === 'test'; }
    public function isProd(): bool { return $this->getAppEnv() === 'prod'; }

    public function isDebug(): bool
    {
        $debug = strtolower(trim($this->getEnv('APP_DEBUG')));
        return $this->isDev() || in_array($debug, ['1','true','yes','on'], true);
    }

    /**
     * Determine logging level based on LOG_LEVEL override or env.
     */
    public function getLogLevel(): Level
    {
        $override = strtolower(trim($this->getEnv('LOG_LEVEL')));
        if ($override !== '') {
            // Map common strings to Level
            return match ($override) {
                'debug' => Level::Debug,
                'info' => Level::Info,
                'notice' => Level::Notice,
                'warning','warn' => Level::Warning,
                'error' => Level::Error,
                'critical','crit' => Level::Critical,
                'alert' => Level::Alert,
                'emergency','emerg' => Level::Emergency,
                default => Level::Info,
            };
        }
        // Defaults per environment
        if ($this->isDev()) return Level::Debug;
        if ($this->isTest()) return Level::Error;
        return Level::Warning; // prod
    }

    /**
     * Whether DB should be used instead of JSON, based on env.
     */
    public function useDb(): bool
    {
        // Use DB if DB_DSN is set or USE_DB is truthy
        $dsn = $this->getDbDsn();
        if ($dsn) {
            return true;
        }
        $useDb = $this->getEnv('USE_DB');
        return $this->isTruthy($useDb);
    }

    public function getDbDsn(): ?string
    {
        $v = $this->getEnv('DB_DSN');
        return $v !== '' ? $v : null;
    }

    public function getDbUser(): ?string
    {
        $v = $this->getEnv('DB_USER');
        return $v !== '' ? $v : null;
    }

    public function getDbPass(): ?string
    {
        $v = $this->getEnv('DB_PASS');
        return $v !== '' ? $v : null;
    }

    public function jsonPath(string $filename): string
    {
        return $this->getDataDir() . '/' . ltrim($filename, '/');
    }

    public function getEnv(string $key): string
    {
        // Prefer $_ENV loaded by phpdotenv; fallback to getenv
        if (isset($_ENV[$key])) {
            return (string)$_ENV[$key];
        }
        $v = getenv($key);
        return $v === false ? '' : (string)$v;
    }

    private function isTruthy(?string $val): bool
    {
        if ($val === null) return false;
        $v = strtolower(trim($val));
        return $v !== '' && $v !== '0' && $v !== 'false' && $v !== 'no' && $v !== 'off';
    }

    private function isDbExplicitlyEnabled(): bool
    {
        return $this->isTruthy($this->getEnv('USE_DB'));
    }
}
