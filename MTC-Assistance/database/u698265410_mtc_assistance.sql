-- Tabla de usuarios (estructura original)
CREATE TABLE usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'user') DEFAULT 'user',
    estado ENUM('activo', 'inactivo') DEFAULT 'activo',
    ultimo_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de organizaciones (nueva estructura)
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

-- Tabla de relación entre usuarios y organizaciones (nueva estructura)
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

-- Tabla de áreas (con organizacion_id incluido)
CREATE TABLE areas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    usuario_id INT NOT NULL,
    organizacion_id INT NOT NULL,
    estado ENUM('activo', 'inactivo') DEFAULT 'activo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (organizacion_id) REFERENCES organizaciones(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

-- Tabla de asistentes (con organizacion_id incluido)
CREATE TABLE asistentes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    telefono VARCHAR(20),
    area_id INT NOT NULL,
    usuario_id INT NOT NULL,
    organizacion_id INT NOT NULL,
    estado ENUM('activo', 'inactivo') DEFAULT 'activo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (area_id) REFERENCES areas(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (organizacion_id) REFERENCES organizaciones(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);

-- Tabla de asistencias (con organizacion_id incluido)
CREATE TABLE asistencias (
    id INT PRIMARY KEY AUTO_INCREMENT,
    asistente_id INT NOT NULL,
    area_id INT NOT NULL,
    usuario_id INT NOT NULL,
    organizacion_id INT NOT NULL,
    fecha DATE NOT NULL,
    estado ENUM('presente', 'ausente', 'justificado') NOT NULL DEFAULT 'ausente',
    observacion TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (asistente_id) REFERENCES asistentes(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (area_id) REFERENCES areas(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (organizacion_id) REFERENCES organizaciones(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    UNIQUE KEY unique_asistencia (asistente_id, area_id, fecha)
);

-- Índices para mejorar el rendimiento
CREATE INDEX idx_areas_organizacion ON areas(organizacion_id);
CREATE INDEX idx_asistentes_organizacion ON asistentes(organizacion_id);
CREATE INDEX idx_asistencias_organizacion ON asistencias(organizacion_id);