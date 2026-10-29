<?php
// Load private database credentials from outside public_html
require_once dirname(__DIR__) . "/config.php";

header('Content-Type: text/plain; charset=utf-8');

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    /*
     * Accept both formats:
     *
     * 1) Regular GET:
     *    ?nodeId=node_3&nodeTemp=34&humidity=50&timeReceived=...
     *
     * 2) The assignment's Base64 format, where the entire query string is
     *    Base64 encoded:
     *    ?bm9kZUlkPW5vZGVfMyZub2RlVGVtcD0zNCZodW1pZGl0eT01MA==
     */
    if (isset($_GET['nodeId']) || isset($_GET['node_name'])) {
        // Regular GET request.
        $parameters = $_GET;
    } else {
        // The Base64 value is the raw query string after the question mark.
        $encodedMessage = $_SERVER['QUERY_STRING'] ?? '';
        $decodedMessage = base64_decode($encodedMessage, true);

        if ($encodedMessage === '' || $decodedMessage === false) {
            http_response_code(400);
            exit("Error: invalid Base64 message.\n");
        }

        // Convert decoded text such as nodeId=node_3&nodeTemp=34 into an array.
        parse_str($decodedMessage, $parameters);
    }

    // Accept the assignment's names and the alternate names used by CURL.
    $nodeName = $parameters['nodeId'] ?? $parameters['node_name'] ?? null;
    $timeReceived = $parameters['timeReceived'] ?? $parameters['time_received'] ?? null;
    $temperature = $parameters['nodeTemp'] ?? $parameters['temperature'] ?? null;
    $humidity = $parameters['humidity'] ?? null;

    if ($nodeName === null || $temperature === null || $humidity === null) {
        http_response_code(400);
        exit("Error: node, temperature, and humidity are required.\n");
    }

    if (!preg_match('/^node_[1-5]$/', $nodeName)) {
        http_response_code(400);
        exit("Error: node must be node_1 through node_5.\n");
    }

    if (!is_numeric($temperature) || $temperature < -10 || $temperature > 100) {
        http_response_code(400);
        exit("Error: temperature must be between -10 and 100 degrees Celsius.\n");
    }

    if (!is_numeric($humidity) || $humidity < 0 || $humidity > 100) {
        http_response_code(400);
        exit("Error: humidity must be between 0 and 100 percent.\n");
    }

    // Confirm that the node is registered before accepting data.
    $registered = $pdo->prepare(
        "SELECT COUNT(*) FROM sensor_register WHERE node_name = :node_name"
    );
    $registered->execute(['node_name' => $nodeName]);

    if ((int)$registered->fetchColumn() === 0) {
        http_response_code(400);
        exit("Error: this node is not registered.\n");
    }

    if ($timeReceived !== null && $timeReceived !== '') {
        $date = DateTime::createFromFormat('Y-m-d H:i:s', $timeReceived);
        $validDate = $date && $date->format('Y-m-d H:i:s') === $timeReceived;

        if (!$validDate) {
            http_response_code(400);
            exit("Error: time must use YYYY-MM-DD HH:MM:SS format.\n");
        }

        // Reject an exact duplicate node/time combination.
        $duplicate = $pdo->prepare(
            "SELECT COUNT(*) FROM sensor_data
             WHERE node_name = :node_name AND time_received = :time_received"
        );
        $duplicate->execute([
            'node_name' => $nodeName,
            'time_received' => $timeReceived
        ]);

        if ((int)$duplicate->fetchColumn() > 0) {
            http_response_code(409);
            exit("Error: this node already has data at that exact time.\n");
        }

        $insert = $pdo->prepare(
            "INSERT INTO sensor_data
             (node_name, time_received, temperature, humidity)
             VALUES (:node_name, :time_received, :temperature, :humidity)"
        );
        $insert->execute([
            'node_name' => $nodeName,
            'time_received' => $timeReceived,
            'temperature' => $temperature,
            'humidity' => $humidity
        ]);
    } else {
        // If no time is supplied, use the database server's current time.
        $insert = $pdo->prepare(
            "INSERT INTO sensor_data
             (node_name, time_received, temperature, humidity)
             VALUES (:node_name, NOW(), :temperature, :humidity)"
        );
        $insert->execute([
            'node_name' => $nodeName,
            'temperature' => $temperature,
            'humidity' => $humidity
        ]);
    }

    echo "Data inserted successfully.\n";
    echo "Node: $nodeName\n";
    echo "Temperature: $temperature\n";
    echo "Humidity: $humidity\n";

} catch (PDOException $e) {
    http_response_code(500);
    echo "Database error: " . $e->getMessage();
}
?>
