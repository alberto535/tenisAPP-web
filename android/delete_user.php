<?php
/*
    Autor: Alberto Ortiz Arribas
    Fecha: 30-04-2025
    Resumen: Borra a los jugadores seleccionados de las tablas 'usuarios' y 'clasificacion'.
*/

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");

$servername = "localhost";
$username = "root";
$password = "";
$database = "l&r";

$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die(json_encode(["error" => "Conexión fallida: " . $conn->connect_error]));
}

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['correos']) && is_array($data['correos']) && count($data['correos']) > 0) {
    $correos = $data['correos'];
    $placeholders = implode(',', array_fill(0, count($correos), '?'));
    $types = str_repeat('s', count($correos));

    // Preparar y ejecutar borrado en tabla usuarios
    $stmtUsuarios = $conn->prepare("DELETE FROM usuarios WHERE correo IN ($placeholders)");
    $stmtUsuarios->bind_param($types, ...$correos);
    $successUsuarios = $stmtUsuarios->execute();
    $stmtUsuarios->close();

    // Preparar y ejecutar borrado en tabla clasificacion
    $stmtClasificacion = $conn->prepare("DELETE FROM clasificacion WHERE correo IN ($placeholders)");
    $stmtClasificacion->bind_param($types, ...$correos);
    $successClasificacion = $stmtClasificacion->execute();
    $stmtClasificacion->close();

    if ($successUsuarios && $successClasificacion) {
        echo json_encode(["success" => true, "message" => "Usuarios eliminados exitosamente de ambas tablas."]);
    } else {
        echo json_encode(["success" => false, "message" => "Error al eliminar usuarios."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "No se recibieron correos válidos."]);
}

$conn->close();
?>
