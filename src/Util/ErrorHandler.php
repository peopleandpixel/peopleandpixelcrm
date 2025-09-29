<?php

namespace App\Util;

use App\Config;
use Psr\Log\LoggerInterface;

class ErrorHandler
{
    private LoggerInterface $logger;
    private string $projectRoot;
    private bool $debug;

    public function __construct(Config $config, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->projectRoot = $config->getProjectRoot();
        $this->debug = $config->isDebug();
    }

    public function register(): void
    {
        set_error_handler([$this, 'handleError']);
        set_exception_handler([$this, 'handleException']);
        register_shutdown_function([$this, 'handleShutdown']);
    }

    public function handleError(int $severity, string $message, string $file = '', int $line = 0): bool
    {
        // Convert all errors to exceptions to be handled uniformly
        if (!(error_reporting() & $severity)) {
            return false; // silenced with @
        }
        $this->handleException(new \ErrorException($message, 0, $severity, $file, $line));
        return true;
    }

    public function handleException(\Throwable $e): void
    {
        http_response_code(500);
        $this->logger->error('Unhandled exception: {message}', [
            'message' => $e->getMessage(),
            'exception' => $e,
        ]);
        $this->render500($e);
    }

    public function handleShutdown(): void
    {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
            $e = new \ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']);
            $this->handleException($e);
        }
    }

    private function render500(?\Throwable $e): void
    {
        $templatesDir = $this->projectRoot . '/templates';
        $twigPath = $templatesDir . '/errors/500.twig';
        $details = null;
        if ($this->debug && $e) {
            $details = [
                'class' => $e::class,
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ];
        }
        // Try Twig first if available and template exists
        if (class_exists(\Twig\Environment::class) && is_file($twigPath)) {
            try {
                $loader = new \Twig\Loader\FilesystemLoader($templatesDir);
                // Configure Twig with cache in non-debug environments to speed up error page rendering
                $options = [];
                if (!$this->debug) {
                    $cacheDir = rtrim($this->projectRoot, '/') . '/var/cache/twig';
                    if (!is_dir($cacheDir)) { @mkdir($cacheDir, 0777, true); }
                    $options['cache'] = $cacheDir;
                } else {
                    $options['debug'] = true;
                    $options['auto_reload'] = true;
                }
                $twig = new \Twig\Environment($loader, $options);
                echo $twig->render('errors/500.twig', [
                    'errorDetails' => $details,
                ]);
                return;
            } catch (\Throwable $te) {
                // fall through to plain text below
            }
        }
        // Fallback plain text
        header('Content-Type: text/plain; charset=UTF-8');
        echo 'An internal error occurred.';
        if ($details) {
            echo "\n\n" . $details['class'] . ': ' . $details['message'] . "\n" . $details['file'] . ':' . $details['line'] . "\n" . $details['trace'];
        }
    }

    private function isDebug(): bool
    {
        $env = getenv('APP_ENV') ?: ($_ENV['APP_ENV'] ?? 'prod');
        $debug = getenv('APP_DEBUG') ?: ($_ENV['APP_DEBUG'] ?? '');
        $isDebug = strtolower((string)$debug);
        return $env === 'dev' || $isDebug === '1' || $isDebug === 'true' || $isDebug === 'yes';
    }
}
