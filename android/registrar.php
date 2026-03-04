<?php
require __DIR__ . '/vendor/autoload.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

ini_set('display_errors', 1);
error_reporting(E_ALL);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;



$conexion = new mysqli('localhost', 'root', '', 'l&r');
if ($conexion->connect_error) {
    echo json_encode(['message' => 'Error en la conexión a la base de datos']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $conexion->real_escape_string($_POST['nombre']);
    $apellidos = $conexion->real_escape_string($_POST['apellidos']);
    $dni = $conexion->real_escape_string($_POST['dni']);
    $fecha_nacimiento = $conexion->real_escape_string($_POST['fecha_nacimiento']);
    $telefono = $conexion->real_escape_string($_POST['telefono']);
    $correo = $conexion->real_escape_string($_POST['correo']);
    $contraseñaInput = $_POST['contraseña'];
    $confirm_password = $_POST['confirm_password']; // <- nombre de campo corregido

    // Verificar que las contraseñas coincidan
    if ($contraseñaInput !== $confirm_password) {
        echo json_encode(['message' => 'Las contraseñas no coinciden']);
        exit;
    }

    // Validaciones varias
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha_nacimiento)) {
        echo json_encode(['message' => 'La fecha de nacimiento debe tener el formato yyyy-mm-dd']);
        exit;
    }
    $d = DateTime::createFromFormat('Y-m-d', $fecha_nacimiento);
    if (!($d && $d->format('Y-m-d') === $fecha_nacimiento)) {
        echo json_encode(['message' => 'La fecha de nacimiento no es válida']);
        exit;
    }

    if (!preg_match('/^\d{9}$/', $telefono)) {
        echo json_encode(['message' => 'El teléfono debe tener exactamente 9 dígitos']);
        exit;
    }

    if (!preg_match('/^\d{8}[A-Z]$/', $dni)) {
        echo json_encode(['message' => 'El DNI debe tener 8 números y una letra mayúscula al final']);
        exit;
    }

    $query = $conexion->prepare('SELECT correo FROM usuarios WHERE correo = ?');
    $query->bind_param('s', $correo);
    $query->execute();
    $query->store_result();

    if ($query->num_rows > 0) {
        echo json_encode(['message' => 'El correo ya está registrado']);
        exit;
    }
    $query->close();

    $pattern = "/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/";
    if (!preg_match($pattern, $contraseñaInput)) {
        echo json_encode(['message' => 'La contraseña debe tener al menos 8 caracteres, incluir una letra, un número y un carácter especial.']);
        exit;
    }

    $contraseña = password_hash($contraseñaInput, PASSWORD_BCRYPT);
    $estado = 'pendiente';
    $division = 0;

    $stmt = $conexion->prepare('INSERT INTO usuarios (nombre, apellidos, dni, fecha_nacimiento, telefono, correo, contraseña, estado, division) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('ssssssssi', $nombre, $apellidos, $dni, $fecha_nacimiento, $telefono, $correo, $contraseña, $estado, $division);

    if ($stmt->execute()) {
        if (enviarCorreoConfirmacion($correo, $nombre)) {
            echo json_encode(['message' => 'Registro exitoso. Se ha enviado un correo de confirmación.']);
        } else {
            echo json_encode(['message' => 'Registro exitoso, pero no se pudo enviar el correo de confirmación.']);
        }
    } else {
        echo json_encode(['message' => 'Error al registrar el usuario']);
    }

    $stmt->close();
    $conexion->close();
} else {
    echo json_encode(['message' => 'Método no permitido']);
}

function enviarCorreoConfirmacion($correo, $nombre) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'control9864@gmail.com';
        $mail->Password = 'uohj asqi ioqf nfrg';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('control9864@gmail.com', 'Equipo de Soporte');
        $mail->addAddress($correo, $nombre);

        $mail->isHTML(true);
        $mail->Subject = 'Confirmación de Registro';
        $mail->Body = "
            <h2>¡Hola, $nombre!</h2>
            <p>Gracias por registrarte en nuestra plataforma.</p>
            <p>$nombre, bienvenido a nuestro sistema. A partir de ahora podrás acceder usando tu correo: $correo y tu contraseña. También puedes acceder a través de nuestra aplicación.</p>
            <p>Si no solicitaste este registro, por favor ignora este mensaje.</p>
            <br>
            <p>Atentamente, <br>Equipo de Soporte</p>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
?>
