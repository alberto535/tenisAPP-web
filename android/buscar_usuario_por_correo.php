<?php
/*
    Autor: Alberto Ortiz Arribas
    Fecha: 20-03-2025
    Resumen: Devuelve los datos de un usuario basándose en su correo (recibido por POST).
*/

header("Content-Type: application/json");

// Validación básica
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['correo'])) {
    echo json_encode(["success" => false, "message" => "Correo no proporcionado"]);
    exit;
}

$correo = $_POST['correo'];

$conn = new mysqli("localhost", "root", "", "l&r");
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Error de conexión"]);
    exit;
}

// Buscar usuario
$sql = "SELECT dni, nombre, apellidos, correo, fecha_nacimiento, telefono FROM usuarios WHERE correo = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $correo);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $usuario = $result->fetch_assoc();
    echo json_encode(["success" => true, "data" => $usuario]);
} else {
    echo json_encode(["success" => false, "message" => "Usuario no encontrado"]);
}

$stmt->close();
$conn->close();
?>
