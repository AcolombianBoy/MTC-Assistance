<?php
require_once '../config/database.php';
session_start();
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['email']) || !isset($data['password'])) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

$email = trim($data['email']);
$password = $data['password'];

try {
    $stmt = $conn->prepare("SELECT id, nombre, password FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'Credenciales inválidas']);
        exit;
    }

    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['nombre'];
        
        echo json_encode([
            'success' => true,
            'message' => 'Login exitoso'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Credenciales inválidas']);
    }
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}