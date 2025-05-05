<?php
require_once '../../config/database.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID del área requerido']);
    exit;
}

try {
    $stmt = $conn->prepare("
        SELECT a.*, COUNT(ast.id) as total_asistentes 
        FROM areas a 
        LEFT JOIN asistentes ast ON a.id = ast.area_id 
        WHERE a.id = ? AND a.usuario_id = ? 
        GROUP BY a.id
    ");
    $stmt->execute([$id, $_SESSION['user_id']]);
    
    if ($stmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'Área no encontrada']);
        exit;
    }

    $area = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($area);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}