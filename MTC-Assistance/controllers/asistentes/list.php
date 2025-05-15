<?php
require_once '../../config/database.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$area_id = $_GET['area'] ?? null;

if (!$area_id) {
    echo json_encode(['success' => false, 'message' => 'ID del área no especificado']);
    exit;
}

try {
    // Verificar que el área pertenezca al usuario
    $stmt = $conn->prepare("
        SELECT a.* 
        FROM asistentes a
        INNER JOIN areas ar ON a.area_id = ar.id
        WHERE a.area_id = ? AND ar.usuario_id = ?
        ORDER BY a.nombre ASC
    ");
    $stmt->execute([$area_id, $_SESSION['user_id']]);
    
    echo json_encode([
        'success' => true,
        'asistentes' => $stmt->fetchAll(PDO::FETCH_ASSOC)
    ]);
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al cargar los asistentes: ' . $e->getMessage()
    ]);
}