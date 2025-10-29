<?php

namespace App\Util;

use App\Config;
use App\Domain\Exception\BadRequestException;
use App\Domain\Exception\ConflictException;
use App\Domain\Exception\DomainException;
use App\Domain\Exception\ForbiddenException;
use App\Domain\Exception\NotFoundException;
use App\Domain\Exception\UnauthorizedException;
use App\Domain\Exception\ValidationException;
use Psr\Log\LoggerInterface;
use App\Service\MetricsService;

class ErrorHandler
{
    private LoggerInterface $logger;
    private string $projectRoot;
    private bool $debug;

    public function __construct(Config $config, LoggerInterface $logger, private readonly ?MetricsService $metrics = null)
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
        // Map exception types to status codes
        [$status, $level] = $this->mapStatusAndLevel($e);
        http_response_code($status);
        $context = [
            'message' => $e->getMessage(),
            'exception' => $e,
            'code' => $status,
            'type' => $e::class,
        ];
        // Log using channel-provided logger (http)
        if ($level === 'error') {
            $this->logger->error('HTTP {code} {type}: {message}', $context);
        } elseif ($level === 'warning') {
            $this->logger->warning('HTTP {code} {type}: {message}', $context);
        } else {
            $this->logger->info('HTTP {code} {type}: {message}', $context);
        }
        // Record metrics (fingerprinted, no PII)
        if ($this->metrics && $this->metrics->isEnabled()) {
            $msg = (string)($e->getMessage() ?? '');
            $fp = substr(hash('sha256', $e::class . '|' . $msg), 0, 16);
            $path = isset($_SERVER['REQUEST_URI']) ? (string)$_SERVER['REQUEST_URI'] : '';
            $this->metrics->recordError([
                'level' => $level,
                'code' => $status,
                'fingerprint' => $fp,
                'route' => null,
                'path' => $path,
            ]);
        }
        $this->renderError($status, $e);
    }

    public function handleShutdown(): void
    {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
            $e = new \ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']);
            $this->handleException($e);
        }
    }

    private function renderError(int $status, ?\Throwable $e = null): void
    {
        $templatesDir = $this->projectRoot . '/templates';
        $template = in_array($status, [400,401,403,404,422,500], true) ? (string)$status : '500';
        $twigPath = $templatesDir . '/errors/' . $template . '.twig';
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
                echo $twig->render('errors/' . $template . '.twig', [
                    'errorDetails' => $details,
                ]);
                return;
            } catch (\Throwable $te) {
                // fall through to plain text below
            }
        }
        // Fallback plain text
        header('Content-Type: text/plain; charset=UTF-8');
        $titles = [
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            422 => 'Unprocessable Entity',
            500 => 'Internal Server Error',
        ];
        $title = $titles[$status] ?? 'Error';
        echo $status . ' – ' . $title;
        if ($details) {
            echo "\n\n" . $details['class'] . ': ' . $details['message'] . "\n" . $details['file'] . ':' . $details['line'] . "\n" . $details['trace'];
        }
    }

    private function mapStatusAndLevel(\Throwable $e): array
    {
        // Returns [status, level]
        return match (true) {
            $e instanceof ValidationException => [422, 'warning'],
            $e instanceof NotFoundException => [404, 'info'],
            $e instanceof UnauthorizedException => [401, 'info'],
            $e instanceof ForbiddenException => [403, 'warning'],
            $e instanceof ConflictException => [409, 'warning'],
            $e instanceof BadRequestException => [400, 'info'],
            $e instanceof DomainException => [400, 'warning'],
            default => [500, 'error'],
        };
    }
}
