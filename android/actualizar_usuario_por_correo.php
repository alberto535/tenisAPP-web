<?php
/*
    Autor: Alberto Ortiz Arribas
    Fecha: 30-04-2025
    Resumen: Actualiza los datos de un usuario en las tablas 'usuarios' y 'clasificacion' basándose en su correo.
*/

header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Método no permitido"]);
    exit;
}

$correo = $_POST['correo'] ?? '';
$nombre = $_POST['nombre'] ?? '';
$apellidos = $_POST['apellidos'] ?? '';
$fecha_nacimiento = $_POST['fecha_nacimiento'] ?? '';
$telefono = $_POST['telefono'] ?? '';

if (empty($correo)) {
    echo json_encode(["success" => false, "message" => "Correo requerido"]);
    exit;
}

$conn = new mysqli("localhost", "root", "", "l&r");
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Error de conexión"]);
    exit;
}

// Actualizar tabla usuarios
$sql1 = "UPDATE usuarios SET nombre=?, apellidos=?, fecha_nacimiento=?, telefono=? WHERE correo=?";
$stmt1 = $conn->prepare($sql1);
$stmt1->bind_param("sssss", $nombre, $apellidos, $fecha_nacimiento, $telefono, $correo);
$result1 = $stmt1->execute();

// Actualizar tabla clasificacion
$sql2 = "UPDATE clasificacion SET nombre=?, apellidos=? WHERE correo=?";
$stmt2 = $conn->prepare($sql2);
$stmt2->bind_param("sss", $nombre, $apellidos, $correo);
$result2 = $stmt2->execute();

if ($result1 && $result2) {
    echo json_encode(["success" => true, "message" => "Datos actualizados correctamente"]);
} else {
    echo json_encode(["success" => false, "message" => "Error al actualizar los datos"]);
}

$stmt1->close();
$stmt2->close();
$conn->close();
?>
