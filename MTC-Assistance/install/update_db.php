<?php
// filepath: c:\xampp\htdocs\mtca\MTC-Assistance\MTC-Assistance\install\update_db.php
header('Content-Type: application/json');

// Configuración de la base de datos
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'mtc_assistance';

try {
    // Conectar a la base de datos
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Ejecutar el script SQL de actualización
    $sql = file_get_contents('../database/update_schema.sql');
    
    // Dividir por ";" para ejecutar cada sentencia por separado
    $queries = explode(';', $sql);
    
    foreach ($queries as $query) {
        $query = trim($query);
        if (!empty($query)) {
            $conn->exec($query);
        }
    }
    
    echo json_encode(['success' => true, 'message' => 'Base de datos actualizada correctamente']);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar la base de datos: ' . $e->getMessage()]);
}
