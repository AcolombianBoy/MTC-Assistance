<?php
// Desactivar la salida de errores para la respuesta JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Configurar para capturar errores
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error PHP: ' . $errstr
    ]);
    exit;
}

set_error_handler('customErrorHandler');

// Cargar dependencias
require_once '../../config/database.php';
require_once '../../models/organization.php';

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

try {
    // Verificar que la conexión es válida
    if (!isset($conn) || !($conn instanceof PDO)) {
        throw new Exception("La conexión a la base de datos no está disponible");
    }
    
    // Verificar si las tablas existen
    $stmt = $conn->prepare("SHOW TABLES LIKE 'organizaciones'");
    $stmt->execute();
    if ($stmt->rowCount() == 0) {
        echo json_encode([
            'success' => false,
            'message' => 'La tabla de organizaciones no existe. Por favor, instala el esquema de la base de datos.'
        ]);
        exit;
    }
    
    $org = new Organization($conn);
    
    // Obtener todas las organizaciones a las que el usuario tiene acceso
    $result = $org->getUserOrganizations($_SESSION['user_id']);
    
    // Asegurar que el resultado sea un array válido para evitar problemas
    if (!is_array($result)) {
        throw new Exception("El resultado no es un array válido");
    }
    
    echo json_encode($result);
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error de base de datos: ' . $e->getMessage()
    ]);
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener organizaciones: ' . $e->getMessage()
    ]);
}
