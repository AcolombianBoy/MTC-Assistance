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

if (!isset($data['organizacion_id']) || !isset($data['email']) || !isset($data['rol'])) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

try {
    $org = new Organization($conn);
    
    // Verificar que el usuario tenga permisos para esta organización
    $access = $org->checkUserAccess($_SESSION['user_id'], $data['organizacion_id']);
    
    if (!$access['success']) {
        echo json_encode(['success' => false, 'message' => 'No tienes permiso para gestionar esta organización']);
        exit;
    }
    
    // Solo los administradores pueden añadir/cambiar roles
    if ($access['rol'] !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Solo los administradores pueden gestionar miembros']);
        exit;
    }
    
    // Buscar usuario por email
    $userStmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ? AND estado = 'activo'");
    $userStmt->execute([$data['email']]);
    
    if ($userStmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
        exit;
    }
    
    $usuario = $userStmt->fetch(PDO::FETCH_ASSOC);
    
    // Añadir usuario a la organización o actualizar su rol
    $result = $org->addUser($data['organizacion_id'], $usuario['id'], $data['rol']);
    
    echo json_encode($result);
    
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al añadir miembro: ' . $e->getMessage()
    ]);
}
