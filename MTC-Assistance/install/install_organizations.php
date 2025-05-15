<?php
// Archivo para instalar específicamente las tablas de organizaciones
// Ruta: c:\xampp\htdocs\mtca\MTC-Assistance\MTC-Assistance\install\install_organizations.php

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
    
    // Verificar si ya existe la tabla de organizaciones
    $stmt = $conn->prepare("SHOW TABLES LIKE 'organizaciones'");
    $stmt->execute();
    $organizationTableExists = $stmt->rowCount() > 0;
    
    if ($organizationTableExists) {
        echo json_encode(['success' => true, 'message' => 'La tabla de organizaciones ya existe.']);
        exit;
    }
    
    // Crear tabla de organizaciones
    $conn->exec("
        CREATE TABLE organizaciones (
            id INT PRIMARY KEY AUTO_INCREMENT,
            nombre VARCHAR(100) NOT NULL,
            descripcion TEXT,
            codigo_unico VARCHAR(20) NOT NULL UNIQUE,
            logo VARCHAR(255),
            estado ENUM('activo', 'inactivo') DEFAULT 'activo',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");
    
    // Crear tabla de relación entre usuarios y organizaciones
    $conn->exec("
        CREATE TABLE usuarios_organizaciones (
            id INT PRIMARY KEY AUTO_INCREMENT,
            usuario_id INT NOT NULL,
            organizacion_id INT NOT NULL,
            rol ENUM('admin', 'editor', 'miembro') NOT NULL DEFAULT 'miembro',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
                ON DELETE CASCADE
                ON UPDATE CASCADE,
            FOREIGN KEY (organizacion_id) REFERENCES organizaciones(id)
                ON DELETE CASCADE
                ON UPDATE CASCADE,
            UNIQUE KEY unique_usuario_org (usuario_id, organizacion_id)
        )
    ");
    
    // Modificar tabla de áreas para relacionarla con organizaciones
    // Verificar si la tabla de áreas existe y tiene la columna
    $stmt = $conn->prepare("SHOW TABLES LIKE 'areas'");
    $stmt->execute();
    if ($stmt->rowCount() > 0) {
        $stmt = $conn->prepare("SHOW COLUMNS FROM areas LIKE 'organizacion_id'");
        $stmt->execute();
        if ($stmt->rowCount() == 0) {
            $conn->exec("
                ALTER TABLE areas
                ADD COLUMN organizacion_id INT AFTER usuario_id,
                ADD FOREIGN KEY (organizacion_id) REFERENCES organizaciones(id)
                    ON DELETE CASCADE
                    ON UPDATE CASCADE
            ");
        }
    }
    
    // Modificar tabla de asistentes para actualizar la referencia
    // Verificar si la tabla de asistentes existe y tiene la columna
    $stmt = $conn->prepare("SHOW TABLES LIKE 'asistentes'");
    $stmt->execute();
    if ($stmt->rowCount() > 0) {
        $stmt = $conn->prepare("SHOW COLUMNS FROM asistentes LIKE 'organizacion_id'");
        $stmt->execute();
        if ($stmt->rowCount() == 0) {
            $conn->exec("
                ALTER TABLE asistentes
                ADD COLUMN organizacion_id INT AFTER usuario_id,
                ADD FOREIGN KEY (organizacion_id) REFERENCES organizaciones(id)
                    ON DELETE CASCADE
                    ON UPDATE CASCADE
            ");
        }
    }
    
    // Modificar tabla de asistencias para actualizar la referencia
    // Verificar si la tabla de asistencias existe y tiene la columna
    $stmt = $conn->prepare("SHOW TABLES LIKE 'asistencias'");
    $stmt->execute();
    if ($stmt->rowCount() > 0) {
        $stmt = $conn->prepare("SHOW COLUMNS FROM asistencias LIKE 'organizacion_id'");
        $stmt->execute();
        if ($stmt->rowCount() == 0) {
            $conn->exec("
                ALTER TABLE asistencias
                ADD COLUMN organizacion_id INT AFTER usuario_id,
                ADD FOREIGN KEY (organizacion_id) REFERENCES organizaciones(id)
                    ON DELETE CASCADE
                    ON UPDATE CASCADE
            ");
        }
    }
    
    // Crear índices para mejorar el rendimiento
    $conn->exec("CREATE INDEX IF NOT EXISTS idx_areas_organizacion ON areas(organizacion_id)");
    $conn->exec("CREATE INDEX IF NOT EXISTS idx_asistentes_organizacion ON asistentes(organizacion_id)");
    $conn->exec("CREATE INDEX IF NOT EXISTS idx_asistencias_organizacion ON asistencias(organizacion_id)");
    
    // Crear una organización por defecto y asignar al usuario actual como admin
    $stmt = $conn->prepare("SELECT id FROM usuarios LIMIT 1");
    $stmt->execute();
    if ($stmt->rowCount() > 0) {
        $userId = $stmt->fetch(PDO::FETCH_ASSOC)['id'];
        
        // Crear la organización por defecto
        $stmt = $conn->prepare("
            INSERT INTO organizaciones (nombre, descripcion, codigo_unico) 
            VALUES (?, ?, ?)
        ");
        $codigo = 'ORG-' . strtoupper(substr(md5(uniqid()), 0, 8));
        $stmt->execute(['Organización Predeterminada', 'Esta es la organización predeterminada del sistema.', $codigo]);
        $orgId = $conn->lastInsertId();
        
        // Asignar el primer usuario como admin
        $stmt = $conn->prepare("
            INSERT INTO usuarios_organizaciones (usuario_id, organizacion_id, rol) 
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$userId, $orgId, 'admin']);
    }
    
    echo json_encode(['success' => true, 'message' => 'Tablas de organizaciones creadas correctamente.']);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error al crear las tablas: ' . $e->getMessage()]);
}
