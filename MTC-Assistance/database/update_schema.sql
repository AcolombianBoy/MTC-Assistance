USE mtc_assistance;

-- Crear tabla de organizaciones
CREATE TABLE organizaciones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    codigo_unico VARCHAR(20) NOT NULL UNIQUE,
    logo VARCHAR(255),
    estado ENUM('activo', 'inactivo') DEFAULT 'activo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Crear tabla de relación entre usuarios y organizaciones
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
);

-- Modificar tabla de áreas para relacionarla con organizaciones
ALTER TABLE areas
ADD COLUMN organizacion_id INT AFTER usuario_id,
ADD FOREIGN KEY (organizacion_id) REFERENCES organizaciones(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE;

-- Modificar tabla de asistentes para actualizar la referencia
ALTER TABLE asistentes
ADD COLUMN organizacion_id INT AFTER usuario_id,
ADD FOREIGN KEY (organizacion_id) REFERENCES organizaciones(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE;

-- Modificar tabla de asistencias para actualizar la referencia
ALTER TABLE asistencias
ADD COLUMN organizacion_id INT AFTER usuario_id,
ADD FOREIGN KEY (organizacion_id) REFERENCES organizaciones(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE;

-- Crear índices para mejorar el rendimiento
CREATE INDEX idx_areas_organizacion ON areas(organizacion_id);
CREATE INDEX idx_asistentes_organizacion ON asistentes(organizacion_id);
CREATE INDEX idx_asistencias_organizacion ON asistencias(organizacion_id);
