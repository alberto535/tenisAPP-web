<?php
header("Content-Type: application/json");

$servername = "localhost";
$username = "root";
$password = "";
$database = "l&r";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die(json_encode(["error" => "Conexión fallida: " . $conn->connect_error]));
}

$response = array();

$sql = "SELECT nombre, apellidos, correo FROM usuarios WHERE liga IS NULL";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $usuarios = array();
    while ($row = $result->fetch_assoc()) {
        $usuarios[] = $row;
    }
    $response['usuarios'] = $usuarios;
} else {
    $response['usuarios'] = [];
}

echo json_encode($response);
$conn->close();
?>
