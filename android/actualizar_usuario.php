<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

$conn = new mysqli('localhost', 'root', '', 'l&r');

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dni = $_POST['dni'] ?? null;
    $nombre = $_POST['nombre'] ?? null;
    $apellidos = $_POST['apellidos'] ?? null;
    $correo = $_POST['correo'] ?? null;
    $fechanacimiento = $_POST['fecha_nacimiento'] ?? null;
    $telefono = $_POST['telefono'] ?? null;

    if (!$dni || !$nombre || !$apellidos || !$correo || !$fechanacimiento || !$telefono) {
        echo json_encode(['success' => false, 'message' => 'Todos los campos son requeridos']);
        exit;
    }

    $stmt = $conn->prepare("UPDATE usuarios SET nombre=?, apellidos=?, correo=?, fecha_nacimiento=?, telefono=? WHERE dni=?");
    $stmt->bind_param('ssssss', $nombre, $apellidos, $correo, $fechanacimiento, $telefono, $dni);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Usuario actualizado correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar usuario']);
    }

    $stmt->close();
}

$conn->close();
?>
