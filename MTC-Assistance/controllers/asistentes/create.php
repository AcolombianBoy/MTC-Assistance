<?php
require_once '../../config/database.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['nombre']) || !isset($data['email']) || !isset($data['area_id']) || !isset($data['organizacion_id'])) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

try {
    // Verificar que el usuario tenga acceso a la organización
    $verifyStmt = $conn->prepare("SELECT rol FROM usuarios_organizaciones WHERE usuario_id = ? AND organizacion_id = ?");
    $verifyStmt->execute([$_SESSION['user_id'], $data['organizacion_id']]);
    
    if ($verifyStmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'No tienes acceso a esta organización']);
        exit;
    }
    
    // Verificar que el área pertenezca a la organización
    $stmt = $conn->prepare("SELECT id FROM areas WHERE id = ? AND organizacion_id = ?");
    $stmt->execute([$data['area_id'], $data['organizacion_id']]);
    
    if ($stmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'Área no encontrada en esta organización']);
        exit;
    }    $stmt = $conn->prepare("
        INSERT INTO asistentes (nombre, email, area_id, usuario_id, organizacion_id) 
        VALUES (?, ?, ?, ?, ?)
    ");
      $stmt->execute([
        $data['nombre'],
        $data['email'],
        $data['area_id'],
        $_SESSION['user_id'],
        $data['organizacion_id']
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Asistente agregado correctamente',
        'id' => $conn->lastInsertId()
    ]);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}