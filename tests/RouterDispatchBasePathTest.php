<?php
declare(strict_types=1);

use App\Router;

require __DIR__ . '/../vendor/autoload.php';

final class RouterDispatchBasePathTest
{
    private int $passed = 0;
    private int $failed = 0;

    private function assertTrue(bool $condition, string $message): void
    {
        if ($condition) {
            $this->passed++;
        } else {
            $this->failed++;
            fwrite(STDERR, "Assertion failed: {$message}\n");
        }
    }

    private function runCaseBasePathStripped(): void
    {
        $router = new Router();
        $hit = false;
        $router->get('/hello', function() use (&$hit): void { $hit = true; });

        // Simulate base path /app/public and request /app/public/hello
        $_SERVER['SCRIPT_NAME'] = '/app/public/index.php';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/app/public/hello?x=1';

        $router->dispatch();
        $this->assertTrue($hit === true, 'Handler should be hit when base path is stripped.');
    }

    private function runCaseNoBasePath(): void
    {
        $router = new Router();
        $hit = false;
        $router->get('/hello', function() use (&$hit): void { $hit = true; });

        // SCRIPT_NAME at root should not set a non-empty base path
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/hello';

        $router->dispatch();
        $this->assertTrue($hit === true, 'Handler should be hit when base path is empty/root.');
    }

    private function runCaseDoesNotStartWithBasePath(): void
    {
        $router = new Router();
        $notFound = false;
        $router->get('/hello', function(): void { /* not used */ });
        $router->setNotFoundHandler(function(string $path, string $method) use (&$notFound): void {
            $notFound = ($path === '/other/hello' && $method === 'GET');
        });

        // Base path /app/public but URI does not start with it -> no stripping, so /hello won't match
        $_SERVER['SCRIPT_NAME'] = '/app/public/index.php';
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/other/hello';

        $router->dispatch();
        $this->assertTrue($notFound === true, 'Should 404 when URI does not start with base path.');
    }

    public function run(): int
    {
        $this->runCaseBasePathStripped();
        $this->runCaseNoBasePath();
        $this->runCaseDoesNotStartWithBasePath();

        $total = $this->passed + $this->failed;
        echo sprintf("RouterDispatchBasePathTest: %d passed, %d failed (of %d)\n", $this->passed, $this->failed, $total);
        return $this->failed === 0 ? 0 : 1;
    }
}

$test = new RouterDispatchBasePathTest();
exit($test->run());
