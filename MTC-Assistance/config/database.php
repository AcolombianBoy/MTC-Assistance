<?php
// Configuración para entorno local (por defecto)
$host = 'localhost';
$username = 'root'; 
$password = '';
$dbname = 'mtc_assistance';

// Detectar si estamos en Hostinger y cambiar la configuración
if ($_SERVER['HTTP_HOST'] !== 'localhost') {
    // Configuración para Hostinger (ajusta estos valores según tu cuenta)
    $host = 'localhost';
    $username = 'tu_usuario_hostinger'; 
    $password = 'tu_contraseña_hostinger';
    $dbname = 'tu_base_de_datos_hostinger';
}

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("SET NAMES utf8");
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Connection failed: ' . $e->getMessage()]);
    exit;
}