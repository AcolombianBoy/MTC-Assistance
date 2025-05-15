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

if (!isset($data['codigo_unico'])) {
    echo json_encode(['success' => false, 'message' => 'Código de organización no proporcionado']);
    exit;
}

try {
    $org = new Organization($conn);
    
    // Buscar la organización por código único
    $orgResult = $org->getByCode($data['codigo_unico']);
    
    if (!$orgResult['success']) {
        echo json_encode(['success' => false, 'message' => 'Organización no encontrada con ese código']);
        exit;
    }
    
    $organizacion = $orgResult['data'];
    
    // Verificar si el usuario ya está en la organización
    $accessResult = $org->checkUserAccess($_SESSION['user_id'], $organizacion['id']);
    
    if ($accessResult['success']) {
        echo json_encode([
            'success' => true,
            'message' => 'Ya perteneces a esta organización',
            'id' => $organizacion['id'],
            'nombre' => $organizacion['nombre']
        ]);
        exit;
    }
    
    // Añadir usuario a la organización
    $result = $org->addUser($organizacion['id'], $_SESSION['user_id'], 'miembro');
    
    if ($result['success']) {
        echo json_encode([
            'success' => true,
            'message' => 'Te has unido a ' . $organizacion['nombre'],
            'id' => $organizacion['id'],
            'nombre' => $organizacion['nombre']
        ]);
    } else {
        echo json_encode($result);
    }
    
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al unirse a la organización: ' . $e->getMessage()
    ]);
}
