<?php

$db = array();
define('BASEPATH', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR);
define('ENVIRONMENT', 'testing');
require __DIR__ . '/../application/config/database.php';
$settings = $db['default'];
$host = (string) ($settings['hostname'] ?? '127.0.0.1');
$port = (int) ($settings['port'] ?? 3306);
$connection = @new mysqli($host, $settings['username'], $settings['password'], $settings['database'], $port);
if ($connection->connect_errno) {
    fwrite(STDERR, "Schema check could not connect to the configured database.\n");
    exit(1);
}

$statement = $connection->prepare('SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?');
if (!$statement) {
    fwrite(STDERR, "Schema check could not prepare its metadata query.\n");
    exit(1);
}
$table = 'class_announcements';
$column = 'source_url';
$statement->bind_param('sss', $settings['database'], $table, $column);
$statement->execute();
$statement->bind_result($count);
$statement->fetch();
$statement->close();
$connection->close();

if ((int) $count !== 1) {
    fwrite(STDERR, "Expected announcement source column is missing.\n");
    exit(1);
}

echo "Database schema check passed: announcement source field exists.\n";
