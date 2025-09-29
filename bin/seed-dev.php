<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Container;
use App\Config;

$projectRoot = dirname(__DIR__);
// Load .env
if (class_exists(\Dotenv\Dotenv::class)) {
    $dotenv = \Dotenv\Dotenv::createImmutable($projectRoot);
    $dotenv->safeLoad();
}

$container = new Container();
/** @var Config $config */
$config = $container->get('config');

if (!$config->isDev()) {
    fwrite(STDERR, "This seeder can only be executed in APP_ENV=dev.\n");
    exit(1);
}

$now = (new DateTimeImmutable())->format(DATE_ATOM);
$dataDir = $config->getDataDir();
@mkdir($dataDir, 0777, true);

function writeJsonIfEmpty(string $file, array $data): void {
    if (!file_exists($file)) {
        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        echo "Created $file\n";
        return;
    }
    $current = json_decode((string)file_get_contents($file), true);
    if (!is_array($current) || count($current) === 0) {
        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        echo "Seeded $file\n";
    } else {
        echo "Skipped $file (already has data)\n";
    }
}

$contacts = [
    [ 'id' => 1, 'name' => 'Alice Example', 'email' => 'alice@example.com', 'phone' => '+1 555 0100', 'company' => 'Example Co', 'notes' => 'Priority client', 'created_at' => $now ],
    [ 'id' => 2, 'name' => 'Bob Sample',   'email' => 'bob@example.com',   'phone' => '+1 555 0101', 'company' => 'Sample LLC', 'notes' => '', 'created_at' => $now ],
];

$tasks = [
    [ 'id' => 1, 'contact_id' => 1, 'title' => 'Onboard Alice', 'status' => 'open', 'created_at' => $now ],
    [ 'id' => 2, 'contact_id' => 2, 'title' => 'Prepare proposal', 'status' => 'open', 'created_at' => $now ],
];

$times = [
    [ 'id' => 1, 'contact_id' => 1, 'date' => substr($now, 0, 10), 'hours' => 2.5, 'notes' => 'Kickoff call' ],
];

$employees = [
    [ 'id' => 1, 'name' => 'Eve Employee', 'email' => 'eve@example.com', 'role' => 'admin', 'created_at' => $now ],
];

$candidates = [
    [ 'id' => 1, 'name' => 'Charlie Candidate', 'email' => 'charlie@example.com', 'status' => 'applied', 'created_at' => $now ],
];

$payments = [
    [ 'id' => 1, 'contact_id' => 1, 'date' => substr($now, 0, 10), 'amount' => 500.00, 'category' => 'invoice', 'notes' => 'Initial deposit' ],
];

$storage = [
    [ 'id' => 1, 'sku' => 'SKU-001', 'name' => 'Widget', 'stock' => 10, 'created_at' => $now ],
];

$storageAdjustments = [
    [ 'id' => 1, 'storage_id' => 1, 'delta' => 10, 'reason' => 'Initial stock', 'created_at' => $now ],
];

writeJsonIfEmpty($config->jsonPath('contacts.json'), $contacts);
writeJsonIfEmpty($config->jsonPath('tasks.json'), $tasks);
writeJsonIfEmpty($config->jsonPath('times.json'), $times);
writeJsonIfEmpty($config->jsonPath('employees.json'), $employees);
writeJsonIfEmpty($config->jsonPath('candidates.json'), $candidates);
writeJsonIfEmpty($config->jsonPath('payments.json'), $payments);
writeJsonIfEmpty($config->jsonPath('storage.json'), $storage);
writeJsonIfEmpty($config->jsonPath('storage_adjustments.json'), $storageAdjustments);

echo "Done.\n";
