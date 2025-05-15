<!-- filepath: c:\xampp\htdocs\mtca\MTC-Assistance\MTC-Assistance\public\debug-org.php -->
<?php
// Archivo de depuración para organizaciones
require_once '../config/database.php';
require_once '../models/organization.php';

// Verificar conexión a la base de datos
echo "<h2>Verificando conexión a la base de datos</h2>";
if (isset($conn) && $conn instanceof PDO) {
    echo "<p style='color:green'>Conexión a la base de datos establecida correctamente.</p>";
} else {
    echo "<p style='color:red'>Error: No se pudo establecer la conexión a la base de datos.</p>";
    exit;
}

// Verificar si las tablas necesarias existen
echo "<h2>Verificando tablas en la base de datos</h2>";
try {
    $tables = [
        "organizaciones" => "Tabla de organizaciones",
        "usuarios_organizaciones" => "Tabla de relación entre usuarios y organizaciones"
    ];
    
    foreach ($tables as $table => $desc) {
        $stmt = $conn->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$table]);
        
        if ($stmt->rowCount() > 0) {
            echo "<p style='color:green'>✓ $desc existe</p>";
        } else {
            echo "<p style='color:red'>✗ $desc no existe</p>";
        }
    }
} catch (PDOException $e) {
    echo "<p style='color:red'>Error al verificar tablas: " . $e->getMessage() . "</p>";
}

// Intentar instalar el esquema si se solicita
if (isset($_GET['install']) && $_GET['install'] == 'true') {
    echo "<h2>Intentando instalar el esquema</h2>";
    try {
        $sql = file_get_contents('../database/update_schema.sql');
        
        // Dividir por ";" para ejecutar cada sentencia por separado
        $queries = explode(';', $sql);
        
        $success = true;
        foreach ($queries as $query) {
            $query = trim($query);
            if (!empty($query)) {
                try {
                    $conn->exec($query);
                    echo "<p style='color:green'>Consulta ejecutada con éxito: " . substr($query, 0, 50) . "...</p>";
                } catch (PDOException $e) {
                    echo "<p style='color:red'>Error en consulta: " . $e->getMessage() . "</p>";
                    $success = false;
                }
            }
        }
        
        if ($success) {
            echo "<p style='color:green'>Esquema instalado correctamente.</p>";
        } else {
            echo "<p style='color:red'>Hubo errores al instalar el esquema.</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color:red'>Error al instalar el esquema: " . $e->getMessage() . "</p>";
    }
}

// Si existe un usuario en la sesión, probemos crear una organización
session_start();
echo "<h2>Información de sesión</h2>";
if (isset($_SESSION['user_id'])) {
    echo "<p>Usuario ID: " . $_SESSION['user_id'] . "</p>";
    echo "<p>Nombre: " . ($_SESSION['user_name'] ?? 'No disponible') . "</p>";
    
    // Ver si el usuario ya está en alguna organización
    echo "<h2>Verificando organizaciones del usuario</h2>";
    try {
        $org = new Organization($conn);
        $result = $org->getUserOrganizations($_SESSION['user_id']);
        
        echo "<pre>";
        print_r($result);
        echo "</pre>";
    } catch (Exception $e) {
        echo "<p style='color:red'>Error al obtener organizaciones: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color:orange'>No hay usuario en sesión actualmente.</p>";
}

// Enlaces para otras acciones
echo "<h2>Acciones</h2>";
echo "<ul>";
echo "<li><a href='debug-org.php?install=true'>Instalar esquema de organizaciones (método actual)</a></li>";
echo "<li><a href='#' id='installOrgsOnly'>Instalar SOLO tablas de organizaciones (recomendado)</a></li>";
echo "<li><a href='auth.html'>Ir a la página de autenticación</a></li>";
echo "<li><a href='home.html'>Ir al home</a></li>";
echo "</ul>";

// Agregar JavaScript para el botón de instalación específica
echo "
<script>
document.getElementById('installOrgsOnly').addEventListener('click', function(e) {
    e.preventDefault();
    
    if (confirm('¿Estás seguro de que deseas instalar solo las tablas de organizaciones?')) {
        fetch('../install/install_organizations.php')
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.success) {
                    location.reload();
                }
            })
            .catch(error => {
                alert('Error: ' + error);
            });
    }
});
</script>
";
