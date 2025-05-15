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

if (!isset($data['id']) || !isset($data['nombre']) || !isset($data['descripcion'])) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

try {
    $org = new Organization($conn);
    
    // Verificar que el usuario tenga permisos para esta organización
    $access = $org->checkUserAccess($_SESSION['user_id'], $data['id']);
    
    if (!$access['success']) {
        echo json_encode(['success' => false, 'message' => 'No tienes permiso para modificar esta organización']);
        exit;
    }
    
    // Solo los administradores pueden editar la organización
    if ($access['rol'] !== 'admin' && $access['rol'] !== 'editor') {
        echo json_encode(['success' => false, 'message' => 'No tienes permisos suficientes para editar esta organización']);
        exit;
    }
    
    // Manejar logo si se proporciona
    $logo = isset($data['logo']) ? $data['logo'] : null;
    
    // Actualizar la organización
    $result = $org->update($data['id'], $data['nombre'], $data['descripcion'], $logo);
    
    echo json_encode($result);
    
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al actualizar la organización: ' . $e->getMessage()
    ]);
}
