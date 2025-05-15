<?php
require_once '../../config/database.php';
require_once '../../models/organization.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Obtener id de la organización
$id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID de organización no especificado']);
    exit;
}

try {
    $org = new Organization($conn);
    
    // Verificar que el usuario tenga permisos para esta organización
    $access = $org->checkUserAccess($_SESSION['user_id'], $id);
    
    if (!$access['success']) {
        echo json_encode(['success' => false, 'message' => 'No tienes acceso a esta organización']);
        exit;
    }
    
    // Obtener detalles de la organización
    $result = $org->getById($id);
    
    if ($result['success']) {
        $result['data']['rol_usuario'] = $access['rol'];
    }
    
    echo json_encode($result);
    
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener la organización: ' . $e->getMessage()
    ]);
}
