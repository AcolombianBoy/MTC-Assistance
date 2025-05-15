<?php
require_once '../../config/database.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$area = $_GET['area'] ?? null;

if (!$area) {
    echo json_encode(['success' => false, 'message' => 'ID del área no especificado']);
    exit;
}

try {
    $stmt = $conn->prepare("
        SELECT 
            a.fecha,
            COUNT(CASE WHEN a.estado = 'presente' THEN 1 END) as presentes,
            COUNT(CASE WHEN a.estado = 'ausente' THEN 1 END) as ausentes
        FROM asistencias a
        INNER JOIN asistentes ast ON a.asistente_id = ast.id
        WHERE ast.area_id = ?
        GROUP BY a.fecha
        ORDER BY a.fecha DESC
    ");
    
    $stmt->execute([$area]);
    $historial = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'historial' => $historial
    ]);

} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al cargar el historial: ' . $e->getMessage()
    ]);
}