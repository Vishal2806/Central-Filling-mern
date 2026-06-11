<?php
session_start();

define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '5432');
define('DB_NAME', getenv('DB_NAME') ?: 'efile_db');
define('DB_MAINTENANCE_NAME', getenv('DB_MAINTENANCE_NAME') ?: 'postgres');
define('DB_USER', getenv('DB_USER') ?: 'postgres');
define('DB_PASS', getenv('DB_PASS') ?: 'postgres');

define('ADVOCATE_DB_HOST', getenv('ADVOCATE_DB_HOST') ?: '127.0.0.1');
define('ADVOCATE_DB_PORT', getenv('ADVOCATE_DB_PORT') ?: '5432');
define('ADVOCATE_DB_NAME', getenv('ADVOCATE_DB_NAME') ?: 'cghccisdb');
define('ADVOCATE_DB_USER', getenv('ADVOCATE_DB_USER') ?: 'postgres');
define('ADVOCATE_DB_PASS', getenv('ADVOCATE_DB_PASS') ?: 'postgres');

define('APP_NAME', 'Court E-Filing Registry');

define('APP_URL', getenv('APP_URL') ?: 'http://localhost');

function getDb($connectionName = null)
{
    static $connections = [];

    $connectionName = $connectionName ?: 'default';

    $configs = [
        'default' => [
            'host' => DB_HOST,
            'port' => DB_PORT,
            'name' => DB_NAME,
            'user' => DB_USER,
            'pass' => DB_PASS,
        ],
        'advocates' => [
            'host' => ADVOCATE_DB_HOST,
            'port' => ADVOCATE_DB_PORT,
            'name' => ADVOCATE_DB_NAME,
            'user' => ADVOCATE_DB_USER,
            'pass' => ADVOCATE_DB_PASS,
        ],
    ];
    $configs[ADVOCATE_DB_NAME] = $configs['advocates'];

    if (isset($configs[$connectionName])) {
        $config = $configs[$connectionName];
    } else {
        $config = $configs['default'];
        $config['name'] = $connectionName;
    }

    $connectionKey = implode('|', [$config['host'], $config['port'], $config['name'], $config['user']]);

    if (!isset($connections[$connectionKey])) {
        if (strpos($config['host'], '/') === 0) {
            $dsn = sprintf('pgsql:host=%s;dbname=%s', $config['host'], $config['name']);
        } else {
            $dsn = sprintf('pgsql:host=%s;port=%s;dbname=%s', $config['host'], $config['port'], $config['name']);
        }

        $connections[$connectionKey] = new PDO($dsn, $config['user'], $config['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT => 5,
        ]);
    }

    return $connections[$connectionKey];
}
