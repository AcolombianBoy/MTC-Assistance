<?php
require_once '../../config/database.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT * FROM areas WHERE usuario_id = ? ORDER BY created_at DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $areas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($areas);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}