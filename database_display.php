<?php
// Load private database credentials from outside the public website folder
require_once dirname(__DIR__) . "/config.php";

try {
    // Connect to MariaDB/MySQL using PDO
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    // Display database errors
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Retrieve sensor data sorted by node name, then time
    $sensorDataQuery = "
        SELECT node_name, time_received, temperature, humidity
        FROM sensor_data
        ORDER BY node_name ASC, time_received ASC
    ";

    $sensorData = $pdo->query($sensorDataQuery);

    // Retrieve registered sensor information sorted by node name
    $sensorRegisterQuery = "
        SELECT node_name, manufacturer, longitude, latitude
        FROM sensor_register
        ORDER BY node_name ASC
    ";

    $sensorRegister = $pdo->query($sensorRegisterQuery);

    // Calculate the averages for node_1
    $averageQuery = $pdo->prepare("
        SELECT AVG(temperature) AS average_temperature,
               AVG(humidity) AS average_humidity
        FROM sensor_data
        WHERE node_name = :node_name
    ");
    $averageQuery->execute(['node_name' => 'node_1']);
    $averages = $averageQuery->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sensor Database</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #eef3f7;
            max-width: 900px;
            margin: 30px auto;
            padding: 0 20px;
        }

        h1 { color: #183b56; }
        h2 { color: #245b7a; margin-top: 35px; }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            margin-bottom: 25px;
        }

        th {
            background-color: #245b7a;
            color: white;
            padding: 12px;
            text-align: left;
        }

        td {
            border: 1px solid #cccccc;
            padding: 10px;
        }

        tr:nth-child(even) { background-color: #f2f7fa; }
        tr:hover { background-color: #dceef7; }
    </style>
</head>

<body>
    <h1>IoT Sensor Database</h1>

    <h2>Sensor Data</h2>
    <table>
        <tr>
            <th>Node Name</th>
            <th>Time Received</th>
            <th>Temperature (°C)</th>
            <th>Humidity (%)</th>
        </tr>

        <?php while ($row = $sensorData->fetch(PDO::FETCH_ASSOC)): ?>
            <tr>
                <td><?= htmlspecialchars($row['node_name']) ?></td>
                <td><?= htmlspecialchars($row['time_received']) ?></td>
                <td><?= htmlspecialchars($row['temperature']) ?></td>
                <td><?= htmlspecialchars($row['humidity']) ?></td>
            </tr>
        <?php endwhile; ?>
    </table>

    <h2>Registered Sensors</h2>
    <table>
        <tr>
            <th>Node Name</th>
            <th>Manufacturer</th>
            <th>Longitude</th>
            <th>Latitude</th>
        </tr>

        <?php while ($row = $sensorRegister->fetch(PDO::FETCH_ASSOC)): ?>
            <tr>
                <td><?= htmlspecialchars($row['node_name']) ?></td>
                <td><?= htmlspecialchars($row['manufacturer']) ?></td>
                <td><?= htmlspecialchars($row['longitude']) ?></td>
                <td><?= htmlspecialchars($row['latitude']) ?></td>
            </tr>
        <?php endwhile; ?>
    </table>

    <h2>Averages for Node 1</h2>
    <p>
        Average Temperature:
        <?= $averages['average_temperature'] !== null
            ? number_format((float)$averages['average_temperature'], 2) . " °C"
            : "No data available" ?>
    </p>
    <p>
        Average Humidity:
        <?= $averages['average_humidity'] !== null
            ? number_format((float)$averages['average_humidity'], 2) . " %"
            : "No data available" ?>
    </p>
</body>
</html>
