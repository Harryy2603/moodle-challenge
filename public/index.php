<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Services\ImportService;
use Dotenv\Dotenv;

if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
}

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'Valid CSV file upload required']);
    exit;
}

$file = $_FILES['csv_file']['tmp_name'];
$isDryRun = isset($_POST['dry_run']) && $_POST['dry_run'] === 'true';

$service = new ImportService();
$result = $service->process($file, $isDryRun);

if (isset($result['error'])) {
    http_response_code(500);
}

echo json_encode($result);