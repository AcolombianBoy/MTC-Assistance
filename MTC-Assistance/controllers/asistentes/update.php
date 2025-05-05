<?php
require_once '../../config/database.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id']) || !isset($data['nombre']) || !isset($data['email'])) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

try {
    // Verificar que el asistente pertenezca al usuario
    $stmt = $conn->prepare("
        SELECT id FROM asistentes 
        WHERE id = ? AND usuario_id = ?
    ");
    $stmt->execute([$data['id'], $_SESSION['user_id']]);
    
    if ($stmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'Asistente no encontrado']);
        exit;
    }

    // Actualizar el asistente
    $stmt = $conn->prepare("
        UPDATE asistentes 
        SET nombre = ?, email = ? 
        WHERE id = ? AND usuario_id = ?
    ");
    
    $stmt->execute([
        $data['nombre'],
        $data['email'],
        $data['id'],
        $_SESSION['user_id']
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Asistente actualizado correctamente'
    ]);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}