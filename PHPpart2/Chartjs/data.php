<?php
// Return sensor data as JSON for the Chart.js graph.
require_once dirname(__DIR__, 2) . "/config.php";

header('Content-Type: application/json; charset=utf-8');

$nodeName = $_GET['node_name'] ?? 'node_1';

if (!preg_match('/^node_[1-5]$/', $nodeName)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid node name.']);
    exit;
}

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $query = $pdo->prepare(
        "SELECT time_received, temperature
         FROM sensor_data
         WHERE node_name = :node_name
         ORDER BY time_received ASC"
    );
    $query->execute(['node_name' => $nodeName]);

    echo json_encode($query->fetchAll(PDO::FETCH_ASSOC));
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed.']);
}
?>
