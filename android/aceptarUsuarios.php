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

$accion = $_POST['accion'] ?? null;
$correos = json_decode($_POST['correos'] ?? '[]');

if ($accion && !empty($correos)) {
    $placeholders = implode(',', array_fill(0, count($correos), '?'));

    if ($accion === 'aceptar') {
        // Cambiar el estado a "activo"
        $stmt = $conn->prepare("UPDATE usuarios SET estado = 'activo' WHERE correo IN ($placeholders)");
    } elseif ($accion === 'rechazar') {
        // Eliminar usuarios rechazados
        $stmt = $conn->prepare("DELETE FROM usuarios WHERE correo IN ($placeholders)");
    } else {
        echo json_encode(["error" => "Acción no válida"]);
        exit;
    }

    $types = str_repeat('s', count($correos));
    $stmt->bind_param($types, ...$correos);

    if ($stmt->execute()) {
        $mensaje = $accion === 'aceptar' ? "Usuarios aceptados correctamente" : "Usuarios rechazados y eliminados correctamente";
        echo json_encode(["message" => $mensaje]);
    } else {
        echo json_encode(["error" => "Error al realizar la operación: " . $conn->error]);
    }
} else {
    echo json_encode(["error" => "Datos incompletos"]);
}

$conn->close();
?>
