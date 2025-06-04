<?php
// Direct database access for equipment lookup
// This bypasses Laravel's routing system

// Get serial from query string
$serial = $_GET['serial'] ?? '';

// Only proceed if there's a serial
if (empty($serial)) {
    echo json_encode([
        'error' => 'No serial number provided',
        'usage' => 'Add ?serial=XRS-2023-001 to the URL'
    ]);
    exit;
}

try {
    // Basic database connection (update credentials as needed)
    $host = 'localhost';
    $db   = 'laragon'; // Update to your actual database name
    $user = 'root';    // Update if different
    $pass = '';        // Update if needed
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    // Connect to database
    $pdo = new PDO($dsn, $user, $pass, $options);

    // Lookup equipment
    $stmt = $pdo->prepare("SELECT * FROM equipments WHERE serial_number = ?");
    $stmt->execute([$serial]);
    $equipment = $stmt->fetch();

    if ($equipment) {
        echo json_encode([
            'status' => 'success',
            'equipment' => $equipment,
            'url' => '/equipment/serial/' . urlencode($equipment['serial_number']),
            'message' => 'Found equipment in database'
        ]);
    } else {
        // Try to get all equipment to help diagnose
        $stmt = $pdo->query("SELECT id, name, serial_number FROM equipments LIMIT 10");
        $allEquipment = $stmt->fetchAll();

        echo json_encode([
            'status' => 'error',
            'message' => 'Equipment not found',
            'searched_for' => $serial,
            'all_equipment' => $allEquipment
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error',
        'error' => $e->getMessage()
    ]);
}
