<?php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

try {
    $advDb   = getDb('advocates');
    $advStmt = $advDb->query('SELECT adv_name, adv_reg, adv_mobile FROM advocate_t ORDER BY adv_name');
    $advocates = $advStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'count' => count($advocates),
        'data' => array_slice($advocates, 0, 5)
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?> 
