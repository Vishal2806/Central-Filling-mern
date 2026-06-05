<?php
require_once __DIR__ . '/config.php';

try {
    $maintenanceDb = getDb(DB_MAINTENANCE_NAME);
    $stmt = $maintenanceDb->prepare('SELECT 1 FROM pg_database WHERE datname = :database_name');
    $stmt->execute([':database_name' => DB_NAME]);

    if (!$stmt->fetchColumn()) {
        $databaseName = '"' . str_replace('"', '""', DB_NAME) . '"';
        $maintenanceDb->exec('CREATE DATABASE ' . $databaseName);
    }

    $db = getDb();

    $db->exec(
        'CREATE TABLE IF NOT EXISTS users (
            id SERIAL PRIMARY KEY,
            name TEXT NOT NULL,
            email TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            role TEXT DEFAULT \'user\'
        )'
    );

    $db->exec(
        'CREATE TABLE IF NOT EXISTS records (
            id SERIAL PRIMARY KEY,
            case_no TEXT,
            case_year TEXT,
            advocate_name TEXT,
            advocate_contact TEXT,
            current_status TEXT,
            total_returns INTEGER DEFAULT 0,
            latest_remark TEXT,
            filing_date DATE,
            filing_time TIME,
            case_nature TEXT,
            case_type_code TEXT,
            paperbook_sets INTEGER,
            created_at TIMESTAMP DEFAULT NOW()
        )'
    );

    $db->exec(
        'CREATE TABLE IF NOT EXISTS record_history (
            id SERIAL PRIMARY KEY,
            record_id INTEGER REFERENCES records(id) ON DELETE CASCADE,
            status TEXT,
            remark TEXT,
            updated_by TEXT,
            created_at TIMESTAMP DEFAULT NOW()
        )'
    );

    $email = 'admin@test.com';
    $stmt = $db->prepare('SELECT id FROM users WHERE email = :email');
    $stmt->execute([':email' => $email]);

    if (!$stmt->fetch()) {
        $passwordHash = password_hash('123456', PASSWORD_DEFAULT);
        $insert = $db->prepare('INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)');
        $insert->execute([
            ':name' => 'Admin',
            ':email' => $email,
            ':password' => $passwordHash,
            ':role' => 'admin',
        ]);
        $message = 'Database is ready. Demo admin user created: admin@test.com / 123456';
    } else {
        $message = 'Database is ready. Demo admin user already exists.';
    }

    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Setup Complete</title><style>body{font-family:Arial,sans-serif;background:#f4f7fb;color:#1f2937;padding:40px;} .card{background:#fff;border:1px solid #e2e8f0;border-radius:16px;padding:24px;max-width:600px;margin:auto;} a{color:#1f3a56;text-decoration:none;}</style></head><body><div class="card"><h1>Setup Complete</h1><p>' . htmlspecialchars($message) . '</p><p><a href="index.php">Go to registry</a></p></div></body></html>';
} catch (Exception $e) {
    echo '<p>Setup failed: ' . htmlspecialchars($e->getMessage()) . '</p>';
}
