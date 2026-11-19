<?php

header("Content-Type: application/json");

require_once __DIR__ . "/../config.php";

$conn = new mysqli(
    $host,
    $username,
    $password,
    $dbname
);

if ($conn->connect_error) {
    http_response_code(500);

    echo json_encode([
        "error" => "Database connection failed"
    ]);

    exit();
}

$sql = "
    SELECT recorded_at, value
    FROM mqtt_potentiometer
    ORDER BY recorded_at ASC
    LIMIT 500
";

$result = $conn->query($sql);

$data = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            "time" => $row["recorded_at"],
            "value" => (float)$row["value"]
        ];
    }
}

$conn->close();

echo json_encode($data);
?>