<?php
file_put_contents('log.txt', "== Inicio de solicitud ==\n", FILE_APPEND);

require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';
require 'PHPMailer-master/src/Exception.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = $_POST['correo'] ?? null;
    file_put_contents('log.txt', "Correo recibido: $correo\n", FILE_APPEND);

    if (!$correo) {
        echo json_encode(['message' => 'Correo no proporcionado']);
        file_put_contents('log.txt', "Correo no proporcionado\n", FILE_APPEND);
        exit;
    }

    $conexion = new mysqli('localhost', 'root', '', 'l&r');
    if ($conexion->connect_error) {
        echo json_encode(['message' => 'Error al conectar con la base de datos']);
        file_put_contents('log.txt', "Error de conexión DB: {$conexion->connect_error}\n", FILE_APPEND);
        exit;
    }

    $usuarioData = null;
    $tabla = null;

    // Buscar en usuarios
    $query = $conexion->prepare('SELECT * FROM usuarios WHERE correo = ?');
    $query->bind_param('s', $correo);
    $query->execute();
    $resultado = $query->get_result();

    if ($resultado->num_rows > 0) {
        $usuarioData = $resultado->fetch_assoc();
        $tabla = 'usuarios';
        file_put_contents('log.txt', "Correo encontrado en tabla usuarios\n", FILE_APPEND);
    } else {
        // Buscar en administradores si no se encuentra en usuarios
        $queryAdmin = $conexion->prepare('SELECT * FROM administradores WHERE correo = ?');
        $queryAdmin->bind_param('s', $correo);
        $queryAdmin->execute();
        $resultadoAdmin = $queryAdmin->get_result();

        if ($resultadoAdmin->num_rows > 0) {
            $usuarioData = $resultadoAdmin->fetch_assoc();
            $tabla = 'administradores';
            file_put_contents('log.txt', "Correo encontrado en tabla administradores\n", FILE_APPEND);
        }
    }

    if (!$usuarioData) {
        echo json_encode(['message' => 'El correo no está registrado']);
        file_put_contents('log.txt', "Correo no encontrado en ninguna tabla\n", FILE_APPEND);
        exit;
    }

    // Generar y guardar token
    $token = bin2hex(random_bytes(16));
    file_put_contents('log.txt', "Token generado: $token\n", FILE_APPEND);

    $update = $conexion->prepare("UPDATE $tabla SET token = ? WHERE correo = ?");
    $update->bind_param('ss', $token, $correo);
    $update->execute();
    file_put_contents('log.txt', "Token actualizado en la tabla $tabla\n", FILE_APPEND);

    // Enviar correo
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'control9864@gmail.com';
        $mail->Password = 'uohj asqi ioqf nfrg'; // contraseña de aplicación
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('control9864@gmail.com', 'TFG Android');
        $mail->addAddress($correo);
        $mail->addReplyTo('control9864@gmail.com', 'TFG Android');

        $mail->isHTML(true);
        $mail->Subject = 'Restablece tu contraseña';
        $mail->Body = 'Haz clic en este enlace para restablecer tu contraseña:<br>
        <a href="http://192.168.1.91/cambiar-password.html?token=' . $token . '">Restablecer contraseña</a>';

        $mail->send();
        echo json_encode(['message' => 'Correo enviado correctamente']);
        file_put_contents('log.txt', "✅ Correo enviado correctamente a $correo\n", FILE_APPEND);
    } catch (Exception $e) {
        echo json_encode(['message' => 'Error al enviar el correo']);
        file_put_contents('log.txt', "❌ Error al enviar el correo: {$mail->ErrorInfo}\n", FILE_APPEND);
    }
} else {
    echo json_encode(['message' => 'Método no permitido']);
    file_put_contents('log.txt', "Método no permitido\n", FILE_APPEND);
}
?>
