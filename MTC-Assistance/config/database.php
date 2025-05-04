<?php
$host = 'localhost';
$dbname = 'mtc_assistance';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("SET NAMES utf8");
} catch(PDOException $e) {
    echo json_encode(['error' => true, 'message' => 'Connection failed: ' . $e->getMessage()]);
    exit;
}