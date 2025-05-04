<?php
require_once '../../config/database.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['nombre'])) {
    echo json_encode(['success' => false, 'message' => 'El nombre es requerido']);
    exit;
}

try {
    $stmt = $conn->prepare("INSERT INTO areas (nombre, descripcion, usuario_id) VALUES (?, ?, ?)");
    $stmt->execute([
        $data['nombre'],
        $data['descripcion'] ?? '',
        $_SESSION['user_id']
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Área creada exitosamente',
        'id' => $conn->lastInsertId()
    ]);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}