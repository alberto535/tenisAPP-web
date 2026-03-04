<?php
header('Content-Type: application/json'); // Cabecera para JSON

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

$response = ["success" => false, "message" => "Error desconocido"]; // Respuesta por defecto

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Método no permitido';
    echo json_encode($response);
    exit;
}

$correo = $_POST['correo'] ?? null;
$password = $_POST['contraseña'] ?? null;

if (!$correo || !$password) {
    $response['message'] = 'Correo y contraseña son requeridos';
    echo json_encode($response);
    exit;
}

$conn = new mysqli('localhost', 'root', '', 'l&r');
if ($conn->connect_error) {
    $response['message'] = 'Error de conexión: ' . $conn->connect_error;
    echo json_encode($response);
    exit;
}

// 1. Verificar si el usuario es administrador
$stmt = $conn->prepare("SELECT nombre, correo, contraseña FROM administradores WHERE correo = ?");
$stmt->bind_param("s", $correo);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();

    if (password_verify($password, $row['contraseña'])) {
        $_SESSION['nombreUsuario'] = $row['nombre'];
        $_SESSION['correo'] = $row['correo'];
        $_SESSION['contraseña'] = $row['contraseña']; // ⚠️ No recomendable guardar la contraseña en la sesión

        $response = [
            "success" => true,
            "role" => "admin",
            "message" => "Inicio de sesión exitoso como administrador"
        ];
    } else {
        $response['message'] = 'Contraseña incorrecta';
    }

    echo json_encode($response);
    $stmt->close();
    $conn->close();
    exit;
}

// 2. Si no es administrador, verificar en la tabla usuarios
$stmt = $conn->prepare("SELECT nombre, correo, contraseña, estado FROM usuarios WHERE correo = ?");
$stmt->bind_param("s", $correo);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();


    if (!password_verify($password, $row['contraseña'])) {
        $response['message'] = 'Contraseña incorrecta';
        echo json_encode($response);
        exit;
    }

    if ($row['estado'] !== 'activo') {
        $response['message'] = 'La cuenta no ha sido validada aún';
        echo json_encode($response);
        exit;
    }

    $_SESSION['nombreUsuario'] = $row['nombre'];
    $_SESSION['correo'] = $row['correo'];
    $_SESSION['contraseña'] = $row['contraseña']; // ⚠️ No recomendable guardar la contraseña en la sesión

    $response = [
        "success" => true,
        "role" => "user",
        "message" => "Inicio de sesión exitoso"
    ];
} else {
    $response['message'] = 'Usuario no encontrado';
}

echo json_encode($response);
$stmt->close();
$conn->close();
?>
