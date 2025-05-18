<?php
session_start();
session_unset();     // Elimina todas las variables de sesión
session_destroy();   // Destruye la sesión
setcookie(session_name(), '', time()-3600); // Elimina la cookie de sesión

echo json_encode(['success' => true, 'message' => 'Sesión cerrada correctamente']);