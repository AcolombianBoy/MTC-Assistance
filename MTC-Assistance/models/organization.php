<?php
// No incluimos database.php aquí para evitar duplicados
// El archivo que use este modelo debe incluir la conexión

class Organization {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    // Crear una nueva organización
    public function create($nombre, $descripcion, $codigo_unico, $logo = null) {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO organizaciones (nombre, descripcion, codigo_unico, logo) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$nombre, $descripcion, $codigo_unico, $logo]);
            
            return [
                'success' => true,
                'id' => $this->conn->lastInsertId(),
                'message' => 'Organización creada con éxito'
            ];
        } catch(PDOException $e) {
            if ($e->getCode() == 23000) { // Duplicate entry
                return [
                    'success' => false,
                    'message' => 'El código único ya está en uso'
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Error al crear la organización: ' . $e->getMessage()
            ];
        }
    }
    
    // Actualizar una organización
    public function update($id, $nombre, $descripcion, $logo = null) {
        try {
            $query = "
                UPDATE organizaciones 
                SET nombre = ?, descripcion = ?
            ";
            $params = [$nombre, $descripcion];
            
            if ($logo !== null) {
                $query .= ", logo = ?";
                $params[] = $logo;
            }
            
            $query .= " WHERE id = ?";
            $params[] = $id;
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            
            return [
                'success' => $stmt->rowCount() > 0,
                'message' => $stmt->rowCount() > 0 ? 'Organización actualizada con éxito' : 'No se realizaron cambios'
            ];
        } catch(PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al actualizar la organización: ' . $e->getMessage()
            ];
        }
    }
    
    // Obtener una organización por ID
    public function getById($id) {
        try {
            $stmt = $this->conn->prepare("
                SELECT * FROM organizaciones WHERE id = ? AND estado = 'activo'
            ");
            $stmt->execute([$id]);
            
            if ($stmt->rowCount() === 0) {
                return [
                    'success' => false,
                    'message' => 'Organización no encontrada'
                ];
            }
            
            return [
                'success' => true,
                'data' => $stmt->fetch(PDO::FETCH_ASSOC)
            ];
        } catch(PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener la organización: ' . $e->getMessage()
            ];
        }
    }
    
    // Obtener una organización por código único
    public function getByCode($codigo) {
        try {
            $stmt = $this->conn->prepare("
                SELECT * FROM organizaciones WHERE codigo_unico = ? AND estado = 'activo'
            ");
            $stmt->execute([$codigo]);
            
            if ($stmt->rowCount() === 0) {
                return [
                    'success' => false,
                    'message' => 'Organización no encontrada'
                ];
            }
            
            return [
                'success' => true,
                'data' => $stmt->fetch(PDO::FETCH_ASSOC)
            ];
        } catch(PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener la organización: ' . $e->getMessage()
            ];
        }
    }
    
    // Obtener todas las organizaciones
    public function getAll() {
        try {
            $stmt = $this->conn->prepare("
                SELECT * FROM organizaciones WHERE estado = 'activo' ORDER BY nombre
            ");
            $stmt->execute();
            
            return [
                'success' => true,
                'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)
            ];
        } catch(PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener las organizaciones: ' . $e->getMessage()
            ];
        }
    }
    
    // Cambiar estado de una organización
    public function changeStatus($id, $estado) {
        try {
            $stmt = $this->conn->prepare("
                UPDATE organizaciones SET estado = ? WHERE id = ?
            ");
            $stmt->execute([$estado, $id]);
            
            return [
                'success' => $stmt->rowCount() > 0,
                'message' => $stmt->rowCount() > 0 ? 'Estado actualizado con éxito' : 'No se realizaron cambios'
            ];
        } catch(PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al cambiar el estado: ' . $e->getMessage()
            ];
        }
    }
    
    // Relacionar un usuario con una organización
    public function addUser($organizacion_id, $usuario_id, $rol = 'miembro') {
        try {
            $stmt = $this->conn->prepare("
                INSERT INTO usuarios_organizaciones (usuario_id, organizacion_id, rol)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$usuario_id, $organizacion_id, $rol]);
            
            return [
                'success' => true,
                'message' => 'Usuario añadido a la organización con éxito'
            ];
        } catch(PDOException $e) {
            if ($e->getCode() == 23000) { // Duplicate entry
                // Intentar actualizar el rol si ya existe
                try {
                    $updateStmt = $this->conn->prepare("
                        UPDATE usuarios_organizaciones 
                        SET rol = ? 
                        WHERE usuario_id = ? AND organizacion_id = ?
                    ");
                    $updateStmt->execute([$rol, $usuario_id, $organizacion_id]);
                    
                    return [
                        'success' => true,
                        'message' => 'Rol del usuario actualizado en la organización'
                    ];
                } catch(PDOException $updateError) {
                    return [
                        'success' => false,
                        'message' => 'Error al actualizar el rol del usuario: ' . $updateError->getMessage()
                    ];
                }
            }
            
            return [
                'success' => false,
                'message' => 'Error al añadir el usuario a la organización: ' . $e->getMessage()
            ];
        }
    }
    
    // Obtener organizaciones a las que pertenece un usuario
    public function getUserOrganizations($usuario_id) {
        try {
            $stmt = $this->conn->prepare("
                SELECT o.*, uo.rol
                FROM organizaciones o
                JOIN usuarios_organizaciones uo ON o.id = uo.organizacion_id
                WHERE uo.usuario_id = ? AND o.estado = 'activo'
                ORDER BY o.nombre
            ");
            $stmt->execute([$usuario_id]);
            
            return [
                'success' => true,
                'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)
            ];
        } catch(PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener las organizaciones del usuario: ' . $e->getMessage()
            ];
        }
    }
    
    // Verificar si un usuario tiene acceso a una organización
    public function checkUserAccess($usuario_id, $organizacion_id) {
        try {
            $stmt = $this->conn->prepare("
                SELECT rol FROM usuarios_organizaciones
                WHERE usuario_id = ? AND organizacion_id = ?
            ");
            $stmt->execute([$usuario_id, $organizacion_id]);
            
            if ($stmt->rowCount() === 0) {
                return [
                    'success' => false,
                    'message' => 'El usuario no tiene acceso a esta organización'
                ];
            }
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return [
                'success' => true,
                'rol' => $result['rol']
            ];
        } catch(PDOException $e) {
            return [
                'success' => false,
                'message' => 'Error al verificar el acceso: ' . $e->getMessage()
            ];
        }
    }
    
    // Generar un código único aleatorio
    public static function generateUniqueCode($length = 8) {
        $chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $code = '';
        
        for ($i = 0; $i < $length; $i++) {
            $code .= $chars[rand(0, strlen($chars) - 1)];
        }
        
        return $code;
    }
}
