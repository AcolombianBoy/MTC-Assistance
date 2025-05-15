<?php
// filepath: c:\xampp\htdocs\mtca\MTC-Assistance\MTC-Assistance\install\create_db.php
header('Content-Type: application/json');

// Configuración de la base de datos
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'mtc_assistance';

try {
    // Conectar a MySQL sin seleccionar una base de datos
    $conn = new PDO("mysql:host=$host", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Crear la base de datos si no existe
    $conn->exec("CREATE DATABASE IF NOT EXISTS $dbname");
    $conn->exec("USE $dbname");
    
    // Ejecutar el script SQL de creación de tablas
    $sql = file_get_contents('../database/schema.sql');
    $conn->exec($sql);
    
    echo json_encode(['success' => true, 'message' => 'Base de datos creada correctamente']);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error al crear la base de datos: ' . $e->getMessage()]);
}
