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
    // Verificar que el usuario tenga acceso a la organización
    $verifyStmt = $conn->prepare("
        SELECT rol FROM usuarios_organizaciones 
        WHERE usuario_id = ? AND organizacion_id = ?
    ");
    $verifyStmt->execute([$_SESSION['user_id'], $organizacion_id]);
    
    if ($verifyStmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'No tienes acceso a esta organización']);
        exit;
    }
    
    // Obtener asistencias de la organización
    $stmt = $conn->prepare("
        SELECT a.*, ast.nombre as asistente_nombre, ar.nombre as area_nombre 
        FROM asistencias a
        JOIN asistentes ast ON a.asistente_id = ast.id
        JOIN areas ar ON a.area_id = ar.id
        WHERE a.organizacion_id = ? 
        ORDER BY a.fecha DESC, ar.nombre ASC, ast.nombre ASC
    ");
    $stmt->execute([$organizacion_id]);
    
    $asistencias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $asistencias
    ]);
} catch(PDOException $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Error al obtener asistencias: ' . $e->getMessage()
    ]);
}
