<?php
// Archivo para depurar la API de organizaciones
// Ruta: c:\xampp\htdocs\mtca\MTC-Assistance\MTC-Assistance\controllers\organizaciones\debug-read.php

require_once '../../config/database.php';
require_once '../../models/organization.php';

// Desactivar la salida de errores en la respuesta JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Configurar para capturar errores
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error PHP: ' . $errstr,
        'error_details' => [
            'file' => $errfile,
            'line' => $errline,
            'type' => $errno
        ]
    ]);
    exit;
}

set_error_handler('customErrorHandler');

// Manejar excepciones no capturadas
function exceptionHandler($exception) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Excepción: ' . $exception->getMessage(),
        'error_details' => [
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ]
    ]);
    exit;
}

set_exception_handler('exceptionHandler');

// Iniciar sesión
session_start();
header('Content-Type: application/json');

// Verificar si hay sesión
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'No hay usuario autenticado'
    ]);
    exit;
}

// Verificar la conexión a la base de datos
try {
    if (!isset($conn) || !($conn instanceof PDO)) {
        throw new Exception("La conexión a la base de datos no está disponible");
    }
    
    // Verificar si las tablas existen
    $tables = ["organizaciones", "usuarios_organizaciones"];
    $missingTables = [];
    
    foreach ($tables as $table) {
        $stmt = $conn->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$table]);
        
        if ($stmt->rowCount() == 0) {
            $missingTables[] = $table;
        }
    }
    
    if (!empty($missingTables)) {
        echo json_encode([
            'success' => false,
            'message' => 'Faltan tablas en la base de datos',
            'missing_tables' => $missingTables,
            'solution' => 'Accede a /public/debug-org.php?install=true para instalar el esquema'
        ]);
        exit;
    }
    
    // Intentar obtener las organizaciones del usuario
    $org = new Organization($conn);
    $result = $org->getUserOrganizations($_SESSION['user_id']);
    
    // Validar el resultado
    if (!is_array($result)) {
        throw new Exception("El método getUserOrganizations no devolvió un array");
    }
    
    echo json_encode($result);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error de base de datos: ' . $e->getMessage(),
        'code' => $e->getCode()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error general: ' . $e->getMessage()
    ]);
}
