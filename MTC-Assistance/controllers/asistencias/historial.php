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
        SELECT 
            a.fecha,
            SUM(CASE WHEN a.estado = 'presente' THEN 1 ELSE 0 END) as presentes,
            SUM(CASE WHEN a.estado = 'ausente' THEN 1 ELSE 0 END) as ausentes
        FROM asistencias a
        WHERE a.area_id = ? AND a.usuario_id = ?
        GROUP BY a.fecha
        ORDER BY a.fecha DESC
    ");
    
    $stmt->execute([$area_id, $_SESSION['user_id']]);
    $historial = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($historial);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}