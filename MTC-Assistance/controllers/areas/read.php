<?php
require_once '../../config/database.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Obtener ID de la organización
if (!isset($_GET['organizacion_id'])) {
    echo json_encode(['success' => false, 'message' => 'ID de organización no proporcionado']);
    exit;
}

$organizacion_id = $_GET['organizacion_id'];

try {
    // Verificar si el usuario tiene acceso a la organización
    $verifyStmt = $conn->prepare("SELECT rol FROM usuarios_organizaciones WHERE usuario_id = ? AND organizacion_id = ?");
    $verifyStmt->execute([$_SESSION['user_id'], $organizacion_id]);
    
    if ($verifyStmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'No tienes acceso a esta organización']);
        exit;
    }
    
    $stmt = $conn->prepare("SELECT * FROM areas WHERE organizacion_id = ? ORDER BY created_at DESC");
    $stmt->execute([$organizacion_id]);
    $areas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($areas);
}catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}