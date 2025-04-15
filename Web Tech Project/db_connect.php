<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=sorting_visualization', 'root', '1234567'); // Adjust credentials as needed
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Connection successful";
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
