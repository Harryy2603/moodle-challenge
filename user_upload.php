<?php

require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use App\Repositories\UserRepository;
use App\Services\ImportService;

if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

$options = getopt("", ["file:", "dry-run", "create-table", "help"]);

if (isset($options['help']) || $argc === 1) {
    echo "Usage: php user_upload.php [options]\n\n";
    echo "Options:\n";
    echo "  --file <filename>    CSV file to process\n";
    echo "  --dry-run            Parse and validate without importing (used with --file)\n";
    echo "  --create-table       Create/rebuild the users table\n";
    echo "  --help               Display available options\n";
    exit(0);
}

if (isset($options['create-table'])) {
    echo "Connecting to PostgreSQL database...\n";
    try {
        $repository = new UserRepository();
        echo "Rebuilding 'users' table...\n";
        $repository->createTable();
        echo "Success: Table 'users' created/rebuilt successfully.\n";
        exit(0);
    } catch (Exception $e) {
        echo "\n[FATAL ERROR] Failed to create table: " . $e->getMessage() . "\n";
        exit(1);
    }
}

if (isset($options['file'])) {
    $filePath = $options['file'];
    $isDryRun = isset($options['dry-run']);

    echo "Processing file: {$filePath}\n";
    if ($isDryRun) {
        echo "[DRY RUN MODE] Parsing and validating only. No database modifications.\n";
    }

    try {
        $importService = new ImportService();
        $result = $importService->process($filePath, $isDryRun);

        if (isset($result['error'])) {
            echo "\n[ERROR] " . $result['error'] . "\n";
            exit(1);
        }

        echo "\n--- IMPORT SUMMARY ---\n";
        echo "Total Processed: " . $result['total_processed'] . "\n";
        echo "Total Valid:     " . $result['total_valid'] . "\n";
        echo "Total Invalid:   " . $result['total_invalid'] . "\n";

        if ($result['total_invalid'] > 0) {
            echo "\n--- VALIDATION ERRORS ---\n";
            foreach ($result['records'] as $record) {
                if (!$record->isValid) {
                    $errors = implode(", ", $record->errors);
                    $displayEmail = $record->email ?: 'Unknown Email';
                    echo "- [{$displayEmail}] : {$errors}\n";
                }
            }
        }
        exit(0);
    } catch (Exception $e) {
        echo "\n[FATAL ERROR] An unexpected error occurred: " . $e->getMessage() . "\n";
        exit(1);
    }
}

echo "Invalid usage. Run 'php user_upload.php --help' for usage instructions.\n";
exit(1);