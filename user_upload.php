<?php

require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use App\Repositories\UserRepository;

// 1. Load environment variables
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

// 2. Parse CLI arguments
$options = getopt("", ["create-table", "help"]);

// 3. Handle --help
if (isset($options['help'])) {
    echo "Usage: php user_upload.php [options]\n";
    echo "Options:\n";
    echo "  --file <filename>    CSV file to process\n";
    echo "  --dry-run            Parse and validate without importing\n";
    echo "  --create-table       Create/rebuild the users table\n";
    echo "  --help               Display available options\n";
    exit(0);
}

// 4. Handle --create-table
if (isset($options['create-table'])) {
    echo "Connecting to PostgreSQL database...\n";
    
    $repository = new UserRepository();
    
    echo "Rebuilding 'users' table...\n";
    $repository->createTable();
    
    echo "Success: Table 'users' created/rebuilt successfully.\n";
    exit(0);
}

echo "Run 'php user_upload.php --help' for usage instructions.\n";