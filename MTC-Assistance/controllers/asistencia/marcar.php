<?php
require_once '../../config/database.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

// Verificar que todos los datos necesarios estén presentes
if (!isset($data['asistente_id']) || !isset($data['estado']) || !isset($data['fecha']) ||
    !isset($data['area_id']) || !isset($data['organizacion_id'])) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

try {
    // Primero verificar que el área existe y está asociada a la organización
    $checkAreaStmt = $conn->prepare("
        SELECT id FROM areas 
        WHERE id = ? AND organizacion_id = ?
    ");
    $checkAreaStmt->execute([$data['area_id'], $data['organizacion_id']]);
    
    if ($checkAreaStmt->rowCount() === 0) {
        echo json_encode([
            'success' => false, 
            'message' => 'El área especificada no existe o no pertenece a esta organización'
        ]);
        exit;
    }
    
    // Verificar que el asistente existe y pertenece al área
    $checkAsistenteStmt = $conn->prepare("
        SELECT id FROM asistentes 
        WHERE id = ? AND area_id = ?
    ");
    $checkAsistenteStmt->execute([$data['asistente_id'], $data['area_id']]);
    
    if ($checkAsistenteStmt->rowCount() === 0) {
        echo json_encode([
            'success' => false, 
            'message' => 'El asistente especificado no existe o no pertenece a esta área'
        ]);
        exit;
    }

    // Ahora podemos proceder con la operación normal
    $stmt = $conn->prepare("
        SELECT id FROM asistencias 
        WHERE asistente_id = ? AND fecha = ?
    ");
    $stmt->execute([$data['asistente_id'], $data['fecha']]);
    
    if ($stmt->rowCount() > 0) {
        // Actualizar registro existente
        $stmt = $conn->prepare("
            UPDATE asistencias 
            SET estado = ? 
            WHERE asistente_id = ? AND fecha = ?
        ");
        $stmt->execute([$data['estado'], $data['asistente_id'], $data['fecha']]);
    } else {
        // Crear nuevo registro
        $stmt = $conn->prepare("
            INSERT INTO asistencias (asistente_id, fecha, estado, area_id, usuario_id, organizacion_id) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['asistente_id'], 
            $data['fecha'], 
            $data['estado'],
            $data['area_id'],
            $_SESSION['user_id'],
            $data['organizacion_id']
        ]);
    }
    
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