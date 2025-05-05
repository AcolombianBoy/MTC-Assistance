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

    // Primero, eliminar registros existentes para esa fecha y área
    $stmt = $conn->prepare("
        DELETE FROM asistencias 
        WHERE area_id = ? AND fecha = ? AND usuario_id = ?
    ");
    $stmt->execute([$data['area_id'], $data['fecha'], $_SESSION['user_id']]);

    // Luego, insertar los nuevos registros
    $stmt = $conn->prepare("
        INSERT INTO asistencias (asistente_id, area_id, usuario_id, fecha, estado)
        VALUES (?, ?, ?, ?, ?)
    ");

    foreach ($data['asistencias'] as $asistencia) {
        $stmt->execute([
            $asistencia['asistente_id'],
            $data['area_id'],
            $_SESSION['user_id'],
            $data['fecha'],
            $asistencia['estado']
        ]);
    }

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Asistencia registrada correctamente']);
} catch(PDOException $e) {
    $conn->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}