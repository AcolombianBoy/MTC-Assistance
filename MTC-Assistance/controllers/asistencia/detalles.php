<?php
require_once '../../config/database.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$area = $_GET['area'] ?? null;
$fecha = $_GET['fecha'] ?? null;

if (!$area || !$fecha) {
    echo json_encode(['success' => false, 'message' => 'Parámetros incompletos']);
    exit;
}

try {
    $stmt = $conn->prepare("
        SELECT 
            a.asistente_id,
            ast.nombre,
            ast.email,
            COALESCE(a.estado, 'ausente') as estado
        FROM asistentes ast
        LEFT JOIN asistencias a ON ast.id = a.asistente_id 
            AND a.fecha = ?
        WHERE ast.area_id = ?
        ORDER BY ast.nombre
    ");
    
    $stmt->execute([$fecha, $area]);
    
    echo json_encode([
        'success' => true,
        'asistencias' => $stmt->fetchAll(PDO::FETCH_ASSOC)
    ]);

} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al cargar los detalles: ' . $e->getMessage()
    ]);
}