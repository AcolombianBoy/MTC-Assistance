<?php
require_once '../../config/database.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['area_id']) || !isset($data['fecha']) || !isset($data['asistencias'])) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

try {
    $conn->beginTransaction();

    foreach ($data['asistencias'] as $asistencia) {
        // Verificar si ya existe un registro
        $stmt = $conn->prepare("
            SELECT id FROM asistencias 
            WHERE asistente_id = ? AND fecha = ?
        ");
        $stmt->execute([$asistencia['asistente_id'], $data['fecha']]);
        
        if ($stmt->rowCount() > 0) {
            // Actualizar registro existente
            $stmt = $conn->prepare("
                UPDATE asistencias 
                SET estado = ? 
                WHERE asistente_id = ? AND fecha = ?
            ");
        } else {
            // Crear nuevo registro
            $stmt = $conn->prepare("
                INSERT INTO asistencias (asistente_id, fecha, estado) 
                VALUES (?, ?, ?)
            ");
        }
        
        $stmt->execute([
            $asistencia['estado'],
            $asistencia['asistente_id'],
            $data['fecha']
        ]);
    }

    $conn->commit();
    echo json_encode([
        'success' => true,
        'message' => 'Asistencia guardada exitosamente'
    ]);
} catch(PDOException $e) {
    $conn->rollBack();
    echo json_encode([
        'success' => false,
        'message' => 'Error al guardar la asistencia: ' . $e->getMessage()
    ]);
}