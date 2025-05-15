<?php
require_once '../../config/database.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['nombre']) || !isset($data['organizacion_id'])) {
    echo json_encode(['success' => false, 'message' => 'El nombre y la organización son requeridos']);
    exit;
}

try {
    // Verificar si el usuario tiene acceso a la organización
    $verifyStmt = $conn->prepare("SELECT rol FROM usuarios_organizaciones WHERE usuario_id = ? AND organizacion_id = ?");
    $verifyStmt->execute([$_SESSION['user_id'], $data['organizacion_id']]);
    
    if ($verifyStmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'No tienes acceso a esta organización']);
        exit;
    }
    
    $stmt = $conn->prepare("INSERT INTO areas (nombre, descripcion, usuario_id, organizacion_id) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $data['nombre'],
        $data['descripcion'] ?? '',
        $_SESSION['user_id'],
        $data['organizacion_id']
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Área creada exitosamente',
        'id' => $conn->lastInsertId()
    ]);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}