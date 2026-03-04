<?php
header('Content-Type: application/json');
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "l&r";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = $_POST['correo'] ?? '';
    $nombre = $_POST['nombre'] ?? '';
    $apellidos = $_POST['apellidos'] ?? '';
    $fechanacimiento = $_POST['fechanacimiento'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $dni = $_POST['dni'] ?? '';

    if (empty($correo) || empty($nombre) || empty($apellidos) || empty($fechanacimiento) || empty($telefono) || empty($dni)) {
        echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios']);
        exit;
    }

    $sql = "UPDATE usuarios SET nombre=?, apellidos=?, fechanacimiento=?, telefono=?, dni=? WHERE correo=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $nombre, $apellidos, $fechanacimiento, $telefono, $dni, $correo);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Perfil actualizado correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar el perfil']);
    }

    $stmt->close();
}

$conn->close();
?>
