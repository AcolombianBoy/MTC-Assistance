<?php
require_once '../../config/database.php';
require_once '../../models/organization.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Obtener ID de la organización
if (!isset($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID de organización no proporcionado']);
    exit;
}

$organizacion_id = $_GET['id'];

try {
    $org = new Organization($conn);
    
    // Verificar que el usuario tenga acceso a la organización
    $access = $org->checkUserAccess($_SESSION['user_id'], $organizacion_id);
    
    if (!$access['success']) {
        echo json_encode(['success' => false, 'message' => 'No tienes acceso a esta organización']);
        exit;
    }
    
    // Obtener estadísticas de la organización
    
    // 1. Número total de áreas
    $areasStmt = $conn->prepare("
        SELECT COUNT(*) as total 
        FROM areas 
        WHERE organizacion_id = ? AND estado = 'activo'
    ");
    $areasStmt->execute([$organizacion_id]);
    $areasCount = $areasStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // 2. Número total de asistentes
    $asistentesStmt = $conn->prepare("
        SELECT COUNT(*) as total 
        FROM asistentes 
        WHERE organizacion_id = ? AND estado = 'activo'
    ");
    $asistentesStmt->execute([$organizacion_id]);
    $asistentesCount = $asistentesStmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // 3. Asistencias de la última semana
    $asistenciasStmt = $conn->prepare("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN estado = 'presente' THEN 1 ELSE 0 END) as presentes,
            SUM(CASE WHEN estado = 'ausente' THEN 1 ELSE 0 END) as ausentes,
            SUM(CASE WHEN estado = 'justificado' THEN 1 ELSE 0 END) as justificados
        FROM asistencias
        WHERE organizacion_id = ? AND fecha >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    ");
    $asistenciasStmt->execute([$organizacion_id]);
    $asistencias = $asistenciasStmt->fetch(PDO::FETCH_ASSOC);
    
    // 4. Últimas 5 asistencias registradas
    $ultimasAsistenciasStmt = $conn->prepare("
        SELECT a.fecha, a.estado, ast.nombre as asistente, ar.nombre as area
        FROM asistencias a
        JOIN asistentes ast ON a.asistente_id = ast.id
        JOIN areas ar ON a.area_id = ar.id
        WHERE a.organizacion_id = ?
        ORDER BY a.created_at DESC
        LIMIT 5
    ");
    $ultimasAsistenciasStmt->execute([$organizacion_id]);
    $ultimasAsistencias = $ultimasAsistenciasStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'total_areas' => $areasCount,
            'total_asistentes' => $asistentesCount,
            'asistencias_semana' => $asistencias,
            'ultimas_asistencias' => $ultimasAsistencias
        ]
    ]);
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Error al obtener estadísticas: ' . $e->getMessage()
    ]);
}
