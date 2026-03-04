<?php
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['nombreUsuario'])) {
    echo json_encode(["error" => "Usuario no autenticado"]);
} else {
    echo json_encode(["usuario" => $_SESSION['nombreUsuario']]);
}
?>
