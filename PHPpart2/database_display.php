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

    // Select the node from the dropdown; use node_1 by default
    $selectedNode = $_GET['node_name'] ?? 'node_1';

    // Confirm that the selected node is registered
    $nodeCheck = $pdo->prepare(
        "SELECT COUNT(*) FROM sensor_register WHERE node_name = :node_name"
    );
    $nodeCheck->execute(['node_name' => $selectedNode]);

    if ((int)$nodeCheck->fetchColumn() === 0) {
        $selectedNode = 'node_1';
    }

    // Retrieve all registered nodes for the dropdown menu
    $nodeList = $pdo->query(
        "SELECT node_name FROM sensor_register ORDER BY node_name ASC"
    );

    // Calculate humidity and temperature statistics for the selected node
    $averageQuery = $pdo->prepare("
        SELECT AVG(temperature) AS average_temperature,
               AVG(humidity) AS average_humidity,
               MAX(humidity) AS maximum_humidity,
               MIN(humidity) AS minimum_humidity
        FROM sensor_data
        WHERE node_name = :node_name
    ");
    $averageQuery->execute(['node_name' => $selectedNode]);
    $averages = $averageQuery->fetch(PDO::FETCH_ASSOC);

    // Retrieve temperature data for node_1 for the graph
    $chartQuery = $pdo->prepare("
        SELECT time_received, temperature
        FROM sensor_data
        WHERE node_name = :node_name
        ORDER BY time_received ASC
    ");
    $chartQuery->execute(['node_name' => $selectedNode]);

    $chartLabels = [];
    $chartTemperatures = [];

    while ($row = $chartQuery->fetch(PDO::FETCH_ASSOC)) {
        $chartLabels[] = $row['time_received'];
        $chartTemperatures[] = $row['temperature'];
    }

    // Retrieve all data for the selected node for JSON display
    $jsonQuery = $pdo->prepare("
        SELECT node_name, time_received, temperature, humidity
        FROM sensor_data
        WHERE node_name = :node_name
        ORDER BY time_received ASC
    ");
    $jsonQuery->execute(['node_name' => $selectedNode]);
    $jsonData = $jsonQuery->fetchAll(PDO::FETCH_ASSOC);


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

        pre {
            background-color: white;
            border: 1px solid #cccccc;
            padding: 15px;
            overflow-x: auto;
        }

        #chart-container {
            background-color: white;
            padding: 15px;
            margin-top: 10px;
            margin-bottom: 30px;
            width: 640px;
            height: 400px;
        }

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

    <h2>Statistics for <?= htmlspecialchars($selectedNode) ?></h2>
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
    <p>
        Maximum Humidity:
        <?= $averages['maximum_humidity'] !== null
            ? number_format((float)$averages['maximum_humidity'], 2) . " %"
            : "No data available" ?>
    </p>
    <p>
        Minimum Humidity:
        <?= $averages['minimum_humidity'] !== null
            ? number_format((float)$averages['minimum_humidity'], 2) . " %"
            : "No data available" ?>
    </p>

    <h2>Temperature Graph</h2>

    <form method="get" action="database_display.php">
        <label for="node_name">Choose a node:</label>
        <select name="node_name" id="node_name" onchange="this.form.submit()">
            <?php while ($node = $nodeList->fetch(PDO::FETCH_ASSOC)): ?>
                <option value="<?= htmlspecialchars($node['node_name']) ?>"
                    <?= $node['node_name'] === $selectedNode ? 'selected' : '' ?>>
                    <?= htmlspecialchars($node['node_name']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </form>

    <div id="chart-container">
        <canvas id="mycanvas"></canvas>
    </div>

    <!-- Course Chart.js setup: jQuery, Chart.js, then app.js -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.1/chart.min.js"></script>
    <script src="js/app.js"></script>

    <h2>JSON Data for <?= htmlspecialchars($selectedNode) ?></h2>
    <pre><?= htmlspecialchars(json_encode($jsonData, JSON_PRETTY_PRINT)) ?></pre>

</body>
</html>
