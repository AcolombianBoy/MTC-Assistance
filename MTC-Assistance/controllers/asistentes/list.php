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
    // Obtener la organización actual
    $organizacion_id = null;
    if (isset($_GET['organizacion'])) {
        $organizacion_id = $_GET['organizacion'];
    } else if (isset($_SESSION['current_organization_id'])) {
        $organizacion_id = $_SESSION['current_organization_id'];
    }
    
    // Verificar que el área pertenezca a la organización o al usuario
    $query = "
        SELECT a.* 
        FROM asistentes a
        INNER JOIN areas ar ON a.area_id = ar.id
        WHERE a.area_id = ? AND (ar.usuario_id = ?";
    
    $params = [$area_id, $_SESSION['user_id']];
    
    if ($organizacion_id) {
        $query .= " OR (ar.organizacion_id = ? AND EXISTS (
            SELECT 1 FROM usuarios_organizaciones uo 
            WHERE uo.usuario_id = ? AND uo.organizacion_id = ?
        ))";
        $params[] = $organizacion_id;
        $params[] = $_SESSION['user_id'];
        $params[] = $organizacion_id;
    }
    
    $query .= ")
        ORDER BY a.nombre ASC";
    
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    
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