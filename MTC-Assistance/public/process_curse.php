<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $curseName = isset($_POST['curse_name']) ? $_POST['curse_name'] : '';
    
    if (!empty($curseName)) {
        // Aquí puedes agregar la lógica para guardar en base de datos si lo necesitas
        
        echo json_encode([
            'success' => true,
            'message' => 'Área creada exitosamente'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'El nombre del área es requerido'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido'
    ]);
}
?>