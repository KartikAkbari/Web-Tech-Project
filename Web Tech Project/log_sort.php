<?php
require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $algorithmName = $data['algorithmName'];
    $dataset = json_encode($data['dataset']);
    $sortedData = json_encode($data['sortedData']);
    $executionTime = $data['executionTime'];
    $comparisons = $data['comparisons'];
    $swaps = $data['swaps'];

    $stmt = $pdo->prepare("
        INSERT INTO sorting_logs (algorithm_name, dataset, sorted_data, execution_time, comparisons, swaps)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$algorithmName, $dataset, $sortedData, $executionTime, $comparisons, $swaps]);

    echo "Log saved successfully.";
}
?>
