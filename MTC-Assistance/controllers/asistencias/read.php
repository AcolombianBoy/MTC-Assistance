<?php
require_once '../../config/database.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$area_id = $_GET['area_id'] ?? null;
$fecha = $_GET['fecha'] ?? date('Y-m-d');

if (!$area_id) {
    echo json_encode(['success' => false, 'message' => 'ID del área requerido']);
    exit;
}

try {
    $stmt = $conn->prepare("
        SELECT a.id, a.nombre, a.email, 
            COALESCE(ast.estado, 'ausente') as estado
        FROM asistentes a
        LEFT JOIN asistencias ast ON a.id = ast.asistente_id 
            AND ast.fecha = ? AND ast.area_id = ?
        WHERE a.area_id = ? AND a.usuario_id = ?
        ORDER BY a.nombre
    ");
    
    $stmt->execute([$fecha, $area_id, $area_id, $_SESSION['user_id']]);
    $asistencias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($asistencias);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}