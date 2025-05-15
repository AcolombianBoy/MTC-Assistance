<?php
require_once '../../config/database.php';
require_once '../../models/organization.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Obtener datos del request
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID de organización no proporcionado']);
    exit;
}

try {
    $org = new Organization($conn);
    
    // Verificar que el usuario tenga permisos para esta organización
    $access = $org->checkUserAccess($_SESSION['user_id'], $data['id']);
    
    if (!$access['success']) {
        echo json_encode(['success' => false, 'message' => 'No tienes permiso para eliminar esta organización']);
        exit;
    }
    
    // Solo los administradores pueden eliminar la organización
    if ($access['rol'] !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Solo los administradores pueden eliminar organizaciones']);
        exit;
    }
    
    // Cambiar estado a inactivo en lugar de eliminar completamente
    $result = $org->changeStatus($data['id'], 'inactivo');
    
    if ($result['success']) {
        echo json_encode([
            'success' => true,
            'message' => 'Organización eliminada exitosamente'
        ]);
    } else {
        echo json_encode($result);
    }
    
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al eliminar la organización: ' . $e->getMessage()
    ]);
}
