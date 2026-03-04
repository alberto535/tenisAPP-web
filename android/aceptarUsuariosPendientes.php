<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Datos de conexión
$servername = "localhost";
$username = "root";
$password = "";
$database = "l&r";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die(json_encode(["error" => "Conexión fallida: " . $conn->connect_error]));
}

$sql = "SELECT nombre, apellidos, dni, fecha_nacimiento, telefono, correo FROM usuarios WHERE estado = 'pendiente'";
$result = $conn->query($sql);

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
