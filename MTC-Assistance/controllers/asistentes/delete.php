<?php
require_once '../../config/database.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID del asistente requerido']);
    exit;
}

try {
    // Obtener la organización actual del usuario
    $organizacionId = $_SESSION['current_organization_id'] ?? null;
    
    // Verificar que el asistente pertenezca al usuario y a la organización
    $stmt = $conn->prepare("
        SELECT id FROM asistentes 
        WHERE id = ? AND (usuario_id = ? OR organizacion_id = ?)
    ");
    $stmt->execute([$data['id'], $_SESSION['user_id'], $organizacionId]);
    
    if ($stmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'Asistente no encontrado']);
        exit;
    }

    // Eliminar el asistente
    $stmt = $conn->prepare("DELETE FROM asistentes WHERE id = ?");
    $stmt->execute([$data['id']]);
    
    echo json_encode(['success' => true, 'message' => 'Asistente eliminado correctamente']);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}