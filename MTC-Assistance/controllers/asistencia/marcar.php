<?php
require_once '../../config/database.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['asistente_id']) || !isset($data['estado']) || !isset($data['fecha'])) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

try {
    // Verificar si ya existe un registro
    $stmt = $conn->prepare("SELECT id FROM asistencias WHERE asistente_id = ? AND fecha = ?");
    $stmt->execute([$data['asistente_id'], $data['fecha']]);
    
    if ($stmt->rowCount() > 0) {
        // Actualizar registro existente
        $stmt = $conn->prepare("UPDATE asistencias SET estado = ? WHERE asistente_id = ? AND fecha = ?");
    } else {
        // Crear nuevo registro
        $stmt = $conn->prepare("INSERT INTO asistencias (estado, asistente_id, fecha) VALUES (?, ?, ?)");
    }
    
    $stmt->execute([$data['estado'], $data['asistente_id'], $data['fecha']]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Asistencia marcada exitosamente'
    ]);
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al marcar asistencia: ' . $e->getMessage()
    ]);
}