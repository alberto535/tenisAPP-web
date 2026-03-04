<?php
/*
    Autor: Alberto Ortiz Arribas
    Fecha: 26-03-2025
    Resumen: Extrae informacion de la tabla usuarios.
*/

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Content-Type: application/json; charset=UTF-8");

$servername = "localhost";
$username = "root";
$password = "";
$database = "l&r";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die(json_encode(["error" => "Conexión fallida: " . $conn->connect_error]));
}

$query = "SELECT nombre, apellidos, telefono, dni, fecha_nacimiento, correo FROM usuarios";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    $usuarios = [];
    while ($row = $result->fetch_assoc()) {
        $usuarios[] = $row;
    }
    echo json_encode(["usuarios" => $usuarios]);
} else {
    echo json_encode(["usuarios" => []]);
}

$conn->close();
?>
