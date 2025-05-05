<?php
require_once '../../config/database.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$area_id = $_GET['area_id'] ?? null;

if (!$area_id) {
    echo json_encode(['success' => false, 'message' => 'ID del área requerido']);
    exit;
}

try {
    $stmt = $conn->prepare("
        SELECT * FROM asistentes 
        WHERE area_id = ? AND usuario_id = ? AND estado = 'activo'
        ORDER BY nombre ASC
    ");
    $stmt->execute([$area_id, $_SESSION['user_id']]);
    $asistentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($asistentes);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}