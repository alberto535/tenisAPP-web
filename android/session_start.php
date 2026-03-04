<?php
session_start();
header('Content-Type: application/json');

// Verifica si el usuario está autenticado
if (isset($_SESSION['nombreUsuario'])) {
    echo json_encode(["usuario" => $_SESSION['nombreUsuario']]);
} else {
    echo json_encode(["error" => "Usuario no autenticado"]);
}
?>
