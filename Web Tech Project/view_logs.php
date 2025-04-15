<?php
require 'db_connect.php'; // Ensure this file connects to the database

// Fetch all logs from the database
$stmt = $pdo->query("SELECT * FROM sorting_logs ORDER BY run_date DESC");
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sorting Logs</title>
    <link rel="stylesheet" href="view_logs.css">
</head>
<body>
    <h2>Sorting Performance Logs</h2>

    <table border="1" cellpadding="10" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Algorithm</th>
                <th>Dataset</th>
                <th>Sorted Data</th>
                <th>Execution Time (s)</th>
                <th>Comparisons</th>
                <th>Swaps</th>
                <th>Run Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($logs): ?>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?= htmlspecialchars($log['id']) ?></td>
                        <td><?= htmlspecialchars($log['algorithm_name']) ?></td>
                        <td><?= htmlspecialchars($log['dataset']) ?></td>
                        <td><?= htmlspecialchars($log['sorted_data']) ?></td>
                        <td><?= htmlspecialchars($log['execution_time']) ?></td>
                        <td><?= htmlspecialchars($log['comparisons']) ?></td>
                        <td><?= htmlspecialchars($log['swaps']) ?></td>
                        <td><?= htmlspecialchars($log['run_date']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8">No logs found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <a href="index.html">Back to Visualization</a> <!-- Link to your main visualization page -->
</body>
</html>
