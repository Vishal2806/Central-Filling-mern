<?php
session_start();

define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '5432');
define('DB_NAME', getenv('DB_NAME') ?: 'efile_db');
define('DB_MAINTENANCE_NAME', getenv('DB_MAINTENANCE_NAME') ?: 'postgres');
define('DB_USER', getenv('DB_USER') ?: 'postgres');
define('DB_PASS', getenv('DB_PASS') ?: 'postgres');

define('APP_NAME', 'Court E-Filing Registry');

define('APP_URL', getenv('APP_URL') ?: 'http://localhost');

function getDb($databaseName = null)
{
    static $connections = [];

    $databaseName = $databaseName ?: DB_NAME;

    if (!isset($connections[$databaseName])) {
        if (strpos(DB_HOST, '/') === 0) {
            $dsn = sprintf('pgsql:host=%s;dbname=%s', DB_HOST, $databaseName);
        } else {
            $dsn = sprintf('pgsql:host=%s;port=%s;dbname=%s', DB_HOST, DB_PORT, $databaseName);
        }

        $connections[$databaseName] = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }

    return $connections[$databaseName];
}
