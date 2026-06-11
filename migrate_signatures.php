<?php
/**
 * Migration: Add signature_path column to record_history table
 * Run this once to add the column if it doesn't exist
 */

require_once __DIR__ . '/config.php';
$db = getDb();

try {
    // Add signature_path column if it doesn't exist
    $db->exec("ALTER TABLE record_history ADD COLUMN signature_path VARCHAR(255) NULL");
    echo "✓ Migration successful: signature_path column added to record_history table\n";
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "ℹ Column already exists: signature_path\n";
    } else {
        echo "✗ Migration failed: " . $e->getMessage() . "\n";
    }
}
?>
