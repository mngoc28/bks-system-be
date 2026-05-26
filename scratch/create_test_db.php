<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    // We use the default connection config but change the DB temporarily to run create database.
    // Or we connect directly via PDO.
    $config = config('database.connections.mysql');
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']}",
        $config['username'],
        $config['password']
    );
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `bks-db-test` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    echo "Database `bks-db-test` created or already exists.\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
