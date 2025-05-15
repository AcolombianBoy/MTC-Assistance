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

if (!isset($data['nombre']) || !isset($data['descripcion'])) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

try {
    $org = new Organization($conn);
    
    // Generar código único aleatorio para la organización
    $codigo_unico = isset($data['codigo_unico']) && !empty($data['codigo_unico']) 
        ? $data['codigo_unico'] 
        : $org->generateUniqueCode();
    
    // Manejar logo si se proporciona
    $logo = isset($data['logo']) ? $data['logo'] : null;
    
    // Crear la organización
    $result = $org->create($data['nombre'], $data['descripcion'], $codigo_unico, $logo);
    
    if ($result['success']) {
        // Asignar al usuario creador como administrador de la organización
        $org->addUser($result['id'], $_SESSION['user_id'], 'admin');
        
        echo json_encode([
            'success' => true,
            'message' => 'Organización creada exitosamente',
            'id' => $result['id'],
            'codigo' => $codigo_unico
        ]);
    } else {
        echo json_encode($result);
    }
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al crear la organización: ' . $e->getMessage()
    ]);
}
