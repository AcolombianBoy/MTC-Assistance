<?php
require_once '../config/database.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['organizacion_id'])) {
    $_SESSION['current_organization_id'] = $data['organizacion_id'];
    echo json_encode(['success' => true, 'message' => 'Sesión sincronizada']);
} else {
    echo json_encode(['success' => false, 'message' => 'ID de organización no proporcionado']);
}